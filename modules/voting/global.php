<?php
   /**
   * Редактор нового голосования
   * 
   * @param string $action url куда отправляется форма
   * @param string $msg
   * @param boolean $active_comment доступность коментариев
   * @return mixed
   */
   function global_add_voting($action, $msg="", $active_comment=true){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($msg)) warning($msg);
      main::add2script("includes/javascript/kr_calendar2.js");
      main::add2link("includes/css/kr_calendar.css");
      main::add_css2head("
         div.calendar * {vertical-align: middle;}
         div.calendar input {margin-left:5px;}
      ");
      main::add_javascript2body("
      function init_calendar(){
        if(KR_AJAX.kr_calendar!=undefined){
            \$('.input_text_b').each(function(){
                  var el_id=this.id;
                  var cl='button_calendar_'+el_id;
                  KR_AJAX.kr_calendar.init(cl, {el:el_id});
            });
         } else setTimeout(init_calendar,200);
      }
      setTimeout(init_calendar,200);");
      $date_input="<div class='calendar'>".in_text("date_final",'input_text_b',"",false," size='12'").
      "<img id='button_calendar_date_final' class='input_calendar' alt='{$main->lang['calendar']}' title='{$main->lang['calendar']}' src='".TEMPLATE_PATH."admin/images/date.png' style='cursor: pointer;'/></div>";
      return (!empty($action)?"<form method='post' action='{$action}'>\n":"").
      "<table class='form' align='center' id='form_{$main->module}'>\n".    
      "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("question", "input_text2")."</td></tr>\n".    
      "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file()."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['vote_case']}:<span class='star'>*</span></td><td class='form_input'>".in_text_many("answer", "input_text2")."</td></tr>\n".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:<span class='star'>*</span></td><td class='form_input'>".get_groups(explode(',', ''),'agroups')."</td></tr>\n".
      ($active_comment?"<tr><td class='form_text'>{$main->lang['active_comments']}</td><td class='form_input '>".in_chck("comments", "input_checkbox")."</td></tr>":"").
      "<tr class='row_tr'><td class='form_text'>{$main->lang['active_voting']}</td><td class='form_input '>".in_chck("active", "input_checkbox", ENABLED)."</td></tr>".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['active_multi_select']}</td><td class='form_input '>".in_chck("active_ms", "input_checkbox")."</td></tr>".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['max_mselect']}</td><td class='form_input '>".in_text("max_multi", "input_text2",1)."</td></tr>".
      "<tr class='row_tr'><td class='form_text'>{$main->lang['date_deactiv_vote']}</td><td class='form_input '>".$date_input."</td></tr>".
      (!empty($action)?"<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n":"").
      "</table>\n".(!empty($action)?"</form>\n":"");
   }
   /**
   * Редактор голосования
   * 
   * @param mixed $vote_id
   * @param string $action url куда отправляется форма
   * @param mixed $msg
   * @param boolean $active_comment доступность коментариев
   * @return mixed
   */
   function global_edit_voting($vote_id, $action, $msg="", $active_comment=true){
      global $main, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($msg)) warning($msg);
      $info = $main->db->sql_fetchrow($main->db->sql_query("SELECT * FROM ".VOTING." WHERE id='{$vote_id}'"));
      if(!empty($info['id'])){
         $vote_case = explode('|', $info['vote_case']);
         unset($vote_case[count($vote_case)-1]);
         main::add2script("includes/javascript/kr_calendar2.js");
         main::add2link("includes/css/kr_calendar.css");
         main::add_css2head("
             div.calendar * {vertical-align: middle;}
             div.calendar input {margin-left:5px;}
         ");
         main::add_javascript2body("
             \$(document).ready(function(){
             \$('.input_text_b').each(function(){
             var el_id=this.id;
             var cl='button_calendar_'+el_id;
             KR_AJAX.kr_calendar.init(cl, {el:el_id});
             });
         })");
         $date_input="<div class='calendar'>".in_text("date_final",'input_text_b',!empty($info['date_final'])?date('d.m.Y',strtotime($info['date_final'])):"",false," size='12'").
         "<img id='button_calendar_date_final' class='input_calendar' alt='{$main->lang['calendar']}' title='{$main->lang['calendar']}' src='".TEMPLATE_PATH."admin/images/date.png' style='cursor: pointer;'/></div>";
         return (!empty($action)?"<form method='post' action='{$action}'>\n":"").
         in_hide("vmodule",$info['module']).(empty($action)?in_hide("vt_id",$vote_id):"").
         "<table class='form' align='center' id='form_{$main->module}'>\n".    
         "<tr class='row_tr'><td class='form_text'>{$main->lang['title']}:<span class='star'>*</span></td><td class='form_input'>".in_text("question", "input_text2", $info['title'])."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['language']}:</td><td class='form_input'>".get_lang_file($info['language'])."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['vote_case']}:<span class='star'>*</span></td><td class='form_input'>".in_text_many("answer", "input_text2", $vote_case)."</td></tr>\n".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['groups']}:<span class='star'>*</span></td><td class='form_input'>".get_groups(explode(',', $info['agroups']),'agroups')."</td></tr>\n".
         ($active_comment?"<tr><td class='form_text'>{$main->lang['active_comments']}</td><td class='form_input '>".in_chck("comments", "input_checkbox", $info['show_comment']==1?ENABLED:"")."</td></tr>":"").
         "<tr class='row_tr'><td class='form_text'>{$main->lang['active_voting']}</td><td class='form_input '>".in_chck("active", "input_checkbox", $info['status']==1?ENABLED:"")."</td></tr>".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['active_multi_select']}</td><td class='form_input '>".in_chck("active_ms", "input_checkbox",$info['multisel']==1?ENABLED:"")."</td></tr>".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['max_mselect']}</td><td class='form_input '>".in_text("max_multi", "input_text2",$info['max_multi'])."</td></tr>".
         "<tr class='row_tr'><td class='form_text'>{$main->lang['date_deactiv_vote']}</td><td class='form_input '>".$date_input."</td></tr>".
         (!empty($action)?"<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n":"").
         "</table>\n".(!empty($action)?"</form>\n":"");
      } else return "";
   }
   /**
   * Запись отредактированого голосования в БД
   * 
   * @param mixed $IDV
   * @return mixed
   */
   function global_save_voting($idvote=0){
      global $main, $adminfile, $pull;
      if(hook_check(__FUNCTION__)) return hook();
      $answers = "";
      $msg = error_empty(array('question'), array('question_err'));
      foreach($_POST['answer'] as $value) $answers .= (!empty($value)) ? $value."|" : "";
      if(empty($answers) OR count(explode('|', $answers))<2) $msg .= $main->lang['err_answer_voting'];
      if(empty($msg)){
         $op = array(
            'title'         => $_POST['question'],
            'vote_case'     => $answers,
            'date'          => kr_datecms("Y-m-d H:i:s"),  
            'status'        => (isset($_POST['active']) AND $_POST['active']==ENABLED) ? 1 : 0,
            'show_comment'  => (isset($_POST['comments']) AND $_POST['comments']==ENABLED) ? 1 : 0,
            'language'      => isset($_POST['language']) ? $_POST['language'] : "",
            'module'        => isset($_POST['vmodule'])?$_POST['vmodule']: "",
            'multisel'      => (isset($_POST['active_ms']) AND $_POST['active_ms']==ENABLED) ? 1 : 0,
            'language'      => isset($_POST['language']) ? $_POST['language'] : "",
            'agroups'       => !empty($_POST['agroups']) ? implode(',',$_POST['agroups']) : "",
            'max_multi'     => isset($_POST['max_multi']) ? intval($_POST['max_multi']) : "0",
         );
         if(!empty($_POST['date_final'])) $op['date_final']=date('Y-m-d',strtotime($_POST['date_final']));
         if($idvote==0) {
            sql_insert($op, VOTING);
            return $main->db->sql_nextid();
         }   else {
            sql_update($op, VOTING, "id='{$idvote}'");
            return $idvote;
         }
      } else return $msg;
   }
   /**
   * Показываем диалог голосования
   * 
   * @param mixed $id_vote
   * @param mixed $links
   * @param mixed $msg
   * @return string
   */
   function global_more_voting($id_vote, $links, $msg=""){
      global $main, $template, $load_tpl;
      if(hook_check(__FUNCTION__)) return hook();
      if(!empty($msg)) warning($msg);
      if(file_exists(TEMPLATE_PATH."{$load_tpl}/vote_{$main->module}.tpl")) $file_tpl = "vote_{$main->module}";
      else $file_tpl = "vote";
      $template->get_tpl($file_tpl, 'vote', '');
      if (preg_match('/(?is)<!--begin\x20voting-->[^\r\n]*[\r\n]*(.*)<!--end\x20voting-->/', $template->template['vote'], $regs)) {
         $template->template['vote'] = $regs[1];
      } 
      $result = $main->db->sql_query("SELECT * FROM ".VOTING." WHERE id='{$id_vote}' AND status='1'");
      $found_vote=$main->db->sql_numrows($result)>0;
      $acc_ok=false;
      if($found_vote) {$vote = $main->db->sql_fetchrow($result);$acc_ok=(!empty($vote['agroups']) AND $vote['agroups']!=",")?check_user_group($vote['agroups']):true;}
      if(is_admin()) $acc_ok=true;
      if(!empty($vote['date_final'])&&strtotime($vote['date_final'])<time()){
          $acc_ok=false;
          $main->db->sql_query("update ".VOTING." set status='0' where id='{$id_vote}'");
      }
      if($found_vote&&$acc_ok){
         $module_vote=!empty($vote['module']);
         $cook = isset($_COOKIE['voting']) ? $_COOKIE['voting'] : "";
         if(!((mb_strpos($vote['vote_ip'], $main->ip.",")===false OR mb_strpos($vote['vote_users'], $main->user['user_name'].",")===false) AND mb_strpos($cook, $vote['id'].",")===false)) {
            if($module_vote){
               return global_result_voting($id_vote);
            } else redirect($main->url(array('module' => $main->module, 'do' => 'result', 'id' => $vote['id']))); 
         }
         $tmp = explode("|", $vote['vote_case']);
         $content="";
         if($vote['multisel']==1) for ($i=0;$i<count($tmp)-1;$i++) $content .= "<tr><td>".in_chck("var[".($i+1)."]",'checkbox')."<label for='var".($i+1)."'>{$tmp[$i]}</label></td></tr>";
         else for ($i=0;$i<count($tmp)-1;$i++) $content .= "<tr><td>".in_radio('var', $i+1, $tmp[$i], "vote{$i}")."</td></tr>";
         $template->set_tpl(array(
               '$vote.action'        => $main->url(array('module'=>$main->module,'do'=>'set_votes','id'=>$id_vote)),
               '$vote.title'         => $vote['title'],
               '$vote.content'       => $content,
               '$vote.submit'        => "<input type='submit' value='{$main->lang['vote']}' />\n",
               '$vote.links'         => $links
            ), 'vote', array('start' => '{', 'end' => '}'));
            if($vote['multisel']==1 AND $vote['max_multi']>0)   $script="<script type='text/javascript'>
         <!--
         var max_checked={$vote['max_multi']};
var check_count=0;
\$('.votesrc').find(':checkbox').on('change',function(){
    if(this.checked){
        if(check_count<max_checked) check_count++;
        else this.checked=false;
    } else {if(check_count>0) check_count--}
})
         // -->
         </script>";
         else $script="";
         return $template->tpl_create(true, 'vote').$script;
      } else {
         $result = $main->db->sql_query("SELECT id, title, vote_case, vote_ip, vote_users, date FROM ".VOTING." WHERE id='".intval($_GET['id'])."'");
         if($main->db->sql_numrows($result)>0){
            return global_result_voting($id_vote);
         } else return info($main->lang['noinfo'],true);
      }
   }
   /**
   * Результат голосования
   * 
   * @param integer $id_vote - идентификатор голосования
   * @param mixed $msg
   * @return string
   */
   function global_result_voting($id_vote,$msg=""){
      global $main, $pull, $template, $load_tpl;
      if(hook_check(__FUNCTION__)) return hook();
      //Подключаем модуль комментариев
      main::init_function('comments');
      if(!isset($_POST['id'])){
         $allcontent="";
         $sum=0;
         $result = $main->db->sql_query("SELECT * FROM ".VOTING." WHERE id='{$id_vote}'");
         if($main->db->sql_numrows($result)>0){
            $vote = $main->db->sql_fetchrow($result);
            $module_vote=!empty($vote['module']);
            $file_tpl = (isset($_POST['voteblock']) AND file_exists(TEMPLATE_PATH."{$load_tpl}/block-vote.tpl")) ? 'block-vote' : 'vote';
            $template->get_tpl($file_tpl, 'vote', '');
            $vote_row="template incorrect";;
            if (preg_match('/(?is)<!--begin\x20show\x20voting-->[^\r\n]*[\r\n]*(.*)<!--end\x20show\x20voting-->/', $template->template['vote'], $regs)) {
               $vote_tpl= preg_replace('/(?is)<!--row\x20result-->[^\r\n]*[\r\n]*(.*)<!--end\x20row\x20result-->/', '{$vote.content}', $regs[1]);
               if (preg_match('/(?is)<!--row\x20result-->[^\r\n]*[\r\n]*(.*)<!--end\x20row\x20result-->/', $regs[1], $rg)) {
                  $vote_row= $rg[1];
               }
               $template->template['vote'] = $vote_tpl;
            } 
            $tmp = explode(",", $vote['result']);
            $tmpcase = explode("|", $vote['vote_case']);
            for ($y=0; $y<count($tmpcase)-1; $y++){
               $case = $tmpcase[$y];
               $vote[$case]=0;
               for ($i=0; $i<count($tmp); $i++) if ($tmp[$i]==$y+1) {$vote[$case]+=1; $sum+=1;}
            }
            if(!isset($_POST['voteblock'])) $allcontent.=open(true);
            $pl=1;$content="";
            for ($y=0; $y<count($tmpcase)-1; $y++){
               $case = $tmpcase[$y];
               $res = ($sum>0) ? ((100*$vote[$case]/$sum)) : 0;
               $proc = round( $res, 2 );
               $row = array('$text_vote'=>"{$case}  - ".$vote[$case],'$val_int'=>intval($proc), '$val_text'=>$proc,'$pcolor'=>$pl);
               $replace = array('key' => array(), 'value' => array());
               foreach($row as $key => $value){$replace['key'][] = '{'.$key.'}'; $replace['value'][] = $value;}
               $content .= (!empty($replace['key'])) ? str_replace($replace['key'], $replace['value'], $vote_row) : "";
               $pl++;
               if($pl == 6) $pl=1;
            }
            $othe_vote=isset($_POST['voteblock'])?"<br /><a class='other_votes link_button' href='".$main->url(array('module' => 'voting'))."' title='{$main->lang['other_votes']}'>{$main->lang['other_votes']}</a>":"";
            $template->set_tpl(array(
                  '$vote.title'         => $vote['title'],
                  '$vote.content'       => $content,
                  '$vote.all'           => "{$main->lang['votes']}: (".$vote['count_vote'].")".$othe_vote
               ), 'vote', array('start' => '{', 'end' => '}'));  
            $allcontent.=$template->tpl_create(true, 'vote');
            if(!isset($_POST['voteblock'])) {
               $allcontent.=close(true);
               if($pull['comments']==ENABLED AND $vote['show_comment']=='1') {
                  echo $allcontent;
                  $allcontent="";
                  comments(VOTING, $vote['id'], $vote['id'], $pull['guests_comments'], $pull['comments_sort'], true, $msg, 'result');
               }
            }
            return $allcontent;
         } else return info($main->lang['noinfo'],true);
      } else add_comment(VOTING, $pull['comments_sort'], $pull['guests_comments'], 'result');
   }
   /**
   * Сохраняет результат голосования
   * 
   */
   function global_set_vote_voting(){
      global $main, $points, $ip;
      if(hook_check(__FUNCTION__)) return hook();
      if((isset($_POST['var']) AND $_POST['var']!="") OR (isset($_POST['var[]']) AND $_POST['var[]']!="")){
         $result = $main->db->sql_query("SELECT * FROM ".VOTING." WHERE id='".intval($_GET['id'])."'");
         $vote = $main->db->sql_fetchrow($result);
         $cook = isset($_COOKIE['voting']) ? $_COOKIE['voting'] : "";
         if((mb_strpos($vote['vote_ip'], $ip.",")===false OR mb_strpos($vote['vote_users'], $main->user['user_name'].",")===false) AND mb_strpos($cook, $vote['id'].",")===false){
            $var=$_POST['var'];
            if(is_array($var)){
               $aresult="";
               foreach ($var as $key => $value) {
                  if($value=='on')  $aresult.=",".intval($key);
               }
               if(strlen($aresult)>0) $aresult=substr($aresult,1);
            } else $aresult=intval($_POST['var']);
            if(!empty($aresult)){
               $main->db->sql_query("UPDATE ".VOTING." SET vote_ip='{$vote['vote_ip']}{$ip},', vote_users='{$vote['vote_users']}{$main->user['user_name']},', result='{$vote['result']}".$aresult.",',count_vote=count_vote+1 WHERE id='{$vote['id']}'");
               $cook = isset($_COOKIE['voting']) ? $_COOKIE['voting'].$vote['id']."," : $vote['id'].",";
               setcookies($cook, "voting");
               add_points($points['voting']);
               return array(0,"Ok");
            } else return array(1,$main->lang['yourisvoted']);
         } else return array(1,$main->lang['yourisvoted']);
      } else return array(2,$main->lang['yourisnotselectvote']);
   }
?>
