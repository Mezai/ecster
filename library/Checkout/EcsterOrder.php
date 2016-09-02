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

class EcsterOrder extends EcsterResource
{

    /**
     * Path that is used to create resources.
     *
     * @var string
     */
    protected $createPath = '/eps/v1/cart';

    protected $getPath = '/orders/v1';

    /**
     * contentType
     *
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * Init a new EcsterOrder instance.
     *
     * @param EcsterConnector $connector
     * @param string $cartKey
     */
    public function __construct($connector)
    {
        parent::__construct($connector);
    }

    /**
    * Get Ecster cartKey
    *
    * @return string cartKey
    */
    public function getCartKey()
    {
        return $this->data['response']['key'];
    }

    /**
     * Get the response
     *
     * return string
     */
    public function getResponse()
    {
        return $this->data['response'];
    }

    /**
     * Create a new order.
     *
     * @param array $data
     * @return void
     */
    public function create(array $data)
    {
        $options = array(
          'url' => $this->connector->getDomain() . $this->createPath,
          'data' => $data
        );

        $this->connector->apply('POST', $this, $options);

        return $this;
    }

    /**
     * Update a order.
     *
     * @param array $data
     * @return void
     */
    public function update(array $data, $cartKey)
    {
        $options = array(
            'url' => $this->connector->getDomain() . "{$this->createPath}/{$cartKey}",
            'data' => $data
        );

        $this->connector->apply('PUT', $this, $options);

        return $this;
    }
    /**
     * Fetch a order.
     *
     * @param  $internalReference ecster order id
     * @return EcsterOrder
     */
    
    public function fetch($internalReference)
    {
        $options = array(
            'url' => $this->connector->getDomain() . "{$this->getPath}/{$internalReference}"
        );

        $this->connector->apply('GET', $this, $options);

        return $this;
    }
}
