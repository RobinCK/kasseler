<div class='open_table'>
<div class="base"><div class="heading"><div class="binner">
<div class='pm_navi'>
    <h1>{%TITLE%}</h1>
    <span>{%BACK%}</span>
</div>
</div></div></div>
<div id='pm_options'>
<table width='100%' cellpadding='0' cellspacing='0'>
<tr>
    <td width='50%'>{%SEARCH%}</td>
    <td width='25%' align='left' class='search_close_button'>{%CLOSE_SEARCH%}</td>
    <td align='right' class='message_button'>{%NEW_MESSAGE%}</td>
</tr>
</table>
</div>
<script type="text/javascript">
    $.krReady(function(){
        $('#pm_options').fixFloat();
    });
</script>
<div id='margin_options'>
<div class='search_users_pm'>
</div>
<div class='list_users_pm'>
<!--begin_pm_table-->
<table cellpadding='0' cellspacing='0' width='100%' class='pm_table'>
<tbody>
<!--begin_pm_row-->
<tr class='{%CLASS_ROW%}'>
    <td width='58' style='padding: 5px;'>{%SMALL_AVATAR%}</td>
    <td width='50' nowrap='nowrap' style='padding-right: 15px;' class='sender_pm'>{%SENDER%}<br /><span class='pm_date'>{%DATE%}</span></td>
    <td>{%SUBJECT%}<br />{%SMALL_TEXT_LINK%}<span class='delte_pm'><a href='#'><span><img class='icon icon-close' src='includes/images/pixel.gif' alt='' /></span></a></span></td>
</tr>
<!--end_pm_row-->
</tbody>
</table>
{%PM_ROW_PAGES%}
<!--end_pm_table-->
</div></div>
<script type="text/javascript">$(document).ready(function(){
    $.krReady(function(){
        $('#margin_options').css({'padding-top': $('#pm_options').height()+25});
    });
})</script>
<!--begin_pm_row_nolist-->
<div style='padding: 10px 0 5px 0;'>
<div class="warning info"><div class="binner"><div style='padding-left: 10px;'><ul>{%MESSAGE%}</ul></div></div></div>
</div>
<!--end_pm_row_nolist-->
</div>
