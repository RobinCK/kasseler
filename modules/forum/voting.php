<?php
   global $main,$tpl_create;
   $main->init_language('voting');
   main::required("modules/voting/global.php");
   /**
   * Формирует редактор для голосования
   * 
   */
   function forum_new_voting(){
      global $main,$template;
      if(hook_check(__FUNCTION__)) return hook();
      return forum_gen_button_voting("delete_voting",array('do'=>'delvoting')).global_add_voting("","",false);
   }
   /**
   * Формирует кнопку для работы с голосованием на форуме
   * 
   * @param string $lang_name - переменная LANG - название у кнопки
   * @param array $param_array - параметры для ajax-url (без "module")
   * @return string
   */
   function forum_gen_button_voting($lang_name,$param_array){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $url=$param_array;
      if(!isset($url['module']))  $url=array_merge(array('module'=>$main->module),$url);
      return "<a href='#' onclick=\"\$('#src_voting').load('".$main->url($url)."',{ajax:true}); return false;\" class='options_show'><span class='closeo'></span>{$main->lang[$lang_name]}</a>";
      return "<h3><a class='linkbutton' style='width:130px;' onclick=\"\$('#src_voting').load('".$main->url($url)."',{ajax:true})\"><b>{$main->lang[$lang_name]}</b></a></h3>";
   }
   /**
   * Инициализация блока редактирования голосования
   * 
   */
   function forum_init_voting(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      return in_hide("vmodule",'forum').
      "<div id='src_voting'>".
      forum_gen_button_voting("addvoting",array('do'=>'new_voting')).
      "</div>";
   }
   /**
   * AJAX удаление опроса на форуме
   * 
   */
   function forum_remove_voting(){
      if(hook_check(__FUNCTION__)) return hook();
      echo forum_gen_button_voting("addvoting",array('do'=>'new_voting'));
   }
   /**
   *  Формирует редактор для редактирования голосования
   * 
   * @param mixed $vote_id
   * @return mixed
   */
   function forum_edit_voting($vote_id){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $editor=global_edit_voting($vote_id,"","",false);
      if(!empty($editor))  return "<div id='src_voting'>".
         forum_gen_button_voting("delete_voting",array('do'=>'delvoting','id'=>$vote_id)).
         $editor."</div>";
      else return forum_init_voting();
   }
   /**
   * Сохранение voting для форума в базу
   * 
   */
   function forum_save_voting(){
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_POST['question'])) {
         return global_save_voting(isset($_POST['vt_id'])?$_POST['vt_id']:0);
      }
   }
   /**
   * сохранить результаты голосования на форуме
   * 
   */
   function forum_set_votes(){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $ret=global_set_vote_voting();
      if($ret[0]==0){
         if(!is_ajax()) redirect($main->ref);
         else {
            echo global_result_voting();
         }
      } else  meta_refresh(5, $main->ref, $ret[1]);
   }
   /**
   * Показ голосования
   * 
   * @param mixed $vote_id
   * @return mixed
   */
   function forum_more_voting($vote_id){
      global $main, $template, $load_tpl;
      if(hook_check(__FUNCTION__)) return hook();
      $file = TEMPLATE_PATH."{$load_tpl}/{$main->module}/vote_{$main->module}.tpl";
      if(file_exists($file)){
         if(isset($template->cache['vote']))  unset($template->cache['vote']);
         $template->get_tpl("forum/vote_{$main->module}", 'vote');
      }
      return global_more_voting($vote_id,"");
   }
?>
