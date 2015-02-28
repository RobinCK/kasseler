{OPEN_TABLE}
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
    <tr> 
        <td align="left" class='forum_menu'><span>{MENU_PROFILE}</span> <span>{MENU_SEARCH}</span> <span>{MENU_USERS}</span></td>
        <td align="right">{MENU_LOGOUT}</td>
    </tr>
</table>
{CLOSE_TABLE}
<div class="iemargin">
<table width="100%">
    <tr><td colspan="3">{FORUM_BREAD_CRUMB}</td></tr>
    <tr><td colspan="3">{MODERATORS}</td></tr>
    <tr> 
    <!--begin showonepost-->
    <td nowrap="nowrap"  width='200'>{POST_NEW_TOPIC} {POST_REPLY_TOPIC}</td>
    <!--end showonepost-->        
    <td></td>
    <!--begin showonepost-->
    <td align='right' nowrap="nowrap"><div style='float: right;'>{PAGINATION}</div></td>
    <!--end showonepost-->
  </tr>
</table>
</div>
{OPEN_TABLE}
<table width='100%' cellspacing="0" cellpadding="0" class='cattable'>
    <tr class='rows2'><td colspan="2" height='22'>{TOPIC_TITLE}<div style="float:right;"><b>{TOPIC_SUBSCRIBE}</b></div></td></tr>
    <tr class='rows2'><td colspan="2" height='22'>{FORUM_VOTE}</td></tr>
    <tr><th width='180' nowrap="nowrap" height='22'>{AUTHOR_POST}</th><th>{MESSAGE_POST}</th></tr>
    <tr><td id='post_table' colspan='2'>
        <!--begin post row-->
        <table width='100%' cellspacing="1" cellpadding="3" class='cattable'>
            <tr class='post{postrow.ROW_CLASS}'>
                <td align='center' width='180'>{postrow.POSTER_NAME}</td>
                <td valign="top"><table width='100%' cellpadding='0' cellspacing='0'><tr><td>{L_POSTED}: {postrow.POST_DATE}&nbsp; &nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</td><td align='right'>{postrow.POST_NUMBER}</td></tr></table></td>
            </tr>
            <tr class='post{postrow.ROW_CLASS}'> 
                <td align="center" valign="top">{postrow.POSTER_AVATAR}<br /><div align='left'>{postrow.RANK_IMAGE}{postrow.POSTER_GROUP}{postrow.COUNTRY}{postrow.USER_NUMBER}{postrow.POSTER_AGE}{postrow.POSTER_POSTS}{postrow.USER_TNX}{postrow.POSTER_JOINED}{postrow.POSTER_STATUS}</div></td>
                <td height="200" valign="top">{postrow.MESSAGE}{postrow.POST_LAST_EDIT}{postrow.SIGNATURE}</td>
            </tr>
            <tr class='post{postrow.ROW_CLASS}'> 
                <td nowrap="nowrap">{BACK_TO_TOP} {postrow.REPORT} {postrow.TNX}</td>
                <td><table width='100%' cellpadding='0' cellspacing='0'><tr><td>{postrow.PROFILE_IMG}</td><td align='right'>{postrow.QUOTE_IMG} {postrow.EDIT_IMG} {postrow.DELETE_IMG} {postrow.IP_IMG}</td></tr></table></td>
            </tr>            
        </table>
        <!--end post row-->
    </td></tr>
</table>
{CLOSE_TABLE}

<div class="iemargin">
<table width="100%">
    <tr> 
        <td nowrap="nowrap"><strong>{L_INDEX} &raquo; {FORUM_NAME}</strong><br />{PAGINATION}</td>
    <!--begin showonepost-->
    <td align="right" nowrap="nowrap">{QUICK_REPLY} {POST_REPLY_TOPIC} {POST_NEW_TOPIC}</td>
    <!--end showonepost-->
    </tr>
</table>
</div>

<!--begin showonepost-->
<!--begin quick_reply-->
<div id='quickreply'>
{OPEN_TABLE}
<form action='{QUICKREPLY_ACTION}' method='post'>
<table width="100%" align="center">
<tr><td width='250' valign='top'>{SMILES_BOX}</td><td valign='top'>{QUICKREPLY_BOX}</td></tr>
<tr><td colspan="2" align='center'><br />{QUICKREPLY_BUTTON}</td></tr>
</table>
</form>
{CLOSE_TABLE}
</div>
<!--end quick_reply-->
{OPEN_TABLE}
<table width="100%" cellspacing="2">
  <tr>
        <td align="left" valign="top" height='22'>{PAGE_NUMBER}</td>
        <td align="right" valign="top" nowrap="nowrap" rowspan="2">{S_AUTH_LIST}</td>
  </tr>
  <tr> 
    <td width="40%" valign="top" nowrap="nowrap" align="left">{S_TOPIC_ADMIN}</td>
  </tr>
</table>
{CLOSE_TABLE}
<!--end showonepost-->