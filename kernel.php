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
                if (!$this->validNumber($number)) {
                    $answer = "Sorry, you've entered an invalid message format. Make sure you're using the 11-digit mobile number. The format is <11-digit mobile number>/Your message.";
                    $response = ['recipient' => ['id' => $senderId], 'message' => ['text' => print_r($msgarray, true)], 'access_token' => $this->accessToken];
                    $client->post($url, ['query' => $response, 'headers' => $header]);
                    return true;
                } else {
                    $message = substr($msgarray[0], 12);
                    $answer = "You're sending this to {$number}:\n\n{$message}";
                    $response = ['recipient' => ['id' => $senderId], 'message' => ['text' => print_r($msgarray, true)], 'access_token' => $this->accessToken];
                }
            }

            $response = $client->post($url, ['query' => $response, 'headers' => $header]);
            return true;
        }

        catch(RequestException $e) {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            file_put_contents("test.json", json_encode($response));
            return $response;
        }
    }

    public function validNumber($number)
    {
        // Check if actual digits
        /*if (!is_numeric($number)) {
            return false;
        }

        // Check if 11 digits
        if (strlen($number) != 11) {
            return false;
        }*/

        // Check if within allowed prefix
        $re = '/[0-9]\d{3}/';
        preg_match_all($re, $number, $matches, PREG_SET_ORDER, 0);
        $allowed = ['0915', '0927', '0995', '0938', '0919', '0813', '0913', '0981', '0934', '0922', '0917', '0935', '0817', '0939', '0921', '0907', '0914', '0998', '0941', '0923', '0945', '0936', '0905', '0940', '0929', '0908', '0918', '0999', '0942', '0924', '0955', '0976', '0906', '0946', '0989', '0909', '0928', '0951', '0943', '0931', '0956', '0997', '0916', '0948', '0920', '0910', '0947', '0912', '0944', '0932', '0994', '0975', '0926', '0950', '0930', '0911', '0949', '0970', '0925', '0933', '0992', '0977', '0978', '0979', '0996', '0937', '0973', '0974'];
        if (!in_array($matches[0], $allowed)) {
            return false;
        }

        // Validation check
        return true;
    }
}