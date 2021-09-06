<?php

if (!defined('_PS_VERSION_')) {
    return;
}

use MijoraVenipak\MjvpDb;

class MijoraVenipakFrontModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if(Tools::isSubmit('carrier_id') && Tools::isSubmit('selected_terminal') && Tools::isSubmit('country_code'))
        {
            $carrierId = Tools::getValue('carrier_id');
            $selected_terminal = Tools::getValue('selected_terminal');
            $country_code = Tools::getValue('country_code');

            $ps_carrier = new Carrier((int)$carrierId);

            if (!Validate::isLoadedObject($ps_carrier)) {
                die(json_encode('FAILED TO GET CARRIER'));
            }

            $pickups_references = array();
            $couriers_references = array();

            foreach (MijoraVenipak::$_carriers as $carrier) {
                if ($carrier['type'] == 'pickup') {
                    $pickups_references[] = Configuration::get($carrier['reference_name']);
                }
                if ($carrier['type'] == 'courier') {
                    $couriers_references[] = Configuration::get($carrier['reference_name']);
                }
            }

            $cDb = new MjvpDb();

            $sql_values = array(
                'country_code' => $country_code,
                'last_select' => date('Y-m-d H:i:s'),
                'status' => $cDb->order_status_new,
                'id_carrier_ref' => $ps_carrier->id_reference,
            );

            if (in_array($ps_carrier->id_reference, $pickups_references)) {
                if (empty($selected_terminal)) {
                    die(json_encode('TERMINAL NOT SELECTED'));
                }
                $terminal = Tools::getValue('terminal');
                $sql_values['terminal_id'] = $selected_terminal;
                if(is_array($terminal) && !empty($terminal))
                {
                    $sql_values['terminal_info'] = json_encode([
                        'name' => $terminal['name'],
                        'company_code' => $terminal['code'],
                        'country' => $terminal['country'],
                        'city' => $terminal['city'],
                        'address' => $terminal['address'],
                        'post_code' => $terminal['zip'],
                    ]);
                }

            }

            if (in_array($ps_carrier->id_reference, $couriers_references)) {
                $sql_values['terminal_id'] = NULL;
            }

            try {
                if (!$cDb->getOrderValue(1, array('id_cart' => $this->context->cart->id))) {
                    $sql_values['id_cart'] = $this->context->cart->id;
                    $result = $cDb->saveOrderInfo($sql_values);
                } else {
                    $result = $cDb->updateOrderInfo($this->context->cart->id, $sql_values);
                }
            } catch (Exception $e) {
                die(json_encode('FAILED TO SAVE VENIPAK ORDER'));
            }

            die(json_encode(array(
                'msg' => 'OK',
                'savedCarrier' => $ps_carrier->id,
                'result' => $result,
            )));
        }
        elseif (Tools::isSubmit('submitFilterTerminals'))
        {
            $filter_keys = Tools::getValue('filter_keys');
            $filtered_terminals = $this->module->getFilteredTerminals($filter_keys);
            die(json_encode(['mjvp_terminals' => $filtered_terminals]));
        }
    }
}
