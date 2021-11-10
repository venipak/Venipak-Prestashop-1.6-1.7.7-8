<?php

require_once "MjvpBase.php";

class MjvpApi extends MjvpBase
{

    private $_liveCurlUrl;
    private $_curlUrl;

    public function __construct()
    {
        parent::__construct();
        $this->_liveCurlUrl = Configuration::get('MJVP_LIVE_API_SERVER');
        $this->_curlUrl = Configuration::get('MJVP_TEST_API_SERVER');
    }

    /**
     * Get terminals for country
     */
    public function getTerminals($country_code, $postcode = '', $city = '', $show_for_sender = false)
    {
        try {
            $queryParams['country'] = $country_code;
            if (!empty($postcode)) {
                preg_match('/\d+/', $postcode, $postcode_numbers);
                $queryParams['postcode'] = $postcode_numbers[0];
            }
            if (!empty($city)) {
                $queryParams['city'] = $city;
            }
            if ($show_for_sender) {
                $queryParams['pickup_enabled'] = 1;
            }

            return $this->executeRequest('ws/get_pickup_points', 'GET', array('queryParams' => $queryParams));
        } catch (Exception $e) {
            throw new Exception('Failed to get terminals. Error: ' . $e->getMessage());
        }
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

        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');
        $api_id = Configuration::get($cModuleConfig->getConfigKey('id', 'API'));

        if (empty($api_id)) {
            throw new Exception(sprintf('%s value is empty','API ID'));
        }

        if (!isset($params['shipments'])) {
            throw new Exception('Empty shipments list');
        }

        $params['desc_type'] = (isset($params['desc_type'])) ? $params['desc_type'] : 1;
        $params['manifest_name'] = (isset($params['manifest_name'])) ? $params['manifest_name'] : '';

        $xml_code = '<manifest title="' . $params['manifest_title'] . '" name="' . $params['manifest_name'] . '">';
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

        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');

        $api_id = Configuration::get($cModuleConfig->getConfigKey('id', 'API'));

        if (empty($api_id)) {
            throw new Exception(sprintf('%s value is empty','API ID'));
        }

        $xml_code = $this->buildInvitationXml($params);

        return $this->buildXml($xml_code, $params['desc_type']);
    }

    public function buildManifestNumber($login_id, $serial_number)
    {
        return $login_id . date('ymd') . sprintf('%03d', (int)$serial_number);
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
     * Build consignor XML structure
     */
    public function buildCustomAddress($type)
    {
        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');
        $xml_code = "<{$type}>";
        $xml_code .= '<name>' . Configuration::get($cModuleConfig->getConfigKey('sender_name', 'SHOP')) . '</name>';
        $xml_code .= '<company_code>' . Configuration::get($cModuleConfig->getConfigKey('company_code', 'SHOP'))  . '</company_code>';
        $xml_code .= '<country>' . Configuration::get($cModuleConfig->getConfigKey('shop_country_code', 'SHOP'))  . '</country>';
        $xml_code .= '<city>' . Configuration::get($cModuleConfig->getConfigKey('shop_city', 'SHOP')) . '</city>';
        $xml_code .= '<address>' . Configuration::get($cModuleConfig->getConfigKey('shop_address', 'SHOP'))  . '</address>';
        $xml_code .= '<post_code>' . Configuration::get($cModuleConfig->getConfigKey('shop_postcode', 'SHOP'))  . '</post_code>';
        $xml_code .= '<contact_person>' . Configuration::get($cModuleConfig->getConfigKey('shop_name', 'SHOP'))  . '</contact_person>';
        $xml_code .= '<contact_tel>' . Configuration::get($cModuleConfig->getConfigKey('shop_phone', 'SHOP')) . '</contact_tel>';
        $xml_code .= '<contact_email>' . Configuration::get($cModuleConfig->getConfigKey('shop_email', 'SHOP'))  . '</contact_email>';
        $xml_code .= "</{$type}>";
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
        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');
        if(Configuration::get($cModuleConfig->getConfigKey('sender_address', 'SHOP')))
        {
            $xml_code .= $this->buildCustomAddress('consignor');
        }
        if((isset($params['consignee']['return_service']) && $params['consignee']['return_service']) || (!isset($params['consignee']['return_service']) && Configuration::get($cModuleConfig->getConfigKey('return_service', 'COURIER'))))
        {
            $xml_code .= $this->buildCustomAddress('return_consignee');
        }
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
        if((isset($params['consignee']['return_service']) && $params['consignee']['return_service']) || (!isset($params['consignee']['return_service']) && Configuration::get($cModuleConfig->getConfigKey('return_service', 'COURIER'))))
            $xml_code .= '<return_service>' . (int) Configuration::get($cModuleConfig->getConfigKey('return_days', 'COURIER')) . '</return_service>';
        $xml_code .= '</attribute>';

        foreach ($params['packs'] as $pack) {
            $xml_code .= '<pack>';
            $xml_code .= '<pack_no>' . $this->buildTrackingNumber($params['api_id'], $pack['serial_number']) . '</pack_no>';
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

    public function buildTrackingNumber($login_id, $serial_number)
    {
        return 'V' . $login_id . 'E' . sprintf('%07d', (int)$serial_number);
    }

    /**
     * Send XML to API
     */
    public function sendXml($xml)
    {

        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');

        $username = Configuration::get($cModuleConfig->getConfigKey('username', 'API'));
        $password = Configuration::get($cModuleConfig->getConfigKey('password', 'API'));

        if (!$this->validateXml($xml)) {
            throw new Exception('Bad XML text.');
            return false;
        }
        $params = [
            'user' => $username,
            'pass' => $password,
            'xml_text' => $xml,
        ];
        $response = $this->executeRequest('import/send.php', 'POST', $params);

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

    public function printManifest($manifest_number)
    {
        $this->getPdfEntity('manifest', $manifest_number);
    }

    public function getLabelLink($labels_numbers)
    {
        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');
        $username = Configuration::get($cModuleConfig->getConfigKey('username', 'API'));
        $password = Configuration::get($cModuleConfig->getConfigKey('password', 'API'));
        $params = array(
            'user' => $username,
            'pass' => $password,
        );

        if (is_array($labels_numbers) && !empty($labels_numbers)) {
            foreach ($labels_numbers as $key => $package)
            {
                $params['pack_no[' . $key . ']'] = $package;
            }
        }

         return $this->executeRequest('ws/print_link', 'POST', $params);
    }

    public function printLabel($label_number)
    {
        $this->getPdfEntity('label', $label_number);
    }

    private function getPdfEntity($type, $label_numbers)
    {

        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');

        $username = Configuration::get($cModuleConfig->getConfigKey('username', 'API'));
        $password = Configuration::get($cModuleConfig->getConfigKey('password', 'API'));

        $pdf_path = '';
        switch ($type) {
            case 'label':
                $pdf_path = MijoraVenipak::$_labelPdfDir;
                $apiFunction = 'getLabel';
                $params = [
                    'packages' => $label_numbers,
                    'format' => Configuration::get($cModuleConfig->getConfigKeyOther('label_size'))
                ];
                break;
            case 'manifest':
                $pdf_path = MijoraVenipak::$_manifestPdfDir;
                $apiFunction = 'getManifest';
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
            $pdf = $this->$apiFunction($username, $password, $params);
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

    public function getTrackingShipment($tracking_code, $tracking_type = 'track_single')
    {
        $types = array(
            'track_single' => 1,
            'track_all' => 2,
            'track_last_status' => 5,
            'track_shipment' => 7,
        );
        if (!isset($types[$tracking_type])) {
            $tracking_type = 'track_single';
        }

        $params = array(
            'queryParams' => array(
                'code' => $tracking_code,
                'type' => $types[$tracking_type],
                'output' => 'csv',
            ),
            'use_live_endpoint' => true,
        );

        return $this->executeRequest('ws/tracking', 'GET', $params);
    }

    public function setTrackingShipment($username, $password, $package_code, $package_type)
    {
        $types = array(
            'doc_code' => 3,
            'bill' => 4,
            'order' => 13,
        );
        if (!isset($types[$package_type])) {
            $package_type = 'doc_code';
        }

        $params = array(
            'user' => $username,
            'pass' => $password,
            'code' => $package_code,
            'type' => $types[$package_type],
        );

        return $this->executeRequest('ws/tracking', 'POST', $params);
    }

    public function getLabel($username, $password, $data = array())
    {
        if (!isset($data['packages'])) {
            throw new Exception('Not received package codes.');
            return false;
        }

        $params = array(
            'user' => $username,
            'pass' => $password,
            'format' => $this->getParamValue($data, 'format', array('a4', 'other'), 'other'),
            'carrier' => $this->getParamValue($data, 'carrier', array('venipak', 'global', 'all'), 'all'),
        );

        if (isset($data['packages'])) {
            foreach ($data['packages'] as $key => $package)
            {
                $params['pack_no[' . $key . ']'] = $package;
            }
        }

        return $this->executeRequest('ws/print_label', 'POST', $params);
    }

    public function getManifest($username, $password, $manifest_id)
    {
        $params = array(
            'user' => $username,
            'pass' => $password,
            'code' => $manifest_id,
        );

        return $this->executeRequest('ws/print_list', 'POST', $params);
    }

    public function getServices($country, $postcode, $params = array())
    {
        if (empty($country) || empty($postcode)) {
            return 'Error: Country or postcode value is empty.';
        }

        $queryParams = array(
            'country' => $country,
            'code' => $postcode,
            'type' => $this->getParamValue($params, 'type', array('route', 'zone', 'all'), 'all'),
            'view' => $this->getParamValue($params, 'view', array('csv', 'json'), 'json'),
        );

        return $this->executeRequest('ws/get_route', 'GET', array('queryParams' => $queryParams));
    }

    private function getParamValue($params, $param_name, $allowed_values, $default_value)
    {
        if (!isset($params[$param_name])) {
            return $default_value;
        }
        if (!is_string($params[$param_name])) {
            return $default_value;
        }
        if (!in_array($params[$param_name], $allowed_values)) {
            return $default_value;
        }

        return $params[$param_name];
    }

    private function validateXml($xml_text, $version = '1.0', $encoding = 'utf-8')
    {
        if (empty(trim($xml_text))) {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new DOMDocument($version, $encoding);
        $doc->loadXML($xml_text);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }

    private function executeRequest($url_suffix, $request_type, $params = array())
    {
        if (empty($url_suffix) || empty($request_type)) {
            throw new Exception('URL suffix or request type is empty.');
            return false;
        }

        $url_query = '';
        if (isset($params['queryParams'])) {
            if (is_array($params['queryParams'])) {
                foreach ($params['queryParams'] as $qParam_key => $qParam_value) {
                    if (empty($url_query)) {
                        $url_query .= '?';
                    } else {
                        $url_query .= '&';
                    }
                    $url_query .= $qParam_key . '=' . $qParam_value;
                }
            }
            unset($params['queryParams']);
        }

        $curl = curl_init();

        $cModuleConfig = $this->module->getModuleService('MjvpModuleConfig');
        $endpoint = (isset($params['use_live_endpoint']) && $params['use_live_endpoint']) || Configuration::get($cModuleConfig->getConfigKey('live_mode', 'API')) ? $this->_liveCurlUrl : $this->_curlUrl;
        $reference_header = "Reference: Prestashop " . _PS_VERSION_;
        $headers = [$reference_header];
        $curl_options = array(
            CURLOPT_URL => $endpoint . $url_suffix . $url_query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request_type,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => $headers,
        );

        curl_setopt_array($curl, $curl_options);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return curl_error($curl);
        }

        curl_close($curl);

        return $this->isJson($response) ? json_decode($response) : $response;
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
