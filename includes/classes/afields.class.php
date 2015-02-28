<?php
   /**
   * Класс для работы с сохранением данных в формате JSON в базе
   */
   class afields{
      private $values=array();
      // public $meta_description="";
      function afields($json_text=""){
         if(!empty($json_text)) $this->values=json_decode($json_text,true);
      }
      private function remove_empty(){
         foreach ($this->values as $key => $value) {
            if(empty($value)) unset($this->values[$key]);
         }
      }
      /**
      * Выдать значение переменной
      * 
      * @param string $name
      */
      function val($name){
         return isset($this->values[$name])?$this->values[$name]:"";
      }
      /**
      * Задать значение переменной
      * 
      * @param string $name
      * @param string $value
      */
      function set($name,$value){
         $this->values[$name]=$value;
      }
      /**
      * Вернуть строку для вставки в БД
      * 
      */
      function sql(){
         $this->remove_empty();
         $ret=str_replace('\\u',"\\\\u",json_encode($this->values)); //fix utf-8 chars
         return $ret;
      }
      /**
      * загрузить данные из POST
      * 
      * @param array $postnames
      */
      function load_from_post($postnames){
         foreach ($postnames as $key => $val) {
            if(is_numeric($key) AND isset($_POST[$val])) $this->values[$val]=trim($_POST[$val]);
            elseif(isset($_POST[$key])) $this->values[$val]=trim($_POST[$key]);
         }
      }
      function load_from_db($table,$where,$filed_name='afields'){
         global $main;
         $main->db->sql_query("select `{$filed_name}` from `{$table}` where {$where}");
         if($main->db->sql_numrows()!=0){
            list($json)=$main->db->sql_fetchrow();
            $this->values=json_decode($json,true);
         }
      }
      function __get($prop_name){
         return isset($this->values[$prop_name])?$this->values[$prop_name]:null;
      }
      function __set($prop_name, $prop_value){
         $this->values[$prop_name] = $prop_value;
         return true;
      }
      public function __isset($prop_name){
         return isset($this->values[$prop_name])&&!empty($this->values[$prop_name]);
      }
      public function __unset($prop_name){
         unset($this->values[$prop_name]);
      }
   }
?>
