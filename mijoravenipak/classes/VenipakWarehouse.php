<?php

class VenipakWarehouse extends ObjectModel
{
    public $id;

    public $warehouse_name;

    public $company_code;

    public $contact;

    public $country_code;

    public $city;

    public $address;

    public $zip_code;

    public $phone;

    public $default_on;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mjvp_warehouse',
        'primary' => 'id_warehouse',
        'fields' => array(
            'warehouse_name' =>      array('type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isGenericName'),
            'company_code' =>        array('type' => self::TYPE_STRING, 'required' => true, 'size' => 32, 'validate' => 'isInt'),
            'contact' =>             array('type' => self::TYPE_STRING, 'required' => true, 'size' => 32, 'validate' => 'isName'),
            'country_code' =>        array('type' => self::TYPE_STRING, 'required' => true, 'size' => 2, 'validate' => 'isLangIsoCode'),
            'city' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 50, 'validate' => 'isCityName'),
            'address' =>             array('type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isAddress'),
            'zip_code' =>            array('type' => self::TYPE_STRING, 'required' => true, 'size' => 6, 'validate' => 'isZipCodeFormat'),
            'phone' =>               array('type' => self::TYPE_BOOL, 'required' => true, 'size' => 15, 'validate' => 'isPhoneNumber'),
            'default_on' =>             array('type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'),
        ),
    );

}
