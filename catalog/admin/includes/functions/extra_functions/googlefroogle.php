<?php
/**
 * googlefroogle.php
 *
 * @package google froogle
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: googlefroogle.php 60 2011-05-31 21:18:45Z numinix $
 */

if (!function_exists('google_cfg_pull_down_currencies')){
	function google_cfg_pull_down_currencies($currencies_id, $key = '') {
		global $db;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$currencies = $db->execute("select code from " . TABLE_CURRENCIES);
		$currencies_array = array();
		while (!$currencies->EOF) {
			$currencies_array[] = array('id' => $currencies->fields['code'],
																'text' => $currencies->fields['code']);
			$currencies->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $currencies_array, $currencies_id);
	}
}

if (!function_exists('google_cfg_pull_down_country_iso3_list')){
	function google_cfg_pull_down_country_iso3_list($countries_id, $key = '') {
		global $db;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$countries = $db->execute("select countries_id, countries_iso_code_3 from " . TABLE_COUNTRIES);
		$countries_array = array();
		while (!$countries->EOF) {
			$countries_array[] = array('id' => $countries->fields['countries_id'],
																'text' => $countries->fields['countries_iso_code_3']);
			$countries->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $countries_array, $countries_id);
	}
} 

if (!function_exists('google_cfg_pull_down_languages_list')){
	function google_cfg_pull_down_languages_list($languages_id, $key = '') {
		global $db;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$languages = $db->execute("select code, name, languages_id from " . TABLE_LANGUAGES);
		$languages_array = array();
		while (!$languages->EOF) {
			$languages_array[] = array('id' => $languages->fields['languages_id'],
																'text' => $languages->fields['name']);
			$languages->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $languages_array, $languages_id);
	}
}
?>