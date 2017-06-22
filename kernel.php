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
}