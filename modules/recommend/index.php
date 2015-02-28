<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi;
$navi = navi(array(), false, false);

function main_recommend($msg=""){
global $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    echo $navi;
    if(!empty($msg)) warning($msg);
    $disabled = (is_user()) ? true : false;
    open();
    echo "<form method='post' action='".$main->url(array('module' => $main->module, 'do' => 'send_recommend'))."'>\n".
    "<table class='form' align='center' id='form_{$main->module}'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("name", "input_text", $main->user['user_name'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_mail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("mail", "input_text", $main->user['user_email'], $disabled)."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_friend_name']}:<span class='star'>*</span></td><td class='form_input'>".in_text("friend_name", "input_text")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['you_friend_mail']}:<span class='star'>*</span></td><td class='form_input'>".in_text("friend_mail", "input_text")."</td></tr>\n".
    captcha().
    "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
    "</table>\n".
    "</form>\n";
    close();
}

function send_recommend(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    filter_arr(array('friend_mail', 'friend_name', 'name', 'mail'), POST, TAGS);
    $msg = error_empty(array('name', 'friend_name', 'friend_mail'), array('error_user_name', 'empty_friend_name', 'empty_friend_mail')).check_captcha().check_mail($_POST['mail']);
    if(empty($msg)){
        add_points($main->points['recoment']);
        $message = preg_replace(array("#{USER}#", "#{SITENAME}#", "#{DESCRIPTION}#", "#{SITEURL}#"), array($_POST['name'], "<a href='{$main->config['http_home_url']}' title='{$main->config['home_title']}'>{$main->config['home_title']}</a>", $main->config['description'], "<a href='{$main->config['http_home_url']}' title='{$main->config['home_title']}'>{$main->config['http_home_url']}</a>"), $main->lang['recommend_message']);
        send_mail($_POST['friend_mail'], $_POST['friend_name'], $_POST['mail'], $_POST['name'], "{$main->title} - {$main->config['site_name_for_mail']}", $message."<br /><br />User name: {$main->user['user_name']}<br />IP: {$main->ip}<br />Date: ".kr_date("Y-m-d H:i:s"));
        meta_refresh(3, $main->url(array('module' => $main->module)), $main->lang['recommend_send']);
    } else main_recommend($msg);
}
function switch_module_recommend(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "send_recommend": send_recommend(); break;
         default: kr_http_ereor_logs("404"); break;
      }
   } else main_recommend();
}
switch_module_recommend();
?>