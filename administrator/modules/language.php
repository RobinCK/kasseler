<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load;
$break_load = false;
if(is_moder()) {
    warning($main->lang['moder_error']);
    $break_load = true;
} elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'total'),
    array('userlang', 'user_lang')
);

function main_language(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    //info($main->lang['info_lang']);
    $langs = array();
    $path = 'includes/language/';
    if(($handle = opendir($path))){
        while(false !== ($file = readdir($handle))) if($file!='.' AND $file!='..' AND $file!='.svn' AND is_dir($path.$file)) $langs[] = $file;
        closedir($handle);
    }
    sort($langs);
    foreach($langs as $value){
        echo "<br /><h2>".(isset($main->lang[$value])?$main->lang[$value]:$value)."</h2>";
        echo "<table cellspacing='1' class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['name_file']}</th><th width='100'>{$main->lang['file_size']}</th><th width='150'>{$main->lang['count_lang']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        $i=1;
        $row = "row1";
        $path = "includes/language/{$value}/";
        if(($handle = opendir($path))){
            while(false !== ($file = readdir($handle))){
                if(preg_match('/(.+?)\.php$/i', $file)){
                    $match = "";
                    preg_match('/\s=\sarray\((.*)\);/is', file_get_contents("includes/language/{$value}/{$file}"), $match);
                    $r = explode("\n", $match[1]);
                    foreach($r as $rows){
                        $_arr = explode('=>', $rows);
                        if(count($_arr)<2) continue;
                        $key = trim($_arr[0]); $key = mb_substr($key, 1, mb_strlen($key)); $key = mb_substr($key, 0, mb_strlen($key)-1);
                        $val = trim($_arr[1]); $val = mb_substr($val, 1, mb_strlen($val)); $val = mb_substr($val, 0, mb_strlen($val)-1); $val = ($val[mb_strlen($val)-1]=="'") ? mb_substr($val, 0, mb_strlen($val)-1) : $val;
                        $_lang[$key] = htmlspecialchars(stripslashes($val));
                    }
                    $r = $_lang;
                    unset($_lang);
                    $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$_GET['module']}&amp;do=edit&amp;id={$file}&amp;dir={$path}").delete_button("{$adminfile}?module={$_GET['module']}&amp;do=delete&amp;id={$file}&amp;dir={$path}", 'ajax_content')."</td></tr></table>";
                    echo "<tr class='{$row}'><td class='col' align='center'>{$i}</td><td>{$file}</td><td align='center'>".get_size(filesize("includes/language/{$value}/{$file}"))."</td><td align='center'>".count($r)."</td><td align='center'>{$op}</td></tr>\n";
                    $row = ($row=="row1") ? "row2" : "row1";
                    $i++;
                }
            }
            closedir($handle);
        }
        echo "</table><br />";
    }  
}

function delang(){
    if(hook_check(__FUNCTION__)) return hook();
    unlink($_GET['dir'].$_GET['id']);
    if(dir_file_count($_GET['dir'], '/(.+?)\.php$/i')==2) remove_dir($_GET['dir']);
    if(is_ajax()) main_language(); else redirect(MODULE);
}

function edit_language(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<h2>{$main->lang['name_file']}: {$_GET['id']}</h2><br />";
    $match = "";
    preg_match('/\s=\sarray\((.*)\);/is', file_get_contents($_GET['dir'].$_GET['id']), $match);
    $r = explode("\n", $match[1]);
    echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save&amp;file={$_GET['dir']}{$_GET['id']}'>\n".
    "<table class='form' width='100%'><tr><th>{$main->lang['key']}</th><th>{$main->lang['value']}</th></tr>";
    foreach($r as $row){
        $_arr = explode('=>', $row);
        if(count($_arr)<2) continue;
        $key = trim($_arr[0]); $key = mb_substr($key, 1, mb_strlen($key)); $key = mb_substr($key, 0, mb_strlen($key)-1);
        $value = preg_replace('/\'(.*?)\'(.*)/', '\\1', trim($_arr[1]));                 
        if($value[mb_strlen($value)-1]=="'") $value = mb_substr($value, 0, mb_strlen($value)-1);
        echo "<tr class='row_tr'><td class='form_text'>{$key}:</td><td class='form_input'>".in_text($key, 'input_text2', str_replace("\\'", "'", $value))."</td></tr>";
    }
    echo "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n</table></form>";
}

function save_language(){
global $magic_quotes, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $magic_quotes = false; $lang = array(); $match = "";
    preg_match('/\s=\sarray\((.*)\);/is', file_get_contents($_GET['file']), $match);
    $r = explode("\n", $match[1]);
    foreach($r as $row){
        $_arr = explode('=>', $row);
        if(count($_arr)<2) continue;
        $key = trim($_arr[0]); $key = mb_substr($key, 1, mb_strlen($key)); $key = mb_substr($key, 0, mb_strlen($key)-1);
        $value = trim($_arr[1]); $value = mb_substr($value, 1, mb_strlen($value)); $value = mb_substr($value, 0, mb_strlen($value)-1);
        if($value[mb_strlen($value)-1]=="'") $value = mb_substr($value, 0, mb_strlen($value)-1);
        $lang[$key] = $value;        
    }
    foreach($_POST as $key => $value) $_POST[$key] = str_replace("'", "\\'", stripslashes($value));
    main::init_function('sources');
    save_config($_GET['file'], '$lang', $lang);
    $magic_quotes = true;
    redirect(MODULE);
}

function user_lang(){
global $mylang, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $path = 'includes/language/';
    $langs = array();
    if(($handle = opendir($path))){
        while(false !== ($file = readdir($handle))){
            if(is_dir($path.$file) AND $file!='.' AND $file!='..' AND $file!='.svn') $langs[] = $file;
        }
        closedir($handle);
    }
    sort($langs);
    $clr = "<table width='100%' cellpadding='2'><tr class='row_tr'><td width='200'><input name='key[]' class='input_text' style='width: 100%' type='text' value='' /></td><td><table width='95%' cellpadding='2' style='margin-left: 15px;'>";
        for($i=0; $i<count($langs); $i++){
            $clr .= "<tr><td width='100'>".(isset($main->lang[$langs[$i]])?$main->lang[$langs[$i]]:$langs[$i]).":</td><td><input class='input_text' name='value[]' style='width: 100%' type='text' value='' /></td></tr>";
        }        
    $clr .= "</table></td></tr></table>";
    echo "<form action='{$adminfile}?module={$_GET['module']}&amp;do=saveuserlang' method='post'><table width='100%' cellpadding='2' class='form'>
    <tr><th>{$main->lang['vari']}</th><th>{$main->lang['vari_value']}</th></tr>";
    foreach($mylang as $key=>$array){
        echo "<tr class='row_tr'><td width='200'><input name='key[]' class='input_text' style='width: 100%' type='text' value='".htmlspecialchars($key, ENT_QUOTES)."' /></td><td><table width='95%' cellpadding='2' style='margin-left: 15px;'>";
        for($i=0; $i<count($langs); $i++){
            if(isset($array[$langs[$i]])) echo "<tr><td width='100'>".(isset($main->lang[$langs[$i]])?$main->lang[$langs[$i]]:$langs[$i]).":</td><td><input class='input_text' name='value[]' style='width: 100%' type='text' value='".htmlspecialchars($array[$langs[$i]], ENT_QUOTES)."' /></td></tr>";
            else echo "<tr><td width='100'>".(isset($main->lang[$langs[$i]])?$main->lang[$langs[$i]]:$langs[$i]).":</td><td><input class='input_text' name='value[]' style='width: 100%' type='text' value='' /></td></tr>";
        }
        echo "</table></td></tr>";
    }
    echo "<tr><td colspan='2' id='insert_vars' style='padding: 0px;'></td></tr>".
    "<tr><td class='form_submit' colspan='2' align='right'><input class='submit' type='submit' onclick='addvari(); return false;' value='{$main->lang['add']}' /></td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".    
    "</table></form>";
echo "<script type='text/javascript'>
<!--
function addvari(){
    $$('insert_vars').innerHTML = $$('insert_vars').innerHTML + \"{$clr}\";
}
// -->
</script>";
}

function save_user_lang(){
global $main, $adminfile, $copyright_file;
    if(hook_check(__FUNCTION__)) return hook();
    $path = 'includes/language/';
    $langs = array();
    if(($handle = opendir($path))){
        while(false !== ($file = readdir($handle))){
            if(is_dir($path.$file) AND $file!='.' AND $file!='..' AND $file!='.svn') $langs[] = $file;
        }
        closedir($handle);
    }
    sort($langs);
    $y = 0;
    $str = '$mylang = array(';
    foreach($_POST['key'] AS $key){
        if(!empty($key)){
            $str .= "\n\t'{$key}' => array(";
            for($i=0; $i<count($langs); $i++){
                $str .= "'{$langs[$i]}' => '{$_POST['value'][$y]}', ";
                $y++;
            }  
            $str .= "),";
        } else $y+=3;
    }
    file_write('includes/config/config_userlang.php', $copyright_file.$str."\n);\n?".">");
    redirect("{$adminfile}?module={$main->module}&do=userlang");
}
function switch_admin_language(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "delete": delang(); break;
         case "edit": edit_language(); break;
         case "save": save_language(); break;
         case "userlang": user_lang(); break;
         case "saveuserlang": save_user_lang(); break;
         default: main_language(); break;
      }
   } elseif($break_load==false) main_language();
}
switch_admin_language();
?>
