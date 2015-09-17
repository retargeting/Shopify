<?php
	session_start();

	require __DIR__.'/../db.php';

	if (isset($_GET['shop']) && $shop = verifyShopStatus($conn, $_GET['shop'])) {
?>
/*
(function(){
var ra_key = "<?php echo $shop['domain_api_key']; ?>";
var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
document.location.protocol ? "https://" : "http://") + "retargeting-data.eu/rajs/" + ra_key + ".js";
var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
*/
(function(){
	var _ra_hostname = (document.location.hostname.replace("www.","") === "checkout.shopify.com" ? Shopify.shop : document.location.hostname.replace("www.",""));
	var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
	document.location.protocol ? "https://" : "http://") + "retargeting-data.eu/" +
	_ra_hostname + "/ra.js"; var s =
	document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();

<?php
	}