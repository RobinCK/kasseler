<!--begin show voting-->
<h2 class="vote_title">{$vote.title}</h2>
<!--row result-->
<div align="left" class="vote">{$text_vote} ({$val_text}%)</div>
<div class="progress voted progress_{$pcolor}"><span title='{$val_int}' style="width: 0;"></span></div>
<!--end row result-->
<table width="100%">
<tr><td align="center" colspan="2">{$vote.all}</td></tr>
</table>
<script type="text/javascript">
    $.krReady(function(){
        $('.progress span').each(function(index) {$(this).animate({width: this.title+"%"}, {duration:600});});
    });
</script>
<!--end show voting-->
