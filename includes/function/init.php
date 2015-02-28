<?php
/**
* Файл инициализации системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/init.php
* @version 2.0
*/
if(!defined("KASSELERCMS") AND !defined("ADMIN_FILE")) die("Access is limited");

define("BLOCK_FILE", true);
define("FUNC_FILE", true);

define('SAFE_MODE', ini_get('safe_mode')=='1'?true:false);

//Класс секундомера
class timer{
    var $start_time;
    
    function timer(){
        $time = 0;
        preg_match('/(.*)\s(.*)/', microtime(), $time);
        $this->start_time = $time[2] + $time[1];
    }

    function stop(){
        $time = 0;
        preg_match('/(.*)\s(.*)/', microtime(), $time);
        return round(($time[2] + $time[1])-$this->start_time, 4);
    }  
}

//session_cache_limiter("private");
if(isset($_POST['PHPSESSID'])) session_id($_POST['PHPSESSID']);
session_start();
if(!defined("INSTALLCMS")&&!defined("UPDATECMS")){
    if((!empty($_POST) AND !isset($_POST['secID'])) OR (!empty($_POST) AND isset($_POST['secID']) AND $_SESSION['form_checked']!=$_POST['secID'])) {
        $loginpost = (($_GET['module']=='account' && $_GET['do']=='sign')) || ($_GET['mod_rewrite'] == 'account/sign.html');
        if(!$loginpost){
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit;
        } else {
            if(isset($_SESSION['form_checked'])) unset($_SESSION['form_checked']);
        }
    };
}

if(function_exists('date_default_timezone_set')) date_default_timezone_set("Europe/London");

function load_includes($pattern, $path){
    if(function_exists('hook_check') AND hook_check(__FUNCTION__)) return hook();
    $files=array();
    if(($tmpHandle = opendir($path))){
        while(false !== ($tmpFile = readdir($tmpHandle))) if(!is_dir($path.$tmpFile) AND preg_match("#$pattern#i",$tmpFile)) $files[]=$path.$tmpFile;
        closedir($tmpHandle);
    }
    return $files;
}

/**
* Функция возвращает текущий HTTP HOST
* 
* @return string
*/
function get_host_name($whith_port=true){
    if(function_exists('hook_check') AND hook_check(__FUNCTION__)) return hook();
    static $_host_name;
    if($_host_name) return $_host_name;
    $_host_name = get_env('HTTP_HOST');
    if(!$_host_name) $_host_name=get_env('SERVER_NAME');
    if(!$whith_port){ 
        if(mb_strpos($_host_name,':')) list($_host_name)=explode(':',$_host_name);
    }
    return preg_replace('/[^a-z0-9-:._]/i', '', $_host_name);
}

/**
 * Функция создания cookies
 *
 * @param string $value
 * @param string $name
 * @param int    $time
 *
 * @return mixed
 */
function setcookies($value, $name, $time=0){
global $config;
    if(hook_check(__FUNCTION__)) return hook();
    $time = ($time==0) ? time() + 60*60*24*intval($config['time_of_life_session']) : $time;
    $time = ($time==1) ? 0 : $time;
    setcookie($name, $value, $time, "/");
}

global $config, $is_mb_string, $database, $revision, $template, $tpl_main, $hooks;
require_once 'includes/config/config.php';

if(defined('E__DATABASECONF___')) require_once 'includes/config/configdb.php';
if(defined('E__DATABASE__')) if(file_exists("includes/classes/{$database['type']}.class.php")) require_once "includes/classes/{$database['type']}.class.php";

$is_mb_string = function_exists('mb_strpos') ? true : false;
header("Content-type: text/html; charset={$config['charset']}");

//Подключение файлов define
foreach(load_includes('.*?\.php', 'includes/define/') as $filename) require $filename;

//Подключение файлов конфигурации 
foreach(load_includes('config_.*?\.php', 'includes/config/') as $filename) require $filename;
require "includes/function/ipblock.php";

require "includes/function/debugging.php";

global $database;
$adminfile = $config['adminfile'];
$magic_quotes = true;
$redirect = "";
$parse_ref = (isset($_SERVER['HTTP_REFERER']) AND !empty($_SERVER['HTTP_REFERER'])) ? parse_url($_SERVER['HTTP_REFERER']) : array();

//Подключение функций хуков
require "includes/function/hooks.php";

//Подключение основных классов системы
require "includes/classes/main.class.php";

if($is_mb_string==false) $main->init_function('mb_string');                                        //Если нет подключен mb_string подключаем собственный
else mb_internal_encoding('UTF-8');                                                                //Устанавливаем кодировку для mb_string

if(isset($_GET['mod_rewrite'])) $main->parse_rewrite();
$_arr_home_modules = explode(',', $config['default_module']);
$main->module = $module_name = (isset($_GET['module'])) ? $_GET['module'] : $_arr_home_modules[0];

// блок дополнительного управления сесиями
$modify_session=array('close_session','update_session');
foreach ($modify_session as $key => $value) {if(isset($_SESSION[$value])) {main::init_function('session_tools'); break;}}

if(!empty($_SESSION['close_session'])){
   unset($_SESSION['close_session']);
   session_logut();
}

if(!empty($_SESSION['update_session'])){
   unset($_SESSION['update_session']);
   unset($_SESSION['cache_session_user']);
   unset($_SESSION['forum_access']);
}

//Загрузка хуков
foreach(load_includes('.*?\.php', 'hooks/autoload/') as $filename) require_once $filename;

//Подключение функций системы
if(defined('E__CORE__')){
    main::init_function('replace', 'gets', 'bool', 'kernel', 'forms', 'captcha', 'add_meta_value');
}

//Check mobile
main::init_class('detect');
$MOBILE = new Mobile_Detect();
if($MOBILE->isMobile()) $main->is_moile = true;

if(isset($_GET['id']) AND !preg_match('/[a-zа-я_\-0-9\.]+/is', $_GET['id'])) kr_http_ereor_logs("403");
if(defined('E__SESSION__')){
    main::init_class('session');
    $session = new session($config['interval_session_update']);
}

//other_function
if(!function_exists('glob')) main::init_function('glob');

if(function_exists('get_magic_quotes_gpc')){                                                       //Проверяем наличие функции
    if(!get_magic_quotes_gpc()){                                                                   //Проверяем включен ли magic_quotes 
        function mq($value){                                                                       //Создаем функция экранирования
            if(is_array($value)) $value = array_map('mq', $value);
            elseif(!empty($value) AND is_string($value)) $value = addslashes($value);
            return $value;
        }
        $_GET     = isset($_GET) ? mq($_GET) : array();                                  //Экранируем $_GET масив
        $_POST    = isset($_POST) ? mq($_POST) : array();                                //Экранируем $_POST масив
        $_COOKIE  = isset($_COOKIE) ? mq($_COOKIE) : array();                            //Экранируем $_COOKIE масив
        $_REQUEST = isset($_REQUEST) ? mq($_REQUEST) : array();                          //Экранируем $_REQUEST масив
    }
}

global $lang, $db, $main, $language, $base; 

function remove_port($host_value){
    $out = "";
    if(preg_match("/([^:]+)/", $host_value, $out)) return $out[1];
    return $host_value; 
}

$httphost = remove_port(get_env('HTTP_HOST'));
if(!empty($parse_ref)) $parse_host=remove_port($parse_ref['host']);

if(function_exists('load_tpl')) load_tpl();
$geterate_time = new timer;
$version_sys = "2";
$revision = '1248';
$license_sys = "FULL";

$modules_sitemap = array(
    'news'     => NEWS,
    'files'    => FILES,
    'media'    => MEDIA,
    'pages'    => PAGES,
    'shop'     => SHOP,
    'jokes'    => '',
    'top_site' => '',
    'faq'      => '',
    'voting'   => '',
);

$copyright_file = "<?php\n/**********************************************/\n/* Kasseler CMS: Content Management System    */\n/**********************************************/\n/*                                            */\n/* Copyright (c)2007-".date('Y')." by Igor Ognichenko  */\n/* http://www.kasseler-cms.net/               */\n/*                                            */\n/**********************************************/\n\nif (!defined('FUNC_FILE')) die('Access is limited');\n\n";

//Проверка и создание директорий для временных файлов
//if(!file_exists("uploads/cache/")) mkdir("uploads/cache/", 0777);
//if(!file_exists("uploads/tmpfiles/")) mkdir("uploads/tmpfiles/", 0777);
  
@ini_set('url_rewriter.tags', '');
@ini_set('arg_separator.output', '&amp;');
@ini_set('register_globals', 'off');
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_NOTICE);
@ini_set("safe_mode", false);

if(isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']) || isset($_SERVER['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_ENV['GLOBALS'])) die('GLOBALS overwrite attempt');
if(count($_REQUEST) > 1000) die('possible exploit');
foreach ($GLOBALS as $key => $dummy) if(is_numeric($key)) die('numeric key detected');

//Отключение register_globals on
if (ini_get('register_globals') == 1) foreach($_REQUEST as $key => $var) unset($GLOBALS[$key]);

foreach (array('PHP_SELF', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_AUTHORIZATION') as $current) {
    if(get_env($current) AND false === mb_strpos(get_env($current), '<')) $$current = get_env($current);
    elseif(!isset($$current) OR false !== mb_strpos($$current, '<')) $$current = '';  // очистка XSS
}
unset($current);

if($config['filrer_referer']==ENABLED AND isset($_POST) AND count($_POST)>0 AND !empty($parse_ref) AND $httphost!=$parse_host) redirect(get_env('HTTP_REFERER')); 

function filtered_request_uri(){
    if(hook_check(__FUNCTION__)) return hook();
    if(strlen($_SERVER['REQUEST_URI']) > 255 || stripos($_SERVER['REQUEST_URI'], "eval(") || stripos($_SERVER['REQUEST_URI'], "CONCAT") || stripos($_SERVER['REQUEST_URI'], "UNION+SELECT") || stripos($_SERVER['REQUEST_URI'], "base64")) {
        header("HTTP/1.1 414 Request-URI Too Long");
        header("Status: 414 Request-URI Too Long");
        header("Connection: Close");
        exit;
    }
}

filtered_request_uri();

if(isset($_GET['http_error'])) kr_http_ereor_logs($_GET['http_error']);
//Определение языкового файла
if(function_exists('get_language')) get_language();

if($config['disable_site']==ENABLED AND !is_support() AND !defined("ADMIN_FILE")){
  if(is_home())  die(stripslashes($config['disable_description']));
  else redirect($config['http_home_url']);
}
//Подключение "хлебных крошек" 
main::init_class('breadcrumb');
$main->init();

//Подключаем плагины
if(defined('E__PLUGINS__')){
    foreach($hooks as $hook => $k) {
        if($k['type']=='plugin'  AND $k['status']==ENABLED AND $k['install']==true AND file_exists("hooks/{$hook}/{$hook}.plugin.php"))  require "hooks/{$hook}/{$hook}.plugin.php";
        if($k['type']=='file' AND $k['status']==ENABLED) $main->conf['hooks'][$k['replace_file']] = $k;
        if($k['type']=='hook' AND $k['status']==ENABLED) if(file_exists('hooks/'.$k['file'])) main::required('hooks/'.$k['file']);
    }
}

if(!empty($main->user['user_folder'])) define("USER_FOLDER", "filedata-".$main->user['user_folder']);
load_tpl();   //?

function post_init(){
    if(hook_check(__FUNCTION__)) return hook();    
}

post_init();
function recalc_config_init(){
   global $main, $moduleinit;
   if(hook_check(__FUNCTION__)) return hook();
   if(!empty($moduleinit)){
      if(!empty($moduleinit['lang'])){
         foreach ($moduleinit['lang'] as $key => $value) {
            if(is_array($value)){
               if(isset($value[$main->language])) $main->lang[$key]=$value[$main->language];
            } else  $main->lang[$key]=$value;
         }
      }
   }
}

recalc_config_init();
if(defined('E__TEMPLATES__')){
    main::inited('class.templates', 'function.templates');
    $template = new template();
    if(!defined("ENGINE") AND !isset($_GET['blockfile'])){
        if(!is_ajax()){
            if(!defined("ADMIN_FILE")) $template->get_tpl('index', 'index');
            else {
                if(isset($_SESSION['admin'])) {
                    $template->path = TEMPLATE_PATH.'admin/';
                    $template->get_tpl('index', 'index');
                } else {
                    $template->path = TEMPLATE_PATH.'admin/';
                    $template->get_tpl('login', 'index');
                }
            }
        }
        if(!isset($_GET['ajaxed'])){
            if(!defined("ADMIN_FILE")){
                main::init_class('usertpl');
                $tpl_create = new user_tpl;
            } else {
                main::init_class('admintpl');
                $tpl_create = new admin_tpl;
            }

            if(!is_ajax()) main::add2script("includes/language/{$language}/lang.js");
            $tpl_create->tpl_creates();
        } else $tpl_create = new tpl_create;
        if(!is_ajax() AND !defined("INSTALLCMS") AND !defined("ADMIN_FILE") AND is_object($db)) $template->set_tpl(array('time' => preg_replace(array("#{query}#", "#{query_time}#", "#{time}#"), array($db->num_queries, $db->total_time_db, $geterate_time->stop()), $lang['page_generate'])), 'index');
    } 
}
