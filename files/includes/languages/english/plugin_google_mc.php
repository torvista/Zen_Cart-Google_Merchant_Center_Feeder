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

//Feed Description embedded at the start of the XML file
define('GOOGLE_MC_PRODUCTS_FEED_DESCRIPTION', 'Product list from Motorvista, Spain: distribution of motorcycle accessories in Europe.');

define('TEXT_GOOGLE_PRODUCTS_STARTED', 'Feed file creation started ' . date("d/m/Y H:i:s"));
define('TEXT_GOOGLE_PRODUCTS_FILE_LOCATION', 'File location:');
define('TEXT_GOOGLE_PRODUCTS_FEED_COMPLETE', 'Feed File completed in %s seconds.');
define('TEXT_GOOGLE_PRODUCTS_FEED_RECORDS', '%1$s items created from %2$s records (%3$s record(s) skipped).');
define('GOOGLE_PRODUCTS_VIEW_FILE', 'View File:');
define('ERROR_GOOGLE_PRODUCTS_DIRECTORY_DOES_NOT_EXIST', 'The folder specified for the feed file ("<b>%s</b>") does not exist.<br>Please correct the path in Admin->Configuration->Google Merchant Center->Output Directory or create the specified directory and chmod to 755 or 777 depending on your host.');
define('ERROR_GOOGLE_PRODUCTS_DIRECTORY_NOT_WRITEABLE', 'The folder specified for the feed file ("<b>' . GOOGLE_PRODUCTS_DIRECTORY . '</b>") is not writeable (currently set to %u).<br>Please chmod the folder to 755 or 777 depending on your host.');
define('ERROR_GOOGLE_PRODUCTS_OPEN_FILE', 'Error opening Google Merchant Center output file "' . DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . GOOGLE_PRODUCTS_OUTPUT_FILENAME . '"');
define('TEXT_GOOGLE_PRODUCTS_UPLOAD_FAILED', 'Upload failed...');
define('TEXT_GOOGLE_PRODUCTS_UPLOAD_OK', 'Upload ok!');
define('TEXT_GOOGLE_PRODUCTS_ERRSETUP', 'Google Merchant Center error setup:');
define('TEXT_GOOGLE_PRODUCTS_ERRSETUP_L', 'Google Merchant Center Feed Language "%s" not defined in zen-cart store.');
define('TEXT_GOOGLE_PRODUCTS_ERRSETUP_C', 'Google Merchant Center Default Currency "%s" not defined in zen-cart store.');
define('FTP_START', 'Initiating FTP connection...');
define('FTP_FAILED', 'Your hosting does not support ftp functions.');
define('FTP_CONNECTION_FAILED', 'Connection failed to "%s".');
define('FTP_CONNECTION_OK', 'Connected to "%s".');
define('FTP_LOGIN_FAILED', 'Login failed to "%s".');
define('FTP_LOGIN_OK', 'FTP Login ok to "%1$s", using username "%2$s"');
define('FTP_CANT_CHANGE_DIRECTORY', 'Cannot change directory on "%1$s" to "%2$s".');
define('FTP_CURRENT_DIRECTORY', 'FTP current directory is "%s".');
define('FTP_UPLOAD_FAILED', 'Upload Failed');
define('FTP_UPLOAD_SUCCESS', 'Uploaded Successfully');
define('FTP_SERVER_NAME', ' Server Name: ');
define('FTP_USERNAME', ' Username: ');
define('FTP_PASSWORD', ' Password: ');
define('TEXT_GOOGLE_PRODUCTS_PROCESSING', 'Processing...');
//[$products->fields['products_id'], $products->fields['products_model'], $productstitle, strlen($products_description), round($price, 2)];
define('TEXT_GOOGLE_PRODUCTS_PRODUCT_SUMMARY_ID', '%7$s - #%1$u | %2$s | "%3$s" | price: %6$s | description "%4$s" (%5$u chars)');
define('TEXT_GOOGLE_PRODUCTS_PRODUCT_SUMMARY_MODEL', '%7$s - %2$s (#%1$u) | "%3$s" | price: %6$s | description "%4$s" (%5$u chars)');
define('TEXT_GOOGLE_PRODUCTS_PRODUCT_SUMMARY_NAME', '"%7$s - %3$s" (#%1$u) | %2$s | price: %6$s | description "%4$s" (%5$u chars)');
define('TEXT_GOOGLE_PRODUCTS_FEED', 'Feed Creation: ');
define('TEXT_GOOGLE_PRODUCTS_YES', 'Yes');
define('TEXT_GOOGLE_PRODUCTS_NO', 'No');
define('TEXT_GOOGLE_PRODUCTS_UPLOAD', 'Feed FTP Upload: ');
