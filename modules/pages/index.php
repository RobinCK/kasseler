<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main, $pages, $tpl_create;
bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'popular')), $main->lang['popular'], "popular");
if($pages['tags_page_status']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'tags')), $main->lang['tags'], "tags");
if(is_user() AND $pages['favorite_page']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'favorite')), $main->lang['favorite'], "favorite");
if($pages['categories']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'categoryes')), $main->lang['categoryes'], "categoryes");
if($pages['list_publications']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'list')), $main->lang['list'], "list");
if(($pages['publications_users']==ENABLED AND is_user()) OR ($pages['publications_guest']==ENABLED AND is_guest()) OR is_support()) $links[] = array($main->url(array('module' => $main->module, 'do' => 'add')), $main->lang['add_pages'], "add");
$navi = navi($links);
//Подключаем глобальные функции модуля
if(isset($_GET['module'])) main::required("modules/{$main->module}/globals.php");

function main_pages(){
global $pages, $main, $navi, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    //Объявляем переменные
    $numrows = 0;
    $wheres = $list_where = $where = "";
    //Определяем тип вывода
    if(isset($_GET['do']) AND in_array($_GET['do'], array('list', 'popular', 'category', 'favorite', 'tags'))) $wheres = $_GET['do'];
    //Если не главная страница сайта, выводим навигацию модуля
    if(!is_home()) echo $navi;
    if($wheres=="list"){ //Если вывод списком
        if($main->config['rewrite']==ENABLED) unset($_GET['id']);
        $main->parse_rewrite(array('module', 'do', 'page', 'id'));
        if(isset($_GET['page']) AND !isset($_GET['id']) AND !preg_match('/([0-9]+)/i', $_GET['page'])){
            $_GET['id'] = $_GET['page'];
            unset($_GET['page']);
        }
        //Устанавливаем количество публикаций на страницу
        $pages['publications_in_page'] = 30;
        //Инициализируем класс вывода списком
        $pub_list = new pub_list();
        //Если выбрана сортировка по букве
        if(isset($_GET['id'])){
            //Добавляем букву в title
            add_meta_value($_GET['id']);
            //Дополняем запрос выборки публикаций
            $list_where = " AND UPPER(p.title) LIKE BINARY('".$_GET['id']."%')";
            bcrumb::add($main->lang['list'],$main->url(array('module' => $main->module, 'do' => 'list')));
            bcrumb::add($_GET['id']);
        } else bcrumb::add($main->lang['list']);
        //Выводим список букв
        list_liter();
    } elseif($pages['clasic_cat']==ENABLED AND !is_home()) show_category($pages['cat_cols']);
    //Устанавливаем правила ЧПУ
    if(empty($wheres)) $main->parse_rewrite(array('module', 'page'));
    elseif($wheres=="popular") {
        $main->parse_rewrite(array('module', 'do', 'page'));
        bcrumb::add($main->lang[$wheres]);
    } elseif($wheres=="favorite" AND $pages['favorite_page']==ENABLED) {
        if(!is_user()) redirect(MODULE);
        bcrumb::add($main->lang[$wheres]);
        $main->parse_rewrite(array('module', 'do', 'page'));
        $result = $main->db->sql_query("SELECT post FROM ".FAVORITE." WHERE modul='{$main->module}' AND users='{$main->user['user_name']}'");
        $rows = $main->db->sql_numrows($result);
        if($rows>0){ 
            $i = 0;
            $post = '';
            while(($row = $main->db->sql_fetchrow($result))){
                $post .= ($i == 0) ? "p.id='{$row['post']}'" : " OR p.id='{$row['post']}'" ;
                $i++;
            }
            $where = " AND ({$post})";
        } else $favorite = true;
    } elseif($wheres=="category"){
        //Устанавливаем правила ЧПУ для category
        $main->parse_rewrite(array('module', 'do', 'id', 'page'));
        $where = ($main->rewrite_id) ?sql_check_chpu_categorys("p") : " AND p.cid LIKE BINARY('%,".$_GET['id'].",%')";
        //Определяем название просматриваемой категории
        list($cat_title) = $main->db->sql_fetchrow($main->db->sql_query("SELECT title FROM ".CAT." WHERE module='{$main->module}' AND ".($main->rewrite_id?"cat_id='{$_GET['id']}'":"cid='{$_GET['id']}'").""));
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
                    $where.= ($ih==1) ? " AND (p.id='{$row['post']}'" : " OR p.id='{$row['post']}'";
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
    if(!isset($favorite) and !isset($tags)){
        //Определяем тип сортировки публикаций
        $order_by = ($wheres!="popular") ? "p.{$pages['sort_publications']} {$pages['sort_type_publications']}" : "p.view DESC";
        //Определяем текущую страницу
        $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
        $offset = ($num-1) * $pages['publications_in_page'];
        if($offset<0) kr_http_ereor_logs(404);
        //Инициализация переменных для определения закладок
        $favorite_select = (is_user() AND $pages['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $pages['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=p.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';	
        //Выполняем запрос в БД
        $result = $main->db->sql_query("SELECT SQL_CALC_FOUND_ROWS p.id, p.pages_id, p.title, p.begin, p.author, p.date, p.view, p.comment, p.cid, p.status, p.language, p.show_group, p.rating, p.voted, p.tags, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select} {FIELDS}
            FROM ".PAGES." AS p LEFT JOIN ".USERS." AS u ON (p.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='pages' and r.idm=p.id) {$favorite_table} {TABLES}
            WHERE p.status='1' AND (p.language='{$main->language}' OR p.language='') AND DATE_FORMAT(p.date, '%Y.%m.%d') <= '".date("Y.m.d")."'{$where}{$list_where} {WHERES}
            ORDER BY {$order_by} LIMIT {$offset}, {$pages['publications_in_page']}", __FUNCTION__); 
        //Узнаем количество полученных публикаций
        $rows = $main->db->sql_numrows($result);
    }
    if(!isset($favorite) and !isset($tags) and $rows>0){
        if ($rows==$pages['publications_in_page'] OR isset($_GET['page'])){
            //Получаем общее количество публикаций
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT FOUND_ROWS()"));
        }
        $i = (1*$num>1) ? ($pages['publications_in_page']*($num-1))+1 : 1*$num;
        $line = "row1";
        main::init_function('rating');
        //Перебираем результат SQL запроса
        while(($row = $main->db->sql_fetchrow($result))){
            //Создаем массив параметров для вывода публикации
            $pub = array(
                'id'         => $row['id'],
                'rewrite_id' => $row['pages_id'],
                'title'      => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['pages_id'], $row['id'])))."' title='{$row['title']}'>{$row['title']}</a>",
                'content'    => parse_bb(preg_replace('/\[PAGE_BREAK\]/', "<br />", $row['begin'])),
                'views'      => $row['view'],
                'comment'    => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['pages_id'], $row['id'])))."#comments' title='{$row['title']}'>{$row['comment']}</a>",
                'count_comm' => $row['comment'],
                'more'       => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['pages_id'], $row['id'])))."' title='{$row['title']}'>{$main->lang['more']}</a>",
                'date'       => user_format_date($row['date']),
                'year'       => format_date($row['date'], 'Y'),
                'month'      => format_date($row['date'], 'm'),
                'day'        => format_date($row['date'], 'd'),
                'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
                'language'   => $row['language'],
                'category'   => cat_parse_new($row['cid'],$catlist),
                'favorite'   => (is_user() AND $pages['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
                'tags'       => ($pages['tags_status']==ENABLED AND !empty($row['tags']))?list_tags($row['tags'], $main->module):'',
                'lang_tags'  => ($pages['tags_status']==ENABLED AND !empty($row['tags']))?$main->lang['tags'].":":'',
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
            //Если рейтинг активен  добавляем JS
            $pub=rating_modify_publisher($row['id'], 'pages', $row, $pub, $pages['ratings']==ENABLED);
            //Выводим публикацию
            if($wheres!="list") publisher($row['id'], $pub); else $pub_list->add_row($row['id'], $pub);
            $i++;
        }
        //Если страница вывода списком выводим публикации через класс
        if($wheres=="list") $pub_list->init();
        //Проверяем нужно ли выводить номера страниц
        if ($rows==$pages['publications_in_page'] OR isset($_GET['page'])){
            //Если количество публикаций больше чем количество публикаций на страницу
            if($numrows>$pages['publications_in_page']){
                //Открываем стилевую таблицу
                open();
                //В зависимости от типа вывода создаем страницы
                if(empty($wheres)) pages($numrows, $pages['publications_in_page'], array('module' => $main->module), true);
                elseif($wheres=="category") pages($numrows, $pages['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);
                elseif($wheres=="tags") pages($numrows, $pages['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);
                elseif($wheres=="list") pages($numrows, $pages['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true, false, isset($_GET['id'])?array('id' => $_GET['id']):array());
                else pages($numrows, $pages['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true);
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

function categoryes_pages(){
global $pages, $navi, $main;
    if(hook_check(__FUNCTION__)) return hook();
    bcrumb::add($main->lang['categoryes']);
    //Выводим навигацию по модулю
    echo $navi;
    //Делаем выборку всех категорий модуля
    $result = $main->db->sql_query("SELECT cid, cat_id, title, image, description FROM ".CAT." WHERE module='{$main->module}' ORDER BY BINARY(UPPER(title));");
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
            for($y=1;$y<=$pages['cat_cols'];$y++){
                if(!isset($array_cat[$i+$y-1])) continue;
                $link = array(
                    'name' => $array_cat[$i+$y-1]['title'],
                    'ico'  => ($pages['categories_ico']==ENABLED AND $array_cat[$i+$y-1]['image']!='no.png') ? "<img src='includes/images/cat/{$array_cat[$i+$y-1]['image']}' alt='{$array_cat[$i+$y-1]['title']}' />" : "",
                    'desc' => ($pages['categories_desc']==ENABLED) ? cut_char($array_cat[$i+$y-1]['description']) : ""
                );
                echo "<div style='width: ".round(100/$pages['cat_cols'], 2)."%;'><a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($array_cat[$i+$y-1]['cat_id'], $array_cat[$i+$y-1]['cid'])))."' title='{$link['name']}'><span>{$link['ico']}<b>{$link['name']}</b><br />{$link['desc']}</span></a></div>";
            }
            echo "</td></tr>";
            $i+=$pages['cat_cols'];
        }
        echo "</table>";
        //Закрываем стилевую таблицу
        close();
    } else info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
}

function more_pages($msg=""){
global $pages, $main, $tpl_create, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    //Подключаем модуль комментариев
    main::init_function('comments');
    //Если установлен ID, пытаемся создать комментарий
    if(isset($_POST['id'])) add_comment(PAGES, $pages['comments_sort'], $pages['guests_comments'], 'more');
    else {
        //Выводим навигацию по модулю
        if($_GET['do']!='print') echo $navi;
        //Создаем условие выборки
        $where = ($main->rewrite_id) ? "p.pages_id='{$_GET['id']}'" : "p.id='{$_GET['id']}'";
        //Инициализация переменных для определения закладок
        $favorite_select = (is_user() AND $pages['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $pages['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=p.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';  
        $catlist=category_array();
        //Выбираем публикацию        
        $result = $main->db->sql_query("SELECT p.id, p.pages_id, p.title, p.content, p.author, p.date, p.view, p.comment, p.cid, p.status, p.language, p.show_comment, p.show_group, p.rating, p.voted, p.tags, p.afields, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select}
            FROM ".PAGES." AS p LEFT JOIN ".USERS." AS u ON (p.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='pages' and r.idm=p.id) {$favorite_table}
            WHERE".(!is_support()?" p.status='1' AND DATE_FORMAT(p.date, '%Y.%m.%d') <= '".date("Y.m.d")."' AND":'')." {$where}");
        //Проверяем наличие публикации с заданным ID
        main::init_function('rating');
        if($main->db->sql_numrows($result)>0){
            $row = $main->db->sql_fetchrow($result);
            //Обновляем просмотры публикации
            $main->db->sql_query("UPDATE ".PAGES." SET view=view+1 WHERE id='{$row['id']}'");
            $row['view']++;
            //Добавляем заголовок в title
            main::init_class("afields");
            $af=new afields($row['afields']);
            add_meta_value($row['title'], $af);
            //Форматируем содержимое публикации
            $content = pagebreak($row['content'], $pages['page_break']);
            //Выводим публикацию
            $pub = array(
                'id'         => $row['id'],
                'rewrite_id' => $row['pages_id'],
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
                'favorite'   => (is_user() AND $pages['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
                'tags'       => ($pages['tags_status']==ENABLED AND !empty($row['tags']))?list_tags($row['tags'], $main->module):'',
                'lang_tags'  => ($pages['tags_status']==ENABLED AND !empty($row['tags']))?$main->lang['tags'].":":'',
                'load_tpl'   => $main->tpl,
                'lang_cat'   => $main->lang['category'],
                'lang_view'  => $main->lang['views'],
                'lang_author'=> $main->lang['author'],
                'print'      => "<a target='_BLANK' href='".$main->url(array('module' => $main->module, 'do' => 'print', 'id' => $_GET['id']))."' title='{$main->lang['print_version']}'>{$main->lang['print_version']}</a>",
                'lang_date'  => $main->lang['date_pub']
            );
            $pub=rating_modify_publisher($row['id'], 'pages', $row, $pub, $pages['ratings']==ENABLED);
            ($_GET['do']!='print')?publisher_more($row['id'], $pub):publisher_print($row['id'], $pub);
            //Выводим схожие публикации
            if($pages['similar_publications']==ENABLED){
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
                    $result = $main->db->sql_query("SELECT id, pages_id, title, date FROM ".PAGES." WHERE status='1' AND (".mb_substr($wheres, 4).") AND ".(($main->rewrite_id) ? "pages_id<>'{$_GET['id']}'" : "id<>'{$_GET['id']}'"));
                    if($main->db->sql_numrows($result)>0){
                        open();
                        echo "<div class='similar_pub'>{$main->lang['similar_pub_title']}</div>";
                        $similar_pub = 'similar_pub1';
                        while(list($id, $page_id, $title, $date) = $main->db->sql_fetchrow($result)){
                            echo "<div class='{$similar_pub}'>".format_date($date)." - <a href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($page_id, $id)))."' title='{$title}'>{$title}</a></div>";
                            $similar_pub = ($similar_pub=="similar_pub1") ? "similar_pub2" : "similar_pub1";
                        }
                        close();
                    }
                }
            }
            //Выводим комментарии
            if($pages['comments']==ENABLED AND $row['show_comment']=='1') comments(PAGES, $row['id'], $row['pages_id'], $pages['guests_comments'], $pages['comments_sort'], true, $msg, 'more', $pages['ratings']==ENABLED);
        } else info($main->lang['noinfo']);
    }
}

function add_pages(){
global $navi, $pages, $main;
    if(hook_check(__FUNCTION__)) return hook();
    bcrumb::add($main->lang['add_pages']);
    echo $navi;
    if(($pages['publications_users']==ENABLED AND is_user()) OR ($pages['publications_guest']==ENABLED AND is_guest()) OR is_support()) global_add_pages();
    else redirect(MODULE);
}

function send_pages(){
global $pages;
    if(hook_check(__FUNCTION__)) return hook();
    if(!(($pages['publications_users']==ENABLED AND is_user()) OR ($pages['publications_guest']==ENABLED AND is_guest()) OR is_support())) redirect(MODULE);
    else global_save_pages();
}

function rss_pages(){
global $main, $pages;
    if(hook_check(__FUNCTION__)) return hook();
    main::inited('class.rss');
    if($pages['rss']==ENABLED){
        $result = $main->db->sql_query("SELECT p.id, p.pages_id, p.title, p.begin, p.author, p.date, c.title
            FROM ".PAGES." AS p LEFT JOIN ".CAT." AS c ON(p.cid=c.cid)
            WHERE p.status='1' AND DATE_FORMAT(p.date, '%Y.%m.%d') <= '".date("Y.m.d")."' AND p.show_group=''
            ORDER BY p.id DESC LIMIT {$pages['rss_limit']}");
        if($main->db->sql_numrows($result)>0){
            $rss_writer = new rss_writer;
            while(list($id, $page_id, $title, $begin, $author, $date, $cat_title) = $main->db->sql_fetchrow($result)){
                $rss_writer->add_item(($main->mod_rewrite) ? $page_id : $id, $title, $date, $cat_title, $begin, $author, $pages['rss_title']);
            }
            $rss_writer->write();
        } else info($main->lang['noinfo']);
    } else info($main->lang['rss_disabled']);
}
function switch_module_pages(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "more": more_pages(); break;
         case "print": more_pages(); break;
         case "categoryes": categoryes_pages(); break;
         case "add": add_pages(); break;
         case "save": send_pages(); break;
         case "upload": global_upload_attach_pages(); break;
         case "rss": rss_pages(); break;
         case "list": main_pages(); break;
         case "popular": main_pages(); break;
         case "favorite": main_pages(); break;
         case "category": main_pages(); break;
         case "attache_page": attache_page_pages(); break;
         case "userinfo":main::required("modules/{$main->module}/userinfo.php"); break;
         default: main_pages(); break;
      }
   } else main_pages();
}
switch_module_pages();
?>