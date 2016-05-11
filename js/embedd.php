<?php
	session_start();

	require __DIR__.'/../db.php';

	if (isset($_GET['shop']) && $shop = verifyShopStatus($conn, $_GET['shop'])) {

		if ($shop['domain_api_key'] != '') {
?>
(function(){
ra_key = "<?php echo $shop['domain_api_key']; ?>";
ra_params = {
	add_to_cart_button_id: '<?php echo ($shop['qs_add_to_cart'] != '' ? $shop['qs_add_to_cart'] : 'form[action="/cart/add"] [type="submit"]'); ?>',
	price_label_id: '<?php echo ($shop['qs_price'] != '' ? $shop['qs_price'] : '#price-preview'); ?>',
};
var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
document.location.protocol ? "https://" : "http://") + "tracking.retargeting.biz/v3/rajs/" + ra_key + ".js";
var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
<?php
		}
	}