<?php
/**
* Блок аудио публикация
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-audio.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
  Header("Location: ../index.php");
  exit;
}

global $main, $tpl_create;
$result = $main->db->sql_query("SELECT * FROM ".AUDIO." ORDER BY id DESC LIMIT 10");
$last = $popular = "";
while($row = $main->db->sql_fetchrow($result)) $last .= "<a href='".$main->url(array('module' => 'audio', 'do' => 'more', 'id' => $row['id']))."' title='{$row['title']} - {$row['name']}'><b>{$row['title']}</b> - {$row['name']}</a><br />";
$result = $main->db->sql_query("SELECT * FROM ".AUDIO." ORDER BY playing DESC LIMIT 10");
while($row = $main->db->sql_fetchrow($result)) $popular .= "<a href='".$main->url(array('module' => 'audio', 'do' => 'more', 'id' => $row['id']))."' title='{$row['title']} - {$row['name']}'><b>{$row['title']}</b> - {$row['name']}</a><br />";
echo "<table cellpadding='3' cellspacing='1' width='100%' class='block_table'><tr><th>{$main->lang['newlatpub']}</th><th>{$main->lang['popular']}</th></tr><tr class='row1'><td>{$last}</td><td>{$popular}</td></tr></table>";
?>