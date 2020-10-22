#version
UPDATE configuration SET configuration_value = '1.15.0' WHERE configuration_key = 'GOOGLE_PRODUCTS_VERSION' LIMIT 1;

#Add Feed file sort option
SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) FROM configuration_group WHERE configuration_group_title = 'Google Merchant Center Feeder' LIMIT 1;
#Add option to sort feed file by id or model
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES (NULL, 'Feed File Sort', 'GOOGLE_PRODUCTS_FEED_SORT', 'ID', 'Create the product feed file ordered by the product id, product model or product name.', @configuration_group_id, 9, NOW(), NULL, 'zen_cfg_select_option(array(\'ID\', \'Model\', \'Name\'),');

#Update GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN to add Products Options Stock to 3rd party attribute-stock plugins dropdown
UPDATE configuration SET configuration_title = 'Attribute-Stock Plugin',
configuration_description = 'Select the third party plugin used for managing attribute/variant stocks or leave as \'none\'.',
set_function = 'zen_cfg_select_drop_down(array(array(\'id\' => \'none\', \'text\' => \'none\'), array(\'id\' => \'stockbyattributes\', \'text\' => \'Stock By Attributes\'), array(\'id\' => \'numinixproductvariants\', \'text\' => \'Numinix Product Variants\'), array(\'id\' => \'posm\', \'text\' => \'Products Options Stock Manager\')),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN' LIMIT 1;

#Update GOOGLE_PRODUCTS_SHIPPING_METHOD to add CEON Advanced Shipper to shipping methods radio buttons
UPDATE configuration SET configuration_description = 'Select the name of the shipping module used in your store (as shown in Modules->Shipping), or leave as \'none\'.',
set_function = 'zen_cfg_select_option(array(\'none\', \'flat\', \'freeshipper\', \'item\', \'perweightunit\', \'table\', \'zones\', \'advshipper\', \'freerules\', \'percategory\', \'zonetable\'),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_SHIPPING_METHOD' LIMIT 1;

#Update GOOGLE_PRODUCTS_MAX_PRODUCTS: change text and default
UPDATE configuration SET configuration_title = 'Limit products to process', configuration_description = 'Limit the quantity of products to process/add to the feed file.<br>For testing or due to server limitations it may be necessary to produce multiple feed files. You may use this Limit in conjunction with the Start point option or override it on the Admin page.<br>Default value is empty for no limit.' WHERE configuration_key = 'GOOGLE_PRODUCTS_MAX_PRODUCTS' LIMIT 1;

#Update GOOGLE_PRODUCTS_START_PRODUCTS: change text and default
UPDATE configuration SET configuration_title = 'Start point for processing', configuration_description = 'Start processing products from this record.<br>This number relates to the quantity of products in the feed (not the product id). When the debug option is true, the feed generation process window will display the record number for use here.<br>For testing or due to server limitations it may be necessary to produce multiple feed files. You may use this start point in conjunction with the Limit option or override it on the Admin page.<br>Default start value is 1.' WHERE configuration_key = 'GOOGLE_PRODUCTS_START_PRODUCTS' LIMIT 1;

#OPTIONAL Update texts only
