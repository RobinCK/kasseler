<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2013 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   * @tutorial функции для работы с конфигами
   */
   if (!defined('FUNC_FILE')) die('Access is limited');
   /**
   * заполнение массива $var значениями из $_POST
   * 
   * @param mixed $var
   */
   function get_vars_by_post($var){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      foreach ($var as $key => $value) {
         if(isset($_POST[$key])) $var[$key]=$_POST[$key];
         elseif($var[$key]=='on') $var[$key]='off';
      }
      return $var;
   }
   /**
   * Преобразовует для выбранных значений $_POST из массива в строку
   * 
   * @param mixed $posts
   */
   function set_post_array_to_string($posts){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $p = is_array($posts)?$posts:array($posts);
      foreach ($p as $key => $value) {
         $_POST[$value]=isset($_POST[$value])?implode(',',$_POST[$value]):"";
      }
   }
   /**
   * Сохранение полностью переменной $config в конфигурационный файл
   * 
   * @param string $file_config - название файла конфига
   * @param mixed $var - название переменной
   * @param string $config - переменная
   * @param bool $post_value - изменять ли $var значениями из $_POST
   */
   function save_config_direct($file_config, $var, &$config, $post_value=false, $inline = false){
      global $main, $copyright_file;
      if(hook_check(__FUNCTION__)) return hook();
      if($post_value) $config = get_vars_by_post($config);
      $string = "{$copyright_file}global {$var};\n{$var} = ";
      if($inline){
         $string .="array(\n";
         foreach ($config as $key => $value) {
            $str = var_export($value,true);
            $str = preg_replace('/[\r\n]+\x20{0,1}/im', '', $str);
            $str = "   '{$key}' => {$str},\n";
            $string .= $str;
         }
         $string .= ")";
      } else $string .= var_export($config,true);
      $string = str_replace('\\\\"','"', $string);
      $string .= ";\n?>";
      $string = preg_replace('/(=>\x20*)[\r\n]+\x20*(array)/simx', '$1$2', $string);
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

?>
