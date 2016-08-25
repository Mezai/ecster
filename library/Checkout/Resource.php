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

abstract class EcsterResource
{
    protected $contentType = null;

    protected $accept = null;

    protected $location;

    public $data = array();

    protected $connector;


    public function __construct($connector)
    {
        $this->connector = $connector;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = (string)$location;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getAccept()
    {
        return $this->accept;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function setAccept($accept)
    {
        $this->accept = $accept;
    }

    public function parse(array $data)
    {
        $this->data = $data;
    }

    public function marshal()
    {
        return $this->data;
    }
}
