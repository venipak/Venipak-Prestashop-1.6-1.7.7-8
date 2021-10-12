<?php

class AdminVenipakShippingController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;
    private $statuses_array;

    public static $status_trans = [];

    /**
     * AdminVenipakShippingController class constructor
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function __construct()
    {

        $this->list_no_link = true;
        $this->className = 'Order';
        $this->table = 'order';
        parent::__construct();
        self::$status_trans = [
            'new' => $this->module->l('new', 'ADMINVENIPAKSHIPPINGCONTROLLER'),
            'registered' => $this->module->l('registered', 'ADMINVENIPAKSHIPPINGCONTROLLER'),
            'at terminal' => $this->module->l('at terminal', 'ADMINVENIPAKSHIPPINGCONTROLLER'),
            'out for delivery' => $this->module->l('out for delivery', 'ADMINVENIPAKSHIPPINGCONTROLLER'),
            'delivered' => $this->module->l('delivered', 'ADMINVENIPAKSHIPPINGCONTROLLER'),
        ];
        $this->toolbar_title = $this->module->l('Venipak Orders');
        $this->_select = '
            mo.labels_numbers as label_number,
            status,
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            a.id_order AS id_print,
            a.id_order AS id_order_1,
            a.id_order AS id_order_2,
            a.id_order AS id_order_3,
            s.`name` AS `shop_name`
		';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'mjvp_orders` mo ON (mo.`id_order` = a.`id_order`)
            LEFT JOIN `' . _DB_PREFIX_ . 'shop` s ON (a.`id_shop` = s.`id_shop`)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'carrier` carrier ON (carrier.`id_carrier` = a.`id_carrier`)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')
    ';

        $this->_sql = '
      SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders` a
      WHERE 1 ' . Shop::addSqlRestrictionOnLang('a');

        $this->_where = ' AND carrier.id_reference IN ('
            . Configuration::get('MJVP_COURIER_ID_REFERENCE') . ','
            . Configuration::get('MJVP_PICKUP_ID_REFERENCE') . ")
            AND mo.`id_order` IS NOT NULL";
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->content .= $this->displayMenu();
            $this->readyOrdersList();
        }
    }

    protected function readyOrdersList()
    {
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->module->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'shop_name' => array(
                'type' => 'text',
                'title' => $this->module->l('Shop'),
                'align' => 'center',
                'filter_key' => 's!name',
                'order_key' => 's!name',
            ),
            'osname' => array(
                'title' => $this->module->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'customer' => array(
                'title' => $this->module->l('Customer'),
                'havingFilter' => true,
            ),
            'label_number' => array(
                'type' => 'text',
                'title' => $this->module->l('Tracking number(s)'),
                'havingFilter' => false,
                'callback' => 'parseLabelNumbers',
            )
        );

        $this->fields_list['id_order_1'] = array(
            'title' => '',
            'align' => 'text-left remove-dashes',
            'search' => false,
            'orderby' => false,
            'callback' => 'labelBtn',
        );
        $this->fields_list['id_order_2'] = array(
            'title' => $this->module->l('Actions'),
            'align' => 'text-center remove-dashes',
            'search' => false,
            'orderby' => false,
            'callback' => 'shipmentInfoBtn',
        );
        $this->fields_list['id_order_3'] = array(
            'title' => '',
            'align' => 'text-left remove-dashes',
            'search' => false,
            'orderby' => false,
            'callback' => 'trackingBtn',
        );
        $this->fields_list['status'] = array(
            'title' => 'Tracking status',
            'align' => 'text-left',
            'callback' => 'transStatus'
        );

        $this->actions = [];
        $this->bulk_actions = array(
            'generateLabels' => array(
                'text' => $this->module->l('Generate Labels'),
                'icon' => 'icon-save'
            ),
            'printLabels' => array(
                'text' => $this->module->l('Print Labels'),
                'icon' => 'icon-print'
            ),
        );
    }

    public function renderList()
    {
        switch (Shop::getContext()) {
            case Shop::CONTEXT_GROUP:
                $this->_where .= ' AND a.`id_shop` IN(' . implode(',', Shop::getContextListShopID()) . ')';
                break;

            case Shop::CONTEXT_SHOP:
                $this->_where .= Shop::addSqlRestrictionOnLang('a');
                break;

            default:
                break;
        }
        $this->_use_found_rows = false;

        unset($this->toolbar_btn['new']);

        return parent::renderList();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef([
            'venipak_prepare_modal_url' =>  $this->context->link->getAdminLink('AdminVenipakshippingAjax') . '&action=prepareModal',
        ]);
    }

    public function labelBtn($id_order)
    {
        $cDb = $this->module->getModuleService('MjvpDb');
        $tracking_number = $cDb->getOrderValue('labels_numbers', ['id_order' => $id_order]);
        if (!$tracking_number) {
            $this->context->smarty->assign('data_button',
            [
                'orders' => $id_order,
                'icon' => 'icon-save',
                'action' => 'submitGenerateLabel',
                'title' => $this->module->l('Generate label'),
            ]);
            return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
        }
        $this->context->smarty->assign('data_button', [
            'orders' => $id_order,
            'icon' => 'icon-file-pdf-o',
            'action' => 'submitPrintLabel',
            'blank' => true,
            'title' => $this->module->l('Print label(s)')
        ]);
        return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
    }

    public function shipmentInfoBtn($id_order)
    {
        $this->context->smarty->assign('data_button', [
            'data' => [
                [
                'identifier' => 'order',
                'value' => $id_order,
                ],
            ],
            'data_order' => $id_order,
            'icon' => 'icon-edit',
            'title' => $this->module->l('Change Shipment Info'),
            'class' => 'change-shipment-modal',
            'href' => '#'
        ]);
        return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
    }

    public function trackingBtn($id_order)
    {
        $cDb = $this->module->getModuleService('MjvpDb');
        $tracking_number = $cDb->getOrderValue('labels_numbers', ['id_order' => $id_order]);
        if($tracking_number)
        {
            $this->context->smarty->assign('data_button', [
                'data' => [
                    [
                        'identifier' => 'id-order',
                        'value' => $id_order,
                    ]
                ],
                'icon' => 'icon-truck',
                'title' => $this->module->l('Shipment Tracking'),
                'class' => 'track-orders',
                'href' => '#'
            ]);
            return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/action_button.tpl');
        }
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::isSubmit('submitGenerateLabel') || Tools::isSubmit('submitBulkgenerateLabelsorder'))
        {
            $orders = Tools::getValue('orderBox');
            $warehouse_groups = $this->module->formatWarehousesOrderGroups($orders);
            if(!empty($warehouse_groups))
            {
                foreach ($warehouse_groups as $warehouse_id => $orders)
                {
                    $this->module->bulkActionSendLabels(
                        [
                            'warehouse_id' => $warehouse_id,
                            'orders' => $orders
                        ]
                    );
                }
            }
        }
        if(Tools::isSubmit('submitPrintLabel'))
        {

            $cApi = $this->module->getModuleService('MjvpApi');
            $cDb = $this->module->getModuleService('MjvpDb');

            $id_order = Tools::getValue('orderBox')[0];
            $labels_numbers = json_decode($cDb->getOrderValue('labels_numbers', ['id_order' => $id_order]), true);
            $cApi->printLabel($labels_numbers);
        }
        if(Tools::isSubmit('submitBulkprintLabelsorder'))
        {
            $orders = Tools::getValue('orderBox');
            $this->module->bulkActionPrintLabels($orders);
        }
    }

    public function parseLabelNumbers($labels)
    {
        return implode(', ', json_decode($labels, true));
    }

    /**
     * @throws SmartyException
     */
    private function displayMenu()
    {
        $menu = array(
            array(
                'label' => $this->module->l('Ready Orders'),
                'url' => $this->context->link->getAdminLink($this->controller_name),
                'active' => Tools::getValue('controller') == $this->controller_name
            ),
            array(
                'label' => $this->module->l('Generated Manifests'),
                'url' => $this->context->link->getAdminLink('AdminVenipakManifests'),
                'active' => false
            ),
            array(
                'label' => $this->module->l('Track all orders'),
                'url' => $this->context->link->getAdminLink($this->controller_name),
                'icon' => 'icon-truck',
                'active' => false,
                'class' => 'track-orders'
            )
        );

        $warehouses = MjvpWarehouse::getWarehouses();

        $this->context->smarty->assign(array(
            'moduleMenu' => $menu,
            'warehouses' => json_encode($warehouses),
        ));

        return $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/admin/manifest_menu.tpl');
    }

    public function transStatus($status)
    {
        if(isset(self::$status_trans[$status]))
            return self::$status_trans[$status];
        return $status;
    }

}
