<?php
/**
* Блок популярных тегов для модуля pages
* 
* @author Wit
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://kasseler-cms.net/
* @filesource blocks/block-tags_pages.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}

echo kr_create_tags('pages', 50);

?>	