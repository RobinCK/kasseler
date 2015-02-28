<?php
/**
* Блок список последних посетивших пользователей
* 
* @author Wit
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://kasseler-cms.net/
* @filesource blocks/block-monitor_last_user.php
* @version 2.0
*/

if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $main, $modules, $supervision;
    //установки
    $admin_view = true; //показывать присутствие админа для других групп
    $limit_user = 10;
    //инициализация
    $HTML = '';
    $admin_view = (is_admin()) ? true : $admin_view;
    $where_user = ($admin_view ) ? '' : " AND u.user_level<>'2'";
    //прием параметров из блока blocks/block-monitor_online.php
    $where = (isset($_SESSION['online_monitor']['user'])) ? $_SESSION['online_monitor']['user'] : '';
    //формирование основного контента
    $result = $main->db->sql_query("SELECT u.uid, u.user_id, u.user_name, u.user_group, u.user_last_visit, u.user_country, g.id, g.title, g.color FROM ".USERS." AS u LEFT JOIN ".GROUPS." AS g ON(u.user_group=g.id) WHERE u.user_activation='0'{$where_user}{$where} ORDER BY u.user_last_visit DESC LIMIT {$limit_user}");
    if($main->db->sql_numrows($result)>0){
        $HTML.= "<table width='100%' valign='top'>";
        while(($row = $main->db->sql_fetchrow($result))){
            $HTML.= "<tr><td width='25px'>".get_flag(($row['user_country'] == '') ? 'default' : $row['user_country'])."</td><td><a class='user_info' style='color:#{$row['color']};' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."'>{$row['user_name']}</a></td><td align='right'>".format_date($row['user_last_visit'], 'd.m.Y')."</td></tr>\n";	
        }
        $HTML.= "</table>";
    }
    //вывод результата
    echo $HTML;
?>