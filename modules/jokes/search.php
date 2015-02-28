<?php
/**
* Файл поиска публикаций
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource modules/jokes/search.php
* @version 2.0
*/
if (!defined('SEARCH_MODULE')) die("Hacking attempt!");

global $_S, $main, $search_key, $length_ignore;

$where_words = "";
$sql = "SELECT * FROM ".JOKES." WHERE status='1'";
$sql .= !empty($_S['author']) ? ($_S['author_full']==true?" AND author='{$_S['author']}'" : " AND UPPER(author) LIKE '".mb_strtoupper($_S['author'])."%'") : '';
if(!empty($_S['story'])){
    foreach(array("\n", "\r", '<br>', '<br />', '<p>', ' -', '.', '&nbsp;', '&', ',', '!', '«', '»', '?', ':', ';', ')', '(', '"', '\'') as $_v) $_S['story'] = str_replace($_v, ' ', $_S['story']);
    while(strstr($_S['story'],"  ")) $_S['story'] = str_replace("  ", " ", $_S['story']);
    $_S['story'] = str_replace('*', '_', $_S['story']);
    $strings = explode(' ', $_S['story']);
    foreach($strings as $value){
        if(mb_strlen($value)>=$length_ignore){
            switch($_S['search_type']){
                case "0": $where_words .= " OR UPPER(title) LIKE '%".mb_strtoupper($value)."%'"; break;
                case "1": $where_words .= " OR UPPER(joke) LIKE '%".mb_strtoupper($value)."%'"; break;
                case "2": $where_words .= " OR UPPER(title) LIKE '%".mb_strtoupper($value)."%' OR UPPER(joke) LIKE '%".mb_strtoupper($value)."%'"; break;
            }
        }
    }
    if(!empty($where_words)){
        $where_words = mb_substr($where_words, 4, mb_strlen($where_words));
        $sql .= " AND {$where_words}";
    }
}

$result = $main->db->sql_query($sql);
if($main->db->sql_numrows($result)>0){
    $insert = "INSERT INTO ".SEARCH." VALUES \n";
    while($row = $main->db->sql_fetchrow($result)){
        $keywords = "";
        if(isset($strings)){
            foreach($strings as $value){
                if(mb_strlen($value)>=$length_ignore) {
                    switch($_S['search_type']){
                        case "0": $keywords .= mb_strpos($row['title'], $value) !== false ? $value." " : ''; break;
                        case "1": $keywords .= mb_strpos($row['joke'], $value) !== false ? $value." " : ''; break;
                        case "2": 
                            $keywords .= mb_strpos($row['title'], $value) !== false ? $value." " : '';
                            $keywords .= mb_strpos($row['joke'], $value) !== false ? $value." " : '';
                            break;
                    }                    
                }
            }
        }
        $insert .= "(NULL, '{$search_key}', '".addslashes($row['title'])."', '{$row['author']}', '".addslashes($row['joke'])."', '{$row['date']}', 'jokes', '{$row['id']}', '{$row['id']}', '".time()."', '{$keywords}'),";
    }        
    $main->db->sql_query(mb_substr($insert, 0, mb_strlen($insert)-1));
}
?>