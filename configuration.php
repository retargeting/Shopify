<?php 
	session_start();

	require __DIR__.'/db.php';

	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';

	//if (!isset($_SESSION['oauth_token']) && !isset($_SESSION['shop'])) {
	if (!verifyShopStatus($conn, $_GET['shop'])) {

		// [begin]OAUTH

		# Guard: http://docs.shopify.com/api/authentication/oauth#verification
		shopify\is_valid_request($_GET, SHOPIFY_APP_SHARED_SECRET) or die('Invalid Request! Request or redirect did not come from Shopify');


		# Step 2: http://docs.shopify.com/api/authentication/oauth#asking-for-permission
		if (!isset($_GET['code']))
		{
			$permission_url = shopify\authorization_url($_GET['shop'], SHOPIFY_APP_API_KEY, array('read_content', 'read_products', 'read_customers', 'read_orders', 'read_script_tags', 'write_script_tags'));
			die("<script>top.location.href='$permission_url'</script>");
		}


		# Step 3: http://docs.shopify.com/api/authentication/oauth#confirming-installation
		try
		{
			# shopify\access_token can throw an exception
			$oauth_token = shopify\access_token($_GET['shop'], SHOPIFY_APP_API_KEY, SHOPIFY_APP_SHARED_SECRET, $_GET['code']);

			$_SESSION['oauth_token'] = $oauth_token;
			$_SESSION['shop'] = $_GET['shop'];

			saveShopDetails($conn, $_GET['shop'], $oauth_token);

			header("Location: http://".$_GET['shop']."/admin/apps/".SHOPIFY_APP_API_KEY);
			die();

			echo 'App Successfully Installed! <br> go back to the admin!';
		}
		catch (shopify\ApiException $e)
		{
			# HTTP status code was >= 400 or response contained the key 'errors'
			echo $e;
			print_R($e->getRequest());
			print_R($e->getResponse());
		}
		catch (shopify\CurlException $e)
		{
			# cURL error
			echo $e;
			print_R($e->getRequest());
			print_R($e->getResponse());
		}

		// [end]OAUTH

	} else {

		shopify\is_valid_request($_GET, SHOPIFY_APP_SHARED_SECRET) or die('Invalid Request! Request or redirect did not come from Shopify');

		$shop = getShopDetails($conn, $_GET['shop']);

		$_SESSION['oauth_token'] = $shop['token'];
		$_SESSION['shop'] = $shop['shop'];

		$shopify = shopify\client($shop['shop'], SHOPIFY_APP_API_KEY, $shop['token']);

		//require __DIR__.'/admin_preload.php';

		$view['status'] = $shop['status'];
		$view['init'] = $shop['init'];
		$view['domain'] = ($shop['domain'] != '' ? $shop['domain'] : $shop['shop']);
		$view['domain_api_key'] = $shop['domain_api_key'];
		$view['discounts_api_key'] = $shop['discounts_api_key'];

		$view['help_pages'] = $shop['help_pages'];
		$view['qs_add_to_cart'] = $shop['qs_add_to_cart'];
		$view['qs_variation'] = $shop['qs_variation'];
		$view['qs_add_to_wishlist'] = $shop['qs_add_to_wishlist'];
		$view['qs_image'] = $shop['qs_image'];
		$view['qs_review'] = $shop['qs_review'];
		$view['qs_price'] = $shop['qs_price'];

?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Retargeting</title>

		<link rel="stylesheet" type="text/css" href="css/configuration.css">
		<link href='//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic' rel='stylesheet' type='text/css'>

		<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
		<script type="text/javascript">
			var RA_STATUS = <?php echo $view['status']; ?>;
		</script>
		<script type="text/javascript">
			ShopifyApp.init({
				apiKey: '<?php echo SHOPIFY_APP_API_KEY; ?>',
				shopOrigin: 'https://<?php echo $_SESSION['shop']; ?>',
				debug: true
			});
		</script>
		<script type="text/javascript" src="js/configuration.js"></script>
	</head>

	<body>

		<section>
			<header class="span-1">
				<h1>Application Status</h1>
			</header>
			<div class="content span-3">
				
				<div class="input">
					<label for="ra_status"><?php echo ($view['status'] ? 'Enabled' : 'Disabled'); ?></label>
					<p class="description"><?php echo ($view['status'] ? 'Your <a href="http://retargeting.biz">Retargeting</a> App is <strong>up</strong> and <strong>running</strong>' : 'Currently your <a href="http://retargeting.biz">Retargeting</a> App is <strong>not</strong> tracking any of your users. To activate it please press the <strong>Enable</strong> button from to top right of your screen.'); ?></p>
				</div>

			</div>
		</section>

		<section>
			<header class="span-1">
				<h1>Configuration</h1>
			</header>
			<div class="content span-3">
				
				<div class="input">
					<label for="ra_domain">Domain</label>
					<input type="text" name="ra_domain" id="ra_domain" placeholder="ex: yourshop.myshopify.com" value="<?php echo $view['domain']; ?>">
					<p class="description">The root domain where the shopify shop is installed.</p>
				</div>

				<div class="input">
					<label for="ra_domain_api_key">Domain API Key</label>
					<input type="text" name="ra_domain_api_key" id="ra_domain_api_key" placeholder="ex: 1238BFDOS0SFODBSFKJSDFU2U32" value="<?php echo $view['domain_api_key']; ?>">
					<p class="description">You can find your Secure Domain API Key in your <a href="http://retargeting.biz">Retargeting</a> account.</p>
				</div>

				<div class="input">
					<label for="ra_discounts_api_key">Discounts API Key</label>
					<input type="text" name="ra_discounts_api_key" id="ra_discounts_api_key" placeholder="ex: 1238BFDOS0SFODBSFKJSDFU2U32" value="<?php echo $view['discounts_api_key']; ?>">
					<p class="description">You can find your Secure Discounts API Key in your <a href="http://retargeting.biz">Retargeting</a> account.</p>
				</div>

			</div>
		</section>

		<section>
			<header class="span-1">
				<h1>Preferences</h1>
			</header>
			<div class="content span-3">
				
				<div class="input">
					<label for="ra_help_pages">Help Pages</label>
					<input type="text" name="ra_help_pages" id="ra_help_pages" placeholder="about-us" value="<?php echo $view['help_pages']; ?>">
					<p class="description">Please add the handles for the pages on which you want the "visitHelpPage" trigger to fire. Use a comma as a separator for listing multiple handles. For example: http://yourshop.com/pages/<strong>about-us</strong> is represented by the "about-us" handle.</p>
				</div>

				<div class="info">
					<label>JavaScript Query Selectors</label>
					<p>The <a href="http://retargeting.biz">Retargeting</a> App should work out of the box for most themes. But, as themes implementation can vary, in case there would be any problems with triggers not working as expected you can modify the following settings to make sure the triggers are linked to the right theme elements.</p>
				</div>

				<div class="input">
					<label for="ra_">Add To Cart Button</label>
					<input type="text" name="ra_add_to_cart" id="ra_add_to_cart" placeholder='form[action="/cart/add"] [type="submit"]' value="<?php echo $view['qs_add_to_cart']; ?>">
					<p class="description">Query selector for the button used to add a product to cart.</p>
				</div>

				<div class="input">
					<label for="ra_">Product Variants Buttons</label>
					<input type="text" name="ra_variation" id="ra_variation" placeholder='[data-option*="option"]' value="<?php echo $view['qs_variation']; ?>">
					<p class="description">Query selector for the product options used to change the preferences of the product.</p>
				</div>

				<div class="input">
					<label for="ra_">Add To Wishlist Button</label>
					<input type="text" name="ra_add_to_wishlist" id="ra_add_to_wishlist" placeholder='not supported yet' value="<?php echo $view['qs_add_to_wishlist']; ?>">
					<p class="description">Query selector for the button used to add a product to wishlist.</p>
				</div>

				<div class="input">
					<label for="ra_">Product Image</label>
					<input type="text" name="ra_image" id="ra_image" placeholder='.featured img' value="<?php echo $view['qs_image']; ?>">
					<p class="description">Query selector for the main product image on a product page.</p>
				</div>

				<div class="input">
					<label for="ra_">Submit Review Button</label>
					<input type="text" name="ra_review" id="ra_review" placeholder='.new-review-form [type="submit"]' value="<?php echo $view['qs_review']; ?>">
					<p class="description">Query selector for the button used to submit a comment/review for a product.</p>
				</div>

				<div class="input">
					<label for="ra_">Price Container</label>
					<input type="text" name="ra_price" id="ra_price" placeholder="#price-preview" value="<?php echo $view['qs_price']; ?>">
					<p class="description">Query selector for the main product price on a product page.</p>
				</div>


			</div>
		</section>

		<section class="init <?php if ($view['init']) echo 'disabled'; ?>">
			
			<article>
				<!--<img src="imgs/logo-big.jpg">-->
				<h1>Hello!</h1>
				<h2>To have access to our awesome features you need a <a href="https://retargeting.biz" target="_blank">Retargeting account</a>.</h2>
				<div class="row">
					<div class="btn-init btn-disableInit">I already have an account</div>
					<a href="https://retargeting.biz/signup"><div class="btn-init btn-cta">Start your 14-day Free Trial</div></a>
				</div>
			</article>
		
		</section>

	</body>
</html>

<?php 

	}
