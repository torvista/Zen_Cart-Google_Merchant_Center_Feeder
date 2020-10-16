DELETE FROM configuration WHERE configuration_key = 'GOOGLE_FROOGLE_TAX_REGION' LIMIT 1;
UPDATE configuration SET configuration_key = 'GOOGLE_BASE_TAX_DISPLAY' WHERE configuration_key = 'GOOGLE_FROOGLE_TAX_DISPLAY' LIMIT 1;

SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;

SET @security_key = SUBSTR(MD5(RAND()),1,10); 

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES 
(NULL, 'Security Key', 'GOOGLE_BASE_KEY', @security_key, 'Enter a random string of numbers and characters to ensure only the admin accesses the file', @configuration_group_id, 0, NOW(), NULL, NULL),
(NULL, 'Included Manufacturers', 'GOOGLE_BASE_POS_MANUFACTURERS', '', 'Enter manufacturer ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to allow all categories', @configuration_group_id, 31, NOW(), NULL, NULL),
(NULL, 'Excluded Manufacturers', 'GOOGLE_BASE_NEG_MANUFACTURERS', '', 'Enter manufacturer ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to deactivate', @configuration_group_id, 31, NOW(), NULL, NULL),

(NULL, 'Display Tax', 'GOOGLE_BASE_TAX_DISPLAY', 'false', 'Display tax per product? (US only)', @configuration_group_id, 21, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Tax Country', 'GOOGLE_BASE_TAX_COUNTRY', 'US', 'The country an item is taxed in', @configuration_group_id, 22, NOW(), NULL, NULL),
(NULL, 'Tax Region', 'GOOGLE_BASE_TAX_REGION', 'CA', 'The geographic region that a tax rate applies to, e.g., in the US, the two-letter state abbreviation, ZIP code, or ZIP code range using * wildcard (examples: CA, 946*)', @configuration_group_id, 22, NOW(), NULL, NULL),
(NULL, 'Tax Rate', 'GOOGLE_BASE_TAX_RATE', '5.00', 'Enter the percentage as a decimal number (without "%" symbol)', @configuration_group_id, 22, NOW(), NULL, NULL),
(NULL, 'Tax on Shipping', 'GOOGLE_BASE_TAX_SHIPPING', 'n', 'Boolean value for whether you charge tax on shipping, y for yes or n for no - the default value is n', @configuration_group_id, 22, NOW(), NULL, 'zen_cfg_select_option(array(\'y\', \'n\'),');

UPDATE configuration SET configuration_description = 'Enter category ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to allow all categories' WHERE configuration_key = 'GOOGLE_BASE_POS_CATEGORIES' LIMIT 1;
UPDATE configuration SET configuration_description = 'Enter category ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to deactivate' WHERE configuration_key = 'GOOGLE_BASE_NEG_CATEGORIES' LIMIT 1;