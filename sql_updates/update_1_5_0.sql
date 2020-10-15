SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Store Address', 'GOOGLE_BASE_ADDRESS', 'http://www.domain.com', 'Enter your website address', @configuration_group_id, 3, NOW(), NULL, NULL),
(NULL, 'Store Description', 'GOOGLE_BASE_DESCRIPTION', '', 'Enter a short description of your store', @configuration_group_id, 3, NOW(), NULL, NULL);