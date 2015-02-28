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

function main_rss(){
global $main, $adminfile, $rss;
    if(hook_check(__FUNCTION__)) return hook();
    $config_rss = array();
    $conf_arr = array(); $i = 1; $row = "row1";
    $conf_vars = detected_rss_var();    
    foreach($conf_vars as $key=>$value) eval("global $".$value[0]."; \$conf_arr['".$value[0]."'] = $".$value[0].";");        
    echo "<table class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['title']}</th><th width='120'>{$main->lang['module']}</th><th width='80'>{$main->lang['limit']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
    foreach($conf_arr as $key=>$config){
        $config_rss[$key] = $config['rss_title'].'|'.$config['rss'];
        $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$key}&amp;do=config")."</td></tr></table>";
        echo "<tr class='{$row}".($config['rss']!=ENABLED?"_warn":"")."'><td align='center'>{$i}</td><td>{$config['rss_title']}</td><td align='center'><a href='".$main->url(array('module' => $key))."'>".(isset($main->lang[$key])?$main->lang[$key]:$key)."</a></td><td align='center'>{$config['rss_limit']}</td><td class='col' align='center' id='onoff_{$key}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$key}', 'onoff_{$key}')\">".($config['rss']==ENABLED ? $main->lang['on'] : $main->lang['off'])."</td><td align='center'>{$op}</td></tr>";
        $row = ($row=='row1') ? "row2" : "row1"; $i++;
    }
    echo "</table>";
    if($config_rss!=$rss){
        main::init_function('sources');
        foreach($config_rss as $key => $value) $_POST[$key] = $value;
        save_config("includes/config/config_rss.php", '$rss', $config_rss);
    }
}

function on_off_rss(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $conf_vars = detected_rss_var();
    $conf_arr = array();
    foreach($conf_vars as $value) eval("global $".$value[0]."; \$conf_arr['".$value[0]."'] = $".$value[0].";");
    if($conf_arr[$_GET['id']]['rss']==ENABLED){
        $conf_arr[$_GET['id']]['rss']="";
        echo $main->lang['off'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $conf_arr[$_GET['id']]['rss']=ENABLED;
        echo $main->lang['on'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
    save_rss_config($_GET['id'], $conf_arr[$_GET['id']]);
}
function switch_admin_rss(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         case "on_off" : on_off_rss(); break;
         default: main_rss(); break;
      }
   } elseif($break_load==false) main_rss();
}
switch_admin_rss();
?>