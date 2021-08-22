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

        MijoraVenipak::checkForClass('MjvpManifest');
        $this->_select = ' (SELECT COUNT(*) FROM `'
            . _DB_PREFIX_ . 'mjvp_orders` o WHERE o.manifest_id = a.manifest_id) as manifest_total,
            CONCAT(a.arrival_date_from, " - ", a.arrival_date_to) as date_arrival ';
        $this->_where = ' AND (SELECT COUNT(*) FROM `'
            . _DB_PREFIX_ . 'mjvp_orders` o WHERE a.manifest_id = o.manifest_id) != 0';
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->l('Select shop');
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
        $warehouses = MjvpWarehouse::getWarehouses();
        $this->addJs('modules/' . $this->module->name . '/views/js/mjvp-manifest.js');
        Media::addJsDef([
                'warehouses' => $warehouses,
                'call_url' => $this->context->link->getAdminLink($this->controller_name, true, [], ['submitCallCarrier' => 1]),
                'call_min_difference' => MijoraVenipak::CARRIER_CALL_MINIMUM_DIFFERENCE,
                'call_errors' => [
                    'warehouse' => $this->module->l('No warehouse selected'),
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
                'label' => $this->l('Ready Orders'),
                'url' => $this->context->link->getAdminLink('AdminVenipakShipping'),
                'active' => false
            ),
            array(
                'label' => $this->l('Generated Manifests'),
                'url' => $this->context->link->getAdminLink($this->controller_name),
                'active' => Tools::getValue('controller') == $this->controller_name
            )
        );

        MijoraVenipak::checkForClass('MjvpWarehouse');
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
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'search' => false,
                'orderby' => false
            ),
            'id_shop' => array(
                'type' => 'text',
                'title' => $this->l('Shop'),
                'align' => 'center',
                'search' => false,
                'havingFilter' => false,
                'orderby' => false,
                'callback' => 'getShopNameById',
            ),
            'date_add' => array(
                'title' => $this->l('Creation Date'),
                'align' => 'center',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
            ),
            'manifest_total' => array(
                'title' => $this->l('Orders in manifest'),
                'align' => 'text-center',
                'search' => false,
                'class' => 'fixed-width-xs',
            ),
            'date_arrival' => array(
                'title' => $this->l('Carrier arrival'),
                'align' => 'center',
                'type' => 'text',
            ),
        );

        $this->fields_list['id'] = array(
            'title' => $this->l('Actions'),
            'align' => 'text-right',
            'search' => false,
            'orderby' => false,
            'callback' => 'printBtn',
        );

        $this->actions = array('none');
    }

    public function getShopNameById($id)
    {
        $shop = new Shop($id);
        return $shop->name;
    }

    public function printBtn($id)
    {
        MijoraVenipak::checkForClass('MjvpDb');
        $cDb = new MjvpDb();

        $arrival_time_from = $cDb->getManifestValue('arrival_date_from', ['id' => $id]);
        $arrival_time_to = $cDb->getManifestValue('arrival_date_to', ['id' => $id]);
        $content = '<span class="btn-group-action">
                        <span class="btn-group">
                            <a target="_blank" class="btn btn-default" href="' . self::$currentIndex . '&token=' . $this->token . '&manifestdone&ajax=1' . '&print' . $this->table . '&id=' . $id . '"><i class="icon-file-pdf-o"></i>&nbsp;' . $this->l('Print Manifest') . '
                            </a>
                        </span>
                    </span>';
        if(!$arrival_time_from || !$arrival_time_to)
        {
            $content .= '<span class="btn-group-action">
                            <span class="btn-group">
                                <a data-manifest="' . $id . '" class="btn btn-default" href="#"><i class="icon-file-pdf-o"></i>&nbsp;' . $this->l('Call Courier') . '
                                </a>
                            </span>
                        </span>';
        }
        return $content;
    }

    public function postProcess()
    {
        MijoraVenipak::checkForClass('MjvpApi');
        if (Tools::isSubmit('printmjvp_manifest')) {
            MijoraVenipak::checkForClass('MjvpDb');
            $cApi = new MjvpApi();
            $cDb = new MjvpDb();
            $id_manifest = Tools::getValue('id');
            $manifest_number = json_decode($cDb->getManifestValue('manifest_id', ['id' => $id_manifest]), true);
            $cApi->printList($manifest_number);
        }
        if(Tools::isSubmit('submitCallCarrier')) {
            $this->processCarrierCall();
        }
    }

    public function processCarrierCall()
    {
        MijoraVenipak::checkForClass('MjvpManifest');
        MijoraVenipak::checkForClass('MjvpWarehouse');
        MijoraVenipak::checkForClass('MjvpHelper');
        MijoraVenipak::checkForClass('MjvpModuleConfig');
        $cHelper = new MjvpHelper();
        $cConfig = new MjvpModuleConfig();
        $cApi = new MjvpApi();

        $form_data = $this->validateCarrierInviteData();
        if(!empty($form_data['errors']))
        {
            die(json_encode(['error' => $form_data['errors']]));
        }

        $invitation_data = [
            'desc_type' => 3
        ];
        $sender = [];
        $id_warehouse = $form_data['id_warehouse'];
        $id_manifest = $form_data['id_manifest'];
        $manifest = new MjvpManifest($id_manifest);
        $manifest->id_warehouse = $id_warehouse;
        $warehouse = new MjvpWarehouse($id_warehouse);
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
        $invitation_data['volume'] = $manifest_volume;

        // Get carrier arrival time
        $arrival_from = $form_data['arrival_date_from'];
        // Fix: convert to format with seconds, otherwise Prestashop fails to validat date
        $manifest->arrival_date_from = date('Y-m-d H:i:s', strtotime($arrival_from));
        $arrival_from_parsed = $cHelper->parseDate($arrival_from);
        $arrival_to = $form_data['arrival_date_from'];
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
            die();
            $status = $cApi->sendXml($courier_invitation_xml);
            if (!isset($status['error']) && $status['text']) {
                $manifest->save();
                die(json_encode(['success' => $this->module->l("Carrier called successfully.")]));
            }
            else
            {
                die(json_encode(['error' => $status['error']]));
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

        // Warehouse
        $id_warehouse = (int)Tools::getValue('id_warehouse');
        $warehouse = new MjvpWarehouse($id_warehouse);
        $data['id_warehouse'] = $id_warehouse;
        if(!Validate::isLoadedObject($warehouse))
        {
            $errors[] = $this->module->l('Selected Warehouse does not exist');
        }

        // Manifest
        $id_manifest = (int)Tools::getValue('id_manifest');
        $manifest = new MjvpManifest($id_manifest);
        $data['id_manifest'] = $id_manifest;
        if(!Validate::isLoadedObject($manifest))
        {
            $errors[] = $this->module->l('Selected Manifest does not exist');
        }

        // Comment
        $comment = Tools::getValue('call_comment', '');
        $data['comment'] = $comment;
        if(strlen($comment) > 50)
        {
            $errors[] = $this->module->l('Maximum comment length is 50 characters');
        }

        $arrival_from = Tools::getValue('arrival_date_from');
        $arrival_to = Tools::getValue('arrival_date_to');
        $data['arrival_from'] = $arrival_from;
        $data['arrival_to'] = $arrival_to;
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
        $data['errors'] = $errors;

        return $data;

    }
}