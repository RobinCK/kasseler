<table cellspacing='1' class='table3' width='100%'>
<tr>
   <th width='25'>#</th>
   <th>{$main->lang['login']}</th>
   <th width='30'>{$main->lang['age']}</th>
   <th width='30'>{$main->lang['gender']}</th>
   <th width='120'>{$main->lang['reg_date']}</th>
   <th width='30'>{$main->lang['website']}</th>
   <th width='40'>{$main->lang['points']}</th>
   <th width='90'>{$main->lang['rating']}</th>
</tr>
<!--begin row-->
<tr class='$pub[_class_row]'>
   <td align='center'>$pub[_num]</td>
   <td>$pub[_user_country] &nbsp;<a style='color:#$pub[color];' href='$pub[_user_link]' id='userinfo_$pub[uid]' class='user_info'>$pub[user_name]</a></td>
   <td align='center'>$pub[_user_birthday]</td>
   <td align='center'><img src='$pub[_user_gender_img]' alt='' style='margin-right: 3px;' /></td>
   <td align='center'>$pub[_user_regdate]</td>
   <td align='center'>$pub[_user_website]</td>
   <td align='center'>$pub[user_points]</td>
   <td>
      <div id='r_$pub[r_id]' class="rating $pub[r_class]" style='margin-top: 10px;'>
         <div>
            <a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', -1);" class="minus" href="#" >-</a><a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', 1);" class="plus" href="#">+</a>
         </div>
         <b id='rating$pub[r_id]'>$pub[rating_result]</b>
      </div>
   </td>
</tr>
<!--end row-->
</table>