#Add option Products Options Stock to 3rd party attribute-stock plugins
UPDATE configuration SET 
configuration_title = 'Attribute-Stock Plugin',
configuration_description = 'Select the third party plugin used for managing attribute/variant stocks or leave as \'none\'.',
set_function = 'zen_cfg_select_drop_down(array(array(\'id\' => \'none\', \'text\' => \'none\'), array(\'id\' => \'stockbyattributes\', \'text\' => \'Stock By Attributes\'), array(\'id\' => \'numinixproductvariants\', \'text\' => \'Numinix Product Variants\'), array(\'id\' => \'posm\', \'text\' => \'Products Options Stock Manager\')),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN' LIMIT 1;

#Add option CEON Advanced Shipper to shipping methods
UPDATE configuration SET 
configuration_description = 'Select the name of the shipping module used in your store (as shown in Modules->Shipping), or leave as \'none\'.',
set_function = 'zen_cfg_select_option(array(\'none\', \'flat\', \'freeshipper\', \'item\', \'perweightunit\', \'table\', \'zones\', \'advshipper\', \'freerules\', \'percategory\', \'zonetable\'),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_SHIPPING_METHOD' LIMIT 1;
