<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

//Фильтрация GET массива
foreach($_GET as $name=>$value){
    if(!is_array($value)){
        if(preg_match('/(\.\.\/|[\'"]|<|>|\(+)/s', $value) OR preg_match('/(\.\.\/|[\'"]|<|>|\(+)/s', $name)) kr_http_ereor_logs("403");
        $_GET[$name] = strip_tags($value);
    }
}

function kr_tracert_debug(){
   $errMsg="";
   $c = 0; //Текущий уровень вложенности кода
   $level = 0; //Уровень, выше которого формируется отчет
   $backtrace = debug_backtrace(); //Получение списка вызова функций до текущего места
   if(count($backtrace)>$level) {
      foreach ($backtrace as $track_point) {
         if($c>$level) { //Формирование отчета
            $errMsg .= "\n".'    \---> ';
            $errMsg .= isset($track_point['class']) ? $track_point['class'] : '';
            $errMsg .= isset($track_point['type']) ? $track_point['type'] : '';
            $errMsg .= isset($track_point['function']) ? $track_point['function'].'()' : '';
            $errMsg .= isset($track_point['file']) ? ' called at ['.$track_point['file'] : '';
            $errMsg .= isset($track_point['line']) ? ' line '.$track_point['line'].']' : '';
         }
         $c++;
      }
   }
   return $errMsg;
}

function kr_error_php_handler($errno, $errstr, $errfile, $errline){
global $config, $lang_dbg;
    if(!empty($errno)) require_once "includes/language/{$config['language']}/debugging.php";
    if(preg_match('/mysql\.class\.php/s', $errfile)) return false;
    $text_error=" {$errstr} in \n {$errfile} \n on line {$errline}";
    if($config['log_debugging_php']==ENABLED){
        file_write("uploads/logs/php_logs.log", kr_date("Y-m-d H:i:s",kr_time())." | {$errstr} | in {$errfile} | on line {$errline} ||\n".kr_tracert_debug()."\n", "a");
    }
    if($config['mode_debugging_php']==ENABLED){
        switch($errno){
            case E_USER_ERROR: error_page(is_admin()?$text_error:$lang_dbg['error_php']); break;
            default: error_page(is_admin()?$text_error:$lang_dbg['error_php']); break;
        }
    }
    return true;
}

$old_error_handler = set_error_handler('kr_error_php_handler');

function kr_http_ereor_logs($int){
global $config, $lang_dbg;
    require_once "includes/language/{$config['language']}/debugging.php";
    switch($int){
        case "400": $error = array("Bad Request",           $lang_dbg['http_400']); break;
        case "401": $error = array("Unauthorized",          $lang_dbg['http_401']); break;
        case "403": $error = array("Forbidden",             $lang_dbg['http_403']); break;
        case "404": $error = array("Not Found",             $lang_dbg['http_404']); break;
        case "500": $error = array("Internal Server Error", $lang_dbg['http_500']); break;
        case "503": $error = array("Service Unavailable",   $lang_dbg['http_503']); break;
        default :   $error = array("Unknown Error",         $lang_dbg['unknown_error']); break;
    }    
    if($config['log_debugging_http']==ENABLED){
        file_write("uploads/logs/http_logs.log", "".gmdate("Y-m-d H:i:s")." | {$error[0]} | http://".get_env('HTTP_HOST').get_env('REQUEST_URI')." | ".(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'null').' | '.$_SERVER["REMOTE_ADDR"]."||\n".kr_tracert_debug()."\n", "a");
    }
    if($config['mode_debugging_http']==ENABLED){
        if(!isset($_SERVER['HTTP_AJAX_ENGINE']) AND !isset($_GET['ajax']) AND !isset($_POST['ajax'])) error_page($lang_dbg['error_http'].$int.": ".$error[0]);
        else echo "<script language='javascript'>alert('".$lang_dbg['error_http'].$int.": ".$error[0]."');</script>";
    } else {
        switch($int){
            case 404: header('HTTP/1.1 404 Not Found'); break;
            case 403: header('HTTP/1.1 403 Forbidden'); break;
            case 500: header('HTTP/1.1 500 Internal Server Error'); break;
        }
        //header ('HTTP/1.1 301 Moved Permanently');
        //header ('Location: index.php');
        exit;
    }   
}

function kr_sql_erorr_logs($code, $msg, $query){
global $config, $lang_dbg;
    require_once "includes/language/{$config['language']}/debugging.php";   
    if($config['log_debugging_sql']==ENABLED){
        file_write("uploads/logs/sql_logs.log", gmdate("Y-m-d H:i:s")." :|: {$code} :|: {$msg} :|: {$query}||\n", "a");
    }
    if ($config['mode_debugging_sql'] == ENABLED) error_page($lang_dbg['error_sql']);
}

function error_page($title) {
global $config, $lang_dbg;   //85   
    if(!isset($_GET['ajax']) AND !isset($_POST['ajax'])) require_once "includes/nocache.php";
    if(empty($lang_dbg)) require_once "includes/language/{$config['language']}/debugging.php";
    $kill = @ob_get_contents();
    if(mb_strlen($kill)>0) @ob_end_clean();
    $content = preg_replace(
        array(
            '/\{error.TITLE\}/i', 
            '/\{error.SEE\}/i'
        ), 
        array(
            $title,
            str_replace('{URL}', "http://{$_SERVER['HTTP_HOST']}/", $lang_dbg['see_log'])
        ), 
        file_get_contents("includes/template/error_page.tpl")
    );
die("<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>{$config['home_title']} - ".strip_tags(str_replace("&#33;", "!", $title))."</title>
<meta http-equiv='content-type' content='text/html; charset={$config['charset']}' />
<meta name='generator' content='{$config['cms_version']}' />
<meta name='Cache-Control' content='no-cache' />
<meta http-equiv='Expires' content='0' />
<base href='http://{$_SERVER['HTTP_HOST']}/' />
<meta name='copyright' content='Copyright (c){$config['cms_version']}' />"."
<link rel='stylesheet' type='text/css' href='includes/template/css/errors.css' />
<script type='text/javascript' src='includes/javascript/jquery/jquery.js'></script>
<script type='text/javascript' src='includes/javascript/kr_ajax.js'></script>
</head>\n
<body class='page_bg'>\n
<script type='text/javascript' src='includes/javascript/function.js'></script>
{$content}
</body>\n</html>");
}

function debug_var($text, $at="a"){
    $file = fopen('uploads/logs/debugging.log', $at);
    fputs ($file, "============================================\ndate: ".date("Y-m-d H:i:s")."\n============================================\n".$text."\n============================================\n\n");
    fclose ($file);
}

/**
* Функция записи в файл
* 
* @param string $file_link
* @param mixed $text
* @param string $at
* @return void
*/
function file_write($file_link, $text, $at="w"){
    $file = fopen($file_link, $at);
    fputs ($file, $text);
    fclose ($file);
}
?>
