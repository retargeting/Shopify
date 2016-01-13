<?php
	require __DIR__.'/../../db.php';
	require __DIR__.'/../../conf.php';
	require __DIR__.'/../../lib/retargeting-rest-api/Client.php';

	function verify_webhook($data, $hmac_header) {
		$calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SHARED_SECRET, true));
		return ($hmac_header == $calculated_hmac);
	}

	$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
	$data = file_get_contents('php://input');
	$verified = verify_webhook($data, $hmac_header);
	
	// $file = "\n\n=========".print_r($verified, true)."::".print_r($_SERVER, true)."=========\n\n".print_r($data, true);
	// $file .= file_get_contents(__DIR__.'/log.txt');
	
	// file_put_contents(__DIR__.'/log.txt', $file);

	// echo "<pre>".file_get_contents(__DIR__.'/log.txt')."</pre>";


	if ($verified && !empty($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {

		$shop = getShopDetails($conn, $_GET['shop']);

		if (empty($shop['domain_api_key']) || empty($shop['api_token']))
			return false;
		

		// get Order through Shopify API


		$client = new Retargeting_REST_API_Client($shop['domain_api_key'], $shop['api_token']);
		$client->setResponseFormat("json");
		$client->setDecoding(false);
		
		$response = $client->order->save(
			array(
				'order_no' => $data['order_number'],
				'lastname' => 'Doe',
				'firstname' => 'John',
				'email' => 'johndoe@mail.com',
				'phone' => '0700555888',
				'state' => 'Dambovita',
				'city' => 'Targoviste',
				'address' => 'Bd. Independentei',
				'discount' => 20,
				'discount_code' => 'RAX204',
				'shipping' => 19,
				'total' => 199
				),
			array(
				array('id' => 307, 'quantity' => 1, 'price' => 18, 'variation_code' => ''),
				array('id' => 48, 'quantity' => 2, 'price' => 300, 'variation_code' => 'R-S'),
				array('id' => 48, 'quantity' => 1, 'price' => 300, 'variation_code' => 'Y-S'),
				array('id' => 50, 'quantity' => 1, 'price' => 22, 'variation_code' => 'Y-S')
				)
		);
	}