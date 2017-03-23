<?php

class Shopify_API {

	private function read_store_data($file) {
		$current = file_get_contents($file);
		$current_json = json_decode($current, true);
		return $current_json;
	}

	private function new_store($url, $token, $app_config_dir) {
		$file = dirname(__FILE__).'/../../'.$app_config_dir.'/store_data.json';
		$current_json = $this->read_store_data($file);
		$new_store = array($url => $token);
		$newdata = array_merge($current_json, $new_store);
		$update = json_encode($newdata, JSON_PRETTY_PRINT);
		file_put_contents($file, $update);
	}

	public function get_store_token($url, $app_config_dir) {
		$file = dirname(__FILE__).'/../../'.$app_config_dir.'/store_data.json';
		$current_json = $this->read_store_data($file);
		return $current_json[$url];
	}

	public function generate_token($key, $secret, $code, $timestamp, $hmac, $shop, $app_config_dir)
	{
		// Compile signature data
		$verification_data = 'code='.$code.'&shop='.$shop.'.myshopify.com&timestamp='.$timestamp;

		// Use signature data to check that the response is from Shopify or not
		if (hash_hmac('sha256', $verification_data, $secret) === $hmac) {

			// Set variables for our request
			$query = array(
				"Content-type" => "application/json", // Tell Shopify that we're expecting a response in JSON format
				"client_id" => $key, // Your API key
				"client_secret" => $secret, // Your app credentials (secret key)
				"code" => $code // Grab the access key from the URL
			);

			// Call our Shopify function
			$shopify_response = $this->shopify_call(NULL, $shop, "/admin/oauth/access_token", $query, 'POST');

			$shopify_response = json_decode($shopify_response['response'], TRUE);

			$token = $shopify_response['access_token'];

			$this->new_store($shop, $token, $app_config_dir);

			return true;
		} 
		else {
			return false;
		}
	}

	public function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
	    
		// Build URL
		$url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
		if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);

		// Configure cURL
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
		// curl_setopt($curl, CURLOPT_SSLVERSION, 3);
		curl_setopt($curl, CURLOPT_USERAGENT, 'PHP eblox test app');
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

		// Setup headers
		$request_headers[] = "";
		if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

		if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
			if (is_array($query)) $query = http_build_query($query);
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
		}
	    
		// Send request to Shopify and capture any errors
		$response = curl_exec($curl);
		$error_number = curl_errno($curl);
		$error_message = curl_error($curl);

		// Close cURL to be nice
		curl_close($curl);

		// Return an error is cURL has a problem
		if ($error_number) {
			return $error_message;
		} else {

			// No error, return Shopify's response by parsing out the body and the headers
			$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

			// Convert headers into an array
			$headers = array();
			$header_data = explode("\n",$response[0]);
			$headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
			array_shift($header_data); // Remove status, we've already set it above
			foreach($header_data as $part) {
				$h = explode(":", $part);
				$headers[trim($h[0])] = trim($h[1]);
			}

			// Return headers and Shopify's response
			return array('headers' => $headers, 'response' => $response[1]);

		}
	    
	}
}