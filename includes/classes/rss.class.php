<?php
if (!defined('FUNC_FILE')) die('Access is limited');

class rss_writer{
    var $items = "";
    var $conf;

    function add_item($id, $title, $date, $cat, $content, $author, $rss_title){
    global $main;
        $arr = array();
        if (preg_match_all('/<img(.*?)src\s{0,}=\s{0,}[\'|"](.+?)[\'|"](.*?)>/si', $content, $regs, PREG_PATTERN_ORDER)) {
           foreach($regs[2] as $k => $v) {
              if (substr($regs[2][$k], 0, 7) == 'uploads') {
                 if (!in_array($regs[2][$k], $arr)) $arr[] = $regs[2][$k];
              }
           }
           if (!empty($arr)) {
              foreach ($arr as $v) $content = str_replace($v, "http://".get_host_name()."/{$v}", $content);
           }
        }
        $content = preg_replace('/<a(.*?)href\s{0,}=\s{0,}[\'|"](.+?)[\'|"](.*?)>/si', "<a\\1href='http://".get_host_name()."/\\2'\\3>", $content);
        $this->conf = $rss_title;
        $this->items .= "<item>\n".
        "<title>{$title}</title>\n".
        "<pubDate>".htmlspecialchars(date("D, j M Y H:i:s O", strtotime($date)))."</pubDate>\n".
        "<guid>".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => $id))."</guid>\n".
        "<description>".htmlspecialchars($content)."</description>\n".
        "<comments>".$main->url(array('module' => $main->module, 'do' => 'more', 'id' => $id))."</comments>\n".
        "<category>{$cat}</category>\n".
        "<author>{$author}</author>\n".
        "</item>";
    }
    
    function write(){
    global $config;
        header("Content-Type: application/rss+xml");
        die("<?xml version='1.0' encoding='{$config['charset']}' ?".">\n".
        "<rss version='2.0'>\n".
        "<channel>\n".
        "<title>{$this->conf}</title>\n".
        "<link>{$config['http_home_url']}</link>\n".
        "<description>{$config['description']}</description>\n".
        "<generator>Kasseler CMS {$config['cms_version']}</generator>\n".
        "<copyright>Copyright (c) Kasseler CMS {$config['cms_version']}</copyright>\n".
        "<language>ru-ru</language>\n".
        "<lastBuildDate>".date("D, j M Y H:i:s O")."</lastBuildDate>\n".
        $this->items.
        "</channel>\n</rss>");
    }
}
?>
