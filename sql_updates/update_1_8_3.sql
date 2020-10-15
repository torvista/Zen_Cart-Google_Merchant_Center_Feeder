UPDATE configuration_group SET configuration_group_title = 'Google Merchant Center Feeder Configuration', configuration_group_description = 'Set Google Merchant Center Options' WHERE configuration_group_title = 'Google Base Feeder Configuration' LIMIT 1;

DELETE FROM configuration WHERE configuration_key = 'GOOGLE_BASE_ASA' LIMIT 1;