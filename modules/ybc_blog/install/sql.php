<?php
/**
 * 2007-2019 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;
    $sqls = array();
    $sqls[] = "
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_category` (
          `id_category` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `id_parent` INT(11) NOT NULL,
          `image` varchar(500) NOT NULL,
          `thumb` varchar(500) NOT NULL,
          `added_by` int(11) NOT NULL,
          `modified_by` int(11) NOT NULL,
          `enabled` tinyint(1) NOT NULL DEFAULT '1',
          `datetime_added` datetime NOT NULL,
          `datetime_modified` datetime NOT NULL,
          `sort_order` int(11) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id_category`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[]="
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_category_shop` (
          `id_category` int(10) unsigned NOT NULL,
          `id_shop` int(11) NOT NULL,
          PRIMARY KEY (`id_category`,`id_shop`)
        )";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_category_lang` (
      `id_category` int(11) NOT NULL,
      `id_lang` int(11) NOT NULL,
      `meta_title` varchar(2000)  NOT NULL,
      `title` varchar(2000)  NOT NULL,
      `description` text ,
      `url_alias` varchar(700) NOT NULL,
      `meta_keywords` varchar(5000)  NOT NULL,
      `meta_description` text,
      PRIMARY KEY (`id_category`,`id_lang`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
$sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_comment` (
  `id_comment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `name` varchar(5000)  NOT NULL,
  `email` varchar(5000)  NOT NULL,
  `id_post` int(11) NOT NULL,
  `subject` varchar(2000)  NOT NULL,
  `comment` text ,
  `reply` text,
  `replied_by` int(11) NOT NULL,
  `customer_reply` int(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `viewed` int(11) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `datetime_added` datetime NOT NULL,
  `reported` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_comment`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_gallery` (
  `id_gallery` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(1000) NOT NULL,
  `thumb` varchar(1000) NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_gallery`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[]="
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_gallery_shop` (
          `id_gallery` int(10) unsigned NOT NULL,
          `id_shop` int(11) NOT NULL,
          PRIMARY KEY (`id_gallery`,`id_shop`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_gallery_lang` (
  `id_gallery` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `title` varchar(1000) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id_gallery`,`id_lang`)
  ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
  $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post` (
  `id_post` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_category_default` INT(11),
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `products` varchar(1000) NOT NULL,
  `thumb` varchar(1000) NOT NULL,
  `image` varchar(500) NOT NULL,
  `added_by` int(11) NOT NULL,
  `is_customer` INT(1) NOT NULL,
  `modified_by` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `datetime_added` datetime NOT NULL,
  `datetime_modified` datetime NOT NULL,
  `datetime_active` date,
  `sort_order` int(11) NOT NULL DEFAULT '1',
  `click_number` int(11) NOT NULL DEFAULT '0',
  `likes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_post`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[]="
    CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post_shop` (
      `id_post` int(10) unsigned NOT NULL,
      `id_shop` int(11) NOT NULL,
      PRIMARY KEY (`id_post`,`id_shop`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post_category` (
      `id_post` int(11) NOT NULL,
      `id_category` int(11) NOT NULL,
      `position` INT (11),
      PRIMARY KEY (`id_post`,`id_category`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post_lang` (
  `id_post` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `title` varchar(2000) NOT NULL,
  `url_alias` varchar(700) NOT NULL,
  `meta_title` varchar(700) NOT NULL,
  `description` text,
  `short_description` text,
  `meta_keywords` varchar(5000) NOT NULL,
  `meta_description` text,
  PRIMARY KEY (`id_post`,`id_lang`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_slide` (
  `id_slide` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(1000) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_slide`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[]="
    CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_slide_shop` (
      `id_slide` int(10) unsigned NOT NULL,
      `id_shop` int(11) NOT NULL,
      PRIMARY KEY (`id_slide`,`id_shop`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_slide_lang` (
  `id_slide` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `caption` varchar(5000) NOT NULL,
  PRIMARY KEY (`id_slide`,`id_lang`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_tag` (
  `id_tag` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_post` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `tag` varchar(200) NOT NULL,
  `click_number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tag`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[]="CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_employee` ( 
    `id_employee_post` INT(11) NOT NULL AUTO_INCREMENT , 
    `id_employee` INT(11) NOT NULL , 
    `name` VARCHAR(222) NOT NULL ,  
    `is_customer` INT(1),
    `avata` VARCHAR(222) NOT NULL , 
    `status` INT(1) NOT NULL ,
    `profile_employee` VARCHAR(222) NOT NULL , 
    PRIMARY KEY (`id_employee_post`)) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_employee_lang` (
    `id_employee_post` int(11) NOT NULL,
    `id_lang` int(11) NOT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY (`id_employee_post`,`id_lang`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ybc_blog_log_view`(
    `ip` varchar(50) NOT NULL,
    `id_post` INT(11) NOT NULL,
    `browser` varchar(70) NOT NULL,
    `id_customer` INT (11) NOT NULL,
    `datetime_added` datetime NOT NULL
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ybc_blog_log_like`(
    `ip` varchar(50) NOT NULL,
    `id_post` INT(11) NOT NULL,
    `browser` varchar(70) NOT NULL,
    `id_customer` INT (11) NOT NULL,
    `datetime_added` datetime NOT NULL
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8';
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_reply` (
      `id_reply` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `id_comment` int(11) NOT NULL,
      `id_user` int(11) NOT NULL,
      `name` varchar(5000) NOT NULL,
      `email` varchar(5000) NOT NULL,
      `reply` text,
      `id_employee` int(11) NOT NULL,
      `approved` INT(1),
      `datetime_added` datetime NOT NULL,
      `datetime_updated` datetime NOT NULL,
      PRIMARY KEY (`id_reply`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[] ="CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post_related_categories` (
        `id_post` INT(11) NOT NULL , 
        `id_category` INT(11) NOT NULL,
         PRIMARY KEY (`id_post`,`id_category`)
         ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
    $sqls[]="CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_polls` ( 
    `id_polls` INT(11) NOT NULL AUTO_INCREMENT ,
    `id_user` INT(11) NOT NULL , 
    `name` VARCHAR(222)  NOT NULL , 
    `email` VARCHAR(222) NOT NULL , 
    `id_post` INT(11) NOT NULL , 
    `polls` INT(1) NOT NULL , 
    `feedback` TEXT NOT NULL, 
    `dateadd` DATETIME NOT NULL ,
     PRIMARY KEY (`id_polls`)) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";
if($sqls)
{
    foreach($sqls as $sql)
    {
        Db::getInstance()->execute($sql);
    }
}