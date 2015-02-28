<?php
   global $main,$patterns,$topic;
   function getHeaders(){
      $headers = array();
      foreach ($_SERVER as $k => $v){
         if (substr($k, 0, 5) == "HTTP_"){
            $k = str_replace('_', ' ', substr($k, 5));
            $k = str_replace(' ', '-', ucwords(strtolower($k)));
            $headers[$k] = $v;
         }
      }
      return $headers;
   }
   function exec_php($url, $post_params = array()){
      if(hook_check(__FUNCTION__)) return hook();
      $ignore_param=array('Host','Content-Type','Content-Length','Connection');
      foreach ($ignore_param as $key => $value) $ignore_param[$key]=strtoupper($value);
      $header=getHeaders();
      foreach ($header as $key => $value) {
         if(in_array(strtoupper($key),$ignore_param)) unset($header[$key]);
      }
      $post_params=array();
      $url=str_replace('&amp;','&',$url);
      $parts = parse_url($url);
      $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80,$errno, $errstr, 30);
      if (!$fp)  return false;
      $data = http_build_query($post_params, '', '&');
      $patch=(!empty($parts['path']) ? $parts['path'] : '/').(!empty($parts['query']) ? "?".$parts['query'] : '');
      $isPOST=count($post_params)!=0;
      $send_info=($isPOST?"POST ":"GET "). $patch . " HTTP/1.1\r\n".
      "Host: " . $parts['host'] . "\r\n".
      ($isPOST?"Content-Type: application/x-www-form-urlencoded\r\n":"").
      ($isPOST?"Content-Length: " . strlen($data) . "\r\n":"").
      "Connection: Close\r\n";
      foreach ($header as $key => $value) $send_info.="{$key}: {$value}\r\n";
      $send_info.="\r\n";
      if($isPOST) $send_info.=$data;
      fwrite($fp, $send_info);
      //fclose($fp);
      return true;
   }
   function save_base_forum_mail($topic_id,$list_user){
      if(hook_check(__FUNCTION__)) return hook();
      sql_update(array('sending'=>'y'),FORUM_SUBSCRIBE," topic_id={$topic_id} and uid in (".implode(',',$list_user).")");
   }
   function send_mail_subscrib($topic_id=0){
      global $main,$patterns,$topic, $config;
      if(hook_check(__FUNCTION__)) return hook();
      if($topic_id==0) {
       if(empty($_SESSION['forum_subs'])) {$topic_id=intval($_GET['id']);$post_id="t.topic_last_post_id";}
       else {foreach ($_SESSION['forum_subs'] as $key => $value){$topic_id=$key;$post_id=$value;}}
      }
      if(isset($_SESSION['forum_subs'])) unset($_SESSION['forum_subs']);
      $main->db->sql_query("select t.topic_title, t.topic_last_post_id, p.poster_id, p.poster_name,p.post_id from 
      ".TOPICS." t, ".POSTS." p where t.topic_id={$topic_id} and p.topic_id=t.topic_id and p.post_id={$post_id}");
      if($main->db->sql_numrows()!=0){
         $row=$main->db->sql_fetchrow();
         if(!is_numeric($post_id)) $post_id=$row['post_id'];
         if($post_id!=$row['topic_last_post_id']) kr_exit();
         $bbc = array();
         $att = array();
         $list = array();
         $poster_id=empty($row['poster_id'])?0:$row['poster_id'];
         $subj=mb_substr("{$row['topic_title']}", 0, 149);
         $i = 0;
         $from='Forum';
         $user_from=is_guest()?$row['poster_name']:$main->user['user_name'];
         $message = preg_replace(
            array('/\{SENDER\}/is', '/\{SITE\}/is', '/\{URL\}/is', '/\{SUBJECT\}/is'),
            array($user_from, "<a href='{$main->config['http_home_url']}' title='{$main->config['home_title']}'>{$main->config['home_title']}</a>",
               "<a href='".$main->url(array('module' => $main->module, 'do' => 'showpost', 'id' => $post_id))."' title='{$main->lang['to_new_pm']}'>{$main->lang['to_new_pm']}</a>",
               "<a href='".$main->url(array('module' => $main->module, 'do' => 'lastpost', 'id' => $topic_id))."' >{$subj}</a>"),$patterns['new_post_forum']
         );
         $result=$main->db->sql_query("select u.* from ".FORUM_SUBSCRIBE." fs,".USERS." u where fs.topic_id={$topic_id} and fs.sending='n' and fs.uid<>{$poster_id} and u.uid=fs.uid");
         while(($row = $main->db->sql_fetchrow($result))){
            if($config['bcc_send']==ENABLED){
               if($i==500){
                  $l=count($bbc);
                  send_mail($bbc[$l-1]['mail'],$main->lang['user'] , $main->config['sends_mail'], $from, $subj, $message, array(), $att, $bbc);
                  save_base_forum_mail($topic_id,$list);
                  $bbc = array();
                  $list = array();
                  $i = 0;
               } 
               if($main->user['user_name']!=$row['user_name']){
                  $bbc[] = array('mail' => $row['user_email'], 'name' => $row['user_name']);
                  $list[] = $row['uid'];
                  $i++;
               }
            } else {
               if($main->user['user_name']!=$row['user_name']){
                  send_mail($row['user_email'],$row['user_name'] , $main->config['sends_mail'], $from, $subj, $message, array(), $att, $bbc);
                  save_base_forum_mail($topic_id,array($row['uid']));
               }
            }
         }        
         if($i>=1 AND !empty($bbc)) {
            $l=count($bbc);
            send_mail($bbc[$l-1]['mail'], $main->lang['user'], $main->config['sends_mail'], $from, $subj, $message, array(), $att, $bbc);
            save_base_forum_mail($topic_id,$list);
         }
      }
      kr_exit();
   }
?>
