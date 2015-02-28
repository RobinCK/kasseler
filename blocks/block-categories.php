<?php
/**
* Блок вывода категорий
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-categories.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main;
$result = $main->db->sql_query("SELECT cid, cat_id, title, module FROM ".CAT." WHERE module='{$main->module}' ORDER BY title");
if($main->db->sql_numrows($result)>0){
    echo "<ul class='list_cat_block'>";
    while(list($cid, $cat_id, $title, $module) = $main->db->sql_fetchrow($result)) {
        echo "<li><a href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($cat_id, $cid)))."' title='{$title}'>{$title}</a></li>";
    }
    echo "</ul>";
}
?>