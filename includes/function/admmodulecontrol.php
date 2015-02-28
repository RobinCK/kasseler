<?php
   function accblocks_status($select=""){
      global $lang;
      if(hook_check(__FUNCTION__)) return hook();
      $select = (isset($_POST['blocks_mcc'])) ? $_POST['blocks_mcc'] : $select;
      $arr = array($lang['all_block'], $lang['only_left_block'], $lang['only_right_block'], $lang['disabled_all_block']);
      $sel = "<select name='blocks_mcc' class='select chzn-search-hide'>\n";
      foreach ($arr as $key => $var) $sel .= "<option value='{$key}'".(($select==$key) ? " selected='selected'" : "").">{$var}</option>\n";
      return $sel."</select>\n";
   }

   function  module_control_config(){
      global $main, $modules;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($modules[$main->module])){
         $value = $modules[$main->module];
         main::init_function('module_access');
         $groups = modulelist_encode_prev($value);
         return "<tr><td colspan='2'><a href='#' onclick=\"\$('#form_{$main->module}_mcc').slideToggle(); \$(this).toggleClass('options_show_ac'); \$(this).children('span:first').toggleClass('openo'); return false;\" class='options_show'><span class='closeo'>&nbsp;</span>{$main->lang['module_control_inmodule']}</a>".
         "<div id='form_{$main->module}_mcc' class='post_options'><table class='form' width='100%'>".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("title_mcc", "input_text2", $value['title'])."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['block_status']}:</td><td class='form_input'>".accblocks_status($value['blocks'])."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:</td><td class='form_input'>".get_groups($groups,'groups_mcc',true, $main->lang['alluser'])."</td></tr>\n".
         "<tr><td class='form_text'>{$main->lang['enabled']}:</td><td class='form_input '>".in_chck("active_mcc", "input_checkbox", (($value['active']==1)?"on":""))."</td></tr>".
         "</table></div></td></tr>";
      } else return '';
   }
   function  module_control_saveconfig(){
      global $main, $modules;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_POST['title_mcc'])){
         $title = empty($_POST['title_mcc'])?$main->module:$_POST['title_mcc'];
         $group = "";
         if(isset($_POST['groups_mcc']) AND is_array($_POST['groups_mcc']) AND count($_POST['groups_mcc'])>0) foreach($_POST['groups_mcc'] as $value) $group .= $value.",";
         $modify = array(
            'title'    => $title,
            'active'   => (isset($_POST['active_mcc']) AND $_POST['active_mcc']==ENABLED) ? 1 : 0,
            'groups'   => $group,
            'blocks'   => $_POST['blocks_mcc'],
            'view'     => 1 
         );
         sql_update($modify, MODULES, " upper(module)=upper('{$main->module}')");
         foreach ($modify as $key => $value) {$modules[$main->module][$key] = $value;}
         main::init_function(array('configs'));
         save_config_direct('config_modules.php', '$modules', $modules, false, true);
      }
   }
?>
