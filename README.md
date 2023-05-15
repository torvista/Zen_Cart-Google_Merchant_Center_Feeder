# Google Merchant Feeder for Zen Cart 1.6.0 beta1
This is my version of Google Merchant Feeder: https://www.zen-cart.com/downloads.php?do=file&id=1375
I found the previous version very difficult to understand and to so to modify...so I made some major changes to facilitate that.

A spreadsheet is included in the docs: GMC Feed info.ods to provide more detail per attribute.

## Current Status May 2023
Working, but liable to minor changes as per GMC complaints...

This readme and the upgrade script are up to date, but NOT the install script: so this is usable for this who wish to try this out on their dev sever that already has a previous v15.

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

## Use

### Shipping
The Admin Configuration options offers a list of the shipping methods to choose, this gets applied to the one country.
But what about when you ship to multiple countries with different rates? The shipping attribute is compulsory for some countries/the item is invalid without it.

A "custom" option is now in the list. This reads the array in /includes/extra_datafiles/plugin_google_mc.php, there are notes in there and an example array which produces this for all products.

      <g:shipping>
        <g:country>ES</g:country>
        <g:service>SEUR 24</g:service>
        <g:price>5 EUR</g:price>
        <g:min_handling_time>1</g:min_handling_time>
        <g:max_handling_time>2</g:max_handling_time>
        <g:min_transit_time>3</g:min_transit_time>
        <g:max_transit_time>4</g:max_transit_time>
      </g:shipping>
      <g:shipping>
        <g:country>PT</g:country>
        <g:postal_code>123456</g:postal_code>
        <g:service>SEUR 24</g:service>
        <g:price>5 EUR</g:price>
        <g:min_handling_time>1</g:min_handling_time>
        <g:max_handling_time>2</g:max_handling_time>
        <g:min_transit_time>3</g:min_transit_time>
        <g:max_transit_time>4</g:max_transit_time>
      </g:shipping>

This is a complete listing/not all the attributes are required.

### Availability
If no STOCK_CHECK: "in_stock".
If STOCK_CHECK and in stock: "in_stock".
If STOCK_CHECK and out of stock:
With a future products_date_available : "backorder" using that date.

With no products_date_available (NULL) or a past date: "backorder" using 
'GOOGLE_PRODUCTS_BIS_DAYS'
to add a availability_date of x days from today. Search for this in the class and change to taste.

Note from GMC: The pre-order [preorder] and backorder [backorder] values are not supported for Buy on Google listings.

## Changelog
2023/05/01:
Moved all the attribute generation to logically-named class methods to facilitate custom modifications.
Fixes:
based on /feeds/google_merchant_cente/example_feed_xml_rss.xml 
changed item title to g:title
changed item link to g:link
changed item description to g:description
removed multiple whitespace from description
removed g:currency (not in GMC spec)
removed g:payment_accepted (not in GMC spec)
removed g:payment_notes (not in GMC spec)
removed g:pickup (not in GMC spec)
removed g:upc (not in GMC spec)
removed g:mpn based on model field (incorrect as per GMC spec)

handle ftp directory not found: /issues/7

	changed:
	renamed all files as plugin_google_mc to get away from the legacy googlefroogle and google base references.
	removed GOOGLE_PRODUCTS_DESCRIPTION from db: moved to GOOGLE_MC_PRODUCTS_FEED_DESCRIPTION in storefront language file


	added:
	expanded shipping attributes for multi-country as detailed above
	options for generating feeds per language
	option for feed product list ordered by date ascending
	GOOGLE_PRODUCTS_OUTPUT_FILENAME: if not set, filename is auto-constructed from STORE_NAME_products_language-code(if a multi-language store)_range(calculated from limit/offset)
	added different options in the storefront file to produce native or friendly urls as required: search for 'GOOGLE_PRODUCTS_URLS'.
added availability_date for backorder
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
improve layout of options on feed generation page

'GOOGLE_PRODUCTS_URLS' to add to admin

update
define('BOX_CONFIGURATION_GOOGLEMERCHANTFEED', 'Google Merchant Center Feeder');//Configuration Menu
define('BOX_GOOGLEFROOGLE', BOX_CONFIGURATION_GOOGLEMERCHANTFEED);//Tools Menu TODO alter constant name to _TOOLS_

make admin form fields sticky
