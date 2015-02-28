<?php
if(!defined('FUNC_FILE')) die('Access is limited');

if(!empty($_POST['data'])){
    $json = json_decode(stripcslashes($_POST['data']), true);
    $result = array();
    if(!empty($json)) foreach($json as $key=>$value) {
        $result[$key] = call_user_func('data_checking', $key, $value['vars']);
    }
    
    echo json_encode($result);
}    
?>
