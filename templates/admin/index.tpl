<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>$title</title>
$meta 
$link  
</head>
<body class="page_bg">
$script  
<div class="wrapper">
<div id="toptop"><div class="dleft"><div class="dright">
<div class="newversion" id="ver_chk">$ver_info</div>
<div class="viewsite"><a href="http://$host/" title="Просмотр сайта" target="_BLANK"><b>$lang[site_show]</b> | $host</a></div>
</div></div></div>
<div id="tophead"><div class="dleft"><div class="dright">
<a class="kasseler-cms" href="http://www.kasseler-cms.net"><img src="templates/$load_tpl/images/kasseler-cms.png" alt="Kasseler CMS" title="Kasseler CMS" /></a> 
<span class="headiconadmin">
<a href="$adminfile?module=blocks&amp;do=add" title="$lang[add_block]"><img src="templates/$load_tpl/images/iks_addblock.png" alt="$lang[add_block]" /></a> 
<a href="$adminfile?module=news&amp;do=add" title="$lang[create_news]"><img src="templates/$load_tpl/images/iks_addnews.png" alt="$lang[create_news]" /></a> 
<a href="$adminfile?module=config" title="$lang[configuratin]"><img src="templates/$load_tpl/images/iks_setting.png" alt="$lang[configuratin]" /></a> 
<a href="$adminfile?module=logout" title="$lang[logout]"><img src="templates/$load_tpl/images/iks_exit.png" alt="$lang[logout]" /></a>
</span>
</div></div></div>
<div id="menuhead"><div class="dleft"><div class="dright">
<span>$main_menu</span>
<div class="help">$help</div>
</div></div></div>
<div id="menuheadline"><div class="dinner"><div class="dleft"><div class="dright">
<div id='menu_js'>$sub_menu</div>
</div></div></div></div>
<div id="bodypage"><div class="dleft"><div class="dright">
<div class="container">
<div id="maincol">$content</div>
<div id="leftcol">
$block_modules 
$block_info 
$blocks
</div>
<div class="clr"></div>
</div></div></div></div>
<div id="footer"><div class="dleft"><div class="dright">
<a class="infoicon" href="link.html" title="info"><img src="templates/$load_tpl/images/infoicon.gif" alt="info" /></a> <span class="copyright">Copyright <a href="http://www.kasseler-cms.net"><b>© 2007-<?php echo date('Y'); ?> by Kasseler CMS.</b></a> All rights reserved</span>
<div class="clr"></div>
</div></div></div>
</div>
</body>
</html>
