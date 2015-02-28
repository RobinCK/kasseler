<?php
/**
* Блок TOP5 сайтов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-top5_sites.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main;

$result = $main->db->sql_query("SELECT * FROM ".TOPSITES." WHERE status='1' ORDER BY hits_in DESC LIMIT 5");
while($row = $main->db->sql_fetchrow($result)) {
    echo "<a href='".$main->url(array('module' => 'top_site', 'do' => 'goto', 'id' => $row['id']))."' title='{$row['title']}'>{$row['title']}</a><br />";
}
?>