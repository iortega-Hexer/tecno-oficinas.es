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
function upgrade_module_3_1_1($object)
{
    if (!$object->isRegisteredInHook('displayFooterYourAccount'))
        $object->registerHook('displayFooterYourAccount');
    $sqls=array();
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category_lang` ADD PRIMARY KEY( `id_category`, `id_lang`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category_shop` ADD PRIMARY KEY( `id_category`, `id_shop`)';     
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_related_categories` ADD PRIMARY KEY( `id_post`, `id_category`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_employee_lang` ADD PRIMARY KEY( `id_employee_post`, `id_lang`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_slide_lang` ADD PRIMARY KEY( `id_slide`, `id_lang`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_slide_shop` ADD PRIMARY KEY( `id_slide`, `id_shop`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_lang` ADD PRIMARY KEY( `id_post`, `id_lang`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_category` ADD PRIMARY KEY( `id_post`, `id_category`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_shop` ADD PRIMARY KEY( `id_post`, `id_shop`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_gallery_lang` ADD PRIMARY KEY( `id_gallery`, `id_lang`)';
    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_gallery_shop` ADD PRIMARY KEY( `id_gallery`, `id_shop`)';
    foreach($sqls as $sql)
    {
        Db::getInstance()->execute($sql);
    }
    return true;
}
