<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

function comments($table, $id, $s_id, $guest_comment, $sort, $pagebreak=false, $warning="", $func='more', $rating_enable = true){
global $userconf, $main, $rewrite, $tpl_create;
    if(hook_check(__FUNCTION__)) return hook();
    echo "<div id='add_comment'>";
    if(is_ajax()) $main->parse_rewrite(array('module', 'do', 'id', 'pagebreak', 'page'));
    $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
    $offset = ($num-1) * 10;
    if($offset<0) kr_http_ereor_logs(404);
    $result = $main->db->sql_query("SELECT c.cid, c.date, c.name, c.comment, u.uid, u.user_id, u.user_avatar, u.user_email, u.user_icq, u.user_signature, u.rating, u.voted, g.title, g.color,r.r_up,r.r_down,r.users
        FROM ".COMMENTS." AS c LEFT JOIN ".USERS." AS u ON (c.name=u.user_name) LEFT JOIN ".GROUPS." AS g ON (u.user_group=g.id) LEFT JOIN ".RATINGS." AS r ON (r.module='comments' and r.idm=c.cid)
        WHERE c.modul='{$main->module}' AND c.parentid='{$id}'
        ORDER BY c.cid {$sort} LIMIT {$offset}, 10");
    open();
    echo "<a name='comments'></a><h2 class='comment'>{$main->lang['comments']}</h2>";
    close();
    $rows = $main->db->sql_numrows($result);
    
    if($rows>0){
        $i = (1*$num>1) ? (10*($num-1))+1 : 1*$num;
        $y = 'row1';
        main::init_function('rating');
        while(list($cid, $date, $name, $comment, $uid, $user_id, $user_avatar, $user_email, $user_icq, $signature, $rating, $voted, $gtitle, $gcolor,$r_up,$r_down,$users) = $main->db->sql_fetchrow($result)){
            $row=array('cid'=>$cid,'r_up'=>$r_up,'r_down'=>$r_down,'users'=>$users);
            $user_avatar = (empty($user_avatar)) ? "guest.png" : $user_avatar;
            $edit = edit_button("#");
            $delete = delete_button("#", " onclick=\"delete_comment({$cid}, {$id}, '".mb_substr(get_env('REQUEST_URI'), 1)."'); return false;\"", false);
            $user_icq = (empty($user_icq)) ? " ---" : $user_icq;
            $pub=array(
                'user_name'     => !is_guest_name($name) ? "<a class='author' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($user_id, $uid)))."' title='{$main->lang['user_profile']}'>{$name}</a>" : $name,
                'uname'         => $name, 
                'comment'       => "<div id='content_comment_{$cid}'><a href='#comment_{$cid}'></a>".parse_bb($comment)."</div>",
                'user_group'    => "<span style='color: #{$gcolor}'>{$gtitle}</span>",
                'user_icq'      => $user_icq,
                'row'           => $y,
                'user_signature'=> parse_bb($signature),
                'date'          => user_format_date($date),
                'avatar'        => get_avatar(array('user_name' => $name, 'user_id' => $user_id, 'uid' => $uid, 'user_avatar' => $user_avatar, 'user_email' => $user_email)),
                'num_comment'   => "<a name='comment_{$i}'></a><a id='comment_{$i}' href='".str_replace("&", "&amp;", get_env('REQUEST_URI'))."#comment_{$i}' title='{$main->lang['comment']} #{$i}'>#{$i}</a>",
                'cid'           => $cid,
                'admin'         => "<table cellpadding='0' cellspacing='0'><tr><td>{$edit}{$delete}</td></tr></table>",
                'edit'          => (is_support() OR ($name==$main->user['user_name'] AND $main->user['uid']!=-1)) ? "<div><a href='#' onclick=\"edit_comment('{$cid}'); return false;\">{$main->lang['edit2']}</a></div>" : "",
                'delete'        => (is_support()) ? "<div><a href='#' onclick=\"delete_comment('{$cid}', '{$id}', '{$table}'); return false;\">{$main->lang['delete']}</a></div>" : ""
            );
            $pub = rating_modify_publisher("c".$row['cid'], 'comments', $row, $pub, $rating_enable);
            show_comment($id, $pub , $cid, "comment_".$y);
            $i++;
            $y = ($y=='row1') ? 'row2' : 'row1';
        }
    } else info($main->lang['nocomment']);
    $pageB = ($pagebreak AND in_array('pagebreak', $rewrite)) ? true : false;
    if($rows==10 OR isset($_GET['page'])){
        list($numrows) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".COMMENTS." WHERE modul='{$main->module}' AND parentid='{$id}'"));
        if($numrows>10){
            open();
            $link = array('module' => $main->module, 'do' => $func, 'id' => case_id($s_id, $id));
            if($pageB) $link = $link+array('pagebreak' => isset($_GET['pagebreak']) ? intval($_GET['pagebreak']) : 1);
            pages($numrows, 10, $link, false);
            close();
        }
    }
    if ($guest_comment!=ENABLED AND is_guest()) info($main->lang['only_guest_comment']);
    else {
        if(!empty($warning)) warning($warning);
        open();
        $action = array('module' => $main->module, 'do' => $func, 'id' => kr_encodeurl($_GET['id']));
        if($pagebreak){
            if(isset($_GET['pagebreak'])){
                $action += array('pagebreak' => intval($_GET['pagebreak']));
                $action += isset($_GET['page']) ? array('page' => intval($_GET['page'])) : array();
            } else $action += isset($_GET['page']) ? array('pagebreak' => 1, 'page' => intval($_GET['page'])) : array();
        } else $action += isset($_GET['page']) ? array('page' => intval($_GET['page'])) : array();
        $user = !is_guest_name($main->user['user_name']) ? "<a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($main->user['user_id'], $main->user['uid'])))."' title='{$main->lang['user_profile']}'>{$main->user['user_name']}</a>" : $main->user['user_name'];        
        if(isset($_POST['comment'])) unset($_POST['comment']);
        echo "<div class='comment_form'><form id='comment_form' method='post' action='".$main->url($action)."' onsubmit=\"add_comment({$id}, '{$sort}', {$rows}, ".(isset($_GET['page']) ? intval($_GET['page']) : 1)."); return false;\">".
            in_hide("id", $id).
            (($pageB) ? in_hide("pagebreak", isset($_GET['pagebreak']) ? intval($_GET['pagebreak']) : 1) : "").
            "<table align='center' id='comment_form_{$main->module}'>\n".
            "<tr><td class='form_text'>{$main->lang['login']}: </td><td class='form_input'>{$user}</td></tr>".
            "<tr><td class='form_text'>{$main->lang['message']}: </td><td class='form_input'>".editor_small("comment")."</td></tr>".
            captcha().
            "<tr><td class='form_submit' colspan='2' align='center'>".send_button()."</td></tr>\n".
            "</table></form></div>";
        close();
    }
    echo "</div>";
}

function save_comment($id, $user, $comment, $table){
global $main;
    if(hook_check(__FUNCTION__)) return hook();
    $main->db->sql_query("INSERT INTO ".COMMENTS." (modul, date, name, ip, comment, parentid) VALUES ('{$main->module}', '".kr_datecms("Y-m-d H:i:s")."', '{$user}', '{$main->ip}', '".bb($comment)."', '{$id}')");
    if(isset($main->points['rating_'.$main->module])) add_points($main->points['rating_'.$main->module]);
    if (is_user()) $main->db->sql_query("UPDATE ".USERS." SET user_comments=user_comments+1 WHERE user_name='{$user}'");
    if(!empty($table)) $main->db->sql_query("UPDATE {$table} SET comment=comment+1 WHERE id='{$id}'");
    list($count) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".COMMENTS." WHERE parentid='{$id}'"));
    return $count;
}

function add_comment($table, $sort, $guest, $func){
global $main, $tpl_create;
if(hook_check(__FUNCTION__)) return hook();
    $modulec = $main->module;
    global $$modulec;
    $localconf = $$modulec;
    $rating_enable = isset($localconf['ratings'])&&$localconf['ratings']==ENABLED;
    $msg = error_empty(array("comment"), array('comment_err')).check_captcha();
    if(empty($msg)){
        $count = save_comment($_POST['id'], $main->user['user_name'], is_ajax() ? kr_filter($_POST['comment']) : kr_filter($_POST['comment']), $table);
        $pages = ceil($count / 10);
        $link = array('module' => $main->module, 'do' => $func, 'id' => kr_normalize_url($_GET['id']));
        if(isset($_POST['pagebreak'])){
            if(isset($_GET['pagebreak'])){
                $link += array('pagebreak' => intval($_GET['pagebreak']));
                $link += isset($_GET['page']) ? array('page' => ($sort=="ASC") ? $pages : 1) : array();
            } else $link += ($pages>1) ? array('pagebreak' => 1, 'page' => ($sort=="ASC") ? $pages : 1) : array();
        } else $link += ($pages>1) ? array('page' => ($sort=="ASC") ? $pages : 1) : array();
        if(!is_ajax()) redirect($main->url($link)."#comment_".(($sort=="ASC") ? $count : "1"));
        else {
            comments($table, $_POST['id'], $_GET['id'], $guest, $sort, (isset($_POST['pagebreak'])) ? true : false, '', $func, $rating_enable);            
            if($sort=="ASC"){ 
            	if($_POST['this_page']!=1) echo "<script language='javascript'>location.href='".str_replace("amp;", "", $main->url($link))."#comment_".(($sort=="ASC") ? $count : "1")."'</script>";
            } elseif($_POST['this_page']!=$pages) echo "<script language='javascript'>location.href='".str_replace("amp;", "", $main->url($link))."#comment_".(($sort=="ASC") ? $count : "1")."'</script>";
            kr_exit();
        }
    } else {
        if(!is_ajax()){
            unset($_POST['id']); if(function_exists($func.'_'.$main->module)) eval(''.$func.'_'.$main->module.'("'.addslashes($msg).'");');
            main::add2script("addEvent(window, 'load', function(){scroll2elm(document.getElementById('comment_form'))});", false);
        } else {
            $text_comment = $_POST['comment'];
            echo "<script language='javascript'>alert('".strip_tags($msg)."')</script>";
            comments($table, $_POST['id'], $_GET['id'], $guest, $sort, (isset($_POST['pagebreak'])) ? true : false, '', $func, $rating_enable);
            main::add2script("setTimeout(function(){\$('#comment').val('{$text_comment}');}, 500);", false);
            kr_exit();
        }
    }
}
?>