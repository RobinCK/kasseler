<?php
/**
* Файлы вывода пользовательских закладок
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource modules/account/info/favorites.php
* @version 2.0
*/
if (!defined('KASSELERCMS') OR !defined('ACCOUNT')) die("Hacking attempt!"); 

global $main, $modules;
$result = $main->db->sql_query("SELECT fav.id AS fav_id, fav.post, fav.modul, fav.users, 
        n.news_id, f.files_id, p.pages_id, m.media_id,
        n.title AS news_title, f.title AS files_title, p.title AS pages_title, m.title AS media_title, j.title AS jokes_title,
        n.date AS news_date, f.date AS files_date, p.date AS pages_date, m.date AS media_date, j.date AS jokes_date,
        n.author AS news_author, f.author AS files_author, p.author AS pages_author, m.placed AS media_author, j.author AS jokes_author
    FROM ".FAVORITE." AS fav
    LEFT JOIN ".NEWS."  AS n ON (fav.post=n.id) 
    LEFT JOIN ".FILES." AS f ON (fav.post=f.id) 
    LEFT JOIN ".PAGES." AS p ON (fav.post=p.id) 
    LEFT JOIN ".MEDIA." AS m ON (fav.post=m.id) 
    LEFT JOIN ".JOKES." AS j ON (fav.post=j.id) 
    WHERE fav.users='{$main->user['user_name']}' AND fav.modul<>'audio' ORDER BY fav.id DESC");
echo "<div id='favorites_content'>";
if($main->db->sql_numrows($result)){
    open();
    echo "<h2 class='account_plug'>{$main->lang['favorite']}</h2><table class='table2 table_tr' width='100%'><thead><tr><th width='25'>#</th><th>{$main->lang['title']}</th><th width='120'>{$main->lang['module']}</th><th width='80'>{$main->lang['date']}</th><th width='80'>{$main->lang['author']}</th><th width='80'>{$main->lang['functions']}</th></tr></thead><tbody>";
    $i=1; $tr = 'row4';
    while($row = $main->db->sql_fetchrow($result)){
        $url = $main->url(array('module' => $row['modul'], 'do' => 'more', 'id' => case_id($row['modul']!='jokes'?$row[$row['modul'].'_id']:$row['post'], $row['post'])));
        $op = "<table cellspacing='1' class='cl'><tr><td>".delete_button("index.php?ajaxed=delete_fav&amp;id={$row['fav_id']}", 'favorites_content')."</td></tr></table>";
        echo "<tr class='{$tr}'><td align='center'>{$i}</td><td><a class='sys_link' href='{$url}' title='{$row[$row['modul'].'_title']}'>{$row[$row['modul'].'_title']}</a></td><td align='center'><a href='".$main->url(array('module' => $row['modul']))."' title='".(isset($main->lang[$row['modul']])?$main->lang[$row['modul']]:$row['modul'])."'>".(isset($main->lang[$row['modul']])?$main->lang[$row['modul']]:$row['modul'])."</a></td><td align='center'>".format_date($row[$row['modul'].'_date'])."</td><td align='center'>".(!is_guest_name($row[$row['modul'].'_author'])?"<a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => urlencode($row[$row['modul'].'_author'])))."' title='{$row[$row['modul'].'_author']}'>{$row[$row['modul'].'_author']}</a>":$row[$row['modul'].'_author'])."</td><td align='center'>{$op}</td></tr>";
        $i++; $tr = ($tr=='row4') ? 'row5' : 'row4';
    }
    echo "</tbody></table>";
    close();
}
echo "</div>";
?>