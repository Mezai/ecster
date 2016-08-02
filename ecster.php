<?php

class ecster extends PaymentModule
{
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

    public function install()
    {
        return parent::install &&
            $this->registerHook('displayShoppingCart')
            $this->registerHook('backOfficeHeader')
            $this->registerHook('paymentReturn')
            $this->registerHook('header');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('ECSTER_USERNAME')
            && Configuration::deleteByName('ECSTER_PASSWORD')
            && Configuration::deleteByName('ECSTER_MODE');
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


        return $this->display(__FILE__, 'ecstercheckout.tpl');
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
