{OPEN_TABLE}
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
    <tr> 
        <td align="left" class='forum_menu'><span>{MENU_PROFILE}</span> <span>{MENU_SEARCH}</span> <span>{MENU_USERS}</span></td>
        <td align="right">{MENU_LOGOUT}</td>
    </tr>
</table>
{CLOSE_TABLE}
{OPEN_TABLE}
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
    <tr> 
        <td align="left" valign="bottom">
            {LAST_VISIT_DATE}<br />
            {CURRENT_TIME}<br />
            <b>{L_INDEX}</b>
        </td>
        <td align="right" valign="bottom">
            {L_SEARCH_SELF}<br />
            {L_SEARCH_UNANSWERED}<br />
            {L_SEARCH_NEW}
        </td>
    </tr>
</table>
{CLOSE_TABLE}
{MSG}
{OPEN_TABLE}
<form action='{SEARCH_ACTION}' method='{SEARCH_METHOD}'>
    {HIDE_INPUTS}
    <table cellpadding='0' width='100%' class='form'>
        <tr><th colspan='2' nowrap="nowrap" height='25'>{LANG_SEARCH}</th></tr>
        <tr class='row_tr'>
            <td class='form_text2' width='50%'>{SEARCH_KEY}</td>
            <td class='form_input'>{INPUT_STRING}<br />{TYPE_SERCHES}</td>
        </tr>
        <tr class='row_tr'>
            <td class='form_text2'>{SEARCH_AUTHOR}</td>
            <td class='form_input'>{INPUT_AUTHOR}</td>
        </tr>
        <tr class='row_tr'>
            <td class='form_text2'>{SEARCH_FORUM}</td>
            <td class='form_input'>{FORUM_LIST}</td>
        </tr>
        <tr>
            <td colspan='2' align='center'><br /><input type='hidden' name='search' value='search' /><input type='submit' value='{LANG_SEARCH}' /><br /><br /></td>
        </tr>
    </table>
</form>
{CLOSE_TABLE}