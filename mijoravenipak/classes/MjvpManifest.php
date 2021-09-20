<?php

class MjvpManifest extends ObjectModel
{
    public $id;

    public $manifest_id;

    public $id_shop;

    public $id_warehouse;

    public $shipment_weight;

    public $call_comment;

    public $arrival_date_from;

    public $arrival_date_to;

    public $closed;

    public $date_add;

    /** @var array Class variables and their validation types */
    public static $definition = array(
        'primary' => 'id',
        'table' => 'mjvp_manifest',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'manifest_id' => array('type' => self::TYPE_STRING, 'size' => 40),
            'id_warehouse' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'shipment_weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'call_comment' => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isGenericName'),
            'arrival_date_from' => array('type' => self::TYPE_DATE, 'validate' => 'isDate',  'allow_null' => true),
            'arrival_date_to' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true),
            'closed' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

}
