<?php

$app_config_dir = 's3-app-config';
require_once(dirname(__FILE__).'/../shopify-methods/shopify.php');
require(dirname(__FILE__).'/../../'.$app_config_dir.'/conf.php');
$shopify = new Shopify_API;

session_start();

// Set variables for our request
$code = $_GET['code'];
$timestamp = $_GET['timestamp'];
$hmac = $_GET['hmac'];

$token_result = $shopify->generate_token(SHOPIFY_APP_API_KEY, SHOPIFY_APP_SHARED_SECRET, $code, $timestamp, $hmac, $_SESSION['shop'], $app_config_dir);

if ($token_result) {
	die('Successfully authorized app for '.$_SESSION['shop'].'.myshopify.com. <a href="https://'.$_SESSION['shop'].'.myshopify.com/admin/apps">Click here to return to Shopify.</a>');
} 
else {
	die('This request is NOT from Shopify! Shop: '.$_SESSION['shop'].'.myshopify.com');
}