<?php

class AdminVenipakManifestsController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    /**
     * AdminVenipakManifestsController class constructor
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function __construct()
    {
        $this->list_no_link = true;
        $this->className = 'MjvpManifest';
        $this->table = 'mjvp_manifest';
        $this->identifier = 'id';
        parent::__construct();

        $this->_select = 'CONCAT(a.arrival_date_from, " - ", a.arrival_date_to) as date_arrival, mw.name as warehouse_name,
            s.`name` AS `shop_name`, 
            a.`id` as id_1,
            a.`id` as id_2,
            (SELECT GROUP_CONCAT(o.id_order SEPARATOR ", ") FROM `' . _DB_PREFIX_ .'mjvp_orders` o WHERE o.`manifest_id` = a.`manifest_id`) as orders';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'mjvp_warehouse` mw ON (a.`id_warehouse` = mw.`id`)
                        LEFT JOIN `' . _DB_PREFIX_ . 'mjvp_orders` mo ON (a.`manifest_id` = mo.`manifest_id`)
                        LEFT JOIN `' . _DB_PREFIX_ . 'shop` s ON (a.`id_shop` = s.`id_shop`)';
        $this->_where = ' AND (SELECT COUNT(*) FROM `'
            . _DB_PREFIX_ . 'mjvp_orders` o WHERE a.manifest_id = o.manifest_id) != 0';
        $this->_group = 'GROUP BY manifest_id';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->content .= $this->displayMenu();
            $this->readyManifestList();
        }
        parent::init();
    }

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function renderList()
    {
        unset($this->toolbar_btn['new']);
        $content =  parent::renderList();
        $content .= $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/call_carrier_modal.tpl');
        return $content;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJs('modules/' . $this->module->name . '/views/js/mjvp-manifest.js');
        Media::addJsDef([
                'call_url' => $this->context->link->getAdminLink($this->controller_name) . '&submitCallCarrier=1',
                'call_min_difference' => MijoraVenipak::CARRIER_CALL_MINIMUM_DIFFERENCE,
                'call_errors' => [
                    'manifest' => $this->module->l('No manifest selected'),
                    'arrival_times' => $this->module->l('Please select carrier arrival time interval.'),
                    'request' => $this->module->l('Failed to request Call courier'),
                    'invalid_dates' => $this->module->l('Incorrect arrival date interval: end of interval is earlier than the beginning.'),
                    'past_date' => $this->module->l('Arrival date cannot be in the past.'),
                    'minutes_quarterly' => $this->module->l('Minutes should be indicated quarterly: 15, 30, 45, 00.'),
                    'date_diff' => $this->module->l(sprintf('There must be at least %s hours difference between carrier arrival intervals',
                        MijoraVenipak::CARRIER_CALL_MINIMUM_DIFFERENCE)),
                ]
            ]
        );
    }


    /**
     * @throws SmartyException|PrestaShopException
     */
    private function displayMenu()
    {
        $menu = array(
            array(
                'label' => $this->module->l('Ready Orders'),
                'url' => $this->context->link->getAdminLink('AdminVenipakShipping'),
                'active' => false
            ),
            array(
                'label' => $this->module->l('Generated Manifests'),
                'url' => $this->context->link->getAdminLink($this->controller_name),
                'active' => Tools::getValue('controller') == $this->controller_name
            )
        );

        $warehouses = MjvpWarehouse::getWarehouses();

        $this->context->smarty->assign(array(
            'moduleMenu' => $menu,
            'warehouses' => $warehouses,
        ));

        return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/manifest_menu.tpl');
    }

    protected function readyManifestList()
    {
        $this->fields_list = array(
            'manifest_id' => array(
                'title' => $this->module->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'a!manifest_id',
                'order_key' => 'a!manifest_id',
            ),
            'shop_name' => array(
                'type' => 'text',
                'title' => $this->module->l('Shop'),
                'align' => 'center',
                'filter_key' => 's!name',
                'order_key' => 's!name',
            ),
            'date_add' => array(
                'title' => $this->module->l('Creation Date'),
                'align' => 'center',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
            'orders' => array(
                'title' => $this->module->l('Orders in manifest'),
                'align' => 'text-center',
                'havingFilter' => true,
            ),
            'date_arrival' => array(
                'title' => $this->module->l('Carrier arrival'),
                'align' => 'center',
                'type' => 'datetime',
                'filter_key' => 'a!arrival_date_from',
            ),
            'warehouse_name' => array(
                'title' => $this->module->l('Warehouse'),
                'align' => 'text-center',
                'filter_key' => 'mw!name',
            ),
            'closed' => array(
                'type' => 'bool',
                'title' => $this->module->l('Closed'),
            ),
        );

        $this->fields_list['id_1'] = array(
            'title' => '',
            'align' => 'text-left remove-dashes',
            'search' => false,
            'orderby' => false,
            'callback' => 'printManifestBtn',
        );

        $this->fields_list['id_2'] = array(
            'title' => '',
            'align' => 'text-left remove-dashes',
            'search' => false,
            'orderby' => false,
            'callback' => 'printCallCarrierBtn',
        );

        $this->actions = [];
    }

    public function printManifestBtn($id_manifest)
    {
        $cDb = $this->module->getModuleService('MjvpDb');
        $closed = $cDb->getManifestValue('closed', ['id' => $id_manifest]);
        if($closed)
        {
            $this->context->smarty->assign('data_button', [
                    'icon' => 'icon-file-pdf-o',
                    'title' => $this->module->l('Print Manifest'),
                    'blank' => true,
                    'manifest' => $id_manifest,
            ]);
            return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
        }
        else
        {
            $this->context->smarty->assign('data_button', [
                'icon' => 'icon-file-pdf-o',
                'title' => $this->module->l('Print and close Manifest'),
                'manifest' => $id_manifest,
                'class' => 'close-manifest',
            ]);
            return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
        }
    }

    public function printCallCarrierBtn($id_manifest)
    {
        $cDb = $this->module->getModuleService('MjvpDb');
        $arrival_time_from = $cDb->getManifestValue('arrival_date_from', ['id' => $id_manifest]);
        $arrival_time_to = $cDb->getManifestValue('arrival_date_to', ['id' => $id_manifest]);
        $closed = $cDb->getManifestValue('closed', ['id' => $id_manifest]);
        $id_warehouse = $cDb->getManifestValue('id_warehouse', ['id' => $id_manifest]);

        if((!$arrival_time_from || !$arrival_time_to) && $closed)
        {
            $this->context->smarty->assign('data_button', [
                'data' => [
                    [
                        'identifier' => 'warehouse',
                        'value' => $id_warehouse,
                    ],
                    [
                        'identifier' => 'manifest',
                        'value' => $id_manifest,
                    ]
                ],
                'icon' => 'icon-phone',
                'title' => $this->module->l('Call Courier'),
                'href' => '#'
            ]);
            return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
        }
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('printmjvp_manifest')) {
            $cApi = $this->module->getModuleService('MjvpApi');
            $cDb = $this->module->getModuleService('MjvpDb');
            $id_manifest = Tools::getValue('id');
            $manifest = $this->module->getModuleService('MjvpManifest', $id_manifest);
            if(!Validate::isLoadedObject($manifest))
            {
                $this->errors[] = $this->module->l("Could not find the specified manifest.");
            }
            else
            {
                $manifest->closed = 1;
                $manifest->update();
                $manifest_number = json_decode($cDb->getManifestValue('manifest_id', ['id' => $id_manifest]), true);
                $cApi->printManifest($manifest_number);
            }
        }
        if(Tools::isSubmit('submitCallCarrier')) {
            $this->processCarrierCall();
        }
    }

    public function processCarrierCall()
    {
        $cHelper = $this->module->getModuleService('MjvpHelper');
        $cConfig = $this->module->getModuleService('MjvpModuleConfig');
        $cApi = $this->module->getModuleService('MjvpApi');

        $form_data = $this->validateCarrierInviteData();
        if(!empty($form_data['errors']))
        {
            die(json_encode(['error' => $form_data['errors']]));
        }

        $invitation_data = [
            'desc_type' => 3
        ];
        $sender = [];

        // If no warehouses are created, data from shop settings will be used for sender data.
        $id_warehouse = $form_data['id_warehouse'];
        if($id_warehouse > 0)
        {
            $warehouse = $this->module->getModuleService('MjvpWarehouse', $id_warehouse);
        }
        else
        {
            $warehouse_data = $this->formTemporayWarehouse();
            if(!empty($warehouse_data['errors']))
            {
                die(json_encode(['error' => $warehouse_data['errors']]));
            }
            $warehouse = $warehouse_data['warehouse'];
        }

        $id_manifest = $form_data['id_manifest'];
        $manifest = $this->module->getModuleService('MjvpManifest', $id_manifest);
        $manifest->id_warehouse = $id_warehouse;
        $sender['name'] = $warehouse->name;
        $sender['code'] = $warehouse->company_code;
        $sender['country_code'] = $warehouse->country_code;
        $sender['city'] = $warehouse->city;
        $sender['address'] = $warehouse->address;
        $sender['postcode'] = $warehouse->zip_code;
        $sender['contact_person'] = $warehouse->contact;
        $sender['contact_phone'] = $warehouse->phone;
        $sender['contact_email'] = Configuration::get($cConfig->getConfigKey('shop_email', 'SHOP'));
        $invitation_data['sender'] = $sender;

        // Get manifest weight
        $shipment_weight = Db::getInstance()->getValue('SELECT SUM(mo.order_weight) 
                FROM ' . _DB_PREFIX_ . 'mjvp_manifest mm
                LEFT JOIN ' . _DB_PREFIX_ . 'mjvp_orders mo ON mo.manifest_id = mm.manifest_id 
                WHERE mm.id = ' . $id_manifest);
        $shipment_weight = Tools::ps_round((float) $shipment_weight, 2);
        if($shipment_weight <= 0)
            $shipment_weight = 0.001;
        $invitation_data['weight'] = $shipment_weight;
        $manifest->shipment_weight = $shipment_weight;

        // Calculate manifest volume
        $manifest_orders = Db::getInstance()->executeS('SELECT mo.id_order 
                FROM ' . _DB_PREFIX_ . 'mjvp_manifest mm
                LEFT JOIN ' . _DB_PREFIX_ . 'mjvp_orders mo ON mo.manifest_id = mm.manifest_id 
                WHERE mm.id = ' . $id_manifest);

        $manifest_volume = 0;
        $product_volume = 0;
        foreach ($manifest_orders as $order)
        {
            $id_order = $order['id_order'];
            $order = new Order($id_order);
            $order_products = $order->getProducts();

            foreach ($order_products as $key => $product) {
                // Calculate volume in m3
                if(Configuration::get('PS_DIMENSION_UNIT') == 'm')
                    $product_volume = $product['width'] * $product['height'] * $product['depth'];
                elseif(Configuration::get('PS_DIMENSION_UNIT') == 'cm')
                    $product_volume = ($product['width'] * $product['height'] * $product['depth']) / 1000000;
                $manifest_volume += (float)$product_volume;
            }
        }
        if($manifest_volume <= 0)
            $manifest_volume = 0.001;

        $invitation_data['volume'] = $manifest_volume;

        // Get carrier arrival time
        $arrival_from = $form_data['arrival_date_from'];
        // Fix: convert to format with seconds, otherwise Prestashop fails to validate date
        $manifest->arrival_date_from = date('Y-m-d H:i:s', strtotime($arrival_from));
        $arrival_from_parsed = $cHelper->parseDate($arrival_from);
        $arrival_to = $form_data['arrival_date_to'];
        $manifest->arrival_date_to = date('Y-m-d H:i:s', strtotime($arrival_to));
        $arrival_to_parsed = $cHelper->parseDate($arrival_to);
        $invitation_data['date_y'] = $arrival_from_parsed['year'];
        $invitation_data['date_m'] = $arrival_from_parsed['month'];
        $invitation_data['date_d'] = $arrival_from_parsed['day'];
        $invitation_data['hour_from'] = $arrival_from_parsed['hour'];
        $invitation_data['min_from'] = $arrival_from_parsed['minute'];
        $invitation_data['hour_to'] = $arrival_to_parsed['hour'];
        $invitation_data['min_to'] = $arrival_to_parsed['minute'];

        $comment = $form_data['call_comment'];
        $manifest->call_comment = $comment;
        if ($comment)
            $invitation_data['comment'] = $comment;

        $courier_invitation_xml = $cApi->buildCourierInvitationXml($invitation_data);
        if ($cHelper->isXMLContentValid($courier_invitation_xml))
        {
            $status = $cApi->sendXml($courier_invitation_xml);
            if (!isset($status['error']) && $status['text']) {
                $manifest->save();
                die(json_encode(['success' => $this->module->l("Carrier called successfully.")]));
            }
            else
            {
                die(json_encode(['error' => $status['error']['text']]));
            }

        }
        else
        {
            die(json_encode(['error' => $this->module->l("Invalid XML format.")]));
        }
    }

    // Re-validate data in case of JavaScript forgery.
    public function validateCarrierInviteData()
    {
        $data = [];
        $errors = [];

        $id_manifest = (int)Tools::getValue('id_manifest');
        $manifest = $this->module->getModuleService('MjvpManifest', $id_manifest);

        // Manifest
        $data['id_manifest'] = $id_manifest;
        if(!Validate::isLoadedObject($manifest))
        {
            $errors[] = $this->module->l('Selected Manifest does not exist');
        }

        // Warehouse
        $id_warehouse = $manifest->id_warehouse;
        $warehouse = $this->module->getModuleService('MjvpManifest', $id_warehouse);
        $data['id_warehouse'] = $id_warehouse;
        if(!Validate::isLoadedObject($warehouse) && $id_warehouse != 0)
        {
            $errors[] = $this->module->l('Selected Warehouse does not exist');
        }

        // Comment
        $comment = Tools::getValue('call_comment', '');
        $data['call_comment'] = $comment;
        if(strlen($comment) > 50)
        {
            $errors[] = $this->module->l('Maximum comment length is 50 characters');
        }

        $arrival_from = Tools::getValue('arrival_date_from');
        $arrival_to = Tools::getValue('arrival_date_to');
        $data['arrival_date_from'] = $arrival_from;
        $data['arrival_date_to'] = $arrival_to;
        if(!$arrival_from || !$arrival_to)
        {
            $errors[] = $this->module->l('Courier arrival interval is not submit.');
        }

        $arrival_from_obj = new DateTime($arrival_from);
        $arrival_to_obj = new DateTime($arrival_to);
        if($arrival_to_obj < $arrival_from_obj)
        {
            $errors[] = $this->module->l('Incorrect arrival date interval: end of interval is earlier than the beginning.');
        }

        $arrival_diff = $arrival_from_obj->diff($arrival_to_obj);
        if($arrival_diff->y == 0 && $arrival_diff->m == 0 && $arrival_diff->d == 0 && $arrival_diff->h < 2)
        {
            $errors[] = $this->module->l(sprintf('There must be at least %s hours difference between carrier arrival intervals',MijoraVenipak::CARRIER_CALL_MINIMUM_DIFFERENCE));
        }

        if(date('i', strtotime($arrival_from)) % 15 != 0 || date('i', strtotime($arrival_to)) % 15 != 0)
        {
            $errors[] = $this->module->l('Minutes should be indicated quarterly: 15, 30, 45, 00.');
        }

        $arrival_from_obj->setTime(0,0,0);
        $arrival_to_obj->setTime(0,0,0);
        $arrival_diff = $arrival_from_obj->diff($arrival_to_obj);
        if($arrival_diff->d > 0)
        {
            $errors[] = $this->module->l('Both times must be on the same day.');
        }

        $data['errors'] = $errors;

        return $data;

    }

    public function formTemporayWarehouse()
    {
        $data = $errors = [];
        $cConfig = $this->module->getModuleService('MjvpModuleConfig');
        $warehouse = $this->module->getModuleService('MjvpWarehouse');
        $shop_name = Configuration::get($cConfig->getConfigKey('sender_name', 'SHOP'));
        if(!$shop_name)
            $errors[] = $this->module->l('Sender name is required.');
        $warehouse->name = $shop_name;

        $company_code = Configuration::get($cConfig->getConfigKey('company_code', 'SHOP'));
        $warehouse->company_code = $company_code;

        $shop_country_code = Configuration::get($cConfig->getConfigKey('shop_country_code', 'SHOP'));
        if(!$shop_country_code)
            $errors[] = $this->module->l('Shop country code is required.');
        $warehouse->country_code = $shop_country_code;

        $shop_city = Configuration::get($cConfig->getConfigKey('shop_city', 'SHOP'));
        if(!$shop_city)
            $errors[] = $this->module->l('Shop city is required.');
        $warehouse->city = $shop_city;

        $shop_address = Configuration::get($cConfig->getConfigKey('shop_address', 'SHOP'));
        if(!$shop_address)
            $errors[] = $this->module->l('Shop address is required.');
        $warehouse->address = $shop_address;

        $shop_postcode = Configuration::get($cConfig->getConfigKey('shop_postcode', 'SHOP'));
        if(!$shop_postcode)
            $errors[] = $this->module->l('Shop postcode is required.');
        $warehouse->zip_code = $shop_postcode;

        $shop_contact = Configuration::get($cConfig->getConfigKey('shop_contact', 'SHOP'));
        $warehouse->contact = $shop_contact;

        $shop_phone = Configuration::get($cConfig->getConfigKey('shop_phone', 'SHOP'));
        if(!$shop_phone)
            $errors[] = $this->module->l('Shop phone is required.');
        $warehouse->phone = $shop_phone;

        if(!empty($errors))
        {
            $errors[] = $this->module->l('Please update your shop settings.');
        }

        $data['warehouse'] = $warehouse;
        $data['errors'] = $errors;
        return $data;
    }
}