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
    array('add_support', 'add_support'),
    array("config", "config")
);

function main_admin(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".USERS." AS u LEFT JOIN ".GROUPS." AS g ON(u.user_group=g.id) WHERE u.user_level>0 ORDER BY u.user_level DESC");
    $tr = "row1"; $i = 1;
    echo "<table width='100%' class='table'><tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='15'>#</th><th>{$main->lang['login']}</th><th align='center' width='140'>{$main->lang['group']}</th><th width='120'>{$main->lang['reg_date']}</th><th width='140'>{$main->lang['level_type']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
    while(($row = $main->db->sql_fetchrow($result))){
        $op = "<table cellspacing='1' class='cl'><tr><td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$row['uid']}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$row['uid']}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td></tr></table>";         
        $group = (!empty($row['title'])) ? "<span style='color: #{$row['color']}'>{$row['title']}</span>" : "<span style='color: #BDBDBD;'><i>{$main->lang['noinfo']}</i></span>";
        echo "<tr class='{$tr}'>
            <td align='center'><input type='checkbox' name='sels[]' value='{$row['uid']}' /></td>
            <td align='center'>{$i}</td>
            <td><a href='{$adminfile}?module=users&amp;do=edit&amp;id={$row['uid']}' title='{$main->lang['edit']} {$row['user_name']}'>{$row['user_name']}</a></td>
            <td align='center'>{$group}</td>
            <td align='center'>".format_date($row['user_regdate'])."</td>
            <td class='col' align='center' id='flip_level_{$row['uid']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=flip_level&amp;id={$row['uid']}', 'flip_level_{$row['uid']}')\">".($row['user_level']==1?$main->lang['moderator']:$main->lang['administrator'])."</td>
            <td align='center'>{$op}</td>
        </tr>";
        $tr = ($tr=='row1') ? 'row2' : 'row1';
        $i++;
    }
    echo "</table>";
}

function flip_level(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    list($active) = $main->db->sql_fetchrow($main->db->sql_query("SELECT user_level FROM ".USERS." WHERE uid='{$_GET['id']}'"));
    if($active==1){
        $main->db->sql_query("UPDATE ".USERS." SET user_level='2' WHERE uid='{$_GET['id']}'");
        echo $main->lang['administrator'];
    } else {
        $main->db->sql_query("UPDATE ".USERS." SET user_level='1' WHERE uid='{$_GET['id']}'");
        echo $main->lang['moderator'];
    }
}

function delete_support(){
    if(hook_check(__FUNCTION__)) return hook();
    sql_update(array('user_level' => '0'), USERS, "uid={$_GET['id']}");
    if (is_ajax()) main_admin(); else redirect(MODULE);
}

function support_access($title, $arr, $name, $id, $style="", $sels=""){
    if(hook_check(__FUNCTION__)) return hook();
    $i = 1;
    $modules_content = "<table".(!empty($style)?$style:"")." width='100%' id='{$id}'><tr><th colspan='3'>{$title}</th></tr>";    
    foreach($arr as $key => $value){
        $value = preg_replace('/(.+?)\.php$/i', '\\1', $value);
        $col = "<td width='33%'>".in_chck("{$name}[]", 'checkbox', (is_array($sels)?(in_array($key, $sels)?true:false):true), "value='{$key}'", false)." {$value}</td>";        
        if($i==1) $modules_content .= "<tr>".$col;
        elseif($i>1 AND $i<3) $modules_content .= $col;        
        elseif($i==3) {$modules_content .= $col."</tr>"; $i=0;}
        $i++;
    }
    if($i>1) for($y = $i; $y<=3; $y++) $modules_content .= "<td>&nbsp;</td>";
    $modules_content .= ($i>1) ? "</tr></table>" : "</table>";
    return $modules_content;
}

function add_support($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $_modules = $__modules = array(); $modules = array(); 
    $mod = scan_dir("administrator/modules/", '/(.+?)\.php$/i');
    asort($mod);
    foreach($mod as $value) {
        $file_name = preg_replace('/(.+?)\.php$/i', '\\1', $value);
        if(in_array($file_name, array('statistic', 'management', 'logout', 'modules', 'serverinfo', 'systeminfo'))) continue;
        $value = isset($main->lang["ad_".$file_name]) ? $main->lang["ad_".$file_name] : $file_name;
        $_modules[$file_name] = $value;
    }
    if(($handle = opendir("modules/"))){
        while (false !== ($file = readdir($handle))) if(is_dir("modules/{$file}") AND file_exists("modules/{$file}/admin/")) $modules[] = $file;                    
        closedir($handle);            
    }
    sort($modules);
    foreach($modules as $value) $__modules[$value] = isset($main->lang[$value]) ? $main->lang[$value] : $value;    
    $modules_content = support_access($main->lang['administration_modules'], $_modules, 'modules_admin', 'admin_table', " style='display: none;'").support_access($main->lang['moderation_module'], $__modules, 'modules_user', 'moder_table');
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_support'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_name", "input_text2")."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['level_type']}:<span class='star'>*</span></td><td class='form_input'>".in_radio('level', '1', $main->lang['moderator'], 'mod_l', true, " onchange=\"$$('admin_table').style.display='none'\"")."<br />".in_radio('level', '2', $main->lang['administrator'], 'mod_2', false, " onchange=\"$$('admin_table').style.display=''\"")."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['access_modules']}:</td><td class='form_input'>{$modules_content}</td></tr>\n".    
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_support(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE UPPER(user_name)='".mb_strtoupper($_POST['user_name'])."'");
    $msg = ($main->db->sql_numrows($result)!=1) ? $main->lang['nosearchuser'] : "";
    if(empty($msg)){
        $info = $main->db->sql_fetchrow($result);
        if($_POST['level']==1 AND isset($_POST['modules_user']) AND !empty($_POST['modules_user'])) $modules = implode(',',$_POST['modules_user']);
        elseif($_POST['level']==2 AND isset($_POST['modules_user']) AND isset($_POST['modules_admin']) AND !empty($_POST['modules_user']) AND !empty($_POST['modules_admin'])) $modules = implode(',', array_merge($_POST['modules_admin'], $_POST['modules_user']));
        elseif($_POST['level']==2 AND isset($_POST['modules_admin']) AND !empty($_POST['modules_admin'])) $modules = implode(',',$_POST['modules_admin']);
        else $modules = '';
        sql_update(array(
            'user_level'        => ($main->user['uid']!=$info['uid']) ? $_POST['level'] : $main->user['user_level'],
            'user_adm_modules'  => $modules
        ), USERS, "uid='{$info['uid']}'");
        redirect(MODULE);
    } else add_support($msg);
}

function edit_support($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE uid='{$_GET['id']}' AND user_level>'0'");
    if($main->db->sql_numrows($result)==0) redirect(MODULE);
    $info = $main->db->sql_fetchrow($result);
    $check_modules = preg_match('/\,/i', $info['user_adm_modules']) ? explode(',', $info['user_adm_modules']) : array($info['user_adm_modules']);
    $_modules = $__modules = array(); $modules = array();
    $mod = scan_dir("administrator/modules/", '/(.+?)\.php$/i');
    asort($mod);
    foreach($mod as $value) {
        $file_name = preg_replace('/(.+?)\.php$/i', '\\1', $value);
        if(in_array($file_name, array('statistic', 'management', 'logout', 'modules', 'serverinfo', 'systeminfo'))) continue;
        $value = isset($main->lang["ad_".$file_name]) ? $main->lang["ad_".$file_name] : $file_name;
        $_modules[$file_name] = $value;
    }
    if(($handle = opendir("modules/"))){
        while (false !== ($file = readdir($handle))) if(is_dir("modules/{$file}") AND file_exists("modules/{$file}/admin/")) $modules[] = $file;                    
        closedir($handle);            
    }
    sort($modules);
    foreach($modules as $value) $__modules[$value] = isset($main->lang[$value]) ? $main->lang[$value] : $value;    
    $modules_content = support_access($main->lang['administration_modules'], $_modules, 'modules_admin', 'admin_table', $info['user_level']==1?" style='display: none;'":"", $check_modules).support_access($main->lang['moderation_module'], $__modules, 'modules_user', 'moder_table', '', $check_modules);
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_edit&amp;id={$_GET['id']}'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:</td><td class='form_input'><b>{$info['user_name']}</b></td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['level_type']}:<span class='star'>*</span></td><td class='form_input'>".in_radio('level', '1', $main->lang['moderator'], 'mod_l', $info['user_level']==1?true:false, " onchange=\"$$('admin_table').style.display='none'\"")."<br />".in_radio('level', '2', $main->lang['administrator'], 'mod_2', $info['user_level']==2?true:false, " onchange=\"$$('admin_table').style.display=''\"")."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['access_modules']}:</td><td class='form_input'>{$modules_content}</td></tr>\n".    
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_edit_support(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
	$result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE uid='{$_GET['id']}'");
	$info = $main->db->sql_fetchrow($result);
	if($_POST['level']==1 AND isset($_POST['modules_user']) AND !empty($_POST['modules_user'])) $modules = implode(',',$_POST['modules_user']);
	elseif($_POST['level']==2 AND isset($_POST['modules_user']) AND isset($_POST['modules_admin']) AND !empty($_POST['modules_user']) AND !empty($_POST['modules_admin'])) $modules = implode(',', array_merge($_POST['modules_admin'], $_POST['modules_user']));
	elseif($_POST['level']==2 AND isset($_POST['modules_admin']) AND !empty($_POST['modules_admin'])) $modules = implode(',',$_POST['modules_admin']);
	else $modules = '';
	sql_update(array(
		'user_level'       => ($main->user['uid']!=$_GET['id']) ? $_POST['level'] : $main->user['user_level'],
		'user_adm_modules' => $modules
	), USERS, "uid='{$info['uid']}'");
    redirect(MODULE);
}

function config_admin(){
global $main, $adminfile, $default_modules_admin;     
    if(hook_check(__FUNCTION__)) return hook();
    main::add2script("includes/javascript/jquery/jquery-ui.min.js");
    main::add_css2head("
            .drag{cursor: move;background-color: #F6F6F6; border: 1px solid #CCCCCC; color: #1C94C4;height: 20px; margin: 3px 0;}
            .list_container{height:300px;width:310px;overflow:auto;border:1px solid silver}
            .sort_src{min-height:30px;padding: 4px;margin-right:10px;margin-left:10px;}
            .select{background-color:#E0E0E0}
            .head_src{padding: 0px;}
         ");
    $en_array = $_modules = array();
    $mod = scan_dir("administrator/modules/", '/(.+?)\.php$/i');    
    foreach($mod as $value) {
        $file_name = preg_replace('/(.+?)\.php$/i', '\\1', $value);
        if(in_array($file_name, array('logout', 'serverinfo', 'systeminfo'))) continue;
        $value = isset($main->lang["ad_".$file_name]) ? $main->lang["ad_".$file_name] : $file_name;
        $_modules[$file_name] = $value;
    }    
    asort($_modules);
    $modules = $_modules;
    $_modules = array();
    if(($handle = opendir("modules/"))){
        while (false !== ($file = readdir($handle))) if (is_dir("modules/{$file}") AND file_exists("modules/{$file}/admin/")) $_modules[$file] = $file;                    
        closedir($handle);            
    }            
    foreach($_modules as $value) $_modules[$value] = isset($main->lang[$value]) ? $main->lang[$value] : $value;
    asort($_modules);
    $mod = $adm = array();
    foreach($default_modules_admin as $val) {
        if(isset($_modules[$val])) {
            $mod[$val] = isset($main->lang["ad_".$val])?$main->lang["ad_".$val]:(isset($main->lang[$val])?$main->lang[$val]:$val);
            unset($_modules[$val]);
        } elseif(isset($modules[$val])) {
            $adm[$val] = isset($main->lang["ad_".$val])?$main->lang["ad_".$val]:(isset($main->lang[$val])?$main->lang[$val]:$val);
            unset($modules[$val]);
        }
    }
    asort($mod); asort($adm);
    $admin_modules=$modules;
    $moder_modules=$_modules;
    //
    echo "<form action='{$adminfile}?module={$main->module}&amp;do=save_config' method='post'>";
    echo "<div class='list_container' style='float:left'>";
    $ul="<ul>";
    $ul.="<li class='head_src'><b>{$main->lang['administration_modules']}</b><ul class='sort_src adm'>";
    foreach ($admin_modules as $key => $value) {$ul.="<li class='drag' title='{$key}'>{$value}</li>";}
    $ul.="</ul></li>";
    $ul.="<li class='head_src'><b>{$main->lang['moderation_module']}</b><ul class='sort_src mod'>";
    foreach ($moder_modules as $key => $value) {$ul.="<li class='drag' title='{$key}'>{$value}</li>";}
    $ul.="</ul></li>";
    $ul.="</ul>";
    echo $ul;
    echo "</div>";
    //-------------------------------
    echo "<div class='list_container' style='float:right'>";
    $ul="<ul>";
    $ul.="<li class='head_src' id='admlist'><b>{$main->lang['administration_modules']}</b><ul class='sort_src adm'>";
    foreach ($adm as $key => $value) {$ul.="<li class='drag' title='{$key}'>{$value}</li>";}
    $ul.="</ul></li>";
    $ul.="<li class='head_src' id='modlist'><b>{$main->lang['moderation_module']}</b><ul id='tt' class='sort_src mod'>";
    foreach ($mod as $key => $value) {$ul.="<li class='drag' title='{$key}'>{$value}</li>";}
    $ul.="</ul></li>";
    $ul.="</ul>";
    echo $ul;
    echo "</div>";
    echo "<table width='100%' class='form' style='clear:left'>".
    "<tr><td class='form_submit' colspan='3' align='center'><br />".send_button(" onclick='return calc_send();' ")."</td></tr>\n".
    "</table>";
    echo "</form>";
    ?>
    <script type="text/javascript">
    //<![CDATA[
    function calc_send(){
       var f=$('form');
       $('#admlist').find('li').each(function(){$('<input/>').attr('type','hidden').attr('name','modsel[]').val(this.title).appendTo(f);});
       $('#modlist').find('li').each(function(){$('<input/>').attr('type','hidden').attr('name','modsel[]').val(this.title).appendTo(f);});
       return true;
    }
    var uil=[];var objs=[];
    function sortable_start(event, ui){
       uil=[]; objs=[];$('.sort_src').each(function(){uil.push($(this).children().length);objs.push(this);})
    }
    function sortable_change(event, ui){
       var a=[];$('.sort_src').each(function(){a.push($(this).children().length);})
       for(i=0;i<uil.length;i++){if(uil[i]<a[i]){if(!$(objs[i]).hasClass('select')){$('.sort_src').not(objs[i]).removeClass('select');$(objs[i]).addClass('select')}}}
    }
    function sortable_stop(event, ui){$('.sort_src').removeClass('select');}
    $('.adm').sortable({connectWith: "ul.adm",start:sortable_start,change:sortable_change,stop:sortable_stop,});
    $('.mod').sortable({connectWith: "ul.mod",start:sortable_start,change:sortable_change,stop:sortable_stop,});
    //]]>
    </script>
    <?php
}

function save_config_admin(){
global $main, $adminfile, $copyright_file; 
    if(hook_check(__FUNCTION__)) return hook();
    $arr_text = "global \$default_modules_admin;\n";    
    if(isset($_POST['modsel'])){
        $arr_text .= '$default_modules_admin = array('."\n";
        foreach($_POST['modsel'] as $val) $arr_text .= "\t'{$val}',\n";    
        $arr_text = mb_substr($arr_text, 0, mb_strlen($arr_text)-2);
        $arr_text .= "\n);";
    }  else $arr_text .= '$default_modules_admin = array();';
    $file_link = "includes/config/admin_panel.php";
    if(is_writable($file_link)){
        $file = fopen($file_link, "w");
        fputs ($file, $copyright_file.$arr_text."\n"."?".">");
        fclose ($file);
    }
    redirect("{$adminfile}?module={$main->module}&do=config");
}

if(isset($_GET['do']) AND $break_load==false){
    switch($_GET['do']){
        case "add_support": add_support(); break;
        case "save_support": save_support(); break;
        case "edit": edit_support(); break;
        case "save_edit": save_edit_support(); break;
        case "flip_level": flip_level(); break;
        case "delete": delete_support(); break;
        case "config": config_admin(); break;
        case "save_config": save_config_admin(); break;
        default: main_admin(); break;
    }
} elseif($break_load==false) main_admin();
?>
