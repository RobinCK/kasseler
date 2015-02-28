<?php
/**
* Ядро системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/kernel.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

function check_form(){
if(hook_check(__FUNCTION__)) return hook();
    if(!isset($_POST['formid']) OR $_POST['formid']!=$_SESSION['form_checked']) die('Form invalid');
}

/**
* Функция вывода страниц без оформления
* 
* @param string $content
* @param string $title
* @return void
*/
function page($content, $title){
global $config, $load_tpl;
if(hook_check(__FUNCTION__)) return hook();
require_once "includes/nocache.php";
 if($config['jquery']!='noload') {
    switch ($config['jquery']){
      case 'local':$jQuery = 'includes/javascript/jquery/jquery.js';break;
      case 'google':$jQuery ='http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js';break;
      case 'yandex':$jQuery ='http://yandex.st/jquery/1.7.0/jquery.min.js';break;
      case 'jquery':$jQuery ='http://code.jquery.com/jquery-1.7.min.js';break;
      $jQuery='includes/javascript/jquery/jquery.js';
    }
 } else $jQuery='includes/javascript/jquery/jquery.js';
die("<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>{$title}</title>
<meta http-equiv='content-type' content='text/html; charset={$config['charset']}' />
<meta name='author' content='{$config['home_title']}' />
<meta name='resource-type' content='document' />
<meta name='document-state' content='dynamic' />
<meta name='distribution' content='global' />
<meta name='robots' content='index, follow' />
<meta name='revisit-after' content='1 days' />
<meta name='rating' content='general' />
<meta name='generator' content='{$config['cms_version']}' />
<meta name='description' content='{$config['description']}' />
<meta name='keywords' content='{$config['keywords']}' />
<meta name='Cache-Control' content='no-cache' />
<meta http-equiv='Expires' content='0' />
<meta name='copyright' content='Copyright (c){$config['cms_version']}' />
<base href='http://".get_host_name()."/' />
<link rel='stylesheet' href='".TEMPLATE_PATH."{$load_tpl}/style.css' type='text/css' />
<link rel='stylesheet' href='includes/css/system.css' type='text/css' />
<link rel='shortcut icon' href='favicon.ico' type='image/x-icon' />
</head>
<body>
<script type='text/javascript' src='{$jQuery}'></script>
<script type='text/javascript' src='includes/javascript/kr_ajax.js'></script>
<script type='text/javascript' src='includes/javascript/function.js'></script>
<script type='text/javascript' src='".TEMPLATE_PATH."{$load_tpl}/jsconfig.js'></script>
{$content}
</body>
</html>");
}

/**
* Функция фильтрации полученных данных
* 
* @param string $str
* @param mixed $html
* @return string
*/
function kr_filter($str, $html=''){
global $config, $filter;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($str)) return '';
    if($html == TAGS) return strip_tags($str);
    if(!is_object($filter)) {
        $tags = array_unique(array_merge(explode(",", $config['htmlTags']), array()));
        $attr = array("abbr", "align", "alt", "axis", "background", "behavior", "bgcolor", "border", "bordercolor", 
            "bordercolordark", "bordercolorlight", "bottompadding", "cellpadding", "cellspacing", "char", 
            "charoff", "cite", "clear", "color", "cols", "direction", "face", "font-weight", "headers", 
            "height", "href", "hspace", "leftpadding", "loop", "noshade", "nowrap", "point-size", "rel", 
            "rev", "rightpadding", "rowspan", "rules", "scope", "scrollamount", "scrolldelay", "size", 
            "span", "src", "start", "summary", "target", "title", "toppadding", "type", "valign", 
            "value", "vspace", "width", "wrap","colspan","class");
        main::init_class('filter');
        $filter->init($tags, $attr, false, false, true);
    }
    return $filter->process($str);
}

/**
* Функция фильтрации массива по заданным ключевым полям
* 
* @param array $array_kay
* @param string $name
* @param mixed $type
*/
function filter_arr($array_kay, $name, $type=""){
    if(hook_check(__FUNCTION__)) return hook();
    if($name == POST) foreach ($array_kay as $value) $_POST[$value] = (isset($_POST[$value])) ? kr_filter($_POST[$value], $type) : "";
    elseif($name == GET) foreach ($array_kay as $value) $_GET[$value] = (isset($_GET[$value])) ? kr_filter($_GET[$value], $type) : "";
}

/**
* Функция даты по Гринвичу с учетом коррекции системы
* 
* @param string $format
* @return string
*/
function kr_date($format, $date=0){
global $config, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    $datev=empty($date)?gmdate('U'):(!isset($userinfo['user_gmt'])?$date:$date-(intval($userinfo['user_gmt'])*60*60));
    return date($format, $datev +(intval($config['GMT_correct'])*60*60));
}
/**
* Функция даты по Гринвичу с учетом коррекции системы 
* 
* @param string $format
* @param int $date
* @return string
*/
function kr_datecms($format, $date=0){
global $config, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    $datev=empty($date)?(gmdate('U') +(intval($config['GMT_correct'])*60*60)):$date;
    return date($format, $datev);
}

/**
* Функция пользовательского ввода даты по Гринвичу для записи в БД с учетом коррекции системы 
* 
* @param string $format
* @return string
*/
function kr_dateuser2db($format=''){
   global $config, $userinfo;
   if(hook_check(__FUNCTION__)) return hook();
   $format = (empty($format)) ? "Y-m-d H:i:s" : $format;
   if(isset($_POST['year'])){
      $datestr = "{$_POST['year']}-{$_POST['month']}-{$_POST['day']}".(isset($_POST['H'])?" {$_POST['H']}:{$_POST['i']}:{$_POST['s']}":"");
      $date = strtotime($datestr);
      $GMT = (empty($userinfo['user_gmt']) OR !isset($userinfo['user_gmt']))?0:$userinfo['user_gmt'];
      $dbdate = (preg_match('/\+|([0-9]*)/', $GMT)) ? $date-intval($GMT)*(60*60) : $date+intval($GMT)*(60*60);
      return date($format,$dbdate +(intval($config['GMT_correct'])*60*60));
   } else return kr_datecms($format);
   return isset($_POST['year']) ? backgmdate("{$_POST['year']}-{$_POST['month']}-{$_POST['day']}".(isset($_POST['H'])?" {$_POST['H']}:{$_POST['i']}:{$_POST['s']}":""), $format) : kr_datecms($format);
}

/**
* Функция даты по Гринвичу с учетом коррекции пользователя
* 
* @param string $format
* @return string
*/
function kr_date_user($format){
global $config,$main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($main->user['user_gmt'])) return date($format, gmdate('U')+(intval($main->user['user_gmt'])*60*60));
    else return kr_date($format);
}

/**
* Функция времени с учетом коррекции системы
* 
* @return int
*/
function kr_time(){
global $config;
    if(hook_check(__FUNCTION__)) return hook();
    return gmdate('U')+(intval($config['GMT_correct'])*60*60);
}

/**
* Функция коррекции выводимой даты
* 
* @param string $date
* @param string $format_date
* @return string
*/
function format_date($date, $format_date=""){
global $config, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    if(!isset($userinfo['user_gmt']) OR empty($userinfo['user_gmt'])) $GMT=0;
     else $GMT=intval($userinfo['user_gmt'])-intval($config['GMT_correct']);
    $format_date = (empty($format_date)) ? $config['date_format'] : $format_date;
    return (preg_match('/\+|([0-9]*)/', $GMT)) ? date($format_date, strtotime($date)+intval($GMT)*(60*60)) : date($format_date, strtotime($date)-intval($GMT)*(60*60));
}
/**
* Функция вывода даты без коррекции
* 
* @param string $date
* @param string $format_date
* @return string
*/
function format_date_orig($date, $format_date=""){
global $config, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    $format_date = (empty($format_date)) ? $config['date_format'] : $format_date;
    return date($format_date, strtotime($date));
}

/**
* Функция обратной коррекции даты
* 
* @param string $date
* @param string $format_date
* @return string
*/
function backgmdate($date, $format_date=""){
global $config, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($userinfo['user_gmt']) OR !isset($userinfo['user_gmt'])) $userinfo['user_gmt'] = 0;
    $GMT = $userinfo['user_gmt'];
    $format_date = (empty($format_date)) ? $config['date_format'] : $format_date;
    return (preg_match('/\+|([0-9]*)/', $GMT)) ? date($format_date, strtotime($date)-intval($GMT)*(60*60)) : date($format_date, strtotime($date)+intval($GMT)*(60*60));
}

/**
* Функция коррекции выводимой даты и определение давности публикации. Сегодня в ..., Вчера в 
* 
* @param mixed $date
* @return string
*/
function user_format_date($date, $his=false){
global $config, $lang, $userinfo, $config;
    if(hook_check(__FUNCTION__)) return hook();
    $format_date = $config['date_format'];
    $d = date("Y-m-d",strtotime($date)-$config['GMT_correct']*60*60);
    $today = date("Y-m-d");
    if(date("Y-m-d", strtotime($d)+60*60*24)==$today OR $d==$today){
        if($d==$today) return "{$lang['today']} ".format_date($date, "H:i");
        else return "{$lang['yesterday']} ".format_date($date, "H:i");
    } elseif(!$his) return format_date($date, $format_date);
    else return format_date($date, "{$format_date} H:i:s");
}

/**
* Функция проверки являться ли посетитель поисковой системой
* 
* @param string $name
* @param string $robots
*/
function check_bots($name){
global $list_bots;
    if(hook_check(__FUNCTION__)) return hook();
    foreach ($list_bots as $bot_name) {
        if (preg_match("/$bot_name/i", $name)) return true;
    }
    return false;
}

/**
* Удаляет директорию включая поддиректории
* 
* @param string $dir
* @return bool
*/
function remove_dir($dir){
    if(hook_check(__FUNCTION__)) return hook();
    if($dir[mb_strlen($dir)-1]!="/") $dir .= "/";
    if(preg_match('/(\/\/)/', $dir)) return false;
    if(file_exists($dir) AND $handle = opendir($dir)){
        while(($obj = readdir($handle))){
            if ($obj!="." AND $obj!=".."){
                if (is_dir($dir.$obj)){
                    if (!remove_dir($dir.$obj)) return false;
                } else if(is_file($dir.$obj)){
                           if (!unlink($dir.$obj)) return false;
                       }
                }
       }
       closedir($handle);
       if(file_exists($dir) AND @rmdir($dir)) return true;
       else return false;
   }
return false;
}

/**
* Возвращает количество файлов в директории и включая поддиректории
* 
* @param string $dir
* @param string $regEx
* @return int
*/
function dir_file_count($dir){
    if(hook_check(__FUNCTION__)) return hook();
    if(!file_exists($dir)) return false;
    if(is_file($dir)) return 1;
    if(is_dir($dir) AND $dh=opendir($dir)){
        $size=0;
        while(($file=readdir($dh))!==false){
            if($file=="." OR $file=="..") continue;
            $size+=dir_file_count($dir."/".$file);
        }
        closedir($dh);
        return $size;
    } else return 0;
}

/**
* Возвращает размер файлов в директории и включая поддиректории
* 
* @param string $dir
* @param int $buf
* @return int
*/
function dir_size($dir, $buf=2){
    if(hook_check(__FUNCTION__)) return hook();
    static $buffer;
    if(isset($buffer[$dir])) return $buffer[$dir];
    if(is_file($dir)) return filesize($dir);
    if(($dh=opendir($dir))){
        $size=0;
        while(($file=readdir($dh))!==false){
            if($file=="." OR $file=="..") continue;
            $size+=dir_size($dir."/".$file,$buf-1);
        }
        closedir($dh);
        if($buf>0) $buffer[$dir]=$size;
     return $size;
     }
return false;
}

/**
* Вырезает текст от начала, входящего текста, до заданного слова
* 
* @param string $text
* @param int $count
* @param string $return
* @return string
*/
function cut_text($text, $count=63, $return=""){
    if(hook_check(__FUNCTION__)) return hook();
    $textarr = explode(" ", $text);
    if (count($textarr)>$count){
        for($i=0; $i<$count; $i++) $return .= $textarr[$i]." ";
        return $return."...";
    } else return $text;
}

/**
* Вырезает текст от начала, входящего текста, до заданного слова
* 
* @param string $text
* @param int $count
* @param string $return
* @return string
*/
function cut_char($text, $count=35, $return=""){
    if(hook_check(__FUNCTION__)) return hook();
    if(mb_strlen($text)>$count) return $return.mb_substr($text, 0, $count)."...";
    else return $text;
}

/**
* Преобразования массива в информационную строку
* 
* @param array $var
* @param string $class
* @param string $text
* @return string
*/
function var2string($var, $class, $text){
    if(hook_check(__FUNCTION__)) return hook();
    $string = "";
    foreach ($var as $var_name=>$var_value){
        if(!is_object($var_value)){
            if(!is_array($var_value)) $string .= "<span class='{$class}'>{$text}: </span>{$var_name} = {$var_value}<br />\n";
            else $string .= "<span class='{$class}'>{$text}: </span><pre>{$var_name} =  ".htmlspecialchars(var_export($var_value, true))."</pre><br />\n";  
        }
    }
    return $string;
}

/**
* Функция транслитерации
* 
* @param string $string
* @param boolean $thisfile Кодировать для файла?
* @return string
*/
function cyr2lat($string, $thisfile=false){
    if(hook_check(__FUNCTION__)) return hook();
    $ch32=$thisfile?'_':'-';
    $cyr = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'і');
    $lat = array('a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'je', 'ju', 'ya', 'i');
    $bad_sym = array("'", ' ', '"', '(', ')', '[', ']', '$', '#', '%', '!', '@', '^', '&', '*', ';', ':', '<', '>', '/', '\\', '|', '?', ',', '~', '`', '-');
    $replace_sym = array('', $ch32, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    return str_replace($cyr, $lat, str_replace($bad_sym, $replace_sym, str_replace('  ', ' ', mb_strtolower($string))));
}   
     
/** 
* Функция перенаправления страницы
* 
* @param string $var
* @return void
*/
function redirect($var){
global $adminfile, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_int($var)){
        if($var==BACK){
            $REFERER = kr_filter(get_env('HTTP_REFERER'), TAGS);
            if(is_ajax()) kr_header("Location: {$REFERER}&ajax=true");
            else kr_header("Location: {$REFERER}");
        }elseif($var==MODULE){
            if(is_ajax()){
                if(!defined("ADMIN_FILE")) kr_header("Location: ".$main->url(array('module' => $main->module))."&ajax=true");
                else kr_header("Location: {$adminfile}?module={$main->module}&ajax=true");
            } else {
                if(!defined("ADMIN_FILE")) kr_header("Location: ".$main->url(array('module' => $main->module)));
                else kr_header("Location: {$adminfile}?module={$main->module}");
            }
        }
    } else {
        $var = str_replace("amp;", "", $var);
        if(is_ajax()) kr_header("Location: {$var}&ajax=true");
        else kr_header("Location: {$var}");
    }
}


function editor_small($name, $rows=5, $size='345px', $value="", $type=0, $butons="", $inited=true){
    return editor($name, $rows, $size, $value, $type, $butons, $inited, 0);
}

function editor_normal($name, $rows=5, $size='345px', $value="", $type=0, $butons="", $inited=true){
    return editor($name, $rows, $size, $value, $type, $butons, $inited, 1);
}

function editor_big($name, $rows=5, $size='345px', $value="", $type=0, $butons="", $inited=true){
    return editor($name, $rows, $size, $value, $type, $butons, $inited, 2);
}

/**
* Функция создания редактора
* 
* @param string $name
* @param int $rows
* @param string $size
* @param string $value
* @param int $type
* @param string $butons
* @param boolean $inited
* @return string
*/
global $script_mce_add;
$script_mce_add=false;
function editor($name, $rows=5, $size='345px', $value="", $type=0, $butons="", $inited=true, $editor_size=2){
global $editor_js, $tpl_create, $config,$main,$script_mce_add;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_array($name)) extract($name, EXTR_OVERWRITE);
    $value = (isset($_POST[$name])) ? $_POST[$name] : $value;
    if($type==0){
        if(empty($value)) $value = (isset($_POST[$name])) ? stripslashes($_POST[$name]) : "";
        else $value = (isset($_POST[$name])) ? stripslashes($_POST[$name]) : $value;
        if(!empty($config['xhtmleditor_g']) AND check_user_group($config['xhtmleditor_g'])){
            if(!$script_mce_add AND (!is_ajax() OR isset($_POST['load_module']))){
              $script_mce_add=true;
               main::add2script("includes/javascript/fn_tinymce.js");
            }
            if($inited) {
               main::init_function(array('language','tinymce'));
               $fsname=get_tmcefile_config($editor_size);
               $tplv=!defined("ADMIN_FILE")?TEMPLATE_PATH."{$main->tpl}/":TEMPLATE_PATH."{$config['template']}/";
               if(file_exists("includes/config/{$fsname}")) main::add2script(file_get_contents("includes/config/{$fsname}", true), false);
               if(is_ajax()){
                $add_script="tinymce.dom.Event.domLoaded = true;\n";
               } else $add_script="";
               main::add2script("\$.krReady(function(){{$add_script}\n init_tiny_mce({language : '".small_language()."',cssp:'{$tplv}',mode : 'exact',elements : '{$name}'});});", false);
            }
           $r="<div><textarea id='{$name}' name='{$name}' rows='".(is_int($rows)?$rows:5)."' cols='60' style='width: {$size};".(!is_int($rows)?" height:{$rows};":"")."' class='main_editor'>".htmlspecialchars($value, ENT_QUOTES)."</textarea></div>";
        } else {
           main::add2script("includes/javascript/kr_bbeditor.js");
           if($inited) main::add2script("\$.krReady(function(){bbeditor.init('{$name}', '{$butons}');});", false);
           $r = "<table cellpadding='0' cellspacing='0' style='width: 100%'><tr><td><textarea id='{$name}' name='{$name}' rows='".(is_int($rows)?$rows:5)."' cols='60' style='width: {$size};".(!is_int($rows)?" height:{$rows};":"")."' class='main_editor'>".htmlspecialchars($value, ENT_QUOTES)."</textarea></td></tr></table>";
        }
    }
    $editor_js = true;
    return $r;
}

/**
* Функция разбора категорий
* 
* @param string $string
* @return string
*/
function cat_parse($string){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($string)) return $main->lang['nocat'];
    $return = "";
    $cats = explode("|", $string);
    $count = count($cats);
    if($count>0 AND $string!='|'){
        for($i=0;$i<$count-1;$i++){
            $list = explode(",", $cats[$i]);
            $return .= "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($list[2], $list[1])))."' title='{$main->lang['view_cat']}'>{$list[0]}</a>";
            $return .= ($i<$count-2) ? ", " : "";
        }
        return $return;
    } else return $main->lang['nocat'];
}
/**
* Возвращает список категорий по модулю
* 
* @param mixed $module
* @param mixed $fieldname
*/
global $cashe_category;
function category_array($module=""){
global $main,$cashe_category;
    if(hook_check(__FUNCTION__)) return hook();
    if ($module=="") $module=$main->module;
    if(isset($cashe_category[$module])) return $cashe_category[$module];
    else {
       $ra=array();
       $main->db->sql_query("select * from ".CAT." where module='{$module}' ORDER BY BINARY(UPPER(title))");
       while ($row=$main->db->sql_fetchrow()) $ra[$row['cid']]=$row;
       $cashe_category[$module]=$ra;
       return $ra;
    }
}

/**
* Новая функция разбора категорий
* 
* @param string $string
* @param array $catlist
* @return mixed
*/
function cat_parse_new($string,$catlist){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($string)) return $main->lang['nocat'];
    $return = "";
    $cats = explode(",", $string);
    $count = count($cats);
    if($count>0 AND $string!=','){
        for($i=0;$i<$count-1;$i++){
        if ($cats[$i]!=""&&is_numeric($cats[$i])){
            $cid=$cats[$i];
               if(isset($catlist[$cid])){
                  $return .= "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($catlist[$cid]['cat_id'],$cid)))."' title='{$main->lang['view_cat']}'>{$catlist[$cid]['title']}</a>";
                  $return .= ($i<$count-2) ? ", " : "";
               }
            }
        }
        return $return;
    } else return $main->lang['nocat'];
}
/**
* код sql для вроверки принадлежности выбранной категории
* 
* @param mixed $alias_table
* @param mixed $chpu
*/
function sql_check_chpu_categorys($alias_table,$chpu=""){
    if(hook_check(__FUNCTION__)) return hook();
    if ($chpu=="") $chpu=$_GET['id'];
    return " AND exists(select kc.title from ".CAT." kc where kc.cat_id LIKE BINARY('{$chpu}') and {$alias_table}.cid like concat('%,',kc.cid,',%') ) ";
}

/**
* Функция выбора URL идентификатора
* 
* @param mixed $rewrite_id
* @param mixed $id
* @return string
*/
function case_id($rewrite_id, $id){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($main->rewrite_id  OR (defined("SYSTEM_FUNC") AND $main->config['rewrite']==ENABLED)) return kr_encodeurl($rewrite_id);
    else return $id;
}

/**
* Функция проверки передаваемых параметров методом POST
* 
* @param array $kays
* @param array $langs
* @param string $msg
*/
function error_empty($kays, $langs, $msg=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    foreach ($kays as $kay=>$value) if(empty($_POST[$value])) $msg .= (isset($lang[$langs[$kay]])) ? $lang[$langs[$kay]] : $langs[$kay];
    return $msg;
}

/**
* Функция проверки e-mail адреса на валидность
* 
* @param mixed $email
* @resurn string
*/
function check_mail($email){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($email) OR !preg_match('/^[_\.0-9a-z-]+@([0-9a-z-]+\.)+[a-z]{2,6}$/i', mb_strtolower($email))) return "{$lang['error_email']}";
    else return "";
}

/**
* Функция преобразования строки в URL учитывая параметр mod_rewrite
* 
* @param string $url
* @return string
*/
function kr_encodeurl($url){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($main->rewrite_id) return str_replace('+', ' ', urlencode($url));
    else return urlencode($url);
}

/**
* Функция преобразования URL в строку учитывая параметр mod_rewrite
* 
* @param string $url
* @return string
*/
function kr_decodeurl($url){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($main->rewrite_id) return urldecode($url);
    else return urldecode($url);
}

/**
* Функция преобразования URL в строку
* 
* @param string $url
* @return string
*/
function kr_normalize_url($url){
    if(hook_check(__FUNCTION__)) return hook();
    return urlencode(urldecode($url));
}

/**
* Функция преобразования строки в строковой массив
* 
* @param string $string
* @param string $return
*/
function array_create($string, $return=""){
    if(hook_check(__FUNCTION__)) return hook();
    $arr = explode(",", $string);
    $count = count($arr);
    for($i=0;$i<$count;$i++) $return .= ($i<$count-1) ? "'{$arr[$i]}', " : "'{$arr[$i]}'";
    return $return;
}

/**
* Функция проверки элемента массива, если, элемент найден возвращается значение в ином случаи его ключ
* 
* @param mixed $key
* @param mixed $array
* @return mixed
*/
function array_value_set($key, $array){
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($array[$key])) return $array[$key]; else return $key;
}

/**
* Функция выполнения SQL запроса INSERT
* 
* @param arrray $array
* @param string $table
* @return resourse
*/
function sql_insert($array, $table){
global $db;
    if(hook_check(__FUNCTION__)) return hook();
    $insert = "INSERT INTO `{$table}` (";
    $values = ") VALUES (";
    $count = count($array);
    $i=0;
    foreach ($array as $kay=>$value){
        $insert .= ($i<$count-1) ? "`{$kay}`, " : "`{$kay}`";
        $values .= ($i<$count-1) ? "'{$value}', " : "'{$value}'";
        $i++;
    }
    return $db->sql_query($insert.$values .= ");");
}

/**
* Функция выполнения SQL запроса UPDATE
* 
* @param array $array
* @param string $table
* @param string $where
* @return resourse
*/
function sql_update($array, $table, $where){
global $db;
    if(hook_check(__FUNCTION__)) return hook();
    $update = "UPDATE {$table} SET ";
    $count = count($array);
    $i=0;
    foreach ($array as $kay=>$value){
        $val=$value!=="NULL"?"'{$value}'":"null"; 
        $update .= ($i<$count-1) ? "`{$kay}`={$val}, " : "`{$kay}`={$val}";
        $i++;
    }
    $update .= " WHERE {$where}";
    return $db->sql_query($update);
}
/**
* Применение replace into при вставке
* 
* @param mixed $array
* @param mixed $table
* @return resourse
*/
function sql_replace_into($array, $table){
global $db;
    if(hook_check(__FUNCTION__)) return hook();
    $replace = "REPLACE INTO `{$table}` (";
    $values = ") VALUES (";
    $count = count($array);
    $i=0;
    foreach ($array as $kay=>$value){
        $replace .= ($i<$count-1) ? "`{$kay}`, " : "`{$kay}`";
        $values .= ($i<$count-1) ? "'{$value}', " : "'{$value}'";
        $i++;
    }
    return $db->sql_query($replace.$values .= ");");
}

/**
* Функция всегда положительного CRC32
* 
* @param string $string
* @return string
*/
function crc32_integer($string){
    if(hook_check(__FUNCTION__)) return hook();
    $crc32 = crc32($string);
    return(($crc32<0) ? $crc32*-1 : $crc32);
}    

/**
* Функция преобразования масива в строковой массив
* 
* @param array $array
* @param string $arr_name
* @param int $level
*/
function arr2str($array, $arr_name='data', $level=0){
    if(hook_check(__FUNCTION__)) return hook();
    if($level==0) return '$'.$arr_name." = ".var_export($array, true).";";
    else return "array(".implode(", ",$array).")";
}

/**
* Функция META перенаправления  с выводом информации на заданных промежуток времени
* 
* @param int $time
* @param string $url
* @param string $message
* @return void
*/
function meta_refresh($time, $url, $message=""){
    if(hook_check(__FUNCTION__)) return hook();
    echo "<meta http-equiv='refresh' content='{$time}; url={$url}'>";
    if(!empty($message)) info($message);
}

/**
* Функция g-zip сжатия страницы
* 
* @param string $contents
* @return string
*/
function gz($contents){
global $config;
    if(hook_check(__FUNCTION__)) return hook();
    if(!check_can_gzip() || $config['gz']!=ENABLED) echo $contents;
    else {
        if(strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) $_encoding = 'x-gzip';
        if(strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) $_encoding = 'gzip';
        if(isset($_encoding)) {
            $_in = $contents;
            $_inlenn = strlen($_in);
            $_out = gzencode($_in, $config['gzlevel']);
            header('Content-Encoding: '.$_encoding);
            echo $_out;
        }  else echo $contents;
    }
}

/**
* Функция экранирования
* 
* @param string $var
* @return string
*/
function magic_quotes($var){
global $magic_quotes;
    if(hook_check(__FUNCTION__)) return hook();
    if($magic_quotes==true AND !is_array($var)){
        return str_replace('\\\"', '"', addslashes(trim($var)));    
    } else return $var;
}

/**
* Функция декодирования bb тегов в HTML
* 
* @param string $string
* @param string $code
* @return string
*/
function bb($string, $code='encode'){
global $bb, $replace;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_class('bbcode');
    if($code=='encode'){
        if(!empty($replace['in'])) $string = str_replace(explode(',', $replace['in']), explode(',', $replace['out']), $string);
        $bb->set_text($string);
        return addslashes($bb->get_html());
    } else {
        $bb->text = $string;
        $bb->html2bb();
        return $bb->get_bb();
    }
}

/**
* Дополнительная функция декодирования bb тегов в HTML
* 
* @param string $text
* @return string
*/
function parse_bb($text){
global $bb, $config, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    $text = str_replace(array('</td><br />', '</tr><br />'), array('</td>', '</tr>'), $text);
    $text = preg_replace('/<table(.*?)><br \/>/is', '<table\\1>', $text);
    if(!is_object($bb)) main::init_class('bbcode');
    if(preg_match('/(.*?)(\[(youtube|flash.*|video|mp3|radio|hide.*|cite.*|spoiler.*|code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\])(.*?)/si', $text)){
        $bb->text = $text;
        $bb->other_bb();
        return stripcslashes($bb->get_bb());
    } else return stripcslashes($text);
}

/**
* Функция определения загружаемого шаблона
* 
* @return void
*/
function load_tpl(){
global $config, $load_tpl, $tpl_config, $main, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    if(defined("ADMIN_FILE")) $load_tpl = "admin";
    elseif(!is_user()) $load_tpl = $config['template'];
    elseif(isset($main->user['user_template']) AND $config['case_template']==ENABLED AND file_exists(TEMPLATE_PATH."{$main->user['user_template']}")) $load_tpl = $main->user['user_template'];
    elseif(isset($main->user['user_template']) AND empty($main->user['user_template'])) $load_tpl = $config['template'];    
    else $load_tpl = $config['template'];
    if(!defined("ADMIN_FILE") AND isset($_COOKIE['them']) AND preg_match('/[a-z0-9\-_]/is', $_COOKIE['them']) AND file_exists(TEMPLATE_PATH."{$_COOKIE['them']}/")) $load_tpl = $_COOKIE['them'];
    if(!preg_match('/^([a-z0-9\-_]*)$/i', $load_tpl))  $load_tpl = $config['template']; 
    if($config['pdaversion']==ENABLED){
        if($main->is_moile) $load_tpl = 'pda'; 
    }
    if(file_exists(TEMPLATE_PATH."{$load_tpl}/config_tpl.php")) main::required(TEMPLATE_PATH."{$load_tpl}/config_tpl.php");
}

/**
* Функция создания анализатора переменных
* 
* @return void
*/
function variables(){
global $variable;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_GET)) $variable['GET'] = var2string($_GET, 'get_var', 'GET');
    if(isset($_POST)) $variable['POST'] = var2string($_POST, 'post_var', 'POST');
    $temp_session=$_SESSION;if(!is_admin()) unset($temp_session['supervision']);
    if(isset($_SESSION)) $variable['SESSION'] = var2string($temp_session, 'session_var', 'SESSION');
    if(isset($_COOKIE)) $variable['COOKIE'] = var2string($_COOKIE, 'cookie_var', 'COOKIE');
    if(isset($_FILES)) $variable['FILES'] = var2string($_FILES, 'files_var', 'FILES');
    $variable['SESSIONID'] = "<span class='files_var'>SESSION_ID: </span>".session_id()."<br />\n";
    
}

/**
* Синоним функции header
* 
* @param mixed $command
* @return void
*/
function kr_header($command){
    if(hook_check(__FUNCTION__)) return hook();
    header($command);
    exit;
}

/**
* Функция отправки e-mail сообщений
* 
* @param string $to
* @param string $sender
* @param string $from
* @param string $from_name
* @param string $subject
* @param string $body
* @return bool
*/
function send_mail($to, $sender, $from, $from_name, $subject, $body, $reply=array(), $attach=array(), $bcc=array(), $cc=array()){
global $config, $MAILMAN, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($config['send_mail']==ENABLED) {
       main::init_class('mailman');
       if(!is_object($MAILMAN)) $MAILMAN = new MAILMAN();
       if(!empty($attach)) foreach($attach as $f) $MAILMAN->attach($f);
       if(!empty($reply)) $MAILMAN->MAIL->AddReplyTo($reply['mail'], $reply['name']);
       $newbody=($config['type_emeils']=='text/html')?nl2br($body):$body;
       $MAILMAN->headers(array('mail'=>$from, 'name'=>parse_mylang($from_name)), array('mail'=>$to, 'name'=>parse_mylang($sender)), parse_mylang($subject), parse_mylang($newbody), $bcc, $cc);
       $MAILMAN->type_send = $config['type_email_send'];
       $MAILMAN->charset = $config['charset_mail'];
       $MAILMAN->is_html = ($config['type_emeils']=='text/html') ? true : false;
       $MAILMAN->send();
    }
}

/**
* Функция разбиения на страницы
* 
* @param mixed $count
* @param mixed $this_page
* @param array $url_array
*/
function other_pages_list($count, $this_page, $url_array){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $uris = array();
    $pages = "<div class='pagebreak' id='{$main->module}_other_pages'>";
    if ($count > 1) {
        if (isset($this_page) AND $this_page>1){
            $pages .= "<a class='sys_link' href='\$uris[".count($uris)."]'><b>&#171;</b></a>";
            $uris[] = $this_page-1;
        }
        for ($i=1;$i<=$count;$i++) {
            if ($i == $this_page) $pages .= "<b class='noselect'>{$i}</b>";
            elseif((($i > ($this_page - 5)) AND ($i < ($this_page + 5))) OR ($i == $count) OR ($i == 1)){
                $pages .= "<a class='sys_link' href='\$uris[".count($uris)."]'><b>{$i}</b></a>";
                $uris[] = $i;
            } elseif ($i<$count AND ($count>6 AND $i==1) OR ($pagenum<$count-5 AND $i==$count-1)) $pages .= "<b class='noselect'>...</b>";
        }
        if ($this_page<$count) {
            $pages .= "<a class='sys_link' href='\$uris[".count($uris)."]'><b>&#187;</b></a>";
            $uris[] = $this_page+1;
        }
        foreach($uris as $kay=>$value) $uris_link[$kay] = $main->url($url_array+array('pagebreak' => $value)+(isset($_GET['page']) ? array('page' => $_GET['page']) : array()));
        $pages = preg_replace('/\$uris\[([0-9]*)\]/sie', "\$uris_link['\\1']", $pages);
    }
    return $pages."</div>";
}

/**
* Функция парсинга тега page_breack
* 
* @param string $text
* @param mixed $config
* @return string
*/
function pagebreak($text, $config){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($config!=ENABLED) return $text = preg_replace('/\[PAGE_BREAK\]/is', '', $text);
    else{
        if(preg_match('/\[PAGE_BREAK\]/s', $text)){
            $arr_page = explode("[PAGE_BREAK]", $text);
            $main->parse_rewrite(array('module', 'do', 'id', 'pagebreak', 'page'));
            $link = array('module' => $main->module, 'do' => $_GET['do'], 'id' => $_GET['id']);
            if(isset($_GET['pagebreak']) AND isset($arr_page[$_GET['pagebreak']-1])) return $arr_page[intval($_GET['pagebreak'])-1].other_pages_list(count($arr_page), intval($_GET['pagebreak']), $link);
            else return $arr_page[0].other_pages_list(count($arr_page), 1, $link);
        } else return $text;
    }
}

/**
* Функция переименования временной директории прикрепленных файлов
* 
* @param string $source
* @param string $dest
* @return bool
*/
function rename_attach($source, $dest){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT id, path FROM ".ATTACH." WHERE path LIKE '{$source}%'");
    if($main->db->sql_numrows($result)>0 AND $source!=$dest){
        if(file_exists($source)) rename($source, $dest);
        while(($row = $main->db->sql_fetchrow($result))) sql_update(array('path' => str_replace($source, $dest, $row['path'])), ATTACH, "id='{$row['id']}'");
        return true;
    } else {
        if(file_exists($source)) {
            if(dir_file_count($source)==0) remove_dir($source);
            elseif($source!=$dest) rename($source, $dest);
        }
        return false;
    }
}

/**
* Функция шифрования пароля пользователей
* 
* @param string $string
* @return string
*/
function pass_crypt($string){
    if(hook_check(__FUNCTION__)) return hook();
    return md5($string);
}

/**
* Функция добавления пунктов к пользователю
* 
* @param int $points
* @return void
*/
function add_points($points=0){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if($main->user['uid']!='-1') {
        if(isset($_SESSION['cache_session_user'])) unset($_SESSION['cache_session_user']);
        $main->user['user_points'] += $points;
        $main->db->sql_query("UPDATE ".USERS." SET user_points=user_points+{$points} WHERE uid='{$main->user['uid']}'");
        list($special) = $main->db->sql_fetchrow($main->db->sql_query("SELECT special FROM ".GROUPS." WHERE id={$main->user['user_group']}"));
        if($special!="1"){
            list($gid, $points) = $main->db->sql_fetchrow($main->db->sql_query("SELECT id, points FROM ".GROUPS." WHERE id>'{$main->user['user_group']}' AND special=0 ORDER BY id LIMIT 1"));
            if($main->user['user_points']>=$points AND !empty($gid)) $main->db->sql_query("UPDATE ".USERS." SET user_group='{$gid}' WHERE uid='{$main->user['uid']}'");
        }
    }
}

/**
* Функция удаления пунктов у пользователю
* 
* @param int $points
* @return void
*/
function delete_points($points, $idrecord, $module=''){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if($main->user['uid']!='-1') {
      main::init_function('points');
      global_delete_points($points, $idrecord, $module);
   }
}

/**
* Функция создает строку переменных из GET массива текучей страницы
* 
* @param array $ignore
* @param array $ignore_key
* @return string
*/
function parse_get($ignore=array(), $ignore_key=array()){
    if(hook_check(__FUNCTION__)) return hook();
    $GET = $_GET; $url = "";
    foreach($GET as $key => $value) $url .= (!in_array($key, $ignore) AND !isset($ignore_key[$key])) ? "&amp;{$key}={$value}" : "";
    return $url;
}

/**
* Функция создания списка тегов
* 
* @param string $tags_str
* @param string $modul
* @return string
*/
function list_tags($tags_str, $modul){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $tags_list = '';
    if ($tags_str!= '') {
        $tags_arr = explode(',', $tags_str);
        foreach ($tags_arr as $key => $value)  $tags_list.= "<span class='tag'><a href='".$main->url(array('module' => $modul, 'do' => 'tags', 'id' => kr_encodeurl($value)))."' title='{$main->lang['tags_goto']} {$value}'>{$value}</a></span>";
    }
    return $tags_list;
}

/**
* Функция создания облака тегов
* 
* @param string $modul
* @param string $limit
* @return string
*/
function kr_create_tags($modul='', $limit='') {
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $content = '';
    $count = $tags = array();
    $sizes = array('tag_level1', 'tag_level2', 'tag_level3', 'tag_level4', 'tag_level5');
    $where = ($modul=='') ? '' : "WHERE modul='{$modul}'";
    $limit = ($limit=='') ? '' : "LIMIT {$limit}";
    $result = $main->db->sql_query("SELECT tag, COUNT(*) AS count FROM ".TAG." {$where} GROUP BY tag ORDER BY count DESC {$limit}");
    if ($main->db->sql_numrows($result)>0) {
        while(($row = $main->db->sql_fetchrow($result))) {
            $tags['tags'][]  = $row['tag'];
            $tags['count'][] = $row['count'];
        }
        $min = min($tags['count']); $max = max($tags['count']); $res = $max - $min;
        foreach ($tags['count'] as $kay => $value) {
            $i = ($res) ? floor(($value-$min)/$res*4) : 0;
            $tags['size'][] = $sizes[$i];
        }
        uasort($tags['tags'], 'kr_sort_tag');
        foreach ($tags['tags'] as $kay => $value) $content.= "&nbsp;&nbsp;<a href='".$main->url(array('module' => $modul, 'do' => 'tags', 'id' => urlencode($value)))."' class='{$tags['size'][$kay]}' title='{$main->lang['tags_goto']} {$value} ({$tags['count'][$kay]}).'>{$value}</a> ";
    }
    return $content;
}

/**
* Функция сортировки массива тегов
* 
* @param string $a
* @param string $b
* @return string
*/
function kr_sort_tag($a, $b) {
    if(hook_check(__FUNCTION__)) return hook();
    return ($a == $b) ? 0 : strcmp($a , $b);
}

function set_calendar_date($id, $module, $date, $status){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".CALENDAR." WHERE module='{$module}' AND id='{$id}'");
    if($main->db->sql_numrows($result)>0){
        $main->db->sql_query("UPDATE ".CALENDAR." SET status='{$status}', date='{$date}' WHERE module='{$module}' AND id='{$id}'");
    } else $main->db->sql_query("INSERT INTO ".CALENDAR." (id, module, date, status) VALUES ('{$id}', '{$module}', '{$date}', '{$status}')");
}

function get_avatar($user_info, $type='normal'){
global $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    $avatr = (strpos($user_info['user_avatar'], 'http://')!==false) ? $user_info['user_avatar'] : "{$userconf['directory_avatar']}{$user_info['user_avatar']}";
    if($type=='micro') return "<img class='avatar lazyload' width='20' src='{$avatr}' alt='' />";
    elseif($type=='mini') return "<img class='avatar lazyload' style='margin: 3px;' width='36' src='{$avatr}' alt='' />";
    elseif($type=='small') return "<img class='avatar lazyload' style='margin: 3px;' width='55' src='{$avatr}' alt='' />";
    elseif($type=='normal') return "<img class='avatar lazyload' style='margin: 3px;' width='100' src='{$avatr}' alt='' />";
    elseif($type=='big') return "<img class='avatar lazyload' style='margin: 3px;' src='{$avatr}' alt='' />";
}

function kr_exit(){
    if(hook_check(__FUNCTION__)) return hook();
    exit;
}

function data_checking($callback, $vars){
    if(hook_check(__FUNCTION__)) return hook();
    if(function_exists($callback) OR file_exists('includes/function/'.$callback.'.php')) {
        if(!function_exists($callback)) main::init_function($callback);
        ob_start();
        echo call_user_func_array($callback, $vars);
        $return = ob_get_contents(); ob_get_clean();
        return $return;
    } else return false;
}

variables();
?>