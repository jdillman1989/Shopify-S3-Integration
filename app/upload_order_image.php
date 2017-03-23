<?php 

header('Access-Control-Allow-Origin: *');

$app_config_dir = 's3-app-config';
require 'vendor/autoload.php';
require_once(dirname(__FILE__).'/../shopify-methods/shopify.php');
$shopify = new Shopify_API;

// {
//     "image": {
//         "data": filename,
//         "format": format,
//     },
//     "order": {
//         "order_id": orderID,
//         "line_item": lineItem,
//         "store": store
//     }
// }

session_start();

$get_data = json_decode($_GET['data'], TRUE);

$_SESSION['shop'] = $get_data['order']['store'];

$token = $shopify->get_store_token($_SESSION['shop'], $app_config_dir);
$_SESSION['token'] = $token;

$s3_config = file_get_contents(dirname(__FILE__).'/../../'.$app_config_dir.'/'.$_SESSION['shop'].'-s3-creds.json');
$s3_creds = json_decode($s3_config, true);

use Aws\S3\S3Client;

$client = new S3Client([
    'version' => 'latest',
    'region' => 'us-east-1',
    'credentials' => [
        'key' => $s3_creds['key'],
        'secret' => $s3_creds['secret']
    ]
]);

$bucket = 'eblox';
$image_name = 'image_'.$get_data['order']['order_id'].'_'.$get_data['order']['line_item'].'.'.$get_data['image']['format'];

$temp_file = dirname(__FILE__).'/../../'.$app_config_dir.'/temp/'.$image_name;
$shopify_image = file_get_contents($get_data['image']['data']);
file_put_contents($temp_file, $shopify_image);

$result = $client->putObject(array(
    'Bucket' => $bucket,
    'Key' => $image_name,
    'SourceFile' => $temp_file,
    'ACL' => 'public-read',
    'Metadata' => array(
        'Order' => $get_data['order']['order_id'],
        'Product' => $get_data['order']['line_item']
    )
));

// Add S3 URL to Note
$query = array(
    "Content-type" => "application/json"
);

$orders = $shopify->shopify_call($_SESSION['token'], $_SESSION['shop'], '/admin/orders/'.$get_data['order']['order_id'].'.json', array(), 'GET');

$order = json_decode($orders['response'], TRUE);

$modify_data = array(
    "order" => array(
        "id" => $get_data['order']['order_id'],
        "note" => $order['order']['note'].' S3 Image: '.$result['ObjectURL'],
    )
);

$post_meta = $shopify->shopify_call($_SESSION['token'], $_SESSION['shop'], '/admin/orders/'.$get_data['order']['order_id'].'.json', $modify_data, 'PUT');

?>