UPDATE configuration SET configuration_title = 'Numinix Product Fields' WHERE configuration_key = 'GOOGLE_BASE_ASA' LIMIT 1;

SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'UPC/ISBN', 'GOOGLE_BASE_ASA_UPC', 'false', 'If using Numinix Product Fields, include UPC/ISBN?', @configuration_group_id, 2, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Description 2', 'GOOGLE_BASE_ASA_DESCRIPTION_2', 'false', 'If using Numinix Product Fields, append description 2 to description?', @configuration_group_id, 2, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');