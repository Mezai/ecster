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

class Ecster_Http_Request
{
    /**
     * HTTP url
     *
     * @var string
     */
    protected $url;

    /**
     * HTTP Method
     *
     * @var string
     */
    protected $method;

    /**
     * HTTP headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Payload
     *
     * @var string
     */
    protected $data;

    /**
     * Init new Ecster_Http_Request instance.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->method = 'GET';
        $this->headers = array();
        $this->data = '';
    }

    /**
     * Set the HTTP Method used for the request.
     *
     *
     * @param string $method a HTTP method.
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = Tools::strtoupper($method);
    }

    /**
     * Gets the HTTP method used for the request.
     *
     * @return string HTTP method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets the request url.
     *
     * @return string the request URL.
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Set header for the request.
     *
     * @param string $name the header name
     * @param mixed $value the header value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = (string)$value;
    }


    /**
     * Get specifiv header for the request
     *
     * @param string $name the header name
     * return string|null the header value
     */

    public function getHeader($name)
    {
        if (!array_key_exists($name, $this->headers)) {
            return null;
        }

        return $this->headers[$name];
    }

    /**
     * Gets the headers specified for the request.
     *
     * @return array headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * Sets the data (payload) for the request.
     *
     * @param string $data the request payload.
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the data (payload) for the request.
     *
     * @return string the request payload
     */
    public function getData()
    {
        return $this->data;
    }
}
