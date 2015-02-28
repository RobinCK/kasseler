<?php
/**
* Функции создания форм и HTML элементов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/forms.php 
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Конвертирует NAME єлемента формы в допустимый для ID
* 
* @param mixed $name
*/
function conv_name_to_id($name){
    if(hook_check(__FUNCTION__)) return hook();
    if (!strpos($name,"[]")) return preg_replace('/(?i)[[\]"\']/', '', $name);
    else return "";
}

/**
* Функция создания выпадающего списка
* 
* @param string $name
* @param array $options
* @param string $class
* @param mixed $value
* @param string $onchange
* @param bool $multiple
* @return string
*/
function in_sels($name, $options, $class, $value="", $onchange="", $multiple="", $size=0){        
    if(hook_check(__FUNCTION__)) return hook();
    $id=conv_name_to_id($name);
    $is = is_array($value); $select = ""; $value = (isset($_POST[$name])) ? $_POST[$name] : $value; 
    $sel = "<select{$onchange}".($id!=""?" id='{$id}'":"")." name='{$name}".($is?'[]':'')."' class='{$class}'".(($is AND $multiple!=false)?" multiple='multiple'":'')."".(($is OR $size!=0)?" size='".($size==0?8:$size)."'":'').">\n";    
    foreach($options as $key=>$val){        
        $match = "";        
        if(preg_match('/(.+?)_optgroup_(.*)/i', $key, $match)){
            if($match[1]=='begin') $sel .= "<optgroup label='{$val}'>\n";
            else $sel .= "</optgroup>\n";
            continue;
        } else {
            if(!$is) $select = ($value==$key) ? " style='font-weight: bold;' selected='selected'" : "";
            else $select = in_array($key, $value) ? " style='font-weight: bold;' selected='selected'" : "";
            $sel .=  "<option value='{$key}'{$select}>{$val}</option>\n";
        }
    }
    return $sel."</select>";
}

function show_group_in_sels($list, $group_title, $value){
   if(hook_check(__FUNCTION__)) return hook();
   $sel = $select = ""; 
   foreach($list as $key=>$val){        
      if(is_array($val)){
         $sel.= "<optgroup label='{$group_title[$key]}'>";
         $sel.=show_group_in_sels($val, $group_title, $value);
         $sel.= "</optgroup>";
      } else {
         $select = in_array($key, $value) ? " style='font-weight: bold;' selected='selected'" : "";
         $sel .=  "<option value='{$key}'{$select}>{$val}</option>\n";
      }
   }
   return $sel;
}

/**
* Функция создания выпадающего списка с групировкой
* 
* @param string $name
* @param array $list
* @param array $group_title
* @param string $class
* @param mixed $value
* @param string $onchange
* @param bool $multiple
* @return string
*/
function in_sels_group($name, $list, $group_title, $class, $value="", $onchange="", $multiple="", $size=0){        
   if(hook_check(__FUNCTION__)) return hook();
   $id=conv_name_to_id($name);
   $is = is_array($value); $select = ""; 
   $value = (isset($_POST[$name])) ? array($_POST[$name]) : (is_array($value)?$value:array($value)); 
   $sel = "<select{$onchange}".($id!=""?" id='{$id}'":"")." name='{$name}".($is?'[]':'')."' class='{$class}'".(($is AND $multiple!=false)?" multiple='multiple'":'')."".(($is OR $size!=0)?" size='".($size==0?8:$size)."'":'').">\n";
   $sel.= show_group_in_sels($list, $group_title, $value);
   return $sel."</select>";
}

/**
* Функция создания текстового поля input
* 
* @param string $name
* @param string $class
* @param string $value
* @param bool $disabled
* @param string $add
* @return bool
*/
function in_text($name, $class="", $value="", $disabled=false, $add=""){
    if(hook_check(__FUNCTION__)) return hook();
    $id=conv_name_to_id($name);
    $value = isset($_POST[$name]) ? stripslashes($_POST[$name]) : $value;
    $value = str_replace("&#036;", '$', $value);
    return "<input".(!empty($class) ? " class='{$class}'" : "")." type='text' name='{$name}' ".($id!=""?" id='{$id}'":"")." value='".htmlspecialchars($value, ENT_QUOTES)."'".(($disabled) ? " readonly='readonly'" : "")."{$add} />";
}

/**
* Функция создания динамически добавляемого текстового поля input
* 
* @param string $name
* @param string $class
* @param array $value
* @return string
*/
function in_text_many($name, $class="", $value=array(), $placeholder=''){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $value = isset($_POST[$name]) ? $_POST[$name] : $value; 
    if(empty($value)){
        $return = "<table width='100%' cellspacing='0' cellpadding='0'><tr><td id='addinputs'>".
        "<table width='100%' cellspacing='0' cellpadding='0'><tr><td><input".(!empty($class) ? " class='{$class}'" : "")."".(!empty($placeholder) ? " placeholder='{$placeholder}'" : "")." type='text' name='{$name}[]' value='' /></td></tr></table>".
        "</td><td width='18' valign='bottom'><img onclick=\"addinput('addinputs', '{$name}', '{$class}', '{$placeholder}')\" style='cursor: pointer; margin-bottom: 4px;' src='includes/images/plus.gif' alt='{$lang['add']}' /></td></tr></table>";
    } else {
        $return = "<table width='100%' cellspacing='0' cellpadding='0'><tr><td id='addinputs'>";
        foreach($value as $link){
            $return .= "<table width='100%' cellspacing='0' cellpadding='0' style='margin-top: 2px;'><tr><td><input".(!empty($class) ? " class='{$class}'" : "")." type='text' name='{$name}[]' value='".htmlspecialchars($link, ENT_QUOTES)."' /></td></tr></table>";    
        }
        $return .= "</td><td width='18' valign='bottom'><img onclick=\"addinput('addinputs', '{$name}', '{$class}', '{$placeholder}')\" style='cursor: pointer; margin-bottom: 4px;' src='includes/images/plus.gif' alt='{$lang['add']}' /></td></tr></table>";
    }
    return $return;
}

/**
* Функция создания текстового поля password
* 
* @param string $name
* @param string $class
* @param string $value
* @return string
*/
function in_pass($name, $class="", $value="", $add=""){
    if(hook_check(__FUNCTION__)) return hook();
    $id=conv_name_to_id($name);
    return "<input".(!empty($class) ? " class='{$class}'" : "")." type='password' name='{$name}' ".($id!=""?" id='{$id}'":"")." value=\"{$value}\"{$add} />";
}

/**
* Функция создания текстового поля textarea
* 
* @param string $name
* @param string $class
* @param int $rows
* @param string $value
* @return string
*/
function in_area($name, $class="", $rows=5, $value=""){
    if(hook_check(__FUNCTION__)) return hook();
    $id=conv_name_to_id($name);
    $value = str_replace("&#036;", '$', isset($_POST[$name])?stripslashes($_POST[$name]):$value);
    return "<textarea".(!empty($class) ? " class='{$class}'" : "")." name='{$name}' ".($id!=""?" id='{$id}'":"")." rows='{$rows}' cols='10'>".htmlspecialchars($value, ENT_QUOTES)."</textarea>";
}

/**
* Функция создания элемента checkbox
* 
* @param string $name
* @param string $class
* @param mixed $checked
* @param string $add
* @return string
*/
function in_chck($name, $class="", $checked=" checked='checked'", $add="", $id=true){
    if(hook_check(__FUNCTION__)) return hook();
    $nid=conv_name_to_id($name);
    return "<input type='hidden' name='hide_{$name}' value='set' /><input type='checkbox'".(!empty($class) ? " class='{$class}'" : "")."".($id?($nid!=""?" id='{$nid}'":""):"")." name='{$name}'".(($checked==ENABLED OR $checked>0 OR isset($_POST[$name])) ? " checked='checked'" : (($checked=="off" OR $checked=="" OR $checked==0) ? "" : $checked))." {$add}/>";
}

/**
* Функция создания скрытого поля hidden
* 
* @param string $name
* @param string $value
* @param string $js
* @return string
*/
function in_hide($name, $value, $js=false){    
global $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    $nid=conv_name_to_id($name);
    if($js) main::add2script("addEvent(window, 'load', function(){document.getElementById('{$name}').value='{$value}'});", false);
    return "<input type='hidden' name='{$name}' ".($nid!=""?" id='{$nid}'":"")." value=\"{$value}\" />\n";
}

/**
* Функция создания элемента radio
* 
* @param string $name
* @param string $value
* @param string $text
* @param string $id
* @param bool $defult_checked
* @return string
*/
function in_radio($name, $value, $text, $id="", $defult_checked=false, $add=""){    
    if(hook_check(__FUNCTION__)) return hook();
    return "<input type='radio' name='{$name}' value=\"{$value}\" id='{$id}'".((($defult_checked AND !isset($_POST[$name])) OR (isset($_POST[$name]) AND $_POST[$name]==$value)) ? " checked='checked'" : "")." {$add}/> <label for='{$id}' class='pointer'>{$text}</label>";
}

/**
* Функция создания кнопки удаления
* 
* @param string $url
* @param string $elm
* @return string
*/
function delete_button($url, $elm='', $onclock=true){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_delete pixel' title='{$lang['delete']}'".($onclock?" onclick=\"update_ajax('{$url}', '{$elm}', '{$lang['realdelete']}'); return false;\"":$elm)."></a>";
}

/**
* Функция создания кнопки установки
* 
* @param string $url
* @param string $add
* @return string
*/
function install_button($url, $add=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_install pixel'{$add} title='{$lang['install']}'></a>";
}

/**
* Функция создания кнопки удаления
* 
* @param string $url
* @param string $add
* @return string
*/
function uninstall_button($url, $add=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_uninstall pixel'{$add} title='{$lang['uninstall']}'></a>";
}

/**
* Функция создания кнопки редактирования
* 
* @param string $url
* @param string $add
* @return string
*/
function edit_button($url="#", $add=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_edit pixel'{$add} title='{$lang['edit']}'></a>";
}

/**
* Функция создания кнопки вверх
* 
* @param string $url
* @param string $elm
* @return string
*/
function up_button($url, $elm){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' title='{$lang['move_up']}' class='up_button' onclick=\"move_ajax('{$url}', '{$elm}'); return false;\"><img src='includes/images/pixel.gif' alt='' /></a>";
}

/**
* Функция создания кнопки вниз
* 
* @param string $url
* @param string $elm
* @return string
*/
function down_button($url, $elm){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' title='{$lang['move_down']}' class='down_button' onclick=\"move_ajax('{$url}', '{$elm}'); return false;\"><img src='includes/images/pixel.gif' alt='' /></a>";
}

/**
* Функция создания кнопки "Отправить"
* 
* @return string
*/
function send_button($onclick=""){
global $tpl_config, $main;
    if(hook_check(__FUNCTION__)) return hook();
    return "<input{$onclick} type='submit' class='submit' value='{$main->lang['send']}' />";
}

/**
* Функция создания кнопки "Поиск" в модуле
* 
* @return string
*/
function button_search_module(){
global $tpl_config, $main;
    if(hook_check(__FUNCTION__)) return hook();
    return "<input type='submit' class='search_module_button' value='{$main->lang['search']}' />";
}

/**
* Функция создания кнопки "Поиск"
* 
* @return string
*/
function button_search(){
global $tpl_config, $main;
    if(hook_check(__FUNCTION__)) return hook();
    return "<input type='submit' class='search_button' value='{$main->lang['search']}' />";
}

/**
* Функция создания кнопки info
* 
* @param string $url
* @param string $add
* @return string
*/
function info_button($url="#", $add=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_info pixel'{$add} title='{$lang['info']}'></a>";
}

/**
* Функция создания кнопки insert
* 
* @param string $url
* @param string $add
* @return string
*/
function insert_button($url="#", $add=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_add pixel'{$add} title='{$lang['insert']}'></a>";
}

/**
* Функция создания кнопки clear
* 
* @param string $url
* @param string $add
* @return string
*/
function clear_button($url="#", $elm=''){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    return "<a href='{$url}' class='admino ico_clear pixel' title='{$lang['clear']}' onclick=\"clear_ajax('{$url}', '{$elm}', '{$lang['realy_clear']}'); return false;\"></a>";
}

/**
* Функция создания кнопки favorite
* 
* @param string $id
* @param string $idelm
* @param array $lang
* @param string $class
* @return string
*/
function favorite_button($id, $idelm, $lang=array(), $class='', $module=''){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $module = empty($module) ? $main->module : $module;
    $del = (isset($lang['del'])) ? $lang['del'] : $main->lang['favorite_del'];
    $add = (isset($lang['add'])) ? $lang['add'] : $main->lang['favorite_add'];
    $picture  = ($id != '') ?  "class='favorite {$class}favorite_off' title='{$del}'" : "class='favorite {$class}favorite_on' title='{$add}'";
    $favorite = "<img id='favorite-{$idelm}' onclick=\"set_favorite({$idelm},'favorite-{$idelm}','{$module}', ['{$del}', '{$add}'], '{$class}');\" {$picture} src='includes/images/pixel.gif' align='left' alt=''/>";
    return $favorite;
}

function in_tag($value=''){
global $main, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    $tags = array();
    $result = $main->db->sql_query("SELECT t.*, (SELECT COUNT(*) FROM ".TAG." as tt WHERE tt.tag=t.tag) AS count FROM ".TAG." as t WHERE t.modul='{$main->module}' GROUP BY tag");
    if($main->db->sql_numrows($result)>0){
        while($row = $main->db->sql_fetchrow($result)) $tags[$row['tag']] = $row['tag'];
    }
    return in_sels('tags', $tags, 'select chzn-add', !empty($value)?explode(',', $value):array(), '', true);    
}
?>