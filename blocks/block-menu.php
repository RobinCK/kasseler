<?php
/**
* Блок меню сайта
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-menu.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $main, $menu_config;
if(count($menu_config)>0){
    echo "<ul class='navs'>\n";
    $arr_groups = array_merge(!empty($main->user['group'])?array($main->user['group']):array(), !empty($main->user['groups'])?explode(',', $main->user['groups']):array());
    foreach($menu_config as $row){
        if(!empty($row['groups'])  AND !check_user_group($row['groups'])) continue;
        echo "<li><a class='modules_menu {$row['class']}' href='{$row['url']}' title='{$row['title']}'><span>{$row['title']}</span></a></li>\n";
    }
     echo "</ul>";
}
?>