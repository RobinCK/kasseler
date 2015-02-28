<?php
/**
* Блок список последних посетивших роботов
* 
* @author Wit
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://kasseler-cms.net/
* @filesource blocks/block-monitor_last_robot.php
* @version 2.0
*/

if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $main, $modules, $supervision;
    //установки
    $limit_bots = 10;
    //инициализация
    $HTML = '';
    //прием параметров из блока blocks/block-monitor_online.php
    $where = (isset($_SESSION['online_monitor']['bots'])) ? $_SESSION['online_monitor']['bots'] : '';
    //если бот запись в бд
    if ($main->user['user_group']==3) {
        if (!isset($_SESSION['online_monitor']['bots_insert'])) {
            $date = kr_datecms("Y-m-d H:i:s");
            if($main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".ROBOT." WHERE name='{$main->user['user_name']}'"))>0){
                $main->db->sql_query("UPDATE ".ROBOT." SET visit='{$date}' WHERE name='{$main->user['user_name']}'");
            } else $main->db->sql_query("INSERT INTO ".ROBOT." (visit, name, country) VALUES ('{$date}', '{$main->user['user_name']}', '{$main->user['user_country']}')");		 
            $_SESSION['online_monitor']['bots_insert'] = true;
        }
    }
    //формирование основного контента
    $result = $main->db->sql_query("SELECT name, country, visit FROM ".ROBOT." WHERE name<>''{$where} ORDER BY visit DESC  LIMIT {$limit_bots}");
    if($main->db->sql_numrows($result)>0){
        $HTML.= "<table width='100%'>";
        while(($row = $main->db->sql_fetchrow($result))){
            $HTML.= "<tr><td width='25px'>".get_flag(($row['country'] == '') ? 'default' : $row['country'])."</td><td>{$row['name']}</td><td align='right'>".format_date($row['visit'], 'd.m.Y')."</td></tr>\n";	
        }
        $HTML.= "</table>";
    }
    //вывод результата
    echo $HTML;
?>