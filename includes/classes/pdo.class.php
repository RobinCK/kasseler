<?php
/**
* Класс для работы системы с СУБД MySQL
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/mysql.class.php
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");
define("DB", true);

class sql_db{
    var $db_connect_id;
    var $query_result;
    var $num_queries = 0;
    var $total_time_db = 0;
    var $time_query = array();
    var $report_error = true;
    var $error = '';
    var $errno = '';
    var $dsn = "mysql";
    var $cache_fetchrow = false;
    var $cache = array();

    /**
    * Конструктор класса
    * 
    * @param array $cfg
    * @param boot $persistency
    * @return resourse
    */
    function sql_db($cfg, $persistency = true) {
        try {
            $this->db_connect_id = new PDO("{$this->dsn}:host={$cfg['host']};dbname={$cfg['name']}", $cfg['user'], $cfg['password']);
            if(!empty($cfg['charset']) AND $cfg['type']=='mysql') $this->db_connect_id->query("SET NAMES '{$cfg['charset']}'");
            return $this->db_connect_id;
        } catch (PDOException $e) {
            die("<center>Connection failed: ".$e->getMessage()."</center>");
        }
    }

    /**
    * Закрывает соединение с сервером
    *     
    * @return bool
    */
    function sql_close() {
        if($this->db_connect_id) {
            $this->db_connect_id = null;
            return true;
        } else return false;
    }

    /**
    * Посылает запрос активной базе данных сервера
    *     
    * @param string $namespace
    * @param string $query        
    * @return resourse
    **/
    function sql_query($query = "", $namespace = '') {
        if($this->db_connect_id) {
            unset($this->query_result);
            if($query != "") {
                $st = microtime(1);
                if(!empty($namespace)) $query = user_change_sql_namespace($query, $namespace);
                $query = preg_replace('/[,\x20]*\{(FIELDS|TABLES|WHERES)\}/i', ' ', $query);
                $this->query_result = $this->db_connect_id->query($query);
                $total_tdb = round(microtime(1)-$st, 5);
                $this->total_time_db += $total_tdb;
                $this->time_query .= array($total_tdb, $query);
            }                                                                                
            if($this->query_result) {                   
                $this->num_queries+=1;
                $this->error = $this->errno = "";
                return $this->query_result;
            } else {    
                $this->error = $this->sql_error($this->db_connect_id);
                $this->errno = $this->sql_errno($this->db_connect_id);
                if($this->report_error) kr_sql_erorr_logs($this->errno, $this->error, $query);
                return false;
            }
        } else return false;
    }        
    
    /**
    * Возвращает количество рядов результата запроса
    * 
    * @param resourse $query_id
    * @return int
    */
    function sql_numrows($query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;        
        if($query_id) return $query_id->rowCount();
        else return false;
    }

    /**
    * Возвращает количество рядов, затронутых последним INSERT, UPDATE, DELETE запросом к серверу
    * 
    * @param resourse $query_id
    * @return int
    */
    function sql_affectedrows(){
        if($this->query_result) return $this->query_result->rowCount();
        else return false;
        
    }

    /**
    * Возвращает количество полей результата запрооса  
    * 
    * @param resourse $query_id
    * @return int
    */
    function sql_numfields($query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) return count($query_id->fetch(PDO::FETCH_ASSOC));
        else return false;
    }

    /**
    * Возвращает название колонки с указанным индексом
    *                                                                                           
    * @param int $offset
    * @param resourse $query_id
    * @return string
    */
    function sql_fieldname($offset, $query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        $row = $query_id->fetch(PDO::FETCH_ASSOC);
        $i = 0;
        if(count($row)-1>=$offset){
            foreach($row as $key => $value){
                if($i==$offset) return $key;
                $i++;
            }
        } else return false;
    }
    
    /**
    * Возвращает массив с обработанным рядом результата запроса
    * 
    * @param resourse $query_id
    * @param bool $debug
    * @return array
    */
    function sql_fetchrow($query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) {
            $fetchrow = $query_id->fetch(PDO::FETCH_BOTH);
            if($this->cache_fetchrow==true) $this->cache[] = $fetchrow;
            return $fetchrow;
        } else return false;
    }

    /**
    * Возвращает ID, сгенерированный колонкой с AUTO_INCREMENT последним запросом INSERT к серверу
    *     
    * @param resourse $query_id
    * @return int
    */
    function sql_nextid($query_id = "") {
        if($this->db_connect_id) return $this->db_connect_id->lastInsertId();
        else return false;
    }

    /**
    * Высвободит всю память, занимаемую результатом, на который ссылается переданный функции указатель
    * 
    * @param resourse $query_id
    * @return bool
    */
    function sql_freeresult($query_id = 0){
        if($query_id) {
            $query_id->closeCursor();
            $query_id = null;
            return true;
        } else return false;
    }
    
    /**
    * Возвращает численный код ошибки выполнения последней операции с MySQL
    * 
    * @param resource $query_id
    * @return int
    */
    function sql_errno($query_id = 0){
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) return $query_id->errorCode();
        else return false;
    }

    /**
    * Возвращает строку ошибки последней операции с MySQL.
    * 
    * @param resource $query_id
    * @return string
    */
    function sql_error($query_id = 0){
        if(!$query_id) $query_id = $this->query_result;
        $info = $query_id->errorInfo();
        if($query_id) return $info[2];
        else return false;
    }
}
require_once 'includes/function/dbfunc.php';
?>
