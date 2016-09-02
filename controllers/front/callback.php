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

class EcsterCallbackModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl =  true;


    public function postProcess()
    {
        require_once(dirname(__FILE__).'/../../library/EcsterCheckout.php');
        try {
            $inputJSON = Tools::file_get_contents('php://input');
            $input = Tools::jsonDecode($inputJSON, true);
            $internalReference = $input['internalReference'];
            $status = $input['status'];
            $cartId = $input['externalReference'];

            Logger::addLog('Order status notificationUrl called with internalReference : '.$internalReference);
            Logger::addLog('Order status input : '.$status);
            Logger::addLog('Cart id prestashop :'.$cartId);
            

            $ecster_order = new EcsterOrder($this->module->config);
            $callback = $ecster_order->fetch($internalReference)->getResponse();
            $cart = new Cart((int)$cartId);
            $orderId = Order::getOrderByCartId($cart->id);
            $order = new Order($orderId);
            Logger::addLog('Order status order object : '.$callback['order']['status']);
            if (Tools::strtolower($status) == "ready") {
                //check if the order already exists
                //
                Logger::addLog('In order success');
                
                
                
                if ($cart->OrderExists()) {
                    header('HTTP/1.1 200 OK', true, 200);
                    die;
                }

                $ecster_customer = $callback['customer'];

                $id_customer = (int)Customer::customerExists($ecster_customer['email'], true, false);
                
                if ($id_customer > 0) {
                    $customer = new Customer($id_customer);
                    Logger::addLog('Ecster module : creating order with existing customer id : '.$id_customer);
                } else {
                    //Create customer
                    if (array_key_exists('recipient', $callback)) {
                        $names = preg_split('/[\s,]+/', $callback['recipient']['name']);
                    } else {
                        $names = preg_split('/[\s,]+/', $ecster_customer['name']);
                    }
                    $customer = new Customer();
                    $customer->firstname = $names[1];
                    $customer->lastname = $names[0];
                    $customer->email = $ecster_customer['email'];
                    $customer->passwd = Tools::passwdGen(8, 'ALPHANUMERIC');
                    $customer->is_guest = 1;
                    $customer->id_default_group = (int)Configuration::get('PS_GUEST_GROUP', null, $cart->id_shop);
                    $customer->newsletter = 0;
                    $customer->optin = 0;
                    $customer->active = 1;
                    $customer->id_gender = 0;

                    Logger::addLog('Ecster module : created new customer with id :'.$customer->id);
                    $customer->add();
                }

                $delivery_address_id = 0;
                $invoice_address_id = 0;
                

                // check if address already exists, if not add it
                foreach ($customer->getAddresses($cart->id_lang) as $address) {
                    if (array_key_exists('recipient', $callback)) {
                        //create delivery address with recipient
                        $recipient = $callback['recipient'];
                        $recipient_names = preg_split('/[\s,]+/', $recipient['name']);

                        if (($address['firstname'] == $recipient_names[0] && $address['lastname'] == $recipient_names[1]
                        || $address['firstname'] == $recipient_names[1] && $address['lastname'] == $recipient_names[0])
                        && $address['postcode'] == $recipient['zip'] && $address['city'] == $recipient['city']
                        && $address['address1'] == $recipient['address']) {
                            //load invoice address
                            $cart->id_address_invoice = $address['id_address'];
                            $invoice_address_id = $address['id_address'];
                        }

                        if (($address['firstname'] ==  $recipient_names[0] && $address['lastname'] == $recipient_names[1] || $address['firstname']
                            == $recipient_names[1] && $address['lastname'] == $recipient_names[0]) && $address['postcode'] == $recipient['zip'] &&
                            $address['city'] == $recipient['city'] && $address['phone_mobile'] == $recipient['cellular'] &&
                            $address['address1'] == $recipient['address']) {
                            //load shipping address
                            $cart->id_address_delivery = $address['id_address'];
                            $delivery_address_id = $address['id_address'];
                        }
                    } else {
                        $ecster_customer_names = preg_split('/[\s,]+/', $ecster_customer['name']);
                        //create delivery address with customer
                        if (($address['firstname'] ==  $ecster_customer_names[0] && $address['lastname'] == $ecster_customer_names[1] || $address['firstname']
                                == $ecster_customer_names[1] && $address['lastname'] == $ecster_customer_names[0]) && $address['postcode'] == $ecster_customer['zip'] &&
                            $address['city'] == $ecster_customer['city'] && $address['phone_mobile'] == $ecster_customer['cellular'] &&
                            $address['address1'] == $ecster_customer['address']) {
                            //load invoice address
                            $cart->id_address_invoice = $address['id_address'];
                            $invoice_address_id = $address['id_address'];
                        }

                        if (($address['firstname'] ==  $ecster_customer_names[0] && $address['lastname'] == $ecster_customer_names[1] || $address['firstname']
                            == $ecster_customer_names[1] && $address['lastname'] == $ecster_customer_names[0]) && $address['postcode'] == $ecster_customer['zip'] &&
                            $address['city'] == $ecster_customer['city'] && $address['phone_mobile'] == $ecster_customer['cellular'] &&
                            $address['address1'] == $ecster_customer['address']) {
                            //load shipping address
                            $cart->id_address_delivery = $address['id_address'];
                            $delivery_address_id = $address['id_address'];
                        }
                    }
                }

                if ($invoice_address_id == 0) {
                    //create
                    if (array_key_exists('recipient', $callback)) {
                        $ecster_recipient = $callback['recipient'];
                        $address_names = preg_split('/[\s,]+/', $ecster_recipient['name']);
                       
                        $address = new Address();
                        $address->firstname =  $address_names[1];
                        $address->lastname = $address_names[0];
                        $address->address1 = $ecster_recipient['address'];
                        $address->postcode = $ecster_recipient['zip'];
                        $address->phone = $ecster_customer['cellular'];
                        $address->phone_mobile = $ecster_customer['cellular'];
                        $address->city = $ecster_recipient['city'];
                        $address->id_country = (int)Country::getByIso('se');
                        $address->id_customer = $customer->id;
                        $address->alias = 'Ecster address';
                        $address->add();
                        $cart->id_address_invoice = $address->id;
                        $invoice_address_id = $address->id;
                    } else {
                        $address_names = preg_split('/[\s,]+/', $ecster_customer['name']);
                        $address = new Address();
                        $address->firstname =  $address_names[1];
                        $address->lastname = $address_names[0];
                        $address->address1 = $ecster_customer['address'];
                        $address->postcode = $ecster_customer['zip'];
                        $address->phone = $ecster_customer['cellular'];
                        $address->phone_mobile = $ecster_customer['cellular'];
                        $address->city = $ecster_customer['city'];
                        $address->id_country = (int)Country::getByIso('se');
                        $address->id_customer = $customer->id;
                        $address->alias = 'Ecster address';
                        $address->add();
                        $cart->id_address_invoice = $address->id;
                        $invoice_address_id = $address->id;
                    }
                    Logger::addLog('Created invoiceAddress');
                }

                if ($delivery_address_id == 0) {
                    if (array_key_exists('recipient', $callback)) {
                        $ecster_recipient = $callback['recipient'];
                        $address_names = preg_split('/[\s,]+/', $ecster_recipient['name']);
                        
                        $address = new Address();
                        $address->firstname =  $address_names[1];
                        $address->lastname = $address_names[0];
                        $address->address1 = $ecster_recipient['address'];
                        $address->postcode = $ecster_recipient['zip'];
                        $address->phone = $ecster_customer['cellular'];
                        $address->phone_mobile = $ecster_customer['cellular'];
                        $address->city = $ecster_recipient['city'];
                        $address->id_country = (int)Country::getByIso('se');
                        $address->id_customer = $customer->id;
                        $address->alias = 'Ecster address';
                        $address->add();
                        $cart->id_address_delivery = $address->id;
                        $delivery_address_id = $address->id;
                    } else {
                        $address_names = preg_split('/[\s,]+/', $ecster_customer['name']);
                        $address = new Address();
                        $address->firstname =  $address_names[1];
                        $address->lastname = $address_names[0];
                        $address->address1 = $ecster_customer['address'];
                        $address->postcode = $ecster_customer['zip'];
                        $address->phone = $ecster_customer['cellular'];
                        $address->phone_mobile = $ecster_customer['cellular'];
                        $address->city = $ecster_customer['city'];
                        $address->id_country = (int)Country::getByIso('se');
                        $address->id_customer = $customer->id;
                        $address->alias = 'Ecster address';
                        $address->add();
                        //$this->createAddress($ecster_customer, $customer);
                        $cart->id_address_delivery = $address->id;
                        $delivery_address_id = $address->id;
                    }
                    Logger::addLog('Created deliveryAddress');
                }

                $new_delivery_options = array();
                $new_delivery_options[(int)$delivery_address_id] = $cart->id_carrier.',';
                $new_delivery_options_serialized = serialize($new_delivery_options);

                Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'cart`
						SET `delivery_option` = \''.pSQL($new_delivery_options_serialized).'\'
						WHERE `id_cart` = '.(int)$cart->id);

                if ($cart->id_carrier > 0) {
                    $cart->delivery_option = $new_delivery_options_serialized;
                } else {
                    $cart->delivery_option = '';
                }

                Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'cart_product`
						SET `id_address_delivery` = \''.pSQL($delivery_address_id).'\'
						WHERE `id_cart` = '.(int)$cart->id);

                $cart->getPackageList(true);

                $cart->id_customer = $customer->id;
                $cart->secure_key = $customer->secure_key;
                
                $cart->save();
                Logger::addLog('Saved cart');

                Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'cart`
					SET `id_customer` = \''.pSQL($customer->id).'\'
					WHERE `id_cart` = '.(int)$cart->id);
                Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'cart`
					SET `secure_key` = \''.pSQL($customer->secure_key).'\'
					WHERE `id_cart` = '.(int)$cart->id);

                $cache_id = 'objectmodel_cart_'.$cart->id.'_0_0';
                Cache::clean($cache_id);
                $amount = (int)($callback['order']['amount']);
                $amount = (float)($amount/100);
                //validate order
                $presta_order = $this->module->validateOrder(
                    $cart->id,
                    Configuration::get('PS_OS_PAYMENT'),
                    $amount,
                    $this->module->displayName,
                    $callback['order']['internalReference'],
                    array(
                        'transaction_id' => $callback['order']['internalReference']
                    ),
                    null,
                    false,
                    $customer->secure_key
                );

                if ($presta_order) {
                    header('HTTP/1.1 200 OK', true, 200);
                    exit;
                }
            }

            if (Tools::strtolower($status) == "fullydelivered") {
                if ($cart->OrderExists() && $order->getCurrentState() != constant('OrderState::FLAG_DELIVERY')) {
                    $order->setCurrentState(constant('OrderState::FLAG_DELIVERY'));
                    header('HTTP/1.1 200 OK', true, 200);
                    exit;
                }
            }

            if (Tools::strtolower($status) == "annuled") {
                if ($cart->OrderExists() && $order->getCurrentState() != constant('OrderState::FLAG_DELIVERY')) {
                    //annuled
                }
            }
        } catch (Exception $e) {
            Logger::addLog('Ecster checkout callback error message: '.$e->getMessage().' and error code : '.$e->getCode());
            header('HTTP/1.1 200 OK', true, 200);
        }
    }
}
