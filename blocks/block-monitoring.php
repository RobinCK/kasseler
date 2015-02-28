<?php
/**
* Блок мониторинга
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-monitoring.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main, $img, $adminfile;
$result = $main->db->sql_query("SELECT s.sid, s.uname, s.is_admin, s.ip, s.time, s.module, s.url, s.user_agent, s.country, u.uid, u.user_id, u.user_name, u.user_group, g.id, g.title, g.color FROM ".SESSIONS." AS s LEFT JOIN ".USERS." AS u ON(s.uname=u.user_name) LEFT JOIN ".GROUPS." AS g ON(u.user_group=g.id) WHERE s.actives='y' GROUP BY s.uname");
$admin = array();
$users = array();
$bots = array();
$guest = array();
while(list($sid, $uname, $is_admin, $ip, $time, $module, $url, $user_agent, $country, $uid, $user_id, $user_name, $user_group, $gid, $gtitle, $color) = $main->db->sql_fetchrow($result)){
    $url = str_replace("&", "&amp;", $url);
    if(($is_admin==1 OR $is_admin==2) AND !is_ip($uname)) $admin[] = "<div class='monitoring'>".get_flag($country)."<a style='color:#{$color};' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($user_id, $uid)))."' id='info_{$uname}' class='user_info'>{$uname}</a><span class='urlm'>".((!empty($url))?"<a href='{$url}'>{$module}</a>":"&nbsp;")."</span></div>\n";
    elseif(!is_ip($uname) AND !is_bot($uname)) $users[] = "<div class='monitoring'>".get_flag($country)."".(!empty($user_name)?"<a style='color:#{$color};' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($user_id, $uid)))."' id='info_{$uname}' class='user_info'>{$uname}</a>":$uname)."<span class='urlm'>".((!empty($url))?"<a href='{$url}'>{$module}</a>":"&nbsp;")."</span></div>\n";
    elseif(is_bot($uname)) $bots[] = "<div class='monitoring'>".get_flag($country)."{$uname}<span class='urlm'>".(!empty($url)?"<a href='{$url}'>{$module}</a>":$module)."</span></div>\n";
    elseif(is_ip($uname)) $guest[] = "<div class='monitoring'>".get_flag($country)."<a href='{$main->config['whois']}{$ip}' title='".htmlspecialchars($user_agent, ENT_QUOTES)."'>{$uname}</a><span class='urlm'>".((!empty($url))?"<a href='{$url}'>{$module}</a>":"&nbsp;")."</span></div>\n";
}
echo "&#187;&nbsp; <a href='{$adminfile}' title='{$main->lang['adminpanel']}'>{$main->lang['adminpanel']}</a><br />
&#187;&nbsp; <a href='{$adminfile}?module=logout'>{$main->lang['logout']}</a><br />
<hr />

<div class='monitoring'>
    <div class='pointer' onclick=\"switcher(this, 'admin_online');\"><span class='pixel pico_hide'></span><span class='pixel pico pico_admin'></span>{$main->lang['admin_mon']}<span> (".count($admin).") </span></div>
    <div><div class='admin_online' style='display:none;'>".((count($admin)>0) ? implode("", $admin)."<hr />": "")."</div></div>

    <div class='pointer' onclick=\"switcher(this, 'user_online');\"><span class='pixel pico_hide'></span><span class='pixel pico pico_user'></span>{$main->lang['users_mon']}<span> (".count($users).") </span></div>
    <div><div class='user_online' style='display:none;'>".((count($users)>0) ? implode("", $users)."<hr />": "")."</div></div>

    <div class='pointer' onclick=\"switcher(this, 'bots_online');\"><span class='pixel pico_hide'></span><span class='pixel pico pico_bot'></span>{$main->lang['bots_mon']}<span> (".count($bots).") </span></div>
    <div><div class='bots_online' style='display:none;'>".((count($bots)>0) ? implode("", $bots)."<hr />": "")."</div></div>

    <div class='pointer' onclick=\"switcher(this, 'guest_online');\"><span class='pixel pico_hide'></span><span class='pixel pico pico_guest'></span>{$main->lang['guest_mon']}<span> (".count($guest).") </span></div>
    <div><div class='guest_online' style='display:none;'>".((count($guest)>0) ? implode("", $guest)."<hr />": "")."</div></div>

    <div style='padding-left: 20px;'><span class='pixel pico pico_users'></span>{$main->lang['all_mon']}<span> (".(count($guest)+count($bots)+count($users)+count($admin)).") </span></div>
</div>";

?>
