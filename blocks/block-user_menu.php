<?php
/**
* Блок меню пользователя
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-user_menu.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main, $supervision, $userconf;
if(is_guest()) $content = "<div><img src='includes/images/blocks/newuser.png' align='left' alt='' style='margin-right: 3px;' /><a class='sys_link' href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['new_user']}'>{$main->lang['new_user']}</a></div>
<div><img src='includes/images/blocks/passlost.png' align='left' alt='' style='margin-right: 3px;' /><a class='sys_link' href='".$main->url(array('module' => 'account', 'do' => 'new_password'))."' title='{$main->lang['forgot_your_password']}'>{$main->lang['forgot_your_password']}</a></div><hr />
<form action='".$main->url(array('module' => 'account', 'do' => 'sign' ))."' method='post'><table width='100%'><tr><td>{$main->lang['login']}:</td><td><input  type='text' name='user_name' /></td></tr><tr><td>{$main->lang['password']}:</td><td><input  type='password' name='user_password' /></td></tr><tr><td colspan='2' align='center'><input type='submit' class='button_style' style='margin-top: 5px;' value='{$main->lang['send']}' /></td></tr></table></form>";
else $content = "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><img src='includes/images/blocks/logout.png' alt='' title='{$main->lang['logout']}' align='left' style='margin-right: 3px;' /> {$main->lang['logout']}</a>";

$online = '';
if(!empty($supervision['users'])) foreach($supervision['users'] as $key) $online .= "<tr><td>".get_flag($key['country'])."<a style='color:#{$key['color']};' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($key['user_id'], $key['uid'])))."' id='info_{$key['user_id']}' class='user_info'>{$key['uname']}</a></td><td align='right'>".((!empty($key['url']))?"<a href='{$key['url']}'>{$key['module']}</a>":"&nbsp;")."</td></tr>\n";
if(!empty($supervision['bots'])) foreach($supervision['bots'] as $key) $online .= "<tr><td>".get_flag($key['country'])."{$key['uname']}</td><td align='right'>".((!empty($key['url']))?"<a href='{$key['url']}'>{$key['module']}</a>":"&nbsp;")."</td></tr>\n";
$online = !empty($online) ? "<table width='100%'>{$online}</table>" : $online;
echo "<div align='center'><b>{$main->lang['hello']}, ".(!is_guest()?"<a href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($main->user['user_id'], $main->user['uid'])))."' title='{$main->lang['user_profile']}'>{$main->user['user_name']}</a>":$main->user['user_name'])."</b><hr />".get_avatar($main->user, 'normal')."</div>
<hr />{$content}<div class='monitoring'><hr />

<div><span class='pixel pico pico_admin'></span>{$main->lang['admin_mon']}<span> (".count($supervision['admin']).") </span></div>
<div><span class='pixel pico pico_user'></span>{$main->lang['users_mon']}<span> (".count($supervision['users']).") </span></div>
<div><span class='pixel pico pico_bot'></span>{$main->lang['bots_mon']}<span> (".count($supervision['bots']).") </span></div>
<div><span class='pixel pico pico_guest'></span>{$main->lang['guest_mon']}<span> (".count($supervision['guest']).") </span></div>
<hr />
<div class='pointer' onclick=\"switcher(this, 'list_online');\"><span class='pixel pico_hide'></span><span class='pixel pico pico_users'></span>{$main->lang['all_mon']}<span> (".count(array_merge($supervision['users'], $supervision['bots'], $supervision['admin'], $supervision['guest'])).") </span></div>
<div><div class='list_online' style='display:none;'>{$online}</div></div>
</div>";
?>