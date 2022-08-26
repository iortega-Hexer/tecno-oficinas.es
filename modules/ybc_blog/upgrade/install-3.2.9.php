<?php
/**
 * 2007-2022 ETS-Soft
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
 *  @copyright  2007-2022 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;
function upgrade_module_3_2_9($object)
{
    $sqls = array();
    $sqls[]=  'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ybc_blog_email_template` ( 
     `id_ybc_blog_email_template` INT(11) NOT NULL AUTO_INCREMENT , 
     `active` INT(11) NOT NULL , 
     `template` VARCHAR(300) NOT NULL , 
     PRIMARY KEY (`id_ybc_blog_email_template`)) ENGINE= '._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';
     $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ybc_blog_email_template_lang` ( 
     `id_ybc_blog_email_template` INT(11) NOT NULL ,`id_lang` INT(11) NOT NULL , 
     `subject` VARCHAR(1000) NOT NULL ,
     PRIMARY KEY (`id_ybc_blog_email_template`, `id_lang`)) ENGINE= '._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';
     if($sqls)
     {
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
     }
     $object->initEmailTemplate(false);
     return true;
}