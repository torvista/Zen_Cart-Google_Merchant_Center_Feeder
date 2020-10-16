UPDATE configuration SET configuration_value = '1.14.0' WHERE configuration_key = 'GOOGLE_PRODUCTS_VERSION' LIMIT 1;

SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Third Party Inventory Plugin', 'GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN', 'none', 'Does your system use a third party plugin for managing variant/attribute inventory?  Select the plugin used or leave as \'none\' for default action:', @configuration_group_id, 560, NOW(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\' => \'none\', \'text\' => \'none\'), array(\'id\' => \'stockbyattributes\', \'text\' => \'Stock By Attributes\'), array(\'id\' => \'numinixproductvariants\', \'text\' => \'Numinix Product Variants\')),');