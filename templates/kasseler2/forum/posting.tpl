<div class="iemargin">
{posting.TOPIC}
    
</div>
{OPEN_TABLE}
{posting.MSG}
<table width="100%">
    <tr> 
        <td class='path_style'><b>{L_INDEX}</b> &raquo; <b>{FORUM_NAME}</b></td>
    </tr>
</table>
<!--content-->

<form method='post' action='{posting.ACTION}'>    
    <table width="100%">
        <tr>
            <td valign='top'>
                <div class='box'>
                    <table width='400'>
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
                    <td width='250' valign='top'>{posting.SMILES}</td>
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
    <tr><td><div class='box'>{L_POSTING_ICO}{posting.CASE_ICO}</div></td></tr>
    
    <tr><td><div class='box'>{posting.ATTACH}</div></td></tr>
    
    <tr><td colspan='2' align='center'><br />{posting.SUBMIT}</td></tr>
</table>
</form>
<!--content-->
{CLOSE_TABLE}