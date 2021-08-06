<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MijoraVenipak extends CarrierModule
{
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

    /**
     * Default countries list for pickup points
     */
    public static $_defaultPickupCountries = array('lt', 'lv', 'ee', 'pl');

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
    );

    /**
     * Fields names and required
     */
    private function getConfigField($config_key)
    {
        return array('name' => 'ERROR_' . $config_key, 'required' => false);
    }

    public static $_order_states = array(
        'MJVP_ORDER_STATE_READY' => array(
            'color' => '#FCEAA8',
            'lang' => array(
                'en' => 'Venipak shipment ready',
                'lt' => 'Venipak siunta paruošta',
            ),
            'icon' => '', // Icon set not working
        ),
        'MJVP_ORDER_STATE_ERROR' => array(
            'color' => '#F24017',
            'lang' => array(
                'en' => 'Error on Venipak shipment',
                'lt' => 'Klaida Venipak siuntoje',
            ),
            'icon' => '', // Icon set not working
        ),
    );

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->name = 'mijoravenipak';
        $this->tab = 'shipping_logistics';
        $this->version = '0.0.1';
        $this->author = 'mijora.lt';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => '1.7.6');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Venipak Shipping');
        $this->description = $this->l('Shipping module for Venipak delivery method');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * Module installation function
     */
    public function install()
    {
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

        foreach (self::$_carriers as $carrier) {
            if (!$this->createCarrier($carrier['id_name'], $carrier['title'], $carrier['image'])) {
                $this->_errors[] = $this->l('Failed to create carrier') . ' ' . $carrier['id_name'] . '.';
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
        return $shipping_cost;
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

        /*self::checkForClass('MjvpFiles');
        $cFiles = new MjvpFiles();

        $cFiles->updateCountriesList();*/

        if (Tools::isSubmit('submit' . $this->name . 'api')) {
            $output .= $this->saveConfig('API', $this->l('API settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'shop')) {
            $output .= $this->saveConfig('SHOP', $this->l('Shop settings updated'));
        }
        if (Tools::isSubmit('submit' . $this->name . 'pickuppoints')) {
            $output .= $this->saveConfig('PICKUPPOINTS', $this->l('Pickup points settings updated'));
        }

        return $output
            . $this->displayConfigApi()
            . $this->displayConfigShop();
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

        $form_fields = array(
            array(
                'type' => 'text',
                'label' => $this->l('API username'),
                'name' => $cModuleConfig->getConfigKey('username', 'API'),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('API password'),
                'name' => $cModuleConfig->getConfigKey('password', 'API'),
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('API ID'),
                'name' => $cModuleConfig->getConfigKey('id', 'API'),
                'size' => 20,
                'required' => true
            ),
        );

        return $this->displayConfig('API', $this->l('API Settings'), $form_fields, $this->l('Save API settings'));
    }

    /**
     * Display Shop section in module configuration
     */
    public function displayConfigShop()
    {
        $country_options = array(
        );

        $form_fields = array(
        );

        return $this->displayConfig('SHOP', $this->l('Shop Settings'), $form_fields, $this->l('Save shop settings'));
    }

    /**
     * Display Pickup points section in module configuration
     */
    public function displayConfigPickupPoints()
    {
        $country_options = array(
        );

        $form_fields = array(
        );

        return $this->displayConfig('PICKUPPOINTS', $this->l('Pickup points'), $form_fields, $this->l('Save pickup points settings'));
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

        $errors = array();
        $txt_required = $this->l('is required');

        if (strtoupper($section_id) == 'API') {
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
        if (strtoupper($section_id) == 'SHOP') {
            foreach ($this->_configKeys['SHOP'] as $key => $key_value) {
                if (empty(Tools::getValue($cModuleConfig->getConfigKey($key, 'SHOP'))) && $this->getConfigField($key)['required']) {
                    $errors[] = $this->getConfigField($key)['name'] . ' ' . $txt_required;
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
            Media::addJsDef(array(
                'mjvp_front_controller_url' => $this->context->link->getModuleLink($this->name, 'front'),
        $this->context->controller->registerJavascript('modules-mjvp-front-js', 'modules/' . $this->name . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/global.css');
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
            $address = new Address($order->id_address_delivery);
            $carrier = new Carrier($order->id_carrier);
            if (!$cHelper->itIsThisModuleCarrier($carrier->id_reference)) {
                return '';
            }

            $check_order_id = $cDb->getOrderIdByCartId($order->id_cart);
            $result = true;
            if (empty($check_order_id)) {
                $result = $cDb->updateOrderInfo($order->id_cart, array('id_order' => $order_id));
            }

            if ($result) {
                $status = $cDb->getOrderValue('status', array('id_cart' => $order->id_cart));
                $error = $cDb->getOrderValue('error', array('id_cart' => $order->id_cart));
                $tracking_numbers = $cDb->getOrderValue('labels_numbers', array('id_cart' => $order->id_cart));
            } else {
                $status = 'error';
                $error = $this->l('Order not found in database');
                $tracking_numbers = '[]';
            }
        } catch (Exception $e) {
            $this->context->controller->errors[] = $this->displayName . " error:<br/>" . $e->getMessage();
            return '';
        }

        $this->context->smarty->assign(array(
            'block_title' => $this->displayName,
            'label_status' => $status,
            'label_error' => $error,
            'label_tracking_numbers' => json_decode($tracking_numbers),
        ));

        return $this->context->smarty->fetch(self::$_moduleDir . 'views/templates/hook/displayAdminOrder.tpl');
    }

    /**
     * Hook to display content on carrier in checkout page
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        $carrier_id_reference = $params['carrier']['id_reference'];

        self::checkForClass('MjvpHelper');
        $cHelper = new MjvpHelper();

        $carrier_type = $cHelper->itIsThisModuleCarrier($carrier_id_reference);

        if ($carrier_type !== 'pickup') {
            return '';
        }

        self::checkForClass('MjvpApi');
        $cApi = new MjvpApi();
        
        self::checkForClass('MjvpDb');
        $cDb = new MjvpDb();

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

        global $smarty;
        $smarty->assign(
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

        return $this->context->smarty->fetch(self::$_moduleDir . 'views/templates/front/pickup_points.tpl');
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

            $is_bulk_send_labels = Tools::isSubmit('submitBulkmjvp_send_labelsorder');

            try {
                if (!isset($params['select']) && $is_bulk_send_labels) {
                    $orders = Tools::getValue('orderBox');

                    if (empty($orders)) {
                        $this->context->controller->errors[] = $this->l('Select at least one order');
                        return true;
                    }

                    if ($is_bulk_send_labels) {
                        $this->bulkActionSendLabels($orders);
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

        try {
            $this->context->controller->confirmations[] = '<pre>'.htmlentities( $cApi->buildManifestXml(array('shipments' => array())) ).'</pre>';
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (empty($errors)) {
            $this->context->controller->confirmations[] = $this->l('Labels sent');
        } else {
            foreach ($errors as $error) {
                $this->context->controller->errors[] = $error;
            }
            if (!empty($success_orders)) {
                $this->context->controller->confirmations[] = $this->l('Labels sent for orders') . ': ' . implode(', ', $success_orders) . '.';
            }
        }
    }
}
