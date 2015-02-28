<?php
    /**
    * Класс фильтрации данных
    * 
    * @author Igor Ognichenko
    * @copyright Copyright (c)2007-2010 by Kasseler CMS
    * @link http://www.kasseler-cms.net/
    * @filesource includes/classes/filter.class.php
    * @version 2.0
    */
    if (!defined("FUNC_FILE")) die("Access is limited");
    global $lawec_cfg;
    $lawec_cfg = array(
        'anti_link_spam'=> 0,
        'anti_mail_spam'=> 0,
        'balance'=> 1,
        'clean_ms_char'=> 0,
        'deny_attribute'=> 1,
        'elements'=> '',
        'hexdec_entity'=> 1,
        'hook'=>'',
        'hook_tag'=>'',
        'keep_bad'=> 6,
        'lc_std_val'=> 1,
        'named_entity'=> 1,
        'no_deprecated_attr'=> 1,
        'parent'=> '',
        'safe'=> 0,
        'schemes'=> 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https',
        'tidy'=> 0,
        'unique_ids'=> 0,
    );
    class filter{
        /**
        * Массив кэшируемых bb кодов
        * 
        * @var array
        */
        var $cache_code = array();
        var $tags;
        var $attr;
        var $tagsm;
        var $attrm;
        var $xss;
        var $bad_tags = array('applet', 'body', 'bgsound', 'base', 'basefont', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'script', 'style', 'title', 'xml', 'form');
        var $bad_attr = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');
        var $regexp_bb = '/\[(code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\](.+?)\[\/(code|php|html|css|xml|javascript|java|cpp|delphi|python|ruby|sql)\]/is';

        function init($tags=array(), $attr=array(), $tagsm=false, $attrm=false, $xss=true) {
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            foreach($tags as $k => $v) $tags[$k] = strtolower($v);
            foreach($attr as $k => $v) $attr[$k] = strtolower($v);
            $this->tags = $tags;
            $this->attr = $attr;
            $this->xss = $xss;
            $this->tagsm = $tagsm;
            $this->attrm = $attrm;
        }

        function process($source) {
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            if(is_array($source)) foreach($source as $k => $v) {
                if(is_string($v)) {
                    //Кэшируем bb коды
                    $source = preg_replace_callback($this->regexp_bb, function($matches) {return $this->cache_code($matches[2], $matches[1]);}, $source);
                    $source[$k] = $this->remove($this->decode($v));
                    //Возвращаем bb коды из кэша
                    $source = preg_replace_callback('/\{cache-bbcode-([0-9]+)-([a-zA-z]+)\}/', function($matches) {return $this->set_cache($matches[1], $matches[2]);}, $source);
                }
            } else if(is_string($source)) {
                //Кэшируем bb коды
                $_this = &$this;
                $source = preg_replace_callback($this->regexp_bb, function($matches) use($_this) {return $_this->cache_code($matches[2], $matches[1]);}, $source);
                $source = $this->remove($this->decode($source));
                //Возвращаем bb коды из кэша
                $source = preg_replace_callback('/\{cache-bbcode-([0-9]+)-([a-zA-z]+)\}/', function($matches) use($_this) {return $_this->set_cache($matches[1], $matches[2]);}, $source);
            }
            return $source;
        }

        function remove($source) {
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            $source = stripcslashes($source);
            $source = addslashes($this->filterTags($source));
            return $source;
        }

        function filterTags($source) {
            global $main, $lawec_cfg, $config;
            main::init_function('htmLawed');
            $cfg = $lawec_cfg;
            $cfg['elements']=$config['htmlTags'];
            $cfg['deny_attribute']="on*";
            return htmLawed($source, $cfg);
        }

        function filterAttr($attr_set) { // неиспользуется
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            $new_set = array();
            for ($i = 0; $i <count($attr_set); $i++) {
                if(!$attr_set[$i]) continue;
                $attr_sub_set = explode('=', trim($attr_set[$i]));
                list($attr_sub_set[0]) = explode(' ', $attr_sub_set[0]);
                if((!preg_match("/^[a-z]*$/", $attr_sub_set[0])) OR (($this->xss) AND ((in_array(strtolower($attr_sub_set[0]), $this->bad_attr)) OR (substr($attr_sub_set[0], 0, 2) == 'on')))) continue;
                if($attr_sub_set[1]) {
                    $attr_sub_set[1] = str_replace('"', '', preg_replace('/\s+/', '', str_replace('&#', '', $attr_sub_set[1])));
                    if((substr($attr_sub_set[1], 0, 1) == "'") AND (substr($attr_sub_set[1], (strlen($attr_sub_set[1]) - 1), 1) == "'")) $attr_sub_set[1] = substr($attr_sub_set[1], 1, (strlen($attr_sub_set[1]) - 2));
                    $attr_sub_set[1] = stripslashes($attr_sub_set[1]);
                }
                if(preg_match('/javascript:|behaviour:|vbscript:|mocha:|livescript:/is', $attr_sub_set[1])) continue;
                $attr_found = in_array(strtolower($attr_sub_set[0]), $this->attr);
                if((!$attr_found AND $this->attrm) OR ($attr_found AND !$this->attrm)) {
                    if($attr_sub_set[1]) $new_set[] = $attr_sub_set[0] . '="' . $attr_sub_set[1] . '"';
                    else if($attr_sub_set[1] == "0") $new_set[] = $attr_sub_set[0] . '="0"';
                        else $new_set[] = $attr_sub_set[0] . '="' . $attr_sub_set[0] . '"';
                }
            }
            return $new_set;
        }

        function decode($source) {
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            $source = html_entity_decode($source, ENT_QUOTES, 'UTF-8');
            $source = preg_replace_callback('/&#(\d+);/m', function($matches) {return chr($matches[1]);}, $source);
            return preg_replace_callback('/&#x([a-f0-9]+);/mi', function($matches) {return chr('0x'.$matches[1]);}, $source);
        }

        /**
        * Функция кэширования bb кодов
        * 
        * @param string $code
        * @param string $bb
        */
        function cache_code($code, $bb){       
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            $this->cache_code[] = addslashes($code);
            return "{cache-bbcode-".(count($this->cache_code)-1)."-{$bb}}";
        }

        /**
        * Функция возвращает bb коды из кэша
        * 
        * @param string $code_id
        * @param string $bb
        */
        function set_cache($code_id, $bb){
            if(function_exists('hook_check') AND hook_check(__METHOD__)) return hook();
            return '['.$bb.']'.$this->cache_code[$code_id].'[/'.$bb.']';
        }
    }

    //Создание объекта класса
    global $filter;
    $filter = new filter;
?>