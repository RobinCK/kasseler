<?php
/**
* Языковый файл
* 
* @author Dmitrey Browko
* @copyright Copyright (c)2007-2012 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!defined('FUNC_FILE')) die('Access is limited');

$lang = array(
    'optimization_add'              => 'оптимизировать',
    'optimization_selected'         => 'Выбранные оптимизации',
    'optimization_master'           => 'Мастер оптимизации Kasseler CMS',
    'optimize_description'          => 'Добро пожаловать в мастер оптимизации.<br />Здесь вы можите удалить старую информацию, убрать неактивных пользователей, оптимизировать базу данных.',
    'optimize_forum'                => 'Очистка устаревших топиков форума',
    'optimize_forum_d'              => 'Очистка топиков форума по которым не было новых постов позже выбранной даты',
    'optimize_comment'              => 'Очистка устаревших комментариев',
    'optimize_comment_d'            => 'Удаление всех комментариев которые были добавлены до выбранной даты',
    'optimize_log_access'           => 'Очистка логов связанных с безопасностью',
    'optimize_log_access_d'         => 'Удаление логов ошибок авторизации, php, http, SQL.',
    'optimize_user'                 => 'Удаление пользователей',
    'optimize_user_activation'      => 'Удаление не активировавшихся пользователей',
    'optimize_user_activation_d'    => 'Удаление пользователей которые зарегистрировались <b>раньше</b> выбранной даты, и не активировали учетную запись.<br/><b>Внимание, отключенные пользователи тоже подпадают под выбранную категорию.</b>',
    'optimize_user_last_visit'      => 'Удаление давно не заходивших пользователей',
    'optimize_user_last_visit_d'    => 'Удаление пользователей которые не заходили <b>позже</b> выбранной даты',
    'optimize_custom'               => 'Очистка информации по модулю "{MODULE}"',
    'optimize_custom_d'             => 'Удаление всех публикаций которые были добавлены <b>ранее</b> выбранной даты',
    'optimize_config'               => 'Настройка даты(по умолчанию) для очистки информации',
    'clear_to'                      => 'очистить все до ',
    'change_table'                  => 'Изменения в таблице <b>{TABLE}</b>: удалено {COUNT} записей',
    'period_day'                    => 'дней',
    'period_month'                  => 'месяцев',
    'period_year'                   => 'лет',
);                          
?>