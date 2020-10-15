UPDATE configuration SET configuration_value = '1.14.1' WHERE configuration_key = 'GOOGLE_PRODUCTS_VERSION' LIMIT 1;

UPDATE configuration SET set_function = 'google_cfg_pull_down_country_iso3_list(' WHERE configuration_key = 'GOOGLE_PRODUCTS_SHIPPING_COUNTRY' LIMIT 1;