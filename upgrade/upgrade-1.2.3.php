<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Venipak test (UAT) API server address has changed.
 * Replace the old default address with the new one, but only where it was left unchanged by the user.
 *
 * @param MijoraVenipak $module
 * @return bool
 */
function upgrade_module_1_2_3($module)
{
    $config_key = 'MJVP_TEST_API_SERVER';
    $old_values = array(
        'https://venipak.uat.megodata.com/',
        'https://venipak.uat.megodata.com',
    );
    $new_value = MijoraVenipak::DEFAULT_TEST_API_SERVER;

    // Collect all configuration scopes (global, shop groups and shops), because in multistore value can be set on each of them
    $scopes = array(
        array('id_shop_group' => 0, 'id_shop' => 0),
    );
    foreach (ShopGroup::getShopGroups(false) as $shop_group) {
        $scopes[] = array('id_shop_group' => (int) $shop_group->id, 'id_shop' => 0);
    }
    foreach (Shop::getShops(false) as $shop) {
        $scopes[] = array('id_shop_group' => (int) $shop['id_shop_group'], 'id_shop' => (int) $shop['id_shop']);
    }

    $result = true;
    foreach ($scopes as $scope) {
        $id_shop_group = $scope['id_shop_group'];
        $id_shop = $scope['id_shop'];

        // Check only values stored exactly in this scope (Configuration::get would return inherited value)
        $has_key = ($id_shop)
            ? Configuration::hasKey($config_key, null, null, $id_shop)
            : Configuration::hasKey($config_key, null, $id_shop_group, null);
        if (!$has_key) {
            continue;
        }

        $current_value = Configuration::get($config_key, null, $id_shop_group, $id_shop);
        if (!in_array($current_value, $old_values, true)) {
            continue;
        }

        $result &= Configuration::updateValue($config_key, $new_value, false, $id_shop_group, $id_shop);
    }

    return (bool) $result;
}
