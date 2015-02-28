<?php
/**
* Генератор sitemap
*
* @author Wit
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://kasseler-cms.net/
* @filesource includes/function/sitemap.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');
global $main, $adminfile, $modules_sitemap, $file_sitemap;

if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit(0);
//if(!file_exists("uploads/tmpfiles/runer.locked")) exit;
$main->config['multilanguage'] = '';

function get_setting_sitemap($str=''){
    if(hook_check(__FUNCTION__)) return hook();
    $set = array('priority'=>'0.5', 'changefreq'=>'monthly');
    if ($str!='') {
        $modul = explode ('|', $str);
        if (isset($modul[0])) $set['priority']   = $modul[0];
        if (isset($modul[1])) $set['changefreq'] = $modul[1];
    }
    return $set;
}

function send_ping($url){
    if(hook_check(__FUNCTION__)) return hook();
    $ch = curl_init();
    $close = true;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2) Gecko/20100115 Firefox/8.0 kasselerbot');
    curl_setopt($ch, CURLOPT_HEADER, 0); //устанавливаем параметр CURL, чтобы в ответе ловить заголовки страницы
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 10); //результат CURL возвращает, а НЕ выводит
    $content = curl_exec($ch);
    if(preg_match('/Location:.*/s', $content)){
        $location = "";
        preg_match('/Location: (.*)$/s', $content, $location);
        $url_str = trim($location[1]);
        curl_close($ch);
        $close = false;
        $content = send_ping($url_str);
    }
    if($close==true) curl_close($ch);
    return $content;
}

$ping_urls = array(
    'google'    => 'http://google.com/webmasters/sitemaps/ping?sitemap=',
    'yandex'    => 'http://webmaster.yandex.ru/wmconsole/sitemap_list.xml?host=',
    'yahoo'     => 'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=',
    'bing'      => 'http://www.bing.com/webmaster/ping.aspx?siteMap=',
    'ask'       => 'http://submissions.ask.com/ping?sitemap=',
);

$result = $main->db->sql_query("SELECT * FROM ".MODULES." WHERE active='1' AND (view='1' OR view='2')");
$row = array();
while (($rows=$main->db->sql_fetchrow())) $row[] = $rows;
$for = array();
foreach ($row as $key=>$value) {
    if($value['module']=='forum') $for = $row[$key];
    if(!isset($modules_sitemap[$value['module']])) unset($row[$key]);
}

$HTML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n\t\txmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n\t\txsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n\n\t";
if(count($row)!=0) {
    foreach ($row as $key=>$value) {
        $set = get_setting_sitemap($value['sitemap']);
        if($modules_sitemap[$value['module']]!='') {
            $res = $main->db->sql_query("SELECT id, {$value['module']}_id, date FROM {$modules_sitemap[$value['module']]} WHERE status='1' ORDER BY date DESC");
            if($main->db->sql_numrows($res)>0){
                while(($row = $main->db->sql_fetchrow($res))){
                    $HTML.= "<url>\n\t\t<loc>".($main->url(array('module' => $value['module'], 'do' => 'more', 'id' => case_id($row["{$value['module']}_id"], $row['id']))))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", strtotime($row['date']))."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
                }
            } else $HTML.= "<url>\n\t\t<loc>".($main->url(array('module' => $value['module'])))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", time())."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
        } else $HTML.= "<url>\n\t\t<loc>".($main->url(array('module' => $value['module'])))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", time())."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
    }
}
if(isset($for['module']) AND $for['active']==1 AND ($for['view']==1 or $for['view']==2)) {
    $res = $main->db->sql_query("SELECT t.topic_id AS id, p.post_time FROM ".TOPICS." AS t LEFT JOIN ".POSTS." AS p ON (p.post_id=t.topic_last_post_id) ORDER BY p.post_time DESC ");
    if($main->db->sql_numrows($res)>0){
        $set = get_setting_sitemap($for['sitemap']);
        while(($row = $main->db->sql_fetchrow($res))){
            $HTML .= "<url>\n\t\t<loc>".($main->url(array('module' => 'forum', 'do' => 'showtopic', 'id' => $row['id'])))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", $row['post_time'])."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
        }
    } else $HTML.= "<url>\n\t\t<loc>".($main->url(array('module' => 'forum')))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", time())."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
}

if(isset($for['module']) AND $for['active']==1 AND ($for['view']==1 or $for['view']==2)) {
    $res = $main->db->sql_query("SELECT t.topic_id AS id, p.post_time, p.post_id AS pid FROM ".TOPICS." AS t LEFT JOIN ".POSTS." AS p ON (p.post_id=t.topic_last_post_id) ORDER BY p.post_time DESC ");
    if($main->db->sql_numrows($res)>0){
        $set = get_setting_sitemap($for['sitemap']);
        while(($row = $main->db->sql_fetchrow($res))){
            $HTML .= "<url>\n\t\t<loc>".($main->url(array('module' => 'forum', 'do' => 'showpost', 'id' => $row['pid'])))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", $row['post_time'])."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
        }
    } else $HTML.= "<url>\n\t\t<loc>".($main->url(array('module' => 'forum')))."</loc>\n\t\t<priority>{$set['priority']}</priority>\n\t\t<lastmod>".date("c", time())."</lastmod>\n\t\t<changefreq>{$set['changefreq']}</changefreq>\n\t</url>\n\t";
}

$HTML.= "\n</urlset>";
file_write($file_sitemap, $HTML);

foreach($ping_urls as $k=>$v){
    send_ping($v.'http://'.get_host_name().'/'.$file_sitemap);
}
?>