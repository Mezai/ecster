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

class Ecster_HTTP_Response
{

    /**
     * HTTP response status code.
     *
     * @var int
     */
    protected $status;


    /**
     * Request object.
     *
     * @var Ecster_HTTP_Request
     */
    protected $request;


    /**
     * HTTP headers.
     *
     * @var array
     */
    protected $headers;


    /**
     * Data.
     *
     * @var string
     */
    protected $data;

    /**
     * Init new instance of the Ecster_HTTP_Response
     *
     * @param Ecster_Http_Request $request
     * @param array $headers
     * @param string $status
     * @param string $data
     */
    public function __construct(Ecster_Http_Request $request, array $headers, $status, $data)
    {
        $this->request = $request;
        $this->headers = array();
        foreach ($headers as $key => $value) {
            $this->headers[Tools::strtolower($key)] = $value;
        }
        $this->status = $status;
        $this->data = $data;
    }


    /**
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the HTTP request that this response originated from.
     *
     * @return obj
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Get data.
     *
     * @return string response payload
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get all headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get a specific header
     *
     * @param string $name
     * @return string|null if the header does not exists.
     */
    public function getHeader($name)
    {
        $name = Tools::strtolower($name);
        if (!array_key_exists($name, $this->headers)) {
            return null;
        }

        return $this->headers[$name];
    }
}
