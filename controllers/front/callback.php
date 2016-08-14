<?php


class EcsterCallbackModuleFrontController extends ModuleFrontController {
	
	public $display_column_left = false;
	public $display_column_right = false;
	public $ssl =  true;


	public function postProcess()
	{
		require_once(dirname(__FILE__).'/../../library/EcsterCheckout.php');
		try {
			$inputJSON = file_get_contents('php://input');
			$input = json_decode( $inputJSON, TRUE );
			$internalReference = $input['internalReference'];
			$status = $input['status'];
			$orderId = $input['externalReference'];

			Logger::addLog('Order status notificationUrl called with internalReference : '.$internalReference);
			Logger::addLog('Order status : '.$status);
			Logger::addLog('Order id prestashop :'.$orderId);
			if (Configuration::get('ECSTER_MODE') == 'live') {
				$connector = EcsterConnector::create(
					Configuration::get('ECSTER_USERNAME'),
					Configuration::get('ECSTER_PASSWORD'),
					EcsterConnector::BASE_URL
				);
			} else {

				$connector = EcsterConnector::create(
					Configuration::get('ECSTER_USERNAME'),
					Configuration::get('ECSTER_PASSWORD'),
					EcsterConnector::TEST_URL
				);
			}

			$ecster_order = new EcsterOrder($connector);
			$callback = $ecster_order->fetch($internalReference)->getResponse();
			if (strtolower($status) == "ready") {
				//check if the order already exists
				//
				Logger::addLog('In order success');
				
				$cart = new Cart((int)$orderId);
				
				if ($cart->OrderExists()) {
					header('HTTP/1.1 200 OK', true, 200);
					die;
				}

				$ecster_customer = $callback['customer'];

                $id_customer = (int)Customer::customerExists($ecster_customer['email'], true, false);
				
				if ($id_customer > 0) {
                                    $customer = new Customer($id_customer
                                    );
                    Logger::addLog('Ecster module : creating order with existing customer id : '.$id_customer);
				}  else {
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
				$cart = new Cart($cart->id);
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

		} catch (Exception $e) {
			Logger::addLog('Ecster checkout callback error message: '.$e->getMessage().' and error code : '.$e->getCode());
			header('HTTP/1.1 200 OK', true, 200);
		}
	}
}
