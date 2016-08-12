<?php


class EcsterCallbackModuleFrontController extends ModuleFrontController {
	
	public $display_column_left = false;
	public $display_column_right = false;
	public $ssl =  true;

	private function createAddress()
	{
		$address_names = explode(',', $ecster_customer['name']);
		$address = new Address();
		$address->firstname =  trim($address_names[1]);
		$address->lastname = trim($address_names[0]);
		$address->address1 = $ecster_customer['address'];
		$address->postcode = $ecster_customer['zip'];
		$address->phone = $ecster_customer['cellular'];
		$address->phone_mobile = $ecster_customer['cellular'];
		$address->city = $ecster_customer['city'];
		$address->id_country = Country::getByIso('se');
		$address->id_customer = $customer->id;
		$address->alias = 'Ecster address';
		$address->add();

	}

	public function postProcess()
	{
		try {
			$internalReference = Tools::getValue('internalReference');
			Logger::addLog('Callback invoked with internalReference : '.$internalReference);
			if ((int)Configuration::get('ECSTER_MODE') == 1) {
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

			$ecster_order = new EcsterOrder($connector, $internalReference);

			$callback = $ecster_order->fetch()->getResponse();

			if ($callback['order']['status'] == "ready") {
				//check if the order already exists
				
				$cart = new Cart((int)$callback['order']['externalReference']);

				if ($cart->OrderExists()) {
					header('HTTP/1.1 200 OK', true, 200);
					die;
				}

				$ecster_customer = $callback['order']['customer'];
                                $id_customer = (int)Customer::customerExists($ecster_customer['email'], true, false);
				if ($id_customer > 0) {
                                    $customer = new Customer($id_customer
                                    );
				}  else {
					//Create customer
					
					$names = explode(',', $ecster_customer['name']); 

					$customer = new Customer();
					$customer->firstname = trim($names[1]);
					$customer->lastname = trim($names[0]);
					$customer->email = $ecster_customer['email'];
					$customer->passwd = Tools::passwdGen(8, 'ALPHANUMERIC');
					$customer->is_guest = 1;
					$customer->id_default_group = (int)Configuration::get('PS_GUEST_GROUP', null, $cart->id_shop);
					$customer->newsletter = 0;
					$customer->optin = 0;
					$customer->active = 1;
					$customer->id_gender = 0;


					$customer->add();
				}

				foreach ($customer->getAddresses($cart->id_lang) as $address) {
					# code...
				}

				if ($invoice_address_id == 0) {
					//create
					$this->createAddress();
				}

				if ($delivery_address_id == 0) {
					$this->createAddress();
				}
				//validate order
				$this->module->validateOrder(
					$cart->id,
					Configuration::get('PS_OS_PAYMENT'),
					$amount,
					$this->module->displayName,
					$ecster_order['internalReference'],
					array(
						'transaction_id' => $ecster_order['internalReference']
					),
					null,
					false,
					$customer->secure_key
				);

				header('HTTP/1.1 200 OK', true, 200);
			}
			


		} catch (Exception $e) {
			Logger::addLog('Ecster checkout callback error message: '.$e->getMessage().' and error code : '.$e->getCode());
		}
	}
}
