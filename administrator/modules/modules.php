<?php
 /**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $modules_links, $main, $adminfile;

function _get_user_module(){
    if(hook_check(__FUNCTION__)) return hook();
    $modules = array();
    if(($handle = opendir("modules/"))){
        while (false !== ($file = readdir($handle))) if(is_dir("modules/{$file}") AND file_exists("modules/{$file}/admin/")) $modules[] = "modules/{$file}/admin/link.php";
        closedir($handle);
    }
    sort($modules);
    return $modules;
}

$modules = _get_user_module();
$count = count($modules);

echo "<table class='modulelist'>";
$i=0;
while($i<$count){
    echo "<tr><td>";
    for($y=1;$y<=3;$y++){
        if(isset($modules[$i+$y-1])){
            $f = str_replace('.php', '', str_replace('.links.php', '', str_replace('modules/', '', str_replace('/admin/link.php', '', $modules[$i+$y-1]))));
            $link = array();
            require_once $modules[$i+$y-1];
            
            $modules_links[$f] = $link;
            $path = isset($link['icon_patch']) ? $link['icon_patch'] : 'includes/images/admin/' ;
            $link['name'] = (isset($main->lang[$link['name']])) ? $main->lang[$link['name']] : $link['name'];
            $link['ico'] = (isset($link['ico']) AND file_exists("{$path}{$link['ico']}")) ? "{$path}{$link['ico']}" : "includes/images/admin/ico.png";
            if(isset($link['desc'])) {
                $link['desc'] = (isset($main->lang[$link['desc']])) ? "<i>".$main->lang[$link['desc']]."</i>" : "<i>".$link['desc']."</i>";
            } else $link['desc'] = '';
            echo "<div><a href='{$adminfile}?module={$f}' title='{$link['name']}'><span><img src='{$link['ico']}' alt='' /><b>{$link['name']}</b><br />{$link['desc']}</span></a></div>";
        }    
    }
    echo "</td></tr>";
    $i+=3;
}
echo "</table>";
?>