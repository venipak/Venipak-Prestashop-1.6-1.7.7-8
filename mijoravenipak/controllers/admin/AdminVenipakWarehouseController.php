<?php

class AdminVenipakWarehouseController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    /**
     * AdminOmnivaltShippingStoresController class constructor
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function __construct()
    {
        $this->list_no_link = true;
        $this->table = 'mjvp_warehouses';
        $this->identifier = 'id_warehouse';
        parent::__construct();
        $this->toolbar_title = $this->l('Venipak Warehouses');

        $this->readyWarehouseList();
    }

    protected function readyWarehouseList()
    {
        $this->fields_list = array(
            'warehouse_name' => array(
                'title' => $this->l('Warehouse name'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'company_code' => array(
                'type' => 'text',
                'title' => $this->l('Company code'),
                'align' => 'center',
            ),
            'contact' => array(
                'title' => $this->l('Full name of contact person'),
                'type' => 'text',
            ),
            'country_code' => array(
                'title' => $this->l('Country code'),
                'type' => 'text',
            ),
            'city' => array(
                'type' => 'text',
                'title' => $this->l('City'),
            ),
            'address' => array(
                'type' => 'text',
                'title' => $this->l('Warehouse address'),
            ),
            'zip_code' => array(
                'type' => 'text',
                'title' => $this->l('Zip code of warehouse'),
            ),
            'phone' => array(
                'type' => 'text',
                'title' => $this->l('Contact phone number'),
            )
        );
    }
}
