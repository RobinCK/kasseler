<?php
 /**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $navi, $main, $break_load, $config;
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
    array('backup', 'backup_db'),
    array("sql", "sql_db"),
    array("config", "config")
);

$config['log_debugging_sql'] = '';
$config['mode_debugging_sql'] = '';

function main_database(){
global $main, $adminfile, $database;
    if(hook_check(__FUNCTION__)) return hook();
    $table = array();
    $result = $main->db->sql_query("SHOW TABLES FROM `{$database['name']}`");
    while(list($name) = $main->db->sql_fetchrow($result)) $table[$name] = $name;
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=operations' method='post'><table width='100%'><tr>\n".
    "<td width='200'>".in_sels('tables', $table, 'select2 chzn-none', $table, "", true)."</td>".
    "<td valign='top' style='padding-left: 20px;'>".in_radio('op', 'optimization', $main->lang['optimization_db'], 'op1', true)."<br />{$main->lang['optimization_db_d']}<br />".in_radio('op', 'repair', $main->lang['repair_db'], 'op2')."<br />{$main->lang['repair_db_d']}</td>".
    "</tr><tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n</table></form>\n";    
}

function operations(){
global $main, $database;
    if(hook_check(__FUNCTION__)) return hook();
    $info = array(); $i = 1; $tr = 'row1'; $total = 0;
    if($_POST['op']=='optimization'){        
        $result = $main->db->sql_query("SHOW TABLE STATUS FROM `{$database['name']}`");
        while(($row = $main->db->sql_fetchrow($result))){
            if(empty($row[0]) OR !in_array($row[0], $_POST['tables'])) continue;
            $info[] = array('name' => $row[0], 'size' => $row['Data_length'] + $row['Index_length'], 'rows' => $row['Rows'], 'end_size' => ($row['Data_free'])?$row['Data_free']:0);
            $main->db->sql_query("OPTIMIZE TABLE {$row[0]}");            
        }
        echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['name_table']}</th><th width='90'>{$main->lang['size_table']}</th><th width='90'>{$main->lang['end_size_table']}</th><th width='100'>{$main->lang['optimize_table']}</th><th width='60'>{$main->lang['rows_table']}</th></tr>";
        foreach($info as $set){
            $total += $set['size']-$set['end_size'];
            echo "<tr class='{$tr}'><td align='center'>{$i}</td><td>{$set['name']}</td><td align='center'>".get_size($set['size'])."</td><td align='center'>".get_size($set['size']-$set['end_size'])."</td><td align='center'>".(($set['end_size']!=0)?"<span style='color: green;'>".get_size($set['end_size'])."</span>":"<span style='color: red;'>".get_size($set['end_size'])."</span>")."</td><td align='center'>{$set['rows']}</td></tr>\n";
            $tr = ($tr=='row1') ? 'row2' : 'row1'; $i++;
        }
        echo "<tr><td colspan='3'><b>{$main->lang['all_size_table']}</b></td><td align='center'><b style='color: green;'>".get_size($total)."</b></td><td colspan='2'>&nbsp;</td></tr></table><br />";
    } else {        
        $result = $main->db->sql_query("SHOW TABLE STATUS FROM `{$database['name']}`");
        while(($row = $main->db->sql_fetchrow($result))){
            if(empty($row[0]) OR !in_array($row[0], $_POST['tables'])) continue;
            $info[] = array('name' => $row[0], 'size' => $row['Data_length'] + $row['Index_length'], 'rows' => $row['Rows']);            
        }
        echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['name_table']}</th><th width='90'>{$main->lang['size_table']}</th><th width='60'>{$main->lang['rows_table']}</th><th width='80'>{$main->lang['status']}</th></tr>";
        foreach($info as $set){
            $result = $main->db->sql_query("REPAIR TABLE {$set['name']}");            
            echo "<tr class='{$tr}'><td align='center'>{$i}</td><td>{$set['name']}</td><td align='center'>".get_size($set['size'])."</td><td align='center'>{$set['rows']}</td><td align='center'>".(!$result?"<span style='color: red;'>{$main->lang['status_error']}</span>":"<span style='color: green;'>{$main->lang['status_ok']}</span>")."</td></tr>";
            $tr = ($tr=='row1') ? 'row2' : 'row1'; $i++;
        }
        echo "</table><br />";        
    }
    main_database();
}

function backup_options(){
global $main, $adminfile, $database;
    if(hook_check(__FUNCTION__)) return hook();
    $table = array();
    $_arr = scan_dir('uploads/backup/', '/(.+?)\.sql/i', true);krsort($_arr);
    $result = $main->db->sql_query("SHOW TABLE STATUS FROM `{$database['name']}`");
    while(($row = $main->db->sql_fetchrow($result))) $table[$row[0]] = $row[0]." [{$row['Rows']}]";
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=backuper_op' method='post'><table width='100%'><tr>\n".
    "<td colspan='2'><b>{$main->lang['list_table_backup']}</b>:</td></tr>".
    "<tr><td width='200'>".in_sels('tables', $table, 'select2 chzn-none', $table, "", true)."</td>".
    "<td valign='top' style='padding-left: 20px;'>".in_radio('op', 'backup', $main->lang['backup_db2'], 'op1', true)."<br />".in_radio('op', 'restore', $main->lang['restore_db'], 'op2')."<br />".in_sels('restore_file', $_arr, 'selects_dump chzn-none', "", " onclick=\"document.getElementById('op2').checked = true;\"", false, 5)."</td>".
    "</tr><tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n</table></form>\n";
}

function backuper_op(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_class('backup');
    $backup = new backuper;
    $backup->dir = 'uploads/backup/';
    if($_POST['op']=='backup'){        
        $backup->prefix = "{$main->user['user_id']}_";
        if(isset($_POST['tables']) AND !empty($_POST['tables'])) $backup->tables = $_POST['tables'];
        $backup->backup();
        backup_options();
    } else {
        $backup->filename = $_POST['restore_file'];
        $backup->restore();
        backup_options();
    }
}

function sql_query_editor($value=''){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    main::add2link('includes/javascript/codemirror/lib/codemirror.css');
    main::add2link('includes/javascript/codemirror/lib/util/simple-hint.css');
    main::add2link('includes/javascript/codemirror/theme/neat.css');
    main::add2script('includes/javascript/codemirror/lib/codemirror.js');
    main::add2script('includes/javascript/codemirror/lib/util/simple-hint.js');
    main::add2script('includes/javascript/codemirror/lib/util/javascript-hint.js');
    main::add2script('includes/javascript/codemirror/mode/sql/sql.js');
    main::add_css2head(".CodeMirror-scroll {height: auto; overflow-y: hidden; overflow-x: auto; width: 100%; min-height: 60px;}");
    return "<form id='form_query' action='{$adminfile}?module={$main->module}&amp;do=sql_query' method='post'>".
    "<div style='font-size: 10px; color: #aaaaaa; text-align:right;'>{$main->lang['your_used_vars']}: {prefix} {date} {datetime} {charset} {user}</div>".
    in_area('query', 'textarea_query', 4, $value).
    "<div align='center'><br /><input type='submit' value='{$main->lang['run_request']}' class='admin_button' /></div>".
    "</form><br /><hr />".
    '<script type="text/javascript">
            var editor = CodeMirror.fromTextArea(document.getElementById("query"), {
                lineNumbers: true,
                theme: "neat",
                onCursorActivity: function() {
                    editor.setLineClass(hlLine, null);
                    hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
                },
                extraKeys: {
                    "F11": function() {
                        var scroller = editor.getScrollerElement();
                        if (scroller.className.search(/\bCodeMirror-fullscreen\b/) === -1) {
                            scroller.className += " CodeMirror-fullscreen";
                            scroller.style.height = "100%";
                            scroller.style.width = "100%";
                            scroller.style.position = "absolute";
                            editor.refresh();
                            $("body").css({"overflow-y": "hidden"});
                        } else {
                            scroller.className = scroller.className.replace(" CodeMirror-fullscreen", "");
                            scroller.style.height = "";
                            scroller.style.width = "";
                            scroller.style.position = "";
                            editor.refresh();
                            $("body").css({"overflow-y": ""});
                        }
                    },
                    "Esc": function() {
                         var scroller = editor.getScrollerElement();
                         if (scroller.className.search(/\bCodeMirror-fullscreen\b/) !== -1) {
                            scroller.className = scroller.className.replace(" CodeMirror-fullscreen", "");
                            scroller.style.height = "";
                            scroller.style.width = "";
                            scroller.style.position = "";
                            editor.refresh();
                            $("body").css({"overflow-y": ""});
                         }
                    },
                    "Ctrl-Space": function(cm) {CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);}
                }
            });
            var hlLine = editor.setLineClass(0, "activeline");
      </script>';
}

function manager_sql(){
global $main, $adminfile, $database;
    if(hook_check(__FUNCTION__)) return hook();
    echo sql_query_editor();
    $tr = "row1";    
    $result = $main->db->sql_query("SHOW TABLE STATUS FROM `{$database['name']}`");
    echo "<br /><br /><table width='100%' class='table'><tr><th>{$main->lang['name_table']}</th><th width='50'>{$main->lang['rows_table']}</th><th width='70'>{$main->lang['sizes']}</th><th width='120'>{$main->lang['create_date']}</th><th width='120'>{$main->lang['update_date']}</th><th width='55'>{$main->lang['type_table']}</th><th width='70'>{$main->lang['charset_db']}</th><th width='120'>{$main->lang['functions']}</th></tr>";
    while(($row = $main->db->sql_fetchrow($result))){
        $op = "<a href='{$adminfile}?module={$main->module}&amp;do=sql_struct&amp;id={$row['Name']}' class='admino ico_view pixel' title='{$main->lang['struct_table']}'></a>".insert_button("{$adminfile}?module={$main->module}&amp;do=sql_insert&amp;id={$row['Name']}").clear_button("{$adminfile}?module={$main->module}&amp;do=sql_clear&amp;id={$row['Name']}", 'ajax_content').delete_button("{$adminfile}?module={$main->module}&amp;do=sql_delete&amp;id={$row['Name']}", 'ajax_content');
        echo "<tr class='{$tr}'><td><a href='{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$row['Name']}'>{$row['Name']}</a></td><td class='sql_manager_col' align='center'>{$row['Rows']}</td><td align='center' class='sql_manager_col'>".get_size($row['Data_length']+$row['Index_length'])."</td><td align='center' class='date_col'>".format_date($row['Create_time'], $main->config['date_format']." H:i:s")."</td><td align='center' class='date_col'>".format_date($row['Update_time'], $main->config['date_format']." H:i:s")."</td><td align='center' class='sql_manager_col'>{$row['Engine']}</td><td align='center' class='charset_col'>{$row['Collation']}</td><td align='center'>{$op}</td></tr>";
        $tr = ($tr=='row1') ? 'row2' : 'row1';
    }
    echo "</table>";
}

function manager_sql_struct(){
global $main, $adminfile, $database; 
    if(hook_check(__FUNCTION__)) return hook();
    echo sql_query_editor();
    $tr = "row1";    
    $result = $main->db->sql_query("SHOW COLUMNS FROM {$_GET['id']}");
    echo "<b>{$main->lang['database']}</b>: <a href='{$adminfile}?module={$main->module}&amp;do=sql' title='{$main->lang['database']}'>{$database['name']}</a> » <a href='{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$_GET['id']}' title='{$main->lang['show_query']}'>{$_GET['id']}</a> [ {$main->lang['structure']} | <a href='{$adminfile}?module={$main->module}&amp;do=sql_insert&amp;id={$_GET['id']}' title='{$main->lang['insert']}'>{$main->lang['insert']}</a> | <a href='{$adminfile}?module={$main->module}&amp;do=sql_delete&amp;id={$_GET['id']}' onclick=\"if(!confirm('{$main->lang['realdelete']}')) return false;\">{$main->lang['delete']}</a> ]<br /><hr /><table width='100%' class='table'><tr><th>{$main->lang['field']}</th><th width='150'>{$main->lang['type_col']}</th><th width='50'>NULL</th><th width='50'>{$main->lang['key_col']}</th><th width='150'>{$main->lang['default_col']}</th><th width='120'>{$main->lang['extra_col']}</th></tr>";
    while(($row = $main->db->sql_fetchrow($result))){
        echo "<tr class='{$tr}'><td>{$row['Field']}</td><td align='center'>{$row['Type']}</td><td align='center'>".($row['Null']=='YES'?"<span style='color: green;'>{$main->lang['yes2']}</span>":"<span style='color: red;'>{$main->lang['no']}</span>")."</td><td align='center'>{$row['Key']}</td><td align='center'>".((!isset($row['Default']) AND $row['Null']=='YES')?"<span style='color: #AAAAAA;'>NULL</span>":$row['Default'])."</td><td align='center'>{$row['Extra']}</td></tr>";
    }
    echo "</table>";
}

function manager_sql_clear(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DELETE FROM {$_GET['id']}");
    if(is_ajax()) manager_sql(); else redirect("{$adminfile}?module={$main->module}&do=sql");
}

function manager_sql_delete(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("DROP TABLE IF EXISTS `{$_GET['id']}`");
    if(is_ajax()) manager_sql(); else redirect("{$adminfile}?module={$main->module}&do=sql");
}

function manager_sql_insert($msg=""){
global $main, $adminfile, $database; 
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SHOW COLUMNS FROM {$_GET['id']}");
    if(!empty($msg)) warning($msg);
    echo sql_query_editor();
    echo "<b>{$main->lang['database']}</b>: <a href='{$adminfile}?module={$main->module}&amp;do=sql' title='{$main->lang['database']}'>{$database['name']}</a> » <a href='{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$_GET['id']}' title='{$main->lang['show_query']}'>{$_GET['id']}</a> [ <a href='{$adminfile}?module={$main->module}&amp;do=sql_struct&amp;id={$_GET['id']}'>{$main->lang['structure']}</a> | {$main->lang['insert']} | <a href='{$adminfile}?module={$main->module}&amp;do=sql_delete&amp;id={$_GET['id']}' onclick=\"if(!confirm('{$main->lang['realdelete']}')) return false;\">{$main->lang['delete']}</a> ]<br /><hr /><form action='{$adminfile}?module={$main->module}&amp;do=sql_insert_manager_set&amp;id={$_GET['id']}' method='post'><table width='100%' class='form'><tr><th width='80'>{$main->lang['field']}</th><th width='120'>{$main->lang['type_col']}</th><th width='30'>NULL</th><th>{$main->lang['value']}</th></tr>";
    while(($row = $main->db->sql_fetchrow($result))){
        $type = preg_replace('/(.+?)\((.*)/i', '\\1', $row['Type']);
        switch($type){
            case "text": $input_box = in_area($row['Field'], 'textarea', 4); break;
            case "tinyblob": $input_box = in_area($row['Field'], 'textarea', 4); break;
            case "blob": $input_box = in_area($row['Field'], 'textarea', 4); break;
            case "mediumblob": $input_box = in_area($row['Field'], 'textarea', 5); break;
            case "longblob": $input_box = in_area($row['Field'], 'textarea', 6); break;
            case "tinytext": $input_box = in_area($row['Field'], 'textarea', 4); break;
            case "mediumtext": $input_box = in_area($row['Field'], 'textarea', 5); break;
            case "longtext": $input_box = in_area($row['Field'], 'textarea', 6); break;
            case "varchar": $input_box = in_text($row['Field'], 'input_text2'); break;
            default: $input_box = in_text($row['Field'], 'input_text'); break; 
        }
        echo "<tr class='row_tr'><td>{$row['Field']}:</td><td align='center' class='sql_manager_col'>{$row['Type']}</td><td align='center' class='sql_manager_col'>".($row['Null']=='YES'?"<span style='color: green;'>{$main->lang['yes2']}</span>":"<span style='color: red;'>{$main->lang['no']}</span>")."</td><td class='form_input'>{$input_box}</td></tr>\n";
    }
    echo "<tr><td class='form_submit' colspan='4' align='center'>".send_button()."</td></tr></table></form>";
}

function manager_sql_insert_set(){
global $main, $adminfile; 
    if(hook_check(__FUNCTION__)) return hook();
    $cols = array(); $insert = array();
    $result = $main->db->sql_query("SHOW COLUMNS FROM {$_GET['id']}");
    while(($row = $main->db->sql_fetchrow($result))) $cols[] = $row['Field'];
    foreach($_POST as $key => $value) if(in_array($key, $cols)) $insert["{$key}"] = $value;    
    $ins = sql_insert($insert, $_GET['id']);
    $msg = (!$ins) ? "<li class='error'>#".$main->db->errno.": ".$main->db->error."</li>" : "";
    if(empty($msg)) redirect("{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$_GET['id']}");
    else manager_sql_insert($msg);
}

function manager_sql_query(){
global $main, $adminfile, $database;    
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($_GET['id']) AND !empty($_POST['query'])){
       	$match = "";
        preg_match_all('/('.$database['prefix'].'[^\s]+)/i', $_POST['query'], $match);
        if(isset($match[0][0])) $_GET['id'] = $match[0][0];
        else $_GET['id'] = '';
    }    
    if(empty($_POST['query']) AND empty($_GET['id'])) redirect(MODULE);
    $query = (isset($_POST['query'])) ? $_POST['query'] : "SELECT * FROM {$_GET['id']} LIMIT 0, 30";
    preg_match_all('/('.$database['prefix'].'[^\s]+)/i', $query, $match);
    $func_col = count($match[0])>1 ? false : true;
    echo sql_query_editor($query);
    echo "<b>{$main->lang['database']}</b>: <a href='{$adminfile}?module={$main->module}&amp;do=sql' title='{$main->lang['database']}'>{$database['name']}</a> » <a href='{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$_GET['id']}' title='{$main->lang['show_query']}'>{$_GET['id']}</a> [ <a href='{$adminfile}?module={$main->module}&amp;do=sql_struct&amp;id={$_GET['id']}'>{$main->lang['structure']}</a> | <a href='{$adminfile}?module={$main->module}&amp;do=sql_insert&amp;id={$_GET['id']}'>{$main->lang['insert']}</a> | <a href='{$adminfile}?module={$main->module}&amp;do=sql_delete&amp;id={$_GET['id']}' onclick=\"if(!confirm('{$main->lang['realdelete']}')) return false;\">{$main->lang['delete']}</a> ]<br /><hr />";
    
    //replace vars
    $query = str_ireplace(array(
        '{prefix}',
        '{date}',
        '{datetime}',
        '{charset}',
        '{user}',
    ), array(
        $database['prefix'],
        kr_datecms("Y-m-d"),
        kr_datecms("Y-m-d H:i:s"),
        $database['charset'],
        $main->user['user_name'],
    ), $query);
    $_query_exp = explode(";\r", $query);
    $querys = array();
    foreach($_query_exp as $v){$t = trim($v); if(!empty($t)) $querys[] = $t;}
    $_count_querys = count($querys);
    if($_count_querys==1) $result = $main->db->sql_query(stripslashes($query));
    if(preg_match('#\(*([^\s]+)#i', $query, $match)) $command=mb_strtoupper($match[1]);
    else $command = "";
    if($_count_querys>1 OR $result){
        if($_count_querys==1 AND ($command=='SELECT' OR $command=='SHOW')){
            $page_conf = array('limit' => 0, 'page' => 0);            
            if(preg_match('/(.*?)LIMIT(.+)/i', $query)){                
                $limits = trim(preg_replace('/(.*?)LIMIT(.+)/i', '\\2', $query));
                $arr_lim = explode(',', $limits);
                if(count($arr_lim)==2) $page_conf = array('limit' => trim($arr_lim[1]), 'page' => trim($arr_lim[0])/trim($arr_lim[1]));
                elseif(count($arr_lim)==1) $page_conf = array('limit' => trim($arr_lim[0]), 'page' => 0);                
            }
            if($page_conf['limit']!=0){                                
                $page_conf['page']++;
                if(is_int($page_conf['page'])){
                    $num = $page_conf['page'];                    
                    $total_query = stripslashes(preg_replace('/(.*?)LIMIT(.+)/i', '\\1', $query));
                    list($total) = $main->db->sql_fetchrow($main->db->sql_query(stripslashes(preg_replace('/SELECT(.*?)FROM(.*)/i', 'SELECT COUNT(*) FROM\\2', preg_replace('/(.*?)LIMIT(.+)/i', '\\1', $query)))));
                    $param = array(
                        'query' => addslashes(str_replace("\n", '', $total_query)),
                        'limit' => $page_conf['limit'],
                        'page'  => $page_conf['page'],
                    );
                    echo multi_page($total, $page_conf['limit'], $page_conf['page'], $param);
                }
            }
            $cols = $main->db->sql_numfields($result); $tr = "row1";
            echo "<div class='sql_manager_query'><table class='table'><tr>".(($command!='SHOW' AND $func_col==true)?"<th style='padding: 0 10px 0 10px;' width='70'>{$main->lang['functions']}</th>":"")."<th style='padding: 0 10px 0 10px;' width='15'>#</th>";    
            for($i=0; $i<$cols; $i++) {
                $name = $main->db->sql_fieldname($i, $result);
                echo "<th style='padding: 0 10px 0 10px;'>{$name}</th>";
            }
            echo "</tr>";
            $id = isset($num) ? ((1*$num>1) ? ($page_conf['limit']*($num-1))+1 : 1*$num) : 1;
            $query_base = base64_encode($query);
            while(($row = $main->db->sql_fetchrow($result))){
                $y = 0;
                $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=row_edit&amp;id={$id}&amp;table={$_GET['id']}&amp;query={$query_base}").delete_button("{$adminfile}?module={$main->module}&amp;do=row_delete&amp;id={$id}&amp;table={$_GET['id']}&amp;query={$query_base}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";         
                echo "<tr class='{$tr}'>".(($command!='SHOW' AND $func_col==true)?"<td align='center'>{$op}</td>":"")."<td align='center'>{$id}</td>";        
                foreach($row as $value){
                    $y++;
                    if($y % 2 == 0) continue;
                    $value = (!empty($value) OR $value==0) ? htmlspecialchars($value) : "&nbsp;";            
                    echo "<td class='sql_manager_col' nowrap='nowrap'>".((mb_strlen($value)>50)?cut_char($value, 50):$value)."</td>";            
                }
                echo "</tr>";
                $tr = "row1"; $i++; $id++;
            }    
            echo "</table><br /></div>";
        } else {
            if($_count_querys>1){
                $res = array('ok' => 0);
                foreach($querys as $v){
                    $result = $main->db->sql_query(stripslashes($v));
                    if($result) $res['ok']++;
                }
                info(str_replace("{COUNT}", $res['ok'].'/'.$_count_querys, $main->lang['successful_sql']));
            } else {
                if(in_array($command, array('DELETE', 'UPDATE', 'INSERT'))) info(str_replace("{COUNT}", $main->db->sql_affectedrows(), $main->lang['successful_sql']));
                else info($main->lang['successful_sql2']);
            }
        }
    } else warning("<li class='error'>#".$main->db->errno.": ".$main->db->error."</li>");
}

function manager_row_delete(){
global $main;  
    if(hook_check(__FUNCTION__)) return hook();
    $cols = array();
    $result = $main->db->sql_query("SHOW COLUMNS FROM {$_GET['table']}");
    while(($row = $main->db->sql_fetchrow($result))) $cols[$row['Field']] = true;    
    $total_query = stripslashes(preg_replace('/(.*?)LIMIT(.+)/i', '\\1', base64_decode($_GET['query'])));
    $result = $main->db->sql_query("{$total_query} LIMIT ".($_GET['id']-1).", 1");    
    if($main->db->sql_numrows($result)>0){
        $where = "";
        $row = $main->db->sql_fetchrow($result);
        foreach($row as $key => $value) {
            if(!empty($value)) $where .= isset($cols[$key]) ? "MD5({$key})='".md5(addslashes($value))."' AND " : "";
            else $where .= isset($cols[$key]) ? "(MD5({$key})='".md5(addslashes($value))."' OR {$key} IS NULL) AND " : "";
        }
        $where = mb_substr($where, 0, mb_strlen($where)-5);
        $main->db->sql_query("DELETE FROM {$_GET['table']} WHERE {$where}");
        $_POST['query'] = base64_decode($_GET['query']);
        $_GET['id'] = $_GET['table'];
        manager_sql_query();
    } else return false;
    return true;
}

function multi_page($num, $perpage, $curpage, $param){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $multipage = '';
    if($num > $perpage) {
        $page = 10; $offset = 5; $pages = @ceil($num / $perpage);
        if($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $curpage + $page - $offset - 1;
            if($from < 1) {
                $from = 1;
                $to = $page;
            } elseif($to > $pages) {
                $to = $pages;
                $from = $pages-$page+1;
            }
        }
        $multipage = ($from>1 ? "<a href='#' onclick=\"editor.setValue('{$param['query']}LIMIT 0, {$param['limit']}'); \$('#form_query').submit(); return false;\"><b>{$main->lang['first_page']}</b></a>\n" : '').
        ($curpage > 1 ? "<a href='#' onclick=\"editor.setValue('{$param['query']}LIMIT ".($param['limit']*($param['page']-2)).", {$param['limit']}'); \$('#form_query').submit(); return false;\"><b>{$main->lang['prev_page']}</b></a>\n" : '');
        for($i = $from; $i <= $to; $i++) $multipage .= $i == $curpage ? "<span class='noselect'><b>{$i}</b></span>" : "<a href='#' onclick=\"editor.setValue('{$param['query']}LIMIT ".($param['limit']*($i-1)).", {$param['limit']}'); \$('#form_query').submit(); return false;\"><b>{$i}</b></a>\n";
        $multipage .= ($curpage < $pages ? "<a href='#' onclick=\"editor.setValue('{$param['query']}LIMIT ".($param['limit']*$param['page']).", {$param['limit']}'); \$('#form_query').submit(); return false;\"><b>{$main->lang['next_page']}</b></a>\n" : '').($to < $pages ? "<a href='#' onclick=\"editor.setValue('{$param['query']}LIMIT ".(@ceil($num/$param['limit'])*$param['limit']-$param['limit']).", {$param['limit']}'); \$('#form_query').submit(); return false;\"><b>{$main->lang['last_page']}</b></a>\n" : '');
        $multipage = $multipage ? "<div><div class='multipage'><div class='gotopage'><b>{$main->lang['page_list']}</b>: </div>{$multipage}</div><br /><br /></div>" : '';
    }
    return $multipage;
}

function manager_row_edit($msg=""){
global $main, $adminfile, $database;  
    if(hook_check(__FUNCTION__)) return hook();
    $total_query = stripslashes(preg_replace('/(.*?)LIMIT(.+)/i', '\\1', base64_decode($_GET['query'])));
    $result = $main->db->sql_query("{$total_query} LIMIT ".($_GET['id']-1).", 1");
    $query_row = $main->db->sql_fetchrow($result);
    $result = $main->db->sql_query("SHOW COLUMNS FROM {$_GET['table']}");
    if(!empty($msg)) warning($msg);
    echo sql_query_editor();
    echo "<b>{$main->lang['database']}</b>: <a href='{$adminfile}?module={$main->module}&amp;do=sql' title='{$main->lang['database']}'>{$database['name']}</a> » <a href='{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$_GET['table']}' title='{$main->lang['show_query']}'>{$_GET['table']}</a><br /><hr /><form action='{$adminfile}?module={$main->module}&amp;do=row_save_edit&amp;id={$_GET['id']}&amp;table={$_GET['table']}&amp;query={$_GET['query']}' method='post'><table width='100%' class='form'><tr><th width='80'>{$main->lang['field']}</th><th width='120'>{$main->lang['type_col']}</th><th width='30'>NULL</th><th>{$main->lang['value']}</th></tr>";
    while(($row = $main->db->sql_fetchrow($result))){
        $type = preg_replace('/(.+?)\((.*)/i', '\\1', $row['Type']);
        switch($type){
            case "text": $input_box = in_area($row['Field'], 'textarea', 4, $query_row[$row['Field']]); break;
            case "tinyblob": $input_box = in_area($row['Field'], 'textarea', 4, $query_row[$row['Field']]); break;
            case "blob": $input_box = in_area($row['Field'], 'textarea', 4, $query_row[$row['Field']]); break;
            case "mediumblob": $input_box = in_area($row['Field'], 'textarea', 5, $query_row[$row['Field']]); break;
            case "longblob": $input_box = in_area($row['Field'], 'textarea', 6, $query_row[$row['Field']]); break;
            case "tinytext": $input_box = in_area($row['Field'], 'textarea', 4, $query_row[$row['Field']]); break;
            case "mediumtext": $input_box = in_area($row['Field'], 'textarea', 5, $query_row[$row['Field']]); break;
            case "longtext": $input_box = in_area($row['Field'], 'textarea', 6, $query_row[$row['Field']]); break;
            case "varchar": $input_box = in_text($row['Field'], 'input_text2', $query_row[$row['Field']]); break;
            default: $input_box = in_text($row['Field'], 'input_text', $query_row[$row['Field']]); break; 
        }
        echo "<tr class='row_tr'><td>{$row['Field']}:</td><td align='center' class='sql_manager_col'>{$row['Type']}</td><td align='center' class='sql_manager_col'>".($row['Null']=='YES'?"<span style='color: green;'>{$main->lang['yes2']}</span>":"<span style='color: red;'>{$main->lang['no']}</span>")."</td><td class='form_input'>{$input_box}</td></tr>\n";
    }
    echo "<tr><td class='form_submit' colspan='4' align='center'>".send_button()."</td></tr></table></form>";
}

function manager_row_save_edit(){
global $main, $adminfile;  
    if(hook_check(__FUNCTION__)) return hook();
    $cols = array(); $update = array();
    $result = $main->db->sql_query("SHOW COLUMNS FROM {$_GET['table']}");
    while(($row = $main->db->sql_fetchrow($result))) $cols[$row['Field']] = true;
    foreach($_POST as $key => $value) if(isset($cols[$key])) $update["{$key}"] = $value;            
    $where = "";
    $total_query = stripslashes(preg_replace('/(.*?)LIMIT(.+)/i', '\\1', base64_decode($_GET['query'])));
    $result = $main->db->sql_query("{$total_query} LIMIT ".($_GET['id']-1).", 1");        
    if($main->db->sql_numrows($result)>0){
        $where = "";        
        $row = $main->db->sql_fetchrow($result);
        foreach($row as $key => $value) {
            if(!empty($value)) $where .= isset($cols[$key]) ? "MD5(`{$key}`)='".md5(addslashes($value))."' AND " : "";
            else $where .= isset($cols[$key]) ? "(MD5(`{$key}`)='".md5(addslashes($value))."' OR `{$key}` IS NULL) AND " : "";
        }
        $where = mb_substr($where, 0, mb_strlen($where)-5);        
    } else return false;
    $ins = sql_update($update, $_GET['table'], $where);
    $msg = (!$ins) ? "<li class='error'>#".$main->db->errno.": ".$main->db->error."</li>" : "";
    if(empty($msg)) redirect("{$adminfile}?module={$main->module}&amp;do=sql_query&amp;id={$_GET['table']}");
    else manager_row_edit($msg);
    return true;
}

function conf_database(){
global $main, $adminfile, $database;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_database' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['serverbd']}</b>:<br /><i>{$main->lang['serverbd_d']}</i></td><td class='form_input2'>".in_text('host', 'input_text2', $database['host'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['userbd']}</b>:<br /><i>{$main->lang['userbd_d']}</i></td><td class='form_input2'>".in_text('user', 'input_text2', $database['user'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['passwordbd']}</b>:<br /><i>{$main->lang['passwordbd_d']}</i></td><td class='form_input2'>".in_pass('password', 'input_text2', $database['password'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['namebd']}</b>:<br /><i>{$main->lang['namebd_d']}</i></td><td class='form_input2'>".in_text('name', 'input_text2', $database['name'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['prefixbd']}</b>:<br /><i>{$main->lang['prefixbd_d']}</i></td><td class='form_input2'>".in_text('prefix', 'input_text2', $database['prefix'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['typebd']}</b>:<br /><i>{$main->lang['typebd_d']}</i></td><td class='form_input2'>".in_sels('type', array('mysql' => 'mysql', 'mysqli' => 'mysqli', 'pdo' => 'pdo'), 'select chzn-search-hide', $database['type'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['charsetbd']}</b>:<br /><i>{$main->lang['charsetbd_d']}</i></td><td class='form_input2'>".in_text('charset', 'input_text2', $database['charset'])."</td></tr>\n".       
    //"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['sql_cache_clear']}</b>:<br /><i>{$main->lang['sql_cache_clear_d']}</i></td><td class='form_input2'>".in_text('sql_cache_clear', 'input_text2', $database['sql_cache_clear'])."</td></tr>\n".
    //"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['no_cache_tables']}</b>:<br /><i>{$main->lang['no_cache_tables_d']}</i></td><td class='form_input2'>".in_text('no_cache_tables', 'input_text2', $database['no_cache_tables'])."</td></tr>\n".
    //"<tr class='row_tr'><td class='form_text2'><b>{$main->lang['cachebd']}</b>:<br /><i>{$main->lang['cachebd_d']}</i></td><td class='form_input2'>".in_chck('cache', 'input_checkbox', $database['cache'])."</td></tr>\n".
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table></form>";
}

function conf_save_database(){
global $database, $adminfile, $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    save_config('configdb.php', '$database', $database);
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}
function switch_admin_database(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case "backup": backup_options(); break;
         case "operations": operations(); break;
         case "backuper_op": backuper_op(); break;
         case "sql": manager_sql(); break;
         case "sql_struct": manager_sql_struct(); break;
         case "sql_clear": manager_sql_clear(); break;
         case "sql_delete": manager_sql_delete(); break;
         case "sql_insert": manager_sql_insert(); break;
         case "sql_insert_manager_set": manager_sql_insert_set(); break;
         case "sql_query": manager_sql_query(); break;
         case "row_delete": manager_row_delete(); break;
         case "row_edit": manager_row_edit(); break;
         case "row_save_edit": manager_row_save_edit(); break;
         case "config": conf_database(); break;
         case "save_database": conf_save_database(); break;
         default: main_database(); break;
      }
   } elseif($break_load==false) main_database();
}
switch_admin_database();
?>