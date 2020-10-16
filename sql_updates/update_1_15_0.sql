#Add option Products Options Stock
UPDATE configuration SET 
configuration_title = 'Attribute-Stock Plugin',
configuration_description = 'Select the third party plugin used for managing attribute/variant stocks or leave as \'none\'.',
set_function = 'zen_cfg_select_drop_down(array(array(\'id\' => \'none\', \'text\' => \'none\'), array(\'id\' => \'stockbyattributes\', \'text\' => \'Stock By Attributes\'), array(\'id\' => \'numinixproductvariants\', \'text\' => \'Numinix Product Variants\'), array(\'id\' => \'posm\', \'text\' => \'Products Options Stock Manager\')),'
WHERE configuration_key = 'GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN' LIMIT 1;
