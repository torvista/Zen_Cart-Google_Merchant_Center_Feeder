<?php
/**
 * googlefroogle.php
 *
 * @package google base feeder
 * @copyright Copyright 2007-2008 Numinix Technology http://www.numinix.com
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: googlefroogle.php 67 2011-09-15 19:26:39Z numinix $
 * @author Numinix Technology
 */
  /* configuration */
  if (phpversion() < 5) die('PHP 5+ required, please contact your host to upgrade.'); 
  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'google_base.php');
  include(DIR_WS_LANGUAGES . 'english/googlefroogle.php');
  $google_base = new google_base();
  
  if ((int)GOOGLE_PRODUCTS_MAX_EXECUTION_TIME > 0) {
    ini_set('max_execution_time', (int)GOOGLE_PRODUCTS_MAX_EXECUTION_TIME); // change to whatever time you need
    set_time_limit((int)GOOGLE_PRODUCTS_MAX_EXECUTION_TIME); // change to whatever time you need
  }
  if ((int)GOOGLE_PRODUCTS_MEMORY_LIMIT > 0) ini_set('memory_limit', (int)GOOGLE_PRODUCTS_MEMORY_LIMIT . 'M'); // change to whatever you need
  
  $keepAlive = 100;  // perform a keep alive every x number of products  
  // include shipping class
  if (GOOGLE_PRODUCTS_SHIPPING_METHOD == 'percategory') { 
    include(DIR_WS_MODULES . 'shipping/percategory.php');
    $percategory = new percategory();
  } elseif (GOOGLE_PRODUCTS_SHIPPING_METHOD == 'free rules shipping') {
    include(DIR_WS_MODULES . 'shipping/freerules.php');
    $freerules = new freerules();
  }
                                                                                                      
  @define('GOOGLE_PRODUCTS_EXPIRATION_DAYS', 29);
  @define('GOOGLE_PRODUCTS_EXPIRATION_BASE', 'now'); // now/product
  @define('GOOGLE_PRODUCTS_OFFER_ID', 'id'); // id/model/false
  @define('GOOGLE_PRODUCTS_DIRECTORY', 'feed/google/');
  @define('GOOGLE_PRODUCTS_OUTPUT_BUFFER_MAXSIZE', 1024*1024*8); // 8MB
  $anti_timeout_counter = 0; //for timeout issues as well as counting number of products processed
  $google_base_start_counter = 0; //for counting all products regardless of inclusion
  @define('GOOGLE_PRODUCTS_USE_CPATH', 'false');
  @define('NL', "<br />\n");
  
  $stock_attributes = false;
  if(GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN !== 'none') {
    $stock_attributes = true;
  }
  
  // process parameters
  $parameters = explode('_', $_GET['feed']); // ?feed=fy_uy_tp
  $feed_parameter = $parameters[0];
  $feed = $google_base->get_feed($feed_parameter);
  $upload_parameter = $parameters[1];
  $upload = $google_base->get_upload($upload_parameter);
  $type_parameter = $parameters[2];
  $type = $google_base->get_type($type_parameter);
  $key = $_GET['key'];
  if ($key != GOOGLE_PRODUCTS_KEY) exit('<p>Incorrect key supplied!</p>');
  $languages_query = "SELECT code, languages_id, directory FROM " . TABLE_LANGUAGES . " WHERE languages_id = " . (int)GOOGLE_PRODUCTS_LANGUAGE . " LIMIT 1";
  $languages = $db->Execute($languages_query);
  if (isset($_GET['upload_file'])) {
    $upload_file = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $_GET['upload_file'];
  } else {
    // sql limiters
    if ((int)GOOGLE_PRODUCTS_MAX_PRODUCTS > 0 || (isset($_GET['limit']) && (int)$_GET['limit'] > 0)) {
      $query_limit = (isset($_GET['limit']) && (int)$_GET['limit'] > 0) ? (int)$_GET['limit'] : (int)GOOGLE_PRODUCTS_MAX_PRODUCTS; 
      $limit = ' LIMIT ' . $query_limit; 
    }
    if ((int)GOOGLE_PRODUCTS_START_PRODUCTS > 0 || (isset($_GET['offset']) && (int)$_GET['offset'] > 0)) {
      $query_offset = (isset($_GET['offset']) && (int)$_GET['offset'] > 0) ? (int)$_GET['offset'] : (int)GOOGLE_PRODUCTS_START_PRODUCTS;
      $offset = ' OFFSET ' . $query_offset;
    }   
    $outfile = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . GOOGLE_PRODUCTS_OUTPUT_FILENAME . "_" . $type . "_" . $languages->fields['code'];
    if ($query_limit > 0) $outfile .= '_' . $query_limit; 
    if ($query_offset > 0) $outfile .= '_' . $query_offset;
    $outfile .= '.xml'; //example domain_products.xml
  }

  
  if (GOOGLE_PRODUCTS_MAGIC_SEO_URLS == 'true') {
    require_once(DIR_WS_CLASSES . 'msu_ao.php');
    include(DIR_WS_INCLUDES . 'modules/msu_ao_1.php');
  }  
  ob_start();
  $product_url_add = (GOOGLE_PRODUCTS_LANGUAGE_DISPLAY == 'true' && $languages->RecordCount() > 0 ? "&language=" . $languages->fields['code'] : '') . (GOOGLE_PRODUCTS_CURRENCY_DISPLAY == 'true' ? "&currency=" . GOOGLE_PRODUCTS_CURRENCY : '');
  //require(DIR_WS_LANGUAGES . $languages->fields['directory'] .'/googlefroogle.php');
  echo '<p>' . sprintf(TEXT_GOOGLE_PRODUCTS_STARTED, $google_base->google_base_version()) . '</p>';
  echo '<p>' . TEXT_GOOGLE_PRODUCTS_FILE_LOCATION . (($upload_file != '') ? $upload_file : $outfile) . '</p>';
  echo "<p>Processing: Feed - " . (isset($feed) && $feed == "yes" ? "Yes" : "No") . ", Upload - " . (isset($upload) && $upload == "yes" ? "Yes" : "No") . '</p>';
  ob_flush();
  flush(); 
  
  if (isset($feed) && $feed == "yes") {
    if (is_dir(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)) {
      if (!is_writeable(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)) {
        echo ERROR_GOOGLE_PRODUCTS_DIRECTORY_NOT_WRITEABLE . NL;
        die;
      }
    } else {
      echo ERROR_GOOGLE_PRODUCTS_DIRECTORY_DOES_NOT_EXIST . NL;
      die;
    }

    $stimer_feed = $google_base->microtime_float();
    
    $dom = new DOMDocument('1.0', 'utf-8');
    $rss = $dom->createElement('rss');
    $rss->setAttribute('version', '2.0');
    $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
    $channel = $dom->createElement('channel');
    $title = $dom->createElement('title');
    $title->appendChild($dom->createCDATASection($google_base->google_base_xml_sanitizer(STORE_NAME)));
    $link = $dom->createElement('link', GOOGLE_PRODUCTS_ADDRESS);
    $channel_description = $dom->createElement('description', $google_base->google_base_xml_sanitizer(GOOGLE_PRODUCTS_DESCRIPTION));
    $channel->appendChild($title);
    $channel->appendChild($link);
    $channel->appendChild($channel_description);
          
    $additional_attributes = '';
    $additional_tables = '';
    // upc
    if (GOOGLE_PRODUCTS_ASA_UPC == 'true') {
      $additional_attributes .= ", p.products_upc, p.products_isbn, p.products_ean";
    }
    // description 2
    if (GOOGLE_PRODUCTS_ASA_DESCRIPTION_2 == 'true') {
      $additional_attributes .= ", pd.products_description2";
    }
    
    if (GOOGLE_PRODUCTS_MAP_PRICING == 'true') {
      $additional_attributes .= ", p.map_price, p.map_enabled";
      $gb_map_enabled = true;
    }
    
    if (GOOGLE_PRODUCTS_META_TITLE == 'true') {
      $additional_attributes .= ", mtpd.metatags_title";
      $additional_tables .= " LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd ON (p.products_id = mtpd.products_id) ";
    }
    
    if (GOOGLE_PRODUCTS_PRODUCT_CONDITION == 'true') {
      $additional_attributes .= ", p.products_condition";
    }
    
    if (defined('GOOGLE_PRODUCTS_PAYMENT_METHODS') && GOOGLE_PRODUCTS_PAYMENT_METHODS != '') {
      $payments_accepted = preg_split('/,/', GOOGLE_PRODUCTS_PAYMENT_METHODS);
    }
    
    switch($type) {
      case "products":
        $products_query = "SELECT distinct(pd.products_name), p.products_id, p.products_model, pd.products_description, p.products_image, p.products_tax_class_id, p.products_price_sorter, p.products_priced_by_attribute, p.products_type, GREATEST(p.products_date_added, IFNULL(p.products_last_modified, 0), IFNULL(p.products_date_available, 0)) AS base_date, p.products_date_available, m.manufacturers_name, p.products_quantity, pt.type_handler, p.products_weight" . $additional_attributes . "
                           FROM " . TABLE_PRODUCTS . " p
                             LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (p.manufacturers_id = m.manufacturers_id)
                             LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                             LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON (p.products_type=pt.type_id)"
                           . $additional_tables . 
                           "WHERE p.products_status = 1
                             AND p.products_type <> 3
                             AND p.product_is_call <> 1
                             AND p.product_is_free <> 1
                             AND pd.language_id = " . (int)$languages->fields['languages_id'] ."
                             AND (
                              p.products_image IS NOT NULL
                              OR p.products_image != ''
                              OR p.products_image != '" . PRODUCTS_IMAGE_NO_IMAGE . "'
                              )
                           GROUP BY pd.products_name
                           ORDER BY p.products_id ASC" . $limit . $offset . ";";

        $products = $db->Execute($products_query);
        $total_products = $products->RecordCount();
        //die('record count: ' . $products->RecordCount());
        while (!$products->EOF) { // run until end of file or until maximum number of products reached
          $google_base_start_counter++;
          /* BEGIN GLOBAL ELEMENTS USED IN ALL ITEMS */
          // reset tax array
          $tax_rate = array();
          list($categories_list, $cPath) = $google_base->google_base_get_category($products->fields['products_id']);
          if (GOOGLE_PRODUCTS_DEBUG == 'true') {
            if (!$google_base->check_product($products->fields['products_id'])) echo $products->fields['products_id'] . ' skipped due to user restrictions<br />';
          }
          if ($google_base->check_product($products->fields['products_id'])) {
            if ($gb_map_enabled && $products->fields['map_enabled'] == '1') {
              $price = $products->fields['map_price'];
            } else {
              $price = $google_base->google_get_products_actual_price($products->fields['products_id']);
            }
            $tax_rate = zen_get_tax_rate($products->fields['products_tax_class_id']);
            // the following will only add the tax if DISPLAY_PRICE_WITH_TAX is set to true in the Zen Cart admin
            $price = zen_add_tax($price, $tax_rate);
            // modify price to match defined currency
            $price = $currencies->value($price, true, GOOGLE_PRODUCTS_CURRENCY, $currencies->get_value(GOOGLE_PRODUCTS_CURRENCY));
                          
            $products_description = $products->fields['products_description'];
            if (GOOGLE_PRODUCTS_ASA_DESCRIPTION_2 == 'true') {
              $products_description .= $products->fields['products_description2'];
            }
            $products_description = trim(substr($google_base->google_base_xml_sanitizer($products_description, $products->fields['products_id']),0,1000));
            if ( (GOOGLE_PRODUCTS_META_TITLE == 'true') && ($products->fields['metatags_title'] != '') ) {
              $productstitle = $google_base->google_base_xml_sanitizer($products->fields['metatags_title']);
            } else {
              $productstitle = $google_base->google_base_xml_sanitizer($products->fields['products_name']); 
            }
            if (GOOGLE_PRODUCTS_DEBUG == 'true') {
              $success = false;
              echo '<p>id: ' . $products->fields['products_id'] . ', price: ' . round($price, 2) . ', description length: ' . strlen($products_description) . ' ';
              if ($price <= 0) {
                echo '- skipped: price below zero, description length less than 15 chars, or title less than 3 chars';
              } elseif (strlen($products_description) < 15) {
                echo '- skipped: description length less than 15 chars';
              } elseif (strlen($productstitle) < 3) {
                echo '- skipped: title less than 3 chars';
              } else {
                echo '- including';
              }
            }
            $default_google_product_category = $google_base->google_base_xml_sanitizer(GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY); 
            if (GOOGLE_PRODUCTS_MAGIC_SEO_URLS == 'true') {
              include(DIR_WS_INCLUDES . 'modules/msu_ao_2.php'); 
            } else { // default
              $link = ($products->fields['type_handler'] ? $products->fields['type_handler'] : 'product') . '_info';
              $cPath_href = (GOOGLE_PRODUCTS_USE_CPATH == 'true' ? 'cPath=' . $cPath . '&' : '');
              $link = zen_href_link($link, $cPath_href . 'products_id=' . (int)$products->fields['products_id'] . $product_url_add, 'NONSSL', false);
            }
            if (GOOGLE_PRODUCTS_OFFER_ID != 'false') {
              switch (GOOGLE_PRODUCTS_OFFER_ID) {
                case 'model':
                  if ($products->fields['products_model']) {
                    $id = $google_base->google_base_xml_sanitizer($products->fields['products_model']);
                    break;
                  }
                case 'UPC':
                  if ($products->fields['products_upc']) {
                    $id = $products->fields['products_upc'];
                    break; 
                  }
                case 'ISBN':
                  if ($products->fields['products_isbn']) {
                    $id = $products->fields['products_isbn'];
                    break;
                  }
                case 'EAN':
                  if ($products->fields['products_ean']) {
                    $id = $products->fields['products_ean'];
                    break;
                  }
                case 'id':
                // continue
                default:
                  $id = $products->fields['products_id'];
                  break;
              } 
            }
            if (GOOGLE_PRODUCTS_PRODUCT_TYPE == 'default' && GOOGLE_PRODUCTS_DEFAULT_PRODUCT_TYPE != '') {
              $product_type = htmlentities(GOOGLE_PRODUCTS_DEFAULT_PRODUCT_TYPE);
            } else {
              $product_type = $categories_list;
              //print_r($product_type);
              //die();
              //$product_type = explode(',', $product_type);
              if (GOOGLE_PRODUCTS_PRODUCT_TYPE == 'top') {
                $product_type = htmlentities($product_type[0]);
              } elseif (GOOGLE_PRODUCTS_PRODUCT_TYPE == 'bottom') {
                $bottom_level = $product_type[sizeof($product_type) + 1]; // sets last category in array as bottom-level
                $product_type = htmlentities($bottom_level);
              } elseif (GOOGLE_PRODUCTS_PRODUCT_TYPE == 'full') {
                $full_path = implode(",", $product_type);
                $product_type = htmlentities($full_path);
              }
            }            
            if ((strlen($products_description) >= 15)) {
              // check if product has attributes
              if (zen_has_product_attributes($products->fields['products_id'], false)) {
                // check if stock by attributes
                $sba_failed = true; // default
                if ($stock_attributes) {
                  // get attributes
                  if(GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN == 'numinixproductvariants') {
                    $stock_attributes = $db->Execute("SELECT stock_id, quantity FROM " . TABLE_PRODUCTS_VARIANTS_ATTRIBUTES_STOCK . "
                                                    WHERE products_id = " . $products->fields['products_id'] . "
                                                    ORDER BY stock_id ASC;");
                    
                  } else {
                    $stock_attributes = $db->Execute("SELECT stock_id, stock_attributes, quantity FROM " . TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK . "
                                                    WHERE products_id = " . $products->fields['products_id'] . "
                                                    ORDER BY stock_id ASC;");
                  }
                  if ($stock_attributes->RecordCount() > 0) {
                    // check for acceptable variant attributes
                    $variant_count = 0;
                    while (!$stock_attributes->EOF) {
                      $variants_title = $productstitle;
                      $variants_price = $price;
                      if(GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN == 'numinixproductvariants') { 
                        $attribute_ids = array();
                        $stock_attributes_group = $db->Execute('SELECT products_attributes_id FROM ' . TABLE_PRODUCTS_VARIANTS_ATTRIBUTES_GROUPS . ' WHERE stock_id = ' . (int)$stock_attributes->fields['stock_id']);
                        while(!$stock_attributes_group->EOF) {
                          $attribute_ids[] = $stock_attributes_group->fields['products_attributes_id'];
                          $stock_attributes_group->MoveNext();
                        }
                      } else {
                        $attribute_ids = explode(',', $stock_attributes->fields['stock_attributes']);
                      }
                      $variant = false;
                      // add read only attributes to the array
                      $attributes = $db->Execute("SELECT products_attributes_id FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE attributes_display_only = 1 AND products_id = " . $products->fields['products_id'] . " ORDER BY products_attributes_id ASC;");
                      if ($attributes->RecordCount() > 0) {
                        while (!$attributes->EOF) {
                          if (!in_array($attributes->fields['products_attributes_id'], $attribute_ids)) {
                            $attribute_ids[] = $attributes->fields['products_attributes_id'];
                          }
                          $attributes->MoveNext();
                        }
                      }
                      $custom_fields = array();
                      $google_product_category_check = false;
                      foreach($attribute_ids as $attribute_id) {
                        $options = $db->Execute("SELECT po.products_options_name, pov.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_weight, pa.products_attributes_weight_prefix FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                 LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pov.products_options_values_id = pa.options_values_id) 
                                                 LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON (po.products_options_id = pa.options_id)
                                                 WHERE pa.products_attributes_id = " . (int)$attribute_id . " 
                                                 LIMIT 1;");
                        // create variants
                        if ($options->RecordCount() > 0 && in_array(strtolower($options->fields['products_options_name']), array('color', 'colour', 'material', 'pattern', 'size', 'age group', 'gender', 'google product category', 'upc', 'ean', 'isbn'))) { // require at least one to create a variant
                          if (in_array(strtolower($options->fields['products_options_name']), array('color', 'colour', 'material', 'pattern', 'size'))) {
                            // at least one of the required attributes for a variant exist
                            $variant = true;
                          }
                          // if at least one variant attribute exists, we can also add the other attribute i.e. age_group, gender, etc
                          if ($variant) {
                            $options_name = str_replace(' ', '_', strtolower($options->fields['products_options_name']));
                            if ($options_name == 'google_product_category') {
                              $google_product_category_check = true;
                            } else {
                              if ($options_name == 'colour') {
                                $options_name = 'color';
                              }
                              $variants_title .= ' ' . $google_base->google_base_xml_sanitizer($options->fields['products_options_values_name']);
                            }
                            
                            $custom_fields[$options_name] = strtolower($google_base->google_base_xml_sanitizer($options->fields['products_options_values_name']));
                             
                            if ($options->fields['price_prefix'] == '-') {
                              $variants_price-= $options->fields['options_values_price'];
                            } else {
                              $variants_price+= $options->fields['options_values_price'];
                            }
                            $variants_weight = $products->fields['products_weight'];
                            if ($options->fields['products_attributes_weight_prefix'] == '-') {
                              $variants_weight-= $options->fields['products_attributes_weight'];
                            } else {
                              $variants_weight+= $options->fields['products_attributes_weight'];
                            }
                            $variants_quantity = $stock_attributes->fields['quantity'];
                          }
                        } 
                      }
                      if ($variant == false) {
                        // no matching variants found, move to next stock combination
                        $stock_attributes->MoveNext();
                        continue;
                      } else {
                        $sba_failed = false;
                        $total_products++;
                        $anti_timeout_counter++;
                        $variant_count++; 
                        $item = $dom->createElement('item');
                        if ($variant_count == 1) {
                          $item->appendChild($dom->createElement('g:id', $id));
                        } else {
                          $item->appendChild($dom->createElement('g:item_group_id', $id));
                          $item->appendChild($dom->createElement('g:id', $id . '-' . $stock_attributes->fields['stock_id']));
                        }
                        foreach($custom_fields as $fieldName => $fieldValue) {
                          $options_values_name = $dom->createElement('g:' . $fieldName);
                          $options_values_name->appendChild($dom->createCDATASection($fieldValue));
                          $item->appendChild($options_values_name);
                        }
                        $item->appendChild($dom->createElement('g:price', number_format($variants_price, 2, '.', '') . ' ' . GOOGLE_PRODUCTS_CURRENCY));  
                        if (GOOGLE_PRODUCTS_TAX_DISPLAY == 'true' && GOOGLE_PRODUCTS_TAX_COUNTRY == 'US' && $tax_rate != '') {
                          $tax = $dom->createElement('g:tax');
                          $tax->appendChild($dom->createElement('g:country', GOOGLE_PRODUCTS_TAX_COUNTRY));
                          if (GOOGLE_PRODUCTS_TAX_REGION != '') {
                            $tax->appendChild($dom->createElement('g:region', GOOGLE_PRODUCTS_TAX_REGION));
                          }
                          if (GOOGLE_PRODUCTS_TAX_SHIPPING == 'y') {
                            $tax->appendChild($dom->createElement('g:tax_ship', GOOGLE_PRODUCTS_TAX_SHIPPING));
                          }
                          $tax->appendChild($dom->createElement('g:rate', $tax_rate));
                          $item->appendChild($tax);
                        }
                        $variantsTitle = $dom->createElement('title');
                        $variantsTitle->appendChild($dom->createCDATASection($variants_title));
                        $item->appendChild($variantsTitle);
                        
                        if (STOCK_CHECK == 'true') {
                          if ($variants_quantity > 0) {
                            $item->appendChild($dom->createElement('g:availability', 'in stock'));
                          } else {
                            // are back orders allowed?
                            if (STOCK_ALLOW_CHECKOUT == 'true') {
                              if ($products->fields['products_date_available'] != 'NULL') {
                                $item->appendChild($dom->createElement('g:availability', 'available for order'));
                              } else {
                                $item->appendChild($dom->createElement('g:availability', 'preorder'));
                              }
                            } else {
                              $item->appendChild($dom->createElement('g:availability', 'out of stock'));
                            }
                          }
                        } else {
                          $item->appendChild($dom->createElement('g:availability', 'in stock'));                  
                        }
                        if(GOOGLE_PRODUCTS_WEIGHT == 'true' && $variants_weight != '') {
                          $item->appendChild($dom->createElement('g:shipping_weight', $variants_weight . ' ' . str_replace(array('pounds', 'kilograms'), array('lb', 'kg'), GOOGLE_PRODUCTS_UNITS)));
                        }                         
                        if (defined('GOOGLE_PRODUCTS_SHIPPING_METHOD') && (GOOGLE_PRODUCTS_SHIPPING_METHOD != '') && (GOOGLE_PRODUCTS_SHIPPING_METHOD != 'none')) {   
                          $shipping_rate = $google_base->shipping_rate(GOOGLE_PRODUCTS_SHIPPING_METHOD, $percategory, $freerules, GOOGLE_PRODUCTS_RATE_ZONE, $products->fields['products_weight'], $variants_price, $products->fields['products_id']);
                          if ((float)$shipping_rate >= 0) {
                            $shipping = $dom->createElement('g:shipping');
                            if (GOOGLE_PRODUCTS_SHIPPING_COUNTRY != '') {
                              $shipping->appendChild($dom->createElement('g:country', $google_base->get_countries_iso_code_2(GOOGLE_PRODUCTS_SHIPPING_COUNTRY)));
                            }                            
                            if (GOOGLE_PRODUCTS_SHIPPING_REGION != '') {
                              $shipping->appendChild($dom->createElement('g:region', GOOGLE_PRODUCTS_SHIPPING_REGION));
                            }
                            if (GOOGLE_PRODUCTS_SHIPPING_SERVICE != '') {
                              $shipping->appendChild($dom->createElement('g:service', GOOGLE_PRODUCTS_SHIPPING_SERVICE));
                            }
                            $shipping->appendChild($dom->createElement('g:price', (float)$shipping_rate . ' ' . GOOGLE_PRODUCTS_CURRENCY));  
                            $item->appendChild($shipping);
                          }
                        }
                        // add universal elements/attributes to products                                         
                        $item = $google_base->universal_attributes($products, $item, $dom);                        
                        $channel->appendChild($item);
                        if (GOOGLE_PRODUCTS_DEBUG == 'true') {
                          $success = true;
                        }
                      }
                      $stock_attributes->MoveNext();
                    }
                  }  
                }
                // if no variants
                if ( ($sba_failed || !$stock_attributes) && $price > 0) {
                  // product still has attributes
                  $attribute_ids = array();
                  // add attributes to the array
                  $attributes = $db->Execute("SELECT products_attributes_id FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = " . $products->fields['products_id'] . " ORDER BY products_attributes_id ASC;");
                  if ($attributes->RecordCount() > 0) {
                    while (!$attributes->EOF) {
                      if (!in_array($attributes->fields['products_attributes_id'], $attribute_ids)) {
                        $attribute_ids[] = $attributes->fields['products_attributes_id'];
                      }
                      $attributes->MoveNext();
                    }
                    $google_product_category_check = false;
                    $custom_fields = array();
                    foreach($attribute_ids as $attribute_id) {
                      $options = $db->Execute("SELECT po.products_options_name, pov.products_options_values_name FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                               LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pov.products_options_values_id = pa.options_values_id) 
                                               LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON (po.products_options_id = pa.options_id)
                                               WHERE pa.products_attributes_id = " . (int)$attribute_id . " 
                                               LIMIT 1;");
                      if ($options->RecordCount() > 0 && in_array(strtolower($options->fields['products_options_name']), array('color', 'colour', 'material', 'pattern', 'size', 'age group', 'gender', 'google product category', 'upc', 'ean', 'isbn'))) {
                        $options_name = str_replace(' ', '_', strtolower($options->fields['products_options_name']));
                        if ($options_name == 'google_product_category') {
                          $google_product_category_check = true;
                        } else if ($options_name == 'colour') {
                          $options_name = 'color';
                        }
                        $custom_fields[$options_name] = strtolower($google_base->google_base_xml_sanitizer($options->fields['products_options_values_name']));
                      } 
                    }
                  }
                  $item = $google_base->create_regular_product($products, $dom);
                  foreach($custom_fields as $fieldName => $fieldValue) {
                    $options_values_name = $dom->createElement('g:' . $fieldName);
                    $options_values_name->appendChild($dom->createCDATASection($fieldValue));
                    $item->appendChild($options_values_name);
                  }
                  // add universal elements/attributes to products                                         
                  $item = $google_base->universal_attributes($products, $item, $dom);
                  // finalize item
                  $channel->appendChild($item);
                  $anti_timeout_counter++;
                  if (GOOGLE_PRODUCTS_DEBUG == 'true') {
                    $success = true;
                  } 
                }
              // if product doesn't have attributes, create a regular item without attributes
              } elseif ($price > 0) {
                if (GOOGLE_PRODUCTS_DEBUG == 'true') {
                  $success = true;
                }
                $item = $google_base->create_regular_product($products, $dom);
                // add universal elements/attributes to products                                         
                $item = $google_base->universal_attributes($products, $item, $dom);
                // finalize item
                $channel->appendChild($item);
                $anti_timeout_counter++;
              } 
            }
            if (GOOGLE_PRODUCTS_DEBUG == 'true') {
              if ($success) {
                echo ' - success';
              } else {
                echo ' - failed';
              }
              echo '</p>';
            }
          }
          ob_flush();
          flush();
          $products->MoveNext();
        }
        $rss->appendChild($channel);
        $dom->appendChild($rss);
        $dom->formatOutput = true;
        if (GOOGLE_PRODUCTS_COMPRESS != 'true') // Use uncompressed file
        {
          $dom->save($outfile); // Write uncompressed file
        }
          else // Compress file
        {
          $outfile.= '.gz'; // Append .gz to end of file name
          $data = $dom->saveXML(); //Save XML feed to string
          $gz = gzopen("$outfile",'w9'); // Open file for writing, 0 (no) to 9 (maximum) compression
          gzwrite($gz, $data, strlen($data)); // Write compressed file
          gzclose($gz); // Close file handler
        } 
      break;
    }
    
    $timer_feed = $google_base->microtime_float()-$stimer_feed;
    
    echo '<p>' . TEXT_GOOGLE_PRODUCTS_FEED_COMPLETE . ' ' . GOOGLE_PRODUCTS_TIME_TAKEN . ' ' . sprintf("%f " . TEXT_GOOGLE_PRODUCTS_FEED_SECONDS, number_format($timer_feed, 6) ) . ' ' . $anti_timeout_counter . ' of ' . $total_products . ' ' . TEXT_GOOGLE_PRODUCTS_FEED_RECORDS . '</p>';  
  }

  if (isset($upload) && $upload == "yes") {
    echo TEXT_GOOGLE_PRODUCTS_UPLOAD_STARTED . NL;
    if ($upload_file == '') $upload_file = $outfile; // use file just created if no upload file was specified
    if($google_base->ftp_file_upload(GOOGLE_PRODUCTS_SERVER, GOOGLE_PRODUCTS_USERNAME, GOOGLE_PRODUCTS_PASSWORD, $upload_file)) {
      echo TEXT_GOOGLE_PRODUCTS_UPLOAD_OK . NL;
      $db->execute("update " . TABLE_CONFIGURATION . " set configuration_value = '" . date("Y/m/d H:i:s") . "' where configuration_key='GOOGLE_PRODUCTS_UPLOADED_DATE'");
    } else {
      echo TEXT_GOOGLE_PRODUCTS_UPLOAD_FAILED . NL;
    }
  }
?>