UPDATE configuration SET configuration_title = 'UPC/ISBN/EAN', configuration_description = 'If using Numinix Product Fields, include UPC/ISBN/EAN?' WHERE configuration_key = 'GOOGLE_PRODUCTS_ASA_UPC' LIMIT 1;
UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'id\', \'model\', \'UPC\', \'ISBN\', \'EAN\', \'false\'),' WHERE configuration_key = 'GOOGLE_PRODUCTS_OFFER_ID' LIMIT 1;

SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Default Product Category', 'GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY', '', 'Enter a default product category from the <a href="http://www.google.com/support/merchants/bin/answer.py?answer=160081" target="_blank">Google Category Taxonomy</a> or leave blank:', @configuration_group_id, 60, NOW(), NULL, NULL);