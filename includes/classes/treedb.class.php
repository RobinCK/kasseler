<?php
   /**
   * Класс создания дерево-видных структур
   * 
   * @author Igor Ognichenko
   * @author Brovko Dmitry
   * @copyright Copyright (c)2007-2010 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource includes/classes/treedb.class.php  
   * @version 2.0
   */
   if (!defined('FUNC_FILE')) die('Access is limited');

   class treedb {
      /**
      * Обрабатываемая таблица
      * 
      * @var string
      */
      var $table = "";
      var $count_char_in_level=2;
      var $pack_db_after_remove=TRUE;
      var $code_num_db=36;// основание для кодирования числе в БД

      /**
      * Конструктор
      * 
      * @param string $table
      * @return treedb
      */
      function treedb($table,$count_char_level=2){
         $this->table = $table;
         $this->count_char_in_level=$count_char_level;
      }
      function get_one_value($sql){
         global $main;
         $result = $main->db->sql_query($sql);
         $row = $main->db->sql_fetchrow($result);
         return $row[0];
      }
      /**
      * Получение нового(максимального знячения для .tree
      * 
      * @param string $parent
      * @return string
      */
      function get_max_id($parent){
         global $main;
         $s_zero = "0000000000"; $space = '';
         $code_num = $this->code_num_db;
         for($i=1;$i<=$this->count_char_in_level;$i++) $space .= '_';
         $scase="when 0 then ''";
         $zero_id='1';
         for($i=1;$i<$this->count_char_in_level;$i++){;
            $lzero=mb_substr($s_zero,1,$this->count_char_in_level-$i);
            $scase.="\n when ".$i." then '{$lzero}'";
            $zero_id="0".$zero_id;
         }
         $sql="select if(isnull(max(t.tree)),'{$parent}{$zero_id}',\n".
         "concat(case mod(LenGth(conv(conv(max(t.tree),{$code_num},10)+1,10,{$code_num})),{$this->count_char_in_level})\n{$scase}\n".
         "end,conv(conv(max(t.tree),{$code_num},10)+1,10,{$code_num}))) newid\n".
         "from  {$this->table} t \n".
         "where t.tree LIKE '{$parent}{$space}'";
         $result = $main->db->sql_query($sql);
         $row = $main->db->sql_fetchrow($result);         
         return $row['newid'];
      }
      /**
      * Добавление ветки дерева
      * 
      * @param string $parent
      * @param string $sql_insert
      * @return string
      */
      function append($parent, $sql_insert){
         global $main;
         $id = $this->get_max_id($parent);         
         $main->db->sql_query(preg_replace('/\{IDTREE\}/i', $id, $sql_insert));
         return $id;
      }
      /**
      * Сдвиг вверх поля tree после операций move и delete
      * 
      * @param string $parent
      */
      function pack_after($parent){
         global $main;
         if ($this->pack_db_after_remove) {
            $new_like = mb_substr($parent,0,mb_strlen($parent)-$this->count_char_in_level);
            $sql="update {$this->table} t \nset t.tree=".$this->get_concat_param($parent,FALSE)."\n".
            "where t.tree like '{$new_like}%' and t.tree>'{$parent}' ";
            $main->db->sql_query($sql);
         }
      }
      /**
      * Удаление ветки дерева
      * 
      * @param mixed $parent
      * @return boolean
      */
      function delete($parent){
         global $main;
         $main->db->sql_query("DELETE FROM {$this->table} WHERE tree LIKE '{$parent}%'");
         $result=$main->db->sql_affectedrows();
         $this->pack_after($parent);
         return $result!=0;
      }
      /**
      * возвращает строку concat для сдвигов веток по дереву
      * 
      * @param string $parent (ветка от которой происходит сдвиг)
      * @param boolean $down_shift( сдвиг вниз?)
      */
      function get_concat_param($parent,$down_shift){
         $s_zero="0000000000";
         $code_num=$this->code_num_db;
         $lenparent=mb_strlen($parent);
         $level=$this->count_char_in_level;  
         $scase="when 0 then ''";
         for($i=1;$i<$level;$i++){;
            $lzero=mb_substr($s_zero,1,$level-$i);
            $scase.="\nwhen {$i} then '{$lzero}'";
         }
         return  "concat(case mod(length(conv(conv(substring(t.tree,1,{$lenparent}),{$code_num},10)".($down_shift?"+":"-")."1,10,{$code_num})),{$level})\n".
         "{$scase} \n".
         "end ,conv(conv(substring(t.tree,1,{$lenparent}),{$code_num},10)".($down_shift?"+":"-")."1,10,{$code_num}),substring(t.tree,".++$lenparent.",LenGth(t.tree)-{$level}))";
      }
      private function exec_before_insert($dest){
         global $main;
         $code_num = $this->code_num_db;
         $new_like = mb_substr($dest,0,mb_strlen($dest)-$this->count_char_in_level);
         $sql="update {$this->table} t \nset t.tree=".$this->get_concat_param($dest,TRUE)."\n".
         "where t.tree like '{$new_like}%' and t.tree>='{$dest}'";
         $main->db->sql_query($sql);
         return $dest;
      }
      /**
      * Вставка ветки перед заданным элементом
      * 
      * @param string $parent
      * @param string $sql_insert
      * @return void
      */
      function insert_before($parent, $sql_insert){
         global $main;
         /*$code_num = $this->code_num_db;
         $new_like = mb_substr($parent,0,mb_strlen($parent)-$this->count_char_in_level);
         $sql="update {$this->table} t \nset t.tree=".$this->get_concat_param($parent,TRUE)."\n".
         "where t.tree like '{$new_like}%' and t.tree>='{$parent}'";
         $main->db->sql_query($sql);*/
         $this->exec_before_insert($parent);
         $main->db->sql_query(preg_replace('/\{IDTREE\}/i', $parent, $sql_insert));
      }
      private function exec_after_insert($dest){
         global $main;
         $s_zero="0000000000";
         $code_num=$this->code_num_db;
         $level=$this->count_char_in_level;  
         $new_like = mb_substr($dest,0,mb_strlen($dest)-$level);
         $sql="update {$this->table} t \nset t.tree=".$this->get_concat_param($dest,TRUE)."\n".
         "where t.tree like '".$new_like."%' and t.tree>'".$dest."' and not(t.tree like '".$dest."%')";
         $main->db->sql_query($sql);
         $new_id=base_convert(base_convert($dest,$code_num,10)+1,10,$code_num);
         $n=$level-(mb_strlen($new_id) % $level);
         $new_id=mb_substr($s_zero,1,$n).$new_id;
         return $new_id;
      }
      private function gen_next_id($dest){
         $s_zero="0000000000";
         $code_num=$this->code_num_db;
         $level=$this->count_char_in_level;  
         $new_id=base_convert(base_convert($dest,$code_num,10)+1,10,$code_num);
         $n=$level-(mb_strlen($new_id) % $level);
         $new_id=mb_substr($s_zero,1,$n).$new_id;
         return $new_id;
      }
      /**
      * Вставка ветки после заданным элементом
      * 
      * @param string $parent
      * @param string $sql_insert
      * @return void
      */
      function insert_after($parent, $sql_insert){
         global $main;
         $s_zero="0000000000";
         $code_num=$this->code_num_db;
         $level=$this->count_char_in_level;  
         $new_like = mb_substr($parent,0,mb_strlen($parent)-$level);
         $sql="update {$this->table} t \nset t.tree=".$this->get_concat_param($parent,TRUE)."\n".
         "where t.tree like '".$new_like."%' and t.tree>'".$parent."' and not(t.tree like '".$parent."%')";
         $main->db->sql_query($sql);
         $new_id=base_convert(base_convert($parent,$code_num,10)+1,10,$code_num);
         $n=$level-(mb_strlen($new_id) % $level);
         $new_id=mb_substr($s_zero,1,$n).$new_id;
         $main->db->sql_query(preg_replace('/\{IDTREE\}/i',$new_id , $sql_insert));
      }
      /**
      * проверка на существование ветки дерева с заданным кодом
      * 
      * @param string $parent (код ветки для поиска)
      * @return void 
      */
      function exists_tree($parent){
         global $main;
         $sql="select t.tree from {$this->table} t where t.tree='{$parent}'";
         $result = $main->db->sql_query($sql);
         $row = $main->db->sql_fetchrow($result);
         return !empty($row['tree']);
      }
      /**
      * Перемещение в конец ветки дерева $dest
      * 
      * @param string $sourse
      * @param string $dest
      * @param string $mode ('' - child, 'a'- after dest, 'b' - before dest)
      * @return void
      */
      function move($sourse, $dest,$mode=''){
         global $main;         
         $len_source=mb_strlen($sourse);
         if ($this->exists_tree($dest) or $dest=="") {
            if($mode==''){
               $new_id=$this->get_max_id($dest);
               $sql="update {$this->table} t \n".
               "set t.tree=concat('{$new_id}',substring(t.tree,".($len_source+1).",length(t.tree)-{$len_source})) \n".
               "where t.tree like '{$sourse}%'";
               $main->db->sql_query($sql);
               $this->pack_after($sourse);
               return true;
            } elseif($mode=='a'){
               $ntree=$this->exec_after_insert($dest);
               $l=strlen($dest);
               if(substr($sourse,0,$l)>$dest) $sourse=$this->gen_next_id(substr($sourse,0,$l)).substr($sourse,$l);
               $sql="update {$this->table} t \n".
               "set t.tree=concat('{$ntree}',substring(t.tree,".($len_source+1).",length(t.tree)-{$len_source})) \n".
               "where t.tree like '{$sourse}%'";
               $main->db->sql_query($sql);
               $this->pack_after($sourse);
               return true;
            } elseif($mode=='b'){
               $ntree=$this->exec_before_insert($dest);
               $l=strlen($dest);
               if(substr($sourse,0,$l)==$dest) $sourse=$this->gen_next_id($dest).substr($sourse,$l);
               $sql="update {$this->table} t \n".
               "set t.tree=concat('{$ntree}',substring(t.tree,".($len_source+1).",length(t.tree)-{$len_source})) \n".
               "where t.tree like '{$sourse}%'";
               $main->db->sql_query($sql);
               $this->pack_after($sourse);
               return true;
            } else return false;
         } 
      }
      /**
      * Перемещение веток дерева
      * 
      * @param string $sourse
      * @param string $dest
      * @return void
      */
      function replace($sourse, $dest){
         global $main;
         if ($this->exists_tree($dest)  and $this->exists_tree($sourse)){ 
            $main->db->sql_query("update {$this->table} t set t.tree=concat('ZZ',substring(t.tree,".(mb_strlen($dest)+1).",length(t.tree)-".(mb_strlen($dest)).")) where t.tree like '{$dest}%'");
            $main->db->sql_query("update {$this->table} t set t.tree=concat('{$dest}',substring(t.tree,".(mb_strlen($sourse)+1).",length(t.tree)-".(mb_strlen($sourse)).")) where t.tree like '{$sourse}%'");
            $main->db->sql_query("update {$this->table} t set t.tree=concat('{$sourse}',substring(t.tree,3,length(t.tree)-2)) where t.tree like 'ZZ%'");
            return TRUE;
         } else return FALSE;  
      }
      /**
      * Конвертация $id в формат базы
      * 
      * @param mixed $id
      */
      function conv_id($id){
         $strh='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
         $ret="";$mid=$id;
         while ($mid>=$this->code_num_db){
            $num=$mid % $this->code_num_db;
            $mid=($mid-$num)/$this->code_num_db;
            $ret=$strh[$num].$ret;
         }
         $ret=$strh[$mid].$ret;
         while (strlen($ret)<$this->count_char_in_level) $ret="0".$ret;
         return $ret;
      }
      /**
      * Сортирует дочерние ветки
      * 
      * @param string $source
      * @param string $sort_where
      */
      function sort_item($source,$sort_order){
         global $main;
         $subs="_______________________";
         $like=substr($subs,0,$this->count_char_in_level);
         $res=$main->db->sql_query("select * from ".$this->table." where tree like '{$source}{$like}' ".$sort_order);
         $i=0;
         while ($row=$main->db->sql_fetchrow($res)){
            $i++;$new_id=$source.$this->conv_id($i);
            $curr=$this->get_one_value("select `tree` from ".$this->table." where id={$row['id']}");
            if ($curr!=$new_id) {
               $this->replace($curr,$new_id);
            }
         }
      }
   }

?>
