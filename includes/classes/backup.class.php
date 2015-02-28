<?php
/**
* Класс создания/загрузки резервной копии БД
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/classes/backup.class.php   
* @version 2.0
*/
if (!defined("FUNC_FILE")) die("Access is limited");

class backuper{
    /**
    * Максимальное время выполнения скрипта в секундах
    * 
    * @var int
    */
    var $time_limit = 600;
    
    /**
    * Тип СУБД
    * 
    * @var string
    */
    var $db_type = 'mysql';
    
    /**
    * Общее количество записей
    * 
    * @var int
    */
    
    var $all_rows = 0;  
           
    /**
    * Размер БД
    * 
    * @var int
    */
    var $size = 0;
    
    /**
    * Тип файла
    * 
    * @var string
    */
    var $filename = '.sql';
    
    /**
    * Каталог хранения резервных копий
    * 
    * @var string
    */
    var $dir = './';
    
    /**
    * Префикс таблиц
    * 
    * @var string
    */
    var $prefix = '';
    
    /**
    * Метод сохранения/чтения резервных копий БД
    * 
    * @var int
    */
    var $method = 0;   //2 = bz2; 1 = gz; 0 = sql;
    
    /**
    * Степень сжатия резервной копии БД
    * 
    * @var int
    */
    var $comp_level = 6;  //max 9
    
    /**
    * Только создавать таблицы
    * 
    * @var bool
    */
    var $only_create = false;
    
    /**
    * Шаблон
    * 
    * @var string
    */
    var $tpl = "";
    
    /**
    * Список выполненных операций
    * 
    * @var string
    */
    var $operations = "";
    
    /**
    * Таймер выполнения
    * 
    * @var object
    */
    var $timer;
    
    /**
    * Массив таблиц подлежащих сохранению
    * 
    * @var array
    */
    var $tables = array();
    
    /**
    * Кэщ чтения файла
    * 
    * @var string
    */
    var $file_cache = "";

    /**
    * Конструктор
    * 
    * @return backuper
    */
    function backuper(){
        $this->template();
    }

    function backup(){
        global $main;
        //Устанавливаем время выполнения скрипта        
        if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit($this->time_limit);
        if(!SAFE_MODE AND function_exists('ob_implicit_flush')) ob_implicit_flush();        
        $this->set_template("<span style='color: #004C87'>Backed up DB<br />");
        $table_array = array();
        //Выбираем список таблиц
        $result = $main->db->sql_query("SHOW TABLES");
        if($main->db->sql_numrows($result)>0){
            while(list($table) = $main->db->sql_fetchrow($result)) {
                if(!empty($this->tables) AND !in_array($table, $this->tables)) continue;
                $table_array[$table] = array(); //Создаем массив таблиц
            }
        }
        //Выбираем список таблиц и их характеристики
        $result = $main->db->sql_query("SHOW TABLE STATUS");
        if($main->db->sql_numrows($result)>0){
            //Выполняем подсчет данных и сбор данных о таблицах БД
            while(($status = $main->db->sql_fetchrow($result))){
                if(isset($table_array[$status['Name']])){
                    $status['Rows'] = empty($status['Rows']) ? 0 : $status['Rows'];
                    //Подсчитываем количество записей
                    $this->all_rows += $status['Rows'];
                    //Подсчитываем размер БД
                    $this->size += $status['Data_length'];
                    $table_size = 1 + round(1 * 1048576 / ($status['Avg_row_length'] + 1));
                    $m = "";
                    if(!empty($status['Collation']) AND preg_match("/^([a-z0-9]+)_/i", $status['Collation'], $m)) $status['Collation'] = $m[1];
                    $status['Engine'] = isset($status['Engine']) ? $status['Engine'] : $status['Type'];
                    $table_array[$status['Name']] = array(
                        'name'          =>     $status['Name'],
                        'engine'        =>     $status['Engine'],
                        'varsion'       =>     $status['Version'],
                        'rows'          =>     $status['Rows'],
                        'data_length'   =>     $status['Data_length'],
                        'charset'       =>     $status['Collation'],
                        'size'          =>     $table_size
                    );
                }
            }
            $backup_name = $this->prefix.date("Y-m-d_H-i");
            //Открываем файл для записи резервной копии
            $fp = $this->backup_open($backup_name, "w");
            //Передаем в шаблон информацию о начале создании резервной копии и названии файла
            $this->set_template("<span style='color: #004C87'>Creating file from backup database: <b>{$this->filename}</b></span><br />");
            //Разрешаем выполнение SHOW CREATE TABLE
            $result = $main->db->sql_query("SET SQL_QUOTE_SHOW_CREATE = 1");
            foreach ($table_array AS $table){
                //Передаем в шаблон название обрабатываемой таблицы
                $this->set_template("<span style='color: #777777;'>Processing table `{$table['name']}` [{$table['rows']}]</span><br />");
                //Получаем SQL для создания передаваемой таблицы
                $result = $main->db->sql_query("SHOW CREATE TABLE `{$table['name']}`");
                $blob = $main->db->sql_fetchrow($result);
                //Записываем в файл создание таблицы
                $this->backup_write($fp, "DROP TABLE IF EXISTS `{$table['name']}`;\n{$blob[1]};\n\n");
                //Если опция only_create = true или количество записей в таблице равна 0, создание INSERT-ов пропускаем
                if ($this->only_create OR $table['rows']==0) continue;
                //Получаем информацию о полях в заданной таблице
                $result = $main->db->sql_query("SHOW COLUMNS FROM `{$table['name']}`");
                $cols = array();
                $numeric_array = array();
                //Определяем тип полей
                while(($col = $main->db->sql_fetchrow($result))){
                    $cols[] = $col[0];
                    $numeric_array[] = preg_match('/^(\w*int|year)/', $col[1]) ? 1 : 0;
                }
                //Записываем результат в файл
                $this->backup_write($fp, "INSERT INTO `{$table['name']}` VALUES\n");
                //Выполняем выборку данных с таблицы
                $result = $main->db->sql_query("SELECT * FROM {$table['name']}");
                $count = $main->db->sql_numrows($result);
                $max_record_insert=500;
                $mxy=0;
                $y = 0;
                $cols_count = count($cols);
                while(($ins = $main->db->sql_fetchrow($result))){
                    $values = "(";
                    $i = 0;
                    //Создаем INSERT-ы
                    foreach ($cols AS $col){
                        if (isset($numeric_array[$i]) AND $numeric_array[$i]==1) $ins[$col] = isset($ins[$col]) ? $ins[$col] : "NULL";
                        else $ins[$col] = isset($ins[$col]) ? '"'.magic_quotes($ins[$col]).'"' : "NULL";
                        $values .= ($i<$cols_count-1) ? "{$ins[$col]}, " : "{$ins[$col]}";
                        $i++;
                    }
                    if($y<$count-1){
                       $values .= ($mxy<$max_record_insert-1) ? "),\n" : ");\n\n";
                    } else $values .= ");\n\n";
                    //Записываем результат в файл
                    $this->backup_write($fp, $values);
                    $y++;$mxy++;
                    if($mxy==$max_record_insert) {$this->backup_write($fp, "INSERT INTO `{$table['name']}` VALUES\n");$mxy=0;}
                }
                //Освобождаем память от запроса
                $main->db->sql_freeresult($result);
            }
        }
        //Закрываем файл
        $this->backup_close($fp);
        //Передаем информацию о резервной копии
        $this->set_template("<br /><span style='color: red;'>File size: ".get_size(filesize($this->dir.$this->filename))."</span><br />");
        $this->set_template("<span style='color: red;'>Tables processed: ".count($table_array)."</span><br />");
        $this->set_template("<span style='color: red;'>Rows processed: ".$this->all_rows."</span>");
        $this->echo_tpl();
    }
    
    /**
    * Функция загрузки резервной копии БД
    * 
    * @return void
    */
    function restore(){
        global $main;
        if(!SAFE_MODE AND function_exists('set_time_limit')) set_time_limit($this->time_limit);
        if(!SAFE_MODE AND function_exists('ob_implicit_flush')) ob_implicit_flush();        
        $this->set_template("<span style='color: #004C87'>Restoring database from backup</span><br />");
        //Определяем тип бекапа        
        $matches = "";
        if(preg_match('/^(.+?)\.sql(\.(bz2|gz))?$/', $this->filename, $matches)) {            
            if (isset($matches[3]) AND $matches[3] == 'bz2') $this->method = 2;
            elseif (isset($matches[2]) AND $matches[3] == 'gz') $this->method = 1;
            else $this->method = 0;
            $this->comp_level = '';
            if(!file_exists($this->dir.$this->filename)) {
                $this->set_template("<span style='color: #004C87'>File not found</span><br />");
                return false;
            }
            $this->set_template("<span style='color: #004C87'>Reading File `{$this->filename}`</span><br />");
        } else {
            $this->set_template("<span style='color: red'>ERROR! Do not select a file!</span><br />");
            return false;
        }        
        $fp = $this->backup_open($this->filename, "r", false);        
        $query_len = $execute = $q = $t = $i = $aff_rows = 0; 
        $index = 4; $tabs = 0; 
        $this->file_cache = $sql = $table = $insert = $last_showed = '';
        $info = array();
        while(($str = $this->backup_read_str($fp)) !== false){
            $query_len += mb_strlen($str); 
            $m = "";
            if(!$insert AND preg_match("/^(INSERT INTO `?([^` ]+)`? .*?VALUES)(.*)$/i", $str, $m)) {
                if($table != $m[2]){
                    $table = $m[2];
                    $tabs++;
                    $this->set_template("<span style='color: #777777;'>Table `{$table}`.</span><br />");
                    $last_showed = $table; $i = 0;
                    $insert = $m[1].' ';
                    $sql .= $m[3];
                    $index++;
                    $info[$index] = isset($info[$index]) ? $info[$index] : 0;
                }
            } else {
                $sql .= $str;
                if($insert) {$i++; $t++;}
            }
            if($sql){
                if(preg_match("/;$/", $str)) {
                    $sql = rtrim($insert.$sql, ";");
                    if($last_showed != $table){
                        $this->set_template("<span style='color: #777777;'>Table `{$table}`.</span><br />");
                        $last_showed = $table;
                    }
                    $insert = ''; $execute = 1;
                }                 
            }            
            if($query_len >= 65536 && preg_match("/,$/", $str)){$sql = rtrim($insert.$sql, ","); $execute = 1;}
            if($execute==1){
                $q++;
                if(!$main->db->sql_query($sql)) {
                    $this->set_template("<span style='color: red'>Bad Request<br />{$main->db->error}</span><br /><pre>".htmlspecialchars($sql)."</pre>"); 
                    break;
                }
                if(preg_match("/^insert/i", $sql)) $aff_rows += $main->db->sql_affectedrows();
                $sql = ''; $query_len = $execute = 0;
            }
        }     
        $this->set_template("<br /><span style='color: red'>Query in database: {$q}</span><br />"); 
        $this->set_template("<span style='color: red'>Tables created: {$tabs}</span><br />"); 
        $this->set_template("<span style='color: red'>Rows inserted: {$aff_rows}</span><br />");         
        $this->backup_close($fp);
        $this->echo_tpl();  
        return true;      
    }

    /**
    * Функция записи в файл
    * 
    * @param string $file
    * @param string $str
    */
    function backup_write($file, $str){
        if ($this->method == 2) bzwrite($file, $str);
        elseif ($this->method == 1) gzwrite($file, $str);
        else fwrite($file, $str);
    }
    
    /**
    * Функция чтения файла
    * 
    * @param resourse $fp
    * @return string
    */
    function backup_read($fp){  
        if($this->method == 2) return bzread($fp, 4096);
        elseif ($this->method == 1) return gzread($fp, 4096);
        else return fread($fp, 4096);
    }
    
    /**
    * Функция построчного чтения файла
    * 
    * @param resourse $fp
    * @return string
    */
    function backup_read_str($fp){
        $string = '';
        $this->file_cache = ltrim($this->file_cache);
        $pos = mb_strpos($this->file_cache, "\n", 0);
        if($pos < 1) {
            while (!$string AND ($str = $this->backup_read($fp))){
                $pos = mb_strpos($str, "\n", 0);
                if ($pos === false)  $this->file_cache .= $str;
                else {
                    $string = $this->file_cache . mb_substr($str, 0, $pos);
                    $this->file_cache = mb_substr($str, $pos + 1);
                }
            }
            if(!$str) {
                if($this->file_cache) {
                    $string = $this->file_cache;
                    $this->file_cache = '';
                    return trim($string);
                }
                return false;
            }
        } else {
            $string = mb_substr($this->file_cache, 0, $pos);
            $this->file_cache = mb_substr($this->file_cache, $pos + 1);
        }
        return trim($string);
    }

    /**
    * Функция открывает файл
    * 
    * @param string $name
    * @param string $mode
    * @return resource
    */
    function backup_open($name, $mode, $set_type=true){
       if ($this->method == 2) {
          $this->filename = $set_type ? "{$name}.sql.bz2" : $name;
          return bzopen($this->dir.$this->filename, "{$mode}");
       } elseif ($this->method == 1) {
          $this->filename = $set_type ? "{$name}.sql.gz" : $name;
          return gzopen($this->dir.$this->filename, "{$mode}b".($mode == "w" ? "{$this->comp_level}" : ""));
       } else{
          $this->filename = $set_type ? "{$name}.sql" : $name;
          return fopen($this->dir.$this->filename, "{$mode}b");
       }
    }

    /**
    * Функция закрывает файл
    *                          
    * @param string $file
    * @return void
    */
    function backup_close($file){
        if ($this->method == 2) bzclose($file);
        elseif ($this->method == 1) gzclose($file);
        else fclose($file);
        @chmod($this->dir.$this->filename, 0666);
    }

    /**
    * Функция создания шаблона для демонстрации выполнения процесса
    * 
    * @return void
    */
    function template(){
        $this->timer = new timer();
        $this->tpl = "<div class='dump'>\$content</div><table width='100%'><tr><td><b>Time: \$timer seconds</b></td><td align='right'>\$download</td></tr></table><hr />";
    }

    /**
    * Функция добавляет запись в шаблон
    * 
    * @param string $string
    * @return void
    */
    function set_template($string){        
        $this->operations .= $string."\n";
    }
    
    function echo_tpl(){
        echo preg_replace(array('/\$content/is', '/\$timer/is', '/\$download/is'), array($this->operations, $this->timer->stop(), "<a href='{$this->dir}{$this->filename}'><b>Download</b></a>"), $this->tpl);
    }
}
?>