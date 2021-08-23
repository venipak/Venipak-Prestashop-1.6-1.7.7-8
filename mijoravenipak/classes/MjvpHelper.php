<?php

class MjvpHelper
{
    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Get country id in API by country code
     */
    public function getCountryId($country_code, $on_error_return_code = false)
    {
        $country_code = strtoupper($country_code);

        return ($on_error_return_code) ? $country_code : false;
    }

    /**
     * Get country code by country id in API
     */
    public function getCountryCodeFromId($country_id)
    {

        return false;
    }

    /**
     * Get all countries list from file/API
     */
    public function getAllCountries()
    {

        return MijoraVenipak::$_defaultPickupCountries;
    }

    /**
     * Get terminals for specific country from file/API
     */
    public function getTerminalsForCountry($country_code)
    {

        return false;
    }

    /**
     * Get service information from API by service code
     */
    public function getServiceInfo($service_code)
    {

        return false;
    }

    /**
     * Get list of all Prestashop carriers
     */
    public function getAllCarriers($id_only = false)
    {
        $carriers = Carrier::getCarriers(
            Context::getContext()->language->id,
            true,
            false,
            false,
            NULL,
            PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
        );
        if ($id_only) {
            $id_list = array();
            foreach ($carriers as $carrier)
                $id_list[] = $carrier['id_carrier'];
            return $id_list;
        }

        return $carriers;
    }

    /**
     * Check if Prestashop carrier belongs to this module
     */
    public function itIsThisModuleCarrier($carrier_reference)
    {
        foreach (MijoraVenipak::$_carriers as $carrier) {
            if (Configuration::get($carrier['reference_name']) == $carrier_reference) {
                if (isset($carrier['type'])) {
                    return $carrier['type'];
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Change order status
     */
    public function changeOrderState($id_order, $state_key)
    {

        return true;
    }

    /**
     * Create new order status
     */
    public function createOrderState($state_key)
    {

        return false;
    }

    /**
     * Check if XML content is valid
     */
    public function isXMLContentValid($xmlContent, $version = '1.0', $encoding = 'utf-8')
    {
        if (trim($xmlContent) == '') {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new DOMDocument($version, $encoding);
        $doc->loadXML($xmlContent);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }

    /**
     * Write log files
     */
    public function writeToLog($message, $file_name = 'errors')
    {
        $logger = new FileLogger(0);
        $logger->setFilename(MijoraVenipak::$_moduleDir . "logs/" . $file_name . '.log');
        $logger->logDebug(print_r($message,true));
    }

    /**
     * Write debug log
     */
    public function onDebug($file, $function, $text, $debug_file_name = 'debug')
    {
        if (MijoraVenipak::$debug_mode) {
            $this->writeToLog("*** Function " . $function . "() on " . basename($file) . " ***\n" . $text, $debug_file_name);
        }
    }

    /**
     * Split arrival date to: year, month, day, hour, minutes.
     */
    public function parseDate($date)
    {
        return date_parse_from_format('Y-m-d h:i:s', $date);
    }
}
