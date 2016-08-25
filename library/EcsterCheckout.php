<?php
/**
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

define('ECSTER_CHECKOUT_DIR', dirname(__FILE__) . '/Checkout');

require_once ECSTER_CHECKOUT_DIR . '/EcsterConnector.php';
require_once ECSTER_CHECKOUT_DIR . '/EcsterBasicConnector.php';
require_once ECSTER_CHECKOUT_DIR . '/Resource.php';
require_once ECSTER_CHECKOUT_DIR . '/EcsterOrder.php';


//Exceptions
require_once ECSTER_CHECKOUT_DIR . '/Exceptions/Exception.php';
require_once ECSTER_CHECKOUT_DIR . '/Exceptions/ConnectionErrorException.php';
require_once ECSTER_CHECKOUT_DIR . '/Exceptions/ApiErrorException.php';
require_once ECSTER_CHECKOUT_DIR . '/Exceptions/ConnectorErrorException.php';

//HTTP
require_once ECSTER_CHECKOUT_DIR . '/HTTP/Request.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/Response.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/Transport.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CurlTransport.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CurlHeaders.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CurlHandle.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CurlFactory.php';
