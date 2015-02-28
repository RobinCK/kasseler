<?php
if(!defined('FUNC_FILE')) die('Access is limited');

/**
* Функция возвращает знак зодиака указанной даты
* 
* @param string $date
* @return array
*/

/*
lobal $img, $main;
if(hook_check(FUNCTION)) return hook();
if(empty($date) OR $date=='0000-00-00') return $main->lang['noinfo'];
$zodiac = array(
//NAME DATE
array('capricorn', "22-12"),
array('sagittarius', "23-11"),
array('scorpio', "24-10"),
array('libra', "24-09"),
array('virgo', "24-08"),
array('leo', "23-07"),
array('cancer', "22-06"),
array('gemini', "22-05"),
array('taurus', "21-04"),
array('aries', "22-03"),
array('pisces', "21-02"),
array('aquarius', "21-01")
);
    for($i=0; $i<count($zodiac); $i++) {
        if((mb_substr($date, 5, 2) == mb_substr($zodiac[$i][1], 3, 2))){
            if(mb_substr($date, 8, 2) >= mb_substr($zodiac[$i][1], 0, 2)){
                $_zodiac_name = $zodiac[$i][0];
            } else {
                if($i == 11){
                    $_zodiac_name = $zodiac[0][0];
                } else {
                    $_zodiac_name = $zodiac[$i+1][0];
                }
            }
        }
    }
    $zodiac_name = $main->lang[$_zodiac_name];
    $zodiac_img = "&lt;img src='includes/images/zodiac/{$_zodiac_name}.gif' title='{$zodiac_name}' alt='{$zodiac_name}' /&gt;";
    return array($zodiac_name, $zodiac_img);
} 
*/
function get_zodiak($date){
global $img, $main;
    if(hook_check(__FUNCTION__)) return hook();
    if(empty($date) OR $date=='0000-00-00') return $main->lang['noinfo'];
    $zodiac = array(
        //NAME                   DATE
        array('capricorn',   "22-12"),
        array('sagittarius', "23-11"),
        array('scorpio',     "24-10"),
        array('libra',       "24-09"),
        array('virgo',       "24-08"),
        array('leo',         "23-07"),
        array('cancer',      "22-06"),
        array('gemini',      "22-05"),
        array('taurus',      "21-04"),
        array('aries',       "22-03"),
        array('pisces',      "21-02"),
        array('aquarius',    "21-01")
    );
    for($i=0; $i<count($zodiac); $i++) {
        if((mb_substr($date, 5, 2) == mb_substr($zodiac[$i][1], 3, 2))){
            if((mb_substr($date, 8, 2) >= mb_substr($zodiac[$i][1], 0, 2))){
                $_zodiac_name = $zodiac[$i][0];
            } else {
                if($i == 11) $_zodiac_name = $zodiac[0][0];
                else $_zodiac_name = $zodiac[$i+1][0];
            }
        }
    }
    $zodiac_name = $main->lang[$_zodiac_name];
    $zodiac_img = "<img src='includes/images/zodiac/{$_zodiac_name}.gif' title='{$zodiac_name}' alt='{$zodiac_name}' />";
    return array($zodiac_name, $zodiac_img);
}
?>
