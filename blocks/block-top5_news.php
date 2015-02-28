<?php
/**
* Блок TOP5 новостей
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-top5_news.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main;

$result = $main->db->sql_query("SELECT id, news_id, title, view FROM ".NEWS." WHERE status='1' ORDER BY view DESC LIMIT 5");
while(list($id, $news_id, $title) = $main->db->sql_fetchrow($result)) {
    echo "<a href='".$main->url(array('module' => 'news', 'do' => 'more', 'id' => case_id($news_id, $id)))."' title='{$title}'>{$title}</a><br />";
}
?>