<?php
/**
* Файл системный функций
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/function/sources.php
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция поиска ключа массива максимальной длины
* 
* @param array $array
* @param int $max
* @return int
*/
function max_length_value($array, $max=0){
    if(hook_check(__FUNCTION__)) return hook();
    foreach ($array as $kay=>$value) $max = ($max<mb_strlen($kay)) ? mb_strlen($kay) : $max;
    return $max;
}

/**
* Функция сохранения файлов конфигурации
* 
* @param string $file_config
* @param string $var
* @param array $config
* @return void
*/
function save_config($file_config, $var, $config, $duble=false){
global $copyright_file;
    if(hook_check(__FUNCTION__)) return hook();
    $string = "{$copyright_file}global {$var};\n{$var} = array(\n";
    $max_legth_var = max_length_value($config)+4;
    $i = 1;
    $count = count($config);
    foreach ($config as $kay=>$value){
        $space = create_space($max_legth_var-mb_strlen($kay));
        $keyval=((isset($_POST[$kay]) OR $value==ENABLED) ? (isset($_POST[$kay]) ? $_POST[$kay] : ((isset($_POST['hide_'.$kay]) AND !isset($_POST[$kay])) ? '' : $value)) : $value);
        if(is_array($keyval)) $keyval=implode(',',$keyval);
        $keyval=magic_quotes($keyval);
        $string .= "    '{$kay}'{$space} => ".($duble==true?'"':"'")."".$keyval."".($duble==true?'"':"'")."";
        $string .= ($i<$count) ? ",\n" : "\n";
        $i++;
    }
    $string .= ");\n?".">";
    $drs = explode('/', $file_config);
    $file_link = (count($drs)==1) ? "includes/config/{$file_config}" : $file_config;
    if(file_exists($file_link)){
       if(is_writable($file_link)){
           $file = fopen($file_link, "w");
           fputs ($file, $string);
           fclose ($file);
       }
    } else {
           $file = fopen($file_link, "w");
           fputs ($file, $string);
           fclose ($file);
    }
}

/**
* Функция обновляет конфигурацию RSS каналов
* 
* @return void
*/
function update_rss_config(){
global $rss, $main;
    if(hook_check(__FUNCTION__)) return hook();
    $config = $_POST;
    $rss[$main->module] = $config['rss_title'].'|'.(isset($config['rss'])?$config['rss']:"");
    save_config("includes/config/config_rss.php", '$rss', $rss);
}

/**
* Функция создание заданного количества пробелов
*  
* @param int $int
* @param string $space
* @return string
*/
function create_space($int, $space="", $char=' '){
    if(hook_check(__FUNCTION__)) return hook();
    for($i=1;$i<=$int;$i++) $space .= $char;
    return $space;
}
?>