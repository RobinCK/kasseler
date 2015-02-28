<?php
/***
* Основной пользовательский файл системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource index.php
* @version 2.0
*/
define('KASSELERCMS', true);
define('E__SESSION__', true);
define('E__TEMPLATES__', true);
define('E__DATABASECONF___', true);
define('E__DATABASE__', true);
define('E__CORE__', true);
define('E__PLUGINS__', true);
require_once "includes/function/init.php";

global $version_sys, $license_sys, $main, $revision;
if(isset($_GET['system'])){ 
    main::required("includes/nocache.php");
    define('SYSTEM_FUNC', true);
    switch($_GET['system']){
        case 'version': echo "VERSION SYSTEM: ".$version_sys." "; break;
        case 'license': echo "LICENSE SYSTEM: ".$license_sys." "; break;
        case 'key': echo "LICENSE KEY: ".$main->config['licence_file']." "; break;
        case 'r': 
            $host = get_host_name();
            $host_nowww = substr($host, 0, 4)=='www.' ? substr($host, 4, strlen($host)) : $host;
            if($host_nowww=='kasseler-cms.net') {
                $last = file_get_contents('http://build.kasseler-cms.net/last_ver');
                $last = substr($last, 0, strlen($last)-2);
                echo "REVISION SYSTEM: {$last} "; 
            } else echo "REVISION SYSTEM: {$revision} ";
        break;
        case 'myip': echo "YOUR IP ADDRES: ".$main->ip; break;
        case 'sitemap': main::required("includes/function/sitemap.php");  break;
    }
    exit;
}

if(!isset($_GET['ajaxed'])){
    if(!isset($_GET['blockfile'])){
        $contents = $template->tpl_create(true);
        $contents .= (is_admin() OR is_moder()) ? $tpl_create->generate_info_insert($geterate_time->stop(), $main->db->total_time_db, $main->db->num_queries, mb_strlen($contents), mb_strlen(gzcompress($contents, $main->config['gzlevel']))) : "";
        main::required("includes/nocache.php");
        gz(replace_link(replace_content($contents)));
    } elseif(file_exists("blocks/{$_GET['blockfile']}")) {
        if(preg_match('/^([a-z0-9\-_]*)$/i', str_replace('.php', '', $_GET['blockfile']))) main::required("blocks/{$_GET['blockfile']}");
        else kr_http_ereor_logs(403);
    }
} else {
    if(!isset($_SERVER["HTTP_REFERER"]) OR empty($_SERVER["HTTP_REFERER"])) {
        header("Location: http://".get_host_name()."");
        exit;
    } else main::required("includes/function/ajax.php");
}


//test
?>
