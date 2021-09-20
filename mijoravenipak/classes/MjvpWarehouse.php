<?php

class MjvpWarehouse extends ObjectModel
{
    public $id;

    public $name;

    public $company_code;

    public $contact;

    public $country_code;

    public $city;

    public $address;

    public $zip_code;

    public $id_shop;

    public $phone;

    public $default_on;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mjvp_warehouse',
        'primary' => 'id',
        'fields' => array(
            'name' =>      array('type' => self::TYPE_STRING, 'required' => true, 'size' => 60, 'validate' => 'isGenericName'),
            'company_code' =>        array('type' => self::TYPE_STRING, 'required' => true, 'size' => 16),
            'contact' =>             array('type' => self::TYPE_STRING, 'required' => true, 'size' => 40, 'validate' => 'isName'),
            'country_code' =>        array('type' => self::TYPE_STRING, 'required' => true, 'size' => 2, 'validate' => 'isLangIsoCode'),
            'city' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 40, 'validate' => 'isCityName'),
            'address' =>             array('type' => self::TYPE_STRING, 'required' => true, 'size' => 50, 'validate' => 'isAddress'),
            'zip_code' =>            array('type' => self::TYPE_STRING, 'required' => true, 'size' => 6, 'validate' => 'isZipCodeFormat'),
            'id_shop' =>             array('type' => self::TYPE_STRING, 'validate' => 'isInt', 'required' => true),
            'phone' =>               array('type' => self::TYPE_BOOL,   'required' => true, 'size' => 30, 'validate' => 'isPhoneNumber'),
            'default_on' =>          array('type' => self::TYPE_BOOL,   'required' => true, 'validate' => 'isBool'),
        ),
    );

    public static function getWarehouses()
    {
        return Db::getInstance()->executeS(
            'SELECT *
                  FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                  WHERE id_shop = ' . Context::getContext()->shop->id
        );
    }

    public function add($auto_date = true, $null_values = false)
    {
        $this->id_shop = Context::getContext()->shop->id;
        return parent::add($auto_date, $null_values);
    }

    public static function getDefaultWarehouse()
    {
        return Db::getInstance()->getValue('SELECT `id`
                  FROM `' . _DB_PREFIX_ . pSQL(self::$definition['table']) . '`
                  WHERE default_on = 1');
    }

}
