<?php
/**
 * googlefroogle.php
 *
 * @package google froogle
 * @copyright Copyright 2007 Numinix Technology http://www.numinix.com
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: googlefroogle.php 57 2011-05-17 00:35:31Z numinix $
 */

define('HEADING_TITLE', 'Google Merchant Center Feeder');
define('TEXT_GOOGLE_PRODUCTS_INFO', '<h3>What is a Product Feed?</h3><p>A product feed is a file listing your products (and associated data) for processing and use by a third party.</p><p>Sending this product feed to the Google Merchant Center regularly, ensures Google will display the current pricing, promotional, or other information for your products.</p>
<h3>Links:</h3>
<p><a href="https://www.google.com/retail/solutions/merchant-center/" target="_blank" class="splitPageLink">Google Merchant Center</a></p>
<p><a href="https://www.zen-cart.com/showthread.php?194527-Google-Merchant-Center-Feeder-for-ZC-v1-5-x" target="_blank" class="splitPageLink">Zen Cart Support Thread for this Plugin</a></p>
<p><a href="https://www.zen-cart.com/downloads.php?do=file&id=1375" target="_blank" class="splitPageLink">Zen Cart Plugin download Page</a></p>
<p><a href="https://github.com/torvista/Zen_Cart-Google_Merchant_Center_Feeder" target="_blank" class="splitPageLink">Github for this modification</a></p>
<p><a href="https://www.numinix.com/zen-cart-plugins-marketing-c-179_250_373_161/google-product-search-feeder" target="_blank" class="splitPageLink">Numinix (original author of this plugin) Subscription alternative</a></p>
');

define('FTP_CONNECTION_FAILED', 'Connection failed to:');
define('FTP_CONNECTION_OK', 'Connected to:');
define('FTP_LOGIN_FAILED', 'Login failed:');
define('FTP_LOGIN_OK', 'Login ok:');
define('FTP_CURRENT_DIRECTORY', 'Current Directory is:');
define('FTP_CANT_CHANGE_DIRECTORY', 'Cannot change directory on:');
define('FTP_UPLOAD_FAILED', 'Upload Failed!');
define('FTP_UPLOAD_SUCCESS', 'Uploaded Successfully.');
define('FTP_SERVER_NAME', ' Server Name: ');
define('FTP_USERNAME', ' Username: ');
define('FTP_PASSWORD', ' Password: ');

define('TEXT_FEED_TYPE', 'Feed Type:');
define('TEXT_FEED_PRODUCTS', 'Products');
define('TEXT_FEED_DOCUMENTS', 'Documents');
define('TEXT_FEED_NEWS', 'News');
define('TEXT_ENTRY_LIMIT', 'Max Products:');
define('TEXT_ENTRY_OFFSET', 'Starting Point/Offset:');
define('TEXT_FEED_ORDER_BY', 'Sort By:');
define('TEXT_FEED_ID', 'ID ');
define('TEXT_FEED_MODEL', 'Model ');
define('TEXT_FEED_NAME', 'Name ');
define('TEXT_FEED_SORT_TITLE', 'Default Sort: ');
define('TEXT_FEED_FILES', 'Generated Feeds');
define('TEXT_DATE_CREATED', 'Created <span class="small">(DD/MM/YYYY)</span>');
define('TEXT_DOWNLOAD_LINK', 'Download');
define('TEXT_ACTION', 'Action');
define('ERROR_GOOGLE_PRODUCTS_DIRECTORY_DOES_NOT_EXIST', 'The folder specified for the feed file ("<b>' . GOOGLE_PRODUCTS_DIRECTORY . '</b>") does not exist.<br>Please correct the path in Admin->Configuration->Google Merchant Center->Output Directory or create the specified directory and chmod to 755 or 777 depending on your host.');
define('ERROR_GOOGLE_PRODUCTS_DIRECTORY_NOT_WRITEABLE', 'The folder specified for the feed file ("<b>' . GOOGLE_PRODUCTS_DIRECTORY . '</b>") is not writeable (currently set to %u).<br>Please chmod the folder to 755 or 777 depending on your host.');
