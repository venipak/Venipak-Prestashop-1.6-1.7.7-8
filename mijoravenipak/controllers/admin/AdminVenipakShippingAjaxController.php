<?php

use MijoraVenipak\MjvpApi;
use MijoraVenipak\MjvpDb;
use MijoraVenipak\MjvpHelper;
use MijoraVenipak\MjvpWarehouse;

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
            case 'prepareModal':
                $this->prepareOrderModal();
                break;
        }
    }


    protected function saveCart()
    {
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
                // Get selected terminal data.
                $order = new Order($id_order);
                $terminals = $this->module->getFilteredTerminals(false, $order);
                $selected_terminal = $this->module->getTerminalById($terminals, $terminal_id);
                $terminal_info = [];
                if(is_object($selected_terminal) && !empty($selected_terminal))
                {
                    $terminal_info = [
                        'name' => $selected_terminal->name,
                        'company_code' => $selected_terminal->code,
                        'country' => $selected_terminal->country,
                        'city' => $selected_terminal->city,
                        'address' => $selected_terminal->address,
                        'post_code' => $selected_terminal->zip,
                    ];
                }


                $data = [
                    'packages' => Tools::getValue('packs', 1),
                    'order_weight' => Tools::getValue('weight', 0),
                    'is_cod' => Tools::getValue('is_cod', 0),
                    'id_carrier_ref' => Tools::getValue('is_pickup', Configuration::get(MijoraVenipak::$_carriers['pickup']['reference_name'])),
                    'terminal_id' => Tools::getValue('id_pickup_point'),
                    'terminal_info' => json_encode($terminal_info),
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
                $field_carrier_call = $field_return_doc = 0;
                if(Tools::isSubmit('mjvp_carrier_call'))
                    $field_carrier_call = 1;
                if(Tools::isSubmit('mjvp_return_doc'))
                    $field_return_doc = 1;
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
                $order_extra_info['carrier_call'] = $field_carrier_call;
                $order_extra_info['return_doc'] = $field_return_doc;
                $data['other_info'] = json_encode($order_extra_info);
            }

            // Order warehouse
            if(empty(MjvpWarehouse::getWarehouses()))
            {
                $data['warehouse_id'] = 0;
            }
            else
            {
                $order_warehouse = (int) Tools::getValue('warehouse');
                $warehouse = new MjvpWarehouse($order_warehouse);
                if(!Validate::isLoadedObject($warehouse))
                {
                    $result['errors'][] = $this->module->l('Selected warehouse does not exist.');
                }
                else
                    $data['warehouse_id'] = $order_warehouse;
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
        $response = $this->module->bulkActionSendLabels((array) $order);

        // 1.7.7 and above
        if(is_array($response) && isset($response['success']))
            $result['success'] = $response['success'];
        if(is_array($response) && isset($response['errors']))
            $result['errors'] = $response['errors'];

        // Below 1.7.7
        if(!empty($this->context->controller->confirmations))
            $result['success'] = $this->context->controller->confirmations;
        if(!empty($this->context->controller->errors))
            $result['errors'] = $this->context->controller->errors;
        die(json_encode($result));
    }

    public function printLabel()
    {
        $cApi = new MjvpApi();
        $label_number = Tools::getValue('label_number');
        if(!is_array($label_number))
            $label_number = (array) $label_number;
        $cApi->printLabel($label_number);
    }

    public function prepareOrderModal()
    {
        $id_order = Tools::getValue('id_order');
        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order) || !isset($order->id_cart)) {
            die(json_encode(['error' => $this->module->l('Could not get a valid order object.')]));
        }

        $cDb = new MjvpDb();
        $cHelper = new MjvpHelper();

        try {
            $carrier = new Carrier($order->id_carrier);
            if (!$cHelper->itIsThisModuleCarrier($carrier->id_reference)) {
                return '';
            }

            $status = $cDb->getOrderValue('status', array('id_cart' => $order->id_cart));
            $error = $cDb->getOrderValue('error', array('id_cart' => $order->id_cart));
            $tracking_numbers = $cDb->getOrderValue('labels_numbers', array('id_cart' => $order->id_cart));
        } catch (Exception $e) {
            die(json_encode(['error' => $this->displayName . " error:<br/>" . $e->getMessage()]));
        }

        $venipak_cart_info = $cDb->getOrderInfo($order->id);
        $cApi = new MjvpApi();

        $order_country_code = $venipak_cart_info['country_code'];
        $pickup_points = $cApi->getTerminals($order_country_code);
        $order_terminal_id = $cDb->getOrderValue('terminal_id', ['id_order' => $order->id]);
        $venipak_carriers = [];
        foreach (MijoraVenipak::$_carriers as $carrier)
        {
            $reference = Configuration::get($carrier['reference_name']);
            $carrier = Carrier::getCarrierByReference($reference);
            $venipak_carriers[$reference] = $carrier->name;
        }

        $order_carrier_reference = $venipak_cart_info['id_carrier_ref'];
        if($order_carrier_reference == Configuration::get(MijoraVenipak::$_carriers['pickup']['reference_name']))
            $venipak_cart_info['is_pickup'] = true;

        $other_info = json_decode($venipak_cart_info['other_info'], true);
        $shipment_labels = json_decode($venipak_cart_info['labels_numbers'], true);

        $warehouses = MjvpWarehouse::getWarehouses();
        $order_warehouse = $cDb->getOrderValue('warehouse_id', array('id_order' => $order->id));
        if(!$order_warehouse)
            $order_warehouse = MjvpWarehouse::getDefaultWarehouse();

        $this->context->smarty->assign(array(
            'block_title' => $this->module->displayName,
            'module_dir' => __PS_BASE_URI__ . 'modules/' . $this->module->name,
            'label_status' => $status,
            'label_error' => $error,
            'order_id' => $order->id,
            'order_terminal_id' => $order_terminal_id,
            'venipak_pickup_points' => $pickup_points,
            'venipak_error' => ($venipak_cart_info['error'] != '' ? $this->module->displayError($venipak_cart_info['error']) : false),
            'label_tracking_numbers' => json_decode($tracking_numbers),
            'orderVenipakCartInfo' => $venipak_cart_info,
            'venipak_carriers' => $venipak_carriers,
            'venipak_other_info' => $other_info,
            'shipment_labels' => $shipment_labels,
            'warehouses' => $warehouses,
            'order_warehouse' => $order_warehouse,
            'delivery_times' => $this->module->deliveryTimes,
            'carrier_reference' => $order_carrier_reference,
            'pickup_reference' => Configuration::get(MijoraVenipak::$_carriers['pickup']['reference_name']),
            'venipak_print_label_url' => $this->context->link->getAdminLink('AdminVenipakshippingAjax') . '&action=printLabel',
        ));

        die(json_encode(['modal' => $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/change_order_info_modal.tpl')]));

    }
}