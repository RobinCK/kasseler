<div class='forum_head_menu'>
    <span>{MENU_PROFILE}</span> <span>{MENU_SEARCH}</span> <span class='topic_col_hide'>{MENU_USERS}</span>
    <span class='forum_logout'>{MENU_LOGOUT}</span>
</div>


{OPEN_TABLE}
<div class='topic_option'>
    <div>{MODERATORS}</div>
    <div class='topic_optionb'>{POST_NEW_TOPIC}<span class='pages'>{PAGINATION}</span></div>
</div>
{CLOSE_TABLE}



<!--begin showtopic row-->
{OPEN_TABLE}
<div class='topic_head'><span>{FORUM_NAME}</span><span class='topic_subscribe'>{L_MARK_TOPICS_READ}</span></div>

<table cellpadding="4" cellspacing="1" width="100%" class='cattable'>
    <tr class='topic_list_head'> 
        <th colspan="3" align="center" height="25" nowrap="nowrap">{L_TOPICS}</th>
        <th width="50" align="center" nowrap="nowrap">{L_REPLIES}</th>
        <th width="100" align="center" nowrap="nowrap">{L_AUTHOR}</th>
        <th width="50" align="center" nowrap="nowrap">{L_VIEWS}</th>            
        <th width="150" align="center" nowrap="nowrap">{L_LASTPOST}</th>
    </tr>        
  <!--begin topic row-->
    <tr class='{topicrow.ROW_CLASS}'> 
    <td class='col2 topic_col_hide' width="40" align="center"><img src="{topicrow.TOPIC_FOLDER_IMG}" alt="{topicrow.L_TOPIC_FOLDER_ALT}" title="{topicrow.L_TOPIC_FOLDER_ALT}" /></td>
    <td class='col topic_col_hide' width='15' align="center">{topicrow.TOPIC_ICO}</td>
    <td class='col2'>{topicrow.TOPIC_TITLE}<span class='topic_col_hide'><br /><span class='desc'>{topicrow.TOPIC_DESC}&nbsp;</span><br />{topicrow.GOTO_PAGE}</span></td>
    <td class='col topic_col_hide' align="center" valign="middle">{topicrow.REPLIES}</td>
    <td class='col2 topic_col_hide' align="center" valign="middle">{topicrow.TOPIC_AUTHOR}</td>
    <td class='col topic_col_hide' align="center" valign="middle">{topicrow.VIEWS}</td>
    <td class='col2 topic_last_info' align="center" valign="middle" nowrap="nowrap">{topicrow.LAST_POST_TIME}<span class='topic_col_hide'><br /></span> {topicrow.LAST_POST_AUTHOR} {topicrow.LAST_POST_IMG}</td>
  </tr>    
  <!--end topic row-->
</table>
{CLOSE_TABLE}
<!--end showtopic row-->



{OPEN_TABLE}
<div class='topic_option2'>
    <div class='forum_histiry'>{L_INDEX} &raquo; {FORUM_NAME}</div>
    <div class='topic_optionb2'>{POST_NEW_TOPIC}
        <span class='butpost quick_link'>{QUICK_LINK}</span>
        <span class='butpost page_number'>{PAGE_NUMBER}</span>
        <span class='butpost quick_sort'>{QUICK_SORT}</span>
    </div>
</div>
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
        <td align="left" valign="top" class='icon_topic'>
            <table cellspacing="3" cellpadding="2">
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
            </table>
        </td>
        <td align="right" class='auth_list'>{S_AUTH_LIST}</td>
    </tr>
</table>
{CLOSE_TABLE}