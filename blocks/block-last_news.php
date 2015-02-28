<?php
/**
* Блок последних новостей
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-last_news.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main;
$result = $main->db->sql_query("SELECT id, news_id, title FROM ".NEWS." WHERE status='1' ORDER BY date DESC LIMIT 5");
while(($row = $main->db->sql_fetchrow($result))){
    echo "<div><a href='".$main->url(array('module' => 'news', 'do' => 'more', 'id' => case_id($row['news_id'], $row['id'])))."' title='{$row['title']}'>{$row['title']}</a></div>";
}

?>