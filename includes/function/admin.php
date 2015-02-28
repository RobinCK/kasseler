<?php
if (!defined('FUNC_FILE')) die('Access is limited');

function _type_form_name_set($type){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    switch($type){
        case "text": $type = "<span style='color: green'>{$lang['text_form']}</span>"; break;
        case "select": $type = "<span style='color: blue'>{$lang['select_form']}</span>"; break;
        case "textarea": $type = "<span style='color: orange'>{$lang['textarea_form']}</span>"; break;
        case "radio": $type = "<span style='color: #777777'>{$lang['radio_form']}</span>"; break;
        case "checkbox": $type = "<span style='color: red'>{$lang['checkbox_form']}</span>"; break;
        case "file": $type = "<span style='color: gray'>{$lang['file_form']}</span>"; break;
    }
    return $type;
}

function in_case_val($name, $class="", $value=array(), $default=0){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $value = isset($_POST[$name]) ? $_POST[$name] : $value; 
    if(empty($value)){
        $return = "<table width='100%' cellspacing='0' cellpadding='0'><tr><td id='addinputs'>".
        "<table width='100%' cellspacing='0' cellpadding='0'><tr><td width='20' align='center'><input type='radio' name='selected' value='0' /></td><td><input".(!empty($class) ? " class='{$class}'" : "")." type='text' name='{$name}[]' value='' /></td></tr></table>".
        "</td><td width='18' valign='bottom'><img onclick=\"addinput_case('addinputs', '{$name}', '{$class}')\" style='cursor: pointer; margin-bottom: 4px;' src='includes/images/plus.gif' alt='{$lang['add']}' /></td></tr></table>";
    } else {
        $i = 0;
        $return = "<table width='100%' cellspacing='0' cellpadding='0'><tr><td id='addinputs'>";
        foreach($value as $link){
            $return .= "<table width='100%' cellspacing='0' cellpadding='0' style='margin-top: 2px;'><tr><td width='20' align='center'><input type='radio' name='selected' value='{$i}' ".($default==$i?"checked='checked'":'')." /></td><td><input".(!empty($class) ? " class='{$class}'" : "")." type='text' name='{$name}[]' value='".htmlspecialchars($link, ENT_QUOTES)."' /></td></tr></table>";    
            $i++;
        }
        $return .= "</td><td width='18' valign='bottom'><img onclick=\"addinput_case('addinputs', '{$name}', '{$class}')\" style='cursor: pointer; margin-bottom: 4px;' src='includes/images/plus.gif' alt='{$lang['add']}' /></td></tr></table>";
    }
    return $return;
}

function get_who_view($select=""){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $select = (isset($_POST['view'])) ? $_POST['view'] : $select;
    $arr = array(1 => $lang['alluser'], 2 => $lang['onlyguest'], 3 => $lang['onlyuser'], 4 => $lang['onlyadmin']);
    $sel = "<select name='view' class='select chzn-search-hide'>";
    foreach ($arr as $key => $var) $sel .= "<option value='".($key)."'".(($select==$key) ? " selected='selected'" : "").">{$var}</option>\n";
    return $sel."</select>\n";
}

function who_view($int){
global $lang;
    if(hook_check(__FUNCTION__)) return hook();
    switch($int){
        case 0:  $view = "<span style='color: red;'>{$lang['onlyadmin']}</span>"; break;
        case 1:  $view = "<span style='color: green;'>{$lang['alluser']}</span>"; break;
        case 2:  $view = "<span style='color: red;'>{$lang['onlyguest']}</span>"; break;
        case 3:  $view = "<span style='color: green;'>{$lang['onlyuser']}</span>"; break;
        case 4:  $view = "<span style='color: red;'>{$lang['onlyadmin']}</span>"; break;
        default: $view = "<span style='color: green;'>{$lang['alluser']}</span>"; break;
    }
    return $view;
}

function up_down_analizy($this, $end, $id, $elm){
global $adminfile, $module_name;
    if(hook_check(__FUNCTION__)) return hook();
    if(!defined("ADMIN_FILE")) return '';
    if($this==1) return "<table cellspacing='1' class='cl'><tr><td>".down_button("{$adminfile}?module={$module_name}&amp;do=move&amp;type=down&amp;id={$id}&amp;pos={$this}", $elm)."</td></tr></table>";
    elseif($this==$end) return "<table cellspacing='1' class='cl'><tr><td>".up_button("{$adminfile}?module={$module_name}&amp;do=move&amp;type=up&amp;id={$id}&amp;pos={$this}", $elm)."</td></tr></table>";
    else return "<table cellspacing='1' class='cl'><tr><td>".up_button("{$adminfile}?module={$module_name}&amp;do=move&amp;type=up&amp;id={$id}&amp;pos={$this}", $elm)."</td><td>".down_button("{$adminfile}?module={$module_name}&amp;do=move&amp;type=down&amp;id={$id}&amp;pos={$this}", $elm)."</td></tr></table>";
}

function detected_rss_var(){
    if(hook_check(__FUNCTION__)) return hook();
    $conf_vars = array();
    foreach(load_includes('config_.*?\.php', 'includes/config/') as $filename){
        $content = file_get_contents($filename);
        if($filename!='includes/config/config_rss.php' AND preg_match('/(.*?)\'rss\'(.*?)/is', $content)){
            //$var = preg_replace('/(.*?)\$(.*?)\s=(.*)/is', '\\2', $content);
            if (preg_match('/(?i).*?\$([^;\x20]+)/s', $content, $regs)) {
               $var = $regs[1];
               $conf_vars[] = array($var, $filename);
            }
        }
    }
    sort($conf_vars);
    return $conf_vars;
}

function save_rss_config($var, $config){
    if(hook_check(__FUNCTION__)) return hook();
    $conf_vars = detected_rss_var();
    $file_config = array();
    foreach($conf_vars as $key=>$value) if($value[0]==$var) $file_config = $value[1];
    if(!empty($file_config)) {
        main::init_function('sources');
        foreach($config as $key => $value) $_POST[$key] = $value;
        save_config($file_config, '$'.$var, $config);
    }
}
?>