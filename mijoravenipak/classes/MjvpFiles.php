<?php

require_once "MjvpBase.php";

class MjvpFiles extends MjvpBase
{
    /**
     * File info for countries list
     */
    private $_countriesList = array(
        'directory' => 'data',
        'file_name' => 'countries.json',
    );

    /**
     * File info for terminals list
     */
    private $_terminalsList = array(
        'directory' => 'data',
        'file_name' => 'terminals_%s.json', // %s - for country code
    );

    /**
     * Periodic time for update files
     */
    private $_updateEvery = 24 * 3600;

    /**
     * Get countries list from file
     */
    public function getCountriesList()
    {
        $file_dir = $this->getFileDir($this->_countriesList['directory'], $this->_countriesList['file_name']);

        if ($this->checkFile($file_dir)) {
            return json_decode($this->getFileContent($file_dir), true);
        }

        return false;
    }

    /**
     * Update countries list file
     */
    public function updateCountriesList($forced = false)
    {
        $file_dir = $this->getFileDir($this->_countriesList['directory'], $this->_countriesList['file_name']);
        $last_update = Configuration::getGlobalValue(MijoraVenipak::$_globalConstants['last_upd_countries']);

        if ($last_update === false || !$this->checkFile($file_dir)) {
            $last_update = 0;
        }

        if (
            $last_update == 0
            || ($last_update + $this->_updateEvery) < time()
            || $forced
        ) {

            $cHelper = $this->module->getModuleService('MjvpHelper');
            Configuration::updateGlobalValue(MijoraVenipak::$_globalConstants['last_upd_countries'], time());

            $all_countries = $cHelper->getAllCountries();
            $this->generateFile($file_dir, json_encode($all_countries));

            return true;
        }

        return false;
    }

    /**
     * Get terminals list for specific country from file
     */
    public function getTerminalsListForCountry($country_code, $assoc = true, $filters = [])
    {
        $file_dir = $this->getFileDir($this->_terminalsList['directory'], str_replace('%s', strtoupper($country_code), $this->_terminalsList['file_name']));

        if (!$this->checkFile($file_dir)) {
            $this->updateCountriesList();
            $this->updateTerminalsList();
        }
        $terminals = json_decode($this->getFileContent($file_dir), $assoc);

        if(!empty($terminals) && !empty($filters))
        {
            $filtered_terminals = [];
            foreach ($terminals as $terminal)
            {
                $terminal_fit = true;
                foreach ($filters as $fiter_key => $filter_value)
                {
                    if($assoc)
                    {
                        if(isset($terminal[$fiter_key]) && $terminal[$fiter_key] != $filter_value)
                        {
                            $terminal_fit = false;
                            break;
                        }
                    }
                    else
                    {
                        if(isset($terminal->{$fiter_key}) && $terminal->{$fiter_key} != $filter_value)
                        {
                            $terminal_fit = false;
                            break;
                        }
                    }
                }
                if($terminal_fit)
                    $filtered_terminals[] = $terminal;
            }
            return $filtered_terminals;
        }

        return $terminals;
    }

    /**
     * Update terminals lists for all selected (in settings) countries
     */
    public function updateTerminalsList($forced = false)
    {
        $cHelper = $this->module->getModuleService('MjvpHelper');
        $cApi = $this->module->getModuleService('MjvpApi');

        $last_update = Configuration::getGlobalValue(MijoraVenipak::$_globalConstants['last_upd_terminals']);

        $selected_countries = explode(';', Configuration::get('MJVP_PP_COUNTRIES'));
        if (!Configuration::get('MJVP_PP_COUNTRIES') || !$selected_countries || empty($selected_countries)) {
            $selected_countries = MijoraVenipak::$_defaultPickupCountries;
        }

        for ($i = 0; $i < count($selected_countries); $i++) {
            if (is_numeric($selected_countries[$i])) {
                $selected_countries[$i] = $cHelper->getCountryCodeFromId($selected_countries[$i]);
            }
        }

        if ($last_update === false) {
            $last_update = 0;
        }

        foreach ($selected_countries as $country_code) {
            if (is_numeric($country_code)) {
                $country_code = $cHelper->getCountryCodeFromId($country_code);
            }
            $file_dir = $this->getFileDir($this->_terminalsList['directory'], str_replace('%s', strtoupper($country_code), $this->_terminalsList['file_name']));
            if (!$this->checkFile($file_dir)) {
                $last_update = 0;
                break;
            }
        }

        if (
            $last_update == 0
            || ($last_update + $this->_updateEvery) < time()
            || $forced
        ) {
            foreach ($selected_countries as $country_code) {
                if (is_numeric($country_code)) {
                    $country_code = $cHelper->getCountryCodeFromId($country_code);
                }
                $file_dir = $this->getFileDir($this->_terminalsList['directory'], str_replace('%s', strtoupper($country_code), $this->_terminalsList['file_name']));

                $country_terminals = $cApi->getTerminals($country_code);
                $this->generateFile($file_dir, json_encode($country_terminals));
            }

            Configuration::updateGlobalValue(MijoraVenipak::$_globalConstants['last_upd_terminals'], time());
            return true;
        }

        return false;
    }

    /**
     * Copy order status icon to right position
     */
    public function setStateIcon($icon_file_name, $state_id)
    {
        $icon_dir = $this->getFileDir('views/images', $icon_file_name);
        $theme_dir = _PS_THEME_DIR_ . 'assets/img/os/';

        if (!$this->checkFile($icon_dir)) {
            return false;
        }

        $file_pathinfo = pathinfo($icon_dir);
        $new_file_name = $state_id . '.' . $file_pathinfo['extension'];

        return $this->copyFile($icon_dir, $theme_dir . $new_file_name);
    }

    /**
     * Function for file creation
     */
    private function generateFile($file_dir, $file_content)
    {
        $path = pathinfo($file_dir);
        if (!file_exists($path['dirname'])) {
            mkdir($path['dirname'], 0755, true);
        }
        $file = fopen($file_dir, "w");
        if (!$file) return false;
        fwrite($file, $file_content);
        fclose($file);
        return true;
    }

    /**
     * Function for file copy
     */
    private function copyFile($file_dir, $target_dir)
    {
        if (!$this->checkFile($file_dir)) {
            return false;
        }

        $target_path = pathinfo($target_dir);
        if (!file_exists($target_path['dirname'])) {
            mkdir($target_path['dirname'], 0755, true);
        }

        return copy($file_dir, $target_dir);
    }

    /**
     * Function to check if file exists
     */
    private function checkFile($file_dir)
    {
        if (!is_file($file_dir) || !file_get_contents($file_dir) || !$this->isJson(file_get_contents($file_dir))) {
            return false;
        }
        return true;
    }

    /**
     * Function to build file path
     */
    private function getFileDir($directory, $file_name)
    {
        $directory = (substr($directory, 0, 1) === '/') ? substr($directory, 1) : $directory;
        return MijoraVenipak::$_moduleDir . $directory . '/' . $file_name;
    }

    /**
     * Function to get content from file
     */
    private function getFileContent($file_dir)
    {
        if ($this->checkFile($file_dir)) {
            return file_get_contents($file_dir);
        }
        return '';
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
