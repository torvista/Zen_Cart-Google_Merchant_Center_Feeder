SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Default Quantity', 'GOOGLE_BASE_DEFAULT_QUANTITY', '0', 'What is the default quantity for products with zero quantity?', @configuration_group_id, 7, NOW(), NULL, NULL), 
(NULL, 'Payment Notes', 'GOOGLE_BASE_PAYMENT_NOTES', 'GoogleCheckout', 'Add payment notes (use this for showing you accept Google Checkout)', @configuration_group_id, 35, NOW(), NULL, NULL);