<?php
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция возвращает текущий языковый параметр
* 
* @return string
*/
function get_lang(){
global $language;
    if(hook_check(__FUNCTION__)) return hook();
    return $language;
}

/**
* Возвращает тип файла
* 
* @param string $file
* @return string
*/
function get_type_file($file){
    if(hook_check(__FUNCTION__)) return hook();
    return preg_replace('/(.+?)[.]([a-zA-z0-9]+)$/', '\\2', $file);
}

/**
* Возвращает имя файла
* 
* @param string $file
* @return string
*/
function get_name_file($file){
    if(hook_check(__FUNCTION__)) return hook();
    return preg_replace('/(.+?)[.]([a-zA-z0-9]+)$/', '\\1', $file);
}

/**
* Функция преобразования байтов в более высшую систему измерения KB, Mb, Gb, TB
* 
* @param mixed $bytes
* @return string
*/
function get_size($bytes){
    if(hook_check(__FUNCTION__)) return hook();
    if ($bytes < 1000 * 1024) return number_format($bytes / 1024, 2) . " KB";
    elseif ($bytes < 1000 * 1048576) return number_format($bytes / 1048576, 2) . " MB";
    elseif ($bytes < 1000 * 1073741824) return number_format($bytes / 1073741824, 2) . " GB";
    else return number_format($bytes / 1099511627776, 2) . " TB";
}

/**
* Функция возвращает текущий USER AGENT
* 
* @return string
*/
function get_user_agent(){
    if(hook_check(__FUNCTION__)) return hook();
    static $ua;
    if($ua) return $ua;
    return $ua = get_env('HTTP_USER_AGENT');
}

/**
* Функция возвращает количество истекших лет от заданной даты
* 
* @param string $date
* @return int
*/
function get_age($date){
    if(hook_check(__FUNCTION__)) return hook();
    $Year = date("Y") - date("Y", strtotime($date));
    if(date("m")<date("m", strtotime($date))) return --$Year;
    if(date("m")==date("m", strtotime($date)))
    if(date("d")<date("d", strtotime($date))){return --$Year;} else return $Year;
    if(date("m")>date("m", strtotime($date))) return $Year;
    return 0;
}

/**
* Функция определение языкового файла
* 
* @return void
*/
function get_language(){
global $config, $userinfo, $language, $module_name, $lang, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $lang =  array(); $main->lang = array();
    if($config['multilanguage']==ENABLED AND isset($_COOKIE['lang']) AND file_exists("includes/language/{$_COOKIE['lang']}/")) $language = $_COOKIE['lang'];
    else $language = empty($userinfo['user_language']) ? $config['language'] : $userinfo['user_language'];
    $main->language = &$language; 
    if(!preg_match('/[a-z0-9\-_]/i', $language)) $language = $config['language'];
    foreach(load_includes('lang*?\.php', "includes/language/{$language}/") as $filename) {
        main::init_language(str_replace('.php', '', basename($filename)));
    }    
    if(defined("ADMIN_FILE") AND file_exists("includes/language/{$language}/admin.php")) main::init_language('admin');
    
    if(!is_home()){
        if(isset($_GET['module']) AND file_exists("includes/language/{$language}/{$module_name}.php")) main::init_language($module_name);
    } else {
        $_arr_home_modules = explode(',', $config['default_module']);
        foreach($_arr_home_modules as $l){
            if(file_exists("includes/language/{$language}/{$l}.php")) main::init_language($l);
        }
    }
    if(file_exists("modules/{$module_name}/language/language.{$language}.php")){
        main::init_language("language.{$language}.php");
    }
    foreach (load_includes('lang_.*?\.php', "includes/language/{$language}/") as $filename){
        main::init_language(str_replace('.php', '', basename($filename)));
    }
    $lang = $main->lang;
}

/**
* Создает строку с произвольных символов
* 
* @param int $size
* @param string $string
* @return string
*/
function get_random_string($size=25, $string=""){
    if(hook_check(__FUNCTION__)) return hook();
    return $string.substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $size);
}

/**
* Функция создания выпадающего списка локализаций системы
* 
* @param string $select
* @return string
*/
function get_lang_file($select="", $nosels=true){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $sels = $nosels==true ? array('' => $lang['no']) : array();
    $dir = opendir('includes/language/');
    while(($file = readdir($dir))) if(is_dir('includes/language/'.$file) AND $file!='.' AND $file!='..' AND $file!='.svn') $sel[$file] = isset($lang[$file])?$lang[$file]:$file;
    closedir($dir);
    return in_sels('language', array_merge($sels, $sel), 'select chzn-search-hide', isset($_POST['lang']) ? $_POST['lang'] :$select);
}

/**
* Функция создания выпадающего списка модулей системы
* 
* @param string $select
* @param string $onchange
* @return string
*/
function select_modules($select="", $onchange="", $sels=array()){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $dir = opendir('modules/');
    while(($file = readdir($dir))) if(is_dir('modules/'.$file) AND $file!='.' AND $file!='..' AND $file!='.svn') $sel[$file] = isset($lang[$file]) ? $lang[$file]:$file;
    closedir($dir);
    return in_sels('module', array_merge($sels, $sel), 'select', $select, $onchange);
}  

/**
* Функция создания выпадающего списка шаблонов системы
* 
* @param string $tpl
* @return string
*/
function select_template($tpl=""){
global $load_tpl, $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $template_dir = opendir(TEMPLATE_PATH);
    while(($file = readdir($template_dir))) if(is_dir(TEMPLATE_PATH.$file) AND $file!='.' AND $file!='..' AND $file!='admin' AND $file!='pda' AND $file!='.svn') $sel[$file] = isset($lang[$file]) ? $lang[$file]:$file;
    closedir($template_dir);
    return in_sels('template', $sel, 'select2 chzn-search-hide', !empty($tpl) ? $tpl : $load_tpl);
}

/**
* Функция создания выпадающего списка категорий
* 
* @param mixed $selection
* @param string $module
* @param mixed $multiple
* @returns string
*/
function get_cat($selection='', $module='', $multiple='', $class=''){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::init_function('get_array_cat');
    
    $is = is_array($selection);
    $sel=get_array_cat(empty($module) ? $main->module : $module);
    return in_sels('cid', count($sel)>0?(!empty($selection)?$sel:(array('' => $main->lang['no_cat'])+$sel)):array('' => $main->lang['no_cat']), $class.'select2 chzn-select', isset($_POST['cid']) ? $_POST['cid'] : $selection, "", ($is AND $multiple!=false)?true:false);
}

/**
* Функция генерации календаря для публикаций
* 
* @param string $date
* @return string
*/
function get_date_case($date){
global $tpl_create, $lang;
    if(hook_check(__FUNCTION__)) return hook();
    main::add2script("addEvent(window, 'load', function(){KR_AJAX.calendar.init('calendar1', {day:'day', month:'month', year:'year'});});", false);    
    $types = explode(" ", $date);
    $arrdate = explode("-", $types[0]);
    $casedate = "{$lang['day']}: <select name='day' id='day' class='chzn-search-hide'>";
    for($i=1;$i<=31;$i++) $casedate .= "<option value='{$i}'".(($i==$arrdate[2]) ? " selected='selected'" : "").">".(($i<10) ? "0".$i : $i)."</option>";
    $casedate .= "</select> {$lang['month']}: <select name='month' id='month' class='chzn-search-hide'>";
    for($i=1;$i<=12;$i++) $casedate .= "<option value='{$i}'".(($i==$arrdate[1]) ? " selected='selected'" : "").">".(($i<10) ? "0".$i : $i)."</option>";
    $casedate .= "</select>";
    if(!isset($types[1]) OR empty($types[1]) OR !defined("ADMIN_FILE"))  return $casedate." {$lang['year']}: <input id='year' name='year' style='width: 50px;' type='text' maxlength='4' value='{$arrdate[0]}' /> <img style='cursor: pointer;' src='includes/images/calendar.jpg' title='{$lang['calendar']}' alt='{$lang['calendar']}' id='button_calendar1' />";
    else {
        $arrtime = explode(":", $types[1]);
        $time = " {$lang['pub_time']}: <select name='H' class='chzn-search-hide'>";
        for($i=0;$i<=23;$i++) $time .= "<option value='{$i}'".(($i==$arrtime[0]) ? " selected='selected'" : "").">{$i}</option>";
        $time .= "</select> <select name='i' class='chzn-search-hide'>";
        for($i=0;$i<=59;$i++) $time .= "<option value='{$i}'".(($i==$arrtime[1]) ? " selected='selected'" : "").">{$i}</option>";
        $time .= "</select> <select name='s' class='chzn-search-hide'>";
        for($i=0;$i<=59;$i++) $time .= "<option value='{$i}'".(($i==$arrtime[2]) ? " selected='selected'" : "").">{$i}</option>";
        $time .= "</select>";
        return $casedate." {$lang['year']}: <input id='year' name='year' style='width: 50px;' type='text' maxlength='4' value='{$arrdate[0]}' /> {$time} <img style='cursor: pointer;' src='includes/images/calendar.jpg' title='{$lang['calendar']}' alt='{$lang['calendar']}' id='button_calendar1' />";
    }
}

/**
* Функция возвращает флаг запрашиваемой страны
* 
* @param string $country
* @return string
*/
function get_flag($country, $align=' align="left"', $style=""){
global $main, $modules;
    if(hook_check(__FUNCTION__)) return hook();
    if($main->config['geoip']==ENABLED) {
        if(isset($modules['top_users']) AND $modules['top_users']['active']==1) return "<a href='".$main->url(array('module' => 'top_users', 'do' => 'country', 'id' => str_replace(" ", "%20", $country)))."' title='{$main->lang['show_user_country']}'><img src='includes/images/country/".str_replace(" ", "_", mb_strtolower($country)).".png'{$align} alt='{$country}' title='{$country}'{$style} /></a>";
        else return "<img class='lazyload' src='includes/images/country/".str_replace(" ", "_", mb_strtolower($country)).".png'{$align} alt='{$country}' title='{$country}'{$style} />";
    } else return "";
}

/**
* Функция создания выпадающего списка групп пользователей
* 
* @param mixed $sel
* @param string $name
* @param bool $empty_row
* @param string $row_lang
* @return string
*/
function get_groups($select="", $name='', $empty_row=false, $row_lang=""){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $is = is_array($select);
    if($empty_row==true) $sel[0]=empty($row_lang)?$main->lang['no']:$row_lang;
    $result = $main->db->sql_query("SELECT * FROM ".GROUPS." ORDER BY id");
    while(($row = $main->db->sql_fetchrow($result))) $sel[$row['id']] = $row['title'];
    return in_sels(!empty($name)?$name:('group'.($is?'s':'')), $sel, 'select2 chzn-search-hide', isset($_POST['groups']) ? $_POST['groups'] : $select, "", $is?true:false);
}

/**
* Функция возвращает название месяца
* 
* @param int $num
* @return string
*/
function lang_month($num){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    switch($num){
        case 1: $month = $main->lang['january']; break;
        case 2: $month = $main->lang['february']; break;
        case 3: $month = $main->lang['march']; break;
        case 4: $month = $main->lang['april']; break;
        case 5: $month = $main->lang['may']; break;
        case 6: $month = $main->lang['june']; break;
        case 7: $month = $main->lang['july']; break;
        case 8: $month = $main->lang['august']; break;
        case 9: $month = $main->lang['september']; break;
        case 10: $month = $main->lang['october']; break;
        case 11: $month = $main->lang['november']; break;
        case 12: $month = $main->lang['december']; break;
    }
    return $month;
}  
?>