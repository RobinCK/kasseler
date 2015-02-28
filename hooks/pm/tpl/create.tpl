<div class="base"><div class="heading"><div class="binner">
<div class='pm_navi'>
    <h1>{%TITLE%}</h1>
    <span>{%BACK%}</span>
</div>
</div></div></div>
{%ERROR%}
<form method="post" action="{%ACTION%}">
<table align='center' width="100%" cellpadding="0" cellspacing="0" class='pm_create_table form'>    
    <tr class='set_placeholder'>
        <td class='form_input'><div style="display:none;">{%LANG_SUBJ%}:</div>{%SUBJ%}</td><td class='form_input' style='width: 150px; padding-right: 20px;'><div style="display:none;">{%LANG_RECIPIENT%}:</div>{%RECIPIENT%}</td>
    </tr>
    <tr>
        <td class='form_input' colspan="2">{%LANG_MESSAGE%}:<br />{%EDITOR%}</td>
    </tr>
    <tr>
        <td><div style="text-align: left;">{%ATTACH%}</div></td>
        <td><div style="text-align: right;">{%SUBMIT%}</div></td>
    </tr>
</table>
</form>
<script type="text/javascript">
    if(testAttribute("input", "placeholder")) {
        $('.set_placeholder td').each(function(){
            $(this).find('input').attr('placeholder', $(this).find('div').text().replace(':', ''));
        });
    } else $('.set_placeholder div').show();
</script>
</div>