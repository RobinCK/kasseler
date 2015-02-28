<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");
global $add_style;
ob_start();
phpinfo();
$content = ob_get_contents(); ob_end_clean();
$content = preg_replace('/<\!DOCTYPE(.*?)<body>/si', '', $content);
$content = preg_replace('/<\/body>(.*?)<\/html>/si', '', $content);
$content = preg_replace('/<img(.+?)>/si', '', $content);
$content = preg_replace('/<a(.+?)>(.*?)<\/a>/si', "\\2", $content);
$add_style='
<style type="text/css">
pre {margin: 0px; font-family: monospace;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center !important; }
.p {text-align: left;}
.e {background-color: #CCE2EF; font-weight: bold; color: #000000;}
.h {background-color: #FAFAFA; border: 1px solid #F8F8F8;}
.v {background-color: #cccccc; color: #000000;}
.vr {background-color: #cccccc; text-align: right; color: #000000;}
.phpinfo table {border-collapse: collapse;}
.phpinfo table tr td {font-size: 13px;}
.phpinfo table tr th {font-size: 13px;}
.phpinfo td, th {border: 1px solid #FAFAFA; font-size: 75%; vertical-align: baseline;}
.phpinfo th {border: 1px solid #F9F9F9; background: #656565; color: #FFFFFF;}
</style>';
echo "<div class='phpinfo'>".$content."</div>";
?>