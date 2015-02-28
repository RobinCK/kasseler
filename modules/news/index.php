<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main, $news;
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'popular')), $main->lang['popular'], "popular");
if($news['tags_page_status']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'tags')), $main->lang['tags'], "tags");
if(is_user() AND $news['favorite_page']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'favorite')), $main->lang['favorite'], "favorite");
if($news['categories']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'categoryes')), $main->lang['categoryes'], "categoryes");
if($news['list_publications']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'list')), $main->lang['list'], "list");
if(($news['publications_users']==ENABLED AND is_user()) OR ($news['publications_guest']==ENABLED AND is_guest()) OR is_support()) $links[] = array($main->url(array('module' => $main->module, 'do' => 'add')), $main->lang['add_news'], "add");
$navi = navi($links);
//Подключаем глобальные функции модуля
if(isset($_GET['module'])) main::required("modules/{$main->module}/globals.php");
bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));

function main_news(){
global $news, $main, $navi, $userinfo;
    if(hook_check(__FUNCTION__)) return hook();
    //Объявляем переменные
    $wheres = $where = "";
    //Определяем тип вывода новостей
    if(isset($_GET['do']) AND in_array($_GET['do'], array('list', 'popular', 'category', 'favorite', 'tags'))) $wheres = $_GET['do'];
    //Если не главная страница сайта, выводим навигацию модуля
    if(!is_home()) echo $navi;
    if($wheres=="list"){ //Если вывод списком
        $main->parse_rewrite(array('module', 'do', 'page', 'id'));
        if(isset($_GET['page']) AND !isset($_GET['id']) AND !preg_match('/([0-9]+)$/i', $_GET['page'])){
            $_GET['id'] = $_GET['page'];
            unset($_GET['page']);
        }
        //Устанавливаем количество публикаций на страницу
        //$news['publications_in_page'] = 30;
        //Инициализируем класс вывода списком
        $pub_list = new pub_list();
        //Если выбрана сортировка по букве
        if(isset($_GET['id'])){
            //Добавляем букву в title
            add_meta_value($_GET['id']);
            //Дополняем запрос выборки публикаций
            $where = " AND UPPER(n.title) LIKE BINARY('".$_GET['id']."%')";
            bcrumb::add($main->lang['list'],$main->url(array('module' => $main->module, 'do' => 'list')));
            bcrumb::add($_GET['id']);
        } else bcrumb::add($main->lang['list']);
        //Выводим список букв
        list_liter();
    } elseif($news['clasic_cat']==ENABLED AND !is_home()) show_category($news['cat_cols']);
    //Устанавливаем правила ЧПУ
    if(empty($wheres)) $main->parse_rewrite(array('module', 'page'));
    elseif($wheres=="popular") {
       $main->parse_rewrite(array('module', 'do', 'page'));
       bcrumb::add($main->lang[$_GET['do']]);
    }
    elseif($wheres=="favorite" AND $news['favorite_page']==ENABLED) {
        if(!is_user()) redirect(MODULE);
        bcrumb::add($main->lang[$wheres]);
        $main->parse_rewrite(array('module', 'do', 'page'));
        $result = $main->db->sql_query("SELECT post FROM ".FAVORITE." WHERE modul='{$main->module}' AND users='{$main->user['user_name']}'");
        $rows = $main->db->sql_numrows($result);
        if($rows>0){ 
            $i = 0;
            $post = '';
            while(($row = $main->db->sql_fetchrow($result))){
                $post .= ($i == 0) ? "n.id='{$row['post']}'" : " OR n.id='{$row['post']}'" ;
                $i++;
            }
            $where .= " AND ({$post})";
        } else $favorite = true;
    } elseif($wheres=="category"){
        //Устанавливаем правила ЧПУ для category
        $main->parse_rewrite(array('module', 'do', 'id', 'page'));
        $where .= ($main->rewrite_id) ?sql_check_chpu_categorys("n") : " AND n.cid LIKE BINARY('%,".$_GET['id'].",%')";
        //Определяем название просматриваемой категории
        list($cat_title) = $main->db->sql_fetchrow($main->db->sql_query("SELECT title FROM ".CAT." WHERE  module='{$main->module}' AND ".($main->rewrite_id?"cat_id='{$_GET['id']}'":"cid='{$_GET['id']}'").""));
        bcrumb::add($main->lang['categoryes'],$main->url(array('module' => $main->module, 'do' => 'categoryes')));
        bcrumb::add($cat_title);
        //Добавляем категорию в title
        add_meta_value($main->lang['category']." ".$cat_title);
    } elseif($wheres=="tags") {
        $tag = (isset($_GET['id'])) ? kr_filter($_GET['id'], TAGS) : '';
        if($tag!='') {
            $main->parse_rewrite(array('module', 'do', 'id', 'page'));
            bcrumb::add($main->lang['tags'],$main->url(array('module' => $main->module, 'do' => 'tags')));
            bcrumb::add($tag);
            $result = $main->db->sql_query("SELECT post FROM ".TAG." WHERE modul='{$main->module}' AND tag='{$tag}'");
            if($main->db->sql_numrows($result)>0){
                $ih =1;
                while(($row = $main->db->sql_fetchrow($result))){			
                    $where.= ($ih==1) ? " AND (n.id='{$row['post']}'" : " OR n.id='{$row['post']}'";
                    $ih++;
                }
                $where.= ")";
            }
        } else {
            bcrumb::add($main->lang[$wheres]);
            $tags = true;
        }
    }
    $catlist=category_array();
    if(is_guest()){
       $atable="";
       $awhere=" g.id=4 ";
    } else {
       $atable=USERS." AS ug,";
       $awhere=" ug.uid={$userinfo['uid']} and (ug.user_groups like concat('%,',g.id,',%') or ug.user_group=g.id) ";
    }
    $awhere="exists(select g.id FROM {$atable} ".GROUPS." AS g WHERE {$awhere} AND n.vgroups LIKE concat('%,', g.id, ',%'))";
    if(!isset($favorite) and !isset($tags)){
        //Определяем тип сортировки публикаций
        $order_by = ($wheres!="popular") ? "n.{$news['sort_publications']} {$news['sort_type_publications']}" : "n.view DESC";
        //Определяем текущую страницу
        $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
        $offset = ($num-1) * $news['publications_in_page'];
        if($offset<0) kr_http_ereor_logs(404);
        //Инициализация переменных для определения закладок
        $favorite_select = (is_user() AND $news['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $news['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=n.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';	
        //Выполняем запрос в БД
        $base_sql="SELECT distinct n.id, n.news_id, n.title, n.fix_news, n.begin, n.author, n.date, n.view, n.comment, n.cid, n.status, n.language, n.voted, n.tags, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select}
            FROM ".NEWS." AS n LEFT JOIN ".USERS." AS u ON (n.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='news' and r.idm=n.id) {$favorite_table}
            WHERE n.status='1' AND n.fix_news = '{FIX_VAL}' AND (n.language='{$main->language}' OR n.language='') AND DATE_FORMAT(n.date, '%Y.%m.%d') <= '".date("Y.m.d")."' ".
            " and (n.vgroups is null or n.vgroups='' or {$awhere}) {$where}";
        $sql="(".str_replace('{FIX_VAL}','y',$base_sql)." ORDER BY {$order_by} ) ";
        $sql.="\n union \n (".str_replace('{FIX_VAL}','n',$base_sql)." ORDER BY {$order_by} LIMIT {$offset}, {$news['publications_in_page']}) ".
        "\n  ORDER BY fix_news DESC ,".str_replace('n.','',$order_by)." LIMIT 0, {$news['publications_in_page']}";
        $result = $main->db->sql_query($sql,__FUNCTION__."_s"); 
        //Узнаем количество полученных публикаций
        $rows = $main->db->sql_numrows($result);
    }
    if(!empty($awhere)) $sql_count="SELECT COUNT(*) FROM ".NEWS." AS n WHERE n.status='1' AND (n.language='{$main->language}' OR n.language='') AND DATE_FORMAT(n.date, '%Y.%m.%d') <= '".date("Y.m.d")."' and (n.vgroups is null or n.vgroups='' or {$awhere}) {$where}";
     else $sql_count="SELECT COUNT(*) FROM ".NEWS." AS n WHERE n.status='1' AND (n.language='{$main->language}' OR n.language='') AND DATE_FORMAT(n.date, '%Y.%m.%d') <= '".date("Y.m.d")."'{$where}";
    if(!isset($favorite) and !isset($tags) and $rows>0){
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
                'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
                'language'   => $row['language'],
                'category'   => cat_parse_new($row['cid'],$catlist),
                'favorite'   => (is_user() AND $news['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
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
            //Если рейтинг 
            $pub = rating_modify_publisher($row['id'], 'news', $row, $pub, $news['ratings']==ENABLED);
            //Выводим публикацию
            if($wheres!="list") publisher($row['id'], $pub); else $pub_list->add_row($row['id'], $pub);
            $i++;
        }
        //Если страница вывода списком выводим публикации через класс
        if($wheres=="list") $pub_list->init();
        //Проверяем нужно ли выводить номера страниц
        if ($rows==$news['publications_in_page'] OR isset($_GET['page'])){
            //Получаем общее количество публикаций
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query($sql_count,__FUNCTION__."_c"));
            //Если количество публикаций больше чем количество публикаций на страницу
            if($numrows>$news['publications_in_page']){
                //Открываем стилевую таблицу
                open();
                //В зависимости от типа вывода создаем страницы
                if(empty($wheres)) pages($numrows, $news['publications_in_page'], array('module' => $main->module), true);
                elseif($wheres=="category") pages($numrows, $news['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);
                elseif($wheres=="tags") pages($numrows, $news['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);
                elseif($wheres=="list") pages($numrows, $news['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true, false, isset($_GET['id'])?array('id' => $_GET['id']):array());
                else pages($numrows, $news['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else {
        if (!isset($tags)) info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
        else {
            $content = kr_create_tags($main->module);
            if ($content!='') {
                open();
                echo $content;
                close();
            } else info($main->lang['noinfo']); 
        }
    }
}

function categoryes_news(){
global $news, $navi, $main;
    if(hook_check(__FUNCTION__)) return hook();
    //Выводим навигацию по модулю
    echo $navi;
    bcrumb::add($main->lang[$_GET['do']]);
    //Делаем выборку всех категорий модуля
    $result = $main->db->sql_query("SELECT cid, cat_id, title, image, description FROM ".CAT." WHERE module='{$main->module}' ORDER BY BINARY(UPPER(title))");
    //Проверяем количество найденных категорий
    if($main->db->sql_numrows($result)>0){
        //Объявляем нужные переменные
        $array_cat = array(); $i=0;   
        //Создаем массив категорий
        while(list($cid, $cat_id, $title, $image, $description) = $main->db->sql_fetchrow($result)) $array_cat[] = array('cid' => $cid, 'cat_id' => $cat_id, 'title'  => $title, 'image'  => $image, 'description' => $description);        
        //Открываем стилевую таблицу
        open();
        //Создаем список категорий
        echo "<table class='catlist'>";
        while($i<count($array_cat)){
            echo "<tr><td>";
            for($y=1;$y<=$news['cat_cols'];$y++){
                if(!isset($array_cat[$i+$y-1])) continue;
                $link = array(
                    'name' => $array_cat[$i+$y-1]['title'],
                    'ico'  => ($news['categories_ico']==ENABLED AND $array_cat[$i+$y-1]['image']!='no.png') ? "<img src='includes/images/cat/{$array_cat[$i+$y-1]['image']}' alt='{$array_cat[$i+$y-1]['title']}' />" : "",
                    'desc' => ($news['categories_desc']==ENABLED) ? cut_char($array_cat[$i+$y-1]['description']) : ""
                );
                echo "<div style='width: ".round(100/$news['cat_cols'], 2)."%;'><a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($array_cat[$i+$y-1]['cat_id'], $array_cat[$i+$y-1]['cid'])))."' title='{$link['name']}'><span>{$link['ico']}<b>{$link['name']}</b><br />{$link['desc']}</span></a></div>";
            }
            echo "</td></tr>";
            $i+=$news['cat_cols'];
        }
        echo "</table>";
        //Закрываем стилевую таблицу
        close();
    } else info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
}

function more_news($msg=""){
global $news, $main, $tpl_create, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    //Подключаем модуль комментариев
    main::init_function(array('comments','bool'));
    main::init_class("afields");
    //Если установлен ID, пытаемся создать комментарий
    if(isset($_POST['id'])) add_comment(NEWS, $news['comments_sort'], $news['guests_comments'], 'more');
    else {
        //Выводим навигацию по модулю
        if($_GET['do']!='print') echo $navi;
        //Создаем условие выборки
        $where = ($main->rewrite_id) ? "n.news_id='{$_GET['id']}'" : "n.id='{$_GET['id']}'";
        //Инициализация переменных для определения закладок
        $favorite_select = (is_user() AND $news['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $news['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=n.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';  
        $catlist=category_array();
        //Выбираем публикацию
        $result = $main->db->sql_query("SELECT n.id, n.news_id, n.title, n.content, n.author, n.date, n.view, n.comment, n.cid, n.status, n.language, n.show_comment, n.voted, n.tags, n.vgroups, n.afields, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select}
            FROM ".NEWS." AS n LEFT JOIN ".USERS." AS u ON (n.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='news' and r.idm=n.id) {$favorite_table}
            WHERE".(!is_support()?" n.status='1' AND DATE_FORMAT(n.date, '%Y.%m.%d') <= '".date("Y.m.d")."' AND":'')." {$where}");
        //Проверяем наличие публикации с заданным ID
        if($main->db->sql_numrows($result)>0){
            $row = $main->db->sql_fetchrow($result);
            $af=new afields($row['afields']);
            if(check_user_group($row['vgroups'])){
               //Обновляем просмотры публикации
               $main->db->sql_query("UPDATE ".NEWS." SET view=view+1 WHERE id='{$row['id']}'");
               $row['view']++;
               //Добавляем заголовок в title
               add_meta_value($row['title'], $af);
               //Форматируем содержимое публикации
               $content = pagebreak($row['content'], $news['page_break']);
               //Выводим публикацию
               main::init_function('rating');
               $pub = array(
                  'id'         => $row['id'],
                  'rewrite_id' => $row['news_id'],
                  'title'      => $row['title'],
                  'content'    => parse_bb($content),
                  'date'       => user_format_date($row['date']),
                  'year'       => format_date($row['date'], 'Y'),
                  'month'      => format_date($row['date'], 'm'),
                  'day'        => format_date($row['date'], 'd'),
                  'lang_month' => lang_month(format_date($row['date'], 'n')),
                  'views'      => $row['view'],
                  'language'   => $row['language'],
                  'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
                  'category'   => cat_parse_new($row['cid'],$catlist),
                  'favorite'   => (is_user() AND $news['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
                  'tags'       => ($news['tags_status']==ENABLED AND !empty($row['tags']))?list_tags($row['tags'], $main->module):'',
                  'lang_tags'  => ($news['tags_status']==ENABLED AND !empty($row['tags']))?$main->lang['tags'].":":'',
                  'load_tpl'   => $main->tpl,
                  'lang_cat'   => $main->lang['category'],
                  'lang_view'  => $main->lang['views'],
                  'lang_author'=> $main->lang['author'],
                  'print'      => "<a target='_BLANK' href='".$main->url(array('module' => $main->module, 'do' => 'print', 'id' => $_GET['id']))."' title='{$main->lang['print_version']}'>{$main->lang['print_version']}</a>",
                  'lang_date'  => $main->lang['date_pub']
               );
               $pub = rating_modify_publisher($row['id'], 'news', $row, $pub, $news['ratings']==ENABLED);
               ($_GET['do']!='print')?publisher_more($row['id'], $pub):publisher_print($row['id'], $pub);

               //Выводим схожие публикации
               if($news['similar_publications']==ENABLED){
                  $array_search = explode(" ", $row['title']);
                  $wheres = "";
                  foreach ($array_search as $key=>$value){
                     if(empty($value)) unset($array_search[$key]);
                     else {
                        if(mb_strlen($array_search[$key])<4) continue;
                        $wheres .= " OR UPPER(title) LIKE BINARY('".addslashes(mb_strtoupper((mb_strlen($array_search[$key])>4) ? mb_substr($array_search[$key], 0, mb_strlen($array_search[$key])-2) : $array_search[$key]))."%')";
                     }
                  }
                  if(mb_strlen($wheres)>29){
                     $result = $main->db->sql_query("SELECT id, news_id, title, date FROM ".NEWS." WHERE status='1' AND (".mb_substr($wheres, 4).") AND ".(($main->rewrite_id) ? "news_id<>'{$_GET['id']}'" : "id<>'{$_GET['id']}'"));
                     if($main->db->sql_numrows($result)>0){
                        open();
                        echo "<div class='similar_pub'>{$main->lang['similar_pub_title']}</div>";
                        $similar_pub = 'similar_pub1';
                        while(list($id, $news_id, $title, $date) = $main->db->sql_fetchrow($result)){
                           echo "<div class='{$similar_pub}'>".format_date($date)." - <a href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($news_id, $id)))."' title='{$title}'>{$title}</a></div>";
                           $similar_pub = ($similar_pub=="similar_pub1") ? "similar_pub2" : "similar_pub1";
                        }
                        close();
                     }
                  }
               }
               //Выводим комментарии
               if($news['comments']==ENABLED AND $row['show_comment']=='1') comments(NEWS, $row['id'], $row['news_id'], $news['guests_comments'], $news['comments_sort'], true, $msg, 'more', $news['ratings']==ENABLED);
            } else warning($main->lang['no_view_content']);
        } else info($main->lang['noinfo']);
    }
}

function add_news(){
global $navi, $news, $main;
    if(hook_check(__FUNCTION__)) return hook();
    bcrumb::add($main->lang['add_news']);
    //Выводим навигацию по модулю
    echo $navi;
    if(($news['publications_users']==ENABLED AND is_user()) OR ($news['publications_guest']==ENABLED AND is_guest()) OR is_support()) global_add_news();
    else redirect(MODULE);
}

function send_news(){
global $news;
    if(hook_check(__FUNCTION__)) return hook();
    if(!(($news['publications_users']==ENABLED AND is_user()) OR ($news['publications_guest']==ENABLED AND is_guest()) OR is_support())) redirect(MODULE);
    else global_save_news();
}

function rss_news(){
global $main, $news;
    if(hook_check(__FUNCTION__)) return hook();
    main::init_class('rss');
    if($news['rss']==ENABLED){
        $result = $main->db->sql_query("SELECT n.id, n.news_id, n.title, n.begin, n.author, n.date, c.title, n.vgroups
            FROM ".NEWS." AS n LEFT JOIN ".CAT." AS c ON(FIND_IN_SET(c.cid, n.cid))
            WHERE n.status='1' AND (n.vgroups is null or n.vgroups='' or FIND_IN_SET(4, n.vgroups)) AND DATE_FORMAT(n.date, '%Y.%m.%d') <= '".kr_datecms("Y.m.d")."' 
            ORDER BY n.id DESC LIMIT {$news['rss_limit']}",__FUNCTION__);
        if($main->db->sql_numrows($result)>0){
            $rss_writer = new rss_writer;
            while(list($id, $news_id, $title, $begin, $author, $date, $cat_title, $vgroups) = $main->db->sql_fetchrow($result)){
                $content=(empty($vgroups) OR $vgroups==',4,')?$begin:$main->lang['only_authorization'];
                $rss_writer->add_item(($main->mod_rewrite) ? $news_id : $id, $title, $date, empty($cat_title)?$main->lang['nocat']:$cat_title, $content, $author, $news['rss_title']);
            }
            $rss_writer->write();
        } else info($main->lang['noinfo']);
    } else info($main->lang['rss_disabled']);
}
function switch_module_news(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   global $database,$check_revision;
   if(intval($database['revision'])>=$check_revision){
      if(isset($_GET['do'])){
         switch($_GET['do']){
            case "more": more_news(); break;
            case "print": more_news(); break;
            case "categoryes": categoryes_news(); break;
            case "add": add_news(); break;
            case "save": send_news(); break;
            case "upload": global_upload_attach_news(); break;
            case "rss": rss_news(); break;
            case "list": main_news(); break;
            case "popular": main_news(); break;
            case "favorite": main_news(); break;
            case "category": main_news(); break;
            case "attache_page": attache_page_news(); break;
            case "userinfo":main::required("modules/{$main->module}/userinfo.php"); break;
            default: main_news(); break;
         }
      } else main_news();
   } else echo warning(str_replace('{REVISION}',$check_revision,$main->lang['garant_revision']), true);
}
switch_module_news();
?>