<?php

class MjvpDb
{
    /**
     * Database tables list
     */
    private $_table_orders = 'mjvp_orders';
    private $_table_warehouses = 'mjvp_warehouse';
    private $_table_manifests = 'mjvp_manifest';

    /**
     * Status values for rows in 'orders' table
     */
    public $order_status_new = 'new';
    public $order_status_registered = 'registered';
    public $order_status_failed = 'error';

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Create tables for module
     */
    public function createTables()
    {
        $sql = array(
            $this->_table_orders => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->_table_orders . '` (
                `id_cart` int(10) unsigned NOT NULL COMMENT "Cart ID",
                `id_order` int(10) COMMENT "Order ID",
                `manifest_id` varchar(30) COMMENT "Manifest identifier on Venipak system",
                `order_weight` float(10) COMMENT "Order weight" DEFAULT 0,
                `cod_amount` float(10) COMMENT "COD amount (default is order total)" DEFAULT 0,
                `packages` int(10) COMMENT "Number of packages used for the order" DEFAULT 1,
                `is_cod` tinyint(1) COMMENT "Determines if shipment is C.O.D" DEFAULT 0,
                `id_carrier_ref` int(10) COMMENT "Carrier reference ID",
                `country_code` varchar(5) NOT NULL COMMENT "Country code used for terminals list",
                `terminal_id` int(10) COMMENT "Terminal ID",
                `warehouse_id` int(10) COMMENT "Warehouse ID from which order will be shipped",
                `terminal_info` text COLLATE utf8_unicode_ci NULL COMMENT "Selected terminal\'s name, company_code, country, city, address, post_code",
                `last_select` datetime NOT NULL COMMENT "Date when last time terminal/courier changed",
                `status` varchar(30) COMMENT "Status for module of current order",
                `other_info` text COLLATE utf8_unicode_ci NULL COMMENT "Json of other order settings array",
                `labels_numbers` text COLLATE utf8_unicode_ci NULL COMMENT "Json of labels numbers array",
                `labels_date` datetime DEFAULT NULL COMMENT "Date when created labels",
                `error` text COLLATE utf8_unicode_ci DEFAULT NULL COMMENT "Order error messages",
                PRIMARY KEY (`id_cart`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

            $this->_table_warehouses => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->_table_warehouses . '` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(60) NOT NULL,
                `company_code` varchar(16) NOT NULL,
                `contact` varchar(40) NOT NULL,
                `country_code` varchar(5),
                `city` varchar(50) NOT NULL,
                `address` varchar(255) NOT NULL,
                `zip_code` varchar(10) NOT NULL,
                `id_shop` int(10) NOT NULL,
                `phone` varchar(15) NOT NULL,
                `default_on` tinyint NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

            $this->_table_manifests => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->_table_manifests  . '` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `manifest_id` varchar(40) NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `id_warehouse` int(10) unsigned DEFAULT 0,
                `shipment_weight` float(10) DEFAULT 0,
                `call_comment` varchar(255) DEFAULT NULL,
                `arrival_date_from` datetime DEFAULT NULL,
                `arrival_date_to` datetime DEFAULT NULL,
                `closed` tinyint(1) COMMENT "Specifies if manifest is closed" DEFAULT 0,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
        );
        foreach ($sql as $query) {
            try {
                $res_query = Db::getInstance()->execute($query);

                if ($res_query === false) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete module tables
     */
    public function deleteTables()
    {
        $sql = array(
            //'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->_table_orders . '`',
        );

        foreach ($sql as $query) {
            try {
                $res_query = Db::getInstance()->execute($query);
            } catch (Exception $e) {
            }
        }

        return true;
    }

    /**
     * Check if table exists
     */
    private function checkTable($table)
    {
        $checker = Db::getInstance()->executeS('SHOW TABLES LIKE "' . pSQL($table) . '"');

        if (empty($checker)) {
            throw new Exception('Database table "' . $table . '" not exists.');
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get value from table
     */
    public function getValue($table_name, $get_column, $where, $where_condition = 'AND')
    {
        if (
            !is_array($where)
            || !$this->checkTable(_DB_PREFIX_ . $table_name)
        ) {
            return false;
        }
        $sql_where = '';
        foreach ($where as $key => $value) {
            if (!empty($sql_where)) {
                $sql_where .= ' ' . pSQL($where_condition) . ' ';
            }
            $sql_where .= pSQL($key) . ' = ' . pSQL($value);
        }

        $result = Db::getInstance()->getValue("SELECT " . pSQL($get_column) . " FROM " . _DB_PREFIX_ . $table_name . " WHERE " . $sql_where);

        return $result;
    }

    /**
     * Get row from table
     */
    public function getRow($table_name, $get_column, $where, $where_condition = 'AND')
    {
        if (
            !is_array($where)
            || !$this->checkTable(_DB_PREFIX_ . $table_name)
        ) {
            return false;
        }
        $sql_where = '';
        foreach ($where as $key => $value) {
            if (!empty($sql_where)) {
                $sql_where .= ' ' . pSQL($where_condition) . ' ';
            }
            $sql_where .= pSQL($key) . ' = ' . pSQL($value);
        }

        $result = Db::getInstance()->getRow("SELECT " . pSQL($get_column) . " FROM " . _DB_PREFIX_ . $table_name . " WHERE " . $sql_where);

        return $result;
    }

    /**
     * Insert row to table
     */
    public function insertRow($table_name, $sql_values)
    {
        if (!$this->checkTable(_DB_PREFIX_ . $table_name)) {
            return false;
        }

        foreach ($sql_values as $key => $value) {
            $sql_values[$key] = pSQL(trim($value));
        }

        $result = Db::getInstance()->insert($table_name, $sql_values);

        return $result;
    }

    /**
     * Update row in table
     */
    public function updateRow($table_name, $sql_values, $where_values, $where_condition = 'AND')
    {
        if (
            !$this->checkTable(_DB_PREFIX_ . $table_name)
            || !$this->getOrderValue(1, $where_values)
        ) {
            return false;
        }

        foreach ($sql_values as $key => $value) {
            $sql_values[$key] = pSQL(trim($sql_values[$key]));
        }

        $sql_where = '';
        foreach ($where_values as $key => $value) {
            if (!empty($sql_where)) {
                $sql_where .= ' ' . pSQL($where_condition) . ' ';
            }
            $sql_where .= pSQL($key) . ' = ' . pSQL($value);
        }

        $result = Db::getInstance()->update($table_name, $sql_values, $sql_where);

        return $result;
    }

    /**
     * Get order id from module table
     */
    public function getOrderIdByCartId($cart_id)
    {
        if (!$this->checkTable(_DB_PREFIX_ . $this->_table_orders)) {
            return false;
        }

        $order_id = Db::getInstance()->getValue("SELECT id_order FROM " . _DB_PREFIX_ . $this->_table_orders . " WHERE id_cart = " . pSQL($cart_id));

        return $order_id;
    }

    /**
     * Get table value from module 'orders' table
     */
    public function getOrderValue($get_column, $where, $where_condition = 'AND')
    {
        return $this->getValue($this->_table_orders, $get_column, $where, $where_condition);
    }

    /**
     * Get table value from module 'manifests' table
     */
    public function getManifestValue($get_column, $where, $where_condition = 'AND')
    {
        return $this->getValue($this->_table_manifests, $get_column, $where, $where_condition);
    }

    /**
     * Insert row to module 'orders' table
     */
    public function saveOrderInfo($sql_values)
    {
        return $this->insertRow($this->_table_orders, $sql_values);
    }

    /**
     * Update row in module 'orders' table
     */
    public function updateOrderInfo($identifier, $sql_values, $where = 'id_cart')
    {
        return $this->updateRow($this->_table_orders, $sql_values, array($where => $identifier));
    }

    /**
     * Update row in module 'orders' table
     */
    public function getOrderInfo($order_id, $sql_values = '*')
    {
        return $this->getRow($this->_table_orders, $sql_values, array('id_order' => $order_id));
    }

    /**
     * Get table value from module 'warehouses' table
     */
    public function getWarehouseValue($get_column, $where, $where_condition = 'AND')
    {
        return $this->getValue($this->_table_warehouses, $get_column, $where, $where_condition);
    }

    /**
     * Get table row from module 'warehouses' table
     */
    public function getWarehouseRow($get_column, $where, $where_condition = 'AND')
    {
        return $this->getRow($this->_table_warehouses, $get_column, $where, $where_condition);
    }
}
