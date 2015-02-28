<?php
if(!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция вызова хука
* 
* @return mixed
*/
function hook($retArray=false) {
global $main;
    $info = debug_backtrace();
    $info = is_array($info[1])?$info[1]:$info;
    //$GLOBALS = array();
    $info['function'] = isset($info['class']) ? $info['class'].'::'.$info['function'] : $info['function'];
    $lastf="";        
    $initv=$main->hooks_info[$info['function']]['level'];
    if(hook_check_h($info['function'])){
        $return = false;
        for($i=$initv;$i<hook_count($info['function']);$i++){
            if(!$main->hooks_info[$info['function']]['next']) continue;
            $lastf=$main->hooks[$info['function']][$i]['function_name'];
            $use = $main->hooks[$info['function']][0]['use'] = true;
            $main->hooks_info[$info['function']]['level']=$main->hooks_info[$info['function']]['level']+1;
            $main->hooks_info[$info['function']]['next']=false;
            $parent_call = $main->hooks[$info['function']][$i]['parent_call'] == true;
            $parent_return = $parent_call ? call_user_func_array($info['function'], $info['args']) : '';
            $new_arg=$parent_call?array_merge(array($parent_return), $info['args']):$info['args'];
            $return = call_user_func_array($main->hooks[$info['function']][$i]['function_name'], $new_arg);
            $main->hooks_info[$info['function']]['results'][$i]=$return;
            $use = $main->hooks[$info['function']][0]['use'] = false;
            break;
        }
        if($initv==0){
           $r=$main->hooks_info[$info['function']]['results'];
           $main->hooks_info[$info['function']]=array('level'=>0,'next'=>true,'results'=>array());
           if($retArray)  return $r;
           else return array_pop($r);
        }
        return $return;
    } else return HOOK_NOT_FOUND;
}

function hook_count($function){
global $main;
    return count($main->hooks[$function]);
}

function hook_check_h($function, $num=0){
   global $main;
   return isset($main->hooks[$function]) AND isset($main->hooks[$function][$num]) AND function_exists($main->hooks[$function][$num]['function_name']);
}

/**
* Функция проверки хука
* 
* @param string $function Contains __FUNCTION__ OR __METHOD__ 
* @return bool
*/
function hook_check($function, $num=0){
   global $main;
   if(isset($main->hooks[$function]) AND $main->hooks_info[$function]['level']>0) $main->hooks_info[$function]['next']=true;
   return isset($main->hooks[$function]) AND isset($main->hooks[$function][$num]) AND function_exists($main->hooks[$function][$num]['function_name']) AND ($main->hooks[$function][0]['use']==false OR $main->hooks_info[$function]['level']<count($main->hooks[$function]));
}

/**
* Функция регистрации хука
* 
* @param string $name Название функции родителя
* @param string $hook Название функции хука
* @param bool $call Флаг вызова функции родителя
* @return void
*/
function hook_register($name, $hook, $call=false){
global $main;
    $main->hooks[$name][] = array('function_name' => $hook, 'parent_call' => $call, 'use' => false);
    $main->hooks_info[$name]=array('level'=>0,'next'=>true,'results'=>array());
}

/**
* Функция удаления хука
* 
* @param string $name Название функции родителя
* @param string $hook Название функции хука
* @return void
*/
function hook_unregister($name, $hook){
global $main;
    foreach($main->hooks[$name] as $k=>$v){
        if($main->hooks[$name][$k]['function_name']==$hook) unset($main->hooks[$name][$k]);
    }
    if(empty($main->hooks[$name])) {
        unset($main->hooks[$name]);
        unset($main->hooks_info[$name]);
    }
}
?>
