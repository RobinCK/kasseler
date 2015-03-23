<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('ADMIN_FILE')) die("Hacking attempt!");


global $navi, $main, $break_load;
$break_load = false;
if(is_moder()) {
    warning($main->lang['moder_error']);
    $break_load = true;
} elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
    warning($main->lang['admin_error']);
    $break_load = true;
}

$navi = array(
    array('', 'home'),
    array('add', 'add_hooks'),
    array('search', 'search_plagin'),
    //array('config', 'config')
);

function main_hooks(){
global $hooks, $main, $adminfile;
    if(hook_check(__FUNCTION__)) return hook();
    $hook_key=array('title'=>"title", 'type'=>'functionhook', 'file' => '', 'status' => 'on', 'install' => false);
    $hook_info=array('author'=>'', 'email'=>'','link'=>'http://localhost','license'=>'GNU','desciption'=>'','version'=> '','create'=> '','cover'=> '');
    if(!empty($hooks)){
        $row = "hrow1";
        echo "<table class='htable' width='100%'><tr><th colspan='2'>{$main->lang['title']}</th><th width='80'>{$main->lang['status']}</th><th width='70'>{$main->lang['functions']}</th></tr>";
        foreach($hooks as $k => $h){
            foreach ($hook_key as $kh => $vh) {if(!isset($h[$kh])) $h[$kh]=$vh;}
            if(!empty($h['info'])) foreach ($hook_info as $kh => $vh) {if(!isset($h['info'][$kh])) $h['info'][$kh]=$vh;}
            //BUTTONS
            $install=(empty($h['install']) or $h['install']==false or !file_exists("hooks/{$k}/{$k}.update.php"))?"<a title='{$main->lang['install']}' href='{$adminfile}?module={$main->module}&amp;do=install&amp;id={$k}' class='admino ico_install pixel".((!empty($h['install']) AND $h['install']==true OR !file_exists("hooks/{$k}/{$k}.install.php"))?' ico_disabled' : '')."'></a>":"<a title='{$main->lang['update_db']}' href='{$adminfile}?module={$main->module}&amp;do=update&amp;id={$k}' class='admino ico_update pixel'></a>";
            $op = "<div style='padding: 0 0 4px 0;'>".
                    "<a onclick=\"return hooks_info(this, '{$adminfile}?module={$main->module}&amp;do=info&amp;id={$k}');\" title='{$main->lang['info']}' href='#' class='admino ico_info pixel".((empty($h['info']))?' ico_disabled' : '')."'></a>".
                    "<a title='{$main->lang['pack']}' href='{$adminfile}?module={$main->module}&amp;do=pack&amp;id={$k}' class='admino ico_load pixel'></a>".
                    "<a onclick=\"update_ajax('{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$k}', 'ajax_content', '{$main->lang['realdelete']}'); return false;\" title='{$main->lang['delete']}' href='#' class='admino ico_delete pixel'></a>".
                "</div><div>".
                    $install.
                    //"<a title='{$main->lang['install']}' href='{$adminfile}?module={$main->module}&amp;do=install&amp;id={$k}' class='admino ico_install pixel".((!empty($h['install']) AND $h['install']==true OR !file_exists("hooks/{$k}/{$k}.install.php"))?' ico_disabled' : '')."'></a>".
                    "<a title='{$main->lang['config']}' href='{$adminfile}?module={$main->module}&amp;do=config&amp;id={$k}' class='admino ico_config pixel".((!file_exists("hooks/{$k}/{$k}.config.php") OR $h['status']!=ENABLED OR $h['install']!=true)?' ico_disabled' : '')."'></a>".
                    "<a title='{$main->lang['uninstall']}' href='{$adminfile}?module={$main->module}&amp;do=uninstall&amp;id={$k}' class='admino ico_uninstall pixel".((!empty($h['install']) AND $h['install']!=true OR !file_exists("hooks/{$k}/{$k}.uninstall.php"))?' ico_disabled' : '')."'></a>".
                "</div>";
            //END BUTTONS
            
            $tcolor = (($h['type']=='plugin' AND !file_exists("hooks/{$k}")) OR ($h['type']!='plugin' AND !file_exists("hooks/{$h['file']}"))) ? ' style="color: #d74848;"' : '';
            echo "<tr class='{$row}".(($h['status']=='')?"_warn":"")."'>
                <td align='center' width='40'><img style='border-radius: 3px;' src='".((!empty($h['info']) AND !empty($h['info']['cover']) AND file_exists("hooks/{$k}/{$h['info']['cover']}"))?"hooks/{$k}/{$h['info']['cover']}":($h['type']=='hook'?"includes/images/hook.png":"includes/images/filehook.png"))."' alt='' /></td>
                <td>".(!empty($h['info']) ? "<a{$tcolor} target='_blank' href='engine.php?do=redirect&amp;url=".urlencode($h['info']['link'])."'>{$h['title']}</a>": "<span{$tcolor}>{$h['title']}</span>")."".(!empty($h['info']) ? "<div style='color: #aaaaaa;'>".cut_text($h['info']['description'], 8)."</div>" : '')."</td>
                <td align='center' id='onoff_".str_replace('.', '', $k)."' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$k}', 'onoff_".str_replace('.', '', $k)."')\">".($h['status']==ENABLED ? $main->lang['on'] : $main->lang['off'])."</td>
                <td align='center'>{$op}</td>
            </tr>";
            $row = ($row=='hrow1') ? "hrow2" : "hrow1";
        }
        echo "</table>";
    } else info($main->lang['noinfo']);
    
}

/**
 * Возвращает информацию из файла плагина
 *
 * @param $filename
 *
 * @return array
 */
function file_hook_info($filename){
   global $main, $revision;
   if(hook_check(__FUNCTION__)) return hook();
   $ret = array(); $msg=""; 
   $c = file_get_contents($filename);
   $a = explode('----------------------------------------------------------------------', $c);
   if(count($a)>=3){
      preg_match_all('/\*\s@([a-z]*)\s(.*?)\n/is', $a[1], $m);
      $info = array();
      for($i=0;$i<count($m[2]);$i++){
         $m[2][$i] = trim($m[2][$i]);
         $info[$m[1][$i]] = $m[2][$i];
      }
      $ret = array(
         'title' => !empty($info['name']) ? $info['name'] : '',
         'type' => !empty($info['filetype']) ? $info['filetype'] : '',
         'status' => 'on',
         'install' => true,
         'minVersion' => !empty($info['minVersion']) ? $info['minVersion'] : '',
         'maxVersion' => !empty($info['maxVersion']) ? $info['maxVersion'] : '',
         'updateLink' => !empty($info['updateLink']) ? $info['updateLink'] : '',
         'info' => array(
            'author' => !empty($info['author']) ? $info['author'] : '',
            'email' => !empty($info['email']) ? $info['email'] : '',
            'link' => !empty($info['link']) ? $info['link'] : '',
            'license' => !empty($info['license']) ? $info['license'] : '',
            'description' => !empty($info['description']) ? $info['description'] : '',
            'version' => !empty($info['version']) ? $info['version'] : '',
            'create' => !empty($info['create']) ? $info['create'] : '',
            'cover' => !empty($info['cover']) ? $info['cover'] : '',
            'logo'  => !empty($info['logo']) ? $info['logo'] : '',
         )
      );
      if(!empty($ret['title']) AND !empty($ret['type']) AND $ret['type']=='plugin' AND
         !empty($ret['info']) AND !empty($ret['info']['cover']) AND !empty($ret['info']['logo']) AND
         !empty($ret['minVersion']) AND !empty($ret['maxVersion'])) {
         if($ret['maxVersion']=='*') $ret['maxVersion'] = '10000';
         if(!($ret['minVersion']<=$revision AND $ret['maxVersion']>=$revision)) $msg = $main->lang['plugin_bad_version'];
      } else $msg = $main->lang['plugin_bad_data'];
   } else $msg = $main->lang['plugin_bad_data'];
   return array($msg, $ret);
}

/**
 * Добавляет в список плагинов
 *
 * @param mixed  $plagin
 * @param mixed  $infoplagin
 * @param string $path
 *
 * @return mixed
 */
function plagin_append_hooks($plagin, $infoplagin, $path = ""){
   global $main, $hooks;
   if(hook_check(__FUNCTION__)) return hook();
   if(empty($hooks[$plagin])){
      if(empty($path)) $path = "hooks/{$plagin}";
      $filename="{$path}/{$plagin}.install.php";
      if(file_exists($filename)) {
         $infoplagin['install']=false;
         $infoplagin['status']='off';
      }
      $hooks[$plagin] = $infoplagin;
   }
}

function hook_on_off(){
global $hooks, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($hooks[$_GET['id']])){
        $h = &$hooks[$_GET['id']];
        if($h['status']==ENABLED OR (isset($h['install']) AND $h['install']==false)){
            $h['status'] = '';
            echo $main->lang['off'];
            echo "<script type='text/javascript'>node = document.getElementById('onoff_".str_replace('.', '', $_GET['id'])."'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
        } else {
            $h['status'] = ENABLED;
            echo $main->lang['on'];
            echo "<script type='text/javascript'>node = document.getElementById('onoff_".str_replace('.', '', $_GET['id'])."'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
        }
        save_hook_config($hooks);
    }
    kr_exit();
}

function hook_config(){
global $main, $adminfile, $hooks;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($hooks[$_GET['id']]) AND $hooks[$_GET['id']]['install']==true AND $hooks[$_GET['id']]['status']==ENABLED){
        if(file_exists("hooks/{$_GET['id']}/{$_GET['id']}.config.php")) {
            echo get_php_content("hooks/{$_GET['id']}/{$_GET['id']}.config.php");
        }
    }
}

function save_hook_config($config, $var = '$hooks'){
global $copyright_file, $hooks;
    if(hook_check(__FUNCTION__)) return hook();    
    main::init_function('sources');
    $max_legth_var = max_length_value($hooks)+4;
    $string = "{$copyright_file}global {$var};\n{$var} = array(\n";
    foreach($config as $k=>$v){
        $space = create_space($max_legth_var-mb_strlen($k));
        $string .= "    '{$k}'{$space} => ".str_replace(
            array("',\n  ", "array (\n  ", ",\n)", "\n  array(  ", "\n  'info'", "', )", ",\n  '"), 
            array("', ", "array(", ")", 'array(', " 'info'", "')", ", '"), 
        var_export($v, true)).",\n";
    }
    $string = mb_substr($string, 0, mb_strlen($string)-2);
    $string .= "\n);\n".'?'.'>';
    $file_link = 'includes/config/config_hooks.php';
    if(is_writable($file_link)){
        $file = fopen($file_link, "w");
        fputs ($file, $string);
        fclose ($file);
    }
}

function hook_delete(){
global $main, $hooks;
    if(hook_check(__FUNCTION__)) return hook();
    $plugin = $_GET['id'];
    if(isset($hooks[$plugin])) {
        if(file_exists("hooks/{$plugin}/{$plugin}.uninstall.php"))  hook_uninstall(false);
        $h = $hooks[$plugin];
        unset($hooks[$plugin]);
        if($h['type'] == 'plugin' AND file_exists("hooks/{$plugin}")) remove_dir("hooks/{$plugin}");
        if(($h['type'] == 'file' OR $h['type'] == 'hook') AND file_exists("hooks/{$h['file']}")) unlink("hooks/{$h['file']}");
        save_hook_config($hooks);
    }
    !is_ajax() ? redirect(MODULE) : main_hooks();
}

function hook_install(){
global $hooks;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($hooks[$_GET['id']])) {
        $hooks[$_GET['id']]['install'] = true;
        $hooks[$_GET['id']]['status'] = 'on';
        require_once "hooks/{$_GET['id']}/{$_GET['id']}.install.php";
        save_hook_config($hooks);
    }
    redirect(MODULE);
}


function hook_update(){
global $hooks;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($hooks[$_GET['id']]) AND $hooks[$_GET['id']]['install']) {
        require_once "hooks/{$_GET['id']}/{$_GET['id']}.update.php";
    }
    redirect(MODULE);
}

function hook_uninstall($redirect = true){
global $hooks;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($hooks[$_GET['id']])) {
        $hooks[$_GET['id']]['install'] = false;
        $hooks[$_GET['id']]['status'] = '';
        require_once "hooks/{$_GET['id']}/{$_GET['id']}.uninstall.php";
        save_hook_config($hooks);
    }
    if($redirect) redirect(MODULE);
}

function hook_info(){
global $main, $hooks;
    if(hook_check(__FUNCTION__)) return hook();
    if(!empty($hooks[$_GET['id']]) AND !empty($hooks[$_GET['id']]['info'])){
        $hi = $hooks[$_GET['id']]['info'];
        main::init_function('get_domain');
        $_arr_a = explode(',', $hi['author']);
        $_arr_e = explode(',', $hi['email']);
        $author = array();
        for($i=0;$i<count($_arr_a);$i++) $author[] = (!empty($_arr_e[$i])) ? "<a href='mailto:{$_arr_e[$i]}'>{$_arr_a[$i]}</a>" : $_arr_a[$i];
        
        echo json_encode(array(
            'content' => array(
                'title'         => $hooks[$_GET['id']]['title'],
                'authors'       => implode(', ', $author),
                'author'        => $_arr_a[0],
                'email'         => $_arr_a[0],
                'link'          => !empty($hi['link']) ? "<a target='_blank' href='{$hi['link']}'>".get_domain($hi['link'])."</a>" : '',
                'license'       => $hi['license'],
                'description'   => $hi['description'],
                'version'       => $hi['version'],
                'create'        => $hi['create'],
                'cover'         => !empty($hi['cover'])?$hi['cover']:'',
                'path'          => $hooks[$_GET['id']]['type']=='plugin' ? "hooks/{$_GET['id']}/" : "hooks/{$hooks[$_GET['id']]['file']}",
                'image'         => (!empty($hi['cover']) AND file_exists("hooks/{$_GET['id']}/{$hi['cover']}")) ? "<img style='border-radius: 3px;' src='hooks/{$_GET['id']}/{$hi['cover']}' alt='' />" : '',
                'supported'     => "{$main->lang['min']} <b>r{$hooks[$_GET['id']]['minVersion']}</b>&nbsp;-&nbsp;{$main->lang['max']} <b>".($hooks[$_GET['id']]['maxVersion']=='*'?'-':"r{$hooks[$_GET['id']]['maxVersion']}")."</b>",
                'mix'           => $hooks[$_GET['id']]['minVersion'],
                'max'           => $hooks[$_GET['id']]['maxVersion'],
                'lang'          => array(
                    'author'        => $main->lang['author'],
                    'homepage'      => $main->lang['homepage'],
                    'version'       => $main->lang['version'],
                    'description'   => $main->lang['descript'],
                    'date'          => $main->lang['date'],
                    'window_title'  => $main->lang['info'],
                    'license'       => $main->lang['license'],
                    'path'          => $main->lang['path'],
                    'supported'     => $main->lang['supported'],
                    'min'           => $main->lang['min'],
                    'max'           => $main->lang['max'],
                )
            ),
            'status'  => 'ok',
            'message' => '',
        ));
    } else echo json_encode(array(
        'content' => array(),
        'status'  => 'error',
        'message' => $main->lang['noinfo'],
    ));
    kr_exit();
}

function hook_pack(){
global $hooks, $MIME;
    if(hook_check(__FUNCTION__)) return hook();
    main::inited('function.zip', 'class.download');
    if(isset($hooks[$_GET['id']])){
        $h = $hooks[$_GET['id']];
        if($h['type']=='plugin'){
            $f = "uploads/tmpfiles/{$_GET['id']}.plugin.zip";
            $p = "hooks/{$_GET['id']}";
        }elseif($h['type']=='file'){
            $f = "uploads/tmpfiles/{$_GET['id']}.zip";
            $p = "hooks/{$h['file']}";
        } else {
            $f = "uploads/tmpfiles/{$_GET['id']}.zip";
            $p = "hooks/{$h['file']}";
        }
        if(zip_create($f, $p)){
            header($_SERVER['SERVER_PROTOCOL']." 200 OK");
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Type: application/zip");
            header('Content-Disposition: attachment; filename="'.basename($f).'";');
            header("Content-Transfer-Encoding: binary");
            if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit(0);
            else @ini_set('max_execution_time', 0);
            echo file_get_contents($f);
            if(file_exists($f)) unlink($f);
        }
    }
    kr_exit();
}

function hook_add($msg=''){
global $main, $adminfile;
    if(is_writable('hooks/')){
        if(!empty($msg)) warning($msg);
        echo "<form action='{$adminfile}?module={$main->module}&do=save_add' enctype='multipart/form-data' method='post''><table width='100%' class='form' align='center'>
            <tr class='row_tr'>
                <td class='form_text'>{$main->lang['upload']}</td><td class='form_input'><input type='file' name='pluginfile' accept='application/x-compressed,application/x-zip-compressed,application/zip,multipart/x-zip' /></td>
            </tr>
            <tr><td colspan='2' align='center'>".send_button()."</td></tr>
        </table></form>";
    } else warning($main->lang['hook_writable']);
}

function hook_save_add(){
global $main, $hooks, $adminfile, $revision;
    $msg = '';
    if(file_exists('uploads/tmpfiles/extract_plugin')) remove_dir("uploads/tmpfiles/extract_plugin");
    main::inited('class.uploader', 'function.zip', 'function.copy');
    if(isset($_FILES["pluginfile"]) AND !empty($_FILES["pluginfile"]['name'])){
        //Создаем новое имя файла
        $new_name = get_name_file(cyr2lat($_FILES["pluginfile"]['name'],true));
        //Создаем массив параметров для загрузки файлов
        $attach = new upload(array(
            'dir'    => 'uploads/tmpfiles/',
            'file'   => $_FILES["pluginfile"],
            'size'   => 1024,
            'type'   => array('zip'),
            'name'   => $new_name
        ));
        if($attach->error) $msg = $attach->get_error_msg();
    } else $msg = $main->lang['plugin_no_load'];
    if(empty($msg)){
        $e = "uploads/tmpfiles/extract_plugin";
        if(!file_exists($e)) mkdir($e);
        zip_extract('uploads/tmpfiles/'.$attach->file, $e.'/');
        
        if(file_exists('uploads/tmpfiles/'.$attach->file)) unlink('uploads/tmpfiles/'.$attach->file);
        $count_d = 0;
        foreach(scandir($e) as $f) if(!in_array($f, array('.', '..')) AND is_dir($e.'/'.$f)) $count_d++;
        $count_a = dir_file_count($e);
        
        if($count_d==1 OR $count_a==1){
            foreach (glob("uploads/tmpfiles/extract_plugin/*") as $filename) {
                
                if(is_file($filename)) $plugin_name = str_replace('.php', '', basename($filename));
                else $plugin_name = basename($filename);
                
                if(is_dir($filename)){
                    if(file_exists($filename.'/'.$plugin_name.'.plugin.php')){
                        list($msg, $plagin_info)=file_hook_info($filename.'/'.$plugin_name.'.plugin.php');
                        if(empty($msg)) plagin_append_hooks($plugin_name, $plagin_info, $filename);
                    } else $msg = $main->lang['plugin_no_search'];
                } else {
                    $c = file_get_contents($filename);
                    $a = explode('----------------------------------------------------------------------', $c);
                    if(count($a)>=3){
                        preg_match_all('/\*\s@([a-z]*)\s(.*?)\n/is', $a[1], $m);
                        $info = array();
                        for($i=0;$i<count($m[2]);$i++){
                            $m[2][$i] = trim($m[2][$i]);
                            $info[$m[1][$i]] = $m[2][$i];
                        }
                        $pn = $plugin_name.'.'.$info['filetype'];
                        $hooks[$pn] = array(
                            'title' => !empty($info['name']) ? $info['name'] : '',
                            'type' => !empty($info['filetype']) ? $info['filetype'] : '',
                            'file' => basename($filename),
                            'status' => 'on',
                            'minVersion' => !empty($info['minVersion']) ? $info['minVersion'] : '',
                            'maxVersion' => !empty($info['maxVersion']) ? $info['maxVersion'] : '',
                            'updateLink' => !empty($info['updateLink']) ? $info['updateLink'] : '',
                            'info' => array(
                                'author' => !empty($info['author']) ? $info['author'] : '',
                                'email' => !empty($info['email']) ? $info['email'] : '',
                                'link' => !empty($info['link']) ? $info['link'] : '',
                                'license' => !empty($info['license']) ? $info['license'] : '',
                                'description' => !empty($info['description']) ? $info['description'] : '',
                                'version' => !empty($info['version']) ? $info['version'] : '',
                                'create' => !empty($info['create']) ? $info['create'] : '',
                            )
                        );
                        if(!empty($hooks[$pn]['title']) AND 
                           !empty($hooks[$pn]['type']) AND 
                           ($hooks[$pn]['type']=='hook' OR $hooks[$pn]['type']=='file') AND
                           !empty($hooks[$pn]['info']) AND
                           !empty($hooks[$pn]['minVersion']) AND
                           !empty($hooks[$pn]['maxVersion'])
                        ) {
                            if(($hooks[$pn]['type']=='file' AND !empty($info['replaceFile'])) OR $hooks[$pn]['type']=='hook') {
                                if($hooks[$pn]['type']=='file') $hooks[$pn]['replace_file'] = $info['replaceFile'];
                                if($hooks[$pn]['maxVersion']=='*') $hooks[$pn]['maxVersion'] = '10000';
                                if(!($hooks[$pn]['minVersion']<=$revision AND $hooks[$pn]['maxVersion']>=$revision)) $msg = $main->lang['plugin_bad_version'];
                            } else $msg = $main->lang['plugin_bad_data'];
                        } else $msg = $main->lang['plugin_bad_data'];
                    } else $msg = $main->lang['plugin_bad_data'];
                }
                if(empty($msg)) {
                    //Проверка наличия
                    if(is_dir($filename)) {
                        if(!file_exists('hooks/'.$plugin_name)) rcopy($filename, 'hooks/'.$plugin_name);
                        else $msg = $main->lang['plugin_exists'];
                    } else {
                        if(!file_exists('hooks/'.$plugin_name)) copy($filename, 'hooks/'.basename($filename));
                        else $msg = $main->lang['hook_exists'];
                    }
                    if(empty($msg)) save_hook_config($hooks);
                }
            }
        } else $msg = $main->lang['plugin_error_folder'];
    } else $msg = $main->lang['plugin_unknown'];
    
    remove_dir("uploads/tmpfiles/extract_plugin");
    if(!empty($msg)) hook_add($msg);
    else redirect(MODULE);
}

/**
* Сканирует каталог с плагинами на предмет неучтенных плагинов
* 
*/
function hook_search(){
   global $main, $hooks, $adminfile;
   if(hook_check(__FUNCTION__)) return hook();
   $dir = opendir("hooks");
   while(($file = readdir($dir))){
      if(!preg_match('/\./', $file)){
         if(empty($hooks[$file]) and $file!='autoload'){
            $filename="hooks/{$file}/{$file}.plugin.php";
            if(file_exists($filename)){
               list($msg, $info) = file_hook_info($filename);
               if(empty($msg)) plagin_append_hooks($file, $info);
            }
         }
      }
   }
   closedir($dir);
   save_hook_config($hooks);
   redirect(MODULE);
}
function switch_admin_hooks(){
   global $main, $break_load;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do']) AND $break_load==false){
      switch($_GET['do']){
         case 'on_off': hook_on_off(); break;//
         case 'config': hook_config(); break;//
         case 'delete': hook_delete(); break;//
         case 'install': hook_install(); break;//
         case 'update': hook_update(); break;//
         case 'uninstall': hook_uninstall(); break;//
         case 'info': hook_info(); break;
         case 'pack': hook_pack(); break;//
         case 'add': hook_add(); break;
         case 'save_add': hook_save_add(); break;
         case 'search': hook_search(); break;
         default: main_hooks(); break;
      }
   } elseif($break_load==false) main_hooks();
}
switch_admin_hooks();
