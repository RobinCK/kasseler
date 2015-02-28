<div class='forum_head_menu'>
    <span>{MENU_PROFILE}</span> <span>{MENU_SEARCH}</span> <span class='topic_col_hide'>{MENU_USERS}</span>
    <span class='forum_logout'>{MENU_LOGOUT}</span>
</div>

{OPEN_TABLE}
<div class='topic_option'>
    <div>{MODERATORS}</div>
    <div class='topic_optionb'>{POST_NEW_TOPIC} {POST_REPLY_TOPIC}<span class='pages'>{PAGINATION}</span></div>
</div>
{CLOSE_TABLE}


{OPEN_TABLE}
<div class='topic_head'><span>{TOPIC_TITLE}</span><span class='topic_subscribe'>{TOPIC_SUBSCRIBE}</span></div>
<!--<table width='100%' cellspacing="0" cellpadding="0" class='cattable'>
    <tr class='rows2'><td colspan="2" height='22'>{FORUM_VOTE}</td></tr>
    <tr><td id='post_table' colspan='2'>-->
<div id='post_table'>
    <!--begin post row-->
    <div class="cattable">
        <div class='container forum_container post{postrow.ROW_CLASS} first-child-{postrow.ROW_CLASS}'>
            <div class='post_header'><span class='post_mini_avatar'>{postrow.MINI_AVATAR}</span><span class='poster_name'>{postrow.POSTER_NAME}</span> <span class='post_num'>{postrow.POST_NUMBER}</span></div>
            <div class="four columns">
                <div class='avatr_post'>{postrow.POSTER_AVATAR}</div>
                <div class='info_post'>
                    {postrow.RANK_IMAGE} {postrow.POSTER_GROUP} {postrow.COUNTRY} {postrow.USER_NUMBER} {postrow.POSTER_AGE} {postrow.POSTER_POSTS} {postrow.USER_TNX} {postrow.POSTER_JOINED} {postrow.POSTER_STATUS}
                </div>
            </div>
        
            <div class="kasb12 columns">                  
                <div>
                    <div class='post_date'>{L_POSTED}: {postrow.POST_DATE} <span>{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></div>
                    <div class='post_content'>
                        {postrow.MESSAGE}
                        <div class='message_post_info'>
                            <div class='post_lastedit'>{postrow.POST_LAST_EDIT}</div>
                            <div class='post_signature'>{postrow.SIGNATURE}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='container forum_container post{postrow.ROW_CLASS} last-child-{postrow.ROW_CLASS}'>
            <div class="four columns">
                <div class='buttom_post2'>{BACK_TO_TOP} {postrow.REPORT} {postrow.TNX}</div>
            </div>
            
            <div class="kasb12 columns">
                <div class='buttom_post'><span class='pm_button'>{postrow.PROFILE_IMG}</span> {postrow.QUOTE_IMG} {postrow.EDIT_IMG} {postrow.DELETE_IMG} {postrow.IP_IMG}</div>
            </div>

        </div>
    </div>
    <!--end post row-->
</div>
{CLOSE_TABLE}

{OPEN_TABLE}
<div class='topic_option'>
    <div class='forum_histiry'>{L_INDEX} &raquo; {FORUM_NAME}</div>
    <div class='topic_optionb2'>{PAGINATION}<span class='butpost'>{POST_NEW_TOPIC} {POST_REPLY_TOPIC}</span></div>
</div>
{CLOSE_TABLE}


<!--begin showonepost-->
<!--begin quick_reply-->
<div id='quickreply'>
{OPEN_TABLE}
<form action='{QUICKREPLY_ACTION}' method='post'>
<table width="100%" align="center">
<tr><td width='250' valign='top' class='forum_smile'>{SMILES_BOX}</td><td valign='top'>{QUICKREPLY_BOX}</td></tr>
<tr><td colspan="2" align='center'><br />{QUICKREPLY_BUTTON}</td></tr>
</table>
</form>
{CLOSE_TABLE}
</div>
<!--end quick_reply-->

{OPEN_TABLE}
<div class='topic_box'>
    {PAGE_NUMBER} <span class='authlist'>{S_AUTH_LIST}</span>
    <div>{S_TOPIC_ADMIN}</div>
</div>
{CLOSE_TABLE}
<!--end showonepost-->