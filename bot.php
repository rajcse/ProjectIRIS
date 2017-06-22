<?php

require "config.php";
require "kernel.php";

$token = $_REQUEST['hub_verify_token'];
$hubVerifyToken = $facebook['verify_token'];
$challenge = $_REQUEST['hub_challenge'];
$accessToken = $facebook['page_access_token'];

$bot = new FbBot();
$bot->setHubVerifyToken($hubVerifyToken);
$bot->setaccessToken($accessToken);
echo $bot->verifyToken($token, $challenge);
$input = json_decode(file_get_contents('php://input'), true);
$message = $bot->readMessage($input);
$textmessage = $bot->sendMessage($message);