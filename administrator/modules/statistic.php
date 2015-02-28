<?php
 /**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");
global $main, $adminfile;

$result = $main->db->sql_query("SHOW TABLE STATUS");
$dbsize = 0;
$tablecounts = 0;
$rowcounts = 0;
$chars = array();
while(($row = $main->db->sql_fetchrow($result))){
    $tablecounts++;
    $dbsize += $row["Data_length"] + $row["Index_length"];
    $rowcounts += $row["Rows"];
    $chars[$row['Collation']] = (isset($chars[$row['Collation']])) ? $chars[$row['Collation']]+1:1;
}
$max_colla = 0;
$set_colla = "";
foreach($chars as $key=>$value){
    if($value>$max_colla){
        $max_colla = $value;
        $set_colla = $key;
    }
}

$newpublications = "<table width='100%' cellspacing='0' cellpadding='1'>";
if(file_exists("modules/news/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".NEWS." WHERE status='0'"));
    $newpublications .= "<tr><td>{$main->lang['newnews']}</td><td align='right'><a href='{$adminfile}?module=news'>{$counts}</a></td></tr>";
}
if(file_exists("modules/pages/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".PAGES." WHERE status='0'"));
    $newpublications .= "<tr><td>{$main->lang['newpages']}</td><td align='right'><a href='{$adminfile}?module=pages'>{$counts}</a></td></tr>";
}
if(file_exists("modules/files/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FILES." WHERE status='0'"));
    $newpublications .= "<tr><td>{$main->lang['newfiles']}</td><td align='right'><a href='{$adminfile}?module=files'>{$counts}</a></td></tr>";
}
if(file_exists("modules/media/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".MEDIA." WHERE status='0'"));
    $newpublications .= "<tr><td>{$main->lang['newmedia']}</td><td align='right'><a href='{$adminfile}?module=media'>{$counts}</a></td></tr>";
}
if(file_exists("modules/jokes/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".JOKES." WHERE status='0'"));
    $newpublications .= "<tr><td>{$main->lang['newjokes']}</td><td align='right'><a href='{$adminfile}?module=jokes'>{$counts}</a></td></tr>";
}
if(file_exists("modules/top_site/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".TOPSITES." WHERE status='0'"));
    $newpublications .= "<tr><td>{$main->lang['newsites']}</td><td align='right'><a href='{$adminfile}?module=top_site'>{$counts}</a></td></tr>";
}
$newpublications .= "</table>";


$allpub = "<table width='100%' cellspacing='0' cellpadding='1'>";
if(file_exists("modules/news/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".NEWS.""));
    $allpub .= "<tr><td>{$main->lang['allnews']}</td><td align='right'><a href='{$adminfile}?module=news'>{$counts}</a></td></tr>";
}
if(file_exists("modules/pages/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".PAGES.""));
    $allpub .= "<tr><td>{$main->lang['allpages']}</td><td align='right'><a href='{$adminfile}?module=pages'>{$counts}</a></td></tr>";
}
if(file_exists("modules/files/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FILES.""));
    $allpub .= "<tr><td>{$main->lang['allfiles']}</td><td align='right'><a href='{$adminfile}?module=files'>{$counts}</a></td></tr>";
}
if(file_exists("modules/media/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".MEDIA.""));
    $allpub .= "<tr><td>{$main->lang['allmedia']}</td><td align='right'><a href='{$adminfile}?module=media'>{$counts}</a></td></tr>";
}
if(file_exists("modules/jokes/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".JOKES.""));
    $allpub .= "<tr><td>{$main->lang['alljokes']}</td><td align='right'><a href='{$adminfile}?module=jokes'>{$counts}</a></td></tr>";
}
if(file_exists("modules/top_site/")){
    list($counts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".TOPSITES.""));
    $allpub .= "<tr><td>{$main->lang['allsites']}</td><td align='right'><a href='{$adminfile}?module=top_site'>{$counts}</a></td></tr>";
}
$allpub .= "</table>";
$dbinfo = "<table width='100%' cellspacing='0' cellpadding='1'>
<tr><td>{$main->lang['dbsize']}:</td><td align='right'>".get_size($dbsize)."</td></tr>
<tr><td>{$main->lang['tablesdb']}:</td><td align='right'>{$tablecounts}</td></tr>
<tr><td>{$main->lang['rowsdb']}:</td><td align='right'>{$rowcounts}</td></tr>
<tr><td>{$main->lang['charsetdb']}:</td><td align='right'>{$set_colla}</td></tr>
</table>";

list($alluser) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*)-1 FROM ".USERS.""));
list($usernoac) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".USERS." WHERE user_activation='1'"));
list($user7days) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".USERS." WHERE user_regdate>'".gmdate("Y-m-d H:i:s", strtotime("-7 day"))."'"));
list($user30days) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".USERS." WHERE user_regdate>'".gmdate("Y-m-d H:i:s", strtotime("-30 day"))."'"));
list($visittoday) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".USERS." WHERE user_last_visit>'".gmdate("Y-m-d H:i:s", strtotime("-1 day"))."'"));

$user_stat = "<table width='100%' cellspacing='0' cellpadding='1'>
<tr><td>{$main->lang['stat_alluser']}:</td><td align='right'><a href='{$adminfile}?module=users' title='{$main->lang['stat_alluser']}'>{$alluser}</a></td></tr>
<tr><td>{$main->lang['nocativeuser']}:</td><td align='right'>{$usernoac}</td></tr>
<tr><td>{$main->lang['sevendaysreg']}:</td><td align='right'>{$user7days}</td></tr>
<tr><td>{$main->lang['30daysreg']}:</td><td align='right'>{$user30days}</td></tr>
<tr><td>{$main->lang['visittoday']}:</td><td align='right'>{$visittoday}</td></tr>
</table>";

$filesystem = "
<table width='100%' cellspacing='0' cellpadding='1'>   
<tr><td>{$main->lang['freespase']}:</td><td align='right'>".get_size(function_exists('disk_free_space')?@disk_free_space("."):0)."</td></tr>
<tr><td>{$main->lang['logsize']}:</td><td align='right'>".get_size(dir_size("uploads/logs/"))."</td></tr>
</table>";

list($allforums) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FORUMS.""));  
list($allposts) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".POSTS.""));  
list($alltopics) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".TOPICS.""));  

$statforum = "
<table width='100%' cellspacing='0' cellpadding='1'>   
<tr><td>{$main->lang['stat_cats']}:</td><td align='right'>{$allforums}</td></tr>
<tr><td>{$main->lang['stat_topics']}:</td><td align='right'>{$alltopics}</td></tr>
<tr><td>{$main->lang['stat_posts']}:</td><td align='right'>{$allposts}</td></tr>
</table>";

echo "<table width='100%' cellspacing='0' cellpadding='4'>
<tr>
<th style='text-align:left;'>{$main->lang['stat_database']}</th>
<th style='text-align:left; padding-left:25px;'>{$main->lang['stat_newpub']}</th>
<th style='text-align:left; padding-left:25px;'>{$main->lang['stat_allpub']}</th>
</tr>
<tr>
<td width='33%' valign='top'>{$dbinfo}</td>
<td width='33%' valign='top' style='padding-left:25px;'>{$newpublications}</td>
<td width='33%' valign='top' style='padding-left:25px;'>{$allpub}</td>
</tr>
<tr>
<th style='text-align:left;'>{$main->lang['stat_user']}</th>
<th style='text-align:left; padding-left:25px;'>{$main->lang['stat_filesystem']}</th>
<th style='text-align:left; padding-left:25px;'>{$main->lang['stat_forum']}</th>
</tr>
<tr>
<td width='33%' valign='top'>{$user_stat}</td>
<td width='33%' valign='top' style='padding-left:25px;'>{$filesystem}</td>
<td width='33%' valign='top' style='padding-left:25px;'>{$statforum}</td>
</tr>
</table>";

$result = $main->db->sql_query("SELECT k.user_last_browser, COUNT(k.user_last_ip) AS count_users FROM ".USERS." AS k WHERE k.user_last_browser<>'' GROUP BY k.user_last_browser");
$us_btow = array();
$sum_und = 0;
while(($row = $main->db->sql_fetchrow($result))){
    if($row['user_last_browser']!='_NOINFO' AND $row['user_last_browser']!='N/A' AND $row['user_last_browser']!='undefined') $us_btow[] = array('name' => $row['user_last_browser'], 'count' => $row['count_users']);
    else $sum_und += $row['count_users'];
}
$sum = 0;
foreach($us_btow AS $value) $sum += $value['count'];
$sum += $sum_und;
echo "<table width='100%'><tr><th colspan='4'>{$main->lang['browsers_statistic']}</th></tr><tr><td>";
$pl = 1;
foreach($us_btow AS $value){    
    $proc = round(100*$value['count']/$sum, 2);
    echo "<div class='vote'><img style='margin-right: 5px;' src='includes/images/browsers/".mb_strtolower(str_replace(" ", "_", preg_replace('/(.*?)[\/\s]+([0-9\.]*)/is', '\\1', $value['name']))).".png' alt='{$value['name']}' align='left' />{$value['name']}</div><div class='progress polled progress_{$pl}'><span style='width: ".intval($proc)."%;'><b>{$proc}%</b></span></div>";
    $pl++;
    if($pl == 6) $pl=1;
}
if ($sum!=0){ 
    $proc = round(100*$sum_und/$sum, 2);
} else {
    $proc = round(100*$sum_und/1, 2);
}
echo "<div class='vote'><img style='margin-right: 5px;' src='includes/images/browsers/other.png' alt='{$main->lang['other_browsers']}' align='left' />{$main->lang['other_browsers']}</div><div class='progress polled progress_{$pl}'><span style='width: ".intval($proc)."%;'><b>{$proc}%</b></span></div>";
echo "</td></tr></table>";

$result = $main->db->sql_query("SELECT k.user_last_os, COUNT(k.user_last_ip) AS count_users FROM ".USERS." AS k WHERE k.user_last_os<>'' GROUP BY k.user_last_os");
$us_btow = array();
$sum_und = 0;
while(($row = $main->db->sql_fetchrow($result))){
    if($row['user_last_os']!='_NOINFO' AND $row['user_last_os']!='N/A' AND $row['user_last_os']!='undefined') $us_btow[] = array('name' => $row['user_last_os'], 'count' => $row['count_users']);
    else $sum_und += $row['count_users'];
}
$sum = 0;
foreach($us_btow AS $value) $sum += $value['count'];
$sum += $sum_und;
echo "<table width='100%'><tr><th>{$main->lang['os_statistic']}</th></tr><tr><td>";
$pl = 1;
foreach($us_btow AS $value){    
    $proc = round(100*$value['count']/$sum, 2);
    echo "<div class='vote'>{$value['name']} </div><div class='progress polled progress_{$pl}'><span style='width: ".intval($proc)."%;'><b>{$proc}%</b></span></div>";
    $pl++;
    if($pl == 6) $pl=1;
}
if ($sum!=0){
    $proc = round(100*$sum_und/$sum, 2);
} else {
    $proc = round(100*$sum_und/1, 2);
}
echo "<div class='vote'>{$main->lang['other_os']} </div><div class='progress polled progress_{$pl}'><span style='width: ".intval($proc)."%;'><b>{$proc}%</b></span></div>";
echo "</td></tr></table>";
?>