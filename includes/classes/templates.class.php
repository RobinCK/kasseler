<?php
/**
* Класс шаблонизатора системы
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/templates.class.php 
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

$generate_template = 0;
class template{
    private $keys = array();
    private $vars = array();
    public $template = array();
    public $cache = array();
    public $path = '';
    public $tpl = '';
    
    
    public function __construct() {
    global $main;
        $this->tpl = $main->tpl;
        $this->path = TEMPLATE_PATH.$main->tpl.'/';
    }
    
    /**
    * Функция обработки тегов показа
    * 
    * @return void
    */
    private function display($index){
        if(is_admin()) {
            $this->template[$index] = preg_replace("#<(guest|user|moder)>(.+?)</(guest|user|moder)>#is", "", $this->template[$index]);
            $this->template[$index] = preg_replace("#<admin>(.+?)</admin>#is", "\\1", $this->template[$index]);
        } elseif (is_moder()) {
            $this->template[$index] = preg_replace("#<(guest|user|admin)>(.+?)</(guest|user|admin)>#is", "", $this->template[$index]);
            $this->template[$index] = preg_replace("#<moder>(.+?)</moder>#is", "\\1", $this->template[$index]);
        } elseif (is_user()) {
            $this->template[$index] = preg_replace("#<(guest|admin|moder)>(.+?)</(guest|admin|moder)>#is", "", $this->template[$index]);
            $this->template[$index] = preg_replace("#<user>(.+?)</user>#is", "\\1", $this->template[$index]);
        } else {
            $this->template[$index] = preg_replace("#<(user|admin|moder)>(.+?)</(user|admin|moder)>#is", "", $this->template[$index]);
            $this->template[$index] = preg_replace("#<guest>(.+?)</guest>#is", "\\1", $this->template[$index]);
        }
    }
    
    public function eval_php($mask_start, $mast_end, $index){
        extract($GLOBALS, EXTR_SKIP);                                                      //Распаковка глобальных переменных
        ob_start();                                                                        //Начало буферизации вывода
        $err = eval(" {$mast_end}{$this->template[$index]}{$mask_start} ");                //Попытка обработки вставок PHP
        if($err!==null) trigger_error("SYSTEM ERROR: php parsing error index '{$index}'", E_USER_WARNING);   //В случаи неудачи выводим ошибку
        $this->template[$index] = ob_get_contents(); ob_end_clean();                       //Конец буферизации
    }
    
    /**
    * Функция получения подшаблона
    * 
    * @param array $array
    */
    public function get_subtpl($array, $key_se = array('start' => '{%', 'end' => '%}')){
        foreach($array as $v){            
            $reg = '/<\!\-\-begin'.$v['selector'].'\-\->(.*?)<\!\-\-end'.$v['selector'].'\-\->/is';
            if(preg_match($reg, $this->template[$v['get_index']], $m)){
                if(!empty($v['new_index'])) $this->template[$v['new_index']] = $m[1];
                $this->template[$v['get_index']] = preg_replace($reg, !empty($v['new_index'])?strtoupper("{$key_se['start']}{$v['new_index']}{$key_se['end']}"):'', $this->template[$v['get_index']]);
                $this->cache[$v['get_index']] = $this->template[$v['get_index']];
                if(!empty($v['new_index'])) $this->cache[$v['new_index']] = $this->template[$v['new_index']];
            }
        }
    }

    /**
    * Функция загрузки и обработки шаблона
    * 
    * @param string $tpl_name
    * @return void
    */
    public function get_tpl($tpl_name, $index='index', $path=''){
    global $main;
        if(!isset($this->cache[$index])){                                                          //Проверяем наличие шаблона в кэше 
            $path = !empty($path) ? $path : $this->path;
            if(isset($_GET['id']) AND file_exists($path."{$tpl_name}-{$main->module}-{$_GET['id']}.tpl")) $file = "{$tpl_name}-{$main->module}-{$_GET['id']}";
            elseif(isset($_GET['module']) AND file_exists($path."/{$main->module}/{$tpl_name}.tpl")) {$path =$path."/{$main->module}/";  $file = "{$tpl_name}";}
            elseif(isset($_GET['module']) AND file_exists("modules/{$main->module}/template/") AND file_exists("modules/{$main->module}/template/{$tpl_name}.tpl")) {$path = "modules/{$main->module}/template/"; $file = "{$tpl_name}";}
            elseif(file_exists($path."{$tpl_name}-{$main->module}.tpl") AND isset($_GET['module'])) $file = "{$tpl_name}-{$main->module}";
            elseif(file_exists($path."{$tpl_name}-home.tpl") AND !isset($_GET['module'])) $file = "{$tpl_name}-home";
            else $file = $tpl_name;
            if(file_exists($path.$file.".tpl")) {
                $this->set_tpl(array('load_tpl'  => $main->tpl), $index);
                $this->template[$index] = file_get_contents($path.$file.".tpl");           //Проверяем наличие файла в файловой системе, если файл найден, загружаем его
            } else trigger_error("SYSTEM ERROR: template file '{$path}{$file}.tpl' not exists.", E_USER_WARNING);                  //В случаи отсутствия файла выводим сообщение об ошибке
            $this->eval_php('<?php', '?'.'>', $index);
            $this->display($index);
            $this->template[$index] = str_replace(array('[?php', '?]'), array('<?php', '?'.'>'), $this->template[$index]);
            $this->cache[$index] = $this->template[$index];                                        //Сохраняем содержимое в кэш
        } else $this->template[$index] = $this->cache[$index];                                     //Возвращаем содержание шаблона
        
        
        /////////
    }

    /**
    * Функция установки переменных шаблона
    * 
    * @param string $key
    * @param string $var
    * @return void
    */
    public function set_tpl($keys, $index='index', $key_se = array('start' => '$', 'end' => '')){
        foreach($keys as $key=>$value){
            $this->keys[$index][] = $key_se['start'].$key.$key_se['end'];                          //Сохраняем ключи
            $this->vars[$index][] = str_replace(array('<?','?>'),array('< ?','? >'),$value);       //Сохраняем значения
        }
    }

    /**
    * Функция создания шаблона
    * 
    * @param bool $return
    * @return string
    */
    public function tpl_create($return=false, $index='index'){
    global $generate_template, $main;
        $generate = new timer;
        if(!empty($main->tpl_tag)) $this->template[$index] = str_ireplace(array_keys($main->tpl_tag), array_values($main->tpl_tag), $this->template[$index]);
        $this->main_viriable_set($index);
        $this->template[$index] = isset($this->keys[$index]) ? str_ireplace($this->keys[$index], $this->vars[$index], $this->template[$index]) : $this->template[$index];
        $this->eval_php('<?php', '?>', $index);
        $content = $this->template[$index];
        unset($this->keys[$index], $this->vars[$index], $this->template[$index]);
        if(isset($this->cache[$index])) $this->template[$index]=$this->cache[$index];
        $generate_template += $generate->stop();
        if(!$return) echo $content;
        else return $content;
        return true;
    }
    
    /**
    * Функция заполнения в шаблоне переменной $main
    * 
    * @param mixed $index
    */
    function main_viriable_set($index='index'){
        $_this = &$this;
        $this->template[$index] = preg_replace_callback('/(\{\$main->([a-zA-Z_]*)([[\'"]*([a-zA-Z_]*)[\'"\]]*)\})/i', function($matches) use($_this) { return $_this->main_var_value($matches[2], $matches[4]);} , $_this->template[$index]);
    }
    
    /**
    * Возвращает значение из переменной $main
    * 
    * @param string $variable - свойство переменной $main
    * @param string $index_var - для индексных свойств переменной $main
    * @return mixed
    */
    function main_var_value($variable, $index_var){
       global $main;
       if(isset($main->$variable)){
          $v=$main->$variable;
          return empty($index_var)?$v:(isset($v[$index_var])?$v[$index_var]:"");
       } return "";
    }
    /**
    * удалить переменную из шаблона
    * 
    * @param mixed $vars
    * @param mixed $index
    */
    public function remove_variable($vars, $index){
       $repl=array();
       if(is_array($vars)){
          foreach ($vars as $key => $value) $repl[$value]= '';
       } else $repl[$vars] = '';
       $this->cache[$index] = str_ireplace(array_keys($repl), array_values($repl), $this->cache[$index]);
       $this->template[$index] = $this->cache[$index]; 
    }
    /**
    * удалить блок из шаблона
    * 
    * @param mixed $block
    * @param mixed $index
    */
    public function remove_block($block, $index){
    global $main;
       $reg='/<\!--'.$block.'-->(.+?)<\!--'.$block.'-->/si';
       $this->cache[$index] = preg_replace($reg, '', $this->cache[$index]);
       $this->template[$index] = $this->cache[$index];
    }
}

/**
* Функция-заглушка предназначена для изменения значений перед преминением в шаблонах
* 
* @param array $keys массив $key->$value
* @param string $namespace строковый идентификатор для возможного hook
*/
function hook_set_tpl($keys, $namespace = ''){
   if(hook_check(__FUNCTION__)) return hook();
   return $keys;
}
?>