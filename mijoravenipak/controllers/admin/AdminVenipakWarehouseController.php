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
        $this->_orderBy = 'id_warehouse';
        $this->table = 'mjvp_warehouses';
        $this->list_id = 'mjvp_warehouses';
        $this->identifier = 'id_warehouse';
        $this->_defaultOrderBy = 'position';
        parent::__construct();
        $this->toolbar_title = $this->l('Venipak Warehouses');

        $this->readyWarehouseList();
    }

    protected function readyWarehouseList()
    {
        $this->fields_list = array(
            'id_warehouse' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
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
            ),
            'default' => array(
                'type' => 'bool',
                'title' => $this->l('Default'),
                'active' => 'status',
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

    /**
     * AdminController::init() override.
     *
     * @see AdminController::init()
     */
    public function init()
    {
        if (Tools::isSubmit('updateattribute')) {
            $this->display = 'editAttributes';
        } elseif (Tools::isSubmit('submitAddattribute')) {
            $this->display = 'editAttributes';
        } elseif (Tools::isSubmit('addmjvp_warehouses')) {
            $this->display = 'add';
        }
        parent::init();
    }

    public function initContent()
    {
        if ($this->display == 'edit' || $this->display == 'add') {
            $this->content .= $this->renderForm();
        } elseif ($this->display == 'editAttributes') {
            if (!$this->object = new Attribute((int)Tools::getValue('id_attribute'))) {
                return;
            }
            $this->content .= $this->renderFormAttributes();
        } elseif ($this->display != 'view' && !$this->ajax) {
            $this->content .= $this->renderList();
            $this->content .= $this->renderOptions();
        } elseif ($this->display == 'view' && !$this->ajax) {
            $this->content = $this->renderView();
        }

        $this->context->smarty->assign(array(
            'table' => $this->table,
            'current' => self::$currentIndex,
            'token' => $this->token,
            'content' => $this->content,
        ));
    }

    public function renderForm()
    {
        $this->table = 'mjvp_warehouses';
        $this->identifier = 'id_warehouse';

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
                    'name' => 'warehouse_name',
                    'filter_key' => 'a!warehouse_name',
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

        if (Tools::isSubmit('updatemjvp_warehouses')) {
            $this->fields_form['input'][] =
                [
                    'type' => 'hidden',
                    'name' => 'update',
                    'value' => 1
                ];
            $this->fields_form['input'][] =
                [
                    'type' => 'hidden',
                    'name' => 'id_warehouse',
                    'value' => (int)Tools::getValue('id_warehouse')
                ];
        }

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

    public function postProcess()
    {
        if (Tools::getValue('submitAddmjvp_warehouses') || Tools::isSubmit('deletemjvp_warehouses') || Tools::isSubmit('updatemjvp_warehouses')
            || Tools::isSubmit('submitBulkdeletemjvp_warehouses')) {
            if (true !== $this->access('edit')) {
                $this->errors[] = $this->trans('You do not have permission to edit this.', array(), 'Admin.Notifications.Error');
                return;
            }

            if (Tools::isSubmit('submitBulkdeletemjvp_warehouses')) {
                $this->processBulkDelete();
            }
        }
        parent::postProcess();
    }

    /**
     * Override processSave to change SaveAndStay button action.
     *
     * @see classes/AdminControllerCore::processUpdate()
     */
    public function processSave()
    {
        $data = [
            'warehouse_name' => pSQL(Tools::getValue('warehouse_name')),
            'company_code' => pSQL(Tools::getValue('company_code')),
            'contact' => pSQL(Tools::getValue('contact')),
            'country_code' => pSQL(Tools::getValue('country_code')),
            'city' => pSQL(Tools::getValue('city')),
            'address' => pSQL(Tools::getValue('address')),
            'zip_code' => pSQL(Tools::getValue('zip_code')),
            'phone' => pSQL(Tools::getValue('phone')),
            'default' => (int)pSQL(Tools::getValue('default_on'))
        ];
        $update = false;
        if ($this->validateData($data)) {
            if (Tools::getValue('update')) {
                $warehouse_id = (int)Tools::getValue('id_warehouse');
                $update = true;
                $result = Db::getInstance()->update('mjvp_warehouses', $data, 'id_warehouse = ' . pSQL($warehouse_id));
            } else {
                $warehouse_id = Db::getInstance()->Insert_ID();
                $result = Db::getInstance()->insert('mjvp_warehouses', $data);
            }

            if ($result) {
                // Reset default
                if (Tools::getValue('default_on')) {
                    Db::getInstance()->update('mjvp_warehouses', ['default' => 0], 'id_warehouse != ' . $warehouse_id);
                }
                if ($update)
                    $this->confirmations[] = $this->trans('Warehouse updated successfully.', [], 'Admin.Notifications.Success');
                else
                    $this->confirmations[] = $this->trans('Warehouse created successfully.', [], 'Admin.Notifications.Success');
            }
        }
    }

    public function processBulkDelete()
    {
        if (!$this->boxes || empty($this->boxes)) {
            $this->errors[] = $this->trans('You must select at least one element to delete.', array(), 'Admin.Notifications.Error');
            return false;
        }
        $result = Db::getInstance()->delete('mjvp_warehouses', 'id_warehouse IN (' . implode(',', $this->boxes) . ")");
        if ($result) {
            $this->confirmations[] = $this->trans('Selected warehouses deleted successfully.', [], 'Admin.Notifications.Success');
        } else {
            $this->errors[] = $this->trans('You must select at least one element to delete.', array(), 'Admin.Notifications.Error');
        }
    }

    public function processDelete()
    {
        $id_warehouse = (int)Tools::getValue('id_warehouse');
        $result = Db::getInstance()->delete('mjvp_warehouses', 'id_warehouse = ' . $id_warehouse);
        if ($result) {
            $this->confirmations[] = $this->trans('Warehouse deleted successfully.', [], 'Admin.Notifications.Success');
        } else {
            $this->errors[] = $this->trans('Could not delete a warehouse.', [], 'Admin.Notifications.Success');
        }
    }

    public function getFieldsValue($obj)
    {
        if (Tools::isSubmit('updatemjvp_warehouses')) {
            $values = Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "mjvp_warehouses WHERE id_warehouse = " . pSQL(Tools::getValue('id_warehouse')));
            $values['update'] = 1;
            if ($values['default']) {
                $values['default_on'] = 1;
            }
            return $values;
        } elseif (Tools::isSubmit('addmjvp_warehouses'))
            return parent::getFieldsValue($obj);
    }

    public function validateData($data)
    {
        if (!Validate::isName($data['warehouse_name']) || !$data['warehouse_name']) {
            $this->errors[] = $this->trans('Warehouse name is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isInt($data['company_code']) || !$data['company_code']) {
            $this->errors[] = $this->trans('Company code is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isName($data['contact']) || !$data['contact']) {
            $this->errors[] = $this->trans('Contact is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isLangIsoCode($data['country_code'])) {
            $this->errors[] = $this->trans('Country code is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isCityName($data['city']) || !$data['city']) {
            $this->errors[] = $this->trans('City is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isAddress($data['address']) || !$data['address']) {
            $this->errors[] = $this->trans('Address is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isZipCodeFormat($data['zip_code']) || !$data['zip_code']) {
            $this->errors[] = $this->trans('Zip code is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isPhoneNumber($data['phone']) || !$data['phone']) {
            $this->errors[] = $this->trans('Phone number is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!Validate::isInt($data['default'])) {
            $this->errors[] = $this->trans('Default value is invalid.', [], 'Admin.Notifications.Error');
        }
        if (!empty($this->errors))
            return false;
        return true;
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
        $id_warehouse = (int) Tools::getValue('id_warehouse');
        $current_status = (int) Db::getInstance()->getValue('SELECT `default` FROM ' . _DB_PREFIX_ . 'mjvp_warehouses WHERE `id_warehouse` = ' . $id_warehouse);

        $result = Db::getInstance()->update('mjvp_warehouses', ['default' => 0]);
        if($current_status)
            $result = Db::getInstance()->update('mjvp_warehouses', ['default' => 0], 'id_warehouse = ' . $id_warehouse);
        else
            $result = Db::getInstance()->update('mjvp_warehouses', ['default' => 1], 'id_warehouse = ' . $id_warehouse);

        return $result;
    }

}
