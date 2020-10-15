#Google Merchant Center FEEDER
#

ALTER TABLE products_options_values MODIFY products_options_values_name varchar(150);

SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;
DELETE FROM configuration WHERE configuration_group_id = @configuration_group_id AND @configuration_group_id != 0;
DELETE FROM configuration_group WHERE configuration_group_id = @configuration_group_id AND @configuration_group_id != 0;

INSERT INTO configuration_group (configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible) VALUES (NULL, 'Google Merchant Center Feeder Configuration', 'Set Google Merchant Center Options', '1', '1');
SET @configuration_group_id=last_insert_id();
UPDATE configuration_group SET sort_order = @configuration_group_id WHERE configuration_group_id = @configuration_group_id;

SET @security_key = SUBSTR(MD5(RAND()),1,10);

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Version', 'GOOGLE_PRODUCTS_VERSION', '1.14.7', 'Version Installed:', @configuration_group_id, 0, NOW(), NULL, NULL),
(NULL, 'Debug', 'GOOGLE_PRODUCTS_DEBUG', 'false', 'Turn on simple debug?', @configuration_group_id, 0, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'), 
(NULL, 'Google Merchant Center FTP Username', 'GOOGLE_PRODUCTS_USERNAME', 'ftp_username', 'Enter your Google Merchant Center FTP username', @configuration_group_id, 1, NOW(), NULL, NULL),
(NULL, 'Google Merchant Center FTP Password', 'GOOGLE_PRODUCTS_PASSWORD', 'ftp_password', 'Enter your Google Merchant Center FTP password', @configuration_group_id, 2, NOW(), NULL, NULL),
(NULL, 'Google Merchant Center Server', 'GOOGLE_PRODUCTS_SERVER', 'uploads.google.com', 'Enter froogle server<br />default: hedwig.google.com', @configuration_group_id, 3, NOW(), NULL, NULL),
(NULL, 'Google Merchant Center PASV', 'GOOGLE_PRODUCTS_PASV', 'true', 'Turn PASV mode on or off for FTP upload?', @configuration_group_id, 4, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Security Key', 'GOOGLE_PRODUCTS_KEY', @security_key, 'Enter a random string of numbers and characters to ensure only the admin accesses the file', @configuration_group_id, 5, NOW(), NULL, NULL),
(NULL, 'Store Address', 'GOOGLE_PRODUCTS_ADDRESS', 'http://www.domain.com', 'Enter your website address', @configuration_group_id, 6, NOW(), NULL, NULL),
(NULL, 'Store Description', 'GOOGLE_PRODUCTS_DESCRIPTION', '', 'Enter a short description of your store', @configuration_group_id, 7, NOW(), NULL, NULL),
(NULL, 'Output File Name', 'GOOGLE_PRODUCTS_OUTPUT_FILENAME', 'domain', 'Set the name of your froogle output file', @configuration_group_id, 8, NOW(), NULL, NULL),
(NULL, 'Compress Feed File', 'GOOGLE_PRODUCTS_COMPRESS', 'false', 'Compress Google Merchant Center file', @configuration_group_id, 9, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Uploaded date', 'GOOGLE_PRODUCTS_UPLOADED_DATE', '', 'Date and time of the last upload', @configuration_group_id, 10, NOW(), NULL, NULL),
(NULL, 'Output Directory', 'GOOGLE_PRODUCTS_DIRECTORY', 'feed/google/', 'Set the name of your froogle output directory', @configuration_group_id, 11, NOW(), NULL, NULL),
(NULL, 'Enable Advanced XML Sanitization', 'GOOGLE_PRODUCTS_XML_SANITIZATION', 'false', 'If weird characters are causing your feed to not validate and you have already ensured your Zen Cart has been properly updated to use the UTF-8 charset, try enabling this option.  If this option is already enabled, try disabling it.', @configuration_group_id, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),

(NULL, 'Max Execution Time', 'GOOGLE_PRODUCTS_MAX_EXECUTION_TIME', '300', 'Override your PHP configuration by entering a max execution time in seconds for the tool (leave blank to disable):', @configuration_group_id, 13, NOW(), NULL, NULL),
(NULL, 'Memory Limit', 'GOOGLE_PRODUCTS_MEMORY_LIMIT', '128M', 'Override your PHP configuration by entering a memory limit for the tool (i.e. 128M or leave blank to disable):', @configuration_group_id, 14, NOW(), NULL, NULL),

(NULL, 'Max products', 'GOOGLE_PRODUCTS_MAX_PRODUCTS', '0', 'Default = 0 for infinite # of products', @configuration_group_id, 20, NOW(), NULL, NULL),
(NULL, 'Starting Point', 'GOOGLE_PRODUCTS_START_PRODUCTS', '0', 'Start at which entry (not product_id)?<br />Default=0', @configuration_group_id, 21, NOW(), NULL, NULL),
(NULL, 'Included Categories', 'GOOGLE_PRODUCTS_POS_CATEGORIES', '', 'Enter category ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to allow all categories', @configuration_group_id, 22, NOW(), NULL, NULL),
(NULL, 'Excluded Categories', 'GOOGLE_PRODUCTS_NEG_CATEGORIES', '', 'Enter category ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to deactivate', @configuration_group_id, 23, NOW(), NULL, NULL),
(NULL, 'Included Manufacturers', 'GOOGLE_PRODUCTS_POS_MANUFACTURERS', '', 'Enter manufacturer ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to allow all categories', @configuration_group_id, 24, NOW(), NULL, NULL),
(NULL, 'Excluded Manufacturers', 'GOOGLE_PRODUCTS_NEG_MANUFACTURERS', '', 'Enter manufacturer ids separated by commas <br>(i.e. 1,2,3)<br>Leave blank to deactivate', @configuration_group_id, 25, NOW(), NULL, NULL),

(NULL, 'Expiration Date Base', 'GOOGLE_PRODUCTS_EXPIRATION_BASE', 'now', 'Expiration Date Base:<ul><li>now - add Adjust to current date;</li><li>product - add Adjust to product date (max(date_added, last_modified, date_available))</li></ul>', @configuration_group_id, 30, NOW(), NULL, 'zen_cfg_select_option(array(\'now\', \'product\'),'),
(NULL, 'Expiration Date Adjust', 'GOOGLE_PRODUCTS_EXPIRATION_DAYS', '29', 'Expiration Date Adjust in Days', @configuration_group_id, 31, NOW(), NULL, NULL),

(NULL, 'Show Default Currency', 'GOOGLE_PRODUCTS_CURRENCY_DISPLAY', 'true', 'Display Currency', @configuration_group_id, 40, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Default Currency', 'GOOGLE_PRODUCTS_CURRENCY', 'USD', 'Select currency', @configuration_group_id, 41, NOW(), NULL, 'google_cfg_pull_down_currencies('),
(NULL, 'Show Offer ID', 'GOOGLE_PRODUCTS_OFFER_ID', 'id', 'A unique alphanumeric identifier for the item - products_id code. ', @configuration_group_id, 42, NOW(), NULL, 'zen_cfg_select_option(array(\'id\', \'model\', \'UPC\', \'ISBN\', \'EAN\', \'false\'),'),
(NULL, 'Shipping Options', 'GOOGLE_PRODUCTS_SHIPPING', '', 'The shipping options available for an item', @configuration_group_id, 46, NOW(), NULL, NULL),
(NULL, 'Default Condition', 'GOOGLE_PRODUCTS_CONDITION', 'new', 'Choose your default condition', @configuration_group_id, 47, NOW(), NULL, 'zen_cfg_select_option(array(\'new\', \'used\', \'refurbished\'),'),
(NULL, 'Condition', 'GOOGLE_PRODUCTS_PRODUCT_CONDITION', 'false', 'If using Numinix Product Fields, include condition?', @configuration_group_id, 48, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Default Product Type', 'GOOGLE_PRODUCTS_DEFAULT_PRODUCT_TYPE', '', 'Enter your product type if using default', @configuration_group_id, 49, NOW(), NULL, NULL),
(NULL, 'Product Type', 'GOOGLE_PRODUCTS_PRODUCT_TYPE', 'top', 'Use top-level, bottom-level, full-path, or your default setting as product_type?', @configuration_group_id, 50, NOW(), NULL, 'zen_cfg_select_option(array(\'default\', \'top\', \'bottom\', \'full\'),'),
(NULL, 'Show Feed Language', 'GOOGLE_PRODUCTS_LANGUAGE_DISPLAY', 'false', 'Display Feed Language', @configuration_group_id, 51, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Feed Language', 'GOOGLE_PRODUCTS_LANGUAGE', '1', 'Set your feed language (required):', @configuration_group_id, 53, NOW(), NULL, 'google_cfg_pull_down_languages_list('),
(NULL, 'Show Weight', 'GOOGLE_PRODUCTS_WEIGHT', 'false', 'Include products weight?', @configuration_group_id, 53, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Weight Units', 'GOOGLE_PRODUCTS_UNITS', 'pounds', 'What unit of weight measure?<br />pounds OR kilograms', @configuration_group_id, 54, NOW(), NULL, 'zen_cfg_select_option(array(\'pounds\', \'kilograms\'),'),
(NULL, 'UPC/ISBN/EAN', 'GOOGLE_PRODUCTS_ASA_UPC', 'false', 'If using Numinix Product Fields, include UPC/ISBN/EAN?', @configuration_group_id, 55, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Description 2', 'GOOGLE_PRODUCTS_ASA_DESCRIPTION_2', 'false', 'If using Numinix Product Fields, append description 2 to description?', @configuration_group_id, 56, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Use Meta Title', 'GOOGLE_PRODUCTS_META_TITLE', 'false', 'Use meta title as the title if it exists (for products only)?', @configuration_group_id, 57, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Enable Map Pricing', 'GOOGLE_PRODUCTS_MAP_PRICING', 'false', 'Enable MAP Pricing (requires separate add-on)?', @configuration_group_id, 58, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'), 
(NULL, 'Use cPath in url', 'GOOGLE_PRODUCTS_USE_CPATH', 'false', 'Use cPath in product info url', @configuration_group_id, 59, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Google Product Category Default', 'GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY', '', 'Enter a default Google product category from the <a href="http://www.google.com/support/merchants/bin/answer.py?answer=160081" target="_blank">Google Category Taxonomy</a> or leave blank (note: you can override this default setting by creating a Google Product Category attribute as per the documentation):', @configuration_group_id, 60, NOW(), NULL, NULL),

(NULL, 'Display Tax', 'GOOGLE_PRODUCTS_TAX_DISPLAY', 'false', 'Display tax per product? (US only)', @configuration_group_id, 70, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Tax Country', 'GOOGLE_PRODUCTS_TAX_COUNTRY', 'US', 'The country an item is taxed in (2-letter ISO CODE)', @configuration_group_id, 71, NOW(), NULL, NULL),
(NULL, 'Tax Region', 'GOOGLE_PRODUCTS_TAX_REGION', 'CA', 'The geographic region that a tax rate applies to, e.g., in the US, the two-letter state abbreviation, ZIP code, or ZIP code range using * wildcard (examples: CA, 946*)', @configuration_group_id, 72, NOW(), NULL, NULL),
(NULL, 'Tax Rate', 'GOOGLE_PRODUCTS_TAX_RATE', '5.00', 'Enter the percentage as a decimal number (without "%" symbol)', @configuration_group_id, 73, NOW(), NULL, NULL),
(NULL, 'Tax on Shipping', 'GOOGLE_PRODUCTS_TAX_SHIPPING', 'n', 'Boolean value for whether you charge tax on shipping, y for yes or n for no - the default value is n', @configuration_group_id, 74, NOW(), NULL, 'zen_cfg_select_option(array(\'y\', \'n\'),'),

(NULL, 'Payments Accepted', 'GOOGLE_PRODUCTS_PAYMENT_METHODS', 'Cash,Check,Visa,MasterCard,AmericanExpress,Discover,WireTransfer', 'What payment methods do you accept?', @configuration_group_id, 80, NOW(), NULL, NULL),
(NULL, 'Payment Notes', 'GOOGLE_PRODUCTS_PAYMENT_NOTES', 'GoogleCheckout', 'Add payment notes (use this for showing you accept Google Checkout)', @configuration_group_id, 81, NOW(), NULL, NULL),

(NULL, 'Select Shipping Method', 'GOOGLE_PRODUCTS_SHIPPING_METHOD', 'none', 'Select a shipping method from the drop-down list that is used in your store, or leave as none', @configuration_group_id, 90, NOW(), NULL, 'zen_cfg_select_option(array(\'zones table rate\', \'flat rate\', \'per item\', \'per weight unit\', \'table rate\', \'zones\', \'percategory\', \'free shipping\', \'free rules shipping\', \'none\'),'),
(NULL, 'Table Zone ID', 'GOOGLE_PRODUCTS_RATE_ZONE', '', 'Enter the table rate ID if using a shipping method that uses table rates:', @configuration_group_id, 91, NOW(), NULL, NULL),  
(NULL, 'Shipping Country', 'GOOGLE_PRODUCTS_SHIPPING_COUNTRY', '', 'Select the destination country for the shipping rates:', @configuration_group_id, 92, NOW(), NULL, 'google_cfg_pull_down_country_iso3_list('),
(NULL, 'Shipping Region', 'GOOGLE_PRODUCTS_SHIPPING_REGION', '', 'Enter the destination region within the selected country (state code, or zip with wildcard *):', @configuration_group_id, 93, NOW(), NULL, NULL),
(NULL, 'Shipping Service', 'GOOGLE_PRODUCTS_SHIPPING_SERVICE', '', 'Enter the shipping service type (i.e. Ground):', @configuration_group_id, 94, NOW(), NULL, NULL),
(NULL, 'Pickup', 'GOOGLE_PRODUCTS_PICKUP', 'do not display', 'Local pickup available?', @configuration_group_id, 95, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\', \'do not display\'),'),

(NULL, 'Alternate Image URL', 'GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL', '', 'Add an alternate URL if your images are hosted offsite (i.e. http://www.domain.com/images/).  Your defined image will be appended to the end of this URL.', @configuration_group_id, 100, NOW(), NULL, NULL),
(NULL, 'Image Handler', 'GOOGLE_PRODUCTS_IMAGE_HANDLER', 'false', 'Resize images using image handler (separate module required)?', @configuration_group_id, 101, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Third Party Inventory Plugin', 'GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN', 'none', 'Does your system use a third party plugin for managing variant/attribute inventory?  Select the plugin used or leave as \'none\' for default action:', @configuration_group_id, 560, NOW(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\' => \'none\', \'text\' => \'none\'), array(\'id\' => \'stockbyattributes\', \'text\' => \'Stock By Attributes\'), array(\'id\' => \'numinixproductvariants\', \'text\' => \'Numinix Product Variants\')),'),
(NULL, 'Magic SEO URLs', 'GOOGLE_PRODUCTS_MAGIC_SEO_URLS', 'false', 'Output Magic SEO URLs (separate module required)?', @configuration_group_id, 999, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');

# Register the configuration page for Admin Access Control
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('configGoogleMerchantFeed','BOX_CONFIGURATION_GOOGLEMERCHANTFEED','FILENAME_CONFIGURATION',CONCAT('gID=',@configuration_group_id),'configuration','Y',@configuration_group_id);

# Register the tools page for Admin Access Control
INSERT IGNORE INTO admin_pages (`page_key`, `language_key`, `main_page`, `page_params`, `menu_key`, `display_on_menu`, `sort_order`) VALUES ('toolsGoogleMerchantFeed', 'BOX_GOOGLEFROOGLE', 'FILENAME_GOOGLEFROOGLE', '', 'tools', 'Y', @configuration_group_id);  