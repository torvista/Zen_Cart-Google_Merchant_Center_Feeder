<?php

declare(strict_types=1);
/**
 * @package Google Merchant Center
 * @link https://github.com/torvista/Zen_Cart-Google_Merchant_Center_Feeder
 * @author: torvista 01 May 2023
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @copyright Copyright 2007 Numinix Technology http://www.numinix.com
 * @author original Numinix Technology
 * @since 1.6.0
 * @version 1.6.0
 */

if (!function_exists('google_cfg_pull_down_currencies')){
    /**
     * @param $currencies_id
     * @param string $key
     * @return string
     */
    function google_cfg_pull_down_currencies($currencies_id, string $key = ''): string
    {
		global $db;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$currencies = $db->Execute('SELECT code FROM ' . TABLE_CURRENCIES);
		$currencies_array = [];
		while (!$currencies->EOF) {
			$currencies_array[] = [
                'id' => $currencies->fields['code'],
                'text' => $currencies->fields['code']
            ];
			$currencies->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $currencies_array, $currencies_id);
	}
}

if (!function_exists('google_cfg_pull_down_country_iso3_list')){
    /**
     * @param $countries_id
     * @param string $key
     * @return string
     */
    function google_cfg_pull_down_country_iso3_list($countries_id, string $key = ''): string
    {
		global $db;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$countries = $db->Execute('SELECT countries_id, countries_iso_code_3 FROM ' . TABLE_COUNTRIES . ' ORDER BY countries_iso_code_3');//steve added ordering
		$countries_array = [];
		while (!$countries->EOF) {
			$countries_array[] = [
                'id' => $countries->fields['countries_id'],
                'text' => $countries->fields['countries_iso_code_3']
            ];
			$countries->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $countries_array, $countries_id);
	}
}

if (!function_exists('google_cfg_pull_down_languages_list')){
    /**
     * @param $languages_id
     * @param string $key
     * @return string
     */
    function google_cfg_pull_down_languages_list($languages_id, string $key = ''): string
    {
		global $db;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$languages = $db->Execute('SELECT code, name, languages_id FROM ' . TABLE_LANGUAGES);
		$languages_array = [];
		while (!$languages->EOF) {
			$languages_array[] = [
                'id' => $languages->fields['languages_id'],
                'text' => $languages->fields['name']
            ];
			$languages->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $languages_array, $languages_id);
	}
}
