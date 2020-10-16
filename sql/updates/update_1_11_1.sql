ALTER TABLE products_options_values MODIFY products_options_values_name varchar(150);

SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Enable Advanced XML Sanitization', 'GOOGLE_PRODUCTS_XML_SANITIZATION', 'false', 'If weird characters are causing your feed to not validate and you have already ensured your Zen Cart has been properly updated to use the UTF-8 charset, try enabling this option.  If this option is already enabled, try disabling it.', @configuration_group_id, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');