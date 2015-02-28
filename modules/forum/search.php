<?php
/**
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('KASSELERCMS')) die("Hacking attempt!");
global $main, $forum, $template;
      
$stop = false;
$length_ignore = 3;
$ignore_str = $where_words = $msg = "";

if(!isset($_GET['action']) AND !isset($_GET['id'])){
    $template->get_tpl('forum/search', 'search');
    $template->set_tpl(array(
        'OPEN_TABLE'            => open(true),
        'CLOSE_TABLE'           => close(true),
        'LOAD_TPL'              => $main->tpl,
        'INPUT_STRING'          => in_text('string', 'input_text2'),
        'INPUT_AUTHOR'          => in_text('author', 'input_text2'),
        'MSG'                   => '',
        
        'MENU_PROFILE'          => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
        'MENU_SEARCH'           => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
        'MENU_USERS'            => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
        'MENU_LOGOUT'           => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",
        
        'SEARCH_ACTION'         => "index.php?module={$main->module}&do=search",
        'SEARCH_METHOD'         => 'get',
        'HIDE_INPUTS'           => in_hide('module', $main->module).in_hide('do', 'search').in_hide('action', 'search'),
        'SEARCH_KEY'            => $main->lang['fotum_search_key'],
        'SEARCH_AUTHOR'         => $main->lang['search_author_topic'],
        'SEARCH_FORUM'          => $main->lang['sase_search_forum'],
        'FORUM_LIST'            => search_forums(),
        'TYPE_SERCHES'          => in_radio('type', '0', $main->lang['only_topic'], 'tp1', true)."<br />".in_radio('type', '1', $main->lang['only_post'], 'tp2')."<br />".in_radio('type', '2', $main->lang['topic_and_post'], 'tp3'),
        'LANG_SEARCH'           => $main->lang['search'],
        'LAST_VISIT_DATE'       => is_user()?$main->lang['forum_last_visit'].": ".format_date(isset($_SESSION['lastVisit'])?$_SESSION['lastVisit']:kr_date("Y-m-d H:i:s"), "{$main->config['date_format']} H:i:s"):"",
        'CURRENT_TIME'          => format_date(kr_date("Y-m-d H:i:s"), "{$main->config['date_format']} H:i:s"),
        'L_INDEX'               => "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",
        'L_SEARCH_NEW'          => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'unanswered'))."' title='{$main->lang['search_new']}'>{$main->lang['search_new']}</a>":"",
        'L_SEARCH_SELF'         => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'egosearch', 'id' => kr_encodeurl($main->user['user_name'])))."' title='{$main->lang['search_self']}'>{$main->lang['search_self']}</a>":"",
        'L_SEARCH_UNANSWERED'   => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'newposts'))."' title='{$main->lang['search_unanswered']}'>{$main->lang['search_unanswered']}</a>":"",
        
    ), 'search', array('start' => '{', 'end' => '}'));  
    
    $template->tpl_create(false, 'search');
} else {    
    if(!isset($_GET['id'])){
        $_S = array(
            'string'     => isset($_GET['string']) ? kr_filter($_GET['string'], HTML) : '',
            'words'      => isset($_GET['string']) ? kr_filter($_GET['string'], HTML) : '',
            'author'     => isset($_GET['author']) ? kr_filter($_GET['author'], HTML) : '',
            'type'       => (isset($_GET['type']) AND preg_match('/0|1|2/', $_GET['type'])) ? $_GET['type'] : 0,
            'forums'     => (isset($_GET['forums']) AND is_array($_GET['forums'])) ? $_GET['forums'] : array(),
            'search_arr' => array(),
        );
        $strings = mb_strpos($_S['words'], '|') !== false ? explode('|', $_S['words']) : array($_S['words']);
        $s = array();
        foreach($strings as $key => $value){
            $strings[$key] = trim($value);
            $s += explode(' ', $strings[$key]);
        }
        $_S['search_arr'] = $s;
        foreach($_S['search_arr'] as $value) if(mb_strlen($value)<$length_ignore) $ignore_str .= $value." ";
        foreach(array("\n", "\r", '<br>', '<br />', '<p>', ' -', '.', '&nbsp;', '&', ',', '!', '«', '»', '?', ':', ';', ')', '(', '"', '\'') as $_v) $_S['words'] = str_replace($_v, ' ', $_S['words']);        
        while(strstr($_S['words'],"  ")) $_S['words'] = str_replace("  "," ",$_S['words']);
        $search_key = crc32_integer(var_export($_S, true));
        $main->db->sql_query("DELETE FROM ".FORUM_KEYS." WHERE `key` = '{$search_key}'");
        sql_insert(array('key' => $search_key, 'query' => addslashes(var_export($_S, true)), 'ignore' => $ignore_str), FORUM_KEYS);
    } else {
        $result = $main->db->sql_query("SELECT `key`, `query`, `ignore` FROM ".FORUM_KEYS." WHERE `key`='{$_GET['id']}'");
        if($main->db->sql_numrows($result)>0){
            list($search_key, $_s, $ignore_str) = $main->db->sql_fetchrow($result);
            if(mb_strpos($_s, 'array') !== false) eval('$_S = '.$_s.';');
            else $_S = array('string' => $_s, 'words' => $_s, 'author' => '', 'type' => 0, 'forums' => array(), 'search_arr' => array($_s));
            //echo "<pre>".var_export($_S, true)."</pre>";
        } //else redirect($main->url(array('module' => $main->module, 'do' => 'search')));
    }
    if((!empty($_S['search_arr']) AND !empty($_S['words'])) OR !empty($_S['author'])){
        $main->db->sql_query("DELETE FROM ".FORUM_SEARCH." WHERE time<'".(time()-24*60*60)."'");
        list($count_keys) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FORUM_SEARCH." WHERE `key` = '{$search_key}'"));
        if($count_keys==0){
            foreach($_S['search_arr'] as $value){
                $value = mb_strpos($value, '*') !== false ? str_replace('*', '_', $value) : $value;
                $value = str_replace(array('*', '+', '-'), '', $value);
                switch($_S['type']){
                    case "0": $where_words .= " OR UPPER(t.topic_title) LIKE '%".mb_strtoupper($value)."%'"; break;
                    case "1": $where_words .= " OR UPPER(p.post_text) LIKE '%".mb_strtoupper($value)."%'"; break;
                    case "2": $where_words .= " OR UPPER(t.topic_title) LIKE '%".mb_strtoupper($value)."%' OR UPPER(p.post_text) LIKE '%".mb_strtoupper($value)."%'"; break;
                }
            }
            $where_words = mb_substr($where_words, 4, mb_strlen($where_words));
            $where = !empty($where_words) ? "WHERE (".$where_words.")" : "WHERE t.topic_id<>'0'";
            if(is_array($_S['forums']) AND count($_S['forums'])>0) $where .= " AND t.forum_id IN (".implode(',', $_S['forums']).")";
            if(!empty($_S['author'])){
                if($_S['type']==0) $where .= " AND t.topic_poster_name LIKE '".str_replace('*', '%', $_S['author'])."'";
                else if($_S['type']==1) $where .= " AND p.poster_name LIKE '".str_replace('*', '%', $_S['author'])."'";
                else if($_S['type']==2) $where .= " AND (t.topic_poster_name LIKE '".str_replace('*', '%', $_S['author'])."' OR p.poster_name LIKE '".str_replace('*', '%', $_S['author'])."')";
            }
            $result = $main->db->sql_query("SELECT t.topic_id, t.topic_poster_name, t.forum_id, t.topic_title, p.poster_name, p.post_text FROM ".TOPICS." AS t LEFT JOIN ".POSTS." AS p ON(p.topic_id=t.topic_id) {$where} GROUP BY t.topic_id");
            if($main->db->sql_numrows($result)>0){
                $insert = "INSERT INTO `".FORUM_SEARCH."` VALUES \n";
                while(($row = $main->db->sql_fetchrow($result))){
                    $keywords = $authors = "";
                    foreach($strings as $value){
                        if(mb_strlen($value)>=$length_ignore) $keywords .= mb_strpos($row['topic_title'], $value) !== false ? $value." " : (mb_strpos($row['post_text'], $value) !== false ? $value." " : '');
                    }
                    $insert .= "(NULL, '{$search_key}', '{$row['topic_id']}', '".time()."', ''),\n";
                }
                $insert = mb_substr($insert, 0 , mb_strlen($insert)-2).";";             
                $main->db->sql_query($insert);
            }
        }
        if(!isset($_GET['id'])) redirect($main->url(array('module' => $main->module, 'do' => 'search', 'id' => $search_key)));
    } else {
        $stop = true;
        $msg = $main->lang['nosearch_param'];
    }
    
    if($stop==false){
        $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
        $offset = ($num-1) * $forum['topic_views_num'];
        $result = $main->db->sql_query("SELECT s.*, 
            t.topic_status, t.topic_type, t.topic_id, t.topic_title, t.topic_poster, t.topic_replies, t.topic_views, t.topic_time, t.topic_first_post_id, t.topic_last_post_id, t.topic_desc, t.ico, 
            p.post_id, p.poster_name, p.poster_id, p.post_time,
            u.uid, u.user_id, u.user_name,
            uu.uid AS poster_uid, uu.user_id AS poster_user_id, uu.user_name AS poster_user_name
            FROM ".TOPICS." AS t, ".POSTS." AS p, ".USERS." AS u, ".USERS." AS uu, ".FORUM_SEARCH." AS s
            WHERE s.key = '{$search_key}' AND s.topic_id=t.topic_id AND p.poster_id=uu.uid AND t.topic_last_post_id=p.post_id AND t.topic_poster=u.uid AND t.topic_first_post_id<>'0'
            ORDER BY length(s.keywords) DESC, t.topic_type DESC, t.topic_time DESC
            LIMIT {$offset}, {$forum['topic_views_num']}");
            $count_rows = $main->db->sql_numrows($result);
    } else $count_rows = 0;
        
    if($count_rows>0){
        $content = ""; $i=1; $row_c = "rows2";
        
        $template->get_tpl('forum/search_result', 'search_result');
        $match = "";
        preg_match('/<\!--begin\stopic\srow-->(.+?)<\!--end\stopic\srow-->/si', $template->template['search_result'], $match);
        $topic_row = $match[1];
        $template->template['search_result'] = preg_replace('/<\!--begin\stopic\srow-->(.+?)<\!--end\stopic\srow-->/si', '{TOPIC_CONTENT}', $template->template['search_result']);
        
        list($count_topics) = $main->db->sql_fetchrow($main->db->sql_query("SELECT COUNT(*) FROM ".FORUM_SEARCH." WHERE `key`='{$search_key}'"));        
        $_ss = $ss = array();
        foreach(explode('|', $_S['string']) as $value) $_ss[] = $value;
        foreach($_ss as $value) {
            foreach(explode(' ', $value) as $val) if(mb_strlen($val)>=$length_ignore) $ss[] = $val;
        }
        $colors_bg = array('#FFDD00', '#0099DD', '#A0EF4A', '#EF95AA', '#EFBD95'); $colors_t = array('#333333', '#FFFFFF', '#333333', '#333333', '#333333');
        $color_count = 0;
        $ig = $ignore_str;
        $se = $_S['words'];
        while($i<=$count_rows){
            $row = $main->db->sql_fetchrow($result);            
            $floder = get_folder_topic($row['topic_id'], $row['topic_last_post_id'], $row['topic_status'], $row['topic_replies'], $row['topic_type']);
            $topic_title = $row['topic_title'];
            foreach($ss as $v){
                $topic_title = preg_replace("/{$v}(.*?)([\s]*)/is", "<span style=\"color: {$colors_t[$color_count]}; background: {$colors_bg[$color_count]};\">{$v}\\2</span>", $topic_title);                
                $color_count++;
                if($color_count>4) $color_count=0;
            }
            $color_count=0;
            $topics_rows = array(
                'topicrow.TOPIC_FOLDER_IMG'     => $floder[0],
                'topicrow.L_TOPIC_FOLDER_ALT'   => $floder[1],
                'topicrow.TOPIC_ICO'            => !empty($row['ico']) ? "<img src='".TEMPLATE_PATH."{$main->tpl}/forum/ico_topic/{$row['ico']}.gif' alt='' />" : "&nbsp;",
                'topicrow.TOPIC_TITLE'          => "<a href='".$main->url(array('module' => $main->module, 'do' => 'showtopic', 'id' => $row['topic_id']))."' title='{$row['topic_title']}'>{$topic_title}</a>",
                'topicrow.TOPIC_DESC'           => $row['topic_desc'],
                'topicrow.GOTO_PAGE'            => '',
                'topicrow.ROW_CLASS'            => $row_c,
                'topicrow.REPLIES'              => $row['topic_replies'],
                'topicrow.TOPIC_AUTHOR'         => (!is_guest_name($row['user_name']) AND $row['topic_poster']!=-1) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title='{$main->lang['user_profile']}'>{$row['user_name']}</a>" : $row['user_name'],
                'topicrow.LAST_POST_TIME'       => user_format_date(gmdate("Y-m-d H:i:s", $row['topic_time']), true),
                'topicrow.LAST_POST_AUTHOR'     => "{$main->lang['ot']} ".((!is_guest_name($row['poster_user_name']) AND $row['poster_id']!=-1) ? "<a class='author user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($row['poster_user_id'], $row['poster_uid'])))."' title='{$main->lang['user_profile']}'>{$row['poster_user_name']}</a>" : $row['poster_user_name']),
                'topicrow.LAST_POST_IMG'        => !empty($row['topic_title']) ? "<a href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $row['topic_id']))."'><img src='".TEMPLATE_PATH."{$main->tpl}/forum/images/icon_topic_latest.png' alt='' /></a>" : "",
                'topicrow.VIEWS'                => $row['topic_views'],
                'LOAD_TPL'                      => $main->tpl
            );
            $row_c = ($row_c=='rows2') ? "rows1" : 'rows2';
            $replace = array('key' => array(), 'value' => array());
            foreach($topics_rows as $key => $value){$replace['key'][] = '{'.$key.'}'; $replace['value'][] = $value;}
            $content .= (!empty($replace['key'])) ? str_replace($replace['key'], $replace['value'], $topic_row) : "";
            $i++;
        }
        $pagenums = "";
        if($count_rows==$forum['topic_views_num'] OR isset($_GET['page'])){
            $pages = ceil($count_topics/$forum['topic_views_num']);
            $pagenums = pages_forum($count_topics, $forum['topic_views_num'], array('module' => $main->module, 'do' => 'search', 'id' => $search_key));
        } else $pages = 1;
        //$tpl->template = preg_replace('/<\!--begin\sshowtopic\srow-->(.+?)<\!--end\sshowtopic\srow-->/si', '{TOPIC_CONTENT}', $tpl->template);
        $_s = explode(' ', $se);
        $se = '';        
        foreach($_s as $value) $se .= "<a href='index.php?module={$main->module}&amp;do=search&amp;action=search&amp;string=".urlencode($value)."'>{$value}</a> ";
        if(empty($ig)) $ig = '-';
        if(empty($se) OR $se==' ' OR $se=='-') $se = '-';
        $template->set_tpl(array(
            'OPEN_TABLE'            => open(true),
            'CLOSE_TABLE'           => close(true),
            'LOAD_TPL'              => $main->tpl,
            'SEARCH_INFO'           => ($se=='-') ? '' : "<b>{$main->lang['search_query']}</b>: {$se} <b>{$main->lang['search_ignore']}</b>: {$ig}",                        
            'L_INDEX'               => "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",
            'L_TOPICS'              => $main->lang['topic'],
            'L_REPLIES'             => $main->lang['replies'],
            'L_AUTHOR'              => $main->lang['poster'],
            'L_VIEWS'               => $main->lang['topic_viws'],
            'L_LASTPOST'            => $main->lang['topic_last'],
            'L_NEW_POSTS'           => $main->lang['new_posts'],
            'L_NO_NEW_POSTS'        => $main->lang['no_new_posts'],
            'L_ANNOUNCEMENT'        => $main->lang['announcement'],
            'L_NEW_POSTS_HOT'       => $main->lang['new_posts_hot'],
            'L_NO_NEW_POSTS_HOT'    => $main->lang['no_new_posts_hot'],
            'L_STICKY'              => $main->lang['sticky'],
            'L_NEW_POSTS_LOCKED'    => $main->lang['new_posts_locked'],
            'L_NO_NEW_POSTS_LOCKED' => $main->lang['no_new_posts_locked'],
            'L_MODERATOR'           => $main->lang['forum_moders'],
            'PAGINATION'            => $pagenums,
            'TOPIC_CONTENT'         => ($count_rows>0)?$content:info($main->lang['notopics'], true),
            'PAGE_NUMBER'           => isset($pages) ? preg_replace(array('/\{THIS\}/i', '/\{ALL\}/i'), array(isset($_GET['page'])?$_GET['page']:1, $pages), $main->lang['numberpage']) : "",
        ), 'search_result', array('start' => '{', 'end' => '}'));  
        
        $template->tpl_create(false, 'search_result');
    } else {
        $template->get_tpl('forum/search', 'search');
        $template->set_tpl(array(
            'OPEN_TABLE'            => open(true),
            'CLOSE_TABLE'           => close(true),
            'LOAD_TPL'              => $main->tpl,
            'INPUT_STRING'          => in_text('string', 'input_text2'),
            'INPUT_AUTHOR'          => in_text('author', 'input_text2'),
            'MSG'                   => empty($msg) ? warning($main->lang['nosearch_topic'], true) : warning($msg, true) ,
            
            'MENU_PROFILE'          => "<a href='".$main->url(array('module' => 'account', 'do' => 'controls'))."' title='{$main->lang['personal_page']}'>{$main->lang['personal_page']}</a>",
            'MENU_SEARCH'           => "<a href='".$main->url(array('module' => $main->module, 'do' => 'search'))."' title='{$main->lang['search']}'>{$main->lang['search']}</a>",
            'MENU_USERS'            => "<a href='".$main->url(array('module' => 'top_users'))."' title='{$main->lang['users']}'>{$main->lang['users']}</a>",
            'MENU_LOGOUT'           => is_user() ? "<a href='".$main->url(array('module' => 'account', 'do' => 'logout'))."' title='{$main->lang['logout']}'><b>{$main->lang['logout']} [ {$main->user['user_name']} ]</b></a>" : "<a href='".$main->url(array('module' => 'account', 'do' => 'login'))."' title='{$main->lang['logined']}'>{$main->lang['logined']}</a> | <a href='".$main->url(array('module' => 'account', 'do' => 'new_user'))."' title='{$main->lang['register']}'>{$main->lang['register']}</a>",
            
            'SEARCH_ACTION'         => "index.php?module={$main->module}&do=search",
            'SEARCH_METHOD'         => 'get',
            'HIDE_INPUTS'           => in_hide('module', $main->module).in_hide('do', 'search').in_hide('action', 'search'),
            'SEARCH_KEY'            => $main->lang['fotum_search_key'],
            'SEARCH_AUTHOR'         => $main->lang['search_author_topic'],
            'SEARCH_FORUM'          => $main->lang['sase_search_forum'],
            'FORUM_LIST'            => search_forums(),
            'TYPE_SERCHES'          => in_radio('type', '0', $main->lang['only_topic'], 'tp1', true)."<br />".in_radio('type', '1', $main->lang['only_post'], 'tp2')."<br />".in_radio('type', '2', $main->lang['topic_and_post'], 'tp3'),
            'LANG_SEARCH'           => $main->lang['search'],
            'LAST_VISIT_DATE'       => is_user()?$main->lang['forum_last_visit'].": ".format_date(isset($_SESSION['lastVisit'])?$_SESSION['lastVisit']:kr_date("Y-m-d H:i:s"), "{$main->config['date_format']} H:i:s"):"",
            'CURRENT_TIME'          => format_date(kr_date("Y-m-d H:i:s"), "{$main->config['date_format']} H:i:s"),
            'L_INDEX'               => "<a href='".$main->url(array('module' => $main->module))."' title='{$forum['forum_title']}'>{$forum['forum_title']}</a>",
            'L_SEARCH_NEW'          => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'unanswered'))."' title='{$main->lang['search_new']}'>{$main->lang['search_new']}</a>":"",
            'L_SEARCH_SELF'         => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'egosearch', 'id' => kr_encodeurl($main->user['user_name'])))."' title='{$main->lang['search_self']}'>{$main->lang['search_self']}</a>":"",
            'L_SEARCH_UNANSWERED'   => is_user()?"<a href='".$main->url(array('module' => $main->module, 'do' => 'newposts'))."' title='{$main->lang['search_unanswered']}'>{$main->lang['search_unanswered']}</a>":"",            
        ), 'search', array('start' => '{', 'end' => '}'));  

        $template->tpl_create(false, 'search');
    }
}
?>