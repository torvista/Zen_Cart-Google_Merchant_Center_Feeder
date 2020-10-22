<?php
/**
 * @package google base feeder
 * @copyright Copyright 2007-2008 Numinix Technology http://www.numinix.com
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: google_base.php 64 2011-08-31 16:07:57Z numinix $
 * @author Numinix Technology
 */
 
  class google_base {

      public array $additional_images_array;//stores the additional images found, per-product, for re-use if that product id comes up again...when does that happen? DISABLED
      public array $image_files; //stores the images per-directory to prevent re-scanning if that directory has already ben scanned

      public function additional_images($product_main_image, $product_id): array
      {
          $debug_additional_images = false;//verbose step-by-step processing output for dummies

          if ($debug_additional_images) {
              echo '<hr>function additional_images' . NL . __LINE__ . ' $product_main_image=' . $product_main_image . ' | $product_id = ' . $product_id . NL;
          }

          // if ($product_main_image !== '') {// removed: it always is !='', or function is not called
          $images_array = [];
          if (isset($this->additional_images_array[$product_id]) && is_array($this->additional_images_array[$product_id])) {//has this product id already been analysed?
              if ($debug_additional_images) {
                  echo __LINE__ . ' $this->additional_images_array exists for #' . $product_id . NL;
                  mv_printVar($this->additional_images_array);
              }
              //no need to process this product_id, return
              $images_array = $this->additional_images_array[$product_id];

          } else {//product id has not been processed previously

              // analyse main image name
              $product_main_image_extension = substr($product_main_image, strrpos($product_main_image, '.'));// eg. '.jpg'
              //$product_main_image_extension = pathinfo($product_main_image, PATHINFO_EXTENSION);//// eg. 'jpg' TODO simplify all this extraction

              if ($debug_additional_images) {
                  echo __LINE__ . ' $product_main_image_extension=' . $product_main_image_extension . NL;
              }
              $product_main_image_filename = str_replace($product_main_image_extension, '', $product_main_image);

              // check for subdirectory
              if (strrpos($product_main_image, '/')) {
                  $products_image_match = substr($product_main_image, strrpos($product_main_image, '/') + 1);//remove subdirectory from main image name

                  if ($debug_additional_images) {
                      echo __LINE__ . ' is in subdirectory, $products_image_match=' . $products_image_match . NL;
                  }

                  $products_image_match = str_replace($product_main_image_extension, '', $products_image_match) . '_';//construct imagenamestub_

                  if ($debug_additional_images) {
                      echo __LINE__ . ' $products_image_match=' . $products_image_match . NL;
                  }

                  $product_main_image_filename = $products_image_match;
              } elseif ($debug_additional_images) {
                  echo __LINE__ . ' is in root' . NL;
              }

              $products_image_directory = str_replace($product_main_image, '', substr($product_main_image, strrpos($product_main_image, '/')));

              if ($products_image_directory !== '') {
                  $products_image_directory = DIR_WS_IMAGES . str_replace($products_image_directory, '', $product_main_image) . "/";
              } else {
                  $products_image_directory = DIR_WS_IMAGES;
              }

              // Check for additional images
              if (isset($this->image_files[$products_image_directory]) && is_array($this->image_files[$products_image_directory])) {//check for files in the same directory as the main image
                  if ($debug_additional_images) {
                      echo __LINE__ . ' this directory "' . $products_image_directory . '" has previously been scanned:' . NL;
                      mv_printVar($this->image_files);
                  }
                  $image_files = $this->image_files[$products_image_directory];//array of all files in image directory
              } else {
                  if ($debug_additional_images) {
                      echo __LINE__ . ' not set $this->image_files[$products_image_directory]: this directory not previously scanned' . NL;
                  }
                  $image_files = scandir($products_image_directory);
                  $this->image_files[$products_image_directory] = $image_files;
                  if ($debug_additional_images) {
                      echo __LINE__ . ' directory "' . $products_image_directory . '" added to $this->image_files' . NL;
                      mv_printVar($this->image_files);
                  }
              }

              if (is_array($image_files) && count($image_files) > 0) {
                  if ($debug_additional_images) {
                      echo __LINE__ . ' files found in "' . $products_image_directory . '"' . NL;
                      mv_printVar($image_files);
                  }

                  foreach ($this->image_files[$products_image_directory] as $file) {
                      /*$file_extension = substr($file, strrpos($file, '.'));
                      if ($debug_additional_images) {
                          echo __LINE__ . ' $file_extension=' . $file_extension . NL;
                      }

                      $file_base = str_replace($file_extension, '', $file);
                      if ($debug_additional_images) {
                          echo __LINE__ . ' $file_base=' . $file_base . NL;
                      }*/
                      // skip the main image and make sure the base and extension match the main image
                      if (($file !== $product_main_image) && (preg_match("/" . $product_main_image_filename . "/i", $file) === 1)) {
                          $images_array[] = $this->google_base_image_url(($products_image_directory !== '' ? str_replace(DIR_WS_IMAGES, '', $products_image_directory) : '') . $file);
                          if ($debug_additional_images) {
                              echo __LINE__ . ' loop build array of additional images' . NL;
                              mv_printVar($images_array);
                          }

                          if (count($images_array) >= GOOGLE_PRODUCTS_MAX_ADDITIONAL_IMAGES) {
                              if ($debug_additional_images) {
                                  echo __LINE__ . ' maximum additional images (' . GOOGLE_PRODUCTS_MAX_ADDITIONAL_IMAGES . ') reached: break out of loop' . NL;
                              }
                              break;
                          }
                      }
                  }
              }
              /*DISABLED: See start of function
              $this->additional_images_array[$product_id] = $images_array;
              if ($debug_additional_images) {
                  echo __LINE__ . ' $this->additional_images_array stored to object' . NL;
                  mv_printVar($this->additional_images_array);
              }
              */
          }
          if ($debug_additional_images) {
              echo __LINE__ . ' images_array of additional images returned from function' . NL;
              mv_printVar($images_array);
          }
          return $images_array;
      }
   
    // writes out the code into the feed file
    function google_base_fwrite($output='', $mode, $products_id = '') { // added products id for debugging
      global $outfile;
      $output = implode("\n", $output);
      //if(strtolower(CHARSET) != 'utf-8') {
        //$output = utf8_encode($output);
      //}
      //$fp = fopen($outfile, $mode);
      //$retval = fwrite($fp, $output, GOOGLE_PRODUCTS_OUTPUT_BUFFER_MAXSIZE);
      if ($mode === 'a') {
          $mode = 'FILE_APPEND';
      }
      file_put_contents($outfile, $output, $mode);
      //return $retval;
    }
    
    // gets the Google Base Feeder version number from the Module Versions file
    function google_base_version() {
      return trim(GOOGLE_PRODUCTS_VERSION);
    }  
    
    // trims the value of each element of an array
    function trim_array($x) {
      if (is_array($x)) {
         return array_map('trim_array', $x);
      }
        return trim($x);
    } 

    // determines if the feed should be generated
    function get_feed($feed_parameter) {
      switch($feed_parameter) {
        case 'fy':
          $feed = 'yes';
          break;
        default: //fn
          $feed = 'no';
          break;
      }
      return $feed;
    }

    // determines if the feed should be automatically uploaded to Google Base
    function get_upload($upload_parameter) {
      switch($upload_parameter) {
        case 'uy':
          $upload = 'yes';
          break;
        default: //un
          $upload = 'no';
          break;
      }
      return $upload;
    }
    
    // returns the type of feed
    function get_type($type_parameter) {
      switch($type_parameter) {
        /*case 'tp':
          $type = 'products';
          break;*/
        case 'td':
          $type = 'documents';
          break;
        case 'tn':
          $type = 'news';
          break;
        default:
          $type = 'products';
          break;
      }
      return $type;
    }
    
    // performs a set of functions to see if a product is valid
    function check_product($products_id) {
        return $this->included_categories_check(GOOGLE_PRODUCTS_POS_CATEGORIES, $products_id) && !$this->excluded_categories_check(GOOGLE_PRODUCTS_NEG_CATEGORIES, $products_id) && $this->included_manufacturers_check(GOOGLE_PRODUCTS_POS_MANUFACTURERS, $products_id) && !$this->excluded_manufacturers_check(GOOGLE_PRODUCTS_NEG_MANUFACTURERS, $products_id);
    }
    
    // check to see if a product is inside an included category
    function included_categories_check($categories_list, $products_id) {
      if ($categories_list === '') {
        return true;
      }
        $categories_array = explode(',', $categories_list);
        $match = false;
        foreach($categories_array as $category_id) {
          if (zen_product_in_category($products_id, $category_id)) {
            $match = true;
            break;
          }
        }
        return $match === true;
    }
    
    // check to see if a product is inside an excluded category
    function excluded_categories_check($categories_list, $products_id) {
      if ($categories_list === '') {
        return false;
      }

        $categories_array = explode(',', $categories_list);
        $match = false;
        foreach($categories_array as $category_id) {
          if (zen_product_in_category($products_id, $category_id)) {
            $match = true;
            break;
          }
        }
        return $match === true;
    }
    
    // check to see if a product is from an included manufacturer
    function included_manufacturers_check($manufacturers_list, $products_id) {
      if ($manufacturers_list === '') {
        return true;
      }

        $manufacturers_array = explode(',', $manufacturers_list);
        $products_manufacturers_id = zen_get_products_manufacturers_id($products_id);
        if (in_array($products_manufacturers_id, $manufacturers_array)) {
          return true;
        }
        return false;
    }
    
    function excluded_manufacturers_check($manufacturers_list, $products_id) {
      if ($manufacturers_list === '') {
        return false;
      }

        $manufacturers_array = explode(',', $manufacturers_list);
        $products_manufacturers_id = zen_get_products_manufacturers_id($products_id);
        if (in_array($products_manufacturers_id, $manufacturers_array)) {
          return true;
        }

        return false;
    }
    
    function google_base_get_category($products_id) {
      global $db;
      
      // get the master_categories_id
      $master_categories_id = $db->Execute("SELECT master_categories_id FROM " . TABLE_PRODUCTS . " WHERE products_id = " . $products_id . " LIMIT 1;");
      $master_categories_id = $master_categories_id->fields['master_categories_id'];
      
      // build the cPath
      $cPath_array = zen_generate_category_path($master_categories_id);
      $category_names = [];
      $cPath = [];
      $cPath_array[0] = array_reverse($cPath_array[0]);
      foreach ($cPath_array[0] as $category) {
        $category_names[] = zen_get_category_name($category['id'], (int)GOOGLE_PRODUCTS_LANGUAGE); // have to use this function just in case of a different language
        $cPath[] = $category['id'];
      }
      return [$category_names, $cPath];  
    }
    
    // returns an array containing the category name and cPath
    /*
    function google_base_get_category($products_id) {
      global $categories_array, $db;
      static $p2c;
      if(!$p2c) {
        $q = $db->Execute("SELECT *
                          FROM " . TABLE_PRODUCTS_TO_CATEGORIES);
        while (!$q->EOF) {
          if(!isset($p2c[$q->fields['products_id']]))
            $p2c[$q->fields['products_id']] = $q->fields['categories_id'];
          $q->MoveNext();
        }
      }
      if(isset($p2c[$products_id])) {
        $retval = $categories_array[$p2c[$products_id]]['name'];
        $cPath = $categories_array[$p2c[$products_id]]['cPath'];
      } else {
        $cPath = $retval =  "";
      }
      return array($retval, $cPath);
    }
    */
    
    // builds the category tree
    function google_base_category_tree($id_parent=0, $cPath='', $cName='', $cats= []){
      global $db, $languages;
      $cat = $db->Execute("SELECT c.categories_id, c.parent_id, cd.categories_name
                           FROM " . TABLE_CATEGORIES . " c
                             LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id
                           WHERE c.parent_id = '" . (int)$id_parent . "'
                           AND cd.language_id='" . (int)$languages->fields['languages_id'] . "'
                           AND c.categories_status= '1'",
                           '', false, 150);//todo what's this
      while (!$cat->EOF) {//todo foreach
        $cats[$cat->fields['categories_id']]['name'] = (zen_not_null($cName) ? $cName . ', ' : '') . trim($cat->fields['categories_name']); // previously used zen_froogle_sanita instead of trim
        $cats[$cat->fields['categories_id']]['cPath'] = (zen_not_null($cPath) ? $cPath . ',' : '') . $cat->fields['categories_id'];
        if (zen_has_category_subcategories($cat->fields['categories_id'])) {
          $cats = $this->google_base_category_tree($cat->fields['categories_id'], $cats[$cat->fields['categories_id']]['cPath'], $cats[$cat->fields['categories_id']]['name'], $cats);
        }
        $cat->MoveNext();
      }
      return $cats;
    }
    
    // create a product that doesn't use stock by attributes
    function create_regular_product($products, $dom) {
      global $id, $price, $tax_rate, $productstitle, $percategory, $freerules;
      $item = $dom->createElement('item');
      $products_title = $dom->createElement('title');
      $products_title->appendChild($dom->createCDATASection($productstitle));
      $item->appendChild($products_title);
      $iD = $dom->createElement('g:id');
      $iD->appendChild($dom->createCDATASection($id));
      $item->appendChild($iD);      
      
		$item->appendChild($dom->createElement('g:price', number_format($price, 2, '.', '') . ' ' . GOOGLE_PRODUCTS_CURRENCY));  
      if (GOOGLE_PRODUCTS_TAX_DISPLAY === 'true' && GOOGLE_PRODUCTS_TAX_COUNTRY === 'US' && $tax_rate != '') {
        $tax = $dom->createElement('g:tax');
        $tax->appendChild($dom->createElement('g:country', GOOGLE_PRODUCTS_TAX_COUNTRY));
        if (GOOGLE_PRODUCTS_TAX_REGION !== '') {
          $tax->appendChild($dom->createElement('g:region', GOOGLE_PRODUCTS_TAX_REGION));
        }
        if (GOOGLE_PRODUCTS_TAX_SHIPPING === 'y') {
          $tax->appendChild($dom->createElement('g:tax_ship', GOOGLE_PRODUCTS_TAX_SHIPPING));
        }
        $tax->appendChild($dom->createElement('g:rate', $tax_rate));
        $item->appendChild($tax);
      }
//availability: only "in_stock", "out_of_stock", "preorder" allowed
        if (STOCK_CHECK === 'true') {//products have physical stock, and it is checked
            if ($products->fields['products_quantity'] > 0) {
                $item->appendChild($dom->createElement('g:availability', 'in_stock'));
            } elseif (STOCK_ALLOW_CHECKOUT === 'true') {//allow checkout without stock
                if ($products->fields['products_date_available'] > date('Y-m-d H:i:s')) {//only NULL if a date has never been set
                    $item->appendChild($dom->createElement('g:availability', 'preorder'));
                } else {
                    $item->appendChild($dom->createElement('g:availability', 'in_stock'));//replaces backorder.
                }
            } else {
                $item->appendChild($dom->createElement('g:availability', 'out_of_stock'));
            }
        } else {//no stock check
            $item->appendChild($dom->createElement('g:availability', 'in_stock'));
        }

      if(GOOGLE_PRODUCTS_WEIGHT === 'true' && $products->fields['products_weight'] !== '') {
        $item->appendChild($dom->createElement('g:shipping_weight', $products->fields['products_weight'] . ' ' . str_replace(['pounds', 'kilograms'], ['lb', 'kg'], GOOGLE_PRODUCTS_UNITS)));
      } 
      if (defined('GOOGLE_PRODUCTS_SHIPPING_METHOD') && (GOOGLE_PRODUCTS_SHIPPING_METHOD !== '') && (GOOGLE_PRODUCTS_SHIPPING_METHOD !== 'none')) {   
        $shipping_rate = $this->shipping_rate(GOOGLE_PRODUCTS_SHIPPING_METHOD, $percategory, $freerules, GOOGLE_PRODUCTS_RATE_ZONE, $products->fields['products_weight'], $price, $products->fields['products_id']);
        if ((float)$shipping_rate >= 0) {
          $shipping = $dom->createElement('g:shipping');
          if (GOOGLE_PRODUCTS_SHIPPING_COUNTRY !== '') {
            $shipping->appendChild($dom->createElement('g:country', $this->get_countries_iso_code_2(GOOGLE_PRODUCTS_SHIPPING_COUNTRY)));
          }
          
          if (GOOGLE_PRODUCTS_SHIPPING_REGION !== '') {
            $shipping->appendChild($dom->createElement('g:region', GOOGLE_PRODUCTS_SHIPPING_REGION));
          }
          if (GOOGLE_PRODUCTS_SHIPPING_SERVICE !== '') {
            $shipping->appendChild($dom->createElement('g:service', GOOGLE_PRODUCTS_SHIPPING_SERVICE));
          }
          $shipping->appendChild($dom->createElement('g:price', (float)$shipping_rate));
          $item->appendChild($shipping);
        }
      }
                       
      return $item;
    }
    
    // takes already created $item and adds universal attributes from $products
    function universal_attributes($products, $item, $dom) {
      global $link, $product_type, $payments_accepted, $google_product_category_check, $default_google_product_category, $products_description;
      if ($products->fields['manufacturers_name'] !== '') {
        $manufacturers_name = $dom->createElement('g:brand');
        $manufacturers_name->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($products->fields['manufacturers_name'])));
        $item->appendChild($manufacturers_name);
      }
      if (GOOGLE_PRODUCTS_PRODUCT_CONDITION === 'true' && $products->fields['products_condition'] !== '') {
        $item->appendChild($dom->createElement('g:condition', $products->fields['products_condition']));
      } else {
        $item->appendChild($dom->createElement('g:condition', GOOGLE_PRODUCTS_CONDITION));
      }
      
      if ($product_type) {
        $item->appendChild($dom->createElement('g:product_type', $product_type));
      }
      if ($products->fields['products_image'] !== '') {
        $item->appendChild($dom->createElement('g:image_link', $this->google_base_image_url($products->fields['products_image'])));
        $additional_images = $this->additional_images($products->fields['products_image'], $products->fields['products_id']);
        if (is_array($additional_images) && count($additional_images) > 0) {
          //$count = 0;//todo remove Limit of 10 is done in function
          foreach ($additional_images as $additional_image) {
            //$count++;
            $item->appendChild($dom->createElement('g:additional_image_link', $additional_image));
            //if ($count === 9) break; // max 10 images including main image
          }
        }
      }
      // only include if less then 30 days as 30 is the max and leaving blank will default to the max
      if (GOOGLE_PRODUCTS_EXPIRATION_DAYS <= 29) {
        $item->appendChild($dom->createElement('g:expiration_date', $this->google_base_expiration_date($products->fields['base_date'])));
      }
      $item->appendChild($dom->createElement('link', $link));
      if ($products->fields['products_model'] != '') {
        $mpn = $dom->createElement('g:mpn');
        $mpn->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($products->fields['products_model'])));
        $item->appendChild($mpn);
      }
      if (GOOGLE_PRODUCTS_ASA_UPC === 'true') {
        if ($products->fields['products_upc'] !== '') {
          $upc = $dom->createElement('g:upc');
          $upc->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($products->fields['products_upc'])));
          $item->appendChild($upc);
        } elseif ($products->fields['products_isbn'] !== '') {
          $isbn = $dom->createElement('g:isbn');
          $isbn->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($products->fields['products_isbn'])));
          $item->appendChild($isbn);
        } elseif ($products->fields['products_ean'] !== '') {
          $ean = $dom->createElement('g:ean');
          $ean->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($products->fields['products_ean'])));
          $item->appendChild($ean);                  
        }
      }
      if (GOOGLE_PRODUCTS_CURRENCY_DISPLAY === 'true') {
        $item->appendChild($dom->createElement('g:currency', GOOGLE_PRODUCTS_CURRENCY));
      }
      if(GOOGLE_PRODUCTS_PICKUP !== 'do not display') {
        $item->appendChild($dom->createElement('g:pickup', GOOGLE_PRODUCTS_PICKUP));
      }
      if (defined('GOOGLE_PRODUCTS_PAYMENT_METHODS') && GOOGLE_PRODUCTS_PAYMENT_METHODS !== '') { 
        foreach($payments_accepted as $payment_accepted) {
          $item->appendChild($dom->createElement('g:payment_accepted', trim($payment_accepted)));
        }
      }
      if (defined('GOOGLE_PRODUCTS_PAYMENT_NOTES') && GOOGLE_PRODUCTS_PAYMENT_NOTES !== '') {
        $item->appendChild($dom->createElement('g:payment_notes', trim(GOOGLE_PRODUCTS_PAYMENT_NOTES)));
      }
      $productsDescription = $dom->createElement('description');
      $productsDescription->appendChild($dom->createCDATASection(substr($products_description, 0, 9988))); // 10000 - 12 to account for cData
      $item->appendChild($productsDescription);
      if ($google_product_category_check === false && GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY !== '') {
        $google_product_category = $dom->createElement('g:google_product_category');
        $google_product_category->appendChild($dom->createCDATASection($default_google_product_category));
        $item->appendChild($google_product_category);
      }     
      return $item;
    }
    
    function google_base_sanita($str, $rt=false) {
      //global $products;
      $str = str_replace(["\r\n", "\r", "\n", "&nbsp;", '’'], [' ', ' ', ' ', ' ', "'"], $str);
      $str = strip_tags($str);
      //$charset = 'UTF-8';
      //if (defined(CHARSET)) {
        //$charset = strtoupper(CHARSET);
      //}
      $str = html_entity_decode($str, ENT_QUOTES);//, $charset);
      //$str = html_entity_decode($str, ENT_QUOTES, $charset);
      //$str = htmlspecialchars($str, ENT_QUOTES, '', false);
      //$str = htmlentities($str, ENT_QUOTES, $charset, false); 
      return $str;
    }
             
    function google_base_xml_sanitizer($str, $products_id = '') { // products id added for debugging purposes
      $str = $this->google_base_sanita($str);
      if (GOOGLE_PRODUCTS_XML_SANITIZATION === 'true') {
        $str = $this->transcribe_cp1252_to_latin1($str); // transcribe windows characters
        $strout = null;

        for ($i = 0; $i < strlen($str); $i++) {//todo IDE
          $ord = ord($str[$i]);
          if (($ord > 0 && $ord < 32) || ($ord >= 127)) {
            $strout .= "&#{$ord};";
          }
          else {
            switch ($str[$i]) {
              case '<':
                $strout .= '&lt;';
                break;
              case '>':
                $strout .= '&gt;';
                break;
              //case '&':
                //$strout .= '&amp;';
                //break;
              case '"':
                $strout .= '&quot;';
                break;
              default:
                $strout .= $str[$i];
            }
          }
        }
        $str = null;
        return $strout;
      }

        return $str;
    }
    
    function transcribe_cp1252_to_latin1($cp1252) {
      return strtr(
        $cp1252,
        [
          "\x80" => "e",  "\x81" => " ",    "\x82" => "'", "\x83" => 'f',
          "\x84" => '"',  "\x85" => "...",  "\x86" => "+", "\x87" => "#",
          "\x88" => "^",  "\x89" => "0/00", "\x8A" => "S", "\x8B" => "<",
          "\x8C" => "OE", "\x8D" => " ",    "\x8E" => "Z", "\x8F" => " ",
          "\x90" => " ",  "\x91" => "`",    "\x92" => "'", "\x93" => '"',
          "\x94" => '"',  "\x95" => "*",    "\x96" => "-", "\x97" => "--",
          "\x98" => "~",  "\x99" => "(TM)", "\x9A" => "s", "\x9B" => ">",
          "\x9C" => "oe", "\x9D" => " ",    "\x9E" => "z", "\x9F" => "Y"
        ]
      );
    }
    
    // creates the url for the products_image
    function google_base_image_url($products_image) {
      if ($products_image === "") {
          return "";
      }
      if (defined('GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL') && GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL !== '') {
        if (strpos(GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL, HTTP_SERVER . '/' . DIR_WS_IMAGES) !== false) {
          $products_image = substr(GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL, strlen(HTTP_SERVER . '/' . DIR_WS_IMAGES)) . $products_image;
        } else {
          return GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL . rawurlencode($products_image);
        } 
      }
      $products_image_extension = substr($products_image, strrpos($products_image, '.'));
      $products_image_base = preg_replace("/" . $products_image_extension . "/", '', $products_image);
      $products_image_medium = $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
      $products_image_large = $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;
      
      // check for a large image else use medium else use small
      if (!file_exists(DIR_WS_IMAGES . 'large/' . $products_image_large)) {
        if (!file_exists(DIR_WS_IMAGES . 'medium/' . $products_image_medium)) {
          $products_image_large = DIR_WS_IMAGES . $products_image;
        } else {
          $products_image_large = DIR_WS_IMAGES . 'medium/' . $products_image_medium;
        }
      } else {
        $products_image_large = DIR_WS_IMAGES . 'large/' . $products_image_large;
      }
      if ((function_exists('handle_image')) && (GOOGLE_PRODUCTS_IMAGE_HANDLER === 'true')) {
        $image_ih = handle_image($products_image_large, '', LARGE_IMAGE_MAX_WIDTH, LARGE_IMAGE_MAX_HEIGHT, '');
        $retval = (HTTP_SERVER . DIR_WS_CATALOG . $image_ih[0]);
      } else {
        $retval = (HTTP_SERVER . DIR_WS_CATALOG . rawurlencode($products_image_large));
      }
     // $retval = str_replace('%2F', '/', $retval);
     // $retval = str_replace('%28', '(', $retval);
	 // return str_replace('%29', ')', $retval);
        return str_replace(['%2F', '%28', '%29'], ['/', '(', ')'], $retval);
    }
    
    // creates the url for a News and Articles Manager article
    function google_base_news_link($article_id) {
      $link = zen_href_link(FILENAME_NEWS_ARTICLE, 'article_id=' . (int)$article_id . $product_url_add, 'NONSSL', false);
      return $link;
    }
    
    function google_base_expiration_date($base_date) {
      if (GOOGLE_PRODUCTS_EXPIRATION_BASE === 'now') {
          $expiration_date = time();
      }
      else {
          $expiration_date = strtotime($base_date);
      }
      $expiration_date += GOOGLE_PRODUCTS_EXPIRATION_DAYS*24*60*60;
      $retval = (date('Y-m-d', $expiration_date));
      return $retval;
    }
    
// SHIPPING FUNCTIONS //

  function get_countries_iso_code_2($countries_id) {
    global $db;

    $countries_query = "select countries_iso_code_2
                        from " . TABLE_COUNTRIES . "
                        where countries_id = '" . $countries_id . "'
                        limit 1";
    $countries = $db->Execute($countries_query);
      return $countries->fields['countries_iso_code_2'];
  }

    function shipping_rate($method, $percategory = '', $freerules = '', $table_zone = '', $products_weight = '', $products_price = '', $products_id = '')
    {
        global $currencies, $percategory, $freerules;
        // skip the calculation for products that are always free shipping
        $rate = 0;
        if (zen_get_product_is_always_free_shipping($products_id)) {
            $rate = 0;
        } else {
            switch ($method) {
//Zen Cart built-in shipping methods
                case "flat":
                    $rate = MODULE_SHIPPING_FLAT_COST;
                    break;
                /*
                 case "freeoptions":
                    $rate = 0;
                    break;
                 */
                case "freeshipper":
                    $rate = 0;
                    break;
                case "item":
                    $rate = MODULE_SHIPPING_ITEM_COST + MODULE_SHIPPING_ITEM_HANDLING;
                    break;
                case "perweightunit":
                    $rate = (MODULE_SHIPPING_PERWEIGHTUNIT_COST * $products_weight) + MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING;
                    break;
                /*
                 case "storepickup":
                    $rate = 0;
                    break;
                 */
                case "table":
                    $rate = $this->numinix_table_rate($products_weight, $products_price);
                    break;
                case "zones":
                    $rate = $this->numinix_zones_rate($products_weight, $products_price, $table_zone);
                    break;
//eof Zen Cart built-in shipping methods
//Third party shipping modules
                case "advshipper"://CEON Advanced Shipper: https://ceon.net/shipping-modules/ceon-advanced-shipper-module
                    $rate = '99';//todo!!
                    break;
                //Numinix shipping module: https://www.numinix.com/zen-cart-plugins-shipping-c-179_250_373_163/free-shipping-rules-dl-755
                case "freerules":
                    if (is_object($freerules)) {
                        if ($freerules->test($products_id)) {
                            $rate = 0;
                        } else {
                            $rate = -1;
                        }
                    }
                    break;
                case "percategory"://Numinix shipping module: https://www.numinix.com/zen-cart-plugins-shipping-c-179_250_373_163/per-category-shipping-standard-dl-771
                    if (is_object($percategory)) {
                        $products_array = [];
                        $products_array[0]['id'] = $products_id;
                        $rate = $percategory->calculation($products_array, $table_zone, (int)MODULE_SHIPPING_PERCATEGORY_GROUPS);
                    }
                    break;
                case "zonetable"://Plugin Zones Table Rate (for Multiple Zones): https://www.zen-cart.com/downloads.php?do=file&id=478
                    $rate = $this->numinix_zones_table_rate($products_weight, $table_zone);
                    break;
//eof Third party shipping modules

                default: // also 'none'
                    $rate = -1;
                    break;
            }
        }
        if ($rate >= 0 && GOOGLE_PRODUCTS_CURRENCY !== '' && $currencies->get_value(GOOGLE_PRODUCTS_CURRENCY) !== '') {
            $rate = $currencies->value($rate, true, GOOGLE_PRODUCTS_CURRENCY, $currencies->get_value(GOOGLE_PRODUCTS_CURRENCY));
        }
        return $rate;
    }
  
  function numinix_table_rate($products_weight, $products_price) {//Zen Cart shipping method: table
    global $currencies;
    
     switch (MODULE_SHIPPING_TABLE_MODE) {
      case ('weight'):
       $order_total = $products_weight;
        break;
      case ('price'):
        $order_total = $products_price;
        break;
      case ('item'):
        $order_total = 1;
        break;
    }

    $table_cost = $this->google_multi_explode(',', ':', MODULE_SHIPPING_TABLE_COST);
    $size = count($table_cost);
    for ($i=0, $n=$size; $i<$n; $i+=2) {
      if (round($order_total,9) <= $table_cost[$i]) {
        //if (strstr($table_cost[$i+1], '%')) {
          if (strpos($table_cost[$i + 1], '%') !== false) {//todo check
          $shipping = ($table_cost[$i+1]/100) * $products_price;
        } else {
          $shipping = $table_cost[$i+1];
        }
        break;
      }
    }
    $shipping += MODULE_SHIPPING_TABLE_HANDLING;
    return $shipping;
  }
    
  function numinix_zones_table_rate($products_weight, $table_zone) {//Plugin: Zones Table Rate (for Multiple Zones) https://www.zen-cart.com/downloads.php?do=file&id=478
    global $currencies;
    
    switch (MODULE_SHIPPING_ZONETABLE_MODE) {
     case ('weight'):
       $order_total = $products_weight;
        break;
      case ('price'):
        $order_total = $products_price;
        break;
      case ('item'):
        $order_total = 1;
        break;
    }
    
    $table_cost = $this->google_multi_explode(',', ':', constant('MODULE_SHIPPING_ZONETABLE_COST_' . $table_zone));
    $size = count($table_cost);
    for ($i=0, $n=$size; $i<$n; $i+=2) {
      if (round($order_total,9) <= $table_cost[$i]) {
        $shipping = $table_cost[$i+1];
        break;
      }
    }
    $shipping += constant('MODULE_SHIPPING_ZONETABLE_HANDLING_' . $table_zone);
    return $shipping;
  }
  
  function numinix_zones_rate($products_weight, $products_price, $table_zone) {//Zen Cart shipping method: zones
    global $currencies;
    
    switch (MODULE_SHIPPING_ZONES_METHOD) {
      case ('Weight'):
        $order_total = $products_weight;
        break;
      case ('Price'):
        $order_total = $products_price;
        break;
      case ('Item'):
        $order_total = 1;
        break;
    }
    
    $zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $table_zone);
    $zones_table = $this->google_multi_explode(',', ':', $zones_cost);
    $size = count($zones_table);
    for ($i=0; $i<$size; $i+=2) {
      if (round($order_total,9) <= $zones_table[$i]) {
        //if (strstr($zones_table[$i+1], '%')) {
          if (strpos($zones_table[$i + 1], '%') !== false) {//todo check
          $shipping = ($zones_table[$i+1]/100) * $products_price;
        } else {
          $shipping = $zones_table[$i+1];
        }
         break;
      }
    }
    $shipping += constant('MODULE_SHIPPING_ZONES_HANDLING_' . $table_zone);
    return $shipping;
  }
  
  function google_multi_explode($delim1, $delim2, $string) {
  	$new_data = [];
  	$data = explode($delim1, $string);
  	foreach ($data as $key => $value) {
  	  $new_data = array_merge($new_data, explode($delim2, $value));
	}
	return $new_data;
  }
// PRICE FUNCTIONS

// Actual Price Retail
// Specials and Tax Included
  function google_get_products_actual_price($products_id) {
    global $db, $currencies;
    $product_check = $db->Execute("SELECT products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call 
                                   FROM " . TABLE_PRODUCTS . " 
                                   WHERE products_id = '" . (int)$products_id . "'" . " LIMIT 1");

    $show_display_price = '';
    $display_normal_price = $this->google_get_products_base_price($products_id);
    //echo $display_normal_price . '<br />';
    $display_special_price = $this->google_get_products_special_price($products_id, $display_normal_price, true);
    //echo $display_special_price . '<br />';
    $display_sale_price = $this->google_get_products_special_price($products_id, $display_normal_price, false);
    //echo $display_sale_price . '<br />';
    $products_actual_price = $display_normal_price;

    if ($display_special_price) {
      $products_actual_price = $display_special_price;
    }
    if ($display_sale_price) {
      $products_actual_price = $display_sale_price;
    }

    // If Free, Show it
    if ($product_check->fields['product_is_free'] === '1') {
      $products_actual_price = 0;
    }
    //die();

    return $products_actual_price;
  }

// computes products_price + option groups lowest attributes price of each group when on
  function google_get_products_base_price($products_id) {
    global $db;
      $product_check = $db->Execute("SELECT products_price, products_priced_by_attribute 
                                     FROM " . TABLE_PRODUCTS . " 
                                     WHERE products_id = " . (int)$products_id);

// is there a products_price to add to attributes
      $products_price = $product_check->fields['products_price'];

      // do not select display only attributes and attributes_price_base_included is true
      $product_att_query = $db->Execute("SELECT options_id, price_prefix, options_values_price, attributes_display_only, attributes_price_base_included 
                                         FROM " . TABLE_PRODUCTS_ATTRIBUTES . " 
                                         WHERE products_id = " . (int)$products_id . " 
                                         AND attributes_display_only != '1' 
                                         AND attributes_price_base_included ='1' 
                                         AND options_values_price > 0". " 
                                         ORDER BY options_id, price_prefix, options_values_price");
      //echo $products_id . ' ';
      //print_r($product_att_query);
      //die();
      $the_options_id = 'x';
      $the_base_price = 0;
// add attributes price to price
      if ($product_check->fields['products_priced_by_attribute'] === '1' && $product_att_query->RecordCount() >= 1) {
        while (!$product_att_query->EOF) {//todo foreach
          if ( $the_options_id != $product_att_query->fields['options_id']) {
            $the_options_id = $product_att_query->fields['options_id'];
            $the_base_price += $product_att_query->fields['options_values_price'];
            //echo $product_att_query->fields['options_values_price'];
            //die();
          }
          $product_att_query->MoveNext();
        }

        $the_base_price = $products_price + $the_base_price;
      } else {
        $the_base_price = $products_price;
      }
      //echo $the_base_price;
      return $the_base_price;
  }
  
//get specials price or sale price
  function google_get_products_special_price($product_id, $product_price, $specials_price_only = false) {
    global $db;
    $product = $db->Execute("SELECT products_price, products_model, products_priced_by_attribute FROM " . TABLE_PRODUCTS . " WHERE products_id = " . (int)$product_id);

    //if ($product->RecordCount() > 0) {
//      $product_price = $product->fields['products_price'];
      //$product_price = zen_get_products_base_price($product_id);
    //} else {
      //return false;
    //}

    $specials = $db->Execute("select specials_new_products_price FROM " . TABLE_SPECIALS . " WHERE products_id = " . (int)$product_id . " AND status='1'");
    if ($specials->RecordCount() > 0) {
//      if ($product->fields['products_priced_by_attribute'] == 1) {
        $special_price = $specials->fields['specials_new_products_price'];
    } else {
      $special_price = false;
    }

    if(strpos($product->fields['products_model'], 'GIFT') === 0) {    //Never apply a salededuction to Ian Wilson's Giftvouchers
      if (zen_not_null($special_price)) {
        return $special_price;
      }
        return false;
    }

// return special price only
    if ($specials_price_only === true) {
      if (zen_not_null($special_price)) {
        return $special_price;
      }
        return false;
    }

// get sale price

// changed to use master_categories_id
//      $product_to_categories = $db->Execute("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
//      $category = $product_to_categories->fields['categories_id'];

      $product_to_categories = $db->Execute("SELECT master_categories_id FROM " . TABLE_PRODUCTS . " WHERE products_id = " . (int)$product_id);
      $category = $product_to_categories->fields['master_categories_id'];

      $sale = $db->Execute("SELECT sale_specials_condition, sale_deduction_value, sale_deduction_type 
                            FROM " . TABLE_SALEMAKER_SALES . " 
                            WHERE sale_categories_all 
                            LIKE '%," . $category . ",%' 
                            AND sale_status = '1' 
                            AND (sale_date_start <= now() OR sale_date_start = '0001-01-01') 
                            AND (sale_date_end >= now() OR sale_date_end = '0001-01-01') 
                            AND (sale_pricerange_from <= '" . $product_price . "' OR sale_pricerange_from = '0') 
                            AND (sale_pricerange_to >= '" . $product_price . "' OR sale_pricerange_to = '0')");

      if ($sale->RecordCount() < 1) {
         return $special_price;
      }

      if ($special_price) {
        $tmp_special_price = $special_price;
      } else {
        $tmp_special_price = $product_price;
      }
      switch ($sale->fields['sale_deduction_type']) {
        case 0:
          $sale_product_price = $product_price - $sale->fields['sale_deduction_value'];
          $sale_special_price = $tmp_special_price - $sale->fields['sale_deduction_value'];
          break;
        case 1:
          $sale_product_price = $product_price - (($product_price * $sale->fields['sale_deduction_value']) / 100);
          $sale_special_price = $tmp_special_price - (($tmp_special_price * $sale->fields['sale_deduction_value']) / 100);
          break;
        case 2:
          $sale_product_price = $sale->fields['sale_deduction_value'];
          $sale_special_price = $sale->fields['sale_deduction_value'];
          break;
        default:
          return $special_price;
      }

      if ($sale_product_price < 0) {
        $sale_product_price = 0;
      }

      if ($sale_special_price < 0) {
        $sale_special_price = 0;
      }

      if (!$special_price) {
        return number_format($sale_product_price, 4, '.', '');
      }

      switch($sale->fields['sale_specials_condition']){
        case 0:
          return number_format($sale_product_price, 4, '.', '');
          break;
        case 1:
          return number_format($special_price, 4, '.', '');
          break;
        case 2:
          return number_format($sale_special_price, 4, '.', '');
          break;
        default:
          return number_format($special_price, 4, '.', '');
      }
  }

// FTP FUNCTIONS //
    function ftp_file_upload($url, $login, $password, $local_file, $ftp_dir = '', $ftp_file = false, $ssl = false, $ftp_mode = FTP_ASCII) {
        $debug_ftp_file_upload = true;//verbose step-by-step processing output for dummiesº

        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . FTP_START . NL;
        if (!is_callable('ftp_connect')) {
        echo '<p class="errorText">' . FTP_FAILED . '</p>';
        return false;
      }

      if (!$ftp_file) {
          $ftp_file = basename($local_file);//todo check change from boolean to string
      }
        echo ($debug_ftp_file_upload ? __LINE__ . ': before ob_start' . NL : '');

      ob_start();
        echo ($debug_ftp_file_upload ? __LINE__ . ': after ob_start' . NL : '');
      if ($ssl) {
          $cd = @ftp_ssl_connect($url);//silenced as an error gets reported
      }
      else {
          $cd = @ftp_connect($url);//silenced to prevent debug, as any error is handled subsequently
      }
      if (!$cd) {
        $out = $this->ftp_get_error_from_ob();
        echo '<p class="errorText">' . sprintf(FTP_CONNECTION_FAILED, $url);
          if ($out !== '') {
              echo $out;
          }
          echo '</p>';
        return false;
      }
        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . sprintf(FTP_CONNECTION_OK, $url) . NL;

      $login_result = @ftp_login($cd, $login, $password);//silenced to prevent debug, as any error is handled subsequently
      if (!$login_result) {
        $out = $this->ftp_get_error_from_ob();
  //      echo FTP_LOGIN_FAILED . FTP_USERNAME . ' ' . $login . FTP_PASSWORD . ' ' . $password . NL;
          echo '<p class="errorText">' .  sprintf(FTP_LOGIN_FAILED, $url);
        if ($out !== '') {
            echo $out;
        }
          echo '</p>';
        ftp_close($cd);
        return false;
      }
//    echo FTP_LOGIN_OK . FTP_USERNAME . ' ' . $login . FTP_PASSWORD . ' ' . $password . NL;
        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . sprintf(FTP_LOGIN_OK, $url, $login) . NL;

        //this is never changed in current code...not tested
        if (($ftp_dir !== '') && !ftp_chdir($cd, $ftp_dir)) {
          echo '<p class="errorText">' .  sprintf(FTP_CANT_CHANGE_DIRECTORY, $url, $ftp_dir);
          $out = $this->ftp_get_error_from_ob();
            if ($out !== '') {
                echo $out;
            }
            echo '</p>';
          ftp_close($cd);
          return false;
        }
        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . sprintf(FTP_CURRENT_DIRECTORY, ftp_pwd($cd)) . NL;

        if (GOOGLE_PRODUCTS_PASV === 'true') {
          $pasv = true;
        } else {
          $pasv = false;
        }
        ftp_pasv($cd, $pasv);
        $upload = ftp_put($cd, $ftp_file, $local_file, $ftp_mode);
        $out = $this->ftp_get_error_from_ob();
        $raw = ftp_rawlist($cd, $ftp_file, true);
        if ($raw !== false) {//returns false if directory not found...todo
            for ($i = 0, $n = count($raw); $i < $n; $i++) {//todo foreach IDE
                $out .= $raw[$i] . '<br>';
            }
        }
        if (!$upload) {
          echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . FTP_UPLOAD_FAILED . NL;
          if (isset($raw[0])) {
              echo $raw[0] . NL;
          }
          echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . $out . NL;
          ftp_close($cd);
          return false;
        }

        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . FTP_UPLOAD_SUCCESS . NL;
        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . $raw[0] . NL;
        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . $out . NL;
        ftp_close($cd);
        return true;
    }

    function ftp_get_error_from_ob() {
        $out = ob_get_clean();
        if ($out !== false) {//false if buffering not active
            $out = str_replace(['\\', '<!--error-->', '<br>', '<br />', "\n", 'in <b>'], ['/', '', '', '', '', ''], $out);
        } else {
            $out = 'Output Buffer was not active';
        }
      if (strpos($out, DIR_FS_CATALOG) !== false) {
        $out = substr($out, 0, strpos($out, DIR_FS_CATALOG));
      }
      return $out;
    }

    function microtime_float() {
       [$usec, $sec] = explode(" ", microtime());
       return ((float)$usec + (float)$sec);
    }

//https://alexwebdevelop.com/monitor-script-memory-usage/
    function print_mem()
    {
        /* Currently used memory */
        $mem_usage = memory_get_usage();

        /* Peak memory usage */
        $mem_peak = memory_get_peak_usage();

        echo 'The script is now using: <strong>' . round($mem_usage / 1024) . 'KB</strong> of memory.<br>';
        echo 'Peak usage: <strong>' . round($mem_peak / 1024) . 'KB</strong> of memory.<br><br>';
    }


}
