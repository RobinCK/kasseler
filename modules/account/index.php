<?php
/**
* Модуль пользовательского профиля
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource modules/account/index.php
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");
define('PRIVMSG', true);
define('ACCOUNT', true); 

global $navi, $tpl_create, $bread_crumb_array, $main, $icon_navi;
$navi = navi(array(), false, false);
$icon_navi = array(
    array('title' => $main->lang['account'], 'url' => $main->url(array('module' => $main->module)), 'image' => 'includes/images/48x48/home.png'),
    array('title' => $main->lang['user_controls'], 'url' => $main->url(array('module' => $main->module, 'do' => 'controls')), 'image' => 'includes/images/48x48/controlpanel.png'),
    array('title' => $main->lang['favorite'], 'url' => $main->url(array('module' => $main->module, 'do' => 'favorite')), 'image' => 'includes/images/admin/cache.png'),
    array('title' => $main->lang['logout'], 'url' => $main->url(array('module' => $main->module, 'do' => 'logout')), 'image' => 'includes/images/48x48/logout.png'),
);

bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));

function main_account(){
global $main, $navi, $link, $icon_navi;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('scan_dir');
    if(!is_user()) redirect($main->url(array('module' => $main->module, 'do' => 'login')));    
    echo $navi;
    open();
    echo "<table align='center' class='account_main'><tr>\n";
    foreach(icon_navi($icon_navi) as $a=>$v) echo "<td width='120' align='center'><a class='account_ico' href='{$v['url']}' title='{$v['title']}'><img src='{$v['image']}' alt='{$v['title']}' /><br />{$v['title']}</a></td>\n";        
    echo "</tr></table>\n";
    close();
}

function login($msg=""){
global $userconf, $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user()) redirect(MODULE);    
    echo $navi;
    if(!empty($msg)) warning($msg);
    open();
    echo "<h3 class='option'>{$main->lang['account_login']}</h3>\n".
    "<form action='".$main->url(array('module' => $main->module, 'do' => 'sign'))."' method='post'>\n".
    "<table align='center' class='form'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:</td><td class='form_input'>".in_text("user_name", "input_text")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['password']}:</td><td class='form_input'>".in_pass("user_password", "input_text")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['save_authorisation']}:</td><td class='form_input'>".in_chck("save_authorise", "")."</td></tr>\n".
    "<tr><td colspan='2' align='center' class='form_submit'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
    echo ($userconf['registration']!='') ? "<center>[<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'new_password'))."' title='{$main->lang['forgot_your_password']}'>{$main->lang['forgot_your_password']}</a> | <a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'new_user'))."' title='{$main->lang['new_user']}'>{$main->lang['new_user']}</a>]</center><br /><br />\n" : "<center>[<a href='".$main->url(array('module' => $main->module, 'do' => 'new_password'))."' title='{$main->lang['forgot_your_password']}'>{$main->lang['forgot_your_password']}</a>]</center><br /><br />\n";
    close();
}

function sign($redirect=true){
global $session, $main, $ip, $config;
    if(hook_check(__FUNCTION__)) return hook();
    //Проверка наличия данных
    if(!isset($_POST['user_name']) OR !isset($_POST['user_password'])) redirect(MODULE);
    //Инициализация функции фильтрации IP
    main::init_function('check_filter_ip');
    $fr = user_login_check($_POST['user_name'], false);
    if($fr['status']==true) $login = $fr['user'];
    else $msg = $fr['message'];
    $pass = kr_filter($_POST['user_password'], TAGS);
    $_SESSION['save_authorise']= array_key_exists('save_authorise',$_POST)?kr_filter($_POST['save_authorise'], TAGS):'off';
    if(empty($msg)) $msg = (empty($login) OR empty($pass)) ? $main->lang['error_login_pass'] : "";
    if(empty($msg)){
        $pass = pass_crypt($pass);
        $user = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".USERS." WHERE user_activation<>'1' AND user_name='{$login}'"));
        if($user['user_filter_active']==1){ 
          if(!check_current_login($user['user_filter_ip'])) {
             main::init_function('sys_message');
             $main->user['user_gmt']=$user['user_gmt'];
             $text=str_replace(array('%ip%','%time%'),array($ip, kr_date_user('d.m.Y H:i:s')),$main->lang['w_ip_filter']);
             send_message($login,$login,"<b style='color:red'>{$main->lang['warning']}</b>",$text);
             die_work_block_ip(); 
          }
        }
        if($user['user_filter_country']==1 AND $user['user_country']!='default'){
           if(!function_exists('geoip_country_code_by_name')) main::init_function('geoip');
           $gi = geoip_open("includes/GeoIP.dat", GEOIP_STANDARD);
           $country = geoip_country_name_by_addr($gi, $ip);
           geoip_close($gi);
           if($user['user_country']!=$country){
              $main->user['user_gmt']=$user['user_gmt'];
              $message=str_replace(array('%ip%','%time%','%country%'),array($ip, kr_date_user('d.m.Y H:i:s'),$country),$main->lang['w_ip_country']);
              send_mail($user['user_email'], $main->lang['user'], $main->config['sends_mail'], $config['home_title']." System", $main->lang['warning'], 
                 $message, array(), array(), array());
           }
        }
       if($user['user_baned']==0 OR $user['user_baned_time']<kr_time()){
          if($user['user_baned']==1) sql_update(array('user_baned'=>0,'user_baned_time'=>0),USERS, " uid={$user['uid']}");
          if($user['user_password']==$pass){
             if ($_SESSION['save_authorise']=='on') setcookies($login.",".$pass, $main->config['user_cookies']);
             else setcookies($login.",".$pass, $main->config['user_cookies'],1);
             $main->db->sql_query("UPDATE ".USERS." SET user_last_os='".kr_filter($main->agent['os'], TAGS)."', user_last_browser='".kr_filter($main->agent['browser'], TAGS)."', user_last_ip='{$main->ip}', user_last_visit=NOW() WHERE user_name='{$login}'");
             if($main->db->sql_numrows($main->db->sql_query("SELECT uname FROM ".SESSIONS." WHERE uname='{$login}' and  sid='".session_id()."'"))==0) $main->db->sql_query("UPDATE ".SESSIONS." SET uname='{$login}', actives='y' WHERE sid='".session_id()."'");
             $session->register($login);
             if($redirect) redirect(BACK);
          } else {
             $log = "".kr_datecms("Y-m-d H:i:s")." | Incorrect username or password | username::{$_POST['user_name']} | password::{$_POST['user_password']} | {$ip}||\n";
             $msg = $main->lang['error_login_pass2'];
          }
       } else $msg = $main->lang['user_ban'];
    } else  $log = "".kr_datecms("Y-m-d H:i:s")." | Empty username or password | username::{$_POST['user_name']} | password::{$_POST['user_password']} | {$ip}||\n";
    if(isset($log) AND !empty($log) AND $main->config['log_error_user_logined'] == ENABLED) file_write("uploads/logs/logined_logs.log", $log, "a");
    login($msg);
}

function logout(){
global $main, $session;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user()){
        $session->kill_session_cache();
        setcookies("", $main->config['user_cookies'], 1);
        $main->db->sql_query("UPDATE ".SESSIONS." SET uname='{$main->ip}', ip='{$main->ip}', time='".time()."', module='{$main->module}', url='".get_env('REQUEST_URI')."', actives='y' WHERE sid='".session_id()."'");
        redirect(MODULE);
    } else redirect(BACK);
}

function new_user($msg=""){
global $main, $userconf;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user() OR empty($userconf['registration'])) {redirect(MODULE);}
    echo navi(array(), false, false, $main->lang['registration_on_site']);
    //$email = (!empty($_POST['user_email'])) ? kr_filter($_POST['user_email'], TAGS) : "";
    if(!empty($msg)) warning($msg);
    main::add_css2head("
       .pass_lock{display: inline-block;width: auto;padding-left: 20px;height:16px;cursor:pointer;background: url('includes/images/16x16/newuser.png') no-repeat scroll left top transparent;}
       .pass_unlock{background: url('includes/images/16x16/passlost.png') no-repeat scroll left top transparent !important;}
       .email-suggestion {display: none; font-style: italic; font-size: 13px; padding-top: 5px;}
       .email-suggestion a {text-decoration: none; border-bottom: 1px dashed blue;}
       a.close {color: #aaa; border: none; font-style: normal;}
       a.close:hover {color: #000;}
    ");
    main::add2script("includes/javascript/jquery/jquery.mailcheck.js");
    info($main->lang['register_info']);
    ?>
    <script type="text/javascript">
    function checkpassword(pas1, pas2, yes, no){if(pas1==pas2) $$('repass_check').innerHTML = '<span style="color:green">'+yes+'</span>'; else $$('repass_check').innerHTML = '<span style="color:red">'+no+'</span>';}
    function checkuser(obj){haja({animation:false, elm:'login_check', action:'index.php?module=account&do=usercheck'}, {'user':obj.value}, {});}
    </script>
    <?php
    open();
    $div_email="<div class='email-suggestion'>{$main->lang['email_supp']} <a href='#' class='apply' title='{$main->lang['email_change']}'><span>user</span>@<b>domain.com</b></a>? <a href='#' class='close' title='{$main->lang['email_ok']}'>✖</a></div>";
    echo "<form action='".$main->url(array('module' => $main->module, 'do' => 'registration'))."' method='post'>\n".
    in_hide("timezone","0").
    "<table class='form' align='center'>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_name", "input_text", '', false, " onblur=\"checkuser(this);\"")."<div id='login_check'><img src='includes/images/pixel.gif' alt='' /></div></td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['email']}:<span class='star'>*</span></td><td class='form_input'>".in_text("user_email", "input_text")."{$div_email}</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'><div class='pass_lock' title='{$main->lang['change_enter_pass']}' style='' onclick='pass_mode(this);'>{$main->lang['password']}:</div></td><td class='form_input'>".in_pass("user_password", "input_text")."</td></tr>\n".
    "<tr class='row_tr'><td class='form_text'>{$main->lang['repassword']}:</td><td class='form_input'>".in_pass("user_repassword", "input_text", '', " onblur=\"checkpassword(this.value, document.getElementById('user_password').value, '{$main->lang['checked_pass']}', '{$main->lang['nochecked_pass']}');\"")."<div id='repass_check'><img src='includes/images/pixel.gif' alt='' /></div></td></tr>\n".
    captcha().
    "<tr><td colspan='2' align='center' class='form_submit'>".send_button()."</td></tr>\n".
    "</table>\n</form>\n";
    echo "<script type='text/javascript'>
    <!--
    \$(document).ready(function(){
       x = new Date()
       \$('#timezone').val(-x.getTimezoneOffset()/60);
    });
    var tr_repass=\$('#user_repassword').parents('tr:first');
    function pass_mode(obj){
     \$(obj).toggleClass('pass_unlock');
     if(\$(obj).hasClass('pass_unlock')){
       tr_repass.hide();
       \$('#user_password').get(0).type = 'text';
       \$('#user_password').on('change.pass',function(){\$('#user_repassword').val(this.value);});
     } else {
       tr_repass.show();
       \$('#user_password').get(0).type = 'password';
       \$('#user_password').off('.pass');
     }
    }
\$('input[name=user_email]').on('focusout', function(){
    var e = \$(this);
    var s = \$('.email-suggestion');
    \$(this).mailcheck({
        suggested: function(el, suggestion){
             s.find('span').text(suggestion.address);
             s.find('b').text(suggestion.domain);
             s.slideDown('fast');
        },
        empty: function(){
            s.slideUp('fast');
        }
    });
    s.find('a').on('click', function(){
        \$(this).hasClass('apply') && e.val(\$(this).text());
        s.slideUp('fast');
        return false;
    });
});    
    // -->    
    </script>";
    close();
}
function user_registration_system(){
   global $main, $userconf, $patterns;
   if(hook_check(__FUNCTION__)) return hook();
   $activation_code = get_random_string(25);
   unset($_SESSION['validate']);
   $new_user=array(
         'user_id'              => cyr2lat($_POST['user_name']),
         'user_name'            => $_POST['user_name'],
         'user_email'           => $_POST['user_email'],
         'user_password'        => pass_crypt($_POST['user_password']),
         'user_regdate'         => kr_datecms("Y-m-d H:i:s"),
         'user_last_visit'      => kr_datecms("Y-m-d H:i:s"),
         'user_group'           => $userconf['default_group'],
         'user_activation'      => ($userconf['registration']=="email" OR $userconf['registration']=="admin")?1:0,
         'user_activation_code' => $activation_code,
         'user_password_update' => time(),
         'user_gmt'             => isset($_POST['timezone'])?(is_numeric($_POST['timezone'])?intval($_POST['timezone']):0):0
      );
   sql_insert($new_user, USERS);
   $uid=$main->db->sql_nextid();
   unset($_SESSION['cache_session_user']);
   $message = preg_replace(array("#{USER}#", "#{SITE}#", "#{PASSWORD}#"), array($_POST['user_name'], "<a href='{$main->config['http_home_url']}'>{$main->config['home_title']}</a>", $_POST['user_password']), $patterns['message_registration']);
   $caption_mail=$main->lang['registration_on_site'].' @ '.$main->config['site_name_for_mail'];
   if($userconf['registration']=="email"){
      $message = preg_replace(array("#{USER}#", "#{SITE}#", "#{CODE}#", "#{EMAIL}#"), array($_POST['user_name'], "<a href='{$main->config['http_home_url']}'>{$main->config['home_title']}</a>", "<a href='{$main->config['http_home_url']}index.php?module=account&do=activation&code={$activation_code}'>{$main->lang['activation_user']}</a>", $_POST['user_email']), $patterns['activation_user'])."<br>=======================================================<br>".$message;
      $caption_mail=$main->lang['activation_user']." + ".$caption_mail;
   }
   send_mail($_POST['user_email'], $_POST['user_name'], $main->config['sends_mail'], "noreply", $caption_mail, $message);
   if($userconf['registration']=='all'){sign(false); meta_refresh(6, $main->url(array('module' => $main->module)), $main->lang['message_reg_all']);}
   elseif($userconf['registration']=='email') meta_refresh(6, $main->url(array('module' => $main->module)), $main->lang['message_reg_email']);
   elseif($userconf['registration']=='admin') meta_refresh(6, $main->url(array('module' => $main->module)), $main->lang['message_reg_admin']);
   else meta_refresh(6, $main->url(array('module' => $main->module)), $main->lang['message_reg_admin']);
   return $uid;
}

function registration(){
global $userconf, $patterns, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($userconf['registration'])) {redirect(MODULE);}
    if(!isset($_POST['user_name']) OR !isset($_POST['user_email']) OR !isset($_POST['user_password']) OR !isset($_POST['user_repassword'])) redirect(MODULE);
    $_POST['user_email'] = mb_strtolower(preg_replace('/([\'"\+\s])/', '', $_POST['user_email']));
    $_POST['user_name'] = preg_replace('/([\'"\+])/', '', $_POST['user_name']);
    filter_arr(array('user_name', 'user_email', 'user_password', 'user_repassword'), POST, TAGS);
    $msg = error_empty(array('user_name'), array('error_user_name')).check_mail($_POST['user_email']).(!isset($_POST['create_user'])?check_captcha():"");
    if(cyr2lat($_POST['user_name'])=="") $msg .= $main->lang['error_uname_cyr2lat'];
    if(array_key_exists('user_name_length',$userconf)&&($userconf['user_name_length']!="")&&(mb_strlen($_POST['user_name'])<intval($userconf['user_name_length']))) $msg .= str_replace('{COUNT}', $userconf['user_name_length'], $main->lang['error_length_uname']);
    if(empty($_POST['user_password'])) $_POST['user_password'] = get_random_string($userconf['password_length']);
    elseif($_POST['user_password']!=$_POST['user_repassword']) $msg .= $main->lang['error_repass'];
    $msg .= user_password_check($_POST['user_password']);
        if(isset($userconf['email_deny'])&&!empty($userconf['email_deny'])){
            // check e-mail denied
            $a=explode(',', mb_strtolower($userconf['email_deny']));
            foreach ($a as $value) {
                if(strpos($_POST['user_email'],"@{$value}")!== false) {
                    $msg .=str_replace('{DOMAIN}', $value, $main->lang['error_email_denied']);
                    break;
                }
            }
        }
    if(empty($msg)){
        $namesearch = mb_strtolower($_POST['user_email']);
        $result = $main->db->sql_query("SELECT uid FROM ".USERS." WHERE user_name='{$_POST['user_name']}' OR user_id='".cyr2lat($_POST['user_name'])."' OR user_email='{$namesearch}'");
        if($main->db->sql_numrows($result)==0){
            if(isset($_SESSION['validate'])){
                user_registration_system();
            } else {
                echo navi(array(), false, false, $main->lang['registration_on_site']);
                open();
                $_SESSION['validate'] = true;
                echo "\n<form id='reguser' action='".$main->url(array('module' => $main->module, 'do' => 'registration'))."' method='post'>\n".
                in_hide("create_user", "true").
                in_hide("user_name", $_POST['user_name']).
                in_hide("user_email", $_POST['user_email']).
                in_hide("user_password", $_POST['user_password']).
                in_hide("user_repassword", $_POST['user_password']).
                in_hide("timezone",isset($_POST['timezone'])?$_POST['timezone']:"").
                "<h3 class='option'>{$main->lang['reg_user_data']}</h3>\n".
                "<table width='200' align='center' class='form'>\n".
                "<tr><td class='form_text'>{$main->lang['login']}:</td><td class='form_input'>{$_POST['user_name']}</td></tr>\n".
                "<tr><td class='form_text'>{$main->lang['email']}:</td><td class='form_input'>{$_POST['user_email']}</td></tr>\n".
                "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
                "</table>".
                "</form>\n";
                close();
                ?>
                <script type="text/javascript">
                //<![CDATA[
                 var sub_text=$('#reguser').find('.submit').val();
                 var tmr=5;
                 function run_timer(){
                    $('#reguser').find('.submit').val(sub_text+'('+tmr+')');
                    tmr--;
                    if(tmr<0) $('#reguser').find('.submit').click();
                    else setTimeout('run_timer();',1000);
                 }
                 setTimeout('run_timer();',1000);
                //]]>
                </script>
                <?php
                
            }
        } else new_user("<li>{$main->lang['error_user_mail']}</li>");
    } else new_user($msg);
}

function user_birthday($birthday){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $date = explode("-", $birthday);    
    $days = $month = $years = array();
    for($i=0;$i<=31;$i++) $days[($i<10)?"0".$i:$i] = ($i<10)?"0".$i:$i;
    $return = "{$main->lang['day']}: ".in_sels('days', $days, 'select chzn-search-hide', $date[2], " style='width: 45px;'");
    for($i=0;$i<=12;$i++) $month[$i] = ($i<10)?"0".$i:$i;
    $return .= " {$main->lang['month']}: ".in_sels('months', $month, 'select chzn-search-hide', $date[1], " style='width: 45px;'");
    for($i=date("Y")-100;$i<=date("Y");$i++) $years[$i] = $i;
    $return .= " {$main->lang['year']}: ".in_sels('years', array('00' => '0000')+$years, 'select chzn-search-hide', $date[0], " style='width: 60px;'");
    return $return;
}

function user_gmt($gmt){
    if(hook_check(__FUNCTION__)) return hook();
    $arr = array(
        '(GMT -12:00) Enevetok, Kvadzhaleyn',
        '(GMT -11:00) Midway Islands, Samoa',
        '(GMT -10:00) Hawaii',
        '(GMT -9:00) Alaska',
        '(GMT -8:00) Pacific Time (U.S. &amp; Canada), Tijuana',
        '(GMT -7:00) Mountain Time (U.S. &amp; Canada), Arizona',
        '(GMT -6:00) Central Time (U.S. &amp; Canada), Mexico City',
        '(GMT -5:00) Eastern Time (U.S. &amp; Canada), Bogota, Lima, Quito',
        '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz, Santiago',
        '(GMT -3:00) Brazil, Buenos Aires, Georgetown, Greenland',
        '(GMT -2:00) Mid-Atlantic',
        '(GMT -1:00) Azores, on Green Island Cape',
        '(GMT) Casablanca, Dublin, Edinburgh, Lisbon, London, Monrovia',
        '(GMT +1:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome',
        '(GMT +2:00) Athens, Bucharest, Kiev, Chisinau, Minsk, Riga, Helsinki',
        '(GMT +3:00) Moscow, Baghdad, Riyadh, Nairobi',
        '(GMT +4:00) Abu Dhabi, Baku, Muscat, Tbilisi',
        '(GMT +5:00) Islamabad, Karachi, Tashkent',
        '(GMT +6:00) Almaty, Colombo, Dhaka, Ekaterinburg',
        '(GMT +7:00) Bangkok, Hanoi, Jakarta, Novosibirsk, Omsk',
        '(GMT +8:00) Beijing, Hong Kong, Krasnoyarsk, Perth, Singapore, Taipei',
        '(GMT +9:00) Irkutsk, Osaka, Sapporo, Seoul, Tokyo',
        '(GMT +10:00) Canberra, Melbourne, Guam, Sydney, Yakutsk',
        '(GMT +11:00) New Caledonia, Solomon Islands, Vladivostok',
        '(GMT +12:00) Auckland, Fiji, Kamchatka, Magadan, Wellington',
        '(GMT +13:00) Anadyr',
        '(GMT +14:00) Kiritimati (Christmas Island)'
    );
    $y = 0;
    $return = "<select name='user_gmt' style='width: 400px' class='select2 chzn-search-hide'>";
    for($i=-12;$i<=14;$i++){
        $return .= "<option value='{$i}'".(($gmt==$i) ? " selected='selected'" : "")." style='font-size: 0.8em'>{$arr[$y]}</option>";
        $y++;
    }
    return  $return."</select>";
}

function controls_account($msg=""){
global $userconf, $main, $tpl_create, $modules;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function(array('select_avatars','modify_filter_ip'));
    main_account();
    main::add_css2head(".centered * {vertical-align: middle;}");
    bcrumb::add($main->lang['user_controls']);
    main::add2script("includes/javascript/kr_tab.js");
    
    //unset($_SESSION['cache_session_user']);
    if(!empty($msg)) warning($msg);
    $_POST['day'] = !isset($_POST['day']) ? '00' : $_POST['day'];
    $_POST['month'] = !isset($_POST['month']) ? '00' : $_POST['month'];
    $_POST['year'] = !isset($_POST['year']) ? '0000' : $_POST['year'];
    $_POST['user_gmt'] = !isset($_POST['user_gmt']) ? '0' : $_POST['user_gmt'];
    $gender = in_radio('user_gender', 0, $main->lang['noinfo'], 'id0', ($main->user['user_gender']==0)?true:false)." ".in_radio('user_gender', 1, $main->lang['male'], 'id1', ($main->user['user_gender']==1)?true:false)." ".in_radio('user_gender', 2, $main->lang['woman'], 'id2', ($main->user['user_gender']==2)?true:false);
    echo "<form id='form_control' enctype='multipart/form-data' method='post' action='".$main->url(array('module' => $main->module, 'do' => 'save_controls'))."'><div class='TabMenu'>
        <div class='tabContent'>
            ".open(true)."
            <div class='tabTitle'>{$main->lang['account_general']}</div>".
            "<table width='100%' class='form' cellspacing='1'>".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_name']}:</td><td class='form_input_account'><a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($main->user['user_id'], $main->user['uid'])))."' title='{$main->lang['user_profile']}'>{$main->user['user_name']}</a></td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['first_name']}:</td><td class='form_input_account'>".in_text("user_first_name", "input_text2", $main->user['user_first_name'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['last_name']}:</td><td class='form_input_account'>".in_text("user_last_name", "input_text2", $main->user['user_last_name'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_ip']}:</td><td class='form_input_account'><a href='{$main->config['whois']}{$main->ip}'>{$main->ip}</a></td></tr>\n".
            (($main->config['geoip']==ENABLED) ? "<tr><td class='form_text'>{$main->lang['country']}:</td><td class='form_input_account'>".get_flag($main->user['user_country'])."</td></tr>\n" : "").
            "<tr class='row_tr'><td class='form_text'>{$main->lang['reg_date']}:</td><td class='form_input_account'>".format_date($main->user['user_regdate'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['birthday']}:</td><td class='form_input_account' id='datacase'>".user_birthday($main->user['user_birthday'])."&nbsp;</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['gender']}:</td><td class='form_input_account'>{$gender}</td></tr>\n".
             "<tr class='row_tr'><td class='form_text'>{$main->lang['occupation']}:</td><td class='form_input_account'>".in_text("user_occupation", "input_text_accaunt", $main->user['user_occupation'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['interests']}:</td><td class='form_input_account'>".in_text("user_interests", "input_text_accaunt", $main->user['user_interests'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['locality']}:</td><td class='form_input_account'>".in_text("user_locality", "input_text_accaunt", $main->user['user_locality']).in_hide('user_locality_hk', $main->user['user_locality'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['signature']}:</td><td class='form_input_account'>".in_area("user_signature", 'textarea', 4, bb($main->user['user_signature'], DECODE))."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['language_site']}:</td><td class='form_input_account'>".get_lang_file(!empty($main->user['user_language'])?$main->user['user_language']:$main->language, false)."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['template_site']}:</td><td class='form_input_account'>".select_template($main->user['user_template'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>GMT:</td><td class='form_input_account' id='time_zone'>".user_gmt($main->user['user_gmt'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['viewemail']}:</td><td class='form_input_account'>".in_chck('user_viewemail', 'checkbox', ($main->user['user_viewemail']==1)?ENABLED:'')."</td></tr>\n".
            "<tr class='tabSend'><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table>
            ".close(true)."
        </div>
        <div class='tabContent'>
            ".open(true)."
            <div class='tabTitle'>{$main->lang['account_contact']}</div>".
            "<table width='100%' class='form' cellspacing='1'>".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_mail']}:<span class='star'>*</span></td><td class='form_input_account'>".in_text("user_email", "input_text_accaunt", $main->user['user_email'])."</td></tr>\n".    
            "<tr class='row_tr'><td class='form_text'>{$main->lang['icq']}:</td><td class='form_input_account'>".in_text("user_icq", "input_text_accaunt", $main->user['user_icq'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['aim']}:</td><td class='form_input_account'>".in_text("user_aim", "input_text_accaunt", $main->user['user_aim'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['yim']}:</td><td class='form_input_account'>".in_text("user_yim", "input_text_accaunt", $main->user['user_yim'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['msn']}:</td><td class='form_input_account'>".in_text("user_msnm", "input_text_accaunt", $main->user['user_msnm'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['skype']}:</td><td class='form_input_account'>".in_text("user_skype", "input_text_accaunt", $main->user['user_skype'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['googletalk']}:</td><td class='form_input_account'>".in_text("user_gtalk", "input_text_accaunt", $main->user['user_gtalk'])."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['home_page']}:</td><td class='form_input_account'>".in_text("user_website", "input_text_accaunt", $main->user['user_website'])."</td></tr>\n".
            "<tr class='tabSend'><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table>
            ".close(true)."
        </div>
        <div class='tabContent'>
            ".open(true)."
            <div class='tabTitle'>{$main->lang['account_avatar']}</div>".
            "<table width='100%' class='form' cellspacing='1'>".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_avatar']}:</td><td class='form_input_account' align='center'><input type='hidden' id='id_set_avatar' name='set_avatar' value='' /><img id='avatarset' class='img_avatar' src='{$userconf['directory_avatar']}{$main->user['user_avatar']}' alt='{$main->lang['you_avatar']}' title='{$main->lang['you_avatar']}' /></td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['case_avatar']}:</td><td class='form_input_account' nowrap='nowrap'>".select_avatars()." <input class='case_submit' type='submit' onclick=\"newWindow = window_open('http://".get_host_name()."/index.php?module={$main->module}&amp;do=case_avatar&amp;id='+document.getElementById('cat').value+'', '', 'toolbar=0,width=720,height=600,resizable=0,menubar=0,scrollbars=1,status=0'); return false;\" value='{$main->lang['case']}' /></td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['load_avatar']}:</td><td class='form_input_account'><input type='file' name='userfile' value='' size='36' /></td></tr>\n".
            "<tr class='tabSend'><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table>
            ".close(true)."
        </div>
        <div class='tabContent'>
            ".open(true)."
            <div class='tabTitle'>{$main->lang['new_user_password']}</div>".
            "<table width='100%' class='form' cellspacing='1'>".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_password']}:</td><td class='form_input_account'>".in_pass("user_password", "input_password_accaunt").in_hide('user_password_hk', '')."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_newpassword']}:</td><td class='form_input_account'>".in_pass("user_newpassword", "input_password_accaunt").in_hide('user_newpassword_hk', '')."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_renewpassword']}:</td><td class='form_input_account'>".in_pass("user_renewpassword", "input_password_accaunt").in_hide('user_renewpassword_hk', '')."</td></tr>\n".
            "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table>
            ".close(true)."
        </div>
        <div class='tabContent'>
            ".open(true)."
            <div class='tabTitle'>{$main->lang['security']}</div>".
            "<table width='100%' class='form' cellspacing='1'>".
            "<tr><td colspan='2' align='center'><b>{$main->lang['user_filter_ip']}:</b></td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['user_filter_active']}:</td><td class='form_input_account'>".in_chck('user_filter_active', 'checkbox', ($main->user['user_filter_active']==1)?ENABLED:'')."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['user_filter_country']}:</td><td class='form_input_account'>".in_chck('user_filter_country', 'checkbox', ($main->user['user_filter_country']==1)?ENABLED:'')."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['user_filter_session']}:</td><td class='form_input_account'>".in_chck('user_filter_session', 'checkbox', ($main->user['user_filter_session']==1)?ENABLED:'')."</td></tr>\n".
            "<tr><td class='form_text'>{$main->lang['user_filter_list']}:</td><td class='form_input_account'>".
             gen_html_editor_filter_ip($main->user['user_filter_ip'],'input_text_account')."</td></tr>\n".
            "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table>
            ".close(true)."
        </div>".(
            (isset($modules['forum']) AND $modules['forum']['active']==1) ? "<div class='tabContent'>
                ".open(true)."
                <div class='tabTitle'>{$main->lang['forum']}</div>".
                "<table width='100%' class='form' cellspacing='1'>".
                "<tr class='row_tr'><td class='form_text'>{$main->lang['notify_forum_topic']}:</td><td class='form_input_account'>".in_chck('user_forum_mail', 'checkbox', ($main->user['user_forum_mail']==1)?true:false)."</td></tr>\n".
                "<tr class='row_tr'><td class='form_text'>{$main->lang['control_subscribe']}:</td><td class='form_input_account centered'>".(in_radio('cmd_forum_mail',0, $main->lang['default_subscribe'],"r0",true)."<br/>".in_radio('cmd_forum_mail',1, $main->lang['all_subscribe'],"r1")."<br/>".in_radio('cmd_forum_mail',2, $main->lang['all_not_subscribe'],"r2"))."</td></tr>\n".
                "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
                "</table>
                ".close(true)."
            </div>" : ''
        )."
    </div></form>
    <br /><script type='text/javascript'>$.krReady(function(){\$('.TabMenu').tabs({classTab:'settingsTab'});})</script>";
    gen_jscript_editor_filter_ip();
}

function save_controls_account(){
global $userconf, $main, $code2languages;
    if(hook_check(__FUNCTION__)) return hook();
    unset($_SESSION['cache_session_user']);
    main::init_class('uploader');
    if(!is_user()) redirect($main->url(array('module' => $main->module, 'do' => 'login')));
    filter_arr(array('cmd_forum_mail', 'user_forum_mail', 'user_filter_active', 'user_filter_country', 'user_filter_session', 'user_first_name', 'user_last_name', 'user_gtalk', 'user_gmt', 'year', 'month', 'day', 'set_avatar', 'user_email', 'user_viewemail', 'user_gender', 'user_skype', 'user_icq', 'user_aim', 'user_yim', 'user_msnm', 'user_website', 'user_occupation', 'user_interests', 'user_locality', 'user_signature', 'template', 'language', 'user_password', 'user_newpassword', 'user_renewpassword'), POST, TAGS);
    if(!empty($_POST['user_password']) AND $main->user['user_password']==pass_crypt($_POST['user_password'])){
        if($_POST['user_newpassword']==$_POST['user_renewpassword'] AND !empty($_POST['user_renewpassword'])) $user_password = pass_crypt($_POST['user_newpassword']);
        else {controls_account($main->lang['error_new_password']); return false;}
    } elseif($main->user['user_password']!=pass_crypt($_POST['user_password']) AND !empty($_POST['user_password'])){controls_account($main->lang['error_this_password']); return false;}
    else $user_password = $main->user['user_password'];
    $msg = check_mail($_POST['user_email']);
    if(!empty($_POST['user_password'])) $msg.= user_password_check($_POST['user_newpassword']);
    $user_birthday = isset($_POST['years']) ? "{$_POST['years']}-{$_POST['months']}-{$_POST['days']}" : '';
    $msg = date('Y-n-d', strtotime($user_birthday)) != $user_birthday ? 'Invalid birthday' : '';
    if(empty($msg)){
        //Загрузка аватары
        $user_avatar = $main->user['user_avatar'];
        if(isset($_FILES['userfile']) AND !empty($_FILES['userfile']['name'])){
            $atrib = array(
                'dir'       => $userconf['directory_avatar'],
                'file'      => $_FILES['userfile'],
                'size'      => $userconf['size_avatar'],
                'type'      => explode(',', $userconf['type_avatar']),
                'width'     => $userconf['width_avatar'],
                'height'    => $userconf['height_avatar'],
                'name'      => $main->user['user_id'],
                'overwrite' => true
            );
            $avatar = new upload($atrib);
            if($avatar->error){
                controls_account($avatar->get_error_msg());
                return false;
            } elseif($avatar->is_upload) $user_avatar = $avatar->file;
        } else $user_avatar = (!empty($_POST['set_avatar'])) ? $_POST['set_avatar'] : $user_avatar;
        
        //Сохранение данных
        $ipf=$_POST['ip_filter'];
        sql_update(array(
            'user_gender'       => isset($_POST['user_gender'])?($_POST['user_gender']!=""?$_POST['user_gender']:"0"):"0",
            'user_email'        => mb_strtolower($_POST['user_email']),
            'user_skype'        => isset($_POST['user_skype'])?$_POST['user_skype']:"",
            'user_gtalk'        => isset($_POST['user_gtalk'])?$_POST['user_gtalk']:"",
            'user_icq'          => isset($_POST['user_icq'])?$_POST['user_icq']:"",
            'user_aim'          => isset($_POST['user_aim'])?$_POST['user_aim']:"",
            'user_yim'          => isset($_POST['user_yim'])?$_POST['user_yim']:"",
            'user_msnm'         => isset($_POST['user_msnm'])?$_POST['user_msnm']:"",
            'user_website'      => empty($_POST['user_website']) ? "http://" : $_POST['user_website'],
            'user_occupation'   => isset($_POST['user_occupation'])?$_POST['user_occupation']:"",
            'user_interests'    => isset($_POST['user_interests'])?$_POST['user_interests']:"",
            'user_locality'     => isset($_POST['user_locality'])?$_POST['user_locality']:"",
            'user_signature'    => isset($_POST['user_signature'])?bb($_POST['user_signature']):"",
            'user_viewemail'    => (isset($_POST['user_viewemail']) AND $_POST['user_viewemail']=='on') ? 1 : 0,
            'user_avatar'       => $user_avatar,
            'user_template'     => $_POST['template'],
            'user_language'     => isset($_POST['language'])?$_POST['language']:"",
            'user_password'     => $user_password,
            'user_birthday'     => $user_birthday,
            'user_gmt'          => empty($_POST['user_gmt']) ? '0' : $_POST['user_gmt'],
            'user_forum_mail'   => (isset($_POST['user_forum_mail']) AND $_POST['user_forum_mail']=='on') ? 1 : 0,
            'user_last_name'       => $_POST['user_last_name'],
            'user_first_name'      => $_POST['user_first_name'],
            'user_filter_ip'       => implode(',',$ipf),
            'user_filter_active'   => (isset($_POST['user_filter_active']) AND $_POST['user_filter_active']=='on') ? 1 : 0,
            'user_filter_country'  => (isset($_POST['user_filter_country']) AND $_POST['user_filter_country']=='on') ? 1 : 0,
            'user_filter_session'  => (isset($_POST['user_filter_session']) AND $_POST['user_filter_session']=='on') ? 1 : 0,
        ), USERS, "user_name='{$main->user['user_name']}'");
        
        if(isset($_POST['language']) AND isset($code2languages[$_POST['language']])) setcookies($code2languages[$_POST['language']], 'lang');
        if(isset($_POST['cmd_forum_mail'])&&intval($_POST['cmd_forum_mail'])>0){
           if(intval($_POST['cmd_forum_mail'])==1){
              $main->db->sql_query("delete from ".FORUM_SUBSCRIBE." where uid=".$main->user['uid']);
              $main->db->sql_query("insert into ".FORUM_SUBSCRIBE." (uid,topic_id)
                 select t.topic_poster,t.topic_id from ".TOPICS." t where t.topic_poster={$main->user['uid']}");
           } elseif(intval($_POST['cmd_forum_mail'])==2){
              $main->db->sql_query("delete from ".FORUM_SUBSCRIBE." where uid=".$main->user['uid']);
           }
        }
        $hash = !empty($_POST['hash']) ? '#tab='.$_POST['hash'].':' : '';
        redirect($main->url(array('module' => $main->module, 'do' => 'controls')).$hash);
    } else controls_account($msg);
    return true;
}

function case_avatar(){
global $parametr_design, $lang, $userconf, $main;
    if(hook_check(__FUNCTION__)) return hook();
    add_meta_value($lang['case_avatar2']);
    $parametr_design = false;
    if(!preg_match('/([a-zA-Z0-9_\-])/s', $_GET['id'])) redirect($main->url(array()));
    open();
    echo "<table class='table' cellspacing='1' align='center'>\n";
    $directory = $userconf['directory_avatar'].$_GET['id']."/";
    $i = 0;
    $dir = opendir($directory);
    while(($file = readdir($dir))){
        if ($file!=".." AND $file!="." AND $file!="index.html" AND $file!=".htaccess" AND is_file("{$directory}{$file}")){
            if($i==0) {echo  "<tr class='bgcolor5'>\n<td align='center' valign='top'><img onclick=\"set_avatar('{$file}', '{$userconf['directory_avatar']}');\" src='{$directory}{$file}' class='case_avatar' alt='{$lang['case_avatar']}' /></td>"; $i++;
            } elseif($i<5){echo  "<td align='center' valign='top'><img onclick=\"set_avatar('{$file}', '{$userconf['directory_avatar']}');\" width='110' height='110' src='{$directory}{$file}' class='case_avatar' alt='{$lang['case_avatar']}' /></td>\n"; $i++;
            } elseif($i==5){echo  "<td align='center' valign='top'><img onclick=\"set_avatar('{$file}', '{$userconf['directory_avatar']}');\" width='110' height='110' src='{$directory}{$file}' class='case_avatar' alt='{$lang['case_avatar']}' /></td>\n</tr>\n"; $i=0;}
        }
    }
    closedir($dir);
    echo "<tr>\n<td align='center' colspan='6' class='form_submit'><input type='submit' onclick='window.close();' value='{$lang['close']}' /></td>\n</tr>\n</table>";
    close();
}

function activation(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    echo navi(array(), false, false, $main->lang['activation_user']);
    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE user_activation_code='{$_GET['code']}'");
    if($main->db->sql_numrows($result)>0){
        meta_refresh(5, $main->url(array('module' => $main->module)), $main->lang['your_user_activeation']);
        sql_update(array('user_activation' => '0'), USERS, "user_activation_code='{$_GET['code']}'");
    } else warning($main->lang['error_activation_user']);
}
 
function check_active_module($mname){
global $modules;
   if(hook_check(__FUNCTION__)) return hook();
   return (isset($modules[$mname])&&($modules[$mname]['active']==1));
}

function get_statistic_info($module_view, $user, $check=true){
   global $modules,$main;
   if(hook_check(__FUNCTION__)) return hook();
   $ret="";$mview=$module_view;
   $titles=array(
      'news'=>'user_list_news',
      'account'=>'user_list_comments',
      'forum'=>'user_list_post',
      'forumq'=>'user_list_gratitude',
      'files'=>'user_list_files',
      'pages'=>'user_list_pages',
   );
   $modulen=array('news'=>'news','account'=>'account','forum'=>'forum','forumq'=>'forum','files'=>'files','pages'=>'pages');
   if(check_active_module($modulen[$mview]) OR !$check){
      switch ($module_view){
         case "news":
            $main->db->sql_query("select count(n.id) as count_news from ".NEWS." AS n where  n.status='1' AND n.author like '{$user}' ");
            list($count)=$main->db->sql_fetchrow();
            $ret="<td>{$main->lang[$titles[$mview]]}:</td><td><a href='".($main->url(array('module' => 'news', 'do' => 'userinfo','user' => urlencode($user))))."'>{$count}</a></td>";
            break;
         case "account":
            $main->db->sql_query("SELECT count(*) FROM ".COMMENTS." where name like '{$user}'");
            list($count)=$main->db->sql_fetchrow();
            $ret="<td>{$main->lang[$titles[$mview]]}:</td><td><a href='".($main->url(array('module' => $main->module, 'do' => 'userinfo','user' => urlencode($user))))."'>{$count}</a></td>";
            break;
         case "forum":
            $main->db->sql_query("SELECT count(*) FROM ".POSTS." AS p where p.poster_name='{$user}' ");
            list($count)=$main->db->sql_fetchrow();
            $ret="<td>{$main->lang[$titles[$mview]]}:</td><td><a href='".($main->url(array('module' => 'forum', 'do' => 'userinfo','op'=>'post','user' => urlencode($user))))."'>{$count}</a></td>";
            break;
         case "forumq":
            $main->db->sql_query("SELECT count(*) FROM ".POSTS." AS p where p.poster_name='{$user}'  and (not p.post_tnx is null) ");
            list($count)=$main->db->sql_fetchrow();
            $ret="<td>{$main->lang[$titles[$mview]]}:</td><td><a href='".($main->url(array('module' => 'forum', 'do' => 'userinfo','op'=>'gratitude','user' => urlencode($user))))."'>{$count}</a></td>";
            break;
         case "files":
            $main->db->sql_query("select count(n.id) as count_news from ".FILES." AS n where n.author like '{$user}' ");
            list($count)=$main->db->sql_fetchrow();
            $ret="<td>{$main->lang[$titles[$mview]]}:</td><td><a href='".($main->url(array('module' => 'files', 'do' => 'userinfo','user' => urlencode($user))))."'>{$count}</a></td>";
            break;
         case "pages":
            $main->db->sql_query("select count(n.id) as count_news from ".PAGES." AS n where n.author like '{$user}' ");
            list($count)=$main->db->sql_fetchrow();
            $ret="<td>{$main->lang[$titles[$mview]]}:</td><td><a href='".($main->url(array('module' => 'pages', 'do' => 'userinfo','user' => urlencode($user))))."'>{$count}</a></td>";
            break;
      }
   } else {
      $ret=!check_active_module($modulen[$mview])?"<td>{$main->lang[$titles[$mview]]}:</td><td>{$main->lang['module_off']}</td>":"";
   }
   return $ret;
}

function information($msg=''){
   global $main, $img, $userconf, $tpl_create, $config, $template;
   if(hook_check(__FUNCTION__)) return hook();
   //Подключаем модуль комментариев
   main::init_function('comments', 'get_zodiak', 'get_domain');
   main::init_function('rating');
   if(isset($_POST['id'])) add_comment('', $userconf['comments_sort'], $userconf['guests_comments'], 'user');
   else {
      $user=$_GET['id'];
      if(!is_guest_name($user)){
         $sql_extra = "(u.uid='".intval($user)."' OR u.user_id='{$user}') AND u.user_id<>'guest'";
         $result = $main->db->sql_query("SELECT u.*, g.id, g.title, g.color,r.r_up,r.r_down,r.users 
            FROM ".USERS." AS u LEFT JOIN ".GROUPS." AS g ON (g.id=u.user_group) LEFT JOIN ".RATINGS." AS r ON (r.module='users' and r.idm=u.uid)
            WHERE {$sql_extra} LIMIT 1");
         if($main->db->sql_numrows($result)>0){
            $row = $main->db->sql_fetchrow($result);

            if(isset($_POST['show']) AND $_POST['show']=='json'){
               $row['json_tpl'] = TEMPLATE_PATH.$main->tpl.'/javascript/userinfo.html';
               $row['user_regdate'] = user_format_date($row['user_regdate']);
               $row['user_last_visit'] = user_format_date($row['user_last_visit']);
               $row['user_group'] = "<font color='#{$row['color']}'>{$row['title']}</font>";
               $row['user_website'] = (!empty($row['user_website']) AND $row['user_website']!='http://') ? "<a href='engine.php?do=redirect&amp;url={$row['user_website']}' target='_BLANK'>".get_domain($row['user_website'])."</a>" : $main->lang['noinfo'];
               $row['mail_send_url'] = $main->url(array('module' => $main->module, 'do' => 'mail', 'id' => urlencode($row['user_name'])));
               $row['pm_send_url'] = $main->url(array('module' => 'account', 'do' => 'pm', 'op' => 'new', 'id' => $row['uid']));
               $row['lang'] = array(
                  'user'          => $main->lang['user'],
                  'send_email'    => $main->lang['send_email'],
                  'more'          => $main->lang['more'],
                  'group'         => $main->lang['group'],
                  'reg_date'      => $main->lang['reg_date'],
                  'last_visit'    => $main->lang['last_visit'],
                  'home_page'     => $main->lang['home_page'],
               );
               $row['buttons'] = array(
                  array('name' => $main->lang['send_email'], 'onclick' => 'location.href=data.mail_send_url;'),
                  array('name' => $main->lang['more'], 'onclick' => 'location.href="'.$main->url(array('module' => $main->module, 'do' => 'user', 'id' => $row['user_id'])).'";'),
               );
               echo json_encode($row);
               kr_exit();
            } else {
               $zodiak = get_zodiak($row['user_birthday']);
               echo navi(array(), false, false, $main->lang['userinfo']." {$row['user_name']}");
               open();
               $hide_pm="display:none;";
               $pub = array(
                  '_user_country'=>get_flag($row['user_country']).$row['user_name'],
                  '_user_avatar'=>"uploads/avatars/{$row['user_avatar']}",
                  '_user_viewemail'=>(($row['user_viewemail']==1 OR is_support()) ? "<a target='_BLANK' href='mailto:{$row['user_email']}'>{$row['user_email']}</a>" : $main->lang['closed']),
                  '_user_icq'=>(empty($row['user_icq']) ? $main->lang['noinfo'] : "<a target='_BLANK' href='http://www.icq.com/people/about_me.php?uin={$row['user_icq']}'>{$row['user_icq']}</a>"),
                  '_user_skype'=>(empty($row['user_skype']) ? $main->lang['noinfo'] : "<a target='_BLANK' href='skype:{$row['user_skype']}?call'>{$row['user_skype']}</a>"),
                  '_user_gtalk'=>(empty($row['user_gtalk']) ? $main->lang['noinfo'] : $row['user_gtalk']),
                  '_user_aim'=>(empty($row['user_aim']) ? $main->lang['noinfo'] : $row['user_aim']),
                  '_user_yim'=>(empty($row['user_yim']) ? $main->lang['noinfo'] : $row['user_yim']),
                  '_user_msnm'=>(empty($row['user_msnm']) ? $main->lang['noinfo'] : $row['user_msnm']),
                  '_user_last_ip'=>(is_support())?"<div align='left'><img src='includes/images/16x16/ip.png' alt='IP' align='left' style='margin-right: 3px;' /> ".($row['user_last_ip']=='0.0.0.0' ? $main->lang['noinfo'] : "<a href='{$main->config['whois']}{$row['user_last_ip']}'>{$row['user_last_ip']}</a>")."</div>":"",
                  '_user_birthday'=>($row['user_birthday']=="0000-00-00")?$main->lang['no']:format_date_orig($row['user_birthday']),
                  '_user_group'=>($row['user_group']!=0)?"<tr><td>{$main->lang['group']}:</td><td><font color='#{$row['color']}'>{$row['title']}</font></td></tr>":"",
                  '_age'=>($row['user_birthday']!='0000-00-00')?get_age($row['user_birthday']):$main->lang['noinfo'],
                  '_zodiak'=>($row['user_birthday']!='0000-00-00')?$zodiak[1]:$main->lang['noinfo'],
                  '_gender'=>($row['user_gender']==1)?$main->lang['male']:(($row['user_gender']==2)?$main->lang['woman']:$main->lang['noinfo']),
                  '_reg_date'=>format_date($row['user_regdate']),
                  '_user_last_visit'=>format_date($row['user_last_visit']),
                  '_info_news'=>get_statistic_info("news",$row['user_name']),
                  '_info_account'=>get_statistic_info("account",$row['user_name']),
                  '_info_forum'=>get_statistic_info("forum",$row['user_name']),
                  '_info_forumq'=>get_statistic_info("forumq",$row['user_name']),
                  '_info_files'=>get_statistic_info("files",$row['user_name']),
                  '_info_pages'=>get_statistic_info("pages",$row['user_name']),
                  '_user_website'=>(!empty($row['user_website']) AND $row['user_website']!='http://') ? "<a href='engine.php?do=redirect&url={$row['user_website']}' target='_BLANK'>".get_domain($row['user_website'])."</a>" : $main->lang['noinfo'],
                  '_user_occupation'=>!empty($row['user_occupation'])?$row['user_occupation']:$main->lang['noinfo'],
                  '_user_interests'=>!empty($row['user_interests'])?$row['user_interests']:$main->lang['noinfo'],
                  '_user_locality'=>!empty($row['user_locality'])?$row['user_locality']:$main->lang['noinfo'],
                  '_user_signature'=>parse_bb($row['user_signature']),
                  '_link_mail'=>$main->url(array('module' => $main->module, 'do' => 'mail', 'id' => urlencode($row['user_name']))),
               );
               $pub = rating_modify_publisher($row['id'], 'users', $row, $pub, $userconf['ratings']==ENABLED);
               $pub = array_merge($pub, $row);
               $template->get_tpl('information', 'information');
               $template->set_tpl(hook_set_tpl($pub,__FUNCTION__), 'information', array('start' => '$pub[', 'end' => ']'));
               $template->tpl_create(false, 'information');
               close();
               if($userconf['comments']==ENABLED) comments('', $row['uid'], $row['user_id'], $userconf['guests_comments'], $userconf['comments_sort'], true, $msg, 'user', $userconf['ratings']==ENABLED);
            }
         } else info($main->lang['noinfo']);
      } else info($main->lang['this_guest_profile']);
   }
}

function check_recov_pass_id($id){
   if (strlen($id)==25 AND preg_match('/([a-zA-Z0-9]+)/i', $id, $regs)) {
      return strlen($regs[1])==25;
   } else return false;
}

function new_password($msg=""){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user()) redirect(MODULE);
    echo navi(array(), false, false, $main->lang['sendnewpassword']);
    if(!empty($msg)) warning($msg);
    $bed_id=((!isset($_GET['id'])) OR (!check_recov_pass_id($_GET['id'])));
    if($bed_id) info($main->lang['sendnewpassword_info']);
    if($bed_id){
        open();
        echo "<form action='".$main->url(array('module' => 'account', 'do' => 'send_new_password'))."' method='post'>".
        "<table class='form'>".
        "<tr class='row_tr'><td class='form_text'>{$main->lang['mail']}:</td><td class='form_input'>".in_text('email_check', 'input_text')."</td></tr>".
        "<tr class='row_tr'><td class='form_text'>{$main->lang['login']}:</td><td class='form_input'>".in_text('login_check', 'input_text')."</td></tr>".
        captcha().
        "<tr><td colspan='2' align='center'><br />".send_button()."</td></tr>".
        "</table>".
        "</form>";
        close();
    } else {
        $result = $main->db->sql_query("SELECT user_name, user_email, user_activation_code FROM ".USERS." WHERE user_activation_code='{$_GET['id']}'");        
        if($main->db->sql_numrows($result)>0){
            open();
            echo "<form action='".$main->url(array('module' => 'account', 'do' => 'save_new_password', 'id' => $_GET['id']))."' method='post'>".
            "<table class='form'>".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_newpassword']}:</td><td class='form_input'>".in_pass("user_newpassword", "input_password_accaunt")."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['you_renewpassword']}:</td><td class='form_input'>".in_pass("user_renewpassword", "input_password_accaunt")."</td></tr>\n".
            captcha().
            "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table>".in_hide("key", $_GET['id']).
            "</form>";
            close();
        } else meta_refresh("5", $main->url(array('module' => $main->module)), $main->lang['nosearchkey']);
    }    
}

function save_new_password(){
global $main, $patterns;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user() OR $_POST['key']!=$_GET['id']) redirect(MODULE);
    $msg = ($_POST['user_newpassword']==$_POST['user_renewpassword'] AND !empty($_POST['user_renewpassword'])) ? "" : $main->lang['error_new_password'];
    $msg .= check_captcha();    
    $msg .= check_recov_pass_id($_GET['id'])?"":$main->lang['sendnewpassword_info'];
    if(empty($msg)){
        $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT user_name, user_email FROM ".USERS." WHERE user_activation_code='{$_POST['key']}'"));
        sql_update(array('user_password' => pass_crypt($_POST['user_newpassword']), 'user_activation_code' => get_random_string(25)), USERS, "user_activation_code='{$_POST['key']}'");
        $ms = preg_replace(
            array(
                '/\{USER\}/is',
                '/\{SITE\}/is',
                '/\{PASSWORD\}/is'
            ),
            array(
                $info['user_name'],
                "<a href='{$main->config['http_home_url']}'>{$main->config['home_title']}</a>",
                $_POST['user_newpassword']
            ),
            $patterns['save_newpassword']
        );
        send_mail($info['user_email'], $info['user_name'], $main->config['sends_mail'], "noreply", str_replace('{SITE}', $main->config['home_title'], $main->lang['change_new_password']), $ms);
        meta_refresh(3, $main->url(array('module' => $main->module)), $main->lang['apply_change_password']);
    } else new_password($msg);
}

function send_new_password(){
global $main, $patterns;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user()) redirect(MODULE);
    $msg = (empty($_POST['email_check']) AND empty($_POST['login_check'])) ? $main->lang['no_parametrs'] : "";
    $msg .= check_captcha();
    if(!empty($_POST['login_check'])){
        $chk = $main->db->sql_query("SELECT user_name, user_email FROM ".USERS." WHERE user_name='".addslashes($_POST['login_check'])."'");
        $msg .= ($main->db->sql_numrows($chk)==0) ? $main->lang['no_login_search'] : "";
    }
    if(!empty($_POST['email_check'])){
        $chk = $main->db->sql_query("SELECT user_name, user_email FROM ".USERS." WHERE user_email='".addslashes($_POST['email_check'])."'");
        $msg .= ($main->db->sql_numrows($chk)==0) ? $main->lang['no_email_search'] : "";
    }
    if(empty($msg)){
        $info = $main->db->sql_fetchrow($chk);
        $random_string = get_random_string(25);
        sql_update(array('user_activation_code' => $random_string), USERS, "user_name='{$info['user_name']}'");
        $ms = preg_replace(
            array(
                '/\{USER\}/is',
                '/\{SITE\}/is',
                '/\{CODE\}/is',
            ),
            array(
                $info['user_name'],
                "<a href='{$main->config['http_home_url']}'>{$main->config['home_title']}</a>",
                "<a href='{$main->config['http_home_url']}index.php?module=account&amp;do=new_password&amp;id={$random_string}'>{$main->config['http_home_url']}index.php?module=account&amp;do=new_password&amp;id={$random_string}</a>"                
            ),
            $patterns['new_password']
        );
        send_mail($info['user_email'], $info['user_name'], $main->config['sends_mail'], 'noreply', $main->lang['change_password'].' @ '.$main->config['site_name_for_mail'], $ms);
        meta_refresh(3, $main->url(array('module' => 'account')), $main->lang['send_mail_instruct']);
    } else new_password($msg);
}


function show_smiles(){
global $smiles, $parametr_design;
    if(hook_check(__FUNCTION__)) return hook();
    $parametr_design = false;
    $list = "<table width='100%'>";
    $i=0;
    foreach($smiles as $arr){
        $imgt = "<td align='center' width='39' height='39'><img onclick=\"bbeditor.insert_focus(' ".magic_quotes($arr[0])." ');\" style='cursor: pointer;' src='{$arr[1]}' alt='".htmlspecialchars($arr[0], ENT_QUOTES)."' title='".htmlspecialchars($arr[0], ENT_QUOTES)."' /></td>";
        if($i==0) $list .= "<tr>{$imgt}";
        elseif($i==6) {$list .= "{$imgt}</tr>"; $i=-1;}
        else $list .= $imgt;
        $i++;
    }
   $list .= ($i<=6) ? "</tr></table>" : "</table>";
   echo $list;
   ?>
   <script type="text/javascript">
   //<![CDATA[
    ajaxload=true; // Почему-то без єтого ошибка JS
   //]]>
   </script>
   <?php
   kr_exit();
}

function user_email($msg=""){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_user()) redirect(MODULE);
    if(!empty($msg)) warning($msg);
    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE user_name='".addslashes($_GET['id'])."'");
    if($main->db->sql_numrows($result)>0){
        $info = $main->db->sql_fetchrow($result);
        open();
        echo "<form id='autocomplete' style='margin: 3px;' method='post' action='".$main->url(array('module' => $main->module, 'do' => 'send_mail', 'id' => $_GET['id']))."'>\n".
        "<table class='form' align='center' width='100%' id='form_{$main->module}'>\n".
        "<tr class='row_tr'><td class='form_text'>{$main->lang['recipient']}:</td><td class='form_input'><b>{$info['user_name']}</b></td></tr>\n".
        "<tr class='row_tr'><td class='form_text'>{$main->lang['subj']}:<span class='star'>*</span></td><td class='form_input'>".in_text("subj", "input_text2")."</td></tr>\n".
        "<tr class='row_tr'><td class='form_text'>{$main->lang['message']}:<span class='star'>*</span></td><td class='form_input'>".editor("message", 9, "97%")."</td></tr>\n".
        captcha()."<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
        "</table></form>";
        close();
    } else redirect(MODULE);
}

function send_user_email(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_user()) redirect(MODULE);
    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE user_name='".addslashes($_GET['id'])."'");
    if($main->db->sql_numrows($result)>0){
        $info = $main->db->sql_fetchrow($result);
        filter_arr(array('subj', 'message'), POST, TAGS);
        $msg = error_empty(array('subj', 'message'), array('subj_err', 'message_err')).check_captcha();
        if(empty($msg)){
            send_mail($info['user_email'], $info['user_name'], $main->user['user_email'], $main->user['user_name'], $_POST['subj'], parse_bb(bb($_POST['message']))."\n\r");
            meta_refresh(3, $main->url(array('module' => $main->module, 'do' => 'user', 'id' => $info['user_id'])), $main->lang['user_email_sends']);
        } else user_email($msg);
    } else redirect(MODULE); 
}

function usercheck(){
global $main,$userconf;
    if(hook_check(__FUNCTION__)) return hook();
    if(!isset($_POST['user'])) redirect(MODULE);
    $fr=user_login_check($_POST['user']);
    if($fr['status']==true){
       $_POST['user']=$fr['user'];
       $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE user_name='{$_POST['user']}' OR user_id='".cyr2lat($_POST['user'])."' LIMIT 1");
       if($main->db->sql_numrows($result)>0) echo $main->lang['checked_user'];
       else echo $main->lang['nochecked_user'];
    } else echo $fr['message'];
    kr_exit();
}

function user_login_check($login, $length=true){
   global $main, $userconf;
   if(hook_check(__FUNCTION__)) return hook();
   $login = trim($login);
   $ret = array('status' =>false,'user' => $login, 'message'=>'');
   $login = kr_filter($login, TAGS);
   if(!empty($login)){
      if(cyr2lat($login)!=""){
         if(
            !array_key_exists('user_name_length', $userconf) OR (
                array_key_exists('user_name_length',$userconf) AND ($userconf['user_name_length']!="") AND (mb_strlen($login)>=intval($userconf['user_name_length']))
            ) OR 
            $length == false
         ){
             $ret = array(
                'status' => true,
                'user'   => $login
             );
         } else $ret['message']=preg_replace('%(?i)<li[^>]*>([^<{]*)(\{COUNT\})*([^<{]*)</li>%s', '<span style="color:red">$1 '.$userconf['user_name_length'].'$3</span>', $main->lang['error_length_uname']);
      } else $ret['message']=preg_replace('%(?i)<li[^>]*>([^<{]*)(\{COUNT\})*([^<{]*)</li>%s', '<span style="color:red">$1</span>', $main->lang['error_uname_cyr2lat']);
   } else $ret['message']=$main->lang['checked_empty'];
   return $ret;
}
function user_password_check($password){
   global $main, $userconf;
   if(hook_check(__FUNCTION__)) return hook();
   return ($userconf['password_length']>mb_strlen($password)) ? str_replace('{COUNT}', $userconf['password_length'], $main->lang['error_lenghtpass']) : "";
}
function icon_navi($icon_navi_var){
   global $main, $icon_navi;
   if(hook_check(__FUNCTION__)) return hook();
   return $icon_navi_var;
}

function favorite(){
   global $main, $navi, $link, $icon_navi;
   if(hook_check(__FUNCTION__)) return hook();
   main::init_function('scan_dir');
   if(!is_user()) redirect($main->url(array('module' => $main->module, 'do' => 'login')));    
   bcrumb::add($main->lang['favorite']);
   echo $navi;
   open();
   echo "<table align='center' class='account_main'><tr>\n";
   foreach(icon_navi($icon_navi) as $a=>$v) echo "<td width='120' align='center'><a class='account_ico' href='{$v['url']}' title='{$v['title']}'><img src='{$v['image']}' alt='{$v['title']}' /><br />{$v['title']}</a></td>\n";
   echo "</tr></table>\n";
   close();
   $files = scan_dir('modules/account/info/', '/(.+?)\.php$/i');
   foreach($files as $file) require_once 'modules/account/info/'.$file; 
}
function switch_module_account(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch ($_GET['do']){
         case "login": login(); break;
         case "sign": sign(); break;
         case "logout": logout(); break;
         case "new_user": new_user(); break;
         case "registration": registration(); break;
         case "controls": controls_account(); break;
         case "case_avatar": case_avatar(); break;
         case "save_controls": save_controls_account(); break;
         case "activation": activation(); break;
         case "user": information(); break;
         case "new_password": new_password(); break;
         case "send_new_password": send_new_password(); break;
         case "save_new_password": save_new_password(); break;
         case "smiles": show_smiles(); break;
         case "mail": user_email(); break;
         case "send_mail": send_user_email(); break;
         case "usercheck": usercheck(); break;
         case "favorite": favorite(); break;
         case "userinfo":main::required("modules/{$main->module}/userinfo.php"); break;
         default: main_account(); break;
      }
   } else main_account();
}
switch_module_account();
?>