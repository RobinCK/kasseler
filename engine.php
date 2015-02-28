<?php
/**
* Файл дополнительных функция системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource engine.php
* @version 2.0
*/  
define('KASSELERCMS', true);
define('ENGINE', true);
define('E__CORE__', true);
define('E__PLUGINS__', true);

require_once "includes/function/init.php";

if(preg_match('/(.*?)engine.php\?do=redirect(.*?)/is', get_env('REQUEST_URI'))) $_GET['do']='redirect';

function download(){
global $config;      
    if(preg_match('/\.\.\//i', $_GET['file']) OR preg_match('/\.\.\\\/i', $_GET['file'])) kr_http_ereor_logs(403);
    main::required("includes/classes/download.class.php");
    $file = "uploads/".$_GET['file'];
    $download = new file_download($file, ($config['download_resume']==ENABLED)?1:0, $config['download_speed']);
    $download->download();
}   

function attach(){ 
global $config, $main;        
    if(preg_match('/\.\.\//i', $_GET['file']) OR !preg_match('/uploads(.*?)/i', $_GET['file'])) kr_http_ereor_logs(403);
    main::required("includes/classes/download.class.php");
    if($_GET['file'][0]=='/') $_GET['file'] = mb_substr($_GET['file'], 1, mb_strlen($_GET['file']));
    $download = new file_download($_GET['file'], ($config['download_resume']==ENABLED)?1:0, $config['download_speed']);
    $download->download();
}   

function tourl(){
global $main, $tpl_create, $security;
    main::inited('function.templates');
    $tpl_create = new tpl_create;
    if(!isset($_GET['url'])) redirect("http://".get_host_name());
    $site = parse_url($_GET['url']);
    if(empty($site['host']))  redirect('http://'.get_env('HTTP_HOST'));
    $goto = preg_replace('/www\.(.*?)/', '\\1', $site['host']);
    if($goto==preg_replace('/www\.(.*?)/', '\\1', get_env('HTTP_HOST'))) redirect($_GET['url']);
    if(in_array($goto, explode(', ', $security['allowed_domain']))) $redirect = true;
    elseif(in_array($goto, explode(', ', $security['negative_domain']))) $redirect = false;
    else $redirect = null;
    if($redirect == true) redirect($_GET['url']);
    $text = preg_replace(
        array('/\{SITE\}/is', '/\{URL2GO\}/is', '/\{SIET2GO\}/is', '/\{CANCEL\}/is', '/\{LINK2GO\}/is'), 
        array("<b>{$main->config['home_title']}</b>", "<b>{$_GET['url']}</b>", "<b>{$site['host']}</b>", "<a href='javascript:window.close()' title='{$main->lang['cancel']}'>{$main->lang['cancel2']}</a>", "<a href='{$_GET['url']}'>{$_GET['url']}</a>"),
        $redirect!==false?$main->lang['redirect_warn']:$main->lang['redirect_stoped']
    );
    $content = preg_replace(        
        array('/\$title/is', '/\$link/is', '/\$meta/is', '/\$content/is', '/\$redirect/is'), 
        array("{$main->config['home_title']} {$main->config['separator']} {$main->lang['redirect_page']}", "<link rel='stylesheet' type='text/css' href='includes/template/css/redirect.css' />", $tpl_create->meta_insert(), $text, $main->lang['redirect_page']), 
        file_get_contents('includes/template/redirect.tpl')
    );
    main::required("includes/nocache.php");
    die($content);    
}

if(isset($_GET['do'])){
    switch($_GET['do']){
        case "download": download(); break;
        case "attach": attach(); break;
        case "redirect": tourl(); break;
    }
}
?>