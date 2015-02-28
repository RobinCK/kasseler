<?php
if (!defined('KASSELERCMS')) die('Access is limited');
    function rating_class($row){
        global $main;
        if(hook_check(__FUNCTION__)) return hook();
        if(isset($row['users']) AND preg_match("/,{$main->user['uid']};(.),/", $row['users'], $regs)) {
            return $regs[1]=='+'?"r_rs_up":"r_rs_down";
        } else return "";
    }
    function get_result_rating($r_up, $r_down){
        global $main;
        if(hook_check(__FUNCTION__)) return hook();
        $all=$r_down+$r_up;
        $curr=$r_up-$r_down;
        $class=$curr>=0?"r_good":"r_bed";

        if($curr<0) $curr = str_replace('-', '', $curr);
        return "<span class='r_result {$class}' title='{$main->lang['votes']} ({$all}) : +{$r_up} ~ -{$r_down}'>{$curr}</span>";
    }
       
    function rating_modify_publisher($id,$module,$row, $pub, $enable=true){
       global $config;
       if(hook_check(__FUNCTION__)) return hook();
       $r_up=!empty($row['r_up'])?$row['r_up']:0;
       $r_down=!empty($row['r_down'])?$row['r_down']:0;
       if((isset($config['ratings']))&&($config['ratings']!=ENABLED)) $enable = false;
       if($enable){
          $pub['r_id']=$id;
          $pub['r_module']=$module;
          $pub['rating_result']=get_result_rating($r_up, $r_down);
          $pub['r_class'] = is_guest()?'disable':rating_class($row);
       } else {
          $pub['r_id']=$id;
          $pub['r_module']=$module;
          $pub['rating_result']="";
          $pub['r_class']='disable';
       }
       return $pub;
    }
?>
