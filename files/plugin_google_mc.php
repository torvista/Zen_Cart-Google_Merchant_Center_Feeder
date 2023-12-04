<?php

declare(strict_types=1);
/**
 * @package Google Merchant Center
 * @link https://github.com/torvista/Zen_Cart-Google_Merchant_Center_Feeder
 * @author: torvista 16 June 2023
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @copyright Copyright 2007 Numinix Technology http://www.numinix.com
 * @author original Numinix Technology
 * @since 1.6.0
 * @version 1.6.0
 */

require('includes/application_top.php');
if (empty($_SESSION)){
    return;
}
$languages = new language();
$langsMultiple = (count($languages->catalog_languages) > 1);
$langSelectedCode = !empty($_GET['langSelected']) ? zen_db_prepare_input($_GET['langSelected']) : GOOGLE_PRODUCTS_LANGUAGE;
$languages->set_language($langSelectedCode);
$langSelected= $db->Execute('SELECT languages_id, code, directory FROM ' . TABLE_LANGUAGES . ' WHERE code = "' . $langSelectedCode . '" LIMIT 1');
$_SESSION['language'] = $langSelected->fields['directory'];
$_SESSION['languages_code'] = $langSelected->fields['code'];
$_SESSION['languages_id'] = $langSelected->fields['languages_id'];

//use selected language for texts
require(DIR_WS_LANGUAGES . $langSelected->fields['directory'] . '/plugin_google_mc.php');
require(DIR_WS_CLASSES . 'plugin_google_mc.php');

$google_mc = new google_mc();

if ((int)GOOGLE_PRODUCTS_MAX_EXECUTION_TIME > 0) {
    ini_set('max_execution_time', GOOGLE_PRODUCTS_MAX_EXECUTION_TIME); // change to whatever time you need
    set_time_limit((int)GOOGLE_PRODUCTS_MAX_EXECUTION_TIME); // change to whatever time you need
}
if ((int)GOOGLE_PRODUCTS_MEMORY_LIMIT > 0) {
    ini_set('memory_limit', (int)GOOGLE_PRODUCTS_MEMORY_LIMIT . 'M');
} // change to whatever you need

$keepAlive = 100;  // perform a keep alive every x number of products

// include shipping class
if (GOOGLE_PRODUCTS_SHIPPING_METHOD === 'percategory') {//Numinix shipping module
    include(DIR_WS_MODULES . 'shipping/percategory.php');
    $percategory = new percategory();
} elseif (GOOGLE_PRODUCTS_SHIPPING_METHOD === 'freerules') {//Numinix shipping module
    include(DIR_WS_MODULES . 'shipping/freerules.php');
    $freerules = new freerules();
}//todo add Advanced Shipper

const GOOGLE_PRODUCTS_OUTPUT_BUFFER_MAXSIZE = 1024 * 1024 * 8; // 8MB
// definitions
//https://support.google.com/merchants/answer/7052112?hl=en&ref_topic=6324338
const GOOGLE_PRODUCTS_CDATA_LENGTH = 12;
const GOOGLE_PRODUCTS_MAX_CHARS_BRAND = 70;
const GOOGLE_PRODUCTS_MAX_CHARS_GTIN = 14;
const GOOGLE_PRODUCTS_MAX_CHARS_ID = 50;
const GOOGLE_PRODUCTS_MAX_CHARS_MPN = 70;
const GOOGLE_PRODUCTS_MAX_CHARS_TITLE = 150;
const GOOGLE_PRODUCTS_MIN_CHARS_DESCRIPTION = 15;
const GOOGLE_PRODUCTS_MAX_CHARS_DESCRIPTION = 5000;
const GOOGLE_PRODUCTS_MAX_CHARS_IMAGE_LINK = 2000;
const GOOGLE_PRODUCTS_MAX_ADDITIONAL_IMAGES = 10;
const NL = "<br>\n";

$anti_timeout_counter = 0; //for timeout issues as well as counting number of products processed

// process parameters: e.g.?feed=fn_uy_tp&upload_file=MYFILE_products_en.xml&key=eeb6cf1423
if (empty($_GET['feed'])) {
    die('no feed parameter!');
}
$parameters = explode('_', $_GET['feed']); // ?feed=fy_uy_tp
$feed_parameter = $parameters[0]; // e.g. 'fn'
$feed = $google_mc->get_feed($feed_parameter);
$upload_parameter = $parameters[1]; // e.g. 'uy'
$upload = $google_mc->get_upload($upload_parameter);
$type_parameter = $parameters[2]; // e.g. 'tp'
$type = $google_mc->get_type($type_parameter);
$key = $_GET['key'];

if ($key !== GOOGLE_PRODUCTS_KEY) {
    exit('<p>Incorrect key "GOOGLE_PRODUCTS_KEY" supplied!</p>');
}

if (isset($_GET['upload_file'])) {
    $outfile = '';
    $upload_file = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $_GET['upload_file'];//todo sanitise?
} else {
    $upload_file = '';
}

//Query modifiers
//constant GOOGLE_PRODUCTS_MAX_PRODUCTS is an empirical limit probably based on server memory: pre-filled on Admin page.
$suffix = empty($_GET['suffix']) ? '' : (int)$_GET['suffix']; //file number 1,2 etc.
$limit = empty($_GET['limit']) ? 0 : (int)$_GET['limit'];
$offset = empty($_GET['offset']) ? 1 : (int)$_GET['offset'];
$singleID = empty($_GET['singleID']) ? '' : (int)$_GET['singleID'];

if (GOOGLE_PRODUCTS_MAGIC_SEO_URLS === 'true') {
    require_once(DIR_WS_CLASSES . 'msu_ao.php');
    include(DIR_WS_INCLUDES . 'modules/msu_ao_1.php');
}

ob_start();
?>
<!DOCTYPE html>
<html <?php
echo HTML_PARAMS; ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            margin-left: 5px;
            font-family: Verdana, sans-serif;
            font-size: 11px;
        }

        h1 {
            font-size: 12px;
        }

        .errorText {
            color: red;
            font-weight: bold;
        }
    </style>
    <title>Google Merchant Center Feed Generator v<?= GOOGLE_PRODUCTS_VERSION; ?></title>
</head>
<body>
<h1>Google Merchant Center Feed Generator v<?= GOOGLE_PRODUCTS_VERSION; ?></h1>
<?php
if (GOOGLE_PRODUCTS_DEBUG === '3') {
    echo 'GOOGLE_PRODUCTS_DEBUG=' . GOOGLE_PRODUCTS_DEBUG . ' (admin)<br>';
    echo 'current SESSION language=' . $_SESSION['language'] . ' (debug texts are hard-coded in english)<br>';
    echo 'GOOGLE_PRODUCTS_SHIPPING_METHOD=' . GOOGLE_PRODUCTS_SHIPPING_METHOD . '<br>';
    echo '$limit=' . $limit . ', $offset=' . $offset . '<br>';
    $google_mc->print_mem();
}
$logfile_link = GOOGLE_PRODUCTS_DIRECTORY . 'GMC' . date('-Ymd') . '.log';
?>

<p><?= TEXT_GOOGLE_PRODUCTS_STARTED; ?></p>
<p><?= TEXT_GOOGLE_PRODUCTS_FEED . (isset($feed) && $feed === 'yes' ? TEXT_GOOGLE_PRODUCTS_YES : TEXT_GOOGLE_PRODUCTS_NO); ?><br>
    <?= TEXT_GOOGLE_PRODUCTS_UPLOAD . (isset($upload) && $upload === 'yes' ? TEXT_GOOGLE_PRODUCTS_YES : TEXT_GOOGLE_PRODUCTS_NO); ?></p>
<?php
//why both?  https://www.php.net/manual/en/function.flush.php
ob_flush();
flush();

//CREATE A FEED FILE
if (isset($feed) && $feed === 'yes') {
//check output file location permissions
    $file_directory = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY;
    if (is_dir($file_directory)) {
        if (!is_writable($file_directory)) {
            echo '<p class="errorText">' . sprintf(ERROR_GOOGLE_PRODUCTS_DIRECTORY_NOT_WRITEABLE, substr(sprintf('%o', fileperms($file_directory)), -4)) . '</p>';
            die;
        }
    } else {
        echo '<p class="errorText">' . sprintf(ERROR_GOOGLE_PRODUCTS_DIRECTORY_DOES_NOT_EXIST, $file_directory) . '</p>';
        die;
    }

    $stimer_feed = $google_mc->microtime_float();

    $additional_attributes = '';
    $additional_tables = '';

    // description 2
    if (defined('GOOGLE_PRODUCTS_ASA_DESCRIPTION_2') && GOOGLE_PRODUCTS_ASA_DESCRIPTION_2 === 'true') {
        $additional_attributes .= ', pd.products_description2';
    }

    if (GOOGLE_PRODUCTS_META_TITLE === 'true') {
        $additional_attributes .= ', mtpd.metatags_title';
        $additional_tables .= ' LEFT JOIN ' . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . ' mtpd ON (p.products_id = mtpd.products_id) ';
    }

    switch ($_GET['feed_sort']) {
        case ('date'):
            $order_by = 'p.products_last_modified';
            break;
        case ('model'):
            $order_by = 'p.products_model';
            break;
        case ('name'):
            $order_by = 'pd.products_name';
            break;
        default:
            $order_by = 'p.products_id';
    }

    // this method is also used in the Admin page to show total
    $products_count_all = $google_mc->count_valid_products();

    if ($limit === 0) {//no limit: all products
        if ($offset > 1) {// MYSQL OFFSET must be used with LIMIT, so have to add a value for LIMIT
            $sql_limit = ' LIMIT ' . $products_count_all;
            $sql_offset = ' OFFSET ' . ($offset - 1);
        } else {//all products, no offset
            $sql_limit = '';
            $sql_offset = '';
        }
    } else {//limit in use, maybe an offset
        $sql_limit = ' LIMIT ' . $limit;
        $sql_offset = $offset > 1 ? ' OFFSET ' . ($offset - 1) : '';
    }

// use these user-defined arrays, for fine control/remove permanent exceptions from the logging
// $excluded_products
// $excluded_models
// $excluded_model_range

    $and_single_productID = '';
    $and_not_excluded_products = '';
    $and_not_excluded_models = '';
    $and_not_excluded_model_range = '';

    // apply a single product filter: for testing
    if (!empty($singleID)) {
        $and_single_productID = ' AND p.products_id = ' . $singleID;
        $sql_limit = ' LIMIT 1 ';
        $sql_offset = '';

    } else {

       // apply normal user-defined filters defined in extra_datafiles
        if (count($excluded_products) > 0) {
            $excluded_products = implode(',', $excluded_products);
            $and_not_excluded_products = ' AND p.products_id NOT IN (' . $excluded_products . ')';
        }
        if (count($excluded_models) > 0) {
            foreach ($excluded_models as $excluded_model) {
                $and_not_excluded_models .= "'" . $excluded_model . "', ";
            }
            $and_not_excluded_models = rtrim($and_not_excluded_models, ', ');
            $and_not_excluded_models = ' AND p.products_model NOT IN (' . $and_not_excluded_models . ')';
        }
        if (count($excluded_model_ranges) > 0) {
            foreach ($excluded_model_ranges as $excluded_model_range) {
                $and_not_excluded_model_range .= ' AND p.products_model NOT LIKE "' . $excluded_model_range . '%"' . " \n";
            }
        }
    }

    // Note changes to this query that affect total, should be replicated in methods count_valid_products.
    // This is not located in the class to avoid passing the various legacy variables.
    $products_sql = 'SELECT p.*, pd.products_name, pd.products_description,
    GREATEST(p.products_date_added, IFNULL(p.products_last_modified, 0), IFNULL(p.products_date_available, 0)) AS base_date,
    m.manufacturers_name, pt.type_handler' . $additional_attributes . '
                           FROM ' . TABLE_PRODUCTS . ' p
                             LEFT JOIN ' . TABLE_MANUFACTURERS . ' m ON (p.manufacturers_id = m.manufacturers_id)
                             LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON (p.products_id = pd.products_id)
                             LEFT JOIN ' . TABLE_PRODUCT_TYPES . ' pt ON (p.products_type=pt.type_id)' . $additional_tables . '
                           WHERE p.products_status = 1
                             AND (p.products_price > 0 OR (p.products_price = 0 AND p.products_priced_by_attribute = 1))
                             AND p.products_type <> 3
                             AND p.product_is_call <> 1
                             AND p.product_is_free <> 1
                             AND (p.products_image IS NOT NULL
                                OR p.products_image != ""
                                OR p.products_image != "' . PRODUCTS_IMAGE_NO_IMAGE . '")
                             AND pd.language_id = ' . (int)$langSelected->fields['languages_id'] .
                             $and_single_productID .
                             $and_not_excluded_products .
                             $and_not_excluded_models .
                             $and_not_excluded_model_range . '
                             ORDER BY ' . $order_by . $sql_limit . $sql_offset;
    //mv_printVar($products_sql);
    $products = $db->Execute($products_sql);
    $products_count = $products->RecordCount();

    if (GOOGLE_PRODUCTS_DEBUG === '3') {
        echo '<p>Total valid products=' . $products_count_all . NL .
            'Processing ' . $products_count . ' products' . ($limit === 0 ? ' (not limited)' : ' (as per limit)') . ', starting from ' . $offset . '.</p>';
    }

    if (GOOGLE_PRODUCTS_OUTPUT_FILENAME !== '') {
        $outfile = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . GOOGLE_PRODUCTS_OUTPUT_FILENAME . '_' . $type;
    } else {
        //create a suffix from the limit and offset...
        //echo '$limit='.$limit.', $offset='.$offset.', $products_count='.$products_count.NL;
        switch (true) {
            case(!empty($suffix)):
                $suffix = '_' . $suffix;
                break;
            case($limit === 0 && $offset === 1):
            case($limit > $products_count_all && $offset === 1):
                //all products
                $suffix = '';
                break;
            case($limit === 0 && $offset !== 1):
                //from somewhere to the end
                $suffix = '_' . $offset . '-end';
                break;
            case($limit < $products_count_all && $offset === 1):
                //from start to middle
                $suffix = '_1-' . $limit;
                break;
            case($offset !== 1 && $limit < ($products_count_all - $offset)):
                //a range
                $start_from = $offset;
                $end_to = $offset + $limit;
                if ($end_to > $products_count_all) {
                    $suffix = '_' . $start_from . '-end';
                } else {
                    $suffix = '_' . $start_from . '-' . $end_to;
                }
                break;
            case($offset !== 1 && $limit > ($products_count_all - $offset)):
                //middle to end
                $suffix = '_' . $offset . '-end';
                break;
            default:
                $suffix = '';
        }
        $lang_suffix = $langsMultiple ? '_' . $langSelected->fields['code'] : '';
        $outfile = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . strtolower(STORE_NAME) . '_' . $type . $lang_suffix . $suffix;
    }
    $outfile .= '.xml';
    echo '<p>' . TEXT_GOOGLE_PRODUCTS_FILE_LOCATION . NL . str_replace(DIR_FS_CATALOG , '/', ($upload_file !== '') ? $upload_file : $outfile) . '</p>';
    echo '<p>' . TEXT_GOOGLE_PRODUCTS_PROCESSING . '</p>';

    $default_google_product_category = $google_mc->google_base_xml_sanitizer(GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY);

    //initialise XML file
    $dom = new DOMDocument('1.0', 'utf-8');

    $rss = $dom->createElement('rss');
    $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
    $rss->setAttribute('version', '2.0');

    $channel = $dom->createElement('channel');

    $title = $dom->createElement('title');
    $title->appendChild($dom->createCDATASection($google_mc->google_base_xml_sanitizer(STORE_NAME)));

    $link = $dom->createElement('link', GOOGLE_PRODUCTS_ADDRESS);

    $channel_description = $dom->createElement('description', $google_mc->google_base_xml_sanitizer(GOOGLE_MC_PRODUCTS_FEED_DESCRIPTION));

    $channel->appendChild($title);
    $channel->appendChild($link);
    $channel->appendChild($channel_description);

    $loop_count = 0;
    foreach ($products as $product) {
        $google_mc->skip_product = false;

//todo each method in this check explodes an array: should be done before getting here
        if (!$google_mc->check_product($product['products_id'])) {
            echo '#' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": <span class="errorText">skipped due to a user-defined filter</span>: see the <a target="_blank" href="' . $logfile_link . '">debug log</a>.' . NL;
            continue;
        }

        //item is a simple product / a variant of a product with attributes
        if (zen_has_product_attributes($product['products_id'])) {
            if (GOOGLE_PRODUCTS_DEBUG === '3') {
                echo '#' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": HAS ATTRIBUTES' . NL;
            }
            if (GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN === 'stockbyattributes' || GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN === 'numinixproductvariants') {
                //this legacy code may be broken for 3rd party attribute-stock handlers...sorry.
                include('plugin_google_mc_attributes_legacy.php');
            } else {
                //copied from attributes controller
                $attributes_query_raw = 'SELECT pa.*
                                     FROM (' . TABLE_PRODUCTS_ATTRIBUTES . ' pa
                                     LEFT JOIN ' . TABLE_PRODUCTS_OPTIONS . ' po ON pa.options_id = po.products_options_id
                                       AND po.language_id = ' . (int)$langSelected->fields['languages_id'] . ')
                                     WHERE pa.products_id = ' . (int)$product['products_id'] . "
                                     AND pa.attributes_display_only = 0
                                     ORDER BY LPAD(po.products_options_sort_order,11,'0'),
                                              LPAD(pa.options_id,11,'0'),
                                              LPAD(pa.products_options_sort_order,11,'0')";

                $attributes = $db->Execute($attributes_query_raw);
                // [products_attributes_id] identifies the product-option-value combination
                // [options_id] identifies the option
                // [options_values_id] the option value
                // [options_values_price] price of the option
                // [price_prefix] +/-

                foreach ($attributes as $attribute) {
                    //echo '&nbsp;&nbsp;&nbsp;[products_attributes_id]=' . $attribute['products_attributes_id'] . ', [options_values_id]=' . $attribute['options_values_id'] . ', [options_values_price]=' . $attribute['options_values_price'] . NL;
                    // all products
                    $item = $google_mc->create_item($dom);
                    $item = $google_mc->add_common_attributes($dom, $item, $product); // 12 attributes
                    //variant-specific attributes
                    $item = $google_mc->add_title($dom, $item, $product, $attribute); // variant adds suffix ":option_name-option_value"
                    $item = $google_mc->add_mpn($dom, $item, $product, $attribute); // attribute dependant CUSTOM ***************
                    $item = $google_mc->add_gtin($dom, $item, $product, $attribute); // attribute dependant CUSTOM **************
                    $item = $google_mc->add_price($dom, $item, $product, $attribute); // attribute dependant***************
                    $item = $google_mc->add_shipping_weight($dom, $item, $product, $attribute); // attribute dependant ************
                    //only for variants
                    $item = $google_mc->add_item_group_id($dom, $item, $product); // same identifier for all variants
                    $item = $google_mc->add_variant_attribute($dom, $item, $product, $attribute); // attribute dependant CUSTOM ************
                    //color [color], pattern [pattern], material [material], age group [age_group], gender [gender], size[size].
                    //add item to xml file
                    $channel->appendChild($item);
                }
            }
        } else {
            // simple product
            //echo 'simple product #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": HAS ATTRIBUTES' . NL;
            $item = $google_mc->create_item($dom);
            $item = $google_mc->add_common_attributes($dom, $item, $product); // 12 attributes
            $item = $google_mc->add_title($dom, $item, $product);
            //$item = $google_mc->add_id($dom, $item, $product); // common attribute
            //$item = $google_mc->add_brand($dom, $item, $product); // common attribute
            $item = $google_mc->add_mpn($dom, $item, $product);
            $item = $google_mc->add_gtin($dom, $item, $product);
            //$item = $google_mc->add_google_product_category($dom, $item, $product); // common attribute
            $item = $google_mc->add_price($dom, $item, $product);
            //$item = $google_mc->add_availability($dom, $item, $product); // common attribute
            $item = $google_mc->add_shipping_weight($dom, $item, $product);
            //$item = $google_mc->add_shipping($dom, $item, $product); // common attribute
            //$item = $google_mc->add_ships_from_country($dom, $item); // common attribute
            ///$item = $google_mc->add_condition($dom, $item, $product); // common attribute
            //$item = $google_mc->add_product_type($dom, $item, $product); // common attribute
            //$item = $google_mc->add_image_links($dom, $item, $product); // common attribute
            //$item = $google_mc->add_expiration_date($dom, $item, $product); // common attribute
            //$item = $google_mc->add_link($dom, $item, $product); // common attribute
            //$item = $google_mc->add_description($dom, $item, $product); // common attribute
            $item = $google_mc->add_is_bundle($dom, $item, $product);
            //add item to xml file
            $channel->appendChild($item);
        }
        if ($google_mc->skip_product) {
            echo '#' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": <span class="errorText">skipped due to an error in attribute creation</span>: see the <a target="_blank" href="' . $logfile_link . '">debug log</a>.' . NL;
            continue;
        }
        if (GOOGLE_PRODUCTS_DEBUG === '3') {
            echo '#' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": added' . NL;
        }

        $loop_count++;
        ob_flush();
        flush();
    }// end of product loop

    //finalise xml file
    $rss->appendChild($channel);
    $dom->appendChild($rss);
    $dom->formatOutput = true;

    if (GOOGLE_PRODUCTS_COMPRESS !== 'true') // Use uncompressed file
    {
        // Write uncompressed file
        $dom->save($outfile);
    } else {
        // Compress file
        $outfile .= '.gz'; // Append .gz to end of file name
        $data = $dom->saveXML(); //Save XML feed to string
        $gz = gzopen("$outfile", 'w9'); // todo, in quotes?? Open file for writing, 0 (no) to 9 (maximum) compression
        gzwrite($gz, $data, strlen($data)); // Write compressed file
        gzclose($gz); // Close file handler
    }

    $timer_feed = $google_mc->microtime_float() - $stimer_feed; ?>
    <p><?= sprintf(TEXT_GOOGLE_PRODUCTS_FEED_RECORDS, $loop_count, $products->RecordCount(), $products->RecordCount() - $loop_count); ?><br>
        <?= sprintf(TEXT_GOOGLE_PRODUCTS_FEED_COMPLETE, number_format($timer_feed, 1)); ?></p>
    <?php
}

//UPLOAD A FEED FILE
if (isset($upload) && $upload === 'yes') {
    if ($upload_file === '') {
        $upload_file = $outfile;//if no upload file was specified, use the file just created
    }

    // FTP no longer supported 09 2023: SFTP instead
    if ($google_mc->ftp_file_upload(GOOGLE_PRODUCTS_SERVER, GOOGLE_PRODUCTS_USERNAME, GOOGLE_PRODUCTS_PASSWORD, $upload_file)) {
        echo '<p>' . TEXT_GOOGLE_PRODUCTS_UPLOAD_OK . '</p>';
        $db->Execute('UPDATE ' . TABLE_CONFIGURATION . ' SET configuration_value = "' . date('Y/m/d H:i:s') . '" WHERE configuration_key = "' . GOOGLE_PRODUCTS_UPLOADED_DATE . '"');
    } else {
        echo '<p class="errorText">' . TEXT_GOOGLE_PRODUCTS_UPLOAD_FAILED . '</p>';
    }
}
if (GOOGLE_PRODUCTS_DEBUG === '3') {
    $google_mc->print_mem();
}
?>
</body>
</html>
