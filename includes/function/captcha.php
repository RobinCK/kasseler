<?php
function generate_captcha(){
   global $captcha, $module_name, $lang, $config, $lang_dbg;
   if(hook_check(__FUNCTION__)) return hook();
   $time=microtime(true);
   return "<!--captcha-->\n".
   "<tr class='row_tr'><td colspan='2' class='form_text' style='text-align:left;'><b>{$lang['captcha_confirmation']}</b></td></tr>\n".
   "<tr class='row_tr'>\n<td class='form_text'>{$lang['captcha']}:</td>\n<td class='form_input'>\n<div id='newseccode' style='width: 120px;'>\n<img style='width: 110px; height: 45px; border: 1px #666666 solid; cursor: pointer;' id='imgseccode' src='captcha.php?{$time}' alt='' onclick=\"captcha();\" /></div>\n</td>\n</tr>\n".
   "<tr class='row_tr'><td class='form_text'>{$lang['captcha_input']}:<span class='star'>*</span></td><td class='form_input'><input class='input_text' type='text' style='width: 97%' name='seccode' id='seccode' /></td></tr>\n<!--/captcha-->\n";
}

function captcha(){
global $captcha, $module_name, $lang, $config, $lang_dbg;
    if(hook_check(__FUNCTION__)) return hook();
    if(defined("ADMIN_FILE") AND !defined("ADMIN_FILE_C")) return false;
    if(empty($config['captcha_free'])) $config['captcha_free'] = '-1,';
    return !check_user_group($config['captcha_free']) ? generate_captcha() : "";
}

function check_captcha(){
global $captcha, $module_name, $lang, $config, $lang_dbg;
    if(hook_check(__FUNCTION__)) return hook();
    if(defined("ADMIN_FILE") AND !defined("ADMIN_FILE_C")) return false;
    $msg = (!isset($_SESSION['security_keystring']) OR !isset($_POST['seccode']) OR $_POST['seccode'] != $_SESSION['security_keystring']) ? $lang['not_correct_capcha'] : "";
    unset($_SESSION['security_keystring']);
    if(empty($config['captcha_free'])) $config['captcha_free'] = '-1,';
    return !check_user_group($config['captcha_free']) ? $msg : "";
}
?>
