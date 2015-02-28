<div class="news publisher opentable">
    <div class="headline">
        <h1>$pub[title]</h1>
        <div class='postmetapos'>
        <span class="postmeta">
            <span>$pub[author]</span>
            <span class="category">$pub[category]</span>
            <span class="review">&nbsp;$pub[views]</span>
            <span class="comm">&nbsp;$pub[count_comm]</span>
            <span class="date">&nbsp;$pub[date]</span>
        </span>
        </div>
        $pub[favorite]
        <div id='r_$pub[r_id]' class="rating $pub[r_class]" style='margin-top: 10px;'>
            <div>
                <a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', -1);" class="minus" href="#" >-</a><a onclick="return set_rating('$pub[r_id]', '$pub[r_module]', 1);" class="plus" href="#">+</a>
            </div>
            <b id='rating$pub[r_id]'>$pub[rating_result]</b>
        </div>
    </div>
    <hr />
    <div class="article">$pub[content]</div>
    <div align='right' class='print'><b>$pub[print]</b></div>
    <div class="tags"><div class="tagsl">$pub[lang_tags] $pub[tags]</div></div>
</div>