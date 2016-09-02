/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {

	function init() 
	{
		toggleEcsterCheckout(true);
	}

	function update()
	{
		var currentCartKey = cartKey;
	    var updatedCartCallback = EcsterPay.updateCart(currentCartKey);

	    var settings = {
		"async": true,
		"crossDomain": true,
		"url": baseDir + 'modules/ecster/ecster_ajax.php',
		"method": "POST",
		"dataType": "json",
			"headers": {
		  		"cache-control": "no-cache",
    			"content-type": "application/x-www-form-urlencoded"
  			},
  			"data": {
    			"cartKey": cartKey,
    			"cartId": cartId
  			}
		};
		$.ajax(settings).done(function(data) {
			currentCartKey = data.cartKey;
			updatedCartCallback(currentCartKey);
		});
	}

	$(document).on('change', '.delivery_option_radio', function() {
		//delay so that ps can update carrier via ajax call first.
		setTimeout(update, 1000);
	});
	function toggleEcsterCheckout(show) 
	{
		var $deliveryMethods = $('#opc_delivery_methods');
		var $orderDetailContents = $('#order-detail-content');
		var $account = $('#opc_new_account');
		var $deliveryTitle = $("h1.step-num span").filter(function() { return ($(this).text() === '2') });
		var $payTitle = $("h1.step-num span").filter(function() { return ($(this).text() === '3') });
		var $carrierArea = $('#carrier_area');
		var $paymentMethods = $('#opc_payment_methods');
		var $psPayHeaderSpan = $("h1.step-num span").filter(function() { return ($(this).text() === '3') });
		var $psPayHeader = $psPayHeaderSpan.parents('h1.step-num');
		if (!show) {
			$('#center_column .opc-main-block').show();
		} else {
			$deliveryMethods.insertAfter($orderDetailContents);
			$account.hide();
			$carrierArea.hide();
			$paymentMethods.hide();
			$psPayHeader.hide();
		}
	}
	init();
});