<?php
   /**
   * Получение содержимого запроса
   * 
   * @param mixed $url
   * @param mixed $charset
   */
   function gcurl_content($url, $charset='utf8'){
      $ch = curl_init();
      $close = true;
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.205 Safari/534.16'); //устанавливаем параметр CURL, чтобы в ответе ловить заголовки страницы
      curl_setopt($ch, CURLOPT_HEADER, 1); //устанавливаем параметр CURL, чтобы в ответе ловить заголовки страницы
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 10); //результат CURL возвращает, а НЕ выводит
      $content = curl_exec($ch);
      if(preg_match('/Location:.*/s', $content)){
         $location = "";
         preg_match('/Location: (.*)$/s', $content, $location);
         $url_str = trim($location[1]);
         curl_close($ch);
         $close = false;
         $content = gsock_content($url_str);
      }
      if($close==true) curl_close($ch);
      if($charset!='UTF-8') $content = mb_convert_encoding($content, 'UTF-8', $charset);
      return $content;
   }
   /**
   * Получение курса валют
   * 
   */
   function get_curse_valut(){
      global $main, $database;
      $result = $main->db->sql_query("SELECT * FROM {$database['prefix']}_client_valut WHERE date>'".date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))-60*60*3)."' LIMIT 1");
      if($main->db->sql_numrows($result)==0){
         $tX = file_get_contents('https://wm.exchanger.ru/asp/XMLbestRates.asp');
         $XML =  @json_decode(@json_encode(simplexml_load_string($tX)), 1);
         $rows = ""; $insert = array();
         $_vals = array('wme_wmg', 'wme_wmr', 'wme_wmu', 'wme_wmz', 'wmu_wmg', 'wmu_wme', 'wmu_wmr', 'wmu_wmz', 'wmz_wmg', 'wmz_wme', 'wmz_wmr', 'wmz_wmu',  'wmr_wmg', 'wmr_wme', 'wmr_wmz', 'wmr_wmu', 'wmg_wmr', 'wmg_wme', 'wmg_wmz', 'wmg_wmu');
         foreach($XML['row'] as $value){
            $value['@attributes']['Direct'];
            $val = strtolower(str_replace(' - ', '_', $value['@attributes']['Direct']));
            if(in_array($val, $_vals)){
               $course = $value['@attributes']['BaseRate'];
               $insert[$val] = $course;
            }
         }
         $insert['date'] = date('Y-m-d H:i:s');
         $next_id = $main->db->sql_nextid(sql_insert($insert, "{$database['prefix']}_client_valut"));
         return array_merge(array('id' => $next_id), $insert);
      } return $main->db->sql_fetchrow($result);
   }
?>
