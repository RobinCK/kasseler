<?php
/**
* Файл обработки Аякс запросов
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/ajax.php
* @version 2.0
*/
if (!defined("KASSELERCMS") AND !defined("ADMIN_FILE")) die("Access is limited");

function favorite(){
global $main, $modules;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_user()) kr_exit();	
    if(isset($_POST['id']) AND preg_match('/^([0-9]*)$/', $_POST['id']) AND isset($modules[$_POST['module']])){
        if($_POST['id'] != '') {
            list($count) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FAVORITE." WHERE modul='{$_POST['module']}' AND users='{$main->user['user_name']}' AND post='{$_POST['id']}'"));
            if($count>0) $main->db->sql_query("DELETE FROM ".FAVORITE." WHERE modul='{$_POST['module']}' AND post='{$_POST['id']}' AND users='{$main->user['user_name']}'");	
            else sql_insert(array('modul' => $_POST['module'], 'users' => $main->user['user_name'], 'post'  => $_POST['id']), FAVORITE);
            
        }
    }
}

function rating(){
   global $main, $database;
   if(hook_check(__FUNCTION__)) return hook();
   $module=$_POST['table'];
   filter_arr(array('voted', 'table', 'id'), POST, TAGS);
   $table = strpos($_POST['table'], $database['prefix'].'_')===false ? $database['prefix'].'_'.$_POST['table'] : $_POST['table'];
   if(!preg_match('/^([a-z0-9\-_]*)$/i', $_POST['table']) OR !preg_match('/^[c]*([0-9]*)$/i', $_POST['id'])) kr_exit();
   preg_match('/^[c]*([0-9]*)$/i', $_POST['id'],$regs);
   $id=intval($regs[1]);
   $voted=intval($_POST['voted']);
   if(!is_guest()){
      list($idr,$r_up,$r_down,$usersv)=$main->db->sql_fetchrow($main->db->sql_query("select id,r_up,r_down,users from ".RATINGS." where `module`='{$module}' and idm={$id}"));
      if($voted==1 OR $voted==-1){
         $users=$main->user['uid'].";".($voted>0?"+":"-").",";
         if(empty($idr)) {
            $r_up=0;$r_down=0;
            $insert=array('idm'=>$id,'module'=>$module,'users'=>",".$users);
            if($voted>0) $insert['r_up']=1;else $insert['r_down']=1;
            sql_insert($insert,RATINGS);
            if($voted>0) $r_up++; else $r_down++;
         } else {
            $modify=$voted>0?"r_up=r_up+1 ":"r_down=r_down+1";
            $main->db->sql_query("update ".RATINGS." set {$modify},`users`=CONCAT(`users`,'{$users}') where id={$idr} and not `users` like '%,{$main->user['uid']};%'");
            if($main->db->sql_affectedrows()!=0){if($voted>0) $r_up++; else $r_down++;}
         }
         $ret=array('up'=>$r_up,'down'=>$r_down) ;
         echo json_encode($ret);
      } else {
         if (preg_match("/,{$main->user['uid']};(.),/", $usersv, $regs)) {
            $modify=$regs[1]=='+'?"r_up=r_up-1 ":"r_down=r_down-1";
            $users=$main->user['uid'].";{$regs[1]},";
            $main->db->sql_query("update ".RATINGS." set {$modify},`users`=REPLACE(`users`,'{$users}','') where id={$idr} and `users` like '%,{$main->user['uid']};%'");
            if($main->db->sql_affectedrows()!=0){
               list($idr,$r_up,$r_down)=$main->db->sql_fetchrow($main->db->sql_query("select id,r_up,r_down from ".RATINGS." where `module`='{$module}' and idm={$id}"));
            }
            $ret=array('up'=>$r_up,'down'=>$r_down) ;
            echo json_encode($ret);
         }
      }
   }
   kr_exit();
}

function preview() {
global $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($_POST['text']) AND !empty($_POST['text'])){
        $html = "<div style='padding: 4px;' align='left'>".str_replace('&amp;#036;', '$', parse_bb(bb(kr_filter($_POST['text']))))."</div>";
        echo stripcslashes($html).$tpl_create->javascript_insert().$tpl_create->link_insert();
    } else echo "              ";
}

function chk_ver(){
global $lang, $version_sys, $revision;
    if(hook_check(__FUNCTION__)) return hook();
    $actial = file_get_contents('http://kasseler-cms.net/index.php?system=r');
    $actial = preg_replace('/(.*?)REVISION SYSTEM:\s([0-9]{1,4})(.*)/is', '\\2', $actial);
    if($actial>$revision) echo "<img src='".TEMPLATE_PATH."admin/images/arrow.gif' alt='>' style='margin-right: 4px;' />{$lang['detect_new_ver']} r{$actial} | <b>{$lang['your_version']}: {$version_sys} r{$revision}</b>";
    else echo "<img src='".TEMPLATE_PATH."admin/images/arrow.gif' alt='>' style='margin-right: 4px;' />{$lang['no_detect_new_ver']} | <b>{$lang['your_version']}: {$version_sys} r{$revision}</b>";
}

function delete_comment(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_support()) {
        $main->db->sql_query("DELETE FROM ".COMMENTS." WHERE cid='{$_POST['cid']}'");
        if(!empty($_POST['table'])) $main->db->sql_query("UPDATE {$_POST['table']} SET comment=comment-1 WHERE id='{$_POST['id']}'");
    }
}

function edit_comment(){
global $main, $lang;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".COMMENTS." WHERE cid='{$_POST['id']}'");
    if($main->db->sql_numrows($result)>0){
        $row = $main->db->sql_fetchrow($result);
        if($row['name']!=$main->user['user_name'] AND !is_support()){
            kr_http_ereor_logs(403);
            return '';
        }
        echo "<div id='content_conteiner_{$_POST['id']}' style='display: none;'>".parse_bb($row['comment'])."</div><div id='edit_conteiner_{$_POST['id']}'><textarea id='edit_comment_area_{$_POST['id']}' style='height: {$_POST['height']}px; width:97%'>".bb($row['comment'], DECODE)."</textarea><div align='right' style='width: 97%'><a href='#' onclick=\"return apply_edit_comment('{$_POST['id']}');\" class='apply_but'><b>{$lang['apply']}</b> <a href='#' class='cancel_but' onclick=\"return cancel_edit_comment('{$_POST['id']}');\"><b>{$lang['cancel']}</b></a></div></div>";
    }
    return true;
}

function apply_edit_comment(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".COMMENTS." WHERE cid='{$_POST['id']}'");
    if($main->db->sql_numrows($result)>0 AND isset($_POST['comment']) AND !empty($_POST['comment']) AND mb_strlen($_POST['comment'])>3){
        $row = $main->db->sql_fetchrow($result);
        if($row['name']!=$main->user['user_name'] AND !is_support()){
            kr_http_ereor_logs(403);
            return '';
        }
        sql_update(array('comment' => bb($_POST['comment'])), COMMENTS, " cid='{$_POST['id']}'");
        echo stripslashes(parse_bb(bb($_POST['comment'])));
    }
    return true;
}

function update_upload(){
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    if(preg_match('/\.\.\//i', $_POST['dir']) OR mb_strpos($_POST['dir'], 'uploads/') === false) {
        kr_http_ereor_logs(403);
        return false;
    }
    $dir = update_list_files($_POST['dir'], isset($_POST['options'])?($_POST['options']=='true'?true:false):true);
    $_SESSION['uploaddir'] = $_POST['dir'];
    if(!empty($dir)) echo $dir."<script type='text/javascript'>$$('uploaddir').value='{$_POST['dir']}'</script>";
    else echo "              ";
    return true;
}

function update_upload_json(){
   if(hook_check(__FUNCTION__)) return hook();
   if(is_admin()){
      if(preg_match('/\.\.\//i', $_POST['dir']) OR mb_strpos($_POST['dir'], 'uploads/') === false) {
         kr_http_ereor_logs(403);
         return false;
      }
      $dir = $_POST['dir'];
   } else $dir = $_SESSION['uploaddir'];
   if(($dir!='uploads/') && file_exists($dir)){
      $attache_dir = opendir($dir);
      $files = array();
      $i = 1; $list_upload = "";
      while(($file = readdir($attache_dir))){
         if(!is_dir($dir.$file)){$files[] = $file;}
      }
      closedir($attache_dir);
      foreach ($files as $key => $value) {
         if(substr($value,0,5)=='mini-'){
            $fv = substr($value,6);
            $find = array_search($fv, $files);
            if($find!==false) unset($files[$key]);
         } else {
            $find = array_search('mini-'.$value, $files);
            if($find!==false) unset($files[$find]);
         }
      }
      sort($files);
   }
   echo json_encode($files);
   return true;
}

function createdir(){
    if(hook_check(__FUNCTION__)) return hook();
    $i = 0;    
    if(!is_support()) return false;
    while(true){
        $subname = ($i>0) ? $i : "";
        if(!file_exists($_POST['dir']."NewDirectory".$subname)){ mkdir($_POST['dir']."NewDirectory".$subname, 0777); break;}
        $i++;
    }
    update_upload();
    return true;
}

function delete_attach(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_function('attache');
    $dir = urldecode($_GET['dir']);
    $file = urldecode($_GET['file']);    
    if(preg_match('/\.\.\//i', $dir) OR mb_strpos($dir, 'uploads/') === false) {
        kr_http_ereor_logs(403);
        return false;
    }
    if(count(explode('/', $dir))>2){
        $result = $main->db->sql_query("SELECT user_id FROM ".ATTACH." WHERE path='{$dir}' AND file='{$file}'");
        $count = $main->db->sql_numrows($result);
        $info = $main->db->sql_fetchrow($result);
        //if(($count>0 AND $info['user_id']==$main->user['uid']) OR $count==0 OR is_support()){
        if(($count>0 AND $info['user_id']==$main->user['uid']) OR is_support()){
            if(!is_dir($dir.$file)){
                if(file_exists($dir.$file)) unlink($dir.$file);
                if(file_exists($dir."mini-".$file)) unlink($dir."mini-".$file);
                $where = (is_support() ? "" : " user_id='{$main->user['uid']}' AND ");
                $main->db->sql_query("DELETE FROM ".ATTACH." WHERE {$where} path='{$dir}' AND file='{$file}'");
            } else remove_dir($dir.$file);
        } else echo "<script type='text/javascript'>alert('{$main->lang['nodeletefile']}')</script>";
        $dire = update_list_files($dir, $_POST['options']=='true'?true:false);
        if(!empty($dire)) echo $dire;
        else echo "              ";
        return true;
    } else kr_http_ereor_logs(403);
}

function renames(){
global $lang, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(!file_exists($_POST['dir'].$_POST['file'])) return false;
    if(preg_match('/\.\.\//i', $_POST['dir']) OR mb_strpos($_POST['dir'], 'uploads/') === false) {
        kr_http_ereor_logs(403);
        return false;
    }
    $new = (preg_match('/^[a-zA-Z0-9_.-]+$/D', trim($_POST['new_name']))) ?  trim($_POST['new_name']) : '';
    if($new == '') {
        echo "<script type='text/javascript'>alert('{$main->lang['new_file_name_empty']}')</script>";
        return false;
    }
    if(count(explode('/', $_POST['dir']))>2){
        if(!is_dir($_POST['dir'].$_POST['file'])){
            $exp = get_type_file(mb_strtolower($_POST['file']));
            if(!file_exists($_POST['dir'].$new.'.'.$exp)){
                $result = $main->db->sql_query("SELECT user_id FROM ".ATTACH." WHERE path='{$_POST['dir']}' AND file='{$_POST['file']}'");
                $count = $main->db->sql_numrows($result);
                $info = $main->db->sql_fetchrow($result);
                //if(($count>0 AND $info['user_id']==$main->user['uid']) OR $count==0 OR is_support()){
                if(($count>0 AND $info['user_id']==$main->user['uid']) OR is_support()){
                    rename($_POST['dir'].$_POST['file'], $_POST['dir'].$new.'.'.$exp);
                    if(file_exists($_POST['dir']."mini-".$_POST['file'])) rename($_POST['dir']."mini-".$_POST['file'], $_POST['dir']."mini-".$new.'.'.$exp);
                    sql_update(array('file' => $new.'.'.$exp), ATTACH, "file='{$_POST['file']}' AND path='{$_POST['dir']}'");
                } else echo "<script type='text/javascript'>alert('{$main->lang['norenamefile']}')</script>";
                update_upload();
            } else echo "<script type='text/javascript'>alert('{$lang['error_rename']}');</script>";
        } else {
            if(!is_support()) return false;
            if(!file_exists($_POST['dir'].$new)){
                rename($_POST['dir'].$_POST['file'], $_POST['dir'].$new);
                update_upload();
            } else echo "<script type='text/javascript'>alert('{$lang['error_rename']}');</script>";
        }
        return true;
    } else kr_http_ereor_logs(403);
}

function quickselect(){
global $main, $limit_fields;
    if(hook_check(__FUNCTION__)) return hook();
    if(!preg_match('/^([a-z0-9\-_]*)$/i', $_POST['table']) OR !preg_match('/^([0-9]*)$/i', $_POST['param'])) kr_exit();
    if(!in_array($_POST['col'], $limit_fields)) return false;
    $result = $main->db->sql_query("SELECT {$_POST['col']} FROM {$_POST['table']} WHERE".stripcslashes($_POST['param'])." UPPER({$_POST['col']}) LIKE '".mb_strtoupper($_POST['string'])."%'");
    while(list($var) = $main->db->sql_fetchrow($result)){
        echo $var." ";
    }
    
}

function delete_fav(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(is_user()){
        define('ACCOUNT', true); 
        $main->db->sql_query("DELETE FROM ".FAVORITE." WHERE users='{$main->user['user_name']}' AND id='{$_GET['id']}'");
        main::required("modules/account/info/favorites.php");
    }
}

function module_function(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      if (preg_match('/([a-zA-Z\-_]*)/si', $_GET['do'], $regs)) {
         $file="includes/ajaxed/".$regs[1].".php";
         if(file_exists($file)) main::required($file);
      } 
   }
}
if(isset($_GET['ajaxed'])){
    switch($_GET['ajaxed']){
        case "apply_edit_comment": apply_edit_comment(); break;
        case "edit_comment": edit_comment(); break;
        case "delete_comment": delete_comment(); break;
        case "rename": renames(); break;
        case "mkdir": createdir(); break;
        case "chk_ver": chk_ver(); break;
        case "preview": preview(); break;
        case "rating": rating(); break;
        case "favorite": favorite(); break;
        case "quickselect": quickselect(); break;
        case "delete_attach": delete_attach(); break;
        case "update_upload": update_upload(); break;
        case "update_upload_json": update_upload_json(); break;
        case "runer": main::init_function('planner'); runer(); break;
        case "delete_fav": delete_fav(); break;
        case "module": module_function(); break;
    }
}
?>
