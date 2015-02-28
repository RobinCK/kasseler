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
    'link_dir'        => 'Path to the file or directory',
    'accept'          => 'I accept',
    'doaccept'        => 'I do not accept',
    'next'            => 'Next',
    'russian'         => 'Russian',
    'english'         => 'English',
    'status'          => 'Status',
    'yes'             => 'Created',
    'no'              => 'Not created',
    'table'           => 'Table',
    'admin'           => 'Administrator name',
    'email'           => 'Administrator e-mail',
    'passwordadmin'   => 'Administrator password',
    'repasswordadmin' => 'Confirm password',
    'connect_error'   => '<li>Unable to connect to the database</li>',
    'database'        => 'Database',
    'server'          => 'Database server',
    'user'            => 'Database username',
    'password'        => 'user password database',
    'dbname'          => 'Database Name',
    'prefix'          => 'Database tables prefix',
    'charset'         => 'Encryption databases',
    'configdb_error'  => '<li>Not set access rights to the file includes/config/configdb.php</li>',
    'config_error'    => '<li>Not set access rights to the file includes/config/config.php</li>',
    'noyeslicense'    => '<li>You do not accept the license agreement! Further installation of the system is impossible.</li> ',
    'chmod_this'      => 'Current parameter',
    'chmod_corect'    => 'Required setting',
    'chmod_dir'       => 'Set permissions on directories',
    'chmod_file'      => 'Set permissions on configuration files',
    'step1_title'     => 'wizard Kasseler CMS',
    'step2_title'     => 'License Agreement',
    'step3_title'     => 'Set access rights',
    'step4_title'     => 'Database Configuration',
    'step5_title'     => 'The creation of the administrator',
    'step6_title'     => 'installed',
    'step1_content'   => '<h1 class="install">Wizard script</h1><br />Before installation, make sure that all files uploaded to the server distribution, and exhibited the necessary access rights for files and folders. <br /> <br /> <b>Warning</b>: When you install the script creates the database structure, create an administrator account, but also prescribed the basic system settings, so after a successful installation, delete the install.php file and install directory, to avoid re-installation script!',
    'step3_content'   => 'Before you install, make sure that all access rights to files and directories are installed exactly as shown in the table. For more information on setting the access permissions (CHMOD) on different FTP managers can be found on the official site of the system.',
    'error_update'    => 'Failed to install updates database',
    'cron_config'     => "Do not forget to edit the file <b>includes/config/configdb.php</b> value 'revision' => '{REVISION}'.",
);
?>