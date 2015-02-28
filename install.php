<?php
/**
* Установочный файл системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource install.php
* @version 2.0
*/
define('KASSELERCMS', true);
define("ADMIN_FILE", true);
define('INSTALLCMS', true);
define('E__CORE__', true);
define('E__DATABASECONF___', true);
//define('E__DATABASE__', true);

require_once "includes/function/init.php";
$page_title = 'Install @ Kasseler CMS';

main::inited('class.templates', 'function.templates', 'function.dbsystable', 'function.scan_dir');
$template = new template();
$template->path = 'install/template/';
$template->get_tpl('index', 'index');
$tpl_create = new tpl_create;
$tpl_create->tpl_creates();


if(isset($_GET['lang']) AND file_exists("install/language/{$_GET['lang']}.php")){
    $_SESSION['wizard_lang'] = $_GET['lang'];
    setcookies($_GET['lang'], "admin_lang");
    setcookies($_GET['lang'], "lang");
    redirect("install.php");
}
global $install_lang, $language_install;
$language_install = isset($_COOKIE["admin_lang"]) ? $_COOKIE["admin_lang"] : 'russian';
main::required("install/language/{$language_install}.php");

function button_case(){
global $language_install;
    return ($language_install=='russian') ? "ru" : "en";
}

function scan_directories($dir='uploads'){
static $row;
    if(@$dir[mb_strlen(@$dir)-1]!="/") @$dir .= "/";
    if(($handle = opendir($dir))){
        while(($obj = readdir($handle))){
            if(!preg_match('/\.+/', $obj)){
                $row = ($row=='row4') ? 'row5' : 'row4';
                $chmod = mb_substr(get_chmod($dir.$obj), 1);
                echo "<tr class='{$row}'><td>{$dir}{$obj}</td><td align='center'><span style='color: ".($chmod==777?'green':'red')."'><b>{$chmod}</b></span></td><td align='center'><span style='color: #777777;'><b>777</b></span></td></tr>";                
                if(is_dir($dir.$obj)) scan_directories($dir.$obj);
            }
        }
        closedir($handle);
    }
}

function step1(){
global $install_lang;
    return array('content' => $install_lang['step1_content']."<div align='right'><a href='install.php?do=step2'><input onclick=\"location.href='install.php?do=step2';\" class='submit' type='image' src='install/template/images/next_".button_case().".png' alt='{$install_lang['next']}' /></a></div>", 'title' => $install_lang['step1_title']);
}

function step2(){
global $install_lang;
    $content = "<textarea rows='17' readonly='readonly' cols='60' style='border: 1px #F1F1F1 solid; background-color: #FFFFFF; width: 99%;'>".file_get_contents("install/licence.txt")."</textarea><br /><br />".
    "<div align='right'>
    <form method='post' action='install.php?do=step3'>
    <input type='hidden' name='apply' value='yes' />
    <input class='submit' type='image' src='install/template/images/apply_".button_case().".png' alt='{$install_lang['accept']}' />
    <a href='#' onclick=\"location.href='http://kasseler-cms.net/'; return false;\"><input class='submit' type='image' src='install/template/images/noapply_".button_case().".png' alt='{$install_lang['doaccept']}' /></a>    
    </form></div>";
    return array('content' => $content, 'title' => $install_lang['step2_title']);
}

function step3(){
global $install_lang;
static $row;
    main::init_function('get_chmod');
    if(isset($_POST['apply']) AND $_POST['apply']=='yes'){
        ob_start();
        echo $install_lang['step3_content']."<br /><br />".
        "<h1>{$install_lang['chmod_dir']}</h1>".
        "<table width='100%' class='table'><tr><th>{$install_lang['link_dir']}</th><th width='150'>{$install_lang['chmod_this']}</th><th width='150'>{$install_lang['chmod_corect']}</th></tr>";
        scan_directories();
        echo "</table>";
        echo "<br /><br /><h1>{$install_lang['chmod_file']}</h1><table width='100%' class='table'><tr><th>{$install_lang['link_dir']}</th><th width='150'>{$install_lang['chmod_this']}</th><th width='150'>{$install_lang['chmod_corect']}</th></tr>";
        $config_dir = opendir("includes/config");
        while(($file = readdir($config_dir))){
            if (preg_match('/(.+?)\.php/', $file)){ 
                $chmod = mb_substr(get_chmod("includes/config/{$file}"), 1);
                $row = ($row=='row4') ? 'row5' : 'row4';
                echo "<tr class='{$row}'><td>includes/config/{$file}</td><td align='center'><span style='color: ".($chmod>=666?'green':'red')."'><b>{$chmod}</b></span></td><td align='center'><span style='color: #777777'><b>666</b></span></td></tr>";
            }
        }
        $row = ($row=='row4') ? 'row5' : 'row4';
        $chmod = mb_substr(get_chmod("sitemap.xml"), 1);
        echo "<tr class='{$row}'><td>/sitemap.xml</td><td align='center'><span style='color: ".($chmod>=666?'green':'red')."'><b>{$chmod}</b></span></td><td align='center'><span style='color: #777777'><b>666</b></span></td></tr>";
        closedir($config_dir);
        echo "</table><br /><br /><div align='right'><a href='install.php?do=step4'><input onclick=\"location.href='install.php?do=step4';\" class='submit' type='image' src='install/template/images/next_".button_case().".png' alt='{$install_lang['next']}' /></a></div>";
        $content = ob_get_contents(); ob_end_clean();
        $_SESSION['apply'] = true;
    } else $content = warning($install_lang['noyeslicense'], true);
    return array('content' => $content, 'title' => $install_lang['step3_title']);
}

function step4($connect=""){
global $install_lang;
    if(isset($_SESSION['apply']) AND $_SESSION['apply']==true){
        $content = "";
        $msg = (!is_writable('includes/config/config.php')) ? $install_lang['config_error'] : "";
        $msg .= (!is_writable('includes/config/configdb.php')) ? $install_lang['configdb_error'] : "";
        if(empty($msg)){
            if(!empty($connect)) $content .= warning($connect, true);
            $content .= "<form action='install.php?do=step5' method='post'>".
            "<table width='100%' class='form'>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['database']}</td><td class='form_input2'>".in_sels("type", array('mysql' => 'mysql', 'mysqli' => 'mysqli', 'pdo' => 'pdo'), 'select')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['server']}</td><td class='form_input2'>".in_text("host", 'input_text2', 'localhost')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['user']}</td><td class='form_input2'>".in_text("user", 'input_text2', 'root')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['password']}</td><td class='form_input2'>".in_pass("password", 'input_text2')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['dbname']}</td><td class='form_input2'>".in_text("name", 'input_text2', 'kasseler')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['prefix']}</td><td class='form_input2'>".in_text("prefix", 'input_text2', 'kasseler')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['charset']}</td><td class='form_input2'>".in_text("db_charset", 'input_text2', 'utf8')."</td></tr>".
            "</table><br /><br /><div align='right'><input class='submit' type='image' src='install/template/images/next_".button_case().".png' alt='{$install_lang['next']}' /></div>".
            "</form>";
        } else $content .= warning($msg, true);
        
    } else $content = warning($install_lang['noyeslicense'], true);
    return array('content' => $content, 'title' => $install_lang['step4_title']);
}

function step5(){
global $install_lang, $db, $database, $config, $revision;
    if(isset($_SESSION['apply']) AND $_SESSION['apply']==true){
        $content = "";
        $database = array(
            'host'                => $_POST['host'],
            'user'                => $_POST['user'],
            'password'            => $_POST['password'],
            'name'                => $_POST['name'],
            'prefix'              => $_POST['prefix'],
            'type'                => $_POST['type'],
            'charset'             => $_POST['db_charset'],
            'cache'               => '',
            'sql_cache_clear'     => 'INSERT,UPDATE,DELETE',
            'no_cache_tables'     => 'sessions',
            'revision'            => $revision
        );
        
        if(file_exists("includes/classes/{$_POST['type']}.class.php")) main::required("includes/classes/{$_POST['type']}.class.php");
        $msg = (!$db->db_connect_id) ? $install_lang['connect_error'] : "";
        if(empty($msg)){
            main::required("includes/function/sources.php");
            $POST = array(
                'http_home_url'    => "http://".str_replace('www.', '', get_host_name())."/",
                'rewrite'          => '',
                'admin_mail'       => 'admin@'.str_replace('www.', '', get_host_name()),
                'contact_mail'     => 'support@'.str_replace('www.', '', get_host_name()),
                'sends_mail'       => 'info@'.str_replace('www.', '', get_host_name()),
                'install_date'     => gmdate("Y-m-d"),
                'user_cookies'     => get_random_string(15)."_user",
                'admin_cookies'    => get_random_string(15)."_admin",
            );
            //planer config
            if(file_exists('sitemap.xml') AND is_writable('sitemap.xml') AND is_writable('includes/config/config_planner.php')){
                $content_config = file_get_contents('includes/config/config_planner.php');
                $content_config = preg_replace('/(.*?)\'url\' => \'index\.php\?system=sitemap\', \'status\' => \'(.*?)\'(.*)/is', "\\1'url' => 'index.php?system=sitemap', 'status' => 'on'\\3", $content_config);
                file_write("includes/config/config_planner.php", $content_config);
            }
            //
            foreach($POST as $key => $value) $_POST[$key] = $value;
            save_config('configdb.php', '$database', $database);
            save_config('config.php', '$config', $config);
            $content = "<form action='install.php?do=step6' method='post'>".in_hide('type', $_POST['type']).
            "<table width='100%' class='form'>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['admin']}</td><td class='form_input2'>".in_text("admin", 'input_text2', 'Admin')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['email']}</td><td class='form_input2'>".in_text("email", 'input_text2', 'admin@'.str_replace('www.', '', get_host_name()))."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['passwordadmin']}</td><td class='form_input2'>".in_pass("password", 'input_text2')."</td></tr>".
            "<tr class='row_tr'><td class='form_text2'>{$install_lang['repasswordadmin']}</td><td class='form_input2'>".in_pass("repassword", 'input_text2')."</td></tr>".
            "</table><br /><br /><div align='right'><input class='submit' type='image' src='install/template/images/install_".button_case().".png' alt='Install' /></div>".
            "</form>";
        } else {
            $step = step4($msg);
            $content .= $step['content'];
        }
    } else $content = warning($install_lang['noyeslicense'], true);
    return array('content' => $content, 'title' => $install_lang['step5_title']);
}

function step6(){
global $db, $install_lang, $database, $adminfile, $license_sys, $version_sys;
    if(isset($_SESSION['apply']) AND $_SESSION['apply']==true){
        $content = "";

        if(file_exists("includes/classes/{$_POST['type']}.class.php")) main::required("includes/classes/{$_POST['type']}.class.php");
        $readdump = fopen("install/sql/install.sql", "rb");
        $stringdump = fread($readdump, filesize("install/sql/install.sql"));
        fclose($readdump);
        $_stringdump = explode(";\r\n", $stringdump);
        if(count($_stringdump)<5) $_stringdump = explode(";\n", $stringdump);
        $stringdump = $_stringdump;
        $dump_count = count($stringdump);
        $row = "row4";
        for ($i = 0; $i < $dump_count; $i++) {
            if(trim($stringdump[$i])=='') continue;
            
            $string = str_replace(array(
                    "{PREFIX}", 
                    "{USER}", 
                    "{DATE}", 
                    "{DATETIME}", 
                    "{CHARSET}"
            ), array(
                    $database['prefix'], 
                    $_POST['admin'], 
                    kr_datecms("Y-m-d"), 
                    kr_datecms("Y-m-d H:i:s"), 
                    $database['charset']
            ), $stringdump[$i]);
                        
            $id = $db->sql_query($string);
            if(preg_match("/CREATE/", $string)){
                $table = explode("`", $string);
                $content .= "<tr class='{$row}'><td width='50%'>{$table[1]}</td>".(($id) ? "<td align='center'><font color='green'><b>{$install_lang['yes']}</b></font></td>" : "<td align='center'><font color='red'><b>{$install_lang['no']}</b></font></td>")."</tr>";
                $row = ($row=='row4') ? 'row5' : 'row4';
            }
        }
        sql_insert(array(
            'user_id'        => cyr2lat($_POST['admin']),
            'user_name'      => $_POST['admin'],
            'user_email'     => $_POST['email'],
            'user_password'  => pass_crypt($_POST['password']),
            'user_group'     => '1',
            'user_avatar'    => 'admin.png',
            'user_website'   => "http://".get_host_name()."/",
            'user_regdate'   => kr_datecms("Y-m-d H:i:s"),
            'user_level'     => '2'
        ), USERS);
        main::required("includes/function/sources.php");
        $database['revision']=get_db_revision(true);
        $_POST=array();
        save_config('configdb.php', '$database', $database);
        if(!step_update()) $content.="<div class='warning'><div class='binner'><div style='padding-left: 10px;'><ul>{$install_lang['error_update']}</ul></div></div></div>";
        $content = "<img src='http://www.kasseler-cms.net/system.php?do=check_install&amp;host=".get_env('HTTP_HOST')."&amp;lic={$license_sys}&amp;version={$version_sys}' alt='check_install' /><table class='table' width='100%'><tr><th>{$install_lang['table']}</th><th>{$install_lang['status']}</th></tr>{$content}</table><br /><br /><div align='right'><a href='{$adminfile}'><input onclick=\"location.href='{$adminfile}';\" class='submit' type='image' src='install/template/images/next_".button_case().".png' alt='{$install_lang['next']}' /></a></div>";
    } else $content = warning($install_lang['noyeslicense'], true);
    return array('content' => $content, 'title' => $install_lang['step6_title']);
}
function step_update(){
   global $install_lang,$config,$database,$main;
   main::required("includes/function/sources.php");
   
   $db_revision=$database['revision'];
   $readdump = fopen("install/sql/update.sql", "rb");
   $stringdump = fread($readdump, filesize("install/sql/update.sql"));
   fclose($readdump);
   preg_match_all('/(?i)--\x20*\{fix\x20*([0-9]*)[^\r\n]*(.*?)--\{fix\x20end\}/sm', $stringdump, $result, PREG_PATTERN_ORDER);
   for ($i = 0; $i < count($result[0]); $i++) {
      $num=intval($result[1][$i]);
      if ($num>$db_revision) {
         $_stringdump = $result[2][$i];
         $_stringdump = str_replace("{PREFIX}", $database['prefix'], $_stringdump);
         if(array_key_exists('admin', $_POST)) $_stringdump = str_replace("{USER}", $_POST['admin'], $_stringdump);
         $_stringdump = str_replace("{DATE}", kr_datecms("Y-m-d"), $_stringdump);
         $_stringdump = str_replace("{DATETIME}", kr_datecms("Y-m-d H:i:s"), $_stringdump);
         $_stringdump = str_replace("{CHARSET}", $database['charset'], $_stringdump);
         preg_match_all('/(.*?);[\r\n]{1,}/si', $_stringdump, $regs, PREG_PATTERN_ORDER);
         $_stringdump = $regs[0];
         $count_sql=0;
         for ($j = 0; $j < count($_stringdump); $j++) {
            $sql=trim($_stringdump[$j]);
            if ($sql!=""){
               $config['mode_debugging_sql']='';
               if (!$main->db->sql_query($sql)){
                  $cron_config = str_replace("{REVISION}", $num, stripslashes($install_lang['cron_config']));
                  $file_link="uploads/tmpfiles/update.run.sql";
                  $file = fopen($file_link, "w");
                  for ($n = $j; $n < count($_stringdump); $n++){if (trim($_stringdump[$n])!="") fputs ($file,($_stringdump[$n].";\r\n"));}
                  fputs ($file,"-- ".$cron_config);
                  fclose ($file);
                  return false;
               };
               $count_sql++;
            }
         }
         set_db_revision($num);
         $database['revision']=get_db_revision();
         save_config('configdb.php', '$database', $database);
      }
   }
   return true;
}

$install = array('content' => '', 'title' => '');
if(isset($_GET['do'])){
    switch($_GET['do']){
        case "step2" : $install = step2(); break;
        case "step3" : $install = step3(); break;
        case "step4" : $install = step4(); break;
        case "step5" : $install = step5(); break;
        case "step6" : $install = step6(); break;
        default: $install = step1(); break;
    }
} else $install = step1();

add_meta_value($install['title']);
$template->set_tpl(array(
    'language'                 => "<a href='install.php?lang=russian'>".(($language_install=='russian')?"<b>{$install_lang['russian']}</b>":$install_lang['russian'])."</a> | <a href='install.php?lang=english'>".(($language_install=='english')?"<b>{$install_lang['english']}</b>":$install_lang['english'])."</a>",
    'content'                  => $install['content'],
    'install_title'            => $install['title'],
));

$contents = $template->tpl_create(true);
main::required("includes/nocache.php");
gz($contents);
?>