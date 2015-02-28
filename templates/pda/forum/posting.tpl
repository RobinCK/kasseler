<div class='forum_head_menu'>
    <span>{MENU_PROFILE}</span> <span>{MENU_SEARCH}</span> <span class='topic_col_hide'>{MENU_USERS}</span>
    <span class='forum_logout'>{MENU_LOGOUT}</span>
</div>

{OPEN_TABLE}
{posting.TOPIC}
{CLOSE_TABLE}   

{OPEN_TABLE}
{posting.MSG}
<!--content-->

<form method='post' action='{posting.ACTION}'>    
    <table width="100%">
        <tr>
            <td valign='top'>
                <div class='box'>
                    <table width='400' class='adaptive_form_768'>
                        <tr>
                            <td width='150'><b>{L_POSTING_TITLE}</b>:</td>
                            <td>{posting.TITLE}</td>
                        </tr>
                        <!--onlytopic-->
                        <tr>
                            <td width='100'><b>{L_POSTING_DESC}</b>:</td>
                            <td>{posting.DESC}</td>
                        </tr>
                        <!--onlytopic-->
                        <!--onlyfirstpost-->
                        <tr>
                            <td width='200'><b>{L_POSTING_FIXED}</b>:</td>
                            <td>{posting.FIXED}</td>
                        </tr>
                        <!--onlyfirstpost-->
                    </table>
                </div>
            </td>
        </tr>
        <!--message box-->
        <tr>
            <td valign='top'>
                <table cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td width='250' valign='top' class='topic_col_hide'>{posting.SMILES}</td>
                    <td valign='top'>{posting.EDITOR}</td>
                </tr>
            </table>
            </td>
               </tr>
               <!--message box-->
    <!--onlyadmin-->
    <tr><td><div class='box'><h3>{L_POSTING_TYPE}</h3>{posting.CASE_TYPE}</div></td></tr>
    <!--onlyadmin-->
    <tr><td><div class='box'>{L_POSTING_VOTING}</div></td></tr>
    <tr><td><div class='box adaptive_form_460'><h3>{L_POSTING_ICO}</h3>{posting.CASE_ICO}</div></td></tr>
    
    <tr><td>{posting.ATTACH}</td></tr>
    
    <tr><td colspan='2' align='center'><br />{posting.SUBMIT}</td></tr>
</table>
</form>
<!--content-->
{CLOSE_TABLE}