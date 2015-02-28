<?php
/**
* ----------------------------------------------------------------------
* @filetype plugin
* @name Плагин личных сообщений
* @description Плагин добавляет страничку личных сообщений пользователя.
* @copyright Kasseler CMS
* @author Игорь Огниченко
* @email ognichenko.igor@gmail.com
* @link http://www.kasseler-cms.net/
* @updateLink *
* @license BSD
* @version 1.0
* @create 01.07.2012
* @cover images/cover.png
* @logo images/logo.png
* @minVersion 1073
* @maxVersion *
* ----------------------------------------------------------------------
*/

if (!defined('FUNC_FILE')) die('Access is limited');

global $main;
define('__PM_PATH__', 'hooks/'.basename(dirname(__FILE__)).'/');

function pm_language(){
global $main;
    if(file_exists(__PM_PATH__.'language/'.$main->language.'.php')) main::init_language($main->language, __PM_PATH__.'language/', '');    
    else main::init_language('russian', __PM_PATH__.'language/', '');
}

if($main->module=='account'){
    if(!is_ajax()) main::add2script("var pm_path = '".__PM_PATH__."';", false);
    /**
    * Хук функции main_account()
    * 
    * @return bool
    */
    function main_account_hook(){
    global $main, $icon_navi;
        if(hook_check(__FUNCTION__)) return hook();
        pm_language();
        if(isset($_GET['do']) AND $_GET['do']=='pm'){
            bcrumb::add($main->lang['pm']);
            $main->parse_rewrite(array('module', 'do', 'op', 'id'));
            if(isset($_GET['op'])){
                switch($_GET['op']){
                    case 'show': account_pm_show(); break;
                    case 'new': account_pm_new(); break;
                    case 'send': account_pm_send(); break;
                    case 'delete': account_pm_delete(); break;
                    case 'delete_message': account_pm_delete_message(); break; 
                    case 'upload': account_pm_upload(); break;
                    case 'search': account_pm_search(); break;
                    case 'autocomplete': account_pm_autocomplete(); break;
                    case 'attache_page': account_pm_attache_page(); break;
                    case 'autoload': main_account_pm(); break;
                    case 'task': task_ckeck(); break;
                    default: main_account_pm(); break;
                }
            } else main_account_pm();
        } else main_account();
    }
    
    function account_pm_delete_message(){
    global $main, $userconf;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user() AND !empty($_POST['data']) AND is_array($_POST['data'])){
            foreach($_POST['data'] as $v) if(!is_numeric($v)) kr_exit();
            $main->db->sql_query("DELETE FROM ".PM." WHERE tid IN (".implode(',', $_POST['data']).") AND ((user_from = '{$main->user['user_name']}' AND type = 1) OR (user = '{$main->user['user_name']}' AND type = 0))"); 
            $result = $main->db->sql_query("SELECT t.* FROM ".PM_TEXT." t LEFT JOIN ".PM." p ON (p.tid=t.tid) WHERE p.mid is null");
            $main->db->sql_query("DELETE t.* FROM ".PM_TEXT." t LEFT JOIN ".PM." p ON (p.tid=t.tid) WHERE p.mid is null");
            if($main->db->sql_numrows($result)>0){
                while((list($tid) = $main->db->sql_fetchrow($result))){
                    if(file_exists($userconf['directory'].$tid) AND is_dir($userconf['directory'].$tid)) remove_dir($userconf['directory'].$tid);
                }
            }
        }
        kr_exit();
    }   
    
    function account_pm_delete(){
    global $main, $userconf;
        if(hook_check(__FUNCTION__)) return hook();
        $main->db->sql_query("DELETE FROM ".PM." WHERE 
            (user_from='{$main->user['user_name']}' AND type = 1 AND user='{$_POST['user']}') OR 
            (user_from='{$_POST['user']}' AND type = 0 AND user='{$main->user['user_name']}')
        ");
        $result = $main->db->sql_query("SELECT tid FROM ".PM_TEXT." WHERE NOT EXISTS (SELECT * FROM ".PM." WHERE `".PM."`.tid = `".PM_TEXT."`.tid)");
        $main->db->sql_query("DELETE FROM ".PM_TEXT." WHERE NOT EXISTS (SELECT * FROM ".PM." WHERE `".PM."`.tid = `".PM_TEXT."`.tid)");
        if($main->db->sql_numrows($result)>0){
            while((list($tid) = $main->db->sql_fetchrow($result))){
                if(file_exists($userconf['directory'].$tid) AND is_dir($userconf['directory'].$tid)) remove_dir($userconf['directory'].$tid);
            }
        }
        kr_exit();
    }
    
    function account_pm_autocomplete(){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user()){
            if(!empty($_POST['q']) AND isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){            
                $result = $main->db->sql_query("SELECT u.*, s.uname FROM ".USERS." AS u LEFT JOIN ".SESSIONS." as s ON (u.user_name=s.uname and s.actives='y') WHERE LOWER(u.user_name) LIKE '".mb_strtolower($_POST['q'])."%'");
                while(($row = $main->db->sql_fetchrow($result))) echo $row['user_name'].'|'.get_avatar($row, 'mini').'|'.(!empty($row['uname'])?"<span style='color:#95cb95'>Online</span>":"<span style='color:#ffb2b2'>Offline</span>")."\n";
                kr_exit();
            } else kr_exit();
        } else kr_exit();
    }
    
    function account_pm_search(){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user()){
            if(!empty($_POST['q']) AND isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
                $result = $main->db->sql_query("SELECT p.mid, p.tid, p.subj, p.user, p.user_from, p.date, p.pm_read, p.status, p.type, t.tid, t.text, u.uid, u.user_id, u.user_name, u.user_avatar FROM ".PM." AS p LEFT JOIN ".PM_TEXT." AS t ON(p.tid=t.tid), ".USERS." AS u, (SELECT (CASE pg.`type` WHEN 1 THEN pg.`user` ELSE pg.user_from END) as usr,max(pg.`mid`) as mxmid from ".PM." AS pg WHERE ((pg.user_from = '{$main->user['user_name']}' AND pg.`type` = 1) OR (pg.`user` = '{$main->user['user_name']}' AND pg.`type` = 0)) GROUP by 1) g WHERE LOWER(u.user_name) LIKE '%".mb_strtolower($_POST['q'])."%' AND p.`mid`=g.mxmid AND ((p.user_from = g.usr) AND (p.user_from = u.user_name) OR (p.user = g.usr) AND (p.user = u.user_name)) ORDER BY p.date DESC");
                 main::init_class('fb');fb::info($main->db->time_query);
                $content = array();
                main::inited('function.format_fully');
                while(($rows = $main->db->sql_fetchrow($result))) {
                    echo implode('{([])}', array(
                        'class_row'         => '',
                        'avatar'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>".get_avatar($rows)."</a>",
                        'small_avatar'      => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>".get_avatar($rows, 'small')."</a>",
                        'sender'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>{$rows['user_name']}</a>",
                        'date'              => format_fully($rows['date']),
                        'subject'           => "<a class='pm_subj sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $rows['uid']))."' title='{$main->lang['to_new_pm']}'>{$rows['subj']}</a>",
                        'small_text'        => str_replace("\r", '', cut_text(strip_tags($rows['text']), 6)),
                        'small_text_link'   => "<a class='pm_text_l sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $rows['uid']))."' title=''>".str_replace("\r", '', cut_text(strip_tags($rows['text']), 6))."</a>",
                        'text'              => str_replace("\r", '', strip_tags($rows['text'])),
                    ))."\r";
                }
                kr_exit();
            } else kr_exit();  
        } else kr_exit();
    }
    
    function account_pm_upload(){
    global $userconf;    
        if(hook_check(__FUNCTION__)) return hook();
        main::init_function('attache');
        upload_attach($userconf);
    }
    
    function account_pm_attache_page(){
    global $main, $userconf;
        if(hook_check(__FUNCTION__)) return hook();
        main::init_function('attache');
        $uploaddir = $userconf['directory'].USER_FOLDER."/";
        echo "<script type='text/javascript'>KR_AJAX.result = ".json_encode(array(
            'time' => time(),
            'content' => in_hide("uploaddir", $uploaddir, true)."<div class='flash' id='upload_progress'></div><div id='upl_up'>".update_list_files($uploaddir)."</div>".SWFUpload("index.php?module={$main->module}&amp;do=pm&op=upload", $userconf['attaching_files_type'], $userconf['attaching_files_size'], $userconf['file_upload_limit'])."</div>",
            'lang'  => array(
                'title' => $main->lang['attach']
            )
        ))."</script>";
        kr_exit();
    }
    
    /**
    * Функция создания нового сообщения
    * 
    */
    function account_pm_new($msg=''){
    global $main, $template;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user()){
            main::add2link(__PM_PATH__.'tpl/style.css');
            if(isset($_GET['id'])) {
                $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE uid='".intval($_GET['id'])."'");
                if($main->db->sql_numrows($result)>0){
                    $info = $main->db->sql_fetchrow($result);
                    $main->lang['create_pm'] .= " [{$info['user_name']}]";
                    $recipient = in_hide('recipient', $info['user_name']);
                } else $recipient = in_text('recipient', 'input_text2', '', false);
            } else $recipient = in_text('recipient', 'input_text2', '', false);
            open();
            $template->get_tpl('tpl/create', 'pm_create', __PM_PATH__);
            $template->set_tpl(array(
                'TITLE'             => $main->lang['create_pm'],
                'BACK'              => "<a class='backlink' href='".$main->url(array('module' => $main->module, 'do' => 'pm'))."'>{$main->lang['pm_back']}</a>",
                'ERROR'             => !empty($msg) ? warning($msg, true) : '',
                'EDITOR'            => editor_small('message', 12),
                'SUBMIT'            => send_button(),
                'LANG_MESSAGE'      => $main->lang['message'],
                'LANG_RECIPIENT'    => $main->lang['recipient'],
                'LANG_SUBJ'         => $main->lang['subj'],
                'RECIPIENT'         => $recipient,
                'SUBJ'              => in_text('subj', 'input_text2', '', false),
                'ATTACH'            => in_hide('attache_page', $main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'attache_page')))."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />",
                'ACTION'            => $main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'send')),
            
            ), 'pm_create', array('start' => '{%', 'end' => '%}'));
            $template->tpl_create(false, 'pm_create');
            echo '<script type="text/javascript">
            $(document).ready(function(){
                    $("#recipient").autocomplete("'.str_replace('&amp;', '&', $main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'autocomplete'))).'", {
                        delay:10,
                        minChars:2,
                        matchSubset:1,
                        autoFill:true,
                        matchContains:1,
                        cacheLength:10,
                        selectFirst:true,
                        formatItem:liFormat,
                        maxItemsToShow:10
                    });
            });
            function liFormat (row, i, num) {
                result = "<table width=\'100%\' class=\'.infoac\'><tr><td width=\'26\'>"+row[1]+"</td><td align=\'left\'><b>"+row[0]+"</b><p class=qnt>"+row[2]+"</p></td></tr></table>";
                return result;
            }
            </script>';
        } else redirect($main->url(array('module' => 'account', 'do' => 'login')));
    }
    
    function account_pm_send(){
    global $main, $userconf, $patterns;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user()){
            main::init_function('session_tools');
            //Фильтрация
            filter_arr(array('recipient', 'subj'), POST, HTML);
            filter_arr(array('message'), POST, TAGS);
            //Проверка данных
            $msg = error_empty(array('message', 'recipient'), array('message_err', 'recipient_err'));
            //Проверка пользователя
            $user_sel = $main->db->sql_query("SELECT uid, user_name, user_email, user_pm_send FROM ".USERS." WHERE user_name='".addslashes($_POST['recipient'])."'");
            $msg .= ($main->db->sql_numrows($user_sel)>0) ? "" : $main->lang['nosearchuser'];
            //Проверка ошибок
            if(empty($msg)){
                //Сохраняем текст
                $tid = $main->db->sql_nextid($main->db->sql_query("INSERT INTO ".PM_TEXT." (text) VALUES ('".bb($_POST['message'])."')"));
                //Проверяем наличие прикрепленных файлов
                if(rename_attach($userconf['directory'].USER_FOLDER."/", $userconf['directory'].$tid."/")){
                    $_POST['message'] = str_replace(USER_FOLDER, $tid, $_POST['message']);
                    sql_update(array('text' => bb($_POST['message'])), PM_TEXT, "tid='{$tid}'");
                }
                $uinf = $main->db->sql_fetchrow($user_sel);
                user_sessions_modify($uinf['user_name'], 'set_this_session_update');
                //Отправка уведомления 
                if($uinf['user_pm_send']==1){
                    $ms = str_replace(
                        array('{SENDER}', '{USER}', '{SITE}', '{URL}', '{SUBJECT}'),
                        array(
                            $main->user['user_name'],
                            $_POST['recipient'],
                            "<a href='{$main->config['http_home_url']}' title='{$main->config['home_title']}'>{$main->config['home_title']}</a>",
                            "<a href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $uinf['uid']))."' title='{$main->lang['to_new_pm']}'>{$main->lang['to_new_pm']}</a>",
                            $_POST['subj']
                        ), $patterns['new_pm']
                    );
                    if(!isset($_SESSION['task'])) $_SESSION['task'] = array();
                    if(!isset($_SESSION['task'][$uinf['user_name']])) $_SESSION['task'][$uinf['user_name']] = serialize("send_mail('{$uinf['user_email']}', '{$uinf['user_name']}', '{$main->config['sends_mail']}', 'noreply', '{$main->lang['send_new_pm']} @ {$main->config['site_name_for_mail']}', '".addslashes($ms)."');");
                }
                
                $main->db->sql_query("UPDATE ".USERS." SET user_new_pm_count = user_new_pm_count+1 WHERE user_name = '{$_POST['recipient']}'");
                
                sql_insert(array(
                    'tid'           => $tid,
                    'subj'          => $_POST['subj'],
                    'user'          => $main->user['user_name'],
                    'user_from'     => $_POST['recipient'],
                    'date'          => kr_datecms("Y-m-d H:i:s"),
                    'status'        => 2,
                    'type'          => 0
                ), PM);
                
                sql_insert(array(
                    'tid'           => $tid,
                    'subj'          => $_POST['subj'],
                    'user'          => $main->user['user_name'],
                    'user_from'     => $_POST['recipient'],
                    'date'          => kr_datecms("Y-m-d H:i:s"),
                    'status'        => 0,
                    'type'          => 1
                ), PM);
                
                if(is_ajax()) {
                    //////////
                    $status = $message = 'error';
                    $content = array();
                    $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE uid='{$uinf['uid']}'");
                    if($main->db->sql_numrows($result)>0){
                        $dialog_info = $main->db->sql_fetchrow($result);
                        $result = $main->db->sql_query("SELECT p.*, t.*, u.uid, u.user_id, u.user_name, u.user_avatar FROM ".PM." as p LEFT JOIN ".PM_TEXT." AS t ON(p.tid=t.tid), ".USERS." AS u WHERE ((p.user_from = '{$dialog_info['user_name']}' AND p.user='{$main->user['user_name']}') OR (p.user = '{$dialog_info['user_name']}' AND p.user_from='{$main->user['user_name']}')) AND p.user = u.user_name AND t.tid>".intval($_POST['lastid'])." GROUP BY t.tid ORDER BY p.date ASC");
                        if($main->db->sql_numrows($result)>0){
                            main::inited('function.format_fully');
                            $html = '';
                            while($row = $main->db->sql_fetchrow($result)){
                                $content[] = array(
                                    'sender'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>{$row['user']}</a>",
                                    'avatar'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>".get_avatar($row)."</a>",
                                    'small_avatar'      => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>".get_avatar($row, 'small')."</a>",
                                    'mini_avatar'       => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>".get_avatar($row, 'mini')."</a>",
                                    'date'              => format_fully($row['date']),
                                    'text'              => str_replace("\r", '', parse_bb($row['text'])),
                                    'mesgae_id'         => $row['tid']
                                );
                                $html .= ob_get_contents(); ob_get_clean();
                            }
                            $status = $message = 'ok';
                        } else $message = 'error';
                    } else $message = 'error';
                    echo "<script type='text/javascript'>set_checked_callback('task_ckeck', function(){dcc['task_ckeck'].time=0;}, 1000)</script>";
                    echo json_encode(array(
                        'status'        => $status,
                        'message'       => $message,
                        'content'       => $content,
                        'html'          => $html,
                    ));
                    //////////////
                    kr_exit();
                } else redirect($main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $uinf['uid'])));
            } else {
                if(is_ajax()) { echo json_encode(array('status' => 'error', 'message' => strip_tags($msg), 'content' => '', 'html' => $html)); kr_exit(); }
                else account_pm_new($msg);
            }
        } else redirect($main->url(array('module' => $main->module, 'do' => 'login')));
    }
    
    /**
    * Функция просмотра сообщений
    * 
    * @return void
    */
    function account_pm_show($msg=''){
    global $main, $template;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user()){
            if(!isset($_GET['id'])) redirect(MODULE);
            $result = $main->db->sql_query("SELECT * FROM ".USERS." WHERE uid='".intval($_GET['id'])."'");
            if($main->db->sql_numrows($result)>0){
                $dialog_info = $main->db->sql_fetchrow($result);
                $result = $main->db->sql_query("SELECT p.*, t.*, u.uid, u.user_id, u.user_name, u.user_avatar FROM ".PM." as p LEFT JOIN ".PM_TEXT." AS t ON(p.tid=t.tid), ".USERS." AS u WHERE ((p.user_from = '{$main->user['user_name']}' AND p.type = 1 and p.user = '{$dialog_info['user_name']}') OR (p.user = '{$main->user['user_name']}' AND p.type = 0 and p.user_from = '{$dialog_info['user_name']}')) AND p.user = u.user_name GROUP BY t.tid ORDER BY p.date DESC");
                if($main->db->sql_numrows($result)>0){
                    //Подключаем необходимые модули 
                    main::inited('function.format_fully', 'function.session_tools');
                    main::add2link(__PM_PATH__.'tpl/style.css');
                    main::add2script('includes/javascript/shortcut.js');
                    main::add2script('$.krReady(function(){shortcut.add("Ctrl+Enter",function() {send_pm_message();});})', false);
                    main::add2script(__PM_PATH__.'javascript/script.js');
                    //Подключаем шаблон
                    $template->get_tpl('tpl/show', 'pm', __PM_PATH__);
                    $template->get_subtpl(array(
                        array('get_index' => 'pm', 'new_index' => 'pm_row', 'selector' => '_pm_row')
                    ));
                    $tpl_row = 'pm_message1';
                    $content_row = '';
                    while(($row = $main->db->sql_fetchrow($result))){
                        $template->get_tpl('pm_row', 'pm_row');
                        $template->set_tpl(array(
                            'SENDER'            => "<input class='hide_id' type='hidden' name='hide_id[]' value='{$row['tid']}' />"."<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>{$row['user']}</a>",
                            'AVATAR'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>".get_avatar($row)."</a>",
                            'SMALL_AVATAR'      => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>".get_avatar($row, 'small')."</a>",
                            'MINI_AVATAR'       => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($row['user_id'], $row['uid'])))."' title=''>".get_avatar($row, 'mini')."</a>",
                            'DATE'              => format_fully($row['date']),
                            'TEXT'              => parse_bb($row['text'])."<input class='hide_id' type='hidden' name='hide_id[]' value='{$row['tid']}' />",
                            'MESGAE_ID'         => $row['tid'],
                            'CLASS'             => $tpl_row.($row['pm_read']=='0'?' pm_msg_noread':''),
                        ), 'pm_row', array('start' => '{%', 'end' => '%}'));
                        $content_row .= $template->tpl_create(true, 'pm_row');
                        $tpl_row = $tpl_row=='pm_message1'?'pm_message2':'pm_message1';
                    }
                    set_this_session_update();
                    sql_update(array('pm_read' => '1'), PM, "user_from = '{$main->user['user_name']}' AND user = '{$dialog_info['user_name']}'");
                    $main->db->sql_query("UPDATE ".USERS." SET user_new_pm_count = (SELECT COUNT(*) FROM ".PM." WHERE (user_from = '{$main->user['user_name']}' AND type = 1 AND pm_read = 0) OR (user = '{$main->user['user_name']}' AND type = 0 AND pm_read = 0)) WHERE user_name = '{$main->user['user_name']}'");
                    $template->set_tpl(array(
                        'TITLE'             => $dialog_info['user_name'],
                        'BACK'              => "<a class='backlink' href='".$main->url(array('module' => $main->module, 'do' => 'pm'))."'>{$main->lang['pm_back']}</a>",
                        'NEW_MESSAGE'       => "<a class='linkbutton sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'new'))."' title='{$main->lang['create_pm']}'><span><img style='margin-right: 5px;' class='icon icon-new icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['create_pm']}</a>",
                        'DELETE'            => "<a style='display:none;' class='delete_message linkbutton color_gray' href='#' onclick='return delete_messages();'><span><img style='margin-right: 5px;' class='icon icon-close icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['delete']}</a>",
                        'PM_ROW'            => $content_row,
                        //
                        'ERROR'             => !empty($msg) ? warning($msg, true) : '',
                        'EDITOR'            => editor_small('message', 8),
                        'RECIPIENT'         => in_hide('recipient', $dialog_info['user_name']),
                        'SUBJ'              => in_hide('subj', ''),
                        //
                        'SUBMIT'            => "<input onclick='send_pm_message(); return false;' type='submit' class='submit' title='{$main->lang['send']} Ctrl+Enter' value='{$main->lang['send']}' />",
                        'ATTACH'            => in_hide('attache_page', $main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'attache_page')))."<input type='button' value='{$main->lang['attach']}' class='color_gray attache_button' onclick='return attache_load();' />",
                        'ACTION'            => $main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'send')),
                    ), 'pm', array('start' => '{%', 'end' => '%}'));
                    echo open(true).$template->tpl_create(true, 'pm').close(true);
                    
                } else redirect($main->url(array('module' => 'account', 'do' => 'pm')));
            } else redirect($main->url(array('module' => 'account', 'do' => 'pm')));
        } else redirect($main->url(array('module' => 'account', 'do' => 'login')));
    }
    
    /**
    * Функция главной страницы сообщений
    * 
    * @return bool
    */
    function main_account_pm(){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        if(is_user()){
            $num = isset($_GET['page']) ? intval($_GET['page']) : "1";
            $offset = ($num-1) * 20;
            $result = $main->db->sql_query("SELECT p.mid, p.tid, p.subj, p.user, p.user_from, p.date, p.pm_read, p.status, p.type, t.tid, t.text, u.uid, u.user_id, u.user_name, u.user_avatar FROM ".PM." AS p LEFT JOIN ".PM_TEXT." AS t ON(p.tid=t.tid), ".USERS." AS u, (SELECT (CASE pg.`type` WHEN 1 THEN pg.`user` ELSE pg.user_from END) as usr,max(pg.`mid`) as mxmid from ".PM." AS pg WHERE ((pg.user_from = '{$main->user['user_name']}' AND pg.`type` = 1) OR (pg.`user` = '{$main->user['user_name']}' AND pg.`type` = 0)) GROUP by 1) g WHERE p.`mid`=g.mxmid AND ((p.user_from = g.usr) AND (p.user_from = u.user_name) OR (p.user = g.usr) AND (p.user = u.user_name)) ORDER BY p.date DESC LIMIT {$offset}, 20");
            if(is_ajax() AND !isset($_POST['load_module'])){
                $count = $main->db->sql_numrows($result);
                if($main->db->sql_numrows($result)>0){
                    $content = array();
                    main::inited('function.format_fully');
                    while(($rows = $main->db->sql_fetchrow($result))) $content[] = array(
                        'class_row'         => '',
                        'avatar'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>".get_avatar($rows)."</a>",
                        'small_avatar'      => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>".get_avatar($rows, 'small')."</a>",
                        'sender'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>{$rows['user_name']}</a>",
                        'date'              => format_fully($rows['date']),
                        'subject'           => "<a class='pm_subj sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $rows['uid']))."' title='{$main->lang['to_new_pm']}'>{$rows['subj']}</a>",
                        'small_text'        => cut_text(strip_tags($rows['text']), 6),
                        'small_text_link'   => "<a class='pm_text_l sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $rows['uid']))."' title=''>".cut_text(strip_tags($rows['text']), 6)."</a>",
                        'text'              => strip_tags($rows['text']),
                    );
                    echo json_encode(array(
                        'content'   => $content,
                        'count'     => $count,
                        'show'      => 20,
                
                    ));
                } else echo json_encode(array('content' => array(), 'count' => 0, 'show' => 20));
                kr_exit();
            }
            if($main->db->sql_numrows($result)>0){
                $data = array();
                while(($rows = $main->db->sql_fetchrow($result))) $data[] = $rows;
                content_account_pm($data);
            } else content_account_pm();
        } else redirect($main->url(array('module' => 'account', 'do' => 'login')));
    }
    
    
    function content_account_pm($data=array()){
    global $main, $template, $tpl_create;
        if(hook_check(__FUNCTION__)) return hook();
        main::inited('function.format_fully');
        main::add2link(__PM_PATH__.'tpl/style.css');
        main::add2script(__PM_PATH__.'javascript/script.js');
        
        $template->get_tpl('tpl/list', 'pm', __PM_PATH__);
        $template->get_subtpl(array(
            array('get_index' => 'pm', 'new_index' => 'pm_row', 'selector' => '_pm_row'),
            array('get_index' => 'pm', 'new_index' => 'pm_row_nolist', 'selector' => '_pm_row_nolist')
        ));
        $tr_row = 'pm_row1'; $last_id = 0; $pm_rows = $msg = '';
        if(!empty($data)){
            foreach($data as $k=>$rows){
                $template->get_tpl('pm_row', 'pm_row');
                $template->set_tpl(array(
                    'SUBJECT'           => "<a class='pm_subj sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $rows['uid']))."' title='{$main->lang['to_new_pm']}'>{$rows['subj']}</a>",
                    'SENDER'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>{$rows['user_name']}</a>",
                    'AVATAR'            => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>".get_avatar($rows)."</a>",
                    'SMALL_AVATAR'      => "<a class='user_info' href='".$main->url(array('module' => $main->module, 'do' => 'user', 'id' => case_id($rows['user_id'], $rows['uid'])))."' title=''>".get_avatar($rows, 'small')."</a>",
                    'DATE'              => format_fully($rows['date']),
                    'SMALL_TEXT'        => cut_text(strip_tags($rows['text']), 6),
                    'SMALL_TEXT_LINK'   => "<a class='pm_text_l sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'show', 'id' => $rows['uid']))."' title=''>".cut_text(strip_tags($rows['text']), 6)."</a>",
                    'TEXT'              => strip_tags($rows['text']),
                    'CLASS_ROW'         => (($rows['pm_read']==0?'pm_noread ':'').$tr_row).' user-'.$rows['user'].' pm_row_'.$rows['mid'],
                ), 'pm_row', array('start' => '{%', 'end' => '%}'));
                $pm_rows .= $template->tpl_create(true, 'pm_row');
                $tr_row = $tr_row=='pm_row1' ? 'pm_row2' : 'pm_row1';
                $last_id++;
            };
        } else {
            $template->get_subtpl(array(
                array('get_index' => 'pm', 'new_index' => 'pm_table', 'selector' => '_pm_table')
            ));
             $template->set_tpl(array(
                'MESSAGE'   => $main->lang['noinfo'],            
            ), 'pm_row_nolist', array('start' => '{%', 'end' => '%}'));
            $msg = $template->tpl_create(true, 'pm_row_nolist');
        }
        open();
        $template->set_tpl(array(
            'PM_ROW'            => $pm_rows,
            'SEARCH'            => "<input class='search_pm' id='search_recipient' type='text' /><span class='icon_b icon-search pm_search_icon'><img src='includes/images/pixel.gif' alt='' /></span><script type='text/javascript'>\$('#search_recipient').attr('placeholder', '{$main->lang['pm_search_user']}')</script>",
            'CLOSE_SEARCH'      => "<a class='color_gray linkbutton close_search' href='#' onclick='return close_search_pm();'><span><img style='margin-right: 5px;' class='icon icon-close icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['cancel']}</a>",
            'TITLE'             => $main->lang['priv_message'],
            'PM_ROW_PAGES'      => "<input type='text' value='1' class='autoloadpage autoloadclass' />",
            'PM_ROW_NOLIST'     => $msg,               //Error message
            'PM_TABLE'          => '',                 //Error message
            'BACK'              => "<a class='backlink' href='".$main->url(array('module' => $main->module))."'>{$main->lang['pm_back']}</a>",
            'LANG_SUBJECT'      => $main->lang['subj'],
            'LANG_SENDER'       => $main->lang['sender'],
            'LANG_DATE'         => $main->lang['date'],
            'NEW_MESSAGE'       => "<a class='linkbutton sys_link' href='".$main->url(array('module' => $main->module, 'do' => 'pm', 'op' => 'new'))."' title='{$main->lang['create_pm']}'><span><img style='margin-right: 5px;' class='icon icon-new icon_relative' src='includes/images/pixel.gif' alt='' /></span>{$main->lang['create_pm']}</a>",
        ), 'pm', array('start' => '{%', 'end' => '%}'));
        echo $template->tpl_create(true, 'pm');
        close();
    }
    
    function controls_account_hook($msg=""){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        ob_start();
        controls_account($msg);
        $content = ob_get_contents(); ob_get_clean();
        $content = preg_replace("#name='user_viewemail' /></td></tr>#is", 
            "name='user_viewemail' /></td></tr>".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['user_pm_send']}:</td><td class='form_input_account'>".in_chck('user_pm_send', 'checkbox', ($main->user['user_pm_send']==1)?true:false)."</td></tr>\n".
            "<tr class='row_tr'><td class='form_text'>{$main->lang['notify_receiving_pm']}:</td><td class='form_input_account'>".in_chck('user_new_pm_window', 'checkbox', ($main->user['user_new_pm_window']==1)?true:false)."</td></tr>\n",
            $content);
        echo $content;
    }
    
    function controls_account_save_hook(){
    global $main, $row;
        if(hook_check(__FUNCTION__)) return hook();
        filter_arr(array('user_pm_send', 'user_new_pm_window'), POST, TAGS);
        sql_update(array(
            'user_new_pm_window'=> (isset($_POST['user_new_pm_window']) AND $_POST['user_new_pm_window']=='on') ? 1 : 0,
            'user_pm_send'      => (isset($_POST['user_pm_send']) AND $_POST['user_pm_send']=='on') ? 1 : 0,            
        ), USERS, "user_name='{$main->user['user_name']}'");
        save_controls_account();
    }
    
    function information_pm_add($msg=''){
       global $main, $row;
       function kr_exit_pm_done(){return false;}
       pm_language();
       if(!isset($_POST['id'])) hook_register('kr_exit', 'kr_exit_pm_done');
       ob_start();
       $main->db->cache = array(); $main->db->cache_fetchrow = true;
       information($msg);
       $main->db->cache_fetchrow = false;
       $content = ob_get_contents(); ob_get_clean();
       if(count($main->db->cache)>0){
          hook_unregister('kr_exit', 'kr_exit_pm_done');
          if(isset($_POST['show']) AND $_POST['show']=='json'){
             $json = json_decode($content, true);
             $json['lang']['send_pm'] = $main->lang['send_pm'];
             $json['buttons'] = array_merge(array(array('name' => $main->lang['send_pm'], 'onclick' => 'location.href=data.pm_send_url;')), $json['buttons']);
             echo json_encode($json);
             kr_exit();
          } else {
             if(!isset($_POST['id'])){
                $info = $main->db->cache[0];
                $_arr = explode("<a class='linkbutton at_b'", $content);
                $_arr[0] .= "<a class='linkbutton at_b' href='".$main->url(array('module' => 'account', 'do' => 'pm', 'op' => 'new', 'user' => $info['uid']))."' title='{$main->lang['send_pm']}'>{$main->lang['send_pm']}</a>";
                echo implode("<a class='linkbutton at_b'", $_arr);
             }
          }
       } else echo $content;
    }
    /**
    * hoook для модификации меню модуля accaunt
    * 
    * @param mixed $icon_navi_var
    */
    function icon_navi_pm($icon_navi_var){
        global $main;
        pm_language();
        $val = array('title' => $main->lang['priv_message'], 'url' => $main->url(array('module' => $main->module, 'do' => 'pm')), 'image' => __PM_PATH__.'images/messages.png');
        array_splice($icon_navi_var, 1, 0, array($val));
        return $icon_navi_var;
    }
    
    hook_register('information', 'information_pm_add');
    hook_register('main_account', 'main_account_hook');
    hook_register('controls_account', 'controls_account_hook');
    hook_register('save_controls_account', 'controls_account_save_hook');
    hook_register('icon_navi', 'icon_navi_pm');
}

//Вынести Конфигурацию в хук
if(defined('ADMIN_FILE')){
    function _get_links_module_hook(){
        if(hook_check(__FUNCTION__)) return hook();
        $m = _get_links_module();
        $m[] = __PM_PATH__.'pm.links.php';
        return $m;
    }
    
    function admin_module_exists_hook($path, $name, $ext=''){
    global $main;
        if($main->module=='pm'){
            return true;
        } else return admin_module_exists($path, $name, $ext);
    }
    
    function get_php_content_hook($file, $eval=""){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        if($main->module=='pm'){
            pm_language();
            $file = __PM_PATH__.'pm.admin.php';
            if(!empty($eval)) eval($eval);
            ob_start();
            main::required($file);
            $content = ob_get_contents(); ob_end_clean();
            return $content;
        } else return get_php_content($file, $eval);
    }
    
    global $list_optimize_date, $default_optimize_date;
    $list_optimize_date = array('pm'=>'optimize_pm');
    $default_optimize_date = array('pm'=>"P1Y");
    
    function adm_optimization_pm(){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        pm_language();
        ob_start();
        adm_optimization_main();
        $content = ob_get_contents(); ob_get_clean();
        $_arr = explode("<div class='elem_op'>", $content);
        $_arr[8] .= adm_gen_optimization_record('pm',$main->lang['optimize_pm'], $main->lang['optimize_pm_d']);
        echo implode("<div class='elem_op'>", $_arr);
    }
    
    function adm_run_optimization_pm(){
    global $main, $userconf;
        if(hook_check(__FUNCTION__)) return hook();
        
        $added=$_POST['added'];
        $dateb=$_POST['dateb'];
        adm_before_optimization();
        hook_register('adm_before_optimization', 'adm_before_optimization_null');
        foreach ($added as $key => $value) {
            if($value=='on'){
                if(isset($dateb[$key])){
                    $date_dec=strtotime($dateb[$key]);
                    $date_db=date("Y-m-d",$date_dec);
                }
                if($key=='pm'){
                    // удаляем личные сообщения
                    $result = $main->db->sql_query("SELECT tid FROM ".PM." WHERE `date`<'{$date_db}'");
                    $main->db->sql_query("DELETE FROM ".PM." WHERE `date`<'{$date_db}'");
                    $main->db->sql_query("DELETE t.* FROM ".PM_TEXT." t LEFT JOIN ".PM." p ON (p.tid=t.tid) WHERE p.mid is null");
                    if($main->db->sql_numrows($result)>0){
                        while((list($tid) = $main->db->sql_fetchrow($result))){
                            if(file_exists($userconf['directory'].$tid) AND is_dir($userconf['directory'].$tid)) remove_dir($userconf['directory'].$tid);
                        }
                    }
                }
            }
        }
        adm_run_optimization();
        hook_unregister('adm_before_optimization', 'adm_before_optimization_null');
    }
    
    function adm_before_optimization_null(){return false;}
    
    function users_custom_delete_pm(){
    global $main;
        if(hook_check(__FUNCTION__)) return hook();
        pm_language();
        ob_start();
        users_custom_delete();
        $content = ob_get_contents(); ob_get_clean();
        $_arr = explode("<tr class='row_tr'>", $content);
        $_arr[7] .= row_csdelete('pm', array($main->lang['delete']), $main->lang['priv_message']);
        echo implode("<tr class='row_tr'>", $_arr);
    }
    
    function users_exec_delete_pm(){
    global $main, $userconf;
        if(hook_check(__FUNCTION__)) return hook();
        //Удаление ЛС
        if(isset($_GET['id'])){
            list($user_id,$user_name)=$main->db->sql_fetchrow($main->db->sql_query("SELECT user_id,user_name,user_avatar FROM ".USERS." WHERE uid=".intval($_GET['id']).""));
            $result = $main->db->sql_query("SELECT tid FROM ".PM." WHERE user_from='{$user_name}' OR user='{$user_name}'");
            $main->db->sql_query("DELETE FROM ".PM." WHERE user_from='{$user_name}' OR user='{$user_name}'");
            $main->db->sql_query("DELETE t.* FROM ".PM_TEXT." t LEFT JOIN ".PM." p ON (p.tid=t.tid) WHERE p.mid is null");
            if($main->db->sql_numrows($result)>0){
                while((list($tid) = $main->db->sql_fetchrow($result))){
                    if(file_exists($userconf['directory'].$tid) AND is_dir($userconf['directory'].$tid)) remove_dir($userconf['directory'].$tid);
                }
            }
        }
        users_exec_delete();
    }
    
    if($main->module=='config'){
        
        function messages_pm_add(){
        global $main;
            if(hook_check(__FUNCTION__)) return hook();
            pm_language();
            ob_start();
            messages();
            $content = ob_get_contents(); ob_get_clean();
            $_arr = explode("<tr class='row_tr'>", $content);
            $_arr[12] .= "<tr class='row_tr'><td class='form_text2'><b>{$main->lang['send_pmr']}</b>:<br /><i>{$main->lang['send_pmr_d']}</i></td><td class='form_input2'>".in_chck('send_pmr', 'input_checkbox', isset($config['send_pmr'])?$config['send_pmr']:"")."</td></tr>\n";
            echo implode("<tr class='row_tr'>", $_arr);
        }
        
        function admin_saves_config_pm(){
        global $config;
            if(hook_check(__FUNCTION__)) return hook();
            if(!isset($config['send_pmr'])) $config['send_pmr']='';
            saves_config();
        }
        
        hook_register('saves_config', 'admin_saves_config_pm');
        hook_register('messages', 'messages_pm_add');
    }
    
    function pm_optimization(){
        pm_language();
        adm_config_optimization();
    }
    
    hook_register('adm_config_optimization', 'pm_optimization');
    hook_register('users_custom_delete', 'users_custom_delete_pm');
    hook_register('users_exec_delete', 'users_exec_delete_pm');
    
    hook_register('adm_run_optimization', 'adm_run_optimization_pm');
    hook_register('adm_optimization_main', 'adm_optimization_pm');
    
    hook_register('_get_links_module', '_get_links_module_hook');
    hook_register('admin_module_exists', 'admin_module_exists_hook');
    hook_register('get_php_content', 'get_php_content_hook');    
}

global $tid_buf, $subject_buf;
$subject_buf='';
function send_mail2pm_send($to, $sender, $from, $from_name, $subject, $body, $reply=array(), $attach=array(), $bcc=array(), $cc=array()){
global $main, $tid_buf, $subject_buf;
    if(hook_check(__FUNCTION__)) return hook();
    if(isset($main->config['send_pmr']) AND $main->config['send_pmr']==ENABLED) {
       if($subject_buf!=$subject){
          $tid = $main->db->sql_nextid($main->db->sql_query("INSERT INTO ".PM_TEXT." (text) VALUES ('".bb($body)."')"));
          $tid_buf=$tid;
          $subject_buf=$subject;
       } else $tid=$tid_buf;
       if(count($bcc)==0) $mails=array(array('name'=>$sender,'mail'=>$to));
       else $mails=$bcc;
       foreach ($mails as $key => $value) {
          $main->db->sql_query("INSERT INTO ".PM." (tid, subj, user, user_from, date, status, type) ".
             "VALUES ('{$tid}', '{$subject}', '{$main->user['user_name']}', '{$value['name']}', '".kr_datecms("Y-m-d H:i:s")."', '0', '1')");
       }
    }
    send_mail($to, $sender, $from, $from_name, $subject, $body, $reply, $attach, $bcc, $cc);
}

hook_register('send_mail', 'send_mail2pm_send');


function send_message_pm($user_from, $user_to, $subj, $text_message){
   global $main;
   if(hook_check(__FUNCTION__)) return hook();
   //Проверка пользователя
   $user_sel = $main->db->sql_query("SELECT uid, user_name, user_email, user_pm_send FROM ".USERS." WHERE user_name='{$user_to}'");
   if($main->db->sql_numrows($user_sel)>0){
      $uinf = $main->db->sql_fetchrow($user_sel);
      //Сохраняем текст
      $tid = $main->db->sql_nextid($main->db->sql_query("INSERT INTO ".PM_TEXT." (text) VALUES ('".bb($text_message)."')"));
      //Отправка уведомления 
      $subj=addslashes($subj);
      $main->db->sql_query("INSERT INTO ".PM." (tid, subj, user, user_from, date, status, type) VALUES ('{$tid}', '{$subj}', '{$user_to}', '{$user_from}', '".kr_datecms("Y-m-d H:i:s")."', '2', '0')");
      $main->db->sql_query("INSERT INTO ".PM." (tid, subj, user, user_from, date, status, type) VALUES ('{$tid}', '{$subj}', '{$user_to}', '{$user_from}', '".kr_datecms("Y-m-d H:i:s")."', '0', '1')");
      return true;
   } else return false;
}
hook_register('send_message', 'send_message_pm');

function check_new_pm_message(){
global $main;
    //delete user_new_pm_window
    if(is_user() AND isset($main->user['user_new_pm_count']) AND $main->user['user_new_pm_count']>0 AND $main->module!='account'){            
        $result = $main->db->sql_query("SELECT t.text, u.user_id, u.uid, u.user_name, u.user_avatar, u.user_email FROM ".PM." AS p LEFT JOIN ".PM_TEXT." AS t ON (p.tid = t.tid), ".USERS." AS u, (SELECT pg.mid, max(pg.mid) as mxmid from ".PM." AS pg WHERE pg.user_from = '{$main->user['user_name']}' AND pg.type = 1 GROUP BY 1) g WHERE p.mid=g.mxmid AND p.user = u.user_name AND p.pm_read = '0' AND p.type = '1' AND user_from = '{$main->user['user_name']}' GROUP BY u.user_name ORDER BY p.date DESC");
        while($row = $main->db->sql_fetchrow($result)){
            main::add2script("\$.notify(\"{$main->lang['new_post']}\", \"".str_replace("\r", '', cut_text(strip_tags($row['text']), 20))."\", '".$main->url(array('module' => 'account', 'do' => 'pm', 'op' => 'show', 'id' => $row['uid']))."', \"".get_avatar($row)."\");", false);
        }
    }
    return load_module();
}

hook_register('load_module', 'check_new_pm_message');

function task_ckeck(){
    if(isset($_SESSION['task'])){
        foreach($_SESSION['task'] as $k => $v){
            //echo unserialize($_SESSION['task']);
            $func = create_function('', unserialize($_SESSION['task'][$k]));
            $func();
            $_SESSION['task'][$k] = '';
        }
    }
}
?>