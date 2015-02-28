<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main;
$navi = navi(array(), false, false);
bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));
main::required("modules/{$main->module}/globals.php");
function albom_foto_category($row){
   global $albom, $main;
   if(hook_check(__FUNCTION__)) return hook();
   return "<td>
   <div class='boxgrid captionfull' style='width: {$albom['miniature_width']}px; height: ".($albom['miniature_height']-2)."px'>
   <a href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($row['cat_id'], $row['cid'])))."' title='{$row['description']}'>
   ".(!empty($row['image'])?"<img src='{$albom['directory']}category/{$row['image']}' alt='{$row['description']}' />" : "<img src='includes/images/pixel.gif' style='width: {$albom['miniature_width']}px; height: ".($albom['miniature_height']-2)."px; background: url(\"".TEMPLATE_PATH."{$main->tpl}/images/thumbs2_albom.png\") no-repeat scroll center center transparent;' alt='' />")."
   </a>
   </div>".($albom['show_name_cat']==ENABLED?"<div><b>{$row['title']}</b></div>":'')."
   </td>";
}

function albom_foto_record($row){
   global $albom, $main;
   if(hook_check(__FUNCTION__)) return hook();
   $href="{$albom['directory']}{$row['time']}/{$row['image']}";
   return "<td>
   <div class='boxgrid captionfull' style='width: ".($albom['miniature_width']-1)."px; height: ".($albom['miniature_height']-2)."px'>
   <a class='zoom' href='".$href."' title='{$row['description']}'>
   <img src='{$albom['directory']}{$row['time']}/mini-{$row['image']}' alt='{$row['description']}' />
   </a>
   </div>".($albom['show_name_photo']==ENABLED?"<div><b>{$row['title']}</b></div>":'')."
   </td>";
}

function main_albom(){
global $main, $albom, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    main::add2script("includes/javascript/jquery/colorbox/jquery.colorbox-min.js");
    main::add2link("includes/javascript/jquery/colorbox/tpl/5/colorbox.css");
    $list = array('cat' => '', 'photo' => '');
    $content = array('cat' => '', 'photo' => '');
    $status = 0;
    if(isset($_GET['do']) AND ($_GET['do']=='categoryes' OR $_GET['do']=='photos')) $main->parse_rewrite(array('module', 'do', 'page'));
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * $albom['cols']*$albom['rows'];
    if((isset($_GET['do']) AND $_GET['do']!='photos') OR !isset($_GET['do'])) {
        if(isset($_GET['do']) AND $_GET['do']=='category') $parent = $main->rewrite_id ? "c.cat_id='{$_GET['id']}'" : "c.cid='{$_GET['id']}'";
        else $parent = "c.cid IS NULL";
        $result = $main->db->sql_query("SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level, c.cid AS parent_id, c.cat_id AS parent_cat{FIELDS} FROM ".CAT." AS t LEFT JOIN ".CAT." AS c ON(SUBSTR(t.tree,1,LENGTH(t.tree)-2)=c.tree){TABLES} WHERE t.module='{$main->module}' AND {$parent}{WHERES} ORDER BY t.cid ASC LIMIT {$offset}, ".($albom['cols']*$albom['rows']),__FUNCTION__);
        if(isset($_GET['do']) AND $_GET['do']=='category'){
            $c = $main->db->sql_query("SELECT title, cat_id, cid FROM ".CAT." WHERE ".((!$main->rewrite_id) ? "cid='{$_GET['id']}'" : "cat_id='{$_GET['id']}'")." LIMIT 1");
            if($main->db->sql_numrows($c)>0){
                list($_c, $_cat_id, $_cid) = $main->db->sql_fetchrow($c);
            } else $_c = '';
            bcrumb::add($_c);
        } else $_c = '';
        $cat_num = $main->db->sql_numrows($result);
        if($cat_num>0){
            $i=0; $status = 1;
            $content['cat'] = "<table width='100%' class='table_showcat'>";
            while(($row = $main->db->sql_fetchrow($result))) {
                if($i==0) $content['cat'] .= "<tr>";
                $content['cat'] .= albom_foto_category($row);
                if($i==$albom['cols']-1){
                    $content['cat'] .= "</tr>";
                    $i=-1;
                }
                $i++;
            }
            if($i<$albom['cols'] AND $i!=0) { for($i=$i;$i<$albom['cols'];$i++) $content['cat'] .= "<td><div class='boxgrid captionfull' style='width: {$albom['miniature_width']}px; height: ".($albom['miniature_height']-2)."px'><img src='includes/images/pixel.gif' alt='' width='{$albom['miniature_width']}' height='{$albom['miniature_height']}' style='background: url(".TEMPLATE_PATH."{$main->tpl}/images/thumbs_albom.png) center center no-repeat;' /></div>".($albom['show_name_cat']==ENABLED?"<div><b> </b></div>":'')."</td>"; $content['cat'] .= "</tr>"; }
            $content['cat'] .= "</table>";
        }
        if($cat_num==$albom['cols']*$albom['rows'] OR isset($_GET['page'])){
            //Получаем общее количество публикаций
            $numrows = $main->db->sql_numrows($main->db->sql_query("SELECT t.*, ROUND(LENGTH(t.tree)/2) AS level, c.cid AS parent_id, c.cat_id AS parent_cat FROM ".CAT." AS t LEFT JOIN ".CAT." AS c ON(SUBSTR(t.tree,1,LENGTH(t.tree)-2)=c.tree) WHERE t.module='{$main->module}' AND {$parent}"));
            //Если количество публикаций больше чем количество публикаций на страницу
            if($numrows>$albom['cols']*$albom['rows']){
                //В зависимости от типа вывода создаем страницы
                $list['cat'] = open(true).pages($numrows, $albom['cols']*$albom['rows'], array('module' => $main->module, 'do' => 'categoryes'), false, true, array(), false, 'right').close(true);
            }
        }
    }
    if((isset($_GET['do']) AND $_GET['do']!='categoryes') OR !isset($_GET['do'])) {
        if(isset($_GET['do']) AND $_GET['do']=='category') {
            $_c= (!$main->rewrite_id) ? "cid='{$_GET['id']}'" : "cat_id='{$_GET['id']}'";
            $cat_info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".CAT." WHERE module='{$main->module}' AND {$_c} LIMIT 1"));
            $sort = isset($albom['sort_type_publications'])? $albom['sort_type_publications']: "ASC";
            $photo = $main->db->sql_query("SELECT a.*{FIELDS} FROM ".ALBOM." AS a{TABLES} WHERE a.cid='{$cat_info['cid']}'{WHERES} ORDER BY a.id {$sort} LIMIT {$offset}, ".($albom['cols']*$albom['rows'])."",__FUNCTION__."2");
        } else if(!isset($_GET['do']) OR (isset($_GET['do']) AND $_GET['do']=='photos')) $photo = $main->db->sql_query("SELECT a.*{FIELDS} FROM ".ALBOM." AS a{TABLES} WHERE a.cid='0'{WHERES} ORDER BY a.id ASC LIMIT {$offset}, ".($albom['cols']*$albom['rows'])."",__FUNCTION__."2");
        $phot_num = $main->db->sql_numrows($photo);
        if(isset($cat_info)) add_meta_value($cat_info['title']);
        if($phot_num>0){
            $i=0; $status = 1;
            $content['photo'] = "<table width='100%' class='table_showphoto'>";
            while(($row = $main->db->sql_fetchrow($photo))) {
                if($i==0) $content['photo'] .= "<tr>";
                $href="{$albom['directory']}{$row['time']}/{$row['image']}";
                $content['photo'] .= albom_foto_record($row);
                if($i==$albom['cols']-1){
                    $content['photo'] .= "</tr>";
                    $i=-1;
                }
                $i++;
            }
            if($i<$albom['cols'] AND $i!=0){ for($i=$i;$i<$albom['cols'];$i++) $content['photo'] .=  "<td><div class='boxgrid captionfull' style='width: {$albom['miniature_width']}px; height: ".($albom['miniature_height']-2)."px'><img src='includes/images/pixel.gif' alt='' width='{$albom['miniature_width']}' height='{$albom['miniature_height']}' style='background: url(".TEMPLATE_PATH."{$main->tpl}/images/thumbs_albom.png) center center no-repeat;' /></div>".($albom['show_name_photo']==ENABLED?"<div><b>&nbsp;</b></div>":'')."</td>"; $content['photo'] .= "</tr>";}
            $content['photo'] .= "</table>";
            if($phot_num==$albom['cols']*$albom['rows'] OR isset($_GET['page'])){
                //Получаем общее количество публикаций
                if(!isset($cat_info)) list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".ALBOM." WHERE cid='0'"));
                else list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".ALBOM." WHERE cid='{$cat_info['cid']}'"));
                //Если количество публикаций больше чем количество публикаций на страницу
                if($numrows>$albom['cols']*$albom['rows']){
                    //В зависимости от типа вывода создаем страницы
                    if(isset($_GET['do']) AND $_GET['do']='category') $list['photo'] = open(true).pages($numrows, $albom['cols']*$albom['rows'], array('module' => $main->module, 'do' => 'category', 'id' => isset($_GET['id'])?$_GET['id']:0), false, true, array(), false, 'right').close(true);
                    else $list['photo'] = open(true).pages($numrows, $albom['cols']*$albom['rows'], array('module' => $main->module, 'do' => 'photos'), false, true, array(), false, 'right').close(true);
                }
            }
        }
    }
    if($status!=0) {
        if(!empty($content['cat'])) echo str_replace(array('$content', '$title'), array($content['cat'], $main->lang['categoryes']), file_get_contents("".TEMPLATE_PATH."{$main->tpl}/albom/category.tpl")).$list['cat'];
        $_desc = str_replace(array('$content', '$lang_desc'), array("<span class='descript_albom'>".(isset($cat_info)?(!empty($cat_info['description'])?$cat_info['description']:"&nbsp;"):'-')."</span>", $main->lang['descript']), file_get_contents(TEMPLATE_PATH."{$main->tpl}/albom/cat_info.tpl"));
        if(!empty($content['photo'])) echo str_replace(array('$content', '$title', '$description'), array($content['photo'], $main->lang['photos'], $albom['show_descript_cat']==ENABLED?$_desc:''), file_get_contents(TEMPLATE_PATH."{$main->tpl}/albom/photos.tpl")).$list['photo'];
    } else info($main->lang['noinfo']);
    ?>
    <script type="text/javascript">
    //<![CDATA[
      $('a.zoom').colorbox({rel:'zoom', transition:"fade",maxHeight:window.innerHeight,maxWidth:window.innerWidth});
    //]]>
    </script>
    <?php
    
}

function _search_next_photo(&$array, $this){
    if(hook_check(__FUNCTION__)) return hook();
    $_tmp = array('next' => -2, 'first' => -1);
    foreach($array as $key => $value){
        if($_tmp['first']==-1) $_tmp['first'] = $key;
        if($_tmp['next']==-1) $_tmp['next'] = $key;
        if($key==$this) $_tmp['next'] = -1;
    }
    if($_tmp['next']==-1) $_tmp['next'] = $_tmp['first'];
    return $_tmp['next'];
}

function _search_prev_photo(&$array, $this){
    if(hook_check(__FUNCTION__)) return hook();
    $_tmp = array('prev' => -1, 'last' => -1, 'stoped' => false);
    foreach($array as $key => $value){
        $_tmp['last'] = $key;
        if($key==$this) $_tmp['stoped'] = true;
        if($_tmp['stoped']==false) $_tmp['prev'] = $key;
    }
    if($_tmp['prev']==-1) $_tmp['prev'] = $_tmp['last'];
    return $_tmp['prev'];
}

function photo_albom($msg=""){
global $main, $albom, $template;
    if(hook_check(__FUNCTION__)) return hook();
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if($albom['comments']==ENABLED) require "includes/function/comments.php";
    $result = $main->db->sql_query("SELECT a.*,r.r_up,r.r_down,r.users{FIELDS} FROM ".ALBOM." AS a LEFT JOIN ".RATINGS." AS r ON (r.module='albom' and r.idm=a.id){TABLES} WHERE a.cid=(SELECT cid FROM ".ALBOM." WHERE id={$id}){WHERES} ORDER BY a.id ASC",__FUNCTION__);
    if($main->db->sql_numrows($result)>0){
        $images = array();
        main::init_function('rating');
        while($row = $main->db->sql_fetchrow($result)) $images[$row['id']] = $row;
        if(isset($images[$id])){
            if(isset($_POST['id'])) add_comment(ALBOM, $albom['comments_sort'], $albom['guests_comments'], 'photo');
            else {
                if($images[$id]['cid']!=0) {
                    $cat = $main->db->sql_fetchrow($main->db->sql_query("SELECT cid, cat_id, title FROM ".CAT." WHERE cid='{$images[$id]['cid']}'"));
                    bcrumb::add($cat['title'],$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($cat['cat_id'], $cat['cid']))));
                    bcrumb::add($images[$id]['title']);
                    add_meta_value($cat['title']);
                }
                add_meta_value($images[$id]['title']);
                $template->get_tpl('photo_info','photo_info');
                $pub = array(
                   'description'=>$albom['show_descript_photo']==ENABLED?"<span class='descript_albom'>{$images[$id]['description']}</span>":'',
                   'lang_desc'=>$albom['show_descript_photo']==ENABLED?$main->lang['descript'].':':'',
                );
                $pub = rating_modify_publisher($images[$id]['id'], 'albom', $images[$id], $pub, $albom['rating']==ENABLED);
                $template->set_tpl(hook_set_tpl($pub,__FUNCTION__),'photo_info',array('start' => '$pub[', 'end' => ']'));
                $_info = $template->tpl_create(true,'photo_info');
                $template->get_tpl('show','show');
                $pub = array(
                   'title'=>$images[$id]['title'],
                   'photo'=>"<a href='".$main->url(array('module' => $main->module, 'do' => 'photo', 'id' => _search_next_photo($images, $id)))."'><img src='{$albom['directory']}{$images[$id]['time']}/{$images[$id]['image']}' alt='{$images[$id]['title']}' /></a>",
                   'back' =>"<div class='prevslide'><a href='".$main->url(array('module' => $main->module, 'do' => 'photo', 'id' => _search_prev_photo($images, $id)))."' title='{$main->lang['prev_photo']}'><img alt='&lt;&lt;' src='includes/images/pixel.gif'/></a></div>",
                   'next' =>"<div class='nextslide'><a href='".$main->url(array('module' => $main->module, 'do' => 'photo', 'id' => _search_next_photo($images, $id)))."' title='{$main->lang['next_photo']}'><img alt='&gt;&gt;' src='includes/images/pixel.gif'/></a></div>",
                   'info' =>($albom['show_descript_photo']==ENABLED OR $albom['rating']==ENABLED)?$_info:'',
                );
                $template->set_tpl(hook_set_tpl($pub,__FUNCTION__),'show',array('start' => '$', 'end' => ''));
                $template->tpl_create(false,'show');
                //Выводим комментарии
                if($albom['comments']==ENABLED) comments(ALBOM, $images[$id]['id'], $images[$id]['id'], $albom['guests_comments'], $albom['comments_sort'], true, $msg, 'photo', $albom['rating']==ENABLED);
            }
        } else redirect(MODULE);
    } else redirect(MODULE);
}

function download_albom(){
global $main, $adminfile, $albom;
    if(hook_check(__FUNCTION__)) return hook();
    if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit(0);
    if(!SAFE_MODE AND function_exists('ignore_user_abort')) ignore_user_abort(1); 
    if(!defined('USER_FOLDER')){
        $new_name = cyr2lat($_POST['uname']);
        define("USER_FOLDER", "filedata-".$new_name);
    }
    main::init_class('uploader');
    
    if(isset($_FILES["Filedata"])){
        //Генерируем новое имя файла
        $new_name = cyr2lat(get_name_file($_FILES["Filedata"]['name'],true));
        if(!file_exists($albom['directory'].USER_FOLDER.'/')) mkdir($albom['directory'].USER_FOLDER.'/');
        //Создаем массив параметров для загрузки файлов
        $atrib = array(
            'dir'   => $albom['directory'].USER_FOLDER.'/',
            'file'  => $_FILES["Filedata"],
            'size'  => 1024000,
            'type'  => explode(",", $albom['photo_type']),
            'name'  => $new_name
        );   
        //Определяем тип загружаемого файла
        $exp = get_type_file(mb_strtolower($_FILES["Filedata"]['name']));
        $attach = new upload($atrib);
        if(!$attach->error){
            //Если файл загружен, подключаем класс работы с графикой
            main::init_class('graphics');
            //Создаем объект класса
            $param = array(
                'name'                  => $new_name.".".$exp,
                'new_name'              => $new_name,
                'width'                 => $albom['max_width'],
                'height'                => $albom['max_height'],
                'watermark'             => $main->config['mark_img'],
                'watermark_position'    => $main->config['mark'],
                'directory_image'       => $albom['directory'].USER_FOLDER.'/',
                'directory_new_image'   => $albom['directory'].USER_FOLDER.'/'
            );
            $graphics = new graphics($param);
            //Возвращаем информацию о изображении
            $size_image = getimagesize($albom['directory'].USER_FOLDER.'/'.$new_name.".".$exp);
            //Выполняем преобразования изображения
            if($size_image[0]>=$albom['miniature_width'] AND $size_image[1]>=$albom['miniature_height']){
                //if($size_image[0]>$albom['max_width'] OR $size_image[1]>$albom['max_height']) {
                    $graphics->resize_image();
                    @copy($albom['directory'].USER_FOLDER.'/mini-'.$new_name.".".$exp, $albom['directory'].USER_FOLDER.'/temp-'.$new_name.".".$exp);
                    @rename($albom['directory'].USER_FOLDER.'/'.$new_name.".".$exp, $albom['directory'].USER_FOLDER.'/orig-'.$new_name.".".$exp);
                    @rename($albom['directory'].USER_FOLDER.'/mini-'.$new_name.".".$exp, $albom['directory'].USER_FOLDER.'/'.$new_name.".".$exp);
                    $graphics->watermark();
                    //Выполняем налаживание водяного знака
                    $param['name'] = 'orig-'.$new_name.".".$exp;
                    $graphics->graphics($param);
                    $graphics->watermark();
                    $param['name'] = $new_name.".".$exp; $param['width'] = 50; $param['height'] = 50;
                    $graphics->graphics($param);
                    $graphics->resize_image();
                    @rename($albom['directory'].USER_FOLDER.'/mini-'.$new_name.".".$exp, $albom['directory'].USER_FOLDER.'/macro-'.$new_name.".".$exp);
                    
                //}
            } else @unlink($albom['directory'].USER_FOLDER.'/'.$new_name.".".$exp);
        }
    }
    kr_exit();
}

function download_cat_albom(){
global $main, $adminfile, $albom;
    if(hook_check(__FUNCTION__)) return hook();
    if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit(0);
    if(!SAFE_MODE AND function_exists('ignore_user_abort')) ignore_user_abort(1);
    main::init_class('uploader');
    if(isset($_FILES["Filedata"])){
        //Генерируем новое имя файла
        $new_name = strtolower(cyr2lat($_POST['uname']));
        if(!file_exists($albom['directory'].'category/')) mkdir($albom['directory'].'category/');
        //Создаем массив параметров для загрузки файлов
        $atrib = array(
            'dir'       => $albom['directory'].'category/',
            'file'      => $_FILES["Filedata"],
            'size'      => 1024000,
            'type'      => explode(",", $albom['photo_type']),
            'name'      => $new_name,
            'overwrite' => true
        );   
        //Определяем тип загружаемого файла
        $exp = get_type_file(mb_strtolower($_FILES["Filedata"]['name']));
        $attach = new upload($atrib);
        if(!$attach->error){
            //Если файл загружен, подключаем класс работы с графикой
            main::init_class('graphics');
            //Создаем объект класса
            $param = array(
                'name'                  => $new_name.".".$exp,
                'new_name'              => $new_name,
                'width'                 => $albom['max_width'],
                'height'                => $albom['max_height'],
                'watermark'             => $main->config['mark_img'],
                'watermark_position'    => $main->config['mark'],
                'directory_image'       => $albom['directory'].'category/',
                'directory_new_image'   => $albom['directory'].'category/'
            );
            $graphics = new graphics($param);
            if($albom['directory'].'category/'.$new_name.".".$exp!=$albom['directory'].'category/'.$new_name.'.png') {
                $graphics->img_convert($albom['directory'].'category/'.$new_name.".".$exp, $albom['directory'].'category/'.$new_name.'.png');
                unlink($albom['directory'].'category/'.$new_name.".".$exp);
            }
            //Возвращаем информацию о изображении
            $size_image = getimagesize($albom['directory'].'category/'.$new_name.'.png');
            $param['name'] =  $new_name.'.png';
            $graphics = new graphics($param);
            //Выполняем преобразования изображения
            if($size_image[0]>=$albom['miniature_width'] AND $size_image[1]>=$albom['miniature_height']){
                if($size_image[0]>$albom['max_width'] OR $size_image[1]>$albom['max_height']) {
                    $graphics->resize_image();
                    unlink($albom['directory']."category/{$new_name}.png");
                    rename($albom['directory']."category/mini-{$new_name}.png", $albom['directory'].'category/'.$new_name.'.png');
                }
            }
            
        }
    }
}
function switch_module_albom(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "download_photo": download_albom(); break;
         case "download_photo_cat": download_cat_albom(); break;
         case "category": main_albom(); break;
         case "categoryes": main_albom(); break;
         case "photos": main_albom(); break;
         case "photo":
         case "more": photo_albom(); break;
         default: kr_http_ereor_logs("404"); break;
      }
   } else main_albom();
}
switch_module_albom();
?>