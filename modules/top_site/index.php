<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");

global $navi, $main, $topsite;
//Создаем навигацию модуля
$links[] = array($main->url(array('module' => $main->module)), $main->lang['home'], "");
$links[] = array($main->url(array('module' => $main->module, 'do' => 'popular')), $main->lang['popular'], "popular");
if(($topsite['publications_users']==ENABLED AND is_user()) OR ($topsite['publications_guest']==ENABLED AND is_guest()) OR is_support())  $links[] = array($main->url(array('module' => $main->module, 'do' => 'add')), $main->lang['add_site'], "add");
$navi = navi($links);

main::required("modules/{$main->module}/globals.php");

function main_top_site(){
global $main, $navi, $topsite, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    if(!is_home()) echo $navi;
    $numrows = 0;
    $wheres = (isset($_GET['do']) AND $_GET['do']=='popular') ? $_GET['do'] : '';
    if(empty($wheres)) $main->parse_rewrite(array('module', 'page'));
    elseif($wheres=="popular") $main->parse_rewrite(array('module', 'do', 'page'));
    //Определяем тип сортировки публикаций
    $order_by = ($wheres!="popular") ? "t.hits_in DESC" : "t.hits_out DESC";
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * $topsite['publications_in_page'];
    $result = $main->db->sql_query("SELECT SQL_CALC_FOUND_ROWS t.*,r.r_up,r.r_down,r.users FROM ".TOPSITES." AS t LEFT JOIN ".RATINGS." AS r ON (r.module='topsites' and r.idm=t.id) WHERE t.status='1' AND (t.language='{$main->language}' OR t.language='') AND DATE_FORMAT(t.date, '%Y.%m.%d') <= '".date("Y.m.d")."' ORDER BY {$order_by} LIMIT {$offset}, {$topsite['publications_in_page']}");
    $rows = $main->db->sql_numrows($result);
    $topsite['publications_cols'] = $rows<$topsite['publications_cols'] ? $rows : $topsite['publications_cols'];
    if($rows>0){
        if ($rows==$topsite['publications_in_page'] OR isset($_GET['page'])){
            //Получаем общее количество публикаций
            list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT FOUND_ROWS()"));
        }
        $i = (1*$num>1) ? ($topsite['publications_in_page']*($num-1))+1 : 1*$num;
        $line = "row1"; $col = 1;
        main::init_function('rating');
        if($topsite['publications_cols']>1) echo "<table width='100%' cellpadding='2' cellspacing='0' style='position: relative; top:-2px;'>";
        //Перебираем результат SQL запроса
        while(($row = $main->db->sql_fetchrow($result))){
            //Создаем массив параметров для вывода публикации
            $imgv = empty($row['img']) ? $topsite['privew_url'].$row['link'] : $topsite['directory'].$row['img'];
            $pub = array(
                'title'        => "<a target='_BLANK' class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'goto', 'id' => $row['id']))."' title='{$row['title']}'>{$row['title']}</a>",
                'link'         => $main->url(array('module' => $main->module, 'do' => 'goto', 'id' => $row['id'])),
                'text_title'   => $row['title'],
                'content'      => $topsite['description_status']==ENABLED ? parse_bb($row['description']) : "",
                'language'     => $row['language'],
                'date'         => user_format_date($row['date']),
                'year'         => format_date($row['date'], 'Y'),
                'month'        => format_date($row['date'], 'm'),
                'day'          => format_date($row['date'], 'd'),
                'lang_date'    => $main->lang['date_pub'],
                'in_hit'       => $row['hits_in'],
                'out_hit'      => $row['hits_out'],                
                'image'        => $topsite['privew_status']==ENABLED ? "<a target='_BLANK' class='sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'goto', 'id' => $row['id']))."' title='{$row['title']}'><img src='{$imgv}' title='{$row['title']}' alt='{$row['title']}' align='left' style='margin: 4px;' /></a>" : "",
                'image_url'    => $imgv,                
                'lang_in_hit'  => $main->lang['in_hit'],
                'lang_out_hit' => $main->lang['out_hit'],                
                'load_tpl'     => $main->tpl,
                'row'          => $line,
                'num_id'       => $i
            );
            $line = ($line=="row1") ? "row2" : "row1";
            //Если рейтинг активен  добавляем JS
            $pub = rating_modify_publisher($row['id'], 'topsites', $row, $pub, $topsite['ratings']==ENABLED);
            //Выводим публикацию
            $c = publisher($row['id'], $pub, true);
            if ($topsite['publications_cols']>1){
                if ($col==1) echo "<tr><td valign='top' width='".round(100/$topsite['publications_cols'], 0)."%'>{$c}</td>\n";
                if ($col<=$topsite['publications_cols']-1 AND $col!=1) echo "<td valign='top' width='".round(100/$topsite['publications_cols'], 0)."%'>{$c}</td>\n";
                if ($col==$topsite['publications_cols']) {echo "<td valign='top'>{$c}</td></tr>\n"; $col=0;}
                $col++;
            } else echo $c;
            $i++;
        }
        if($topsite['publications_cols']>1) echo ($col<$topsite['publications_cols'] AND $col!=1) ? "</tr></table>" : "</table>";
        //pages($numrows, $topsite['publications_in_page'], array('module' => $main->module), true);
        if ($rows==$topsite['publications_in_page'] OR isset($_GET['page'])){
            //Если количество публикаций больше чем количество публикаций на страницу
            if($numrows>$topsite['publications_in_page']){
                //Открываем стилевую таблицу
                open();
                //В зависимости от типа вывода создаем страницы
                if(empty($wheres)) pages($numrows, $topsite['publications_in_page'], array('module' => $main->module), true);
                elseif($wheres=="popular") pages($numrows, $topsite['publications_in_page'], array('module' => $main->module, 'do' => $wheres), true);
                //Закрываем стилевую таблицу
                close();
            }
        }
    } else info($main->lang['noinfo']);
}

function add_top_site(){
global $navi, $topsite;    
    if(hook_check(__FUNCTION__)) return hook();
    //Выводим навигацию по модулю
    echo $navi;
    if(($topsite['publications_users']==ENABLED AND is_user()) OR ($topsite['publications_guest']==ENABLED AND is_guest()) OR is_support()) global_add_topsite();
    else redirect(MODULE);
}

function send_top_site(){
global $topsite;
    if(hook_check(__FUNCTION__)) return hook();
    if(!(($topsite['publications_users']==ENABLED AND is_user()) OR ($topsite['publications_guest']==ENABLED AND is_guest()) OR is_support())) redirect(MODULE);
    else global_save_topsite();
}

function goto_top_site(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".TOPSITES." WHERE id='{$_GET['id']}'");
    if($main->db->sql_numrows($result)){
        $info = $main->db->sql_fetchrow($result);
        sql_update(array('hits_out' => $info['hits_out']+1), TOPSITES, "id={$_GET['id']}");
        redirect($info['link']);
    } else redirect(MODULE);
}

function referer_top_site(){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $result = $main->db->sql_query("SELECT * FROM ".TOPSITES." WHERE link='http://".$_GET['id']."' LIMIT 1");
    if($main->db->sql_numrows($result)>0){
        $info = $main->db->sql_fetchrow($result);
        sql_update(array('hits_in' => $info['hits_in']+1), TOPSITES, "id='{$info['id']}'");
    }
    //redirect("http://".get_host_name()."/");
}
function switch_module_top_site(){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   if(isset($_GET['do'])){
      switch($_GET['do']){        
         case "add": add_top_site(); break;
         case "save": send_top_site(); break;
         case "goto": goto_top_site(); break;
         case "referer": referer_top_site(); break;
         default: main_top_site(); break;  
      }
   } else main_top_site();
}
switch_module_top_site();
?>