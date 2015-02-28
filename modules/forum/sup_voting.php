<?php
   function forum_new_votiong(){
      global $main,$template;
      if(hook_check(__FUNCTION__)) return hook();
      $main->init_language('voting');
      main::required("modules/voting/global.php");
       unset($template->cache['index']);
      $template->get_tpl('ifframe','index');
      echo global_add_voting($main->url(array('module'=>$main->module,'do'=>'save_votiong')));
   }
?>
