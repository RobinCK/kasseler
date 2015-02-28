{OPEN_TABLE}
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
    <tr> 
        <td align="left" class='forum_menu'><span>{MENU_PROFILE}</span> <span>{MENU_SEARCH}</span> <span>{MENU_USERS}</span></td>
        <td align="right">{MENU_LOGOUT}</td>
    </tr>
</table>
{CLOSE_TABLE}
{OPEN_TABLE}
<table width="100%" cellspacing="2" cellpadding="2">
    <tr> 
        <td align="left" valign="bottom">{FORUM_BREAD_CRUMB}<b>{L_MODERATOR}: {MODERATORS}</b></td>
        <td align="right" valign="bottom">{L_MARK_TOPICS_READ}</td>
    </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2">  
    <tr> 
        <td align="left" valign="middle" width="100">{POST_NEW_TOPIC}</td>
        <td align="left" valign="middle"><strong>{L_INDEX} &raquo; {FORUM_NAME}</strong></td>
        <td align="right" valign="bottom" nowrap="nowrap"><div style='float: right;'>{PAGINATION}</div></td>
    </tr>
</table>
{CLOSE_TABLE}
<!--begin showtopic row-->
{OPEN_TABLE}
<table cellpadding="4" cellspacing="1" width="100%" class='cattable'>
    <tr> 
        <th colspan="3" align="center" height="25" nowrap="nowrap">{L_TOPICS}</th>
        <th width="50" align="center" nowrap="nowrap">{L_REPLIES}</th>
        <th width="100" align="center" nowrap="nowrap">{L_AUTHOR}</th>
        <th width="50" align="center" nowrap="nowrap">{L_VIEWS}</th>            
        <th width="150" align="center" nowrap="nowrap">{L_LASTPOST}</th>
    </tr>        
  <!--begin topic row-->
    <tr class='{topicrow.ROW_CLASS}'> 
    <td class='col2' width="40" align="center"><img src="{topicrow.TOPIC_FOLDER_IMG}" alt="{topicrow.L_TOPIC_FOLDER_ALT}" title="{topicrow.L_TOPIC_FOLDER_ALT}" /></td>
    <td class='col' width='15' align="center">{topicrow.TOPIC_ICO}</td>
    <td class='col2'>{topicrow.TOPIC_TITLE}<br /><span class='desc'>{topicrow.TOPIC_DESC}&nbsp;</span><br />{topicrow.GOTO_PAGE}</td>
    <td class='col' align="center" valign="middle">{topicrow.REPLIES}</td>
    <td class='col2' align="center" valign="middle">{topicrow.TOPIC_AUTHOR}</td>
    <td class='col' align="center" valign="middle">{topicrow.VIEWS}</td>
    <td class='col2' align="center" valign="middle" nowrap="nowrap">{topicrow.LAST_POST_TIME}<br />{topicrow.LAST_POST_AUTHOR} {topicrow.LAST_POST_IMG}</td>
  </tr>    
  <!--end topic row-->
</table>
{CLOSE_TABLE}
<!--end showtopic row-->
{OPEN_TABLE}
<table width="100%" cellspacing="2" cellpadding="2">
    <tr> 
        <td align="left" valign="middle" width="100">{POST_NEW_TOPIC}</td>
        <td align="left" valign="middle"><strong>{L_INDEX} &raquo; {FORUM_NAME}</strong></td>
        <td align="right" valign="middle">{QUICK_LINK}</td>
    </tr>
    <tr>
        <td align="left" colspan="2">{PAGE_NUMBER}</td>
    <td align='right'>{QUICK_SORT}</td>
    </tr>
</table>
{CLOSE_TABLE}
{OPEN_TABLE}
<table cellpadding="4" cellspacing="1" width="100%" class='cattable'>
  <tr> 
    <th nowrap="nowrap" style='text-align: left;'>{SHOWS_FORM}</th>
  </tr>    
  <tr class='rows1'> 
    <td>{LOGINED_USER_LIST}</td>    
  </tr>  
</table>
{CLOSE_TABLE}
{OPEN_TABLE}
<table width="100%" cellspacing="0" align="center" cellpadding="0">
    <tr>
        <td align="left" valign="top"><table cellspacing="3" cellpadding="2">
            <tr>
                <td width="30" align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_new_big.png" alt="{L_NEW_POSTS}" /></td><td>{L_NEW_POSTS}</td>
                <td width="30" align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_big.png" alt="{L_NO_NEW_POSTS}" /></td><td>{L_NO_NEW_POSTS}</td>
                <td width="30" align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_announce.png" alt="{L_ANNOUNCEMENT}" /></td><td>{L_ANNOUNCEMENT}</td>
            </tr>
            <tr> 
                <td align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_new_hot.png" alt="{L_NEW_POSTS_HOT}" /></td><td>{L_NEW_POSTS_HOT}</td>                
                <td align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_hot.png" alt="{L_NO_NEW_POSTS_HOT}" /></td><td>{L_NO_NEW_POSTS_HOT}</td>
                <td align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_sticky.png" alt="{L_STICKY}" /></td><td>{L_STICKY}</td>
            </tr>
            <tr>
                <td align='center'><img src="templates/{LOAD_TPL}/forum/images/folder_locked_big_new.png" alt="{L_NEW_POSTS_LOCKED}" /></td><td>{L_NEW_POSTS_LOCKED}</td>
                <td align='center'><img src="templates/{LOAD_TPL}/forum/images/folder_locked_big.png" alt="{L_NO_NEW_POSTS_LOCKED}" /></td><td>{L_NO_NEW_POSTS_LOCKED}</td>
        <td colspan="2">&nbsp;</td>
            </tr>
        </table></td>
        <td align="right">{S_AUTH_LIST}</td>
    </tr>
</table>
{CLOSE_TABLE}