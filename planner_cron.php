<?php
   /**
   * @author Dmitrey Browko
   * @copyright Copyright (c)2007-2012 by Kasseler CMS
   * @link http://www.kasseler-cms.net/
   * @version 2.0
   * @tutorial запуск планировщика CMS из внешних планировщиков
   */
   define('KASSELERCMS', true);
   define('E__SESSION__', true);
   define('E__TEMPLATES__', true);
   define('E__DATABASECONF___', true);
   define('E__DATABASE__', true);
   define('E__CORE__', true);
   define('E__PLUGINS__', true);
   if(empty($_SERVER['SERVER_ADDR'])){
      $rootp = str_replace("planner_cron.php","",$_SERVER['SCRIPT_NAME']);
      chdir ($rootp);
      $_GET = array('ajaxed'=>true);
      $SERVER=array(
         'PHP_FCGI_MAX_REQUESTS' => '250',
         'FCGI_ROLE' => 'RESPONDER',
         'REDIRECT_STATUS' => '200',
         'REDIRECT_URL' => '/index.php',
         'SERVER_ADDR' => '127.0.0.1',
         'REMOTE_ADDR' => '127.0.0.1',
         'REQUEST_URI' => '/index.php',
         'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12;fdnet (.NET CLR 3.5.30729)',
         'SERVER_PORT' => '80',
         'GATEWAY_INTERFACE' => 'CGI/1.1',
         'SERVER_PROTOCOL' => 'HTTP/1.1',
         'REQUEST_METHOD' => 'GET',
         'QUERY_STRING' => '',
         'HTTP_HOST' => '127.0.0.1'
      );
      $_FILES["Filedata"] = array();
      $_SERVER = array_merge($_SERVER,$SERVER);
      require_once "includes/function/init.php";
      global $main;
      $sysdate = gmdate('U') +(intval($config['GMT_correct'])*60*60);
      echo date('Y-m-d H:i:s', $sysdate)."\n";
      main::init_function(array('bool','planner')); 
      runer();
   } else {
      header('HTTP/1.1 404 Not Found');
   }
?>
