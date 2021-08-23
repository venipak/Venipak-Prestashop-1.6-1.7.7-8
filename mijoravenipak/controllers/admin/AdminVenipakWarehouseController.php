<?php

include __DIR__ . "/../../classes/MjvpWarehouse.php";

class AdminVenipakWarehouseController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    /**
     * AdminVenipakWarehouseController class constructor
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function __construct()
    {
        $this->list_no_link = true;
        $this->_orderBy = 'id';
        $this->className = 'MjvpWarehouse';
        $this->table = 'mjvp_warehouse';
        $this->list_id = 'mjvp_warehouse';
        $this->identifier = 'id';
        $this->_select = ' mw.id as arrival_time, ';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'mjvp_warehouse` mw ON (a.`id` = mw.`id`)
    ';

        parent::__construct();
        $this->toolbar_title = $this->module->l('Venipak Warehouses');
        $this->prepareWarehouseList();
    }

    protected function prepareWarehouseList()
    {
        $this->fields_list = array(
            'id' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'name' => array(
                'title' => $this->module->l('Warehouse name'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'company_code' => array(
                'type' => 'text',
                'title' => $this->module->l('Company code'),
                'align' => 'center',
            ),
            'contact' => array(
                'title' => $this->module->l('Full name of contact person'),
                'type' => 'text',
            ),
            'country_code' => array(
                'title' => $this->module->l('Country code'),
                'type' => 'text',
            ),
            'city' => array(
                'type' => 'text',
                'title' => $this->module->l('City'),
            ),
            'address' => array(
                'type' => 'text',
                'title' => $this->module->l('Warehouse address'),
            ),
            'zip_code' => array(
                'type' => 'text',
                'title' => $this->module->l('Zip code of warehouse'),
            ),
            'phone' => array(
                'type' => 'text',
                'title' => $this->module->l('Contact phone number'),
            ),
            'default_on' => array(
                'type' => 'bool',
                'title' => $this->module->l('Default'),
                'active' => 'status',
            ),
            'arrival_time' => array(
                'type' => 'text',
                'title' => $this->module->l('Carrier arrival'),
                'search' => false,
                'callback' => 'getArrivalTime'
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected', array(), 'Admin.Notifications.Info'),
                'icon' => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(), 'Admin.Notifications.Info'),
            ),
        );

        $this->actions = array('edit', 'delete');
    }

    public function renderForm()
    {
        $this->table = 'mjvp_warehouse';
        $this->identifier = 'id';

        $countries = array(
            array(
                'id' => 'LT',
                'name' => $this->trans('Lithuania', array(), 'Admin.Global'),
            ),
            array(
                'id' => 'LV',
                'name' => $this->trans('Latvia', array(), 'Admin.Global'),
            ),
            array(
                'id' => 'EE',
                'name' => $this->trans('Estonia', array(), 'Admin.Global'),
            ),
        );


        $this->fields_form = array(
            'legend' => array(
                'title' => $this->trans('Warehouse', array(), 'Admin.Catalog.Feature'),
                'icon' => 'icon-info-sign',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->trans('Warehouse name', array(), 'Admin.Global'),
                    'name' => 'name',
                    'filter_key' => 'a!name',
                    'required' => true,
                    'col' => '3',
                    'hint' => $this->trans('Enter the warehouse name', array(), 'Admin.Catalog.Help') . '&nbsp;' . $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info') . ' <>;=#{}',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Company code', array(), 'Admin.Catalog.Feature'),
                    'name' => 'company_code',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->trans('Enter the company code', array(), 'Admin.Catalog.Help') . '&nbsp;' . $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info') . ' <>;=#{}',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Contact person', array(), 'Admin.Catalog.Feature'),
                    'name' => 'contact',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->trans('Enter contact person\'s full name.', array(), 'Admin.Catalog.Help'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->trans('Country code', array(), 'Admin.Catalog.Feature'),
                    'name' => 'country_code',
                    'required' => true,
                    'col' => '2',
                    'options' => array(
                        'query' => $countries,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'hint' => $this->trans('Select the country code.', array(), 'Admin.Catalog.Help'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('City', array(), 'Admin.Catalog.Feature'),
                    'name' => 'city',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->trans('Enter warehouse city.', array(), 'Admin.Catalog.Help'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Address', array(), 'Admin.Catalog.Feature'),
                    'name' => 'address',
                    'required' => true,
                    'col' => '2',
                    'hint' => $this->trans('Enter warehouse address.', array(), 'Admin.Catalog.Help'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Zip code', array(), 'Admin.Catalog.Feature'),
                    'name' => 'zip_code',
                    'required' => true,
                    'placeholder' => $this->trans('e.g 55555', array(), 'Admin.Global'),
                    'col' => '2',
                    'hint' => $this->trans('Enter warehouse zip code.', array(), 'Admin.Catalog.Help'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Phone', array(), 'Admin.Catalog.Feature'),
                    'name' => 'phone',
                    'required' => true,
                    'col' => '2',
                    'placeholder' => $this->trans('e.g +3706666666', array(), 'Admin.Global'),
                    'hint' => $this->trans('Enter warehouse phone number.', array(), 'Admin.Catalog.Help'),
                ),
                array(
                    'type' => 'checkbox',
                    'name' => 'default',
                    'values' => array(
                        'query' => array(
                            array('id' => 'on', 'name' => $this->trans('Set as default warehouse.', array(), 'Admin.Shopparameters.Feature'), 'val' => '1'),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                )
            ),
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->trans('Shop association', array(), 'Admin.Global'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->fields_form['submit'] = array(
            'title' => $this->trans('Save', array(), 'Admin.Actions'),
        );

        return parent::renderForm();
    }

    /**
     * Change object status (active, inactive).
     *
     * @return ObjectModel|false
     *
     * @throws PrestaShopException
     */
    public function processStatus()
    {
        $id_warehouse = (int) Tools::getValue('id');
        $current_status = (int) Db::getInstance()->getValue('SELECT `default_on` FROM ' . _DB_PREFIX_ . 'mjvp_warehouse WHERE `id` = ' . $id_warehouse);

        $result = Db::getInstance()->update('mjvp_warehouse', ['default_on' => 0]);
        if($current_status)
            $result = Db::getInstance()->update('mjvp_warehouse', ['default_on' => 0], 'id = ' . $id_warehouse);
        else
            $result = Db::getInstance()->update('mjvp_warehouse', ['default_on' => 1], 'id = ' . $id_warehouse);

        return $result;
    }

    public function beforeAdd($object)
    {
        // If new warehouse will be default, reset the current default.
        if($object->default_on)
            Db::getInstance()->update('mjvp_warehouse', ['default_on' => 0], 'default_on = 1');
    }

    // If warehouse is updated to default, reset previous default.
    public function afterUpdate($object)
    {
        if($object->default_on)
        {
            Db::getInstance()->update('mjvp_warehouse', ['default_on' => 0], 'id != ' . $object->id);
        }
    }

    // Add default_on value, if checkbox is not selected.
    public function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        if(!Tools::isSubmit('default_on'))
        {
            $object->default_on = 0;
        }
    }

    // Fix issue with checkbox not being checked, when editing default warehouse.
    public function getFieldsValue($object)
    {
        $fields_value = parent::getFieldsValue($object);

        if($object->id)
        {
            $fields_value['default_on'] = $object->default_on;
        }

        return $fields_value;
    }

    public function getArrivalTime($id_warehouse)
    {
        $manifest_rows = Db::getInstance()->executeS("SELECT arrival_date_to, arrival_date_from FROM " . _DB_PREFIX_ . "mjvp_manifest
            WHERE id_warehouse = " . $id_warehouse);

        foreach ($manifest_rows as $manifest_row)
        {
            date_default_timezone_set('Europe/Vilnius');
            $time_data = [
                $manifest_row['arrival_date_to'],
                $manifest_row['arrival_date_from'],
            ];
            if(count($time_data) < 2)
                return $this->module->l('No arrival today.');
            $date_arrival_from = new DateTime($time_data[0]);
            $date_arrival_to = new DateTime($time_data[1]);
            $now = new DateTime('NOW');
            if($date_arrival_to > $now)
            {
                $date_arrival_from->setTime(0,0,0);
                $now->setTime(0,0,0);
                $date_diff = $date_arrival_from->diff($now);
                if($date_diff->d == 0)
                {
                    $from_time = date('H:i', strtotime($time_data[0]));
                    $to_time = date('H:i', strtotime($time_data[1]));
                    return $from_time . ' - ' . $to_time;
                }
            }
        }

        return $this->module->l('No arrival today.');
    }
}
