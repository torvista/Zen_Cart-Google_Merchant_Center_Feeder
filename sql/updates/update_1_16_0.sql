#UPGRADE SQL 01/05/2023

#version 1.5.0->1.6.0 and make it read-only
UPDATE configuration SET configuration_title = 'Google Merchant Center: Version', configuration_value = '1.16.0', configuration_description = 'The Google Merchant Center version.', set_function = 'zen_cfg_read_only(' WHERE configuration_key = 'GOOGLE_PRODUCTS_VERSION';

UPDATE configuration SET configuration_description = 'Set the name of the output file (_products.xml will be appended),<br>or leave blank for it to be auto-generated from the Store Name, language and limit/offset used.' WHERE configuration_key = 'GOOGLE_PRODUCTS_OUTPUT_FILENAME';

#OPTIONAL SQL FOR DELETING UNUSED CONSTANTS

DELETE FROM configuration WHERE configuration_key = "GOOGLE_PRODUCTS_DESCRIPTION" LIMIT 1;
DELETE FROM configuration WHERE configuration_key = "GOOGLE_PRODUCTS_START_PRODUCTS" LIMIT 1;
