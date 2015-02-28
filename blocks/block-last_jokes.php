<?php
/**
* Блок последних анекдотов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-last_jokes.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $db, $prefix, $config;
$result = $db->sql_query("SELECT id, title FROM ".JOKES." WHERE status='1' ORDER BY date DESC LIMIT 5");
while(list($id, $title) = $db->sql_fetchrow($result)) {
    echo "<a href='".$main->url(array('module' => 'jokes', 'do' => 'more', 'id' => $id))."' title='{$title}'>{$title}</a><br />";
}

?>