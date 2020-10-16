ALTER TABLE products ADD products_upc varchar(32) NULL default NULL after products_model; 
ALTER TABLE products ADD products_isbn varchar(32) NULL default NULL after products_upc;

SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Froogle Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'UPC', 'GOOGLE_BASE_UPC', 'false', 'Include products UPC?', @configuration_group_id, 31, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'UPC', 'GOOGLE_BASE_ISBN', 'false', 'Include products ISBN?', @configuration_group_id, 32, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');