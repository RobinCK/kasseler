<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");
define('SEARCH_MODULE', true);

global $navi, $length_ignore, $date;
$navi = navi(array(), false, false);
$length_ignore = 3;
$cache_html = 1;
$cache_html_arr = array();

function main_search(){
global $main, $navi;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_home()) echo $navi;
    main::required("modules/{$main->module}/form.php");
}

function get_search_module(){
global $modules;
    if(hook_check(__FUNCTION__)) return hook();
    $mod = array();
    $patch = 'modules/';
    if(($handle = opendir($patch))){
        while(false !== ($file = readdir($handle))) if(is_dir($patch.$file) AND $file!='forum' AND file_exists($patch.$file.'/search.php')) $mod[] = $file;
        closedir($handle);
    }
    $sel = array();
    foreach($mod as $value) if(isset($modules[$value]) AND $modules[$value]['active']==1) $sel[$value] = $modules[$value]['title'];
    return $sel;
}

function cache_html($sting){
global $cache_html, $cache_html_arr;
    if(hook_check(__FUNCTION__)) return hook();
    $cache_html = empty($cache_html) ? 1 : $cache_html;
    $cache_html_arr[$cache_html] = $sting;
    $return = "{CACHE_HTML}_".$cache_html;
    $cache_html++;
    return $return;
}

function restore_html($int){
global $cache_html, $cache_html_arr;
    if(hook_check(__FUNCTION__)) return hook();
    return "<{$cache_html_arr[$int]}>";
}


function search_result(){
global $main, $navi, $_S, $search_key, $modules, $length_ignore, $lang, $cache_html_arr, $cache_html, $date;
    if(hook_check(__FUNCTION__)) return hook();
    $is_date = false; $_modules = array(); 
    //Если поиск по дате
    if(isset($_GET['do']) AND $_GET['do']=='date' AND preg_match('/([0-9]{4}-[0-9]{2})|([0-9]{4}-[0-9]{2}-[0-9]{2})/', $_GET['id'])){
        //Проверяем поиск по дате или вывод указанной даты
        if(preg_match('/([0-9]{4}-[0-9]{2})|([0-9]{4}-[0-9]{2}-[0-9]{2})/', $_GET['id'])){
            //Ставим флаг поиска по дате
            $is_date = true;
            $date = $_GET['id'];
            //Создаем ключ поиска
            $search_key = crc32_integer("date_".$date);
            //Проверяем тип поиска по дате
            if(preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})/', $date)) $result = $main->db->sql_query("SELECT * FROM ".CALENDAR." WHERE date='{$date}' GROUP BY module");
            else $result = $main->db->sql_query("SELECT * FROM ".CALENDAR." WHERE date LIKE '{$date}%' GROUP BY module");
            while($row = $main->db->sql_fetchrow($result)) $_modules[] = $row['module'];
            $main->db->sql_query("DELETE FROM ".SEARCH." WHERE time < '".(time()-24*60*60)."'");
            //Удаляем ключ поиска
            $main->db->sql_query("DELETE FROM ".SEARCH_KEY." WHERE `key` = '{$search_key}'");
            //Сохраняем ключ поиска
            sql_insert(array('key' => $search_key, 'query' => $date), SEARCH_KEY);
            //Выполняем подсчет количества результата поиска
            if(preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})/', $date)) list($count_dates) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".SEARCH." WHERE `key`='{$search_key}'"));
            else list($count_dates) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".SEARCH." WHERE `key` LIKE '{$search_key}%'"));
            //if($count_dates==0) foreach($_modules as $val) if(file_exists("modules/{$val}/calendar.php")) require_once "modules/{$val}/calendar.php";
            //Указываем ключ поиска для вывода результата
            $_GET['id'] = $search_key;
        }
    } else echo $navi;
    //Проверяем выполняется поиск или вывод
    if(isset($_GET['id']) AND preg_match('/([0-9]*)/', $_GET['id'])){
        //Получаем данные ключа поиска
        $result = $main->db->sql_query("SELECT `key`, `query` FROM ".SEARCH_KEY." WHERE `key`='{$_GET['id']}'");
        if($main->db->sql_numrows($result)>0){
            list($search_key, $_s) = $main->db->sql_fetchrow($result);
            //Проверяем ключ поиска, поиск по дате или обычный
            if(!preg_match('/([0-9]{4}-[0-9]{2})|([0-9]{4}-[0-9]{2}-[0-9]{2})/', $_s)) {
                //Если обычный поиск создаем параметры поиска 
                eval('$_S = '.$_s.';');
                //Имитируем передачу POST
                foreach($_S as $key => $value) $_POST[$key] = $value;
                //Подключаем форму поиска
                main::required("modules/{$main->module}/form.php");
            } else $is_date = true; //Устанавливаем флаг вывода даты
            //Выполняем выборку результата поиска
            $result = $main->db->sql_query("SELECT * FROM ".SEARCH." WHERE `key`='{$search_key}'");
            //Если результата поиска не найден
            if($main->db->sql_numrows($result)==0){
                //Проверяем тип вывода результата
                if($is_date==false) {
                    //Выполняем повторный поиск
                    if($_S['modules']==array('')) foreach(get_search_module() as $k => $v) main::required("modules/{$k}/search.php");
                    else foreach($_S['modules'] as $v) main::required("modules/{$v}/search.php");
                } else {
                    //Выполняем повторный поиск по дате
                    $_GET['id'] = $_s;
                    if($is_date==true) $result = $main->db->sql_query("SELECT * FROM ".CALENDAR." WHERE date='{$_s}' GROUP BY module");
                    else $result = $main->db->sql_query("SELECT * FROM ".CALENDAR." WHERE date LIKE '{$_s}%' GROUP BY module");
                    while($row = $main->db->sql_fetchrow($result)) $_modules[] = $row['module'];
                    foreach($_modules as $val) if(file_exists("modules/{$val}/calendar.php")) main::required("modules/{$val}/calendar.php");
                    $_GET['id'] = $search_key;
                }
            }
            //Определяем тип сортировки результата
            switch($_S['sortby']){
                //По ключевым словам
                case 'key': $order = "LENGTH(keywords)"; break;
                //По дате
                case 'date': $order = "date"; break;
                //По заголовку
                case 'title': $order = "title"; break;
                //По автору
                case 'author': $order = "author"; break;
                default: $order = "author"; break;
            }
            if($is_date==true){
                //В случаи вывода по дате инициализируем создание параметров поиска
                $_S = array(
                    'story'          => '',            //Строка поиска
                    'author'         => '',            //Автор публикации
                    'author_full'    => false,         //Полное/не полное имя автора
                    'sortby'         => 'title',       //Метод сортировки
                    'sort_type'      => 'ASC',         //Тип сортировки
                    'result_in_page' => 15,            //Количество результатов на страницу
                    'search_type'    => 0,             //Тип поиска 
                    'view_type'      => 1,             //Тип вывода результата
                    'modules'        => array('')      //Модули для поиска
                );
            }
            //Определяем текущую страницу
            $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
            //Определяем лимит вывода
            $offset = ($num-1) * $_S['result_in_page'];
            //Выполняем выборку результата поиска
            $result = $main->db->sql_query("SELECT * FROM ".SEARCH." WHERE `key`='{$search_key}' ORDER BY {$order} ".(isset($_S['sort_type'])?$_S['sort_type']:'ASC').", id LIMIT {$offset}, {$_S['result_in_page']}");
            //Узнаем количество результатов поиска
            $count_rows = $main->db->sql_numrows($result);
            if($count_rows>0){ //Если больше 0
                $color_count = 0; $ss = array();
                //Создаем параметры цветовой схемы для выделения ключевых слов
                $colors_bg = array('#FFDD00', '#0099DD', '#A0EF4A', '#EF95AA', '#EFBD95'); 
                $colors_t = array('#333333', '#FFFFFF', '#333333', '#333333', '#333333');
                //Если вывод не по дате
                if($is_date==false) {
                    //Создаем список ключевых слов поиска
                    foreach(array("\n", "\r", '<br>', '<br />', '<p>', ' -', '.', '&nbsp;', '&', ',', '!', '«', '»', '?', ':', ';', ')', '(', '"', '\'') as $_v) $_S['story'] = str_replace($_v, ' ', $_S['story']);
                    while(strstr($_S['story'],"  ")) $_S['story'] = str_replace("  ", " ", $_S['story']);
                    $_S['story'] = str_replace('*', '_', $_S['story']);
                    foreach(explode(' ', $_S['story']) as $val) if(mb_strlen($val)>=$length_ignore) $ss[] = $val;
                }
                //Если вывод результата как заголовки или шаблон результата не найден
                if($_S['view_type']==0 OR !file_exists(TEMPLATE_PATH."{$main->tpl}/search.tpl")){
                    //Открываем стилевую таблицу
                    open();
                    //Выполняем перебор результатов поиска
                    while($row = $main->db->sql_fetchrow($result)){
                        //Удаляем форматирование текста и обрезаем его до 25 слов
                        $content = cut_text(strip_tags($row['content']), 25);
                        $title = $row['title'];
                        //Выполняем подсветку ключевых слов
                        foreach($ss as $v){
                            $content = preg_replace("/({$v}[^\x20]*)/is", "<span style=\"color: {$colors_t[$color_count]}; background: {$colors_bg[$color_count]};\">\\1</span>", $content);
                            $title = preg_replace("/({$v}[^\x20]*)/is", "<span style=\"color: {$colors_t[$color_count]}; background: {$colors_bg[$color_count]};\">\\1</span>", $title);
                            $color_count++;
                            if($color_count>4) $color_count=0;
                        }
                        $color_count=0;
                        //Выводим результат поиска
                        echo "<div class='search_row'><a href='".$main->url(array('module' => $row['module'], 'do' => 'more', 'id' => case_id($row['rewrite_id'], $row['subid'])))."' title='{$row['title']}' class='seaarch_result'>{$title}</a><div>{$content}</div><span class='desc'><b>{$main->lang['module']}</b>: <a href='".$main->url(array('module' => $row['module']))."' title='{$modules[$row['module']]['title']}'>{$modules[$row['module']]['title']}</a> <b>{$main->lang['date']}</b>: ".user_format_date($row['date'])." <b>{$main->lang['author']}</b>: {$row['author']}</span></div>";
                    }
                    //Закрываем стилевую таблицу
                    close();
                } else {//В случаи вывода полной публикации
                    //Загружаем шаблон результатов поиска
                    $tpl = file_get_contents(TEMPLATE_PATH."{$main->tpl}/search.tpl");
                    //Выполняем перебор результатов поиска
                    while($row = $main->db->sql_fetchrow($result)){
                        $content = $row['content']; $title = $row['title'];
                        //Кэшируем HTML
                        $content = preg_replace('/<(a|img|\/a)(.*?)>/ie', "cache_html('\\1\\2');", $content);
                        //Выполняем подсветку ключевых слов
                        foreach($ss as $v){
                            $content = preg_replace("/({$v}[^\x20<{]*)/is", "<span style=\"color: {$colors_t[$color_count]}; background: {$colors_bg[$color_count]};\">\\1</span>", $content);
                            $title = preg_replace("/({$v}[^\x20<{]*)/is", "<span style=\"color: {$colors_t[$color_count]}; background: {$colors_bg[$color_count]};\">\\1</span>", $title);
                            $color_count++;
                            if($color_count>4) $color_count=0;
                        }
                        //Возвращаем HTML из кэша
                        $content = preg_replace('/{CACHE_HTML}_([0-9]*)/ie', "restore_html('\\1');", $content);
                        //Обнуляем параметры
                        $cache_html_arr = array(); $color_count=0; $cache_html = 1;
                        //Создаем массив параметров для шаблона
                        $vars = array(
                            'title'    => "<a class='seaarch_result' href='".$main->url(array('module' => $row['module'], 'do' => 'more', 'id' => case_id($row['rewrite_id'], $row['subid'])))."' title='{$row['title']}'>{$title}</a>",
                            'content'  => $content,
                            'module'   => "<a href='".$main->url(array('module' => $row['module']))."' title='{$modules[$row['module']]['title']}'>{$modules[$row['module']]['title']}</a>",
                            'author'   => $row['author'],
                            'date'     => user_format_date($row['date']),
                        );
                        //Выполняем замену параметров в шаблоне
                        echo preg_replace('#\$pub\[([a-z_-]+)\]#ise', "isset(\$vars['\\1']) ? \$vars['\\1'] : ''", preg_replace('#\$lang\[([a-z_-]+)\]#ise', "isset(\$lang['\\1']) ? \$lang['\\1'] : ''", $tpl));
                    }
                }
                //Вывод количества страниц результата
                if($count_rows==$_S['result_in_page'] OR isset($_GET['page'])){
                    //Получаем общее количество публикаций
                    list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".SEARCH." WHERE `key`='{$search_key}'"));
                    //Если количество публикаций больше чем количество публикаций на страницу
                    if($numrows>$_S['result_in_page']){
                        //Открываем стилевую таблицу
                        open();
                        if(!(isset($_GET['do']) AND $_GET['do']=='date' AND preg_match('/([0-9]{4}-[0-9]{2})|([0-9]{4}-[0-9]{2}-[0-9]{2})/', $_GET['id']))) pages($numrows, $_S['result_in_page'], array('module' => $main->module, 'do' => 'result', 'id' => $search_key), true, false);
                        else pages($numrows, $_S['result_in_page'], array('module' => $main->module, 'do' => 'date', 'id' => $_s), true, false);
                        //Закрываем стилевую таблицу
                        close();
                    }
                }
            } else {
                //Если повторный поиск по БД не дал результата, выводим сообщение что поиск не дал результата
                if(!(isset($_GET['do']) AND $_GET['do']=='date' AND preg_match('/([0-9]{4}-[0-9]{2})|([0-9]{4}-[0-9]{2}-[0-9]{2})/', $_GET['id']))) main::required("modules/{$main->module}/form.php");
                info($main->lang['findnotfound']);
            }
        } else redirect(MODULE);//Если не найден ключ поиска отправляем пользователя на главную страницу модуля
    } else {//В случаи если выполняется поиск а не вывод результата
        // Выполняем фильтрацию данных
        $_S = array(
            //Строка поиска
            'story'          => isset($_GET['story'])  ? kr_filter($_GET['story'],  TAGS) : '',
            //Автор публикации
            'author'         => isset($_GET['author']) ? kr_filter($_GET['author'], TAGS) : '',
            //Полное/не полное имя автора
            'author_full'    => isset($_GET['author_full']) ? true : false,
            //Метод сортировки
            'sortby'         => (isset($_GET['sortby']) AND in_array($_GET['sortby'], array('key', 'date', 'title', 'author'))) ? $_GET['sortby'] : 'date',
            //Тип сортировки
            'sort_type'      => (isset($_GET['sort_type']) AND in_array($_GET['sort_type'], array('desc', 'asc'))) ? $_GET['sort_type'] : 'desc',
            //Количество результатов на страницу
            'result_in_page' => (isset($_GET['result_in_page']) AND preg_match('/[0-9]*/', $_GET['result_in_page']) AND $_GET['result_in_page']>0) ? $_GET['result_in_page'] : 15,
            //Тип поиска
            'search_type'    => (isset($_GET['search_type']) AND preg_match('/0|1|2/', $_GET['search_type'])) ? $_GET['search_type'] : 0,
            //Тип вывода результата
            'view_type'      => (isset($_GET['view_type']) AND preg_match('/0|1/', $_GET['view_type'])) ? $_GET['view_type'] : 0,
            //Модули для поиска
            'modules'        => (isset($_GET['sel_modules']) AND is_array($_GET['sel_modules'])) ? $_GET['sel_modules'] : array('')
        );
        //Удаляем лишнее пробелы
        $_S['story'] = trim($_S['story']);
        if(!empty($_S['story'])){
            //Создаем ключ поиска
            $search_key = crc32_integer(var_export($_S, true));
            //Удаляем результат поиска которому более суток
            $main->db->sql_query("DELETE FROM ".SEARCH." WHERE time < '".(time()-24*60*60)."'");
            //Удаляем ключ поиска если он есть
            $main->db->sql_query("DELETE FROM ".SEARCH_KEY." WHERE `key` = '{$search_key}'");
            //Создаем новый ключ поиска
            sql_insert(array('key'   => $search_key, 'query' => addslashes(var_export($_S, true))), SEARCH_KEY);
            list($count_result) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".SEARCH." WHERE `key`='{$search_key}'"));
            if($count_result==0){//Если в БД нет результата поиска
                //Выполняем поиск в указанных модулях
                if($_S['modules']==array('')) foreach(get_search_module() as $k => $v) main::required("modules/{$k}/search.php");
                else foreach($_S['modules'] as $v) main::required("modules/{$v}/search.php");
            }
            //Делаем редирект на страницу результата поиска
            redirect($main->url(array('module' => $main->module, 'do' => 'result', 'id' => $search_key)));
        } else {
            //Если строка поиска пуста
            foreach($_S as $key => $value) $_POST[$key] = $value;
            //Подключаем форму поиска
            main::required("modules/{$main->module}/form.php");
            //Оповещаем пользователя о том что строка поиска пуста
            warning($main->lang['findnotkey']);
        }
    }
}
function switch_module_search(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case 'result': search_result(); break;
         case 'date': search_result(); break;
         default: main_search(); break;
      }
   } else main_search();
}
switch_module_search();
?>