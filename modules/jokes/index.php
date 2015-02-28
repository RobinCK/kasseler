<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main, $jokes, $tpl_create;
bcrumb::add($main->lang['home'],$main->url(array()));
bcrumb::add($main->lang[$main->module],$main->url(array('module' => $main->module)));
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'popular')), $main->lang['popular'], "popular");
if(is_user() AND $jokes['favorite_page']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'favorite')), $main->lang['favorite'], "favorite");
if($jokes['categories']==ENABLED) $links[] = array($main->url(array('module' => $main->module, 'do' => 'categoryes')), $main->lang['categoryes'], "categoryes");
if(($jokes['publications_users']==ENABLED AND is_user()) OR ($jokes['publications_guest']==ENABLED AND is_guest()) OR is_support()) $links[] = array($main->url(array('module' => $main->module, 'do' => 'add')), $main->lang['addjokes'], "add");
$navi = navi($links);
//Подключаем глобальные функции модуля
main::required("modules/{$main->module}/globals.php");

function main_jokes(){
global $jokes, $main, $navi, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    //Объявляем переменные
	$numrows = 0;
    $wheres = $list_where = $where = "";
    //Определяем тип вывода
    if(isset($_GET['do']) AND in_array($_GET['do'], array('list', 'popular', 'category', 'favorite', 'more'))) $wheres = $_GET['do'];
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
        $jokes['publications_in_page'] = 30;
        //Инициализируем класс вывода списком
        $pub_list = new pub_list();
        //Если выбрана сортировка по букве
        if(isset($_GET['id'])){
            //Добавляем букву в title
            add_meta_value($_GET['id']);
            //Дополняем запрос выборки публикаций
            $list_where = " AND UPPER(j.title) LIKE BINARY('".$_GET['id']."%')";
        }
        //Выводим список букв
        list_liter();
    } elseif($jokes['clasic_cat']==ENABLED AND !is_home()) show_category($jokes['cat_cols']);
    //Устанавливаем правила ЧПУ
    if(empty($wheres)) $main->parse_rewrite(array('module', 'page'));
    elseif($wheres=="popular") {
       $main->parse_rewrite(array('module', 'do', 'page'));
       bcrumb::add($main->lang[$wheres]);
    }  elseif($wheres=="favorite" AND $jokes['favorite_page']==ENABLED) {
        if(!is_user()) redirect(MODULE);
        $main->parse_rewrite(array('module', 'do', 'page'));
        $result = $main->db->sql_query("SELECT post FROM ".FAVORITE." WHERE modul='{$main->module}' AND users='{$main->user['user_name']}'");
        $rows = $main->db->sql_numrows($result);
        if($rows>0){ 
            $i = 0;
            $post = '';
            while(($row = $main->db->sql_fetchrow($result))){
                $post .= ($i == 0) ? "j.id='{$row['post']}'" : " OR j.id='{$row['post']}'" ;
                $i++;
            }
            $where = " AND ({$post})";
        } else $favorite = true;
    } elseif($wheres=="category") {
        //Устанавливаем правила ЧПУ для category
        $main->parse_rewrite(array('module', 'do', 'id', 'page'));
        $where = ($main->rewrite_id) ? sql_check_chpu_categorys("j") : " AND j.cid LIKE BINARY('%,".$_GET['id'].",%')";
        //Определяем название просматриваемой категории
        list($cat_title) = $main->db->sql_fetchrow($main->db->sql_query("SELECT title FROM ".CAT." WHERE ".($main->rewrite_id?"cat_id='{$_GET['id']}'":"cid='{$_GET['id']}'").""));
        bcrumb::add($main->lang['categoryes'],$main->url(array('module' => $main->module, 'do' => 'categoryes')));
        bcrumb::add($cat_title);
        //Добавляем категорию в title
        add_meta_value($main->lang['category']." ".$cat_title);
    }elseif($wheres=='more') $where = " AND j.id='{$_GET['id']}'";
    if(!isset($favorite)){
        //Определяем тип сортировки публикаций
        $order_by = ($wheres!="popular") ? "j.{$jokes['sort_publications']} {$jokes['sort_type_publications']}" : "j.rating DESC";
        //Определяем текущую страницу
        $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
        $offset = ($num-1) * $jokes['publications_in_page'];
        if($offset<0) kr_http_ereor_logs(404);
        $favorite_select = (is_user() AND $jokes['favorite_status']==ENABLED) ? ", fav.id AS favorite_id" : '';
        $favorite_table  = (is_user() AND $jokes['favorite_status']==ENABLED) ? "LEFT JOIN ".FAVORITE." AS fav ON (fav.post=j.id AND fav.users='{$main->user['user_name']}' AND fav.modul='{$main->module}')" : '';  
        //Выполняем запрос в БД
        $result = $main->db->sql_query("SELECT SQL_CALC_FOUND_ROWS j.id, j.title, j.joke, j.author, j.date, j.cid, j.status, j.language, j.rating, j.voted, u.uid, u.user_id, u.user_name,r.r_up,r.r_down,r.users{$favorite_select}
            FROM ".JOKES." AS j LEFT JOIN ".USERS." AS u ON (j.author = u.user_name) LEFT JOIN ".RATINGS." AS r ON (r.module='jokes' and r.idm=j.id) {$favorite_table}
            WHERE j.status='1' AND (j.language='{$main->language}' OR j.language='') AND DATE_FORMAT(j.date, '%Y.%m.%d') <= '".date("Y.m.d")."'{$where}{$list_where}
            ORDER BY {$order_by} 
            LIMIT {$offset}, {$jokes['publications_in_page']}"); 
        //Узнаем количество полученных публикаций
        $rows = $main->db->sql_numrows($result);
    }
    if(!isset($favorite) AND $rows>0){      
        if ($rows==$jokes['publications_in_page'] OR isset($_GET['page'])){
            //Получаем общее количество публикаций
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT FOUND_ROWS()"));
        }
        $i = (1*$num>1) ? ($jokes['publications_in_page']*($num-1))+1 : 1*$num;
        $line = "row1";
        $catlist=category_array();
        //Перебираем результат SQL запроса
        main::init_function('rating');
        while(($row = $main->db->sql_fetchrow($result))){
            //Создаем массив параметров для вывода публикации
            $pub = array(
                'id'         => $row['id'],
                'rewrite_id' => $row['id'],
                'title'      => $row['title'],
                'content'    => parse_bb($row['joke']),
                'date'       => user_format_date($row['date']),
                'year'       => format_date($row['date'], 'Y'),
                'month'      => format_date($row['date'], 'm'),
                'day'        => format_date($row['date'], 'd'),
                'favorite'   => (is_user() AND $jokes['favorite_status']==ENABLED) ? favorite_button($row['favorite_id'], $row['id']) : '',
                'author'     => (!is_guest_name($row['author']) AND !empty($row['user_id'])) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['author']}</a>" : $row['author'],
                'language'   => $row['language'],
                'category'   => cat_parse_new($row['cid'],$catlist),
                'lang_cat'   => $main->lang['category'],
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
            $pub = rating_modify_publisher($row['id'], 'jokes', $row, $pub, $jokes['ratings']==ENABLED);
            //Выводим публикацию
            if($wheres!="list") publisher($row['id'], $pub); else $pub_list->add_row($row['id'], $pub);
            $i++;
        }
        //Если страница вывода списком выводим публикации через класс
        if($wheres=="list") $pub_list->init();
        //Проверяем нужно ли выводить номера страниц
        if ($rows==$jokes['publications_in_page'] OR isset($_GET['page'])){
            //Если количество публикаций больше чем количество публикаций на страницу
            if($numrows>$jokes['publications_in_page']){
                //Открываем стилевую таблицу
                open();
                //В зависимости от типа вывода создаем страницы
                if(empty($wheres)) pages($numrows, $jokes['publications_in_page'], array('module' => $main->module), true);
                elseif($wheres=="category") pages($numrows, $jokes['publications_in_page'], array('module' => $main->module, 'do' => $wheres, 'id' => $_GET['id']), true);
                elseif($wheres=="list") pages($numrows, $jokes['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true, false, isset($_GET['id'])?array('id' => $_GET['id']):array());
                else pages($numrows, $jokes['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
}

function categoryes_jokes(){
global $jokes, $navi, $main;
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
            for($y=1;$y<=$jokes['cat_cols'];$y++){
                if(!isset($array_cat[$i+$y-1])) continue;
                $link = array(
                    'name' => $array_cat[$i+$y-1]['title'],
                    'ico'  => ($jokes['categories_ico']==ENABLED AND $array_cat[$i+$y-1]['image']!='no.png') ? "<img src='includes/images/cat/{$array_cat[$i+$y-1]['image']}' alt='{$array_cat[$i+$y-1]['title']}' />" : "",
                    'desc' => ($jokes['categories_desc']==ENABLED) ? cut_char($array_cat[$i+$y-1]['description']) : ""
                );
                echo "<div style='width: ".round(100/$jokes['cat_cols'], 2)."%;'><a class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'category', 'id' => case_id($array_cat[$i+$y-1]['cat_id'], $array_cat[$i+$y-1]['cid'])))."' title='{$link['name']}'><span>{$link['ico']}<b>{$link['name']}</b><br />{$link['desc']}</span></a></div>";
            }
            echo "</td></tr>";
            $i+=$jokes['cat_cols'];
        }
        echo "</table>";
        //Закрываем стилевую таблицу
        close();
    } else info($main->lang['noinfo']); //Выводим уведомление что "нет информации"
}

function add_jokes(){
global $navi, $jokes, $main;
    if(hook_check(__FUNCTION__)) return hook();
    bcrumb::add($main->lang['addjokes']);
    //Выводим навигацию по модулю
    echo $navi;
    if(($jokes['publications_users']==ENABLED AND is_user()) OR ($jokes['publications_guest']==ENABLED AND is_guest()) OR is_support()) global_add_jokes();
    else redirect(MODULE);
}

function send_jokes(){
global $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    if(!(($jokes['publications_users']==ENABLED AND is_user()) OR ($jokes['publications_guest']==ENABLED AND is_guest()) OR is_support())) redirect(MODULE);
    else global_save_jokes();
}

function rss_jokes(){
global $main, $jokes;
    if(hook_check(__FUNCTION__)) return hook();
    
    main::inited('class.rss');
    
    if($jokes['rss']==ENABLED){
        $result = $main->db->sql_query("SELECT j.id, j.title, j.joke, j.author, j.date, c.title
            FROM ".JOKES." AS j LEFT JOIN ".CAT." AS c ON(j.cid=c.cid)
            WHERE j.status='1' AND DATE_FORMAT(j.date, '%Y.%m.%d') <= '".date("Y.m.d")."'
            ORDER BY j.id DESC LIMIT {$jokes['rss_limit']}");
        if($main->db->sql_numrows($result)>0){
            $rss_writer = new rss_writer;
            while(list($id, $title, $begin, $author, $date, $cat_title) = $main->db->sql_fetchrow($result)){
                $rss_writer->add_item(($main->mod_rewrite) ? $id : $id, $title, $date, $cat_title, $begin, $author, $jokes['rss_title']);
            }
            $rss_writer->write();
        } else info($main->lang['noinfo']);
    } else info($main->lang['rss_disabled']);
}
function switch_module_jokes(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "categoryes": categoryes_jokes(); break;
         case "add": add_jokes(); break;
         case "save": send_jokes(); break;
         case "upload": global_upload_attach_jokes(); break;
         case "rss": rss_jokes(); break;
         case "list": main_jokes(); break;
         case "popular": main_jokes(); break;
         case "category": main_jokes(); break;
         default: main_jokes(); break;
      }
   } else main_jokes();
}
switch_module_jokes();
?>