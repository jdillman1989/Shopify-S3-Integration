<?php

require(dirname(__FILE__).'/../../s3-app-config/conf.php');

session_start();

$_SESSION['shop'] = $_GET['shop'];
$redirect_uri = 'https://shopify.thinksolid.com/s3-app/generate_token.php';

$install_url = 'https://'.$_SESSION['shop'].'.myshopify.com/admin/oauth/authorize?client_id='.SHOPIFY_APP_API_KEY.'&scope=read_content,write_content,read_themes,write_themes,read_products,write_products,read_customers,write_customers,read_orders,write_orders,read_script_tags,write_script_tags,read_fulfillments,write_fulfillments,read_shipping,write_shipping&redirect_uri='.urlencode($redirect_uri);

echo $install_url;

// Redirect
header('Location: '.$install_url);
die();
