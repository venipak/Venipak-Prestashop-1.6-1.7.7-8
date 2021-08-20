<?php

class MjvpManifest extends ObjectModel
{
    public $id;

    public $manifest_id;

    public $id_shop;

    public $date_add;

    public $id_warehouse;

    public $shipment_weight;

    public $call_comment;

    public $carrier_arrival_date;

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
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'carrier_arrival_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public function getManifestItems()
    {
        $sql = '
      SELECT * FROM `' . _DB_PREFIX_ . 'venipak_cart` ic
      WHERE id_venipak_manifest = ' . $this->id_venipak_manifest;
        $result = DB::getInstance()->executeS($sql);

        $items = array();
        foreach ($result as $row) {
            $items[] = array(
                'tracking_number' => implode(' ', explode(',', $row['label_number'])),
                'amount' => $row['packs'],
                'weight' => $row['weight'],
                'delivery_address' => $this->generateDeliveryAddress($row)
            );
        }

        return $items;
    }


    public function toString()
    {
        return 'ID: ' . $this->id_venipak_manifest . ' | ID_SHOP: ' . $this->id_shop . ' | DATE: ' . $this->date_add;
    }

}
