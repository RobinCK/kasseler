<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
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
      array('search', 'search_modules'),
      array('reinit_modules', 'reinitmodule'),
      array("save_config", "save"),
      array("back_config", "cancel")
   );

   function main_moduleslist(){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('module_access');
      $result = $main->db->sql_query("SELECT * FROM ".GROUPS." ORDER BY id");
      $group = array(0 => array('text'=>$main->lang['alluser'],'color'=>'green'));
      while(($row = $main->db->sql_fetchrow($result))) $group[$row['id']] = array('text'=>$row['title'], 'color'=>"#".$row['color']);
      $result = $main->db->sql_query("SELECT * FROM ".MODULES." ORDER BY pos");
      $count = $main->db->sql_numrows($result);
      if($count>0){
         $row = "row1";
         echo "<table class='table' width='100%'><tr><th width='25'>#</th><th>{$main->lang['title']}</th><th width='80'>{$main->lang['name']}</th><th width='135'>{$main->lang['who_views']}</th><th width='70'>{$main->lang['position']}</th><th width='80'>{$main->lang['status']}</th><th width='80'>{$main->lang['functions']}</th></tr>";
         while(($rows = $main->db->sql_fetchrow($result))){
            if(!file_exists("modules/{$rows['module']}")) $rows['title'] = "<span style='color: red;'>{$rows['title']}</span>";
            $op = "";
            if(file_exists("modules/{$rows['module']}/install.php")) $op .= install_button("{$adminfile}?module={$main->module}&amp;do=install&amp;id={$rows['module']}");
            $op .= edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$rows['id']}");
            if(file_exists("modules/{$rows['module']}/uninstall.php")) $op .= uninstall_button("{$adminfile}?module={$main->module}&amp;do=uninstall&amp;id={$rows['module']}");
            $gr = modulelist_encode_prev($rows);
            if(count($gr)==1) $who_view = isset($group[$gr[0]])?"<span style='color: {$group[$gr[0]]['color']};'>{$group[$gr[0]]['text']}</span>":"<span style='color: red;'> - </span>";
            else $who_view = "<span style='color: green;'> - </span>";
            $up_down = ($count>1) ? up_down_analizy($rows['pos'], $count, $rows['id'], 'ajax_content') : "";
            echo "<tr class='{$row}".(($rows['active']==0)?"_warn":"")."'>".
            "<td align='center' class='col'>{$rows['pos']}</td>".
            "<td>".(!empty($main->lang[$rows['title']])?$main->lang[$rows['title']]:$rows['title'])."</td><td align='center'>{$rows['module']}</td>".
            "<td class='col' align='center' id='view_{$rows['id']}'>".$who_view."</td>".
            "<td align='center'>{$up_down}</td><td class='col' align='center' id='onoff_{$rows['id']}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$_GET['module']}&amp;do=on_off&amp;id={$rows['id']}', 'onoff_{$rows['id']}')\">".($rows['active']==1 ? $main->lang['on'] : $main->lang['off'])."</td><td align='center'>{$op}</td></tr>";
            $row = ($row=='row1') ? "row2" : "row1";
         }
         echo "</table>";
      } else info($main->lang['noinfo']);
   }

   function on_off_moduleslist(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      list($active) = $main->db->sql_fetchrow($main->db->sql_query("SELECT active FROM ".MODULES." WHERE id='{$_GET['id']}'"));
      if($active==1){
         $main->db->sql_query("UPDATE ".MODULES." SET active='0' WHERE id='{$_GET['id']}'");
         echo $main->lang['off'];
         echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className+'_warn';</script>";
      } else {
         $main->db->sql_query("UPDATE ".MODULES." SET active='1' WHERE id='{$_GET['id']}'");
         echo $main->lang['on'];
         echo "<script type='text/javascript'>node = document.getElementById('onoff_{$_GET['id']}'); for(i=0;i<20;i++){if(node.nodeName!='TR') node = node.parentNode; else break;} node.className = node.className.replace('_warn', '');</script>";
      }
   }

   function moves_moduleslist(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if($_GET['type']=="up") $next = $_GET['pos']-1; else $next = $_GET['pos']+1;
      list($id_tmp) = $main->db->sql_fetchrow($main->db->sql_query("SELECT id FROM ".MODULES." WHERE pos='{$next}'"));
      $main->db->sql_query("UPDATE ".MODULES." SET pos='{$_GET['pos']}' WHERE id='{$id_tmp}'");
      $main->db->sql_query("UPDATE ".MODULES." SET pos='{$next}' WHERE id='{$_GET['id']}'");
      if (is_ajax()) main_moduleslist(); else redirect(MODULE);
   }

   function saves_config_moduleslist(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $config = "<?php\n/**********************************************/\n/* Kasseler CMS: Content Management System    */\n/**********************************************/\n/*                                            */\n/* Copyright (c)2007-2010 by Igor Ognichenko  */\n/* http://www.kasseler-cms.net/               */\n/*                                            */\n/**********************************************/\nif (!defined('FUNC_FILE')) die('Access is limited');\n\n$"."modules = array(\n";
      $result = $main->db->sql_query("SELECT * FROM ".MODULES." ORDER BY pos");
      if($main->db->sql_numrows($result)>0){
         while(($row = $main->db->sql_fetchrow($result))){
            $config .= "    '{$row['module']}' => array('id' => '{$row['id']}', 'title' => '".addslashes($row['title'])."', 'active' => '{$row['active']}', 'view' => '{$row['view']}', 'blocks' => '{$row['blocks']}', 'groups' => '{$row['groups']}', 'pos' => '{$row['pos']}'),\n";
         }    
         file_write("includes/config/config_modules.php", mb_substr($config, 0, mb_strlen($config)-2)."\n);\n?".">");
      } else file_write("includes/config/config_modules.php", $config."\n);\n?".">");
      main::init_function('session_tools');
      all_user_sessions_modify('set_this_session_update');
      if(!is_ajax()) redirect(MODULE); else main_moduleslist();
   }

   function back_config_moduleslist(){
      global $modules, $main;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("DELETE FROM ".MODULES);
      foreach($modules as $key=>$arr){
         sql_insert(array(
               'id'        => $arr['id'],
               'title'     => $arr['title'],
               'module'    => $key,
               'title'     => $arr['title'],
               'active'    => $arr['active'],
               'view'      => $arr['view'],
               'blocks'    => $arr['blocks'],
               'groups'    => $arr['groups'],
               'pos'       => $arr['pos']
            ), MODULES);
      }
      if(!is_ajax()) redirect(MODULE); else main_moduleslist();
   }

   function search_modules(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      adm_modulelist_reinit();
      $dir = opendir("modules");
      while(($file = readdir($dir))){
         if(!preg_match('/\./', $file)) {            
            if($main->db->sql_numrows($main->db->sql_query("SELECT * FROM ".MODULES." WHERE module='{$file}'"))==0){
               list($pos) = $main->db->sql_fetchrow($main->db->sql_query("SELECT MAX(pos) FROM ".MODULES.""));
               sql_insert(array(
                     'title'   => !empty($main->lang[$file])?$main->lang[$file]:$file,
                     'module'  => $file,
                     'active'  => '0',
                     'groups'  => '1',
                     'view'    => '1',
                     'pos'     => $pos+1
                  ), MODULES);
            }
         }
      }
      closedir($dir);
      //Удаляем модуля, которые не существуют и обновляем позиции
      $i=1;
      $result = $main->db->sql_query("SELECT id, module FROM ".MODULES." ORDER BY pos");
      while(($row = $main->db->sql_fetchrow($result))){
         if(file_exists('modules/'.$row['module'])){
            $main->db->sql_query("UPDATE ".MODULES." SET pos='{$i}' WHERE id='{$row['id']}'");
            $i++;
         } else $main->db->sql_query("DELETE FROM ".MODULES." WHERE id='{$row['id']}'");
      }    
      redirect(MODULE);
   }

   function blocks_status($select=""){
      global $lang;
      if(hook_check(__FUNCTION__)) return hook();
      $select = (isset($_POST['blocks'])) ? $_POST['blocks'] : $select;
      $arr = array($lang['all_block'], $lang['only_left_block'], $lang['only_right_block'], $lang['disabled_all_block']);
      $sel = "<select name='blocks' class='select chzn-search-hide'>\n";
      foreach ($arr as $key => $var) $sel .= "<option value='{$key}'".(($select==$key) ? " selected='selected'" : "").">{$var}</option>\n";
      return $sel."</select>\n";
   }

   function edit_moduleslist($msg=""){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('module_access');
      if(!empty($msg)) warning($msg);
      $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".MODULES." WHERE id='{$_GET['id']}'"));
      $groups = modulelist_encode_prev($result);
      echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save&amp;id={$_GET['id']}'>\n".
      "<table class='form' align='center' id='form_{$main->module}'>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title", "input_text2", $result['title'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['block_status']}:</td><td class='form_input'>".blocks_status($result['blocks'])."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:</td><td class='form_input'>".get_groups($groups,'groups',true, $main->lang['alluser'])."</td></tr>\n".
      "<tr><td class='form_text'>{$main->lang['enabled']}:</td><td class='form_input '>".in_chck("active", "input_checkbox", (($result['active']==1)?"on":""))."</td></tr>".
      "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
      "</table>\n</form>\n";
   ?>
   <script type="text/javascript">
      //<![CDATA[
      var grl;
      $(document).ready(function(){
            grl=$('#groups');
            var opz=grl.find('option:[value="0"]').get(0);
            var exists_all = opz.selected;
            grl.on('change',function(){
                  var a=$(this).val();
                  if(a!=null){
                     if(a[0]=='0') {
                        if(exists_all&&a.length>1) opz.selected = false;
                        else {
                           grl.find('option').each(function(){this.selected=false;})
                           opz.selected = true;
                        }
                     }
                     exists_all = opz.selected;
                     grl.trigger("liszt:updated");
                  }
            })
      });
      //]]>
   </script>
   <?php

   }

   function save_moduleslist(){
      if(hook_check(__FUNCTION__)) return hook();
      $msg = error_empty(array('title'), array('errortitle'));
      if(empty($msg)){       
         $group = "";
         if(isset($_POST['groups']) AND is_array($_POST['groups']) AND count($_POST['groups'])>0) foreach($_POST['groups'] as $value) $group .= $value.",";
         sql_update(array(
               'title'    => $_POST['title'],            
               'active'   => (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,
               'groups'   => $group,
               'blocks'   => $_POST['blocks'],
               'view'     => 1 //$_POST['view']
            ), MODULES, "id='{$_GET['id']}'");
         redirect(MODULE);
      } else edit_moduleslist($msg);
   }

   function install_module(){
      if(hook_check(__FUNCTION__)) return hook();
      include("modules/{$_GET['id']}/install.php");
      redirect(MODULE);
   }

   function uninstall_module(){
      if(hook_check(__FUNCTION__)) return hook();
      include("modules/{$_GET['id']}/uninstall.php");
      redirect(MODULE);
   }

   function load_case_moduleslist(){
      global $lang;
      if(hook_check(__FUNCTION__)) return hook();
      if($_GET['type']=="view"){
         $arr = array($lang['alluser'], $lang['onlyguest'], $lang['onlyuser'], $lang['onlyadmin']);
         echo "<select class='ajax_edit' id='sel_ajax'>";
         for($i=0;$i<count($arr);$i++){
            $selected = ($_POST['value']==$i+1) ? " selected='selected'" : "";
            echo "<option value='".($i+1)."'{$selected}>{$arr[$i]}</option>";
         }
         echo "</select>";
      }
   }

   function update_table(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      if($_GET['type']=='view'){
         echo "<input type='hidden' id='hide_{$_POST['id']}' value='{$_POST['value']}' />".who_view($_POST['value']);
         $arr = explode("_", $_POST['id']);
         $main->db->sql_query("UPDATE ".MODULES." SET view='{$_POST['value']}' WHERE id='{$arr[1]}'");
      }
   }
   function adm_modulelist_reinit(){
      global $main, $moduleinit;
      if(hook_check(__FUNCTION__)) return hook();
      $moduleinit=array('lang'=>array());
      main::init_function(array('configs','initmodule'));
      scan_init_modules();
      save_config_direct('config_init.php','$moduleinit',$moduleinit);
   }
   function switch_admin_moduleslist(){
      global $main, $break_load;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_GET['do']) AND $break_load==false){
         switch($_GET['do']){        
            case "on_off" : on_off_moduleslist(); break;
            case "move" : moves_moduleslist(); break;        
            case "save_config" : saves_config_moduleslist(); break;
            case "back_config" : back_config_moduleslist(); break;
            case "search" : search_modules(); break;
            case "edit" : edit_moduleslist(); break;
            case "save" : save_moduleslist(); break;
            case "install" : install_module(); break;
            case "uninstall" : uninstall_module(); break;
            case "load_case" : load_case_moduleslist(); break;
            case "update" : update_table(); break;
            case "reinit_modules" : adm_modulelist_reinit(); break;
            default: main_moduleslist(); break;
         }
      } elseif($break_load==false) main_moduleslist();
   }
switch_admin_moduleslist();
?>