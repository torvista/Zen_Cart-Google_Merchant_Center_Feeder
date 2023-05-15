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

require('includes/application_top.php');
if (!class_exists('language')) {
    require DIR_FS_CATALOG . DIR_WS_CLASSES . 'language.php';
}
$languages = new language();
require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'plugin_google_mc.php');
$google_mc = new google_mc();
//mv_printVar($languages);
//mv_printVar($languages->language);
/**
 * @param $url
 * @param $login
 * @param $password
 * @param string $ftp_dir
 * @param bool $ssl
 * @param int $port
 * @param int $timeout
 * @return string
 */
function ftp_get_rawlist($url, $login, $password, string $ftp_dir = '', bool $ssl = false, int $port = 21, int $timeout = 30): string
{
    $out = FTP_CONNECTION_OK . ' ' . $url . '<br>';
    if ($ssl) {
        $cd = ftp_ssl_connect($url);
    } else {
        $cd = ftp_connect($url, $port, $timeout);
    }
    if (!$cd) {
        return $out . FTP_CONNECTION_FAILED . ' ' . $url . '<br>';
    }
    ftp_set_option($cd, FTP_TIMEOUT_SEC, $timeout);
    $login_result = ftp_login($cd, $login, $password);
    if (!$login_result) {
        ftp_close($cd);
        return $out . FTP_LOGIN_FAILED . FTP_USERNAME . ' ' . $login . FTP_PASSWORD . ' ' . $password . '<br>';
    }
    if ($ftp_dir !== '') {
        if (!ftp_chdir($cd, $ftp_dir)) {
            ftp_close($cd);
            return $out . FTP_CANT_CHANGE_DIRECTORY . '&nbsp;' . $url . '<br>';
        }
    }
    $out .= ftp_pwd($cd) . '<br>';
    $raw = ftp_rawlist($cd, $ftp_file, true);//todo $ftp_file undefined
    for ($i = 0, $n = count($raw); $i < $n; $i++) {
        $out .= $raw[$i] . '<br>';
    }
    ftp_close($cd);
    return $out;
}

?>
<?php
//todo: never used?
if (isset($_GET['action']) && $_GET['action'] === 'ftpdir') {
    ob_start();
    echo TEXT_GOOGLE_PRODUCTS_FTP_FILES . '<br>';
    echo ftp_get_rawlist(GOOGLE_PRODUCTS_SERVER, GOOGLE_PRODUCTS_USERNAME, GOOGLE_PRODUCTS_PASSWORD);
    $out = ob_get_contents();
    ob_end_clean();
    echo '<pre>';
    echo $out;
    exit();
}

if (isset($_GET['action']) && ($_GET['action'] === 'delete')) {
    if (file_exists(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $_GET['file'])) {
        unlink(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $_GET['file']);
    }
    zen_redirect(zen_href_link(FILENAME_GOOGLEFROOGLE));
}
// sticky limit: TODO not working yet, need to add parameters to the js reload after the form submission
$limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : (int)GOOGLE_PRODUCTS_MAX_PRODUCTS;
// sticky offset
$offset = !empty($_GET['offset']) ? (int)$_GET['offset'] : GOOGLE_PRODUCTS_START_PRODUCTS;
// sticky languages
if (count($languages->catalog_languages) > 1) {
    $langsMultiple = $languages->catalog_languages;
    $langSelected = isset($_GET['langSelected']) ? (int)$_GET['langSelected'] : $languages->catalog_languages[DEFAULT_LANGUAGE]['code'];
} else {
    $langSelected = $languages->catalog_languages[$_SESSION['languages_code']]['id'];
    $langsMultiple = false;
}

?>
<!doctype html>
<html <?php
echo HTML_PARAMS; ?>>
<head>
    <?php
    require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <script>
        <!--
        function init() {
            cssjsmenu('navbar');
            if (document.getElementById) {
                var kill = document.getElementById('hoverJS');
                kill.disabled = true;
            }
        }

        // -->
    </script>
    <script><!--
        function getObject(name) {
            var ns4 = (document.layers) ? true : false;
            var w3c = (document.getElementById) ? true : false;
            var ie4 = (document.all) ? true : false;

            if (ns4) return eval('document.' + name);
            if (w3c) return document.getElementById(name);
            if (ie4) return eval('document.all.' + name);
            return false;
        }

        //--></script>
    <script><!--

        var req, name;

        function loadFroogleXMLDoc(request, field, loading) {

            name = field;
            var url = "<?= HTTP_SERVER . DIR_WS_CATALOG . FILENAME_GOOGLEFROOGLE . '.php?'; ?>" + request;
            // Internet Explorer
            try {
                req = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    req = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (oc) {
                    req = null;
                }
            }

            // Mozilla/Safari
            if (!req && typeof XMLHttpRequest != "undefined") {
                req = new XMLHttpRequest();
            }

            // Call the processChange() function when the page has loaded
            if (req != null) {
                processLoading(loading);
                req.onreadystatechange = processChange;
                req.open("GET", url, true);
                req.send(null);
            }
        }

        function processChange() {
            if (req.readyState == 4 && req.status == 200)
                getObject(name).innerHTML = req.responseText;
        }

        function processLoading(text) {
            getObject(name).innerHTML = text;
        }

        //--></script>
    <style>
        .limiters {
            width: 100px;
        }

        .buttonRow {
            padding: 5px 0;
        }

        table#googleFiles {
            margin-left: 0;
            border-collapse: collapse;
            border: 1px solid #036;
            font-size: small;
        }

        table#googleFiles th {
            background-color: #036;
            border-bottom: 1px double #fff;
            color: #fff;
            text-align: left;
            padding: 8px;
        }

        table#googleFiles td {
            border: 1px solid #036;
            vertical-align: top;
            padding: 5px 10px;
        }

        #gmc_logo {
            background-image: url("https://www.gstatic.com/merchants/appbar/logo_light.svg");
            background-size: cover;
            background-repeat: no-repeat;
            width: 284px;
            height: 29px;
            margin-top: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
<!-- header //-->
<?php
require(DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->
<div class="row">
    <div><a id="gmc_logo" href="https://merchants.google.com" target="_blank"></a></div>
    <h1><?= HEADING_TITLE . ' ' . GOOGLE_PRODUCTS_VERSION; ?></h1
    <br>
    <?php
    //check output file location permissions
    $error_file = true;
    if (is_dir(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)) {
        if (is_writable(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)) {
            $error_file = false;
        } else {
            echo '<p class="errorText">' . sprintf(ERROR_GOOGLE_PRODUCTS_DIRECTORY_NOT_WRITEABLE, substr(sprintf('%o', fileperms(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)), -4)) . '</p>';
        }
    } else {
        echo '<p class="errorText">' . ERROR_GOOGLE_PRODUCTS_DIRECTORY_DOES_NOT_EXIST . '</p>';
    }

    if (!$error_file) { ?>
        <div>
            <?php
            if (GOOGLE_PRODUCTS_DEBUG === '3') {
                //use a bigger popup window to show more line-by-line product details
                $popup_width = 1100;
                $popup_height = 750;
            } else {
                //summary only
                $popup_width = 400;
                $popup_height = 400;
            }

            // The form action executes the script in the shop root in a popup window, and also reloads the admin page after a delay so it can find the newly-created file and list it in the table
            ?>
            <form method="get" action="<?= HTTP_SERVER . DIR_WS_CATALOG . FILENAME_GOOGLEFROOGLE . '.php'; ?>" name="google" target="googlefeed"
                  onsubmit="window.open('', 'googlefeed', 'resizable=1, statusbar=5, width=<?= $popup_width; ?>, height=<?= $popup_height; ?>, top=15, left=35, scrollbars=yes');setTimeout('location.reload();', 5000);">
                <?php
                echo zen_draw_hidden_field('feed', 'fy_un_tp'); //this replaces the original Products option
                echo zen_draw_hidden_field('languageAdmin', $_SESSION['language']); // pass the currently selected Admin language to the popup, but not affect the shopfront selected language
                ?>

                <label for="limit" class="control-label"><?= TEXT_ENTRY_LIMIT . zen_draw_input_field('limit', $limit, 'class="limiters" id="limit"'); ?></label> <span>(total valid products <?= $google_mc->count_valid_products(); ?>)</span><br>

                <label for="offset" class="control-label"><?= TEXT_ENTRY_OFFSET . zen_draw_input_field('offset', $offset, 'class="limiters" id="offset"'); ?></label><br>

                <label for="offset" class="control-label"><?= TEXT_ENTRY_TEST_SINGLE_ID . zen_draw_input_field('singleID', '', 'class="limiters" id="singleID"'); ?></label><br>

                <div>
                    <h4 class="control-label"><?= sprintf(TEXT_FEED_PRODUCTS_IDENTIFIER, GOOGLE_PRODUCTS_OFFER_ID); ?></h4>
                </div>

                <?php
                //todo use this to change the default and relocate the configuration switch GOOGLE_PRODUCTS_FEED_SORT to gID=6
                ?>
                <div title="<?= TEXT_FEED_SORT_TITLE . GOOGLE_PRODUCTS_FEED_SORT; ?>">
                    <p class="control-label"><?= TEXT_FEED_ORDER_BY; ?></p>
                    <label class="control-label"><?= TEXT_FEED_ID . zen_draw_radio_field('feed_sort', 'id', GOOGLE_PRODUCTS_FEED_SORT === 'ID', 'class="form-control"'); ?></label><br>
                    <label class="control-label"><?= TEXT_FEED_MODEL . zen_draw_radio_field('feed_sort', 'model', GOOGLE_PRODUCTS_FEED_SORT === 'Model', 'class="form-control"'); ?></label><br>
                    <label class="control-label"><?= TEXT_FEED_NAME . zen_draw_radio_field('feed_sort', 'name', GOOGLE_PRODUCTS_FEED_SORT === 'Name', 'class="form-control"'); ?></label><br>
                    <label class="control-label"><?= TEXT_FEED_DATE . zen_draw_radio_field('feed_sort', 'date', GOOGLE_PRODUCTS_FEED_SORT === 'Date', 'class="form-control"'); ?></label>
                </div>

                <?php
                if ($langsMultiple !== false) { ?>
                <div><p class="control-label"><?= TEXT_LANGUAGE_OPTIONS; ?></p>
                    <?php
                    foreach ($langsMultiple as $langCode => $langArray) { ?>
                        <label class="control-label"><?= $langArray['name']; ?>: <?= zen_draw_radio_field('langSelected', $langArray['id'], $langArray['id'] = $langSelected, 'class="limiters input-group" id="langSelected"'); ?></label><br>
                        <?php
                    }
                    //todo All languages
                    //echo '<label class="control-label">TEXT_LANGUAGE_ALL:' . zen_draw_radio_field('langSelected', '0', $langSelect === '0', 'class="limiters form-control" id="langSelected"') . '</label></div>';
                    }
                    zen_draw_hidden_field('langSelected', $langSelected); ?>
<?php /* todo some other time
                    <div>
                        <label class="control-label"><?= TEXT_GENERATE_MULTIPLE_FEEDS . zen_draw_checkbox_field('multipleFeeds', '1', false, 'class="form-control"'); ?></label><br>
                    </div>
 */ ?>
                    <button type="submit" class="btn btn-primary"><?= IMAGE_GO; ?></button>
                    <input type="hidden" name="key" value="<?= GOOGLE_PRODUCTS_KEY; ?>">
            </form>
        </div>
        <hr>
        <div>
            <h2><?= TEXT_FEED_FILES; ?></h2>
            <table id="googleFiles">
                <tr>
                    <th><?= TEXT_DATE_CREATED ?></th>
                    <th><?= TEXT_DOWNLOAD_LINK ?></th>
                    <th><?= TEXT_ACTION ?></th>
                </tr>

                <?php
                //check output file location permissions
                if ($handle = opendir(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)) {
                    while (($file = readdir($handle)) !== false) {
                        if (str_ends_with($file, '.xml')) {
                            $filetime = filemtime(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $file);
                            $date = date('d/m/Y H:i:s', $filetime);
                            ?>
                            <tr>
                                <td><?= $date; ?></td>
                                <td><a href="<?= HTTP_SERVER . DIR_WS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $file; ?>" target="_blank"><?= $file; ?></a></td>
                                <td>
                                    <a href="<?= zen_href_link(FILENAME_GOOGLEFROOGLE, 'file=' . $file . '&action=delete'); ?>"><?= IMAGE_DELETE; ?></a>
                                    <a href="#"
                                       onclick="window.open('<?= HTTP_SERVER . DIR_WS_CATALOG . FILENAME_GOOGLEFROOGLE; ?>.php?feed=fn_uy_tp&upload_file=<?= $file; ?>&key=<?= GOOGLE_PRODUCTS_KEY; ?>', 'googlefrooglefeed', 'resizable=1, statusbar=5, width=600, height=400, top=5, left=50, scrollbars=yes'); return false;"><?= IMAGE_UPLOAD; ?></a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    closedir($handle);
                }
                ?>
            </table>
        </div>
        <?php
    } ?>
    <hr>
    <div>
        <?= TEXT_GOOGLE_PRODUCTS_LINKS; ?>
    </div>
    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php
require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
