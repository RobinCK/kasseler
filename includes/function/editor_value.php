<?php
if(!defined('FUNC_FILE')) die('Access is limited');
   /**
   * Переменная в котору заносится значение record из БД
   * 
   * @var mixed
   */
   global $editor_value;
   function editval_set($arrval=array()){
   global $editor_value;
      if(hook_check(__FUNCTION__)) return hook();
      $editor_value=$arrval;
   }
   
   /**
   * для получения значения в функция которые изпользуются и для NEW и для EDIT
   * 
   * @param mixed $name
   * @param mixed $defaultvalue
   * @return mixed
   */
   function editval($name, $defaultvalue=""){
      global $editor_value;
      if(hook_check(__FUNCTION__)) return hook();
      return isset($editor_value[$name])?$editor_value[$name]:$defaultvalue;
   }
   /**
   * Аналогично editval но для массива
   * 
   * @param mixed $name
   * @param mixed $defaultvalue
   * @return mixed
   */
   function editval_array($name, $defaultvalue=array("")){
      global $editor_value;
      if(hook_check(__FUNCTION__)) return hook();
      return isset($editor_value[$name])?explode(",",$editor_value[$name]):$defaultvalue;
   }
   /**
   * Аналогично editval но для редактора текста
   * 
   * @param mixed $name
   * @param mixed $defaultvalue
   */
   function editval_editor($name, $defaultvalue=""){
      global $editor_value;
      if(hook_check(__FUNCTION__)) return hook();
      return isset($editor_value[$name])?bb($editor_value[$name], DECODE):$defaultvalue;
   }
?>
