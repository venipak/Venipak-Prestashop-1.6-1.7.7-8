<?php

if (!defined('_PS_VERSION_')) {
    return;
}

use MijoraVenipak\Classes\MjvpDb;

class MijoraVenipakFrontModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if(Tools::isSubmit('carrier_id') || Tools::getValue('selected_terminal'))
        {
            $carrierId = Tools::getValue('carrier_id');
            if(!$carrierId)
                $carrierId = $this->context->cart->id_carrier;
            $selected_terminal = Tools::getValue('selected_terminal');
            $country_code = Tools::getValue('country_code');

            if(!$country_code)
            {
                $address = new Address($this->context->cart->id_address_delivery);
                $country_code = Country::getIsoById($address->id_country);
            }

            $ps_carrier = new Carrier((int)$carrierId);

            if (!Validate::isLoadedObject($ps_carrier)) {
                die(json_encode('FAILED TO GET CARRIER'));
            }

            $pickups_reference = Configuration::get(MijoraVenipak::$_carriers['pickup']['reference_name']);
            $courier_reference = Configuration::get(MijoraVenipak::$_carriers['courier']['reference_name']);

            $cDb = $this->module->getModuleService('MjvpDb');
            $sql_values = array(
                'country_code' => $country_code,
                'last_select' => date('Y-m-d H:i:s'),
                'status' => $cDb->order_status_new,
                'id_carrier_ref' => $ps_carrier->id_reference,
            );

            if(Tools::isSubmit('update-data-opc'))
            {
                $data = [
                    'step_name' => 'delivery',
                    'ajax' => 1,
                    'cart' => $this->context->cart
                ];
                $result = $this->module->hookActionValidateStepComplete($data);
                if(is_array($result) && isset($result['errors']))
                {
                    $this->context->smarty->assign('errors', $result['errors']);
                    die(json_encode(['errors' => $this->context->smarty->fetch(_PS_THEME_DIR_.'errors.tpl')]));
                }
            }

            if ($ps_carrier->id_reference == $pickups_reference) {
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
                        'is_cod' => $terminal['cod_enabled']
                    ]);
                }

            }

            if ($ps_carrier->id_reference == $courier_reference) {
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
