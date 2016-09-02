<?php

class EcsterOrderModel extends ObjectModel {

	public $id_ecster_order;

	public $id_cart_key;

	public static $definition = array(
		'table' => 'ecster_order',
		'primary' => 'id_ecster_order',
		'multilang' => false,
		'fields' => array(
			'id_ecster_order' => array('type' => self::TYPE_INT, 'required' => true, 'size' => 10),
			'id_cart_key' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255)
		),
	);

	public function __construct($id_ecster_order)
    {
        parent::__construct($id_ecster_order);
    }

    public function cartExists() {
    	$exists = DB::getInstance()->getValue(
            'SELECT `id_cart_key` FROM `'._DB_PREFIX_.'ecster_order` WHERE `id_ecster_order`
            = '.(int)$cartId
        );
        return $exists;
    }

    public static function storeCartKey($cartId, $cartKey) 
    {
        Db::getInstance()->Execute('
            INSERT INTO `'._DB_PREFIX_.'ecster_order` (`id_ecster_order`, `id_cart_key`) VALUES ('.(int)$cartId.', \''.pSQL($cartKey).'\')
        ');
    }

    public static function getCartKey($cartId)
    {
        $cartKey = DB::getInstance()->getValue(
            'SELECT `id_cart_key` FROM `'._DB_PREFIX_.'ecster_order` WHERE `id_ecster_order`
            = '.(int)$cartId
        );
        return $cartKey;
    }
}