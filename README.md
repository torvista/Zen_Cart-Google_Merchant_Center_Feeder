# Google Merchant Feeder for Zen Cart
This is my development version (named here as 1.6.0) of Google Merchant Feeder: https://www.zen-cart.com/downloads.php?do=file&id=1375

## Branch 1.6.0 Current Status 2023/05
Very much a work in progress right now, so liable to more changes soon.
This readme and the upgrade script are up to date, but not the install script.

Tested on Zen Cart 158a, php 7.3-8.2.5.
Do not install this OR ANY PLUGIN on a production server before testing on a development server.

## Upgrading
1. In the Admin, copy the text from GOOGLE_PRODUCTS_DESCRIPTION to the storefront language file:
define('GOOGLE_MC_PRODUCTS_FEED_DESCRIPTION', GOOGLE_PRODUCTS_DESCRIPTION);
This constant will be removed by the upgrade sql to allow multi-language feeds to be created.

1. Backup the databse and ensure it can be restored...of course do this on your development server first.

1. Copy and paste the contents of sql/updates/update_1_16_0.sql into the Admin SQL Patch tool and run it.  
Note ANY error as it means not all the script completed and you'll have to deal with it manually: try running it in phpMyadmin.

## Installation
1. Copy all the files to the equivalent locations in your installation...they are all new/no core file overwrites (on a new install).

1. Install the database constants  
New install: the file \sql\install.sql IS NOT YET UPDATED for 1.6.0.

Upgrading from a previous versions: use the files subsequent to your version from \sql\updates

## Changelog
2023/05/01:
	changed:
	renamed all files as plugin_google_mc to modernise it
	removed GOOGLE_PRODUCTS_DESCRIPTION from db: moved to GOOGLE_MC_PRODUCTS_FEED_DESCRIPTION in storefront language file
	
	added:
	options for generating feeds per language
	option for feed product list ordered by date ascending
	GOOGLE_PRODUCTS_OUTPUT_FILENAME: if not set, filename is auto-constructed from STORE_NAME_products_language-code(if a multi-language store)_range(calculated from limit/offset)
	added different options in the storefront file to produce native or friendly urls as required: search for 'GOOGLE_PRODUCTS_URLS'.
	
	public function sftp_file_upload in class, since ftp is obsolete from 09 2023. This method is NOT working and I'm not going to fix it.
	
	fettling:
	ZC158 admin header
	use <?= for embedded <?php echo (easier to read)
	
2023/04/30: Further revisions on IDE inspections for obsolete/deprecated code/compliance with PHP8.  
2022/09/19: Bugfix: SQL OFFSET was used without LIMIT.  
2020/10: Revised this based on IDE inspections for obsolete/deprecated code/compliance with PHP7.  
2020/09: Starting point for this was Numinix Google Merchant Feeder: https://www.zen-cart.com/downloads.php?do=file&id=1375  
which is listed as 1.4.7, but actually is 1.14.7.

### TODO
'GOOGLE_PRODUCTS_URLS' to add to admin
update
define('BOX_CONFIGURATION_GOOGLEMERCHANTFEED', 'Google Merchant Center Feeder');//Configuration Menu
define('BOX_GOOGLEFROOGLE', BOX_CONFIGURATION_GOOGLEMERCHANTFEED);//Tools Menu TODO alter constant name to _TOOLS_
make admin form fields sticky
