<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!"); 

global $main, $break_load, $navi;
$break_load = false;
if(is_moder()) {
    warning($main->lang['moder_error']);
    $break_load = true;
} elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array("delete_tmp_dir", "delete_tmp_dir"),
    array("config", "config")
);

function main_attach(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $result = $main->db->sql_query("SELECT a.*, u.* FROM ".ATTACH." AS a LEFT JOIN ".USERS." AS u ON(a.user_id=u.uid) ORDER BY id DESC LIMIT {$offset}, 30");
    $rows_c = $main->db->sql_numrows($result);
    if($rows_c>0){
        $tr = 'row1'; 
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['dir']}</th><th width='150'>{$main->lang['file']}</th><th width='80'>{$main->lang['module']}</th><th width='80'>{$main->lang['user']}</th><th width='120'>{$main->lang['date']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        while(($row = $main->db->sql_fetchrow($result))){
            $op = "<table cellspacing='1' class='cl'><tr><td>".delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$row['id']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$tr}".(preg_match('/filedata\-(.*?)/i', $row['path'])?"_warn":'')."'><td align='center'>{$i}</td><td>{$row['path']}</td><td align='center'><a target='_BLANK' href='{$row['path']}{$row['file']}'>{$row['file']}</a></td><td align='center'><a href='".$main->url(array('module' => $row['module']))."'>{$row['module']}</a></td><td align='center'>".($row['uid']!='-1' ? "<a class='author' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['user_name']}</a>" : $row['user_name'])."</td><td align='center'>".user_format_date($row['date'])."</td><td align='center'>{$op}</td></tr>";
            $tr = ($tr=='row1') ? 'row2' : 'row1';            
            $i++;
        }
        echo "</table>";
        if ($rows_c==30 OR isset($_GET['page'])){            
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".ATTACH.""));            
            //Если количество больше чем количество на страницу
            if($numrows>30){
                //Открываем стилевую таблицу
                open();                
                //создаем страницы
                pages($numrows, 30, array('module' => $main->module), true, false, array(), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else info($main->lang['noinfo']);
}

function config_attache(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $conf_vars = $conf_arr = array(); $i = 1; $row = "row1";
    foreach(load_includes('config_.*?\.php', 'includes/config/') as $filename){
        $content = file_get_contents($filename);
        if($filename!='includes/config/config_user.php' AND preg_match('/(.*?)\'attaching\'(.*?)/is', $content)){        
            $var = preg_replace('/.*?\$([^;\x20]*?)\s=.*/is', '\\1', $content);
            $conf_vars[] = array($var, $filename);
        }
    }
    sort($conf_vars);
    foreach($conf_vars as $key=>$value) eval("global $".$value[0]."; \$conf_arr['".$value[0]."'] = $".$value[0].";");        
    echo "<table class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['module']}</th><th width='140'>{$main->lang['dir']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
    foreach($conf_arr as $key=>$config){
        $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$key}&amp;do=config")."</td></tr></table>";
        echo "<tr class='{$row}".($config['attaching']!=ENABLED?"_warn":"")."'><td align='center'>{$i}</td><td><a href='".$main->url(array('module' => $key))."'>".(isset($main->lang[$key])?$main->lang[$key]:$key)."</a></td><td align='center'>{$config['directory']}</td><td class='col' align='center' id='onoff_{$key}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$key}', 'onoff_{$key}')\">".($config['attaching']==ENABLED ? $main->lang['on'] : $main->lang['off'])."</td><td align='center'>{$op}</td></tr>";
        $row = ($row=='row1') ? "row2" : "row1"; $i++;        
    }
    echo "</table>";
}

function on_off_attach(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $conf_vars = $conf_arr = array();
    foreach(load_includes('config_.*?\.php', 'includes/config/') as $filename){
        $content = file_get_contents($filename);
        if($filename!='includes/config/config_user.php' AND preg_match('/(.*?)\'attaching\'(.*?)/is', $content)){        
            $var = preg_replace('/.*?\$([^;\x20]*?)\s=.*/is', '\\1', $content);
            $conf_vars[] = array($var, $filename);
        }
    }
    sort($conf_vars);
    foreach($conf_vars as $key=>$value) eval("global $".$value[0]."; \$conf_arr['".$value[0]."'] = $".$value[0].";");
    if($conf_arr[$_GET['id']]['attaching']==ENABLED){
        $conf_arr[$_GET['id']]['attaching']="";
        echo $main->lang['off'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    } else {
        $conf_arr[$_GET['id']]['attaching']=ENABLED;
        echo $main->lang['on'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    }
    $file_config = array();
    foreach($conf_vars as $key=>$value) if($value[0]==$_GET['id']) $file_config = $value[1];
    if(!empty($file_config)) {
        main::required("includes/function/sources.php");
        foreach($conf_arr[$_GET['id']] as $key => $value) $_POST[$key] = $value;
        save_config($file_config, '$'.$_GET['id'], $conf_arr[$_GET['id']]);
    }
}

function delete_admin_attache(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".ATTACH." WHERE id='{$_GET['id']}'"));
    unlink($info['path'].$info['file']);
    if(file_exists($info['path'].'mini-'.$info['file'])) unlink($info['path'].'mini-'.$info['file']);
    $main->db->sql_query("DELETE FROM ".ATTACH." WHERE id='{$_GET['id']}'");
    if(is_ajax()) main_attach(); else redirect(MODULE);
}

function remove_tmp_folder($dir){
    if(hook_check(__FUNCTION__)) return hook();
    if($dir[mb_strlen($dir)-1]!="/") $dir .= "/";
    if(file_exists($dir) AND $handle = opendir($dir)){
        while(($obj = readdir($handle))){
            if($obj!="." AND $obj!=".."){
                if(is_dir($dir.$obj) AND !preg_match('/filedata\-(.*?)/i', $obj)){
                    if(!remove_tmp_folder($dir.$obj)) return false;
                } elseif(is_dir($dir.$obj) AND preg_match('/filedata\-(.*?)/i', $obj)) remove_dir($dir.$obj);
            }
       }
       closedir($handle);
       return true;
   }
   return true;
}

function delete_tmp_dir_attach(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    remove_tmp_folder('uploads/');
    $main->db->sql_query("DELETE FROM ".ATTACH." WHERE path LIKE '%filedata-%'");
    redirect(MODULE);
}
function switch_admin_attach(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "delete_tmp_dir" : delete_tmp_dir_attach(); break;
         case "on_off" : on_off_attach(); break;
         case "delete": delete_admin_attache(); break;
         case "config": config_attache(); break;
         default: main_attach(); break;
      }
   } elseif($break_load==false) main_attach();
}
switch_admin_attach();
?>
