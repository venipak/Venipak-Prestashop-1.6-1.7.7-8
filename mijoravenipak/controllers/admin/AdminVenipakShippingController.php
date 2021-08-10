<?php

class AdminVenipakShippingController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    private $total_orders = 0;

    /**
     * AdminOmnivaltShippingStoresController class constructor
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
        $this->toolbar_title = $this->l('Venipak Manifest - Ready Orders');
        $this->_select = '
            oc.tracking_number as label_number,
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            a.id_order AS id_print,
            a.id_order AS id_label_print
		';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'order_carrier` oc ON (oc.`id_order` = a.`id_order`)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'carrier` carrier ON (carrier.`id_carrier` = a.`id_carrier`)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')
    ';

        $this->_sql = '
      SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders` a
      WHERE 1 ' . Shop::addSqlRestrictionOnLang('a');
        $this->total_orders = DB::getInstance()->getValue($this->_sql);

        $this->_where = ' AND carrier.id_reference IN ('
            . Configuration::get('MJVP_COURIER_ID_REFERENCE') . ','
            . Configuration::get('MJVP_PICKUP_ID_REFERENCE') . ")";
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->l('Select shop');
        } else {
            $this->content .= $this->displayMenu();
            $this->readyOrdersList();
        }
    }

    protected function readyOrdersList()
    {
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'search' => false,
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
            'osname' => array(
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
            'label_number' => array(
                'type' => 'text',
                'title' => $this->l('Tracking number'),
                'havingFilter' => false,
            )
        );

        $this->fields_list['id_label_print'] = array(
            'title' => $this->l('PDF'),
            'align' => 'text-center',
            'search' => false,
            'orderby' => false,
            'callback' => 'labelBtn',
        );

        $this->actions = array('none');

        $this->bulk_actions = array(
            'registerItella' => array(
                'text' => $this->l('Generate Labels'),
                'icon' => 'icon-save'
            ),
            'generateItellaLabel' => array(
                'text' => $this->l('Print Labels'),
                'icon' => 'icon-tag'
            ),
            'printItellaManifest' => array(
                'text' => $this->l('Print Manifest'),
                'icon' => 'icon-file-pdf-o'
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

    /**
     * @throws SmartyException
     */
    private function displayMenu()
    {
        $menu = array(
            array(
                'label' => $this->l('Ready Orders'),
                'url' => $this->context->link->getAdminLink($this->controller_name, true),
                'active' => Tools::getValue('controller') == $this->controller_name
            ),
//            array(
//                'label' => $this->l('Generated Manifests'),
//                'url' => $this->context->link->getAdminLink('AdminItellashippingItellaManifestDone', true),
//                'active' => false
//            )
        );

        ItellaShipping::checkForClass('ItellaStore');
        $storeObj = new ItellaStore();
        $stores = ItellaStore::getStores();

        $this->context->smarty->assign(array(
            'moduleMenu' => $menu,
            'stores' => json_encode($stores),
            'call_url' => false // dont need js handling call courier functionality
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'itellashipping/views/templates/admin/manifest_menu.tpl');
    }

    public function getShopNameById($id)
    {
        $shop = new Shop($id);
        return $shop->name;
    }

    public function labelBtn($id)
    {
        $order = new Order((int) $id);
        if (!$order->getWsShippingNumber()) {
            return '<span class="btn-group-action">
                <span class="btn-group">
                  <a class="btn btn-default" target="_blank" href="' . self::$currentIndex . '&token=' . $this->token . '&submitBulkregisterItellaorder' . '&orderBox[]=' . $id . '"><i class="icon-save"></i>&nbsp;' . $this->l('Generate Label') . '
                  </a>
                </span>
            </span>';
        }
        return '<span class="btn-group-action">
                <span class="btn-group">
                    <a class="btn btn-default" target="_blank" href="' . self::$currentIndex . '&token=' . $this->token . '&submitBulkgenerateItellaLabelorder' . '&orderBox[]=' . $id . '"><i class="icon-tag"></i>&nbsp;' . $this->l('Label') . '
                    </a>
                
                    <a class="btn btn-default" href="' . self::$currentIndex . '&token=' . $this->token . '&submitBulkprintItellaManifestorder' . '&orderBox[]=' . $id . '"><i class="icon-file-pdf-o"></i>&nbsp;' . $this->l('Manifest') . '
                    </a>
                </span>
            </span>';
    }

}