<?php

class AdminOrdersController extends AdminOrdersControllerCore
{
    /**
     * Add bulk actions for module 'Venipak' by Mijora
     */
    public function addMjvpBulkAction($key, $config)
    {
        if (!is_array($this->bulk_actions)) {
            $this->bulk_actions = array();
        }

        $this->bulk_actions[$key] = $config;
    }
}
