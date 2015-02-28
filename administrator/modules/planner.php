<?php
   /**
   * @author Igor Ognichenko
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('ADMIN_FILE')) die("Hacking attempt!");

   global $navi, $main, $break_load, $default_modules_admin;
   main::required('includes/config/admin_panel.php');
   main::init_function('planner');
   global $planner,$structure;
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
   array('add', 'add_planner'),
   array('config', 'config'),
   );
   /**
   * Список заданий
   * 
   */
   function main_planner(){
      global $main, $adminfile,$planner,$template;
      if(hook_check(__FUNCTION__)) return hook();
      $class='row1';
      $content="";
      foreach ($planner as $key => $value) {
         $lastrun=isset($value['lastrun'])?$value['lastrun']:"";
         $content.="<tr class='{$class}'>".
         "<td>{$value['name']}</td>".
         "<td>{$value['time']}</td>".
         "<td title='{$value['url']}'>{$value['url']}</td>".
         "<td>{$lastrun}</td>".
         "<td align='center' id='onoff_{$key}' style='cursor: pointer;' onclick=\"onoff('{$adminfile}?module={$main->module}&amp;do=on_off&amp;id={$key}', 'onoff_{$key}')\" >".($value['status']=='on'?$main->lang['on']:$main->lang['off'])."</td>".
         "<td>".edit_button("{$adminfile}?module={$main->module}&amp;do=edit&amp;id={$key}").delete_button("{$adminfile}?module={$main->module}&amp;do=delete&amp;id={$key}".parse_get(array('module', 'do', 'id')), 'ajax_content')."</td>".
         "</tr>";
         $class=$class=='row1'?"row2":"row1";
      }
      $template->get_tpl('admin_planner', 'admin_planner');
      $template->set_tpl(array('name'=>$main->lang['name'],"time"=>$main->lang['time_planer'],
      "url"=>$main->lang['script_planner'],'lastrun'=>$main->lang['lasttime_planner'],
      "status"=>$main->lang['status'],'function'=>$main->lang['functions'],'content'=>$content),'admin_planner');
      $template->tpl_create(false,'admin_planner');
   }
   /**
   * Создание задания
   * 
   * @param mixed $val_edit
   * @return mixed
   */
   function add_planner($val_edit=array()){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      if(count($val_edit)==0){
         $init=array('time' => '00:00', 'timeout' => '10', 'lastrun' => '', 'nextrun' => '', 'name' => '', 'weekdays' => '0,1,2,3,4,5', 'url' => '', 'status' => 0);
      } else $init=$val_edit;
      $week=array(0=>'pn',1=>'vt',2=>'sr',3=>'ct',4=>'pt',5=>'sb',6=>'vs');
      $checks="";
      $weekdays=$init['weekdays']!=""?explode(',',$init['weekdays']):array();
      foreach ($week as $key=>$value) {
         $checked=in_array($key,$weekdays)?"on":"";
         $checks.=", ".in_chck("weeks[]","input_checkbox",$checked," id='{$value}' value='{$key}'")." <label for='{$value}' > {$main->lang[$value]} </label> ";
      }
      $checks=mb_substr($checks,1);
      echo "<form enctype='multipart/form-data' method='post' action='{$adminfile}?module={$main->module}&amp;do=save'>\n".
      (isset($init['id'])?in_hide('id',$init['id']):"").
      "<table class='form' align='center' id='form_{$main->module}'>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['name']}:</td><td class='form_input'>".in_text("name", "input_text2",$init['name'])."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text'>{$main->lang['time_planer']}:</td><td class='form_input'>".in_text("time", "input_text",$init['time'],false," style='width:40px' ")."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['timeout_planner']}:</td><td class='form_input'>".in_text("timeout", "input_text",$init['timeout'],false," style='width:40px'")."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text'>{$main->lang['weekdays_planner']}:</td><td class='form_input'>".$checks."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text'>{$main->lang['script_planner']}:</td><td class='form_input'>".in_text("url", "input_text2",$init['url'])."</td></tr>\n".    
      "<tr><td class='form_text'>{$main->lang['enabled']}:</td><td class='form_input '>".in_chck("status", "input_checkbox", (($init['status']==1||$init['status']=='on')?"on":""))."</td></tr>".
      "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
      "</table>\n</form>\n";
   }
   /**
   * Редактирование задания
   * 
   */
   function edit_planner(){
      global $main,$planner;
      if(hook_check(__FUNCTION__)) return hook();
      $id=intval($_GET['id']);
      $planner[$id]['id']=$id;
      add_planner($planner[$id]);
   }
   /**
   * Сохранение информации о задании
   * 
   */
   function save_planner(){
      global $structure,$planner, $main;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('sources');
      $weeks=isset($_POST['weeks'])?implode(',',$_POST['weeks']):"";
      if(isset($_POST['id'])) $arr=& $planner[intval($_POST['id'])];
      else $arr=& $planner[];
      foreach ($structure as $key => $value) {
         if(isset($_POST[$value])) $arr[$value]=$_POST[$value];
         else $arr[$value]='';
      }
      $arr['weekdays']=$weeks;
      $arr['status']=isset($_POST['status'])?"on":"";
      if(($weeks=='')||(trim($_POST['url'])=="")) $arr['status']='';
      if($arr['status']=='on'){
         $next = gmdate('Y-m-d', time())." ".$arr['time'];
         if(gmdate('Y-m-d H:i', strtotime($next))<gmdate('Y-m-d H:i')) $next = gmdate('Y-m-d', time()+60*60*24)." ".$arr['time'];
         $arr['nextrun']=$next;
      }
      save_file_planer('config_planner.php');
      redirect(MODULE);
   }
   function delete_planner(){
      global $planner;
      if(hook_check(__FUNCTION__)) return hook();
      $id=intval($_GET['id']);
      unset($planner[$id]);
      save_file_planer('config_planner.php');
      redirect(MODULE);
   }
   function on_off_planner(){
      global $planner,$main;
      if(hook_check(__FUNCTION__)) return hook();
      $id=intval($_GET['id']);
      $planner[$id]['status']=$planner[$id]['status']=='on'?"":"on";
      save_file_planer('config_planner.php');
      echo ($planner[$id]['status']=='on')?$main->lang['on']:$main->lang['off'];
   }

   function admin_planner_config(){
      global $main, $adminfile, $config;
      if(hook_check(__FUNCTION__)) return hook();
      $othe_cron = isset($config['othe_cron'])?$config['othe_cron']:false;
      $run_flush = isset($config['run_flush'])?$config['run_flush']:1;
      echo "<form id='block_form' action='{$adminfile}?module={$_GET['module']}&amp;do=save_conf' method='post'><table align='center' class='form' id='form_{$main->module}'>".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['runned_cron']}</b>:<br /><i>{$main->lang['runned_cron_d']}</i></td><td class='form_input2'>".in_chck('othe_cron', 'input_checkbox', $othe_cron)."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['runned_bed']}</b>:<br /><i>{$main->lang['runned_bed_d']}</i></td><td class='form_input2'>".in_text('run_flush', 'input_text2', $run_flush)."</td></tr>\n".
      "<tr><td class='form_submit' colspan='2' align='center'><input type='submit' value='{$main->lang['send']}' /></td></tr>\n".
      "</table></form>";
   }
   
   function admin_planner_save_conf(){
   global $config, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function('sources');
      $config['othe_cron']=!empty($_POST['othe_cron'])?$_POST['othe_cron']:'off';
      $config['run_flush']=$_POST['run_flush'];
      save_config('config.php', '$config', $config);
      redirect("{$adminfile}?module={$_GET['module']}&do=config");
   }
   function switch_admin_planner(){
      global $main, $break_load;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_GET['do']) AND $break_load==false){
         switch($_GET['do']){       
            case "add" : add_planner(); break;
            case "edit":edit_planner();break;
            case "save":save_planner();break;
            case "delete":delete_planner();break;
            case "on_off" : on_off_planner(); break; 
            case "config": admin_planner_config(); break;
            case "save_conf": admin_planner_save_conf(); break;
            default: main_planner(); break;
         }
      } elseif($break_load==false) main_planner();
   }
   switch_admin_planner();
?>