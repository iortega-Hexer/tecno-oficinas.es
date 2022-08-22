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
include_once(_PS_MODULE_DIR_.'ybc_blog/ybc_blog_defines.php');
function upgrade_module_3_0_1($object)
{
    if (!$object->isRegisteredInHook('blogArchivesBlock'))
        $object->registerHook('blogArchivesBlock');
    if (!$object->isRegisteredInHook('blogComments'))
        $object->registerHook('blogComments');
    if (!$object->isRegisteredInHook('blogPositiveAuthor'))
        $object->registerHook('blogPositiveAuthor');
    if (!$object->isRegisteredInHook('customerAccount'))
        $object->registerHook('customerAccount');
    if (!$object->isRegisteredInHook('displayMyAccountBlock'))
        $object->registerHook('displayMyAccountBlock');
    if (!$object->isRegisteredInHook('displayLeftFormManagament'))
        $object->registerHook('displayLeftFormManagament');
    if (!$object->isRegisteredInHook('displayRightFormManagament'))
        $object->registerHook('displayRightFormManagament');
    if (!$object->isRegisteredInHook('blogRssCategory'))
        $object->registerHook('blogRssCategory');
    if (!$object->isRegisteredInHook('blogRssSideBar'))
        $object->registerHook('blogRssSideBar');
    if (!$object->isRegisteredInHook('blogRssAuthor'))
        $object->registerHook('blogRssAuthor');
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
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_post_lang` ADD COLUMN `meta_title` VARCHAR(1000) DEFAULT NULL AFTER `title`';
    }
    if(!$object->checkCreatedColumn('ybc_blog_category_lang','meta_title'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ybc_blog_category_lang` ADD COLUMN `meta_title` VARCHAR(1000) DEFAULT NULL AFTER `title`';
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
    Configuration::updateValue('YBC_BLOG_HOME_PER_ROW',4);
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ybc_blog_log_view`(
    `ip` varchar(50) DEFAULT NULL,
    `id_post` INT(11) NOT NULL,
    `browser` varchar(70) DEFAULT NULL,
    `id_customer` INT (11) DEFAULT NULL,
    `datetime_added` datetime NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ybc_blog_log_like`(
    `ip` varchar(50) DEFAULT NULL,
    `id_post` INT(11) NOT NULL,
    `browser` varchar(70) DEFAULT NULL,
    `id_customer` INT (11) DEFAULT NULL,
    `datetime_added` datetime NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
    $sqls[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_reply` (
      `id_reply` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `id_comment` int(11) DEFAULT NULL,
      `id_user` int(11) DEFAULT NULL,
      `name` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
      `email` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
      `reply` text CHARACTER SET utf8,
      `id_employee` int(11) DEFAULT NULL,
      `approved` INT(1),
      `datetime_added` datetime NOT NULL,
      `datetime_updated` datetime NOT NULL,
      PRIMARY KEY (`id_reply`)
    )";
    $sqls[] ="CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_post_related_categories` ( 
        `id_post` INT(11) NOT NULL , 
        `id_category` INT(11) NOT NULL )
    ENGINE = InnoDB";
    $sqls[]="CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ybc_blog_polls` ( 
    `id_polls` INT(11) NOT NULL AUTO_INCREMENT ,
    `id_user` INT(11) NOT NULL , 
    `name` VARCHAR(222) CHARACTER SET utf8 NOT NULL , 
    `email` VARCHAR(222) NOT NULL , 
    `id_post` INT(11) NOT NULL , 
    `polls` INT(1) NOT NULL , 
    `feedback` TEXT CHARACTER SET utf8 NOT NULL, 
    `dateadd` DATETIME NOT NULL ,
     PRIMARY KEY (`id_polls`)) ENGINE = InnoDB";
    foreach($sqls as $sql)
    {
        Db::getInstance()->execute($sql);
    }
    return _installTabs() && _installDb();
    
}
function _installTabs()
{
    $ybc_defines = new Ybc_blog_defines();
    foreach($ybc_defines->subTabs as $tab)
    {
        if($tabId = Tab::getIdFromClassName($tab['class_name']))
        {
            $tab = new Tab($tabId);
            if($tab)
                $tab->delete();
        }                
    }
    $languages = Language::getLanguages(false);
    $blogTabId = Tab::getIdFromClassName('AdminYbcBlog');
    if($blogTabId)
    {
        foreach($ybc_defines->subTabs as $tabArg)
        {
            if(!Tab::getIdFromClassName($tabArg['class_name']))
            {
                $tab = new Tab();
                $tab->class_name = $tabArg['class_name'];
                $tab->module = 'ybc_blog';
                $tab->id_parent = $blogTabId;   
                $tab->icon= $tabArg['icon'];         
                foreach($languages as $lang){
                        $tab->name[$lang['id_lang']] = $tabArg['tab_name'];
                }
                $tab->save();
            }
        }                
    }            
    return true;
}
function _installDb()
{
    $languages = Language::getLanguages(false); 
    $ybc_defines = new Ybc_blog_defines();  
    if($ybc_defines->configs)
    {
        foreach($ybc_defines->configs as $key => $config)
        {
            if(Configuration::get($key)===false && $key!='YBC_BLOG_URL_NO_ID')
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
            
        }
    }
    if($ybc_defines->configs_seo)
    {
        foreach($ybc_defines->configs_seo as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->configs_sitemap)
    {
        foreach($ybc_defines->configs_sitemap as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->configs_homepage)
    {
        $ybc_defines->configs_homepage['YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME']=array(
                'default' =>'',
        );
        foreach($ybc_defines->configs_homepage as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
            
        }
    }
    if($ybc_defines->configs_categorypage)
    {
        foreach($ybc_defines->configs_categorypage as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->configs_productpage)
    {
        foreach($ybc_defines->configs_productpage as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
            
        }
    }
    if($ybc_defines->configs_sidebar)
    {
        foreach($ybc_defines->configs_sidebar as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->configs_email)
    {
        foreach($ybc_defines->configs_email as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->socials)
    {
        if(Configuration::get($key)===false)
        {
            foreach($ybc_defines->socials as $key => $config)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->rss)
    {
        foreach($ybc_defines->rss as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
            
        }
    }
    if($ybc_defines->customer_settings)
    {
        foreach($ybc_defines->customer_settings as $key => $config)
        {
            if(Configuration::get($key)===false)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    }
    if($ybc_defines->configs_image)
    {
        foreach($ybc_defines->configs_image as $key=>$config)
        {
            if($config['type']=='image')
            {
                Configuration::updateValue($key.'_WIDTH',$config['default'][0]);
                Configuration::updateValue($key.'_HEIGHT',$config['default'][1]);
            }
            else
            {
                Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '');
            }
        }
    } 
    Configuration::updateValue('YBC_BLOG_ALERT_EMAILS',Configuration::get('PS_SHOP_EMAIL'));
    if (defined('_PS_ADMIN_DIR_'))
    {
        $adminforder= str_replace(_PS_ROOT_DIR_,'',_PS_ADMIN_DIR_);
        $adminforder= trim(trim($adminforder,'\\'),'/');
        Configuration::updateValue('YBC_BLOG_ADMIN_FORDER',$adminforder);
    }
    return true;
}