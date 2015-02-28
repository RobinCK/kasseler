<?php
/**
* Файл админ панели
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource admin.php
* @version 2.0
*/
define('KASSELERCMS', true);
define("ADMIN_FILE", true);
define('E__SESSION__', true);
define('E__TEMPLATES__', true);
define('E__DATABASECONF___', true);
define('E__DATABASE__', true);
define('E__CORE__', true);
define('E__PLUGINS__', true);
require_once "includes/function/init.php";

if(isset($_SESSION['cache_session_user'])) unset($_SESSION['cache_session_user']);
if(!is_ajax()){
    $contents = $template->tpl_create(true);
    main::required("includes/nocache.php");
    gz($contents);
} else {
    if(isset($_GET['loadinfo'])) $tpl_create->block_info(); else $tpl_create->admin_content();
}
?>