<?php

class MjvpWarehouse extends ObjectModel
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
        'primary' => 'id',
        'fields' => array(
            'warehouse_name' =>      array('type' => self::TYPE_STRING, 'required' => true, 'size' => 60, 'validate' => 'isGenericName'),
            'company_code' =>        array('type' => self::TYPE_STRING, 'required' => true, 'size' => 16),
            'contact' =>             array('type' => self::TYPE_STRING, 'required' => true, 'size' => 40, 'validate' => 'isName'),
            'country_code' =>        array('type' => self::TYPE_STRING, 'required' => true, 'size' => 2, 'validate' => 'isLangIsoCode'),
            'city' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 40, 'validate' => 'isCityName'),
            'address' =>             array('type' => self::TYPE_STRING, 'required' => true, 'size' => 50, 'validate' => 'isAddress'),
            'zip_code' =>            array('type' => self::TYPE_STRING, 'required' => true, 'size' => 6, 'validate' => 'isZipCodeFormat'),
            'phone' =>               array('type' => self::TYPE_BOOL, 'required' => true, 'size' => 30, 'validate' => 'isPhoneNumber'),
            'default_on' =>             array('type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'),
        ),
    );

}
