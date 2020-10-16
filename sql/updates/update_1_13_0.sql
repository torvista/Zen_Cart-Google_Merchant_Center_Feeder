SET @configuration_group_id=0;
SELECT (@configuration_group_id:=configuration_group_id) 
FROM configuration_group 
WHERE configuration_group_title = 'Google Merchant Center Feeder Configuration' 
LIMIT 1;

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Version', 'GOOGLE_PRODUCTS_VERSION', '1.13.0', 'Version Installed:', @configuration_group_id, 0, NOW(), NULL, NULL);

# Register the configuration page for Admin Access Control
INSERT IGNORE INTO admin_pages (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES ('configGoogleMerchantFeed','BOX_CONFIGURATION_GOOGLEMERCHANTFEED','FILENAME_CONFIGURATION',CONCAT('gID=',@configuration_group_id),'configuration','Y',@configuration_group_id);

# Register the tools page for Admin Access Control
INSERT IGNORE INTO admin_pages (`page_key`, `language_key`, `main_page`, `page_params`, `menu_key`, `display_on_menu`, `sort_order`) VALUES
('toolsGoogleMerchantFeed', 'BOX_GOOGLEFROOGLE', 'FILENAME_GOOGLEFROOGLE', '', 'tools', 'Y', @configuration_group_id);  