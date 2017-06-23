<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class FbBot
{
    private $hubVerifyToken = null;
    private $accessToken = null;
    private $token = false;
    protected $client = null;

    public function setHubVerifyToken($value)
    {
        $this->hubVerifyToken = $value;
    }

    public function setAccessToken($value)
    {
        $this->accessToken = $value;
    }

    function verifyToken($hub_verify_token, $challenge)
    {
        try {
            if ($hub_verify_token === $this->hubVerifyToken) {
                return $challenge;
            }
            else {
                throw new Exception("Token not verified");
            }
        }

        catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function readMessage($input)
    {
        try {
            $payloads = null;
            $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
            $messageText = $input['entry'][0]['messaging'][0]['message']['text'];
            $postback = $input['entry'][0]['messaging'][0]['postback'];
            $loctitle = $input['entry'][0]['messaging'][0]['message']['attachments'][0]['title'];
            if (!empty($postback)) {
                $payloads = $input['entry'][0]['messaging'][0]['postback']['payload'];
                return ['senderid' => $senderId, 'message' => $payloads];
            }

            if (!empty($loctitle)) {
                $payloads = $input['entry'][0]['messaging'][0]['postback']['payload'];
                return ['senderid' => $senderId, 'message' => $messageText, 'location' => $loctitle];
            }

            return ['senderid' => $senderId, 'message' => $messageText];
        }

        catch(Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function sendMessage($input)
    {
        try {
            require "config.php";
            $client = new GuzzleHttp\Client();
            $url = "https://graph.facebook.com/v2.6/me/messages";
            $messageText = strtolower($input['message']);
            $senderId = $input['senderid'];
            $msgarray = explode(' ', $messageText);
            $response = null;
            $header = [
                'content-type' => 'application/json'
            ];

            if (in_array('help', $msgarray)) {
                $answer = "My name is Iris. To send a message, enter a phone number and a slash, followed by your message. For example: 09771234567/Hello, my name is Iris.";
                $response = ['recipient' => ['id' => $senderId], 'message' => ['text' => $answer], 'access_token' => $this->accessToken];
            } else {
                // Try to parse
                $number = substr($msgarray[0], 0, 11);
                $valid = $this->validNumber($number);
                if (!$valid['status']) {
                    $answer = "ERROR: {$valid['message']}";
                } else {
                    $message = substr($msgarray[0], 12);
                    if (strlen($message) > 420) {
                        $answer = "ERROR: Message too long. Maximum is 420 characters.";
                    } else {
                        $arr_post_body = [
                            "message_type"      =>      "SEND",
                            "mobile_number"     =>      $number,
                            "shortcode"         =>      $chikka['shortcode'],
                            "message_id"        =>      $this->generateRandomString(32),
                            "message"           =>      urlencode($message),
                            "client_id"         =>      $chikka['id'],
                            "secret_key"        =>      $chikka['secret']
                        ];

                        $query_string = http_build_query($arr_post_body);
                        $URL = "https://post.chikka.com/smsapi/request";
                        $curl_handler = curl_init();
                        curl_setopt($curl_handler, CURLOPT_URL, $URL);
                        curl_setopt($curl_handler, CURLOPT_POST, count($arr_post_body));
                        curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $query_string);
                        curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, FALSE);
                        $response = curl_exec($curl_handler);
                        curl_close($curl_handler);
                        $resp = json_decode($response);
                        if ($resp->status == 200) {
                            $answer = "Message sent to {$number}!";
                        } else {
                            $answer = "ERROR: Message sending failed! Please try again later. Error code {$resp->status}, query string {$query_string}";
                        }
                    }
                }
                $response = ['recipient' => ['id' => $senderId], 'message' => ['text' => $answer], 'access_token' => $this->accessToken];
            }

            $client->post($url, ['query' => $response, 'headers' => $header]);
            return true;
        }

        catch(RequestException $e) {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            file_put_contents(time() . ".json", json_encode($response));
            return $response;
        }
    }

    public function validNumber($number)
    {
        $return = [];

        if (!is_numeric($number)) {
            $return['status'] = false;
            $return['message'] = 'Mobile number not valid digits.';
        } else if (strlen($number) != 11) {
            $return['status'] = false;
            $return['message'] = 'Mobile number not 11 digits.';
        } else {
            $re = '/[0-9]\d{3}/';
            preg_match_all($re, $number, $matches, PREG_SET_ORDER, 0);
            $allowed = ['0915', '0927', '0995', '0938', '0919', '0813', '0913', '0981', '0934', '0922', '0917', '0935', '0817', '0939', '0921', '0907', '0914', '0998', '0941', '0923', '0945', '0936', '0905', '0940', '0929', '0908', '0918', '0999', '0942', '0924', '0955', '0976', '0906', '0946', '0989', '0909', '0928', '0951', '0943', '0931', '0956', '0997', '0916', '0948', '0920', '0910', '0947', '0912', '0944', '0932', '0994', '0975', '0926', '0950', '0930', '0911', '0949', '0970', '0925', '0933', '0992', '0977', '0978', '0979', '0996', '0937', '0973', '0974'];
            if (!in_array($matches[0][0], $allowed)) {
                $return['status'] = false;
                $return['message'] = 'Prefix ' . $matches[0][0] . ' not in allowed list.';
            } else {
                $return['status'] = true;
                $return['message'] = null;
            }
        }
        return $return;
    }

    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}