<?php
   /**********************************************/
   /* Kasseler CMS: Content Management System    */
   /**********************************************/
   /*                                            */
   /* Copyright (c)2007-2012 by Igor Ognichenko  */
   /* http://www.kasseler-cms.net/               */
   /* глобальные функции для модуля albom        */
   /*                                            */
   /**********************************************/
   if (!defined('FUNC_FILE')) die('Access is limited');
   function global_add_albom(){
   global $main, $adminfile, $tpl_create, $albom;
       if(hook_check(__FUNCTION__)) return hook();
       main::init_function('get_mask_types');
       main::add2script("modules/{$main->module}/script.js");
       main::add2script("includes/javascript/jquery/jquery.swfupload.js");
       $action = defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&amp;do=save":$main->url(array('module' => $main->module, 'do' => 'save'));
       $arrlink=array('module' => $main->module, 'do' => 'resize_image');
       $resize_image = defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&do=resize_image":($main->mod_rewrite? $main->url($arrlink):$main->urljs($arrlink));
       echo "<form id='autocomplete' method='post' action='{$action}'>\n".
       "<table class='form' align='center' id='form_{$main->module}'>\n".
       "<tr><td class='form_text' style='width: 300px'>{$main->lang['photo']}:<br /><i>".str_replace('{SIZE}', "{$albom['miniature_width']}x{$albom['miniature_height']}", $main->lang['minimum_size'])."</i></td>
       <td class='form_input'>
           <div class='textwrapper'>
               <script type='text/javascript'>
               <!--
                   function update_upload(path){
                       location.href = '{$resize_image}';
                   }
                   setTimeout(function(){
                       KR_AJAX.swfupload({
                           upload_url: '/index.php?module={$main->module}&do=download_photo',
                           post_params: {
                               'PHPSESSID' : '".session_id()."',
                               'uname':'{$main->user['user_name']}',
                               'secID':(jsSecretID?jsSecretID:'')
                           },
                           file_size_limit : ".($albom['max_size']==""?"1024000":$albom['max_size']).",
                           file_types : '".get_mask_types($albom['photo_type'])."',
                           file_types_description : '".($albom['photo_type']!='*'?str_replace(",", ";", mb_strtoupper($albom['photo_type'])):'All')."',
                           file_upload_limit : 100,
                           button_image_url : '".$main->config['http_home_url']."includes/images/pixel.gif'
                       });
                   }, 700);
               // -->
               </script>".
               '<div id="Buttons" style="text-align:left;"><span id="UploadPhotos"><input type="button" id="Progress" /><i id="fAddPhotos"></i><input type="button" id="AddPhotos" value="'.$main->lang['upload'].'" /></span></div>'.
               "</div>
       </td></tr>\n".
       "</table>\n</form>";
   }
   
   function global_save_photos_albom(){
   global $main, $albom, $adminfile;
       if(hook_check(__FUNCTION__)) return hook();
       $patch = $albom['directory'].USER_FOLDER.'/';
       $temp = $orig = $macro = $images = array();
       if(($handle = opendir($patch))){
           while(false !== ($file = readdir($handle))){
               if($file!='.' AND $file!='..'){
                    if(preg_match('/temp-(.*)/', $file)) $temp[] = $file;
                    elseif(preg_match('/macro-(.*)/', $file)) $macro[] = $file;
                    elseif(preg_match('/orig-(.*)/', $file)) $orig[] = $file;
                    else $images[] = $file;
               }
           }
           closedir($handle);
       }
       if(isset($_SESSION['processed_image']) AND count($orig)==count($_SESSION['processed_image'])){
           $time = time();
           foreach($_SESSION['processed_image'] as $k=>$v){
               sql_insert(array(
                   'title'         => $v[0],
                   'cid'           => !empty($v[4])?$v[4]:0,
                   'description'   => $v[2],
                   'image'         => $v[3],
                   'time'          => $time,
               ), ALBOM);
           }
           rename($albom['directory'].USER_FOLDER, $albom['directory'].$time);
           $link=defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}":$main->url(array('module' => $main->module));
           redirect($link);
       } else global_resize_image_albom($main->lang['noprocesing']);
   }
   
   function global_resize_image_albom($msg=""){
   global $albom, $main, $tpl_create, $adminfile;
       if(hook_check(__FUNCTION__)) return hook();
       if(!is_ajax()){
           main::add2script("modules/{$main->module}/script.js");
           main::add2script('includes/javascript/jquery/jquery.Jcrop.min.js');
           if(!isset($_POST['send_processing'])) unset($_SESSION['processed_image']);
           main::add_css2head(".albom_edit{cursor:pointer;}");
       }
       $patch = $albom['directory'].USER_FOLDER.'/';
       $temp = $orig = $macro = $images = array();
       if(($handle = opendir($patch))){
           while(false !== ($file = readdir($handle))){
               if($file!='.' AND $file!='..'){
                    if(preg_match('/temp-(.*)/', $file)) $temp[] = $file;
                    elseif(preg_match('/macro-(.*)/', $file)) $macro[] = $file;
                    elseif(preg_match('/orig-(.*)/', $file)) $orig[] = $file;
                    else $images[] = $file;
               }
           }
           closedir($handle);
       }
       if(count($macro)>0){
           if(!empty($msg)) warning($msg);
           echo "<div id='update_process'><table width='100%' class='form' cellpadding='3'>";
           foreach($macro as $v){
               $im = str_replace('macro-', 'temp-', $v);
               $processiong = global_find_processiong($v);
               $no_info = "<span style='color: red;'>{$main->lang['no']}</span>";
               $info = array(
                   'title' => $processiong==-1 ? $no_info : (!empty($_SESSION['processed_image'][$processiong][0])?$_SESSION['processed_image'][$processiong][0]:$no_info),
                   'cid' => $processiong==-1 ? $no_info : (!empty($_SESSION['processed_image'][$processiong][1])?$_SESSION['processed_image'][$processiong][1]:$no_info),
                   'desc' => ($processiong==-1 OR empty($_SESSION['processed_image'][$processiong][2])) ? $no_info : $_SESSION['processed_image'][$processiong][2],
                   'processed' => $processiong!=-1 ? "<span style='color: green;'>{$main->lang['processed']}</span>" : "<span style='color: red;'>{$main->lang['notprocessed']}</span>",
               );
               $file_macro=str_replace('macro-', '', $v);
               $link_delete_photo=defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&amp;do=delete_photo&amp;file={$file_macro}":$main->url(array('module' => $main->module, 'do' => 'delete_photo','file'=>$file_macro));
               echo "<tr class='row_tr'>\n".
               "<td height='50' width='60'><a onclick=\"\$(this).colorbox();\" href='{$patch}{$im}'><img src='{$patch}{$v}' alt='' /></a></td>\n".
               "<td><span class='desc_albom'>{$main->lang['title']}: {$info['title']}<br />{$main->lang['category']}: {$info['cid']}<br />{$main->lang['descript']}: {$info['desc']}<br/></span></td>\n".
               "<td width='90' align='right'>{$info['processed']}</td>".
               "<td width='120' align='right'>
                   <a href='#' onclick=\"return processed_photo('{$albom['directory']}".USER_FOLDER."/', '".$file_macro."', {h:{$albom['miniature_height']}, w:{$albom['miniature_width']}});\">{$main->lang['edit']}</a><br />
                   <a href='{$link_delete_photo}' onclick=\"update_ajax(this.href, 'ajax_content', '{$main->lang['realdelete']}'); return false;\">{$main->lang['delete']}</a></td>".
               "</tr>\n";
           }
           echo "</table></div>";
           $form_action = defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&amp;do=save":$main->url(array('module' => $main->module, 'do' => 'save'));
           $link_crop_img = defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&amp;do=crop_img":$main->url(array('module' => $main->module, 'do' => 'crop_img'));
           $link_update_url = defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&amp;do=update_processed":$main->url(array('module' => $main->module, 'do' => 'update_processed'));
           echo (!is_ajax() OR (isset($_GET['do']) AND $_GET['do']=='delete_photo')) ? ("<form action='{$form_action}' method='post'><center><br /><input type='hidden' name='send_processing' value='true' />".send_button()."</center></form>".
           "<div id='cropHS' style='position:absolute; left:-10000px'>
               <table class='form' id='form_{$main->module}'>
                   <tr class='row_tr'><td class='form_text'>{$main->lang['photo_name']}:</td><td class='form_input' align='left'>".in_text('title', 'input_text')."</td></tr>
                   <tr class='row_tr'><td class='form_text'>{$main->lang['category']}:</td><td class='form_input' align='left'>".get_cat('', '', '', 'chzn-none ')."</td></tr>
                   <tr class='row_tr'><td class='form_text'>{$main->lang['descript']}:</td><td class='form_input' align='left'>".in_area('description', 'textarea', 1)."</td></tr>
                   ".((!is_ajax() OR (isset($_GET['do']) AND $_GET['do']=='delete_photo'))?"<tr class='row_tr'><td colspan='2' align='center'><br /><img style='border: 1px #dfe5ea solid; height:400px;' id='crop_image' src='includes/images/pixel.gif' alt='' style='margin:0' /><br /><br /></td></tr>":'')."
               </table>            
           </div>".
           in_hide('crop_img', $link_crop_img).in_hide('updateUrl', $link_update_url)) : '';          
       } else info($main->lang['error_load_albom']);
   }
   
   function global_find_processiong($image){
       if(hook_check(__FUNCTION__)) return hook();
       $image = str_replace('temp-', '', basename($image));
       $image = str_replace('macro-', '', $image);
       $image = str_replace('mini-', '', $image);
       $image = str_replace('orig-', '', $image);
       if(isset($_SESSION['processed_image']) AND !empty($_SESSION['processed_image'])){
           foreach($_SESSION['processed_image'] as $k=>$v){
               if($v[3]==$image) return $k;
           }
           return -1;
       } else return -1;
   }
   
   function global_delete_photo_albom(){
   global $main, $albom, $adminfile;
       if(hook_check(__FUNCTION__)) return hook();
       $patch ="{$albom['directory']}".USER_FOLDER;
       if(file_exists("{$patch}/{$_GET['file']}")) unlink("{$patch}/{$_GET['file']}");
       if(file_exists("{$patch}/macro-{$_GET['file']}")) unlink("{$patch}/macro-{$_GET['file']}");
       if(file_exists("{$patch}/orig-{$_GET['file']}")) unlink("{$patch}/orig-{$_GET['file']}");
       if(file_exists("{$patch}/temp-{$_GET['file']}")) unlink("{$patch}/temp-{$_GET['file']}");
       if(file_exists("{$patch}/mini-{$_GET['file']}")) unlink("{$patch}/mini-{$_GET['file']}");
       if(!is_ajax()){
          $redirect = defined('ADMIN_FILE')?"{$adminfile}?module={$main->module}&amp;do=resize_image":$main->url(array('module' => $main->module, 'do' => 'resize_image'));
          redirect($redirect);
       }
       global_resize_image_albom();
   }
   
   function global_crop_image_albom(){
   global $main, $albom;
       if(hook_check(__FUNCTION__)) return hook();
       //'width' 'height' 'imageWidth' 'imageHeight' 'cropLeft' 'cropTop' 'image'
       $base_name = preg_replace('/(.*?)\?(.*)/si', '\\1', str_replace('temp-', '', basename($_POST['image'])));
       $patch = isset($_POST['type']) ? $albom['directory']."category/" : "{$albom['directory']}".USER_FOLDER."/"; 
       $exp = get_type_file($base_name); 
       if($exp=='png') $img_r = imagecreatefrompng($patch.$base_name);
       elseif($exp=='jpg' OR $exp=='jpeg') $img_r = imagecreatefromjpeg($patch.$base_name);
       elseif($exp=='gif') $img_r = imagecreatefromgif($patch.$base_name);
       $dst_r = imagecreatetruecolor($_POST['width'], $_POST['height']);
       $info = getimagesize($patch.$base_name);
       imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['cropLeft'], $_POST['cropTop'], $_POST['imageWidth'], $_POST['imageHeight'], $info[0], $info[1]);
       imagedestroy($img_r);
       $mini_r = imagecreatetruecolor($albom['miniature_width'], $albom['miniature_height']);
       imagecopyresampled($mini_r, $dst_r, 0, 0, 0, 0, $albom['miniature_width'], $albom['miniature_height'], $_POST['width'], $_POST['height']);
       if(!isset($_POST['type'])) imagepng($mini_r, $patch.'mini-'.$base_name);    
       else {
           if(file_exists($patch.'cat-'.$base_name)) unlink($patch.'cat-'.$base_name);
           imagepng($mini_r, $patch.'cat-'.$base_name);
       }
       imagedestroy($dst_r);
       imagedestroy($mini_r);
       if(isset($_POST['type'])) {
           @unlink("{$albom['directory']}category/{$base_name}");
           echo "var croped_image = '{$albom['directory']}category/cat-{$base_name}';";
       } else echo "var croped_image = '{$albom['directory']}".USER_FOLDER."/mini-{$base_name}';";
       kr_exit();
   }
   
   function global_update_processed_albom(){
   global $main;
       if(hook_check(__FUNCTION__)) return hook();
       $cid_info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".CAT." WHERE cid='{$_POST['cid']}'"));
       $_POST['image'] = preg_replace('/(.*?)\?rand.*/i', '\\1', $_POST['image']) ;
       if(!isset($_SESSION['processed_image']) OR global_find_processiong($_POST['image'])==-1) $_SESSION['processed_image'][] = array($_POST['title'], $cid_info['title'], $_POST['description'], str_replace('temp-', '', basename($_POST['image'])), $_POST['cid']);
       global_resize_image_albom();
   }   
?>
