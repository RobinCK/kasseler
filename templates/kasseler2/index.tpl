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
<div class="loginform">
<?php
if(!is_user())
echo "<form action='index.php?module=account&amp;do=sign' method='post'>
<div class='login'><span>{$lang['login']}:</span><div class='input'><input type='text' name='user_name' value='' /></div></div>
<div class='pass'><span>{$lang['password']}:</span><div class='input'><input type='password' name='user_password' value='' /></div></div>
<input title='{$lang['login']}' class='btn' onclick='submit();' onmouseover=\"this.className='btnhover'\" onmouseout=\"this.className='btn'\" value='{$lang['login']}' type='image' src='templates/$load_tpl/images/spacer.png' />
<div class='clr'></div>
</form>";
else echo "<span class='hello_user'>{$lang['hello']}, <a href='".$main->url(array('module' => 'account'))."' title=''>{$userinfo['user_name']}</a></span>";
?>
</div>
<div class="topicons">
<a style="float: left;" target='_BLANK' href="index.php?module=news&amp;do=rss" title="RSS"><img src="templates/$load_tpl/images/rss_icon.gif" alt="RSS" /></a> 
<a style="float: left;" href="$module[search]" title="$lang[search]"><img src="templates/$load_tpl/images/search_icon.gif" alt="$lang[search]" /></a>
</div></div></div></div>
<div id="tophead"><div class="dleft"><div class="dright">
<span class="headicons">$topbaner</span><a class="kasselerlogo kasseler-cms" href="http://www.kasseler-cms.net/"><img src="templates/$load_tpl/images/kasseler-cms.png" alt="Kasseler CMS" title="Kasseler CMS" /></a>
</div></div></div>
<div id="menuhead2"><div class="dleft"><div class="dright"><span>
<?php
echo "<a id='menuhead_home' class='".(is_home()?"ac":"noac")."' href='http://".get_env('HTTP_HOST')."' title='{$lang['home']}'><b>{$lang['home']}</b></a>
<a id='menuhead_account' class='".(($module_name=='account' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'account'))."' title='{$lang['account']}'><b>{$lang['account']}</b></a>
<a id='menuhead_news' class='".(($module_name=='news' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'news'))."' title='{$lang['news']}'><b>{$lang['news']}</b></a>
<a id='menuhead_recommend' class='".(($module_name=='recommend' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'recommend'))."' title='{$lang['recommend']}'><b>{$lang['recommend']}</b></a>
<a id='menuhead_contact' class='".(($module_name=='contact' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'contact'))."' title='{$lang['contact']}'><b>{$lang['contact']}</b></a>";
?>
</span></div></div></div>
<script type='text/javascript'>
<!--
//var mids = ['menuhead_home', 'menuhead_account', 'menuhead_news', 'menuhead_recommend', 'menuhead_contact'], active = '';
var mids = ['menuhead_home', 'menuhead_account', 'menuhead_news'], active = '';
$('.ac').each(function(){active=this.id;})
$('#menuhead2').on('mouseover','a',function(){$('#'+mids.join(',#')).attr('class','noac');$(this).attr('class','ac');
}).on('mouseout','a',function(){if($$(active)) $$(active).className='ac'; if(active!=this.id) this.className='noac';
}).on('mousedown','a',function(){active=this.id;});
// -->
</script>
<div id="bodypage"><div class="dleft"><div class="dright"><div class="container">
<table width='100%' cellpadding="0" cellspacing="0">
<tr>
<td id='leftcol' valign='top' style='display: none;'>$block_left</td>
<td valign='top' id="maincol">$message $block_center <!--$bread_crumbs--> $modules $block_down</td>
<td id='rightcol' valign='top' style='display: none;'>$block_right</td>
</tr>
</table>
</div></div></div></div>
<div id="footer_us"><div class="dleft"><div class="dright">
<div class="count">$footbaner</div>
<!--copyright-->
<span class="copyright">$time<br /><b>$license</b></span>
<div class="clr"></div>
</div></div></div>
<table width='100%'><tr><td valign='top'>$var_info $query_info</td><td valign='top'><div class="centroarts"><a href="#" onclick="location.href='http'+'://validator.w3.org/check?uri=referer'; return false;" title="XHTML Validation"><img src="templates/$load_tpl/images/xhtml.png" alt="XHTML Validation" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="location.href='http'+'://jigsaw.w3.org/css-validator/check/referer'; return false;" title="CSS Validation"><img src="templates/$load_tpl/images/css.png" alt="CSS Validation" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="location.href='http'+'://centroarts.com/'; return false;" title="Designed by CENTROARTS.com"><img src="templates/$load_tpl/images/centroarts_com.png" alt="Designed by CENTROARTS.com" /></a></div></td></tr></table>
</div>
$footer
</body>
</html>