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

require_once(dirname(__FILE__).'/library/EcsterCheckout.php');
require_once(dirname(__FILE__).'/models/EcsterOrderModel.php');

class Ecster extends PaymentModule
{
    private $html = '';
    private $post_errors = array();
    public $config;
    public function __construct()
    {
        $this->name = 'ecster';
        $this->version = '1.0.0';
        $this->author = 'JET';
        $this->tab = 'payments_gateways';
        $this->need_instance = 1;
        $this->module_key = '';
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

       
        $this->config = EcsterConnector::create(
            Configuration::get('ECSTER_USERNAME'),
            Configuration::get('ECSTER_PASSWORD'),
            (Configuration::get('ECSTER_MODE') == 'live') ?EcsterConnector::BASE_URL : EcsterConnector::TEST_URL
        );

        parent::__construct();

        $this->displayName = $this->l('Ecster');
        $this->description = $this->l('Lets your customers pay via Ecster Checkout');

        if (!extension_loaded('curl')) {
            $this->warning = $this->l('You need to activate curl to use Ecster');
        }
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {   

        require_once dirname(__FILE__).'/ecster_install.php';
        $ecster_install = new EcsterInstall();
        return parent::install()
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('header')
            && $ecster_install->createTables();
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->unregisterHook('displayShoppingCart')
            && $this->unregisterHook('backOfficeHeader')
            && $this->unregisterHook('paymentReturn')
            && $this->unregisterHook('header')
            && Configuration::deleteByName('ECSTER_USERNAME')
            && Configuration::deleteByName('ECSTER_PASSWORD')
            && Configuration::deleteByName('ECSTER_ECPID')
            && Configuration::deleteByName('ECSTER_MODE');
    }

    /**
     * Hook header
     *
     * @return void
     */
    public function hookHeader()
    {
        if (Configuration::get('ECSTER_MODE') == 'live') {
        	$this->context->controller->addJS(
        		'https://secure.ecster.se/pay/integration/ecster-pay.js'
        	);
        } else {
        	$this->context->controller->addJS('https://labs.ecster.se/pay/integration/ecster-pay-labs.js'
        	);
        }
      

        $this->context->controller->addJS($this->_path.'views/js/ecstercheckout.js');
    }

    /**
     * Validate user input.
     *
     * 
     * @return void
     */
    private function postValidation()
    {
        if (!Tools::getValue('ECSTER_USERNAME')) {
            $this->post_errors[] = $this->l('You need to provide Ecster username');
        }

        if (!Tools::getValue('ECSTER_PASSWORD')) {
            $this->post_errors[] = $this->l('You need to provide Ecster password');
        }

        if (!Tools::getValue('ECSTER_ECPID')) {
            $this->post_errors[] = $this->l('You need to provide Ecster ECP ID');
        }
    }

    /**
     * getContent
     * 
     * 
     * @return $html form
     */
    public function getContent()
    {
        if (Tools::isSubmit('saveBtn')) {
            $this->postValidation();
            if (!count($this->post_errors)) {
                $this->postProcess();
            } else {
                foreach ($this->post_errors as $error) {
                    $this->html = $this->displayError($error);
                }
            }
        } else {
            $this->html .= '<br />';
        }
        $this->html .= $this->renderForm();
        return $this->html;
    }
    /**
     * Build a ecster order.
     *
     * 
     * @param  null|int $cartId use existing cart or new up one with cartId
     * @return array ecster order data
     */
    public function buildOrder($cartId = null)
    {
        if (is_null($cartId)) {
            $cart = $this->context->cart;
        } else {
            $cart = new Cart((int)$cartId);
        }
        $checkoutcart = array();
        $create = array();
        $create['locale'] = array(
            'language' => $this->context->language->iso_code,
            'country' => $this->context->country->iso_code
        );
        $products = $cart->getProducts();
        $carrier = new Carrier($cart->id_carrier, $this->context->language->id);
        $shippingCost = $cart->getCarrierCost($carrier->id); 
        $create['deliveryMethods'][] = array(
            'id' => $carrier->id_reference,
            'name' => $carrier->name,
            'description' => $carrier->delay,
            'price' => (int)($shippingCost * 100),
            'selected' => true
        );
        $create['cart']['amount'] = (int)($cart->getOrderTotal(true, Cart::BOTH) * 100);
        $create['cart']['currency'] = $this->context->currency->iso_code;
        $create['cart']['message'] = null;
        $create['cart']['externalReference'] = (int)$this->context->cart->id;
        $create['cart']['rows'] = array();
        
        foreach ($products as $product) {
            $price = Tools::ps_round($product['price_wt'], _PS_PRICE_DISPLAY_PRECISION_);
            $price = (int)($price * 100);
            $tax_rate = $product['rate'] . "%";
            $checkoutcart[] = array(
                'name' => $product['name'],
                'description' => $product['reference'],
                'quantity' => (int)$product['quantity'],
                'unitPrice' => $price,
                'unit' => 'pcs',
                'vatCode' => $tax_rate,
                'discount' => 0,
            );
        }

        $discounts = $cart->getCartRules();

        if (count($discounts) > 0) {
            foreach ($discounts as $discount) {
                $price = $discount['value_real'];
                $tax_discount = (int)round((($discount['value_real'] / $discount['value_tax_exc']) - 1.0) * 100);
                $checkoutcart[] = array(
                    'name' => $discount['name'],
                    'quantity' => 1,
                    'unitPrice' => -($price * 100),
                    'vatCode' => (string)$tax_discount.'%'
                ); 
            }
        }
        
        foreach ($checkoutcart as $item) {
            $create['cart']['rows'][] = $item;
        }
        $create['eCommercePlatform'] = array(
            'reference' => Configuration::get('ECSTER_ECPID'),
            'info' => 'version 1.0.0'
        );
        $create['customer'] = null;
        $create['returnInfo'] = array(
            'ok' => (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?fc=module&module=ecster&controller=checkout'
        );
        $create['notificationUrl'] = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?fc=module&module=ecster&controller=callback';

        return $create;
    }

    /**
     * Update configuration
     *
     * @return void
     */
    public function postProcess()
    {
        Configuration::updateValue('ECSTER_USERNAME', Tools::getValue('ECSTER_USERNAME'));
        Configuration::updateValue('ECSTER_PASSWORD', Tools::getValue('ECSTER_PASSWORD'));
        Configuration::updateValue('ECSTER_ECPID', Tools::getValue('ECSTER_ECPID'));
        Configuration::updateValue('ECSTER_MODE', Tools::getValue('ECSTER_MODE'));

        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    /**
     * Render the backoffice form
     * 
     * @return a tpl fetched
     */
    public function renderForm()
    {
        $ecster_mode = array(
        array(
            'id_option' => 'live',
            'name' => 'Live'
            ),
        array(
            'id_option' => 'beta',
            'name' => 'Test'
        ),
    );

        $fields_form = array(
        'form' => array(
            'legend' => array(
                'title' => $this->l('Configure Ecster'),
                'icon' => 'icon-cogs'
                ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Ecster username'),
                    'desc' => $this->l('Username for Ecster'),
                    'required' => true,
                    'name' => 'ECSTER_USERNAME',
                    'class' => 'fixed-width-xxl',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Password from Ecster'),
                    'desc' => $this->l('Password for Ecster'),
                    'class' => 'fixed-width-xxl',
                    'name' => 'ECSTER_PASSWORD',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('ECP ID'),
                    'desc' => $this->l('ECP ID from Ecster'),
                    'class' => 'fixed-width-xxl',
                    'name' => 'ECSTER_ECPID',
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Live mode'),
                    'desc' => $this->l('Select test or live mode'),
                    'name' => 'ECSTER_MODE',
                    'options' => array(
                        'query' => $ecster_mode,
                        'id' => 'id_option',
                        'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'button pull-right'
                    )
                ),
            );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
        ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveBtn';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
        '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
            );
        return $helper->generateForm(array($fields_form));
    }

    public function hookDisplayShoppingCart()
    {
        if (!$this->active) {
            return;
        }
        
        try {
            $order = new EcsterOrder($this->config);
       
        	$cartKey = $order->create($this->buildOrder())->getCartKey();
            
            $is_ssl = Tools::usingSecureMode();
            $cms = new CMS((int)Configuration::get('PS_CONDITIONS_CMS_ID'), (int)$this->context->cookie->id_lang);
            $termsPage = $this->context->link->getCMSLink($cms, $cms->link_rewrite, $is_ssl);
            $errorPage = $this->context->link->getModuleLink($this->name, 'error');
            $this->context->smarty->assign(array(
                'cartKey' => $cartKey,
                'termsPage' => $termsPage,
                'cartId' => $this->context->cart->id,
                'errorPage' => $errorPage
            ));
            
            return $this->display(__FILE__, 'ecstercheckout.tpl');
        } catch (Ecster_ApiErrorException $e) {
            Logger::addLog('Failed starting ecster with error message : '.$e->getMessage().' and error code :'.$e->getCode());
        }
    }

    /**
     * getConfigFieldsValues
     * 
     * @return array config values
     */
    public function getConfigFieldsValues()
    {
        return array(
            'ECSTER_USERNAME' => Tools::getValue('ECSTER_USERNAME', Configuration::get('ECSTER_USERNAME')),
            'ECSTER_PASSWORD' => Tools::getValue('ECSTER_PASSWORD', Configuration::get('ECSTER_PASSWORD')),
            'ECSTER_ECPID' => Tools::getValue('ECSTER_ECPID', Configuration::get('ECSTER_ECPID')),
            'ECSTER_MODE' => Tools::getValue('ECSTER_MODE', Configuration::get('ECSTER_MODE'))
            );
    }
}
