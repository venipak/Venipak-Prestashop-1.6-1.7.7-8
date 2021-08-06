<?php

if (!defined('_PS_VERSION_')) {
    return;
}

include MijoraVenipak::$_moduleDir . 'classes/MjvpVenipak.php';

class MjvpApi
{
    private $cVenipak;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->cVenipak = new MjvpVenipak();
    }

    /**
     * Get token for API
     */
    public function getToken()
    {
        /*MijoraVenipak::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $token = Configuration::get($cModuleConfig->getConfigKey('MJVP_API_TOKEN', 'API'));
        if (empty($token)) {
            throw new Exception('Failed to get token.');
            return false;
        }
        return $token;*/
        return '';
    }

    /**
     * Get list of countries
     */
    public function getAllCountries($return_objects = true)
    {

        return false;
    }

    /**
     * Get list of services
     */
    public function getAllServices()
    {

        return false;
    }

    /**
     * Get terminals for country
     */
    public function getTerminals($country_code, $postcode = '', $city = '', $show_for_sender = false)
    {
        try {
            $params = array('country' => $country_code);
            
            if (!empty($postcode)) {
                preg_match('/\d+/', $postcode, $postcode_numbers);
                $params['postcode'] = $postcode_numbers[0];
            }
            if (!empty($city)) {
                $params['city'] = $city;
            }
            if ($show_for_sender) {
                $params['pickup_enabled'] = 1;
            }

            return $this->cVenipak->getPickupPoints($params);
        } catch (Exception $e) {
            throw new Exception('Failed to get terminals. Error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Get list of departments
     */
    public function getDepartmentsList()
    {

        return false;
    }

    /**
     * Set values for sender
     */
    public function setSender($custom_values = array())
    {
        if (!is_array($custom_values)) {
            $custom_values = array();
        }

        return false;
    }

    /**
     * Set values for receiver
     */
    public function setReceiver($values)
    {
        if (!is_array($values)) {
            throw new Exception('Failed to set receiver. An array of values not received.');
            return false;
        }

        return false;
    }

    /**
     * Set values for parcel
     */
    public function setParcel($values)
    {
        if (!is_array($values)) {
            throw new Exception('Failed to set parcel. An array of values not received.');
            return false;
        }

        return false;
    }

    /**
     * Set values for item
     */
    public function setItem($values)
    {
        if (!is_array($values)) {
            throw new Exception('Failed to set item. An array of values not received.');
            return false;
        }

        return false;
    }

    /**
     * Set all objects for order
     */
    public function setOrder($order_objects)
    {

        return false;
    }

    /**
     * Generate order
     */
    public function generateOrder($order)
    {

        return false;
    }

    /**
     * Get all orders
     */
    public function getOrdersList()
    {

        return false;
    }

    public function buildXml($xml_content, $desc_type = 1)
    {
        $xml_code = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml_code .= '<description type="' . $desc_type . '">';
        $xml_code .= $xml_content;
        $xml_code .= '</description>';

        return $xml_code;
    }

    public function buildManifestXml($params)
    {
        MijoraVenipak::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $api_id = Configuration::get($cModuleConfig->getConfigKey('id', 'API'));

        if (empty($api_id)) {
            throw new Exception(sprintf('%s value is empty','API ID'));
        }

        if (!isset($params['shipments'])) {
            throw new Exception('Empty shipments list');
        }

        $params['desc_type'] = (isset($params['desc_type'])) ? $params['desc_type'] : 1;
        $params['order_code'] = (isset($params['order_code'])) ? $params['order_code'] : '???';
        $params['order_id'] = (isset($params['order_id'])) ? $params['order_id'] : 1;
        $params['manifest_id'] = (isset($params['manifest_id'])) ? $params['manifest_id'] : $params['order_id'];
        $manifest_title = $api_id . date('ymd') . str_pad($params['manifest_id'], 3, '0', STR_PAD_LEFT);

        $xml_code = '<manifest title="' . $manifest_title . '" name="PS-' . $params['order_code'] . '">';
        foreach ($params['shipments'] as $shipment) {
            $xml_code .= $this->buildShipmentXml($params['shipments']);
        }
        $xml_code .= '</manifest>';

        return $this->buildXml($xml_code, $params['desc_type']);
    }

    public function buildShipmentXml($params)
    {
        $params['name'] = (isset($params['name'])) ? $params['name'] : '';
        $params['lastname'] = (isset($params['lastname'])) ? $params['lastname'] : '';
        $params['country_code'] = (isset($params['country_code'])) ? $params['country_code'] : '';
        $params['city'] = (isset($params['city'])) ? $params['city'] : '';
        $params['address'] = (isset($params['address'])) ? $params['address'] : '';
        $params['postcode'] = (isset($params['postcode'])) ? $params['postcode'] : '';
        $params['phone'] = (isset($params['phone'])) ? $params['phone'] : '';

        $xml_code = '<shipment>';
        $xml_code .= '<consignee>';
        $xml_code .= '<name>' . $params['name'] . ' ' . $params['lastname'] . '</name>';
        $xml_code .= '<country>' . $params['country_code'] . '</country>';
        $xml_code .= '<city>' . $params['city'] . '</city>';
        $xml_code .= '</consignee>';
        $xml_code .= '</shipment>';

        return $xml_code;
    }

    public function buildShipmentRegisterXml($params)
    {

    }
}
