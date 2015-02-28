<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");
global $main, $adminfile;

echo "<table width='100%' cellspacing='0' cellpadding='1'>";
if(file_exists("modules/news/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".NEWS." WHERE status='0'"));
    echo "<tr><td>{$main->lang['newnews']}</td><td align='right'><a href='{$adminfile}?module=news'>{$counts}</a></td></tr>";
}
if(file_exists("modules/pages/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".PAGES." WHERE status='0'"));
    echo "<tr><td>{$main->lang['newspages']}</td><td align='right'><a href='{$adminfile}?module=pages'>{$counts}</a></td></tr>";
}
if(file_exists("modules/files/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FILES." WHERE status='0'"));
    echo "<tr><td>{$main->lang['newfiles']}</td><td align='right'><a href='{$adminfile}?module=files'>{$counts}</a></td></tr>";
}
if(file_exists("modules/media/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".MEDIA." WHERE status='0'"));
    echo "<tr><td>{$main->lang['newmedia']}</td><td align='right'><a href='{$adminfile}?module=media'>{$counts}</a></td></tr>";
}
if(file_exists("modules/jokes/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".JOKES." WHERE status='0'"));
    echo "<tr><td>{$main->lang['newjokes']}</td><td align='right'><a href='{$adminfile}?module=jokes'>{$counts}</a></td></tr>";
} 
if(file_exists("modules/top_site/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".TOPSITES." WHERE status='0'"));
    echo "<tr><td>{$main->lang['newsites']}</td><td align='right'><a href='{$adminfile}?module=top_site'>{$counts}</a></td></tr>";
}
echo "</table>";
?>
