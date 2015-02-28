<?php
 /**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $lang, $config, $title, $license_sys;

$title = $lang['systeminfo'];
echo "<b>{$lang['licence']}</b>: <a href='http://www.kasseler-cms.net/' target='_blank'><font color='red'>KASSELER / {$license_sys}</font></a><br />
<b>{$lang['status_site']}</b>: ".(($config['disable_site']!=ENABLED) ? "<font color='green'>{$lang['mode_on']}</font>" : "<font color='red'>{$lang['mode_off']}</font>")."<br />
<b>{$lang['rewrite_status']}</b>: ".(($config['rewrite']==ENABLED) ? "<font color='green'>{$lang['mode_on']}</font>" : "<font color='red'>{$lang['mode_off']}</font>")."<br />
<b>{$lang['gzip']}</b>: ".(($config['gz']==ENABLED) ? "<font color='green'>{$lang['mode_on']}</font>" : "<font color='red'>{$lang['mode_off']}</font>")."<br />
<b>{$lang['gzip_level']}</b>: {$config['gzlevel']}<br />
<b>{$lang['def_template']}</b>: ".(isset($lang[$config['template']]) ? $lang[$config['template']] : $config['template'])."<br />
<b>{$lang['language']}</b>: ".(isset($lang[$config['language']]) ? $lang[$config['language']] : $config['language'])."<br />
<b>Debugging PHP</b>: ".(($config['mode_debugging_php']==ENABLED) ? "<font color='green'>{$lang['mode_on']}</font>" : "<font color='red'>{$lang['mode_off']}</font>")."<br />
<b>Debugging SQL</b>: ".(($config['mode_debugging_sql']==ENABLED) ? "<font color='green'>{$lang['mode_on']}</font>" : "<font color='red'>{$lang['mode_off']}</font>")."<br />
<b>Debugging HTTP</b>: ".(($config['mode_debugging_http']==ENABLED) ? "<font color='green'>{$lang['mode_on']}</font>" : "<font color='red'>{$lang['mode_off']}</font>")."<br />";
?>