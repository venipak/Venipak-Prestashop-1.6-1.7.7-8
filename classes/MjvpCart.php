<?php

class MjvpCart extends ObjectModel
{
    public $id;

    public $id_cart;

    public $door_code;

    public $cabinet_number;

    public $warehouse_number;

    public $delivery_time;

    public $date_add;

    public $date_upd;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mjvp_cart',
        'primary' => 'id',
        'fields' => array(
            'id_cart' =>             array('type' => self::TYPE_INT, 'required' => true, 'size' => 10),
            'door_code' =>           array('type' => self::TYPE_STRING, 'required' => false, 'size' => 10),
            'cabinet_number' =>      array('type' => self::TYPE_STRING, 'required' => false, 'size' => 10),
            'warehouse_number' =>    array('type' => self::TYPE_STRING, 'required' => false, 'size' => 10),
            'delivery_time' =>       array('type' => self::TYPE_INT, 'required' => false, 'size' => 1, 'validate' => 'isInt'),
            'date_add' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

}
