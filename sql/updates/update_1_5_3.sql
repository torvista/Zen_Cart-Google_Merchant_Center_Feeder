DELETE FROM configuration WHERE configuration_key='GOOGLE_BASE_UPC';
DELETE FROM configuration WHERE configuration_key='GOOGLE_BASE_ISBN';

SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Auction Site Attributes', 'GOOGLE_BASE_ASA', 'false', 'Activate Auction Site Attributes?', @configuration_group_id, 2, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
