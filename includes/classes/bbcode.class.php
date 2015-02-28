<?php
/**
* Класс преобразований bb кодов в HTML и HTML в bb коды
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/bbcode.class.php
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

global $bb;
function new_nl2br($matches){
   return $matches[1]!="\r"?$matches[1]."<br />":$matches[0];
}
class bbcode {
    /**
    * Конвертируемый текст
    * 
    * @var string
    */
    var $text = '';
    
    /**
    * Массив символов которые нужно заменить на их код
    * 
    * @var array
    */
    var $array_replace = array('/<#/', '/&lt;/', '/&gt;/', '/&quot;/', '/:/', '/\[/', '/\]/', '/\)/', '/\(/', '/#\s{1};/');
    
    /**
    * Коды символов для замены массива $this->array_replace
    * 
    * @var array
    */
    var $array_html = array("&#60;", "&#60;", "&#62;", "&#34;", "&#58;", "&#91;", "&#93;", "&#41;", "&#40;", "&#59;");
    
    /**
    * Коды символов
    * 
    * @var array
    */
    var $arr_html = array('/&\#60;/', '/&lt;/', '/&\#62;/', '/&gt;/', '/&\#34;/', '/&\#58;/', '/&\#91;/', '/&\#93;/', '/&\#41;/', '/&\#40;/');
    
    /**
    * Массив замены кодов с $this-> arr_html на символы
    * 
    * @var array
    */
    var $arr_replace = array("<", "<", ">", ">", "&quot;", ":", "[", "]", ")", "(");
    
    /**
    * Массив кэшируемых bb кодов
    * 
    * @var array
    */
    var $cache_code = array();

    /**
    * Функция кэша bb кодов и передачи текста в класс преобразования
    * 
    * @param mixed $text
    * @return void
    */
    function set_text($text){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        //cache bbcode
        $tags = 'youtube|video|flash|mp3|radio|code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql';
        $this->text = preg_replace_callback('%\[nobb\](.+?)\[/nobb\]%is', function($matches) { global $bb; return $bb->nobb_tag($matches[1]);}, $text);
        foreach(explode('|', $tags) as $t) $this->text = preg_replace_callback('%\[('.$t.')\](.+?)\[\/('.$t.')\]%is', function($matches) { global $bb; return $bb->cache_code($matches[2], $matches[1]);}, $this->text);
        $this->bb2html();
    }
    
    function replace_cache(){
        $this->text = preg_replace_callback('%\{cache-bbcode-([0-9]+)-([a-zA-Z0-9]+)\}%', function($matches) { global $bb; return $bb->set_cache($matches[1], $matches[2]);}, $this->text);
        if(preg_match('/cache\-bbcode/', $this->text)) $this->replace_cache();
    }

    /**
    * Функция возвращает готовый HTML код полученный с bb кодов  за исключением кэшируемых
    * 
    * @return string
    */
    function get_html(){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $this->text = nl2br($this->text);
        //$this->text = preg_replace_callback('/([^\]>]\x20*)[\r\n]+/', "new_nl2br", $this->text);
        $this->parse_smiles();
        $this->replace_cache();
        return $this->text;
    }

    /**
    * Возвращает bb код полученный с HTML
    * 
    * @return string
    */
    function get_bb(){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        return $this->text;
    }
    
    /**
    * Функция кэширования bb кодов
    * 
    * @param string $code
    * @param string $bb
    * @return string
    */
    function cache_code($code, $bb){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $this->cache_code[] = $code;
        return "{cache-bbcode-".(count($this->cache_code)-1)."-{$bb}}";
    }
    
    /**
    * Функция очистки кэша bb кодов
    * 
    * @param int $code_id
    * @param string $bb
    */
    function set_cache($code_id, $bb){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        return '['.$bb.']'.stripslashes(stripslashes($this->cache_code[$code_id])).'[/'.$bb.']';
    }
    
    /**
    * Функция парсинга смайлов
    * 
    * @return void
    */
    function parse_smiles(){
    global $smiles;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        rsort($smiles);
        foreach($smiles as $key => $value) {
            $smile_c = str_replace(array('(', ')', '{', '}', '[', ']', '/', '*', '!', ' '), array('\(', '\)', '\{', '\}', '\[', '\]', '\/', '\*', '\!', '\s'), magic_quotes($value[0]));
            $this->text = preg_replace('/(?!" )'.$smile_c.'(?! ")/m', "<!--start smile--><img src='{$value[1]}' alt=\" ".htmlspecialchars($value[0], ENT_QUOTES)." \" /><!--end smile-->", $this->text);
        }
    }
    
    /**
    * Функция преобразования bb кодов в HTML
    * 
    * @return void
    */
    function bb2html(){
         if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();

         $this->text = preg_replace_callback('%\[url=(.*?)\](.*?)\[/url\]%is', function($matches) { global $bb; return $bb->build_url(array('html' => $matches[1], 'show' => $matches[2], 'st' => ''));}, $this->text);
         $this->text = preg_replace_callback('%\[url\]([\w]+?://([\w\#$\%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]%is', function($matches) { global $bb; return $bb->build_url(array('html' => $matches[1], 'show' => $matches[1], 'st' => ''));}, $this->text);
         
         
         $array_bb = array(
             '%\[mail\s*=\s*([\.\w\-]+\@[\.\w\-]+\.[\w\-]+)\s*\](.*?)\[/mail\]%si',
             '%\[mail\](\S+?)\[/mail\]%si',
             '%\[img=([a-zA-Z]+) alt=(.+?)\]([^&\?\r\n ]*?\.(png|jpg|gif|jpeg|bmp))\[/img\]%si',
             '%\[img alt=(.+?)\]([^&\?\r\n ]*?\.(png|jpg|gif|jpeg|bmp))\[/img\]%si',
             '%\[img\]([^&\?\r\n ]*?\.(png|jpg|gif|jpeg|bmp))\[/img\]%si',
             '%\[backcolor=(\#[0-9A-F]{6}|[a-z]+)\](.*?)\[/backcolor\]%si',
             '%\[span=(.*?)\](.*?)\[/span\]%si',
             '%\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*?)\[/color\]%si',
             '%\[(left|right|center|justify)\](.*?)\[/\\1\]%si',
             '%\[family=([A-Za-z ]+)\](.*?)\[/family\]%si',
             '%\[size=([0-9]*)\](.*?)\[/size\]%si',
             '%\[blockquote\](.*?)\[/blockquote\]%si',
             '%\[sub\](.*?)\[/sub\]%si',
             '%\[sup\](.*?)\[/sup\]%si',
             '%\[li\](.*?)\[/li\]%si',
             '%\[b\](.+?)\[/b\]%si',
             '%\[i\](.+?)\[/i\]%si',
             '%\[u\](.+?)\[/u\]%si',
             '%\[s\](.+?)\[/s\]%si',
             '%\[h1\](.+?)\[/h1\]%si',
             '%\[h2\](.+?)\[/h2\]%si',
             '%\[h3\](.+?)\[/h3\]%si',
             '%\[h4\](.+?)\[/h4\]%si',
             '%\[h5\](.+?)\[/h5\]%si',
             '%\[h6\](.+?)\[/h6\]%si',
             '%\[hr\]%si',
             '%\(tm\)%si',
             '%\(c\)%si',
             '%\(r\)%si'
        );

         $array_html = array(
             "<a href='mailto:\\1'>\\2</a>",
             "<a href='mailto:\\1'>\\1</a>",
             "<img src='\\3' align='\\1' alt='\\2' title='\\2' />",
             "<img src='\\2' border='0' alt='\\1' title='\\1' />",
             "<img src='\\1' border='0' alt='\\1' title='\\1' />",
             "<!--start background--><span style='background: \\1'>\\2</span><!--end background-->",
             "<!--start span--><span class='\\1'>\\2</span><!--end span-->",
             "<!--start color--><span style='color: \\1'>\\2</span><!--end color-->",
             "<div align='\\1'>\\2</div>",
             "<!--start font-family--><span style='font-family: \\1'>\\2</span><!--end font-family-->",
             "<!--start font-size--><span style='font-size: \\1px;'>\\2</span><!--end font-size-->",
             "<blockquote>\\1</blockquote>",
             "<sub>\\1</sub>",
             "<sup>\\1</sup>",
             "<li>\\1</li>",
             "<b>\\1</b>",
             "<i>\\1</i>",
             "<u>\\1</u>",
             "<s>\\1</s>",
             "<h1>\\1</h1>",
             "<h2>\\1</h2>",
             "<h3>\\1</h3>",
             "<h4>\\1</h4>",
             "<h5>\\1</h5>",
             "<h6>\\1</h6>",
             "<hr />",
             "&#153;",
             "&copy;",
             "&reg;"
        );     
        
        
        $this->text = str_replace('$', "&#036;", $this->text);
        $this->text = preg_replace_callback('%\[nobb\](.+?)\[/nobb\]%is', function($matches) { global $bb; return $bb->nobb_tag($matches[1]);}, $this->text);
        $this->text = preg_replace_callback('%\[(miniature|attach)=(.+?)\]%is', function($matches) { global $bb; return $bb->attach_tags($matches[1], $matches[2]);}, $this->text);
        $this->text = preg_replace_callback('%(^|\s)((http|news|https|ftp|aim|ed2k|magnet)://\w+[^\s\[\]]+)%is', function($matches) { global $bb; return $bb->build_url(array('html' => $matches[2], 'show' => $matches[2], 'st' => $matches[1]));}, $this->text);
        
        $this->text = preg_replace($array_bb, $array_html, $this->text);
    }

    /**
    * Функция преобразования дополнительных bb кодов в  HTML
    * 
    * @return void
    */
    function other_bb(){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $this->text = preg_replace_callback('%\[(code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\](.+?)\[\/(code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\]%is', function($matches) { global $bb; return $bb->codes_tag($matches[1], $matches[2]);}, $this->text);
        $array_bb = array(
             '%\[flash=(.+?)\,(.+?)\](.+?)\[\/flash\]%is',
             '%\[mp3\](.+?)\[\/mp3\]%is',
             '%\[radio\](.+?)\[\/radio\]%is',
        );

        $array_html = array(
             "<div class='flashr'><object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='\\1' height='\\2'><param name='movie' value='\\3' /><param name='play' value='true' /><param name='loop' value='true' /><param name='quality' value='high' /><param name='allowscriptaccess' value='always' /><embed src='\\3' allowScriptAccess='always' width='\\1' height='\\2' play='true' loop='true' quality='high'></embed></object></div>",
             "<object type='application/x-shockwave-flash' data='includes/flash/mp3player.swf' width='200' height='20'><param name='wmode' value='transparent' /><param name='movie' value='includes/flash/mp3player.swf' /><param name='FlashVars' value='mp3=\\1&amp;showstop=1&amp;bgcolor1=ffffff&amp;bgcolor2=cccccc&amp;buttoncolor=999999&amp;buttonovercolor=0&amp;slidercolor1=cccccc&amp;slidercolor2=999999&amp;sliderovercolor=666666&amp;textcolor=0&amp;showvolume=1' /></object>",
             "<object id='mediaPlayer' classid='CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95' codebase='http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701' standby='Loading...' type='application/x-oleobject' width='250' height='80'><param name='FileName' value='\\1' /><param name='ShowControls' value='1' /><param name='ShowPositionControls' value='0' /><param name='ShowTracker' value='0' /><param name='ShowDisplay' value='0' /><param name='ShowStatusBar' value='1' /><param name='AutoSize' value='1' /><param name='AutoStart' value='True' /><param name='volume' value='50' /><embed type='application/x-mplayer2' pluginspage='http://www.microsoft.com/windows/mediaplayer/en/default.asp' filename='\\1' src='\\1' name='mediaPlayer' showcontrols='1' showpositioncontrols='0' showtracker='0' showdisplay='0' showstatusbar='1' aut ostart='0' volume='50' width='250' height='50'></object>",
        );
        $this->text = preg_replace_callback('%\[video\](.+?)\[\/video\]%is', function($matches) {global $bb; return $bb->create_player($matches[1]);}, $this->text);
        $this->text = preg_replace_callback('%\[youtube\](.+?)\[\/youtube\]%is', function($matches) {global $bb; return $bb->youtube($matches[1]);}, $this->text);
        $this->text = preg_replace_callback('%\[hide(=([0-9]*))*\](.+?)\[/hide\]%is', function($matches) {global $bb; return $bb->hide_tag($matches[2], $matches[3]);}, $this->text);
        $this->text = preg_replace_callback('%(\[cite(.+?)?\].*\[/cite\])%is', function($matches) {global $bb; return $bb->quote_tag($matches[1]);}, $this->text);
        $this->text = preg_replace_callback('%\[spoiler(=([^"\x27]*))*\](.+?)\[/spoiler\]%is', function($matches) {global $bb; return $bb->spoiler_tag($matches[2], $matches[3]);}, $this->text);
        
        $this->text = preg_replace('%\[\/(youtube|video|flash|mp3|radio|code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\]<br />%is', "[/\\1]", $this->text);
        $this->text = preg_replace($array_bb, $array_html, $this->text);
    }
    
    function youtube($link){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $parsed = parse_url($link);
        if(isset($parsed['query'])){
            $par = "";
            parse_str($parsed['query'], $par);
            if(isset($par['v'])) $link = $par['v'];
            else return 'Bad link youtube';
        } else return 'Bad link youtube';
        return "<div class='flashr'><object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='640' height='390'><param name='movie' value='http://www.youtube.com/v/{$link}' /><param name='play' value='true' /><param name='loop' value='true' /><param name='quality' value='high' /><param name='allowscriptaccess' value='always' /><param name='allowfullscreen' value='true' /><embed src='http://www.youtube.com/v/{$link}' allowScriptAccess='always' width='640' height='390' play='true' loop='true' quality='high'></embed></object></div>";
    }

    /**
    * Функция создания видео проигрывателя
    * 
    * @param string $url
    * @return string
    */
    function create_player($url){
    global $player_id, $tpl_create;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $url = trim($url);
        if(empty($player_id)){
            $player_id = 1;
            main::add2script('includes/javascript/jw/swfobject.js');
        } else $player_id++;
        main::add2script("swfobject.registerObject('player_{$player_id}','9.0.0');", false);
        //param name='wmode' value='opaque' />
        $params = array(
            'movie'             => "includes/javascript/jw/flvplayer.swf",
            'allowfullscreen'   => "true",
            'allowscriptaccess' => "always",
            'flashvars'         => "file={$url}",
            'width'             => 540,
            'height'            => 405,
            'image'             => "includes/javascript/jw/preview.png",
            'skin'              => "includes/javascript/jw/skins/modieus/modieus.swf",
            'repeat'            => "list",
            'shuffle'           => "true"
        );
        
        $param = "";
        foreach($params as $key => $value) $params['flashvars'] = ($key!='flashvars') ? "{$key}={$value}&amp;".$params['flashvars'] : $params['flashvars'];
        foreach($params as $key => $value) $param .= "<param name='{$key}' value='{$value}' />\n";
        return "<div class='flashr'>\n<object id='player_{$player_id}' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' name='player_{$player_id}' width='{$params['width']}' height='{$params['height']}'>\n{$param}".
               "<object type='application/x-shockwave-flash' data='{$params['movie']}' width='{$params['width']}' height='{$params['height']}'>\n{$param}".
               "<p><a href='http://get.adobe.com/flashplayer/'>Get Flash</a> to see this player.</p>".
               "</object>\n</object>\n</div>";
    }

    /**
    * Функция преобразования дополнительных тэгов таких как php, code, html, css…
    * 
    * @param string $type
    * @param string $code
    * @return string
    */
    function codes_tag($type, $code){
    global $tpl_create;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(empty($type) OR empty($code)) return "";
        $code = str_replace('\\\\"', '"', $code);
        $code = preg_replace("/\\\\/", '\\\\\\', $code);
        $code = str_replace('\\\\', '&#92;', $code);
        $code = htmlspecialchars($code, ENT_QUOTES);
        $code = str_replace(']', '&#93;', $code);
        $code = str_replace('&amp;#92;', '&#92;', $code);
        return "<a href='#' class='codeshow' onclick='return show_code(this);'>Показать код <b>[".mb_strtoupper($type)."]</b></a><br /><pre style='overflow: visible; width: 200px; display:none;'  class='syntaxNotReady brush: ".mb_strtolower($type)."; ruler: true;'>{$code}</pre>";
    }
    
    function restore_set_cache($code_id, $bb){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $text= str_replace('\\\\"', '"', $this->cache_code[$code_id]);
        return '['.$bb.']'.$text.'[/'.$bb.']';
    }
    /**
    * Специальный stripcslashes для того что бы незатрагивались bb коды в сохраненном html
    * 
    */
    function bb_stripcslashes(){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       $this->text = preg_replace_callback('%\[(youtube|video|flash|mp3|radio|code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\](.+?)\[\/(youtube|video|flash|mp3|radio|code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\]%is', function($matches) { global $bb; return $bb->cache_code($matches[2], $matches[1]);}, $this->text);
       $this->text = stripcslashes($this->text);
       $this->text = preg_replace_callback('%\{cache-bbcode-([0-9]+)-([a-zA-z0-9]+)\}%', function($matches) {global $bb; return $bb->restore_set_cache($matches[1], $matches[2]);}, $this->text);
    }
    /**
    * Функция преобразования HTML в bb коды
    * 
    * @return void
    */
    function html2bb(){
        /*[X]*/
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        //<img src='\\3' align='\\1' alt='\\2' title='\\2' />
        $this->bb_stripcslashes();
        
        $this->text = preg_replace_callback('%<!--start smile--><img src=\'(.*?)\' alt=\"(.*?)\" /><!--end smile-->%is', function($matches) {global $bb; return preg_replace($bb->arr_html, $bb->arr_replace, $matches[2]);}, $this->text);
        $this->text = preg_replace_callback('%<a href=\'engine.php\?do=redirect&amp;url=(.*?)\' target=\'_blank\' title=\'(.*?)\'>(.*?)</a>%is', function($matches) {global $bb; return $bb->url2bb($matches[1], $matches[3]);}, $this->text);
        
        $array_html = array(
            '%&reg;%is',
            '%&copy;%is',
            '%&\#153;%is',
            '%<hr />%is',
            '%<h6>(.*?)</h6>%is',
            '%<h5>(.*?)</h5>%is',
            '%<h4>(.*?)</h4>%is',
            '%<h3>(.*?)</h3>%is',
            '%<h2>(.*?)</h2>%is',
            '%<h1>(.*?)</h1>%is',
            '%<s>(.*?)</s>%is',
            '%<u>(.*?)</u>%is',
            '%<i>(.*?)</i>%is',
            '%<b>(.*?)</b>%is',
            '%<li>(.*?)</li>%is',
            '%<sup>(.*?)</sup>%is',
            '%<sub>(.*?)</sub>%is',
            '%<div align=\'(.*?)\'>(.*?)</div>%is',
            '%<blockquote>(.*?)</blockquote>%is',
            '%<a href=\'mailto:(.*?)\'>(.*?)</a>%is',
            '%<img src=\'(.*?)\' align=\'(.*?)\' alt=\'(.*?)\' title=\'(.*?)\' />%is',
            '%<img src=\'(.*?)\' border=\'0\' alt=\'(.*?)\' title=\'(.*?)\' />%is',
            '%<!--start color--><span style=\'color:\s(.*?)\'>(.*?)</span><!--end color-->%is',
            '%<!--start background--><span style=\'background:\s(.*?)\'>(.*?)</span><!--end background-->%is',
            '%<!--start span--><span class=\'(.*?)\'>(.*?)</span><!--end span-->%is',
            '%<!--start font-family--><span style=\'font-family:\s(.*?)\'>(.*?)</span><!--end font-family-->%is',
            '%<!--start font-size--><span style=\'font-size:\s(.*?)px;\'>(.*?)</span><!--end font-size-->%is'
        );

        $array_bb = array(
            "(r)",
            "(c)",
            "(tm)",
            "[hr]",
            "[h6]\\1[/h6]",
            "[h5]\\1[/h5]",
            "[h4]\\1[/h4]",
            "[h3]\\1[/h3]",
            "[h2]\\1[/h2]",
            "[h1]\\1[/h1]",
            "[s]\\1[/s]",
            "[u]\\1[/u]",
            "[i]\\1[/i]",
            "[b]\\1[/b]",
            "[li]\\1[/li]",
            "[sup]\\1[/sup]",
            "[sub]\\1[/sub]",
            "[\\1]\\2[/\\1]",
            "[blockquote]\\1[/blockquote]",
            "[mail=\\1]\\2[/mail]",
            "[img=\\2 alt=\\3]\\1[/img]",
            "[img alt=\\2]\\1[/img]",
            "[color=\\1]\\2[/color]",
            "[backcolor=\\1]\\2[/backcolor]",
            "[span=\\1]\\2[/span]",
            "[family=\\1]\\2[/family]",
            "[size=\\1]\\2[/size]"
        );
        
        $this->text = str_replace("&#036;", "$", $this->text);
        $this->text = str_replace("<br />\r", "\r", $this->text);
        $this->text = str_replace("<br />", "\r", $this->text);
        $this->text = preg_replace_callback('%<\!\-\-start\snobb\-\->(.+?)<\!\-\-end nobb\-\->%is', function($matches) {global $bb; return $bb->nobb_tag($matches[1], 'decode');}, $this->text);
        $this->text = preg_replace_callback('%<\!\-\-start\sminiature\-\->(.+?)<\!\-\-end miniature\-\->%is', function($matches) {global $bb; return $bb->attach_tags('', $matches[1], 'decode', 'miniature');}, $this->text);
        $this->text = preg_replace_callback('%<\!\-\-start\sattach\-\->(.+?)<\!\-\-end attach\-\->%is', function($matches) {global $bb; return $bb->attach_tags('', $matches[1], 'decode', 'attach');}, $this->text);
        $this->text = preg_replace($array_html, $array_bb, $this->text);
        $this->text = str_replace("<br />\r", "", $this->text);
        $this->text = preg_replace('/[\r\n]+$/i', '', $this->text);
    }

    /**
    * Функция преобразования прикрепленных файлов
    * 
    * @param string $tag
    * @param string $attach
    * @param string $code
    * @param string $decodetype
    * @return string
    */
    function attach_tags($tag, $attach, $code='encode', $decodetype='', $alt=''){
    global $thumb;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $thumb = (!isset($thumb) OR empty($thumb)) ? 0 : $thumb;
        $match = "";
        if($code=='encode'){
            if($tag=='attach'){
                if(preg_match('/title=/i', $attach)){
                    $_e = explode(' title=', $attach);
                    $attach = $_e[0];
                    $title = $_e[1];
                } else  $title = basename($attach);
                return "<!--start attach--><a href='engine.php?do=attach&amp;file={$attach}' title='{$title}'>{$title}</a><!--end attach-->";
            } else {
                preg_match('/(.+?)\salign=(.+?)$/is', $attach, $match);
                $title = basename($match[1]);
                if(preg_match('/^(.*?) alt=(.*?)$/m', $attach)){
                    $alt = preg_replace('/^(.*?) alt=(.*?)$/m', '\\2', $attach);
                    $match[2] = str_replace(' alt='.$alt, '', $match[2]);
                } else $alt = $title;
                
                $thumb++;
                return "<!--start miniature--><a id='thumb{$thumb}' href='".str_replace("mini-", '', $match[1])."' onclick=\"return miniature_click(this);\"><img class='miniature' src='{$match[1]}' alt='{$alt}' align='{$match[2]}' /></a><!--end miniature-->";
            }
        } else {
            if($decodetype=='miniature') {
                preg_match('/(.+?)img class=\'miniature\' src=\'(.+?)\' alt=\'(.+?)\' align=\'(.*?)\'(.*)/mis', $attach, $match);
                $title = basename($match[2]);
                if($title==$match[3]) return "[miniature={$match[2]} align={$match[4]}]";
                else return "[miniature={$match[2]} align={$match[4]} alt={$match[3]}]";
            } else {
                $_t = preg_replace('/(.+?)title=\'(.+?)\'(.*)$/is', '\\2', $attach);
                $_u = preg_replace('/(.+?)&amp;file=(.+?)\'\stitle(.+?)$/is', '\\2', $attach);
                if($_t!=basename($_u)) return "[attach={$_u} title={$_t}]";
                else return "[attach={$_u}]";
            }
        }
    }
    
    /**
    * Функция обработки кода nobb
    * 
    * @param string $text
    * @param string $code
    * @return string
    */
    function nobb_tag($text, $code='encode'){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if($code=="encode") return "<!--start nobb-->".preg_replace($this->array_replace, $this->array_html, $text)."<!--end nobb-->";
        else return "[nobb]".preg_replace($this->arr_html, $this->arr_replace, $text)."[/nobb]";
    }

    /**
    * Функция обработки кода hide
    * 
    * @param string $text
    * @return string
    */
    function hide_tag($num,$text){
    global $lang;
       if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
       if(is_support() OR is_user()){
          $num=empty($num)?0:intval($num);
          $user_post=(isset($_SESSION['cache_session_user']) AND isset($_SESSION['cache_session_user']['user_posts']))?intval($_SESSION['cache_session_user']['user_posts']):0;
          if($user_post>=$num) return $text;
          else return "<div class='hide_conteiner'><div>".str_replace("{COUNT}",$num-$user_post,$lang['little_point'])."</div></div>";
       } else return "<div class='hide_conteiner'><div>{$lang['hide']}</div></div>";
    }

    /**
    * Функция обработки кода cite
    * 
    * @param string $text
    * @return string
    */
    function quote_tag($text=""){
    global $html;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if ($text == "") return "";
        $text = str_replace(chr(173).']', '&#93;', $text);
        $html = $this->wrap_style('quote');
        $text = preg_replace('%\[cite\]%i', "<!--quote_start-->".$html['start'], $text);
        $text = preg_replace_callback('%\[cite=([^\],]+?),([^\]]+?)\]%i', function($matches) {global $bb; return $bb->quote_user_tag($matches[1], $matches[2]);}, $text);
        $text = preg_replace_callback('%\[cite=([^\]]+?)\]%i', function($matches) {global $bb; return $bb->quote_user_tag($matches[1], '');}, $text);
        $text = preg_replace('%\[/cite\]%i', $html['end']."<!--quote_end-->", $text);
        //$text = str_replace("\n", "<br />", $text);
        return stripslashes($text);
    }

    /**
    * Разрешение для функции quote_tag если в теге встречается user
    * 
    * @param string $name
    * @param string $date
    * @return string
    */
    function quote_user_tag($name="", $date=""){
    global $html;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $date = preg_replace("#:#", "&#58;", $date);
        if ($date != "") $default = '\[cite='.$name.','.$date.'\]';
        else $default = '\[cite='.$name.'\]';
        if (strstr($name, '<!--c1-->') or strstr($date, '<!--c1-->')) return $default;
        if ($date == "") $html = $this->wrap_style('quote', " ({$name})");
        else $html = $this->wrap_style('quote', " ({$name} &#064; {$date})");
        return "<!--quote_start-{$name}+{$date}-->{$html['start']}";
    }
    
    /**
    * Функция обработки кода spoiler
    * 
    * @param string $text
    * @return string
    */
    function spoiler_tag($title,$text){
    global $lang;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        if(empty($title)) $title=$lang['hide_content'];
        return "<div class='spl_src'><div class='spl_head' onclick='switch_spoiler(this);'><span>{$title}</span></div><div class='spl_text'>{$text}</div></div>";
    }

    /**
    * Функция создания стиля для тега cite
    * 
    * @param string $type
    * @param string $extra
    * @return string
    */
    function wrap_style( $type='quote', $extra="" ){
    global $lang;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        $used = array(
            'quote'      => array('title' => $lang['quote']    , 'css_top' => 'quotetop'     , 'css_main' => 'quotemain')
        );
        return array( 'start' => "<div class='{$used[$type]['css_top']}'>{$used[ $type ]['title']}{$extra}</div><div class='{$used[$type]['css_main']}'>", 'end' => "</div>");
    }
    
    /**
    * Функция преобразования ссылок в BB коды
    * 
    * $url
    */
    function url2bb($url, $title){
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
        return "[url=".urldecode($url)."]{$title}[/url]";
    }

    /**
    * Функция парсинга ссылок
    * 
    * @param string $url
    * @param int $skip_it
    * @return string
    */
    function build_url($url=array(), $skip_it=0){
    global $lang;
        if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
    	$match = "";
        if(preg_match('/^(mailto|mail):(.*)$/i', $url['html'])) return "<a href='{$url['html']}'>{$url['show']}</a>";
        if(preg_match('/([.,?]|&#33;)$/', $url['html'], $match)) $url = array('end' => $match[1], 'html'   => preg_replace('/([.,?]|&#33;)$/', "", $url['html']), 'show'   => preg_replace('/([.,?]|&#33;)$/', "", $url['show']));
        if(!isset($url['end'])) $url['end'] = "";
        if (preg_match('%\[\/(html|cite|code|sql|javascript|css)%i', $url['html'])) return $url['html'];
        $url['html'] = preg_replace(array('%&amp;%', '%\[%', '%\]%', '%javascript:%i'), array("&", "%5b", "%5d", "javascript&#58; "), $url['html']);
        if (!preg_match('%^(http|news|https|ftp|aim|ed2k|magnet)://%', $url['html'])) $url['html'] = 'http://'.$url['html'];
        $url['show'] = preg_replace(array("/&amp;/", "/javascript:/i") , array("&", "javascript&#58;"), $url['show']);
        if(!preg_match('%^(http|news|https|ftp|aim|ed2k|magnet):\/\/%i', $url['show']) OR mb_strlen($url['show'])-58<3 OR preg_match( "/^<img src/i", $url['show'])) $skip_it = 1;
        if ($skip_it!=1){
            $stripped = preg_replace('%^(http|news|https|ftp|aim|ed2k|magnet)://(\S+)$%i', "\\2", $url['show']);
            $uri_type = preg_replace('%^(http|news|https|ftp|aim|ed2k|magnet)://(\S+)$%i', "\\1", $url['show']);
            $url['show'] = "{$uri_type}://".mb_substr($stripped, 0, 35)."...".mb_substr($stripped, -15);
        }
        $url['st'] = (isset($url['st'])) ? $url['st'] : "";
        $site = parse_url($url['html']);
        preg_replace('/www\.(.*?)/', '\\1', $site['host']);
        return $url['st']."<a href='engine.php?do=redirect&amp;url=".urlencode($url['html'])."' target='_blank' title='{$lang['new_open_window']}'>{$url['show']}</a>{$url['end']}";
    }
}

//Создание объекта класса
$bb = new bbcode;
global $main;
if(!is_ajax()) {
    main::add2script("includes/javascript/jquery/colorbox/jquery.colorbox-min.js");
    main::add2link("includes/javascript/jquery/colorbox/tpl/5/colorbox.css");
    main::add_js_function('miniature');
}
?>
