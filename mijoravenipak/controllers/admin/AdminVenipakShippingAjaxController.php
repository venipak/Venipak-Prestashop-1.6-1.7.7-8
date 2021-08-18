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
                $id_order = Tools::getValue('id_order', NULL);
                $this->printLabel($id_order);
                break;
            case 'generateLabel':
                $id_order = Tools::getValue('id_order', NULL);
                try {
                    echo $this->generateLabel($id_order);
                } catch (\Exception $th) {
                    $error_msg = $th->getMessage();
                    ItellaShipping::checkForClass('ItellaCart');
                    $itellaCart = new ItellaCart();
                    $itellaCart->saveError($id_order, $error_msg);
                    echo json_encode(array('errors' => $error_msg));
                }
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
                $errors[] = $this->module->l('Please select a terminal.');
                if(!empty($errors))
                {
//                    $this->showErrors($errors);
//                    return;
                }
                else
                    $result = $cDb->updateOrderInfo($id_order, [
                        'order_weight' => Tools::getValue('weight', 0),
                        'id_carrier_reference' => Tools::getValue('is_pickup', Configuration::get(MijoraVenipak::$_carriers['courier']['reference_name'])),
                        'terminal_id' => Tools::getValue('id_pickup_point'),
                    ], 'id_order');
            }
        }
        elseif (Configuration::get(self::$_carriers['courier']['reference_name']) == $selected_carrier_reference)
        {
            // Validate extra fields
            $field_door_code = Tools::getValue('mjvp_door_code', 0);
            $field_cabinet_number = Tools::getValue('mjvp_cabinet_number', 0);
            $field_warehouse_number = Tools::getValue('mjvp_warehouse_number', 0);
            $field_delivery_time = Tools::getValue('mjvp_delivery_time', 'nwd');
            if(strlen($field_door_code) > MijoraVenipak::EXTRA_FIELDS_SIZE)
                $errors['mjvp_door_code'] = $this->module->l('The door code is too long.');
            if(strlen($field_cabinet_number) > MijoraVenipak::EXTRA_FIELDS_SIZE)
                $errors['mjvp_cabinet_number'] = $this->module->l('The cabinet number is too long.');
            if(strlen($field_warehouse_number) > MijoraVenipak::EXTRA_FIELDS_SIZE)
                $errors['mjvp_warehouse_number'] = $this->module->l('The warehouse number is too long.');
            if(!isset($this->deliveryTimes[$field_delivery_time]))
                $errors['mjvp_delivery_time'] = $this->module->l('Selected delivery time does not exist.');

            if(!empty($errors))
            {
//                $this->showErrors($errors);
            }

            $order_extra_info = MijoraVenipak::$_order_additional_info;

//            $sql_extra_info = $cDb->getOrderValue('other_info', array('id_order' => $cart->id));
//            $sql_extra_info = (array) json_decode($sql_extra_info);
//            foreach($sql_extra_info as $key => $value) {
//                $order_extra_info[$key] = $value;
//            }

            $order_extra_info['door_code'] = $field_door_code;
            $order_extra_info['cabinet_number'] = $field_cabinet_number;
            $order_extra_info['warehouse_number'] = $field_warehouse_number;
            $order_extra_info['delivery_time'] = $field_delivery_time;

            $cDb->updateOrderInfo($id_order, array('other_info' => json_encode($order_extra_info)), 'id_order');
        }

        // update order carrier
        if (!isset($result['errors'])) {
            // check that carrier hasnt changed
            $order = new Order((int) $result['id_order']);
            $order_carrier = new OrderCarrier((int) $order->getIdOrderCarrier());
            $changed = false;
            if ($result['data']['is_pickup'] && $order->id_carrier != Configuration::get('ITELLA_PICKUP_POINT_ID')) {
                $order->id_carrier = Configuration::get('ITELLA_PICKUP_POINT_ID');
                $order_carrier->id_carrier = Configuration::get('ITELLA_PICKUP_POINT_ID');
                $changed = true;
            } elseif (!$result['data']['is_pickup'] && $order->id_carrier != Configuration::get('ITELLA_COURIER_ID')) {
                $order->id_carrier = Configuration::get('ITELLA_COURIER_ID');
                $order_carrier->id_carrier = Configuration::get('ITELLA_COURIER_ID');
                $changed = true;
            }

            if ($changed) {
                $order_carrier->update();
                // Only prestashop 1.7 has carrier change functionality
                if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                    $this->context->currency = isset($this->context->currency) ? $this->context->currency : new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                    $order->refreshShippingCost();
                }
                $order->update();
            }

            $result['order_carrier'] = $order_carrier;
        }

        exit(json_encode($result));
    }
}