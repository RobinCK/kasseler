<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('ADMIN_FILE')) die("Hacking attempt!");

   global $main, $config, $title, $navi;
   main::init_language('optimiztion');
   $break_load = false;
   if(is_moder()) {
      warning($main->lang['moder_error']);
      $break_load = true;
   } elseif(!empty($main->user['user_adm_modules']) AND !in_array($main->module, explode(',', $main->user['user_adm_modules']))){
      warning($main->lang['admin_error']);
      $break_load = true;
   }
   $navi = array(array('', 'optimization_master'), array('config', 'config'));

   global $lang_date, $list_optimize_date, $list_period, $default_optimize_date, $optimize_date,
   $table_stat;
   $optimize_date=array();

   $_list_optimize_date=array(
      'news'=>'optimize_news',
      'forum'=>'optimize_forum',
      'comment'=>'optimize_comment',
      'usac'=>'optimize_user_activation',
      'user_lastvis'=>'optimize_user_last_visit',
      'audio'=>'',
      'files'=>'',
      'jokes'=>'','albom'=>''
   );
   $_default_optimize_date=array(
      'news'=>"P6M",
      'forum'=>"P1Y",
      'comment'=>"P1Y",
      'usac'=>"P1M",
      'user_lastvis'=>"P6M",
      'audio'=>"P1Y",
      'files'=>"P1Y",
      'jokes'=>"P1Y",
      'albom'=>"P1Y",
   );

   $list_optimize_date = !empty($list_optimize_date) ? array_merge($list_optimize_date, $_list_optimize_date) : $_list_optimize_date;
   $default_optimize_date = !empty($default_optimize_date) ? array_merge($default_optimize_date, $_default_optimize_date) : $_default_optimize_date;

   $file_config="includes/config/config_optimization.php";
   if(file_exists($file_config)){
      main::required($file_config);
   }
   foreach ($default_optimize_date as $key => $value) {
      if(!isset($optimize_date[$key])) $optimize_date[$key]=$value;
   }
   $list_period=array('D'=>$main->lang['period_day'],'M'=>$main->lang['period_month'],'Y'=>$main->lang['period_year']);

   $lang_date= mb_strtolower($main->lang['date']);
   function adm_before_optimization(){
      global $main, $table_stat, $database;
      if(hook_check(__FUNCTION__)) return hook();
      $dbr=$main->db->sql_query("SHOW TABLE STATUS FROM `{$database['name']}`");
      while (($row=$main->db->sql_fetchrow($dbr))){
         $table_stat[$row['Name']]=array('count'=>$row['Rows'],'size'=>($row['Data_length']+$row['Index_length']));
      }
   }
   function optm_gen_calendar($code_name){
      global $main, $lang_date, $optimize_date;
      if(hook_check(__FUNCTION__)) return hook();
      $date = new DateTime();
      if(preg_match('/P([0-9]*)(D|M|Y)/', $optimize_date[$code_name], $regs)){
         $dm=($regs[2]=='D')?"day":(($regs[2]=='M')?"months":"years");
         $date->modify("-{$regs[1]} {$dm}");
      }
      $prev_date=$date->format("d.m.Y");
      return "<span>, {$lang_date}</span>".in_text("dateb[{$code_name}]",'input_text_b',$prev_date,false," size='12'").
      "<img id='button_calendar_{$code_name}' class='input_calendar' alt='{$main->lang['calendar']}' title='{$main->lang['calendar']}' src='".TEMPLATE_PATH."admin/images/date.png' style='cursor: pointer;'/>";
   }
   function adm_gen_optimization_record($code_name,$lname,$lnamed,$show_date=true){
      global $main,$tpl_create;
      if(hook_check(__FUNCTION__)) return hook();
      main::add2script("includes/javascript/kr_calendar2.js");
      main::add2link("includes/css/kr_calendar.css");
      return "<div class='elem_op'>".(!empty($lname)?"<b>{$lname}</b><br />":"").
      "{$lnamed}".(!empty($code_name)?("<div class='calendar'>".in_chck("added[{$code_name}]",'checkbox')."<label for='added{$code_name}'>{$main->lang['optimization_add']}</label>".($show_date?optm_gen_calendar($code_name):"")."</div>"):"")."</div>\n";
   }
   function adm_gen_optimization_module($module_list){
      global $main, $modules;
      if(hook_check(__FUNCTION__)) return hook();
      $ret="";
      foreach ($module_list as $key => $value) {
         if(isset($modules[$value]))  $ret.=adm_gen_optimization_record($value,str_replace('{MODULE}',$main->lang[$value],$main->lang['optimize_custom']),$main->lang['optimize_custom_d']);
      }
      return $ret;
   }
   function adm_optimization_main(){
      global $main, $tpl_create, $adminfile, $userconf, $lang_date;
      if(hook_check(__FUNCTION__)) return hook();
      main::add2script("includes/javascript/jquery/jquery.disable.text.select.js");
      main::add_css2head("
         .elem_op{display:none}
         .elem_op input[type='checkbox'] {vertical-align: middle;}
         .elem_op label{margin-left:5px; vertical-align: middle;cursor:pointer;}
         #navigate{margin:10px 0; height:35px}
         #info{height:100px}
         #list_active ul {list-style-image: url('".TEMPLATE_PATH."admin/images/okay.png');}
         #list_active ul li{padding:0;margin: 2px 0 2px 22px;font-weight: bolder;}
         #list_active a{color:green}
         div.calendar * {vertical-align: middle;}
         div.calendar input {margin-left:5px;}
      ");
      open();
      echo "<form id='optimization_form' method='post' action='{$adminfile}?module={$_GET['module']}&amp;do=run_optimization'>";
      echo "<div style='margin: 10px;'><div id='info'>".
      adm_gen_optimization_record('','',$main->lang['optimize_description']).
      adm_gen_optimization_module(array('news','audio','files','jokes','albom')).
      adm_gen_optimization_record('forum',$main->lang['optimize_forum'],$main->lang['optimize_forum_d']).
      adm_gen_optimization_record('comment',$main->lang['optimize_comment'],$main->lang['optimize_comment_d']).
      adm_gen_optimization_record('log',$main->lang['optimize_log_access'],$main->lang['optimize_log_access_d'],false).
      (($userconf['registration']!='all')?adm_gen_optimization_record('usac',$main->lang['optimize_user_activation'],$main->lang['optimize_user_activation_d']):"").
      adm_gen_optimization_record('user_lastvis',$main->lang['optimize_user_last_visit'],$main->lang['optimize_user_last_visit_d']).
      "</div><div id='navigate'>".
      "<a class='d_button' id='prev' onclick='prev();'><b>{$main->lang['prev_page']}</b></a>".
      "<a class='d_button' id='next' onclick='next();'><b>{$main->lang['next_page']}</b></a>".
      "<a class='d_button' id='run' style='float: right;' onclick='submit();'><b>{$main->lang['run']}</b></a>".
      "</div><div id='list_active'><div style='font-weight: bolder;color:green;'>{$main->lang['optimization_selected']}:</div><ul><li>&nbsp;</li></ul></div></div>";
      echo "</form>";
      close();
   ?>
   <script type="text/javascript">
      //<![CDATA[
      <?php echo "date_lang='{$main->lang['clear_to']}';\n";   ?>
      info=$('#info');
      lactive=$('#list_active').find('ul');
      lactive.find('li').remove();
      info.find('.elem_op:first').show();
      $('#prev').css({visibility:'hidden'});
      $run=$('#run').css({visibility:'hidden'});
      function next(){
         var dv=info.find('.elem_op:visible');
         var nx=dv.next();
         if(nx.length!=0){
            dv.hide();
            nx.show();
            $('#prev').css({visibility:''});
            if(nx.next().length==0) $('#next').css({visibility:'hidden'});
         } else $('#next').css({visibility:'hidden'});
      }
      function prev(){
         var dv=info.find('.elem_op:visible');
         var nx=dv.prev();
         if(nx.length!=0){
            dv.hide();
            nx.show();
            $('#next').css({visibility:''});
            if(nx.prev().length==0) $('#prev').css({visibility:'hidden'});
         } else $('#prev').css({visibility:'hidden'});
      }
      function submit(){
         $('#optimization_form').get(0).submit();
      }
      function update_selected(){
         lactive.find('li').remove();
         var ls=info.find(':checked').each(function(){
               var text=$(this).parents('.elem_op:first').find('b:first').text();
               var datein=$(this).parents('.elem_op:first').find('.input_text_b');
               if(datein.length>0) text=text+": <a>"+date_lang+" "+datein.val()+"</a>";
               $('<li/>').html(text).appendTo(lactive);
         });
         $run.css({visibility:((ls.length>0)?'':"hidden")});
      }
      info.find(':checkbox').on('click',function(e){
            update_selected();
      });
      info.find('.input_text_b').on('change',function(){
            update_selected();
      });
      $(document).ready(function(){
            if($.fn.disableTextSelect) info.parent().find('label,.d_button').disableTextSelect();
            $('.input_text_b').each(function(){
                  var el_id=this.id;
                  var cl='button_calendar_'+el_id.substring(5);
                  KR_AJAX.kr_calendar.init(cl, {el:el_id});
            });
            KR_AJAX.kr_calendar.onchange=update_selected;
      });
      //]]>
   </script>
   <?php
   }
   /**
   * Чистим данные о пользователях
   * 
   * @param mixed $dbwhere
   */
   function adm_remove_users($dbwhere){
      global $main,$userconf;
      if(hook_check(__FUNCTION__)) return hook();
      $dbr=$main->db->sql_query("select * from ".USERS." where {$dbwhere}");
      while ($row=$main->db->sql_fetchrow($dbr)){
         if(strtolower($row['user_avatar'])!='default.png'){
            $avatar=$userconf['directory_avatar'].$row['user_avatar'];
            if(file_exists($avatar)) unlink($avatar);
         }
      }
      $main->db->sql_query("delete from ".USERS." where {$dbwhere}");
   }
   function adm_empty_news($date_db){
      global $main, $news;
      if(hook_check(__FUNCTION__)) return hook();
      $dbr= $main->db->sql_query("select id from ".NEWS." where `date`<'{$date_db}' and (`begin` regexp 'end attach' or content regexp 'end attach') ");
      // чистим аттачи
      while ($row=$main->db->sql_fetchrow($dbr)){
         $pach=$news['directory'].$row['id']."/";
         if(file_exists($pach)) remove_dir($pach);
         //удаляем из таблицы attache
         $main->db->sql_query("delete from ".ATTACH." where module='news' and path='{$pach}'");
      }
      // удаляем сами ньюсы
      $main->db->sql_query("delete from ".NEWS." where `date`<'{$date_db}'");
      // удяляем коментарии по ньюсам, удаляем так что б чистить и те записи которые по какой-то причине когда-то неудалились
      $main->db->sql_query("DELETE c.* FROM ".COMMENTS." c left JOIN ".NEWS." n on (c.parentid=n.id)
      WHERE c.modul='news' and n.id is null");
   }
   function adm_empty_forum($date_dec){
      global $main, $forum;
      if(hook_check(__FUNCTION__)) return hook();
      $dbr=$main->db->sql_query("select g.post_id from ".POSTS." g, ".TOPICS." t left JOIN ".POSTS." p ".
         "on (p.topic_id=t.topic_id and p.post_time>{$date_dec})
         where p.post_id is null and g.topic_id=t.topic_id");
      // чистим аттачи постов
      while ($row=$main->db->sql_fetchrow($dbr)){
         $pach=$forum['directory'].$row['post_id']."/";
         if(file_exists($pach)) remove_dir($pach);
         //удаляем из таблицы attache
         $main->db->sql_query("delete from ".ATTACH." where module='forum' and path='{$pach}'");
      }
      // чистим топики
      $main->db->sql_query("DELETE t.*
         FROM ".TOPICS." t left JOIN ".POSTS." p on (p.topic_id = t.topic_id and p.post_time>{$date_dec})
         WHERE p.post_id is null");
      // чистим посты
      $main->db->sql_query("DELETE p.*
         FROM ".POSTS." p left JOIN ".TOPICS." t on (t.topic_id = p.topic_id) WHERE t.forum_id is null");
      // фиксим все forum_last_post_id (а вдруг у форума уже нет постов)
      $main->db->sql_query("update ".FORUMS." f left join ".POSTS." p on (p.forum_id=f.forum_id)
      set f.forum_last_post_id=0 where p.post_id is null");
      // фиксим таблицу доступа
      $main->db->sql_query("delete a.* from ".FORUM_ACC." a left join ".FORUMS." f on (f.forum_id=a.idv)
      where a.typeacc='f' and f.forum_id is null");
      $main->db->sql_query("delete a.* from ".FORUM_ACC." a left join ".CAT_FORUM." c on (c.cat_id=a.idv)
      where a.typeacc='c' and c.cat_id is null");
   }
   function adm_empty_comment($date_db){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("delete from ".COMMENTS." where `date`<'{$date_db}'");
   }
   function adm_empty_log($date_db){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $paths=array('logined_logs.log','php_logs.log','http_logs.log','sql_logs.log');
      foreach ($paths as $value) {
         $pach="uploads/logs/{$value}";
         if(file_exists($pach)) unlink($pach);
      }
   }
   function adm_empty_usac($date_db){
      global $main, $userconf;
      if(hook_check(__FUNCTION__)) return hook();
      if($userconf['registration']!='all'){
         adm_remove_users("user_activation<>0 and user_regdate<'{$date_db}'");
      }
   }
   function adm_empty_user_lastvis($date_db){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      adm_remove_users("user_last_visit<'{$date_db}'");
   }

   function adm_empty_audio($date_db){
      global $main, $audio;
      if(hook_check(__FUNCTION__)) return hook();
      $dbr= $main->db->sql_query("select `file` from ".AUDIO." where `date`<'{$date_db}' ");
      // чистим аттачи
      while ($row=$main->db->sql_fetchrow($dbr)){
         $pach=$audio['directory']."record/".$row['file'];
         if(file_exists($pach)) unlink($pach);
      }
      // удаляем сами аудио
      $main->db->sql_query("delete from ".AUDIO." where `date`<'{$date_db}'");
      // удяляем коментарии по ньюсам, удаляем так что б чистить и те записи которые по какой-то причине когда-то неудалились
      $main->db->sql_query("DELETE c.* FROM ".COMMENTS." c left JOIN ".AUDIO." n on (c.parentid=n.id)
      WHERE c.modul='audio' and n.id is null");
   }
   function adm_empty_files($date_db){
      global $main, $files;
      if(hook_check(__FUNCTION__)) return hook();
      $dbr= $main->db->sql_query("select id from ".FILES." where `date`<'{$date_db}' and (`description` regexp 'end attach' or content regexp 'end attach') ");
      // чистим аттачи
      while ($row=$main->db->sql_fetchrow($dbr)){
         $pach=$files['directory'].$row['id']."/";
         if(file_exists($pach)) remove_dir($pach);
         //удаляем из таблицы attache
         $main->db->sql_query("delete from ".ATTACH." where module='files' and path='{$pach}'");
      }
      // удаляем сами FILES
      $main->db->sql_query("delete from ".FILES." where `date`<'{$date_db}'");
      // удяляем коментарии по файлам
      $main->db->sql_query("DELETE c.* FROM ".COMMENTS." c left JOIN ".FILES." n on (c.parentid=n.id)
      WHERE c.modul='files' and n.id is null");
   }
   function adm_empty_jokes($date_db){
      global $main, $jokes;
      if(hook_check(__FUNCTION__)) return hook();
      $main->db->sql_query("delete from ".JOKES." where `date`<'{$date_db}'");
      // удяляем коментарии
      $main->db->sql_query("DELETE c.* FROM ".COMMENTS." c left JOIN ".JOKES." n on (c.parentid=n.id)
      WHERE c.modul='jokes' and n.id is null");
   }
   function adm_empty_albom($date_dec){
      global $main, $albom;
      $dbr= $main->db->sql_query("select `time` from ".ALBOM." where `time`<{$date_dec}");
      // чистим аттачи
      while ($row=$main->db->sql_fetchrow($dbr)){
         $pach=$albom['directory'].$row['time']."/";
         if(file_exists($pach)) remove_dir($pach);
      }
      $main->db->sql_query("delete from ".ALBOM." where `time`<'{$date_dec}'");
      // удяляем коментарии по файлам
      $main->db->sql_query("DELETE c.* FROM ".COMMENTS." c left JOIN ".ALBOM." n on (c.parentid=n.id)
      WHERE c.modul='albom' and n.id is null");
   }

   function adm_run_optimization(){
      global $main, $database, $news, $forum, $userconf, $audio;
      if(hook_check(__FUNCTION__)) return hook();
      $added=$_POST['added'];
      $dateb=$_POST['dateb'];
      adm_before_optimization();
      foreach ($added as $key => $value) {
         if($value=='on'){
            if(isset($dateb[$key])){
               $date_dec=strtotime($dateb[$key]);
               $date_db=date("Y-m-d",$date_dec);
            }
            switch ($key){
               case "news": //чистим новости
                  adm_empty_news($date_db);
                  break;
               case "forum": //чистим форум
                  adm_empty_forum($date_dec);
                  break;
               case "comment": //удаляем коментарии
                  adm_empty_comment($date_db);
                  break;
               case "log":// чистим логи
                  adm_empty_log($date_db);
                  break;
               case "usac"://удаляем неактивировавшихся пользователей 
                  adm_empty_usac($date_db);
                  break;
               case "user_lastvis":// удаление пользователей которые давно незаходили
                  adm_empty_user_lastvis($date_db);
                  break;
               case "audio":// Удаление старых аудиозаписей
                  adm_empty_audio($date_db);
                  break;
               case "files":// Удаление старых файлов из модуля "Файлы"
                  adm_empty_files($date_db);
                  break;
               case "jokes":// удаляем сами анекдоты
                  adm_empty_jokes($date_db);
                  break;
               case "albom":// Удаление старых файлов из модуля "Альбом"
                  adm_empty_albom($date_dec);
                  break;
            }
         }
      }
      $main->db->sql_query("delete from ".FORUM_SEARCH);
      $main->db->sql_query("delete from ".FORUM_KEYS);
      $main->db->sql_query("delete from ".SEARCH);
      $main->db->sql_query("delete from ".SEARCH_KEY);
      $dbr=$main->db->sql_query("SHOW TABLES FROM `{$database['name']}`");
      while ((list($name)=$main->db->sql_fetchrow($dbr))){
         $main->db->sql_query("OPTIMIZE TABLE `{$name}`");
      }
      global $table_stat;
      $dbr=$main->db->sql_query("SHOW TABLE STATUS FROM `{$database['name']}`");
      while (($row=$main->db->sql_fetchrow($dbr))){
         if($table_stat[$row['Name']]['count']!=$row['Rows']){
            $search=array('{TABLE}','{COUNT}');
            $replace=array($row['Name'],($table_stat[$row['Name']]['count']-$row['Rows']));
            echo str_replace($search,$replace,$main->lang['change_table'])."<br />";
         }
      }
   }
   function gen_config_row($key,$text){
      global $list_period, $main,$optimize_date;
      if(isset($optimize_date[$key])&&preg_match('/P([0-9]*)(D|M|Y)/', $optimize_date[$key], $regs)){
         $intval=intval($regs[1]);
         $listval=$regs[2];
      } else {
         $intval=0;
         $listval="D";
      }
      $lang_text=isset($main->lang[$text])?$main->lang[$text]:str_replace('{MODULE}',$main->lang[$key],$main->lang['optimize_custom']);
      return "<tr>".
      "<td align='right' width='50%'>{$lang_text}</td>".
      "<td>".in_text("counts[{$key}]",'counts',$intval,false).in_sels("typec[{$key}]",$list_period,"",$listval)."</td>".
      "</tr>";
   }
   function adm_config_optimization(){
      global $main,$tpl_create, $list_optimize_date, $adminfile;
      if(hook_check(__FUNCTION__)) return hook();
      main::add_css2head("
         .counts{width:40px;text-align: right;}
      ");
      open();
      echo "<form method='post' action='{$adminfile}?module={$main->module}&amp;do=save_config'>\n";
      echo "<b>{$main->lang['optimize_config']}</b>";
      echo "<table width='100%' class='table'>";
      foreach ($list_optimize_date as $key => $value) {
         echo gen_config_row($key,$value);
      }
      echo "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
      "</table></form>";
      close();
   }
   function adm_save_config_optimization(){
      global $main, $optimize_date;
      if(hook_check(__FUNCTION__)) return hook();
      main::init_function("sources");
      $counts=$_POST['counts'];
      $typec=$_POST['typec'];
      foreach ($counts as $key => $value) {
         if(isset($typec[$key])&&isset($optimize_date[$key])) $optimize_date[$key]="P{$counts[$key]}{$typec[$key]}";
      }
      save_config("config_optimization.php",'$optimize_date',$optimize_date);
      redirect(MODULE);
   }
   function switch_admin_optimization(){
      global $main, $break_load;
      if(hook_check(__FUNCTION__)) return hook();
      if(isset($_GET['do']) AND $break_load==false){
         switch($_GET['do']){       
            case "run_optimization":adm_run_optimization();break;
            case "config":adm_config_optimization();break;
            case "save_config":adm_save_config_optimization();break;
            default: adm_optimization_main(); break;
         }
      } else  adm_optimization_main();
   }
   switch_admin_optimization();
?>