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

class Ecster_CurlHeaders
{

  /**
   * Curl headers.
   *
   * @var array
   */
  protected $headers;

    public function __construct()
    {
        $this->headers = array();
    }


    public function processHeader($curl, $header)
    {
        $curl = null;
        //TODO replace with regexp, e.g. /^([^:]+):([^:]*)$/ ?
        $pos = strpos($header, ':');
        // Didn't find a colon.
        if ($pos === false) {
            // Not real header, abort.
            return Tools::strlen($header);
        }
        $key = Tools::substr($header, 0, $pos);
        $value = trim(Tools::substr($header, $pos+1));
        $this->headers[$key] = trim($value);
        return Tools::strlen($header);
    }

  /**
   * Get headers.
   *
   * @return array $headers
   */
  public function getHeaders()
  {
      return $this->headers;
  }
}
