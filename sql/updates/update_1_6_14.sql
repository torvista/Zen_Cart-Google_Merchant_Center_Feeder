UPDATE configuration
  SET configuration_description = 'Enter category ids separated by commas <br>(i.e. 1,2,3)<br />Leave blank to allow all categories'
  WHERE configuration_key = 'GOOGLE_BASE_POS_CATEGORIES'
  LIMIT 1;
  
UPDATE configuration
  SET configuration_description = 'Enter category ids separated by commas <br>(i.e. 1,2,3)<br />Leave blank to deactivate'
  WHERE configuration_key = 'GOOGLE_BASE_NEG_CATEGORIES'
  LIMIT 1;  