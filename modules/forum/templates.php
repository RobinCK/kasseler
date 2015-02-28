<?php
    /**
    * Шаблонизатор форума
    * 
    * @author Igor Ognichenko
    * @copyright Copyright (c)2007-2010 by Kasseler CMS
    * @link http://www.kasseler-cms.net/
    * @filesource modules/forum/templates.php
    * @version 2.0
    */
    if (!defined('KASSELERCMS')) die("Hacking attempt!");

    function pages_forum($numrows, $limit, $url_array, $last_url_array=array()){
        global $main, $links_text;
        if(hook_check(__FUNCTION__)) return hook();
        $content = ""; $numpages = ceil($numrows/$limit); $pagenum = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
        if(isset($_GET['page'])) add_meta_value($main->lang['title_page'].intval($_GET['page']));

        if($numpages > 1){
            $content = "<div class='forumnum'><div class='gotopage'>{$main->lang['go_to_page']}:</div>";
            if(isset($pagenum) AND $pagenum>1) {
                $prev_link = $main->url(array_merge($url_array, array('page' => $pagenum-1), $last_url_array));
                $links_text .= "<link rel='prev' href='{$prev_link}' />\n";
                $content .= "<a class='sys_link' href='{$prev_link}'><b>&#171;</b></a>";
            }
            for($i = 1; $i <= $numpages; $i++){
                if($i == $pagenum) $content .= "<span class='noselect'><b>{$i}</b></span>";
                elseif((($i > ($pagenum - 6)) && ($i < ($pagenum + 6))) OR ($i == $numpages) || ($i == 1)) @$content .= "<a href='".$main->url(array_merge($url_array, array('page' => $i), $last_url_array))."'><b>{$i}</b></a> ";            
                if($i<$numpages){
                    if(($pagenum > 7) && ($i == 1)) $content .= "<span class='more'><b>...</b></span>";
                    if(($pagenum < ($numpages - 6)) && ($i == ($numpages - 1))) $content .= "<span class='more'><b>...</b></span>";
                }
            }
            if($pagenum<$numpages) {
                $next_link = $main->url(array_merge($url_array, array('page' => $pagenum+1), $last_url_array));
                $links_text .= "<link rel='next' href='{$next_link}' />\n";
                $content .= "<a class='sys_link' href='{$next_link}'><b>&#187;</b></a>";
            }
            $content .= "</div>";
        }    
        return $content;
    }


    function quick_link($forum_id=0){
        global $main;
        if(hook_check(__FUNCTION__)) return hook();
        $result = $main->db->sql_query("SELECT f.forum_name, f.forum_id, f.cat_id, c.cat_id, c.cat_title FROM ".FORUMS." AS f LEFT JOIN ".CAT_FORUM." AS c ON(f.cat_id=c.cat_id) ORDER BY c.cat_sort, f.pos");
        $_cat_id = 0;
        $return = "<select name='quick_link' class='quick_link' onchange='location.href=this.value'>";
        while(($row = $main->db->sql_fetchrow($result))){
            if($_cat_id!=$row['cat_id']){
                if($_cat_id!=0) $return .= "</optgroup>";
                $return .= "<optgroup label='{$row['cat_title']}'>";
                $_cat_id = $row['cat_id'];
            }
            $return .= "<option value='".$main->url(array('module' => $main->module, 'do' => 'showforum', 'id' => $row['forum_id']))."'".(($row['forum_id']==$forum_id)?" selected='selected'":"").">{$row['forum_name']}</option>";
        }
        $return .= "</optgroup></select>";
        return $return;
    }

    function search_forums(){
        global $main;
        if(hook_check(__FUNCTION__)) return hook();
        $result = $main->db->sql_query("SELECT f.forum_name, f.forum_id, f.cat_id, c.cat_id, c.cat_title FROM ".FORUMS." AS f LEFT JOIN ".CAT_FORUM." AS c ON(f.cat_id=c.cat_id) ORDER BY c.cat_sort, f.pos");
        $_cat_id = 0; $cat_title = ""; $sel = $sel2 = array();
        while(($row = $main->db->sql_fetchrow($result))){
            if($_cat_id!=$row['cat_id']){
                if($cat_title!='') $sel2 += array('begin_optgroup_'.$_cat_id => $cat_title)+$sel+array('end_optgroup_'.$_cat_id => '');
                $cat_title = $row['cat_title']; $_cat_id = $row['cat_id'];
            }
            $sel[$row['forum_id']] = $row['forum_name'];
        }
        $sel2 += array('begin_optgroup_'.$_cat_id => $cat_title)+$sel+array('end_optgroup_'.$_cat_id => '');
        return in_sels('forums', $sel2, 'case_forum_sel', array(), "", true, 10);
    }

    function quick_sort(){
        global $main;
        if(hook_check(__FUNCTION__)) return hook();
        return in_sels('sort', array('last_post' => $main->lang['srf_last_post'], 'title' => $main->lang['srf_title'], 'author' => $main->lang['srf_author'], 'time' => $main->lang['srf_time'], 'replies' => $main->lang['srf_replies'], 'views' => $main->lang['srf_views']), 'quick_sort_row chzn-search-hide', isset($_GET['sort'])?$_GET['sort']:'last_post', " onchange=\"location.href='http://".get_host_name()."/index.php?module={$main->module}&amp;do=showforum&amp;id={$_GET['id']}&amp;page=".(isset($_GET['page'])?$_GET['page']:1)."&amp;sort='+this.value+'&amp;type=".(isset($_GET['type'])?$_GET['type']:"Z-A")."&amp;time=".(isset($_GET['time'])?$_GET['time']:'all')."&amp;mod=true'\"")." ".
        in_sels('type', array('Z-A' => 'Z-A', 'A-Z' => 'A-Z'), 'quick_sort_type chzn-search-hide', isset($_GET['type'])?$_GET['type']:'Z-A', " onchange=\"location.href='http://".get_host_name()."/index.php?module={$main->module}&amp;do=showforum&amp;id={$_GET['id']}&amp;page=".(isset($_GET['page'])?$_GET['page']:1)."&amp;sort=".(isset($_GET['sort'])?$_GET['sort']:"time")."&amp;type='+this.value+'&amp;time=".(isset($_GET['time'])?$_GET['time']:'all')."&amp;mod=true'\"")." ".
        in_sels('time', array('lastvisit' => $main->lang['srfd_lastvisit'], 'all' => $main->lang['srfd_all'], 'today' => $main->lang['srfd_today'], 'five_days' => $main->lang['srfd_five_days'], 'seven_days' => $main->lang['srfd_seven_days'], 'ten_days' => $main->lang['srfd_ten_days'], 'fifteen_days' => $main->lang['srfd_fifteen_days'], 'twenty_days' => $main->lang['srfd_twenty_days'], 'twenty_five_days' => $main->lang['srfd_twenty_five_days'], 'thirty_days' => $main->lang['srfd_thirty_days'], 'sixty_days' => $main->lang['srfd_sixty_days'], 'ninety_days' => $main->lang['srfd_ninety_days']), 'quick_sort_time chzn-search-hide', isset($_GET['time'])?$_GET['time']:'all', " onchange=\"location.href='http://".get_host_name()."/index.php?module={$main->module}&amp;do=showforum&amp;id={$_GET['id']}&amp;page=".(isset($_GET['page'])?$_GET['page']:1)."&amp;sort=".(isset($_GET['sort'])?$_GET['sort']:"time")."&amp;type=".(isset($_GET['type'])?$_GET['type']:"Z-A")."&amp;time='+this.value+'&amp;mod=true'\"");
    }
?>