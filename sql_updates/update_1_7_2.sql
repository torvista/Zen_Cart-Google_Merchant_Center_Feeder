SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Use Meta Title', 'GOOGLE_BASE_META_TITLE', 'false', 'Use meta title as the title if it exists (for products only)?', @configuration_group_id, 40, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Select Shipping Method', 'GOOGLE_BASE_SHIPPING_METHOD', 'none', 'Select a shipping method from the drop-down list that is used in your store, or leave as none', @configuration_group_id, 50, NOW(), NULL, 'zen_cfg_select_option(array(\'zones table rate\', \'flat rate\', \'per item\', \'per weight unit\', \'table rate\', \'zones\', \'percategory\', \'free shipping\', \'free rules shipping\', \'none\'),'),
(NULL, 'Table Zone ID', 'GOOGLE_BASE_RATE_ZONE', '', 'Enter the table rate ID if using a shipping method that uses table rates:', @configuration_group_id, 51, NOW(), NULL, NULL), 
(NULL, 'Shipping Country', 'GOOGLE_BASE_SHIPPING_COUNTRY', '', 'Select the destination country for the shipping rates:', @configuration_group_id, 52, NOW(), NULL, 'zen_cfg_pull_down_country_list('),
(NULL, 'Shipping Region', 'GOOGLE_BASE_SHIPPING_REGION', '', 'Enter the destination region within the selected country (state code, or zip with wildcard *):', @configuration_group_id, 53, NOW(), NULL, NULL),
(NULL, 'Shipping Service', 'GOOGLE_BASE_SHIPPING_SERVICE', '', 'Enter the shipping service type (i.e. Ground):', @configuration_group_id, 54, NOW(), NULL, NULL);