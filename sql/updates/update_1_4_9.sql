UPDATE configuration_group
	SET configuration_group_title = 'Google Base Feeder Configuration'
	WHERE configuration_group_title = 'Google Froogle Configuration'
	LIMIT 1;