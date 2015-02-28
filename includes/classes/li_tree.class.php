<?php
if (!defined("FUNC_FILE")) die("Access is limited");

   class li_tree{
      var $treelist;
      var $tree_direct;
      var $db_tree_length=2;
      var $show_id=false;
      var $my_id="";
      var $prefix_tree="";
      var $my_class="";
      var $class_tree="";
      var $root_caption="";
      var $my_add_attr="";
      /**
      * Конструктор
      */
      function li_tree($id_tag,$class_tag){
         $this->treelist=array();
         $this->tree_direct=array();
         $this->my_id=$id_tag;
         $this->my_class=$class_tag;
      }
      public function &get_parent($parent,$arrlist=array()){
         $ret=null;
         $this->cn+=1;
         if($parent!=""){
            if(count($arrlist)==0) $arrlist=&$this->treelist;
            foreach ($arrlist as $key => $value) {
               if($key==$parent) {$ret=&$arrlist[$key];};
               $k=min(strlen($parent),strlen($key));
               if($ret==null&&(substr($parent,0,$k)==substr($key,0,$k))){
                  if(array_key_exists('children',$arrlist[$key])&&count($arrlist[$key]['children'])>0) $ret=&$this->get_parent($parent,$arrlist[$key]['children']);
                  else $ret=&$arrlist[$key];
               }
               if($ret!=null) break;
            }
         }
         return $ret;
      }
      /**
      * Загрузить данные из БД
      * 
      * @param mixed $dbresult
      * @param mixed $field_caption
      * @param mixed $field_id
      * @param mixed $field_tree
      */
      function load_db($dbresult,$field_caption,$field_id,$field_tree,$any_fields=array()){
         global $main;
         while ($row=$main->db->sql_fetchrow($dbresult)){
            $stree=$row[$field_tree];
            $corr=&$this->get_parent(substr($stree,0,-2));
            $this->tree_direct[]=array('id'=>$this->prefix_tree.$row[$field_id],'tree'=>$row[$field_tree],'caption'=>$row[$field_caption],'children'=>array(),'content'=>'','class'=>$this->class_tree);
            $key=count($this->tree_direct)-1;
            if(count($any_fields)==1&&$any_fields[0]=='all'){
               foreach ($row as $k => $value) if(!is_numeric($k)) $this->tree_direct[$key][$k]=$row[$k];
            } elseif(count($any_fields)!=0){
               foreach ($any_fields as $k => $value)  $this->tree_direct[$key][$k]=$row[$value];
            }
            if(gettype($corr)==='NULL') {
               if(isset($this->treelist[$stree])) $this->treelist[]=&$this->tree_direct[$key];
               else $this->treelist[$stree]=&$this->tree_direct[$key];
            }
            else {
               if(isset($corr['children'][$stree])) $corr['children'][]=&$this->tree_direct[$key];
               else $corr['children'][$stree]=&$this->tree_direct[$key];
            }
         }
      }
      function get_li($value){
         $id_tree=$this->show_id?" id='{$value['id']}'":"";
         $class=$value['class']!=""?$value['class']:$this->class_tree;
         $class_tree=($class!=""?" class='{$class}'":"");
         if(isset($value['mcaption'])) $caption=$value['mcaption'];
         else $caption= "<span{$class_tree}>{$value['caption']}</span>";
         return "<li{$id_tree}>{$caption}\n";
      }
      function return_info($carr){
         $ret="";
         foreach ($carr as $key => $value) {
            $ret.=$this->get_li($value);
            if(isset($value['content'])) $ret.=$value['content'];
            $isul=count($value['children'])>0||isset($value['ul']);
            if($isul) $ret.="<ul>";
            if(isset($value['ul'])) $ret.=$value['ul'];
            if(count($value['children'])>0) $ret.=$this->return_info($value['children']);
            if($isul) $ret.="</ul>";
            $ret.="</li>";
         }
         return $ret;
      }
      function out_ul_src(){
         return $this->return_info($this->treelist);
      }
      /**
      * Сформировать html
      * 
      */
      function out_html(){
         $class=($this->my_class!=""?" class='{$this->my_class}' ":"");
         $add_attr=$this->my_add_attr!=""?" {$this->my_add_attr} ":"";
         $root_exists=$this->root_caption!="";
         if ($root_exists){
            $ret="\n<ul{$class}{$add_attr}><li><span>{$this->root_caption}</span>\n";
            $class="";$add_attr="";
         } else $ret="";
         $ret.="\n<ul".($this->my_id!=""?" id='{$this->my_id}'":"")."{$class}{$add_attr}>\n";
         $ret.=$this->out_ul_src();
         $ret.="\n</ul>\n";
         if ($root_exists) $ret.="\n</li></ul>\n";
         return $ret;
      }
      function echo_html(){
         echo $this->out_html();
      }
   }
?>
