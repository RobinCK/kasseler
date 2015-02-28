<?php
if(!defined('UPDATECMS')) die('Update access is limited');
global $main;

if(is_writable('includes/config/config_messages.php')){
    $_config = "<?php\n/**********************************************/\n/* Kasseler CMS: Content Management System    */\n/**********************************************/\n/*                                            */\n/* Copyright (c)2007-2010 by Igor Ognichenko  */\n/* http://www.kasseler-cms.net/               */\n/*                                            */\n/**********************************************/\nif (!defined('FUNC_FILE')) die('Access is limited');\n\n$"."messages = array(\n";
    $result = $main->db->sql_query("SELECT * FROM ".MESSAGE." ORDER BY pos");
    if($main->db->sql_numrows($result)>0){
        while(($row = $main->db->sql_fetchrow($result))){
            $_config .= "    array('id' => '{$row['id']}', 'title' => '".addslashes($row['title'])."', 'content' => '".addslashes($row['content'])."', 'groups' => '{$row['groups']}', 'status' => '{$row['status']}', 'pos' => '{$row['pos']}', 'tpl' => '{$row['tpl']}'),\n";
        }    
        file_write("includes/config/config_messages.php", mb_substr($_config, 0, mb_strlen($_config)-2)."\n);\n?".">");
    } else file_write("includes/config/config_messages.php", $_config."\n);\n?".">");
    echo "<span style='color: green;'>Fixed config file <b>config_messages.php</b></span>";
} else echo "<span style='color: red;'>Config file <b>config_messages.php</b> is not writable! Please resave the configuration file messages!</span>";

?>