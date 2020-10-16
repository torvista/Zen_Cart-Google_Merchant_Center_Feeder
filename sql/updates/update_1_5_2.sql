SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Show Weight', 'GOOGLE_BASE_WEIGHT', 'false', 'Include products weight?', @configuration_group_id, 33, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Weight Units', 'GOOGLE_BASE_UNITS', 'pounds', 'What unit of weight measure?<br />pounds OR kilograms', @configuration_group_id, 33, NOW(), NULL, 'zen_cfg_select_option(array(\'pounds\', \'kilograms\'),');

UPDATE configuration
	SET set_function = 'zen_cfg_select_option(array(\'id\', \'model\', \'UPC\', \'ISBN\', \'false\'),'
	WHERE configuration_title = 'Show Offer ID'
	AND configuration_key = 'GOOGLE_FROOGLE_OFFER_ID'
	AND set_function = 'zen_cfg_select_option(array(\'id\', \'model\', \'false\'),'
	LIMIT 1;