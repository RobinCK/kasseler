<?php
/**
* Блок последнего опроса
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-last_voting.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main, $ip;
    $ipl=explode(".",$ip);
    $whip="vote_ip REGEXP '(^{$ipl[0]}\\.{$ipl[1]}\\.{$ipl[2]}\\.{$ipl[3]},|,{$ipl[0]}\\.{$ipl[1]}\\.{$ipl[2]}\\.{$ipl[3]},)'";
    $whuser="vote_users REGEXP '(^{$main->user['user_name']},|,{$main->user['user_name']},)'";
    $result = $main->db->sql_query("SELECT 
    id,title,vote_case,`date`,`comment`,`status`,`language`,show_comment, module, multisel, count_vote, agroups, max_multi, date_final,result,
    (select vp.id from ".VOTING." AS vp where vp.id=v.id and {$whip} and {$whuser}) as found_vote
    FROM ".VOTING." AS v WHERE ((module is null) or module='') AND status='1' ORDER BY id DESC LIMIT 1");
    if($main->db->sql_numrows($result)>0){
        $vote = $main->db->sql_fetchrow($result);
        $cook = isset($_COOKIE['voting']) ? $_COOKIE['voting'] : "";
        $acc_ok=(!empty($vote['agroups']) AND $vote['agroups']!=",")?check_user_group($vote['agroups']):true;
        if(is_admin()) $acc_ok=true;
        if(!empty($vote['date_final']) AND $vote['date_final']!='0000-00-00' AND strtotime($vote['date_final'])<time()){
            $acc_ok=false;
            $main->db->sql_query("update ".VOTING." set status='0' where id='{$vote['id']}'");
        }
        if($acc_ok AND empty($vote['found_vote']) AND mb_strpos($cook, $vote['id'].",")===false){
            $tmp = explode("|", $vote['vote_case']);
            $variables = "<table align='center' width='100%'>";
            for ($i=0;$i<count($tmp)-1;$i++){
                if($vote['multisel']==1) $variables .= "<tr><td align='left'>".in_chck("var[".($i+1)."]",'checkbox')."<label for='var".($i+1)."'> {$tmp[$i]}</label></td></tr>";
                else $variables .= "<tr><td align='left'>".in_radio('var', $i+1, $tmp[$i], "vote{$i}")."</td></tr>";
            } 
            $variables .= "</table>";
            echo "<div id='voting_result'><div id='voting_block'><form onsubmit=\"set_vote(this, '".strip_tags($main->lang['yourisnotselectvote'])."'); return false;\" method='post' action='".$main->url(array('module' => 'voting', 'do' => 'set_votes', 'id' => $vote['id']))."'>\n<div>\n".
            "<div><h2 class='vote_title'>{$vote['title']}</h2></div>\n".
            "<div><br />{$variables}</div>\n".
            "<div class='vote_button'>
                <br /><table align='center'><tr>
                <td><input class='vote_button' type='submit' value='{$main->lang['vote']}' /></td>
                <td><input onclick=\"location.href='".$main->url(array('module' => 'voting', 'do' => 'result', 'id' => $vote['id']))."'\" class='color_gray' type='button' value='{$main->lang['result_vote']}' /></td>
                </tr></table>
            </div>\n".
            "</div>\n</form>\n</div>\n</div>\n";
            if($vote['multisel']==1 AND $vote['max_multi']>0) echo "<script type='text/javascript'>
                <!--
                var max_checked={$vote['max_multi']};
                var check_count=0;
                \$('#voting_block').find(':checkbox').on('change',function(){
                if(this.checked){
                if(check_count<max_checked) check_count++;
                else this.checked=false;
                } else {if(check_count>0) check_count--}
                })
                // -->
                </script>";
        } else {
            $sum = 0;
            $template->get_tpl('block-vote', 'block-vote', '');
            $vote_row="template incorrect";;
            if (preg_match('/(?is)<!--begin\x20show\x20voting-->[^\r\n]*[\r\n]*(.*)<!--end\x20show\x20voting-->/', $template->template['block-vote'], $regs)) {
               $vote_tpl= preg_replace('/(?is)<!--row\x20result-->[^\r\n]*[\r\n]*(.*)<!--end\x20row\x20result-->/', '{$vote.content}', $regs[1]);
               if (preg_match('/(?is)<!--row\x20result-->[^\r\n]*[\r\n]*(.*)<!--end\x20row\x20result-->/', $regs[1], $rg)) {
                  $vote_row= $rg[1];
               }
               $template->template['block-vote'] = $vote_tpl;
            }
            $tmp = explode(",", $vote['result']);
            $tmpcase = explode("|", $vote['vote_case']);
            for ($y=0; $y<count($tmpcase)-1; $y++){
               $case = $tmpcase[$y];
               $vote[$case]=0;
               for ($i=0; $i<count($tmp); $i++) if ($tmp[$i]==$y+1) {$vote[$case]+=1; $sum+=1;}
            }
            $pl=1;$content="";
            for ($y=0; $y<count($tmpcase)-1; $y++){
               $case = $tmpcase[$y];
               $res = ($sum>0) ? ((100*$vote[$case]/$sum)) : 0;
               $proc = round( $res, 2 );
               $row = array('$text_vote'=>"{$case}  - ".$vote[$case],'$val_int'=>intval($proc), '$val_text'=>$proc,'$pcolor'=>$pl);
               $replace = array('key' => array(), 'value' => array());
               foreach($row as $key => $value){$replace['key'][] = '{'.$key.'}'; $replace['value'][] = $value;}
               $content .= (!empty($replace['key'])) ? str_replace($replace['key'], $replace['value'], $vote_row) : "";
               $pl++;
               if($pl == 6) $pl=1;
            }
            $othe_vote=isset($_POST['voteblock'])?"<br /><a class='other_votes link_button' href='".$main->url(array('module' => 'voting'))."' title='{$main->lang['other_votes']}'>{$main->lang['other_votes']}</a>":"";
            $template->set_tpl(array(
                '$vote.title'         => $vote['title'],
                '$vote.content'       => $content,
                '$vote.all'           => "{$main->lang['votes']}: (".$vote['count_vote'].")".$othe_vote
            ), 'block-vote', array('start' => '{', 'end' => '}'));  
            echo $template->tpl_create(true, 'block-vote'); 
            
        }
    } else echo "&nbsp;";
?>
