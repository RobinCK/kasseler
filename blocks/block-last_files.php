<?php
/**
* Блок последних файлов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-last_files.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main;

$result = $main->db->sql_query("SELECT id, files_id, title, hits FROM ".FILES." WHERE status='1' ORDER BY date DESC LIMIT 5");
while(list($id, $files_id, $title) = $main->db->sql_fetchrow($result)) {
    echo "<a href='".$main->url(array('module' => 'files', 'do' => 'more', 'id' => case_id($files_id, $id)))."' title='{$title}'>{$title}</a><br />";
}

?>