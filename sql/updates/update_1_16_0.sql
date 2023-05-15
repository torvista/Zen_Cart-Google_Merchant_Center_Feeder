#UPGRADE SQL 01/05/2023

##configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function

#CHECK
#GOOGLE_PRODUCTS_LANGUAGE_DISPLAY
#GOOGLE_PRODUCTS_LANGUAGE
#GOOGLE_PRODUCTS_USE_CPATH
#GOOGLE_PRODUCTS_PAYMENT_METHODS
#GOOGLE_PRODUCTS_PAYMENT_NOTES
#GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN for 3rd party

#ADD
#'GOOGLE_PRODUCTS_BIS_DAYS'
# GOOGLE_PRODUCTS_LINK_TYPE

#UPDATE
#make GOOGLE_PRODUCTS_VERSION read-only (value is set by code)
UPDATE configuration SET configuration_title = 'Google Merchant Center: Version', configuration_value = '', configuration_description = 'The Google Merchant Center version.', set_function = 'zen_cfg_read_only(' WHERE configuration_key = 'GOOGLE_PRODUCTS_VERSION';

#update description for GOOGLE_PRODUCTS_FEED_SORT
UPDATE configuration SET 
configuration_title = 'Default Sort Order for Feed products', 
configuration_description = 'Set the default sort order for the feed.<br>This may be overriden on the GMC Feed page.' WHERE configuration_key = 'GOOGLE_PRODUCTS_FEED_SORT';

#Update GOOGLE_PRODUCTS_MAX_PRODUCTS text
UPDATE configuration SET 
configuration_title = 'Max products to process',
configuration_description = '<p>Limit the number of products to process, to prevent timeouts.<br>This may be overriden on the GMC Feed page.</p><p>Default is 0 (no limit).</p>'
WHERE configuration_key = 'GOOGLE_PRODUCTS_MAX_PRODUCTS' LIMIT 1;

#Update GOOGLE_PRODUCTS_OFFER_ID to remove false option GOOGLE_PRODUCTS_OFFER_ID
UPDATE configuration SET 
configuration_title = 'Product Identifer ID',
configuration_description = 'A unique identifier for each item in the feed.<br>This is for internal/Google reference and not used for product comparison. The identifier should be also unique for variants/attributes.<br>Limited to 50-12 (38) characters.<br>id is recommended unless it can be certain that <b>ALL</b> products have the other unique identifier. Note that only id and model are in-built Zen Cart fields: other fields require custom coding.',
set_function = 'zen_cfg_select_option(array(\'id\', \'model\', \'EAN\', \'ISBN\', \'UPC\'),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_OFFER_ID' LIMIT 1;

#update description for filename GOOGLE_PRODUCTS_OUTPUT_FILENAME
UPDATE configuration SET configuration_description = 'Set the name of the output file (_products.xml will be appended),<br>or leave blank for it to be auto-generated from the Store Name, language and limit/offset used.' WHERE configuration_key = 'GOOGLE_PRODUCTS_OUTPUT_FILENAME';

#update description for Condition true/false GOOGLE_PRODUCTS_PRODUCT_CONDITION
UPDATE configuration SET configuration_description = '"Condition" is optional for new products but required for used/refurbished products.<br>"false": condition attribute is not set<br>"true": condition attribute is set based on value of custom field "products_condition" in the "products" table or falls back to Default Condition.' WHERE configuration_key = 'GOOGLE_PRODUCTS_PRODUCT_CONDITION';

#update order for Default Condition GOOGLE_PRODUCTS_CONDITION
UPDATE configuration SET 
configuration_description = 'Set the default value for the condition attribute.',
set_function = 'zen_cfg_select_option(array(\'new\', \'refurbished\', \'used\'),'
 WHERE configuration_key = 'GOOGLE_PRODUCTS_CONDITION';

#Update debug to have levels GOOGLE_PRODUCTS_DEBUG
UPDATE configuration SET 
configuration_title = 'Logging detail level',
configuration_description = '<p>A log may be created in the same directory as the feed files.</p><p>There are two types of entries in the log.<br>- ERROR: for skipped products/where a compulsory attribute could not be created.<br>- NOTICE: for included products where an optional attribute could not be created.</p><p>0 - no debug logs<br>1 - Log skipped products only<br>2 - Log skipped products and notices<br>3 - Log skipped products,  notices and show the result for each product processed in the popup (default)',
set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),',
configuration_value = '3'
WHERE configuration_key = 'GOOGLE_PRODUCTS_DEBUG' LIMIT 1;

#update description for Expiry date GOOGLE_PRODUCTS_EXPIRATION_DAYS
UPDATE configuration SET configuration_description = 'GMC uses a default expiry period of 30 days. The optional attribute [expiration_date] is used to reduce the period.<br>Set a value less than 30.' WHERE configuration_key = 'GOOGLE_PRODUCTS_EXPIRATION_DAYS';

#ADD none and GPC to Product Type GOOGLE_PRODUCTS_PRODUCT_TYPE
UPDATE configuration SET 
configuration_description = 'Set the (optional) Product Type attribute.<br>
"default": static text as defined in Default Product Type<br>
"top": the topmost category name above the product\'s master category<br>
"bottom": the master category name for the product<br>
"full": the complete comma-separated category path to the product<br>
"GPC": the Google Product Category (number or text) taken from a custom product field for Google Product Category.',
set_function = 'zen_cfg_select_option(array(\'none\', \'default\', \'top\', \'bottom\', \'full\', \'GPC\'),' WHERE configuration_key = 'GOOGLE_PRODUCTS_PRODUCT_TYPE';

#Update GOOGLE_PRODUCTS_SHIPPING_METHOD to remove CEON Advanced Shipper as never coded, and add a custom method to shipping methods radio buttons
UPDATE configuration SET configuration_description = 'Select the name of the shipping module used in your store (as shown in Modules->Shipping), or leave as \'none\'.<br>This is <i>intended</i> to provide a shipping cost per product, for the specified country in "Shipping Country" but the results should be carefully checked: using the custom option may be easier to provide a site-specific/multi-country/language solution.',
set_function = 'zen_cfg_select_option(array(\'none\', \'flat\', \'freeshipper\', \'item\', \'perweightunit\', \'table\', \'zones\', \'freerules\', \'percategory\', \'zonetable\', \'custom\'),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_SHIPPING_METHOD' LIMIT 1;

#DELETE
#OPTIONAL SQL FOR DELETING UNUSED CONSTANTS

#Moved to language define
DELETE FROM configuration WHERE configuration_key = "GOOGLE_PRODUCTS_DESCRIPTION" LIMIT 1;

#These are obsolete/not used anywhere!
#DELETE FROM configuration WHERE configuration_key = "GOOGLE_PRODUCTS_SHIPPING" LIMIT 1;
#DELETE FROM configuration WHERE configuration_key = "GOOGLE_PRODUCTS_CURRENCY_DISPLAY" LIMIT 1;

#Motorvista
UPDATE configuration SET configuration_value = 'test' 
WHERE configuration_key = 'GOOGLE_PRODUCTS_OUTPUT_FILENAME' LIMIT 1;

UPDATE configuration SET configuration_value = '4000' 
WHERE configuration_key = 'GOOGLE_PRODUCTS_MAX_PRODUCTS' LIMIT 1;

UPDATE configuration SET configuration_value = 'GPC' 
WHERE configuration_key = 'GOOGLE_PRODUCTS_PRODUCT_TYPE' LIMIT 1;

UPDATE configuration SET configuration_value = 'custom' 
WHERE configuration_key = 'GOOGLE_PRODUCTS_SHIPPING_METHOD' LIMIT 1;
