<?php /** * 2007-2016 PrestaShop * * NOTICE OF LICENSE * * This source file is subject to the Academic Free License (AFL 3.0) * that is bundled with this package in the file LICENSE.txt.  * It is also available through the world-wide-web at this URL: * http://opensource.org/licenses/afl-3.0.php * If you did not receive a copy of the license and are unable to * obtain it through the world-wide-web, please send an email * to license@prestashop.com so we can send you a copy immediately.  * * DISCLAIMER
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

require_once(dirname(__FILE__).'/../../library/EcsterCheckout.php');
class EcsterCallbackModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $ssl =  true;

    private $ecsterCart;

    private function setState($state)
    {
        $orderId = Order::getOrderByCartId($this->ecsterCart->id);
        $order = new Order($orderId);
        if ($this->ecsterCart->OrderExists() && $order->getCurrentState() != constant($state)) {
            $order->setCurrentState(constant($state));
            header('HTTP/1.1 200 OK', true, 200);
            exit;
        }
    }

    private function customerExists(array $ecster_customer) {
    	return ($id_customer = (int)Customer::customerExists($ecster_customer['email'], true, false)) > 0 ? $id_customer : false;
    }

    private function createCustomer(array $ecster_customer)
    {        
            $customer = new Customer();
            $customer->firstname = $this->splitNames($ecster_customer, 'firstname');
            $customer->lastname = $this->splitNames($ecster_customer, 'lastname');
            $customer->email = $ecster_customer['email'];
            $customer->passwd = Tools::passwdGen(8, 'ALPHANUMERIC');
            $customer->is_guest = 1;
            $customer->id_default_group = (int)Configuration::get('PS_GUEST_GROUP', null, $this->ecsterCart->id_shop);
            $customer->newsletter = 0;
            $customer->optin = 0;
            $customer->active = 1;
            $customer->id_gender = 0;

            $customer->add();
            return $customer;

    }

    private function splitNames(array $ecster_customer, $name)
    {
        $names = preg_split('/[\s,]+/', $ecster_customer['name']);
        $nameArray = array(
            'firstname' => $names[0],
            'lastname' => $names[1],
        );
        return $nameArray[$name];
    }

    private function createAddress(Customer $customer, array $ecster_address, $type)
    {
        $address = new Address();
        $address->firstname = $this->splitNames($ecster_address, 'firstname');
        $address->lastname = $this->splitNames($ecster_address, 'lastname');
        $address->address1 = $ecster_address['address'];
        $address->postcode = $ecster_address['zip'];
        $address->phone = $ecster_address['cellular'];
        $address->phone_mobile = $ecster_address['cellular'];
        $address->city = $ecster_address['city'];
        $address->id_country = (int)Country::getByIso('se');
        $address->id_customer = $customer->id;
        $address->alias = 'Ecster address';
        $address->add();
        if ($type == 'shipping') {
            $this->ecsterCart->id_address_delivery = $address->id;
        }
        if ($type == 'invoice') {
            $this->ecsterCart->id_address_invoice = $address->id;
        }
    }

    private function checkIfAddressExists(Customer $customer, array $ecster_address)
    {
        $ecster_address['firstname'] = $this->splitNames($ecster_address, 'firstname');
        $ecster_address['lastname'] = $this->splitNames($ecster_address, 'lastname');

        $address = $customer->getAddresses($this->ecsterCart->id_lang);

        foreach ($addresses as $key => $value) {

           if ($addresses[$key]['firstname'] === $ecster_address['firstname'] && $addresses[$key]['lastname'] === $ecster_address['lastname'] && $addresses[$key]['address1'] === $ecster_address['address'] &&
           		$addresses[$key]['city'] === $ecster_address['city'] && $addresses[$key]['postcode'] === $ecster_address['zip']) {
           		return $addresses[$key]['id_address'];
           }
        }
        return false;
    }

    public function postProcess()
    {
        try {
            $input = Tools::jsonDecode(Tools::file_get_contents('php://input'), true);
            $internalReference = $input['internalReference'];
            $ecster_order = new EcsterOrder($this->module->config);
            $ecster = $ecster_order->fetch($internalReference)->getResponse();
            $this->ecsterCart = new Cart((int)$input['externalReference']);

            if (Tools::strtolower($input['status']) == "ready") {
                if ($this->ecsterCart->OrderExists()) {
                    header('HTTP/1.1 200 OK', true, 200);
                    exit;
                }

                $ecsterOrder = $ecster['order'];
                if ($id_customer = $this->customerExists($ecster['customer'])) {
                		$customer = new Customer($id_customer);
                } else {
                		$customer = $this->createCustomer($ecster['customer']);
                }

                if ($ecsterOrder['idMethod'] === 'BANKID' || $ecsterOrder['idMethod'] === 'BANKID_MOBILE') 
                	if (array_key_exists('recipient', $ecster)) {

                		if ($id_address = $this->checkIfAddressExists($customer, $ecster['customer'])) {
                			$this->ecsterCart->id_address_invoice = $id_address;
                		} else {
                			$this->createAddress($customer, $ecster['customer'], 'invoice');
                		}
                		if ($id_address = $this->checkIfAddressExists($customer, $ecster['recipient'])) {
                			$this->ecsterCart->id_address_delivery = $id_address;
                		} else {
                			$this->createAddress($customer, $ecster['recipient'], 'shipping');
                		}

                	} else {
                		
                		if ($id_address = $this->checkIfAddressExists($customer, $ecster['customer'])) {
                			$this->ecsterCart->id_address_delivery = $id_address;
                			$this->ecsterCart->id_address_invoice = $id_address;

                		} else {
                			$this->createAddress($customer, $ecster['customer'], 'invoice');
                			$this->createAddress($customer, $ecster['customer'], 'shipping');
                		}
                	}

                if ($ecster['idMethod'] === 'NAME') {
                	if ($id_address = $this->checkIfAddressExists($customer, $ecster['recipient'])) {
                		$this->ecsterCart->id_address_delivery = $id_address;
                		$this->ecsterCart->id_address_invoice = $id_address;
                	} else {
                		$this->createAddress($customer, $ecster['recipient'], 'invoice');
                		$this->createAddress($customer, $ecster['recipient'], 'shipping');
                	}
                }

                $new_delivery_options = array();
                $new_delivery_options[(int)$this->ecsterCart->id_address_delivery] = $this->ecsterCart->id_carrier.',';
                $new_delivery_options_serialized = serialize($new_delivery_options);
                Db::getInstance()->update('cart', array(
                    'delivery_option' => pSQL($new_delivery_options_serialized)),
                    'id_cart='.(int)$this->ecsterCart->id
                );
                $this->ecsterCart->delivery_option = '';
                if ($this->ecsterCart->id_carrier > 0) {
                    $this->ecsterCart->delivery_option = $new_delivery_options_serialized;
                }
                Db::getInstance()->update('cart_product', array('id_address_delivery' => pSQL($this->ecsterCart->id_address_delivery)), 'id_cart='.(int)$this->ecsterCart->id);
                $this->ecsterCart->getPackageList(true);
                $this->ecsterCart->id_customer = $customer->id;
                $this->ecsterCart->secure_key = $customer->secure_key;
                $this->ecsterCart->save();
                Logger::addLog('Saved cart');
                Db::getInstance()->update('cart', array('id_customer' => pSQL($customer->id)), 'id_cart='.(int)$this->ecsterCart->id);
                Db::getInstance()->update('cart', array('secure_key' => pSQL($customer->secure_key)), 'id_cart='.(int)$this->ecsterCart->id);
                Cache::clean('objectmodel_cart_'.$this->ecsterCart->id.'_0_0');
                $amount = (int)($ecsterOrder['amount']);
                $amount = (float)($amount/100);
                //validate order
                $validation = $this->module->validateOrder(
                    $this->ecsterCart->id,
                    Configuration::get('PS_OS_PAYMENT'),
                    $amount,
                    $this->module->displayName,
                    $ecsterOrder['internalReference'],
                    array(
                        'transaction_id' => $ecsterOrder['internalReference']
                    ),
                    null,
                    false,
                    $customer->secure_key
                );
                if ($validation) {
                	$products = $this->ecsterCart->getProducts();
				    foreach ($products as $product) {
				      $this->ecsterCart->deleteProduct($product["id_product"]);
				    }
                	header('HTTP/1.1 200 OK', true, 200);
                	exit;
                }
                
            }

            if (Tools::strtolower($input['status']) == "fullydelivered") {
                $this->setState('OrderState::FLAG_DELIVERY');
            }

            if (Tools::strtolower($input['status']) == "annuled") {
                $this->setState('OrderState::FLAG_NO_HIDDEN');
            }
        } catch (Exception $e) {
            Logger::addLog('Ecster checkout callback error message: '.$e->getMessage().' and error code : '.$e->getCode());
        }
    }
}
