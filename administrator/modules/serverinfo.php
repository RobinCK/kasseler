<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

function gd_version(){
static $gd_version_number = null;
    if ($gd_version_number === null) {
        ob_start();
        phpinfo(8);
        $module_info = ob_get_contents(); ob_end_clean();
        $matches = "";
        if(preg_match('/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i', $module_info, $matches)) $gdversion_h = $matches[1];
        else $gdversion_h = 0;
    } 
    return $gdversion_h;
} 

function db_version() {
global $db;
    list($dbversion) = $db->sql_fetchrow($db->sql_query("SELECT VERSION()"));
    $dbversion_tmp = explode("-", $dbversion);
return $dbversion_tmp[0];
}

function getcfg($varname) {
global $lang;
  $result = get_cfg_var($varname);
  if ($result == 0) return "<span style='color: red;'>{$lang['mode_off']}</span>";
  elseif ($result == 1) return "<span style='color: green;'>{$lang['mode_on']}</span>";
  else return $result;
}

global $lang, $config, $database, $title, $adminfile;
$title = $lang['serverinfo'];
$safemode = (@ini_get('safe_mode') == 1) ? "<font color='red'>{$lang['mode_on']}</font>" : "<font color='green'>{$lang['mode_off']}</font>";
$maxupload = get_size(str_replace(array('M','m'), '', @ini_get('upload_max_filesize'))*1024*1024);  
$maxpost = get_size(str_replace(array('M','m'), '', @ini_get('post_max_size'))*1024*1024); 
$dis_func = get_cfg_var('disable_functions');
define('IS_PHPINFO', (!preg_match("/phpinfo/", $dis_func)) ? 1 : 0 ); 
if(function_exists('apache_get_modules')) {
    if(array_search('mod_rewrite',apache_get_modules())) $mod_rewrite = "<span style='color: green;'>{$lang['mode_on']}</span>";
    else $mod_rewrite = "<span style='color: red;'>{$lang['mode_off']}</span>";
} else $mod_rewrite = "<span style='color: #BABABA;'>{$lang['undefined']}</span>";

echo "
<b>OS</b>: ".php_uname("s")." ".php_uname("r")."<br />
<b>Server IP</b>: ".get_env('SERVER_ADDR')."<br />
<b>Server Web Port</b>: ".get_env('SERVER_PORT')."<br />
<b>PHP</b>: ".phpversion()."<br />
<b>PHP run mode</b>: ".mb_strtoupper(php_sapi_name())."<br />
<b>PHPINFO</b>: ".(IS_PHPINFO ? "<a href='{$adminfile}?module=phpinfo'>{$lang['mode_on']}</a>" : "<span style='color: red;'>{$lang['mode_off']}</span>")."<br />
<b>GD</b>: ".gd_version()."<br />
<b>DB</b>: ".db_version()." <b>".mb_strtoupper($database['type'])."</b><br />
<b>Mod Rewrite</b>: {$mod_rewrite}<br />
<b>Magic Quotes gpc</b>: ".getcfg('magic_quotes_gpc')."<br />
<b>Display errors</b>: ".getcfg('display_errors')."<br />
<b>Safe Mode</b>: {$safemode}<br />
<b>Execution Time</b>: ".ini_get('max_execution_time')."<br />
<b>Post Size</b>: {$maxpost}<br />
<b>Upload file size</b>: {$maxupload}<br />
<b>Memory Limit</b>: ".getcfg('memory_limit')."<br />";
?>