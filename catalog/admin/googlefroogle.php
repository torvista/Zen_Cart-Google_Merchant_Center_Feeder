<?php

declare(strict_types=1);

/**
 * googlefroogle.php
 *
 * @package google froogle
 * @copyright Copyright 2007 Numinix Technology http://www.numinix.com
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: googlefroogle.php 2023 04 28 torvista $
 * @author Numinix Technology
 */

  require('includes/application_top.php');

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
        }
		else {
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
		for($i=0, $n=count($raw); $i<$n; $i++){
			$out .= $raw[$i] . '<br>';
		}
		ftp_close($cd);
		return $out;
	}
?>
<?php //todo: never used?
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
// sticky limit: TODO not working yet, need to add parms to js reload with form submission
$limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : (int)GOOGLE_PRODUCTS_MAX_PRODUCTS;
// sticky offset
$start = !empty($_GET['start']) ? (int)$_GET['start'] : (int)GOOGLE_PRODUCTS_START_PRODUCTS;
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script src="includes/menu.js"></script>
<script src="includes/general.js"></script>
<script>
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
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

function loadFroogleXMLDoc(request,field, loading) {

   name = field;
   var url="<?php echo HTTP_SERVER . DIR_WS_CATALOG . FILENAME_GOOGLEFROOGLE . '.php?' ?>"+request;
   // Internet Explorer
   try { req = new ActiveXObject("Msxml2.XMLHTTP"); }
   catch(e) {
      try { req = new ActiveXObject("Microsoft.XMLHTTP"); }
      catch(oc) { req = null; }
   }

   // Mozilla/Safari
   if (!req && typeof XMLHttpRequest != "undefined") { req = new XMLHttpRequest(); }

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
  /*label{display:block;width:200px;float:left;}*/
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
</style>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<div class="container-fluid">
      <!-- body_text //-->
      <div class="row">
     <h1><?php echo HEADING_TITLE. ' ' . GOOGLE_PRODUCTS_VERSION; ?></h1>
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
 if (!$error_file){ ?>
<div>
    <?php
    if (GOOGLE_PRODUCTS_DEBUG === 'true') {
        //outputs line details for each product processed
        $popup_width = 1100;
        $popup_height = 750;
    } else {
        //summary only
        $popup_width = 400;
        $popup_height = 400;
    } ?>
    <?php // The form action executes the script in the shop root in a popup window, and also reloads the admin page after a delay so it can find/list the newly-created file in the table ?>
      <form method="get" action="<?php echo HTTP_SERVER . DIR_WS_CATALOG . FILENAME_GOOGLEFROOGLE . '.php'; ?>" name="google" target="googlefeed" onsubmit="window.open('', 'googlefeed', 'resizable=1, statusbar=5, width=<?php echo $popup_width; ?>, height=<?php echo $popup_height; ?>, top=5, left=25, scrollbars=yes');
          setTimeout('location.reload();', 5000);">
        <?php // the code only supports products, so the selections previously offered below are unused/commented out ?>
        <!--<label for="feed"><?php //echo TEXT_FEED_TYPE ?></label>
        <select name="feed" id="feed">
          <option value="fy_un_tp"><?php //echo TEXT_FEED_PRODUCTS ?></option>
          <option value="fy_un_td"><?php //echo TEXT_FEED_DOCUMENTS ?></option>
          <option value="fy_un_tn"><?php //echo TEXT_FEED_NEWS ?></option>
        </select>
        <br>-->
        <?php echo zen_draw_hidden_field('feed', 'fy_un_tp'); //this replaces the options above ?>
        <label for="limit"><?php echo TEXT_ENTRY_LIMIT; ?></label>
        <?php echo zen_draw_input_field('limit', $limit, 'class="limiters" id="limit"');
        $total_products = $db->Execute('SELECT COUNT(*) FROM ' . TABLE_PRODUCTS);
        echo '(total ' . $total_products->fields['COUNT(*)'] . ')';
        ?>
        <br>
        <label for="start"><?php echo TEXT_ENTRY_OFFSET; ?></label>
        <?php echo zen_draw_input_field('start', $start, 'class="limiters" id="start"'); ?>
        <br>
<?php //todo use this to change default and remove configuration switch ?>
          <div title="<?php echo TEXT_FEED_SORT_TITLE . GOOGLE_PRODUCTS_FEED_SORT; ?>"><b><?php echo TEXT_FEED_ORDER_BY; ?></b>
              <label><?php echo TEXT_FEED_ID . zen_draw_radio_field('feed_sort', 'id', GOOGLE_PRODUCTS_FEED_SORT === 'ID'); ?></label>
              <label><?php echo TEXT_FEED_MODEL . zen_draw_radio_field('feed_sort', 'model', GOOGLE_PRODUCTS_FEED_SORT === 'Model'); ?></label>
              <label><?php echo TEXT_FEED_NAME . zen_draw_radio_field('feed_sort', 'name', GOOGLE_PRODUCTS_FEED_SORT === 'Name'); ?></label>
          </div>
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_GO; ?></button>
          <input type="hidden" name="key" value="<?php echo GOOGLE_PRODUCTS_KEY; ?>">
      </form>
</div>
          <hr>
          <div>
      <h2><?php echo TEXT_FEED_FILES; ?></h2>
              <table id="googleFiles">
                  <tr>
                      <th><?php echo TEXT_DATE_CREATED ?></th>
                      <th><?php echo TEXT_DOWNLOAD_LINK ?></th>
                      <th><?php echo TEXT_ACTION ?></th>
                  </tr>

                  <?php
                  //check output file location permissions
                        if ($handle = opendir(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY)) {
                              while (($file = readdir($handle)) !== false) {
                                  //echo "filename: $file : filetype: " . filetype($handle . $file) . "\n<br>";
                                  if ($file !== '.' && $file !== '..' && $file !== 'index.html') {
                                      $filetime = filemtime(DIR_FS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $file);
                                      $date = date('j/m/Y H:i:s', $filetime);
                                      ?>
                                      <tr>
                                          <td><?php echo $date; ?></td>
                                          <td><a href="<?php echo HTTP_SERVER . DIR_WS_CATALOG . GOOGLE_PRODUCTS_DIRECTORY . $file; ?>" target="_blank"><?php echo $file; ?></a></td>
                                          <td>
                                              <a href="<?php echo zen_href_link(FILENAME_GOOGLEFROOGLE, 'file=' . $file . '&action=delete'); ?>"><?php echo IMAGE_DELETE; ?></a>
                                              <a href="#" onclick="window.open('<?php echo HTTP_SERVER . DIR_WS_CATALOG . FILENAME_GOOGLEFROOGLE; ?>.php?feed=fn_uy_tp&upload_file=<?php echo $file; ?>&key=<?php echo GOOGLE_PRODUCTS_KEY; ?>', 'googlefrooglefeed', 'resizable=1, statusbar=5, width=600, height=400, top=5, left=50, scrollbars=yes'); return false;"><?php echo IMAGE_UPLOAD; ?></a>
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
     <?php } ?>
          <hr>
    <div>
        <img src="images/google_merchant_center_logo.gif" width="174" height="80" alt="Google Merchant Center logo">
        <?php echo TEXT_GOOGLE_PRODUCTS_INFO; ?>
    </div>
</div>
<!-- body_eof //-->
    <!-- body_text_eof //-->
</div>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>