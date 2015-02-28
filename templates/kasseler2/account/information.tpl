<table width='100%' class='table' id='table_{$main->module}'>
   <tr><td width='190' valign='top' class='user_info'>
         <div align='center'><h3>$pub[_user_country]</h3><img src='$pub[_user_avatar]' alt='$pub[user_name]' /></div><hr />
         <div align='left'><img src='includes/images/16x16/email.png' alt='E-mail' align='left' style='margin-right: 3px;' /> $pub[_user_viewemail]</div>
         <div align='left'><img src='includes/images/16x16/icq.png' alt='ICQ' align='left' style='margin-right: 3px;' /> $pub[_user_icq]</div>
         <div align='left'><img src='includes/images/16x16/skype.png' alt='Skype' align='left' style='margin-right: 3px;' /> $pub[_user_skype]</div>
         <div align='left'><img src='includes/images/16x16/talk.png' alt='Google Talk' align='left' style='margin-right: 3px;' /> $pub[_user_gtalk]</div>
         <div align='left'><img src='includes/images/16x16/aim.png' alt='AIM' align='left' style='margin-right: 3px;' /> $pub[_user_aim]</div>
         <div align='left'><img src='includes/images/16x16/yim.png' alt='YIM' align='left' style='margin-right: 3px;' /> $pub[_user_yim]</div>
         <div align='left'><img src='includes/images/16x16/msn.png' alt='MSNM' align='left' style='margin-right: 3px;' /> $pub[_user_msnm]</div>
         $pub[_user_last_ip]
      </td>
      <td valign='top'>
         <table width='100%' class='notable'>
            <tr><td width='190'>{$main->lang['birthday']}:</td><td>$pub[_user_birthday]</td></tr>
            $pub[_user_group]
            <tr><td>{$main->lang['age']}:</td><td>$pub[_age]</td></tr>
            <tr><td>{$main->lang['zodiak']}:</td><td>$pub[_zodiak]</td></tr>
            <tr><td>{$main->lang['gender']}:</td><td>$pub[_gender]</td></tr>
            <tr><td>{$main->lang['reg_date']}:</td><td>$pub[_reg_date]</td></tr>
            <tr><td>{$main->lang['last_visit']}:</td><td>$pub[_user_last_visit]</td></tr>
            <tr>$pub[_info_news]</tr>
            <tr>$pub[_info_account]</tr>
            <tr>$pub[_info_forum]</tr>
            <tr>$pub[_info_forumq]</tr>
            <tr>$pub[_info_files]</tr>
            <tr>$pub[_info_pages]</tr>
            <tr><td>{$main->lang['home_page']}:</td><td>$pub[_user_website]</td></tr>
            <tr><td>{$main->lang['occupation']}:</td><td>$pub[_user_occupation]</td></tr>
            <tr><td>{$main->lang['interests']}:</td><td>$pub[_user_interests]</td></tr>
            <tr><td>{$main->lang['locality']}:</td><td>$pub[_user_locality]</td></tr>
            <tr><td>{$main->lang['rating']}:</td><td>
                  <div id='r_$pub[r_id]' class="rating $pub[r_class]" style='margin-top: 10px;'>
                     <div>
                        <a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', -1);" class="minus" href="#" >-</a><a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', 1);" class="plus" href="#">+</a>
                     </div>
                     <b id='rating$pub[r_id]'>$pub[rating_result]</b>
                  </div>

               </td></tr>
            <tr><td colspan='2'><hr />$pub[_user_signature]</td></tr>
         </table>
         <div align='right' class='op_account' style='padding-top: 10px; display:none;'>
            <a class='linkbutton at_b' href='$pub[_link_mail]' title='{$main->lang["send_email"]}'>{$main->lang['send_email']}</a></div>
      </td>
      </tr>
</table><br />