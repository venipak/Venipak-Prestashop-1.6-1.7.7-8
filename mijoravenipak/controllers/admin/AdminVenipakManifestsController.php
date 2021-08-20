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
        $this->_select = ' a.manifest_id, (SELECT COUNT(*) FROM `'
            . _DB_PREFIX_ . 'mjvp_orders` o WHERE o.manifest_id = a.manifest_id) as manifest_total';

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
            'call_url' => $this->context->link->getAdminLink($this->controller_name),
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
                'title' => $this->l('Date'),
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
        return '<span class="btn-group-action">
                <span class="btn-group">
                    <a target="_blank" class="btn btn-default" href="' . self::$currentIndex . '&token=' . $this->token . '&manifestdone&ajax=1' . '&print' . $this->table . '&id=' . $id . '"><i class="icon-file-pdf-o"></i>&nbsp;' . $this->l('Print Manifest') . '
                    </a>
                </span>
            </span>

            <span class="btn-group-action">
                <span class="btn-group">
                    <a data-manifest="' . $id . '" class="btn btn-default" href="#"><i class="icon-file-pdf-o"></i>&nbsp;' . $this->l('Call Courier') . '
                    </a>
                </span>
            </span>';
    }

    public function postProcess()
    {
        if (Tools::isSubmit('printmjvp_manifest')) {
            MijoraVenipak::checkForClass('MjvpApi');
            MijoraVenipak::checkForClass('MjvpManifest');
            MijoraVenipak::checkForClass('MjvpDb');

            $cApi = new MjvpApi();
            $cDb = new MjvpDb();
            $id_manifest = Tools::getValue('id');
            $manifest_number = json_decode($cDb->getManifestValue('manifest_id', ['id' => $id_manifest]), true);
            $cApi->printList($manifest_number);
        }
    }
}