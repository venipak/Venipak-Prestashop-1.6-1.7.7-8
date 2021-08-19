<?php

class AdminVenipakshippingAjaxController extends ModuleAdminController
{
    public function __construct()
    {
        if (!Context::getContext()->employee->isLoggedBack()) {
            exit('Restricted.');
        }

        parent::__construct();
        $this->parseActions();
    }

    private function parseActions()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'saveOrder':
                $this->saveCart();
                break;
            case 'printLabel':
                $this->printLabel();
                break;
            case 'generateLabel':
                $this->generateLabel();
                break;
        }
    }


    protected function saveCart()
    {
        MijoraVenipak::checkForClass('MjvpDb');
        $cDb = new MjvpDb();
        $selected_carrier_reference = (int) Tools::getValue('is_pickup');
        $id_order = Tools::getValue('id_order');
        if(Configuration::get(MijoraVenipak::$_carriers['pickup']['reference_name']) == $selected_carrier_reference)
        {
            // Check if terminal was selected
            $terminal_id = (int) Tools::getValue('id_pickup_point');
            if(!$terminal_id)
            {
                $result['errors'][] = $this->module->l('Please select a terminal.');
            }
            else
            {
                $data = [
                    'packages' => Tools::getValue('packs', 1),
                    'order_weight' => Tools::getValue('weight', 0),
                    'is_cod' => Tools::getValue('is_cod', 0),
                    'id_carrier_ref' => Tools::getValue('is_pickup', Configuration::get(MijoraVenipak::$_carriers['pickup']['reference_name'])),
                    'terminal_id' => Tools::getValue('id_pickup_point'),
                ];

                // If cod is disabled, cod ammount will not be submitted at all.
                if(Tools::isSubmit('cod_amount'))
                {
                    $data['cod_amount'] = Tools::getValue('cod_amount', 0);
                }

                $res = $cDb->updateOrderInfo($id_order, $data, 'id_order');
                if($res)
                {
                    $result['success'][] = $this->module->l('Shipment data updated successfully.');
                }
                else
                {
                    $result['errors'][] = $this->module->l('Failed to updated shipment data data.');
                }

            }
        }
        elseif (Configuration::get(MijoraVenipak::$_carriers['courier']['reference_name']) == $selected_carrier_reference)
        {
            $data = [
                'packages' => Tools::getValue('packs', 1),
                'order_weight' => Tools::getValue('weight', 0),
                'is_cod' => Tools::getValue('is_cod', 0),
                'id_carrier_ref' => Tools::getValue('is_pickup', Configuration::get(MijoraVenipak::$_carriers['courier']['reference_name'])),
                'terminal_id' => 0,
            ];
            if(Tools::isSubmit('cod_amount'))
            {
                $data['cod_amount'] = Tools::getValue('cod_amount', 0);
            }
            // Validate extra fields
            $extra_fields = Tools::getValue('venipak_extra');
            if($extra_fields && is_array($extra_fields))
            {
                $field_door_code = isset($extra_fields['door_code']) ? $extra_fields['door_code'] : '';
                $field_cabinet_number = isset($extra_fields['cabinet_number']) ? $extra_fields['cabinet_number'] : '';
                $field_warehouse_number = isset($extra_fields['warehouse_number']) ? $extra_fields['warehouse_number'] : '';
                $field_delivery_time = isset($extra_fields['delivery_time']) ? $extra_fields['delivery_time'] : 'nwd';
                if(strlen($field_door_code) > MijoraVenipak::EXTRA_FIELDS_SIZE)
                    $result['errors'][] = $this->module->l('The door code is too long.');
                if(strlen($field_cabinet_number) > MijoraVenipak::EXTRA_FIELDS_SIZE)
                    $result['errors'][] = $this->module->l('The cabinet number is too long.');
                if(strlen($field_warehouse_number) > MijoraVenipak::EXTRA_FIELDS_SIZE)
                    $result['errors'][] = $this->module->l('The warehouse number is too long.');
                if(!isset($this->module->deliveryTimes[$field_delivery_time]))
                    $result['errors'][] = $this->module->l('Selected delivery time does not exist.');

                $order_extra_info = [];
                $order_extra_info['door_code'] = $field_door_code;
                $order_extra_info['cabinet_number'] = $field_cabinet_number;
                $order_extra_info['warehouse_number'] = $field_warehouse_number;
                $order_extra_info['delivery_time'] = $field_delivery_time;
                $data['other_info'] = json_encode($order_extra_info);
            }

            if (!isset($result['errors'])) {
                $res = $cDb->updateOrderInfo($id_order, $data, 'id_order');
                if ($res) {
                    $result['success'][] = $this->module->l('Shipment data updated successfully.');
                } else {
                    $result['errors'][] = $this->module->l('Failed to updated shipment data data.');
                }
            }
        }

        // Update order carrier.
        if (!isset($result['errors'])) {
            $selected_carrier = Carrier::getCarrierByReference($selected_carrier_reference);
            $order = new Order($id_order);
            $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
            $changed = false;
            if ($selected_carrier->id != $order_carrier->id_carrier) {
                $order->id_carrier = $selected_carrier->id;
                $order_carrier->id_carrier = $selected_carrier->id;
                $changed = true;
            }
            if ($changed) {
                $order_carrier->update();
                $this->context->currency = isset($this->context->currency) ? $this->context->currency : new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                $order->refreshShippingCost();
                $order->update();
            }
        }

        die(json_encode($result));
    }

    public function generateLabel()
    {
        $order = (int) Tools::getValue('id_order');
        $this->module->bulkActionSendLabels((array) $order);
        if(!empty($this->context->controller->confirmations))
            $result['success'] = $this->context->controller->confirmations;
        if(!empty($this->context->controller->errors))
            $result['errors'] = $this->context->controller->errors;
        die(json_encode($result));
    }

    public function printLabel()
    {
        MijoraVenipak::checkForClass('MjvpApi');
        $cApi = new MjvpApi();
        $label_number = Tools::getValue('label_number');
        $cApi->printLabel($label_number);
    }
}