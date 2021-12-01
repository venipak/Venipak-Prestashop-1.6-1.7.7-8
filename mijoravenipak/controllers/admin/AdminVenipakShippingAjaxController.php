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
            case 'prepareModal':
                $this->prepareOrderModal();
                break;
            case 'trackOrders':
                $this->getOrderTrackingModal();
                break;
        }
    }


    protected function saveCart()
    {
        $cDb = $this->module->getModuleService('MjvpDb');
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
                        'is_cod' => $selected_terminal->cod_enabled
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

                // Order warehouse
                if(empty(MjvpWarehouse::getWarehouses()))
                {
                    $data['warehouse_id'] = 0;
                }
                else
                {
                    $order_warehouse = (int) Tools::getValue('warehouse');
                    $warehouse = $this->module->getModuleService('MjvpWarehouse', $order_warehouse);
                    if(!Validate::isLoadedObject($warehouse))
                    {
                        // If order was not assigned warehouse (i.e none were created at that moment), then assign the default warehouse.
                        $data['warehouse_id'] = MjvpWarehouse::getDefaultWarehouse();
                    }
                    else
                        $data['warehouse_id'] = $order_warehouse;
                }

                $return_service = 0;
                if(Tools::isSubmit('mjvp_return_service'))
                    $return_service = 1;
                $order_extra_info = [];
                $order_extra_info['return_service'] = $return_service;
                $data['other_info'] = json_encode($order_extra_info);

                $res = $this->createVenipakOrderIfNotExists($order);
                $res &= $cDb->updateOrderInfo($id_order, $data, 'id_order');
                if($res)
                {
                    $result['success'][] = $this->module->l('Shipment data updated successfully.');
                }
                else
                {
                    $result['errors'][] = $this->module->l('Failed to update shipment data.');
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
                $return_service = 0;
                if(Tools::isSubmit('mjvp_return_service'))
                    $return_service = 1;

                $order_extra_info = [];
                $order_extra_info['return_service'] = $return_service;
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
                $warehouse = $this->module->getModuleService('MjvpWarehouse', $order_warehouse);
                if(!Validate::isLoadedObject($warehouse))
                {
                    // If order was not assigned warehouse (i.e none were created at that moment), then assign the default warehouse.
                    $data['warehouse_id'] = MjvpWarehouse::getDefaultWarehouse();
                }
                else
                    $data['warehouse_id'] = $order_warehouse;
            }

            if (!isset($result['errors'])) {
                $order = new Order($id_order);
                $res = $this->createVenipakOrderIfNotExists($order);
                $res &= $cDb->updateOrderInfo($id_order, $data, 'id_order');
                if ($res) {
                    $result['success'][] = $this->module->l('Shipment data updated successfully.');
                } else {
                    $result['errors'][] = $this->module->l('Failed to update shipment data.');
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
                $this->module->refreshShippingCost($order);
                $order->update();
            }
        }

        die(json_encode($result));
    }

    public function createVenipakOrderIfNotExists($order)
    {
        // Fix issue if customer selected Venipak carrier/terminal, but order data was not registered.
        // As that functionality is handled by JavaScript, the problem is possible due to browser's cache.
        $cDb = $this->module->getModuleService('MjvpDb');
        $venipak_cart_info = $cDb->getOrderInfo($order->id);
        if(!$venipak_cart_info)
        {
            $order_weight = $order->getTotalWeight();

            // Convert to kg, if weight is in grams.
            if(Configuration::get('PS_WEIGHT_UNIT') == 'g')
                $order_weight *= 0.001;
            
            $is_cod = 0;
            if(in_array($order->module, MijoraVenipak::$_codModules))
                $is_cod = 1;

            $address = new Address($order->id_address_delivery);
            $country = new Country();
            $country_code = $country->getIsoById($address->id_country);
            $newOrderData = [
                'id_order' => $order->id,
                'id_cart' => $order->id_cart,
                'id_carrier_ref' => $selected_carrier_reference,
                'order_weight' => $order_weight,
                'is_cod' => $is_cod,
                'cod_amount' => $order->total_paid_tax_incl,
                'country_code' => $country_code,
            ];
            $res = $cDb->saveOrderInfo($newOrderData);
            return $res;
        }
        return true;
    }

    public function generateLabel()
    {
        $order = (int) Tools::getValue('id_order');
        $cDb = $this->module->getModuleService('MjvpDb');
        $warehouse_id = $cDb->getOrderValue('warehouse_id', array('id_order' => $order));
        $response = $this->module->bulkActionSendLabels(
            [
                'warehouse_id' => $warehouse_id,
                'orders' => (array) $order,
            ]
        );

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
        $cApi = $this->module->getModuleService('MjvpApi');
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

        $cDb = $this->module->getModuleService('MjvpDb');
        $cHelper = $this->module->getModuleService('MjvpHelper');

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
        $order_country_code = $venipak_cart_info['country_code'];

        $cFiles = $this->module->getModuleService('MjvpFiles');
        $pickup_points = $cFiles->getTerminalsListForCountry($order_country_code, false);
        if(!$pickup_points)
            $pickup_points = [];
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

    public function getOrderTrackingModal()
    {
        $cDb = $this->module->getModuleService('MjvpDb');
        $id_order = (int) Tools::getValue('id_order');

        // If single order is tracked, we return modal with tracking history.
        if(Validate::isLoadedObject(new Order($id_order)))
        {
            $labels_numbers = $cDb->getOrderValue('labels_numbers', array('id_order' => $id_order));
            $orders[] = [
                'id_order' => $id_order,
                'labels_numbers' => $labels_numbers,
            ];

            $orders_tracking_numbers = [];
            foreach ($orders as $row)
            {
                $orders_tracking_numbers[$row['id_order']] = json_decode($row['labels_numbers'], true);
            }
            $cApi = $this->module->getModuleService('MjvpApi');
            $shipments = [];
            $csv_fields = [
                'pack_no', 'shipment_no', 'date', 'status', 'terminal'
            ];

            foreach ($orders_tracking_numbers as $order_id => $tracking_numbers)
            {
                $shipment = [];
                $shipment['order_id'] = $order_id;
                $shipment['heading'] = $this->module->l(sprintf("Order #%d (packets: %s)", $order_id, implode(', ', $tracking_numbers)));
                foreach ($tracking_numbers as $tracking_number)
                {
                    $csv = $cApi->getTrackingShipment($tracking_number);
                    $count = 0;
                    $shipment_data = [];
                    if (($handle = fopen("data://text/csv," . $csv, "r")) !== false) {
                        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                            $count++;
                            if($count == 1)
                                continue;
                            $row = [];
                            $num = count($data);
                            for ($c = 0; $c < $num; $c++) {
                                $row[$csv_fields[$c]] = $data[$c];
                            }
                            $shipment_data[] = $row;
                        }
                        $shipment['data'] = $shipment_data;
                        $shipments[$tracking_number] = $shipment;
                        fclose($handle);
                    }
                }
            }

            $this->context->smarty->assign(array(
                'shipments' => $shipments,
                'module_dir' => __PS_BASE_URI__ . 'modules/' . $this->module->name,
            ));
            die(json_encode(['modal' => $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/tracking_modal.tpl')]));
        }
        // If query is to track all orders, when we simply update their tracking status and reload the page.
        else
        {
            // Get all undelivered Venipak orders
            $orders = Db::getInstance()->executeS('SELECT mo.* FROM ' . _DB_PREFIX_ . 'mjvp_orders mo
                LEFT JOIN ' ._DB_PREFIX_ . 'orders o ON o.`id_order` = mo.`id_order`
                LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON (o.`id_carrier` = c.`id_carrier`)
                WHERE mo.`id_order` IS NOT NULL 
                AND mo.`labels_numbers` IS NOT NULL 
                AND mo.`manifest_id` IS NOT NULL 
                AND mo.`status` != "delivered"
                AND mo.`last_select` > date_sub(now(), interval 1 month)
                AND c.id_reference IN (' . Configuration::get('MJVP_COURIER_ID_REFERENCE') . ','
                . Configuration::get('MJVP_PICKUP_ID_REFERENCE') . ')'
            );
            $orders_tracking_numbers = [];
            if(empty($orders))
            {
                die(json_encode(['warning' => $this->module->l('No trackable orders found')]));
            }
            foreach ($orders as $row)
            {
                $orders_tracking_numbers[$row['id_order']] = json_decode($row['labels_numbers'], true);
            }

            $cApi = $this->module->getModuleService('MjvpApi');
            foreach ($orders_tracking_numbers as $order_id => $tracking_numbers)
            {
                foreach ($tracking_numbers as $tracking_number)
                {
                    $csv = $cApi->getTrackingShipment($tracking_number);
                    if (($handle = fopen("data://text/csv," . $csv, "r")) !== false) {
                        $lastRow = [];
                        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                            // Omit header
                            if(isset($data[3]) && $data[3] != 'Status')
                                $lastRow = $data;
                        }
                        fclose($handle);
                        // Check the last row of the tracking history to get the last tracking status.
                        if(isset($lastRow[3]))
                        {
                            $cDb = $this->module->getModuleService('MjvpDb');
                            $cDb->updateOrderInfo($order_id, ['status' => strtolower($lastRow[3])], 'id_order');
                        }
                    }
                }
            }
            die(json_encode(['success' => $this->module->l('Orders tracking updated.')]));
        }
    }
}