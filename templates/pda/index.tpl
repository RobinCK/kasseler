<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
	<title>$title</title>
	$meta
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	$link
	<!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->   

	<link rel="shortcut icon" href="templates/$load_tpl/images/favicon.ico">
	<link rel="apple-touch-icon" href="templates/$load_tpl/images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="templates/$load_tpl/images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="templates/$load_tpl/images/apple-touch-icon-114x114.png">
    
    $script
    <!--[if lt IE 9]>
        <script src="templates/$load_tpl/js/css3-mediaqueries.js"></script>
    <![endif]-->   
    <script type="text/javascript">
(function($){
    jQuery.fn.responsiveTable = function(){
        var Self = $(this);
        var defCol = Self.find('tr:first td');
        var td = Self.find('tr > td');
        var tdWidth=Self.find('td:first').width();
        
        var resize = function(){
            var parentW = Self.parent().width();
            if(parentW<Self.width() || tdWidth*(Self.find('tr:first td').length+1)<parentW){
                Self.find('tr').remove();
                var colCount=Math.floor(parentW/tdWidth);
                var rows=Math.ceil(td.length/colCount);
                t = 0;
                for(var y=0;y<rows;y++){
                    Self.append('<tr></tr>');
                    for(var i=0;i<colCount;i++){
                        if(!td[t]) break;
                        Self.find('tr:last').append(td[t]);
                        t++;
                    }
                }
            }
        }
        $(window).bind('resize', resize);
        $(window).trigger('resize');
    };
})(jQuery);

$(document).ready(function(){
    setTimeout(function(){
        $('.table_showcat').responsiveTable();
        $('.table_showphoto').responsiveTable();
    }, 10);
});
    
        $(function() {
            var pull         = $('#pull');
                menu         = $('nav ul');
                menuHeight    = menu.height();

            $(pull).on('click', function(e) {
                e.preventDefault();
                menu.slideToggle();
            });

            $(window).resize(function(){
                var w = $(window).width();
                if(w > 320 && menu.is(':hidden')) {
                    menu.removeAttr('style');
                }
            });
        });
        $.krReady(function(){
            $.DOMlive.reg('button,input[type="submit"],input[type="reset"],input[type="button"],.linkbutton,.subscribe,.forum_button', function(){
                $(this).addClass('button');
            });
            $('.srccrumbs').append('<div class="buttonbc"><span class="label"><a href="#" onclick="return false;">&nbsp;</a></span><span class="arrow"><span></span></span></div>');
            
        });
    </script>
</head>
<body>

	<!-- Delete everything in this .container and get started on your own site! -->

	<div class="container">
        <nav class="clearfix">
            <ul class="clearfix">
                <?php
                    echo "<li><a id='menuhead_home' class='".(is_home()?"ac":"noac")."' href='http://".get_env('HTTP_HOST')."' title='{$lang['home']}'><b>{$lang['home']}</b></a></li>
                    <li><a id='menuhead_account' class='".(($module_name=='account' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'account'))."' title='{$lang['account']}'><b>{$lang['account']}</b></a></li>
                    <li><a id='menuhead_news' class='".(($module_name=='news' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'news'))."' title='{$lang['news']}'><b>{$lang['news']}</b></a></li>
                    <li><a id='menuhead_contact' class='".(($module_name=='contact' AND !is_home())?"ac":"noac")."' href='".$main->url(array('module'=>'contact'))."' title='{$lang['contact']}'><b>Контакты</b></a></li>";
                ?>
            </ul>
            <a href="#" id="pull">&nbsp;</a>
        </nav>
        <div class='logo'><a href='http://www.kasseler-cms.net/'><img src='templates/$load_tpl/images/logo.png' alt='Kasseler CMS' /></a></div>
        <div class='clearfix'>
            $bread_crumbs
        </div>
        
        <!--BEGIN leftblock-->
		<div class="four columns blocks_column">
            <div class='grey_back'>
                <div class='margin-content'>
			        $block_left
                </div>
            </div>
		</div>
        <!--END leftblock-->
		<div class="<?php global $modules, $main; if($modules[$main->module]['blocks']==0) echo 'nine'; elseif($modules[$main->module]['blocks']==1 OR $modules[$main->module]['blocks']==2) echo 'kasb12';  else echo 'kasb3';?> columns">
            <div class='margin-content'>
			    $message $block_center $modules $block_down
            </div>
		</div>
        <!--BEGIN rightblock-->
        <div class="four columns four-right blocks_column">
            <div class='grey_back'>
                <div class='margin-content'>
                    $block_right
                </div>
            </div>
        </div>
		<!--END rightblock-->
		<div class="kasb3 columns copyright">
            <footer>$license</footer>
        </div>
	</div>
    <div class='container var_info'>
        $var_info
        $query_info
    </div>

</body>
</html>