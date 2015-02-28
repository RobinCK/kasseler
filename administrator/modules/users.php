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
    array('add_user', 'add_user'),
    array("search", "search_user"),
    array("config", "config")
);
global $userconf;
if(!isset($userconf['user_name_length'])) $userconf['user_name_length']="";
function main_users(){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 30;
    $sort = (isset($_GET['sort']) AND !empty($_GET['sort'])) ? $_GET['sort'] : "uid";
    $sorttype = (isset($_GET['sorttype']) AND !empty($_GET['sorttype'])) ? $_GET['sorttype'] : "ASC";
    if(isset($_GET['do']) AND $_GET['do']=='search'){
        $get_last = (isset($_GET['lastvisit_y'])) ? "{$_GET['lastvisit_y']}-{$_GET['lastvisit_m']}-{$_GET['lastvisit_d']}" : "0000-00-00";
        $get_reg = (isset($_GET['regdate_y'])) ? "{$_GET['regdate_y']}-{$_GET['regdate_m']}-{$_GET['regdate_d']}" : "0000-00-00";
        $where = (!empty($_GET['user'])) ? " AND UPPER(user_name) LIKE '".mb_strtoupper($_GET['user'])."%'" : ""; 
        $where .= (!empty($_GET['mail'])) ? " AND UPPER(user_email) LIKE '".mb_strtoupper($_GET['mail'])."%'" : ""; 
        $where .= ($get_reg!='0000-00-00') ? " AND user_regdate LIKE '{$get_reg}%'" : ""; 
        $where .= ($get_last!='0000-00-00') ? " AND user_last_visit LIKE '{$get_last}%'" : ""; 
        $where .= (!empty($_GET['ip'])) ? " AND user_last_ip LIKE '{$_GET['ip']}%'" : ""; 
        $where .= (!empty($_GET['country'])) ? " AND user_country='{$_GET['country']}'" : ""; 
        $where .= (isset($_GET['type']) AND $_GET['type']!='-1') ? " AND user_level='{$_GET['type']}'" : ""; 
        $where .= (isset($_GET['group']) AND $_GET['group']!='0') ? " AND (user_group='{$_GET['group']}' OR user_groups LIKE '%,{$_GET['group']},%')" : "";
        $page_url_arr = array(
            'group'         => isset($_GET['group'])?$_GET['group']:0,
            'type'          => isset($_GET['type'])?$_GET['type']:'-1',
            'country'       => isset($_GET['country'])?$_GET['country']:'',
            'ip'            => isset($_GET['ip'])?$_GET['ip']:'',
            'mail'          => isset($_GET['mail'])?$_GET['mail']:'',
            'user'          => isset($_GET['user'])?$_GET['user']:'',
            'lastvisit_y'   => isset($_GET['lastvisit_y'])?$_GET['lastvisit_y']:'0000',
            'lastvisit_m'   => isset($_GET['lastvisit_m'])?$_GET['lastvisit_m']:'00',
            'lastvisit_d'   => isset($_GET['lastvisit_d'])?$_GET['lastvisit_d']:'00',
            'regdate_y'     => isset($_GET['regdate_y'])?$_GET['regdate_y']:'0000',
            'regdate_m'     => isset($_GET['regdate_m'])?$_GET['regdate_m']:'00',
            'regdate_d'     => isset($_GET['regdate_d'])?$_GET['regdate_d']:'00',
        );
    } else $where = "";
    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE user_name<>'Guest'{$where} ORDER BY {$sort} {$sorttype} LIMIT {$offset}, 30");    
    $rows_c = $main->db->sql_numrows($result);
    if($rows_c>0){
        $row = "row1";
        $i = (1*$num>1) ? (30*($num-1))+1 : 1*$num;
        echo "<div align='right'>".sort_as(array(array("user_name", $main->lang['asname']), array("user_regdate", $main->lang['asregdate']), array("user_last_visit", $main->lang['aslastvisit']), array("user_activation", $main->lang['asstatus'])))."</div><br />\n";
        echo "<form id='send_ajax_form' action='{$adminfile}?module={$_GET['module']}&amp;do=change_op".parse_get(array('module', 'do', 'id'))."' method='post'>\n<table class='table' width='100%'><tr><th width='25' align='center'>".in_chck("checkbox_sel", "", "", "onclick=\"ckeck_uncheck_all();\"")."</th><th width='25'>#</th><th>{$main->lang['login']}</th><th width='150'>{$main->lang['mail']}</th><th width='120'>{$main->lang['reg_date']}</th><th width='120'>{$main->lang['last_visit']}</th><th width='70'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        while(($rows = $main->db->sql_fetchrow($result))){
            $op = edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['uid']}").
            "<a href='{$adminfile}?module={$main->module}&amp;do=custom_delete&amp;id={$rows['uid']}' class='admino ico_delete pixel' title='{$main->lang['delete']}'></a>";
            //delete_button("{$adminfile}?module={$main->module}&amp;do=custom_delete&amp;id={$rows['uid']}".parse_get(array('module', 'do', 'id')), 'ajax_content').
            $href=$main->url(array('module' => 'account', 'do' => 'user','id'=>$rows['uid']));
            echo "<tr class='{$row}".(($rows['user_activation']==1)?"_warn":"")."'><td align='center'><input type='checkbox' name='sels[]' value='{$rows['uid']}' /></td><td align='center'>{$i}</td><td><a href='{$href}'>{$rows['user_name']}</a></td><td align='center'>{$rows['user_email']}</td><td align='center'>".user_format_date($rows['user_regdate'])."</td><td align='center'>".user_format_date($rows['user_last_visit'])."</td><td class='col' align='center' id='onoff_{$rows['uid']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$rows['uid']}', 'onoff_{$rows['uid']}')\">".(($rows['user_activation']==0) ? $main->lang['status_on'] : $main->lang['status_off'])."</td><td align='center'>{$op}</td></tr>";
            $row = ($row=='row1') ? "row2" : "row1";
            $i++;
        }
        echo "</table><table width='100%'><tr><td>".get_function_checked()."</td></tr></table></form>";
        if ($rows_c==30 OR isset($_GET['page'])){
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".USERS." WHERE user_name<>'Guest'{$where}"));            
            //Если количество больше чем количество на страницу
            if($numrows>30){
                //Открываем стилевую таблицу
                open();
                //создаем страницы
                pages($numrows, 30, (isset($_GET['do']) AND $_GET['do']=='search')?array('module' => $main->module, 'do' => 'search'):array('module' => $main->module), true, false, isset($page_url_arr)?$page_url_arr:array(), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else info($main->lang['noinfo']);
}

function change_op_users(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['sels']) AND is_array($_POST['sels']) AND !empty($_POST['sels'])){
        if($_POST['op']=="status"){
            foreach($_POST['sels'] as $value){
                list($status) = $main->db->sql_fetchrow($main->db->sql_query("SELECT user_activation FROM ".USERS." WHERE uid='{$value}'"));
                sql_update(array('user_activation' => (($status!=1) ? 1 : 0)), USERS, "uid='{$value}'");
            }
        } else {
            foreach($_POST['sels'] as $value) dels_users($value);
        }
    }
    if(!is_ajax()) redirect(MODULE);
    else main_users();
}

function dels_users($uid=0){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $uid = $uid>0 ? $uid : (isset($_GET['id'])?intval($_GET['id']):0);
    $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT user_name FROM ".USERS." WHERE uid='{$uid}'"));
    $main->db->sql_query("DELETE FROM ".USERS." WHERE uid='{$uid}'");    
    $main->db->sql_query("DELETE FROM ".COMMENTS." WHERE name='{$result['user_name']}'");
    //admin.png guest.png default.png
    if (is_ajax()) main_users(); else redirect(MODULE);
}

function add_user($msg=""){
global $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($msg)) warning($msg);
    echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save_user'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_name", "input_text2")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['mail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_email", "input_text2")."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['password']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_password", "input_text2")."</td></tr>\n".    
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
}

function save_user(){
global $userconf, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('user_name', 'user_password'), array('error_user_name', 'error_new_password')).check_mail($_POST['user_email']);
    if(empty($msg)){
        sql_insert(array(
            'user_id'              => cyr2lat($_POST['user_name']),
            'user_name'            => $_POST['user_name'],
            'user_email'           => $_POST['user_email'],
            'user_password'        => pass_crypt($_POST['user_password']),
            'user_regdate'         => kr_datecms("Y-m-d H:i:s"),
            'user_last_visit'      => kr_datecms("Y-m-d H:i:s"),
            'user_group'           => $userconf['default_group'],
            'user_activation'      => 0,
            'user_activation_code' => get_random_string(25),
            'user_password_update' => time(),
            'user_gmt'             => isset($_POST['timezone'])?(is_numeric($_POST['timezone'])?intval($_POST['timezone']):""):"0"
        ), USERS);
        redirect(MODULE);
    } else add_user($msg);    
}

function config_users(){
global $userconf, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['directory_avatar']}</b>:<br /><i>{$main->lang['directory_avatar_d']}</i></td><td class='form_input2'>".in_text('directory_avatar', 'input_text2', $userconf['directory_avatar'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['guest_name']}</b>:<br /><i>{$main->lang['guest_name_d']}</i></td><td class='form_input2'>".in_text('guest_name', 'input_text2', $userconf['guest_name'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['size_avatar']}</b>:<br /><i>{$main->lang['size_avatar_d']}</i></td><td class='form_input2'>".in_text('size_avatar', 'input_text2', $userconf['size_avatar'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['width_avatar']}</b>:<br /><i>{$main->lang['width_avatar_d']}</i></td><td class='form_input2'>".in_text('width_avatar', 'input_text2', $userconf['width_avatar'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['height_avatar']}</b>:<br /><i>{$main->lang['height_avatar_d']}</i></td><td class='form_input2'>".in_text('height_avatar', 'input_text2', $userconf['height_avatar'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['type_avatar']}</b>:<br /><i>{$main->lang['type_avatar_d']}</i></td><td class='form_input2'>".in_text('type_avatar', 'input_text2', $userconf['type_avatar'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['password_length']}</b>:<br /><i>{$main->lang['password_length_d']}</i></td><td class='form_input2'>".in_text('password_length', 'input_text2', $userconf['password_length'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['user_name_length']}</b>:<br /><i>{$main->lang['user_name_length_d']}</i></td><td class='form_input2'>".in_text('user_name_length', 'input_text2', $userconf['user_name_length'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['default_group']}</b>:<br /><i>{$main->lang['default_group_d']}</i></td><td class='form_input2'>".get_groups($userconf['default_group'], 'default_group', true)."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['enabled_load_avatar']}</b>:<br /><i>{$main->lang['enabled_load_avatar_d']}</i></td><td class='form_input2'>".in_chck('load_avatar', 'checkbox', $userconf['load_avatar'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['ratings']}</b>:<br /><i>{$main->lang['ratings_du']}</i></td><td class='form_input2'>".in_chck('ratings', 'checkbox', $userconf['ratings'])."</td></tr>\n".    
    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['conf_comments']}</b>:<br /><i>{$main->lang['conf_comments_d']}</i></td><td class='form_input2'>".in_chck('comments', 'input_checkbox', $userconf['comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['guests_comments']}</b>:<br /><i>{$main->lang['guests_comments_d']}</i></td><td class='form_input2'>".in_chck('guests_comments', 'input_checkbox', $userconf['guests_comments'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['comments_sort']}</b>:<br /><i>{$main->lang['comments_sort_d']}</i></td><td class='form_input2'>".in_sels('comments_sort', array('ASC'=>'ASC', 'DESC'=>'DESC'), 'select chzn-search-hide', $userconf['comments_sort'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['registration_c']}</b>:<br /><i>{$main->lang['registration_c_d']}</i></td><td class='form_input2'>".in_sels('registration', array('all' => $main->lang['allregister'], 'email' => $main->lang['emailregister'], 'admin' => $main->lang['adminregister'], '' => $main->lang['noregister']), 'select2 chzn-search-hide', $userconf['registration'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['email_denied']}</b>:<br /><i>{$main->lang['email_denied_d']}</i></td><td class='form_input2'>".in_area('email_deny', 'input_text2',5, isset($userconf['email_deny'])?$userconf['email_deny']:"")."</td></tr>\n".    
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table></form>";
}

function trim_value(&$value){
    if(hook_check(__FUNCTION__)) return hook();
    $value = trim($value);
}

function save_conf_users(){
global $userconf, $adminfile, $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('sources');
    if(isset($_POST['email_deny'])){
       $em=str_replace(';',',',$_POST['email_deny']);
       $a=explode(',',$em);
       array_walk($a, 'trim_value');
       $_POST['email_deny']=implode(',',$a);
       $userconf['email_deny']='';
    } else $userconf['email_deny']='';
    save_config('config_user.php', '$userconf', $userconf);
    redirect("{$adminfile}?module={$_GET['module']}&do=config");
}

function user_birthday($birthday){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $date = explode("-", $birthday);    
    $days = $month = $years = array();
    for($i=0;$i<=31;$i++) $days[($i<10)?"0".$i:$i] = ($i<10)?"0".$i:$i;
    $return = "{$main->lang['day']}: ".in_sels('days', $days, '', $date[2]);
    for($i=0;$i<=12;$i++) $month[$i] = ($i<10)?"0".$i:$i;
    $return .= " {$main->lang['month']}: ".in_sels('months', $month, '', $date[1]);
    for($i=date("Y")-100;$i<=date("Y");$i++) $years[$i] = $i;
    $return .= " {$main->lang['year']}: ".in_sels('years', array('00' => '0000')+$years, '', $date[0]);            
    return $return;
}

function edit_user($msg=""){
global $main, $adminfile, $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function(array('select_avatars','modify_filter_ip'));

    $user = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".USERS." WHERE uid='{$_GET['id']}'"));
    if(!empty($msg)) warning($msg);
    
    $groups = array();
    $result = $main->db->sql_query("SELECT id, title FROM ".GROUPS." ORDER BY title");
    while(($row = $main->db->sql_fetchrow($result))) $groups[$row['id']] = $row['title'];    
    //if(isset($groups[$user['user_group']])) unset($groups[$user['user_group']]);
    $gender = in_radio('user_gender', 0, $main->lang['noinfo'], 'id0', ($user['user_gender']==0)?true:false)." ".in_radio('user_gender', 1, $main->lang['male'], 'id1', ($user['user_gender']==1)?true:false)." ".in_radio('user_gender', 2, $main->lang['woman'], 'id2', ($user['user_gender']==2)?true:false);
    
    echo "<form enctype='multipart/form-data' action='{$adminfile}?module={$main->module}&amp;do=save_edit_user&amp;id={$_GET['id']}' method='post'>".
    "<table class='form' width='100%'>".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['ip']}:</td><td class='form_input'><a href='{$main->config['whois']}{$user['user_last_ip']}'>{$user['user_last_ip']}</a></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_name", "input_text2", $user['user_name'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['key_link']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_id", "input_text2", $user['user_id'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['birthday']}:</td><td class='form_input' id='datacase'>".user_birthday($user['user_birthday'])."&nbsp;</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['gender']}:</td><td class='form_input'>{$gender}</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_mail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_email", "input_text2", $user['user_email'])."</td></tr>\n".
    
    "<tr class='row_tr'><td colspan='2' style='padding: 0;'><a href='#' onclick=\"\$('#form_{$main->module}_contacts').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['user_contact']}</a></td></tr>".
    "</table><div id='form_{$main->module}_contacts' class='post_options'><table class='form' width='100%'>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['icq']}:</td><td class='form_input'>".in_text("user_icq", "input_text2", $user['user_icq'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['aim']}:</td><td class='form_input'>".in_text("user_aim", "input_text2", $user['user_aim'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['yim']}:</td><td class='form_input'>".in_text("user_yim", "input_text2", $user['user_yim'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['msn']}:</td><td class='form_input'>".in_text("user_msnm", "input_text2", $user['user_msnm'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['home_page']}:</td><td class='form_input'>".in_text("user_website", "input_text2", $user['user_website'])."</td></tr>\n".
    "</table></div>".
    
    "<a href='#' onclick=\"\$('#form_{$main->module}_udetail').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['user_detail']}</a>".
    "<div id='form_{$main->module}_udetail' class='post_options'><table class='form' width='100%'>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['first_name']}:</td><td class='form_input'>".in_text("user_first_name", "input_text2", $user['user_first_name'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['last_name']}:</td><td class='form_input'>".in_text("user_last_name", "input_text2", $user['user_last_name'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['occupation']}:</td><td class='form_input'>".in_text("user_occupation", "input_text2", $user['user_occupation'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['interests']}:</td><td class='form_input'>".in_text("user_interests", "input_text2", $user['user_interests'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['locality']}:</td><td class='form_input'>".in_text("user_locality", "input_text2", $user['user_locality'])."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['signature']}:</td><td class='form_input'>".in_area("user_signature", 'textarea', 3, bb($user['user_signature'], DECODE))."</td></tr>\n".        
    "</table></div>".
    
    "<table class='form' width='100%'>".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['group']}:</td><td class='form_input'>".get_groups($user['user_group'], 'user_group', true)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['case_groups']}:</td><td class='form_input'>".in_sels('user_groups', $groups, 'select2', explode(',', $user['user_groups']), "", true)."</td></tr>\n".
    
    "<tr class='row_tr'><td colspan='2' style='padding: 0;'><a href='#' onclick=\"\$('#form_{$main->module}_personal').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['personalcontrols']}</a></td></tr>".
    "</table><div id='form_{$main->module}_personal' class='post_options'><table class='form' width='100%'>".
//    "<tr class='row_tr'><th colspan='2' class='form_th'>{$main->lang['personalcontrols']}</th></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['case_language_site']}:</td><td class='form_input'>".get_lang_file($user['user_language'], false)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['case_template_site']}:</td><td class='form_input'>".select_template($user['user_template'])."</td></tr>\n".    
    "<tr class='row_tr'><td class='form_text'>{$main->lang['adm_viewemail']}:</td><td class='form_input'>".in_chck('user_viewemail', 'checkbox', ($user['user_viewemail']==1)?ENABLED:'')."</td></tr>\n".    
    "<tr><th colspan='2' class='form_th'>{$main->lang['controlsavatars']}:</th></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['adm_you_avatar']}:</td><td class='form_input' align='center'>".in_hide('this_avatar', $user['user_avatar'])."<input type='hidden' id='id_set_avatar' name='set_avatar' value='' /><img id='avatar' class='img_avatar' src='{$userconf['directory_avatar']}{$user['user_avatar']}' alt='{$main->lang['you_avatar']}' title='{$main->lang['you_avatar']}' /></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['case_avatar']}:</td><td class='form_input' nowrap='nowrap'>".select_avatars()." <input class='case_submit' type='submit' onclick=\"newWindow = window_open('index.php?module=account&amp;do=case_avatar&amp;id='+document.getElementById('cat').value+'', '', 'toolbar=0,width=720,height=600,resizable=0,menubar=0,scrollbars=1,status=0'); return false;\" value='{$main->lang['case']}' /></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['adm_load_avatar']}:</td><td class='form_input'><input type='file' name='userfile' value='' size='43' style='width: 297px;' /></td></tr>\n".
    "</table></div>".
    
    "<a href='#' onclick=\"\$('#form_{$main->module}_security').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['security']}</a>".
    "<div id='form_{$main->module}_security' class='post_options'><table class='form' width='100%'>".
    "<tr><th colspan='2' class='form_th'>{$main->lang['new_user_password']}:</th></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['password']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_password", "input_text2")."</td></tr>\n".
    "<tr><th colspan='2' class='form_th'>{$main->lang['user_filter_ip']}:</th></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['user_filter_active']}:</td><td class='form_input'>".in_chck('user_filter_active', 'checkbox', ($user['user_filter_active']==1)?ENABLED:'')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['user_filter_country']}:</td><td class='form_input'>".in_chck('user_filter_country', 'checkbox', ($user['user_filter_country']==1)?ENABLED:'')."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['user_filter_session']}:</td><td class='form_input'>".in_chck('user_filter_session', 'checkbox', ($user['user_filter_session']==1)?ENABLED:'')."</td></tr>\n".
    "<tr><td class='form_text'>{$main->lang['user_filter_list']}:</td><td class='form_input'>".
    gen_html_editor_filter_ip($user['user_filter_ip']).
    in_hide("filter_ip",'')."</td></tr>\n".
    "</table></div>".
    "<table class='form' width='100%'>".
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table>".
    "</form>";
    gen_jscript_editor_filter_ip();
    ?>
    <script type="text/javascript">
    //<![CDATA[
     $('#user_groups').css('width','99%');
    //]]>
    </script>
    <?php
    
}

function save_edit_user(){
global $main, $userconf;    
    if(hook_check(__FUNCTION__)) return hook();
    $msg = error_empty(array('user_name'), array('error_user_name')).check_mail($_POST['user_email']);
    if(isset($_FILES['userfile']) AND !empty($_FILES['userfile']['tmp_name'])){
        main::init_class('uploader');
        $atrib = array(
            'dir'       => $userconf['directory_avatar'],
            'file'      => $_FILES['userfile'],
            'size'      => $userconf['size_avatar'],
            'type'      => explode(',', $userconf['type_avatar']),
            'width'     => $userconf['width_avatar'],
            'height'    => $userconf['height_avatar'],
            'name'      => $_POST['user_id'],
            'overwrite' => true
        );
        $avatar = new upload($atrib);
        if($avatar->error) $msg.=$avatar->get_error_msg();
        elseif($avatar->is_upload) $user_avatar = $avatar->file;
    }
    if(!isset($user_avatar) OR empty($user_avatar)) $user_avatar = (!empty($_POST['set_avatar'])) ? $_POST['set_avatar'] : $_POST['this_avatar'];            
    if(empty($msg)){
        $row=$main->db->sql_fetchrow($main->db->sql_query("select * from ".USERS." where uid='{$_GET['id']}'"));
        if(!empty($row) AND $row['user_name']!=$_POST['user_name'])  rename_user($row['user_name'],$_POST['user_name']);
        $user_birthday = isset($_POST['years']) ? "{$_POST['years']}-{$_POST['months']}-{$_POST['days']}" : "";
        $ipf=$_POST['ip_filter'];
        sql_update(array(
            'user_name'            => $_POST['user_name'],
            'user_id'              => $_POST['user_id'],
            'user_birthday'        => $user_birthday,
            'user_gender'          => $_POST['user_gender'],
            'user_email'           => $_POST['user_email'], 
            'user_icq'             => $_POST['user_icq'],
            'user_aim'             => $_POST['user_aim'],
            'user_yim'             => $_POST['user_yim'],
            'user_msnm'            => $_POST['user_msnm'],
            'user_website'         => $_POST['user_website'],
            'user_occupation'      => $_POST['user_occupation'],
            'user_interests'       => $_POST['user_interests'],
            'user_locality'        => $_POST['user_locality'],
            'user_signature'       => bb($_POST['user_signature']),
            'user_viewemail'       => (isset($_POST['user_viewemail']) AND $_POST['user_viewemail']=='on') ? 1 : 0,
            'user_group'           => $_POST['user_group'],
            'user_groups'          => isset($_POST['user_groups'])?(is_array($_POST['user_groups'])?("0,".implode(',', $_POST['user_groups']).","):("0,".$_POST['user_groups']).","):"",
            'user_avatar'          => $user_avatar,
            'user_language'        => $_POST['language'],
            'user_template'        => $_POST['template'],
            'user_last_name'       => $_POST['user_last_name'],
            'user_first_name'      => $_POST['user_first_name'],
            'user_filter_ip'       => implode(',',$ipf),
            'user_filter_active'   => (isset($_POST['user_filter_active']) AND $_POST['user_filter_active']=='on') ? 1 : 0,
            'user_filter_country'  => (isset($_POST['user_filter_country']) AND $_POST['user_filter_country']=='on') ? 1 : 0,
            'user_filter_session'  => (isset($_POST['user_filter_session']) AND $_POST['user_filter_session']=='on') ? 1 : 0,
            
        ), USERS, "uid='{$_GET['id']}'");
        if(!empty($_POST['user_password'])) sql_update(array('user_password' => pass_crypt($_POST['user_password'])), USERS, "uid='{$_GET['id']}'");
        main::init_function('session_tools');
        user_sessions_modify($_POST['user_name'],'set_this_session_update');
        //user_sessions_kill_all($_POST['user_name']);
        redirect(MODULE);
    } else add_user($msg);
}

function search_user(){
global $main, $adminfile, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_GET['user']) OR isset($_GET['mail']) OR isset($_GET['ip'])) {
        main_users();
        echo "<br /><br /><hr />";
    }
    $result = $main->db->sql_query("SELECT user_country FROM ".USERS." GROUP BY user_country");
    $countrys = "";
    while(($row = $main->db->sql_fetchrow($result))){
        $countrys .= "<option value='{$row['user_country']}'".((isset($_GET['country']) AND $_GET['country']==$row['user_country'])?" selected='selected'":"")." style='padding: 1px; padding-left: 20px; background-image: url(includes/images/country/".str_replace(" ", "_", mb_strtolower($row['user_country'])).".png); background-position: left bottom; background-repeat: no-repeat;'>{$row['user_country']}</option>";
    }     
    main::add2script("includes/javascript/kr_calendar.js");
    main::add2link("includes/css/kr_calendar.css");
    $result = $main->db->sql_query("SELECT * FROM ".GROUPS."");
    while(($row = $main->db->sql_fetchrow($result))) $groups[$row['id']] = $row['title'];
    $groups = array_merge(array('0' => $main->lang['all']), $groups);
    $type = array('-1' => $main->lang['all_users'], '0' => $main->lang['only_users'], '1' => $main->lang['only_moder'], '2' => $main->lang['only_admin']);
    $result = $main->db->sql_query("SELECT user_country FROM ".USERS." GROUP BY user_country");
    echo "<form action='{$adminfile}?module={$main->module}' method='get'>".
    in_hide("module", $main->module).in_hide('do', 'search')."<table width='100%' class='form'>".
    "<tr class='row_tr'><td width='50%' class='form_input'>{$main->lang['login']}:<br />".in_text('user', 'input_text', !empty($_GET['user'])?$_GET['user']:"")."</td><td>{$main->lang['ip']}: <br />".in_text('ip', 'input_text', !empty($_GET['ip'])?$_GET['ip']:"")."</td></tr>".
    "<tr class='row_tr'><td class='form_input'>{$main->lang['mail']}:<br />".in_text('mail', 'input_text', !empty($_GET['mail'])?$_GET['mail']:"")."</td><td>{$main->lang['group']}: <br />".in_sels('group', $groups, 'select', !empty($_GET['group'])?$_GET['group']:"")."</td></tr>".
    "<tr class='row_tr'><td class='form_input'>{$main->lang['reg_date']}:<br />".in_text('regdate_d', '', !empty($_GET['regdate_d'])?$_GET['regdate_d']:'00', true, "size='2'")." ".in_text('regdate_m', '', !empty($_GET['regdate_m'])?$_GET['regdate_m']:'00', true, "size='2'")." ".in_text('regdate_y', '', !empty($_GET['regdate_y'])?$_GET['regdate_y']:'0000', true, "size='4'")."&nbsp;<img style='cursor: pointer;' src='includes/images/calendar.jpg' title='{$main->lang['calendar']}' alt='{$main->lang['calendar']}' id='button_calendar2' /></td><td>{$main->lang['country']}: <br /><select name='country' class='select'><option value=''>{$main->lang['all']}</option>{$countrys}</select></td></tr>".
    "<tr class='row_tr'><td class='form_input'>{$main->lang['last_visit']}:<br />".in_text('lastvisit_d', '', !empty($_GET['lastvisit_d'])?$_GET['lastvisit_d']:'00', true, "size='2'")." ".in_text('lastvisit_m', '', !empty($_GET['lastvisit_m'])?$_GET['lastvisit_m']:'00', true, "size='2'")." ".in_text('lastvisit_y', '', !empty($_GET['lastvisit_y'])?$_GET['lastvisit_y']:'0000', true, "size='4'")."&nbsp;<img style='cursor: pointer;' src='includes/images/calendar.jpg' title='{$main->lang['calendar']}' alt='{$main->lang['calendar']}' id='button_calendar1' /></td><td>Types: <br />".in_sels('type', $type, 'select',!empty($_GET['type'])?$_GET['type']:"-1")."</td></tr>".
    "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
    "</table>".
    "</form>";
    main::add2script("addEvent(window, 'load', function(){KR_AJAX.calendar.init('calendar1', {day:'lastvisit_d', month:'lastvisit_m', year:'lastvisit_y'});});", false);
    main::add2script("addEvent(window, 'load', function(){KR_AJAX.calendar.init('calendar2', {day:'regdate_d', month:'regdate_m', year:'regdate_y'});});", false);
    ?>
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function(){
       var sel = $('select[name="country"]');
       var nm = sel.attr('id');
       var sel_chzn = sel.next().find('ul');
       sel.find('option').each(function(i){
          if(this.value!==''){sel_chzn.find('#'+nm+'_chzn_o_'+i).attr('style',$(this).attr('style'));}
       });
    });
    //]]>
    </script>
    <?php
    
}

function on_off_users(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    list($active) = $main->db->sql_fetchrow($main->db->sql_query("SELECT user_activation FROM ".USERS." WHERE uid='{$_GET['id']}'"));
    if($active==1){
        $main->db->sql_query("UPDATE ".USERS." SET user_activation='0' WHERE uid='{$_GET['id']}'");
        echo $main->lang['status_on'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
    } else {
        $main->db->sql_query("UPDATE ".USERS." SET user_activation='1' WHERE uid='{$_GET['id']}'");
        echo $main->lang['status_off'];
        echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
    }
}
function row_csdelete($name_lang,$sels,$title=''){
 global $main;
   return "<tr class='row_tr'><td class='form_input' style='width:50%;text-align: right;'>".(!empty($title)?$title:$main->lang[$name_lang]).":</td><td>".in_sels($name_lang,$sels, 'select select2 chzn-search-hide',0,"",false)."</td></tr>";
}
function users_custom_delete(){
   global $main,$adminfile,$database;
   if(hook_check(__FUNCTION__)) return hook();
   if(!empty($_GET['id'])){
      $uid=intval($_GET['id']);
      $type_empty=array($main->lang['delete'],$main->lang['replace_guest']);
      echo "<form action='{$adminfile}?module={$main->module}&amp;do=exec_delete&amp;id={$uid}' method='POST'>".
      "<table width='100%' class='form'>".
      row_csdelete('comments',$type_empty).
      row_csdelete('news',$type_empty).
      row_csdelete('pages',$type_empty).
      row_csdelete('jokes',$type_empty).
      row_csdelete('files',$type_empty).
      row_csdelete('media',$type_empty).
      row_csdelete('audio',$type_empty).
      row_csdelete('forum',$type_empty);
      echo "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
      "</table>".
      "</form>";
   } else redirect("{$adminfile}?module={$main->module}");
}
function empty_table_and_attach_by_username($post_check,$table_modify,$user_name,$directory){
   global $main,$userconf;
   if(isset($_POST[$post_check])){
      if(intval($_POST[$post_check])==0) {
         $dbr=$main->db->sql_query("select * from ".$table_modify." where upper(`author`)=upper('{$user_name}')");
         while (($row=$main->db->sql_fetchrow($dbr))){ // чистим attach
            $dir=$directory.$row['id']."/";
            if(file_exists($dir)){
               remove_dir($dir);
               $main->db->sql_query("delete from ".ATTACH." where upper(path)=upper('{$dir}')");
            }
         }
         $main->db->sql_query("delete from ".$table_modify." where upper(`author`)=upper('{$user_name}')");
      }
      else $main->db->sql_query("update ".$table_modify." set `author`='{$userconf['guest_name']}' where upper(`author`)=upper('{$user_name}')");
   }
}
function users_exec_delete(){
   global $main,$files,$pages,$news,$media,$faq,$audio,$forum,$userconf;
   if(hook_check(__FUNCTION__)) return hook();
   $uid=intval($_GET['id']);
   $modul_table=array("audio"=>AUDIO,"pages"=>PAGES,"files"=>FILES,"jokes"=>JOKES,"media"=>MEDIA,"audio"=>AUDIO,"faq"=>FAQ,"news"=>NEWS);
   // проверить заполненость $files,$pages,$news,$media
   if($uid>0&&!empty($files)&&!empty($pages)&&!empty($news)&&!empty($media)&&!empty($faq)&&!empty($audio)&&!empty($forum)){
      $pri_key=array();
      list($user_id,$user_name,$user_avatar)=$main->db->sql_fetchrow($main->db->sql_query("select user_id,user_name,user_avatar from ".USERS." where uid={$uid}"));
      if(!empty($user_id)){
         $main->db->sql_query("delete from ".SEARCH." where upper(`author`)=upper('{$user_name}')");
         $t_s=SEARCH;$t_sk=SEARCH_KEY;
         $main->db->sql_query("delete from {$t_sk} where not exists(select id from {$t_s}  where `{$t_s}`.`key`=`{$t_sk}`.`key`)");
         if(isset($_POST['comments'])){
            if(intval($_POST['comments'])==0){
               $r=$main->db->sql_query("select c.name,c.modul,c.parentid,count(c.cid) as ccu from ".COMMENTS." c
                  where upper(c.`name`)=upper('{$user_name}') group by c.name,c.modul,c.parentid");
               while (($row=$main->db->sql_fetchrow($r))){
                  $table=isset($modul_table[$row['modul']])?$modul_table[$row['modul']]:"";
                  if(!empty($table)){
                     if(empty($pri_key[$table])){
                        $rd = $main->db->sql_query("SHOW COLUMNS FROM {$table}");
                        while (($rown=$main->db->sql_fetchrow($rd))){if($rown['Key']=='PRI') {$pri_key[$table]=$rown['Field'];break;}}
                     }
                     if(!empty($pri_key[$table])) $main->db->sql_query("update {$table} set `comment`=`comment`-{$row['ccu']} where {$pri_key[$table]}={$row['parentid']}");
                  }
               }   
               $main->db->sql_query("delete from ".COMMENTS." where upper(`name`)=upper('{$user_name}')");
            } else {
               $main->db->sql_query("update ".COMMENTS." set `name`='{$main->lang['guest']}' where upper(`name`)=upper('{$user_name}')");
            }
         }
         empty_table_and_attach_by_username('news',NEWS,$user_name,$news['directory']);//NEWS
         empty_table_and_attach_by_username('pages',PAGES,$user_name,$pages['directory']);//PAGES
         if(isset($_POST['jokes'])){ //JOKES
            if(intval($_POST['jokes'])==0) $main->db->sql_query("delete from ".JOKES." where upper(`author`)=upper('{$user_name}')");
            else $main->db->sql_query("update ".JOKES." set `author`='{$main->lang['guest']}' where upper(`author`)=upper('{$user_name}')");
         }
         empty_table_and_attach_by_username('files',FILES,$user_name,$files['directory']);//FILES
         empty_table_and_attach_by_username('media',MEDIA,$user_name,$media['directory']);//MEDIA
         empty_table_and_attach_by_username('audio',AUDIO,$user_name,$audio['directory']);//AUDIO
         if(isset($_POST['forum'])){ //FORUM
            $main->db->sql_query("delete from ".FORUM_ACC." where thisuser='u' and ugid={$uid}");
            if(intval($_POST['forum'])==0){
               $dbr=$main->db->sql_query("select * from ".POSTS." where poster_id={$uid}");
               while (($row=$main->db->sql_fetchrow($dbr))){ // чистим attach
                  $dir=$forum['directory'].$row['post_id']."/";
                  if(file_exists($dir)){
                     remove_dir($dir);
                     $main->db->sql_query("delete from ".ATTACH." where upper(path)=upper('{$dir}')");
                  }
               }
               $main->db->sql_query("delete from ".POSTS." where poster_id={$uid}");
               $main->db->sql_query("delete from ".TOPICS." where topic_poster={$uid}");
               main::init_function('forumtools');
               fix_topic_info();
               fix_forum_info();
            } else {
               $main->db->sql_query("update ".TOPICS." set topic_poster=-1,`topic_poster_name`='{$userconf['guest_name']}' where topic_poster={$uid}");
               $main->db->sql_query("update ".POSTS." set poster_id=-1,poster_name='{$userconf['guest_name']}' where poster_id={$uid}");
            }
            // меняем цытирование этого пользователя
            $dbr=$main->db->sql_query("select * from ".POSTS." where upper(post_text) REGEXP upper('\\\[cite={$user_name},')");
            while (($row=$main->db->sql_fetchrow($dbr))){
               $text= preg_replace('/(\[cite='.$user_name.',)/si', '[cite='.$userconf['guest_name'].',', $row['post_text']);
               sql_update(array('post_text'=>$text),POSTS," post_id=".$row['post_id']);
            }
         }
         if (!empty($user_avatar) AND !preg_match('%(animation/|static/|default.png|user.png|admin.png)%si', $user_avatar, $regs)) {
            //удаляем нестандартную аватару
            if(file_exists($userconf['directory_avatar'].$user_avatar)) unlink($userconf['directory_avatar'].$user_avatar);
         }
         $main->db->sql_query("delete from ".USERS." where uid={$uid}");
         main::init_function('session_tools');
         user_sessions_kill_all($user_id);
         info($main->lang['user_deleting']);
      } else warning($main->lang['no_login_search']);
   }
}
function rename_user($prev_user_name,$new_user_name){
   global $main,$files,$pages,$news,$media,$faq,$audio,$forum,$userconf;
   if(hook_check(__FUNCTION__)) return hook();
   $modul_table=array("audio"=>AUDIO,"pages"=>PAGES,"files"=>FILES,"jokes"=>JOKES,"media"=>MEDIA,"audio"=>AUDIO,"faq"=>FAQ,"news"=>NEWS);
   if(!empty($files)&&!empty($pages)&&!empty($news)&&!empty($media)&&!empty($faq)&&!empty($audio)&&!empty($forum)){
      $pri_key=array();
      list($uid,$user_id,$user_name,$user_avatar)=$main->db->sql_fetchrow($main->db->sql_query("select uid,user_id,user_name,user_avatar from ".USERS." where upper(user_name)=upper('{$prev_user_name}')"));
      if(!empty($user_id)){
         $main->db->sql_query("update ".SEARCH." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".COMMENTS." set `name`='{$new_user_name}' where upper(`name`)=upper('{$user_name}')");
         $main->db->sql_query("update ".NEWS." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".PAGES." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".JOKES." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".FILES." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".MEDIA." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".AUDIO." set `author`='{$new_user_name}' where upper(`author`)=upper('{$user_name}')");
         $main->db->sql_query("update ".TOPICS." set `topic_poster_name`='{$new_user_name}' where topic_poster={$uid}");
         $main->db->sql_query("update ".POSTS." set `poster_name`='{$new_user_name}' where poster_id={$uid}");
         $dbr=$main->db->sql_query("select * from ".POSTS." where upper(post_text) REGEXP upper('\\\[cite={$user_name},')");
         while (($row=$main->db->sql_fetchrow($dbr))){
            $text= preg_replace('/(\[cite='.$user_name.',)/si', '[cite='.$new_user_name.',', $row['post_text']);
            sql_update(array('post_text'=>$text),POSTS," post_id=".$row['post_id']);
         }
      }
   }
}
function switch_admin_users(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){      
         case "on_off" : on_off_users(); break; 
         case "delete": dels_users(); break;
         case "save_user": save_user(); break;
         case "add_user": add_user(); break;
         case "config": config_users(); break;
         case "save_conf": save_conf_users(); break;
         case "edit": edit_user(); break;
         case "save_edit_user": save_edit_user(); break;
         case "search": search_user(); break;
         case "change_op": change_op_users(); break;
         case "custom_delete":users_custom_delete();break;
         case "exec_delete":users_exec_delete();break;
         default: main_users(); break;
      }
   } elseif($break_load==false) main_users();
}
switch_admin_users();
?>