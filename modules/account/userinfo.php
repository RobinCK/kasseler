<?php
   /**
   * Модуль информации по пользователю
   * 
   * @author Dmitrey Browko
   * @copyright Copyright (c)2011 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   */
   if (!defined('KASSELERCMS')) die("Hacking attempt!");
   global $main;
   function ui_comments(){
      global $main,$tpl_create,$adminfile,$database;
      if(hook_check(__FUNCTION__)) return hook();
      $count_in_page=30;
      $user=addslashes($_GET['user']);
      $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
      $offset = ($num-1) * $count_in_page;
      $result = $main->db->sql_query("SELECT * FROM ".COMMENTS." where name like '{$user}' ORDER BY cid DESC LIMIT {$offset}, {$count_in_page}");
      $rows_c = $main->db->sql_numrows($result);
      $bufdb=array();
      $tables=array();
      while(($row = $main->db->sql_fetchrow($result))){
         $bufdb[]=$row;
         $modul=$row['modul'];
         if(!isset($tables[$modul])) $tables[$modul]=array($row['parentid']);
         else $tables[$modul][]=$row['parentid'];
      }
      if($main->rewrite_id){
         foreach ($tables as $key => $value) {
            if($key=='account') $main->db->sql_query("select * from ".$database['prefix']."_users where uid in (".implode(",",$value).")");
            else $main->db->sql_query("select * from ".$database['prefix']."_{$key} where id in (".implode(",",$value).")");
            $tables[$key]=array();
            while (($row=$main->db->sql_fetchrow())){
               $tables[$key][isset($row['id'])?$row['id']:$row['uid']]=$row;
            }
         }
      }
      if($rows_c>0){
         $tr = 'row1';
         $i = (1*$num>1) ? ($count_in_page*($num-1))+1 : 1*$num;
         open();
         echo "<table width='100%' class='table'><tr><th width='15'>#</th><th>{$main->lang['comment']}</th><th width='100'>IP</th><th width='120'>{$main->lang['date']}</th><th width='80'>{$main->lang['module']}</th></tr>";
         foreach ($bufdb as $row) {
            $rid=$row['parentid'];
            $modul=$row['modul'];
            if(isset($tables[$modul])&&isset($tables[$modul][$rid])){
               $nm=($modul == "account" ? "user" : $modul)."_id";
               $mid=(isset($tables[$modul][$rid][$nm]))?$tables[$modul][$rid][$nm]:$rid;
               $nm=($modul == "account" ? "user_name" : "title");
               $title=(isset($tables[$modul][$rid][$nm]))?$tables[$modul][$rid][$nm]:"";
            } else {
               $mid=$rid;
               $title="";
            }
            $text="<a href='".$main->url(array('module'=>$row['modul'], 'do'=>$row['modul']!='account'?'more':'user', 'id'=> case_id($mid, $rid)))."#comment_".$row['cid']."'>".cut_text(strip_tags($row['comment']), 4)."</a>";
            $ip = is_admin()?$row['ip']:$main->lang['closed'];
            $module=isset($main->lang[$row['modul']])?$main->lang[$row['modul']]:$row['modul'];
            echo "<tr class='{$tr}'".(!empty($title)?" title='{$title}'":"")."><td align='center'>{$i}</td><td>{$text}</td><td align='center'>{$ip}</td><td align='center'>".user_format_date($row['date'])."</td><td align='center'><a href='".$main->url(array('module' => $row['modul']))."'>{$module}</a></td></tr>";
            $tr = ($tr=='row1') ? 'row2' : 'row1'; $i++;
         }
         echo "</table>";
         if ($rows_c==$count_in_page OR isset($_GET['page'])){
            //Получаем общее количество
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".COMMENTS." where name like '{$user}' "));
            //Если количество больше чем количество на страницу
            if($numrows>$count_in_page){
               //Открываем стилевую таблицу
               open();                
               //создаем страницы
               pages($numrows, $count_in_page, array('module' => $main->module,'do'=> 'userinfo','user'=>$_GET['user']), true, false, array(), false);
               //Закрываем стилевую таблицу
               close();
            }
         }        
         close();
      } else info($main->lang['noinfo']);
      if(is_ajax()) kr_exit();
   }
   if($_GET['do']=='userinfo'){
      $main->parse_rewrite(array('module', 'do', 'user','page'));
      bcrumb::add($main->lang['userinfo'],$main->url(array('module'=>'account','do'=>'user','id'=>$_GET['user'])));
      bcrumb::add($main->lang['user_list_comments']);
      ui_comments();
   } else kr_http_ereor_logs("404");

?>
