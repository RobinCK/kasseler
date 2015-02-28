<?php
/**
* Блок TOP5 пользователей
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-top5_users.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
$count_users = 5; //Количество выводимых пользователей 

global $main, $userconf;
$result = $main->db->sql_query("SELECT uid, user_id, user_name, user_avatar, user_posts, user_points FROM ".USERS." WHERE user_name<>'Guest' ORDER BY user_points DESC LIMIT {$count_users}");
echo "<table cellspacing='0' cellpadding='1'>";
while(($row = $main->db->sql_fetchrow($result))) echo "<tr><td align='center' width='60'>".get_avatar($row, 'small')."</td><td><a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['user_name']}</a><br />{$main->lang['points']}: {$row['user_points']}<br />{$main->lang['posts']}: {$row['user_posts']}</td></tr>\n";
echo "</table>";
?>