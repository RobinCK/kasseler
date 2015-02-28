<?php
    /**
    * @author Igor Ognichenko, Browko Dmitrey
    * @copyright Copyright (c)2007-2010 by Kasseler CMS
    * @link http://www.kasseler-cms.net/
    * @version 2.0
    */
    if (!defined('KASSELERCMS')) die("Hacking attempt!");

    global $main, $forum, $template;

    $row = forum_post_info(intval($_GET['id']));
    forum_open_access_forum($row['tree'], $row['forum_id']);
    if(check_access_forum(accModerator)){
        $template->get_tpl('forum/show_ip', 'show_ip');

        $result = $main->db->sql_query("SELECT DISTINCT(poster_name), poster_ip, poster_id FROM ".POSTS." WHERE poster_ip='{$row['poster_ip']}'");
        $list = "<table width='100%' cellspacing='0' cellpadding='3'>";
        $ip_row = 'showip_row1';
        while(($pos = $main->db->sql_fetchrow($result))){
            $list .= "<tr class='{$ip_row}'><td><a href='{$main->config['whois']}{$pos['poster_ip']}' target='_BLANK'>{$pos['poster_ip']}</a> - <a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id(urlencode($pos['poster_name']), $pos['poster_id'])))."' title='{$main->lang['user_profile']}'>{$pos['poster_name']}</a></td></tr>";
            $ip_row = ($ip_row=='showip_row')?'showip_row2':'showip_row1';
        }
        $list .= "</table>";
        $result = $main->db->sql_query("SELECT DISTINCT(poster_name), poster_ip, poster_id FROM ".POSTS." WHERE poster_name='{$row['poster_name']}'");
        $list2 = "<table width='100%' cellspacing='0' cellpadding='3'>";
        $ip_row = 'showip_row1';
        while(($post = $main->db->sql_fetchrow($result))){
            $list2 .= "<tr class='{$ip_row}'><td><a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id(urlencode($post['poster_name']), $post['poster_id'])))."' title='{$main->lang['user_profile']}'>{$post['poster_name']}</a> - <a href='{$main->config['whois']}{$post['poster_ip']}' target='_BLANK'>{$post['poster_ip']}</a></td></tr>";
            $ip_row = ($ip_row=='showip_row1')?'showip_row2':'showip_row1';
        }
        $list2 .= "</table>";
        $template->set_tpl(array(
        'OPEN_TABLE'                => open(true),
        'CLOSE_TABLE'               => close(true),    
        'LOAD_TPL'                  => $main->tpl,   
        'L_INDEX'                   => "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",     
        'showip.OTHER_IPS_USER'     => $list2,
        'showip.OTHER_USERS_IPS'    => $list,
        'L_THIS_USER_IP'            => $main->lang['this_user_ip'],
        'L_OTHER_USER_IP'           => $main->lang['other_user_ip'],
        'L_OTHER_IP_USERS'          => $main->lang['other_ip_users'],
        'showip.USER_IP'            => "<a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id(urlencode($row['poster_name']), $row['poster_id'])))."' title='{$main->lang['user_profile']}'>{$row['poster_name']}</a> - <a href='{$main->config['whois']}{$row['poster_ip']}' target='_BLANK'>{$row['poster_ip']}</a>",        
        ), 'show_ip', array('start' => '{', 'end' => '}'));  

        $template->tpl_create(false, 'show_ip');  
    } else kr_http_ereor_logs("403");
?>