ShopifyApp.ready(function(){
	ShopifyApp.Bar.initialize({
		icon: 'https://retargeting.biz/static/images/logo.png',
		title: 'Configuration',
		buttons: { 
			primary: {
				label: 'Save Changes', 
				message: 'save', 
				callback: function() { 
					ShopifyApp.Bar.loadingOn();
					saveConfiguration();
				}
			},
			secondary: {
				label: (RA_STATUS ? 'Disable' : 'Enable'), 
				message: 'changeStatus', 
				callback: function() { 
					ShopifyApp.Bar.loadingOn();
					changeStatus();
				}
			}
		}
	});

	$('.btn-disableInit').click(disableShopInit);

	function disableShopInit() {

		$.post( "ajax_saveConfiguration.php", {
				disable_init: true
			}, function(data) {
				var data = JSON.parse(data);
				if (data.errors.length > 0) {

					var msg = '';
					for(var i = 0; i < data.errors.length; i ++) {
						msg += data.errors[i] + ' ';
					}

					ShopifyApp.Modal.alert({
						title: "Sorry!",
						message: msg,
						okButton: "I understand"
					});
				} else {
					ShopifyApp.Bar.loadingOff();

					$('section.init').fadeOut('fast');
				}
			} 
		)
	}

	function saveConfiguration() {
		console.info('Retargeting Info :: Saving configuration.');

		$.post( "ajax_saveConfiguration.php", { 
				domain: $('input[name="ra_domain"]').val(), 
				domain_api_key: $('input[name="ra_domain_api_key"]').val(), 
				api_token: $('input[name="ra_api_token"]').val(), 
				help_pages: $('input[name="ra_help_pages"]').val(), 
				qs_add_to_cart: $('input[name="ra_add_to_cart"]').val(), 
				qs_variation: $('input[name="ra_variation"]').val(), 
				qs_add_to_wishlist: $('input[name="ra_add_to_wishlist"]').val(), 
				qs_image: $('input[name="ra_image"]').val(), 
				qs_review: $('input[name="ra_review"]').val(), 
				qs_price: $('input[name="ra_price"]').val()
			}, function(data) {
				var data = JSON.parse(data);
				if (data.errors.length > 0) {
					ShopifyApp.Bar.loadingOff();

					var msg = '';
					for(var i = 0; i < data.errors.length; i ++) {
						msg += data.errors[i] + ' ';
					}

					ShopifyApp.Modal.alert({
						title: "Sorry!",
						message: msg,
						okButton: "I understand"
					});
				} else {
					location.reload();	
				}
			} 
		)
	}

	function changeStatus() {
		console.info('Retargeting Info :: Change status.');

		$.post( "ajax_saveConfiguration.php", {
				changeStatus: true,
				domain: $('input[name="ra_domain"]').val(), 
				domain_api_key: $('input[name="ra_domain_api_key"]').val(), 
				api_token: $('input[name="ra_api_token"]').val(), 
				help_pages: $('input[name="ra_help_pages"]').val(), 
				qs_add_to_cart: $('input[name="ra_add_to_cart"]').val(), 
				qs_variation: $('input[name="ra_variation"]').val(), 
				qs_add_to_wishlist: $('input[name="ra_add_to_wishlist"]').val(), 
				qs_image: $('input[name="ra_image"]').val(), 
				qs_review: $('input[name="ra_review"]').val(), 
				qs_price: $('input[name="ra_price"]').val()
			}, function(data) {
				var data = JSON.parse(data);
				if (data.errors.length > 0) {
					ShopifyApp.Bar.loadingOff();

					var msg = '';
					for(var i = 0; i < data.errors.length; i ++) {
						msg += data.errors[i] + ' ';
					}

					ShopifyApp.Modal.alert({
						title: "Sorry!",
						message: msg,
						okButton: "I understand"
					});
				} else {
					location.reload();	
				}
			}
		)
	}
});