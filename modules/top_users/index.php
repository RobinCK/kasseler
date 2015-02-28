<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if(!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main, $news, $tpl_create;
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'groups')), $main->lang['groups'], "groups");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'points')), $main->lang['points_rule'], "points");
$navi = navi($links);

function main_top_users($wheres=''){
global $main, $navi, $img, $userconf, $tpl_create, $template;
    if(hook_check(__FUNCTION__)) return hook();
    echo $navi;
    if(empty($wheres)) $main->parse_rewrite(array('module', 'page'));
    elseif($wheres=="country" OR $wheres=="group") $main->parse_rewrite(array('module', 'do', 'id', 'page'));
    $where = ($wheres=='country') ? " AND u.user_country='{$_GET['id']}'" : "";
    $where = ($wheres=='group') ? " AND (u.user_group='{$_GET['id']}' OR u.user_groups LIKE '%,{$_GET['id']},%')" : $where;
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 50;
    if($offset<0) kr_http_ereor_logs(404);
    $result = $main->db->sql_query("SELECT u.uid, u.user_id, u.user_name, u.user_avatar, u.user_website, u.user_birthday, u.user_country, u.user_gender, u.user_regdate, u.user_last_ip, u.user_last_os, u.user_last_browser, u.user_group, u.user_points, u.rating, u.voted, g.id, g.title, g.color,r.r_up,r.r_down,r.users 
        FROM ".USERS." AS u LEFT JOIN ".GROUPS." AS g ON (g.id=u.user_group) LEFT JOIN ".RATINGS." AS r ON (r.module='users' and r.idm=u.uid)
        WHERE u.user_name<>'Guest'{$where} 
        ORDER BY u.user_points DESC, BINARY(UPPER(u.user_name))
        LIMIT {$offset}, 50");
    $_temp = array();
    if($main->db->sql_numrows($result)>0){
        open();
        $template->get_tpl('publisher-top_users', 'publisher-top_users');
        $template->get_subtpl(array(
           array('get_index' => 'publisher-top_users', 'new_index' => 'CONTENT', 'selector' => ' row'),
           ),array('start' => '{$pub[', 'end' => ']}'));
           $row = "row1";
        $i = (1*$num>1) ? (50*($num-1))+1 : 1*$num;
        main::init_function('rating');
        $content = "";
        while(($rows = $main->db->sql_fetchrow($result))){
           $rows['_class_row'] = $row;
           $rows['_num'] = $i;
           $rows['_user_country'] = get_flag($rows['user_country']);
           $rows['_user_link'] = $main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])));
           $rows['_user_birthday'] = ($rows['user_birthday']!='0000-00-00')?get_age($rows['user_birthday']):"-";
           $rows['_user_regdate'] = format_date($rows['user_regdate']);
           $rows['_user_website'] = ((!empty($rows['user_website']) AND $rows['user_website']!='http://')?"<a href='engine.php?do=redirect&amp;url={$rows['user_website']}' title='{$main->lang['website']}' target='_BLANK'><img src='includes/images/16x16/home.png' alt='{$main->lang['website']}' /></a>":"-");
           $gender_image = $rows['user_gender']==1?"male.png":($rows['user_gender']==2?"female.png":"");
           $rows['_user_gender_img'] = !empty($gender_image)?"includes/images/16x16/{$gender_image}":"";
           $rows = rating_modify_publisher($rows['uid'], 'users', $rows, $rows, $userconf['ratings']==ENABLED);
           $template->get_tpl('CONTENT', 'CONTENT');
           $template->set_tpl($rows, 'CONTENT', array('start' => '$pub[', 'end' => ']'));
           $content .= $template->tpl_create(true, 'CONTENT');
           $_temp = array('group' => $rows['title'], 'country' => $rows['user_country']);
           $row = ($row=="row1") ? "row2" : "row1";
           $i++; 
        }
        $template->get_tpl('publisher-top_users', 'publisher-top_users');
        $template->set_tpl(array('CONTENT'=>$content), 'publisher-top_users', array('start' => '{$pub[', 'end' => ']}'));
        $template->tpl_create(false, 'publisher-top_users');
        close();
        if ($main->db->sql_numrows($result)==50 OR isset($_GET['page'])){
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(u.uid) FROM ".USERS." AS u  WHERE u.user_name!='Guest'{$where}"));
            if($numrows>50){
                open();
                if(empty($wheres)) pages($numrows, 50, array('module' => $main->module), false);
                elseif($wheres=='country') pages($numrows, 50, array('module' => $main->module, 'do' => 'country', 'id' => str_replace(" ", "%20", $_GET['id'])), false);
                elseif($wheres=='group') pages($numrows, 50, array('module' => $main->module, 'do' => 'group', 'id' => str_replace(" ", "%20", $_GET['id'])), false);
                close();
            }
        }
        if(!empty($wheres) AND !empty($_temp)){
            if($wheres=='country') {
                add_meta_value($main->lang['regions']);
                add_meta_value($_temp['country']);
            } else {
                add_meta_value($main->lang['groups']);
                add_meta_value($_temp['group']);
            }
        }
    } else info($main->lang['noinfo']);
}

function show_goups(){
global $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    echo $navi;
    open();
    $result = $main->db->sql_query("SELECT g.*, (SELECT COUNT(uid) FROM ".USERS." AS u WHERE u.user_group=g.id OR u.user_groups LIKE CONCAT('%,',g.id,',%')) AS count FROM ".GROUPS." AS g ORDER BY id");
    echo "<table align='center' width='100%' class='table' id='form_{$main->module}'>".
    "<tr><th width='150'>{$main->lang['name']}</th><th>{$main->lang['group_descript']}</th><th width='80'>{$main->lang['count_points']}</th><th width='80'>{$main->lang['count_user_in_group']}</th></tr>";
    while(($rows = $main->db->sql_fetchrow($result))){
        echo "<tr><td><a href='".$main->url(array('module' => $main->module, 'do' => 'group', 'id' => $rows['id']))."' title='{$rows['title']}' style='color: #{$rows['color']};'>{$rows['title']}</a></td><td>{$rows['description']}</td><td align='center'>{$rows['points']}</td><td align='center'>{$rows['count']}</td></tr>";
    }
    echo "</table>";
    close();
}

function points_rule(){
global $main, $points, $navi, $modules;
    if(hook_check(__FUNCTION__)) return hook();
    echo $navi;
    open();
    echo "<table align='center' width='100%' class='table' id='form_{$main->module}'>".
    "<tr><th width='200'>{$main->lang['action_points']}</th><th>{$main->lang['descript_points']}</th><th width='50'>{$main->lang['count_points']}</th></tr>".
    ((file_exists('modules/media/') AND isset($modules['media']) AND $modules['media']['active']==1)?"<tr><td><b>{$main->lang['p_add_media']}</b>:</td><td>{$main->lang['p_add_media_d']}</td><td align='center'>{$points['add_media']}</td></tr>\n".    
    "<tr><td><b>{$main->lang['p_comment_media']}</b>:</td><td>{$main->lang['p_comment_media_d']}</td><td align='center'>{$points['comment_media']}</td></tr>\n".
    "<tr><td><b>{$main->lang['p_rating_media']}</b>:</td><td>{$main->lang['p_rating_media_d']}</td><td align='center'>{$points['rating_media']}</td></tr>\n":"").
    
    ((isset($modules['news']) AND $modules['news']['active']==1)?"<tr><td><b>{$main->lang['p_add_news']}</b>:</td><td>{$main->lang['p_add_news_d']}</td><td align='center'>{$points['add_news']}</td></tr>\n".    
    "<tr><td><b>{$main->lang['p_comment_news']}</b>:</td><td>{$main->lang['p_comment_news_d']}</td><td align='center'>{$points['comment_news']}</td></tr>\n".
    "<tr><td><b>{$main->lang['p_rating_news']}</b>:</td><td>{$main->lang['p_rating_news_d']}</td><td align='center'>{$points['rating_news']}</td></tr>\n":"").
    
    ((file_exists('modules/pages/') AND isset($modules['pages']) AND $modules['pages']['active']==1)?"<tr><td><b>{$main->lang['p_add_pages']}</b>:</td><td>{$main->lang['p_add_pages_d']}</td><td align='center'>{$points['add_pages']}</td></tr>\n".    
    "<tr><td><b>{$main->lang['p_comment_pages']}</b>:</td><td>{$main->lang['p_comment_pages_d']}</td><td align='center'>{$points['comment_pages']}</td></tr>\n".
    "<tr><td><b>{$main->lang['p_rating_pages']}</b>:</td><td>{$main->lang['p_rating_pages_d']}</td><td align='center'>{$points['rating_pages']}</td></tr>\n":"").
    
    ((file_exists('modules/files/') AND isset($modules['files']) AND $modules['files']['active']==1)?"<tr><td><b>{$main->lang['p_add_files']}</b>:</td><td>{$main->lang['p_add_files_d']}</td><td align='center'>{$points['add_files']}</td></tr>\n".    
    "<tr><td><b>{$main->lang['p_comment_files']}</b>:</td><td>{$main->lang['p_comment_files_d']}</td><td align='center'>{$points['comment_files']}</td></tr>\n".
    "<tr><td><b>{$main->lang['p_rating_files']}</b>:</td><td>{$main->lang['p_rating_files_d']}</td><td align='center'>{$points['rating_files']}</td></tr>\n":"").
    
    ((file_exists('modules/jokes/') AND isset($modules['jokes']) AND $modules['jokes']['active']==1)?"<tr><td><b>{$main->lang['p_add_jokes']}</b>:</td><td>{$main->lang['p_add_jokes_d']}</td><td align='center'>{$points['add_jokes']}</td></tr>\n".    
    "<tr><td><b>{$main->lang['p_rating_jokes']}</b>:</td><td>{$main->lang['p_rating_jokes_d']}</td><td align='center'>{$points['rating_jokes']}</td></tr>\n":"").
    
    ((file_exists('modules/faq/') AND isset($modules['faq']) AND $modules['faq']['active']==1)?"<tr><td><b>{$main->lang['p_add_faq']}</b>:</td><td>{$main->lang['p_add_faq_d']}</td><td align='center'>{$points['add_faq']}</td></tr>\n":"").
    
    "<tr><td><b>{$main->lang['p_rating_account']}</b>:</td><td>{$main->lang['p_rating_account_d']}</td><td align='center'>{$points['rating_account']}</td></tr>\n".
    
    ((isset($modules['voting']) AND $modules['voting']['active']==1)?"<tr><td><b>{$main->lang['p_voting']}</b>:</td><td>{$main->lang['p_voting_d']}</td><td align='center'>{$points['voting']}</td></tr>\n".
    "<tr><td><b>{$main->lang['p_comment_voting']}</b>:</td><td>{$main->lang['p_comment_voting_d']}</td><td align='center'>{$points['comment_voting']}</td></tr>\n":"").
    
    ((isset($modules['contact']) AND $modules['contact']['active']==1)?"<tr><td><b>{$main->lang['p_contact']}</b>:</td><td>{$main->lang['p_contact_d']}</td><td align='center'>{$points['contact']}</td></tr>\n":"").
    ((isset($modules['recommend']) AND $modules['recommend']['active']==1)?"<tr><td><b>{$main->lang['p_recoment']}</b>:</td><td>{$main->lang['p_recoment_d']}</td><td align='center'>{$points['recoment']}</td></tr>\n":"").
    
    ((isset($modules['forum']) AND $modules['forum']['active']==1)?"<tr><td><b>{$main->lang['p_forum_topic']}</b>:</td><td>{$main->lang['p_forum_post_d']}</td><td align='center'>{$points['forum_topic']}</td></tr>\n".    
    "<tr><td><b>{$main->lang['p_forum_post']}</b>:</td><td>{$main->lang['p_forum_topic_d']}</td><td align='center'>{$points['forum_post']}</td></tr>\n":"").
    "</table>";   
    close();
}
function switch_module_top_users(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "country": main_top_users('country'); break;
         case "group": main_top_users('group'); break;
         case "groups": show_goups(); break;
         case "points": points_rule(); break;
         default: main_top_users(); break;  
      }
   } else main_top_users();
}
switch_module_top_users();
?>