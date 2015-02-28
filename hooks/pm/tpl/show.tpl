<div class='open_table main_pm_con'>
<div class="base"><div class="heading"><div class="binner">
<div class='pm_navi'>
    <h1>{%TITLE%}</h1>
    <span>{%BACK%}</span>
</div>
</div></div></div>
<div id='pm_options'>
<table width='100%' cellpadding='0' cellspacing='0'>
<tr>
    <td class='message_button'>{%NEW_MESSAGE%} {%DELETE%}</td>
</tr>
</table>
</div>
<div id='margin_options'>
<div class='padding_editor'>
<table cellpadding='0' cellspacing='0' width='100%' class='pm_message_table'>
<tbody>
<!--begin_pm_row-->
<tr class='{%CLASS%}'>
    <td rowspan="2" valign="top" width='58' style='padding: 5px;' align="center">{%MINI_AVATAR%}</td>
    <td><b>{%SENDER%}</b></td>
    <td width="120" align="right"><span class='pm_date'>{%DATE%}</span></td>
</tr>
<tr class='pm_message {%CLASS%}'>
    <td colspan="2" valign="top">{%TEXT%}<input type='hidden' value='{%MESGAE_ID%}' class='message_id' /></td>
</tr>
<!--end_pm_row-->
</tbody>
</table>
</div>
{%ERROR%}
<form method="post" action="{%ACTION%}" class='message_post'>
<table align='center' width="100%" cellpadding="0" cellspacing="0" class='pm_create_table form'>
    <tr>
        <td class='form_input' colspan="2">{%EDITOR%}</td>
    </tr>
    <tr>
        <td><div style="text-align: left;">{%ATTACH%}</div></td>
        <td><div style="text-align: right;">{%SUBMIT%}</div></td>
    </tr>
</table>
{%RECIPIENT%}
{%SUBJ%}
</form>
</div>
</div>
<script type="text/javascript">
    function editor_setup(){        
        $('.pm_create_table').css({left:$('.main_pm_con').offset().left, width:$('.main_pm_con').width()});
        
    }
    $.krReady(function(){
        $('#margin_options').css({'padding-top': $('#pm_options').height()+30});
        $('#pm_options').fixFloat();
        editor_setup();        
        setTimeout(function(){
            $('.pm_create_table').fixedEd();
            $('.padding_editor').css({'padding-bottom':$('.pm_create_table').height()});
        }, 100);
    });
    

    
jQuery.fn.fixedEd = function(){
    var tbh = $(this);
    var scroll = function(){
        pos = $('.main_pm_con').offset().top+$('.main_pm_con').height();
        if(pos<=parseInt($(window).scrollTop(), 10)+$(window).height()) tbh.css({position:'absolute', top: pos-tbh.height()});
        else tbh.css({position:'fixed', top: $(window).height()-$('.pm_create_table').height()});
    }
    $(window).scroll(scroll);
    scroll();
};
    
    
</script>