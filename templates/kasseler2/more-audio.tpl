<div class="opentable" style='padding: 3px;'>
<div>$pub[biography] $pub[content]</div>
<table width='100%'><tr><td><span class='track_desc'>$pub[lang_tags] $pub[tags] $pub[lang_cat]: $pub[category] $pub[downloads] $pub[playing]</span></td><td width='90' valign='top' align='right' style='padding-right: 10px;'><b>$pub[download]</b></td></tr></table>
<table width='100%'><tr><td class='track_desc'>$pub[lang_author_audio] $pub[author_audio]</td>
<td width='90' valign='bottom'>
<div id='r_$pub[r_id]' class="rating $pub[r_class]" style='margin-top: 10px;'>
    <div>
        <a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', -1);" class="minus" href="#" >-</a><a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', 1);" class="plus" href="#">+</a>
    </div>
    <b id='rating$pub[r_id]'>$pub[rating_result]</b>
</div>
</td></tr></table>
</div>