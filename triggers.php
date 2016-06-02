<?php
	session_start();

	require __DIR__.'/db.php';

	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';

	
	//if (isset($_SESSION['oauth_token']) && isset($_SESSION['shop'])) {
	if (isset($_GET['shop']) && $shop = verifyShopStatus($conn, $_GET['shop'])) {

		$shopify = shopify\client($shop['shop'], SHOPIFY_APP_API_KEY, $shop['token']);

		if (isset($_GET['t'])) {

			if ($_GET['t'] == 'sendProduct') {

				$productHandle = $_GET['h'];
				$categoryHandle = $_GET['ch'];

				if ($productHandle == '') {
					echo 'false';
					return false;
				}

				try
				{
					$product = $shopify('GET /admin/products.json?handle='.$productHandle);
					
					if (count($product) > 0) {
						$product = $product[0];

						$productImage = '';
						if (array_key_exists('image', $product)) $productImage = $product['image']['src'];

						$category = 'false';
						if ($categoryHandle != '') {
							$category = $shopify('GET /admin/custom_collections.json?handle='.$categoryHandle);
							if (count($category) == 0) $category = $shopify('GET /admin/smart_collections.json?handle='.$categoryHandle);

							if (count($category) > 0) {
								$category = $category[0];
								$category = '{
									"id": '.$category['id'].',
									"name": "'.$category['title'].'",
									"parent": false,
									"breadcrumb": []
								}';
							}
						}
						
						$productInventoryVariations = 'false';
						if (count($product['variants'])) {
							$productInventoryStock = array();
							foreach ($product['variants'] as $variant) {
								$entry = implode('-', explode('/', str_replace(' ', '', str_replace('"', '', $variant['title']))));
								$productInventoryStock[] = '"'.$entry.'": '.($variant['inventory_quantity'] > 0 ? 'true' : 'false');
							}
							$productInventoryVariations = 'true';
							$productInventoryStock = '{'.implode(', ', $productInventoryStock).'}';
						} else {
							$productInventoryStock = 'true';
						}
?>

var _ra_globals_productId = "<?php echo $product['id']; ?>";

var _ra = _ra || {};
_ra.sendProductInfo = {
	"id": <?php echo $product['id']; ?>,
	"name": "<?php echo $product['title']; ?>",
	"url": window.location.href,
	"img": "<?php echo $productImage; ?>", 
	"price": "<?php echo ($product['variants'][0]['compare_at_price'] == '' ? $product['variants'][0]['price'] : $product['variants'][0]['compare_at_price']); ?>",
	"promo": "<?php echo ($product['variants'][0]['compare_at_price'] == '' ? 0 : $product['variants'][0]['price']); ?>",
	"stock": <?php echo ($product['variants'][0]['inventory_quantity'] <= 0 ? 0 : 1); ?>,
	"brand": false,
	"category": [<?php echo $category; ?>],
	"inventory": {
		"variations": <?php echo $productInventoryVariations; ?>,
		"stock": <?php echo $productInventoryStock; ?>
	}
}

if (_ra.ready !== undefined) {
	_ra.sendProduct(_ra.sendProductInfo);
}

<?php
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


			} else if ($_GET['t'] == 'sendCategory') {

				$categoryHandle = $_GET['h'];

				if ($categoryHandle == '') {
					echo 'false';
					return false;
				}

				try
				{
					$category = $shopify('GET /admin/custom_collections.json?handle='.$categoryHandle);
					if (count($category) == 0) $category = $shopify('GET /admin/smart_collections.json?handle='.$categoryHandle);

					if (count($category) > 0) {
						$category = $category[0];

?>

// sendCategory
var _ra = _ra || {};
_ra.sendCategoryInfo = {
	"id": <?php echo $category['id']; ?>,
	"name": "<?php echo $category['title']; ?>",
	"parent": false,
	"breadcrumb": [] 
};

if (_ra.ready !== undefined) {
	_ra.sendCategory(_ra.sendCategoryInfo);
}

<?php
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
			} else if ($_GET['t'] == 'addToCart') {

				$productHandle = $_GET['h'];
				
				if ($productHandle == '') {
					echo 'false';
					return false;
				}

				try
				{
					$product = $shopify('GET /admin/products.json?handle='.$productHandle);

					if (count($product) > 0) {
						$product = $product[0];

?>

// addToCart
_ra.addToCart(<?php echo $product['id']; ?>, 1, false);

<?php
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
		}
	}
