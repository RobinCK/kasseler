<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");
if(isset($_SESSION['admin'])) unset($_SESSION['admin']);
if(isset($_SESSION['cache_session_user'])) unset($_SESSION['cache_session_user']);
global $adminfile, $main;
setcookies("", $main->config['admin_cookies'], 1);
$main->db->sql_query("UPDATE ".SESSIONS." SET is_admin='0' WHERE sid='".session_id()."'");
redirect($adminfile);
?>