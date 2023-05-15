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
class google_mc
{
    public $shipping_rates_custom;
    protected $debug;
    public $debug_log_file;
    public $skip_product = false;

    public function __construct()
    {
        $this->debug = (GOOGLE_PRODUCTS_DEBUG !== '0');
        $this->debug_log_file = DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . 'GMC' . date('-Ymd') . '.log';
        if (file_exists($this->debug_log_file)) {
            //truncate file on each run
            file_put_contents($this->debug_log_file, '');
        }

        if (GOOGLE_PRODUCTS_SHIPPING_METHOD === 'custom') {
            $custom_datafile = DIR_FS_CATALOG . 'includes/extra_datafiles/plugin_google_mc.php';
            if (file_exists($custom_datafile)) {
                require($custom_datafile);
                if (!empty($google_mc_custom_shipping) && is_array($google_mc_custom_shipping)) {
                    $this->shipping_rates_custom = $google_mc_custom_shipping;
                } else {
                    error_log('GMC: no array $google_mc_custom_shipping found in "' . $custom_datafile . '"');
                }
            } else {
                error_log('GMC: custom shipping file not found: "' . $custom_datafile . '"');
            }
        }
    }

    protected $image_files;
    //public array $additional_images_array;//stores the additional images found, per-product, for re-use if that product id comes up again...when does that happen? DISABLED
    //public array $image_files; //stores the images per-directory to prevent re-scanning if that directory has already been scanned

    /**
     * @param $product_main_image
     * @param $product_id
     * @return array
     */
    private function additional_images($product_main_image, $product_id): array
    {
        $debug_additional_images = false;//verbose step-by-step processing output for dummies

        if ($debug_additional_images) {
            echo '<hr>function additional_images' . NL . __LINE__ . ' $product_main_image=' . $product_main_image . ' | $product_id = ' . $product_id . NL;
        }
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
            //steve strrpos may return false: invalid offset for substr
            //$products_image_directory = str_replace($product_main_image, '', substr($product_main_image, strrpos($product_main_image, '/')));
            $products_image_directory = str_replace($product_main_image, '', substr($product_main_image, (strrpos($product_main_image, '/') === false ? 0 : strrpos($product_main_image, '/'))));

            if ($products_image_directory !== '') {
                $products_image_directory = DIR_WS_IMAGES . str_replace($products_image_directory, '', $product_main_image) . '/';
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
                    if (($file !== $product_main_image) && (preg_match('/' . $product_main_image_filename . '/i', $file) === 1)) {
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

    // writes out the code into the feed file: UNUSED!!

    /**
     * @param $mode
     * @param string $output
     * @param string $products_id
     * @return void
     */
    private function google_base_fwrite($mode, string $output = '', string $products_id = '')
    { // added products id for debugging
        global $outfile;
        $output = implode("\n", $output); //todo change to PHP_EOL?
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

    /**
     * @return string
     */
    public function google_base_version(): string
    {
        return trim(GOOGLE_PRODUCTS_VERSION);
    }

    // trims the value of each element of an array

    /**
     * @param $x
     * @return array|string
     */
    private function trim_array($x)
    {
        if (is_array($x)) {
            return array_map('trim_array', $x);
        }
        return trim($x);
    }

    // determines if the feed should be generated

    /**
     * @param $feed_parameter
     * @return string
     */
    public function get_feed($feed_parameter): string
    {
        switch ($feed_parameter) {
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

    /**
     * @param $upload_parameter
     * @return string
     */
    public function get_upload($upload_parameter): string
    {
        switch ($upload_parameter) {
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

    /**
     * @param $type_parameter
     * @return string
     */
    public function get_type($type_parameter): string
    {
        switch ($type_parameter) {
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


    /**
     * @param $products_id
     * @return array[]
     */
    public function google_base_get_category($products_id): array
    {
        global $db;

        // get the master_categories_id
        $master_categories_id = $db->Execute('SELECT master_categories_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . (int)$products_id . ' LIMIT 1');
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

    /** builds the category tree
     * @param string $id_parent
     * @param string $cPath
     * @param string $cName
     * @param array $cats
     * @return array|mixed
     */
    private function google_base_category_tree(string $id_parent = '0', string $cPath = '', string $cName = '', array $cats = [])
    {
        global $db, $languages;
        $cat = $db->Execute(
            'SELECT c.categories_id, c.parent_id, cd.categories_name
                           FROM ' . TABLE_CATEGORIES . ' c
                             LEFT JOIN ' . TABLE_CATEGORIES_DESCRIPTION . ' cd on c.categories_id = cd.categories_id
                           WHERE c.parent_id = ' . (int)$id_parent . '
                           AND cd.language_id=' . (int)$languages->fields['languages_id'] . '
                           AND c.categories_status= 1',
            '',
            false,
            150
        );
        //todo what's this
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

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_availability($dom, $item, $product)
    {
        switch (true) {
            case (STOCK_CHECK === 'false'):
            case ($product['products_quantity'] > 0):
                $item->appendChild($dom->createElement('g:availability', 'in_stock'));
                break;

            case ($product['products_quantity'] <= 0 && $product['products_date_available'] > date('Y-m-d H:i:s')):
                $item->appendChild($dom->createElement('g:availability', 'backorder'));
                $item->appendChild($dom->createElement('g:availability_date', date('Y-m-d\TH:i:sO')));
                break;

            //past date/null/not set both come under this clause
            case ($product['products_quantity'] <= 0 && $product['products_date_available'] < date('Y-m-d H:i:s')):
                $item->appendChild($dom->createElement('g:availability', 'backorder'));
                $bisDate = date_create();
                date_add($bisDate, date_interval_create_from_date_string(GOOGLE_PRODUCTS_BIS_DAYS . ' days'));
                $item->appendChild($dom->createElement('g:availability_date', date_format($bisDate, 'Y-m-d')));
                break;

            default:
                //if in doubt, in-stock!
                $item->appendChild($dom->createElement('g:availability', 'in_stock'));
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_brand($dom, $item, $product)
    {
        if ($product['manufacturers_name'] === '') {
            $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": manufacturers_name field empty');
        } else {
            $manufacturers_name = $this->google_base_xml_sanitizer($product['manufacturers_name']);
            $manufacturers_name_length = strlen($manufacturers_name) + GOOGLE_PRODUCTS_CDATA_LENGTH;
            if (strlen($manufacturers_name) > GOOGLE_PRODUCTS_MAX_CHARS_BRAND) {
                $this->debug('ERROR: brand field "<![CDATA[' . $manufacturers_name . ']]>" length (' . $manufacturers_name_length . ') exceeds permitted length (' . GOOGLE_PRODUCTS_MAX_CHARS_BRAND . ')');
                $this->skip_product = true;
            } else {
                $manufacturers_name = $dom->createElement('g:brand');
                $manufacturers_name->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($product['manufacturers_name'])));
                $item->appendChild($manufacturers_name);
            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_is_bundle($dom, $item, $product)
    {
        if (GOOGLE_PRODUCTS_BUNDLE === 'true') {
            //custom code here

            //torvista/lat9 "Product Kit"
            global $kitObserver;
            if (isset($kitObserver) && is_object($kitObserver)) {
                if ($kitObserver->kit->kit_product_type === (int)$product['products_type']) {
                    $item->appendChild($dom->createElement('g:is_bundle', 'yes'));
                }
            }
            //eof torvista/lat9 "Product Kit"
        }
        return $item;
    }

    /** 12/17 common attributes used for all products
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_common_attributes($dom, $item, $product)
    {
        $item = $this->add_id($dom, $item, $product);
        $item = $this->add_brand($dom, $item, $product);
        //$item = $this->add_mpn($dom, $item, $product);//attribute dependant CUSTOM ***************
        //$item = $this->add_gtin($dom, $item, $product);//attribute dependant CUSTOM **************
        $item = $this->add_google_product_category($dom, $item, $product);
        //$item = $this->add_title($dom, $item, $product);
        //$item = $this->add_price($dom, $item, $product);//attribute dependant***************
        $item = $this->add_availability($dom, $item, $product);//attribute dependant CUSTOM ***********
        //$item = $this->add_shipping_weight($dom, $item, $product);//attribute dependant ************
        $item = $this->add_shipping($dom, $item, $product);
        $item = $this->add_ships_from_country($dom, $item);
        $item = $this->add_condition($dom, $item, $product);//attribute dependant CUSTOM *****************
        $item = $this->add_product_type($dom, $item, $product);
        $item = $this->add_image_links($dom, $item, $product);
        $item = $this->add_expiration_date($dom, $item, $product);
        $item = $this->add_link($dom, $item, $product);
        $item = $this->add_description($dom, $item, $product);
        return $item;
    }

    /** Optional for new products
     * Required for used refurbished products
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_condition($dom, $item, $product)
    {
        if (GOOGLE_PRODUCTS_PRODUCT_CONDITION === 'true') {
            $conditions_allowed = ['new', 'refurbished', 'used'];
            if (GOOGLE_PRODUCTS_PRODUCT_CONDITION_FIELD !== '' && !empty($product['products_condition']) && in_array($product['products_condition'], $conditions_allowed)) {
                $item->appendChild($dom->createElement('g:condition', $product['products_condition']));
            } elseif (in_array(GOOGLE_PRODUCTS_CONDITION, $conditions_allowed)) {
                $item->appendChild($dom->createElement('g:condition', GOOGLE_PRODUCTS_CONDITION));
            } else {
                $item->appendChild($dom->createElement('g:condition', 'new'));
            }
        }
        return $item;
    }

    /** used only for products with attributes where an option name may match one of the permitted attribute types:
     * color [color], pattern [pattern], material [material], age group [age_group], gender [gender], size[size].
     * @param $dom
     * @param $item
     * @param $product
     * @param $attribute
     * @return mixed
     */
    public function add_variant_attribute($dom, $item, $product, $attribute = false) {
        // add any equivalent option name (spelling variation, language) that may match
        $age_group_array = ['age group'];
        $color_array = ['color', 'colour', 'Oil Type', 'Tipo de Aceite', 'Finish', 'Acabado', 'Digit Colour', 'Color del Dígito', 'Lens Colour', 'Color Lentes'];
        $gender_array = ['gender', 'género', 'sex', 'sexo'];
        $material_array = ['material'];
        $pattern_array = ['pattern', 'dibujo'];
        $size_array = ['size', 'tamaño'];

        if ($attribute) {
            global $db, $languages;;
            $sql = 'SELECT products_options_name
                    FROM ' . TABLE_PRODUCTS_OPTIONS . ' popt
                    WHERE products_options_id = ' . $attribute['options_id'] . '
                    AND popt.language_id = ' . (int)$_SESSION['languages_id'] . ' LIMIT 1';
            $result = $db->Execute($sql);
            $option_name = strtolower($result->fields['products_options_name']);

            switch (true) {
                case (in_array($option_name, $age_group_array)):
                    $attribute_name = 'age_group';
                    break;
                case (in_array($option_name, $color_array)):
                    $attribute_name = 'color';
                    break;
                case (in_array($option_name, $gender_array)):
                    $attribute_name = 'gender';
                    break;
                case (in_array($option_name, $material_array)):
                    $attribute_name = 'material';
                    break;
                case (in_array($option_name, $pattern_array)):
                    $attribute_name = 'pattern';
                    break;
                case (in_array($option_name, $size_array)):
                    $attribute_name = 'size';
                    break;
                default:
                    $attribute_name = '';
            }
            if ($attribute_name !== '') {
                //note returned value is english
                $sql = 'SELECT products_options_values_name
                        FROM ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' pov
                        WHERE pov.products_options_values_id = ' . $attribute['options_values_id'] . '
                        AND pov.language_id = ' . (int)$languages->catalog_languages['en']['id'] . ' LIMIT 1';
                $result = $db->Execute($sql);
                $option_value_name = $result->fields['products_options_values_name'];

                if ($option_value_name !== '') {
                    $attribute = $dom->createElement('g:' . $attribute_name);
                    $value = $this->google_base_xml_sanitizer($option_value_name);
                    $attribute->appendChild($dom->createCDATASection($value));
                    $item->appendChild($attribute);
                }
            }
        }
        return $item;
    }

    /** Required
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_description($dom, $item, $product)
    {
        $products_description = $product['products_description'];
        //Numinix Product Fields additional description
        if (GOOGLE_PRODUCTS_ASA_DESCRIPTION_2 === 'true') {
            $products_description .= $product['products_description2'];
        }
        if ($products_description === '') {
            $this->debug('ERROR: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": description field empty, PRODUCT SKIPPED');
            $this->skip_product = true;
        } else {
//torvista: my site only, using boilerplate text!
            if (function_exists('mv_get_boilerplate')) {
                $products_description = mv_get_boilerplate($products_description, (int)$product['products_id']);
            }
//eof boilerplate text
//unwrap text
            $products_description = str_replace(["\r", "\n"], '', $products_description);
            $products_description = $this->google_base_xml_sanitizer($products_description);
            $length = strlen($products_description) + GOOGLE_PRODUCTS_CDATA_LENGTH;
            if ($length > GOOGLE_PRODUCTS_MAX_CHARS_DESCRIPTION) {
                $products_description = zen_trunc_string($products_description, GOOGLE_PRODUCTS_MAX_CHARS_DESCRIPTION - GOOGLE_PRODUCTS_CDATA_LENGTH, true);
               // $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": description field exceeded permitted length (' . GOOGLE_PRODUCTS_MAX_CHARS_DESCRIPTION . '), truncated.');
            }
            $description = $dom->createElement('g:description');
            $description->appendChild($dom->createCDATASection($products_description));
            $item->appendChild($description);
        }
        return $item;
    }

    /** Optional default (when attribute is not set) is 30 days. Use this to set an expiry date less than 30
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_expiration_date($dom, $item, $product)
    {
        if (GOOGLE_PRODUCTS_EXPIRATION_DAYS !== '' && (int)GOOGLE_PRODUCTS_EXPIRATION_DAYS <= 29) {
            $item->appendChild($dom->createElement('g:expiration_date', $this->google_base_expiration_date($product['base_date'])));
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_google_product_category($dom, $item, $product)
    {
        //is custom GPC field defined?
        if (GOOGLE_PRODUCTS_PRODUCT_GPC_FIELD !== '' && !empty($product[GOOGLE_PRODUCTS_PRODUCT_GPC_FIELD])) {
            $gpc = $product[GOOGLE_PRODUCTS_PRODUCT_GPC_FIELD];
        } else {
            $gpc = GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY;
        }
        if (empty($gpc)) {
            $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": google products category not defined');
        } else {
            $gpc = $this->google_base_xml_sanitizer($gpc);
            $gpc_feed = $dom->createElement('g:google_product_category');
            $gpc_feed->appendChild($dom->createCDATASection($gpc));
            $item->appendChild($gpc_feed);
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @param $attribute
     * @return mixed
     */
    public function add_gtin($dom, $item, $product, $attribute = false)
    {
        $gtin = '';
        $variant_info_error = '';
        //this is a variant
        if ($attribute !== false) {
            //a variant should have its own gtin, but this is not a core function and best added to whatever plugin is handling the stock
            global $db, $sniffer;
            switch (GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN) {
                case 'numinixproductvariants': //handled in legacy code...to be ported here
                case 'stockbyattributes'://handled in legacy code...to be ported here
                case 'posm': //ean is not a core feature of posm either, so this is custom code on a plugin!
                    if ($sniffer->field_exists('products_options_stock', 'pos_ean')) {
                        $hash = generate_pos_option_hash($product['products_id'], [$attribute['options_id'] => $attribute['options_values_id']]);
                        $posm_record = $db->Execute('SELECT pos_mpn, pos_ean FROM ' . TABLE_PRODUCTS_OPTIONS_STOCK . ' WHERE products_id = ' . (int)$product['products_id'] . ' AND pos_hash = "' . $hash . '" LIMIT 1', false, false, 0, true);
                        //$posm_record->fields (array):
                        //Array
                        //(
                        //    [pos_id] => 3419
                        //    [products_id] => 16
                        //    [pos_name_id] => 2
                        //    [products_quantity] => 3
                        //    [pos_hash] => 79f51ae2e843cc3692d459aa60c392df
                        //    [pos_model] => HT-GPX-A01
                        //    [pos_mpn] => GPX-A01
                        //    [pos_ean] =>
                        //    [pos_date] => 0001-01-01
                        //    [last_modified] => 2022-08-22 20:07:17
                        //)
                        $gtin = empty($posm_record->fields['pos_ean']) ? '' : $posm_record->fields['pos_ean'];
                        $variant_info_error = empty($gtin) ? ' (for pos_mpn=' . $posm_record->fields['pos_mpn'] . ') ' : '';
                    }
                default : //none: no variant-stock handler...so suggest adding a field to the products_attributes table
            }
            //echo '$attribute[\'options_id\']=' . $attribute['options_id'] . ', $attribute[\'options_values_id\']=' . $attribute['options_values_id'] . ', $gtin=' . $gtin . '<br>';
        }

        // custom GTIN field defined?
        if ($gtin === '') {
            if (GOOGLE_PRODUCTS_PRODUCT_GTIN_FIELD !== '') {
                if (empty($product[GOOGLE_PRODUCTS_PRODUCT_GTIN_FIELD])) {
                    $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": GTIN field empty' . $variant_info_error);
                } else {
                    $gtin = $product[GOOGLE_PRODUCTS_PRODUCT_GTIN_FIELD];
                }
            }
        }
        if ($gtin !== '') {
            $gtin = $this->google_base_xml_sanitizer($gtin);
            $gtin_length = strlen($gtin);
            if ($gtin_length > GOOGLE_PRODUCTS_MAX_CHARS_GTIN) {
                $this->debug('ERROR: GTIN field "' . $gtin . '" length (' . $gtin_length . ') exceeds permitted length (' . GOOGLE_PRODUCTS_MAX_CHARS_GTIN . '), PRODUCT SKIPPED');
                $this->skip_product = true;
            } else {
                $gtin_feed = $dom->createElement('g:gtin');
                $gtin_feed->appendChild($dom->createCDATASection($gtin));
                $item->appendChild($gtin_feed);
            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_image_links($dom, $item, $product)
    {
        if ($product['products_image'] === '') {
            $this->debug('ERROR: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": image link field empty, PRODUCT SKIPPED');
            $this->skip_product = true;
        } elseif (strlen($this->google_base_image_url($product['products_image'])) > GOOGLE_PRODUCTS_MAX_CHARS_IMAGE_LINK) {
            $this->debug('ERROR: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": image_link exceeds permitted length . (' . GOOGLE_PRODUCTS_MAX_CHARS_IMAGE_LINK . '), PRODUCT SKIPPED');
            $this->skip_product = true;
        } elseif (!file_exists(DIR_WS_IMAGES . $product['products_image']) || (str_contains($product['products_image'], '&'))) {
            $this->debug('ERROR: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": image "' . DIR_WS_IMAGES . $product['products_image'] . '" not found/invalid name, PRODUCT SKIPPED');
            $this->skip_product = true;
        } else {
            $item->appendChild($dom->createElement('g:image_link', $this->google_base_image_url($product['products_image'])));

            // add additional images
            $additional_images = $this->additional_images($product['products_image'], $product['products_id']);
            if (is_array($additional_images) && count($additional_images) > 0) {
                foreach ($additional_images as $additional_image) {
                    $item->appendChild($dom->createElement('g:additional_image_link', $additional_image));
                }
            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_item_group_id($dom, $item, $product)
    {
        /* using model
        $id = zen_get_products_model($product['products_id']);
        $item_group_id = $dom->createElement('g:add_item_group_id');
        $item_group_id->appendChild($dom->createCDATASection($id));
        $item->appendChild($item_group_id);
        return $item;*/
        return $this->add_id($dom, $item, $product, true);

    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @param $item_group_id : the "normal" id is used as the item_group_id for a variant, so reuse the same code
     * @return mixed
     */
    public function add_id($dom, $item, $product, $item_group_id = false)
    {
        $id = '';
        switch (GOOGLE_PRODUCTS_OFFER_ID) {
            case 'EAN': //custom field
                if (!empty($product['products_ean'])) {
                    $id = $product['products_ean'];
                } else {
                    $this->skip_product = true;
                }
                break;
            case 'ISBN': //custom field
                if (!empty($product['products_isbn'])) {
                    $id = $product['products_isbn'];
                } else {
                    $this->skip_product = true;
                }
                break;
            case 'UPC': //custom field
                if (!empty($product['products_upc'])) {
                    $id = $product['products_upc'];
                } else {
                    $this->skip_product = true;
                }
                break;
            case 'model':
                if (!empty($product['products_model'])) {
                    $id = '<![CDATA[' . $this->google_base_xml_sanitizer($product['products_model']) . ']]>';
                } else {
                    $this->skip_product = true;
                }
                break;
            default: //id
                $id = $product['products_id'];
        }
        if ($this->skip_product) {
            $this->debug('ERROR: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": skipped due to missing the unique Product Identifier (as chosen in Admin): "' . GOOGLE_PRODUCTS_OFFER_ID . '"');
        } else {
            $id_length = strlen($id);
            if ($id_length > GOOGLE_PRODUCTS_MAX_CHARS_ID) {
                $this->debug('ERROR: id field "' . $id . '" length (' . $id_length . ') exceeds permitted length (' . GOOGLE_PRODUCTS_MAX_CHARS_ID . ') PRODUCT SKIPPED');
                $this->skip_product = true;
            } else {
                if ($item_group_id) {
                    $item->appendChild($dom->createElement('g:item_group_id', $id));
                } else {
                    $item->appendChild($dom->createElement('g:id', $id));
                }

            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_link($dom, $item, $product)
    {
        switch (GOOGLE_PRODUCTS_LINK_TYPE) {
            /*case ('default'):
                $link = zen_href_link(zen_get_info_page($product['products_id']), 'cPath=' .  zen_get_generated_category_path_rev($product['master_categories_id']) . '&products_id=' . $product['products_id']);
                break;*/
            case ('native_no_cpath'):
                //https://www.motorvista.es.local/tienda/index.php?main_page=product_info&amp;products_id=7834
                $link = zen_href_link(FILENAME_DEFAULT . '.php', 'main_page=' . zen_get_info_page($product['products_id']) . '&products_id=' . $product['products_id'], 'NONSSL', '', '', true);
                break;
            case ('native'):
                //https://www.motorvista.es.local/tienda/index.php?main_page=product_info&amp;cPath=1_5536_4569&amp;products_id=7834
                $link = zen_href_link(
                    FILENAME_DEFAULT . '.php',
                    'main_page=' . zen_get_info_page($product['products_id']) . '&cPath=' . zen_get_generated_category_path_rev($product['master_categories_id']) . '&products_id=' . $product['products_id'],
                    'NONSSL',
                    '',
                    '',
                    true
                );
                break;
            case ('magic_seo'):
                include(DIR_WS_INCLUDES . 'modules/msu_ao_2.php');
                break;
            default:// as per shopfront: vanilla dynamic URL/friendly url if 3rd party plugin in use
                // https://www.motorvista.es.local/tienda/index.php?main_page=product_info&amp;cPath=1_5536_4569&amp;products_id=7834
                // https://www.motorvista.es.local/tienda/manufacturers/cobrra/chain-oilers/nemo-2-chain-oiler-kit/
                $link = zen_href_link(zen_get_info_page($product['products_id']), 'cPath=' . zen_get_generated_category_path_rev($product['master_categories_id']) . '&products_id=' . $product['products_id']);
        }
        $item->appendChild($dom->createElement('g:link', $link));
        return $item;
    }

    /** MANUFACTURERS product number MUST be that...NOT the stores version or equivalent: if it is not identical, better to not include it at all.
     * @param $dom
     * @param $item
     * @param $product
     * @param $attribute
     * @return mixed
     */
    public function add_mpn($dom, $item, $product, $attribute = false)
    {
        $mpn = '';
        //this is a variant
        if ($attribute !== false) {
            //a variant should have its own mpn, but this is not a core function and best added to whatever plugin is handling the stock
            global $db, $sniffer;
            switch (GOOGLE_PRODUCTS_SWITCH_STOCK_PLUGIN) {
                case 'numinixproductvariants': // handled in legacy code...to be ported here
                case 'stockbyattributes': // handled in legacy code...to be ported here
                case 'posm': // mpn is not a core feature of posm either, so this is custom code on a plugin!
                    if ($sniffer->field_exists('products_options_stock', 'pos_mpn')) {
                        $hash = generate_pos_option_hash($product['products_id'], [$attribute['options_id'] => $attribute['options_values_id']]);
                        $posm_record = $db->Execute('SELECT pos_mpn FROM ' . TABLE_PRODUCTS_OPTIONS_STOCK . ' WHERE products_id = ' . (int)$product['products_id'] . ' AND pos_hash = "' . $hash . '" LIMIT 1', false, false, 0, true);
                        //$posm_record->fields (array):
                        //Array
                        //(
                        //    [pos_id] => 3419
                        //    [products_id] => 16
                        //    [pos_name_id] => 2
                        //    [products_quantity] => 3
                        //    [pos_hash] => 79f51ae2e843cc3692d459aa60c392df
                        //    [pos_model] => HT-GPX-A01
                        //    [pos_mpn] => GPX-A01
                        //    [pos_ean] =>
                        //    [pos_date] => 0001-01-01
                        //    [last_modified] => 2022-08-22 20:07:17
                        //)
                        $mpn = empty($posm_record->fields['pos_mpn']) ? '' : $posm_record->fields['pos_mpn'];
                    }
                default : //none: no variant-stock handler...so suggest adding a field to the products_attributes table
            }
        }

        //is custom MPN field defined?
        if ($mpn === '') {
            if (GOOGLE_PRODUCTS_PRODUCT_MPN_FIELD !== '') {
                if (empty($product[GOOGLE_PRODUCTS_PRODUCT_MPN_FIELD])) {
                    $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": mpn field empty');
                } else {
                    $mpn = $product[GOOGLE_PRODUCTS_PRODUCT_MPN_FIELD];
                }
            }
        }

        if ($mpn !== '') {
            $mpn = $this->google_base_xml_sanitizer($mpn);
            $mpn_length = strlen($mpn) + GOOGLE_PRODUCTS_CDATA_LENGTH;
            if ($mpn_length > GOOGLE_PRODUCTS_MAX_CHARS_MPN) {
                $this->debug('ERROR: mpn field "<![CDATA[' . $mpn . ']]>" length (' . $mpn_length . ') exceeds permitted length (' . GOOGLE_PRODUCTS_MAX_CHARS_MPN . ')');
                $this->skip_product = true;
            } else {
                $mpn_feed = $dom->createElement('g:mpn');
                $mpn_feed->appendChild($dom->createCDATASection($mpn));
                $item->appendChild($mpn_feed);
            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @param $attribute
     * @return mixed
     */
    public function add_price($dom, $item, $product, $attribute = false)
    {
        global $currencies;

        //custom code addition
        if (GOOGLE_PRODUCTS_MAP_PRICING === 'true' && $product['map_enabled'] === '1') {
            //extra fields available:
            // $additional_attributes .= ', p.map_price, p.map_enabled';
            $price = $product['map_price'];
        } else {
            $price = $this->google_get_products_actual_price($product['products_id']);
        }

        // for a variant. A world of pain awaits for discounts...so here is only the base price + option price
        if ($attribute) {
            if ($attribute['price_prefix'] === '-') {
                $price -= $attribute['options_values_price'];
            } else {
                $price += $attribute['options_values_price'];
            }
        }

        if ($price === '') {
            $this->debug('ERROR: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": price field empty/zero PRODUCT SKIPPED');
            $this->skip_product = true;
        } else {
            //38 = Canada, 223 = USA
            if (GOOGLE_PRODUCTS_TAX_COUNTRY === 'US' || GOOGLE_PRODUCTS_TAX_COUNTRY === 'CA') {
                // modify price to match defined currency
                $price = $currencies->value($price, true, GOOGLE_PRODUCTS_CURRENCY, $currencies->get_value(GOOGLE_PRODUCTS_CURRENCY));
                $item->appendChild($dom->createElement('g:price', number_format($price, 2, '.', '') . ' ' . GOOGLE_PRODUCTS_CURRENCY));
                //do not add tax: need extra attribute for tax in USA and Canada
                $item = $this->add_tax($dom, $item, $product);//ONLY for country US, to override the global setting in GMC for specific products
            } else {
                //rest of the world
                $tax_rate = zen_get_tax_rate($product['products_tax_class_id']);
                // add the tax if DISPLAY_PRICE_WITH_TAX is set to true in the Zen Cart admin
                $price = zen_add_tax($price, $tax_rate);
                // modify price to match defined currency
                $price = $currencies->value($price, true, GOOGLE_PRODUCTS_CURRENCY, $currencies->get_value(GOOGLE_PRODUCTS_CURRENCY));
                $item->appendChild($dom->createElement('g:price', number_format($price, 2, '.', '') . ' ' . GOOGLE_PRODUCTS_CURRENCY));
            }
        }
        return $item;
    }

    /** Optional
     * Use the product type [product_type] attribute to include your own product categorisation system in your product data.
     * Unlike the Google product category [google_product_category] attribute, which uses a collection of predefined categories,
     * you choose which value to include for product type.
     * The values that you submit can be used to organise the bidding and reporting in your Google Ads Shopping campaign.
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_product_type($dom, $item, $product)
    {
        if (GOOGLE_PRODUCTS_PRODUCT_TYPE === 'top' || GOOGLE_PRODUCTS_PRODUCT_TYPE === 'bottom' || GOOGLE_PRODUCTS_PRODUCT_TYPE === 'full') {
            [$categories_list, $cPath] = $this->google_base_get_category($product['products_id']); //returns breadcrumb path and cPath to master category
        } else {
            $categories_list = [];
        }
        $product_type = '';
        switch (GOOGLE_PRODUCTS_PRODUCT_TYPE) {
            case 'default':
                $product_type = GOOGLE_PRODUCTS_DEFAULT_PRODUCT_TYPE;
                break;
            case 'top':
                $product_type = $categories_list[0];
                break;
            case 'bottom':
                $product_type = $categories_list[array_key_last($categories_list)];
                break;
            case 'full':
                $product_type = implode(' > ', $categories_list);
                break;
            case 'GPC':
                if (GOOGLE_PRODUCTS_PRODUCT_GPC_FIELD !== '') {
                    $product_type = zen_products_lookup($product['products_id'], GOOGLE_PRODUCTS_PRODUCT_GPC_FIELD);
                    break;
                } else {
                    $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": GPC field empty');
                }
            default: //also 'none'
        }
        if ($product_type !== '') {
            $product_type_feed = $dom->createElement('g:product_type');
            $product_type_feed->appendChild($dom->createCDATASection(htmlentities($this->google_base_xml_sanitizer($product_type))));
            $item->appendChild($product_type_feed);
        }
        return $item;
    }

    /** todo needs more work/investigation by those who do not use the custom option
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_shipping($dom, $item, $product)
    {
        if (GOOGLE_PRODUCTS_SHIPPING_METHOD === 'custom') {
            return $this->add_shipping_attributes_custom($dom, $item);
        }
        //original
        global $percategory, $freerules, $price;
        if (GOOGLE_PRODUCTS_SHIPPING_METHOD !== '' && GOOGLE_PRODUCTS_SHIPPING_METHOD !== 'none') {
            $shipping_rate = $this->shipping_rate(GOOGLE_PRODUCTS_SHIPPING_METHOD, $percategory, $freerules, GOOGLE_PRODUCTS_RATE_ZONE, $product['products_weight'], $price, $product['products_id']);

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
                $shipping->appendChild($dom->createElement('g:price', (string)$shipping_rate));
                $item->appendChild($shipping);
            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @return mixed|void
     */
    public function add_shipping_attributes_custom($dom, $item)
    {
        //    'ES' => [
        //        'delivery_area'=>
        //            ['region'=>'all'],
        //            //['postal_code'=>''],
        //            //['location_id'=>''],
        //            //['location_group_name'=>''],
        //        'service'=>'SEUR 24',
        //        'price'=>5,
        //        'handling_time'=>
        //            ['min_handling_time'=> 1, 'max_handling_time'=>2],
        //        'transit_time'=>
        //            ['min_transit_time'=> 3, 'max_transit_time'=>4]
        //    ],
        //error_log('GMC:' . print_r($shipping_rate, true));
        foreach ($this->shipping_rates_custom as $country => $rate) {
            $shipping = $dom->createElement('g:shipping');

            //country required
            $shipping->appendChild($dom->createElement('g:country', $country));

            //delivery area optional
            if (!empty($rate['delivery_area'])) {//must only have one element enable
                $keys = array_keys($rate['delivery_area']);
                //error_log('GMC:' . print_r($delivery_keys, true));
                $shipping->appendChild($dom->createElement('g:' . $keys[0], $rate['delivery_area'][$keys[0]]));
            }

            //service optional
            if (!empty($rate['service'])) {
                $shipping->appendChild($dom->createElement('g:service', $rate['service']));
            }

            //price required
            $shipping->appendChild($dom->createElement('g:price', (float)$rate['price'] . ' ' . GOOGLE_PRODUCTS_CURRENCY));

            //handling time optional
            if (!empty($rate['handling_time']) && count($rate['handling_time']) === 2) {
                $shipping->appendChild($dom->createElement('g:min_handling_time', $rate['handling_time']['min_handling_time']));
                $shipping->appendChild($dom->createElement('g:max_handling_time', $rate['handling_time']['max_handling_time']));
            }

            //transit time optional
            if (!empty($rate['transit_time']) && count($rate['transit_time']) === 2) {
                $shipping->appendChild($dom->createElement('g:min_transit_time', $rate['transit_time']['min_transit_time']));
                $shipping->appendChild($dom->createElement('g:max_transit_time', $rate['transit_time']['max_transit_time']));
            }
            $item->appendChild($shipping);
            return $item;
        }
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @param $attribute
     * @return mixed
     */
    public function add_shipping_weight($dom, $item, $product, $attribute = false)
    {
        if (GOOGLE_PRODUCTS_WEIGHT === 'true') {
            if ($product['products_weight'] === '') {
                $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": weight field empty');
            } else {
                $weight = $product['products_weight'];
                if ($attribute) {
                    if ($attribute['products_attributes_weight_prefix'] === '-') {
                        $weight -= $attribute['products_attributes_weight'];
                    } else {
                        $weight += $attribute['products_attributes_weight'];
                    }
                }
                if ($weight < 0) {
                    $this->debug('NOTICE: #' . $product['products_id'] . ' - ' . $product['products_model'] . ' - "' . $product['products_name'] . '": add_shipping_weight field less than zero!' . ($attribute ? '(products_attributes_id #' . $attribute['products_attributes_id'] . ')' : ''));
                } else {
                    $item->appendChild($dom->createElement('g:shipping_weight', $weight . ' ' . str_replace(['pounds', 'kilograms'], ['lb', 'kg'], GOOGLE_PRODUCTS_UNITS)));
                }
            }
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @return mixed
     */
    public function add_ships_from_country($dom, $item)
    {
        $country_iso_2 = zen_get_countries((int)STORE_COUNTRY, true);
        if (($country_iso_2['countries_iso_code_2']) !== '') {
            $item->appendChild($dom->createElement('g:ships_from_country', $country_iso_2['countries_iso_code_2']));
        }
        return $item;
    }

    /** todo needs more work for feeds targeting the US
     * @param $dom
     * @param $item
     * @param $product
     * @return mixed
     */
    public function add_tax($dom, $item, $product)
    {
        $tax_rate = (string)zen_get_tax_rate($product['products_tax_class_id']); // returns a string

        if (GOOGLE_PRODUCTS_TAX_DISPLAY === 'true' && (GOOGLE_PRODUCTS_TAX_COUNTRY === 'US' || GOOGLE_PRODUCTS_TAX_COUNTRY === 'CA') && $tax_rate != '') {
            $tax = $dom->createElement('g:tax');
            $tax->appendChild($dom->createElement('g:country', GOOGLE_PRODUCTS_TAX_COUNTRY));
            if (GOOGLE_PRODUCTS_TAX_REGION !== '') {
                $tax->appendChild($dom->createElement('g:region', GOOGLE_PRODUCTS_TAX_REGION));
            }
            if (GOOGLE_PRODUCTS_TAX_SHIPPING === 'y') {
                $tax->appendChild($dom->createElement('g:tax_ship', 'yes'));
            }
            $tax->appendChild($dom->createElement('g:rate', $tax_rate));
            $item->appendChild($tax);
        }
        return $item;
    }

    /**
     * @param $dom
     * @param $item
     * @param $product
     * @param $attribute
     * @return mixed
     */
    public function add_title($dom, $item, $product, $attribute = false)
    {
        $title_suffix = '';
        if ($attribute) {
            global $db;
            $sql = 'SELECT products_options_name
                    FROM ' . TABLE_PRODUCTS_OPTIONS . ' popt
                    WHERE products_options_id = ' . $attribute['options_id'] . '
                    AND popt.language_id = ' . (int)$_SESSION['languages_id'] . ' LIMIT 1';
            $result = $db->Execute($sql);
            $option_name = $result->fields['products_options_name'];
            $sql = 'SELECT products_options_values_name
                    FROM ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' pov
                    WHERE pov.products_options_values_id = ' . $attribute['options_values_id'] . '
                    AND pov.language_id = ' . (int)$_SESSION['languages_id'] . ' LIMIT 1';
            $result = $db->Execute($sql);
            $option_value_name = $result->fields['products_options_values_name'];
            $title_suffix = ':' . $option_name . '-' . $option_value_name;
        }

        $title = $this->google_base_xml_sanitizer(
            (GOOGLE_PRODUCTS_META_TITLE === 'true' && !empty($product['metatags_title']) ? $product['metatags_title'] : $product['products_name']) . $title_suffix
        );

        if ($title === '') {
            $this->debug('ERROR: #' . $product['products_id'] . ': title field empty PRODUCT SKIPPED');
            $this->skip_product = true;
        } else {
            $length = strlen($title) + GOOGLE_PRODUCTS_CDATA_LENGTH;
            if ($length > GOOGLE_PRODUCTS_MAX_CHARS_TITLE) {
                $title = zen_trunc_string($title, GOOGLE_PRODUCTS_MAX_CHARS_TITLE - GOOGLE_PRODUCTS_CDATA_LENGTH, false);
                $this->debug('NOTICE: title field "<![CDATA[' . $title . ']]>" length (' . $length . ') exceeded permitted length (' . GOOGLE_PRODUCTS_MAX_CHARS_TITLE . ')');
            }
            $products_title = $dom->createElement('g:title');
            $products_title->appendChild($dom->createCDATASection($title));
            $item->appendChild($products_title);
        }
        return $item;
    }

    /** performs a set of functions to see if a product is valid
     * @param $products_id
     * @return bool
     */
    public function check_product($products_id): bool
    {
        return $this->check_included_categories(GOOGLE_PRODUCTS_POS_CATEGORIES, $products_id)
            && !$this->check_excluded_categories(GOOGLE_PRODUCTS_NEG_CATEGORIES, $products_id)
            && $this->check_included_manufacturers(GOOGLE_PRODUCTS_POS_MANUFACTURERS, $products_id)
            && !$this->check_excluded_manufacturers(GOOGLE_PRODUCTS_NEG_MANUFACTURERS, $products_id);
    }

    /** check to see if a product is inside an INCLUDED category
     * @param $categories_list
     * @param $products_id
     * @return bool
     */
    private function check_included_categories($categories_list, $products_id): bool
    {
        if ($categories_list === '') {
            return true;
        }
        $categories_array = explode(',', $categories_list);
        $match = false;
        foreach ($categories_array as $category_id) {
            if (zen_product_in_category($products_id, $category_id)) {
                $match = true;
                break;
            }
        }
        return $match === true;
    }

    /** check to see if a product is inside an EXCLUDED category
     * @param $categories_list
     * @param $products_id
     * @return bool
     */
    private function check_excluded_categories($categories_list, $products_id): bool
    {
        if ($categories_list === '') {
            return false;
        }

        $categories_array = explode(',', $categories_list);
        $match = false;
        foreach ($categories_array as $category_id) {
            if (zen_product_in_category($products_id, $category_id)) {
                $match = true;
                break;
            }
        }
        return $match === true;
    }

    /** check to see if a product is from an included manufacturer
     * @param $manufacturers_list
     * @param $products_id
     * @return bool
     */
    private function check_included_manufacturers($manufacturers_list, $products_id): bool
    {
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

    /** check to see if a product is from an excluded manufacturer
     * @param $manufacturers_list
     * @param $products_id
     * @return bool
     */
    private function check_excluded_manufacturers($manufacturers_list, $products_id): bool
    {
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

    /** todo factor in the excluded/included arrays
     * counts the maximum valid products for display in the admin page and for calculating the filename limits suffix in the feed popup
     * @return int
     */
    public function count_valid_products(): int
    {
        global $db;
        $products_count_sql = 'SELECT COUNT(products_id) as products_max FROM ' . TABLE_PRODUCTS . ' p
                                 WHERE p.products_status = 1
                                 AND (p.products_price > 0 OR (p.products_price = 0 AND p.products_priced_by_attribute = 1))
                                 AND p.products_type <> 3
                                 AND p.product_is_call <> 1
                                 AND p.product_is_free <> 1
                                 AND (p.products_image IS NOT NULL
                                 OR p.products_image != ""
                                 OR p.products_image != "' . PRODUCTS_IMAGE_NO_IMAGE . '")';
        $products_count = $db->Execute($products_count_sql);
        return (int)$products_count->fields['products_max'];
    }

    /** create the item/product wrapper
     * @param $dom
     * @return mixed
     */
    public function create_item($dom)
    {
        return $dom->createElement('item');
    }

    /** TODO: remove this and use discrete methods: already done for one use of this function
     * create a simple product/that doesn't use attributes
     * @param $products
     * @param $dom
     * @return mixed
     */
    public function create_regular_product($products, $dom)
    {
        global $id, $price, $tax_rate, $productstitle, $percategory, $freerules;
        $item = $dom->createElement('item');

        $iD = $dom->createElement('g:id');
        $iD->appendChild($dom->createCDATASection($id));
        $item->appendChild($iD);

        $products_title = $dom->createElement('g:title');
        $products_title->appendChild($dom->createCDATASection($productstitle));
        $item->appendChild($products_title);

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
//availability: "in_stock", "out_of_stock", "preorder" (products not yet released: requires availability_date), "backorder" (products that will be back in stock: requires availability_date)
//replaced original code with switch as easier to understand/modify
        switch (true) {
            case (STOCK_CHECK === 'false'):
                $item->appendChild($dom->createElement('g:availability', 'in_stock'));
            case ($products->fields['products_quantity'] > 0):
                $item->appendChild($dom->createElement('g:availability', 'in_stock'));
                break;
            case ($products->fields['products_quantity'] <= 0 && $products->fields['products_date_available'] > date('Y-m-d H:i:s')):
                $item->appendChild($dom->createElement('g:availability', 'backorder'));
                $item->appendChild($dom->createElement('g:availability_date', date('Y-m-d')));
                break;
            //past date/null/not set both come under this clause
            case ($products->fields['products_quantity'] <= 0 && $products->fields['products_date_available'] < date('Y-m-d H:i:s')):
                $item->appendChild($dom->createElement('g:availability', 'backorder'));
                define('GOOGLE_PRODUCTS_BIS_DAYS', '10'); //default back in stock delay in days, to add to today's date
                $bisDate = date_create();
                date_add($bisDate, date_interval_create_from_date_string(GOOGLE_PRODUCTS_BIS_DAYS . ' days'));
                $item->appendChild($dom->createElement('g:availability_date', date_format($bisDate, 'Y-m-d')));
                break;
            default:
                //if in doubt, in-stock!
                $item->appendChild($dom->createElement('g:availability', 'in_stock'));
        }
        /* original code
                if (STOCK_CHECK === 'true') { // ZC constant, products have physical stock, and it is checked
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
        */
        if (GOOGLE_PRODUCTS_WEIGHT === 'true' && $products->fields['products_weight'] !== '') {
            $item->appendChild($dom->createElement('g:shipping_weight', $products->fields['products_weight'] . ' ' . str_replace(['pounds', 'kilograms'], ['lb', 'kg'], GOOGLE_PRODUCTS_UNITS)));
        }

        //Shipping
        if (defined('GOOGLE_PRODUCTS_SHIPPING_METHOD') && (GOOGLE_PRODUCTS_SHIPPING_METHOD !== '') && (GOOGLE_PRODUCTS_SHIPPING_METHOD !== 'none')) {
            $shipping_rate = $this->shipping_rate(GOOGLE_PRODUCTS_SHIPPING_METHOD, $percategory, $freerules, GOOGLE_PRODUCTS_RATE_ZONE, $products->fields['products_weight'], $price, $products->fields['products_id']);

            if (GOOGLE_PRODUCTS_SHIPPING_METHOD === 'custom') {
                $shipping = $this->add_shipping_attributes_custom($dom, $item);
            } else {
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
                    $shipping->appendChild($dom->createElement('g:price', (string)$shipping_rate));
                }
            }
            $item->appendChild($shipping);
        }

        return $item;
    }

    /**
     * @param $message
     * @param $level
     * @return void
     */
    public function debug($message)
    {
        //no debug log
        if (GOOGLE_PRODUCTS_DEBUG === '0') {
            return;
        }
        //only errors/skipped products
        if (GOOGLE_PRODUCTS_DEBUG === '1' && !str_contains($message, 'ERROR')) {
            return;
        }
        error_log(date('H:i:s') . ": $message\n", 3, $this->debug_log_file);
    }

    /** adds universal attributes from $products to the previously-created $item
     * @param $products
     * @param $item
     * @param $dom
     * @return mixed
     */
    public function universal_attributes($products, $item, $dom)
    {
        global $link, $product_type, $payments_accepted, $google_product_category_check, $default_google_product_category, $products_description;

        if (GOOGLE_PRODUCTS_PRODUCT_CONDITION === 'true' && $products->fields['products_condition'] !== '') {
            $item->appendChild($dom->createElement('g:condition', $products->fields['products_condition']));
        } else {
            $item->appendChild($dom->createElement('g:condition', GOOGLE_PRODUCTS_CONDITION));
        }

        if ($product_type) {
            $item->appendChild($dom->createElement('g:product_type', $product_type));
        }
        //steve createElement chokes on an ampersand
        if ($products->fields['products_image'] !== '' && file_exists(DIR_WS_IMAGES . $products->fields['products_image']) && (!str_contains($products->fields['products_image'], '&'))) {
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
        // only include if less than 30 days as 30 is the max and leaving blank will default to the max
        if (GOOGLE_PRODUCTS_EXPIRATION_DAYS <= 29) {
            $item->appendChild($dom->createElement('g:expiration_date', $this->google_base_expiration_date($products->fields['base_date'])));
        }

        $item->appendChild($dom->createElement('g:link', $link));

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
        if (GOOGLE_PRODUCTS_PICKUP !== 'do not display') {
            $item->appendChild($dom->createElement('g:pickup', GOOGLE_PRODUCTS_PICKUP));
        }
        if (defined('GOOGLE_PRODUCTS_PAYMENT_METHODS') && GOOGLE_PRODUCTS_PAYMENT_METHODS !== '') {
            foreach ($payments_accepted as $payment_accepted) {
                $item->appendChild($dom->createElement('g:payment_accepted', trim($payment_accepted)));
            }
        }
        if (defined('GOOGLE_PRODUCTS_PAYMENT_NOTES') && GOOGLE_PRODUCTS_PAYMENT_NOTES !== '') {
            $item->appendChild($dom->createElement('g:payment_notes', trim(GOOGLE_PRODUCTS_PAYMENT_NOTES)));
        }
        $productsDescription = $dom->createElement('g:description');
        $productsDescription->appendChild($dom->createCDATASection(substr($products_description, 0, 9988))); // 10000 - 12 to account for cData
        $item->appendChild($productsDescription);
        if ($google_product_category_check === false && GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY !== '') {
            $google_product_category = $dom->createElement('g:google_product_category');
            $google_product_category->appendChild($dom->createCDATASection($default_google_product_category));
            $item->appendChild($google_product_category);
        }
        return $item;
    }

    /** adds universal attributes from $products to the previously-created $item
     * @param $products
     * @param $item
     * @param $dom
     * @return mixed
     */
    public function universal_attributes_temp($products, $item, $dom)
    {
        global $link, $product_type, $payments_accepted, $google_product_category_check, $default_google_product_category, $products_description;
        /*
                if (GOOGLE_PRODUCTS_PRODUCT_CONDITION === 'true' && $products->fields['products_condition'] !== '') {
                    $item->appendChild($dom->createElement('g:condition', $products->fields['products_condition']));
                } else {
                    $item->appendChild($dom->createElement('g:condition', GOOGLE_PRODUCTS_CONDITION));
                }
        */
        /*
        if ($product_type) {
            $item->appendChild($dom->createElement('g:product_type', $product_type));
        }
        */
        //steve createElement chokes on an ampersand
        /*
        if ($products->fields['products_image'] !== '' && file_exists(DIR_WS_IMAGES . $products->fields['products_image']) && (!str_contains($products->fields['products_image'], '&'))) {
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
        }*/
        // only include if less than 30 days as 30 is the max and leaving blank will default to the max
        /*
                if (GOOGLE_PRODUCTS_EXPIRATION_DAYS <= 29) {
                    $item->appendChild($dom->createElement('g:expiration_date', $this->google_base_expiration_date($products->fields['base_date'])));
                }
        */
        /*
        $item->appendChild($dom->createElement('g:link', $link));
*/
        /* products model is NOT mpn
        if ($products->fields['products_model'] != '') {
            $mpn = $dom->createElement('g:mpn');
            $mpn->appendChild($dom->createCDATASection($this->google_base_xml_sanitizer($products->fields['products_model'])));
            $item->appendChild($mpn);
        }
        */
        /* attribute does not exist
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
        }*/
        /* does not exist
        if (GOOGLE_PRODUCTS_CURRENCY_DISPLAY === 'true') {
            $item->appendChild($dom->createElement('g:currency', GOOGLE_PRODUCTS_CURRENCY));
        }*/
        /*
        if (GOOGLE_PRODUCTS_PICKUP !== 'do not display') {
            $item->appendChild($dom->createElement('g:pickup', GOOGLE_PRODUCTS_PICKUP));
        }*/
        /*
                if (defined('GOOGLE_PRODUCTS_PAYMENT_METHODS') && GOOGLE_PRODUCTS_PAYMENT_METHODS !== '') {
                    foreach ($payments_accepted as $payment_accepted) {
                        $item->appendChild($dom->createElement('g:payment_accepted', trim($payment_accepted)));
                    }
                }*/
        /*
                if (defined('GOOGLE_PRODUCTS_PAYMENT_NOTES') && GOOGLE_PRODUCTS_PAYMENT_NOTES !== '') {
                    $item->appendChild($dom->createElement('g:payment_notes', trim(GOOGLE_PRODUCTS_PAYMENT_NOTES)));
                }*/
        /*
                $productsDescription = $dom->createElement('g:description');
                $productsDescription->appendChild($dom->createCDATASection(substr($products_description, 0, 9988))); // 10000 - 12 to account for cData
                $item->appendChild($productsDescription);
        */
        /*
            if ($google_product_category_check === false && GOOGLE_PRODUCTS_DEFAULT_PRODUCT_CATEGORY !== '') {
                $google_product_category = $dom->createElement('g:google_product_category');
                $google_product_category->appendChild($dom->createCDATASection($default_google_product_category));
                $item->appendChild($google_product_category);
            }*/
        return $item;
    }

    /**
     * @param $str
     * @param $rt
     * @return string
     */
    private function google_base_sanita($str, $rt = false): string
    {
        //global $products;
        $str = strip_tags($str);
        $str = str_replace(["\r\n", "\r", "\n", '&nbsp;', '’'], [' ', ' ', ' ', ' ', "'"], $str);
        $str = preg_replace('/[[:blank:]]+/', ' ', $str); // remove multiple white spaces created above
        //$charset = 'UTF-8';
        //if (defined(CHARSET)) {
        //$charset = strtoupper(CHARSET);
        //}
        // todo is this needed?? tags are already stripped above
        $str = html_entity_decode($str, ENT_QUOTES);//, $charset);
        //$str = html_entity_decode($str, ENT_QUOTES, $charset);
        //$str = htmlspecialchars($str, ENT_QUOTES, '', false);
        //$str = htmlentities($str, ENT_QUOTES, $charset, false);
        return $str;
    }

    /**
     * @param $str
     * @param $products_id
     * @return string|null
     */
    public function google_base_xml_sanitizer($str, $products_id = ''): ?string
    { // products id added for debugging purposes
        $str = $this->google_base_sanita($str);
        if (GOOGLE_PRODUCTS_XML_SANITIZATION === 'true') {
            $str = $this->transcribe_cp1252_to_latin1($str); // transcribe windows characters
            $strout = null;

            for ($i = 0; $i < strlen($str); $i++) {
                $ord = ord($str[$i]);
                if (($ord > 0 && $ord < 32) || ($ord >= 127)) {
                    $strout .= "&#{$ord};";
                } else {
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

    /**
     * @param $cp1252
     * @return string
     */
    private function transcribe_cp1252_to_latin1($cp1252): string
    {
        return strtr(
            $cp1252,
            [
                "\x80" => 'e',
                "\x81" => ' ',
                "\x82" => "'",
                "\x83" => 'f',
                "\x84" => '"',
                "\x85" => '...',
                "\x86" => '+',
                "\x87" => '#',
                "\x88" => '^',
                "\x89" => '0/00',
                "\x8A" => 'S',
                "\x8B" => '<',
                "\x8C" => 'OE',
                "\x8D" => ' ',
                "\x8E" => 'Z',
                "\x8F" => ' ',
                "\x90" => ' ',
                "\x91" => '`',
                "\x92" => "'",
                "\x93" => '"',
                "\x94" => '"',
                "\x95" => '*',
                "\x96" => '-',
                "\x97" => '--',
                "\x98" => '~',
                "\x99" => '(TM)',
                "\x9A" => 's',
                "\x9B" => '>',
                "\x9C" => 'oe',
                "\x9D" => ' ',
                "\x9E" => 'z',
                "\x9F" => 'Y'
            ]
        );
    }

    // creates the url for the products_image

    /**
     * @param $products_image
     * @return array|string|string[]
     */
    private function google_base_image_url($products_image)
    {
        if ($products_image === '') {
            return '';
        }
        if (defined('GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL') && GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL !== '') {
            if (strpos(GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL, HTTP_SERVER . '/' . DIR_WS_IMAGES) !== false) {
                $products_image = substr(GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL, strlen(HTTP_SERVER . '/' . DIR_WS_IMAGES)) . $products_image;
            } else {
                return GOOGLE_PRODUCTS_ALTERNATE_IMAGE_URL . rawurlencode($products_image);
            }
        }
        $products_image_extension = substr($products_image, strrpos($products_image, '.'));
        $products_image_base = preg_replace('/' . $products_image_extension . '/', '', $products_image);
        $products_image_medium = $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
        $products_image_large = $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

        // check for a large image else use medium else use small
        if (file_exists(DIR_WS_IMAGES . 'large/' . $products_image_large)) {
            $products_image_large = DIR_WS_IMAGES . 'large/' . $products_image_large;
        } elseif (file_exists(DIR_WS_IMAGES . 'medium/' . $products_image_medium)) {
            $products_image_large = DIR_WS_IMAGES . 'medium/' . $products_image_medium;
        } else {
            $products_image_large = DIR_WS_IMAGES . $products_image;
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

    /**
     * @param $article_id
     * @return array|mixed|string|string[]|null
     */
    private function google_base_news_link($article_id)
    {
        return zen_href_link(FILENAME_NEWS_ARTICLE, 'article_id=' . (int)$article_id . $product_url_add, 'NONSSL', false);
    }

    /**
     * @param $base_date
     * @return false|string
     */
    private function google_base_expiration_date($base_date)
    {
        if (GOOGLE_PRODUCTS_EXPIRATION_BASE === 'now') {
            $expiration_date = time();
        } else {
            $expiration_date = strtotime($base_date);
        }
        $expiration_date += GOOGLE_PRODUCTS_EXPIRATION_DAYS * 24 * 60 * 60;
        return (date('Y-m-d', $expiration_date));
    }

// SHIPPING FUNCTIONS //

    /**
     * @param $countries_id
     * @return mixed
     */
    public function get_countries_iso_code_2($countries_id)
    {
        global $db;

        $countries_query = 'SELECT countries_iso_code_2
                        FROM ' . TABLE_COUNTRIES . '
                        WHERE countries_id = ' . (int)$countries_id . '
                        LIMIT 1';
        $countries = $db->Execute($countries_query);
        return $countries->fields['countries_iso_code_2'];
    }

    /**
     * @param string $method //shipping module
     * @param string $percategory //todo may be null
     * @param string $freerules //todo may be null
     * @param string $table_zone
     * @param string $products_weight
     * @param string $products_price //todo is float
     * @param string $products_id
     * @return float|int|array //array for custom processing
     */
    public function shipping_rate($method, $percategory = '', $freerules = '', string $table_zone = '', string $products_weight = '', $products_price = '', string $products_id = '')
    {
        global $currencies, $percategory, $freerules;
        // skip the calculation for products that are always free shipping
        $rate = 0;
        if (zen_get_product_is_always_free_shipping($products_id)) {
            $rate = 0;
        } else {
            switch ($method) {
//Zen Cart built-in shipping methods
                case 'flat':
                    $rate = MODULE_SHIPPING_FLAT_COST;
                    break;
                /*
                 case "freeoptions":
                    $rate = 0;
                    break;
                 */
                case 'freeshipper':
                    $rate = 0;
                    break;
                case 'item':
                    $rate = MODULE_SHIPPING_ITEM_COST + MODULE_SHIPPING_ITEM_HANDLING;
                    break;
                case 'perweightunit':
                    $rate = (MODULE_SHIPPING_PERWEIGHTUNIT_COST * $products_weight) + MODULE_SHIPPING_PERWEIGHTUNIT_HANDLING;
                    break;
                /*
                 case "storepickup":
                    $rate = 0;
                    break;
                 */
                case 'table':
                    $rate = $this->numinix_table_rate($products_weight, $products_price);
                    break;
                case 'zones':
                    $rate = $this->numinix_zones_rate($products_weight, $products_price, $table_zone);
                    break;
//eof Zen Cart built-in shipping methods
//Third party shipping modules
                //Numinix shipping module: https://www.numinix.com/zen-cart-plugins-shipping-c-179_250_373_163/free-shipping-rules-dl-755
                case 'freerules':
                    if (is_object($freerules)) {
                        if ($freerules->test($products_id)) {
                            $rate = 0;
                        } else {
                            $rate = -1;
                        }
                    }
                    break;
                case 'percategory'://Numinix shipping module: https://www.numinix.com/zen-cart-plugins-shipping-c-179_250_373_163/per-category-shipping-standard-dl-771
                    if (is_object($percategory)) {
                        $products_array = [];
                        $products_array[0]['id'] = $products_id;
                        $rate = $percategory->calculation($products_array, $table_zone, (int)MODULE_SHIPPING_PERCATEGORY_GROUPS);
                    }
                    break;
                case 'zonetable'://Plugin Zones Table Rate (for Multiple Zones): https://www.zen-cart.com/downloads.php?do=file&id=478
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

    /**
     * @param $products_weight
     * @param $products_price
     * @return float|int|mixed
     */
    private function numinix_table_rate($products_weight, $products_price)
    {//Zen Cart shipping method: table
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
        for ($i = 0, $n = $size; $i < $n; $i += 2) {
            if (round($order_total, 9) <= $table_cost[$i]) {
                //if (strstr($table_cost[$i+1], '%')) {
                if (strpos($table_cost[$i + 1], '%') !== false) {//todo check
                    $shipping = ($table_cost[$i + 1] / 100) * $products_price;
                } else {
                    $shipping = $table_cost[$i + 1];
                }
                break;
            }
        }
        $shipping += MODULE_SHIPPING_TABLE_HANDLING;
        return $shipping;
    }

    /**
     * @param $products_weight
     * @param $table_zone
     * @return mixed
     */
    private function numinix_zones_table_rate($products_weight, $table_zone)
    {//Plugin: Zones Table Rate (for Multiple Zones) https://www.zen-cart.com/downloads.php?do=file&id=478
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
        for ($i = 0, $n = $size; $i < $n; $i += 2) {
            if (round($order_total, 9) <= $table_cost[$i]) {
                $shipping = $table_cost[$i + 1];
                break;
            }
        }
        $shipping += constant('MODULE_SHIPPING_ZONETABLE_HANDLING_' . $table_zone);
        return $shipping;
    }

    /**
     * @param $products_weight
     * @param $products_price
     * @param $table_zone
     * @return float|int|mixed
     */
    private function numinix_zones_rate($products_weight, $products_price, $table_zone)
    {//Zen Cart shipping method: zones
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
        for ($i = 0; $i < $size; $i += 2) {
            if (round($order_total, 9) <= $zones_table[$i]) {
                //if (strstr($zones_table[$i+1], '%')) {
                if (strpos($zones_table[$i + 1], '%') !== false) {//todo check
                    $shipping = ($zones_table[$i + 1] / 100) * $products_price;
                } else {
                    $shipping = $zones_table[$i + 1];
                }
                break;
            }
        }
        $shipping += constant('MODULE_SHIPPING_ZONES_HANDLING_' . $table_zone);
        return $shipping;
    }

    /**
     * @param $delim1
     * @param $delim2
     * @param $string
     * @return array
     */
    private function google_multi_explode($delim1, $delim2, $string): array
    {
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
    /**
     * @param $products_id
     * @return int|mixed|string
     */
    public function google_get_products_actual_price($products_id)
    {
        global $db, $currencies;
        $product_check = $db->Execute(
            'SELECT products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call
                                   FROM ' . TABLE_PRODUCTS . '
                                   WHERE products_id = ' . (int)$products_id . ' LIMIT 1'
        );

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

    /**
     * @param $products_id
     * @return int|mixed
     */
    private function google_get_products_base_price($products_id)
    {
        global $db;
        $product_check = $db->Execute(
            'SELECT products_price, products_priced_by_attribute
                                     FROM ' . TABLE_PRODUCTS . '
                                     WHERE products_id = ' . (int)$products_id
        );

// is there a products_price to add to attributes
        $products_price = $product_check->fields['products_price'];

        // do not select display only attributes and attributes_price_base_included is true
        $product_att_query = $db->Execute(
            'SELECT options_id, price_prefix, options_values_price, attributes_display_only, attributes_price_base_included
                                         FROM ' . TABLE_PRODUCTS_ATTRIBUTES . '
                                         WHERE products_id = ' . (int)$products_id . "
                                         AND attributes_display_only != '1'
                                         AND attributes_price_base_included ='1'
                                         AND options_values_price > 0" . '
                                         ORDER BY options_id, price_prefix, options_values_price'
        );
        //echo $products_id . ' ';
        //print_r($product_att_query);
        //die();
        $the_options_id = 'x';
        $the_base_price = 0;
// add attributes price to price
        if ($product_check->fields['products_priced_by_attribute'] === '1' && $product_att_query->RecordCount() >= 1) {
            while (!$product_att_query->EOF) {//todo foreach
                if ($the_options_id != $product_att_query->fields['options_id']) {
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

    /**
     * @param $product_id
     * @param $product_price
     * @param bool $specials_price_only
     * @return false|mixed|string
     */
    private function google_get_products_special_price($product_id, $product_price, bool $specials_price_only = false)
    {
        global $db;
        $product = $db->Execute('SELECT products_price, products_model, products_priced_by_attribute FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . (int)$product_id);

        //if ($product->RecordCount() > 0) {
//      $product_price = $product->fields['products_price'];
        //$product_price = zen_get_products_base_price($product_id);
        //} else {
        //return false;
        //}

        $specials = $db->Execute('SELECT specials_new_products_price FROM ' . TABLE_SPECIALS . ' WHERE products_id = ' . (int)$product_id . ' AND status = 1');
        if ($specials->RecordCount() > 0) {
//      if ($product->fields['products_priced_by_attribute'] == 1) {
            $special_price = $specials->fields['specials_new_products_price'];
        } else {
            $special_price = false;
        }

        if (strpos($product->fields['products_model'], 'GIFT') === 0) {    //Never apply a salededuction to Ian Wilson's Giftvouchers
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

        $product_to_categories = $db->Execute('SELECT master_categories_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . (int)$product_id);
        $category = $product_to_categories->fields['master_categories_id'];

        $sale = $db->Execute(
            'SELECT sale_specials_condition, sale_deduction_value, sale_deduction_type
                            FROM ' . TABLE_SALEMAKER_SALES . "
                            WHERE sale_categories_all
                            LIKE '%," . $category . ",%'
                            AND sale_status = '1'
                            AND (sale_date_start <= now() OR sale_date_start = '0001-01-01')
                            AND (sale_date_end >= now() OR sale_date_end = '0001-01-01')
                            AND (sale_pricerange_from <= '" . $product_price . "' OR sale_pricerange_from = '0')
                            AND (sale_pricerange_to >= '" . $product_price . "' OR sale_pricerange_to = '0')"
        );

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

        switch ($sale->fields['sale_specials_condition']) {
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

    /** BIG TODO...initial attempt for sftp...tried to do it on Windows, gave up
     * @param $local_file
     * @return bool
     */
    public function sftp_file_upload($local_file): bool
    {
        define('GMC_SFTP_SERVER', 'partnerupload.google.com');
        define('GMC_SFTP_SERVER_PORT', '19321');
        define('GMC_SFTP_SERVER_USERNAME', '');
        define('GMC_SFTP_SERVER_PASSWORD', '');

//Send file via sftp to server
        $strServer = GMC_SFTP_SERVER;
        $strServerPort = GMC_SFTP_SERVER_PORT;
        $strServerUsername = GMC_SFTP_SERVER_USERNAME;
        $strServerPassword = GMC_SFTP_SERVER_PASSWORD;
        $csv_filename = $local_file;

//connect to server
        $resConnection = ssh2_connect($strServer, $strServerPort);

        if (ssh2_auth_password($resConnection, $strServerUsername, $strServerPassword)) {
            //Initialize SFTP subsystem

            echo 'connected';
            $resSFTP = ssh2_sftp($resConnection);
//stackoverflow question
            // $resFile = fopen("ssh2.sftp://{$resSFTP}/".$csv_filename, 'w');
            // fwrite($resFile, "Testing");
            // fclose($resFile);
//answer
            $resFile = fopen("ssh2.sftp://{$resSFTP}/" . $csv_filename, 'w');
            $srcFile = fopen($csv_filename, 'r');
            $writtenBytes = stream_copy_to_stream($srcFile, $resFile);
            fclose($resFile);
            fclose($srcFile);
            return true;
        } else {
            echo 'Unable to authenticate on server';
            return false;
        }
    }

// FTP FUNCTIONS //

    /**
     * @param $url
     * @param $login
     * @param $password
     * @param $local_file
     * @param string $ftp_dir
     * @param bool $ftp_file
     * @param bool $ssl
     * @param int $ftp_mode
     * @return bool
     */
    public function ftp_file_upload($url, $login, $password, $local_file, string $ftp_dir = '', $ftp_file = false, bool $ssl = false, $ftp_mode = FTP_ASCII): bool
    {
        $debug_ftp_file_upload = false;//verbose step-by-step processing output for dummiesº

        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . FTP_START . NL;
        if (!is_callable('ftp_connect')) {
            echo '<p class="errorText">' . FTP_FAILED . '</p>';
            return false;
        }

        if (!$ftp_file) {
            $ftp_file = basename($local_file);//todo check change from boolean to string
        }
        echo($debug_ftp_file_upload ? __LINE__ . ': before ob_start' . NL : '');

        ob_start();
        echo($debug_ftp_file_upload ? __LINE__ . ': after ob_start' . NL : '');
        if ($ssl) {
            $cd = ftp_ssl_connect($url);//silenced as an error gets reported
        } else {
            $cd = ftp_connect($url);//silenced to prevent debug, as any error is handled subsequently
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

        $login_result = ftp_login($cd, $login, $password);//silenced to prevent debug, as any error is handled subsequently
        if (!$login_result) {
            $out = $this->ftp_get_error_from_ob();
            //      echo FTP_LOGIN_FAILED . FTP_USERNAME . ' ' . $login . FTP_PASSWORD . ' ' . $password . NL;
            echo '<p class="errorText">' . sprintf(FTP_LOGIN_FAILED, $url);
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
            echo '<p class="errorText">' . sprintf(FTP_CANT_CHANGE_DIRECTORY, $url, $ftp_dir);
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
            for ($i = 0, $n = count($raw); $i < $n; $i++) {
                $out .= $raw[$i] . '<br>';
            }
        } else {
            echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . FTP_DIRECTORY_NOT_FOUND . NL;
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
        if (is_array($raw)
        ) {
            echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . $raw[0] . NL;
        }
        echo ($debug_ftp_file_upload ? __LINE__ . ': ' : '') . $out . NL;
        ftp_close($cd);
        return true;
    }

    /**
     * @return array|false|string|string[]
     */
    private function ftp_get_error_from_ob()
    {
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

    /**
     * @return float
     */
    public function microtime_float(): float
    {
        [$usec, $sec] = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

//https://alexwebdevelop.com/monitor-script-memory-usage/

    /**
     * @return void
     */
    public function print_mem()
    {
        /* Currently used memory */
        $mem_usage = memory_get_usage();

        /* Peak memory usage */
        $mem_peak = memory_get_peak_usage();

        echo 'The script is now using: <strong>' . round($mem_usage / 1024) . 'KB</strong> of memory.<br>';
        echo 'Peak usage: <strong>' . round($mem_peak / 1024) . 'KB</strong> of memory.<br><br>';
    }


}
