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
     * Create main XML structure
     */
    public function buildXml($xml_content, $desc_type = 1)
    {
        $xml_code = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml_code .= '<description type="' . $desc_type . '">';
        $xml_code .= $xml_content;
        $xml_code .= '</description>';

        return $xml_code;
    }

    /**
     * Create XML structure for Manifest
     */
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
        $params['manifest_id'] = (isset($params['manifest_id'])) ? $params['manifest_id'] : '';
        $params['manifest_name'] = (isset($params['manifest_name'])) ? $params['manifest_name'] : '';
        $manifest_title = $this->cVenipak->buildManifestNumber($api_id, $params['manifest_id']);
        Configuration::updateValue($cModuleConfig->getConfigKeyOther('last_manifest_id'), $manifest_title);

        $xml_code = '<manifest title="' . $manifest_title . '" name="' . $params['manifest_name'] . '">';
        foreach ($params['shipments'] as $shipment) {
            $shipment['api_id'] = $api_id;
            $xml_code .= $this->buildShipmentXml($shipment);
        }
        $xml_code .= '</manifest>';

        return $this->buildXml($xml_code, $params['desc_type']);
    }

    /**
     * Create XML structure for Carrier invitation
     */
    public function buildCourierInvitationXml($params)
    {
        MijoraVenipak::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $api_id = Configuration::get($cModuleConfig->getConfigKey('id', 'API'));

        if (empty($api_id)) {
            throw new Exception(sprintf('%s value is empty','API ID'));
        }

        $xml_code = $this->buildInvitationXml($params);

        return $this->buildXml($xml_code, $params['desc_type']);
    }

    /**
     * Build sender XML structure
     */
    public function buildInvitationXml($params)
    {
        $params['desc_type'] = (isset($params['desc_type'])) ? $params['desc_type'] : 1;
        $params['sender']['name'] = (isset($params['sender']['name'])) ? $params['sender']['name'] : '';
        $params['sender']['company_code'] = (isset($params['sender']['company_code'])) ? $params['sender']['company_code'] : '';
        $params['sender']['country'] = (isset($params['sender']['country'])) ? $params['sender']['country'] : '';
        $params['sender']['city'] = (isset($params['sender']['city'])) ? $params['sender']['city'] : '';
        $params['sender']['address'] = (isset($params['sender']['address'])) ? $params['sender']['address'] : '';
        $params['sender']['post_code'] = (isset($params['sender']['post_code'])) ? $params['sender']['post_code'] : '';
        $params['sender']['contact_person'] = (isset($params['sender']['contact_person'])) ? $params['sender']['contact_person'] : '';
        $params['sender']['contact_tel'] = (isset($params['sender']['contact_tel'])) ? $params['sender']['contact_tel'] : '';
        $params['sender']['contact_email'] = (isset($params['sender']['contact_email'])) ? $params['sender']['contact_email'] : '';
        $params['weight'] = (isset($params['weight'])) ? $params['weight'] : 0;
        $params['volume'] = (isset($params['volume'])) ? $params['volume'] : 0;
        $params['date_y'] = (isset($params['date_y'])) ? $params['date_y'] : '';
        $params['date_m'] = (isset($params['date_m'])) ? $params['date_m'] : '';
        $params['date_d'] = (isset($params['date_d'])) ? $params['date_d'] : '';
        $params['date_d'] = (isset($params['date_d'])) ? $params['date_d'] : '';
        $params['hour_from'] = (isset($params['hour_from'])) ? $params['hour_from'] : '';
        $params['min_from'] = (isset($params['min_from'])) ? $params['min_from'] : '';
        $params['hour_to'] = (isset($params['hour_to'])) ? $params['hour_to'] : '';
        $params['comment'] = (isset($params['comment'])) ? $params['comment'] : '';

        $xml_code = '<sender>';
        $xml_code .= '<name>' . $params['sender']['name'] . '</name>';
        if (!empty($params['sender']['code'])) {
            $xml_code .= '<company_code>' . $params['sender']['code'] . '</company_code>';
        }
        $xml_code .= '<country>' . $params['sender']['country_code'] . '</country>';
        $xml_code .= '<city>' . $params['sender']['city'] . '</city>';
        $xml_code .= '<address>' . $params['sender']['address'] . '</address>';
        $xml_code .= '<post_code>' . $params['sender']['postcode'] . '</post_code>';
        $xml_code .= '<contact_person>' . $params['sender']['contact_person'] . '</contact_person>';
        $xml_code .= '<contact_tel>' . $params['sender']['contact_phone'] . '</contact_tel>';
        $xml_code .= '<contact_email>' . $params['sender']['contact_email'] . '</contact_email>';
        $xml_code .= '</sender>';
        $xml_code .= '<weight>' . $params['weight']. '</weight>';
        $xml_code .= '<volume>' . $params['volume']. '</volume>';
        $xml_code .= '<date_y>' . $params['date_y']. '</date_y>';
        $xml_code .= '<date_m>' . $params['date_m']. '</date_m>';
        $xml_code .= '<date_d>' . $params['date_d']. '</date_d>';
        $xml_code .= '<hour_from>' . $params['hour_from']. '</hour_from>';
        $xml_code .= '<min_from>' . $params['min_from']. '</min_from>';
        $xml_code .= '<hour_to>' . $params['hour_to']. '</hour_to>';
        $xml_code .= '<min_to>' . $params['min_to']. '</min_to>';
        if (isset($params['comment']))
            $xml_code .= '<comment>' . $params['comment']. '</comment>';
        return $xml_code;
    }

    /**
     * Build shipment XML structure
     */
    public function buildShipmentXml($params)
    {
        $params['api_id'] = (isset($params['api_id'])) ? $params['api_id'] : '';
        $params['sender']['name'] = (isset($params['consignee']['name'])) ? $params['consignee']['name'] : '';
        $params['consignee']['code'] = (isset($params['consignee']['code'])) ? $params['consignee']['code'] : '';
        $params['consignee']['country_code'] = (isset($params['consignee']['country_code'])) ? $params['consignee']['country_code'] : '';
        $params['consignee']['city'] = (isset($params['consignee']['city'])) ? $params['consignee']['city'] : '';
        $params['consignee']['address'] = (isset($params['consignee']['address'])) ? $params['consignee']['address'] : '';
        $params['consignee']['postcode'] = (isset($params['consignee']['postcode'])) ? $params['consignee']['postcode'] : '';
        $params['consignee']['person'] = (isset($params['consignee']['person'])) ? $params['consignee']['person'] : '';
        $params['consignee']['phone'] = (isset($params['consignee']['phone'])) ? $params['consignee']['phone'] : '';
        $params['consignee']['email'] = (isset($params['consignee']['email'])) ? $params['consignee']['email'] : '';
        $params['packs'] = (isset($params['packs'])) ? $params['packs'] : array();

        $xml_code = '<shipment>';
        $xml_code .= '<consignee>';
        $xml_code .= '<name>' . $params['consignee']['name'] . '</name>';
        if (!empty($params['consignee']['code'])) {
            $xml_code .= '<company_code>' . $params['consignee']['code'] . '</company_code>';
        }
        $xml_code .= '<country>' . $params['consignee']['country_code'] . '</country>';
        $xml_code .= '<city>' . $params['consignee']['city'] . '</city>';
        $xml_code .= '<address>' . $params['consignee']['address'] . '</address>';
        $xml_code .= '<post_code>' . $params['consignee']['postcode'] . '</post_code>';
        $xml_code .= '<contact_person>' . $params['consignee']['person'] . '</contact_person>';
        $xml_code .= '<contact_tel>' . $params['consignee']['phone'] . '</contact_tel>';
        $xml_code .= '<contact_email>' . $params['consignee']['email'] . '</contact_email>';
        $xml_code .= '</consignee>';

        $xml_code .= '<attribute>';
        if(isset($params['consignee']['delivery_time']))
            $xml_code .= '<delivery_type>' . (!$params['consignee']['delivery_time'] ? 'nwd' : $params['consignee']['delivery_time']) . '</delivery_type>';

        if(isset($params['consignee']['return_doc']))
            $xml_code .= '<return_doc>' . ($params['consignee']['return_doc'] ? 1 : 0) . '</return_doc>';

        // Extra params are always set. Just check if they are not empty (i.e to not send door code 0, etc.)
        if(isset($params['consignee']['door_code']) && $params['consignee']['door_code'])
            $xml_code .= '<comment_door_code>' . $params['consignee']['door_code'] . '</comment_door_code>';
        if(isset($params['consignee']['cabinet_number']) && $params['consignee']['cabinet_number'])
            $xml_code .= '<comment_office_no>' . $params['consignee']['cabinet_number'] . '</comment_office_no>';
        if(isset($params['consignee']['warehouse_number']) && $params['consignee']['warehouse_number'])
            $xml_code .= '<comment_warehous_no>' . $params['consignee']['warehouse_number'] . '</comment_warehous_no>';
        if(isset($params['consignee']['carrier_call']) && $params['consignee']['carrier_call'])
            $xml_code .= '<comment_call>' . $params['consignee']['carrier_call'] . '</comment_call>';
        if($params['consignee']['cod'])
            $xml_code .= '<cod>' . $params['consignee']['cod'] . '</cod>';
        if($params['consignee']['cod_type'])
            $xml_code .= '<cod_type>' . $params['consignee']['cod_type'] . '</cod_type>';
        $xml_code .= '</attribute>';

        foreach ($params['packs'] as $pack) {
            $xml_code .= '<pack>';
            $xml_code .= '<pack_no>' . $this->cVenipak->buildTrackingNumber($params['api_id'], $pack['serial_number']) . '</pack_no>';
            if (!empty($pack['document_number'])) {
                $xml_code .= '<doc_no>' . $pack['document_number'] . '</doc_no>';
            }
            $xml_code .= '<weight>' . $pack['weight'] . '</weight>';
            $xml_code .= '<volume>' . $pack['volume'] . '</volume>';
            $xml_code .= '</pack>';
        }
        $xml_code .= '</shipment>';

        return $xml_code;
    }

    /**
     * Send XML to API
     */
    public function sendXml($xml)
    {
        MijoraVenipak::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $username = Configuration::get($cModuleConfig->getConfigKey('username', 'API'));
        $password = Configuration::get($cModuleConfig->getConfigKey('password', 'API'));

        $response = $this->cVenipak->sendXml($username, $password, $xml);

        return $this->convertXmlToArray($response);
    }

    /**
     * Convert XML text to PHP array
     */
    private function convertXmlToArray($xml_text)
    {
        $xml = simplexml_load_string($xml_text, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;
    }

    public function printList($labels)
    {
        $this->getPdfEntity('manifest', $labels);
    }

    public function printLabel($label_number)
    {
        $this->getPdfEntity('label', $label_number);
    }

    private function getPdfEntity($type, $label_numbers)
    {
        MijoraVenipak::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $username = Configuration::get($cModuleConfig->getConfigKey('username', 'API'));
        $password = Configuration::get($cModuleConfig->getConfigKey('password', 'API'));

        $pdf_path = '';
        switch ($type) {
            case 'label':
                $pdf_path = MijoraVenipak::$_labelPdfDir;
                $apiFunction = 'printLabel';
                $params = ['packages' => $label_numbers];
                break;
            case 'manifest':
                $pdf_path = MijoraVenipak::$_manifestPdfDir;
                $apiFunction = 'printList';
                $params = $label_numbers;
                break;
            default:
                break;
        }

        if(is_array($label_numbers))
            $filename = md5(implode('&', $label_numbers) . $password);
        else
            $filename = md5($label_numbers . $password);
        $pdf = $this->getPdf($pdf_path, $filename);
        if(!$pdf)
            // todo: pass manifest ID instead, when getting list
            $pdf = $this->cVenipak->$apiFunction($username, $password, $params);
        if ($pdf) { // check if its not empty
            $path = $pdf_path . $filename . '.pdf';
            $is_saved = file_put_contents($path, $pdf);
            if (!$is_saved) { // make sure it was saved
                throw new Exception("Failed to save label pdf to: " . $path);
            }
            $this->printPdf($path, $filename);
        } else {
            throw new Exception("Downloaded label data is empty.");
        }
    }

    private function getPdf($pdf_path, $filename)
    {
        $pdf = false;
        if(!file_exists($pdf_path))
        {
            mkdir($pdf_path, 0755, true);
        }
        if(file_exists($pdf_path . $filename . 'pdf'))
        {
            $pdf = file_get_contents($pdf_path . $filename . 'pdf');
        }
        return $pdf;
    }

    private function printPdf($path, $filename)
    {
        // make sure there is nothing before headers
        if (ob_get_level()) ob_end_clean();
        header("Content-Type: application/pdf; name=\" " . $filename . ".pdf");
        header("Content-Transfer-Encoding: binary");
        // disable caching on client and proxies, if the download content vary
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        readfile($path);
    }

}
