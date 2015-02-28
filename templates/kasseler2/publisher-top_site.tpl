<div class="opentable" style='padding: 3px;'>
<h1>$pub[title]</h1>
<div align='center'><a href='$pub[link]' title='$pub[text_title]'><img class='topsite' src='$pub[image_url]' alt='$pub[text_title]' /></a><br />$pub[content]</div>
<table width='100%'><tr><td class='track_desc'>$pub[lang_in_hit]: $pub[in_hit], $pub[lang_out_hit]: $pub[out_hit]</td>
<td width='90' valign='bottom'>
<div id='r_$pub[r_id]' class="rating $pub[r_class]" style='margin-top: 10px;'>
    <div>
        <a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', -1);" class="minus" href="#" >-</a><a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', 1);" class="plus" href="#">+</a>
    </div>
    <b id='rating$pub[r_id]'>$pub[rating_result]</b>
</div>
</td></tr></table>
</div>