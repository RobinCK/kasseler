<?php
if(!defined("FUNC_FILE")) die("Access is limited");
define("DB", true);

class sql_db {
    public $db_connect_id;
    private $query_result;
    public $num_queries = 0;
    public $total_time_db = 0;
    public $time_query = array();
    public $report_error = true;
    public $errno = '';
    public $error = '';
    public $cache_fetchrow = false;
    public $cache = array();

    /**
    * Конструктор класса
    * 
    * @param array $cfg
    * @param boot $persistency
    * @return resourse
    */
    public function __construct(&$cfg, $persistency = true) {
        $port = explode(':', $cfg['host']);
        if(isset($port[1])) $this->db_connect_id = mysqli_connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name'], $port[1]);
        else $this->db_connect_id = mysqli_connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name']);
        if($this->db_connect_id) {
            if(!empty($cfg['charset'])) mysqli_query($this->db_connect_id, "SET NAMES '{$cfg['charset']}'");
            return $this->db_connect_id;
        } else $this->error = mysqli_connect_error();
    }
    
    /**
    * Деструктор закрывает соединение с сервером
    *     
    * @return bool
    */
    public function __destruct() {
        if($this->db_connect_id) {
            return @mysqli_close($this->db_connect_id);
        } else return false;
    }
    

    /**
    * Посылает запрос активной базе данных сервера
    *     
    * @param string $namespace
    * @param string $query
    * @return resourse
    **/
    public function sql_query($query = "", $namespace = '') {
        if($this->db_connect_id) {
            unset($this->query_result);
            if(!empty($query)) {
                $st = microtime(1);
                if(!empty($namespace)) $query = user_change_sql_namespace($query, $namespace);
                $query = preg_replace('/[,\x20]*\{(FIELDS|TABLES|WHERES)\}/i', ' ', $query);
                $this->query_result = @mysqli_query($this->db_connect_id, $query);
                $total_tdb = round(microtime(1)-$st, 5);
                $this->total_time_db += $total_tdb;
                $this->time_query[] = array($total_tdb, $query);
            }
            if($this->query_result) {
                $this->num_queries+=1;
                return $this->query_result;
            } else {
                if($this->report_error) {
                    kr_sql_erorr_logs(mysqli_errno($this->db_connect_id), mysqli_error($this->db_connect_id), $query);
                    $this->errno = mysqli_errno($this->db_connect_id);
                    $this->error = mysqli_error($this->db_connect_id);
                }
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
    public function sql_numrows($query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) return @mysqli_num_rows($query_id);
        else return false;
    }

    /**
    * Возвращает количество рядов, затронутых последним INSERT, UPDATE, DELETE запросом к серверу
    * 
    * @param resourse $query_id
    * @return int
    */
    public function sql_affectedrows() {
        if($this->db_connect_id) return @mysqli_affected_rows($this->db_connect_id);
        else return false;
    }

    /**
    * Возвращает количество полей результата запрооса  
    * 
    * @param resourse $query_id
    * @return int
    */
    public function sql_numfields($query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) return @mysqli_num_fields($query_id);
        else return false;
    }

    /**
    * Возвращает название колонки с указанным индексом
    * 
    * @param int $offset
    * @param resourse $query_id
    * @return string
    */
    public function sql_fieldname($offset, $query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) {
            mysqli_field_seek($query_id, $offset);
            $field = mysqli_fetch_field($query_id);
            return $field->name;
        } else return false;
    }

    /**
    * Возвращает массив с обработанным рядом результата запроса
    * 
    * @param resourse $query_id
    * @param bool $debug
    * @return array
    */
    public function sql_fetchrow($query_id = 0) {
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) {
            $fetchrow = @mysqli_fetch_array($query_id);
            if($this->cache_fetchrow==true) $this->cache[] = $fetchrow;
            return $fetchrow;
        } else return false;
    }

    /**
    * Возвращает ID, сгенерированный колонкой с AUTO_INCREMENT последним запросом INSERT к серверу
    *     
    * @return int
    */
    public function sql_nextid() {
        if($this->db_connect_id) return  @mysqli_insert_id($this->db_connect_id);
        else return false;
    }

    /**
    * Высвободит всю память, занимаемую результатом, на который ссылается переданный функции указатель
    * 
    * @param resourse $query_id
    * @return bool
    */
    public function sql_freeresult($query_id = 0){
        if(!$query_id) $query_id = $this->query_result;
        if($query_id) {
            if(is_resource($query_id)) @mysqli_free_result($query_id);
            return true;
        } else return false;
    }
}

global $database, $db;
$db = new sql_db($database, false);
if(!$db->db_connect_id && !defined("INSTALLCMS") AND !isset($_GET['ajaxed'])) die("<br /><center>There seems to be a problem with the MySQL server, sorry for the inconvenience. We should be back shortly.</center>");
require_once 'includes/function/dbfunc.php';
?>