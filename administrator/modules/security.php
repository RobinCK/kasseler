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
    array('', 'home'),
    array('addip', 'addblockedip'),
    array('replace', 'content_replace'),
    array("logined_logs", "loginedlogs"),
    array('php_logs', 'phplogs'),
    array("http_logs", "httplogs"),
    array("sql_logs", "sqllogs"),
    array("config", "config")    
);

function main_security(){
global $adminfile, $main, $security;
    if(hook_check(__FUNCTION__)) return hook();
    if(count($security['look_ip'])>0){
        $tr = 'row1';
        echo "<table class='table' width='100%'><tr><th width='25'>#</th><th width='120'>IP</th><th>{$main->lang['descript']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        foreach($security['look_ip'] as $k => $val){
            $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$k}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$k}", 'ajax_content')."</td></tr></table>";
            echo "<tr class='{$tr}'><td align='center'>".($k+1)."</td><td align='center'>{$val[0]}</td><td>{$val[1]}</td><td align='center'>{$op}</td></tr>";
            $tr = ($tr=='row1') ? 'row2' : 'row1';
        }
        echo "</table>";
    } else info($main->lang['noinfo']);
}

function save_look_security($array){
global $copyright_file, $security;
    if(hook_check(__FUNCTION__)) return hook();
    $c = $copyright_file.'$security = array('."\n".
    "\t'allowed_domain' => '{$security['allowed_domain']}',\n".
    "\t'negative_domain' => '{$security['negative_domain']}',\n\t'captcha' => '{$security['captcha']}',\n\t'look_ip' => array(\n";
    foreach($array as $val) $c .= "\t\tarray('{$val[0]}', '{$val[1]}'),\n";
    $c = mb_substr($c, 0, mb_strlen($c)-1)."\n\t)\n);\n?".">";
    $file_link = "includes/config/config_security.php";
    if(is_writable($file_link)){
        $file = fopen($file_link, "w");
        fputs ($file, $c);
        fclose ($file);
    }
}

function delete_security(){
global $security;
    if(hook_check(__FUNCTION__)) return hook();
    unset($security['look_ip'][$_GET['id']]);
    save_look_security($security['look_ip']);
    if(is_ajax()) main_security(); else redirect(MODULE);
}

function edit_ip_security($msg=''){
global $security, $main, $adminfile;    
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}' method='post'>\n<table align='center' class='form' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>IP:<span class='star'>*</span></td><td class='form_input'>".in_text('ip', 'input_text2', $security['look_ip'][$_GET['id']][0])."</td></tr>\n".
    "<tr><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".in_area('description', 'textarea', 3, $security['look_ip'][$_GET['id']][1])."</td></tr>\n".        
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table></form>";
}

function addip_security($msg=''){
global $security, $main, $adminfile;    
    if(!empty($msg)) warning($msg);
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=save_addip' method='post'>\n<table align='center' class='form' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>IP:<span class='star'>*</span></td><td class='form_input'>".in_text('ip', 'input_text2')."</td></tr>\n".
    "<tr><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input'>".in_area('description', 'textarea', 3)."</td></tr>\n".        
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table></form>";
}

function save_edit_ip_security(){
global $security, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($_POST['ip'])){
        $security['look_ip'][$_GET['id']] = array($_POST['ip'], $_POST['description']);
        save_look_security($security['look_ip']);
        redirect(MODULE);
    } else edit_ip_security($main->lang['allerror']);
}

function save_addip_security(){
global $security, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($_POST['ip'])){
        $security['look_ip'][] = array($_POST['ip'], $_POST['description']);
        save_look_security($security['look_ip']);
        redirect(MODULE);
    } else addip_security($main->lang['allerror']);
}

function phplogs_security(){    
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $path = "uploads/logs/php_logs.log";
    if(file_exists($path)){
        echo "<div style='overflow: auto; max-height: 698px; width: 100%; padding: 3px;'><table width='99%' class='table'><tr><th>{$main->lang['date']}</th><th>{$main->lang['line']}</th><th>{$main->lang['error']}</th><th>{$main->lang['file']}</th></tr>";
        $contents = $match = ''; $tr = 'row4';
        $handle = fopen($path, "rb");        
        while (!feof($handle)) {
            $contents .= fread($handle, 1024);
            $pos = mb_strpos($contents, "||", 0);
            if($pos>1){
                $row = explode("||", $contents);
                $contents = $row[count($row)-1];
                unset($row[count($row)-1]);
                foreach($row as $value) {                    
                    $info_arr = explode('|', $value);
                    $info_arr[0]=preg_replace(array('/\x20*\\\\\\--->[^\r\n]*[\r\n]*/si','/[\r\n]{2,4}/si'), array('',''), $info_arr[0]);
                    $info = array(
                        'date'  => trim($info_arr[0]),
                        'error' => trim($info_arr[1]),
                        'file'  => trim($info_arr[2]),
                        'line'  => trim($info_arr[3])
                    );                    
                    echo "<tr class='{$tr}'><td nowrap='nowrap' align='center'>".format_date($info['date'])."</td><td nowrap='nowrap' align='center'>".str_replace('on line ', '', $info['line'])."</td><td nowrap='nowrap'>".htmlspecialchars($info['error'])."</td><td nowrap='nowrap'>".str_replace('\\', '/', str_replace('in ', '', $info['file']))."</td></tr>";
                    $tr = ($tr=='row5') ? 'row4' : 'row5';
                }   
            };
        }
        fclose($handle);                                
        echo "</table></div><br /><div align='right'><a href='{$adminfile}?module={$main->module}&amp;do=delete_log&amp;file=uploads/logs/php_logs.log'><b>{$main->lang['clear']}</b></a></div>";
    } else info($main->lang['noinfo']);
}

function delete_log_security(){
    if(hook_check(__FUNCTION__)) return hook();
    if(file_exists($_GET['file'])) unlink($_GET['file']);
    redirect(BACK);
}

function loginedlogs_security(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $path = "uploads/logs/logined_logs.log";
    if(file_exists($path)){
        echo "<div style='overflow: auto; max-height: 698px; width: 100%; padding: 3px;'><table width='99%' class='table'><tr><th>{$main->lang['date']}</th><th>{$main->lang['error']}</th><th>{$main->lang['login']}</th><th>{$main->lang['password']}</th><th>{$main->lang['ip']}</th></tr>";
        $contents = $match = ''; $tr = 'row4';
        $handle = fopen($path, "rb");        
        while (!feof($handle)) {
            $contents .= fread($handle, 1024);
            $pos = mb_strpos($contents, "||", 0);
            if($pos>1){
                $row = explode("||", $contents);
                $contents = $row[count($row)-1];
                unset($row[count($row)-1]);
                foreach($row as $value) {                    
                    $info_arr = explode('|', $value);                    
                    $info = array(
                        'date'  => trim($info_arr[0]),
                        'error' => trim($info_arr[1]),
                        'user'  => trim($info_arr[2]),
                        'pass'  => trim($info_arr[3]),
                        'ip'    => trim($info_arr[4])
                    );
                    $_ = explode('::', $info_arr[2]);                    
                    $__ = explode('::', $info_arr[3]);
                    echo "<tr class='{$tr}'><td nowrap='nowrap' align='center'>".format_date($info['date'])."</td><td nowrap='nowrap' align='center'>".htmlspecialchars($info['error'])."</td><td nowrap='nowrap' align='center'>{$_[1]}</td><td nowrap='nowrap' align='center'>{$__[1]}</td><td nowrap='nowrap' align='center'>{$info['ip']}</td></tr>";
                    $tr = ($tr=='row5') ? 'row4' : 'row5';
                }
            }        
        }
        fclose($handle);                                
        echo "</table></div><br /><div align='right'><a href='{$adminfile}?module={$main->module}&amp;do=delete_log&amp;file=uploads/logs/logined_logs.log'><b>{$main->lang['clear']}</b></a></div>";
    } else info($main->lang['noinfo']);
}

function httplogs_security(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $path = "uploads/logs/http_logs.log";
    if(file_exists($path)){
        echo "<div style='overflow: auto; max-height: 698px; width: 100%; padding: 3px;'><table width='99%' class='table'><tr><th>{$main->lang['date']}</th><th>{$main->lang['error']}</th><th>{$main->lang['page']}</th><th>REFERER</th><th>{$main->lang['ip']}</th></tr>";
        $contents = $match = ''; $tr = 'row4';
        $handle = fopen($path, "rb");        
        while (!feof($handle)) {
            $contents .= fread($handle, 1024);
            $pos = mb_strpos($contents, "||", 0);
            if($pos>1){
                $row = explode("||", $contents);
                $contents = $row[count($row)-1];
                unset($row[count($row)-1]);
                foreach($row as $value) {                    
                    $info_arr = explode('|', $value);                    
                    if(count($info_arr)<5) continue;
                    $info = array(
                        'date'  => trim($info_arr[0]),
                        'error' => trim($info_arr[1]),
                        'url'   => trim($info_arr[2]),
                        'ref'   => trim($info_arr[3]),
                        'ip'    => trim($info_arr[4])
                    );
                    echo "<tr class='{$tr}'><td nowrap='nowrap' align='center'>".format_date($info['date'])."</td><td nowrap='nowrap' align='center'>{$info['error']}</td><td nowrap='nowrap' align='center'>{$info['url']}</td><td nowrap='nowrap' align='center'>{$info['ref']}</td><td nowrap='nowrap' align='center'>{$info['ip']}</td></tr>";
                    $tr = ($tr=='row5') ? 'row4' : 'row5';
                }
            }        
        }
        fclose($handle);                                
        echo "</table></div><br /><div align='right'><a href='{$adminfile}?module={$main->module}&amp;do=delete_log&amp;file=uploads/logs/http_logs.log'><b>{$main->lang['clear']}</b></a></div>";
    } else info($main->lang['noinfo']);
}

function sqllogs_security(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $path = "uploads/logs/sql_logs.log";
    if(file_exists($path)){
        echo "<div style='overflow: auto; max-height: 698px; width: 100%; padding: 3px;'>";
        $contents = $match = ''; $tr = 'row4';
        $handle = fopen($path, "rb");        
        while (!feof($handle)) {
            $contents .= fread($handle, 1024);
            $pos = mb_strpos($contents, "||", 0);
            if($pos>1){
                $row = explode("||", $contents);
                $contents = $row[count($row)-1];
                unset($row[count($row)-1]);
                foreach($row as $value) {                    
                    $info_arr = explode(':|:', $value);                    
                    $info = array(
                        'date'  => trim($info_arr[0]),
                        'code'  => trim($info_arr[1]),
                        'msg'   => trim($info_arr[2]),
                        'query' => trim($info_arr[3])
                    );
                    echo "<table width='100%' class='table2'><tr class='{$tr}'><td nowrap='nowrap' width='100'>{$main->lang['date']}:</td><td>".format_date($info['date'])."</td></tr><tr class='{$tr}'><td nowrap='nowrap'>{$main->lang['code_error']}:</td><td>{$info['code']}</td></tr><tr class='{$tr}'><td nowrap='nowrap'>{$main->lang['message']}:</td><td>{$info['msg']}:</td></tr><tr class='{$tr}'><td nowrap='nowrap'>{$main->lang['query_string']}:</td><td><span style='color: #7F3F3F; font-family: Courier New;'>".htmlspecialchars($info['query'])."</span></td></tr></table><hr />";
                    $tr = ($tr=='row5') ? 'row4' : 'row5';
                }
            }        
        }
        fclose($handle);                                
        echo "</div><br /><div align='right'><a href='{$adminfile}?module={$main->module}&amp;do=delete_log&amp;file=uploads/logs/sql_logs.log'><b>{$main->lang['clear']}</b></a></div>";
    } else info($main->lang['noinfo']);
}

function config_security(){
global $main, $adminfile, $config, $security;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['adminfile']}</b>:<br /><i>{$main->lang['adminfile_d']}</i></td><td class='form_input2'>".in_text('adminfile', 'input_text2', $config['adminfile'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['logined_admin']}</b>:<br /><i>{$main->lang['logined_admin_d']}</i></td><td class='form_input2'>".in_sels('logined_admin', array('0' => $main->lang['standart_method'], '1' => $main->lang['specify_method']), 'select2 chzn-search-hide', $config['logined_admin'])."</td></tr>\n".        
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['capcha_symbols']}</b>:<br /><i>{$main->lang['capcha_symbols_d']}</i></td><td class='form_input2'>".in_text('capcha_symbols', 'input_text2', $config['capcha_symbols'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['capcha_count_symbols']}</b>:<br /><i>{$main->lang['capcha_count_symbols_d']}</i></td><td class='form_input2'>".in_text('capcha_count_symbols', 'input_text2', $config['capcha_count_symbols'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['filrer_referer']}</b>:<br /><i>{$main->lang['filrer_referer_d']}</i></td><td class='form_input2'>".in_chck('filrer_referer', 'checkbox', $config['filrer_referer'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['capcha_admin_login']}</b>:<br /><i>{$main->lang['capcha_admin_login_d']}</i></td><td class='form_input2'>".in_chck('captcha', 'checkbox', $security['captcha'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['allowed_domain']}</b>:<br /><i>{$main->lang['allowed_domain_d']}</i></td><td class='form_input'>".in_area('allowed_domain', 'textarea', 3, $security['allowed_domain'])."</td></tr>\n".
    "<tr><td class='form_text2'><b>{$main->lang['negative_domain']}</b>:<br /><i>{$main->lang['negative_domain_d']}</i></td><td class='form_input'>".in_area('negative_domain', 'textarea', 3,  $security['negative_domain'])."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function save_conf_security(){
global $config, $adminfile, $main, $security;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config.php', '$config', $config);
    $security['allowed_domain'] = $_POST['allowed_domain'];
    $security['negative_domain'] = $_POST['negative_domain'];
    $security['captcha'] = isset($_POST['captcha']) ? 'on' : '';
    save_look_security($security['look_ip']);
    redirect("{$adminfile}?module={$main->module}&do=config");
}

function replace_security(){
global $main, $replace, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_replace' method='post'><table align='center' class='form' id='form_{$main->module}'>".    
    "<tr class='row_tr'><td class='form_text2' width='130'>{$main->lang['in_replace']}:</td><td style='padding: 5px;'>".in_area('in', 'textarea', 5, $replace['in'])."</td></tr>".
    "<tr><td class='form_text2'>{$main->lang['out_replace']}:</td><td style='padding: 5px;'>".in_area('out', 'textarea', 5, $replace['out'])."</td></tr>".             
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}
function save_replace_security(){
global $main, $replace, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('config_replace.php', '$replace', $replace);
    redirect("{$adminfile}?module={$main->module}&do=replace");
}
function switch_admin_security(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){       
         default: main_security(); break;
         case "php_logs": phplogs_security(); break;
         case "logined_logs": loginedlogs_security(); break;
         case "http_logs": httplogs_security(); break;
         case "config": config_security(); break;
         case "save_conf": save_conf_security(); break;
         case "sql_logs": sqllogs_security(); break;
         case "replace": replace_security(); break;
         case "save_replace": save_replace_security(); break;
         case "delete": delete_security(); break;
         case "edit": edit_ip_security(); break;
         case "save_edit": save_edit_ip_security(); break;
         case "addip": addip_security(); break;
         case "save_addip": save_addip_security(); break;
         case "delete_log": delete_log_security(); break;
      }
   } elseif($break_load==false) main_security();
}
switch_admin_security();
?>