{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="ecster-pay-ctr"></div>

<script>
	// <![CDATA[
	var cartKey = "{$cartKey|escape:'htmlall':'UTF-8'}";
	var cartId = "{$cartId|escape:'htmlall':'UTF-8'}";
    var termsPage = "{$termsPage|escape:'htmlall':'UTF-8'}";
	var errorPage = "{$errorPage|escape:'htmlall':'UTF-8'}";
	//]]>
	EcsterPay.start({
    	cartKey: cartKey, // from create cart REST call
    	shopTermsUrl: termsPage,
    	showCart: false,
    	showPaymentResult: true,
        onCheckoutStartInit: function() {

        },
        onCheckoutStartSuccess: function() {

        },
    	onPaymentSuccess: function() {

        },
        onPaymentFailure: function() {
    		window.location = errorPage;
    	},
    });
</script>