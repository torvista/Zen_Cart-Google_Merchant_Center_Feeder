SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Include Zero Quantity', 'GOOGLE_BASE_ZERO_QUANTITY', 'false', 'Include products with zero quantity?', @configuration_group_id, 7, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');