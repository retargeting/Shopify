<?php

require __DIR__.'/../../db.php';

require __DIR__.'/../../vendor/autoload.php';
use phpish\shopify;

require __DIR__.'/../../conf.php';
require __DIR__.'/../../lib/retargeting-rest-api/Client.php';

function verify_webhook($data, $hmac_header) {
	$calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SHARED_SECRET, true));
	return ($hmac_header == $calculated_hmac);
}

$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data = file_get_contents('php://input');
$verified = verify_webhook($data, $hmac_header);

if ($verified && !empty($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']) && $shop = verifyShopStatus($conn, $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {

	$shopify = shopify\client($shop['shop'], SHOPIFY_APP_API_KEY, $shop['token']);

	if (empty($shop['domain_api_key']) || empty($shop['api_token'])) {
		return false;
	}
		
	// get Order through Shopify API - unsolved issue
	// $order = $shopify('GET /admin/orders.json?ids='.$data['id']);

	$client = new Retargeting_REST_API_Client($shop['domain_api_key'], $shop['api_token']);
	$client->setResponseFormat("json");
	$client->setDecoding(false);
		
	$orderObj = json_decode($data);

	$discountCodes = array();
		foreach ($orderObj->discount_codes as $dc) {
			$discountCodes[] = $dc->code;
		}
	$discountCodes = implode(', ', $discountCodes);

	$lineItems = array();

	foreach ($orderObj->line_items as $li) {
		$lineItems[] = array(
			'id' => $li->id,
			'quantity' => $li->quantity,
			'price' => $li->price,
			'variation_code' => $li->variant_id
		);
	}

	$response = $client->order->save(
		array(
			'order_no' => 'sohpify-'.$orderObj->order_number,
			'lastname' => '',
			'firstname' => '',
			'email' => $orderObj->email,
			'phone' => '',
			'state' => '',
			'city' => '',
			'address' => '',
			'discount' => $orderObj->total_discounts,
			'discount_code' => $discountCodes,
			'shipping' => 0,
			'total' => $orderObj->total_price
			),
		$lineItems
	);

}

