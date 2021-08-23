<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MijoraVenipak extends CarrierModule
{
    const CONTROLLER_SHIPPING = 'AdminVenipakShipping';
    const CONTROLLER_WAREHOUSE = 'AdminVenipakWarehouse';
    const CONTROLLER_ADMIN_AJAX = 'AdminVenipakshippingAjax';
    const CONTROLLER_ADMIN_MANIFEST = 'AdminVenipakManifests';
    const EXTRA_FIELDS_SIZE = 10;
    const CARRIER_CALL_MINIMUM_DIFFERENCE = 2; // hours

    /**
     * Debug mode activation, which writes operations to log files
     */
    public static $debug_mode = false;

    /**
     * List of carriers
     *
     * type - Carrier type (courier/pickup)
     * id_name - Name for ID in database
     * reference_name - Name for Reference in database
     * title - Carrier title
     * image - Carrier image file in module images directory
     */
    public static $_carriers = array(
        'courier' => array(
            'type' => 'courier',
            'id_name' => 'MJVP_COURIER_ID',
            'reference_name' => 'MJVP_COURIER_ID_REFERENCE',
            'title' => 'Venipak courier',
            'image' => '',
        ),
        'pickup' => array(
            'type' => 'pickup',
            'id_name' => 'MJVP_PICKUP_ID',
            'reference_name' => 'MJVP_PICKUP_ID_REFERENCE',
            'title' => 'Venipak pickup point',
            'image' => '',
        ),
    );

    /**
     * Main module directory path
     */
    public static $_moduleDir = _PS_MODULE_DIR_ . 'mijoravenipak/';
    public static $_labelPdfDir = _PS_MODULE_DIR_ . 'mijoravenipak/pdf/labels/';
    public static $_manifestPdfDir = _PS_MODULE_DIR_ . 'mijoravenipak/pdf/manifests/';

    /**
     * Default countries list for pickup points
     */
    public static $_defaultPickupCountries = array('lt', 'lv', 'ee', 'pl');

    /**
     * COD modules
     */
    public static $_codModules = array('ps_cashondelivery');

    public $deliveryTimes = [];

    public static $_order_additional_info = array(
       'door_code' => '',
       'cabinet_number' => '',
       'warehouse_number' => '',
       'delivery_time' => 0,
        'carrier_call' => 0
    );

    /**
     * Global constants list
     */
    public static $_globalConstants = array(
        'last_upd_countries' => 'MJVP_LAST_UPDATE_COUNTRIES',
        'last_upd_terminals' => 'MJVP_LAST_UPDATE_TERMINALS',
    );

    /**
     * Classes use in the module
     */
    private static $_classMap = array(
        'MjvpHelper' => 'classes/MjvpHelper.php',
        'MjvpApi' => 'classes/MjvpApi.php',
        'MjvpModuleConfig' => 'classes/MjvpModuleConfig.php',
        'MjvpFiles' => 'classes/MjvpFiles.php',
        'MjvpDb' => 'classes/MjvpDb.php',
        'MjvpCart' => 'classes/MjvpCart.php',
        'MjvpWarehouse' => 'classes/MjvpWarehouse.php',
        'MjvpManifest' => 'classes/MjvpManifest.php',
        'MjvpVenipak' => 'classes/MjvpVenipak.php', //Temporary
    );

    /**
     * List of hooks
     */
    protected $_hooks = array(
        'header',
        'actionOrderGridDefinitionModifier',
        'actionAdminOrdersListingFieldsModifier',
        'displayCarrierExtraContent',
        'updateCarrier',
        'displayAdminOrder',
        'actionValidateStepComplete',
        'actionValidateOrder',
        'actionAdminControllerSetMedia'
    );

    /**
     * List of fields keys in module configuration
     */
    public $_configKeys = array(
        'API' => array(
            'username' => 'MJVP_API_USER',
            'password' => 'MJVP_API_PASS',
            'id' => 'MJVP_API_ID',
        ),
        'SHOP' => array(
            'shop_name' => 'MJVP_SHOP_NAME',
            'shop_contact' => 'MJVP_SHOP_CONTACT',
            'company_code' => 'MJVP_SHOP_COMPANY_CODE',
            'shop_country_code' => 'MJVP_SHOP_COUNTRY_CODE',
            'shop_city' => 'MJVP_SHOP_CITY',
            'shop_address' => 'MJVP_SHOP_ADDRESS',
            'shop_postcode' => 'MJVP_SHOP_POSTCODE',
            'shop_phone' => 'MJVP_SHOP_PHONE',
            'shop_email' => 'MJVP_SHOP_EMAIL',
        ),
        'COURIER' => array(
            'door_code' => 'MJVP_COURIER_DOOR_CODE',
            'warehouse_number' => 'MJVP_COURIER_WAREHOUSE_NUMBER',
            'cabinet_number' => 'MJVP_COURIER_CABINET_NUMBER',
            'delivery_time' => 'MJVP_COURIER_DELIVERY_TIME',
            'call_before_delivery' => 'MJVP_COURIER_CALL_BEFORE_DELIVERY',
        ),
        'LABEL' => array(
            'label_size' => 'MJVP_LABEL_SIZE',
            'label_counter' => 'MJVP_COUNTER_PACKS',
        ),
        'ADVANCED' => array(
            'carrier_disable_passphrase' => 'MJVP_CARRIER_DISABLE_PASSPHRASE',
        ),
    );

    public $_configKeysOther = array(
        'counter_manifest' => array(
            'key' => 'MJVP_COUNTER_MANIFEST',
            'default_value' => array('counter' => 0, 'date' => ''),
        ),
        'counter_packs' => array(
            'key' => 'MJVP_COUNTER_PACKS',
            'default_value' => 0,
        ),
        'label_size' => array(
            'key' => 'MJVP_LABEL_SIZE',
            'default_value' => 'a6',
        ),
        'last_manifest_id' => array(
            'key' => 'MJVP_LAST_MANIFEST_ID',
            'default_value' => 0,
        ),
    );

    /**
     * Fields names and required
     */
    private function getConfigField($section_id, $config_key)
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        if ($section_id == 'SHOP') {
            return array('name' => str_replace('_', ' ', $config_key), 'required' => true);
        }

        if ($section_id == 'LABEL') {
            if ($config_key == $cModuleConfig->getConfigKey('label_counter', $section_id)) {
                return array(
                    'name' => $this->l('Last pack number'),
                    'required' => false,
                    'type' => 'number',
                    'min' => Configuration::get($config_key),
                    'max' => 9999999,
                );
            }
        }

        if ($section_id == 'ADVANCED') {
            if ($config_key == $cModuleConfig->getConfigKey('carrier_disable_passphrase', $section_id)) {
                return array(
                    'name' => $this->l('Carrier disable passphrase'),
                    'validate' => 'isGenericName',
                );
            }
        }

        return array('name' => 'ERROR_' . $config_key, 'required' => false);
    }

    public static $_order_states = array(
        'order_state_ready' => array(
            'key' => 'MJVP_ORDER_STATE_READY',
            'color' => '#FCEAA8',
            'lang' => array(
                'en' => 'Venipak shipment ready',
                'lt' => 'Venipak siunta paruošta',
            ),
        ),
        'order_state_error' => array(
            'key' => 'MJVP_ORDER_STATE_ERROR',
            'color' => '#F24017',
            'lang' => array(
                'en' => 'Error on Venipak shipment',
                'lt' => 'Klaida Venipak siuntoje',
            ),
        ),
    );

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->name = 'mijoravenipak';
        $this->tab = 'shipping_logistics';
        $this->version = '0.1.0';
        $this->author = 'mijora.lt';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => '1.7.6');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Venipak Shipping');
        $this->description = $this->l('Shipping module for Venipak delivery method');
        $this->available_countries = array('LT', 'LV', 'EE', 'PL');
        $this->countries_names = array('LT' => 'Lithuania', 'LV' => 'Latvia', 'EE' => 'Estonia', 'PL' => 'Poland');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->deliveryTimes = [
            'nwd' => $this->l('Anytime'),
            'nwd10' => $this->l('Until 10:00'),
            'nwd12' => $this->l('Until 12:00'),
            'nwd8_14' => $this->l('8:00-14:00'),
            'nwd14_17' => $this->l('14:00-17:00'),
            'nwd18_22' => $this->l('18:00-22:00'),
        ];
    }

    /**
     * Module installation function
     */
    public function install()
    {
        $this->registerTabs();

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        if (!parent::install()) {
            return false;
        }

        foreach ($this->_hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->_errors[] = $this->l('Failed to install hook') . ' ' . $hook . '.';
                return false;
            }
        }

        if (!$this->createDbTables()) {
            $this->_errors[] = $this->l('Failed to create tables.');
            return false;
        }

        if (!$this->addOrderStates()) {
            $this->_errors[] = $this->l('Failed to order states.');
            return false;
        }

        foreach (self::$_carriers as $carrier) {
            if (!$this->createCarrier($carrier['id_name'], $carrier['title'], $carrier['image'])) {
                $this->_errors[] = $this->l('Failed to create carrier') . ' ' . $carrier['id_name'] . '.';
                return false;
            }
        }

        if (!$this->createOtherDbRecords()) {
            $this->_errors[] = $this->l('Failed to create other database records.');
            return false;
        }

        $this->getVenipakTerminals();

        return true;
    }

    /**
     * Provides list of Admin controllers info
     *
     * @return array BackOffice Admin controllers
     */
    private function getModuleTabs()
    {
        return array(
            self::CONTROLLER_SHIPPING => array(
                'title' => $this->l('Venipak Orders'),
                'parent_tab' => (int) Tab::getIdFromClassName('AdminParentShipping')
            ),
            self::CONTROLLER_WAREHOUSE => array(
                'title' => $this->l('Venipak Warehouses'),
                'parent_tab' => (int) Tab::getIdFromClassName('AdminParentShipping')
            ),
            self::CONTROLLER_ADMIN_AJAX => array(
                'title' => $this->l('VenipakAdminAjax'),
                'parent_tab' => -1
            ),
            self::CONTROLLER_ADMIN_MANIFEST => array(
                'title' => $this->l('Venipak Manifests'),
                'parent_tab' => -1
            )
        );
    }

    /**
     * Registers module Admin tabs (controllers)
     */
    private function registerTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true; // Nothing to register
        }

        foreach ($tabs as $controller => $tabData) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $controller;
            $tab->name = array();
            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $tabData['title'];
            }

            $tab->id_parent = $tabData['parent_tab'];
            $tab->module = $this->name;
            if (!$tab->save()) {
                $this->displayError($this->l('Error while creating tab ') . $tabData['title']);
                return false;
            }
        }
        return true;
    }

    /**
     * Add Venipak order states
     */
    private function addOrderStates()
    {

        foreach (self::$_order_states as $os)
        {
            $order_state = (int)Configuration::get($os['key']);
            $order_status = new OrderState($order_state, (int)Context::getContext()->language->id);

            if (!$order_status->id || !$order_state) {
                $orderState = new OrderState();
                $orderState->name = array();
                foreach (Language::getLanguages() as $language) {
                    if (strtolower($language['iso_code']) == 'lt')
                        $orderState->name[$language['id_lang']] = $os['lang']['lt'];
                    else
                        $orderState->name[$language['id_lang']] = $os['lang']['en'];
                }
                $orderState->send_email = false;
                $orderState->color = $os['color'];
                $orderState->hidden = false;
                $orderState->delivery = false;
                $orderState->logable = true;
                $orderState->invoice = false;
                $orderState->unremovable = false;
                if ($orderState->add()) {
                    Configuration::updateValue($os['key'], $orderState->id);
                }
                else
                    return false;
            }
        }
        return true;
    }

    /**
     * Deletes module Admin controllers
     * Used for module uninstall
     *
     * @return bool Module Admin controllers deleted successfully
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function deleteTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true; // Nothing to remove
        }

        foreach (array_keys($tabs) as $controller) {
            $idTab = (int) Tab::getIdFromClassName($controller);
            $tab = new Tab((int) $idTab);

            if (!Validate::isLoadedObject($tab)) {
                continue; // Nothing to remove
            }

            if (!$tab->delete()) {
                $this->displayError($this->l('Error while uninstalling tab') . ' ' . $tab->name);
                return false;
            }
        }

        return true;
    }

    /**
     * Module uninstall function
     */
    public function uninstall()
    {
        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();

        $cDb->deleteTables();
        $this->deleteTabs();

        foreach (self::$_carriers as $carrier) {
            if (!$this->deleteCarrier($carrier['id_name'])) {
                $this->_errors[] = $this->l('Failed to delete carrier') . ' ' . $carrier['id_name'] . '.';
                return false;
            }
        }

        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Compute the shipping price depending on the ranges that were set in the back office
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        if($params instanceof Cart)
        {
            $cart = $params;
            if($this->checkCarrierDisablePassphrase($cart))
                return false;
        }
        return $shipping_cost;
    }

    /**
     * Check if cart contains products, whose description contains @carrier_disable_passphrase
     */
    private function checkCarrierDisablePassphrase($cart)
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();
        $carrier_disable_passphrase = Configuration::get($cModuleConfig->getConfigKey('carrier_disable_passphrase', 'ADVANCED'));
        if($carrier_disable_passphrase)
        {

            $cart_products = $cart->getProducts();
            $id_lang = $this->context->language->id;
            foreach ($cart_products as $product)
            {
                // Cart products don't have description, so get it.
                $product_description = Db::getInstance()->getValue(
                    (new DbQuery())
                        ->select('description')
                        ->from('product_lang')
                        ->where('`id_product` = ' . $product['id_product'] . ' AND `id_lang` = ' . $id_lang)
                );

                /**
                 * Carrier cannot be used for the cart, if cart contains any product, whose description contains @carrier_disable_passphrase
                 */
                if(strpos($product_description, $carrier_disable_passphrase) !== false)
                    return true;
            }

        }
        return false;
    }

    /**
     * Compute the shipping price without using the ranges
     */
    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    /**
     * Creates a shipping method
     */
    public function createCarrier($key, $name, $image = '')
    {
        $carrier = new Carrier();
        $carrier->name = $name;
        $carrier->delay[Configuration::get('PS_LANG_DEFAULT')] = '1-2 business days';
        $carrier->is_module = true;
        $carrier->external_module_name = $this->name;
        $carrier->need_range = true;
        $carrier->range_behavior = 0;
        $carrier->shipping_external = true;
        $carrier->shipping_handling = false;
        $carrier->url = '';
        $carrier->active = true;
        $carrier->deleted = 0;

        if (!$carrier->add()) {
            return false;
        }

        $groups = Group::getGroups(true);
        foreach ($groups as $group) {
            Db::getInstance()->insert('carrier_group', array(
                'id_carrier' => (int) $carrier->id,
                'id_group' => (int) $group['id_group']
            ));
        }

        $rangePrice = new RangePrice();
        $rangePrice->id_carrier = (int) $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '1000';
        $rangePrice->add();

        $rangeWeight = new RangeWeight();
        $rangeWeight->id_carrier = (int) $carrier->id;
        $rangeWeight->delimiter1 = '0';
        $rangeWeight->delimiter2 = '1000';
        $rangeWeight->add();

        $zones = Zone::getZones(true);
        foreach ($zones as $zone) {
            Db::getInstance()->insert(
                'carrier_zone',
                array('id_carrier' => (int) $carrier->id, 'id_zone' => (int) $zone['id_zone'])
            );
            Db::getInstance()->insert(
                'delivery',
                array('id_carrier' => (int) $carrier->id, 'id_range_price' => (int) $rangePrice->id, 'id_range_weight' => NULL, 'id_zone' => (int) $zone['id_zone'], 'price' => '0')
            );
            Db::getInstance()->insert(
                'delivery',
                array('id_carrier' => (int) $carrier->id, 'id_range_price' => NULL, 'id_range_weight' => (int) $rangeWeight->id, 'id_zone' => (int) $zone['id_zone'], 'price' => '0')
            );
        }
        try {
            $image_path = self::$_moduleDir . 'views/images/' . $image;
            $image_path = (empty($image)) ? self::$_moduleDir . 'logo.png' : $image_path;

            copy($image_path, _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
        } catch (Exception $e) {
        }

        Configuration::updateValue($key, $carrier->id);
        Configuration::updateValue($key . '_REFERENCE', $carrier->id);

        return true;
    }

    /**
     * Deletes a shipping method
     */
    public function deleteCarrier($key)
    {
        $carrier = new Carrier((int) (Configuration::get($key)));
        if (!$carrier) {
            return true; // carrier doesnt exist, no further action needed
        }

        if (Configuration::get('PS_CARRIER_DEFAULT') == (int) $carrier->id) {
            $this->updateDefaultCarrier();
        }

        $carrier->active = 0;
        $carrier->deleted = 1;

        if (!$carrier->update()) {
            return false;
        }

        return true;
    }

    /**
     * Change default carrier when deleting carrier
     */
    private function updateDefaultCarrier()
    {
        self::checkForClass('MjvpHelper');
        $cHelper = new MjvpHelper();

        $carriers = $cHelper->getAllCarriers();
        foreach ($carriers as $carrier) {
            if ($carrier['external_module_name'] != $this->name && $carrier['active'] && !$carrier['deleted']) {
                Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier['id_carrier']);
                break;
            }
        }
    }

    /**
     * Create other configuration values in database
     */
    private function createOtherDbRecords()
    {
        foreach ($this->_configKeysOther as $item => $data ) {
            $data_value = (is_array($data['default_value'])) ? json_encode($data['default_value']) : $data['default_value'];
            if (!Configuration::hasKey($data['key'])) {
                Configuration::updateValue($data['key'], $data_value);
            }
        }

        return true;
    }

    /**
     * Get terminals for all countries
     */
    private function getVenipakTerminals()
    {
        self::checkForClass('MjvpFiles');
        $cFiles = new MjvpFiles();
        $cFiles->updateCountriesList();
        $cFiles->updateTerminalsList();
    }

    /**
     * Function for hooks control
     */
    public function setHooks($action = 'register')
    {
        $allowed_actions = array('register', 'unregister');
        if (!in_array($action, $allowed_actions)) {
            $msg = "Unsupported hook action - allowed only ";
            $first = true;
            foreach ($allowed_actions as $act) {
                if (!$first) {
                    $msg .= ', ';
                } else {
                    $first = false;
                }
                $msg .= "'" . $act . "'";
            }
            throw new Exception($msg);
        }
        $action .= 'Hook';
        foreach ($this->_hooks as $hook) {
            if (!$this->$action($hook)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Add module classes files
     */
    public static function checkForClass($className)
    {
        if (!class_exists($className)) {
            if (isset(self::$_classMap[$className])) {
                require_once self::$_moduleDir . self::$_classMap[$className];
                if (!class_exists($className)) {
                    throw new Exception('Class "' . $className . '" not found.');
                }
            } else {
                throw new Exception('Class "' . $className . '" not exists in classes map.');
            }
        }
    }

    /**
     * Create module database tables
     */
    public function createDbTables()
    {
        try {
            self::checkForClass('MjvpDb');
            $cDb = new MjvpDb();

            $result = $cDb->createTables();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name . 'api')) {
            $output .= $this->saveConfig('API', $this->l('API settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'shop')) {
            $output .= $this->saveConfig('SHOP', $this->l('Shop settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'pickuppoints')) {
            $output .= $this->saveConfig('PICKUPPOINTS', $this->l('Pickup points settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'courier')) {
            $output .= $this->saveConfig('COURIER', $this->l('Courier settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'label')) {
            $output .= $this->saveConfig('LABEL', $this->l('Labels settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'advanced')) {
            $output .= $this->saveConfig('ADVANCED', $this->l('Advanced settings updated'));
        }

        return $output
            . $this->displayConfigApi()
            . $this->displayConfigShop()
            . $this->displayConfigCourier()
            . $this->displayConfigLabel()
            . $this->displayConfigAdvancedSettings();
    }

    /**
     * Display menu in module configuration
     */
    public function displayConfigMenu()
    {
        $menu = array(
            array(
                'label' => $this->l('Module settings'),
                'url' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
                'active' => Tools::getValue('controller') == 'AdminModules'
            ),
        );

        $this->context->smarty->assign(array(
            'moduleMenu' => $menu
        ));

        return $this->context->smarty->fetch(self::$_moduleDir . 'views/templates/admin/configs_menu.tpl');
    }

    /**
     * Display API section in module configuration
     */
    public function displayConfigApi()
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $section_id = 'API';

        $form_fields = array(
            array(
                'type' => 'text',
                'label' => $this->l('API username'),
                'name' => $cModuleConfig->getConfigKey('username', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('API password'),
                'name' => $cModuleConfig->getConfigKey('password', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('API ID'),
                'name' => $cModuleConfig->getConfigKey('id', $section_id),
                'size' => 20,
                'required' => true
            ),
        );

        return $this->displayConfig($section_id, $this->l('API Settings'), $form_fields, $this->l('Save API settings'));
    }

    /**
     * Display Shop section in module configuration
     */
    public function displayConfigShop()
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $section_id = 'SHOP';
        $country_options = [];

        foreach ($this->available_countries as $country) {
            if (isset($this->countries_names[$country])) {
                array_push($country_options, array(
                    'id_option' => $country,
                    'name' => $country . ' - ' . $this->countries_names[$country]
                ));
            }
        }

        $form_fields = array(
            array(
                'type' => 'text',
                'label' => $this->l('Shop Name'),
                'name' => $cModuleConfig->getConfigKey('shop_name', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Company code'),
                'name' => $cModuleConfig->getConfigKey('company_code', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Address'),
                'name' => $cModuleConfig->getConfigKey('shop_address', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('City'),
                'name' => $cModuleConfig->getConfigKey('shop_city', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'select',
                'label' => $this->l('Country Code'),
                'name' => $cModuleConfig->getConfigKey('shop_country_code', $section_id),
                'options' => array(
                    'query' => $country_options,
                    'id' => 'id_option',
                    'name' => 'name'
                ),
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Postcode'),
                'name' => $cModuleConfig->getConfigKey('shop_postcode', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Contact person'),
                'name' => $cModuleConfig->getConfigKey('shop_contact', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Mob. Phone'),
                'name' => $cModuleConfig->getConfigKey('shop_phone', $section_id),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Email'),
                'name' => $cModuleConfig->getConfigKey('shop_email', $section_id),
                'size' => 20,
                'required' => true
            ),
        );

        return $this->displayConfig($section_id, $this->l('Shop Settings'), $form_fields, $this->l('Save shop settings'));
    }

    /**
     * Display Courier section in module configuration
     */
    public function displayConfigCourier()
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $section_id = 'COURIER';

        $swither_values = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->trans('Yes', array(), 'Admin.Global')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->trans('No', array(), 'Admin.Global')
            )
        );

        $form_fields = array(
            array(
                'type' => 'switch',
                'label' => $this->l('Door code'),
                'name' => $cModuleConfig->getConfigKey('door_code', $section_id),
                'desc' => $this->l('Add input for customers to enter their door code, when selected courier.'),
                'values' => $swither_values
            ),
            array(
                'type' => 'switch',
                'label' => $this->l('Cabinet number'),
                'name' => $cModuleConfig->getConfigKey('cabinet_number', $section_id),
                'desc' => $this->l('Allow customers to input cabinet number.'),
                'values' => $swither_values
            ),
            array(
                'type' => 'switch',
                'label' => $this->l('Warehouse number'),
                'name' => $cModuleConfig->getConfigKey('warehouse_number', $section_id),
                'desc' => $this->l('Allow customers to select warehouse.'),
                'values' => $swither_values
            ),
            array(
                'type' => 'switch',
                'label' => $this->l('Enable delivery time selection'),
                'name' => $cModuleConfig->getConfigKey('delivery_time', $section_id),
                'desc' => $this->l('Allow customers to select delivery time.'),
                'values' => $swither_values
            ),
            array(
                'type' => 'switch',
                'label' => $this->l('Enable carrier call before delivery'),
                'name' => $cModuleConfig->getConfigKey('call_before_delivery', $section_id),
                'desc' => $this->l('Enable this option, if you want courier to call a consignee before shipment delivery'),
                'values' => $swither_values
            ),
        );

        return $this->displayConfig($section_id, $this->l('Courier Settings'), $form_fields, $this->l('Save courier settings'));
    }


    /**
     * Display Advanced settings section in module configuration
     */
    public function displayConfigAdvancedSettings()
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $section_id = 'ADVANCED';

        $form_fields = array(
            array(
                'type' => 'text',
                'label' => $this->l('Carrier disable passphrase'),
                'name' => $cModuleConfig->getConfigKey('carrier_disable_passphrase', $section_id),
                'desc' => $this->l('Carriers will not be used for the cart, if cart contains any product, whose description contains this passphrase.'),
            ),
        );

        return $this->displayConfig($section_id, $this->l('Advanced settings'), $form_fields, $this->l('Save Advanced settings'));
    }

    /**
     * Display Labels section in module configuration
     */
    public function displayConfigLabel()
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $section_id = 'LABEL';

        $form_fields = array(
            array(
                'type' => 'radio',
                'label' => $this->l('Label size'),
                'name' => $cModuleConfig->getConfigKey('label_size', $section_id),
                'values' => array(
                    array(
                        'id' => 'A4',
                        'value' => 'a4',
                        'label' => 'A4',
                    ),
                    array(
                        'id' => 'A6',
                        'value' => 'a6',
                        'label' => 'A6',
                    ),
                ),
                'desc' => $this->l('Paper size of printing labels'),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Last pack number'),
                'name' => $cModuleConfig->getConfigKey('label_counter', $section_id),
                'size' => 20,
                'required' => false,
                'desc' => $this->l('This field allows to change the last package number that is included in the label code (label code always must be unique)') . '. ' . sprintf($this->l('This value must be a number and not longer than %u characters'), 7) . '.',
                //'disabled' => true,
            ),
        );

        return $this->displayConfig($section_id, $this->l('Labels Settings'), $form_fields, $this->l('Save labels settings'));
    }

    /**
     * Display Pickup points section in module configuration
     */
    public function displayConfigPickupPoints()
    {
        $section_id = 'PICKUPPOINTS';

        $country_options = array(
        );

        $form_fields = array(
        );

        return $this->displayConfig($section_id, $this->l('Pickup points'), $form_fields, $this->l('Save pickup points settings'));
    }

    /**
     * Build section display in module configuration
     */
    public function displayConfig($section_id, $section_title, $form_fields = array(), $submit_title = '')
    {
        /*self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();*/

        $fieldsForm[0]['form'] = array(
            'legend' => array(
                'title' => $section_title,
            ),
            'input' => $form_fields,
            'submit' => array(
                'title' => (!empty($submit_title)) ? $submit_title : $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ),
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name . strtolower($section_id);
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load saved settings
        if (isset($this->_configKeys[strtoupper($section_id)])) {
            foreach ($this->_configKeys[strtoupper($section_id)] as $key) {
                $value = Configuration::get($key);
                /*if ($key === $cModuleConfig->getConfigKey('MJVP_PP_COUNTRIES', 'PICKUPPOINTS')) {
                    $value = explode(';', $value);
                    $key .= '[]';
                }*/
                $helper->fields_value[$key] = $value;
            }
        }

        return $helper->generateForm($fieldsForm);
    }

    /**
     * Save section values in module configuration
     */
    public function saveConfig($section_id, $success_message = '')
    {
        $errors = $this->validateConfig($section_id);
        $output = null;

        if (!empty($errors)) {
            $output .= $this->displayError($errors);
        } else {
            foreach ($this->_configKeys[strtoupper($section_id)] as $key) {
                $value = Tools::getValue($key);
                if (is_array($value)) {
                    $value = implode(';', $value);
                }
                Configuration::updateValue($key, strval($value));
            }
            $success_message = (!empty($success_message)) ? $success_message : $this->l('Settings updated');
            $output .= $this->displayConfirmation($success_message);
        }

        return $output;
    }

    /**
     * Validate section values in module configuration
     */
    protected function validateConfig($section_id)
    {
        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $section_id = strtoupper($section_id);

        $errors = array();
        $txt_required = $this->l('is required');

        if ($section_id == 'API') {
            if (empty(Tools::getValue($cModuleConfig->getConfigKey('username', 'API')))) {
                $errors[] = $this->l('API username') . ' ' . $txt_required;
            }
            if (empty(Tools::getValue($cModuleConfig->getConfigKey('password', 'API')))) {
                $errors[] = $this->l('API password') . ' ' . $txt_required;
            }
            if (empty(Tools::getValue($cModuleConfig->getConfigKey('id', 'API')))) {
                $errors[] = $this->l('API ID') . ' ' . $txt_required;
            }
        }
        if ($section_id == 'SHOP') {
            foreach ($this->_configKeys[$section_id] as $key => $key_value) {
                $configField = $this->getConfigField($section_id, $key);
                if (empty(Tools::getValue($cModuleConfig->getConfigKey($key, 'SHOP'))) && $configField['required']) {
                    $errors[] = $configField['name'] . ' ' . $txt_required;
                }
            }
        }
        if ($section_id == 'LABEL') {
            foreach ($this->_configKeys[$section_id] as $key => $key_value) {
                $configField = $this->getConfigField($section_id, $key_value);
                $field_value = Tools::getValue($cModuleConfig->getConfigKey($key, $section_id));
                if (isset($configField['type']) && $configField['type'] === 'number') {
                    $field_value = (float) $field_value;
                    if (isset($configField['min']) && $field_value < $configField['min']) {
                        $errors[] = sprintf($this->l('%s must be more then %d'), $configField['name'], $configField['min']);
                    }
                    if (isset($configField['max']) && $field_value > $configField['max']) {
                        $errors[] = sprintf($this->l('%s must be less then %d'), $configField['name'], $configField['max']);
                    }
                }
            }
        }

        if ($section_id == 'ADVANCED') {
            foreach ($this->_configKeys[$section_id] as $key => $key_value) {
                $configField = $this->getConfigField($section_id, $key_value);
                $field_value = Tools::getValue($cModuleConfig->getConfigKey($key, $section_id));
                if (isset($configField['validate'])) {
                    $validate = $configField['validate'];
                    if(!Validate::$validate($field_value))
                    {
                        $errors[] = sprintf($this->l('%s is not valid.'), $configField['name']);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Update default carrier
     */
    public function hookUpdateCarrier($params)
    {
        $id_carrier_old = (int) ($params['id_carrier']);
        $id_carrier_new = (int) ($params['carrier']->id);

        foreach (self::$_carriers as $carrier) {
            if ($id_carrier_old == (int) (Configuration::get($carrier['id_name'])))
                Configuration::updateValue($carrier['id_name'], $id_carrier_new);
        }
    }

    /**
     * Hook for js/css files and other elements in header
     */
    public function hookHeader($params)
    {
        if (!$this->active) return;

        if (in_array($this->context->controller->php_self, array('order', 'order-opc'))) {

            $address = new Address($params['cart']->id_address_delivery);
            $country = new Country();
            $country_code = $country->getIsoById($address->id_country);
            self::checkForClass('MjvpApi');
            $cApi = new MjvpApi();
            $all_terminals_info = $cApi->getTerminals($country_code);
            $filtered_terminals = $this->filterTerminalsByCartWeight($all_terminals_info);
            // Reindexing. For some reason, forEach in JS fails, if elements are not indexed.
            $filtered_terminals = array_values($filtered_terminals);

            Media::addJsDef(array(
                    'mjvp_front_controller_url' => $this->context->link->getModuleLink($this->name, 'front'),
                    'mjvp_translates' => array(
                        'loading' => $this->l('Loading'),
                    ),
                    'mjvp_terminal_select_translates' => array(
                    'modal_header' => $this->l('Pickup points map'),
                    'terminal_list_header' => $this->l('Pickup points list'),
                    'seach_header' => $this->l('Search around'),
                    'search_btn' => $this->l('Find'),
                    'modal_open_btn' => $this->l('Select pickup point'),
                    'geolocation_btn' => $this->l('Use my location'),
                    'your_position' => $this->l('Distance calculated from this point'),
                    'nothing_found' => $this->l('Nothing found'),
                    'no_cities_found' => $this->l('There were no cities found for your search term'),
                    'geolocation_not_supported' => $this->l('Geolocation is not supported'),
                    'select_pickup_point' => $this->l('Select a pickup point'),
                    'search_placeholder' => $this->l('Enter postcode/address'),
                    'workhours_header' => $this->l('Workhours'),
                    'contacts_header' => $this->l('Contacts'),
                    'no_pickup_points' => $this->l('No points to select'),
                    'select_btn' => $this->l('select'),
                    'back_to_list_btn' => $this->l('reset search'),
                    'no_information' => $this->l('No information'),
                    ),
                    'mjvp_terminals' => $filtered_terminals
                )
            );
            $this->context->controller->registerJavascript('modules-mjvp-terminals-mapping-js', 'modules/' . $this->name . '/views/js/terminal-mapping.js');
            $this->context->controller->registerJavascript('modules-mjvp-front-js', 'modules/' . $this->name . '/views/js/front.js');
            $this->context->controller->registerJavascript('modules-mjvp-terminals-mapinit-js', 'modules/' . $this->name . '/views/js/terminals_map_init.js');
            $this->context->controller->addCSS($this->_path . 'views/css/global.css');
            $this->context->controller->addCSS($this->_path . 'views/css/three-dots.min.css');
            $this->context->controller->addCSS($this->_path . 'views/css/terminal-mapping.css');
        }
    }


    /**
     * Hook to display block in Prestashop order edit
     */
    public function hookDisplayAdminOrder($params)
    {
        $order_id = (int)$params['id_order'];
        $order = new Order($order_id);

        if (!is_object($order) || !isset($order->id_cart)) {
            return '';
        }

        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();
        
        self::checkForClass('MjvpHelper');
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
            $this->context->controller->errors[] = $this->displayName . " error:<br/>" . $e->getMessage();
            return '';
        }

        $venipak_cart_info = $cDb->getOrderInfo($order->id);

        self::checkForClass('MjvpApi');
        $cApi = new MjvpApi();

        $order_country_code = $venipak_cart_info['country_code'];
        $pickup_points = $cApi->getTerminals($order_country_code);
        $order_terminal_id = $cDb->getOrderValue('terminal_id', ['id_order' => $order->id]);
        $venipak_carriers = [];
        foreach (self::$_carriers as $carrier)
        {
            $reference = Configuration::get($carrier['reference_name']);
            $carrier = Carrier::getCarrierByReference($reference);
            $venipak_carriers[$reference] = $carrier->name;
        }

        $order_carrier_reference = $venipak_cart_info['id_carrier_ref'];
        if($order_carrier_reference == Configuration::get(self::$_carriers['pickup']['reference_name']))
            $venipak_cart_info['is_pickup'] = true;

        $other_info = json_decode($venipak_cart_info['other_info'], true);

        $shipment_labels = json_decode($venipak_cart_info['labels_numbers'], true);
        $this->context->smarty->assign(array(
            'block_title' => $this->displayName,
            'module_dir' => __PS_BASE_URI__ . 'modules/' . $this->name,
            'label_status' => $status,
            'label_error' => $error,
            'order_id' => $order->id,
            'order_terminal_id' => $order_terminal_id,
            'venipak_pickup_points' => $pickup_points,
            'venipak_error' => ($venipak_cart_info['error'] != '' ? $this->displayError($venipak_cart_info['error']) : false),
            'label_tracking_numbers' => json_decode($tracking_numbers),
            'orderVenipakCartInfo' => $venipak_cart_info,
            'venipak_carriers' => $venipak_carriers,
            'venipak_other_info' => $other_info,
            'shipment_labels' => $shipment_labels,
            'delivery_times' => $this->deliveryTimes,
            'carrier_reference' => $order_carrier_reference,
            'pickup_reference' => Configuration::get(self::$_carriers['pickup']['reference_name']),
            'venipak_print_label_url' => $this->context->link->getAdminLink('AdminVenipakshippingAjax') . '&action=printLabel',
        ));

        return $this->context->smarty->fetch(self::$_moduleDir . 'views/templates/hook/displayAdminOrder.tpl');
    }

    public function hookActionValidateStepComplete($params)
    {
        self::checkForClass('MjvpCart');

        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();

        $errors = array();

        if($params['step_name'] != 'delivery')
            return;
        $cart = $params['cart'];
        $carrier = new Carrier($cart->id_carrier);
        $carrier_reference = $carrier->id_reference;

        if(Configuration::get(self::$_carriers['pickup']['reference_name']) == $carrier_reference)
        {
            // Check if terminal was selected
            $terminal_id = $cDb->getOrderValue('terminal_id', array('id_cart' => $cart->id));
            if(!$terminal_id)
            {
                $errors['mjvp_terminal'] = $this->l('Please select a terminal.');
                if(!empty($errors))
                {
                    $this->showErrors($errors);
                    $params['completed'] = false;
                    return;
                }
            }
        }
        elseif (Configuration::get(self::$_carriers['courier']['reference_name']) == $carrier_reference)
        {
            // Validate extra fields
            $field_door_code = Tools::getValue('mjvp_door_code', 0);
            $field_cabinet_number = Tools::getValue('mjvp_cabinet_number', 0);
            $field_warehouse_number = Tools::getValue('mjvp_warehouse_number', 0);
            $field_delivery_time = Tools::getValue('mjvp_delivery_time', 'nwd');
            $field_carrier_call = 0;
            if(Tools::isSubmit('mjvp_carrier_call'))
                $field_carrier_call = 1;
            if(strlen($field_door_code) > self::EXTRA_FIELDS_SIZE)
                $errors['mjvp_door_code'] = $this->l('The door code is too long.');
            if(strlen($field_cabinet_number) > self::EXTRA_FIELDS_SIZE)
                $errors['mjvp_cabinet_number'] = $this->l('The cabinet number is too long.');
            if(strlen($field_warehouse_number) > self::EXTRA_FIELDS_SIZE)
                $errors['mjvp_warehouse_number'] = $this->l('The warehouse number is too long.');
            if(!isset($this->deliveryTimes[$field_delivery_time]))
                $errors['mjvp_delivery_time'] = $this->l('Selected delivery time does not exist.');

            if(!empty($errors))
            {
                $this->showErrors($errors);
                $params['completed'] = false;
                return;
            }

            $order_extra_info = self::$_order_additional_info;

            $sql_extra_info = $cDb->getOrderValue('other_info', array('id_cart' => $cart->id));
            $sql_extra_info = (array) json_decode($sql_extra_info);
            foreach($sql_extra_info as $key => $value) {
                $order_extra_info[$key] = $value;
            }

            $order_extra_info['door_code'] = $field_door_code;
            $order_extra_info['cabinet_number'] = $field_cabinet_number;
            $order_extra_info['warehouse_number'] = $field_warehouse_number;
            $order_extra_info['delivery_time'] = $field_delivery_time;
            $order_extra_info['carrier_call'] = $field_carrier_call;

            $cDb->updateOrderInfo($cart->id, array('other_info' => json_encode($order_extra_info)));
        }
    }

    /**
     * Hook to display content on carrier in checkout page
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        self::checkForClass('MjvpHelper');
        $cHelper = new MjvpHelper();

        self::checkForClass('MjvpApi');
        $cApi = new MjvpApi();
        
        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();

        self::checkForClass('MjvpModuleConfig');
        $cModuleConfig = new MjvpModuleConfig();

        $carrier_id_reference = $params['carrier']['id_reference'];

        $carrier_type = $cHelper->itIsThisModuleCarrier($carrier_id_reference);

        if ($carrier_type !== 'pickup') {

            $configuration = [
                'show_door_code' => Configuration::get($cModuleConfig->getConfigKey('door_code', 'COURIER')),
                'show_cabinet_number' => Configuration::get($cModuleConfig->getConfigKey('cabinet_number', 'COURIER')),
                'show_warehouse_number' => Configuration::get($cModuleConfig->getConfigKey('warehouse_number', 'COURIER')),
                'show_delivery_time' => Configuration::get($cModuleConfig->getConfigKey('delivery_time', 'COURIER')),
                'show_carrier_call' => Configuration::get($cModuleConfig->getConfigKey('call_before_delivery', 'COURIER')),
                'delivery_times' => $this->deliveryTimes
            ];

            $cart = $params['cart'];
            $order_extra_info = self::$_order_additional_info;

            $sql_extra_info = $cDb->getOrderValue('other_info', array('id_cart' => $cart->id));
            $sql_extra_info = (array) json_decode($sql_extra_info);

            foreach ($order_extra_info as $key => $value) {
                if (isset($sql_extra_info[$key])) {
                    $order_extra_info[$key] = $sql_extra_info[$key];
                }
            }

            $this->context->smarty->assign(
                array_merge($configuration, $order_extra_info)
            );
            
            return $this->context->smarty->fetch(self::$_moduleDir . '/views/templates/front/courier_extra_content.tpl');
        }

        $address = new Address($params['cart']->id_address_delivery);
        $country = new Country();
        $country_code = $country->getIsoById($address->id_country);

        if (empty($country_code)) {
            return '';
        }

        try {
            $all_terminals_info = $cApi->getTerminals($country_code);
            if (empty($all_terminals_info)) {
                return '';
            }
        } catch (Exception $e) {
            return '';
        }

        try {
            $sql_terminal_id = $cDb->getOrderValue('terminal_id', array('id_cart' => $params['cart']->id));
        } catch (Exception $e) {
            $sql_terminal_id = '';
        }

        $quantity = 0;
        foreach ($params['cart']->getProducts() as $product) {
            $quantity = $quantity + $product['cart_quantity'];
        }

        $this->context->smarty->assign(
            array(
                'terminals' => $all_terminals_info,
                'postcode' => $address->postcode,
                'city' => $address->city,
                'country_code' => $country_code,
                'selected_terminal' => $sql_terminal_id,
                'images_url' => $this->_path . 'views/images/',
                'cart_quantity' => $quantity,
            )
        );

        return $this->context->smarty->fetch(self::$_moduleDir . 'views/templates/front/pickuppoints_extra_content.tpl');
    }

    /**
     * Hook for bulk actions (Work only until Prestashop 1.7.6)
     */
    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if ($this->context->controller instanceof AdminOrdersController) {
            $this->context->controller->addMjvpBulkAction('mjvp_send_labels', array(
                'text' => $this->l('Send Venipak labels'),
                'icon' => 'icon-cloud-upload'
            ));
            $this->context->controller->addMjvpBulkAction('mjvp_print_labels', array(
                'text' => $this->l('Print Venipak labels'),
                'icon' => 'icon-print'
            ));

            $is_bulk_send_labels = Tools::isSubmit('submitBulkmjvp_send_labelsorder');
            $is_bulk_print_labels = Tools::isSubmit('submitBulkmjvp_print_labelsorder');

            try {
                if (!isset($params['select']) && ($is_bulk_send_labels || $is_bulk_print_labels)) {
                    $orders = Tools::getValue('orderBox');

                    if (empty($orders)) {
                        $this->context->controller->errors[] = $this->l('Select at least one order');
                        return true;
                    }

                    if ($is_bulk_send_labels) {
                        $this->bulkActionSendLabels($orders);
                    }

                    if ($is_bulk_print_labels) {
                        $this->bulkActionPrintLabels($orders);
                    }
                }
            } catch (Exception $e) {
                $this->context->controller->errors[] = $e->getMessage();
            }
        }
    }

    /**
     * Hook to send labels when launch bulk action
     */
    public function bulkActionSendLabels($orders_ids)
    {

        self::checkForClass('MjvpApi');
        $cApi = new MjvpApi();

        self::checkForClass('MjvpHelper');
        $cHelper = new MjvpHelper();

        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();

        $errors = array();
        $success_orders = array();
        $found = false;
        $notfound_ids = array();
        $prev_manifest = json_decode(Configuration::get($this->_configKeysOther['counter_manifest']['key']));

        $current_date = date('ymd');
        $manifest_id = ($current_date != $prev_manifest->date) ? 1 : (int)$prev_manifest->counter + 1;
        $manifest = array(
            'manifest_id' => $manifest_id,
            'manifest_name' => Configuration::get('PS_SHOP_NAME'),
            'shipments' => array(),
        );

        try {
            $error_order_no = '-';

            // Venipak returns batch of label numbers that have no relation to shipments for which label was generated, other than their order.
            // This gets complicated when packs are used. So we sort orders (this way we will know which label number belongs to which order)
            // and map each order to it's packages and parse the response accordingly.
            sort($orders_ids);
            $order_packages_mapping = [];

            foreach ($orders_ids as $order_id) {
                $error_order_no = ' #' . $order_id;
                $order = new Order((int)$order_id);
                $address = new Address($order->id_address_delivery);
                $carrier = new Carrier($order->id_carrier);
                if (!empty($order->id_carrier) && $cHelper->itIsThisModuleCarrier($carrier->id_reference)) {
                    $found = true;
                    $order_products = $order->getProducts();
                    $country_iso = Country::getIsoById($address->id_country);
                    $consignee_name = $address->firstname . ' ' . $address->lastname;
                    $consignee_code = '';
                    if (!empty($address->company)) {
                        $consignee_name = $address->company;
                        if (empty($address->dni)) {
                            $errors[] = $this->l('Order') . $error_order_no. '. ' . $this->l('Company code is missing');
                            continue;
                        } else {
                            $consignee_code = $address->dni;
                        }
                    }
                    $consignee_address = $address->address1;
                    if (!empty($address->address2)) {
                        $consignee_address .= ', ' . $address->address2;
                    }
                    $consignee_phone = (!empty($address->phone_mobile)) ? $address->phone_mobile : $address->phone;
                    if (!in_array($country_iso, $this->available_countries)) {
                        $errors[] = $this->l('Order') . $error_order_no. '. ' . $this->l('Consignee country is not allowed');
                        continue;
                    }

                    $order_info = $cDb->getOrderInfo($order_id);
                    $packages = $order_info['packages'];
                    $order_packages_mapping[$order_id] = $packages;
                    $shipment_pack = [];
                    for($i = 1; $i <= $packages; $i++)
                    {
                        $pack_no = (int)Configuration::get($this->_configKeysOther['counter_packs']['key']) + 1;
                        $shipment_pack[$i] = array(
                            'serial_number' => $pack_no,
                            'document_number' => '',
                            'weight' => Tools::ps_round($order_info['order_weight'] / $packages, 2),
                            'volume' => 0,
                        );
                        foreach ($order_products as $key => $product) {
                            // Calculate volume in m3
                            if(Configuration::get('PS_DIMENSION_UNIT') == 'm')
                                $product_volume = $product['width'] * $product['height'] * $product['depth'];
                            elseif(Configuration::get('PS_DIMENSION_UNIT') == 'cm')
                                $product_volume = ($product['width'] * $product['height'] * $product['depth']) / 1000000;
                            $shipment_pack[$i]['volume'] += Tools::ps_round((float)$product_volume / $packages);
                        }
                        Configuration::updateValue($this->_configKeysOther['counter_packs']['key'], $pack_no);
                    }

                    // Get other info fields for Venipak carrier (door code, cabinet number, warehouse, delivery time).
                    $door_code = '';
                    $cabinet_number = '';
                    $warehouse_number = '';
                    $delivery_time = '';
                    $carrier_call = '';
                    $carrier_reference = $carrier->id_reference;
                    $consignee = [];
                    $currency = new Currency($order->id_currency);
                    $currency_iso = strtoupper($currency->iso_code);
                    if(Configuration::get(self::$_carriers['courier']['reference_name']) == $carrier_reference)
                    {
                        $other_info = json_decode($cDb->getOrderValue('other_info', array('id_order' => $order_id)));
                        $door_code = $other_info->door_code;
                        $cabinet_number = $other_info->cabinet_number;
                        $warehouse_number = $other_info->warehouse_number;
                        $delivery_time = $other_info->delivery_time;
                        $carrier_call = $other_info->carrier_call;
                        $consignee = [
                            'name' => $consignee_name,
                            'code' => $consignee_code,
                            'country_code' => $country_iso,
                            'city' => $address->city,
                            'address' => $consignee_address,
                            'postcode' => $address->postcode,
                            'phone' => $consignee_phone,
                            'door_code' => $door_code,
                            'cabinet_number' => $cabinet_number,
                            'warehouse_number' => $warehouse_number,
                            'carrier_call' => $carrier_call,
                            'delivery_time' => $delivery_time,
                            'return_doc' => isset($other_info->return_doc) ? $other_info->return_doc : 0,
                            'cod' => $order_info['is_cod'] ? $order_info['cod_amount'] : '',
                            'cod_type' => $order_info['is_cod'] ? $currency_iso : '',
                        ];
                    }
                    // If carrier is pickup terminal, consignee has to use terminal data.
                    if(Configuration::get(self::$_carriers['pickup']['reference_name']) == $carrier_reference)
                    {
                        $terminal_info = json_decode($cDb->getOrderValue('terminal_info', array('id_order' => $order_id)));
                        $contact_person = '';
                        $contact_phone = '';
                        $contact_email = '';
                        $address = new Address($order->id_address_delivery);
                        if(Validate::isLoadedObject($address))
                        {
                            $contact_person = $address->firstname . ' ' . $address->lastname;
                            $contact_phone = $address->phone;
                        }
                        $customer = new Customer($order->id_customer);

                        if(Validate::isLoadedObject($customer))
                        {
                            $contact_email = $customer->email;
                        }
                        $consignee = [
                            'name' => $terminal_info->name,
                            'code' => $terminal_info->company_code,
                            'country_code' => $terminal_info->country,
                            'city' => $terminal_info->city,
                            'address' => $terminal_info->address,
                            'postcode' => $terminal_info->post_code,
                            'person' => $contact_person,
                            'phone' => $contact_phone,
                            'email' => $contact_email,
                            'cod' => $order_info['is_cod'] ? $order_info['cod_amount'] : '',
                            'cod_type' => $order_info['is_cod'] ? $currency_iso : '',
                        ];
                    }

                    $manifest['shipments'][] = array(
                        'order_id' => $order_id,
                        'order_code' => $order->reference,
                        'consignee' => $consignee,
                        'packs' => $shipment_pack,
                    );
                    $success_orders[] = $error_order_no;
                } else {
                    $notfound_ids[] = $error_order_no;
                }
            }
            $manifest_xml = $cApi->buildManifestXml($manifest);
            if ($cHelper->isXMLContentValid($manifest_xml)) {
                $status = $cApi->sendXml($manifest_xml);
                if(!isset($status['error']) && $status['text'])
                {
                    $manifest_number = Configuration::get($this->_configKeysOther['last_manifest_id']['key']);
                    self::checkForClass('MjvpManifest');
                    $mjvp_manifest = new MjvpManifest();
                    $mjvp_manifest->manifest_id = $manifest_number;
                    $mjvp_manifest->id_shop = $this->context->shop->id;
                    $mjvp_manifest->arrival_date_from = null;
                    $mjvp_manifest->arrival_date_to = null;
                    $mjvp_manifest->save(true);

                    // Multiple labels - $status['text'] is array
                    if(isset($status['text']) && is_array($status['text']))
                    {
                        $offset = 0;
                        foreach ($order_packages_mapping as $order_id => $mapping)
                        {
                            $this->changeOrderStatus($order_id, Configuration::get(self::$_order_states['order_state_ready']['key']));
                            $order_labels = array_slice($status['text'], $offset, $mapping);

                            // Add first label number to OrderCarrier as tracking number.
                            $id_order_carrier = (int) Db::getInstance()->getValue('
                                SELECT `id_order_carrier`
                                FROM `' . _DB_PREFIX_ . 'order_carrier`
                                WHERE `id_order` = ' . (int) $order_id);
                            $order_carrier = new OrderCarrier($id_order_carrier);
                            $order_carrier->tracking_number = $order_labels[0];
                            $order_carrier->save();

                            $cDb->updateRow('mjvp_orders', [
                                'labels_numbers' => json_encode($order_labels),
                                'manifest_id' => $manifest_number,
                                'status' => 'registered',
                                'labels_date' => date('Y-m-d h:i:s')],
                                ['id_order' => $order_id]);
                            $offset += $mapping;
                        }
                    }
                    elseif(isset($status['text']))
                    {
                        $id_order = array_key_first($order_packages_mapping);
                        $id_order_carrier = (int) Db::getInstance()->getValue('
                                SELECT `id_order_carrier`
                                FROM `' . _DB_PREFIX_ . 'order_carrier`
                                WHERE `id_order` = ' . (int) $order_id);
                        $order_carrier = new OrderCarrier($id_order_carrier);
                        $order_carrier->tracking_number = $status['text'];
                        $order_carrier->save();

                        $this->changeOrderStatus($order_id, Configuration::get(self::$_order_states['order_state_ready']['key']));
                        $cDb->updateRow('mjvp_orders', [
                            'labels_numbers' => json_encode([$manifest_id => $status['text']]),
                            'manifest_id' => $manifest_number,
                            'status' => 'registered',
                            'labels_date' => date('Y-m-d h:i:s')],
                            ['id_order' => $id_order]);
                    }

                }
                if (isset($status['error'])) {
                    if (isset($status['error']['text'])) {
                        $errors[] = '<b>' . $this->l('API error') . ':</b> ' . $status['error']['text'];
                    } else {
                       $errors[] = '<b>' . $this->l('API error') . ':</b> ' . $this->l('Unknown error'); 
                    }
                } else {
                    Configuration::updateValue($this->_configKeysOther['counter_manifest']['key'], json_encode(array('counter' => $manifest_id, 'date' => $current_date)));
                }
            }
        } catch (Exception $e) {
            $errors[] = $this->l('Order') . $error_order_no. '. ' . $e->getMessage();
        }

        if (!$found) {
            $errors[] = $this->l('None of the selected orders have a Venipak shipping method');
        } else if (!empty($notfound_ids)) {
            $errors[] = sprintf($this->l('Shipping method for orders %s is not Venipak.'), implode(', ', $notfound_ids));
        }

        if (empty($errors)) {
            $this->context->controller->confirmations[] = $this->l('Successfully created label for the shipment.');
        } else {
            $this->showErrors($errors);
            if (!empty($success_orders)) {
                $this->context->controller->confirmations[] = $this->l('Successfully included orders in manifest') . ': ' . implode(', ', $success_orders) . '.';
            }
        }
    }

    /**
     * Mass label printing
     */
    public function bulkActionPrintLabels($orders_ids)
    {
        // Get all tracking numbers and invoke print_list
        MijoraVenipak::checkForClass('MjvpApi');
        $cApi = new MjvpApi();
        $labels_numbers_db = Db::getInstance()->executeS('SELECT `labels_numbers` FROM ' . _DB_PREFIX_ . "mjvp_orders
            WHERE `id_order` IN (" . implode(',', $orders_ids) . ')');
        $labels_numbers = [];
        foreach ($labels_numbers_db as $labels_numbers_row)
        {
            if($labels_numbers_row['labels_numbers'])
                $labels_numbers = array_merge($labels_numbers, json_decode($labels_numbers_row['labels_numbers'], true));
        }
        $cApi->printLabel($labels_numbers);
    }

    private function showErrors($errors)
    {
       foreach ($errors as $key => $error) {
            $this->context->controller->errors[$key] = $error;
        } 
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $cart = $params['cart'];
        $id_order = $order->id;
        $id_cart = $cart->id;

        $carrier = new Carrier($cart->id_carrier);
        $carrier_reference = $carrier->id_reference;
        if(!in_array($carrier_reference, Configuration::getMultiple([self::$_carriers['courier']['reference_name'], self::$_carriers['pickup']['reference_name']])))
            return;

        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();
        $check_order_id = $cDb->getOrderIdByCartId($id_cart);

        if (empty($check_order_id)) {

            $order_weight = $order->getTotalWeight();

            // Convert to kg, if weight is in grams.
            if(Configuration::get('PS_WEIGHT_UNIT') == 'g')
                $order_weight *= 0.001;

            $is_cod = 0;
            if(in_array($order->module, self::$_codModules))
                $is_cod = 1;

             $cDb->updateOrderInfo($id_cart, array(
                 'id_order' => $id_order,
                 'order_weight' => $order_weight,
                 'cod_amount' => $order->total_paid_tax_incl,
                 'is_cod' => $is_cod
             ));
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (get_class($this->context->controller) == 'AdminOrdersController') {
            {
                Media::addJsDef([
                    'venipak_generate_label_url' => $this->context->link->getAdminLink('AdminVenipakshippingAjax') . '&action=generateLabel',
                    'venipak_save_order_url' => $this->context->link->getAdminLink('AdminVenipakshippingAjax') . '&action=saveOrder',
                ]);
                $this->context->controller->addJs('modules/' . $this->name . '/views/js/mjvp-admin.js');
                $this->context->controller->addCSS($this->_path . 'views/css/mjvp-admin.css');
            }
        }
        if(isset($this->context->controller->module) && $this->context->controller->module = $this)
        {
            $this->context->controller->addCSS($this->_path . 'views/css/mjvp-admin.css');
        }
    }

    private function filterTerminalsByCartWeight($terminals)
    {
        $cart_weight = $this->context->cart->getTotalWeight();
        foreach ($terminals as $key => $terminal)
        {
            if(isset($terminal->size_limit) && $terminal->size_limit < $cart_weight)
                unset($terminals[$key]);
        }
        return $terminals;
    }

    public function changeOrderStatus($id_order, $status)
    {
        $order = new Order((int)$id_order);
        if ($order->current_state != $status)
        {
            $history = new OrderHistory();
            $history->id_order = (int)$id_order;
            $history->id_employee = Context::getContext()->employee->id;
            $history->changeIdOrderState((int)$status, $order);
        }
    }
}
