<?php
	session_start();

	require __DIR__.'/../db.php';

	if (isset($_GET['shop']) && $shop = verifyShopStatus($conn, $_GET['shop'])) {

		$qs = array(
			"add_to_cart" => ($shop['qs_add_to_cart'] != '' ? $shop['qs_add_to_cart'] : 'form[action="/cart/add"] [type="submit"]'),
			"variation" => ($shop['qs_variation'] != '' ? $shop['qs_variation'] : '[data-option*="option"]'),
			"add_to_wishlist" => ($shop['qs_add_to_wishlist'] != '' ? $shop['qs_add_to_wishlist'] : ''),
			"image" => ($shop['qs_image'] != '' ? $shop['qs_image'] : '.featured img'),
			"review" => ($shop['qs_review'] != '' ? $shop['qs_review'] : '.new-review-form [type="submit"]'),
			"price" => ($shop['qs_price'] != '' ? $shop['qs_price'] : '#price-preview'),
			"help_pages" => ($shop['help_pages'] != '' ? $shop['help_pages'] : 'about-us'),
			);

		$qs['help_pages'] = explode(',', str_replace(' ', '', $qs['help_pages']));
		$qsHelpPages = array();
		foreach ($qs['help_pages'] as $page) {
			$qsHelpPages[] = 'window.location.pathname.indexOf("/'.$page.'") !== -1';
		}
		$qs['help_pages'] = (count($qsHelpPages) > 0 ? implode(' || ', $qsHelpPages) : 'false');
?>
/* All Pages Triggers */

var _ra_temp = null;

// RA::setEmail
function _ra_prepSetEmail(el) {
	el.addEventListener('submit', function(event) {
		event.preventDefault();

		/* possible intrusive */
		var self = this;
		var _ra_email = document.querySelector('input[name="customer[email]"]').value;
		if (typeof _ra === 'undefined' || typeof _ra.setEmail === 'undefined') {
			self.submit();
		} else {
			_ra.setEmail({
				"email": _ra_email
			}, function() {
				self.submit();
			});
		}
		
		/* non-intrusive
		// shopify's submit
		this.submit();
		*/
	});
}
_ra_temp = document.querySelector('form#customer_login');
if (_ra_temp !== null) _ra_prepSetEmail(_ra_temp);
_ra_temp = document.querySelector('form#create_customer');
if (_ra_temp !== null) _ra_prepSetEmail(_ra_temp);

// RA::sendCategory
if (window.location.pathname.indexOf('/collections/') !== -1 && window.location.pathname.indexOf('/products/') === -1) {
	var _ra_handle = window.location.pathname.match(/\/collections\/([a-z0-9\-]+)/) !== null ? window.location.pathname.match(/\/collections\/([a-z0-9\-]+)/)[1] : '';
	_ra_getTriggers('sendCategory', _ra_handle);
}

// RA::sendProduct
if (window.location.pathname.indexOf('/products/') !== -1) {
	var _ra_handleP = window.location.pathname.match(/\/products\/([a-z0-9\-]+)/) !== null ? window.location.pathname.match(/\/products\/([a-z0-9\-]+)/)[1] : '';
	var _ra_handleC = window.location.pathname.match(/\/collections\/([a-z0-9\-]+)/) !== null ? window.location.pathname.match(/\/collections\/([a-z0-9\-]+)/)[1] : '';
	_ra_getTriggers('sendProduct', _ra_handleP, _ra_handleC);

	//document.addEventListener("DOMContentLoaded", function(event) { 
	
		// RA::addToCart
		var _ra_atc = document.querySelectorAll('<?php echo $qs['add_to_cart']; ?>');
		if (_ra_atc.length > 0) {
			for(var i = 0; i < _ra_atc.length; i ++) {
				_ra_atc[i].addEventListener("click", function() {
					if (typeof _ra_globals_productId === 'undefined') return;
					var _ra_vcode = [], _ra_vdetails = {};
					var _ra_v = document.querySelectorAll('<?php echo $qs['variation']; ?>');
					for(var i = 0; i < _ra_v.length; i ++) {
						var _ra_label = document.querySelector('[for="' + _ra_v[i].getAttribute('id') + '"');
						_ra_label = (_ra_label !== null ? _ra_label = document.querySelector('[for="' + _ra_v[i].getAttribute('id') + '"').textContent : _ra_v[i].getAttribute('data-option') );
						var _ra_value = (typeof _ra_v[i].value !== 'undefined' ? _ra_v[i].value : _ra_v[i].textContent);
						_ra_vcode.push(_ra_value);
						_ra_vdetails[_ra_value] = {
							"category_name": _ra_label,					
							"category": _ra_label,
							"value": _ra_value,
							"stock": 1
						};
					}
					_ra.addToCart(_ra_globals_productId, {
						"code": _ra_vcode.join('-'),
						"details": _ra_vdetails
					});
				});
			}
		}

		// RA::setVariation
		var _ra_sv = document.querySelectorAll('<?php echo $qs['variation']; ?>');
		if (_ra_sv.length > 0) {
			for(var i = 0; i < _ra_sv.length; i ++) {
				_ra_sv[i].addEventListener("change", function() {
					if (typeof _ra_globals_productId === 'undefined') return;
					var _ra_vcode = [], _ra_vdetails = {};
					var _ra_v = document.querySelectorAll('<?php echo $qs['variation']; ?>');
					for(var i = 0; i < _ra_v.length; i ++) {
						var _ra_atc = document.querySelector('<?php echo $qs['add_to_cart']; ?>');
						var _ra_stock = (_ra_atc.getAttribute('disabled') === 'disabled' ? 0 : 1);
						var _ra_label = document.querySelector('[for="' + _ra_v[i].getAttribute('id') + '"');
						_ra_label = (_ra_label !== null ? _ra_label = document.querySelector('[for="' + _ra_v[i].getAttribute('id') + '"').textContent : _ra_v[i].getAttribute('data-option') );
						var _ra_value = (typeof _ra_v[i].value !== 'undefined' ? _ra_v[i].value : _ra_v[i].textContent);
						_ra_vcode.push(_ra_value);
						_ra_vdetails[_ra_value] = {
							"category_name": _ra_label,					
							"category": _ra_label,
							"value": _ra_value,
							"stock": _ra_stock
						};
					}
					_ra.setVariation(_ra_globals_productId, {
						"code": _ra_vcode.join('-'),
						"details": _ra_vdetails
					});
				});
			}
		}

		// RA::clickImage
		var _ra_sv = document.querySelectorAll('<?php echo $qs['image']; ?>');
		if (_ra_sv.length > 0) {
			for(var i = 0; i < _ra_sv.length; i ++) {
				_ra_sv[i].addEventListener("click", function() {
					if (typeof _ra_globals_productId === 'undefined') return;
					_ra.clickImage(_ra_globals_productId);
				});
			}
		}

		// RA::commentOnProduct
		var _ra_sv = document.querySelectorAll('<?php echo $qs['review']; ?>');
		if (_ra_sv.length > 0) {
			for(var i = 0; i < _ra_sv.length; i ++) {
				_ra_sv[i].addEventListener("click", function() {
					if (typeof _ra_globals_productId === 'undefined') return;
					_ra.commentOnProduct(_ra_globals_productId);
				});
			}
		}

		// RA::mouseOverPrice
		var _ra_sv = document.querySelectorAll('<?php echo $qs['price']; ?>');
		if (_ra_sv.length > 0) {
			for(var i = 0; i < _ra_sv.length; i ++) {
				_ra_sv[i].addEventListener("mouseover", function() {
					var _ra_handleP = window.location.pathname.match(/\/products\/([a-z0-9\-]+)/) !== null ? window.location.pathname.match(/\/products\/([a-z0-9\-]+)/)[1] : '';

					var _ra_vcode = {};
					var _ra_v = document.querySelectorAll('<?php echo $qs['variation']; ?>');
					for(var i = 0; i < _ra_v.length; i ++) {
						var _ra_value = (typeof _ra_v[i].value !== 'undefined' ? _ra_v[i].value : _ra_v[i].textContent);
						_ra_vcode[_ra_v[i].getAttribute('data-option')] = _ra_value;
					}

					_ra_getTriggers('mouseOverPrice', _ra_handleP, '', _ra_vcode);
				});
			}
		}

		// RA::mouseOverAddToCart
		var _ra_atc = document.querySelectorAll('<?php echo $qs['add_to_cart']; ?>');
		if (_ra_atc.length > 0) {
			for(var i = 0; i < _ra_atc.length; i ++) {
				_ra_atc[i].addEventListener("mouseover", function() {
					if (typeof _ra_globals_productId === 'undefined') return;
					_ra.mouseOverAddToCart(_ra_globals_productId);
				});
			}
		}

		// RA::likeFacebook
		if (typeof FB !== "undefined" && typeof _ra_globals_productId !== 'undefined') {
			FB.Event.subscribe("edge.create", function () {
				_ra.likeFacebook(_ra_globals_productId);
			});
		};

	//});	

}

// RA::saveOrder
if (window.location.pathname.indexOf('/thank_you') !== -1) {
	var _ra = _ra || {};	
	_ra.saveOrderInfo = {
		"order_no": Shopify.checkout.order_id,
		"lastname": Shopify.checkout.shipping_address.last_name,
		"firstname": Shopify.checkout.shipping_address.first_name,
		"email": Shopify.checkout.email,
		"phone": Shopify.checkout.shipping_address.phone,
		"state": Shopify.checkout.shipping_address.province,
		"city": Shopify.checkout.shipping_address.city,
		"address": Shopify.checkout.shipping_address.address1 + (Shopify.checkout.shipping_address.address2 !== '' ? ' ' + Shopify.checkout.shipping_address.address2 : ''),
		"discount": (Shopify.checkout.discount === null ? 0 : Shopify.checkout.discount.amount),
		"discount_code": (Shopify.checkout.discount === null ? "" : Shopify.checkout.discount.code),
		"shipping": Shopify.checkout.shipping_rate.price,
		"total": Shopify.checkout.total_price
	};
	_ra.saveOrderProducts = [];
	for(var i = 0; i < Shopify.checkout.line_items.length; i ++) {
		_ra.saveOrderProducts.push({ "id": Shopify.checkout.line_items[i].product_id, "quantity": Shopify.checkout.line_items[i].quantity, "price": Shopify.checkout.line_items[i].price, "variation_code": Shopify.checkout.line_items[i].variant_title.split(' / ').join('-') });
	}
	
	if( _ra.ready !== undefined ){
		_ra.saveOrder(_ra.saveOrderInfo, _ra.saveOrderProducts);
	}
}

// RA::visitHelpPage
if (<?php echo $qs['help_pages']; ?>) {
	var _ra = _ra || {};
	_ra.visitHelpPageInfo = {
		"visit" : true
	}
	
	if (_ra.ready !== undefined) {
		_ra.visitHelpPage();
	}
}

// RA::checkoutIds
if (window.location.pathname.indexOf('/cart') !== -1) {
	_ra_ajaxRequest('GET', '/cart.js', '', _ra_ajaxProcessRequest_checkoutIds);
}


/* Helper Functions */

function _ra_getTriggers(s, h, hc, v) {
	var _ra_js = document.createElement("script");
	_ra_js.type ="text/javascript";
	_ra_js.async = true;
	_ra_js.src = "https://shopify.cornelb.ro/shopify/triggers.php?shop="+encodeURI(window.location.host)+"&t="+s+"&h="+h+"&hc="+hc+"&v="+encodeURI(JSON.stringify(v));
	var s = document.getElementsByTagName("script")[0];
	document.getElementsByTagName("head")[0].appendChild(_ra_js);
}

function _ra_ajaxRequest(type, url, data, f) {
    xmlHttp = new XMLHttpRequest(); 
    xmlHttp.onreadystatechange = f;
    xmlHttp.open(type, url, true);
    xmlHttp.send(null);
}

function _ra_ajaxProcessRequest_checkoutIds() {
    if ( xmlHttp.readyState == 4 && xmlHttp.status == 200 ) {
    	var data = JSON.parse(xmlHttp.responseText);
		
		_ra = _ra || {};
    	_ra.checkoutIdsInfo = [];
    	for(var i = 0; i < data.items.length; i ++) _ra.checkoutIdsInfo.push(data.items[i].id);

		if (_ra.ready !== undefined) {
			_ra.checkoutIds(_ra.checkoutIdsInfo);
		}
    }
}

<?php
	}