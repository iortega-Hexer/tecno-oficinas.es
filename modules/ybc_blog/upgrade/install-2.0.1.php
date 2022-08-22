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
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_category_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_post_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_list_helper_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_paggination_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_comment_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_slide_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_gallery_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_link_class.php');
function upgrade_module_2_0_1($object)
{
    if (!$object->isRegisteredInHook('displayFooterProduct'))
        $object->registerHook('displayFooterProduct');
    if (!$object->isRegisteredInHook('displayRightColumn'))
        $object->registerHook('displayRightColumn');
    $sqls=array();
    if(!$object->checkCreatedColumn('ybc_blog_category','id_parent'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category` ADD COLUMN `id_parent` INT(11) NOT NULL default "0" AFTER `id_category`';
	if(!$object->checkCreatedColumn('ybc_blog_comment','email'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_comment` ADD COLUMN `email` VARCHAR(500) NOT NULL default "0" AFTER `id_user`';
    if(!$object->checkCreatedColumn('ybc_blog_comment','name'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_comment` ADD COLUMN `name` INT(11) NOT NULL default "0" AFTER `id_user`';
    if(!$object->checkCreatedColumn('ybc_blog_post','datetime_active'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post` ADD COLUMN `datetime_active` datetime NULL';
    $sqls[]="
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_category_shop` (
          `id_category` int(10) unsigned NOT NULL,
          `id_shop` int(11) DEFAULT NULL
        )";
    $sqls[]="
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_gallery_shop` (
          `id_gallery` int(10) unsigned NOT NULL,
          `id_shop` int(11) DEFAULT NULL
        )";
    $sqls[]="
    CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post_shop` (
      `id_post` int(10) unsigned NOT NULL,
      `id_shop` int(11) DEFAULT NULL
    )";
    $sqls[]="
    CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_slide_shop` (
      `id_slide` int(10) unsigned NOT NULL,
      `id_shop` int(11) DEFAULT NULL
    )";
    $sqls[]="CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_employee` ( 
    `id_employee_post` INT(11) NOT NULL AUTO_INCREMENT , 
    `id_employee` INT(11) NOT NULL , 
    `name` VARCHAR(222) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,  
    `avata` VARCHAR(222) NOT NULL , 
    `profile_employee` VARCHAR(222) NOT NULL , 
    PRIMARY KEY (`id_employee_post`)) ENGINE = InnoDB";
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_employee_lang` (
    `id_employee_post` int(11) NOT NULL,
    `id_lang` int(11) NOT NULL,
    `description` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL)";
    foreach($sqls as $sql)
    {
        Db::getInstance()->execute($sql);
    }
    // install 3.0.1
    $sqls=array();
    if(!$object->checkCreatedColumn('ybc_blog_post','is_customer'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post` ADD COLUMN `is_customer` INT(1) DEFAULT NULL AFTER `added_by`';
    if(!$object->checkCreatedColumn('ybc_blog_employee','is_customer'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_employee` ADD COLUMN `is_customer` INT(1) DEFAULT NULL AFTER `name`';
    if(!$object->checkCreatedColumn('ybc_blog_comment','customer_reply'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_comment` ADD COLUMN `customer_reply` INT(1) DEFAULT NULL AFTER `replied_by`';
    if(!$object->checkCreatedColumn('ybc_blog_comment','viewed'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_comment` ADD COLUMN `viewed` INT(1) DEFAULT NULL AFTER `rating`';
    if(!$object->checkCreatedColumn('ybc_blog_post_category','position'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_category` ADD COLUMN `position` INT(1) DEFAULT NULL AFTER `id_category`';
    if(!$object->checkCreatedColumn('ybc_blog_employee','status'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_employee` ADD COLUMN `status` INT(1) DEFAULT NULL AFTER `avata`';
    if(!$object->checkCreatedColumn('ybc_blog_gallery','thumb'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_gallery` ADD COLUMN `thumb` varchar(222) DEFAULT NULL AFTER `image`';
    if(!$object->checkCreatedColumn('ybc_blog_category','thumb'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category` ADD COLUMN `thumb` varchar(222) DEFAULT NULL AFTER `image`';
    if(!$object->checkCreatedColumn('ybc_blog_post_lang','meta_title'))
    {
        $query='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_lang` ADD COLUMN `meta_title` VARCHAR(1000) DEFAULT NULL AFTER `title`';
    }
    if(!$object->checkCreatedColumn('ybc_blog_post_lang','url_alias'))
    {
        $query='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_lang` ADD COLUMN `url_alias` VARCHAR(1000) DEFAULT NULL AFTER `title`';
        Db::getInstance()->execute($query);
        if($object->checkCreatedColumn('ybc_blog_post','url_alias'))
        {
            $posts = Db::getInstance()->executeS('SELECT url_alias,id_post FROM '._DB_PREFIX_.'ybc_blog_post');
            if($posts)
            {
                foreach($posts as $post)
                {
                     Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_post_lang SET url_alias="'.pSQL($post['url_alias']).'" WHERE id_post='.(int)$post['id_post']) ; 
                }   
            } 
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post` DROP `url_alias`');   
        }                
    }
    if(!$object->checkCreatedColumn('ybc_blog_category_lang','url_alias'))
    {
        $query='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category_lang` ADD COLUMN `url_alias` INT(1) DEFAULT NULL AFTER `title`';
        Db::getInstance()->execute($query);
        if($object->checkCreatedColumn('ybc_blog_category','url_alias'))
        {
            $categories = Db::getInstance()->executeS('SELECT url_alias,id_category FROM '._DB_PREFIX_.'ybc_blog_category');
            if($categories)
            {
                foreach($categories as $category)
                {
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_category_lang SET url_alias="'.pSQL($category['url_alias']).'" WHERE id_category='.(int)$category['id_category']);
                }
            }
            Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'ybc_blog_category DROP `url_alias`');
        }
    }
    if(!$object->checkCreatedColumn('ybc_blog_slide_lang','url'))
    {
        $query='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_slide_lang` ADD COLUMN `url` INT(1) DEFAULT NULL AFTER `caption`';
        Db::getInstance()->execute($query);
        if($object->checkCreatedColumn('ybc_blog_slide','url'))
        {
            $slides= Db::getInstance()->executeS('SELECT id_slide,url FROM '._DB_PREFIX_.'ybc_blog_slide');
            if($slides)
            {
                foreach($slides as $slide)
                {
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_slide_lang SET url="'.pSQL($slide['url']).'" WHERE id_slide='.(int)$slide['id_slide']);
                }
            }
            Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'ybc_blog_slide DROP `url`');
        }
    }
    foreach($sqls as $sql)
    {
        Db::getInstance()->execute($sql);
    }
    // end install 3.0.1
    $shops= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'shop');
    $categories = Db::getInstance()->executeS('SELECT id_category FROM '._DB_PREFIX_.'ybc_blog_category WHERE id_category NOT IN (SELECT id_category FROM '._DB_PREFIX_.'ybc_blog_category_shop)');
    $import_categories =array();
    $import_posts =array();
    
    if($categories)
    {
        foreach($categories as $category)
        {
            foreach($shops as $shop)
            {
                
                if($shop['id_shop']==Context::getContext()->shop->id)
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_category_shop (`id_category`, `id_shop`) VALUES('.(int)$category['id_category'].','.(int)$shop['id_shop'].')');
                    
                }
                else
                {
                    $objCategory = new Ybc_blog_category_class($category['id_category']);
                    if(isset($import_categories[$shop['id_shop']][$objCategory->id_parent]))
                        $objCategory->id_parent = $import_categories[$shop['id_shop']][$objCategory->id_parent];
                    else
                       $objCategory->id_parent= 0;     
                    if($id_new = $objCategory->duplicate())
                    {
                        $import_categories[$shop['id_shop']][$objCategory->id]=$id_new;
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_category_shop SET id_shop="'.(int)$shop['id_shop'].'" WHERE id_category="'.(int)$id_new.'"');
                    }
                        
                }
            }
        }
    }
    $posts = Db::getInstance()->executeS('SELECT id_post FROM '._DB_PREFIX_.'ybc_blog_post WHERE id_post NOT IN (SELECT id_post FROM '._DB_PREFIX_.'ybc_blog_post_shop)');
    if($posts)
    {
        foreach($posts as $post)
        {
            foreach($shops as $shop)
            {
                if($shop['id_shop']==Context::getContext()->shop->id)
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_post_shop (`id_post`, `id_shop`) VALUES('.(int)$post['id_post'].','.(int)$shop['id_shop'].')');
                }
                else
                {
                    $objPost= new Ybc_blog_post_class($post['id_post']);  
                    if($id_new = $objPost->duplicate())
                    {
                        $import_posts[$shop['id_shop']][$objPost->id]=$id_new;
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_post_shop SET id_shop="'.(int)$shop['id_shop'].'" WHERE id_post="'.(int)$id_new.'"');
                    }
                        
                }
            }
        }
    }
    $postCategories= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post_category');
    if($postCategories)
    {
        foreach($postCategories as $postCategory)
        {
            foreach($shops as $shop)
            {
                if($shop['id_shop']!=Context::getContext()->shop->id)
                {
                    $id_post= isset($import_posts[$shop['id_shop']][$postCategory['id_post']])?$import_posts[$shop['id_shop']][$postCategory['id_post']]:0;
                    $id_category = isset($import_categories[$shop['id_shop']][$postCategory['id_category']])?$import_categories[$shop['id_shop']][$postCategory['id_category']]:0;
                    if($id_post && $id_category)
                    {
                        $position = 1+ (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_category='.(int)$id_category);
                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_post_category (id_post,id_category,position) VALUES('.(int)$id_post.','.(int)$id_category.','.(int)$position.')');
                    }
                        
                }
            }
        }
    }
    $postComments= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_comment');
    if($postComments)
    {
        foreach($postComments as $comment)
        {
            foreach($shops as $shop)
            {
                if($shop['id_shop']!=Context::getContext()->shop->id)
                {
                    $objComment= new Ybc_blog_comment_class($comment['id_comment']);
                    $objComment->id_post = isset($import_posts[$shop['id_shop']][$objComment->id_post])?$import_posts[$shop['id_shop']][$objComment->id_post]:0;
                    if($objComment->id_post)
                    {
                        $objComment->duplicate();
                    }
                }
            }
        }
    }
    $blogTags= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_comment');
    if($blogTags)
    {
        foreach($blogTags as $tag)
        {
            foreach($shops as $shop)
            {
                if($shop['id_shop']!=Context::getContext()->shop->id)
                {
                    $id_post = isset($import_posts[$shop['id_shop']][$tag['id_post']])?$import_posts[$shop['id_shop']][$tag['id_post']]:0;
                    if($id_post)
                    {
                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_tag (id_post,id_lang,tag,click_number) VALUES('.(int)$id_post.','.(int)$tag['id_lang'].',"'.pSQL($tag['tag']).'","'.(int)$tag['click_number'].'")');
                    }
                }
            }
        }
    }
    $galleries = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_gallery WHERE id_gallery NOT IN (SELECT id_gallery FROM '._DB_PREFIX_.'ybc_blog_gallery_shop)');
    if($galleries)
    {
        foreach($galleries as $gallery)
        {
            foreach($shops as $shop)
            {
                if($shop['id_shop']==Context::getContext()->shop->id)
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_gallery_shop (`id_gallery`, `id_shop`) VALUES('.(int)$gallery['id_gallery'].','.(int)$shop['id_shop'].')');
                }
                else
                {
                    $objGallery= new Ybc_blog_gallery_class($gallery['id_gallery']);  
                    if($id_new=$objGallery->duplicate())
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_gallery_shop SET id_shop="'.(int)$shop['id_shop'].'" WHERE id_gallery="'.(int)$id_new.'"');
                    
                }
            }
        }
    }
    $slides = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_slide WHERE id_slide NOT IN (SELECT id_slide FROM '._DB_PREFIX_.'ybc_blog_slide_shop)');
    if($slides)
    {
        foreach($slides as $slide)
        {
            foreach($shops as $shop)
            {
                if($shop['id_shop']==Context::getContext()->shop->id)
                {
                    Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_slide_shop (`id_slide`, `id_shop`) VALUES('.(int)$slide['id_slide'].','.(int)$shop['id_shop'].')');
                }
                else
                {
                    $objSlide= new Ybc_blog_slide_class($slide['id_slide']);  
                    if($id_new=$objSlide->duplicate())
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_slide_shop SET id_shop="'.(int)$shop['id_shop'].'" WHERE id_slide="'.(int)$id_new.'"');
                }
            }
        }
    }
   
    return true;
}