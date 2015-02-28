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
            {FORUM_BREAD_CRUMB}
        </td>
        <td align="right" valign="bottom">
            {L_SEARCH_SELF}<br />
            {L_SEARCH_UNANSWERED}<br />
            {L_SEARCH_NEW}<br />
            {L_MARK_ALLREAD}
        </td>
    </tr>
    <tr> 
        <td align="left" valign="bottom" colspan='2'>
            {FORUM_MODER}
        </td>
    </tr>
</table>
{CLOSE_TABLE}
<!--begin cat_forum row-->
{OPEN_TABLE}
<table width="100%" cellpadding="2" cellspacing="1" class='cattable'>
  <tr> 
    <td colspan="5" height="28" class='cattitle'>{catrow.CAT_DESC}</td>
  </tr>
  <tr> 
    <th colspan="2" height="25">{L_FORUM}</th>
    <th width="40">{L_TOPICS}</th>
    <th width="70">{L_POSTS}</th>
    <th width="200">{L_LASTPOST}</th>
  </tr>
<!--begin forum subcategory-->            
  <tr class='rows1'>
    <td class='col3' width="40" align="center" height="50"><img src="{subforum.FORUM_FOLDER_IMG}" alt="{subforum.L_FORUM_FOLDER_ALT}" title="{subforum.L_FORUM_FOLDER_ALT}" /></td>
    <td class='col4' ><b class='cattitle'>{subforum.title}</b><br /><span class='desc'><i>{subforum.description}</i></span>{subforum.catforum_moder}<br /><span class='desc'>{subforum.links}</span></td>
    <td class='col3' align="center">{subforum.TOPICS}</td>
    <td class='col4' align="center">{subforum.POSTS}</td>
    <td class='col3'><div align='left'>{subforum.LAST_POST_TITLE}<br />{subforum.LAST_POST_AUTHOR}</div><div align='right'>{subforum.LAST_POST_TIME} {subforum.LAST_POST_IMG}</div></td>
  </tr>
<!--end forum subcategory-->
<!--begin forum row-->            
  <tr class='rows1'>
    <td class='col3' width="40" align="center" height="50"><img src="{catrow.forumrow.FORUM_FOLDER_IMG}" alt="{catrow.forumrow.L_FORUM_FOLDER_ALT}" title="{catrow.forumrow.L_FORUM_FOLDER_ALT}" /></td>
    <td class='col4'><b>{catrow.forumrow.FORUM_NAME}</b><br /><span class='desc'><i>{catrow.forumrow.FORUM_DESC}</i>{catrow.forumrow.SUBFORUM_MODER}</span></td>
    <td class='col3' align="center">{catrow.forumrow.TOPICS}</td>
    <td class='col4' align="center">{catrow.forumrow.POSTS}</td>
    <td class='col3'><div align='left'>{catrow.forumrow.LAST_POST_TITLE}<br />{catrow.forumrow.LAST_POST_AUTHOR}</div><div align='right'>{catrow.forumrow.LAST_POST_TIME} {catrow.forumrow.LAST_POST_IMG}</div></td>
  </tr>
<!--end forum row-->
</table>
{CLOSE_TABLE}
<!--end cat_forum row-->    
{OPEN_TABLE}
<table width="100%" cellpadding="3" cellspacing="1">
    <tr> 
        <td colspan="2" height="28"><b>{L_WHO_IS_ONLINE}</b></td>
    </tr>
</table>
<table width="100%" cellpadding="3" cellspacing="1">
    <tr> 
        <td align="center" valign="middle" rowspan="2" width='40'><img src="templates/{LOAD_TPL}/forum/images/folder_big.png" alt="{L_WHO_IS_ONLINE}" /></td>
        <td align="left">{TOTAL_POSTS}<br />{TOTAL_THEMS}<br />{TOTAL_USERS}</td>
    </tr>
    <tr> 
        <td align="left">{TOTAL_USERS_ONLINE} <br />{LOGGED_IN_USER_LIST}</td>
    </tr>
</table>
{CLOSE_TABLE}
<!--begin login-->
{OPEN_TABLE}
{USER_LOGINED}
{CLOSE_TABLE}
<!--end login-->
{OPEN_TABLE}
<table cellspacing="3" align="center" cellpadding="0">
    <tr> 
        <td width="40" align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_new_big.png" alt="{L_NEW_POSTS}"/></td>
        <td>{L_NEW_POSTS}</td>
        <td width="40" align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_big.png" alt="{L_NO_NEW_POSTS}" /></td>
        <td>{L_NO_NEW_POSTS}</td>
        <td width="40" align="center"><img src="templates/{LOAD_TPL}/forum/images/folder_locked_big.png" alt="{L_FORUM_LOCKED}" /></td>
        <td>{L_FORUM_LOCKED}</td>
    </tr>
</table>
{CLOSE_TABLE}