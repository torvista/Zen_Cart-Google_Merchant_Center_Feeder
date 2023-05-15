<?php

declare(strict_types=1);
/**
 * @package Google Merchant Center
 * @link https://github.com/torvista/Zen_Cart-Google_Merchant_Center_Feeder
 * @author: torvista 01 May 2023
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @copyright Copyright 2007 Numinix Technology http://www.numinix.com
 * @author original Numinix Technology
 * @since 1.6.0
 * @version 1.6.0
 */

define('HEADING_TITLE', 'Google Merchant Center');
define('TEXT_GOOGLE_PRODUCTS_LINKS', '
<h3>Links:</h3>
<p><a href="https://www.google.com/retail/solutions/merchant-center/" target="_blank" class="splitPageLink">Google Merchant Center</a></p>
<p><a href="https://www.zen-cart.com/showthread.php?194527-Google-Merchant-Center-Feeder-for-ZC-v1-5-x" target="_blank" class="splitPageLink">Zen Cart Support Thread for this Plugin</a></p>
<p><a href="https://www.zen-cart.com/downloads.php?do=file&id=1375" target="_blank" class="splitPageLink">Zen Cart Plugin download Page</a></p>
<p><a href="https://github.com/torvista/Zen_Cart-Google_Merchant_Center_Feeder" target="_blank" class="splitPageLink">Github for this modification</a></p>
<p><a href="https://www.numinix.com/zen-cart-plugins-marketing-c-179_250_373_161/google-product-search-feeder" target="_blank" class="splitPageLink">Numinix (original author of this plugin) Subscription alternative</a></p>
');
//FTP to GMC obsolete from 09 2023
define('FTP_CONNECTION_FAILED', 'Connection failed to:');
define('FTP_CONNECTION_OK', 'Connected to:');
define('FTP_LOGIN_FAILED', 'Login failed:');
define('FTP_LOGIN_OK', 'Login ok:');
define('FTP_CURRENT_DIRECTORY', 'Current Directory is:');
define('FTP_CANT_CHANGE_DIRECTORY', 'Cannot change directory on:');
define('FTP_UPLOAD_FAILED', 'Upload Failed!');
define('FTP_DIRECTORY_NOT_FOUND', 'Directory not found!');
define('FTP_UPLOAD_SUCCESS', 'Uploaded Successfully.');
define('FTP_SERVER_NAME', ' Server Name: ');
define('FTP_USERNAME', ' Username: ');
define('FTP_PASSWORD', ' Password: ');

define('TEXT_FEED_TYPE', 'Feed Type:');
define('TEXT_FEED_PRODUCTS', 'Products');
define('TEXT_FEED_PRODUCTS_IDENTIFIER', 'Using "%s" as the unique product identifier.');
define('TEXT_FEED_DOCUMENTS', 'Documents');
define('TEXT_FEED_NEWS', 'News');
define('TEXT_ENTRY_LIMIT', 'Number of Products to Process:');
define('TEXT_ENTRY_OFFSET', 'Starting Point (Offset):');
define('TEXT_ENTRY_TEST_SINGLE_ID', 'Test a single ID#:');
define('TEXT_FEED_ORDER_BY', 'Order By:');
define('TEXT_FEED_ID', 'ID ');
define('TEXT_FEED_MODEL', 'Model ');
define('TEXT_FEED_NAME', 'Name ');
define('TEXT_FEED_DATE', 'Date (Oldest first) ');
define('TEXT_LANGUAGE_OPTIONS', 'Languages:');
define('TEXT_FEED_SORT_TITLE', 'Default Sort: ');
define('TEXT_GENERATE_MULTIPLE_FEEDS', 'Create multiple feeds sized by limit: ');
define('TEXT_FEED_FILES', 'Generated Feed Files');
define('TEXT_DATE_CREATED', 'Created <span class="small">(DD/MM/YYYY)</span>');
define('TEXT_DOWNLOAD_LINK', 'Download');
define('TEXT_ACTION', 'Action');
define('TEXT_GOOGLE_XML_EXAMPLE', 'Google XML Example');
define('TEXT_LOG_FILES', 'Log Files');
define('ERROR_GOOGLE_PRODUCTS_DIRECTORY_DOES_NOT_EXIST', 'The folder specified for the feed file ("<b>%s</b>") does not exist.<br>Please correct the path in Admin->Configuration->Google Merchant Center->Output Directory or create the specified directory and chmod to 755 or 777 depending on your host.');
define('ERROR_GOOGLE_PRODUCTS_DIRECTORY_NOT_WRITEABLE', 'The folder specified for the feed file ("<b>' . GOOGLE_PRODUCTS_DIRECTORY . '</b>") is not writeable (currently set to %u).<br>Please chmod the folder to 755 or 777 depending on your host.');
