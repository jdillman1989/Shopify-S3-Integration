<?php 

// Get our helper functions
require_once(dirname(__FILE__).'/../shopify-methods/shopify.php');
$shopify = new Shopify_API;

session_start();

$query = array(
	"Content-type" => "application/json"
);

$orders = $shopify->shopify_call($_SESSION['token'], $_SESSION['shop'], '/admin/orders.json?status=any', array(), 'GET');
$orders = json_decode($orders['response'], TRUE);

$tbody = '';

foreach ($orders['orders'] as $order) {
	if (strpos($order['note'], 's3.amazonaws.com') !== false) {
		$tbody .= '<tr>'
					.'<td>'
						.'<p><a href="https://'.$_SESSION['shop'].'.myshopify.com/admin/orders/'.$order['id'].'">'.$order['id'].'</a></p>'
					.'</td>'
					.'<td>'
						.'<p>'.$order['created_at'].'</p>'
					.'</td>'
					.'<td>'
						.'<p>'.$order['email'].'</p>'
					.'</td>'
					.'<td>'
						.'<p>'.$order['note'].'</p>'
					.'</td>'
				.'</tr>';
	}
}

$return = '<table class="s3-orders">'
				.'<thead>'
					.'<tr>'
						.'<td>'
							.'<p><strong>Order ID</strong></p>'
						.'</td>'
						.'<td>'
							.'<p><strong>Date</strong></p>'
						.'</td>'
						.'<td>'
							.'<p><strong>Email</strong></p>'
						.'</td>'
						.'<td>'
							.'<p><strong>Image Upload</strong></p>'
						.'</td>'
					.'</tr>'
				.'</thead>'
				.'<tbody>'
					.$tbody
				.'</tbody>'
			.'</table>';

echo $return;

?>