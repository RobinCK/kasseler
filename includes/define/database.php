<?php
/**
* Дефайны таблица БД
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource includes/define/database.php 
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

global $database;
@define("ACC",           "{$database['prefix']}_acc");                //Таблица прав доступа к форумам
@define("ATTACH",        "{$database['prefix']}_attach");             //Таблица прикрепленных файлов
@define("ALBOM",         "{$database['prefix']}_albom");              //Таблица альбома
@define("SESSIONS",      "{$database['prefix']}_sessions");           //Таблица сессий пользователей
@define("USERS",         "{$database['prefix']}_users");              //Таблица пользователей
@define("GROUPS",        "{$database['prefix']}_groups");             //Таблица групп пользователей
@define("MESSAGE",       "{$database['prefix']}_message");            //Таблица сообщений на главной
@define("NEWS",          "{$database['prefix']}_news");               //Таблица новостей
@define("FILES",         "{$database['prefix']}_files");              //Таблица модуля файлов
@define("MEDIA",         "{$database['prefix']}_media");              //Таблица модуля Media
@define("JOKES",         "{$database['prefix']}_jokes");              //Таблица модуля анекдоты
@define("MODULES",       "{$database['prefix']}_modules");            //Таблица модулей системы
@define("BLOCKS",        "{$database['prefix']}_blocks");             //Таблица блоков
@define("CAT",           "{$database['prefix']}_categories");         //Таблица категорий
@define("CALENDAR",      "{$database['prefix']}_calendar");           //Таблица блока "календарь"
@define("FAQ",           "{$database['prefix']}_faq");                //Таблица модуля FAQ
@define("VOTING",        "{$database['prefix']}_voting");             //Таблица модуля опросы
@define("TOPSITES",      "{$database['prefix']}_topsites");           //Таблица модуля топ сайтов
@define("COMMENTS",      "{$database['prefix']}_comment");            //Таблица комментариев 
@define("POSTS",         "{$database['prefix']}_forum_posts");        //Таблица сообщений форума
@define("TOPICS",        "{$database['prefix']}_forum_topics");       //Таблица тем форума
@define("FORUMS",        "{$database['prefix']}_forum_forums");       //Таблица форумов
@define("CAT_FORUM",     "{$database['prefix']}_forum_categories");   //Таблица категорий форума
@define("FORUM_ACC",     "{$database['prefix']}_forum_acc");          //Таблица доступа к форумам
@define("FORUM_SEARCH",  "{$database['prefix']}_forum_search");       //Таблица поиска для форума
@define("FORUM_KEYS",    "{$database['prefix']}_forum_search_keys");  //Таблица ключей поиска для форума
@define("FORUM_SUBSCRIBE","{$database['prefix']}_forum_subscription");//Таблица подписок на forum topic
@define("FORUM_READ",    "{$database['prefix']}_forum_read");         //Таблица форума о прочтении тем
@define("PAGES",         "{$database['prefix']}_pages");              //Таблица модуля статьи
@define("PM",            "{$database['prefix']}_pm");                 //Таблица личных сообщений
@define("PM_TEXT",       "{$database['prefix']}_pm_text");            //Таблица текстов к личным сообщениям
@define("MENU",          "{$database['prefix']}_menu");               //Таблица блока "меню сайта"
@define("SEARCH",        "{$database['prefix']}_search");             //Таблица поиска на сайте
@define("SEARCH_KEY",    "{$database['prefix']}_search_keys");        //Таблица ключей поиска на сайте
@define("SHOP",          "{$database['prefix']}_shop");               //Таблица магазина
@define("SHOP_CLIENT",   "{$database['prefix']}_shop_clients");       //Таблица заказов с магазина
@define("RADIO",         "{$database['prefix']}_internet_radio");     //Таблица radio
@define("FAVORITE",      "{$database['prefix']}_favorite");           //Таблица закладок
@define("TAG",           "{$database['prefix']}_tags");               //Таблица тегов
@define("ROBOT",         "{$database['prefix']}_robot");              //Таблица поисковых ботов
@define("AUDIO_AUTHORS", "{$database['prefix']}_audio_authors");      //Таблица авторов к аудио записям
@define("AUDIO",         "{$database['prefix']}_audio");              //Таблица аудио записей
@define("STATIC_PAGE",   "{$database['prefix']}_static");             //Таблица страниц
@define("REPORTS",       "{$database['prefix']}_forum_reports");      //Таблица жалоб
@define("SYSTEMDB",      "{$database['prefix']}_system");             //Таблица настроек хранимых в БД
@define("RATINGS",       "{$database['prefix']}_rating");             //Таблица рейтинга информации
?>
