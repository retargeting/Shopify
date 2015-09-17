<?php 
	session_start();

	require __DIR__.'/db.php';

	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '
	<products>';

	if (isset($_GET['shop']) && $shop = verifyShopStatus($conn, $_GET['shop'])) {

		$shopify = shopify\client($shop['shop'], SHOPIFY_APP_API_KEY, $shop['token']);

		try
		{
			# Making an API request can throw an exception
			$products = $shopify('GET /admin/products.json', array('published_status'=>'published'));
			
			foreach ($products as $product) {
				
				$customCol = $shopify('GET /admin/custom_collections.json?product_id='.$product['id']);
				$smartCol = $shopify('GET /admin/smart_collections.json?product_id='.$product['id']);

				$colHandle = false;
				if (count($customCol) > 0) $colHandle = $customCol[0]['handle'];
				else if (count($smartCol) > 0) $colHandle = $smartCol[0]['handle'];

				$productUrl = $shop['shop'];
				if ($colHandle) $productUrl .= '/collections/'.$colHandle;
				$productUrl .= '/products/'.$product['handle'];

				//$productUrl = $shop['shop'].'/products/'.$product['handle'];
				
				echo '
					<product>	
						<id>'.$product['id'].'</id>
						<stock>'.($product['variants'][0]['inventory_quantity'] <= 0 ? 0 : 1).'</stock>
						<price>'.($product['variants'][0]['compare_at_price'] == '' ? $product['variants'][0]['price'] : $product['variants'][0]['compare_at_price']).'</price>
						<promo>'.($product['variants'][0]['compare_at_price'] == '' ? 0 : $product['variants'][0]['price']).'</promo>
						<url>'.$productUrl.'</url>
						<image>'.(array_key_exists('image', $product) ? $productImage = $product['image']['src'] : '').'</image>
					</product>';
			}
		}
		catch (shopify\ApiException $e)
		{
			# HTTP status code was >= 400 or response contained the key 'errors'
			echo $e;
			print_r($e->getRequest());
			print_r($e->getResponse());
		}
		catch (shopify\CurlException $e)
		{
			# cURL error
			echo $e;
			print_r($e->getRequest());
			print_r($e->getResponse());
		}

	}

	echo '
	</products>';