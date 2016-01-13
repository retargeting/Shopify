<?php
	session_start();

	require __DIR__.'/db.php';

	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';

	if (isset($_POST['domain_api_key']) && isset($_POST['api_token']) && isset($_SESSION['oauth_token'])) {
		
		$shopify = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);

		$newDomain = $_POST['domain'];

		$newDomainApiKey = $_POST['domain_api_key'];
		$newApiToken = $_POST['api_token'];
		$changeStatus = (isset($_POST['changeStatus']) ? true : false);

		$newHelpPages = $_POST['help_pages'];
		$newAddToCart = $_POST['qs_add_to_cart'];
		$newVariation = $_POST['qs_variation'];
		$newAddToWishlist = $_POST['qs_add_to_wishlist'];
		$newImage = $_POST['qs_image'];
		$newReview = $_POST['qs_review'];
		$newPrice = $_POST['qs_price'];

		$response = updateShopDetails($conn, $_SESSION['shop'], $_SESSION['oauth_token'], $newDomain, $newDomainApiKey, $newApiToken, $changeStatus, $newHelpPages, $newAddToCart, $newVariation, $newAddToWishlist, $newImage, $newReview, $newPrice);

		try
		{
			$shop = getShopDetails($conn, $_SESSION['shop']);
			
			$res = array();

			if ($shop['status']) {
				// enable Retargeting App
				
				// base embedding
				$scriptTag = array(
					'script_tag' => array(
						'event' => 'onload',
						'src' => 'https://retargeting.biz/shopify/js/embedd.php'
					)
				);

				$res[] = $shopify('POST /admin/script_tags.json', $scriptTag);

				// triggers
				$scriptTag = array(
					'script_tag' => array(
						'event' => 'onload',
						'src' => 'https://retargeting.biz/shopify/js/triggers.php'
					)
				);

				$res[] = $shopify('POST /admin/script_tags.json', $scriptTag);

				// add Order/Create Webhook
				$webhook = array(
					'webhook' => array(
						'topic' => 'orders/create',
						'address' => 'https://retargeting.biz/shopify/webhooks/orders/create.php',
						'format' => 'json'
					)
				);

				$res[] = $shopify('POST /admin/webhooks.json', $webhook);

			} else {
				// disable Retargeting App

				$stBase = null;
				$stTriggers = null;

				// remove ScriptTags
				$scriptTags = $shopify('GET /admin/script_tags.json');
				$res[] = $scriptTags;
				foreach ($scriptTags as $st) {
					if ($st['src'] == 'https://retargeting.biz/shopify/js/embedd.php') {
						$res[] = $shopify('DELETE /admin/script_tags/'.$st['id'].'.json');
					}
					if ($st['src'] == 'https://retargeting.biz/shopify/js/triggers.php') {
						$res[] = $shopify('DELETE /admin/script_tags/'.$st['id'].'.json');
					}
					if ($st['src'] == 'https://shopify.cornelb.ro/shopify/js/embedd.php') {
						$res[] = $shopify('DELETE /admin/script_tags/'.$st['id'].'.json');
					}
					if ($st['src'] == 'https://shopify.cornelb.ro/shopify/js/triggers.php') {
						$res[] = $shopify('DELETE /admin/script_tags/'.$st['id'].'.json');
					}
				}

				// remove Order/Create Webhook
				$webhooks = $shopify('GET /admin/webhooks.json');
				$res[] = $webhooks;
				foreach ($webhooks as $wh) {
					if ($wh['address'] == 'https://retargeting.biz/shopify/webhooks/orders/create.php') {
						$res[] = $shopify('DELETE /admin/webhooks/'.$wh['id'].'.json');
					}
				}
			}

		}
		catch (shopify\ApiException $e)
		{
			# HTTP status code was >= 400 or response contained the key 'errors'
			$response['kraken'] = $e;
			$response['kraken'] = print_r($e->getRequest(), true);
			$response['kraken'] = print_r($e->getResponse(), true);
		}
		catch (shopify\CurlException $e)
		{
			# cURL error
			$response['kraken'] = $e;
			$response['kraken'] = print_r($e->getRequest(), true);
			$response['kraken'] = print_r($e->getResponse(), true);
		}

		$response['info'] =  $res;
		echo json_encode($response);
	} else if (isset($_POST['disable_init'])  && isset($_SESSION['oauth_token'])) {

		$response = disableShopInit($conn, $_SESSION['shop'], $_SESSION['oauth_token']);
		echo json_encode($response);
	}
