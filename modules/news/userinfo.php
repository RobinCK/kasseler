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
   global $nextpage, $main;
   function ui_news(){
      global $main,$news,$tpl_create,$nextpage,$navi;
      if(hook_check(__FUNCTION__)) return hook();
      //Выводим навигацию по модулю
      echo $navi;
      $user=addslashes($_GET['user']);
      $catlist=category_array();
      $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
      $offset = ($num-1) * $news['publications_in_page'];
      $where_news=" n.status='1' AND DATE_FORMAT(n.date, '%Y.%m.%d') <= '".date("Y.m.d")."' AND n.author like '{$user}' ";
      $main->db->sql_query("select count(n.id) as cnt from ".NEWS." AS n where {$where_news}");
      list($numrows)=$main->db->sql_fetchrow();
      //Выполняем запрос в БД
      $result = $main->db->sql_query("SELECT n.id, n.news_id, n.title, n.begin, n.author, n.date, n.view, n.comment, n.cid, n.status, n.language, n.voted, n.tags, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users
      FROM ".NEWS." AS n LEFT JOIN ".USERS." AS u ON (n.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='news' and r.idm=n.id)
      WHERE {$where_news}
      ORDER BY n.id LIMIT {$offset}, {$news['publications_in_page']}"); 
      //Узнаем количество полученных публикаций
      $rows = $main->db->sql_numrows($result);
      if($rows>0){
         if($numrows>$news['publications_in_page']){
            //Открываем стилевую таблицу
            open();
            //В зависимости от типа вывода создаем страницы
            pages($numrows, $news['publications_in_page'],$nextpage, true);
            //Закрываем стилевую таблицу
            close();
         }
         $i = (1*$num>1) ? ($news['publications_in_page']*($num-1))+1 : 1*$num;
         $line = "row1";
         //Перебираем результат SQL запроса
         main::init_function('rating');
         while(($row = $main->db->sql_fetchrow($result))){
            //Создаем массив параметров для вывода публикации
            $pub = array(
            'id'         => $row['id'],
            'rewrite_id' => $row['news_id'],
            'title'      => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['news_id'], $row['id'])))."' title='{$row['title']}'>{$row['title']}</a>",
            'content'    => parse_bb(preg_replace('/\[PAGE_BREAK\]/', "<br />", $row['begin'])),
            'views'      => $row['view'],
            'comment'    => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['news_id'], $row['id'])))."#comments' title='{$row['title']}'>{$row['comment']}</a>",
            'count_comm' => $row['comment'],
            'more'       => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['news_id'], $row['id'])))."' title='{$row['title']}'>{$main->lang['more']}</a>",
            'date'       => user_format_date($row['date']),
            'year'       => format_date($row['date'], 'Y'),
            'month'      => format_date($row['date'], 'm'),
            'day'        => format_date($row['date'], 'd'),
            'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
            'language'   => $row['language'],
            'category'   => cat_parse_new($row['cid'],$catlist),
            'favorite'   => "",
            'tags'       => ($news['tags_status']==ENABLED AND !empty($row['tags']))?list_tags($row['tags'], $main->module):'',
            'lang_tags'  => ($news['tags_status']==ENABLED AND !empty($row['tags']))?$main->lang['tags'].":":'',
            'lang_cat'   => $main->lang['category'],
            'lang_view'  => $main->lang['views'],
            'lang_comm'  => $main->lang['comments'],
            'lang_author'=> $main->lang['author'],
            'lang_date'  => $main->lang['date_pub'],
            'lang_title' => $main->lang['name'],
            'lang_month' => lang_month(format_date($row['date'], 'n')),
            'load_tpl'   => $main->tpl,
            'row'        => $line,
            'num_id'     => $i
            );
            $line = ($line=="row1") ? "row2" : "row1";
            //Если рейтинг активен  
            $pub = rating_modify_publisher($row['id'], 'news', $row, $pub, $news['ratings']==ENABLED);
            //Выводим публикацию
            publisher($row['id'], $pub);
            $i++;
         }
         if($numrows>$news['publications_in_page']){
            //Открываем стилевую таблицу
            open();
            //В зависимости от типа вывода создаем страницы
            pages($numrows, $news['publications_in_page'],$nextpage, true);
            //Закрываем стилевую таблицу
            close();
         }         
      }
      if(is_ajax()) exit;
   }
   if($_GET['do']=='userinfo'){
      $main->parse_rewrite(array('module', 'do', 'user','page'));
      $nextpage=array('module'=>$main->module, 'do'=>$_GET['do'], 'user'=>$_GET['user']);
      bcrumb::add($main->lang['userinfo'],$main->url(array('module'=>'account','do'=>'user','id'=>$_GET['user'])));
      bcrumb::add($main->lang['user_list_news']);
      ui_news();
   } else kr_http_ereor_logs("404");

?>
