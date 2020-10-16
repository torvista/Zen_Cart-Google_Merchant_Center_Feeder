SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Product Type', 'GOOGLE_BASE_PRODUCT_TYPE', 'top', 'Use top-level, bottom-level, or full-path as product_type?', @configuration_group_id, 34, NOW(), NULL, 'zen_cfg_select_option(array(\'top\', \'bottom\', \'full\'),'),
(NULL, 'Starting Point', 'GOOGLE_BASE_START_PRODUCTS', '0', 'Start at which entry (not product_id)?<br />Default=0', @configuration_group_id, 24, NOW(), NULL, NULL);