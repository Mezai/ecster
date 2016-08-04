<?php


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
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CURLHeaders.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CurlHandle.php';
require_once ECSTER_CHECKOUT_DIR . '/HTTP/CurlFactory.php';
