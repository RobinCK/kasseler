<div class="opentable" style='padding: 8px 3px 8px 3px;'>
<div class="binner commentbase">
<div class="commentinner">
<div>
<div style="float: left; width: 100px; margin-right: 10px;">
$pub[avatar]
<div class="commentinfo">
<div><b>$pub[user_group]</b></div>
$pub[edit]
$pub[delete]
</div></div>
<div style="float: left; width: 76%;">
<div class="heading">
<span class="argr"><a title="$lang[quotes]" style='cursor:pointer' onclick="bbeditor.insert('<b>$pub[uname]', '</b>,');"><img src="templates/$load_tpl/images/quote.gif" alt="$lang[quotes]"/></a></span>
<h2>$pub[user_name] $lang[wrote]:</h2>
<div class="moreinfo" style='position:relative;'>
<div style='position:absolute; right: 0px; top: -14px'>
<div id='r_$pub[r_id]' class="rating $pub[r_class]" style='margin-top: 10px;'>
    <div>
        <a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', -1);" class="minus" href="#" >-</a><a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', 1);" class="plus" href="#">+</a>
    </div>
    <b id='rating$pub[r_id]'>$pub[rating_result]</b>
</div>
</div>
$pub[date] | ICQ: $pub[user_icq]
<div class="clr"></div>
</div></div>
<div class="maincont">
$pub[comment]
<div class="clr"></div>
<p class="signature">
--------------------<br />
$pub[user_signature]</p>
</div></div>
<div class="clr"></div>
</div></div></div>
</div>