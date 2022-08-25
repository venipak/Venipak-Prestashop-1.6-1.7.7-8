<?php

class MijoraVenipakCarriersModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if(Tools::isSubmit('id_carrier')) {
            $id_carrier = (int) Tools::getValue('id_carrier');
            $ps_carrier = new Carrier($id_carrier);
            $mjvpVenipak = Module::getInstanceByName('mijoravenipak');
            $content = $mjvpVenipak->hookDisplayCarrierExtraContent(
                [
                    'cart' => $this->context->cart,
                    'carrier' => (array) $ps_carrier
                ]
            );
            die(json_encode([
                'carrier_content' => $content,
                'mjvp_map_template' => $this->context->smarty->fetch(MijoraVenipak::$_moduleDir . 'views/templates/front/map-template.tpl'),
            ]));
        }
    }
}