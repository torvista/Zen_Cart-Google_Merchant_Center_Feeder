SET @configuration_group_id=0;
SELECT @configuration_group_id:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Google Base Feeder Configuration'
LIMIT 1;
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'Alternate Image URL', 'GOOGLE_BASE_ALTERNATE_IMAGE_URL', '', 'Add an alternate URL if your images are hosted offsite (i.e. http://www.domain.com/images/).  Your defined image will be appended to the end of this URL.', @configuration_group_id, 36, NOW(), NULL, NULL);