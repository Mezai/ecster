

<div id="ecster-pay-ctr"></div>
<script>
	// <![CDATA[
	var cartKey = "{$cartKey|escape:'htmlall':'UTF-8'}";
	var termsPage ="{$termsPage|escape:'htmlall':'UTF-8'}";
	//]]>
	EcsterPay.start({
    	cartKey: cartKey, // from create cart REST call
    	shopTermsUrl: termsPage
	});
</script>