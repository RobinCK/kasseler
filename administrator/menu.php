<?php
if (!defined('ADMIN_FILE')) die("Hacking attempt!");
global $adminfile, $admin_menu;
    $admin_menu = array(
        'home'      => array(
            'submenu' => array(
                array('title' => 'statistic',       'link' => "{$adminfile}?module=statistic"),
                array('title' => 'server_info',     'link' => "{$adminfile}?module=phpinfo"),
                array('title' => 'visite_kasseler', 'link' => "http://www.kasseler-cms.net/"),
            ),
            'link'    => $adminfile
        ), 
        'management'   => array(
            'submenu' => array(
                array('title' => 'access_rights',   'link' => "{$adminfile}?module=admin"),
                array('title' => 'ad_blocks',       'link' => "{$adminfile}?module=blocks"),
                array('title' => 'ad_moduleslist',  'link' => "{$adminfile}?module=moduleslist"),
                array('title' => 'ad_users',        'link' => "{$adminfile}?module=users"),
                array('title' => 'groups',          'link' => "{$adminfile}?module=groups"),
                array('title' => 'categories',      'link' => "{$adminfile}?module=categories"),
                array('title' => 'ad_messages',     'link' => "{$adminfile}?module=messages"),
                array('title' => 'ad_rss',          'link' => "{$adminfile}?module=rss"),
                array('title' => 'ad_sendmail',     'link' => "{$adminfile}?module=sendmail"),
            ),
            'link'    => "{$adminfile}?module=management"
        ),
        'modules'   => array(
            'submenu' => array(
                array('title' => 'news',            'link' => "{$adminfile}?module=news"),
                array('title' => 'pages',           'link' => "{$adminfile}?module=pages"),
                array('title' => 'files',           'link' => "{$adminfile}?module=files"),
                array('title' => 'forum',           'link' => "{$adminfile}?module=forum"),
                array('title' => 'media',           'link' => "{$adminfile}?module=media"),
                array('title' => 'shop',            'link' => "{$adminfile}?module=shop"),
                array('title' => 'jokes',           'link' => "{$adminfile}?module=jokes"),
                array('title' => 'top_site',        'link' => "{$adminfile}?module=top_site"),
                array('title' => 'faq',             'link' => "{$adminfile}?module=faq"),
                array('title' => 'voting',          'link' => "{$adminfile}?module=voting")
            ),
            'link'    => "{$adminfile}?module=modules"
        ),
        'tools'    => array(
            'submenu' => array(
                array('title' => 'filemanager',     'link' => "{$adminfile}?module=filemanager"),
                array('title' => 'sql_db',          'link' => "{$adminfile}?module=database&amp;do=sql"),
                array('title' => 'backup_db',       'link' => "{$adminfile}?module=database&amp;do=backup"),
                array('title' => 'optimize_db',     'link' => "{$adminfile}?module=database"),
                array('title' => 'sitemap',         'link' => "{$adminfile}?module=sitemap")
            ),
            'link'    => ""
        )
    );
?>