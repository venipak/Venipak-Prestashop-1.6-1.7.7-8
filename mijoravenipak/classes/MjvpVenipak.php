<?php

namespace MijoraVenipak;

use DOMDocument, Exception;

if (!defined('_PS_VERSION_')) {
    return;
}

class MjvpVenipak
{
    //private $_curlUrl = 'https://go.venipak.lt/';
    private $_curlUrl = 'https://venipak.uat.megodata.com/'; //DEMO

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    public function getTrackingShipment($tracking_code, $tracking_type = 'track_single')
    {
        $types = array(
            'track_single' => 1,
            'track_all' => 2,
            'track_last' => 5,
            'track_shipment' => 7,
        );
        if (!isset($types[$tracking_type])) {
            $tracking_type = 'track_single';
        }

        $params = array(
            'queryParams' => array(
                'code' => $tracking_code,
                'type' => $types[$tracking_type],
                'output' => 'html',
            ),
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

    public function printLabel($username, $password, $data = array())
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

    public function printList($username, $password, $manifest_id)
    {
        $params = array(
            'user' => $username,
            'pass' => $password,
            'code' => $manifest_id,
        );

        return $this->executeRequest('ws/print_list', 'POST', $params);
    }

    public function getLabelLink($username, $password, $packages)
    {
        $params = array(
            'user' => $username,
            'pass' => $password,
        );

        if (is_array($packages) && !empty($packages)) {
            foreach ($packages as $key => $package)
            {
                $params['pack_no[' . $key . ']'] = $package;
            }
        }

        return $this->executeRequest('ws/print_link', 'POST', $params);
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

    public function getPickupPoints($params = array())
    {
        $queryParams = array();

        if (isset($params['country'])) {
            $queryParams['country'] = $params['country'];
        }
        if (isset($params['postcode'])) {
            $queryParams['zip'] = $params['postcode'];
        }
        if (isset($params['city'])) {
            $queryParams['city'] = $params['city'];
        }
        if (isset($params['pickup_enabled'])) {
            $queryParams['pick_up_enabled'] = 1;
        }

        return $this->executeRequest('ws/get_pickup_points', 'GET', array('queryParams' => $queryParams));
    }

    public function sendXml($username, $password, $xml_text)
    {
        if (!$this->validateXml($xml_text)) {
            throw new Exception('Bad XML text.');
            return false;
        }

        $params = array(
            'user' => $username,
            'pass' => $password,
            'xml_text' => $xml_text,
        );

        return $this->executeRequest('import/send.php', 'POST', $params);
    }

    public function buildTrackingNumber($login_id, $serial_number)
    {
        return 'V' . $login_id . 'E' . sprintf('%07d', (int)$serial_number);
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

        $curl_options = array(
            CURLOPT_URL => $this->_curlUrl . $url_suffix . $url_query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request_type,
            CURLOPT_POSTFIELDS => $params,
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