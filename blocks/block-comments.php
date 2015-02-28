<?php
/**
* Блок последних комментариев
* 
* @author Wit
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://kasseler-cms.net/
* @filesource blocks/block-comments.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

global $main, $modules;
$result = $main->db->sql_query("SELECT u.uid, u.user_id, c.cid, c.modul, c.date, c.name, c.comment, c.parentid,
        n.news_id, f.files_id, p.pages_id, m.media_id,
        n.title AS news_title, 
        f.title AS files_title, 
        p.title AS pages_title,
        m.title AS media_title,
        a.name AS audio_title,
        aa.title AS albom_title
    FROM ".COMMENTS." AS c
    LEFT JOIN ".USERS." AS u ON (c.name=u.user_name) 
    LEFT JOIN ".NEWS."  AS n ON (c.modul='news'  AND c.parentid=n.id) 
    LEFT JOIN ".FILES." AS f ON (c.modul='files' AND c.parentid=f.id) 
    LEFT JOIN ".PAGES." AS p ON (c.modul='pages' AND c.parentid=p.id) 
    LEFT JOIN ".MEDIA." AS m ON (c.modul='media' AND c.parentid=m.id) 
    LEFT JOIN ".AUDIO." AS a ON (c.modul='audio' AND c.parentid=a.id) 
    LEFT JOIN ".ALBOM." AS aa ON (c.modul='albom' AND c.parentid=aa.id) 
    WHERE  c.modul IN ('news', 'files', 'pages', 'media', 'audio', 'albom')
    ORDER BY c.date DESC 
    LIMIT 5");

if($main->db->sql_numrows($result)>0){
    while($comment = $main->db->sql_fetchrow($result)){
        $modul = $modules[$comment['modul']]['title'];
        $title = $comment["{$comment['modul']}_title"];
        $y=10;
        while($y>1){
            $text = cut_text(strip_tags(preg_replace('/\[(.*?)\](.*?)\[\/(.*?)\]/i', '\\2', $comment['comment'])), $y);
            if(mb_strlen($text)>80) $y--; else break;
        }
        $user = (!is_guest_name($comment['name']) AND !empty($comment['user_id'])) ? "<a class='user_info' href='".$main->url(array('module' => 'account', 'do' => 'user', 'id' => case_id($comment['user_id'], $comment['uid'])))."' title='{$main->lang['user_profile']}'>{$comment['name']}</a>" : $comment['name'] ;
        $more = $main->url(array('module' => $comment['modul'], 'do' => ($comment['modul']!='albom')?'more':'photo', 'id' => case_id(isset($comment["{$comment['modul']}_id"])?$comment["{$comment['modul']}_id"]:$comment['parentid'], $comment['parentid'])));
        echo "<div>{$user} {$main->lang['wrote']}<br /><a href='".$main->url(array('module' => $comment['modul']))."'><b>".(isset($main->lang[$comment['modul']])?$main->lang[$comment['modul']]:$comment['modul'])."</b></a>: <a href='{$more}'>{$title}</a><br />{$text}</div>";
    }
}
?>	