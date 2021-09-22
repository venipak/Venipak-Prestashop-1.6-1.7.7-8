<?php

abstract class MjvpBase
{
    protected $module;

    public static $moduleInstance;


    public function __construct()
    {
        if(self::$moduleInstance)
        {
            $this->module = self::$moduleInstance;
        }
        else
        {
            $module = new MijoraVenipak();
            $this->module = $module;
            self::$moduleInstance = $module;
        }
    }
}