<?php

require_once "MjvpBase.php";

class MjvpModuleConfig extends MjvpBase
{

    /**
     * Get config key from all keys list
     */
    public function getConfigKey($key_name, $section = '')
    {
        if (empty($section)) {
            foreach ($this->module->_configKeys as $section_key => $section_values) {
                foreach ($section_values as $key => $value) {
                    if ($key === $key_name) {
                        $section = $section_key;
                        break 2;
                    }
                }
            } 
        }
        return isset($this->module->_configKeys[$section][$key_name]) ? $this->module->_configKeys[$section][$key_name] : '';
    }

    /**
     * Get config key from all keys list
     */
    public function getConfigKeyOther($key_name)
    {
        return isset($this->module->_configKeysOther[$key_name]['key']) ? $this->module->_configKeysOther[$key_name]['key']  : '';
    }
}
