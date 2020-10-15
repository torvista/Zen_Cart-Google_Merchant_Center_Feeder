SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Payments Accepted', 'GOOGLE_BASE_PAYMENT_METHODS', 'Cash,Check,Visa,MasterCard,AmericanExpress,Discover,WireTransfer,GoogleCheckout', 'What payment methods do you accept?', @configuration_group_id, 35, NOW(), NULL, NULL);