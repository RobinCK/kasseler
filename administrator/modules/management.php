<?php
 /**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");

global $adminfile, $lang, $main;
function _get_links_module(){
    if(hook_check(__FUNCTION__)) return hook();
    $modules = scan_dir("administrator/links/", '/(.+?)\.php$/i');
    foreach($modules as $k => $v) $modules[$k] = "administrator/links/{$v}";
    sort($modules);
    return $modules;
}
$modules = _get_links_module();
$count = count($modules);

echo "<table class='modulelist'>";
    $i=0;
    while($i<$count){
        echo "<tr><td>";
        for($y=1;$y<=3;$y++){
            if(isset($modules[$i+$y-1])){
                $link = array();
                require_once $modules[$i+$y-1];
                $path = isset($link['icon_patch']) ? $link['icon_patch'] : 'includes/images/admin/' ;
                $link['name'] = (isset($main->lang[$link['name']])) ? $main->lang[$link['name']] : $link['name'];
                $link['ico'] = (isset($link['ico']) AND file_exists("{$path}{$link['ico']}")) ? "{$path}{$link['ico']}" : "includes/images/admin/ico.png";
                if(isset($link['desc'])) {
                    $link['desc'] = (isset($main->lang[$link['desc']])) ? "<i>".$main->lang[$link['desc']]."</i>" : "<i>".$link['desc']."</i>";
                } else $link['desc'] = '';
                $mod = str_replace(".php", "", str_replace('.links.php', '', basename($modules[$i+$y-1])));
                echo "<div><a href='{$adminfile}?module={$mod}' title='{$link['name']}'><span><img src='{$link['ico']}' alt='' /><b>{$link['name']}</b><br />{$link['desc']}</span></a></div>";
            }    
        }
        echo "</td></tr>";
        $i+=3;
    }
echo "</table>";
?>