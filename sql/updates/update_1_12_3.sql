SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Max Execution Time', 'GOOGLE_PRODUCTS_MAX_EXECUTION_TIME', '300', 'Override your PHP configuration by entering a max execution time in seconds for the tool (leave blank to disable):', @configuration_group_id, 13, NOW(), NULL, NULL),
(NULL, 'Memory Limit', 'GOOGLE_PRODUCTS_MEMORY_LIMIT', '128', 'Override your PHP configuration by entering a memory limit in megabytes for the tool (leave blank to disable):', @configuration_group_id, 14, NOW(), NULL, NULL);