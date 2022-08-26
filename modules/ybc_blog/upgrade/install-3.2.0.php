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
function upgrade_module_3_2_0($object)
{
    $object->registerHook('actionObjectLanguageAddAfter');
    $object->registerHook('displayFooterCategory');
    $sqls = array();
    if(!$object->checkCreatedColumn('ybc_blog_category_lang','image'))
    {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category_lang` ADD `image` VARCHAR(222) NULL AFTER `meta_description`, ADD `thumb` VARCHAR(222) NULL AFTER `image`');
        $categories  = Db::getInstance()->executeS('SELECT image,thumb,id_category FROM `'._DB_PREFIX_.'ybc_blog_category`');
        if($categories)
        {
            foreach($categories as $category)
            {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_category_lang` SET image="'.pSQL($category['image']).'",thumb="'.pSQL($category['thumb']).'" WHERE id_category="'.(int)$category['id_category'].'"');
            }
        }
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category` DROP IF EXISTS image';
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category` DROP IF EXISTS thumb';
    }
    if(!$object->checkCreatedColumn('ybc_blog_gallery_lang','image'))
    {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'ybc_blog_gallery_lang` ADD `image` VARCHAR(222) NULL AFTER `description`, ADD `thumb` VARCHAR(222) NULL AFTER `image`');
        $galleries  = Db::getInstance()->executeS('SELECT image,thumb,id_gallery FROM `'._DB_PREFIX_.'ybc_blog_gallery`');
        if($galleries)
        {
            foreach($galleries as $gallery)
            {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_gallery_lang` SET image="'.pSQL($gallery['image']).'",thumb="'.pSQL($gallery['thumb']).'" WHERE id_gallery="'.(int)$gallery['id_gallery'].'"');
            }
        }
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_gallery` DROP IF EXISTS image';
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_gallery` DROP IF EXISTS thumb';
    }
    if(!$object->checkCreatedColumn('ybc_blog_post_lang','image'))
    {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_lang` ADD `image` VARCHAR(222) NULL AFTER `meta_description`, ADD `thumb` VARCHAR(222) NULL AFTER `image`');
        $posts  = Db::getInstance()->executeS('SELECT image,thumb,id_post FROM `'._DB_PREFIX_.'ybc_blog_post`');
        if($posts)
        {
            foreach($posts as $post)
            {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_post_lang` SET image="'.pSQL($post['image']).'",thumb="'.pSQL($post['thumb']).'" WHERE id_post="'.(int)$post['id_post'].'"');
            }
        }
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post` DROP IF EXISTS image';
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post` DROP IF EXISTS thumb';
    }
    if(!$object->checkCreatedColumn('ybc_blog_slide_lang','image'))
    {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'ybc_blog_slide_lang` ADD `image` VARCHAR(222) NULL AFTER `caption`');
        $slides  = Db::getInstance()->executeS('SELECT image,id_slide FROM `'._DB_PREFIX_.'ybc_blog_slide`');
        if($slides)
        {
            foreach($slides as $slide)
            {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_slide_lang` SET image="'.pSQL($slide['image']).'" WHERE id_slide="'.(int)$slide['id_slide'].'"');
            }
        }
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ybc_blog_slide` DROP IF EXISTS image';
    }
    if($sqls)
    {
        foreach($sqls as $sql)
        {
            Db::getInstance()->execute($sql);
        }
    }
    return true;
}