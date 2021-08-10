<?php

if (!defined('_PS_VERSION_')) {
    return;
}

class MjvpDb
{
    /**
     * Database tables list
     */
    private $_table_orders = 'mjvp_orders';
    private $_table_warehouses = 'mjvp_warehouse';
    private $_table_cart = 'mjvp_cart';

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
            `country_code` varchar(255) NOT NULL COMMENT "Country code used for terminals list",
            `terminal_id` int(10) COMMENT "Terminal ID",
            `last_select` datetime NOT NULL COMMENT "Date when last time terminal/courier changed",
            `status` varchar(255) COMMENT "Status for module of current order",
            `labels_numbers` text COLLATE utf8_unicode_ci NULL COMMENT "Json of labels numbers array",
            `labels_date` datetime DEFAULT NULL COMMENT "Date when created labels",
            `error` text COLLATE utf8_unicode_ci DEFAULT NULL COMMENT "Order error messages",
            PRIMARY KEY (`id_cart`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->_table_warehouses . '` (
            `id_warehouse` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `warehouse_name` varchar(255) NOT NULL,
            `company_code` varchar(32) NOT NULL,
            `contact` varchar(255) NOT NULL,
            `country_code` varchar(3),
            `city` varchar(50) NOT NULL,
            `address` varchar(255) NOT NULL,
            `zip_code` int(10) NOT NULL,
            `phone` varchar(15) NOT NULL,
            `default_on` tinyint NOT NULL,
            PRIMARY KEY (`id_warehouse`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->_table_cart . '` (
            `id_mjvp_cart` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) unsigned NOT NULL,
            `door_code` varchar(10),
            `cabinet_number` varchar(10),
            `warehouse_number` varchar(10),
            `delivery_time` varchar(10),
            `date_add` datetime NOT NULL,
            `date_upd` datetime DEFAULT NULL,
            PRIMARY KEY (`id_mjvp_cart`)
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
        if (
            !is_array($where)
            || !$this->checkTable(_DB_PREFIX_ . $this->_table_orders)
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

        $result = Db::getInstance()->getValue("SELECT " . pSQL($get_column) . " FROM " . _DB_PREFIX_ . $this->_table_orders . " WHERE " . $sql_where);

        return $result;
    }

    /**
     * Insert row to module 'orders' table
     */
    public function saveOrderInfo($sql_values)
    {
        if (!$this->checkTable(_DB_PREFIX_ . $this->_table_orders)) {
            return false;
        }

        foreach ($sql_values as $key => $value) {
            $sql_values[$key] = pSQL(trim($value));
        }

        $result = Db::getInstance()->insert($this->_table_orders, $sql_values);

        return $result;
    }

    /**
     * Update row in module 'orders' table
     */
    public function updateOrderInfo($cart_id, $sql_values)
    {
        if (
            !$this->checkTable(_DB_PREFIX_ . $this->_table_orders)
            || !$this->getOrderValue(1, array('id_cart' => $cart_id))
        ) {
            return false;
        }

        foreach ($sql_values as $key => $value) {
            $sql_values[$key] = pSQL(trim($sql_values[$key]));
        }

        $result = Db::getInstance()->update($this->_table_orders, $sql_values, 'id_cart = ' . pSQL($cart_id));

        return $result;
    }
}
