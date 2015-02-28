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
        $this->db_connect_id = ($persistency) ? @mysql_pconnect($cfg['host'], $cfg['user'], $cfg['password']) : @mysql_connect($cfg['host'], $cfg['user'], $cfg['password']);
        if ($this->db_connect_id) {
            if ($cfg['name'] != "" && !@mysql_select_db($cfg['name'],$this->db_connect_id)) {
                @mysql_close($this->db_connect_id);
                $this->db_connect_id = false;
            }
            if(!empty($cfg['charset'])) @mysql_query("SET NAMES '{$cfg['charset']}'",$this->db_connect_id);
            return $this->db_connect_id;
        } else {
            return false;
        }
    }

    /**
    * Закрывает соединение с сервером
    *     
    * @return bool
    */
    function sql_close() {
        if ($this->db_connect_id) {
            if ($this->query_result) @mysql_free_result($this->query_result);
            $result = @mysql_close($this->db_connect_id);
            return $result;
        } else {
            return false;
        }
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
                $this->query_result = @mysql_query($query, $this->db_connect_id);
                $total_tdb = round(microtime(1)-$st, 5);
                $this->total_time_db += $total_tdb;
                $this->time_query[] = array($total_tdb, $query);
            }
            if($this->query_result) {
                $this->num_queries+=1;
                $this->error = $this->errno = "";
                return $this->query_result;
            } else {    
                $this->error = mysql_error($this->db_connect_id);
                $this->errno = mysql_errno($this->db_connect_id);
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
        if($query_id) return @mysql_num_rows($query_id);
        else return false;
    }

    /**
    * Возвращает количество рядов, затронутых последним INSERT, UPDATE, DELETE запросом к серверу
    * 
    * @param resourse $query_id
    * @return int
    */
    function sql_affectedrows() {
        if($this->db_connect_id) return @mysql_affected_rows($this->db_connect_id);
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
        if($query_id) return @mysql_num_fields($query_id);
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
        if($query_id) return @mysql_field_name($query_id, $offset);
        else return false;
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
            $fetchrow = @mysql_fetch_array($query_id);
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
        if($this->db_connect_id) return  @mysql_insert_id($this->db_connect_id);
        else return false;
    }

    /**
    * Высвободит всю память, занимаемую результатом, на который ссылается переданный функции указатель
    * 
    * @param resourse $query_id
    * @return bool
    */
    function sql_freeresult($query_id = 0){
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) {
            @mysql_free_result($query_id);
            return true;
        } else return false;
    }

}

global $database, $db;
$db = new sql_db($database, false);
if (!$db->db_connect_id && !defined("INSTALLCMS") AND !isset($_GET['ajaxed'])) die("<br /><center>There seems to be a problem with the MySQL server, sorry for the inconvenience. We should be back shortly.</center>");
require_once 'includes/function/dbfunc.php';
?>
