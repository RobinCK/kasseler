<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main, $files, $tpl_create;
bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'popular')), $main->lang['popular'], "popular");
if($files['tags_page_status']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'tags')), $main->lang['tags'], "tags");
if(is_user() AND $files['favorite_page']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'favorite')), $main->lang['favorite'], "favorite");
if($files['categories']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'categoryes')), $main->lang['categoryes'], "categoryes");
if($files['list_publications']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'list')), $main->lang['list'], "list");
if(($files['publications_users']==ENABLED AND is_user()) OR ($files['publications_guest']==ENABLED AND is_guest()) OR is_support()) $links[] = array($main->url(array('module' => $main->module, 'do' => 'add')), $main->lang['add_files'], "add");
$navi = navi($links);
//Подключаем глобальные функции модуля
main::required("modules/{$main->module}/globals.php");

function main_files(){
global $files, $main, $navi, $tpl_create;
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
        $files['publications_in_page'] = 30;
        //Инициализируем класс вывода списком
        $pub_list = new pub_list();
        //Если выбрана сортировка по букве
        if(isset($_GET['id'])){
            //Добавляем букву в title
            add_meta_value($_GET['id']);
            //Дополняем запрос выборки публикаций
            $list_where = " AND UPPER(f.title) LIKE BINARY('".$_GET['id']."%')";
            bcrumb::add($main->lang[$wheres],$main->url(array('module' => $main->module, 'do' => 'list')));
            bcrumb::add($_GET['id']);
        } else bcrumb::add($main->lang[$wheres]);
        //Выводим список букв
        list_liter();
    } elseif($files['clasic_cat']==ENABLED AND !is_home()) show_category($files['cat_cols']);
    //Устанавливаем правила ЧПУ
    if(empty($wheres)) $main->parse_rewrite(array('module', 'page'));
    elseif($wheres=="popular") {
      $main->parse_rewrite(array('module', 'do', 'page'));
      bcrumb::add($main->lang[$wheres]);
    } elseif($wheres=="favorite" AND $files['favorite_page']==ENABLED) {
        if(!is_user()) redirect(MODULE);
        bcrumb::add($main->lang[$wheres]);
        $main->parse_rewrite(array('module', 'do', 'page'));
        $result = $main->db->sql_query("SELECT  post FROM ".FAVORITE." WHERE modul='{$main->module}' AND users='{$main->user['user_name']}'");
        $rows = $main->db->sql_numrows($result);
        if($rows>0){ 
            $i = 0;
            $post = '';
            while(($row = $main->db->sql_fetchrow($result))){
                $post .= ($i == 0) ? "f.id='{$row['post']}'" : " OR f.id='{$row['post']}'" ;
                $i++;
            }
            $where = " AND ({$post})";
        } else $favorite = true;
    } elseif($wheres=="category") {
        //Устанавливаем правила ЧПУ для category
        $main->parse_rewrite(array('module', 'do', 'id', 'page'));
        $where = ($main->rewrite_id) ? sql_check_chpu_categorys("f") : " AND f.cid LIKE BINARY('%,".$_GET['id'].",%')";
        //Определяем название просматриваемой категории
        list($cat_title) = $main->db->sql_fetchrow($main->db->sql_query("SELECT title FROM ".CAT." WHERE  module='{$main->module}' AND ".($main->rewrite_id?"cat_id='{$_GET['id']}'":"cid='{$_GET['id']}'").""));
        bcrumb::add($main->lang['categoryes'],$main->url(array('module' => $main->module, 'do' => 'categoryes')));
        bcrumb::add($cat_title);
        //Добавляем категорию в title
        add_meta_value($main->lang['category']." ".$cat_title);
    } elseif($wheres=="tags") {
        $tag = (isset($_GET['id'])) ? kr_filter($_GET['id'], TAGS) : '';
        if ($tag!='') {
            $main->parse_rewrite(array('module', 'do', 'id', 'page'));
            bcrumb::add($main->lang['tags'],$main->url(array('module' => $main->module, 'do' => 'tags')));
            bcrumb::add($tag);
            $result = $main->db->sql_query("SELECT post FROM ".TAG." WHERE modul='{$main->module}' AND tag='{$tag}'");
            if($main->db->sql_numrows($result)>0){
                $ih =1;
                while(($row = $main->db->sql_fetchrow($result))){
                    $where.= ($ih==1) ? " AND (f.id='{$row['post']}'" : " OR f.id='{$row['post']}'";
                    $ih++;
                }
                $where.= ")";
            }
        } else {
            bcrumb::add($main->lang[$wheres]);
            $tags = true;
        }
    }
    if (!isset($favorite) and !isset($tags)) {
        //Определяем тип сортировки публикаций
        $order_by = ($wheres!="popular") ? "f.{$files['sort_publications']} {$files['sort_type_publications']}" : "f.view DESC";
        //Определяем текущую страницу
        $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
        $offset = ($num-1) * $files['publications_in_page'];
        if($offset<0) kr_http_ereor_logs(404);
        //Инициализация переменных для определения закладок
        $favorite_select = (is_user() AND $files['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $files['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=f.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';	
        //Выполняем запрос в БД
        $result = $main->db->sql_query("SELECT SQL_CALC_FOUND_ROWS f.id, f.files_id, f.title, f.description, f.author, f.date, f.view, f.comment, f.cid, f.status, f.language, f.show_group, f.rating, f.voted, f.tags, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select}
            FROM ".FILES." AS f LEFT JOIN ".USERS." AS u ON (f.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='files' and r.idm=f.id) {$favorite_table}
            WHERE f.status='1' AND (f.language='{$main->language}' OR f.language='') AND DATE_FORMAT(f.date, '%Y.%m.%d') <= '".date("Y.m.d")."'{$where}{$list_where}
            ORDER BY {$order_by} LIMIT {$offset}, {$files['publications_in_page']}",__FUNCTION__."_s"); 
        //Узнаем количество полученных публикаций
        $rows = $main->db->sql_numrows($result);
    }
    if(!isset($favorite) and !isset($tags) and $rows>0){
        if ($rows==$files['publications_in_page'] OR isset($_GET['page'])){
            //Получаем общее количество публикаций
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT FOUND_ROWS()"));
        }
        $i = (1*$num>1) ? ($files['publications_in_page']*($num-1))+1 : 1*$num;
        $line = "row1";
        $catlist=category_array();
        //Перебираем результат SQL запроса
        main::init_function('rating');
        while(($row = $main->db->sql_fetchrow($result))){
            //Создаем массив параметров для вывода публикации
            $pub = array(
                'id'         => $row['id'],
                'rewrite_id' => $row['files_id'],
                'title'      => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['files_id'], $row['id'])))."' title='{$row['title']}'>{$row['title']}</a>",
                'content'    => parse_bb($row['description']),
                'views'      => $row['view'],
                'comment'    => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['files_id'], $row['id'])))."#comments' title='{$row['title']}'>{$row['comment']}</a>",
                'count_comm' => $row['comment'],
                'more'       => "<a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($row['files_id'], $row['id'])))."' title='{$row['title']}'>{$main->lang['more']}</a>",
                'date'       => user_format_date($row['date']),
                'year'       => format_date($row['date'], 'Y'),
                'month'      => format_date($row['date'], 'm'),
                'day'        => format_date($row['date'], 'd'),
                'tags'       => list_tags($row['tags'], $main->module),
                'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
                'language'   => $row['language'],
                'category'   => cat_parse_new($row['cid'],$catlist),
                'favorite'   => (is_user() AND $files['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
                'tags'       => ($files['tags_status']==ENABLED AND !empty($row['tags']))?list_tags($row['tags'], $main->module):'',
                'lang_tags'  => ($files['tags_status']==ENABLED AND !empty($row['tags']))?$main->lang['tags'].":":'',
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
            $pub = rating_modify_publisher($row['id'], 'files',$row,$pub,$files['ratings']==ENABLED);
            //Выводим публикацию
            if($wheres!="list") publisher($row['id'], $pub); else $pub_list->add_row($row['id'], $pub);
            $i++;
        }
        //Если страница вывода списком выводим публикации через класс
        if($wheres=="list") $pub_list->init();
        //Проверяем нужно ли выводить номера страниц
        if ($rows==$files['publications_in_page'] OR isset($_GET['page'])){
            //Если количество публикаций больше чем количество публикаций на страницу
            if($numrows>$files['publications_in_page']){
                //Открываем стилевую таблицу
                open();
                //В зависимости от типа вывода создаем страницы
                if(empty($wheres)) pages($numrows, $files['publications_in_page'], array('module' => $main->module), true);
                elseif($wheres=="category") pages($numrows, $files['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);
                elseif($wheres=="tags") pages($numrows, $files['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);               
                elseif($wheres=="list") pages($numrows, $files['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true, false, isset($_GET['id'])?array('id' => $_GET['id']):array());
                else pages($numrows, $files['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else {
        if(!isset($tags)) info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
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

function categoryes_files(){
global $files, $navi, $main;
    if(hook_check(__FUNCTION__)) return hook();
    bcrumb::add($main->lang['categoryes']);
    //Выводим навигацию по модулю
    echo $navi;    
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
            for($y=1;$y<=$files['cat_cols'];$y++){
                if(!isset($array_cat[$i+$y-1])) continue;
                $link = array(
                    'name' => $array_cat[$i+$y-1]['title'],
                    'ico'  => ($files['categories_ico']==ENABLED AND $array_cat[$i+$y-1]['image']!='no.png') ? "<img src='includes/images/cat/{$array_cat[$i+$y-1]['image']}' alt='{$array_cat[$i+$y-1]['title']}' />" : "",
                    'desc' => ($files['categories_desc']==ENABLED) ? cut_char($array_cat[$i+$y-1]['description']) : ""
                );
                echo "<div style='width: ".round(100/$files['cat_cols'], 2)."%;'><a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($array_cat[$i+$y-1]['cat_id'], $array_cat[$i+$y-1]['cid'])))."' title='{$link['name']}'><span>{$link['ico']}<b>{$link['name']}</b><br />{$link['desc']}</span></a></div>";
            }
            echo "</td></tr>";
            $i+=$files['cat_cols'];
        }
        echo "</table>";
        //Закрываем стилевую таблицу
        close();
    } else info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
}

function more_files($msg=""){
global $files, $main, $tpl_create, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    //Подключаем модуль комментариев
    main::init_function('comments');
    main::init_class("afields");
    //Если установлен ID, пытаемся создать комментарий
    if(isset($_POST['id'])) add_comment(FILES, $files['comments_sort'], $files['guests_comments'], 'more');
    else {
        //Выводим навигацию по модулю
        if($_GET['do']!='print') echo $navi;
        //Создаем условие выборки
        $where = ($main->rewrite_id) ? "f.files_id='{$_GET['id']}'" : "f.id='{$_GET['id']}'";
        //Инициализация переменных для определения закладок
        $favorite_select = (is_user() AND $files['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $files['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=f.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';  
        $catlist=category_array();
        //Выбираем публикацию
        $result = $main->db->sql_query("SELECT f.id, f.files_id, f.title, f.content, f.author, f.date, f.url, f.view, f.homepage, f.version, f.hits, f.filesize, f.comment, f.cid, f.status, f.language, f.show_comment, f.show_group, f.rating, f.voted, f.tags, f.afields, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select}
            FROM ".FILES." AS f LEFT JOIN ".USERS." AS u ON (f.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='files' and r.idm=f.id) {$favorite_table}
            WHERE".(!is_support()?" f.status='1' AND DATE_FORMAT(f.date, '%Y.%m.%d') <= '".date("Y.m.d")."' AND":'')." {$where}");
        //Проверяем наличие публикации с заданным ID
        main::init_function('rating');
        if($main->db->sql_numrows($result)>0){
            if(!empty($msg)) warning($msg);
            $row = $main->db->sql_fetchrow($result);
            $af = new afields($row['afields']);
            //Обновляем просмотры публикации
            $main->db->sql_query("UPDATE ".FILES." SET view=view+1 WHERE id='{$row['id']}'");
            $row['view']++;
            //Добавляем заголовок в title
            add_meta_value($row['title'], $af);
            $guset_download = ($files['download_guest']!=ENABLED AND is_guest()) ? "<div class='warning_guest'>{$main->lang['download_only_user']}</div>" : "";
            $content = parse_bb($row['content']).
            "<br /><br /><b>{$main->lang['downloads_file']}</b>: {$row['hits']} {$main->lang['count_hits']}<br />".
            (!empty($row['filesize'])?"<b>{$main->lang['filesize']}</b>: ".get_size($row['filesize'])."<br />":"").
            (!empty($row['version'])?"<b>{$main->lang['file_version']}</b>: {$row['version']}<br />":"").
            ((!empty($row['homepage']) AND $row['homepage']!='http://')?"<b>{$main->lang['homepage']}</b>: <a href='{$row['homepage']}'>{$row['homepage']}</a><br />":"").
            ((!empty($row['url']) AND $row['url']!='http://')?"<br />{$guset_download}<br /><a href='#' onclick=\"location.href='".$main->url(array('module' => $main->module, 'do' => 'download', 'id' => case_id($row['files_id'], $row['id'])))."'; return false;\" title='{$row['title']}'><b>{$main->lang['download']}</b></a>":"");
            //Выводим публикацию
            $pub = array(
                'id'         => $row['id'],
                'rewrite_id' => $row['files_id'],
                'title'      => $row['title'],
                'content'    => $content,
                'date'       => user_format_date($row['date']),
                'year'       => format_date($row['date'], 'Y'),
                'month'      => format_date($row['date'], 'm'),
                'day'        => format_date($row['date'], 'd'),
                'lang_month' => lang_month(format_date($row['date'], 'n')),
                'views'      => $row['view'],
                'language'   => $row['language'],
                'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
                'category'   => cat_parse_new($row['cid'],$catlist),
                'favorite'   => (is_user() AND $files['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
                'tags'       => ($files['tags_status']==ENABLED AND !empty($row['tags']))?list_tags($row['tags'], $main->module):'',
                'lang_tags'  => ($files['tags_status']==ENABLED AND !empty($row['tags']))?$main->lang['tags'].":":'',
                'load_tpl'   => $main->tpl,
                'lang_cat'   => $main->lang['category'],
                'lang_view'  => $main->lang['views'],
                'lang_author'=> $main->lang['author'],
                'print'      => "<a target='_BLANK' href='".$main->url(array('module' => $main->module, 'do' => 'print', 'id' => $_GET['id']))."' title='{$main->lang['print_version']}'>{$main->lang['print_version']}</a>",
                'lang_date'  => $main->lang['date_pub']
            );
            $pub = rating_modify_publisher($row['id'], 'files', $row, $pub, $files['ratings']==ENABLED);
            ($_GET['do']!='print')?publisher_more($row['id'], $pub):publisher_print($row['id'], $pub);
            //Выводим схожие публикации
            if($files['similar_publications']==ENABLED){
                $array_search = explode(" ", $row['title']);
                $wheres = "";
                foreach ($array_search as $key=>$value){
                    if(empty($value)) unset($array_search[$key]);
                    else {
                        if (mb_strlen($array_search[$key])<4) continue;
                        $wheres .= " OR UPPER(title) LIKE BINARY('".addslashes(mb_strtoupper((mb_strlen($array_search[$key])>4) ? mb_substr($array_search[$key], 0, mb_strlen($array_search[$key])-2) : $array_search[$key]))."%')";
                    }
                }
                if(mb_strlen($wheres)>29){
                    $result = $main->db->sql_query("SELECT id, files_id, title, date FROM ".FILES." WHERE status='1' AND (".mb_substr($wheres, 4).") AND ".(($main->rewrite_id) ? "files_id<>'{$_GET['id']}'" : "id<>'{$_GET['id']}'"));
                    if($main->db->sql_numrows($result)>0){
                        open();
                        echo "<div class='similar_pub'>{$main->lang['similar_pub_title']}</div>";
                        $similar_pub = 'similar_pub1';
                        while(list($id, $files_id, $title, $date) = $main->db->sql_fetchrow($result)){
                            echo "<div class='{$similar_pub}'>".format_date($date)." - <a href='".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => case_id($files_id, $id)))."' title='{$title}'>{$title}</a></div>";
                            $similar_pub = ($similar_pub=="similar_pub1") ? "similar_pub2" : "similar_pub1";
                        }
                        close();
                    }
                }
            }
            //Выводим комментарии
            if($files['comments']==ENABLED AND $row['show_comment']=='1') comments(FILES, $row['id'], $row['files_id'], $files['guests_comments'], $files['comments_sort'], true, $msg, 'more', $files['ratings']==ENABLED);
        } else info($main->lang['noinfo']);
    }
}

function add_files(){
global $navi, $files,$main;
    if(hook_check(__FUNCTION__)) return hook();
    bcrumb::add($main->lang['add_files']);
    //Выводим навигацию по модулю
    echo $navi;
    if(($files['publications_users']==ENABLED AND is_user()) OR ($files['publications_guest']==ENABLED AND is_guest()) OR is_support()) global_add_files();
    else redirect(MODULE);
}

function send_files(){
global $files;
    if(hook_check(__FUNCTION__)) return hook();
    if(!(($files['publications_users']==ENABLED AND is_user()) OR ($files['publications_guest']==ENABLED AND is_guest()) OR is_support())) redirect(MODULE);
    else global_save_files();
}

function rss_files(){
global $main, $files;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::inited('class.rss');
    
    if($files['rss']==ENABLED){
        $result = $main->db->sql_query("SELECT f.id, f.files_id, f.title, f.description, f.author, f.date, c.title
            FROM ".FILES." AS f LEFT JOIN ".CAT." AS c ON(f.cid=c.cid)
            WHERE f.status='1' AND DATE_FORMAT(f.date, '%Y.%m.%d') <= '".date("Y.m.d")."' AND f.show_group=''
            ORDER BY f.id DESC LIMIT {$files['rss_limit']}");
        if($main->db->sql_numrows($result)>0){
            $rss_writer = new rss_writer;
            while(list($id, $files_id, $title, $description, $author, $date, $cat_title) = $main->db->sql_fetchrow($result)){
                $rss_writer->add_item(($main->mod_rewrite) ? $files_id : $id, $title, $date, $cat_title, $description, $author, $files['rss_title']);
            }
            $rss_writer->write();
        } else info($main->lang['noinfo']);
    } else info($main->lang['rss_disabled']);
}

function download_files(){
global $main, $files;
    if(hook_check(__FUNCTION__)) return hook();
    if(!($files['download_guest']!=ENABLED AND is_guest())){
        $where = ($main->rewrite_id) ? "files_id='{$_GET['id']}'" : "id='{$_GET['id']}'";
        $result = $main->db->sql_fetchrow($main->db->sql_query("SELECT id, files_id, title, url FROM ".FILES." WHERE {$where}"));
        $url = (mb_strpos($result['url'], 'http://')!==false) ? $result['url'] : "http://".get_host_name()."/{$files['directory']}{$result['id']}/{$result['url']}";
        $main->db->sql_query("UPDATE ".FILES." SET hits=hits+1 WHERE id='{$result['id']}'");
        redirect($url);
    } else redirect($main->url(array('module' => 'account')));
}
function switch_module_files(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "more": more_files(); break;
         case "print": more_files(); break;
         case "categoryes": categoryes_files(); break;
         case "add": add_files(); break;
         case "save": send_files(); break;
         case "upload": global_upload_attach_files(); break;
         case "attache_page": attache_page_files(); break;
         case "rss": rss_files(); break;
         case "list": main_files(); break;
         case "popular": main_files(); break;
         case "category": main_files(); break;
         case "favorite": main_files(); break;
         case "download": download_files(); break;
         case "userinfo":main::required("modules/{$main->module}/userinfo.php"); break;
         default: main_files(); break;
      }
   } else main_files();
}
switch_module_files();
?>