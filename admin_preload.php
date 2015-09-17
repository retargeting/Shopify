<?php 
	
	$domainApiKey = null;
	$discountsApiKey = null;
	$status = 0;

	/*
	try
	{

		$mfDomainApiKey = null;
		$mfDiscountsApiKey = null;
		$mfStatus = null;

		$metafields = $shopify('GET /admin/metafields.json?namespace=ra_configuration');

		foreach ($metafields as $mf) {
			if ($mf['key'] == 'domain_api_key') $mfDomainApiKey = $mf;
			if ($mf['key'] == 'discounts_api_key') $mfDiscountsApiKey = $mf;
			if ($mf['key'] == 'app_status') $mfStatus = $mf;
		}

		if ($mfStatus == null) {
			$metafield = $shopify('POST /admin/metafields.json', array(
				'metafield' => array(
					'namespace' => 'ra_configuration',
					'key' => 'app_status',
					'value' => 0,
					'value_type' => 'integer'
				)
			));
			$status = 0;
		} else { 
			$status = $mfStatus['value'];
		}

		if ($mfDomainApiKey == null) {
			$metafield = $shopify('POST /admin/metafields.json', array(
				'metafield' => array(
					'namespace' => 'ra_configuration',
					'key' => 'domain_api_key',
					'value' => '::null::',
					'value_type' => 'string'
				)
			));
			$domainApiKey = '';
		} else { 
			$domainApiKey = $mfDomainApiKey['value'];
			if ($domainApiKey == '::null::') $domainApiKey = '';
		}

		if ($mfDiscountsApiKey == null) {
			$metafield = $shopify('POST /admin/metafields.json', array(
				'metafield' => array(
					'namespace' => 'ra_configuration',
					'key' => 'discounts_api_key',
					'value' => '::null::',
					'value_type' => 'string'
				)
			));
			$discountsApiKey = '';
		} else { 
			$discountsApiKey = $mfDiscountsApiKey['value'];
			if ($discountsApiKey == '::null::') $discountsApiKey = '';
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
	*/

	$_SESSION['app_status'] = $status;

	$view['status'] = $status;
	$view['domain_api_key'] = $domainApiKey;
	$view['discounts_api_key'] = $discountsApiKey;