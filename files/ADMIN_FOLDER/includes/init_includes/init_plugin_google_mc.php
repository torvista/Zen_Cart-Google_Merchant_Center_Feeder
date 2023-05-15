<?php

declare(strict_types=1);
/**
 * @package Google Merchant Center
 * @link https://github.com/torvista/Zen_Cart-Google_Merchant_Center_Feeder
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @author: torvista 30 April 2023
 * @since 1.6.0
 * @version 1.6.0
 */


// -----
// Wait until an admin is logged in before installing or updating ...
//
if (!isset($_SESSION['admin_id'])) {
    return;
}

//Google Merchant Center Plugin Version
$plugin_google_mc_version = '1.6.0 beta01';

//version needs to be in db for storefront popup to access it
$plugin_version = $db->Execute('SELECT configuration_value FROM ' . TABLE_CONFIGURATION . ' WHERE configuration_key = "GOOGLE_PRODUCTS_VERSION" LIMIT 1');
if (!$plugin_version->EOF && $plugin_version->fields['configuration_value'] !== $plugin_google_mc_version) {
    $db->Execute('UPDATE ' . TABLE_CONFIGURATION . ' SET configuration_value = "' . $plugin_google_mc_version . '" WHERE configuration_key = "GOOGLE_PRODUCTS_VERSION" LIMIT 1');
    $messageStack->add('plugin Google MC: version updated to ' . GOOGLE_PRODUCTS_VERSION, 'success');
}
//disabled auto install-upgrade for now
return;

/**
 * @param string $constant_name
 * @return void
 */
function _remove_constant(string $constant_name = '')
{
    global $db, $messageStack;
    if ($constant_name === '') {
        return;
    }
    $db->Execute('DELETE FROM ' . TABLE_CONFIGURATION . ' WHERE configuration_key = "' . $constant_name . '" LIMIT 1');
    $messageStack->add('plugin Google MC: obsolete configuration_key "' . $constant_name . '" removed from database', 'success');
}

/**
 * @param string $constant_name_old
 * @param string $constant_name_new
 * @return void
 */
function _rename_constant(string $constant_name_old = '', string $constant_name_new = '')
{
    global $db, $messageStack;
    if ($constant_name_old === '' || $constant_name_new === '') {
        return;
    }
    $db->Execute('UPDATE ' . TABLE_CONFIGURATION . ' SET configuration_key = "' . $constant_name_new . '" WHERE configuration_key = "' . $constant_name_old . '"');
    $messageStack->add('plugin Google MC: obsolete configuration_key "' . $constant_name_old . '" renamed to "' . $constant_name_old . '" in database', 'success');
}

//UPGRADE
if (defined('GOOGLE_PRODUCTS_VERSION')) {

//REMOVE legacy version constant pre 1.6.0: moved to language define
    if (defined('GOOGLE_PRODUCTS_DESCRIPTION')) {
        _remove_constant('GOOGLE_PRODUCTS_DESCRIPTION');
    }

//REMOVE legacy offset pre 1.6.0: no default offset
    if (defined('GOOGLE_PRODUCTS_START_PRODUCTS')) {
        _remove_constant('GOOGLE_PRODUCTS_START_PRODUCTS');
    }

//end UPGRADE
}
/////////////////////////////////////////////

