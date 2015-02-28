<?php
/**
* Языковый файл
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @version 2.0
*/
if (!@defined("INSTALLCMS")) die("Access is limited");
global $install_lang;
$install_lang = array(
      'link_dir'               => 'Путь к файлу или каталогу',
      'accept'                 => 'Принимаю',
      'doaccept'               => 'Не принимаю',      
      'next'                   => 'Далее',
      'russian'                => 'Русский',
      'english'                => 'Английский',
      'status'                 => 'Статус',
      'yes'                    => 'Создана',
      'no'                     => 'Не создана',
      'table'                  => 'Таблица',
      'admin'                  => 'Имя администратора',
      'email'                  => 'E-mail администратора',
      'passwordadmin'          => 'Пароль администратора',
      'repasswordadmin'        => 'Повторите пароль',
      'connect_error'          => '<li>Не удалось подключится к базе данных</li>',
      'database'               => 'База данных',
      'server'                 => 'Сервер базы данных',
      'user'                   => 'Имя пользователя базы данных',
      'password'               => 'Пароль пользователя базы данных',
      'dbname'                 => 'Имя базы данных',
      'prefix'                 => 'Префикс таблиц базы данных',
      'charset'                => 'Кодировка базы данных',
      'configdb_error'         => '<li>Не установлены права доступа к файлу includes/config/configdb.php</li>',
      'config_error'           => '<li>Не установлены права доступа к файлу includes/config/config.php</li>',
      'noyeslicense'           => '<li>Вы не приняли условие лицензионного соглашения! Дальнейшая установка системы невозможна.</li>',
      'chmod_this'             => 'Текущий параметр',
      'chmod_corect'           => 'Требуемый параметр',      
      'chmod_dir'              => 'Установка прав доступа на каталоги',
      'chmod_file'             => 'Установка прав доступа на файлы конфигурации',
      'step1_title'            => 'Мастер установки Kasseler CMS',
      'step2_title'            => 'Лицензионное соглашение',
      'step3_title'            => 'Установка прав доступа',      
      'step4_title'            => 'Конфигурация базы данных',      
      'step5_title'            => 'Создание администратора',      
      'step6_title'            => 'Установка системы',      
      'step1_content'          => '<h1 class="install">Мастер установки скрипта</h1><br />Прежде чем начать установку убедитесь, что все файлы дистрибутива загружены на сервер, а также выставлены необходимые права доступа для папок и файлов.<br /><br /><b>Внимание</b>: при установки скрипта создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки системы, поэтому после успешной установки удалите файл install.php и каталог install во избежание повторной установки скрипта!',
      'step3_content'          => 'Перед началом установки убедитесь, что все права доступа к файлам и каталогам установлены именно так как показано в таблице. Подробную информацию по установки прав доступа (CHMOD) на разных FTP менеджерах можно найти на официальном сайте системы.',
      'error_update'           => 'Ошибка установки обновлений БД',
      'cron_config'            => "Не забудьте отредактировать в файле <b>includes/config/configdb.php</b> значение 'revision' => '{REVISION}'.",
);
?>