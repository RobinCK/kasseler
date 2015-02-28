<?php
   /**
   * Файл обнослений БД
   * 
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2011 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @filesource update.php
   * @version 2.0
   */
   define('KASSELERCMS', true);
   define("ADMIN_FILE", true);
   define('INSTALLCMS', true);
   define('UPDATECMS', true);
   define('E__DATABASECONF___', true);
   define('E__DATABASE__', true);
   define('E__CORE__', true);
   
   
   require_once "includes/function/init.php";
   $page_title = 'Update @ Kasseler CMS';
   main::inited('class.templates', 'function.templates', 'function.sources', 'function.dbsystable');
   $template = new template();
   $template->path = 'install/template/';
   $template->get_tpl('index', 'index');
   $tpl_create = new tpl_create;
   $tpl_create->tpl_creates();

   if(isset($_GET['lang']) AND file_exists("install/language/{$_GET['lang']}.php")){
      $_SESSION['wizard_lang'] = $_GET['lang'];
      setcookies($_GET['lang'], "admin_lang");
      setcookies($_GET['lang'], "lang");
      redirect("update.php");
   }
   global $config, $install_lang, $language_install, $update_lan,$databaseg;
   global $dir_install_exists,$db_revision, $moduleinit;
   $db_revision=get_db_revision(true);
   if(!isset($database['revision'])||intval($database['revision'])!=$db_revision){
      $database['revision']=$db_revision;
      save_config('configdb.php', '$database', $database);
   }
   $language_install = isset($_COOKIE["admin_lang"]) ? $_COOKIE["admin_lang"] : $config['language'];
   $dir_install_exists=is_dir("install");
   if (!$dir_install_exists) $template->get_tpl(TEMPLATE_PATH."admin/login.tpl");
   function button_case(){
      global $language_install;
      return ($language_install=='russian') ? "ru" : "en";
   }
   if ($config['language']=='russian'||$config['language']=='ukraine'){
      $update_lang = array(
      'notfound_title'       => 'Ошибка обновления',
      'notfound_slq_content' => 'Не найден файл <b>install/sql/update.sql</b>, проверьте его наличие.',
      'notfound_dir_install' => 'Не найден каталог <b>install</b>, проверьте его наличие.',
      'step1_title'          => 'Проверка на необходимость обновления БД',
      'step1_content'        => '<h1 class="install">Мастер обновления БД</h1><br />Количетство обнаруженных обновлений для БД : <b>{COUNT_FIX}</b> <br /><br />',
      'step2_title'          => 'Применение исправлений для БД',
      'exec'                 => 'Выполнить',
      'update_to'            => "Текущая ревизия php файлов <b>{$revision}</b>, обновить БД до",
      'exception_title'      => 'Ошибка выполнения обновления БД',
      'exception_content'    => "Возникла ошибка при выполении <b>fix {REVISION}</b>:<br/><b>{QUERY}</b><br/>, для выполнения ревизии <b>{REVISION}</b> необходимо выполнить все из файла <b>uploads/tmpfiles/update.run.sql</b>.<br/>",
      'run_exists_title'     => "Незавершенное обновление",
      'run_exists_content'   => "Обнаружен файл (update.run.sql) предыдущего запуска, который завершился неудачно.Выполните его вручную, затем удалите. И незабудьте изменить значение revision в файле includes/config/configdb.php ."
      );
   } else {
      $update_lang = array(
      'notfound_title'       => 'Failure to update',
      'notfound_slq_content' => 'File not found <b>install/sql/update.sql</ b>, check its availability.',
      'notfound_dir_install' => 'No directory is found <b> install </ b>, check its availability.',
      'step1_title'          => 'Check on the need to update the database',
      'step1_content'        => '<h1 class="install">The master database updates</h1><br />The number of detected updates to the database : <b>{COUNT_FIX}</b> <br /><br />',
      'step2_title'          => 'Apply patches to the database',
      'exec'                 => 'Run',
      'update_to'            => "Current revision php files <b>{$revision}</b>, update the database to ",
      'exception_title'      => 'Run-time error updating the database',
      'exception_content'    => "There was an error meet the following <b>fix {REVISION}</b>:<br/><b>{QUERY}</b><br/>, to perform the audit <b>{REVISION}</ b> You must complete all of the file <b>uploads/tmpfiles/update.run.sql</b>.<br/>",
      'run_exists_title'     => "Work in progress update",
      'run_exists_content'   => "File is detected (update.run.sql) the previous run, which fails.Run it manually, then remove. And do not forget to change the value of revision in the file includes/config/configdb.php ."
      );
   }
   function check_install_dir(){
      global $update_lang,$dir_install_exists;
      if (!$dir_install_exists)  return array('content' =>$update_lang['notfound_dir_install'] , 'title' => $update_lang['notfound_title']);
      else return array();
   }
   function check_update_sql(){
      global $update_lang;
      $ret=check_install_dir();
      if (count($ret)==0){
         if (file_exists("uploads/tmpfiles/update.run.sql"))  return array('content' =>$update_lang['run_exists_content'] , 'title' => $update_lang['run_exists_title']);
         if (!file_exists("install/sql/update.sql"))  return array('content' =>$update_lang['notfound_slq_content'] , 'title' => $update_lang['notfound_title']);
         else return array();
      } else return $ret;
   }
   function scan_count_db_fix(){
      global $config,$revision,$database,$db_revision;
      $readdump = fopen("install/sql/update.sql", "rb");
      $stringdump = fread($readdump, filesize("install/sql/update.sql"));
      fclose($readdump);
      $ret=array();
      preg_match_all('/(?i)--\x20*\{fix\x20*([0-9]*)[^\r\n]*(.*?)--\{fix\x20end\}/sm', $stringdump, $result, PREG_PATTERN_ORDER);
      for ($i = 0; $i < count($result[0]); $i++) {
         $num=intval($result[1][$i]);
         if ($num>$db_revision) $ret[$num]=$num;
      }
      return $ret;
   }
   function step1(){
      global $update_lang,$revision, $main, $moduleinit;
      check_create_system_table();
      $moduleinit=array('lang'=>array());
      main::init_function(array('configs','initmodule'));
      scan_init_modules();
      scan_init_plagin();
      save_config_direct('config_init.php','$moduleinit',$moduleinit);
      $ret=check_update_sql();
      if (count($ret)==0){
         $fix=scan_count_db_fix();
         $count_fix=count($fix);
         $content = "<form action='update.php?do=step2' method='post'>".
         str_replace("{COUNT_FIX}", $count_fix, $update_lang['step1_content']);
         $strac='';
         foreach ($fix as $key => $value) {
            $strac.=", <a href='http://diff.kasseler-cms.net/svn/patches/{$value}.html' target='_blank'>#{$value}</a>";
         }
         $strac.="<p> {$update_lang['update_to']}:".in_sels("update_to",$fix,"",$revision)."</p>";
         if ($count_fix!=0) $content.=substr($strac,1)."<div align='right'><input class='submit' type='image' src='install/template/images/install_".button_case().".png' alt='{$update_lang['exec']}' /></div>";
         $content.="</form>";
         return array('content' =>$content , 'title' => $update_lang['step1_title']);
      } else return $ret;
   }
   function step2(){
      global $update_lang,$config,$database,$revision,$main;
      $ret=check_update_sql();
      if (count($ret)==0){
         $db_revision=$database['revision'];
         $update_to=intval($_POST['update_to']);
         $readdump = fopen("install/sql/update.sql", "rb");
         $stringdump = fread($readdump, filesize("install/sql/update.sql"));
         $content='<pre>';
         fclose($readdump);
         preg_match_all('/(?i)--\x20*\{fix\x20*([0-9]*)[^\r\n]*(.*?)--\{fix\x20end\}/sm', $stringdump, $result, PREG_PATTERN_ORDER);
         for ($i = 0; $i < count($result[0]); $i++) {
            $num=intval($result[1][$i]);
            if ($num>$db_revision AND $num<=$update_to and ($num<>799)) {
               //$_stringdump = explode(";\r\n", $result[2][$i]);
               $_stringdump = $result[2][$i];               
               $_stringdump = str_replace("{PREFIX}", $database['prefix'], $_stringdump);
               if (array_key_exists('admin', $_POST)) $_stringdump = str_replace("{USER}", $_POST['admin'], $_stringdump);
               $_stringdump = str_replace("{DATE}", kr_datecms("Y-m-d"), $_stringdump);
               $_stringdump = str_replace("{DATETIME}", kr_datecms("Y-m-d H:i:s"), $_stringdump);
               $_stringdump = str_replace("{CHARSET}", $database['charset'], $_stringdump);
               preg_match_all('/(.*?);[\r\n]{1,}/si', $_stringdump, $regs, PREG_PATTERN_ORDER);
               $_stringdump = $regs[0];
               $count_sql=0;
               for ($j = 0; $j < count($_stringdump); $j++) {
                  $sql=trim($_stringdump[$j]);
                  if ($sql!=""){
                     $config['mode_debugging_sql']='';
                     if (!$main->db->sql_query($sql)){
                        $file_link="uploads/tmpfiles/update.run.sql";
                        $file = fopen($file_link, "w");
                        for ($n = $j; $n < count($_stringdump); $n++){if (trim($_stringdump[$n])!="") fputs ($file,($_stringdump[$n].";\r\n"));}
                        fputs ($file,"REPLACE INTO  ".SYSTEMDB." VALUES ('".DBREVISION."', '{$num}');");
                        fclose ($file);
                        $content=str_replace("{QUERY}",$sql,$update_lang['exception_content']);
                        $content=str_replace("{REVISION}",$num,$content);
                        //$content.=$cron_config;
                        return array('content' =>$content , 'title' => $update_lang['exception_title']);
                     };
                     $count_sql++;
                  }
               }
               if(file_exists("install/update/r{$num}.php")){
                   ob_start();
                   main::required("install/update/r{$num}.php");
                   $content .= "<br/>".ob_get_contents(); ob_get_clean();
               }
               $content.="<br/>exec fixed {$num}: {$count_sql} command";
               set_db_revision($num,true);
               $database['revision']=get_db_revision();
               save_config('configdb.php', '$database', $database);
            }
         }
         $content.="</pre>";
         return array('content' =>$content , 'title' => $update_lang['step2_title']);
      } else return $ret;
   }
   $install = array('content' => '', 'title' => '');
   if(isset($_GET['do'])){
      switch($_GET['do']){
         case "step2" : $install = step2(); break;
         default: $install = step1(); break;
      }
   } else $install = step1();

   add_meta_value($install['title']);
   $template->set_tpl(array(
        'language'         => "<a href='update.php?lang=russian'>".(($language_install=='russian')?"<b>{$install_lang['russian']}</b>":$install_lang['russian'])."</a> | <a href='update.php?lang=english'>".(($language_install=='english')?"<b>{$install_lang['english']}</b>":$install_lang['english'])."</a>", 
        'content'          => $install['content'], 
        'login_title'      => ' ', 
        'install_title'    => $install['title'], 
   ));

   $contents = $template->tpl_create(true);
   main::required("includes/nocache.php");
   gz($contents);
?>
