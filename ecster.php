<?php

require_once(dirname(__FILE__).'/library/EcsterCheckout.php');

class Ecster extends PaymentModule
{
    private $html = '';
    private $post_errors = array();

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
        return parent::install()
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('header');
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
            && Configuration::deleteByName('ECSTER_MODE');
    }

    /**
     * Hook header
     *
     * @return void
     */
    public function hookHeader()
    {
        $this->context->controller->addJS('https://labs.ecster.se/pay/integration/ecster-pay-labs.js');

        $this->context->controller->addJS($this->_path.'views/js/ecstercheckout.js');
    }

    public function postValidation()
    {
        if (!Tools::getValue('ECSTER_USERNAME')) {
            $this->post_errors[] = $this->l('You need to provide Ecster username');
        }

        if (!Tools::getValue('ECSTER_PASSWORD')) {
            $this->post_errors[] = $this->l('You need to provide Ecster password');
        } 
    }
    
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
     * Update configuration
     *
     * @return void
     */
    public function postProcess()
    {
        Configuration::updateValue('ECSTER_USERNAME', Tools::getValue('ECSTER_USERNAME'));
        Configuration::updateValue('ECSTER_PASSWORD', Tools::getValue('ECSTER_PASSWORD'));
        Configuration::updateValue('ECSTER_MODE', Tools::getValue('ECSTER_MODE'));

        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }


    public function renderForm() {
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
                    'type' => 'switch',
                    'label' => $this->l('Test mode'),
                    'desc' => $this->l('Select test or live mode'),
                    'name' => 'ECSTER_MODE',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Live')
                            ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Test')
                            )
                        ),
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

        $connector = EcsterConnector::create(
            Configuration::get('ECSTER_USERNAME'),
            Configuration::get('ECSTER_PASSWORD'),
            EcsterConnector::TEST_URL
         );
        
        $order = new EcsterOrder($connector);
        $cart = $this->context->cart;
        $checkoutcart = array(); 
        $create['locale'] = array(
            'language' => $this->context->language->iso_code,
            'country' => $this->context->country->iso_code
        );

        $products = $cart->getProducts();

        $carriers = Carrier::getCarriers($this->context->language->id, true);

        $default_carrier = Carrier::getDefaultCarrierSelection($carriers);

        $shipping_price = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        
        foreach ($carriers as $key => $value) {
            foreach ($value as $k => $v) {
                if ($k == "id_carrier" && $v == (string)$default_carrier) {
                    $create['deliveryMethods'][] = array(
                        'id' => $value['id_carrier'],
                        'name' => $value['name'],
                        'description' => $value['delay'],
                        'price' => (int)$shipping_price * 100,
                        'selected' => true
                    );
                }
            }
        }

        
        $create['cart']['amount'] = intval($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) * 100);
        $create['cart']['currency'] = $this->context->currency->iso_code;
        $create['cart']['message'] = null;
        $create['cart']['externalReference'] = (int)$this->context->cart->id;



        $create['cart']['rows'] = array();
        
        foreach ($products as $product) {
            $price = Tools::ps_round($product['price_wt'], _PS_PRICE_DISPLAY_PRECISION_);
            $price = (int)($price * 100);
            $tax_rate = $product['rate'] . "%"; 
            $checkoutcart[] = array(
                'partNumber' => 'random',
                'name' => $product['name'],
                'description' => $product['reference'],
                'quantity' => (int)$product['quantity'],
                'unitPrice' => $price,
                'unit' => 'pcs',
                'vatCode' => $tax_rate,
                'discount' => 0,
            );
        }

        
        foreach ($checkoutcart as $item) {
            $create['cart']['rows'][] = $item;
        }

        $create['eCommercePlatform'] = array(
            'reference' => '2cc5c43d-fb92-472f-828a-1a5ad703d512',
            'info' => 'info about version'
        );



        $create['customer'] = null;
        $create['returnInfo'] = array(
            'ok' =>(Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?fc=module&module=ecster&controller=checkout' 
        );
        $create['notificationUrl'] = null;
       
        try {
            $cartKey = $order->create($create)->getCartKey();
            $is_ssl = Tools::usingSecureMode();
	    $cms = new CMS((int)Configuration::get('PS_CONDITIONS_CMS_ID'), (int)$this->context->cookie->id_lang);
	    $termsPage = $this->context->link->getCMSLink($cms, $cms->link_rewrite, $is_ssl);
            $this->context->smarty->assign(array(
                'cartKey' => $cartKey,
                'termsPage' => $termsPage
            ));
            
            return $this->display(__FILE__, 'ecstercheckout.tpl');

        } catch (Ecster_ApiErrorException $e) {
            var_dump($e->getMessage()); 
        }

       
    }
    public function getConfigFieldsValues()
    {
return array(
            'ECSTER_USERNAME' => Tools::getValue('ECSTER_USERNAME', Configuration::get('ECSTER_USERNAME')),
            'ECSTER_PASSWORD' => Tools::getValue('ECSTER_PASSWORD', Configuration::get('ECSTER_PASSWORD')),
            'ECSTER_MODE' => Tools::getValue('ECSTER_MODE', Configuration::get('ECSTER_MODE'))
            );
        
    }
}
