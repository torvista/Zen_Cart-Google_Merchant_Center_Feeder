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

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
$autoLoadConfig[200][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_plugin_google_mc.php'
];
