<?php
   global $main, $acc_info, $acc_buffer, $groups, $listacc, $acc,$uid;
   global $fileds_acc,$empty_acc,$lang_acc,$listacc,$acc_opened, $convert_fields, $forum_access, $detail_mode_acc;
   define('accView', 'acc_view'); //просматривать темы
   define('accRead', 'acc_read');//читать темы
   define('accWrite', 'acc_write');//начинать темы
   define('accPost', 'acc_post');//отвечать на сообщения
   define('accEdit', 'acc_edit');//редактировать сообщения
   define('accDelete', 'acc_delete');//удалять сообщения
   define('accUpload', 'acc_upload');//загружать файлы на форум
   define('accDownload', 'acc_download');//загружать файлы с форума
   define('accVoting', 'acc_voting');// голосовать на форуме
   define('accModerator', 'acc_moderator');//модерировать этот форум
   $detail_mode_acc = false;
   $acc_opened=array();// list load forum acc
   $listacc=array('acc_view','acc_read','acc_write','acc_post','acc_edit','acc_delete','acc_upload', 
      'acc_download','acc_voting','acc_moderator');
   $fileds_acc="";   
   foreach ($listacc as $key => $value) {
      $fileds_acc.=", fa.{$value}";
      $acc[$value]=0;
      $empty_acc[$value]=0;
   }
   $fileds_acc = substr($fileds_acc,1);
   $lang_acc=array('acc_view'=>$main->lang['accview'],'acc_read'=>$main->lang['accread'],'acc_write'=>$main->lang['accwrite'],
      'acc_post'=>$main->lang['accpost'],'acc_edit'=>$main->lang['accedit'],
      'acc_delete'=>$main->lang['accdelete'],'acc_upload'=>$main->lang['accfileupload'], 
      'acc_download'=>$main->lang['accfiledownload'],'acc_voting'=>$main->lang['accvoting'],
      'acc_moderator'=>$main->lang['accmoderator']);    
   if(is_guest()){
      $uid=0;
      $groups='4';
   } else {
      $uid=$main->user['uid'];
      $groups = user_group_acc_encode($main->user['user_group'], $main->user['user_groups']);
   }

   function user_group_acc_encode($ugroup, $ugroups){
      $pos=strpos($ugroups,",{$ugroup},");
      if ($pos === false) $ugroups=strlen($ugroups)>0?$ugroups."{$ugroup},":$ugroup;
      if(substr($ugroups,-1)==',') $ugroups=substr($ugroups,0,-1);
      return $ugroups;
   }
   function tree_parents($ptree){
      $tree=$ptree;$arr=array();
      while($tree!=""){$arr[]=$tree;$tree=substr($tree,0,-2);}
      return $arr;
   }
   function convert_parents($ptree){
      if(hook_check(__FUNCTION__)) return hook();
      return " in ('".implode("','",tree_parents($ptree))."')";
   }

   function load_acc($dbresult){
      global $acc_info,$main,$listacc;
      if(hook_check(__FUNCTION__)) return hook();
      $acc_info=$acc_buffer=array();
      while (($row=$main->db->sql_fetchrow($dbresult))){
         if($row['ugid']!=""){
            if(!isset($acc_info[$row['tree']])) $acc_info[$row['tree']]=array();
            $idg=$row['thisuser'].$row['ugid'];
            $fc=$row['typeacc']=='f'?$row['typeacc'].$row['idv']:'tc';
            if(!isset($acc_info[$row['tree']][$fc])) $acc_info[$row['tree']][$fc]=array();
            $acc_info[$row['tree']][$fc][$idg]=array();
            foreach ($listacc as $value) {
               $acc_info[$row['tree']][$fc][$idg][$value]=$row[$value];
            }
         }
      }
   }

   global $tree_moder;
   $tree_moder=array();
   function open_forum_moder($tree,$forum_id=0){
      global $main, $tree_moder;
      if($forum_id==0){
         $like_forum=$like_tree="(fc.tree ".convert_parents($tree)." or fc.tree like '{$tree}__')";
      } else {
         $like_forum="ff.forum_id={$forum_id}";
         $like_tree="fc.tree ".convert_parents($tree);
      }
      $rs=$main->db->sql_query("
         (SELECT  fa.ugid, fa.thisuser,fa.typeacc,
         cast(concat('.',fa.idv) as char(60)) as tree,
         case fa.thisuser
         when 'u' then (select concat(user_group,';', user_id,';', user_name,';', color) from ".USERS." u, ".GROUPS." g where u.uid=fa.ugid and g.id=u.user_group)
         else (select concat(title,';', color) from ".GROUPS." g where g.id=fa.ugid)
         end as info
         FROM ".($forum_id==0?CAT_FORUM." fc,":"")." ".FORUM_ACC." fa, ".FORUMS." ff
         WHERE {$like_forum}  AND fa.typeacc = 'f' and fa.acc_moderator=1 ".($forum_id==0?"and ff.cat_id =fc.cat_id":"")." and fa.idv=ff.forum_id) 
         UNION
         (SELECT  fa.ugid, fa.thisuser,fa.typeacc,
         case fa.typeacc when 'f' then '' else fc.tree end as tree,
         case fa.thisuser
         when 'u' then (select concat(user_group,';', user_id,';', user_name,';', color) from ".USERS." u, ".GROUPS." g where u.uid=fa.ugid and g.id=u.user_group)
         else (select concat(title,';', color) from ".GROUPS." g where g.id=fa.ugid)
         end as info
         FROM ".CAT_FORUM." fc, ".FORUM_ACC." fa
         WHERE {$like_tree} AND fa.typeacc = 'c' and fa.acc_moderator=1 and fa.idv=fc.cat_id) order by 3,2");
      while (($row=$main->db->sql_fetchrow($rs))){$tree_moder[$row['tree']][$row['thisuser'].$row['ugid']]=$row;}
   }
   function gen_forum_moder_view($value){
      global $main;
      if(hook_check(__FUNCTION__)) return hook();
      $ret="";
      switch ($value['thisuser']) {
         case 'u':
            list($user_group,$user_id,$user_name,$color)=explode(';',$value['info']);
            $href=$main->url(array('module'=>'account','do'=>'user','id'=>$value['ugid']));
            $stylecolor=empty($color)?"":"style='color:#{$color}'";
            $ret.=", <a href='{$href}' {$stylecolor}>{$user_name}</a>";
            break;
         case 'g':
            list($user_name,$color)=explode(';',$value['info']);
            $href=$main->url(array('module'=>'top_users','do'=>'group','id'=>$value['ugid']));
            $stylecolor=empty($color)?"":"style='color:#{$color}'";
            $ret.=", <a href='{$href}' {$stylecolor}>{$user_name}</a>";
            break;
      }
      return $ret;
   }
   function forum_list_moderators($tree, $forum_id, $use_tree=false, $show_lang_moder=false,$br=true){
      global $main, $tree_moder;
      if(hook_check(__FUNCTION__)) return hook();
      $used=array();$ret=""; 
      $keyf=$forum_id!=0?".".$forum_id:$tree;
      if(isset($tree_moder[$keyf])){
         foreach ($tree_moder[$keyf]  as $key => $value) {if(!isset($used[$key])){$used[$key]=true;$ret.=gen_forum_moder_view($value);}}
      }
      if($use_tree){
         $ptree=tree_parents($tree);
         foreach ($ptree as $value) {
            if(isset($tree_moder[$value])){
               $keyf=$value;
               foreach ($tree_moder[$keyf]  as $key => $value) {if(!isset($used[$key])){$used[$key]=true;$ret.=gen_forum_moder_view($value);}}
            }
         }
      }
      if(!empty($ret)) $ret=($show_lang_moder?($br?"<br/>":"")."<b>".$main->lang['moderators'].":</b> ":"").substr($ret,2);
      else {
         if($use_tree) $ret=$main->lang['nomoder'];
      }
      return $ret;
   }
   /**
   * массив информации с доступом
   * 
   * @param mixed $row
   */
   function forum_encode_db_to_session($row){
      global $empty_acc, $convert_fields;
      if(hook_check(__FUNCTION__)) return hook();
      $ret=array();
      foreach ($empty_acc as $key => $value){$ret[$convert_fields[$key]]=isset($row[$key])?$row[$key]:0;}
      return $ret;
   }
   /**
   * Обьеденение доступов когда доступ для групп
   * 
   * @param mixed $init
   * @param mixed $append
   */
   function forum_concat_access($init, $append){
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($init)) return $append;
      foreach ($init as $key => $value) {
         $init[$key]=($value<0 OR $append[$key]<0)?-1:(($value OR $append[$key])?1:0);
      }
      return $init;
   }
   /**
   * декодировать информацию о доступе в более широкое представление
   * 
   * @param mixed $access
   */
   function forum_encode_session_access($access = array()){
      global $main, $convert_fields;
      if(hook_check(__FUNCTION__)) return hook();
      if(empty($access) AND isset($_SESSION['forum_access'])) $access = $_SESSION['forum_access'];
      $return=array('fm'=>array(), 'cat'=>array());
      if(!empty($access)){
         $cf=array_flip($access['fields']);
         $forum_acc=&$access['access']['fm'];
         $cat_acc=&$access['access']['cat'];
         foreach ($forum_acc as $key => $value) {
            $return['fm'][$key]=array();
            foreach ($value as $k => $v) {$return['fm'][$key][$cf[$k]]=$v;}
         }
         foreach ($cat_acc as $key => $value) {
            $return['cat'][$key]=array();
            foreach ($value as $k => $v) {$return['cat'][$key][$cf[$k]]=$v;}
         }
      } 
      return $return;
   }

   function forum_check_personal_up_forum($personal, $id_forum, $tree){
      if(hook_check(__FUNCTION__)) return hook();
      $return = false;
      if(isset($personal[$id_forum])) $return = true;
      if(forum_check_personal_up_tree($personal, $tree)) $return = true;
      $a=array($personal, $id_forum, $tree, $return);
      return $return;
   }

   function forum_check_personal_up_tree($personal, $tree){
      if(hook_check(__FUNCTION__)) return hook();
      $return = false;
      while(!empty($tree)){
         if(isset($personal[$tree])) $return = true;
         $tree= substr($tree,0,-2);
      }
      return $return;
   }
   function forum_debug_info(&$debug,$id,$this_forum, $gk, $group){
      global $detail_mode_acc;
      if(hook_check(__FUNCTION__)) return hook();
      if($detail_mode_acc) {
         if($this_forum){
            if(!isset($debug['fm']['mf'.$id])) $debug['fm']['mf'.$id]=array('g','grp'=>array($group=>$gk));
            else $debug['fm']['mf'.$id]['grp'][$group] = $gk;
         } else {
            if(!isset($debug['cat']['mc'.$id])) $debug['cat']['mc'.$id]=array('g','grp'=>array($group=>$gk));
            else $debug['cat']['mc'.$id]['grp'][$group] = $gk;
         }
      }
   }
   /**
   * Загрузка информации о доступе к форумам
   * 
   */
   function forum_full_access_load($return_access = false){
      global $main, $forum, $fileds_acc, $groups, $uid, $empty_acc, $convert_fields, $forum_access, $detail_mode_acc;
      if(hook_check(__FUNCTION__)) return hook();
      $return = array();
      if(!$return_access) $return=isset($_SESSION['forum_access'])?$_SESSION['forum_access']:array('user'=>-1);
      $indexload=isset($forum['change_acc'])?$forum['change_acc']:"";
      if(!isset($return['user']) OR ($return['user']!=$main->user['uid'])) $return=array();
      if(!isset($return['index']) OR $return['index']!=$indexload){
         $return = array('index'=>$indexload, 'access'=>array('fm'=>array(), 'cat'=>array()), 'user'=> $main->user['uid']);
         $convert_fields=array(); $i=0;
         foreach ($empty_acc as $key => $value) {$convert_fields[$key]=$i; $i++;}
         $return['fields']=$convert_fields;
         $debug = array();
         $forum_acc=&$return['access']['fm'];
         $cat_acc=&$return['access']['cat'];
         $main->db->sql_query("select fc.tree, ff.forum_id FROM ".CAT_FORUM." fc, ".FORUMS." ff where ff.cat_id=fc.cat_id");
         $forumsinf=array();
         while (($row=$main->db->sql_fetchrow())) $forumsinf[$row['forum_id']] = $row['tree'];
         $main->db->sql_query("select tree, cat_id FROM ".CAT_FORUM." order by tree");
         $cats=array();
         while (($row=$main->db->sql_fetchrow())) $cats[$row['tree']] = $row['cat_id'];
         $main->db->sql_query("
            (SELECT fc.tree, fa.ugid,fa.thisuser,fa.typeacc,fa.idv,{$fileds_acc} FROM ".CAT_FORUM." fc, ".FORUM_ACC." fa, ".FORUMS." ff
            where fc.cat_id=ff.cat_id AND fa.typeacc = 'f' AND fa.idv = ff.forum_id and ((fa.thisuser='g' and fa.ugid in ({$groups}))  or (fa.thisuser='u' and fa.ugid={$uid}))
            ) union (
            SELECT fc.tree, fa.ugid,fa.thisuser,fa.typeacc,fa.idv,{$fileds_acc} FROM ".CAT_FORUM." fc, ".FORUM_ACC." fa 
            where fa.typeacc = 'c' and fa.idv=fc.cat_id AND ((fa.thisuser='g' and fa.ugid in ({$groups}))  or (fa.thisuser='u' and fa.ugid={$uid}))
            ) order by 1,3 desc,4 desc, 2");
         $tree='';
         $personal=array(); $agroup = array('fm' => array(), 'cat' => array());
         while (($row=$main->db->sql_fetchrow())){
            $thisforum=$row['typeacc']=='f';
            $thisuser=$row['thisuser']=='u';
            $idcf=$row['idv'];
            $tree=$row['tree'];
            if($thisforum AND $thisuser){
               if(!isset($forum_acc[$idcf])) {
                  $forum_acc[$idcf]=forum_encode_db_to_session($row);
                  $debug['fm']['mf'.$idcf]=array('p','pm'=>'mf'.$idcf);
               }
               $personal[$idcf]=true;
            } elseif(!$thisforum AND $thisuser){
               if(!isset($cat_acc[$tree])) {
                  $cat_acc[$tree]=forum_encode_db_to_session($row);
                  $debug['cat']['mc'.$idcf]=array('p','pm'=>'mc'.$idcf);
               }
               $personal[$tree]=true;
            } elseif(!$thisuser AND $thisforum){
               if(!forum_check_personal_up_forum($personal, $idcf, $tree)){
                  if(!isset($agroup['fm'][$idcf])) $agroup['fm'][$idcf]=array();
                  $agroup['fm'][$idcf][$row['ugid']]=forum_encode_db_to_session($row);
               }
            } elseif(!$thisuser AND !$thisforum){
               if(!forum_check_personal_up_tree($personal, $tree)) {
                  if(!isset($agroup['cat'][$tree])) $agroup['cat'][$tree]=array();
                  $agroup['cat'][$tree][$row['ugid']]=forum_encode_db_to_session($row);
               }
            }
         }
         foreach ($agroup['fm'] as $key => $value){
            $g = array_flip(explode(",",$groups));
            if(empty($forum_acc[$key])) $forum_acc[$key] = array();
            foreach ($value as $k => $val) {
               $forum_acc[$key]=forum_concat_access($forum_acc[$key],$val);
               forum_debug_info($debug,$key,true,"mf".$key, $k);
               unset($g[$k]);
            }
            if(!empty($g)){
               $tree = $forumsinf[$key];
               while(!empty($tree)){
                  if(isset($agroup['cat'][$tree])){
                     foreach ($agroup['cat'][$tree] as $k => $val) {
                        if(isset($g[$k])){
                           $forum_acc[$key]=forum_concat_access($forum_acc[$key],$val);
                           forum_debug_info($debug,$key,true,'mc'.$cats[$tree], $k);
                           unset($g[$k]);
                        }
                     }
                  }
                  $tree = substr($tree, 0, -2);
                  if(empty($g)) break;
               }
            }
            if(empty($forum_acc[$key])) unset($forum_acc[$key]);
         }
         foreach ($agroup['cat'] as $key => $value){
            $g = array_flip(explode(",",$groups));
            $tree = $key;
            if(empty($cat_acc[$key])) $cat_acc[$key] = array();
            while(!empty($tree)){
               if(isset($agroup['cat'][$tree])){
                  foreach ($agroup['cat'][$tree] as $k => $val) {
                     if(isset($g[$k])){
                        $cat_acc[$key] = forum_concat_access($cat_acc[$key],$val);
                        forum_debug_info($debug,$cats[$key],false,'mc'.$cats[$tree], $k);
                        unset($g[$k]);
                     }
                  }
               }
               $tree = substr($tree, 0, -2);
               if(empty($g)) break;
            }
            if(empty($cat_acc[$key])) unset($cat_acc[$key]);
         }
         if($detail_mode_acc){
            foreach ($cats as $key => $value) {
               $key_id="mc".$cats[$key];
               if(!isset($debug['cat'][$key_id])){
                  $debug['cat'][$key_id]=array();
                  $tree = $key;
                  while(!empty($tree)){
                     $cat_id="mc".$cats[$tree];
                     if(!empty($debug['cat'][$cat_id])){$debug['cat'][$key_id]=$debug['cat'][$cat_id]; break;}
                     $tree=substr($tree,0,-2);
                  }
               }
            }
            foreach ($forumsinf as $key => $value) {
               $key_id="mf".$key;
               if(empty($debug['fm'][$key_id])){
                  $cat_id="mc".$cats[$value];
                  if(isset($debug['cat'][$cat_id])) $debug['fm'][$key_id]=$debug['cat'][$cat_id];
                  else $debug['fm'][$key_id]=array();
               }
            }
            $return['debug'] = $debug;
         }
         $forum_access_bak = $forum_access;
         $forum_access = forum_encode_session_access($return);
         $return['forum_read']=array();
         foreach ($forumsinf as $key => $value) {
            forum_open_access_forum($value, $key);
            if(check_access_forum(accRead, accView)) $return['forum_read'][]=$key;
         }
         $forum_access = $forum_access_bak;
         if($return_access) return $return;
         $_SESSION['forum_access'] = $return;
      }
      if($return_access) return $return;
      $forum_access = forum_encode_session_access();
   }
   function forum_full_access_load_user($user_id, $ugroup, $ugroups){
      global $uid, $groups;
      if(hook_check(__FUNCTION__)) return hook();
      $uid=$user_id; $groups = user_group_acc_encode($ugroup, $ugroups);
      return forum_full_access_load(true);
   }
   function forum_full_access_load_group($group){
      global $uid, $groups;
      if(hook_check(__FUNCTION__)) return hook();
      $uid=0; $groups=$group;
      return forum_full_access_load(true);
   }
   /**
   * загрузить уровень доступа для ветки категории
   * 
   * @param string $tree
   */
   function forum_open_access_tree($tree){
      global $forum_access, $empty_acc, $acc;
      if(hook_check(__FUNCTION__)) return hook();
      $acc=$empty_acc;
      $stree=$tree;
      while(strlen($stree)>0){
         if(isset($forum_access['cat'][$stree])) {$acc=$forum_access['cat'][$stree]; break;}
         $stree=substr($stree,0,-2);
      }
      return $acc;
   }
   /**
   * загрузить уровень доступа для форума
   * 
   * @param string $tree
   * @param integer $forum_id
   */
   function forum_open_access_forum($tree, $forum_id){
      global $forum_access, $empty_acc, $acc;
      if(hook_check(__FUNCTION__)) return hook();
      $acc=$empty_acc;
      if(!isset($forum_access['fm'][$forum_id])) $acc=forum_open_access_tree($tree);
      else $acc=$forum_access['fm'][$forum_id];
      return $acc;
   }
   /**
   * универсальная проверка на доступ
   * 
   * @param mixed $access
   */
   function check_access_forum($access){
      global $acc;
      if(hook_check(__FUNCTION__)) return hook();
      $superuser = is_admin() OR  ($acc[accModerator]==1);
      if(func_num_args()==1) $acclist =is_array($access)?$access:array($access);
      else $acclist=func_get_args();
      if(!empty($access)){
         $return = true;
         foreach ($acclist as $value) {if($acc[$value]!=1) $return=false;}
         return $return OR $superuser;
      } else return $superuser;
   }
?>
