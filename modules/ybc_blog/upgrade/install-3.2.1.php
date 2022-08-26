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
function upgrade_module_3_2_1($object)
{
    if(!is_dir(_PS_YBC_BLOG_IMG_DIR_))
        @mkdir(_PS_YBC_BLOG_IMG_DIR_);
    if(file_exists(dirname(__FILE__).'/index.php'))
        Tools::copy(dirname(__FILE__).'/index.php',_PS_YBC_BLOG_IMG_DIR_.'index.php');
    $object->recurseCopy(dirname(__FILE__).'/../views/img/slide',_PS_YBC_BLOG_IMG_DIR_.'/slide');
    $object->deleteDir(dirname(__FILE__).'/../views/img/slide');
    $object->recurseCopy(dirname(__FILE__).'/../views/img/post',_PS_YBC_BLOG_IMG_DIR_.'/post');
    $object->deleteDir(dirname(__FILE__).'/../views/img/post');
    $object->recurseCopy(dirname(__FILE__).'/../views/img/gallery',_PS_YBC_BLOG_IMG_DIR_.'/gallery');
    $object->deleteDir(dirname(__FILE__).'/../views/img/gallery');
    $object->recurseCopy(dirname(__FILE__).'/../views/img/category',_PS_YBC_BLOG_IMG_DIR_.'/category');
    $object->deleteDir(dirname(__FILE__).'/../views/img/category');
    $object->recurseCopy(dirname(__FILE__).'/../views/img/avata',_PS_YBC_BLOG_IMG_DIR_.'/avata');
    $object->deleteDir(dirname(__FILE__).'/../views/img/avata');
    if(Configuration::get('YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE')===false)
        Configuration::get('YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE',1);
    return true;
}