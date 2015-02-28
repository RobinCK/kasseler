<!--begin voting-->
<div class="opentable" style='padding: 30px;'>
<form action='{$vote.action}' method="post">
<div align="center">
<div><h2 class="vote_title">{$vote.title}</h2></div>
<div><br/>
<table align="center" class='votesrc'>
{$vote.content}
</table>
</div>
<div><br/>{$vote.submit}</div>
<div><br/>
{$vote.links}
</div>
</div>
</form>
</div>
<!--end voting-->

<!--begin show voting-->
<h2 class="vote_title">{$vote.title}</h2>
<!--row result-->
<div align="left" class="vote">{$text_vote}</div>
<div class="progress polled progress_{$pcolor}"><span title='{$val_int}' style="width: 0;"><b>{$val_text}%</b></span></div>
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
