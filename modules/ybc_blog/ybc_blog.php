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

if(!defined('_PS_VERSION_'))
	exit;
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_category_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_post_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_list_helper_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_paggination_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_comment_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_polls_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_slide_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_gallery_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_link_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_employee_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ImportExport.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_browser.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/ybc_blog_defines.php');
if(!function_exists('ets_getCookie'))
    include_once(_PS_MODULE_DIR_.'ybc_blog/classes/cookie');
class Ybc_blog extends Module
{
    private $depthLevel = false;
    private $excludedCats = array();
    private $prefix = '-';
    private $blogCategoryDropDown;
    private $baseAdminPath;
    private $errorMessage = false;
    private $_html = '';
    public $blogDir;
    public $alias;
    public $friendly;
    public $is17 = false;
    public $configTabs = array();
    public $import_ok=false;   
    public $errors = array();
    public $sort = false;
    public function __construct()
	{

		//Ajax search
        $this->ajaxProductSearch();
       
        //Init
        $this->name = 'ybc_blog';
		$this->tab = 'front_office_features';
		$this->version = '3.1.8';
		$this->author = 'ETS-Soft';
		$this->need_instance = 0;
		$this->secure_key = Tools::encrypt($this->name);
		$this->bootstrap = true;
        if(version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true; 
        $this->module_key = 'da314fdf1af6d043f9b2f15dce2bef1e';
        parent::__construct();
        $this->shortlink = 'https://mf.short-link.org/';
        if(Tools::getValue('configure')==$this->name && Tools::isSubmit('othermodules'))
        {
            $this->displayRecommendedModules();
        }
        if(!Configuration::get('YBC_BLOG_POST_SORT_BY'))
            $this->sort = 'p.id_post DESC, ';
        else
        {
            if(Configuration::get('YBC_BLOG_POST_SORT_BY')=='sort_order')
                $this->sort = 'p.sort_order ASC, ';
            else
                $this->sort = 'p.'.Configuration::get('YBC_BLOG_POST_SORT_BY').' DESC, ';
        }
		$this->displayName = $this->l('BLOG');
        $this->description = $this->l('The most powerful, flexible and feature-rich blog module for Prestashop. BLOG provides everything you need to create a professional blog area for your website.');
		$this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);
        $this->configTabs = array(
            'general' => $this->l('General'),
            'gallery' => $this->l('Gallery'),
            'slider' => $this->l('Slider'),            
            'comment' => $this->l('Likes and Comments'), 
            'polls' => $this->l('Polls'),     
        );
        $this->blogDir = $this->_path;  
        $this->alias = Configuration::get('YBC_BLOG_ALIAS',$this->context->language->id);
        $this->friendly = (int)Configuration::get('YBC_BLOG_FRIENDLY_URL') && (int)Configuration::get('PS_REWRITING_SETTINGS') ? true : false;    
        $recaptcha = Tools::getValue('g-recaptcha-response') ? Tools::getValue('g-recaptcha-response') : '';
        $secret = Configuration::get('YBC_BLOG_CAPTCHA_TYPE')=='google' ? Configuration::get('YBC_BLOG_CAPTCHA_SECRET_KEY') : Configuration::get('YBC_BLOG_CAPTCHA_SECRET_KEY3');
        $this->link_capcha="https://www.google.com/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $recaptcha . "&remoteip=" . Tools::getRemoteAddr();
        $this->setMetas();

    }
    
    /**
	 * @see Module::install()
	 */
    public function install()
	{
	    return parent::install()&& $this->registerHook('displayLeftColumn')
        && $this->registerHook('displayBackOfficeHeader') 
        && $this->registerHook('displayHome') 
        && $this->registerHook('displayFullBlogHome')
        && $this->registerHook('displayHeader')
        && $this->registerHook('displayFooter')
        && $this->registerHook('blogSearchBlock')
        && $this->registerHook('blogTagsBlock')
        && $this->registerHook('blogNewsBlock')
        && $this->registerHook('blogCategoriesBlock')
        && $this->registerHook('blogSlidersBlock')
        && $this->registerHook('blogGalleryBlock')
        && $this->registerHook('blogPopularPostsBlock')
        && $this->registerHook('moduleRoutes')
        && $this->registerHook('blogSidebar')
        && $this->registerHook('blogFeaturedPostsBlock')
        && $this->registerHook('displayRightColumn')
        && $this->registerHook('displayFooterProduct')
        && $this->registerHook('blogArchivesBlock')
        && $this->registerHook('blogComments')
        && $this->registerHook('blogPositiveAuthor')
        && $this->registerHook('blogRssCategory')
        && $this->registerHook('customerAccount')
        && $this->registerHook('displayMyAccountBlock')
        && $this->registerHook('displayLeftFormManagament')
        && $this->registerHook('displayRightFormManagament')
        && $this->registerHook('displayLeftFormComments')
        && $this->registerHook('displayRightFormComments')
        && $this->registerHook('blogRssSideBar')
        && $this->registerHook('blogRssAuthor')
        && $this->registerHook('blogCategoryBlock')
        && $this->registerHook('displayBackOfficeFooter')
        && $this->registerHook('displayFooterYourAccount')
        && $this->_installDb()
        && $this->_installTabs() && $this->_copyForderMail();
    }    
    /**
	 * @see Module::uninstall()
	 */
	public function uninstall()
	{
        return parent::uninstall() &&  $this->_uninstallDb() && $this->_uninstallTabs();
    }
    private function _installDb()
    {
        $languages = Language::getLanguages(false);
        //Install db structure
        Configuration::updateValue('PS_ALLOW_HTML_IFRAME',1);
        require_once(dirname(__FILE__).'/install/sql.php');
        require_once(dirname(__FILE__).'/install/data.php');   
        $ybc_defines = new Ybc_blog_defines();        
        if($ybc_defines->configs)
        {
            foreach($ybc_defines->configs as $key => $config)
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
        if($ybc_defines->configs_seo)
        {
            foreach($ybc_defines->configs_seo as $key => $config)
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
        if($ybc_defines->configs_sitemap)
        {
            foreach($ybc_defines->configs_sitemap as $key => $config)
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
        if($ybc_defines->configs_homepage)
        {
            $ybc_defines->configs_homepage['YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME']=array(
                'label' => $this->l('Select blog categories to display'),
                    'type' => 'blog_categories',
                    'html_content' =>$this->displayBlogCategoryTre($this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),$this->getSelectedCategories()),
					'categories' => $this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),
					'name' => 'categories',
                    'selected_categories' => $this->getSelectedCategories(),
                    'default' =>'',
            );
            foreach($ybc_defines->configs_homepage as $key => $config)
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
        if($ybc_defines->configs_postpage)
        {
            foreach($ybc_defines->configs_postpage as $key => $config)
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
        if($ybc_defines->configs_postlistpage)
        {
            foreach($ybc_defines->configs_postlistpage as $key => $config)
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
        if($ybc_defines->configs_categorypage)
        {
            foreach($ybc_defines->configs_categorypage as $key => $config)
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
        if($ybc_defines->configs_productpage)
        {
            foreach($ybc_defines->configs_productpage as $key => $config)
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
        if($ybc_defines->configs_sidebar)
        {
            foreach($ybc_defines->configs_sidebar as $key => $config)
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
        if($ybc_defines->configs_email)
        {
            foreach($ybc_defines->configs_email as $key => $config)
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
        if($ybc_defines->socials)
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
        if($ybc_defines->rss)
        {
            foreach($ybc_defines->rss as $key => $config)
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
        if($ybc_defines->customer_settings)
        {
            foreach($ybc_defines->customer_settings as $key => $config)
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
    public function _copyForderMail()
    {
        $languages = Language::getLanguages(false);
        $temp_dir_ltr = dirname(__FILE__) . '/mails/en';
        if ($languages && is_array($languages))
        {
            if (!@file_exists($temp_dir_ltr))
                return true;
            foreach ($languages as $language)
            {
                if(isset($language['iso_code']) && $language['iso_code'] != 'en')
                {
                     if (($new_dir = dirname(__FILE__) . '/mails/'. $language['iso_code']))
                     {
                        $this->recurseCopy($temp_dir_ltr, $new_dir);
                     }
                }
            }
        }
        return true;
     }
    public function recurseCopy($src, $dst)
    {
        if(!@file_exists($src))
            return false;
        $dir = opendir($src);
        if (!@is_dir($dst))
            @mkdir($dst);
        while(false !== ($file = readdir($dir)))
        {
            if (( $file != '.' ) && ($file != '..' ))
            {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                elseif (!@file_exists($dst . '/' . $file))
                {
                    @copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    private function _uninstallDb()
    {
        $ybc_defines = new Ybc_blog_defines();
        if($ybc_defines->configs)
        {
            foreach($ybc_defines->configs as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_seo)
        {
            foreach($ybc_defines->configs_seo as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_sitemap)
        {
            foreach($ybc_defines->configs_sitemap as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_homepage)
        {
            foreach($ybc_defines->configs_homepage as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_postpage)
        {
            foreach($ybc_defines->configs_postpage as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_postlistpage)
        {
            foreach($ybc_defines->configs_postlistpage as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_categorypage)
        {
            foreach($ybc_defines->configs_categorypage as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_productpage)
        {
            foreach($ybc_defines->configs_productpage as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_sidebar)
        {
            foreach($ybc_defines->configs_sidebar as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->configs_email)
        {
            foreach($ybc_defines->configs_email as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->socials)
        {
            foreach($ybc_defines->socials as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->rss)
        {
            foreach($ybc_defines->rss as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        if($ybc_defines->customer_settings)
        {
            foreach($ybc_defines->customer_settings as $key => $config)
            {
                Configuration::deleteByName($key);
            }
            unset($config);
        }
        $tbls = array('post', 'post_lang','post_shop','post_category', 'category','category_shop', 'category_lang', 'comment', 'gallery','gallery_shop', 'gallery_lang', 'tag', 'slide', 'slide_shop','slide_lang','employee','employee_lang','log_view','log_like','post_related_categories','reply','polls');
        foreach($tbls as $tbl)
        {
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'ybc_blog_'.pSQL($tbl).'`');
        }
        $dirs = array('post','category','slide','gallery','post/thumb','gallery/thumb','avata','category/thumb');
        foreach($dirs as $dir)
        {
            $files = glob(dirname(__FILE__).'/views/img/'.$dir.'/*'); 
            foreach($files as $file){ 
              if(is_file($file) && $file!=dirname(__FILE__).'/views/img/'.$dir.'/index.php')
                @unlink($file); 
            }
        }   
        if(file_exists(dirname(__FILE__).'/cache/ybc_blog.data.zip'))
            unlink(dirname(__FILE__).'/cache/ybc_blog.data.zip');    
        return true;
    }
    public function getContent()
	{
        if(!$this->active)
            return '';           
        $this->ajaxPostSearch();
        $this->ajaxCustomerSearch();
        if(Tools::getValue('action')=='getCountMessageYbcBlog')
        {   
            die(
                Tools::jsonEncode(
                    array(
                        'count' => $this->countCommentsWithFilter(' AND bc.viewed=0',false),
                    )
                )   
            );
        }
        $ybc_defines = new Ybc_blog_defines();
	   $this->baseAdminPath = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
	   $this->context->controller->addJqueryPlugin('tagify');
       $this->context->controller->addJqueryUI('ui.sortable');
	   $control = trim(Tools::getValue('control'));
       if(!$control) 
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post&list=true');
       if($control=='category')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Blog posts and blog categories'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postCategory();   
       }
       elseif($control=='post')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Blog posts and blog categories'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postPost();   
       }
       elseif($control=='config')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Global settings'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs);   
       }
       elseif($control=='sitemap')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'sitemap'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_sitemap);   
       }
       elseif($control=='seo')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Seo'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_seo);   
       }
       elseif($control=='image')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Image'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_image,dirname(__FILE__).'/views/img/avata/',Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH',300),Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT',300));
            if(Tools::isSubmit('deldefaultavataimage'))
            {
                @unlink(dirname(__FILE__).'views/img/avata/'.Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT'));
                Configuration::updateValue('YBC_BLOG_IMAGE_AVATA_DEFAULT','');  
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message' => $this->displayConfirmation($this->l('Image deleted')),
                            'image_default' => $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/default_customer.png',
                        )
                    ));
                }                                                          
            }   
       }
       elseif($control=='sidebar')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Sidebar'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_sidebar);  
            if(Tools::isSubmit('action') && Tools::getValue('action')=='updateSidebarOrdering')
            {
                $positions= Tools::getValue('sidebar-position-sidebar');
                foreach($positions as $key=> $position)
                    $positions[$key] ='sidebar_'.$position;
                Configuration::updateValue('YBC_BLOG_POSITION_SIDEBAR',implode(',',$positions));
                die(
                    Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message'=> $this->displaySuccessMessage($this->l('Position updated')),
                        )
                    )
                );
            } 
            if(Tools::isSubmit('action') && Tools::getValue('action')=='updateBlock')
            {
                Configuration::updateValue(Tools::getValue('field'),Tools::getValue('value_filed'));
                die(Tools::jsonEncode(
                    array(
                        'messageType' => 'success',
                        'message'=> $this->displaySuccessMessage($this->l('Updated')),
                    )
                ));
            } 
       }
       elseif($control=='homepage')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Home page'))
                return $this->display(__FILE__,'error_access.tpl');
            $ybc_defines->configs_homepage['YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME']=array(
                'label' => $this->l('Select blog categories to display'),
                    'type' => 'blog_categories',
                    'html_content' =>$this->displayBlogCategoryTre($this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),$this->getSelectedCategories()),
					'categories' => $this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),
					'name' => 'categories',
                    'selected_categories' => $this->getSelectedCategories(),
                    'default' =>'',
            );
            $this->_postConfig($ybc_defines->configs_homepage);  
            if(Tools::isSubmit('action') && Tools::getValue('action')=='updateSidebarOrdering')
            {
                $positions= Tools::getValue('sidebar-position-homepage');
                foreach($positions as $key=> $position)
                    $positions[$key] ='homepage_'.$position;
                Configuration::updateValue('YBC_BLOG_POSITION_HOMEPAGE',implode(',',$positions));
                die(
                    Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message'=> $this->displaySuccessMessage($this->l('Position updated')),
                        )
                    )
                );
            } 
            if(Tools::isSubmit('action') && Tools::getValue('action')=='updateBlock')
            {
                Configuration::updateValue(Tools::getValue('field'),Tools::getValue('value_filed'));
                die(Tools::jsonEncode(
                    array(
                        'messageType' => 'success',
                        'message'=> $this->displaySuccessMessage($this->l('Updated')),
                    )
                ));
            } 
       }
       elseif($control=='postpage')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Post page'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_postpage);   
       }
       elseif($control=='postlistpage')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Post listing pages'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_postlistpage);   
       }
       elseif($control=='categorypage')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Category page'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_categorypage);   
       }
       elseif($control=='productpage')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Product detail page'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_productpage);   
       }
       elseif($control=='email')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Email'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postConfig($ybc_defines->configs_email);   
       }
       elseif($control=='socials')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Socials'))
                return $this->display(__FILE__,'error_access.tpl');
             $this->_postConfig($ybc_defines->socials);   
       }      
       elseif($control=='comment')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Blog comments'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postComment();   
       }
       elseif($control=='polls')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Blog comments'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postPolls();   
       }
       elseif($control=='gallery')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Blog gallery'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postGallery();   
       }
       elseif($control=='slide')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Blog slider'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postSlide();   
       }
       elseif($control=='export')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Import/Export'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postExport();
       }
       elseif($control=='employees')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'none'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postEmployee();
       }
       elseif($control=='rss')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Rss feed'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postRSS();
       }
       elseif($control=='author')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Authors'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postCustomerSettingAuthor();
       }
       elseif($control=='customer')
       {
            if(!$this->checkProfileEmployee($this->context->employee->id,'Authors'))
                return $this->display(__FILE__,'error_access.tpl');
            $this->_postCustomer();
       }
       elseif($control=='comment_reply')
       {
            $this->_posstReply();
       }
       return $this->getAminHtml($control);
    }
    public function renderAdminBodyHtml($control)
    {
        $ybc_defines = new Ybc_blog_defines();
       if($control=='category')
       {
            $this->renderCategoryForm();   
       }
       elseif($control=='post')
       {
            $this->renderPostForm();   
       }
       elseif($control=='config')
       {
            $this->renderConfig($ybc_defines->configs, $this->l('Global settings'),'icon-AdminAdmin');   
       }
       elseif($control=='seo')
       {
            $this->renderConfig($ybc_defines->configs_seo, $this->l('Seo'),'icon-seo');   
       }
       elseif($control=='image')
       {
            $this->renderConfig($ybc_defines->configs_image, $this->l('Image'),'icon-cogs');   
       }
       elseif($control=='email')
       {
            $this->renderConfig($ybc_defines->configs_email, $this->l('Email'),'icon-email');   
       }
       elseif($control=='sidebar')
       {
            $this->renderConfig($ybc_defines->configs_sidebar, $this->l('Sidebar'),'icon-sidebar');   
       }
       elseif($control=='homepage')
       {
            $ybc_defines->configs_homepage['YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME']=array(
                'label' => $this->l('Select blog categories to display'),
                    'type' => 'blog_categories',
                    'html_content' =>$this->displayBlogCategoryTre($this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),$this->getSelectedCategories()),
					'categories' => $this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),
					'name' => 'categories',
                    'selected_categories' => $this->getSelectedCategories(),
                    'default' =>'',
            );
            $this->renderConfig($ybc_defines->configs_homepage, $this->l('Home page'),'icon-homepage');   
       }
       elseif($control=='postpage')
       {
            $this->renderConfig($ybc_defines->configs_postpage, $this->l('Post details page'),'icon-postpage');   
       }
       elseif($control=='postlistpage')
       {
            $this->renderConfig($ybc_defines->configs_postlistpage, $this->l('Post listing pages'),'icon-postlistpage');   
       }
       elseif($control=='categorypage')
       {
            $this->renderConfig($ybc_defines->configs_categorypage, $this->l('Product categories page'),'icon-categorypage');   
       }
       elseif($control=='productpage')
       {
            $this->renderConfig($ybc_defines->configs_productpage, $this->l('Product details page'),'icon-productpage');   
       }
       elseif($control=='sitemap')
       {
            $this->renderConfig($ybc_defines->configs_sitemap, $this->l('Google sitemap'),'icon-sitemap');   
       }
       elseif($control=='socials')
       {
            $this->renderConfig($ybc_defines->socials, $this->l('Socials'),'icon-socials'); ;   
       }
       elseif($control=='rss')
       {
            $this->renderRSS();
       }
       elseif($control=='comment')
       {
            $this->renderCommentsForm();   
       }
       elseif($control=='polls')
       {
            $this->renderPollsForm();
       }
       elseif($control=='gallery')
       {
            $this->renderGalleryForm();   
       }
       elseif($control=='slide')
       {
            $this->renderSlideForm();   
       }
       elseif($control=='export')
       {
            $this->renderExportForm();   
       }
       elseif($control=='employees')
       {
            $this->renderEmployeeFrom();
       }
       elseif($control=='customer')
       {
            $this->renderCustomerForm();
       }
       elseif($control=='author')
       {
            $this->renderAuthorForm();
       }
       elseif($control=='comment_reply')
       {
            $this->displayReplyComment();
       }
       return $this->_html;
    }
    public function getAminHtml($control)
    {       
        $this->smarty->assign(array(
            'ybc_blog_ajax_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ajaxproductsearch=true',
            'ybc_blog_author_ajax_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ajaxCustomersearch=true',
            'ybc_blog_default_lang' => Configuration::get('PS_LANG_DEFAULT'),
            'ybc_blog_is_updating' => Tools::getValue('id_post') || Tools::getValue('id_category') ? 1 :  0,
            'ybc_blog_is_config_page' => Tools::getValue('control') == 'config' ? 1 : 0,
            'ybc_blog_invalid_file' => $this->l('Invalid file'),
            'ybc_blog_module_dir' => $this->_path,
            'ybc_blog_sidebar' => $this->renderSidebar(),
            'ybc_blog_body_html' => $this->renderAdminBodyHtml($control),
            'ybc_blog_error_message' => $this->errorMessage,
            'control' => Tools::getValue('control'),
        ));
        return $this->display(__FILE__, 'admin.tpl');
    }
        
    /**
     * Category 
     */
    
    public function renderCategoryForm()
    {
        $this->baseAdminPath = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        //List 
        if(trim(Tools::getValue('list'))=='true')
        {
            $fields_list = array(
                'id_category' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'thumb_link'=>array(
                    'title'=> $this->l('Image'),
                    //'width' => 40,
                    'type' => 'text',
                    'strip_tag'=>false,
                ),
                'title' => array(
                    'title' => $this->l('Name'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'description' => array(
                    'title' => $this->l('Description'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'sort_order' => array(
                    'title' => $this->l('Sort order'),
                    //'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                    'update_position' => true,
                ),
                'enabled' => array(
                    'title' => $this->l('Enabled'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                ),
            );
            //Filter
            $filter = "";
            if(trim(Tools::getValue('id_category'))!='')
                $filter .= " AND c.id_category = ".(int)trim(urldecode(Tools::getValue('id_category')));
            if(trim(Tools::getValue('sort_order'))!='')
                $filter .= " AND c.sort_order = ".(int)trim(urldecode(Tools::getValue('sort_order')));
            if(trim(Tools::getValue('title'))!='')
                $filter .= " AND cl.title like '%".addslashes(trim(urldecode(Tools::getValue('title'))))."%'";
            if(trim(Tools::getValue('description'))!='')
                $filter .= " AND cl.description like '%".addslashes(trim(urldecode(Tools::getValue('description'))))."%'";
             if(trim(Tools::getValue('enabled'))!='')
                $filter .= " AND c.enabled =".(int)Tools::getValue('enabled');
            
            //Sort
            $sort = "";
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = "c.sort_order ASC,";
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countCategoriesWithFilter($filter,Tools::getValue('id_parent',0));
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $categories = $this->getCategoriesWithFilter($filter, $sort, $start, $paggination->limit,Tools::getValue('id_parent',0));
            if($categories)
            {
                foreach($categories as &$cat)
                {
                    $cat['view_url'] = $this->getLink('blog',array('id_category' => $cat['id_category']));
                    if(Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_category WHERE id_parent='.(int)$cat['id_category']))
                    {
                        $cat['child_view_url'] = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&list=true&id_parent='.(int)$cat['id_category'];
                    }
                    if($cat['thumb'] && file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$cat['thumb']))
                        $cat['thumb_link'] = '<img src="'.$this->_path.'views/img/category/thumb/'.$cat['thumb'].'" style="width:40px;"/>';
                    elseif($cat['image'] && file_exists(dirname(__FILE__).'views/img/category/'.$cat['image']))
                        $cat['thumb_link'] = '<img src="'.$this->_path.'views/img/category/'.$cat['image'].'" style="width:40px;"/>';
                    else
                        $cat['thumb_link']='';
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $thumb='';
            $lever=0;
            $listData = array(
                'name' => 'ybc_category',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category',
                'identifier' => 'id_category',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => ($id_parent=Tools::getValue('id_parent')? '<a href="'.$this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&list=true" title="'.$this->l('Categories').'">':'').$this->l('Categories').($id_parent=Tools::getValue('id_parent') ? '</a>' :''). ( ($id_parent=Tools::getValue('id_parent'))? $this->getThumbCategory($id_parent,$thumb,$lever):''),
                'fields_list' => $fields_list,
                'field_values' => $categories,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' =>trim(Tools::getValue('sort_order'))!='' || trim(Tools::getValue('enabled'))!='' || trim(Tools::getValue('id_category'))!='' || trim(Tools::getValue('description'))!='' || trim(Tools::getValue('title'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'sort'=> Tools::getValue('sort','sort_order'),
                'sort_type' => Tools::getValue('sort_type','asc'),
            );            
            return $this->_html .= $this->renderList($listData);      
        }
        //Form
        if(Tools::isSubmit('id_category') && Tools::getValue('id_category'))
        {
            $blogCategory= new Ybc_blog_category_class(Tools::getValue('id_category'));
        }
        else
            $blogCategory= new Ybc_blog_category_class();
        $blogcategoriesTree= $this->getBlogCategoriesTree(0,true,$this->context->language->id,Tools::getValue('id_category'));
        $depth_level =-1;
        $this->getBlogCategoriesDropdown($blogcategoriesTree,$depth_level,$blogCategory->id_parent,Tools::getValue('id_category'));  
        $blogCategoryotpionsHtml = $this->blogCategoryDropDown;
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Manage categories'),	
                    'icon' => 'icon-AdminCatalog',			
				),
				'input' => array(
                    array(
                        'type'=>'select_category',
                        'label'=>$this->l('Parent category'),
                        'name'=>'id_parent',
                        'blogCategoryotpionsHtml'=>$blogCategoryotpionsHtml,
                        'form_group_class'=>'parent_category',
                        'tab'=>'basic',
                    ),					
					array(
						'type' => 'text',
						'label' => $this->l('Category title'),
						'name' => 'title',
						'lang' => true,    
                        'required' => true,   
                        'class' => 'title',  
                        'tab'=>'basic',            
					), 
                    array(
						'type' => 'text',
						'label' => $this->l('Meta title'),
						'name' => 'meta_title',
						'lang' => true,        
                        'tab'=>'seo',            
					), 
                    array(
						'type' => 'textarea',
						'label' => $this->l('Meta description'),
						'name' => 'meta_description',
                        'lang' => true,	
                        'tab'=>'seo',
                        'desc' => $this->l('Should contain your focus keyword and be attractive. Meta description should be less than 300 characters.'),				
					),
                    array(
						'type' => 'tags',
						'label' => $this->l('Meta keywords'),
						'name' => 'meta_keywords',
                        'lang' => true,
                        'tab'=>'seo',
                        'hint' => array(
    						$this->l('To add "keywords" click in the field, write something, and then press "Enter."'),
    					),
                        'desc'=>$this->l('Enter your focus keywords and minor keywords'),						
					),
                    array(
						'type' => 'textarea',
						'label' => $this->l('Description'),
						'name' => 'description',
						'lang' => true,  
                        'tab'=>'basic',
                        'autoload_rte' => true,                      
					),
					array(
						'type' => 'text',
						'label' => $this->l('Url alias'),
						'name' => 'url_alias',
                        'required' => true,
                        'lang'=>true,
                        'tab'=>'seo',
                        'hint' => $this->l('Only letters and the hyphen (-) character are allowed.'),
                        'desc' => $this->l('Should be as short as possible and contain your focus keyword'),						
					),
                    array(
						'type' => 'file',
						'label' => $this->l('Category thumbnail image'),
						'name' => 'thumb',
                        'imageType' => 'thumb',
                        'tab'=>'basic',
                        'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_CATEGORY_THUMB_WIDTH',null,null,null,300).'x'.Configuration::get('YBC_BLOG_IMAGE_CATEGORY_THUMB_HEIGHT',null,null,null,170),						
					),
                    array(
						'type' => 'file',
						'label' => $this->l('Main category image'),
						'name' => 'image',
                        'tab'=>'basic',
                        'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_CATEGORY_WIDTH',null,null,null,1920).'x'.Configuration::get('YBC_BLOG_IMAGE_CATEGORY_HEIGHT',null,null,null,750),               						
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'enabled',
                        'is_bool' => true,
                        'tab'=>'basic',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveCategory';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsValues(Ybc_blog_defines::$categoryFields,'id_category','Ybc_blog_category_class','saveCategory'),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'cancel_url' => $this->baseAdminPath.'&control=category&list=true',
            'post_key' => 'id_category',
            'tab_category'=>true,
            'addNewUrl' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category',
		);
        
        if(Tools::isSubmit('id_category') && $this->itemExists('category','id_category',(int)Tools::getValue('id_category')))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_category');
            $category = new Ybc_blog_category_class((int)Tools::getValue('id_category'));
            if($category->image)
            {             
                $helper->tpl_vars['display_img'] = $this->_path.'views/img/category/'.$category->image;
                $helper->tpl_vars['img_del_link'] = $this->baseAdminPath.'&id_category='.Tools::getValue('id_category').'&delcategoryimage=true&control=category';                
            }
            if($category->thumb)
            {             
                $helper->tpl_vars['display_thumb'] = $this->_path.'views/img/category/thumb/'.$category->thumb;
                $helper->tpl_vars['thumb_del_link'] = $this->baseAdminPath.'&id_category='.Tools::getValue('id_category').'&delcategorythumb=true&control=category';                
            }
        }
		$helper->override_folder = '/';
        $this->_html .= $helper->generateForm(array($fields_form));			
    }
    private function _postCategory()
    {
        $errors = array();
        $id_category = (int)Tools::getValue('id_category');
        if($id_category && !$this->itemExists('category','id_category',$id_category) && !Tools::isSubmit('list'))
            Tools::redirectAdmin($this->baseAdminPath);
        /**
         * Change status 
         */
         if(Tools::isSubmit('change_enabled'))
         {
            Hook::exec('actionUpdateBlog', array(
                'id_category' =>(int)Tools::getValue('id_category'),
            ));
            $status = (int)Tools::getValue('change_enabled') ?  1 : 0;
            $field = Tools::getValue('field');
            $id_category = (int)Tools::getValue('id_category');            
            if(($field == 'enabled' && $id_category))
            {
                $this->changeStatus('category',$field,$id_category,$status);
                if($status==1)
                    $title= $this->l('Click to disabled');
                else
                    $title=$this->l('Click to enabled');
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_category,
                        'enabled' => $status,
                        'field' => $field,
                        'message' => $this->displaySuccessMessage($this->l('The status has been successfully updated')) ,
                        'messageType'=>'success',
                        'title'=>$title,
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_category='.$id_category,
                    )));
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&list=true');
            }
         }
        /**
         * Delete image 
         */         
         if($id_category && $this->itemExists('category','id_category',$id_category) && Tools::isSubmit('delcategoryimage'))
         {
            Hook::exec('actionUpdateBlog', array(
                'id_category' => (int)$id_category,
            ));
            $category = new Ybc_blog_category_class($id_category);
            $icoUrl = dirname(__FILE__).'/views/img/category/'.$category->image; 
            if($category->image && file_exists($icoUrl))
            {
                @unlink($icoUrl);
                $category->image = '';
                $category->datetime_modified = date('Y-m-d H:i:s');
                $category->modified_by = (int)$this->context->employee->id;
                $category->update();  
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message' => $this->displayConfirmation($this->l('Category image deleted')),
                        )
                    ));
                }                 
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.$id_category.'&control=category');
            }
            else
                $errors[] = $this->l('Image does not exist');   
         }
         if($id_category && $this->itemExists('category','id_category',$id_category) && Tools::isSubmit('delcategorythumb'))
         {
            Hook::exec('actionUpdateBlog', array(
                'id_category' => (int)$id_category,
            ));
            $category = new Ybc_blog_category_class($id_category);
            $icoUrl = dirname(__FILE__).'/views/img/category/thumb/'.$category->thumb; 
            if($category->thumb && file_exists($icoUrl))
            {
                @unlink($icoUrl);
                $category->thumb = '';
                $category->datetime_modified = date('Y-m-d H:i:s');
                $category->modified_by = (int)$this->context->employee->id;
                $category->update();  
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message' => $this->displayConfirmation($this->l('Category thumbnail image deleted')),
                        )
                    ));
                }                 
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.$id_category.'&control=category');
            }
            else
                $errors[] = $this->l('Thumbnail does not exist');   
         }
        /**
         * Delete category 
         */ 
         if(Tools::isSubmit('del'))
         {
            $id_category = (int)Tools::getValue('id_category');
            Hook::exec('actionUpdateBlog', array(
                'id_category' => (int)$id_category,
            ));
            if(!$this->itemExists('category','id_category',$id_category))
                $errors[] = $this->l('Category does not exist');
            elseif($this->_deleteCategory($id_category))
            {                
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&list=true');
            }                
            else
                $errors[] = $this->l('Could not delete the category. Please try again');    
         }    
         if(Tools::getValue('action')=='updateCategoryOrdering' && $categories=Tools::getValue('cateogires'))
         {
            $page = Tools::getValue('page',1);
            foreach($categories as $key=> $category)
            {
                $position=  1+ $key + ($page-1)*20;
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_category SET sort_order="'.(int)$position.'" WHERE id_category='.(int)$category);
            }
            die(
                Tools::jsonEncode(
                    array(
                        'page'=>$page,
                    )
                )
            );
        }              
        /**
         * Save category 
         */
        if(Tools::isSubmit('saveCategory'))
        {            
            if($id_category && $this->itemExists('category','id_category',$id_category))
            {
                Hook::exec('actionUpdateBlog', array(
                    'id_category' => (int)$id_category,
                ));
                $category = new Ybc_blog_category_class($id_category);  
                $category->datetime_modified = date('Y-m-d H:i:s');
                $category->modified_by = (int)$this->context->employee->id;
                if(Tools::getValue('id_parent')!=$category->id_parent)
                {
                    $category->sort_order = 1+(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_category =cs.id_category AND c.id_parent="'.(int)Tools::getValue('id_parent').'" AND cs.id_shop='.(int)$this->context->shop->id);
                }
            }
            else
            {
                $category = new Ybc_blog_category_class();
                $category->datetime_added = date('Y-m-d H:i:s');
                $category->datetime_modified = date('Y-m-d H:i:s');
                $category->modified_by = (int)$this->context->employee->id;
                $category->added_by = (int)$this->context->employee->id;
                $category->sort_order = 1+(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_category =cs.id_category AND c.id_parent="'.(int)Tools::getValue('id_parent').'" AND cs.id_shop='.(int)$this->context->shop->id);
            }
            $category->enabled = trim(Tools::getValue('enabled',1)) ? 1 : 0;
            $category->id_parent =(int)Tools::getValue('id_parent');
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
			{			
			    $category->title[$language['id_lang']] = trim(Tools::getValue('title_'.$language['id_lang'])) != '' ? trim(Tools::getValue('title_'.$language['id_lang'])) :  trim(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT')));
                $category->meta_title[$language['id_lang']] = trim(Tools::getValue('meta_title_'.$language['id_lang'])) != '' ? trim(Tools::getValue('meta_title_'.$language['id_lang'])) :  trim(Tools::getValue('meta_title_'.Configuration::get('PS_LANG_DEFAULT')));
                $category->url_alias[$language['id_lang']] = trim(Tools::getValue('url_alias_'.$language['id_lang'])) != '' ? trim(Tools::getValue('url_alias_'.$language['id_lang'])) :  trim(Tools::getValue('url_alias_'.Configuration::get('PS_LANG_DEFAULT')));
                if($category->title[$language['id_lang']] && !Validate::isCleanHtml($category->title[$language['id_lang']]))
                    $errors[] = $this->l('Title in '.$language['name'].' is not valid');
                if($category->url_alias[$language['id_lang']] && !Ybc_blog::checkIsLinkRewrite($category->url_alias[$language['id_lang']]))
                    $errors[] = $this->l('Url alias in '.$language['name'].' is not valid');
                if($category->url_alias[$language['id_lang']] && Db::getInstance()->getValue('SELECT cs.id_category FROM '._DB_PREFIX_.'ybc_blog_category_lang cl, '._DB_PREFIX_.'ybc_blog_category_shop cs WHERE cl.id_category = cs.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND  cl.url_alias ="'.pSQL($category->url_alias[$language['id_lang']]).'" AND cs.id_category!="'.(int)$category->id.'"'))
                    $errors[] = $this->l('Url alias in '.$language['name'].' is exists'); 
                if($category->meta_title[$language['id_lang']] && !Validate::isCleanHtml($category->meta_title[$language['id_lang']]))
                    $errors[] = $this->l('Meta title in '.$language['name'].' is not valid');
                $category->meta_description[$language['id_lang']] = trim(Tools::getValue('meta_description_'.$language['id_lang'])) != '' ? trim(Tools::getValue('meta_description_'.$language['id_lang'])) :  trim(Tools::getValue('meta_description_'.Configuration::get('PS_LANG_DEFAULT')));
                if($category->meta_description[$language['id_lang']] && !Validate::isCleanHtml($category->meta_description[$language['id_lang']], true))
                    $errors[] = $this->l('Meta description in '.$language['name'].' is not valid');
                $category->meta_keywords[$language['id_lang']] = trim(Tools::getValue('meta_keywords_'.$language['id_lang'])) != '' ? trim(Tools::getValue('meta_keywords_'.$language['id_lang'])) :  trim(Tools::getValue('meta_keywords_'.Configuration::get('PS_LANG_DEFAULT')));
                if($category->meta_keywords[$language['id_lang']] && !Validate::isTagsList($category->meta_keywords[$language['id_lang']], true))
                    $errors[] = $this->l('Meta keywords in '.$language['name'].' are not valid');
                $category->description[$language['id_lang']] = trim(Tools::getValue('description_'.$language['id_lang'])) != '' ? trim(Tools::getValue('description_'.$language['id_lang'])) :  trim(Tools::getValue('description_'.Configuration::get('PS_LANG_DEFAULT')));
                if($category->description[$language['id_lang']] && !Validate::isCleanHtml($category->description[$language['id_lang']], true))
                    $errors[] = $this->l('Description in '.$language['name'].' is not valid');                	
            }
            
            if(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT'))=='')
                $errors[] = $this->l('You need to set blog category title');                    
            if($category->url_alias[Configuration::get('PS_LANG_DEFAULT')]=='')
                $errors[] = $this->l('Url alias is required');
            /**
             * Upload image 
             */  
            $oldImage = false;
            $newImage = false;       
            if(isset($_FILES['image']['tmp_name']) && isset($_FILES['image']['name']) && $_FILES['image']['name'])
            {
                if(file_exists(dirname(__FILE__).'/views/img/category/'.$_FILES['image']['name']))
                {
                    $_FILES['image']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['image']['name'];
                }
                
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image']['name'], '.'), 1));
    			$imagesize = @getimagesize($_FILES['image']['tmp_name']);
    			if (isset($_FILES['image']) &&				
    				!empty($_FILES['image']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{
    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
    				if ($error = ImageManager::validateUpload($_FILES['image']))
    					$errors[] = $error;
    				elseif (!$temp_name || !move_uploaded_file($_FILES['image']['tmp_name'], $temp_name))
    					$errors[] = $this->l('Can not upload the file');
    				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/category/'.$_FILES['image']['name'], Configuration::get('YBC_BLOG_IMAGE_CATEGORY_WIDTH',null,null,null,1920), Configuration::get('YBC_BLOG_IMAGE_CATEGORY_HEIGHT',null,null,null,750), $type))
    					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
    				if (isset($temp_name))
    					@unlink($temp_name);
                    if($category->image)
                        $oldImage = dirname(__FILE__).'/views/img/category/'.$category->image;
                    $category->image = $_FILES['image']['name'];
                    $newImage = dirname(__FILE__).'/views/img/category/'.$category->image;			
    			}
                
            }
            $oldThumb='';
            $newThumb='';
            if(isset($_FILES['thumb']['tmp_name']) && isset($_FILES['thumb']['name']) && $_FILES['thumb']['name'])
            {
                if(file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$_FILES['thumb']['name']))
                {
                    $_FILES['thumb']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['thumb']['name'];
                }
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['thumb']['name'], '.'), 1));
    			$imagesize = @getimagesize($_FILES['thumb']['tmp_name']);
    			if (isset($_FILES['thumb']) &&				
    				!empty($_FILES['thumb']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{
    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
    				if ($error = ImageManager::validateUpload($_FILES['thumb']))
    					$errors[] = $error;
    				elseif (!$temp_name || !move_uploaded_file($_FILES['thumb']['tmp_name'], $temp_name))
    					$errors[] = $this->l('Can not upload the file');
    				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/category/thumb/'.$_FILES['thumb']['name'], Configuration::get('YBC_BLOG_IMAGE_CATEGORY_THUMB_WIDTH',null,null,null,300), Configuration::get('YBC_BLOG_IMAGE_CATEGORY_THUMB_HEIGHT',null,null,null,170), $type))
    					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
    				if (isset($temp_name))
    					@unlink($temp_name);
                    if($category->image)
                        $oldThumb = dirname(__FILE__).'/views/img/category/thumb/'.$category->thumb;
                    $category->thumb = $_FILES['thumb']['name'];
                    $newThumb = dirname(__FILE__).'/views/img/category/thumb/'.$category->thumb;			
    			}
                
            }			
            
            /**
             * Save 
             */    
             
            if(!$errors)
            {
                if (!Tools::getValue('id_category'))
    			{
    				if (!$category->add())
                    {
                        $errors[] = $this->displayError($this->l('The category could not be added.'));
                        if($newImage && file_exists($newImage))
                            @unlink($newImage);   
                        if($newThumb && file_exists($newThumb))
                            @unlink($newThumb);                  
                    }
                    else
                    {
                        $id_category = $this->getMaxId('category','id_category');
                        Hook::exec('actionUpdateBlogImage', array(
                            'id_category' =>(int)$category->id,
                            'image' => $newImage ? $category->image :false,
                            'thumb' => $newThumb ? $category->thumb : false,
                        ));
                    }                	                    
    			}				
    			elseif (!$category->update())
                {
                    if($newImage && file_exists($newImage))
                        @unlink($newImage); 
                    if($newThumb && file_exists($newThumb))
                        @unlink($newThumb); 
                    $errors[] = $this->displayError($this->l('The category could not be updated.'));
                }
                else
                {
                    if($oldImage && file_exists($oldImage))
                        @unlink($oldImage); 
                    if($oldThumb && file_exists($oldThumb))
                        @unlink($oldThumb); 
                    Hook::exec('actionUpdateBlogImage', array(
                        'id_category' =>(int)$category->id,
                        'image' => $newImage ? $category->image :false,
                        'thumb' => $newThumb ? $category->thumb : false,
                    ));
                }
    					                
            }
         }
         if (count($errors))
         {
            if($newImage && file_exists($newImage))
                @unlink($newImage); 
            if($newThumb && file_exists($newThumb))
                @unlink($newThumb); 
            $this->errorMessage = $this->displayError($errors);  
         }
         $changedImages = array();
         if(!$errors && isset($newImage) && $newImage && file_exists($newImage) && isset($category) && $category){
                $changedImages[] = array(
                    'name' => 'image',
                    'url' => $this->_path.'views/img/category/'.$category->image,
                    'delete_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.Tools::getValue('id_category').'&delcategoryimage=true&control=category',
                );
         } 
         if(!$errors && isset($newThumb) && $newThumb && file_exists($newThumb) && isset($category) && $category){
                $changedImages[] = array(
                    'name' => 'thumb',
                    'url' => $this->_path.'views/img/category/thumb/'.$category->thumb,
                    'delete_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.Tools::getValue('id_category').'&delcategorythumb=true&control=category',
                );
         }        
         if(Tools::isSubmit('ajax'))
            {
                die(Tools::jsonEncode(
                    array(
                        'messageType' => $errors ? 'error' : 'success',
                        'message' => $errors ? $this->errorMessage : (isset($id_category) && $id_category ? $this->displaySuccessMessage($this->l('Category updated'),$this->l('View category'),$this->getLink('blog',array('id_category'=>$id_category))) : $this->displayConfirmation($this->l('Category updated'))),
                        'images' => isset($changedImages) && $changedImages ? $changedImages : array(),
                        'postUrl' => !$errors && Tools::isSubmit('saveCategory') && !(int)Tools::getValue('id_category') ? $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.$this->getMaxId('category','id_category').'&control=category' : 0,
                        'itemKey' => 'id_category',
                        'itemId' => !$errors && Tools::isSubmit('saveCategory') && !(int)Tools::getValue('id_category') ? $this->getMaxId('category','id_category') : ((int)Tools::getValue('id_category') > 0 ? (int)Tools::getValue('id_category') : 0),
                    )
                ));
            } 
         if (Tools::isSubmit('saveCategory') && Tools::isSubmit('id_category'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.Tools::getValue('id_category').'&control=category');
		 elseif (Tools::isSubmit('saveCategory'))
         {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_category='.$this->getMaxId('category','id_category').'&control=category');
         }
    }
    public function displaySuccessMessage($msg, $title = false, $link = false)
    {
         $this->smarty->assign(array(
            'msg' => $msg,
            'title' => $title,
            'link' => $link
         ));
         if($msg)
            return $this->displayConfirmation($this->display(__FILE__, 'success_message.tpl'));
    }
    private function _deleteCategory($id_category)
    {
        if($this->itemExists('category','id_category',$id_category))
        {
            $category = new Ybc_blog_category_class($id_category);
            if($category->image && file_exists(dirname(__FILE__).'/views/img/category/'.$category->image))
            {
                @unlink(dirname(__FILE__).'/views/img/category/'.$category->image);
            } 
            if($category->thumb && file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$category->thumb))
            {
                @unlink(dirname(__FILE__).'/views/img/category/thumb/'.$category->thumb);
            }
            $id_parent = $category->id_parent;           
            if($category->delete())
            {                                
                $posts = $this->getPostsByIdCategory($id_category);
                if($posts)
                {
                    foreach($posts as $post)
                    {
                        if($this->itemExists('post','id_post',$post['id_post']))
                        {
                            $categories = $this->getCategoriesByIdPost($post['id_post']);
                            if(count($categories) <= 1)
                            {
                                $this->_deletePost($post['id_post']);
                            }
                        }
                    }
                }
                $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_category=".(int)$id_category;
                Db::getInstance()->execute($req);
                $req ="DELETE FROM "._DB_PREFIX_."ybc_blog_category_shop WHERE id_category=".(int)$id_category;
                Db::getInstance()->execute($req);
                $categories = Db::getInstance()->executeS('SELECT c.id_category FROM '._DB_PREFIX_.'ybc_blog_category c
                INNER JOIN '._DB_PREFIX_.'ybc_blog_category_shop cs ON (c.id_category=cs.id_category)
                WHERE cs.id_shop= "'.(int)$this->context->shop->id.'" AND c.id_parent='.(int)$id_parent.' ORDER BY c.sort_order ASC');
                if($categories)
                {
                    foreach($categories as $key=> $category)
                    {
                        $position =$key+1;
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_post_category SET position="'.(int)$position.'" WHERE id_category='.(int)$category['id_category']);
                    }
                }
                return true;
            }
        }
        return false;        
    }    
    
    /**
     * Post 
     */
    public function renderPostListByCustomer()
    {
        if(!Tools::isSubmit('editpost') && !Tools::isSubmit('addpost'))
        {
            $fields_list = array(
                'id_post' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'id_post','sort_type'=>'asc')),
                    'sort_desc' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'id_post','sort_type'=>'desc')),
                    'filter' => true,
                ),
                'title' => array(
                    'title' => $this->l('Title'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'title','sort_type'=>'asc')),
                    'sort_desc' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'title','sort_type'=>'desc')),
                    'filter' => true,
                    'strip_tag'=>false,
                ),
                'total_comment' => array(
                    'title' => $this->l('Comments'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'total_comment','sort_type'=>'asc')),
                    'sort_desc' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'total_comment','sort_type'=>'desc')),
                ),
                'enabled' => array(
                    'title' => $this->l('Status'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'enable','sort_type'=>'asc')),
                    'sort_desc' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','sort'=>'enable','sort_type'=>'desc')),
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Published')
                            ),
                            1=>array(
                                'enabled'=>-1,
                                'title' =>$this->l('Pending')
                            ),
                            2 => array(
                                'enabled' => 0,
                                'title' => $this->l('Unpublished')
                            ),
                            3 => array(
                                'enabled' => 2,
                                'title' => $this->l('Schedule publish date')
                            ),
                        )
                    )
                )
            );
            //Filter
            $filter=" AND p.added_by =".(int)$this->context->customer->id." AND p.is_customer=1";
            if(trim(Tools::getValue('id_post'))!='')
                $filter .= " AND p.id_post = ".(int)trim(urldecode(Tools::getValue('id_post')));
            if(trim(Tools::getValue('sort_order'))!='')
                $filter .= " AND p.sort_order = ".(int)trim(urldecode(Tools::getValue('sort_order')));
            if(trim(Tools::getValue('click_number'))!='')
                $filter .= " AND p.click_number = ".(int)trim(urldecode(Tools::getValue('click_number')));
            if(trim(Tools::getValue('likes'))!='')
                $filter .= " AND p.likes = ".(int)trim(urldecode(Tools::getValue('likes')));            
            if(trim(Tools::getValue('title'))!='')
                $filter .= " AND pl.title like '%".addslashes(trim(urldecode(Tools::getValue('title'))))."%'";
            if(trim(Tools::getValue('description'))!='')
                $filter .= " AND pl.description like '%".addslashes(trim(urldecode(Tools::getValue('description'))))."%'";
            if(trim(Tools::getValue('id_category'))!='')
                $filter .= " AND p.id_post IN (SELECT id_post FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_category = ".(int)trim(urldecode(Tools::getValue('id_category'))).") ";
            if(trim(Tools::getValue('enabled'))!='')
                $filter .= " AND p.enabled = ".(int)trim(urldecode(Tools::getValue('enabled')));
            if(trim(Tools::getValue('is_featured'))!='')
                $filter .= " AND p.is_featured = ".(int)trim(urldecode(Tools::getValue('is_featured')));
            //Sort
            $sort = "";
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = false;
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 && Tools::getValue('tabmanagament')=='post' ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countPostsWithFilter($filter);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','page'=>'_page_',)).$this->getUrlExtraFrontEnd($fields_list,'ybc_submit_ybc_post');
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $posts = $this->getPostsWithFilter($filter, $sort, $start, $paggination->limit);            
            if($posts)
            {
                foreach($posts as &$post)
                {
                    $post['id_category'] = $this->getCategoriesStrByIdPost($post['id_post']);
                    $post['view_url'] = $this->getLink('blog',array('id_post'=>$post['id_post']));
                    $post['title']= '<a href="'.$post['view_url'].'" title="'.$post['title'].'">'.$post['title'].'</a>';
                    if(($privileges= explode(',',Configuration::get('YBC_BLOG_AUTHOR_PRIVILEGES'))) && in_array('edit_blog',$privileges))
                    {
                        $post['edit_url'] = $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','editpost'=>1,'id_post'=>$post['id_post']));
                    }
                    if(($privileges= explode(',',Configuration::get('YBC_BLOG_AUTHOR_PRIVILEGES'))) && in_array('delete_blog',$privileges))
                    {
                        $post['delete_url'] = $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','deletepost'=>1,'id_post'=>$post['id_post']));
                    }
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_post',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post')),
                'identifier' => 'id_post',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Blog posts'),
                'fields_list' => $fields_list,
                'field_values' => $posts,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParamsFontEnd($fields_list,'ybc_submit_ybc_post'),
                'show_reset' => trim(Tools::getValue('likes'))!='' || trim(Tools::getValue('sort_order'))!='' || trim(Tools::getValue('click_number'))!='' || trim(Tools::getValue('enabled'))!='' || trim(Tools::getValue('is_featured'))!='' ||  trim(Tools::getValue('id_category'))!=''  ||  trim(Tools::getValue('id_post'))!='' || trim(Tools::getValue('description'))!='' || trim(Tools::getValue('title'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'totalPost' => (int)$this->countPostsWithFilter(" AND p.added_by =".(int)$this->context->customer->id." AND p.is_customer=1"),
                'preview_link' => $this->getLink('blog'),
                'show_add_new' => true,
                'link_addnew'=> $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'post','addpost'=>1)),
                'sort'=>Tools::getValue('sort'),
                'sort_type'=>Tools::getValue('sort_type'),
                                
            );            
            return $this->renderListPostByCustomer($listData);
        }
        else
            return $this->displayFormBlog();
    }
    public function renderPostForm($filter='',$list=false)
    {
        //List 
        $show_reset=false;
        if(trim(Tools::getValue('list'))=='true' || $list)
        {
            $fields_list = array(
                'id_post' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'thumb_link'=>array(
                    'title'=> $this->l('Image'),
                    //'width' => 40,
                    'type' => 'text',
                    'strip_tag'=>false,
                ),
                'title' => array(
                    'title' => $this->l('Title'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'id_category' => array(
                    'title' => $this->l('Categories'),
                    //'width' => 100,
                    'type' => 'select',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'id_category',
                        'value' => 'title',
                        'list' => $this->getCategories()
                    )
                ),
                'name_author'=>(
                      array(
                        'title'=>$this->l('Author'),
                        //'width' => 40,
                        'type' => 'text',
                        //'width' => 100,
                        'filter'=>true,
                        'strip_tag'=>false,
                      )  
                ),
                'sort_order' => array(
                    'title' => $this->l('Sort order'),
                    //'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                    'update_position' => true,
                ),
                'position' => array(
                    'title' => $this->l('Sort order'),
                    //'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                    'update_position' => true,
                ),
                'click_number' => array(
                    'title' => $this->l('Views'),
                    //'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'likes' => array(
                    'title' => $this->l('Likes'),
                    //'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'enabled' => array(
                    'title' => $this->l('Status'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Published')
                            ),
                            1=>array(
                                'enabled'=>-1,
                                'title' =>$this->l('Pending')
                            ),
                            2 => array(
                                'enabled' => 0,
                                'title' => $this->l('Disabled')
                            ),
                            2 => array(
                                'enabled' => -2,
                                'title' => $this->l('Preview')
                            ),
                            3=>array(
                                'enabled'=>2,
                                'title' =>$this->l('Schedule publish date')
                            )
                        )
                    )
                ),
                'is_featured' => array(
                    'title' => $this->l('Featured'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'is_featured',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'is_featured' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'is_featured' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                ),
            );
            if(trim(Tools::getValue('id_category'))!='')
                unset($fields_list['sort_order']);
            else
                unset($fields_list['position']);
            //Filter
            if(trim(Tools::getValue('id_post'))!='')
                $filter .= " AND p.id_post = ".(int)trim(urldecode(Tools::getValue('id_post')));
            if(trim(Tools::getValue('sort_order'))!='')
                $filter .= " AND p.sort_order = ".(int)trim(urldecode(Tools::getValue('sort_order')));
            if(trim(Tools::getValue('click_number'))!='')
                $filter .= " AND p.click_number = ".(int)trim(urldecode(Tools::getValue('click_number')));
            if(trim(Tools::getValue('likes'))!='')
                $filter .= " AND p.likes = ".(int)trim(urldecode(Tools::getValue('likes')));            
            if(trim(Tools::getValue('title'))!='')
                $filter .= " AND pl.title like '%".addslashes(trim(urldecode(Tools::getValue('title'))))."%'";
            if(trim(Tools::getValue('description'))!='')
                $filter .= " AND pl.description like '%".addslashes(trim(urldecode(Tools::getValue('description'))))."%'";
            if(trim(Tools::getValue('id_category'))!='')
                $filter .= " AND p.id_post IN (SELECT id_post FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_category = ".(int)trim(urldecode(Tools::getValue('id_category'))).") ";
            if(trim(Tools::getValue('enabled'))!='')
                $filter .= " AND p.enabled = ".(int)trim(urldecode(Tools::getValue('enabled')));
            if(trim(Tools::getValue('is_featured'))!='')
                $filter .= " AND p.is_featured = ".(int)trim(urldecode(Tools::getValue('is_featured')));
            if(trim(Tools::getValue('name_author'))!='')
                $filter .=" AND (CONCAT(e.firstname,' ', e.lastname) like '%".pSQL(trim(Tools::getValue('name_author')))."%' OR CONCAT(c.firstname,' ', c.lastname) like '%".pSQL(trim(Tools::getValue('name_author')))."%')";
            //Sort
            $sort = 'p.id_post DESC,';
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort = trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            if($filter)
                $show_reset=true;
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countPostsWithFilter($filter,false);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $posts = $this->getPostsWithFilter($filter, $sort, $start, $paggination->limit,false);        
            if($posts)
            {
                foreach($posts as &$post)
                {
                    $post['id_category'] = $this->getCategoriesStrByIdPost($post['id_post']);
                    $url = $this->getLink('blog',array('id_post'=>$post['id_post']));
                    if($post['enabled']==-2)
                    {
                        if(Tools::strpos('?',$url)!==false)
                            $url .= '&preview=1';
                        else
                            $url .= '?preview=1';
                    }
                    $post['view_url'] = $url;
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_post',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post',
                'identifier' => 'id_post',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Posts'),
                'fields_list' => $fields_list,
                'field_values' => $posts,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' =>  $show_reset,
                'totalRecords' => $totalRecords,
                'preview_link' => $this->getLink('blog'),
                'sort' => Tools::getValue('sort','id_post'),   
                'sort_type' => Tools::getValue('sort_type','desc'),             
            );            
            return $list? $this->renderList($listData): $this->_html .= $this->renderList($listData);      
        }
        //Form
        if(Tools::isSubmit('id_post') && $this->itemExists('post','id_post',(int)Tools::getValue('id_post')))
        {
            $post= new Ybc_blog_post_class(Tools::getValue('id_post'));
        }
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Manage posts'),				
				),
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Post title'),
						'name' => 'title',
						'lang' => true,    
                        'required' => true, 
                        'tab'=>'basic',
                        'class' => 'title',
					    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',                               
					),
                    array(
                        'type' => 'text',
						'label' => $this->l('Meta title'),
						'name' => 'meta_title',
						'lang' => true,    
                        'tab'=>'seo',   
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'desc' => $this->l('Should contain your focus keyword and be attractive'),
                    ),
                    array(
						'type' => 'textarea',
						'label' => $this->l('Meta description'),
						'name' => 'meta_description',
                        'lang' => true,
                        'tab'=>'seo',
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'desc' => $this->l('Should contain your focus keyword and be attractive. Meta description should be less than 300 characters.'),						
					),
                    array(
						'type' => 'tags',
						'label' => $this->l('Meta keywords'),
						'name' => 'meta_keywords',
                        'lang' => true,
                        'tab'=>'seo',
                        'hint' => array(
    						$this->l('To add "keywords" click in the field, write something, and then press "Enter."'),
    						$this->l('Invalid characters:').' &lt;&gt;;=#{}'
    					),
                        'desc'=>$this->l('Enter your focus keywords and minor keywords'),						
					),
                    array(
						'type' => 'text',
						'label' => $this->l('Url alias'),
						'name' => 'url_alias',
                        'required' => true,
                        'lang'=>true,
                        'tab'=>'seo',
                        'hint' => $this->l('Only letters and the hyphen (-) character are allowed.'),
                        'desc' => $this->l('Should be as short as possible and contain your focus keyword'),						
					),
                    array(
						'type' => 'tags',
						'label' => $this->l('Tags'),
						'name' => 'tags',                        
                        'lang' => true,
                        'tab'=>'option',
                        'hint' => array(
    						$this->l('To add "tags" click in the field, write something, and then press "Enter."'),
    						$this->l('Invalid characters:').' &lt;&gt;;=#{}'
    					),
                        'desc'=>$this->l('Tags are separated by a comma. Related posts are the posts in the same tag or in the same post categories.'),							
					),
                    array(
						'type' => 'textarea',
						'label' => $this->l('Short description'),
						'name' => 'short_description',
						'lang' => true,  
                        'required' => true,
                        'autoload_rte' => true,
                        'tab'=>'basic',
                        'hint' => $this->l('Invalid characters:').' <>;=#{}',
                        'desc' => $this->l('Short description is displayed in post listing pages'),                      
					),
                    array(
						'type' => 'textarea',
						'label' => $this->l('Post content'),
						'name' => 'description',
						'lang' => true,  
                        'autoload_rte' => true,
                        'required' => true,
                        'tab'=>'basic',
                        'hint' => $this->l('Invalid characters:').' <>;=#{}',
                        'desc' => $this->l('Post content is displayed in post details page (single page).'),                          
					),
                    array(
						'type' => 'file',
						'label' => $this->l('Post thumbnail'),
						'name' => 'thumb',
                        'imageType' => 'thumb',
                        'required' => true,
                        'tab'=>'basic',
                        'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH',null,null,null,260).'x'.Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT',null,null,null,180).'. '.$this->l('Post thumbnail image is required. You should adjust your image to the recommended size before uploading it.'),						
					),
                    array(
						'type' => 'file',
						'label' => $this->l('Blog post main image'),
						'name' => 'image',
                        'tab'=>'basic',
                        'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_BLOG_WIDTH',null,null,null,1920).'x'.Configuration::get('YBC_BLOG_IMAGE_BLOG_HEIGHT',null,null,null,750),						
					),
                    array(
    					'type' => 'blog_categories',
    					'label' => $this->l('Post categories'),
                        'html_content' =>$this->displayBlogCategoryTre($this->getBlogCategoriesTree(0),$this->getSelectedCategories((int)Tools::getValue('id_post'))),
    					'categories' => $this->getBlogCategoriesTree(0),
    					'name' => 'categories',
                        'required' => true,
                        'tab'=>'basic',
                        'selected_categories' => $this->getSelectedCategories((int)Tools::getValue('id_post'))                                           
    				),
                    array(
						'type' => 'products_search',
						'label' => $this->l('Related products'),
						'name' => 'products',
                        'selected_products' => $this->getSelectedProducts((int)Tools::getValue('id_post')),	
                        'tab'=>'option',					
					    'hint' => array(
    						$this->l('To add "products", type in product name and choose the product from the dropdown'),
    						$this->l('Invalid characters:').' &lt;&gt;;=#{}'
    					),	
                        'desc' => $this->l('Related products are displayed on post details page'),
                    ),
                    array(
    					'type'  => 'categories',
    					'label' => $this->l('Related product categories'),
    					'name'  => 'related_categories',
                        'tab'=>'option',
    					'tree'  => array(
    						'id'      => 'categories-tree',
    						'selected_categories' => $this->getSelectedRelatedCategories((int)Tools::getValue('id_post')),
                            'use_search' => true,
                            'use_checkbox' => true,
    					),
                        'showRequired' => true,
                        'desc' => $this->l('Check on product categories that you want to display this post on their "Related posts" section on the front office'),
    				),
                    array(
						'type' => 'text',
						'label' => $this->l('Views'),
						'name' => 'click_number',
                        'required' => true,      
                        'tab'=>'option',
                        'desc' => $this->l('The number of post view will be increased from this number'),                  						
					),
                    array(
						'type' => 'text',
						'label' => $this->l('Likes'),
						'name' => 'likes',
                        'required' => true, 
                        'tab'=>'option',    
                        'desc' => $this->l('The number of post likes will be increased from this number'),                   						
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Is featured post'),
						'name' => 'is_featured',
                        'is_bool' => true,
                        'tab'=>'option',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
                        'desc' => $this->l('Enable this if you want to display this post in "Featured posts" section on the front office')					
					),
                    array(
						'type' => 'select',
						'label' => $this->l('Status'),
						'name' => 'enabled',
                        'tab'=>'basic',
						'options' => array(
                            'query' => array( 
                                    array(
                                        'id_option' => 1, 
                                        'name' => $this->l('Published')
                                    ),        
                                    array(
                                        'id_option' => -1, 
                                        'name' => $this->l('Pending')
                                    ),
                                    array(
                                        'id_option'=>-2,
                                        'name' => $this->l('Preview'),
                                    ),
                                    array(
                                        'id_option' => 0, 
                                        'name' => $this->l('Disabled')
                                    ),
                                    array(
                                        'id_option' => 2, 
                                        'name' => $this->l('Schedule publish date')
                                    ),
                                ),                             
                             'id' => 'id_option',
                			 'name' => 'name'  
                        ),					
					),
                    array(
						'type' => 'date',
						'label' => $this->l('Publish date'),
						'name' => 'datetime_active',	
                        'tab'=>'basic',
                        'desc'=> $this->l('You can select the time to automatically publish this post. Leave blank to save this post as draft'),				
					),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				),
                'buttons'=> array(
                    array(
                        'type'=>'submit',
                        'name' =>'submitSaveAndPreview',
                        'title' => $this->l('Save and preview'),
                        'class' => Tools::getValue('id_post') && isset($post) && $post->enabled!=-2 ? 'pull-right hide':'pull-right',
                        'icon'=>'process-icon-save',
                    )
                ),
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savePost';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = $this->context->employee->id ? Tools::getAdminTokenLite('AdminModules'): false;
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsValues(Ybc_blog_defines::$postFields,'id_post','Ybc_blog_post_class','savePost'),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'post_key' => 'id_post',
            'tab_post' => true,
            'check_suspend' => $this->checkPostSuspend(),
            'form_author_post' => $this->getFormAuthorPost(Tools::getValue('id_post')),
            'cancel_url' => $this->baseAdminPath.'&control=post&list=true',
            'addNewUrl' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post',
            'preview_link' => Tools::getValue('id_post') ? $this->getLink('blog',array('id_post'=>Tools::getValue('id_post'))):'',
		);
        
        if(Tools::isSubmit('id_post') && $this->itemExists('post','id_post',(int)Tools::getValue('id_post')))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_post');
            $post = new Ybc_blog_post_class((int)Tools::getValue('id_post'));
            if($post->image && file_exists(dirname(__FILE__).'/views/img/post/'.$post->image))
            {             
                $helper->tpl_vars['display_img'] = $this->_path.'views/img/post/'.$post->image;
                $helper->tpl_vars['img_del_link'] = $this->baseAdminPath.'&id_post='.Tools::getValue('id_post').'&delpostimage=true&control=post';                
            }
            if($post->thumb && file_exists(dirname(__FILE__).'/views/img/post/thumb/'.$post->thumb))
            {             
                $helper->tpl_vars['display_thumb'] = $this->_path.'views/img/post/thumb/'.$post->thumb;
                $helper->tpl_vars['thumb_del_link'] = $this->baseAdminPath.'&id_post='.Tools::getValue('id_post').'&delpostthumb=true&control=post';                
            }
        }
        
		$helper->override_folder = '/';
        $this->_html .= $helper->generateForm(array($fields_form));			
    }
    public function checkPostSuspend(){
        if(!Tools::getValue('id_post'))
            return false;
        else
        {
            $author = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p
                INNER JOIN '._DB_PREFIX_.'ybc_blog_employee ybc ON (p.is_customer=ybc.is_customer AND p.added_by = ybc.id_employee)
                WHERE id_post="'.(int)Tools::getValue('id_post').'" AND ybc.status=-1');
            if($author)
            {
                return true;
            }
        }   
        return false;
    }
    public function getFormAuthorPost($id_post)
    {
        
        if(!$id_post)
            return '';
        $post = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p WHERE id_post='.(int)$id_post);
        if($post['is_customer'])
        {
            $author = Db::getInstance()->getRow('SELECT c.id_customer,c.firstname,c.lastname,ybe.is_customer,ybe.name FROM '._DB_PREFIX_.'customer c
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee ybe on (ybe.is_customer=1 AND ybe.id_employee=c.id_customer)
                WHERE c.id_customer= "'.$post['added_by'].'"
            ');
            if($author)
                $author['link']= $this->context->link->getAdminLink('AdminCustomers').'&updatecustomer&id_customer='.$author['id_customer'];
        }
        else
        {
            $author = Db::getInstance()->getRow('SELECT e.id_employee,e.firstname,e.lastname,ybe.is_customer,ybe.name FROM '._DB_PREFIX_.'employee e
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee ybe on (ybe.is_customer=0 AND ybe.id_employee=e.id_employee)
                WHERE e.id_employee="'.(int)$post['added_by'].'"
            ');
            if($author)
                $author['link']= $this->context->link->getAdminLink('AdminEmployees').'&id_employee='.(int)$author['id_employee'].'&updateemployee';
        }
        if($author)
        {
            $this->context->smarty->assign(
                array(
                    'author'=> $author,
                )
            );
        }
        $admin_authors= Db::getInstance()->executeS(
        'SELECT e.id_employee,e.firstname,e.lastname,ybe.name,ybe.is_customer FROM '._DB_PREFIX_.'employee e
        LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee ybe ON (ybe.is_customer =0 AND ybe.id_employee=e.id_employee)
        ');
        if($admin_authors)
        {
            foreach($admin_authors as &$admin_author)
                $admin_author['link'] = $this->context->link->getAdminLink('AdminEmployees').'&id_employee='.(int)$admin_author['id_employee'].'&updateemployee';    
        }
        $this->context->smarty->assign(
            array(
                'YBC_BLOG_ALLOW_CUSTOMER_AUTHOR' => Configuration::get('YBC_BLOG_ALLOW_CUSTOMER_AUTHOR') && $this->countCustomersFilter(false),
                'admin_authors' => $admin_authors,
                'post'=> $post,                
            )
        );
        return $this->display(__FILE__,'form_author_post.tpl');
    }
    private function _postCustomer()
    {
        $errors=array();
        if(Tools::isSubmit('deleteAllPostCustomer') && $id_author=Tools::getValue('id_author'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_author' => (int)$id_author,
            ));
            $posts= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post WHERE is_customer=1 AND added_by="'.(int)$id_author.'"');
            if($posts)
            {
                foreach($posts as $post)
                {
                    if($this->_deletePost($post['id_post']))
                    {   
                        $posts = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p, '._DB_PREFIX_.'ybc_blog_post_shop ps  WHERE p.id_post = ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" order by sort_order asc');
                        if($posts)
                        {
                            foreach($posts as $key=> $post)
                            {
                                $position=$key+1;
                                Db::getInstance()->execute('update '._DB_PREFIX_.'ybc_blog_post SET sort_order ="'.(int)$position.'" WHERE id_post='.(int)$post['id_post']);
                            }
                        }
                    }
                }
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer&list=true');
        }
        if(Tools::isSubmit('delemployeeimage'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)Tools::getValue('id_customer').' AND is_customer=1');
            Hook::exec('actionUpdateBlog', array(
                'id_author' =>(int)Tools::getValue('id_customer'),
            ));
            $employeePost = new Ybc_blog_post_employee_class($id_employee_post);
            @unlink(dirname(__FILE__).'/../views/img/avata/'.$employeePost->avata);
            $employeePost->avata='';
            $employeePost->update();
            if(Tools::isSubmit('ajax'))
            {
                die(Tools::jsonEncode(
                    array(
                        'messageType' => 'success',
                        'message' => $this->displayConfirmation($this->l('Avatar image deleted')),
                    )
                ));
            }            
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post');
                 
        }
        if(Tools::isSubmit('change_enabled'))
        {
            $status = (int)Tools::getValue('change_enabled');
            $field = Tools::getValue('field');
            $id_customer = (int)Tools::getValue('id_customer');  
            Hook::exec('actionUpdateBlog', array(
                'id_author' =>(int)Tools::getValue('id_customer'),
            ));
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$id_customer.' AND is_customer=1');          
            if(($field == 'status' && $id_customer))
            {
                if($id_employee_post)
                {
                    $employeePost= new Ybc_blog_post_employee_class($id_employee_post);
                    $employeePost->status=$status;
                    $employeePost->update();
                }
                else
                {
                    $employeePost = new Ybc_blog_post_employee_class();
                    $employeePost->status=$status;
                    $customer = new Customer($id_customer);
                    $employeePost->id_employee = $id_customer;
                    $employeePost->is_customer=1;
                    $employeePost->name = $customer->firstname.' '.$customer->lastname;
                    $employeePost->add();
                }  
                if($status==1)
                    $title= $this->l('Click to suspend'); 
                else
                    $title= $this->l('Click to active');
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_customer,
                        'enabled' => Tools::getValue('change_enabled'),
                        'field' => $field,
                        'message' => $this->displaySuccessMessage($this->l('The status has been successfully updated')),
                        'messageType'=>'success',
                        'title'=>$title,
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_customer='.$id_customer,
                    )));
                }  
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer&list=true');
            }
        }
        if($id_customer=Tools::getValue('id_customer'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$id_customer.' AND is_customer=1');
            if(Tools::isSubmit('saveBlogEmployee'))
            {
                Hook::exec('actionUpdateBlog', array(
                    'id_author' =>(int)Tools::getValue('id_customer'),
                ));
                if($id_employee_post)
                {
                    $employeePost= new Ybc_blog_post_employee_class($id_employee_post);
                }
                else
                    $employeePost = new Ybc_blog_post_employee_class();
                $employeePost->id_employee=$id_customer;
                $employeePost->is_customer=1;
                $employeePost->status = (int)Tools::getValue('status');
                if(!Tools::getValue('name'))
                {
                    $errors[]=$this->l('Name is required');
                }
                else
                    $employeePost->name=Tools::getValue('name');
                $employeePost->profile_employee = '';
                $languages= Language::getLanguages(false);
                foreach($languages as $language)
                {
                    $employeePost->description[$language['id_lang']]= Tools::getValue('description_'.$language['id_lang'],Tools::getValue('description_'.Configuration::get('PS_LANG_DEFAULT')));
                }
                $oldImage = false;
                $newImage = false;  
                $changedImages=array(); 
                if(isset($_FILES['avata']['tmp_name']) && isset($_FILES['avata']['name']) && $_FILES['avata']['name'])
                {
                    if(file_exists(dirname(__FILE__).'/views/img/avata/'.$_FILES['avata']['name']))
                    {
                        $file_name = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['avata']['name'];
                    } 
                    else
                       $file_name = $_FILES['avata']['name'];                
                    $type = Tools::strtolower(Tools::substr(strrchr($_FILES['avata']['name'], '.'), 1));
        			$imagesize = @getimagesize($_FILES['avata']['tmp_name']);
        			if (isset($_FILES['avata']) &&
        				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
        			)
        			{
        				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
        				if ($error = ImageManager::validateUpload($_FILES['avata']))
        					$errors[] = $error;
        				elseif (!$temp_name || !move_uploaded_file($_FILES['avata']['tmp_name'], $temp_name))
        					$errors[] = $this->l('Can not upload the file');
        				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/avata/'.$file_name, null, null, $type))
        					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
        				if (isset($temp_name))
        					@unlink($temp_name);
                        if($employeePost->avata)
                            $oldImage = dirname(__FILE__).'/views/img/avata/'.$employeePost->avata;
                        $employeePost->avata = $file_name;
                        $newImage = dirname(__FILE__).'/views/img/avata/'.$employeePost->avata;			
        			}
                    elseif(isset($_FILES['avata']) &&				
        				!empty($_FILES['avata']['tmp_name']) &&
        				!empty($imagesize) &&
        				in_array($type, array('jpg', 'gif', 'jpeg', 'png')
        			))
                        $errors[] = $this->l('Avatar is invalid'); 
                                  
                }
                if(!$errors)
                {
                    if($id_employee_post)
                    {
                        if(!$employeePost->update())
                            $errors[] = $this->displayError($this->l('The employee could not be updated.'));
                    }
                    else
                        if(!$employeePost->add())
                            $errors[] = $this->displayError($this->l('The employee could not be updated.'));
                            
                }
                if (count($errors))
                {
                    if($newImage && file_exists($newImage))
                        @unlink($newImage);
                    $this->errorMessage = $this->displayError($errors);  
                }
                elseif($oldImage)
                    @unlink($oldImage);
                if(isset($newImage) && $newImage && file_exists($newImage) && !$errors && isset($employeePost)){
                    $changedImages[] = array(
                        'name' => 'avata',
                        'url' => $this->_path.'views/img/avata/'.$employeePost->avata,
                        'delete_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_customer='.Tools::getValue('id_customer').'&delemployeeimage=true&control=customer',
                    );
                }  
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => $errors ? 'error' : 'success',
                            'message' => $errors ? $this->errorMessage :  $this->displaySuccessMessage($this->l('Author customer saved')),
                            'images' => isset($changedImages) && $changedImages ? $changedImages : array(),
                            'postUrl' => !$errors && Tools::isSubmit('saveBlogEmployee') && (int)Tools::getValue('id_customer') ? $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_customer='.(int)Tools::getValue('id_customer').'&control=customer' : 0,
                            'itemKey' => 'id_employee',
                            'itemId' => !$errors ? $id_customer:0,
                        )
                    ));
                }        
                if(!$errors)
                {
                    if (Tools::isSubmit('saveBlogEmployee') && Tools::isSubmit('id_customer'))
            			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_customer='.Tools::getValue('id_customer').'&control=customer');
               }
            }
        }
    }
    private function _postEmployee()
    {
        $errors=array();
        if(Tools::isSubmit('deleteAllPostEmployee') && $id_author=Tools::getValue('id_author'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_author' =>(int)$id_author,
            ));
            $posts= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post WHERE is_customer=0 AND added_by="'.(int)$id_author.'"');
            if($posts)
            {
                foreach($posts as $post)
                {
                    if($this->_deletePost($post['id_post']))
                    {   
                        $posts = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p, '._DB_PREFIX_.'ybc_blog_post_shop ps  WHERE p.id_post = ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" order by sort_order asc');
                        if($posts)
                        {
                            foreach($posts as $key=> $post)
                            {
                                $position=$key+1;
                                Db::getInstance()->execute('update '._DB_PREFIX_.'ybc_blog_post SET sort_order ="'.(int)$position.'" WHERE id_post='.(int)$post['id_post']);
                            }
                        }
                    }
                }
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=employees&list=true');
        }
        if(Tools::isSubmit('delemployeeimage'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)Tools::getValue('id_employee').' AND is_customer=0');
            Hook::exec('actionUpdateBlog', array(
                'id_author' =>(int)Tools::getValue('id_employee'),
            ));
            $employeePost = new Ybc_blog_post_employee_class(Tools::getValue('id_employee'));
            @unlink(dirname(__FILE__).'/../views/img/avata/'.$employeePost->avata);
            $employeePost->avata='';
            $employeePost->update();
            if(Tools::isSubmit('ajax'))
            {
                die(Tools::jsonEncode(
                    array(
                        'messageType' => 'success',
                        'message' => $this->displayConfirmation($this->l('Avatar image deleted')),
                    )
                ));
            }            
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post');
                 
        }
        if(Tools::isSubmit('change_enabled'))
        {
            $status=(int)Tools::getValue('change_enabled');
            $field = Tools::getValue('field');
            $id_employee = (int)Tools::getValue('id_employee');  
            Hook::exec('actionUpdateBlog', array(
                'id_author' =>(int)Tools::getValue('id_employee'),
            ));
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$id_employee.' AND is_customer=0');          
            if(($field == 'status' && $id_employee))
            {
                if($id_employee_post)
                {
                    $employeePost= new Ybc_blog_post_employee_class($id_employee_post);
                    $employeePost->status=$status;
                    $employeePost->update();
                }
                else
                {
                    $employeePost = new Ybc_blog_post_employee_class();
                    $employeePost->status=$status;
                    $employee = new Employee($id_employee);
                    $employeePost->id_employee = $id_employee;
                    $employeePost->name = $employee->firstname.' '.$employee->lastname;
                    $employeePost->add();
                } 
                if($status==1)
                    $title= $this->l('Click to suspend'); 
                else
                    $title= $this->l('Click to active');
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_employee,
                        'enabled' => Tools::getValue('change_enabled'),
                        'field' => $field,
                        'message' => $this->displaySuccessMessage($this->l('The status has been successfully updated')),
                        'messageType'=>'success',
                        'title'=>$title,
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=employees&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_employee='.$id_employee,
                    )));
                }  
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=employees&list=true');
            }
        }
        if($id_employee=Tools::getValue('id_employee'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$id_employee.' AND is_customer=0');
            if(Tools::isSubmit('saveBlogEmployee'))
            {
                Hook::exec('actionUpdateBlog', array(
                    'id_author' =>(int)Tools::getValue('id_employee'),
                ));
                if($id_employee_post)
                {
                    $employeePost= new Ybc_blog_post_employee_class($id_employee_post);
                }
                else
                    $employeePost = new Ybc_blog_post_employee_class();
                $employeePost->id_employee=$id_employee;
                $employeePost->is_customer=0;
                if(!Tools::getValue('name'))
                {
                    $errors[]=$this->l('Name is required');
                }
                else
                    $employeePost->name=Tools::getValue('name');
                $employeePost->profile_employee = implode(',',Tools::getValue('profile_employee'));
                $employeePost->status = (int)Tools::getValue('status');
                $languages= Language::getLanguages(false);
                foreach($languages as $language)
                {
                    $employeePost->description[$language['id_lang']]= Tools::getValue('description_'.$language['id_lang'],Tools::getValue('description_'.Configuration::get('PS_LANG_DEFAULT')));
                }
                $oldImage = false;
                $newImage = false;  
                $changedImages=array(); 
                if(isset($_FILES['avata']['tmp_name']) && isset($_FILES['avata']['name']) && $_FILES['avata']['name'])
                {
                    if(file_exists(dirname(__FILE__).'/views/img/avata/'.$_FILES['avata']['name']))
                    {
                        $file_name = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['avata']['name'];
                    } 
                    else
                       $file_name = $_FILES['avata']['name'];                
                    $type = Tools::strtolower(Tools::substr(strrchr($_FILES['avata']['name'], '.'), 1));
        			$imagesize = @getimagesize($_FILES['avata']['tmp_name']);
        			if (isset($_FILES['avata']) &&
        				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
        			)
        			{
        				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
        				if ($error = ImageManager::validateUpload($_FILES['avata']))
        					$errors[] = $error;
        				elseif (!$temp_name || !move_uploaded_file($_FILES['avata']['tmp_name'], $temp_name))
        					$errors[] = $this->l('Can not upload the file');
        				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/avata/'.$file_name, Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH',null,null,null,300), Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT',null,null,null,300), $type))
        					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
        				if (isset($temp_name))
        					@unlink($temp_name);
                        if($employeePost->avata)
                            $oldImage = dirname(__FILE__).'/views/img/avata/'.$employeePost->avata;
                        $employeePost->avata = $file_name;
                        $newImage = dirname(__FILE__).'/views/img/avata/'.$employeePost->avata;			
        			}
                    elseif(isset($_FILES['avata']) &&				
        				!empty($_FILES['avata']['tmp_name']) &&
        				!empty($imagesize) &&
        				in_array($type, array('jpg', 'gif', 'jpeg', 'png')
        			))
                        $errors[] = $this->l('Avatar is invalid'); 
                                  
                }
                if(!$errors)
                {
                    if($id_employee_post)
                    {
                        if(!$employeePost->update())
                            $errors[] = $this->displayError($this->l('The employee could not be updated.'));
                    }
                    else
                        if(!$employeePost->add())
                            $errors[] = $this->displayError($this->l('The employee could not be updated.'));
                }
                if (count($errors))
                {
                    if($newImage && file_exists($newImage))
                        @unlink($newImage);
                    $this->errorMessage = $this->displayError($errors);  
                }
                elseif($oldImage)
                    @unlink($oldImage);
                if(isset($newImage) && $newImage && file_exists($newImage) && !$errors && isset($employeePost)){
                    $changedImages[] = array(
                        'name' => 'avata',
                        'url' => $this->_path.'views/img/avata/'.$employeePost->avata,
                        'delete_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_employee='.Tools::getValue('id_employee').'&delemployeeimage=true&control=employees',
                    );
                }  
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => $errors ? 'error' : 'success',
                            'message' => $errors ? $this->errorMessage :  $this->displaySuccessMessage($this->l('Author administrators saved')),
                            'images' => isset($changedImages) && $changedImages ? $changedImages : array(),
                            'postUrl' => !$errors && Tools::isSubmit('saveBlogEmployee') && !(int)Tools::getValue('id_employee') ? $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_employee='.$id_employee.'&control=employees' : 0,
                            'itemKey' => 'id_employee',
                            'itemId' => !$errors ? $id_employee:0,
                        )
                    ));
                }        
                if(!$errors)
                {
                    if (Tools::isSubmit('saveBlogEmployee') && Tools::isSubmit('id_employee'))
            			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_employee='.Tools::getValue('id_employee').'&control=employees');
               }
            }
        }
    }
    private function _postPost()
    {
    $errors = array();
    $id_post = (int)Tools::getValue('id_post');
    if($id_post && !$this->itemExists('post','id_post',$id_post) && !Tools::isSubmit('list'))
        Tools::redirectAdmin($this->baseAdminPath);
    /**
     * Change status 
     */
     if(Tools::isSubmit('change_enabled'))
     {
        $status = (int)Tools::getValue('change_enabled') ?  1 : 0;
        $field = Tools::getValue('field');
        $id_post = (int)Tools::getValue('id_post');   
        Hook::exec('actionUpdateBlog', array(
            'id_post' =>(int)$id_post,
        ));         
        if(($field == 'enabled' || $field=='is_featured') && $id_post)
        {
            $post_class= new Ybc_blog_post_class($id_post);
            Hook::exec('actionUpdateBlog', array(
                'id_post' =>(int)$id_post,
            ));
            $this->changeStatus('post',$field,$id_post,$status);
            if(Configuration::get('YBC_BLOG_CUSTOMER_EMAIL_APPROVED_POST') && $field == 'enabled' && $status==1 && $post_class->is_customer)
            {
                $customer= new Customer($post_class->added_by);
                $template_customer_vars=array(
                    '{customer_name}' => $customer->firstname .' '.$customer->lastname,
                    '{post_title}' => $post_class->title[$this->context->language->id],
                    '{post_link}'=> $this->getLink('blog',array('id_post'=>$post_class->id)),
                    '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                    '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                );
                Mail::Send(
        			Context::getContext()->language->id,
        			'approved_blog_customer',
        			$this->l('Your post has been approved'),
        			$template_customer_vars,
			        $customer->email,
        			$customer->firstname .' '.$customer->lastname,
        			null,
        			null,
        			null,
        			null,
        			dirname(__FILE__).'/mails/'
                );
            }
            if($field=='enabled')
            {
                if($status==1)
                    $title=$this->l('Click to mark as draft');
                else
                    $title = $this->l('Click to mark as published');
            }
            else
            {
                if($status==1)
                    $title=$this->l('Click to unmark featured post');
                else
                    $title = $this->l('Click to mark as featured');
            }
            if(Tools::isSubmit('ajax'))
            {
                die(Tools::jsonEncode(array(
                    'listId' => $id_post,
                    'enabled' => $status,
                    'field' => $field,
                    'message' =>$field == 'enabled' ? $this->displaySuccessMessage($this->l('The status has been successfully updated')):$this->displaySuccessMessage($this->l('The featured post has been successfully updated')),
                    'messageType'=>'success',
                    'title' => $title,
                    'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_post='.$id_post,
                )));
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post&list=true');
        }
     }
     if(Tools::getValue('action')=='updatePostOrdering' && $pots=Tools::getValue('posts'))
     {
        $page = Tools::getValue('page',1);
        foreach($pots as $key=> $post)
        {
            Hook::exec('actionUpdateBlog', array(
                'id_post' =>(int)$post,
            )); 
            $position=  1+ $key + ($page-1)*20;
            if((int)Tools::getValue('id_category'))
            {
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_post_category SET position="'.(int)$position.'" WHERE id_post='.(int)$post.' AND id_category='.(int)Tools::getValue('id_category'));
            }
            else
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_post SET sort_order="'.(int)$position.'" WHERE id_post='.(int)$post);
        }
        die(
            Tools::jsonEncode(
                array(
                    'page'=>$page,
                )
            )
        );
    }
     
    /**
     * Delete image 
     */         
     if($id_post && $this->itemExists('post','id_post',$id_post) && (Tools::isSubmit('delpostimage') || Tools::isSubmit('delpostthumb')))
     {
        $post = new Ybc_blog_post_class($id_post);
        $imageUrl = dirname(__FILE__).'/views/img/post/'.$post->image;
        $thumbUrl = dirname(__FILE__).'/views/img/post/thumb/'.$post->thumb;
        $post->datetime_modified = date('Y-m-d H:i:s');
        $post->modified_by = (int)$this->context->employee->id;
        Hook::exec('actionUpdateBlog', array(
            'id_post' =>(int)$id_post,
        )); 
        if(Tools::isSubmit('delpostthumb'))
        {
            if(file_exists($thumbUrl) && $post->thumb)
            {
                @unlink($thumbUrl);  
                $post->thumb = '';              
                $post->update();    
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message' => $this->displayConfirmation($this->l('Thumbnail image deleted')),
                        )
                    ));
                }            
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.$id_post.'&control=post');
            }
            else
            {
                $errors[] = $this->l('Thumbnail image does not exist');
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'error',
                            'message' => $this->displayError($errors),
                        )
                    ));
                }  
                
            }
                 
        }
        elseif(Tools::isSubmit('delpostimage'))
        {
            if($post->image && file_exists($imageUrl))
            {
                @unlink($imageUrl);
                $post->image = '';                
                $post->update();  
                Hook::exec('actionUpdateBlog', array(
                    'id_post' =>(int)$id_post,
                ));
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message' => $this->displayConfirmation($this->l('Image deleted')),
                        )
                    ));
                }               
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.$id_post.'&control=post');                        
            }
            else
            {
                $errors[] = $this->l('Image does not exist'); 
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'error',
                            'message' => $this->displayError($errors),
                        )
                    ));
                }  
            }                      
        }
        else
            $errors[] = $this->l('Image does not exist');   
     }
    /**
     * Delete post 
     */ 
     if(Tools::isSubmit('del'))
     {            
        $id_post = (int)Tools::getValue('id_post');
        Hook::exec('actionUpdateBlog', array(
            'id_post' =>(int)$id_post,
        ));
        if(!$this->itemExists('post','id_post',$id_post))
            $errors[] = $this->l('Post does not exist');
        elseif($this->_deletePost($id_post))
        {   
            $posts = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p, '._DB_PREFIX_.'ybc_blog_post_shop ps  WHERE p.id_post=ps.id_post AND  ps.id_shop="'.(int)$this->context->shop->id.'" order by p.sort_order asc');
            if($posts)
            {
                foreach($posts as $key=> $post)
                {
                    $position=$key+1;
                    Db::getInstance()->execute('update '._DB_PREFIX_.'ybc_blog_post SET sort_order ="'.(int)$position.'" WHERE id_post='.(int)$post['id_post']);
                }
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post&list=true');
        }                
        else
            $errors[] = $this->l('Could not delete the post. Please try again');
     }                  
    /**
     * Save post 
     */
    if(Tools::isSubmit('savePost'))
    {            
        if($id_post && $this->itemExists('post','id_post',$id_post))
        {
            
            $post = new Ybc_blog_post_class($id_post);  
            $post->datetime_modified = date('Y-m-d H:i:s');
            $post->modified_by = (int)$this->context->employee->id;
            $post->is_customer= (int)Tools::getValue('is_customer');
            if($post->is_customer)
            {
                if(!Tools::getValue('customer_author'))
                    $errors[]=  $this->l('Community - Authors is required');
                else
                    $post->added_by = (int)Tools::getValue('customer_author');
            }
            else
            {
                if(!Tools::getValue('admin_author'))
                    $errors[]=  $this->l('Administrator - Author is required');
                else
                    $post->added_by = (int)Tools::getValue('admin_author');
            }    
            
        }
        else
        {
            $post = new Ybc_blog_post_class();
            $post->datetime_added = date('Y-m-d H:i:s');
            $post->datetime_modified = date('Y-m-d H:i:s');
            $post->modified_by = (int)$this->context->employee->id;
            $post->added_by = (int)$this->context->employee->id;
            $post->is_customer=0;
            $post->sort_order =1+ (int)Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_post_shop WHERE id_shop='.(int)$this->context->shop->id);
        }
        $post->products = trim(trim(Tools::getValue('inputAccessories','')),'-');
        $enabled = $post->enabled;
        if($post->id || !Tools::isSubmit('submitSaveAndPreview'))
            $post->enabled = Tools::getValue('enabled');
        else
            $post->enabled = -2;
        if($enabled!=$post->enabled && $post->enabled==1)
            $updatestatus=true;
        else
            $updatestatus=false;
        $post->is_featured = Tools::getValue('is_featured') ? 1 : 0;
        if($post->enabled==2)
        {
            if(Tools::getValue('datetime_active')=='')
                $errors[]=$this->l('Publish date is required');
            elseif(Tools::getValue('datetime_active')=='0000-00-00' || !Validate::isDate(Tools::getValue('datetime_active')))
                $errors[] = $this->l('Publish date is not valid');
            else
                $post->datetime_active= Tools::getValue('datetime_active');
        }
        $languages = Language::getLanguages(false);
        $post->click_number = (int)Tools::getValue('click_number');
        $post->likes = (int)Tools::getValue('likes');
        $tags = array();                     
        $categories = Tools::getValue('blog_categories');            
        if(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT'))=='')
            $errors[] = $this->l('You need to set blog post title');
        if(Tools::getValue('short_description_'.Configuration::get('PS_LANG_DEFAULT'))=='')
            $errors[] = $this->l('You need to set blog post short description');
        if(Tools::getValue('description_'.Configuration::get('PS_LANG_DEFAULT'))=='')
            $errors[] = $this->l('You need to set blog post content');            
        if(Tools::getValue('url_alias_'.Configuration::get('PS_LANG_DEFAULT'))=='')
            $errors[] = $this->l('Url alias is required');
        if(!$categories || !is_array($categories))
            $errors[] = $this->l('You need to choose at least 1 category'); 
        if(!Tools::getValue('main_category'))
            $errors[] = $this->l('Main category is required');
        elseif(!in_array(Tools::getValue('main_category'),$categories))
            $errors[] = $this->l('Main category is not valid');
        else    
            $post->id_category_default = (int)Tools::getValue('main_category');
        if(Tools::getValue('click_number')=='')
            $errors[] = $this->l('Views are required');
        elseif(!Validate::isInt(Tools::getValue('click_number')))
            $errors[] = $this->l('Views are not valid');
        if(Tools::getValue('likes')=='')
            $errors[] = $this->l('Likes are required');
        elseif(!Validate::isInt(Tools::getValue('likes')))
            $errors[] = $this->l('Likes are not valid');
        if(!$post->thumb && !(isset($_FILES['thumb']['tmp_name']) && isset($_FILES['thumb']['name']) && $_FILES['thumb']['name']))
            $errors[]= $this->l('Post thumbnail image is required');
        if(!$errors)
        {
            foreach ($languages as $language)
    		{			
    			$title = trim(Tools::getValue('title_'.$language['id_lang']));
                $title_default =trim(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT')));
                $meta_title = trim(Tools::getValue('meta_title_'.$language['id_lang']));
                $meta_title_default = trim(Tools::getValue('meta_title_'.Configuration::get('PS_LANG_DEFAULT')));
                $url_alias = trim(Tools::getValue('url_alias_'.$language['id_lang']));
                $url_alias_default= trim(Tools::getValue('url_alias_'.Configuration::get('PS_LANG_DEFAULT')));
                if($title && !Validate::isCleanHtml($title))
                    $errors[] = $this->l('Title in '.$language['name'].' is not valid');
                else
                    $post->title[$language['id_lang']] = $title ? $title:$title_default;
                if($meta_title && !Validate::isCleanHtml($meta_title))
                    $errors[] = $this->l('Meta title in '.$language['name'].' is not valid');
                else
                    $post->meta_title[$language['id_lang']] = $meta_title ? $meta_title: $meta_title_default;
                if($url_alias && str_replace(array('0','1','2','3','4','5','6','7','8','9'),'',Tools::substr($url_alias,0,1))=='')
                    $errors[] = $this->l('Post alias').' in '.$language['name'].' '.$this->l('cannot have number on the start position because it will cause error when you enable "Remove post ID" option');  
                elseif($url_alias && !Ybc_blog::checkIsLinkRewrite($url_alias))
                    $errors[] = $this->l('Url alias in '.$language['name'].' is not valid');
                elseif($url_alias && Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl,'._DB_PREFIX_.'ybc_blog_post_shop ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.url_alias ="'.pSQL($url_alias).'" AND ps.id_post!="'.(int)$post->id.'"'))
                    $errors[] = $this->l('Url alias in '.$language['name'].' is exists');
                else
                    $post->url_alias[$language['id_lang']]= $url_alias ? $url_alias:$url_alias_default;                    
               
                if(trim(Tools::getValue('meta_description_'.$language['id_lang'])) && !Validate::isCleanHtml(trim(Tools::getValue('meta_description_'.$language['id_lang'])), true))
                    $errors[] = $this->l('Meta description in '.$language['name'].' is not valid');
                else
                     $post->meta_description[$language['id_lang']] = trim(Tools::getValue('meta_description_'.$language['id_lang'])) != '' ? trim(Tools::getValue('meta_description_'.$language['id_lang'])) :  trim(Tools::getValue('meta_description_'.Configuration::get('PS_LANG_DEFAULT')));
                if(trim(Tools::getValue('meta_keywords_'.$language['id_lang'])) && !Validate::isTagsList(trim(Tools::getValue('meta_keywords_'.$language['id_lang'])), true))
                    $errors[] = $this->l('Meta keywords in '.$language['name'].' are not valid');
                else
                    $post->meta_keywords[$language['id_lang']] = trim(Tools::getValue('meta_keywords_'.$language['id_lang'])) != '' ? trim(Tools::getValue('meta_keywords_'.$language['id_lang'])) :  trim(Tools::getValue('meta_keywords_'.Configuration::get('PS_LANG_DEFAULT')));
                
                if(trim(Tools::getValue('short_description_'.$language['id_lang'])) && !Validate::isCleanHtml(trim(Tools::getValue('short_description_'.$language['id_lang'])), true))
                    $errors[] = $this->l('Short description in '.$language['name'].' is not valid');
                else
                    $post->short_description[$language['id_lang']] = trim(Tools::getValue('short_description_'.$language['id_lang'])) != '' ? trim(Tools::getValue('short_description_'.$language['id_lang'])) :  trim(Tools::getValue('short_description_'.Configuration::get('PS_LANG_DEFAULT')));
                if($post->short_description[$language['id_lang']] && !self::checkIframeHTML($post->short_description[$language['id_lang']]))
                    $errors[] =$this->l('Short description in '.$language['name'].' is not valid.').' '.$this->displayErrorIframe();
                if(trim(Tools::getValue('description_'.$language['id_lang'])) && !Validate::isCleanHtml(trim(Tools::getValue('description_'.$language['id_lang'])), true))
                    $errors[] = $this->l('Description in '.$language['name'].' is not valid');
                elseif(trim(Tools::getValue('description_'.$language['id_lang'])) && !self::checkIframeHTML(trim(Tools::getValue('description_'.$language['id_lang']))))
                    $errors[] =$this->l('Description in '.$language['name'].' is not valid.').' '.$this->displayErrorIframe();
                else
                    $post->description[$language['id_lang']] = trim(Tools::getValue('description_'.$language['id_lang'])) != '' ? trim(Tools::getValue('description_'.$language['id_lang'])) :  trim(Tools::getValue('description_'.Configuration::get('PS_LANG_DEFAULT')));
                if($post->products && !preg_match('/^[0-9]+([\-0-9])*$/', $post->products))
                {
                    $errors[] = $this->l('Products are not valid');
                }
                $tagStr = trim(Tools::getValue('tags_'.$language['id_lang']));
                
                if($tagStr && Validate::isTagsList($tagStr))
                    $tags[$language['id_lang']] = explode(',',$tagStr);
                elseif($tagStr && !Validate::isTagsList($tagStr))
                {
                    $tags[$language['id_lang']] = array();
                    $errors[] = $this->l('Tags in '.$language['name'].' are not valid');
                }
                else
                    $tags[$language['id_lang']] = array();                                                           
            }
        }          
        /**
         * Upload image 
         */  
        $oldImage = false;
        $newImage = false;   
        if(isset($_FILES['image']['tmp_name']) && isset($_FILES['image']['name']) && $_FILES['image']['name'])
        {
            $_FILES['image']['name'] = str_replace(' ','_',$_FILES['image']['name']);
            if(file_exists(dirname(__FILE__).'/views/img/post/'.$_FILES['image']['name']))
            {
                $_FILES['image']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['image']['name'];
            }                
            $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image']['name'], '.'), 1));
			$imagesize = @getimagesize($_FILES['image']['tmp_name']);
			if (isset($_FILES['image']) &&
				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
			)
			{
				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
				if ($error = ImageManager::validateUpload($_FILES['image']))
					$errors[] = $error;
				elseif (!$temp_name || !move_uploaded_file($_FILES['image']['tmp_name'], $temp_name))
					$errors[] = $this->l('Can not upload the file');
				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/post/'.$_FILES['image']['name'], Configuration::get('YBC_BLOG_IMAGE_BLOG_WIDTH',null,null,null,1920), Configuration::get('YBC_BLOG_IMAGE_BLOG_HEIGHT',null,null,null,750), $type))
					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
				if (isset($temp_name))
					@unlink($temp_name);
                if($post->image)
                    $oldImage = dirname(__FILE__).'/views/img/post/'.$post->image;
                $post->image = $_FILES['image']['name'];
                $newImage = dirname(__FILE__).'/views/img/post/'.$post->image;			
			}
            elseif(isset($_FILES['image']) &&				
				!empty($_FILES['image']['tmp_name']) &&
				!empty($imagesize) &&
				in_array($type, array('jpg', 'gif', 'jpeg', 'png')
			))
                $errors[] = $this->l('Image is invalid');                
        }
        
       
        /**
         * Upload thumbnail
         */  
        $oldThumb = false;
        $newThumb = false;   
        if(isset($_FILES['thumb']['tmp_name']) && isset($_FILES['thumb']['name']) && $_FILES['thumb']['name'])
        {
            $_FILES['thumb']['name'] = str_replace(' ','_',$_FILES['thumb']['name']);
            if(file_exists(dirname(__FILE__).'/views/img/post/thumb/'.$_FILES['thumb']['name']))
            {
                $_FILES['thumb']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['thumb']['name'];
            }                
            $type = Tools::strtolower(Tools::substr(strrchr($_FILES['thumb']['name'], '.'), 1));
			$thumbsize = @getimagesize($_FILES['thumb']['tmp_name']);
			if (isset($_FILES['thumb']) &&				
				!empty($_FILES['thumb']['tmp_name']) &&
				!empty($thumbsize) &&
				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
			)
			{
				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
				if ($error = ImageManager::validateUpload($_FILES['thumb']))
					$errors[] = $error;
				elseif (!$temp_name || !move_uploaded_file($_FILES['thumb']['tmp_name'], $temp_name))
					$errors[] = $this->l('Can not upload the file');
				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/post/thumb/'.$_FILES['thumb']['name'], Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH',null,null,null,260), Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT',null,null,null,180), $type))
					$errors[] = $this->displayError($this->l('An error occurred during the thumbnail upload process.'));
				if (isset($temp_name))
					@unlink($temp_name);
                if($post->thumb)
                    $oldThumb = dirname(__FILE__).'/views/img/post/thumb/'.$post->thumb;
                $post->thumb = $_FILES['thumb']['name'];
                $newThumb = dirname(__FILE__).'/views/img/post/thumb/'.$post->thumb;			
			}
            elseif(isset($_FILES['thumb']) &&
				!in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
			)
                $errors[] = $this->l('Thumbnail image is invalid');                
        }				
        
        /**
         * Save 
         */    
        $changedImages = array();
        if(!$errors)
        {
            if (!Tools::getValue('id_post'))
			{
				if (!$post->add())
                {
                    $errors[] = $this->displayError($this->l('The post could not be added.')); 
                    if($newImage && file_exists($newImage))
                        @unlink($newImage);
                    if($newThumb && file_exists($newThumb))
                        @unlink($newThumb);
                }    					
                else
                {
                    $id_post = $this->getMaxId('post','id_post');
                    $this->updateCategories($categories, $id_post);
                    $relatedCategories= Tools::getValue('related_categories');
                    $this->updateRelatedCategories($relatedCategories,$id_post);
                    $this->updateTags($id_post, $tags);  
                    Hook::exec('actionUpdateBlogImage', array(
                        'id_post' =>(int)$post->id,
                        'image' => $newImage ? $post->image :false,
                        'thumb' => $newThumb ? $post->thumb : false,
                    ));
                }
                                    
			}				
			elseif (!$post->update())
            {
                if($newImage && file_exists($newImage))
                    @unlink($newImage);
                if($newThumb && file_exists($newThumb))
                    @unlink($newThumb);
                $errors[] = $this->displayError($this->l('The post could not be updated.'));
            }    					
            else
            {
                if($oldImage && file_exists($oldImage))
                {
                    @unlink($oldImage);                        
                }
                if($oldThumb && file_exists($oldThumb))
                {
                    @unlink($oldThumb);                        
                }
                Hook::exec('actionUpdateBlogImage', array(
                    'id_post' =>(int)$post->id,
                    'image' => $newImage ? $post->image :false,
                    'thumb' => $newThumb ? $post->thumb : false,
                ));
                $this->updateCategories($categories, $id_post);   
                $relatedCategories= Tools::getValue('related_categories');
                $this->updateRelatedCategories($relatedCategories,$id_post);
                $this->updateTags($id_post, $tags);
                if(Configuration::get('YBC_BLOG_CUSTOMER_EMAIL_APPROVED_POST') && $updatestatus &&  $post->is_customer)
                {
                    $customer= new Customer($post->added_by);
                    $template_customer_vars=array(
                        '{customer_name}' => $customer->firstname .' '.$customer->lastname,
                        '{post_title}' => $post->title[$this->context->language->id],
                        '{post_link}'=> $this->getLink('blog',array('id_post'=>$post->id)),
                        '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                        '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                    );
                    Mail::Send(
            			Context::getContext()->language->id,
            			'approved_blog_customer',
            			$this->l('Your post has been approved'),
            			$template_customer_vars,
    			        $customer->email,
            			$customer->firstname .' '.$customer->lastname,
            			null,
            			null,
            			null,
            			null,
            			dirname(__FILE__).'/mails/'
                    );
                }
            }  
            Hook::exec('actionUpdateBlog', array(
                'id_post' =>(int)$post->id,
            ));                               
        }
     }
     if (count($errors))
     {
        if($newImage && file_exists($newImage))
            @unlink($newImage);
        if($newThumb && file_exists($newThumb))
            @unlink($newThumb);
        $this->errorMessage = $this->displayError($errors);  
     }
     if(isset($newThumb) && $newThumb && file_exists($newThumb) && !$errors && isset($post))
     {
        $changedImages[] = array(
            'name' => 'thumb',
            'url' => $this->_path.'views/img/post/thumb/'.$post->thumb,
            'delete_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.Tools::getValue('id_post').'&delpostthumb=true&control=post',
        );
     } 
     if(isset($newImage) && $newImage && file_exists($newImage) && !$errors && isset($post)){
        $changedImages[] = array(
            'name' => 'image',
            'url' => $this->_path.'views/img/post/'.$post->image,
            'delete_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.Tools::getValue('id_post').'&delpostimage=true&control=post',
        );
     }  
     if(Tools::isSubmit('ajax'))
     {
            $itemId=!$errors && Tools::isSubmit('savePost') && !(int)Tools::getValue('id_post') ? $this->getMaxId('post','id_post') : ((int)Tools::getValue('id_post') > 0 ? (int)Tools::getValue('id_post') : 0);
            $array = array(
                'messageType' => $errors ? 'error' : 'success',
                'message' => $errors ? $this->errorMessage : (isset($id_post) && $id_post ? $this->displaySuccessMessage($this->l('Post saved'),$this->l('View this post'),$this->getLink('blog',array('id_post'=>$id_post))) : $this->displayConfirmation($this->l('Post saved'))),
                'images' => isset($changedImages) && $changedImages ? $changedImages : array(),
                'postUrl' => !$errors && Tools::isSubmit('savePost') && !(int)Tools::getValue('id_post') ? $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.$this->getMaxId('post','id_post').'&control=post' : 0,
                'itemKey' => 'id_post',
                'itemId' => $itemId,
                'link_preview'=> Tools::isSubmit('submitSaveAndPreview') && !$errors  ? $this->getLink('blog',array('id_post'=>$post->id,'preview'=>1)):'',
            );
            if(!$errors)
                $array['form_author_post']= $this->getFormAuthorPost($itemId);
            die(Tools::jsonEncode(
                $array
            ));
     }        
     
     if(!$errors)
     {
        if (Tools::isSubmit('savePost') && Tools::isSubmit('id_post'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.Tools::getValue('id_post').'&control=post');
		 elseif (Tools::isSubmit('savePost'))
         {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_post='.$this->getMaxId('post','id_post').'&control=post');
         }
     }
}

public function _deletePost($id_post)
{
    if($this->itemExists('post','id_post',$id_post))
    {
        $post = new Ybc_blog_post_class($id_post);
        if($post->image && file_exists(dirname(__FILE__).'/views/img/post/'.$post->image))
        {
            @unlink(dirname(__FILE__).'/views/img/post/'.$post->image);
        }
        if($post->thumb && file_exists(dirname(__FILE__).'/views/img/post/thumb/'.$post->thumb))
        {
            @unlink(dirname(__FILE__).'/views/img/post/thumb/'.$post->thumb);
        }             
        if($post->delete())
        {
            $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_post=".(int)$id_post;
            Db::getInstance()->execute($req);
            $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_tag WHERE id_post=".(int)$id_post;
            Db::getInstance()->execute($req);
            $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_comment WHERE id_post=".(int)$id_post;
            Db::getInstance()->execute($req);
            $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_post_shop WHERE id_post=".(int)$id_post;
            Db::getInstance()->execute($req);
            return true;
        }
    }
    return false;        
}
private function _deleteComment($id_comment)
{
    if($this->itemExists('comment','id_comment',$id_comment))
    {
        $comment = new Ybc_blog_comment_class($id_comment);
        return $comment->delete();
    }
    return false; 
}
private function _deleteSlide($id_slide)
{
    if($this->itemExists('slide','id_slide',$id_slide))
    {
        $slide = new Ybc_blog_slide_class($id_slide);
        if($slide->image && file_exists(dirname(__FILE__).'/views/img/slide/'.$slide->image))
        {
            @unlink(dirname(__FILE__).'/views/img/slide/'.$slide->image);
        }            
        if($slide->delete())
        {
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_slide_shop WHERE id_slide='.(int)$id_slide);
            return true;
        }
    }
    return false;        
}
private function _deleteGallery($id_gallery)
{
    if($this->itemExists('gallery','id_gallery',$id_gallery))
    {
        $gallery = new Ybc_blog_gallery_class($id_gallery);
        if($gallery->image && file_exists(dirname(__FILE__).'/views/img/gallery/'.$gallery->image))
        {
            @unlink(dirname(__FILE__).'/views/img/gallery/'.$gallery->image);
        } 
        if($gallery->thumb && file_exists(dirname(__FILE__).'/views/img/gallery/thumb/'.$gallery->thumb))
        {
            @unlink(dirname(__FILE__).'/views/img/gallery/thumb/'.$gallery->thumb);
        }            
        if($gallery->delete())
        {
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_gallery_shop WHERE id_gallery='.(int)$id_gallery);
            return true;
        }
    }
    return false;        
}
public function updateCategories($categories, $id_post)
{
    $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_post = ".(int)$id_post .($categories? ' AND id_category NOT IN ('.implode(',',array_map('intval',$categories)).')':'');
    Db::getInstance()->execute($req);
    if($categories)
    {            
        foreach($categories as $cat)
        {
            if(!$this->checkPostCategory($id_post, (int)$cat))
            {
                $position = 1+ (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_category='.(int)$cat);
                $req = "INSERT INTO "._DB_PREFIX_."ybc_blog_post_category(id_post, id_category,position) VALUES(".(int)$id_post.", ".(int)$cat.",".(int)$position.")";
                Db::getInstance()->execute($req);   
            }                
        }
    }
}
public function updateRelatedCategories($categories, $id_post)
{
    $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_post_related_categories WHERE id_post = ".(int)$id_post .($categories? ' AND id_category NOT IN ('.implode(',',array_map('intval',$categories)).')':'');
    Db::getInstance()->execute($req);
    if($categories)
    {            
        foreach($categories as $cat)
        {
            if(!$this->checkPostRelatedCategory($id_post, (int)$cat))
            {
                $req = "INSERT INTO "._DB_PREFIX_."ybc_blog_post_related_categories(id_post, id_category) VALUES(".(int)$id_post.", ".(int)$cat.")";
                Db::getInstance()->execute($req);   
            }                
        }
    }
}
public function checkPostCategory($id_post, $id_category)
{
    $req = "SELECT * FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_post = ".(int)$id_post." AND id_category = ".(int)$id_category;
    return Db::getInstance()->getRow($req);
}
public function checkPostRelatedCategory($id_post, $id_category)
{
    $req = "SELECT * FROM "._DB_PREFIX_."ybc_blog_post_related_categories WHERE id_post = ".(int)$id_post." AND id_category = ".(int)$id_category;
    return Db::getInstance()->getRow($req);
}
public function getCategories($id_category=0)
{
    $req = "SELECT c.*, cl.*
            FROM "._DB_PREFIX_."ybc_blog_category c
            INNER JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category=cs.id_category AND cs.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category
            WHERE cl.id_lang = ".(int)$this->context->language->id.($id_category ? ' AND c.id_category<"'.(int)$id_category.'"':'');
    return Db::getInstance()->executeS($req);
}
public function getCategoriesWithFilter($filter = false, $sort = false, $start = false, $limit = false,$id_parent=0)
{          
    $req = "SELECT c.*, cl.*
            FROM "._DB_PREFIX_."ybc_blog_category c
            INNER JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category=cs.id_category AND cs.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category
            WHERE c.id_parent='".(int)$id_parent."' AND  cl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '')." 
            ORDER BY ".($sort ? $sort : '')." c.id_category desc " . ($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");      
    return Db::getInstance()->executeS($req);
}
public function getSlidesWithFilter($filter = false, $sort = false, $start = false, $limit = false)
{          
    $req = "SELECT s.*, sl.caption, sl.url
            FROM "._DB_PREFIX_."ybc_blog_slide s
            INNER JOIN "._DB_PREFIX_."ybc_blog_slide_shop ss ON (s.id_slide=ss.id_slide AND ss.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_slide_lang sl ON s.id_slide = sl.id_slide
            WHERE sl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '')." 
            ORDER BY ".($sort ? $sort : '')." s.id_slide ASC " . ($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");      
    return Db::getInstance()->executeS($req);
}
public function countSlidesWithFilter($filter = false)
{          
    $req = "SELECT COUNT(s.id_slide) as total_slides
            FROM "._DB_PREFIX_."ybc_blog_slide s
            INNER JOIN "._DB_PREFIX_."ybc_blog_slide_shop ss ON (s.id_slide=ss.id_slide AND ss.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_slide_lang sl ON s.id_slide = sl.id_slide
            WHERE sl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '');
    $row = Db::getInstance()->getRow($req);
    return isset($row['total_slides']) ? (int)$row['total_slides'] : 0;
}
public function getGalleriesWithFilter($filter = false, $sort = false, $start = false, $limit = false)
{          
    $req = "SELECT g.*, gl.title, gl.description
            FROM "._DB_PREFIX_."ybc_blog_gallery g
            INNER JOIN "._DB_PREFIX_."ybc_blog_gallery_shop gs ON (g.id_gallery=gs.id_gallery AND gs.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_gallery_lang gl ON g.id_gallery = gl.id_gallery
            WHERE gl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '')." 
            ORDER BY ".($sort ? $sort : '')." g.id_gallery ASC " . ($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");      
    
    return Db::getInstance()->executeS($req);
}
public function countGalleriesWithFilter($filter = false)
{    
    $req = "SELECT COUNT(g.id_gallery) as total_galleries
            FROM "._DB_PREFIX_."ybc_blog_gallery g
            INNER JOIN "._DB_PREFIX_."ybc_blog_gallery_shop gs ON (g.id_gallery=gs.id_gallery AND gs.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_gallery_lang gl ON g.id_gallery = gl.id_gallery
            WHERE gl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '');
    $row = Db::getInstance()->getRow($req);
    return isset($row['total_galleries']) ? (int)$row['total_galleries'] : 0;
}
public function getCategoryById($id_category, $id_lang = false)
{
    if(!$id_lang)
        $id_lang = (int)$this->context->language->id;
    $req = "SELECT c.*, cl.*
            FROM "._DB_PREFIX_."ybc_blog_category c
            INNER JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category =cs.id_category AND cs.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category
            WHERE cl.id_lang = ".(int)$id_lang." AND c.id_category=".(int)$id_category;
    return Db::getInstance()->getRow($req);
}
public function countCategoriesWithFilter($filter,$id_parent=0)
{
    $req = "SELECT c.*, cl.*
            FROM "._DB_PREFIX_."ybc_blog_category c
            INNER JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category=cs.id_category AND cs.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category
            WHERE c.id_parent='".(int)$id_parent."' AND  cl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '');     
    $res = Db::getInstance()->executeS($req);
    return $res ? count($res) : 0;
}
public function getSelectedCategories($id_post=0)
{
    if(Tools::isSubmit('submitPostStay'))
    {
        $categories = Tools::getValue('blog_categories');
        if(is_array($categories))
            return $categories;
        else
            return array();
    }            
    $categories = array();
    if($id_post)
    {
        $req = "SELECT id_category FROM "._DB_PREFIX_."ybc_blog_post_category
                WHERE id_post = ".(int)$id_post;            
        $rows = Db::getInstance()->executeS($req);
        if($rows)
            foreach($rows as $row)
                $categories[] = $row['id_category'];
    }
    else
        $categories = Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME') ? explode(',',Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME')):array();
    return $categories;        
}
public function getSelectedProducts($id_post)
{
    $products = array();
    if(Tools::isSubmit('inputAccessories') && trim(trim(Tools::getValue('inputAccessories')),','))
    {
        $products = explode('-', trim(trim(Tools::getValue('inputAccessories')),'-'));
    }
    elseif($id_post)
    {
        $req = "SELECT products FROM "._DB_PREFIX_."ybc_blog_post
                WHERE id_post = ".(int)$id_post;            
        $row = Db::getInstance()->getRow($req);
        if($row)
        {
            $products = explode('-', trim($row['products'],'-'));                
        }          
    }        
    if($products)
    {
        foreach($products as $key => &$product)
        {
            $product = (int)$product;
        }
        unset($key);
        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`
			FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').'
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
            WHERE pl.`id_lang` = '.(int)$this->context->language->id.' AND p.`id_product` IN ('.implode(',',array_map('intval',$products)).') GROUP BY p.id_product';
        $product_list = Db::getInstance()->executeS($sql);
        if($product_list)
        {
            if(version_compare(_PS_VERSION_, '1.7', '>='))
                $type_image= ImageType::getFormattedName('small');
            else
                $type_image= ImageType::getFormatedName('small');

            foreach($product_list as &$product)
            {
                $id_image = Db::getInstance()->getValue("SELECT id_image FROM "._DB_PREFIX_."image WHERE id_product=".(int)$product['id_product'].' AND cover=1');
                $product['link_image'] =str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($product['link_rewrite'], $id_image, $type_image));
            }
        }
        return $product_list;          
    }        
    return false;
}
public function getTagsByIdPost($id_post, $id_lang = false)
{
    if(!$id_lang)
        $id_lang = $this->context->language->id;
    $req = "SELECT * FROM "._DB_PREFIX_."ybc_blog_tag
            WHERE id_lang = ".(int)$id_lang." AND id_post = ".(int)$id_post."
            ORDER by tag asc";
    $tags = Db::getInstance()->executeS($req);
    if($tags)
    {
        foreach($tags as &$tag)
        {
            $tag['link'] = $this->getLink('blog',array('tag' => urlencode($tag['tag'])));
        }
    }
    return $tags;
}
public function increasTagViews($tag)
{
    $sql = "UPDATE "._DB_PREFIX_."ybc_blog_tag
            SET click_number = click_number + 1
            WHERE tag = '".pSQL($tag)."'";
    return Db::getInstance()->execute($sql);
}
public function getTags($limit = 20, $id_lang = false)
{
    if(!$id_lang)
        $id_lang = $this->context->language->id;
    $req = "SELECT DISTINCT ROUND(SUM(t.click_number)/COUNT(t.id_tag)) as viewed, t.tag FROM "._DB_PREFIX_."ybc_blog_tag t
            WHERE id_lang = ".(int)$id_lang."
            GROUP BY  t.tag
            ORDER BY viewed desc, tag asc
            LIMIT 0,".(int)$limit;
    $tags = Db::getInstance()->executeS($req);        
    if($tags)
    {
        foreach($tags as &$tag)
        {
            $tag['link'] = $this->getLink('blog',array('tag' => urlencode($tag['tag'])));
        }
    }
    return $tags;
}
public function updateTags($id_post, $tags)
{
  if($id_post && $tags && is_array($tags))
  {
       foreach($tags as $id_lang => $tagList)
       {
        if($tagList && is_array($tagList))
        {
             $str = '';
             foreach($tagList as $tag)
             {
                  $tag = Tools::strtolower($tag);
                  if($tag && !$this->checkTagLang($id_post, $id_lang, $tag))
                  {
                   $req = "INSERT INTO "._DB_PREFIX_."ybc_blog_tag(id_tag,id_post, id_lang, tag, click_number)
                                        VALUES(null, ".(int)$id_post.", ".(int)$id_lang.", '".pSQL($tag)."',0)";
                   Db::getInstance()->execute($req);
                  }
                  $str .= $tag.',';
             }
             $str = explode(',',Tools::rtrimString($str, ','));
             $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_tag 
                                    WHERE id_post = ".(int)$id_post." AND id_lang = ".(int)$id_lang." AND tag NOT IN ('".implode("','",array_map('pSQL',$str))."')";
             Db::getInstance()->execute($req);
        }
        else
        {
             $req = "DELETE FROM "._DB_PREFIX_."ybc_blog_tag 
                                    WHERE id_post = ".(int)$id_post." AND id_lang = ".(int)$id_lang;
             Db::getInstance()->execute($req);
        }
       }
  }
}
public function checkTagLang($id_post, $id_lang, $tag)
{       
    $req = "SELECT * FROM "._DB_PREFIX_."ybc_blog_tag
            WHERE id_lang = ".(int)$id_lang." AND id_post = ".(int)$id_post." AND tag = '".pSQL($tag)."'";
    return Db::getInstance()->getRow($req);
}
public function getTagStr($id_post, $id_lang)
{
    if(!$id_post || !$id_lang)
        return '';
    $req = "SELECT tag FROM "._DB_PREFIX_."ybc_blog_tag WHERE id_post = ".(int)$id_post." AND id_lang = ".(int)$id_lang;
    $tags = Db::getInstance()->executeS($req);
    $tagStr = '';
    if($tags)
    {
        foreach($tags as $tag)
            $tagStr .= $tag['tag'].',';
    }
    return trim($tagStr,',');        
}
/**
 * Sidebar 
 */
 public function renderSidebar()
 {
    $this->baseAdminPath = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    $list = array(
                array(
                    'label' => $this->l('Posts'),
                    'url' => $this->baseAdminPath.'&control=post&list=true',
                    'id' => 'ybc_tab_post',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Blog posts and blog categories'),
                    'controller'=>'AdminYbcBlogPost',
                    'icon' => 'icon-AdminPriceRule'
                ),
                array(
                    'label' => $this->l('Categories'),
                    'url' => $this->baseAdminPath.'&control=category&list=true',
                    'id' => 'ybc_tab_category',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Blog posts and blog categories'),
                    'controller'=>'AdminYbcBlogCategory',
                    'icon' => 'icon-AdminCatalog'
                ),
                array(
                    'label' => $this->l('Comments'),
                    'url' => $this->baseAdminPath.'&control=comment&list=true',
                    'id' => 'ybc_tab_comment',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Blog comments'),
                    'controller'=>'AdminYbcBlogComment',
                    'icon' => 'icon-comments',
                    'total_result' => $this->countCommentsWithFilter(' AND bc.viewed=0',false),
                ),
                array(
                    'label' => $this->l('Polls'),
                    'url' => $this->baseAdminPath.'&control=polls&list=true',
                    'id' => 'ybc_tab_polls',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Blog comments'),
                    'controller'=>'AdminYbcBlogPolls',
                    'icon' => 'icon-polls',
                ),
                array(
                    'label' => $this->l('Slider'),
                    'url' => $this->baseAdminPath.'&control=slide&list=true',
                    'id' => 'ybc_tab_slide',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Blog slider'),
                    'icon' => 'icon-AdminParentModules',
                    'controller'=>'AdminYbcBlogSlider',
                ),
                array(
                    'label' => $this->l('Photo gallery'),
                    'url' => $this->baseAdminPath.'&control=gallery&list=true',
                    'id' => 'ybc_tab_gallery',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Blog gallery'),
                    'icon' => 'icon-AdminDashboard',
                    'controller'=>'AdminYbcBlogGallery',
                ),
                array(
                    'label' => $this->l('Seo'),
                    'url' => $this->baseAdminPath.'&control=seo&list=true',
                    'id' => 'ybc_tab_seo',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Seo'),
                    'icon' => 'icon-seo',
                    'controller'=>'AdminYbcBlogSeo',
                ),
                array(
                    'label' => $this->l('Google sitemap'),
                    'url' => $this->baseAdminPath.'&control=sitemap&list=true',
                    'id' => 'ybc_tab_sitemap',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Sitemap'),
                    'icon' => 'icon-sitemap',
                    'controller'=>'AdminYbcBlogSitemap',
                ),
                array(
                    'label' => $this->l('RSS feed'),
                    'url' => $this->baseAdminPath.'&control=rss&list=true',
                    'id' => 'ybc_tab_rss',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Rss feed'),
                    'icon' => 'icon-rss',
                    'controller'=>'AdminYbcBlogRSS',
                ),
                array(
                    'label' => $this->l('Socials'),
                    'url' => $this->baseAdminPath.'&control=socials&list=true',
                    'id' => 'ybc_tab_socials',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Socials'),
                    'icon' => 'icon-socials',
                    'controller'=>'AdminYbcBlogSocials',
                ),
                array(
                    'label' => $this->l('Email'),
                    'url' => $this->baseAdminPath.'&control=email&list=true',
                    'id' => 'ybc_tab_email',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Email'),
                    'icon' => 'icon-email',
                    'controller'=>'AdminYbcBlogEmail',
                ),
                array(
                    'label'=> $this->l('Image'),
                    'id'=>'ybc_tab_image',
                    'icon'=>'icon-image',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Image'),
                    'url' => $this->baseAdminPath.'&control=image&list=true',
                    'controller'=>'AdminYbcBlogImage',
                ),
                array(
                    'label' => $this->l('Sidebar'),
                    'url' => $this->baseAdminPath.'&control=sidebar&list=true',
                    'id' => 'ybc_tab_sidebar',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Sidebar'),
                    'icon' => 'icon-sidebar',
                    'controller'=>'AdminYbcBlogSidebar',
                ),
                array(
                    'label' => $this->l('Home page'),
                    'url' => $this->baseAdminPath.'&control=homepage&list=true',
                    'id' => 'ybc_tab_homepage',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Home page'),
                    'icon' => 'icon-homepage',
                    'controller'=>'AdminYbcBlogHomepage',
                ),
                array(
                    'label' => $this->l('Post listing pages'),
                    'url' => $this->baseAdminPath.'&control=postlistpage&list=true',
                    'id' => 'ybc_tab_postlistpage',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Post listing pages'),
                    'icon' => 'icon-postlistpage',
                    'controller'=>'AdminYbcBlogPostListPage',
                ),
                array(
                    'label' => $this->l('Post details page'),
                    'url' => $this->baseAdminPath.'&control=postpage&list=true',
                    'id' => 'ybc_tab_postpage',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Post detail page'),
                    'icon' => 'icon-postpage',
                    'controller'=>'AdminYbcBlogPostpage',
                ),
                array(
                    'label' => $this->l('Product categories page'),
                    'url' => $this->baseAdminPath.'&control=categorypage&list=true',
                    'id' => 'ybc_tab_categorypage',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Category page'),
                    'icon' => 'icon-categorypage',
                    'controller'=>'AdminYbcBlogCategorypage',
                ),
                array(
                    'label' => $this->l('Product details page'),
                    'url' => $this->baseAdminPath.'&control=productpage&list=true',
                    'id' => 'ybc_tab_productpage',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Product detail page'),
                    'icon' => 'icon-productpage',
                    'controller'=>'AdminYbcBlogProductpage',
                ),
                array(
                    'label'=> $this->l('Authors'),
                    'id'=>'ybc_tab_employees',
                    'icon'=>'icon-user',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Authors'),
                    'url' => $this->baseAdminPath.'&control=employees&list=true',
                    'controller'=>'AdminYbcBlogAuthor',
                ),
                array(
                    'label' => $this->l('Import/Export'),
                    'url' => $this->baseAdminPath.'&control=export',
                    'id' => 'ybc_tab_export',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Import/Export'),
                    'icon' => 'icon-exchange',
                    'controller'=>'AdminYbcBlogImport',
                ),
                array(
                    'label' => $this->l('Statistics'),
                    'url' => $this->context->link->getAdminLink('AdminYbcBlogStatistics'),
                    'id' => 'ybc_tab_statistics',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Statistics'),
                    'icon' => 'icon-chart',
                    'controller'=>'AdminYbcBlogStatistics',
                ),
                array(
                    'label' => $this->l('Global settings'),
                    'url' => $this->baseAdminPath.'&control=config',
                    'id' => 'ybc_tab_config',
                    'hasAccess' => $this->checkProfileEmployee($this->context->employee->id,'Global settings'),
                    'icon' => 'icon-AdminAdmin',
                    'controller'=>'AdminYbcBlogSetting',
                ),
            );
    $intro = true;
    $localIps = array(
        '127.0.0.1',
        '::1'
    );
    $baseURL = Tools::strtolower(self::getBaseLink());
    if(!Tools::isSubmit('intro') && (in_array(Tools::getRemoteAddr(), $localIps) || preg_match('/^.*(localhost|demo|dev|test|:\d+).*$/i', $baseURL)))
        $intro = false;
    if($intro)
        $list[] = array(
                    'label' => $this->l('Other modules'),
                    'subtitle' => $this->l('Made by ETS-Soft'),
                    'url' => $this->baseAdminPath.'&othermodules=1',
                    'id' => 'ybc_tab_other_modules',
                    'hasAccess' => true,
                );
    $this->context->smarty->assign(
		array(
			'link' => $this->context->link,
			'list' => $list,
            'admin_path' => $this->baseAdminPath,
            'active' => 'ybc_tab_'.(trim(Tools::getValue('control')) ? trim(Tools::getValue('control')) : (Tools::getValue('controller')=='AdminYbcBlogStatistics' ? 'statistics'  :'post'))			
		)
	);
    return $this->display(__FILE__, 'sidebar.tpl');
 }
/**
 * Functions 
 */
public function itemExists($tbl, $primaryKey, $id)
{
	$req = 'SELECT `'.pSQL($primaryKey).'`
			FROM `'._DB_PREFIX_.'ybc_blog_'.pSQL($tbl).'` tbl
			WHERE tbl.`'.pSQL($primaryKey).'` = '.(int)$id;
	$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);        
	return ($row);
}
public function getMaxId($tbl, $primaryKey)
{
    $req = 'SELECT max(`'.pSQL($primaryKey).'`) as maxid
			FROM `'._DB_PREFIX_.'ybc_blog_'.pSQL($tbl).'` tbl';				
	$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
    return isset($row['maxid']) ? (int)$row['maxid'] : 0;
}
public function getMaxOrder($tbl)
{
    $req = 'SELECT max(`sort_order`) as maxorder
			FROM `'._DB_PREFIX_.'ybc_blog_'.pSQL($tbl).'` tbl';				
	$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
    return isset($row['maxorder']) ? (int)$row['maxorder'] : 0;
}
public function getFieldsCustomerValues()
{
    $fields=array();
    if($id_employee_post=(int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)Tools::getValue('id_customer').' AND is_customer=1'))
    {
        $blogEmployee = new Ybc_blog_post_employee_class($id_employee_post);
        $fields['status'] =(int)$blogEmployee->status;
    }
    else
    {
        $blogEmployee = new Ybc_blog_post_employee_class();
        $fields['status'] = 1; 
    }
        
    $customer = new Customer(Tools::getValue('id_customer'));
    $fields['id_customer'] = $customer->id;
    $fields['name'] =$blogEmployee->name?$blogEmployee->name:$customer->firstname.' '.$customer->lastname;
    $languages= Language::getLanguages(false);
    foreach($languages as $language)
    {
        $fields['description'][$language['id_lang']] =$blogEmployee->description[$language['id_lang']];
    }
    $fields['control'] =trim(Tools::getValue('control')) ? trim(Tools::getValue('control')) : '';  
    return $fields;
}
public function getFieldsEmployeeValues()
{
    $fields=array();
    if($id_employee_post=(int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)Tools::getValue('id_employee').' AND is_customer=0'))
    {
        $blogEmployee = new Ybc_blog_post_employee_class($id_employee_post);
        $fields['status'] = $blogEmployee->status;
    }
    else
    {
        $blogEmployee = new Ybc_blog_post_employee_class();
        $fields['status'] = 1;
    }
    $employee = new Employee(Tools::getValue('id_employee'));
    $fields['id_employee'] = $employee->id;
    $fields['name'] =$blogEmployee->name?$blogEmployee->name:$employee->firstname.' '.$employee->lastname;
    $languages= Language::getLanguages(false);
    $fields['profile_employee'] =$blogEmployee->profile_employee;
    foreach($languages as $language)
    {
        $fields['description'][$language['id_lang']] =$blogEmployee->description[$language['id_lang']];
    }
    $fields['control'] =trim(Tools::getValue('control')) ? trim(Tools::getValue('control')) : '';
    
    return $fields;
}
public function getFieldsValues($formFields, $primaryKey, $objClass, $saveBtnName)
{
	$fields = array();        
	if (Tools::isSubmit($primaryKey))
	{
		$obj = new $objClass((int)Tools::getValue($primaryKey));
		$fields[$primaryKey] = (int)Tools::getValue($primaryKey, $obj->$primaryKey);            
	}
	else
    {
        $obj = new $objClass();
    }
    foreach($formFields as $field)
    {
        if(!isset($field['primary_key']) && !isset($field['multi_lang']) && !isset($field['connection']))
        {
            $fieldName = $field['name'];
            $fields[$field['name']] = trim(Tools::getValue($field['name'], $obj->$fieldName));       
        }
                 
    }   
    $languages = Language::getLanguages(false);
    
    /**
     *  Default
     */
    
    if(!Tools::isSubmit($saveBtnName) && !Tools::isSubmit($primaryKey))
    {
        foreach($formFields as $field)
        {
            if(isset($field['default']) && !isset($field['multi_lang']))
            {
                if(isset($field['default_submit']))
                    $fields[$field['name']] = (int)Tools::getValue($field['name']) ? (int)Tools::getValue($field['name']) : $field['default'];
                else
                    $fields[$field['name']] = $field['default'];
            }
        }
    }
    
    /**
     * Multiple language 
     */
	foreach ($languages as $lang)
	{
	    foreach($formFields as $field)
        {
            if(!Tools::isSubmit($saveBtnName) && !Tools::isSubmit($primaryKey))
            {
                if(isset($field['multi_lang']))
                {
                    if(isset($field['default']))
                        $fields[$field['name']][$lang['id_lang']] = $field['default'];
                    else
                        $fields[$field['name']][$lang['id_lang']] = '';
                }
            }
            elseif(Tools::isSubmit($saveBtnName))
            {
                if(isset($field['multi_lang']))
                    $fields[$field['name']][$lang['id_lang']] = Tools::getValue($field['name'].'_'.(int)$lang['id_lang']);  
                
            }
            else{                    
                if(isset($field['multi_lang']))
                {
                    $fieldName = $field['name'];
                    $field_langs = $obj->$fieldName;                        
                    $fields[$field['name']][$lang['id_lang']] = isset($field_langs[$lang['id_lang']]) ? $field_langs[$lang['id_lang']]:'';
                }                        
            }                
        }
	}
    $fields['control'] = trim(Tools::getValue('control')) ? trim(Tools::getValue('control')) : '';
    
    /**
     * Tags 
     */
     if($primaryKey=='id_post')
     {
        foreach ($languages as $lang)
        {
            if(Tools::isSubmit('savePost'))
            {                    
                $fields['tags'][$lang['id_lang']] = trim(trim(Tools::getValue('tags_'.(int)$lang['id_lang'])),',') ? trim(trim(Tools::getValue('tags_'.(int)$lang['id_lang'])),',') : '';
            }
            else
                $fields['tags'][$lang['id_lang']] = $this->getTagStr((int)Tools::getValue('id_post'), (int)$lang['id_lang']);                
            
        }            
     }
     return $fields;
}
public function renderList($listData)
{      
    if(isset($listData['fields_list']) && $listData['fields_list'])
    {
        foreach($listData['fields_list'] as $key => &$val)
        {
            if(isset($val['filter']) && $val['filter'] && $val['type']=='int')
            {
                $val['active']['max'] =  trim(Tools::getValue($key.'_max'));   
                $val['active']['min'] =  trim(Tools::getValue($key.'_min'));   
            }  
            elseif($listData['name']=='ybc_blog_employee' && Tools::getValue('control')!='employees')
            {
                $val['active']='';
            }
            elseif($listData['name']=='ybc_blog_customer' && Tools::getValue('control')!='customer')
            {
                $val['active']='';
            }
            elseif($key=='has_post' && !Tools::isSubmit('has_post'))
                $val['active']=1;
            else               
                $val['active'] = trim(Tools::getValue($key));
        }
    }    
    $this->context->smarty->assign($listData);
    return $this->display(__FILE__, 'list_helper.tpl');
}
public function renderListByCustomer($listData)
{
    if(isset($listData['fields_list']) && $listData['fields_list'])
    {
        foreach($listData['fields_list'] as $key => &$val)
        {
            $val['active'] = trim(Tools::getValue($key));
        }
    }    
    $this->context->smarty->assign($listData);
    return $this->display(__FILE__, 'list_helper_customer.tpl');
}
public function renderListPostByCustomer($listData)
{
    if(isset($listData['fields_list']) && $listData['fields_list'])
    {
        foreach($listData['fields_list'] as $key => &$val)
        {
            $val['active'] = trim(Tools::getValue($key));
        }
    }    
    $this->context->smarty->assign($listData);
    return $this->display(__FILE__, 'list_post_by_customer.tpl');
}
public function getUrlExtra($field_list)
{
    $params = '';
    if(trim(Tools::getValue('sort')) && isset($field_list[trim(Tools::getValue('sort'))]))
    {
        $params .= '&sort='.trim(Tools::getValue('sort')).'&sort_type='.(trim(Tools::getValue('sort_type')) =='asc' ? 'asc' : 'desc');
    }
    if($field_list)
    {
        foreach($field_list as $key => $val)
        {
            if(Tools::getValue($key)!='')
            {
                $params .= '&'.$key.'='.urlencode(Tools::getValue($key));
            }
        }
        unset($val);
    }
    return $params;
}
public function getUrlExtraFrontEnd($field_list,$submit)
{
    $params = '';
    if(trim(Tools::getValue('sort')) && isset($field_list[trim(Tools::getValue('sort'))]))
    {
        $params .= '&sort='.trim(Tools::getValue('sort')).'&sort_type='.(trim(Tools::getValue('sort_type')) =='asc' ? 'asc' : 'desc');
    }
    if($field_list)
    {
        $ok=false;
        foreach($field_list as $key => $val)
        {
            if(Tools::getValue($key)!='')
            {
                $params .= '&'.$key.'='.urlencode(Tools::getValue($key));
                $ok=true;
            }
        }
        if($ok)
            $params .='&'.$submit.'=1';
        unset($val);
    }
    return $params;
}
public function getFilterParams($field_list)
{
    $params = '';        
    if($field_list)
    {
        foreach($field_list as $key => $val)
        {
            if(Tools::getValue($key)!='')
            {
                $params .= '&'.$key.'='.urlencode(Tools::getValue($key));
            }
        }
        unset($val);
    }
    return $params;
}
public function getFilterParamsFontEnd($field_list,$submit)
{
    $params = '';        
    if($field_list)
    {
        foreach($field_list as $key => $val)
        {
            if(Tools::getValue($key)!='')
            {
                $params .= '&'.$key.'='.urlencode(Tools::getValue($key));
            }
        }
        unset($val);
    }
    if($params)
        $params .='&'.$submit.'=1';
    return $params;
}
public function getEmployeesFilter($filter = false, $sort = false, $start = false, $limit = false,$having='')
{
    $sql ="SELECT e.*,CONCAT(e.firstname, ' ', e.lastname) as employee, be.name,bel.description,be.profile_employee,be.avata,pl.name as profile_name,IFNULL(be.status,1) as status,count(bp.id_post) as total_post FROM "._DB_PREFIX_."employee e
        LEFT JOIN "._DB_PREFIX_."ybc_blog_employee be ON (e.id_employee = be.id_employee AND be.is_customer=0)
        LEFT JOIN "._DB_PREFIX_."ybc_blog_employee_lang bel ON (bel.id_employee_post=be.id_employee_post AND bel.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN "._DB_PREFIX_."profile p ON (e.id_profile=p.id_profile)
        LEFT JOIN "._DB_PREFIX_."profile_lang pl ON (p.id_profile=pl.id_profile AND pl.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN "._DB_PREFIX_."ybc_blog_post bp ON (bp.added_by=e.id_employee AND bp.is_customer=0)
        LEFT JOIN "._DB_PREFIX_."ybc_blog_post_shop bps ON (bps.id_post=bp.id_post AND bps.id_shop='".(int)$this->context->shop->id."')
        WHERE 1 ".($filter ? $filter: '')."
        GROUP BY e.id_employee
        HAVING (1 ".($having ? $having :' ')." )
        ".($sort ?' ORDER BY '.$sort :'').($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");
    return Db::getInstance()->executeS($sql);
}
public function getCustomersFilter($filter = false, $sort = false, $start = false, $limit = false,$having='')
{
    $group_author= explode(',',Configuration::get('YBC_BLOG_GROUP_CUSTOMER_AUTHOR'));
    if($group_author)
    {
        $sql ="SELECT c.*,CONCAT(c.firstname, ' ', c.lastname) as customer, be.name,bel.description,be.profile_employee,be.avata,IFNULL(be.status,1) as status,count(bps.id_post) as total_post FROM "._DB_PREFIX_."customer c
            INNER JOIN "._DB_PREFIX_."customer_group cg ON (cg.id_customer=c.id_customer)
            INNER JOIN "._DB_PREFIX_."group g ON (cg.id_group=g.id_group)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee be ON (c.id_customer = be.id_employee AND be.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee_lang bel ON (bel.id_employee_post=be.id_employee_post AND bel.id_lang='".(int)$this->context->language->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post bp ON (bp.added_by=c.id_customer AND bp.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_shop bps ON (bp.id_post=bps.id_post AND bps.id_shop='".(int)$this->context->shop->id."')
            WHERE c.id_shop='".(int)$this->context->shop->id."' AND  g.id_group in (".implode(',',array_map('intval',$group_author)).") ".($filter ? $filter: '')."
            GROUP BY c.id_customer
            HAVING (1 ".($having ? $having :' ')." )
            ".($sort ?' ORDER BY '.$sort :'').($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");
        return Db::getInstance()->executeS($sql);
    } 
    return array();
}
public function getPostsWithFilter($filter = false, $sort = false, $start = false, $limit = false,$fontend=true)
{ 
    $req = "SELECT p.*,pc.id_category, pl.title, pl.description, pl.short_description, pl.meta_keywords, pl.meta_description,pc.position,count(pcm.id_comment) as total_comment,IFNULL(ybe.status,1) as status
            FROM "._DB_PREFIX_."ybc_blog_post p
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON p.id_post = pl.id_post
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_category pc ON (p.id_post = pc.id_post ".(Tools::getValue('id_category')? ' AND pc.id_category="'.(int)Tools::getValue('id_category').'"' :'').") 
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_related_categories rpc ON (p.id_post = rpc.id_post)
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            LEFT JOIN "._DB_PREFIX_."ybc_blog_comment pcm on (pcm.id_post=p.id_post)
            WHERE ".($fontend ? " (p.enabled=1 OR p.enabled=-1) AND (ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND ":"")." pl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '')."  
            GROUP BY p.id_post
            ORDER BY ".($sort ? $sort : '')." p.id_post DESC " . ($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");
    $posts = Db::getInstance()->executeS($req);   
    if($posts)
    {
        foreach($posts as &$post)
        {
            $post['thumb_link'] = $post['thumb'] && file_exists(dirname(__FILE__).'/views/img/post/thumb/'.$post['thumb']) ? '<img src="'.$this->_path.'views/img/post/thumb/'.$post['thumb'].'" style="width:40px;"/>':'';
            $post['description'] = $post['description'];
            $post['short_description'] = $post['short_description'];
            $author= Db::getInstance()->getRow('SELECT name,IFNULL(status,1) as status FROM '._DB_PREFIX_.'ybc_blog_employee WHERE is_customer="'.(int)$post['is_customer'].'" AND id_employee="'.(int)$post['added_by'].'"');
            
            if($post['is_customer'])
            {
                if($customer=Db::getInstance()->getValue('SELECT concat(firstname," ",lastname) as fullname  FROM '._DB_PREFIX_.'customer WHERE id_shop="'.(int)$this->context->shop->id.'" AND id_customer="'.(int)$post['added_by'].'"'))
                {
                    $link_author= $this->getlink('blog',array('id_author'=>$post['added_by'],'is_customer'=>$post['is_customer']));
                    if(!$author['name'])
                        $post['name_author'] = $customer;
                    else
                        $post['name_author']=$author['name'];
                    $post['name_author'] = '<a href="'.$link_author.'" target="_blank" title="'.$post['name_author'].'" > '.$post['name_author'].'</a> ('.$this->l('Role: customer').($author && $author['status']<=0  ? ', suspend':'').')';
                }
                else
                    $post['name_author']='';
                $post['status_author']=$author['status'];
                
            }
            else
            {
                if($employee=Db::getInstance()->getValue('SELECT concat(firstname," ",lastname) as fullname FROM '._DB_PREFIX_.'employee WHERE id_employee="'.(int)$post['added_by'].'"'))
                {
                    $link_author=$this->getlink('blog',array('id_author'=>$post['added_by']));
                    if(!$author['name'])
                        $post['name_author'] = $employee;
                    else 
                        $post['name_author']=$author['name'];
                    $id_profile= Db::getInstance()->getValue('SELECT id_profile FROM '._DB_PREFIX_.'employee WHERE id_employee='.(int)$post['added_by']);
                    if($id_profile==1)
                        $post['status_author']=1;
                    else
                        $post['status_author']=$author['status'];
                    $profile= Db::getInstance()->getValue('SELECT name from '._DB_PREFIX_.'profile_lang WHERE id_lang="'.$this->context->language->id.'" AND id_profile='.(int)$id_profile);
                    $post['name_author'] = '<a href="'.$link_author.'" title="'.$post['name_author'].'" > '.$post['name_author'].'</a> ('.$this->l('Role: ').$profile.($author && $author['status'] <=0 && $id_profile!=1 ? ', suspend':'').')';
                }
                else
                    $post['name_author']='';
                
            }
        }
    }     
    return $posts;
}
public function countPostsWithFilter($filter,$fontend=true)
{
    $req = "SELECT DISTINCT p.*, pl.title, pl.description
            FROM "._DB_PREFIX_."ybc_blog_post p
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON p.id_post = pl.id_post
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_category pc ON p.id_post = pc.id_post
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            LEFT JOIN "._DB_PREFIX_."ybc_blog_comment pcm on (pcm.id_post=p.id_post)
            WHERE ".($fontend ? "(p.enabled=1 OR p.enabled=-1) AND (ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND ":"")."pl.id_lang = ".(int)$this->context->language->id.($filter ? $filter : '');     
    $res = Db::getInstance()->executeS($req);
    return $res ? count($res) : 0;
}   
public function countEmployeesFilter($filter,$having='')
{
    $sql ="SELECT e.*,CONCAT(e.firstname, ' ', e.lastname) as employee, be.name,bel.description,be.profile_employee,be.avata,pl.name as profile_name,count(bp.id_post) as total_post FROM "._DB_PREFIX_."employee e
        LEFT JOIN "._DB_PREFIX_."ybc_blog_employee be ON (e.id_employee = be.id_employee AND be.is_customer=0)
        LEFT JOIN "._DB_PREFIX_."ybc_blog_employee_lang bel ON (bel.id_employee_post=be.id_employee_post AND bel.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN "._DB_PREFIX_."profile p ON (e.id_profile=p.id_profile)
        LEFT JOIN "._DB_PREFIX_."profile_lang pl ON (p.id_profile=pl.id_profile AND pl.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN "._DB_PREFIX_."ybc_blog_post bp ON (bp.added_by=e.id_employee AND bp.is_customer=0)
        WHERE 1 ".($filter ? $filter: '')."
        GROUP BY e.id_employee
        HAVING (1 ".($having ? $having :' ').")";
    return count(Db::getInstance()->executeS($sql));
} 
public function countCustomersFilter($filter,$having='')
{
    $group_author= explode(',',Configuration::get('YBC_BLOG_GROUP_CUSTOMER_AUTHOR'));
    if($group_author)
    {
        $sql ="SELECT c.*,CONCAT(c.firstname, ' ', c.lastname) as customer, be.name,bel.description,be.profile_employee,be.avata,count(bp.id_post) as total_post FROM "._DB_PREFIX_."customer c
            INNER JOIN "._DB_PREFIX_."customer_group cg ON (cg.id_customer=c.id_customer)
            INNER JOIN "._DB_PREFIX_."group g ON (cg.id_group=g.id_group)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee be ON (c.id_customer = be.id_employee AND be.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee_lang bel ON (bel.id_employee_post=be.id_employee_post AND bel.id_lang='".(int)$this->context->language->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post bp ON (bp.added_by=c.id_customer AND bp.is_customer=1)
            WHERE c.id_shop = '".(int)$this->context->shop->id."' AND g.id_group in (".implode(',',array_map('intval',$group_author)).") ".($filter ? $filter: '')."
            GROUP BY c.id_customer
            HAVING (1 ".($having ? $having :' ')." )";        
        return count(Db::getInstance()->executeS($sql));
    }
    return 0;
    
} 
public function getCategoriesStrByIdPost($id_post)
{
    $categories = Db::getInstance()->executeS("
        SELECT DISTINCT cl.id_category, cl.title
        FROM "._DB_PREFIX_."ybc_blog_post_category pc
        LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON pc.id_category = cl.id_category AND cl.id_lang=".(int)$this->context->language->id."
        WHERE pc.id_post=".(int)$id_post."
    ");
    $this->smarty->assign(array('categories' => $categories));
    return $this->display(__FILE__,'categories_str.tpl');
}
public function changeStatus($tbl, $field, $id , $status)
{
    $req = "UPDATE "._DB_PREFIX_."ybc_blog_".pSQL($tbl)." SET `".pSQL($field)."`=".(int)$status." WHERE id_".pSQL($tbl)."=".(int)$id;
    return Db::getInstance()->execute($req);
}
public function getPostsByIdCategory($id_category, $id_lang = false, $enabled = false)
{
    if(!$id_lang)    
        $id_lang = $this->context->language->id;
    if(!Configuration::get('YBC_BLOG_POST_SORT_BY'))
        $sort = 'p.id_post DESC, ';
    else
    {
        if(Configuration::get('YBC_BLOG_POST_SORT_BY')=='sort_order')
            $sort = 'pc.position ASC, ';
        else
            $sort = 'p.'.Configuration::get('YBC_BLOG_POST_SORT_BY').' DESC, ';
    }    
    return $this->getPostsWithFilter(' AND pc.id_category="'.(int)$id_category.'"'.($enabled ? ' AND p.enabled=1':''),$sort);
}
public function getPostById($id_post, $id_lang = false)
{
    if(!$id_lang)    
        $id_lang = $this->context->language->id;
    $req = "SELECT p.*, pl.*, e.firstname, e.lastname
            FROM "._DB_PREFIX_."ybc_blog_post p
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON p.id_post = pl.id_post AND pl.id_lang=".(int)$id_lang."
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            WHERE (ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.id_post = ".(int)$id_post;
    $post= Db::getInstance()->getRow($req);
    if($post)
    {
        $post['description'] = $post['description'];
        $post['short_description'] =$post['short_description'];
        $post['pending'] = $post['added_by']==$this->context->customer->id && $post['is_customer'] && ($post['enabled']==1 || $post['enabled']==-1) ? 1 :0; 
        return $post;
    }
    return false;
    
}
public function getCategoriesByIdPost($id_post, $id_lang = false, $enabled = false)
{
    if(!$id_lang)    
        $id_lang = $this->context->language->id;
    $req = "SELECT c.*, cl.* 
            FROM "._DB_PREFIX_."ybc_blog_category c
            INNER JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category=cs.id_category AND cs.id_shop ='".(int)$this->context->shop->id."')
            LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category AND cl.id_lang=".(int)$id_lang."
            WHERE c.id_category IN (SELECT id_category FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_post = ".(int)$id_post.")
            ".($enabled ? " AND c.enabled = 1" : '');
    $categories = Db::getInstance()->executeS($req);
    if($categories)
    {
        foreach($categories as &$cat)
            $cat['link'] = $this->getLink('blog',array('id_category' => $cat['id_category']));
    }
    return $categories;
}
private function getProductInfo($id_product, $id_lang = false)
{
    if(!$id_lang)
        $id_lang = $this->context->language->id;
    $id_customer = ($this->context->customer->id) ? (int)($this->context->customer->id) : 0;
    $id_group = null;
    if ($id_customer) {
        $id_group = Customer::getDefaultGroupId((int)$id_customer);
    }
    if (!$id_group) {
        $id_group = (int)Group::getCurrent()->id;
    }
    $group= new Group($id_group);
    if($group->price_display_method)
        $tax=false;
    else
        $tax=true;
    $product = new Product($id_product, true, $id_lang, $this->context->shop->id);
    if(!$product->active)
        return false;
    $pinfo = array();   
    $pinfo['short_description'] = $product->description_short;  
    $pinfo['name'] = $product->name;
    $price = $product->getPrice($tax,null);
    $oldPrice = $product->getPriceWithoutReduct(!$tax,null);
    $discount = $oldPrice - $price;
    $pinfo['price'] = Tools::displayPrice($price);       
    $pinfo['old_price'] = Tools::displayPrice($oldPrice); 
    $pinfo['discount_percent'] = (($oldPrice - $price) >0 ?  round(($oldPrice - $price) / $oldPrice * 100):0);
    $pinfo['discount_amount'] = Tools::displayPrice($discount);
    $pinfo['product'] = array('id_product' => $id_product);
    $images = $product->getImages((int)$this->context->cookie->id_lang);
    $link = $this->context->link;
    if(isset($images[0]))
	    $id_image = Configuration::get('PS_LEGACY_IMAGES') ? ($product->id.'-'.$images[0]['id_image']) : $images[0]['id_image'];
	else
        $id_image = $this->context->language->iso_code.'-default';			
    $pinfo['img_url'] =  $link->getImageLink($product->link_rewrite, $id_image, $this->is17 ? ImageType::getFormattedName('home') : ImageType::getFormatedName('home'));
    $pinfo['link'] = $this->context->link->getProductLink($product,null,null,null,null,null,$product->cache_default_attribute);
    return $pinfo;
}
public function getRelatedProductByProductsStr($pstr)
{
    
    if($pstr && Configuration::get('YBC_BLOG_SHOW_RELATED_PRODUCTS'))
    {
        $products = array();
        $ids = explode('-', $pstr);
        if($ids)
        {
            foreach($ids as $pid)
            {
                $product = $this->getProductInfo((int)$pid);
                if($product)
                    $products[] = $product;
            }
        }
        return $products;
    }
    return false;
}
public function renderSettingCustomer()
{
    $ybc_defines = new Ybc_blog_defines();
    $configs = $ybc_defines->customer_settings;
    $fields_form = array(
		'form' => array(
			'input' => array(),
            'submit' => array(
				'title' => $this->l('Save'),
			)
        ),
	);
    if($configs)
    {
        foreach($configs as $key => $config)
        {
            $arg = array(
                'name' => isset($config['multiple']) && $config['multiple']? $key.'[]' :$key,
                'type' => $config['type'],
                'label' => $config['label'],
                'desc' => isset($config['desc']) ? $config['desc'] : false,
                'required' => isset($config['required']) && $config['required'] ? true : false,
                'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                'values' => isset($config['values']) ? $config['values']:false,
                'multiple' => isset($config['multiple'])? $config['multiple'] : false,
                'lang' => isset($config['lang']) ? $config['lang'] : false,
                'class' => isset($config['class']) ? $config['class'] : '',
                'form_group_class' => isset($config['form_group_class']) ? $config['form_group_class'] : '',
                'tab' => isset($config['tab']) && $config['tab'] ? $config['tab'] : 'general',
                'suffix' => isset($config['suffix']) && $config['suffix'] ? $config['suffix'] : false,
                'html_content' => isset($config['html_content']) ? $this->displayBlogCategoryTre($this->getBlogCategoriesTree(0),Configuration::get('YBC_BLOG_CATEGOGY_CUSTOMER') ? explode(',',Configuration::get('YBC_BLOG_CATEGOGY_CUSTOMER')):array(),$key) : false,
                'selected_categories' => isset($config['selected_categories']) ? $config['selected_categories'] : false,
                'categories' => isset($config['categories'])? $this->getBlogCategoriesTree(0) :false,
            );
            if(isset($arg['suffix']) && !$arg['suffix'])
                unset($arg['suffix']);
            $fields_form['form']['input'][] = $arg;
        }
    }       
    $helper = new HelperForm();
	$helper->show_toolbar = false;
	$helper->table = $this->table;
	$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
	$helper->default_form_language = $lang->id;
	$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
	$this->fields_form = array();
	$helper->module = $this;
	$helper->identifier = $this->identifier;
	$helper->submit_action = 'saveCustomerAuthor';
	$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=author';
	$helper->token = Tools::getAdminTokenLite('AdminModules');
	$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));        
    $fields = array();        
    $languages = Language::getLanguages(false);
    $helper->override_folder = '/';
    if(Tools::isSubmit('saveCustomerAuthor'))
    {            
        if($configs)
        {
            foreach($configs as $key => $config)
            {       
                if(isset($config['lang']) && $config['lang'])
                {                        
                    foreach($languages as $l)
                    {
                        $fields[$key][$l['id_lang']] = Tools::getValue($key.'_'.$l['id_lang'],isset($config['default']) ? $config['default'] : '');
                    }
                }
                else
                    $fields[$key] = Tools::getValue($key,isset($config['default']) ? $config['default'] : '');                    
            }
        }
    }
    else
    {
        if($configs)
        {
                foreach($configs as $key => $config)
                {
                    if(isset($config['lang']) && $config['lang'])
                    {                    
                        foreach($languages as $l)
                        {
                            $fields[$key][$l['id_lang']] = Configuration::get($key,$l['id_lang']);
                        }
                    }
                    elseif($config['type']=='checkbox')
                    {
                        $fields[$key] =Configuration::get($key)? explode(',',Configuration::get($key)):array();
                    }
                        
                    else
                        $fields[$key] = Configuration::get($key);     
                                   
                }
        }
    }
    $helper->tpl_vars = array(
		'base_url' => $this->context->shop->getBaseURL(),
		'language' => array(
			'id_lang' => $language->id,
			'iso_code' => $language->iso_code
		),
		'fields_value' => $fields,
		'languages' => $this->context->controller->getLanguages(),
		'id_language' => $this->context->language->id,
        'cancel_url' => $this->baseAdminPath.'&control=post&list=true',
        'isConfigForm' => true,
        'image_baseurl' => $this->_path.'views/img/',
        'name_controller' => 'ybc-blog-panel-settings',
    );
    
    $this->_html .= $helper->generateForm(array($fields_form));
}
/**
 * Render config form 
 */
 public function renderRSS()
 {
    $ybc_defines = new Ybc_blog_defines();
    $configs = $ybc_defines->rss;
    $fields_form = array(
		'form' => array(
			'legend' => array(
				'title' => $this->l('RSS feed'),
				'icon' => 'icon-rss'
			),
			'input' => array(),
            'submit' => array(
				'title' => $this->l('Save'),
			)
        ),
	);
    if($configs)
    {
        foreach($configs as $key => $config)
        {
            $arg = array(
                'name' => isset($config['multiple']) && $config['multiple']? $key.'[]' :$key,
                'type' => $config['type'],
                'label' => $config['label'],
                'desc' => isset($config['desc']) ? $config['desc'] : false,
                'required' => isset($config['required']) && $config['required'] ? true : false,
                'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                'values' => isset($config['values']) ? $config['values']:false,
                'multiple' => isset($config['multiple'])? $config['multiple'] : false,
                'lang' => isset($config['lang']) ? $config['lang'] : false,
                'class' => isset($config['class']) ? $config['class'] : '',
                'form_group_class' => isset($config['form_group_class']) ? $config['form_group_class'] : '',
                'tab' => isset($config['tab']) && $config['tab'] ? $config['tab'] : 'general',
                'suffix' => isset($config['suffix']) && $config['suffix'] ? $config['suffix'] : false,
            );
            if(isset($arg['suffix']) && !$arg['suffix'])
                unset($arg['suffix']);
            $fields_form['form']['input'][] = $arg;
        }
    }        
    $helper = new HelperForm();
	$helper->show_toolbar = false;
	$helper->table = $this->table;
	$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
	$helper->default_form_language = $lang->id;
	$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
	$this->fields_form = array();
	$helper->module = $this;
	$helper->identifier = $this->identifier;
	$helper->submit_action = 'saveRSS';
	$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=rss';
	$helper->token = Tools::getAdminTokenLite('AdminModules');
	$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));        
    $fields = array();        
    $languages = Language::getLanguages(false);
    $helper->override_folder = '/';
    if(Tools::isSubmit('saveRSS'))
    {            
        if($configs)
        {
            foreach($configs as $key => $config)
            {       
                if(isset($config['lang']) && $config['lang'])
                {                        
                    foreach($languages as $l)
                    {
                        $fields[$key][$l['id_lang']] = Tools::getValue($key.'_'.$l['id_lang'],isset($config['default']) ? $config['default'] : '');
                    }
                }
                else
                    $fields[$key] = Tools::getValue($key,isset($config['default']) ? $config['default'] : '');                    
            }
        }
    }
    else
    {
        if($configs)
        {
                foreach($configs as $key => $config)
                {
                    if(isset($config['lang']) && $config['lang'])
                    {                    
                        foreach($languages as $l)
                        {
                            $fields[$key][$l['id_lang']] = Configuration::get($key,$l['id_lang']);
                        }
                    }
                    elseif($config['type']=='checkbox')
                    {
                        $fields[$key] =Configuration::get($key)? explode(',',Configuration::get($key)):array();
                    }
                        
                    else
                        $fields[$key] = Configuration::get($key);     
                                   
                }
        }
    }
    $urls_rss=array();
    $languages = Language::getLanguages(true);
    foreach($languages as $lang)
        $urls_rss[]= array(
            'link'=>$this->getLink('rss',array(),$lang['id_lang']),
            'img'=> $this->getBaseLink().'img/l/'.$lang['id_lang'].'.jpg'
        );
    $helper->tpl_vars = array(
		'base_url' => $this->context->shop->getBaseURL(),
		'language' => array(
			'id_lang' => $language->id,
			'iso_code' => $language->iso_code
		),
		'fields_value' => $fields,
		'languages' => $this->context->controller->getLanguages(),
		'id_language' => $this->context->language->id,
        'cancel_url' => $this->baseAdminPath.'&control=post&list=true',
        'isConfigForm' => true,
        'urls_rss' => $urls_rss,
        'image_baseurl' => $this->_path.'views/img/',
    );
    $this->_html .= $helper->generateForm(array($fields_form));
 }
 public function renderConfig($configs,$title,$icon)
 {
    $fields_form = array(
		'form' => array(
			'legend' => array(
				'title' => $title,
				'icon' => $icon
			),
			'input' => array(),
            'submit' => array(
				'title' => $this->l('Save'),
			)
        ),
	);
    if($configs)
    {
        foreach($configs as $key => $config)
        {
            $arg = array(
                'name' => $key,
                'type' => $config['type'],
                'label' => $config['label'],
                'autoload_rte' => isset($config['autoload_rte'])? $config['autoload_rte'] :false,
                'desc' => isset($config['desc']) ? $config['desc'] : false,
                'required' => isset($config['required']) && $config['required'] ? true : false,
                'required2' => isset($config['required2']) && $config['required2'] ? true : false,
                'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                'values' => isset($config['values']) ? $config['values'] : array(),
                'lang' => isset($config['lang']) ? $config['lang'] : false,
                'class' => isset($config['class']) ? $config['class'] : '',
                'form_group_class' => isset($config['form_group_class']) ? $config['form_group_class'] : '',
                'tab' => isset($config['tab']) && $config['tab'] ? $config['tab'] : 'general',
                'suffix' => isset($config['suffix']) && $config['suffix'] ? $config['suffix'] : false,
                'html_content' => isset($config['html_content']) ? $config['html_content']:false,
                'categories' => isset($config['categories']) ? $config['categories']:false,
                'selected_categories' => isset($config['selected_categories']) ? $config['selected_categories']:false,
            );
            if(isset($arg['suffix']) && !$arg['suffix'])
                unset($arg['suffix']);
            $fields_form['form']['input'][] = $arg;
        }
    }        
    $helper = new HelperForm();
	$helper->show_toolbar = false;
	$helper->table = $this->table;
	$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
	$helper->default_form_language = $lang->id;
	$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
	$this->fields_form = array();
	$helper->module = $this;
	$helper->identifier = $this->identifier;
	$helper->submit_action = 'saveConfig';
	$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control');
	$helper->token = Tools::getAdminTokenLite('AdminModules');
	$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));        
    $fields = array();        
    $languages = Language::getLanguages(false);
    $helper->override_folder = '/';
    if(Tools::isSubmit('saveConfig'))
    {            
        if($configs)
        {
            foreach($configs as $key => $config)
            {                    
                if(isset($config['lang']) && $config['lang'])
                {                        
                    foreach($languages as $l)
                    {
                        $fields[$key][$l['id_lang']] = Tools::getValue($key.'_'.$l['id_lang'],isset($config['default']) ? $config['default'] : '');
                    }
                }
                else
                    $fields[$key] = Tools::getValue($key,isset($config['default']) ? $config['default'] : '');                    
            }
        }
    }
    else
    {
        if($configs)
        {
                foreach($configs as $key => $config)
                {
                    if(isset($config['lang']) && $config['lang'])
                    {                    
                        foreach($languages as $l)
                        {
                            $fields[$key][$l['id_lang']] = Configuration::get($key,$l['id_lang']);
                        }
                    }
                    elseif($config['type']=='checkbox')
                        $fields[$key] = explode(',',Configuration::get($key)); 
                    elseif($config['type']=='image')
                    {
                        $fields[$key]['width'] = Configuration::get($key.'_WIDTH');
                        $fields[$key]['height'] = Configuration::get($key.'_HEIGHT');
                    }
                    elseif($config['type']=='file')
                    {    
                        if(Configuration::get($key))
                        {
                            $display_img = $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.Configuration::get($key);
                            $img_del_link = $this->baseAdminPath.'&deldefaultavataimage=true&control=image';
                        }
                        else
                        {
                            $display_img = $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/default_customer.png';
                        }             
                    }    
                    else
                        $fields[$key] = Configuration::get($key);                   
                }
        }
    }
    $urls_sitemap=array();
    $languages = Language::getLanguages(true);
    foreach($languages as $lang)
        $urls_sitemap[]= array(
            'link'=>$this->context->link->getModuleLink('ybc_blog','sitemap',array(),null,$lang['id_lang']),
            'img'=> $this->getBaseLink().'img/l/'.$lang['id_lang'].'.jpg'
        );
    $sidebars=array(
        'sidebar_new' => array(
            'title'=>$this->l('Latest posts'),
            'name'=> 'YBC_BLOG_SHOW_LATEST_NEWS_BLOCK',
        ),
        'sidebar_popular' =>array(
            'name'=>'YBC_BLOG_SHOW_POPULAR_POST_BLOCK',
            'title'=>  $this->l('Popular posts'),
        ),
        'sidebar_featured' => array(
            'title'=>$this->l('Featured posts'),
            'name'=>'YBC_BLOG_SHOW_FEATURED_BLOCK',
        ),
        'sidebar_gallery' => array(
            'title'=>$this->l('Photo gallery'),
            'name'=>'YBC_BLOG_SHOW_GALLERY_BLOCK',
        ),
        'sidebar_archived' => array(
            'title'=>$this->l('Archived posts'),
            'name'=>'YBC_BLOG_SHOW_ARCHIVES_BLOCK',
        ),
        'sidebar_categories' => array(
            'title'=>$this->l('Blog categories'),
            'name'=>'YBC_BLOG_SHOW_CATEGORIES_BLOCK',
        ),
        'sidebar_search' => array(
            'title'=>$this->l('Search in blog'),
            'name'=>'YBC_BLOG_SHOW_SEARCH_BLOCK',
        ),
        'sidebar_tags' => array(
            'title'=>$this->l('Blog tags'),
            'name'=>'YBC_BLOG_SHOW_TAGS_BLOCK'
        ),
        'sidebar_comments' => array(
            'title'=>$this->l('Latest comments'),
            'name'=>'YBC_BLOG_SHOW_COMMENT_BLOCK',
        ),
        'sidebar_authors' => array(
            'title'=>$this->l('Top authors'),
            'name'=>'YBC_BLOG_SHOW_AUTHOR_BLOCK',
        ),
        'sidebar_htmlbox' => array(
            'title'=>$this->l('HTML box'),
            'name'=>'YBC_BLOG_SHOW_HTML_BOX',
        ),
        'sidebar_rss' => array(
            'title'=>$this->l('Blog Rss'),
            'name'=>'YBC_BLOG_ENABLE_RSS_SIDEBAR',
        ),
    );
    $homepages=array(
        'homepage_new'=>array(
            'title'=>$this->l('Latest posts'),
            'name'=>'YBC_BLOG_SHOW_LATEST_BLOCK_HOME',
        ),
        'homepage_popular' => array(
            'title'=>$this->l('Popular posts'),
            'name'=>'YBC_BLOG_SHOW_POPULAR_BLOCK_HOME'
        ),
        'homepage_featured' => array(
            'title'=>$this->l('Featured posts'),
            'name'=> 'YBC_BLOG_SHOW_FEATURED_BLOCK_HOME',
        ),
        'homepage_categories' => array(
            'title'=>$this->l('Featured categories'),
            'name'=> 'YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME',
        ),
        'homepage_gallery' => array(
            'title'=>$this->l('Photo gallery'),
            'name'=>'YBC_BLOG_SHOW_GALLERY_BLOCK_HOME',
        ),
    );
    $position_sidebar= explode(',',Configuration::get('YBC_BLOG_POSITION_SIDEBAR') ? Configuration::get('YBC_BLOG_POSITION_SIDEBAR'):'sidebar_categories,sidebar_search,sidebar_new,sidebar_popular,sidebar_featured,sidebar_tags,sidebar_gallery,sidebar_archived,sidebar_comments,sidebar_authors,sidebar_htmlbox,sidebar_rss');
    if(!in_array('sidebar_htmlbox',$position_sidebar))
        $position_sidebar[]='sidebar_htmlbox';
    $position_homepages= explode(',',Configuration::get('YBC_BLOG_POSITION_HOMEPAGE')? Configuration::get('YBC_BLOG_POSITION_HOMEPAGE'):'homepage_new,homepage_popular,homepage_featured,homepage_categories,homepage_gallery');
    $helper->tpl_vars = array(
		'base_url' => $this->context->shop->getBaseURL(),
		'language' => array(
			'id_lang' => $language->id,
			'iso_code' => $language->iso_code
		),
		'fields_value' => $fields,
		'languages' => $this->context->controller->getLanguages(),
		'id_language' => $this->context->language->id,
        'cancel_url' => $this->baseAdminPath.'&control=post&list=true',
        'isConfigForm' => true,
        'sidebars'=>$sidebars,
        'position_sidebar'=>$position_sidebar,
        'url_sitemap' => trim($this->getBaseLink(),'/').'/modules/ybc_blog/sitemap.php',
        'urls_sitemap' => $urls_sitemap,
        'homepages' => $homepages,
        'position_homepages'=>$position_homepages,
        'configTabs' =>Tools::getValue('control')=='config'? $this->configTabs:array(),
        'image_baseurl' => $this->_path.'views/img/',
        'display_img' => isset($display_img)? $display_img : '',
        'img_del_link' => isset($img_del_link) ? $img_del_link :'',
        'link_module_blog' => $this->_path,
    );
    
    $this->_html .= $helper->generateForm(array($fields_form));		
 }
 
 private function _postConfig($configs,$dirImg='',$width_image='',$height_image='')
 {
    $errors = array();
    $languages = Language::getLanguages(false);
    $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
    if(Tools::isSubmit('saveConfig'))
    { 
        Hook::exec('actionUpdateBlog', array());
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && trim(Tools::getValue($key.'_'.$id_lang_default) == ''))
                    {
                        $errors[] = $config['label'].' '.$this->l('is required');
                    }                        
                }
                elseif($config['type']=='image')
                {
                    if(!Tools::getValue($key.'_WIDTH'))
                        $errors[] = $config['label'].' '.$this->l('width is required');
                    if(Tools::getValue($key.'_WIDTH') && ((int)Tools::getValue($key.'_WIDTH') <50 || (int)Tools::getValue($key.'_WIDTH') >3000))
                        $errors[] = $config['label'].' '.$this->l('The width needs to be from 50 to 3000');
                    if(!Tools::getValue($key.'_HEIGHT'))
                        $errors[] = $config['label'].' '.$this->l('height is required');
                    if(Tools::getValue($key.'_HEIGHT') && ((int)Tools::getValue($key.'_HEIGHT')<50 || (int)Tools::getValue($key.'_HEIGHT')>3000) )
                        $errors[] = $config['label'].' '.$this->l('The height needs to be from 50 to 3000');
                }
                else
                {
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && trim(Tools::getValue($key) == ''))
                    {
                        $errors[] = $config['label'].' '.$this->l('is required');
                    }
                    if(isset($config['validate']) && method_exists('Validate',$config['validate']))
                    {
                        $validate = $config['validate'];
                        if(!Validate::$validate(trim(Tools::getValue($key))))
                            $errors[] = $config['label'].' '.$this->l('is invalid');
                        unset($validate);
                    }
                    elseif(!Validate::isCleanHtml(trim(Tools::getValue($key))))
                    {
                        $errors[] = $config['label'].' '.$this->l('is invalid');
                    }   
                }                    
            }
        }
        if(Tools::getValue('YBC_BLOG_CAPTCHA_TYPE')=='google' && !Tools::getValue('YBC_BLOG_CAPTCHA_SITE_KEY'))
        {
            $errors[] = $this->l('Site key is required');
        }
        if(Tools::getValue('YBC_BLOG_CAPTCHA_TYPE')=='google2' && !Tools::getValue('YBC_BLOG_CAPTCHA_SITE_KEY3'))
        {
            $errors[] = $this->l('Site key is required');
        }
        if(Tools::getValue('YBC_BLOG_CAPTCHA_TYPE')=='google' && !Tools::getValue('YBC_BLOG_CAPTCHA_SECRET_KEY'))
        {
            $errors[] = $this->l('Secret key is required');
        }
        if(Tools::getValue('YBC_BLOG_CAPTCHA_TYPE')=='google3' && !Tools::getValue('YBC_BLOG_CAPTCHA_SECRET_KEY3'))
        {
            $errors[] = $this->l('Secret key is required');
        }
        //Custom validation
        if(Tools::getValue('control')=='seo')
        {
            foreach($languages as $lang)
            {
                if($lang['id_lang']==$id_lang_default)
                {
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_'.$lang['id_lang'])))
                        $errors[] = $this->l('Blog alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_POST_'.$lang['id_lang'])))
                        $errors[] = $this->l('Single post page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_CATEGORY_'.$lang['id_lang'])))
                        $errors[] = $this->l('Category page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_GALLERY_'.$lang['id_lang'])))
                        $errors[] = $this->l('Gallery page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_LATEST_'.$lang['id_lang'])))
                        $errors[] = $this->l('Latest posts page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_POPULAR_'.$lang['id_lang'])))
                        $errors[] = $this->l('Popular posts page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_FEATURED_'.$lang['id_lang'])))
                        $errors[] = $this->l('Featured posts page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_SEARCH_'.$lang['id_lang'])))
                        $errors[] = $this->l('Search page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_AUTHOR2_'.$lang['id_lang'])))
                        $errors[] = $this->l('Community author page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_AUTHOR_'.$lang['id_lang'])))
                        $errors[] = $this->l('Author page alias is not valid in ').$lang['iso_code'];
                    if(!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_TAG_'.$lang['id_lang'])))
                        $errors[] = $this->l('Tag page alias is not valid in').$lang['iso_code'];
                }
                else
                {
                    if(Tools::getValue('YBC_BLOG_ALIAS_'.$lang['id_lang']) && !Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_'.$lang['id_lang'])))
                        $errors[] = $this->l('Blog alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_POST_'.$lang['id_lang']) && !Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_POST_'.$lang['id_lang'])))
                        $errors[] = $this->l('Single post page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_CATEGORY_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_CATEGORY_'.$lang['id_lang'])))
                        $errors[] = $this->l('Category page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_GALLERY_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_GALLERY_'.$lang['id_lang'])))
                        $errors[] = $this->l('Gallery page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_LATEST_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_LATEST_'.$lang['id_lang'])))
                        $errors[] = $this->l('Latest posts page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_POPULAR_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_LATEST_'.$lang['id_lang'])))
                        $errors[] = $this->l('Popular posts page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_FEATURED_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_LATEST_'.$lang['id_lang'])))
                        $errors[] = $this->l('Featured posts page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_SEARCH_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_SEARCH_'.$lang['id_lang'])))
                        $errors[] = $this->l('Search page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_AUTHOR2_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_AUTHOR2_'.$lang['id_lang'])))
                        $errors[] = $this->l('Community author page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_AUTHOR_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_AUTHOR_'.$lang['id_lang'])))
                        $errors[] = $this->l('Author page alias is not valid in ').$lang['iso_code'];
                    if(Tools::getValue('YBC_BLOG_ALIAS_TAG_'.$lang['id_lang'])&&!Validate::isLinkRewrite(Tools::getValue('YBC_BLOG_ALIAS_TAG_'.$lang['id_lang'])))
                        $errors[] = $this->l('Tag page alias is not valid in ').$lang['iso_code'];
                }
            }
            
            if(!$errors)
            {
                $aliasArg = array('YBC_BLOG_ALIAS','YBC_BLOG_ALIAS_POST','YBC_BLOG_ALIAS_CATEGORY','YBC_BLOG_ALIAS_GALLERY','YBC_BLOG_ALIAS_LATEST','YBC_BLOG_ALIAS_POPULAR','YBC_BLOG_ALIAS_FEATURED','YBC_BLOG_ALIAS_SEARCH','YBC_BLOG_ALIAS_AUTHOR','YBC_BLOG_ALIAS_AUTHOR2','YBC_BLOG_ALIAS_TAG');
                $alias = array();
                foreach($languages as $lang)
                {
                    $alias[$lang['id_lang']]=array();
                    foreach($aliasArg as $aliaKey)
                    {
                        $postedAlias = trim(Tools::getValue($aliaKey.'_'.$lang['id_lang']));
                        
                        if($postedAlias && in_array($postedAlias,$alias[$lang['id_lang']]))
                        {
                            $errors[] = $this->l('Alias need to be unique in ').$lang['iso_code'];
                            break;                        
                        }
                        elseif($postedAlias){
                            $alias[$lang['id_lang']][] = $postedAlias;
                        }
                    } 
                }
                
            }
        }
        if(Tools::isSubmit('YBC_BLOG_SHOW_AUTHOR_BLOCK') && (int)Tools::getValue('YBC_BLOG_AUTHOR_NUMBER') <= 0)
            $errors[] = $this->l('Maximum number of positive authors need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_SHOW_COMMENT_BLOCK') && (int)Tools::getValue('YBC_BLOG_COMMENT_LENGTH') <= 0)
            $errors[] = $this->l('Maximum comment length of latest comments displayed need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_SHOW_COMMENT_BLOCK') && (int)Tools::getValue('YBC_BLOG_COMMENT_NUMBER') <= 0)
            $errors[] = $this->l('Maximum number of latest comments displayed in sidebar need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED') && (int)Tools::getValue('YBC_BLOG_GALLERY_POST_NUMBER') <= 0)
            $errors[] = $this->l('Maximum number of featured gallery images displayed need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK') && (int)Tools::getValue('YBC_BLOG_LATES_POST_NUMBER') <= 0)
            $errors[] = $this->l('Number of latest posts displayed need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_SHOW_POPULAR_POST_BLOCK') && (int)Tools::getValue('YBC_BLOG_PUPULAR_POST_NUMBER') <= 0)
            $errors[] = $this->l('Number of popular posts displayed need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_SHOW_FEATURED_BLOCK') && (int)Tools::getValue('YBC_BLOG_FEATURED_POST_NUMBER') <= 0)
            $errors[] = $this->l('Maximum number of featured posts displayed need to be greater than 0');            
        if(Tools::isSubmit('YBC_BLOG_LATES_POST_NUMBER') && (int)Tools::getValue('YBC_BLOG_MAX_COMMENT') < 0)
            $errors[] = $this->l('Maximum number of latest comments displayed need to be from 0');     
        if(Tools::isSubmit('YBC_BLOG_DEFAULT_RATING') && (int)Tools::getValue('YBC_BLOG_DEFAULT_RATING') < 1 || (int)Tools::getValue('YBC_BLOG_DEFAULT_RATING') >5)
            $errors[] = $this->l('Default rating must be between 1 - 5');     
        if(Tools::isSubmit('YBC_BLOG_ITEMS_PER_PAGE') && (int)Tools::getValue('YBC_BLOG_ITEMS_PER_PAGE') <= 0)
            $errors[] = $this->l('Number of items per page need to be greater than 0');     
        if(Tools::isSubmit('YBC_BLOG_SHOW_TAGS_BLOCK') && (int)Tools::getValue('YBC_BLOG_TAGS_NUMBER') <= 0)
            $errors[] = $this->l('Maximum number of tags displayed on Tags block need to be greater than 0');     
        if(Tools::isSubmit('YBC_BLOG_POST_EXCERPT_LENGTH') && (int)Tools::getValue('YBC_BLOG_POST_EXCERPT_LENGTH') < 0)
            $errors[] = $this->l('Post excerpt length cannot be smaller than 0');
        if(Tools::isSubmit('YBC_BLOG_GALLERY_PER_PAGE') && (int)Tools::getValue('YBC_BLOG_GALLERY_PER_PAGE') <= 0)
            $errors[] = $this->l('Number of image per page need to be greater than 0');
       // if(Tools::isSubmit('YBC_BLOG_CATEGORY_PER_PAGE') && (int)Tools::getValue('YBC_BLOG_CATEGORY_PER_PAGE') <= 0)
//            $errors[] = $this->l('Number of category per page need to be greater than 0');
        if(Tools::isSubmit('YBC_BLOG_COMMENT_PER_PAGE') && (int)Tools::getValue('YBC_BLOG_COMMENT_PER_PAGE') <= 0)
            $errors[] = $this->l('Number of comment per page need to be greater than 0');
        if(Tools::getValue('control')=='homepage')
        {
            if(Tools::getValue('YBC_BLOG_SHOW_LATEST_BLOCK_HOME') && Tools::getValue('YBC_BLOG_LATEST_POST_NUMBER_HOME')<=0)
                $errors[] = $this->l('Maximum number of latest posts displayed need to be greater than 0');
            if(Tools::getValue('YBC_BLOG_SHOW_POPULAR_BLOCK_HOME') && Tools::getValue('YBC_BLOG_POPULAR_POST_NUMBER_HOME')<=0)
                $errors[] = $this->l('Maximum number of popular posts displayed need to be greater than 0');
            if(Tools::getValue('YBC_BLOG_SHOW_FEATURED_BLOCK_HOME') && Tools::getValue('YBC_BLOG_FEATURED_POST_NUMBER_HOME')<=0)
                $errors[] = $this->l('Maximum number of featured posts displayed need to be greater than 0');
            if(Tools::getValue('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME') && Tools::getValue('YBC_BLOG_GALLERY_POST_NUMBER_HOME')<=0)
                $errors[] = $this->l('Maximum number of featured gallery images displayed need to be greater than 0');
            if(Tools::getValue('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME') && Tools::getValue('YBC_BLOG_CATEGORY_POST_NUMBER_HOME')<=0)
                $errors[] = $this->l('Maximum number of post categories displayed need to be greater than 0');    
        }
        if($emailsStr = Tools::getValue('YBC_BLOG_ALERT_EMAILS'))
        {
            $emails = explode(',',$emailsStr);
            if($emails)
            {
                foreach($emails as $email)
                {
                    if(!Validate::isEmail(trim($email)))
                    {
                        $errors[] = $this->l('One of the submitted emails is not valid');
                        break;
                    }
                }
            }
        }
        if(!$errors)
        {
            if($configs)
            {
                foreach($configs as $key => $config)
                {
                    if(isset($config['lang']) && $config['lang'])
                    {
                        $valules = array();
                        foreach($languages as $lang)
                        {
                            if($config['type']=='switch')                                                           
                                $valules[$lang['id_lang']] = (int)trim(Tools::getValue($key.'_'.$lang['id_lang'])) ? 1 : 0;                                
                            else
                                $valules[$lang['id_lang']] = trim(Tools::getValue($key.'_'.$lang['id_lang'])) ? trim(Tools::getValue($key.'_'.$lang['id_lang'])) : trim(Tools::getValue($key.'_'.$id_lang_default));
                        }
                        Configuration::updateValue($key,$valules,true);
                    }
                    else
                    {
                        if($config['type']=='switch')
                        {                           
                            Configuration::updateValue($key,(int)trim(Tools::getValue($key)) ? 1 : 0);
                        }
                        elseif($config['type']=='checkbox')
                            Configuration::updateValue($key,implode(',',Tools::getValue($key))); 
                        elseif($config['type']=='image')
                        {
                            Configuration::updateValue($key.'_WIDTH',Tools::getValue($key.'_WIDTH'));
                            Configuration::updateValue($key.'_HEIGHT',Tools::getValue($key.'_HEIGHT'));
                        }
                        elseif($config['type']=='blog_categories')
                        {
                            Configuration::updateValue($key,implode(',',Tools::getValue('blog_categories')));
                        }
                        elseif($config['type']=='file')
                        {      
                            if(isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['name']) && $_FILES[$key]['name'])
                            {
                                if(file_exists($dirImg.$_FILES[$key]['name']))
                                {
                                    $_FILES[$key]['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES[$key]['name'];
                                }
                                $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$key]['name'], '.'), 1));
                    			$imagesize = @getimagesize($_FILES[$key]['tmp_name']);
                    			if (isset($_FILES[$key]) &&				
                    				!empty($_FILES[$key]['tmp_name']) &&
                    				!empty($imagesize) &&
                    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
                    			)
                    			{
                    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
                    				if ($error = ImageManager::validateUpload($_FILES[$key]))
                    					$errors[] = $error;
                    				elseif (!$temp_name || !move_uploaded_file($_FILES[$key]['tmp_name'], $temp_name))
                    					$errors[] = $this->l('Can not upload the file');
                    				elseif(!ImageManager::resize($temp_name, $dirImg.$_FILES[$key]['name'], $width_image, $height_image, $type))
                    					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
                    				if (isset($temp_name))
                    					@unlink($temp_name);
                                    if(Configuration::get($key))
                                    {
                                        @unlink($dirImg.Configuration::get($key));
                                    }
                                    Configuration::updateValue($key,$_FILES[$key]['name']);
                    			}
                                
                            }
                        }
                        else
                            Configuration::updateValue($key,trim(Tools::getValue($key)));   
                    }                        
                }
            }
        }
        if (count($errors))
        {
           $this->errorMessage = $this->displayError($errors);  
        }
        if(Tools::getValue('control')=='sidebar')
        {
            $config_values=array(
                'YBC_BLOG_SHOW_CATEGORIES_BLOCK' => Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK'),
                'YBC_BLOG_SHOW_POPULAR_POST_BLOCK' => Configuration::get('YBC_BLOG_SHOW_POPULAR_POST_BLOCK'),
                'YBC_BLOG_SHOW_LATEST_NEWS_BLOCK' => Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK'),
                'YBC_BLOG_SHOW_GALLERY_BLOCK' => Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK'),
                'YBC_BLOG_SHOW_ARCHIVES_BLOCK' => Configuration::get('YBC_BLOG_SHOW_ARCHIVES_BLOCK'),
                'YBC_BLOG_SHOW_CATEGORIES_BLOCK' => Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK'),
                'YBC_BLOG_SHOW_SEARCH_BLOCK' => Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK'),
                'YBC_BLOG_SHOW_TAGS_BLOCK' => Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK'),
                'YBC_BLOG_SHOW_COMMENT_BLOCK' => Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK'),
                'YBC_BLOG_SHOW_AUTHOR_BLOCK' => Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK'),
                'YBC_BLOG_SHOW_HTML_BOX' => Configuration::get('YBC_BLOG_SHOW_HTML_BOX'),
                'YBC_BLOG_SHOW_FEATURED_BLOCK' => Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK'),
            );
        }
        if(Tools::getValue('control')=='homepage')
        {
            $config_values=array(
                'YBC_BLOG_SHOW_LATEST_BLOCK_HOME' => Configuration::get('YBC_BLOG_SHOW_LATEST_BLOCK_HOME'),
                'YBC_BLOG_SHOW_POPULAR_BLOCK_HOME' => Configuration::get('YBC_BLOG_SHOW_POPULAR_BLOCK_HOME'),
                'YBC_BLOG_SHOW_FEATURED_BLOCK_HOME' => Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK_HOME'),
                'YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME' => Configuration::get('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME'),
                'YBC_BLOG_SHOW_GALLERY_BLOCK_HOME' => Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME'),
            );
        }
        if(Tools::isSubmit('ajax'))
        {
            die(Tools::jsonEncode(
                array(
                    'messageType' => $errors ? 'error' : 'success',
                    'message' => $errors ? $this->errorMessage : $this->displayConfirmation($this->l('Configuration saved')),
                    'ybc_link_desc'=>$this->getLink(),
                    'config_values' => isset($config_values) ? $config_values:'',
                )
            ));
        }
        
        if(!count($errors))
           Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='+Tools::getValue('control'));            
    }
 }
 public function getLink($controller = 'blog', $params = array(),$id_lang=0)
 {
    $context = Context::getContext();      
    $id_lang =  $id_lang ? $id_lang : $context->language->id;
    $alias = $this->alias;
    $friendly = $this->friendly;
    $blogLink = new Ybc_blog_link_class();
    $subfix = (int)Configuration::get('YBC_BLOG_URL_SUBFIX') ? '.html' : '';
    $page = isset($params['page']) && $params['page'] ? $params['page'] : '';
    if(trim($page)!='')
    {
        $page = $page.'/';
    }
    else
        $page='';        
    if($friendly && $alias)
    {    
        $url = $blogLink->getBaseLinkFriendly(null, null).$blogLink->getLangLinkFriendly($id_lang, null, null).$alias.'/';
        if($controller=='gallery')
        {                
           $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_GALLERY',$id_lang)) ? $subAlias : 'gallery').($page ? '/'.rtrim($page,'/') : '');
           return $url;
        }
        elseif($controller=='category')
        {
            $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORIES',$id_lang)) ? $subAlias : 'categories').($page ? '/'.rtrim($page,'/') : '');
            return $url;
        }
        elseif($controller=='comment')
        {
            $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_COMMENTS',$id_lang)) ? $subAlias : 'comments').($page ? '/'.rtrim($page,'/') : '');
            return $url;
        }
        elseif($controller=='rss')
        {
            $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$id_lang)) ? $subAlias : 'rss');
            if(isset($params['id_category']) && $categoryAlias = $this->getCategoryAlias((int)$params['id_category']))
            {
                $url .= '/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$id_lang)) ? $subAlias : 'category').'/'.(int)$params['id_category'].'-'.$categoryAlias.$subfix;
            }
            elseif(isset($params['id_author']) && isset($params['is_customer']) && $params['is_customer'] &&  $authorAlias = (isset($params['alias']) ? $params['alias'] : 'community-author'))
            {
                $url .= '/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR2',$id_lang)) ? $subAlias : 'community-author').'/'.(int)$params['id_author'].'-'.$authorAlias;
            }
            elseif(isset($params['id_author']) && $authorAlias = (isset($params['alias']) ? $params['alias'] : 'author'))
            {
                $url .= '/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$id_lang)) ? $subAlias : 'author').'/'.(int)$params['id_author'].'-'.$authorAlias;
            }
            elseif(isset($params['latest_posts']))
            {
                $url .= '/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_LATEST',$id_lang)) ? $subAlias : 'latest-posts');
            }
            elseif(isset($params['popular_posts']))
            {
                $url .= '/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_POPULAR',$id_lang)) ? $subAlias : 'popular-posts');
            }
            elseif(isset($params['featured_posts']))
            {
                $url .= '/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_FEATURED',$id_lang)) ? $subAlias : 'featured-posts');
            }
            return $url;
        }
        elseif($controller=='blog')
        {
            if(isset($params['edit_comment']) && (int)$params['edit_comment'] && isset($params['id_post']) && $params['id_post'] && $postAlias = $this->getPostAlias((int)$params['id_post']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$id_lang)) ? $subAlias : 'post').'/'.(int)$params['id_post'].'-'.(int)$params['edit_comment'].'-'.$postAlias.$subfix;
            }
            elseif( isset($params['all_comment']) && $params['all_comment'] &&  isset($params['id_post']) && $postAlias = $this->getPostAlias((int)$params['id_post']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$id_lang)) ? $subAlias : 'post').'/allcomments/'.(int)$params['id_post'].'-'.$postAlias.$subfix;
            }
            elseif(isset($params['id_post']) && $postAlias = $this->getPostAlias((int)$params['id_post']))
            {
                if(Configuration::get('YBC_BLOG_URL_NO_ID'))
                    $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$id_lang)) ? $subAlias : 'post').'/'.$postAlias.$subfix;
                else
                    $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$id_lang)) ? $subAlias : 'post').'/'.$params['id_post'].'-'.$postAlias.$subfix;
            }
            elseif(isset($params['id_category']) && $categoryAlias = $this->getCategoryAlias((int)$params['id_category']))
            {
                 if(Configuration::get('YBC_BLOG_URL_NO_ID'))
                    $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$id_lang)) ? $subAlias : 'category').($page ? '/'.rtrim($page) : '/').$categoryAlias.$subfix;
                 else
                    $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$id_lang)) ? $subAlias : 'category').($page ? '/'.rtrim($page) : '/').$params['id_category'].'-'.$categoryAlias.$subfix;
            }
            elseif(isset($params['id_author']) && isset($params['is_customer']) && $params['is_customer'] && $authorAlias = (isset($params['alias']) ? $params['alias'] : 'community-author'))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR2',$id_lang)) ? $subAlias : 'community-author').($page ? '/'.rtrim($page) : '/').(int)$params['id_author'].'-'.$authorAlias;
            }
            elseif(isset($params['id_author']) && $authorAlias = (isset($params['alias']) ? $params['alias'] : 'author'))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$id_lang)) ? $subAlias : 'author').'/'.$page.(int)$params['id_author'].'-'.$authorAlias;
            }
            elseif(isset($params['tag']))
            {
                $url .= $page.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_TAG',$id_lang)) ? $subAlias : 'tag').'/'.(string)$params['tag'];
            }
            elseif(isset($params['search']))
            {
                $url .= $page.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_SEARCH',$id_lang)) ? $subAlias : 'search').'/'.(string)$params['search'];
            }
            elseif(isset($params['latest']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_LATEST',$id_lang)) ? $subAlias : 'latest').($page ? '/'.rtrim($page,'/') : '');
            }
            elseif(isset($params['popular']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_POPULAR',$id_lang)) ? $subAlias : 'popular').($page ? '/'.rtrim($page,'/') : '');
            }
            elseif(isset($params['featured']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_FEATURED',$id_lang)) ? $subAlias : 'featured').($page ? '/'.rtrim($page,'/') : '');
            }
            elseif(isset($params['month']) && isset($params['year']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_MONTHS',$id_lang)) ? $subAlias : 'month').'/'.$params['month'].'/'.$params['year'].($page ? '/'.rtrim($page,'/') : '');
            }
            elseif(isset($params['year']))
            {
                $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_YEARS',$id_lang)) ? $subAlias : 'year').'/'.$params['year'].($page ? '/'.rtrim($page,'/') : '');
            }
            else
            {
                if($page)
                    $url .= trim($page,'/');
                else
                    $url = rtrim($url,'/');
            } 
            if(isset($params['edit_comment']) && (int)$params['edit_comment'] && isset($params['id_post']) && $params['id_post'])  
                $url .='#ybc-blog-form-comment';
            return $url;            
        }
        elseif($controller=='author')
        {
            $url .= (($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$id_lang)) ? $subAlias : 'author').($page ? '/'.rtrim($page,'/') : '');
            return $url;
        }            
    }
    $extra='';
    if($params)
        foreach($params as $key=> $param)
            $extra ='&'.$key.'='.$param;
    return Tools::getShopDomainSsl(true).__PS_BASE_URI__.'index.php?fc=module&module='.$this->name.'&controller='.$controller.'&id_lang='.$this->context->language->id.$extra;
 }
 private function getCategoryAlias($id_category)
 {
    $req = "SELECT cl.url_alias
            FROM "._DB_PREFIX_."ybc_blog_category_lang cl
            WHERE cl.id_category = ".(int)$id_category.' AND cl.id_lang='.(int)$this->context->language->id;
    $row = Db::getInstance()->getRow($req);
    if(isset($row['url_alias']))
        return $row['url_alias'];
    return false;
 }
 private function getPostAlias($id_post)
 {
    $req = "SELECT pl.url_alias
            FROM "._DB_PREFIX_."ybc_blog_post_lang pl
            WHERE pl.id_post = ".(int)$id_post.' AND pl.id_lang='.(int)$this->context->language->id;
    $row = Db::getInstance()->getRow($req);
    if(isset($row['url_alias']))
        return $row['url_alias'];
    return false;
 }
 public function getPollsWithFilter($filter = false, $sort = false, $start = false, $limit = false,$fontend=true)
 {
    $req = "SELECT po.*,pl.description,pl.short_description,p.thumb,pl.title
            FROM "._DB_PREFIX_."ybc_blog_polls po
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps on (po.id_post=ps.id_post)
            INNER JOIN "._DB_PREFIX_."ybc_blog_post p ON (p.id_post=ps.id_post)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            WHERE ".($fontend ? "(ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.enabled=1 AND ":"")." ps.id_shop=".(int)$this->context->shop->id." ".($filter ? $filter : '')."
            ORDER BY ".($sort ? $sort : '')." po.id_polls desc " . ($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");
    $polls= Db::getInstance()->executeS($req);
    return $polls;
 }
 public function countPollsWithFilter($filter,$fontend=true)
 {
    $req = "SELECT po.*,pl.description,pl.short_description,p.thumb,pl.title
            FROM "._DB_PREFIX_."ybc_blog_polls po
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps on (po.id_post=ps.id_post)
            INNER JOIN "._DB_PREFIX_."ybc_blog_post p ON (p.id_post=ps.id_post)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            WHERE ".($fontend ? "(ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.enabled=1 AND ":"")." ps.id_shop=".(int)$this->context->shop->id." ".($filter ? $filter : '');
    $polls= Db::getInstance()->executeS($req);
    return count($polls);
 }
 public function getCommentsWithFilter($filter = false, $sort = false, $start = false, $limit = false,$fontend=true)
 {          
    $req = "SELECT bc.*,pl.description,pl.short_description,p.thumb,pl.title
            FROM "._DB_PREFIX_."ybc_blog_comment bc
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps on (bc.id_post=ps.id_post)
            INNER JOIN "._DB_PREFIX_."ybc_blog_post p ON (p.id_post=ps.id_post)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            WHERE ".($fontend ? "(ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.enabled=1 AND ":"")." ps.id_shop=".(int)$this->context->shop->id." ".($filter ? $filter : '')."
            ORDER BY ".($sort ? $sort : '')." bc.id_comment desc " . ($start !== false && $limit ? " LIMIT ".(int)$start.", ".(int)$limit : "");
    $comments= Db::getInstance()->executeS($req);
    if($comments)
    {
        foreach($comments as &$comment)
        {
            if($comment['customer_reply']==1)
            {
                $customer= Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'customer WHERE id_shop="'.(int)$this->context->shop->id.'" AND  id_customer='.(int)$comment['replied_by']);
                $comment['efirstname']= $customer['firstname'];
                $comment['elastname']= $customer['lastname'];
            }
            else
            {
                $employee= Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'employee WHERE id_employee='.(int)$comment['replied_by']);
                $comment['efirstname']= $employee['firstname'];
                $comment['elastname']= $employee['lastname'];
            }
            if($this->checkPermisionComment('edit',$comment['id_comment']))
                $comment['url_edit'] = $this->getLink('blog',array('id_post'=>$comment['id_post'],'edit_comment'=>$comment['id_comment']));
            if($this->checkPermisionComment('delete',$comment['id_comment']))
                $comment['url_delete'] = $this->context->link->getModuleLink('ybc_blog','managementcomments',array('deletecomment'=>1,'id_comment'=>$comment['id_comment']));
            $sql = 'SELECT * FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_comment='.(int)$comment['id_comment'].' AND approved=1';
            $comment['replies'] = Db::getInstance()->executeS($sql);
            if($comment['replies'])
            {
                foreach($comment['replies'] as &$reply)
                {
                    $reply['reply']= str_replace("\n",'<'.'b'.'r'.'>',$reply['reply']);
                    if($reply['id_employee'])
                    {
                        if($name= Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$reply['id_employee'].'" AND is_customer=0'))
                            $reply['name']= $name;
                        elseif($name = Db::getInstance()->getValue('SELECT CONCAT(firstname," ",lastname) FROM '._DB_PREFIX_.'employee WHERE id_employee='.(int)$reply['id_employee']))
                            $reply['name']= $name;
                    }
                    if($reply['id_user'])
                    {
                        if($name= Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$reply['id_user'].'" AND is_customer=1'))
                            $reply['name']= $name;
                        elseif($name = Db::getInstance()->getValue('SELECT CONCAT(firstname," ",lastname) FROM '._DB_PREFIX_.'customer WHERE id_customer='.(int)$reply['id_user']))
                            $reply['name']= $name;
                    }
                }
                
            }
            $comment['comment'] = str_replace("\n",'<'.'b'.'r'.'>',$comment['comment']);
        }
    }
    return $comments;
 }
 public function getCommentById($id_comment)
 {          
    return Db::getInstance()->getRow("SELECT bc.*, e.firstname as efirstname, e.lastname as elastname,pl.title as post_title
            FROM "._DB_PREFIX_."ybc_blog_comment bc
            LEFT JOIN "._DB_PREFIX_."employee e ON e.id_employee = bc.replied_by
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON bc.id_post = pl.id_post AND pl.id_lang=".(int)$this->context->language->id."
            WHERE bc.id_comment=".(int)$id_comment."
            ");
 }
 public function countCommentsWithFilter($filter = false,$fontend=true)
 { 
    $req = "SELECT COUNT(bc.id_comment) as total_comment
            FROM "._DB_PREFIX_."ybc_blog_comment bc
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps on (bc.id_post=ps.id_post)
            INNER JOIN "._DB_PREFIX_."ybc_blog_post p ON (p.id_post=ps.id_post)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
            LEFT JOIN "._DB_PREFIX_."customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
            LEFT JOIN "._DB_PREFIX_."employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            WHERE ".($fontend ? "(ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.enabled=1 AND ":"")."  ps.id_shop=".(int)$this->context->shop->id." ".($filter ? $filter : '');
     $row = Db::getInstance()->getRow($req);
     return isset($row['total_comment']) ?  (int)$row['total_comment'] : 0;
 }
 public function getEverageReviews($id_post)
 {
    $totalRating = $this->getTotalReviewsWithRating($id_post);
    $numRating = $this->countTotalReviewsWithRating($id_post);
    if($numRating > 0)
    {
        $rat = Tools::ps_round($totalRating/$numRating,2);
        $rat_ceil = ceil($totalRating/$numRating);
        $rat_floor = floor($totalRating/$numRating);
        if($rat_ceil-$rat <=0.25)
            return $rat_ceil;
        if($rat-$rat_floor<=0.25)   
            return $rat_floor;
        return $rat_floor+0.5;        
    }
        
    return 0;        
 }
 public function getTotalReviewsWithRating($id_post)
 {
    $req = "SELECT SUM(rating) as total_rating
            FROM "._DB_PREFIX_."ybc_blog_comment
            WHERE id_post = ".(int)$id_post." AND rating > 0 AND approved = 1";
    $row = Db::getInstance()->getRow($req);
    if(isset($row['total_rating']))
        return (int)$row['total_rating'];
    return 0;
 }
 public function countTotalReviewsWithRating($id_post)
 {
    $req = "SELECT COUNT(rating) as num_rating
            FROM "._DB_PREFIX_."ybc_blog_comment
            WHERE id_post = ".(int)$id_post." AND rating > 0 AND approved = 1";
    $row = Db::getInstance()->getRow($req);
    if(isset($row['num_rating']))
        return (int)$row['num_rating'];
    return 0;
 }
 
 /**
  * Hooks 
  */
 public function hookDisplayLeftColumn()
 {
    if(Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && (Tools::getValue('fc')!='module' || Tools::getValue('module')!=$this->name))
        return '';
    $params=array();
    $sidebars=array(
            'sidebar_new' => Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK') ? $this->hookBlogNewsBlock($params):'',
            'sidebar_popular' => Configuration::get('YBC_BLOG_SHOW_POPULAR_POST_BLOCK') ? $this->hookBlogPopularPostsBlock($params):'',
            'sidebar_featured' => Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') ? $this->hookBlogFeaturedPostsBlock($params):'',
            'sidebar_gallery' => Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK') ? $this->hookBlogGalleryBlock($params):'',
            'sidebar_archived' => Configuration::get('YBC_BLOG_SHOW_ARCHIVES_BLOCK')? $this->hookBlogArchivesBlock():'',
            'sidebar_categories' => Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK') ? $this->hookBlogCategoriesBlock():'',
            'sidebar_search' => Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK') ? $this->hookBlogSearchBlock():'',
            'sidebar_tags' => Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK') ? $this->hookBlogTagsBlock():'',
            'sidebar_comments' => Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK') ? $this->hookBlogComments():'',
            'sidebar_authors' => Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK') ? $this->hookBlogPositiveAuthor():'',
            'sidebar_htmlbox' => Configuration::get('YBC_BLOG_SHOW_HTML_BOX') ? $this->displayHtmlContent():'',
            'sidebar_rss' => Configuration::get('YBC_BLOG_ENABLE_RSS_SIDEBAR') && in_array('side_bar',explode(',',Configuration::get('YBC_BLOC_RSS_DISPLAY'))) ? $this->hookBlogRssSideBar():'',
    );
    $sidebars_postion= explode(',',Configuration::get('YBC_BLOG_POSITION_SIDEBAR') ? Configuration::get('YBC_BLOG_POSITION_SIDEBAR') :'sidebar_categories,sidebar_search,sidebar_new,sidebar_popular,sidebar_featured,sidebar_tags,sidebar_gallery,sidebar_archived,sidebar_comments,sidebar_authors,sidebar_htmlbox,sidebar_rss');
    if(!in_array('sidebar_htmlbox',$sidebars_postion))
        $sidebars_postion[] = 'sidebar_htmlbox';
    $this->context->smarty->assign(
        array(
            'sidebars_postion' => $sidebars_postion,
            'sidebars'=>$sidebars
        )
    );
    return $this->display(__FILE__, 'blocks.tpl');
  }
  public function displayHtmlContent()
  {
    if($content = Configuration::get('YBC_BLOG_CONTENT_HTML_BOX',$this->context->language->id))
    {
        $this->context->smarty->assign(
            array(
                'html_content_box' => $content,
                'page' => 'html_box',
                'html_title_box' => Configuration::get('YBC_BLOG_TITLE_HTML_BOX',$this->context->language->id) ? : $this->l('Html box'),
            )
        );
        return $this->display(__FILE__,'html_box.tpl');
    }
    return '';
  }
  public function hookBlogSidebar()
  {
      return $this->hookDisplayLeftColumn();
  }
  public function hookRightColumn()
  {
      return $this->hookDisplayLeftColumn();
  }      
  public function hookDisplayBackOfficeHeader()
  {
        $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css');
        $this->context->controller->addCSS($this->_path.'views/css/admin_all.css');
        if((Tools::getValue('controller')=='AdminModules' && Tools::getValue('configure')==$this->name) || Tools::getValue('controller')=='AdminYbcBlogStatistics')
        {
            $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css');
            $this->context->controller->addCSS($this->_path.'views/css/admin.css');
            $this->context->controller->addCSS($this->_path.'views/css/other.css');
            if(!$this->is17)
            {
                $this->context->controller->addCSS($this->_path.'views/css/admin_fix16.css'); 
            }
        }
        if(Tools::getValue('controller')=='AdminYbcBlogStatistics')
        {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryPlugin('autocomplete');
            $this->context->controller->addCSS((__PS_BASE_URI__).'modules/'.$this->name.'/views/css/nv.d3_rtl.css','all');
            $this->context->controller->addCSS((__PS_BASE_URI__).'modules/'.$this->name.'/views/css/nv.d3.css','all');
        }
  }
  public function hookDisplayFooter()
  {
        $this->smarty->assign(array(
                'like_url' => $this->getLink('like'),
                'YBC_BLOG_SLIDER_SPEED' => (int)Configuration::get('YBC_BLOG_SLIDER_SPEED') > 0 ? (int)Configuration::get('YBC_BLOG_SLIDER_SPEED') : 5000,
                'YBC_BLOG_GALLERY_SPEED' => (int)Configuration::get('YBC_BLOG_GALLERY_SPEED') > 0 ? (int)Configuration::get('YBC_BLOG_GALLERY_SPEED') : 5000,
                'YBC_BLOG_GALLERY_SPEED' => (int)Configuration::get('YBC_BLOG_GALLERY_SPEED') > 0 ? (int)Configuration::get('YBC_BLOG_GALLERY_SPEED') : 5000,
                'YBC_BLOG_GALLERY_SKIN' => Configuration::get('YBC_BLOG_GALLERY_SKIN') ? Configuration::get('YBC_BLOG_GALLERY_SKIN') : 'default',
                'YBC_BLOG_GALLERY_AUTO_PLAY' => (int)Configuration::get('YBC_BLOG_GALLERY_AUTO_PLAY') ? 1 : 0,
                'ybc_like_error' =>  addslashes($this->l('There was a problem while submitting your request. Try again later'))                                   
            )
        );
        return $this->display(__FILE__, 'footer.tpl');
  }
  public function hookDisplayHeader()
  { 
        $this->assignConfig();
        if($this->is17)
        {
            $this->context->controller->addCSS($this->_path.'views/css/fix17.css');
        }                
        else
            $this->context->controller->addCSS($this->_path.'views/css/fix16.css');
        if(Tools::getValue('controller')=='myaccount'){
            $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css');
            $this->context->controller->addCSS($this->_path.'views/css/material-icons.css');
            $this->context->controller->addCSS($this->_path.'views/css/blog.css');
            return '';
        }
        
        if(Tools::getValue('controller')=='index'  && Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_POPULAR_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_LATEST_BLOCK_HOME')) 
            return '';
        if(Tools::getValue('controller')=='index'  && !Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_POPULAR_BLOCK_HOME') && !Configuration::get('YBC_BLOG_SHOW_LATEST_BLOCK_HOME') && !Configuration::get('YBC_BLOG_ENABLE_RSS') && !Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_ARCHIVES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_POPULAR_POST_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK')) 
            return '';
        if( (Tools::getValue('fc')!='module' || Tools::getValue('module')!=$this->name) && Tools::getValue('controller')!='index' && Tools::getValue('controller')!='product' &&  Tools::getValue('controller')!='category'  && !Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_ENABLE_RSS') && !Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_ARCHIVES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_POPULAR_POST_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK'))
            return '';
        elseif((Tools::getValue('fc')!='module' || Tools::getValue('module')!=$this->name) && Tools::getValue('controller')!='index' && Tools::getValue('controller')!='product' &&  Tools::getValue('controller')!='category'  && Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY'))
            return '';
        if(Tools::getValue('controller')=='category' && Tools::getValue('fc')!='module'  && Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_DISPLAY_CATEGORY_PAGE')) 
            return '';
        if(Tools::getValue('controller')=='product'  && Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_DISPLAY_PRODUCT_PAGE')) 
            return '';
        if(Tools::getValue('controller')=='product'  && !Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_DISPLAY_PRODUCT_PAGE')&& !Configuration::get('YBC_BLOG_ENABLE_RSS') && !Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_ARCHIVES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_POPULAR_POST_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK')) 
            return '';
        if(Tools::getValue('controller')=='category' && Tools::getValue('fc')!='module'  && !Configuration::get('YBC_BLOG_DISPLAY_BLOG_ONLY') && !Configuration::get('YBC_BLOG_DISPLAY_CATEGORY_PAGE')&& !Configuration::get('YBC_BLOG_ENABLE_RSS') && !Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_ARCHIVES_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_POPULAR_POST_BLOCK') && !Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK')) 
            return '';
        
        if(Module::isInstalled('ybc_blog')&& Module::isEnabled('ybc_blog'))
        {
            if($this->checkCreatedColumn('ybc_blog_post','datetime_active'))
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_post SET enabled=1 WHERE datetime_active!="0000-00-00" AND datetime_active!="" AND enabled=2 AND datetime_active<=NOW()');
        }
        $this->context->controller->addJS($this->_path.'views/js/owl.carousel.js');
        $this->context->controller->addJS($this->_path.'views/js/jquery.prettyPhoto.js');
        $this->context->controller->addJS($this->_path.'views/js/prettyPhoto.inc.js');
        $this->context->controller->addJS($this->_path.'views/js/slick.js');
        $this->context->controller->addJS($this->_path.'views/js/jquery.lazyload.min.js'); 
        $this->context->controller->addJS($this->_path.'views/js/blog.js');           
        $this->context->controller->addCSS($this->_path.'views/css/prettyPhoto.css');
        $this->context->controller->addCSS($this->_path.'views/css/font-awesome.css');
        $this->context->controller->addCSS($this->_path.'views/css/material-icons.css');
        $this->context->controller->addCSS($this->_path.'views/css/owl.carousel.css');
        $this->context->controller->addCSS($this->_path.'views/css/owl.theme.css');
        $this->context->controller->addCSS($this->_path.'views/css/slick.css');
        $this->context->controller->addCSS($this->_path.'views/css/owl.transitions.css');
        $this->context->controller->addCSS($this->_path.'views/css/blog.css');
              
        if(Configuration::get('YBC_BLOG_RTL_MODE')=='auto' && isset($this->context->language->is_rtl) && $this->context->language->is_rtl || Configuration::get('YBC_BLOG_RTL_MODE')=='rtl')
            $this->context->controller->addCSS($this->_path.'views/css/rtl.css'); 
        if(trim(Tools::getValue('fc'))=='module' && trim(Tools::getValue('module'))=='ybc_blog')
        {
            $this->context->controller->addJS($this->_path.'views/js/jquery.nivo.slider.js');
            $this->context->controller->addCSS($this->_path.'views/css/nivo-slider.css');
            $this->context->controller->addCSS($this->_path.'views/css/themes/default/default.css');                
        }
        if(Tools::getValue('controller')=='category' && Configuration::get('YBC_BLOG_DISPLAY_CATEGORY_PAGE') && $id_category=Tools::getValue('id_category'))
        {
            if(Tools::isSubmit('displayPostRelatedCategories'))
            {
                die(Tools::jsonEncode(
                    array(
                        'html_block' => $this->displayPostRelatedCategories($id_category),
                    )
                ));
            }
            $this->context->controller->addJS($this->_path.'views/js/related.js'); 
        }
        return $this->getInternalStyles();
  }
  public function assignConfig()
  {          
      $assign = array();
      $ybc_defines = new Ybc_blog_defines();
      foreach($ybc_defines->configs as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_seo as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_sitemap as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_sidebar as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      $ybc_defines->configs_homepage['YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME']=array(
            'label' => $this->l('Select blog categories to display'),
                'type' => 'blog_categories',
                'html_content' =>$this->displayBlogCategoryTre($this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),$this->getSelectedCategories()),
    			'categories' => $this->getBlogCategoriesTree(0,true,$this->context->language->id,0,false),
    			'name' => 'categories',
                'selected_categories' => $this->getSelectedCategories(),
                'default' =>'',
      );
      foreach($ybc_defines->configs_homepage as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_postpage as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_postlistpage as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_productpage as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_categorypage as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->configs_email as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->socials as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->customer_settings as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      foreach($ybc_defines->rss as $key => $val)
      {
            $assign[$key] = isset($val['lang']) && $val['lang'] ? Configuration::get($key, $this->context->language->id) : ($val['type']=='checkbox' || $val['type']=='blog_categories' ? explode(',',Configuration::get($key)) : Configuration::get($key));
      }
      if(Configuration::get('YBC_BLOG_RTL_MODE')=='auto' && isset($this->context->language->is_rtl) && $this->context->language->is_rtl || Configuration::get('YBC_BLOG_RTL_MODE')=='rtl')
        $rtl = true;
     else
        $rtl = false;
      $assign['YBC_BLOG_RTL_CLASS'] = $rtl ? 'ybc_blog_rtl_mode' : 'ybc_blog_ltr_mode'; 
      $assign['YBC_BLOG_SHOP_URI'] = _PS_BASE_URL_.__PS_BASE_URI__;  
      if(trim(Tools::getValue('fc'))=='module' && trim(Tools::getValue('module'))=='ybc_blog' && Tools::getValue('controller')=='managementblog' && Tools::getValue('tabmanagament')=='post')
      {
            $this->context->smarty->assign('add_tmce',true);
      }
      $this->context->smarty->assign(
            array(
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT'),
                'allowGuestsComments' => (int)Configuration::get('YBC_BLOG_ALLOW_GUEST_COMMENT') ? true : false,
                'blogCommentAction' => $this->getLink('blog',array('id_post'=>(int)Tools::getValue('id_post'))),
                'hasLoggedIn' => $this->context->customer->isLogged(true), 
                'allow_report_comment' =>(int)Configuration::get('YBC_BLOG_ALLOW_REPORT') ? true : false,
                'display_related_products' =>(int)Configuration::get('YBC_BLOG_SHOW_RELATED_PRODUCTS') ? true : false,
                'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                'default_rating' => (int)Tools::getValue('rating') > 0 && (int)Tools::getValue('rating') <=5 ? (int)Tools::getValue('rating')  :(int)Configuration::get('YBC_BLOG_DEFAULT_RATING'),
                'use_capcha' => (int)Configuration::get('YBC_BLOG_USE_CAPCHA') ? true : false,
                'use_facebook_share' => (int)Configuration::get('YBC_BLOG_ENABLE_FACEBOOK_SHARE') ? true : false,
                'use_google_share' => (int)Configuration::get('YBC_BLOG_ENABLE_GOOGLE_SHARE') ? true : false,
                'use_twitter_share' => (int)Configuration::get('YBC_BLOG_ENABLE_TWITTER_SHARE') ? true : false,                    
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'show_tags' => (int)Configuration::get('YBC_BLOG_SHOW_POST_TAGS') ? true : false,
                'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'enable_slideshow' => (int)Configuration::get('YBC_BLOG_ENABLE_POST_SLIDESHOW') ? true : false,
                'prettyAutoPlay' => (int)Configuration::get('YBC_BLOG_GALLERY_AUTO_PLAY') ? 1 : 0,
                'show_author' => (int)Configuration::get('YBC_BLOG_SHOW_POST_AUTHOR') ? 1 : 0,
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')), 
                'blog_related_product_type' => Tools::strtolower(Configuration::get('YBC_RELATED_PRODUCTS_TYPE')),
                'blog_related_posts_type' => Tools::strtolower(Configuration::get('YBC_RELATED_POSTS_TYPE')),
                'blog_dir' => $this->blogDir,
                'image_folder' => $this->blogDir.'views/img/',
            )
      );          
      $this->context->smarty->assign(array('blog_config' => $assign));
  }
  public function loadMoreBlog($postData)
  {
        $this->context->smarty->assign(
            array(
                'blog_posts' => $postData['posts'],
                'blog_paggination' => $postData['paggination'],
                'blog_category' => $postData['category'],
                'blog_latest' => $postData['latest'],
                'blog_dir' => $postData['blogDir'],
                'blog_tag' => $postData['tag'],
                'blog_search' => $postData['search'],
                'is_main_page' => !$postData['category'] && !$postData['tag'] && !$postData['search'] && !Tools::isSubmit('latest') && !Tools::isSubmit('id_author') ? true : false,
                'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                'show_featured_post' => (int)Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'path' => $this->getBreadCrumb(),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false, 
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),   
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')),
                'author' => $postData['author'],     
                'breadcrumb' => $this->is17 ? $this->getBreadCrumb() : false,
                'loadajax'=>1,              
            )
        );
        $this->assignConfig();
        die(
            Tools::jsonEncode(
                array(
                     'list_blog'=> $this->display(__FILE__,'blog_list.tpl'),   
                     'blog_paggination'=>$postData['paggination'],            
                )
            )
        );
  }
  public function loadMoreAuhors($authors,$panigation)
  {
        $this->context->smarty->assign(
            array(
                'is_main_page' =>false,
                'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                'show_featured_post' => (int)Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'path' => $this->getBreadCrumb(),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false, 
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),   
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'authors' => $authors,
                'blog_paggination' => $panigation,
                'breadcrumb' => $this->is17 ? $this->getBreadCrumb() : false, 
            )
       );
       die(
            Tools::jsonEncode(
                array(
                     'list_blog'=> $this->display(__FILE__,'authors_list.tpl'),   
                     'blog_paggination'=>$panigation,            
                )
            )
        ); 
  }
  public function loadMoreCategories($categoryData)
  {
    
        $this->context->smarty->assign(
            array(
                'blog_categories' => $categoryData['categories'],
                'blog_paggination' => $categoryData['paggination'],
                'path' => $this->getBreadCrumb(),
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),                 
                'breadcrumb' => $this->is17 ? $this->getBreadCrumb() : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'image_folder' => $this->blogDir.'views/img/category/',
            )
        );
        $this->assignConfig();
        die(
            Tools::jsonEncode(
                array(
                     'list_blog'=> $this->display(__FILE__,'categories_list.tpl'),   
                     'blog_paggination'=>$categoryData['paggination'],            
                )
            )
        );
  }
  public function loadMoreComments($posts,$paggination)
  {
        $this->context->smarty->assign(
            array(
                'posts' => $posts,
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'comment_length' => (int)Configuration::get('YBC_BLOG_COMMENT_LENGTH') ? (int)Configuration::get('YBC_BLOG_COMMENT_LENGTH'):120,
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),
                'comment_paggination' => $paggination->render(),          
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'image_folder' => $this->_path.'views/img/avata/',
            )
        );
        $this->assignConfig();
        die(
            Tools::jsonEncode(
                array(
                     'list_blog'=> $this->display(__FILE__,'comment_list.tpl'),   
                     'blog_paggination'=> $paggination->render(),            
                )
            )
        );
  }
  public function hookBlogSearchBlock()
  {
        if(!Configuration::get('YBC_BLOG_SHOW_SEARCH_BLOCK'))
            return;
        $this->smarty->assign(
            array(
                'action' => $this->getLink('blog'),
                'search' => urldecode(trim(Tools::getValue('search'))),
                'id_lang' => $this->context->language->id
            )
        );
        if(trim(Tools::getValue('blog_search'))!='')
        {
            Tools::redirect($this->getLink('blog',array('search'=>urlencode(trim(Tools::getValue('blog_search'))))));
        }
        return $this->display(__FILE__, 'search_block.tpl');
  }
  public function hookBlogRssSideBar()
  {
        $this->context->smarty->assign(
            array(
                'url_rss' => $this->getLink('rss'),
                'link_latest_posts' => $this->getLink('rss',array('latest_posts'=>1)),
                'link_popular_posts' => $this->getLink('rss',array('popular_posts'=>1)),
                'link_featured_posts' => $this->getLink('rss',array('featured_posts'=>1)),
            )
        );
        return $this->display(__FILE__,'rss_block.tpl');
  }
  public function hookBlogComments()
  {
        if(!Configuration::get('YBC_BLOG_SHOW_COMMENT_BLOCK'))
            return '';
        $limit = Configuration::get('YBC_BLOG_COMMENT_NUMBER') ? (int)Configuration::get('YBC_BLOG_COMMENT_NUMBER'):20;
        $posts = $this->getCommentsWithFilter(' AND bc.approved=1','bc.id_comment DESC,',0,$limit);
        if($posts)
        {
            foreach($posts as &$post)
            {
                $post['link'] = $this->getLink('blog',array('id_post' => $post['id_post']));
                if($post['thumb'])
                    $post['thumb'] = $this->_path.'views/img/post/thumb/'.$post['thumb'];
                $post['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                $post['liked'] = $this->isLikedPost($post['id_post']);
                if($post['id_user'] && !$post['name'])
                    $post['name']=  Db::getInstance()->getValue('SELECT CONCAT(firstname, " ", lastname) FROM '._DB_PREFIX_.'customer WHERE id_shop ="'.(int)$this->context->shop->id.'" AND id_customer="'.(int)$post['id_user'].'"');
                if($post['id_user'])
                {
                    $customerinfo = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$post['id_user'].'" AND is_customer=1');
                    if($customerinfo && $customerinfo['avata'])
                    {
                        $post['avata'] = $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.$customerinfo['avata'];
                    }
                    else
                       $post['avata'] = $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png'); 
                }
                else
                {
                    $post['avata'] = $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png');
                }
                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);
            }
        }
        $this->smarty->assign(
            array(
                'posts' => $posts,
                'all_comment_link' => $this->getLink('comment'),
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'comment_length' => (int)Configuration::get('YBC_BLOG_COMMENT_LENGTH') ? (int)Configuration::get('YBC_BLOG_COMMENT_LENGTH'):120,
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'page' => 'comment_block',
            )
        );
        return $this->display(__FILE__,'comment_block.tpl');
  }
  public function displayRecommendedModules()
    {
        $cacheDir = dirname(__file__) . '/../../cache/'.$this->name.'/';
        $cacheFile = $cacheDir.'module-list.xml';
        $cacheLifeTime = 24;
        $cacheTime = (int)Configuration::getGlobalValue('ETS_MOD_CACHE_'.$this->name);
        $profileLinks = array(
            'en' => 'https://addons.prestashop.com/en/207_ets-soft',
            'fr' => 'https://addons.prestashop.com/fr/207_ets-soft',
            'it' => 'https://addons.prestashop.com/it/207_ets-soft',
            'es' => 'https://addons.prestashop.com/es/207_ets-soft',
        );
        if(!is_dir($cacheDir))
        {
            @mkdir($cacheDir, 0755,true);
            if ( @file_exists(dirname(__file__).'/index.php')){
                @copy(dirname(__file__).'/index.php', $cacheDir.'index.php');
            }
        }
        if(!file_exists($cacheFile) || !$cacheTime || time()-$cacheTime > $cacheLifeTime * 60 * 60)
        {
            if(file_exists($cacheFile))
                @unlink($cacheFile);
            if($xml = self::file_get_contents($this->shortlink.'ml.xml'))
            {
                $xmlData = @simplexml_load_string($xml);
                if($xmlData && (!isset($xmlData->enable_cache) || (int)$xmlData->enable_cache))
                {
                    @file_put_contents($cacheFile,$xml);
                    Configuration::updateGlobalValue('ETS_MOD_CACHE_'.$this->name,time());
                }
            }
        }
        else
            $xml = Tools::file_get_contents($cacheFile);
        $modules = array();
        $categories = array();
        $categories[] = array('id'=>0,'title' => $this->l('All categories'));
        $enabled = true;
        $iso = Tools::strtolower($this->context->language->iso_code);
        $moduleName = $this->displayName;
        $contactUrl = '';
        if($xml && ($xmlData = @simplexml_load_string($xml)))
        {
            if(isset($xmlData->modules->item) && $xmlData->modules->item)
            {
                foreach($xmlData->modules->item as $arg)
                {
                    if($arg)
                    {
                        if(isset($arg->module_id) && (string)$arg->module_id==$this->name && isset($arg->{'title'.($iso=='en' ? '' : '_'.$iso)}) && (string)$arg->{'title'.($iso=='en' ? '' : '_'.$iso)})
                            $moduleName = (string)$arg->{'title'.($iso=='en' ? '' : '_'.$iso)};
                        if(isset($arg->module_id) && (string)$arg->module_id==$this->name && isset($arg->contact_url) && (string)$arg->contact_url)
                            $contactUrl = $iso!='en' ? str_replace('/en/','/'.$iso.'/',(string)$arg->contact_url) : (string)$arg->contact_url;
                        $temp = array();
                        foreach($arg as $key=>$val)
                        {
                            if($key=='price' || $key=='download')
                                $temp[$key] = (int)$val;
                            elseif($key=='rating')
                            {
                                $rating = (float)$val;
                                if($rating > 0)
                                {
                                    $ratingInt = (int)$rating;
                                    $ratingDec = $rating-$ratingInt;
                                    $startClass = $ratingDec >= 0.5 ? ceil($rating) : ($ratingDec > 0 ? $ratingInt.'5' : $ratingInt);
                                    $temp['ratingClass'] = 'mod-start-'.$startClass;
                                }
                                else
                                    $temp['ratingClass'] = '';
                            }
                            elseif($key=='rating_count')
                                $temp[$key] = (int)$val;
                            else
                                $temp[$key] = (string)strip_tags($val);
                        }
                        if($iso)
                        {
                            if(isset($temp['link_'.$iso]) && isset($temp['link_'.$iso]))
                                $temp['link'] = $temp['link_'.$iso];
                            if(isset($temp['title_'.$iso]) && isset($temp['title_'.$iso]))
                                $temp['title'] = $temp['title_'.$iso];
                            if(isset($temp['desc_'.$iso]) && isset($temp['desc_'.$iso]))
                                $temp['desc'] = $temp['desc_'.$iso];
                        }
                        $modules[] = $temp;
                    }
                }
            }
            if(isset($xmlData->categories->item) && $xmlData->categories->item)
            {
                foreach($xmlData->categories->item as $arg)
                {
                    if($arg)
                    {
                        $temp = array();
                        foreach($arg as $key=>$val)
                        {
                            $temp[$key] = (string)strip_tags($val);
                        }
                        if(isset($temp['title_'.$iso]) && $temp['title_'.$iso])
                                $temp['title'] = $temp['title_'.$iso];
                        $categories[] = $temp;
                    }
                }
            }
        }
        if(isset($xmlData->{'intro_'.$iso}))
            $intro = $xmlData->{'intro_'.$iso};
        else
            $intro = isset($xmlData->intro_en) ? $xmlData->intro_en : false;
        $this->smarty->assign(array(
            'modules' => $modules,
            'enabled' => $enabled,
            'module_name' => $moduleName,
            'categories' => $categories,
            'img_dir' => $this->_path . 'views/img/',
            'intro' => $intro,
            'shortlink' => $this->shortlink,
            'ets_profile_url' => isset($profileLinks[$iso]) ? $profileLinks[$iso] : $profileLinks['en'],
            'trans' => array(
                'txt_must_have' => $this->l('Must-Have'),
                'txt_downloads' => $this->l('Downloads!'),
                'txt_view_all' => $this->l('View all our modules'),
                'txt_fav' => $this->l('Prestashop\'s favourite'),
                'txt_elected' => $this->l('Elected by merchants'),
                'txt_superhero' => $this->l('Superhero Seller'),
                'txt_partner' => $this->l('Module Partner Creator'),
                'txt_contact' => $this->l('Contact us'),
                'txt_close' => $this->l('Close'),
            ),
            'contactUrl' => $contactUrl,
         ));
         echo $this->display(__FILE__, 'module-list.tpl');
         die;
    }
    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create(array(
                "http" => array(
                    "timeout" => $curl_timeout,
                    "max_redirects" => 101,
                    "header" => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
                ),
                "ssl"=>array(
                    "allow_self_signed"=>true,
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            ));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }
  public function hookBlogPositiveAuthor()
  {
        if(!Configuration::get('YBC_BLOG_SHOW_AUTHOR_BLOCK'))
            return '';
        $sql ='SELECT COUNT(p.id_post) as total_post, p.added_by,p.is_customer FROM '._DB_PREFIX_.'ybc_blog_post p
            INNER JOIN '._DB_PREFIX_.'ybc_blog_post_shop ps ON (p.id_post =ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'")
            LEFT JOIN '._DB_PREFIX_.'employee e ON (e.id_employee=p.added_by AND p.is_customer=1)
            LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer =p.added_by AND p.is_customer=0)
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
            WHERE (ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.enabled=1 
            GROUP BY p.added_by,p.is_customer ORDER BY total_post DESC LIMIT 0,'.(Configuration::get('YBC_BLOG_AUTHOR_NUMBER') ? Configuration::get('YBC_BLOG_AUTHOR_NUMBER') : 3).'';
        $authors= Db::getInstance()->executeS($sql);
        if($authors)
        {
            foreach($authors as &$author)
            {
                if($author['is_customer'])
                {
                    $information = Db::getInstance()->getRow('
                    SELECT * FROM '._DB_PREFIX_.'customer c
                    LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee be ON (be.id_employee=c.id_customer AND be.is_customer=1)
                    LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee_lang bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
                    WHERE c.id_customer="'.(int)$author['added_by'].'"');
                    if(!$information['name'])
                        $information['name']=$information['firstname'].' '.$information['lastname'];
                    $author['information']=$information;
                    $author['link']=$this->getLink('blog',array('id_author'=>$author['added_by'],'is_customer'=>1,'alias'=> Tools::link_rewrite($information['name'])));
                    if(isset($information['avata'])&&$information['avata'])
                        $author['avata'] = $this->getBaseLink().'modules/ybc_blog/views/img/avata/'.$information['avata'];
                    else
                       $author['avata']=$this->getBaseLink().'modules/ybc_blog/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png'); 
                }
                else
                {
                    $information = Db::getInstance()->getRow('
                    SELECT * FROM '._DB_PREFIX_.'employee e
                    LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee be ON (be.id_employee=e.id_employee AND be.is_customer=0)
                    LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee_lang bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
                    WHERE e.id_employee="'.(int)$author['added_by'].'"');
                    if(!$information['name'])
                        $information['name']=$information['firstname'].' '.$information['lastname'];
                    $author['information']=$information;
                    $author['link']=$this->getLink('blog',array('id_author'=>$author['added_by'],'is_customer'=>0,'alias'=> Tools::link_rewrite($information['name'])));
                    if(isset($information['avata']) && $information['avata'])
                        $author['avata'] = $this->getBaseLink().'modules/ybc_blog/views/img/avata/'.$information['avata'];
                    else
                       $author['avata']=$this->getBaseLink().'modules/ybc_blog/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png'); 
                }
                $sql ='SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p
                INNER JOIN '._DB_PREFIX_.'ybc_blog_post_shop ps ON (p.id_post=ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'")
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_post_lang pl ON (p.id_post=pl.id_post AND pl.id_lang="'.(int)$this->context->language->id.'")
                WHERE p.enabled=1 AND  p.added_by ="'.(int)$author['added_by'].'" AND p.is_customer="'.(int)$author['is_customer'].'"';
                $author['posts'] = Db::getInstance()->executeS($sql);
                if($author['posts'])
                {
                    foreach($author['posts'] as &$post)
                    {
                        $post['link'] = $this->getLink('blog',array('id_post'=>$post['id_post']));
                    }
                }
            }
        }
        $this->context->smarty->assign(
            array(
                'authors'=>$authors,
                'author_link' => $this->getLink('author'),
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'page' => 'positive_author',
            )
        );
        return $this->display(__FILE__,'positive_author.tpl');
  }
  public function hookBlogCategoriesBlock()
  {       
        if(!Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK'))
            return;
        if((int)Tools::getValue('id_category'))
            $id_category = (int)Tools::getValue('id_category');
        elseif(Tools::getValue('category_url_alias'))
        {
            $id_category = (int)Db::getInstance()->getValue('SELECT cs.id_category FROM '._DB_PREFIX_.'ybc_blog_category_lang cl,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE cs.id_category=cl.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND cl.url_alias ="'.pSQL(Tools::getValue('category_url_alias')).'"');
        }
        elseif($id_post = (int)Tools::getValue('id_post'))
        {
            $post = new Ybc_blog_post_class($id_post);
            $id_category = $post->id_category_default;
        }
        elseif(Tools::getValue('post_url_alias'))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl ,'._DB_PREFIX_.'ybc_blog_post_shop ps  WHERE ps.id_shop="'.(int)$this->context->shop->id.'" AND ps.id_post=pl.id_post AND pl.url_alias ="'.pSQL(Tools::getValue('post_url_alias')).'"');
            if($id_post)
            {
                $post = new Ybc_blog_post_class($id_post);
                $id_category = $post->id_category_default; 
            }
            else
                $id_category=0;
        }
        else    
            $id_category=0;
        $this->smarty->assign(
            array(
                'active' => $id_category,
                'link_view_all'=> $this->getLink('category'),
                'preview_link' => $this->getLink('blog')
            )
        );
        $blockCategTree = $this->getBlogCategoriesTree(0);
        $this->context->smarty->assign('blockCategTree', $blockCategTree);
        $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_.'ybc_blog/views/templates/hook/category-tree-branch.tpl');
        return $this->display(__FILE__, 'categories_block.tpl');
  }
  public function displayBlogCategoriesSub($id_category) {
        $this->smarty->assign(
            array(
                'active' => (int)Tools::getValue('id_category'),
            )
        );
        $blockCategTree = $this->getBlogCategoriesTree($id_category);
        $this->context->smarty->assign('blockCategTree', $blockCategTree);
        $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_.'ybc_blog/views/templates/hook/category-tree-branch.tpl');
        return $this->display(__FILE__, 'categories_block.tpl');
  }
  public function hookBlogRssCategory()
  {
        $blockCategTree = $this->getBlogCategoriesTree(0);
        $this->context->smarty->assign('blockCategTree', $blockCategTree);
        $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_.'ybc_blog/views/templates/hook/rss-category-tree-branch.tpl');
        return $this->display(__FILE__, 'rss_categories_block.tpl');
  }
  public function hookBlogRssAuthor()
  {
        $employees= Db::getInstance()->executeS(
            'SELECT e.id_employee, e.firstname,e.lastname,be.name,bel.description FROM '._DB_PREFIX_.'employee e
            INNER JOIN '._DB_PREFIX_.'ybc_blog_post p ON (p.added_by=e.id_employee AND p.is_customer=0)
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee be ON (e.id_employee=be.id_employee AND be.is_customer=0)
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee_lang bel ON (be.id_employee_post= bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'") 
            WHERE be.status>=0 OR be.status is NULL OR e.id_profile=1
            GROUP BY e.id_employee
        ');
        if($employees)
        {
            foreach($employees as &$employee)
            {
                $employee['name'] = $employee['name'] ? $employee['name'] : $employee['firstname'].' '.$employee['lastname'];
                $employee['link']= $this->getLink('rss',array('id_author'=>$employee['id_employee'],'is_customer'=>0,'alias'=>Tools::link_rewrite($employee['name'])));
            }
        }
        
        $group_authors= explode(',',Configuration::get('YBC_BLOG_GROUP_CUSTOMER_AUTHOR'));
        if($group_authors)
        {
            $customers= Db::getInstance()->executeS(
                'SELECT c.id_customer, c.firstname,c.lastname,be.name,bel.description FROM '._DB_PREFIX_.'customer c
                INNER JOIN '._DB_PREFIX_.'customer_group gs ON (gs.id_customer=c.id_customer)
                INNER JOIN '._DB_PREFIX_.'ybc_blog_post p ON (p.added_by=c.id_customer AND p.is_customer=1)
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee be ON (c.id_customer=be.id_employee AND be.is_customer=1)
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee_lang bel ON (be.id_employee_post= bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
                WHERE (be.status>=0 OR be.status is NULL) AND gs.id_group IN ('.implode(',',array_map('intval',$group_authors)).') GROUP BY c.id_customer
            ');
            if($customers)
            {
                foreach($customers as &$customer)
                {
                    $customer['name'] = $customer['name'] ? $customer['name'] : $customer['firstname'].' '.$customer['lastname'];
                    $customer['link']= $this->getLink('rss',array('id_author'=>$customer['id_customer'],'is_customer'=>1,'alias'=>Tools::link_rewrite($customer['name'])));
                }
            }
            
        }
        else
            $customers=array();
        $this->context->smarty->assign(
            array(
                'employees'=>$employees,
                'customers'=>$customers
            )
        );
        return $this->display(__FILE__,'rss_author_block.tpl');
        
  }
  public function hookBlogTagsBlock()
  {
        if(!Configuration::get('YBC_BLOG_SHOW_TAGS_BLOCK'))
            return;
        $tags = $this->getTags((int)Configuration::get('YBC_BLOG_TAGS_NUMBER') > 0 ? (int)Configuration::get('YBC_BLOG_TAGS_NUMBER') : 20);
        if(is_array($tags) && $tags)
            shuffle($tags);
        $this->smarty->assign(
            array(
                'tags' => $tags
            )
        );
        return $this->display(__FILE__, 'tags_block.tpl');
  }
  public function hookBlogNewsBlock($params)
  {           
        if(isset($params['page']) && $params['page']=='home')
        {
            if(!Configuration::get('YBC_BLOG_SHOW_LATEST_BLOCK_HOME'))
                return '';
            $postCount = (int)Configuration::get('YBC_BLOG_LATEST_POST_NUMBER_HOME') > 0 ? (int)Configuration::get('YBC_BLOG_LATEST_POST_NUMBER_HOME') : 5;
            $this->context->smarty->assign(
                array(
                    'display_desc' => Configuration::get('YBC_BLOG_HOME_DISPLAY_DESC'),
                )
            );
        }
        else
        {
            if(!Configuration::get('YBC_BLOG_SHOW_LATEST_NEWS_BLOCK'))
                return '';
            $this->context->smarty->assign(
                array(
                    'display_desc' => Configuration::get('YBC_BLOG_SIDEBAR_DISPLAY_DESC'),
                )
            );
            $postCount = (int)Configuration::get('YBC_BLOG_LATES_POST_NUMBER') > 0 ? (int)Configuration::get('YBC_BLOG_LATES_POST_NUMBER') : 5;
        }  
        $posts = $this->getPostsWithFilter(' AND p.enabled=1','p.datetime_active DESC,',0,$postCount);
        if($posts)
        {
            foreach($posts as $key => &$post)
            {
                $post['link'] = $this->getLink('blog',array('id_post' => $post['id_post']));
                if($post['thumb'])
                    $post['thumb'] = $this->_path.'views/img/post/thumb/'.$post['thumb'];
                $post['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                $post['liked'] = $this->isLikedPost($post['id_post']);
                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);
                
            }
            unset($key); 
        }                           
        $this->smarty->assign(
            array(
                'posts' => $posts,
                'latest_link' => $this->getLink('blog',array('latest' => true)),
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'hook' => 'homeblog',
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'page' => isset($params['page']) && $params['page'] ? $params['page'] : false,
            )
        );
        return $this->display(__FILE__, 'latest_posts_block.tpl');
  }
  public function hookDisplayHome()
  { 
        $homepages=array(
            'homepage_new'=>Configuration::get('YBC_BLOG_SHOW_LATEST_BLOCK_HOME') ? $this->hookBlogNewsBlock(array('page'=>'home')):'',
            'homepage_popular' => Configuration::get('YBC_BLOG_SHOW_POPULAR_BLOCK_HOME') ? $this->hookBlogPopularPostsBlock(array('page'=>'home')):'',
            'homepage_featured' => Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK_HOME') ? $this->hookBlogFeaturedPostsBlock(array('page'=>'home')):'',
            'homepage_categories' => Configuration::get('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME') ? $this->hookBlogCategoryBlock(array('page'=>'home')):'',
            'homepage_gallery' => Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME') ? $this->hookBlogGalleryBlock(array('page'=>'home')):'',
        );
        $position_homepages= explode(',',Configuration::get('YBC_BLOG_POSITION_HOMEPAGE') ? Configuration::get('YBC_BLOG_POSITION_HOMEPAGE'):'homepage_new,homepage_popular,homepage_featured,homepage_categories,homepage_gallery');
        $this->context->smarty->assign(
            array(
                'position_homepages' => $position_homepages,
                'homepages'=>$homepages
            )
        );
        return $this->display(__FILE__, 'home_blocks.tpl');
  }
  public function hookDisplayFullBlogHome()
  {
        $homepages=array(
            'homepage_new'=>Configuration::get('YBC_BLOG_SHOW_LATEST_BLOCK_HOME') ? $this->hookBlogNewsBlock(array('page'=>'home')):'',
            'homepage_popular' => Configuration::get('YBC_BLOG_SHOW_POPULAR_BLOCK_HOME') ? $this->hookBlogPopularPostsBlock(array('page'=>'home')):'',
            'homepage_featured' => Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK_HOME') ? $this->hookBlogFeaturedPostsBlock(array('page'=>'home')):'',
            'homepage_categories' => Configuration::get('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME') ? $this->hookBlogCategoryBlock(array('page'=>'home')):'',
            'homepage_gallery' => Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME') ? $this->hookBlogGalleryBlock(array('page'=>'home')):'',
        );
        $position_homepages= explode(',',Configuration::get('YBC_BLOG_POSITION_HOMEPAGE') ? Configuration::get('YBC_BLOG_POSITION_HOMEPAGE'):'homepage_new,homepage_popular,homepage_featured,homepage_categories,homepage_gallery');
        $this->context->smarty->assign(
            array(
                'position_homepages' => $position_homepages,
                'homepages'=>$homepages
            )
        );
        return $this->display(__FILE__, 'home_blocks.tpl');
  }
  public function hookBlogPopularPostsBlock($params)
  {
        if(isset($params['page']) && $params['page']=='home')
        {
            $postCount = (int)Configuration::get('YBC_BLOG_POPULAR_POST_NUMBER_HOME') > 0 ? (int)Configuration::get('YBC_BLOG_POPULAR_POST_NUMBER_HOME') : 5;
            $this->context->smarty->assign(
                array(
                    'display_desc' => Configuration::get('YBC_BLOG_HOME_DISPLAY_DESC'),
                )
            );
        }
        else
        {
            $this->context->smarty->assign(
                array(
                    'display_desc' => Configuration::get('YBC_BLOG_SIDEBAR_DISPLAY_DESC'),
                )
            );
            $postCount = (int)Configuration::get('YBC_BLOG_PUPULAR_POST_NUMBER') > 0 ? (int)Configuration::get('YBC_BLOG_PUPULAR_POST_NUMBER') : 5;
        }
                                    
        $posts = $this->getPostsWithFilter(' AND p.enabled=1','p.click_number desc,',0,$postCount);
        if($posts)
            foreach($posts as &$post)
            {
                $post['link'] = $this->getLink('blog',array('id_post' => $post['id_post']));
                if($post['thumb'])
                    $post['thumb'] = $this->_path.'views/img/post/thumb/'.$post['thumb'];
                $post['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                $post['liked'] = $this->isLikedPost($post['id_post']);
                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);
            }
        $this->smarty->assign(
            array(
                'posts' => $posts,
                'popular_link' => $this->getLink('blog',array('popular' => true)),
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'page' => isset($params['page']) && $params['page'] ? $params['page'] : false,
            )
        );
        return $this->display(__FILE__, 'popular_posts_block.tpl');
  }
  public function hookBlogFeaturedPostsBlock($params)
  {
        if(isset($params['page']) && $params['page']=='home')
        {
            $this->context->smarty->assign(
                array(
                    'display_desc' => Configuration::get('YBC_BLOG_HOME_DISPLAY_DESC'),
                )
            );
            $postCount = (int)Configuration::get('YBC_BLOG_FEATURED_POST_NUMBER_HOME') > 0 ? (int)Configuration::get('YBC_BLOG_FEATURED_POST_NUMBER_HOME') : 5;
        }    
        else
        {
            $this->context->smarty->assign(
                array(
                    'display_desc' => Configuration::get('YBC_BLOG_SIDEBAR_DISPLAY_DESC'),
                )
            );
            $postCount = (int)Configuration::get('YBC_BLOG_FEATURED_POST_NUMBER') > 0 ? (int)Configuration::get('YBC_BLOG_FEATURED_POST_NUMBER') : 5;
        }                 
        $posts = $this->getPostsWithFilter(' AND p.enabled=1 && p.is_featured=1',$this->sort,0,$postCount);
        if($posts)
            foreach($posts as &$post)
            {
                $post['link'] = $this->getLink('blog',array('id_post' => $post['id_post']));
                if($post['thumb'])
                    $post['thumb'] = $this->_path.'views/img/post/thumb/'.$post['thumb'];
                $post['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                $post['liked'] = $this->isLikedPost($post['id_post']);
                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);
            }
        $this->smarty->assign(
            array(
                'posts' => $posts,
                'featured_link' => $this->getLink('blog',array('featured' => true)),
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'page' => isset($params['page']) && $params['page'] ? $params['page'] : false,
            )
        );
        return $this->display(__FILE__, 'featured_posts_block.tpl');
  }
  public function hookBlogSlidersBlock()
  {
        if(!Configuration::get('YBC_BLOG_SHOW_SLIDER'))
            return;
        $slides = $this->getSlidesWithFilter(' AND s.enabled=1','s.sort_order asc, s.id_slide asc,');            
        if($slides)
            foreach($slides as &$slide)
            {
                if($slide['image'])
                    $slide['image'] = $this->_path.'views/img/slide/'.$slide['image'];
            }
        $this->smarty->assign(
            array(
                'loading_img' => $this->_path.'views/img/img/loading.gif',
                'slides' => $slides,
                'nivoTheme' => 'default',
                'nivoAutoPlay' => (int)Configuration::get('YBC_BLOG_SLIDER_AUTO_PLAY') ? true : false,
            )
        );
        return $this->display(__FILE__, 'slider_block.tpl');
  }
  public function hookBlogGalleryBlock($params)
  {                
        if(isset($params['page']) && $params['page']=='home')
        {
            if(!Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK_HOME'))
                return '';
            $postCount = (int)Configuration::get('YBC_BLOG_GALLERY_POST_NUMBER_HOME') > 0 ? (int)Configuration::get('YBC_BLOG_GALLERY_POST_NUMBER_HOME') : 10;
        }    
        else
        {
            if(!Configuration::get('YBC_BLOG_SHOW_GALLERY_BLOCK'))
                return '';
            $postCount = (int)Configuration::get('YBC_BLOG_GALLERY_POST_NUMBER') > 0 ? (int)Configuration::get('YBC_BLOG_GALLERY_POST_NUMBER') : 10;
        }
        $galleries = $this->getGalleriesWithFilter(' AND g.enabled=1  AND g.is_featured=1','g.sort_order asc, g.id_gallery asc,',0,$postCount);
        if($galleries)
            foreach($galleries as &$gallery)
            {
                if($gallery['thumb'])
                    $gallery['thumb'] =  $this->_path.'views/img/gallery/thumb/'.$gallery['thumb'];   
                else
                     $gallery['thumb']=$this->_path.'views/img/gallery/'.$gallery['image']; 
                if($gallery['image'])
                {                       
                    $gallery['image'] = $this->_path.'views/img/gallery/'.$gallery['image'];    
                }  
                      
            }      
        $this->smarty->assign(
            array(
                'galleries' => $galleries,
                'gallery_link' => $this->getLink('gallery',array()),                    
                'page' => isset($params['page']) && $params['page'] ? $params['page'] : false,
            )
        );
        return $this->display(__FILE__, 'gallery_block.tpl');
  }
  /**
    * polls
  */
    private function _postPolls()
    {
        /**
        * Change status 
        */
        if(Tools::isSubmit('change_enabled'))
        {
            $status = (int)Tools::getValue('change_enabled') ?  1 : 0;
            $field = Tools::getValue('field');
            $id_polls = (int)Tools::getValue('id_polls');   
            $polls_class = new Ybc_blog_polls_class($id_polls);   
            Hook::exec('actionUpdateBlog', array(
                'id_post' =>(int)$polls_class->id_post,
            ));	      
            if($id_polls)
            {
                $this->changeStatus('polls',$field,$id_polls,$status);
                if($status==1)
                    $title = $this->l('Click to mark this as unhelpful');
                else
                    $title = $this->l('Click to mark this as helpful');
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_polls,
                        'enabled' => $status,
                        'field' => $field,
                        'message' =>  $this->displaySuccessMessage($this->l('The status has been successfully updated')),
                        'messageType'=>'success',
                        'title'=>$title,
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=polls&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_polls='.$id_polls,
                    )));
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=polls&list=true');
            }
        }            
        /**
        * Delete comment 
        */ 
        if(Tools::isSubmit('del'))
        {
            $id_polls = (int)Tools::getValue('id_polls');
            if($this->itemExists('polls','id_polls',$id_polls))
            {      
                $polls_class = new Ybc_blog_polls_class($id_polls);
                Hook::exec('actionUpdateBlog', array(
                    'id_post' =>(int)$polls_class->id_post,
                ));	 
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_polls WHERE id_polls='.(int)$id_polls);
                  Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=2&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=polls&list=true');
            }                   
        }                  
        /**
        * form send mail
        */
        if(Tools::isSubmit('sendmailform') &&$id_polls=Tools::getValue('id_polls'))
        {
            $polls_class = new Ybc_blog_polls_class($id_polls);
            $this->context->smarty->assign(
                array(
                    'polls_class' => $polls_class,
                    
                )
            );
            if(Tools::getValue('ajax'))
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'html_form' => $this->display(__FILE__,'form_send_mail_polls.tpl'),
                        )
                    )
                );   
            }
            return $this->display(__FILE__,'form_send_mail_polls.tpl');
        }
        if(Tools::isSubmit('send_mail_polls') && $id_polls=Tools::getValue('id_polls'))
        {
            $errors=array();
            if(trim(Tools::getValue('message_email'))=='')
            {
                $errors[]=$this->l('Message is required');
            }
            if(trim(Tools::getValue('subject_email'))=='')
                $errors[]=$this->l('Subject is required');
            if(!$errors)
            {
                $polls_class = new Ybc_blog_polls_class($id_polls);
                $template_customer_vars=array(
                    '{message_email}'  => Tools::getValue('message_email'),
                    '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                    '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                );
                Mail::Send(
        			Context::getContext()->language->id,
        			'reply_polls_customer',
        			Tools::getValue('subject_email'),
        			$template_customer_vars,
    		        $polls_class->email,
        			$polls_class->name,
        			null,
        			null,
        			null,
        			null,
        			dirname(__FILE__).'/mails/'
                );
                die(Tools::jsonEncode(
                    array(
                        'message' =>$this->displaySuccessMessage($this->l('Email was sent successfully')),
                        'messageType'=>'success'
                    )
                ));
            }
            else
            {
                die(Tools::jsonEncode(
                    array(
                        'message' =>$this->displayError($errors),
                        'messageType'=>'error'
                    )
                ));
            }
            
        }
    }
   
  /**
   * Comments 
   */
  private function _postComment()
  {
        $errors = array();
        $id_comment = (int)Tools::getValue('id_comment');
        if(Tools::getValue('list')!='true' && ($id_comment && !$this->itemExists('comment','id_comment',$id_comment) || !$id_comment))            
            Tools::redirectAdmin($this->baseAdminPath);
        if(Tools::getValue('submitBulkActionMessage') && Tools::getValue('message_readed') && $bulk_action_message=Tools::getValue('bulk_action_message'))
        {
            if($bulk_action_message=='delete_selected')
            {
                foreach(Tools::getValue('message_readed') as $id_comment => $value)
                {
                    if($value)
                    {
                        Hook::exec('actionUpdateBlog', array(
                            'id_comment' => (int)$id_comment,
                        ));
                        if($this->itemExists('comment','id_comment',$id_comment))
                            $this->_deleteComment($id_comment);
                    }
                }
                die(Tools::jsonEncode(
                    array(
                        'url_reload' => $this->context->link->getAdminLink('AdminModules', true).'&conf=2&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&list=true',
                    )
                ));
            }
            else
            {
                if($bulk_action_message=='mark_as_approved')
                {
                    $value_field=1;
                    $field='approved';
                }
                elseif($bulk_action_message=='mark_as_unapproved')
                {
                    $value_field=0;
                    $field='approved';
                }
                elseif($bulk_action_message=='mark_as_read')
                {
                    $value_field=1;
                    $field='viewed';
                }
                else
                {
                    $value_field=0;
                    $field='viewed';
                }
                foreach(Tools::getValue('message_readed') as $id_comment => $value)
                {
                    if($value)
                    {
                        Hook::exec('actionUpdateBlog', array(
                            'id_comment' => (int)$id_comment,
                        ));
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_comment SET `'.pSQL($field).'`='.(int)$value_field.' WHERE id_comment='.(int)$id_comment);
                    }
                }
                die(Tools::jsonEncode(
                    array(
                        'url_reload' => $this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&list=true',
                    )
                ));
            }
        }
        /**
         * Change status 
         */
         if(Tools::isSubmit('change_enabled'))
         {
            $status = (int)Tools::getValue('change_enabled') ?  1 : 0;
            $field = Tools::getValue('field');
            $id_comment = (int)Tools::getValue('id_comment');   
            $comment = new Ybc_blog_comment_class($id_comment);       
            $post= new Ybc_blog_post_class($comment->id_post);  
            Hook::exec('actionUpdateBlog', array(
                'id_post' => (int)$comment->id_post,
            )); 
            if($field == 'approved' || $field == 'reported' && $id_comment)
            {
                $this->changeStatus('comment',$field,$id_comment,$status);
                if($field=='approved' && $status==1 && Configuration::get('YBC_BLOG_ENABLE_MAIL_APPROVED'))
                {
                   Mail::Send(
                        $this->context->language->id, 
                        'approved_comment',
                        Mail::l('Your comment has been approved'),
                        array('{customer_name}' => $comment->name, '{email}' => $comment->email,'{rating}' => ' '.($comment->rating != 1 ? $this->l('stars','blog') : $this->l('star','blog')), '{subject}' => $comment->subject, '{comment}'=>$comment->comment,'{post_title}'=>$post->title[$this->context->language->id],'{post_link}' => $this->getLink('blog', array('id_post' => $comment->id_post)),'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                        $comment->email, null, null, null, null, null, 
                        dirname(__FILE__).'/mails/', 
                        false, $this->context->shop->id
                    ); 
                }
                if($field=='approved')
                {
                    if($status==1)
                        $title = $this->l('Click to mark as unapproved');
                    else
                        $title = $this->l('Click to mark as approved');
                }
                else
                {
                    if($status==1)
                        $title = $this->l('Click to mark as unreported');
                    else
                        $title = $this->l('Click to mark as reported');
                }
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_comment,
                        'enabled' => $status,
                        'field' => $field,
                        'message' => $field == 'approved' ? $this->displaySuccessMessage($this->l('The status has been successfully updated')):$this->displaySuccessMessage($this->l('The status has been successfully updated')),
                        'messageType'=>'success',
                        'title'=>$title,
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_comment='.$id_comment,
                    )));
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&list=true');
            }
         }            
        /**
         * Delete comment 
         */ 
         if(Tools::isSubmit('del'))
         {
            $id_comment = (int)Tools::getValue('id_comment');
            Hook::exec('actionUpdateBlog', array(
                'id_comment' => (int)$id_comment,
            ));
            if(!$this->itemExists('comment','id_comment',$id_comment))
                $errors[] = $this->l('Comment does not exist');
            elseif($this->_deleteComment($id_comment))
            {                
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=2&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&list=true');
            }                
            else
                $errors[] = $this->l('Could not delete the comment. Please try again');    
         }                  
        /**
         * Save comment 
         */
        if(Tools::isSubmit('saveComment'))
        {
            if($id_comment && $this->itemExists('comment','id_comment',$id_comment))
            {
                $comment = new Ybc_blog_comment_class($id_comment);    
                $post= new Ybc_blog_post_class($comment->id_post);   
                Hook::exec('actionUpdateBlog', array(
                    'id_post' => (int)$comment->id_post,
                ));               
            }
            else
            {
                $errors[] = $this->l('Comment does not exist');
            }
            $approved = $comment->approved;
            $comment->subject = trim(Tools::getValue('subject',''));
            $comment->comment = trim(Tools::getValue('comment',''));
            $comment->reply = trim(Tools::getValue('reply',''));
            $comment->rating = trim(Tools::getValue('rating',0)) >=0 && trim(Tools::getValue('rating',0)) <=5 ? trim(Tools::getValue('rating',0)) : 0;
            $comment->approved = trim(Tools::getValue('approved',1)) ? 1 : 0;
            $comment->reported = trim(Tools::getValue('reported',0)) ? 1 : 0;
            $comment->replied_by = (int)$this->context->employee->id;
            if(Tools::strlen($comment->subject) < 10)
                $errors[] = $this->l('Subject need to be at least 10 characters');
            if(Tools::strlen($comment->subject) >300)
                $errors[] = $this->l('Subject can not be longer than 300 characters');  
            if(!Validate::isCleanHtml($comment->subject,false))
                $errors[] = $this->l('Subject need to be clean HTML');
            if(Tools::strlen($comment->comment) < 20)
                $errors[] = $this->l('Comment need to be at least 20 characters');
            if(!Validate::isCleanHtml($comment->comment,false))
                $errors[] = $this->l('Comment need to be clean HTML');
            if(Tools::strlen($comment->comment) >2000)
                $errors[] = $this->l('Comment can not be longer than 2000 characters');                  
            
            if(!Validate::isCleanHtml($comment->reply,false))
                $errors[] = $this->l('Reply need to be clean HTML');
            if(Tools::strlen($comment->reply) >2000)
                $errors[] = $this->l('Reply can not be longer than 2000 characters');
            if(!$errors)
            {
                if(!$comment->update())
                {                        
                    $errors[] = $this->displayError($this->l('The comment could not be updated.'));
                }
                else
                {
                    if($approved!=$comment->$approved && $comment->approved==1 && Configuration::get('YBC_BLOG_ENABLE_MAIL_APPROVED'))
                    {
                        Mail::Send(
                            $this->context->language->id, 
                            'approved_comment',
                            Mail::l('Your comment has been approved'),
                            array('{customer_name}' => $comment->name, '{email}' => $comment->email,'{rating}' => ' '.($comment->rating != 1 ? $this->l('stars','blog') : $this->l('star','blog')), '{subject}' => $comment->subject, '{comment}'=>$comment->comment,'{post_title}'=>$post->title[$this->context->language->id],'{post_link}' => $this->getLink('blog', array('id_post' => $comment->id_post)),'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                            $comment->email, null, null, null, null, null, 
                            dirname(__FILE__).'/mails/', 
                            false, $this->context->shop->id
                        );
                    }    
                }        					                
            }
         }
         if(Tools::isSubmit('ajax'))
         {
            die(Tools::jsonEncode(
                array(
                    'messageType' => $errors ? 'error' : 'success',
                    'message' => $errors ? $this->displayError($errors) : $this->displayConfirmation($this->l('Comment saved')),
                )
            ));
         }
         if (count($errors))
         {                
            $this->errorMessage = $this->displayError($errors);  
         }
         elseif (Tools::isSubmit('saveComment') && Tools::isSubmit('id_comment'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_comment='.Tools::getValue('id_comment').'&control=comment');
		 elseif (Tools::isSubmit('saveComment'))
         {
            Tools::redirectAdmin($this->baseAdminPath);
         }
   }
   public function renderPollsForm()
   {
        //List 
        if(trim(Tools::getValue('list'))=='true')
        {
            $fields_list = array(
                'id_polls' => array(
                    'title' => $this->l('Vote ID'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'name' => array(
                    'title' => $this->l('Name'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'email' => array(
                    'title' => $this->l('Email'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'title'=>array(
                    'title'=>$this->l('Blog post'),
                    'type' => 'text',
                    'filter' => true,  
                    'strip_tag'=>false,
                ),
                'feedback'=>array(
                    'title'=>$this->l('Feedback'),
                    'type' => 'text',
                    'filter' => true,
                ),
                'polls' => array(
                    'title' => $this->l('Helpful'),
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                )
            );
            //Filter
            $filter = "";
            if(trim(Tools::getValue('id_polls'))!='')
                $filter .= " AND po.id_polls = ".(int)trim(urldecode(Tools::getValue('id_polls')));
            if(trim(Tools::getValue('feedback'))!='')
                $filter .= " AND po.feedback like '%".addslashes(trim(urldecode(Tools::getValue('feedback'))))."%'";             
            if(trim(Tools::getValue('name'))!='')
                $filter .= " AND po.name like '%".addslashes(trim(urldecode(Tools::getValue('name'))))."%'";
            if(trim(Tools::getValue('polls'))!='')
                $filter .= " AND po.polls = ".(int)trim(urldecode(Tools::getValue('polls')));
            if(trim(Tools::getValue('title'))!='')
                $filter .= " AND pl.title like '%".pSQL(Tools::getValue('title'))."%'";
            //Sort
            
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort = trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = 'po.id_polls DESC,';
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countPollsWithFilter($filter,false);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=polls&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $polls = $this->getPollsWithFilter($filter, $sort, $start, $paggination->limit,false);
            if($polls)
            {
                foreach($polls as &$poll)
                {
                    $poll['title'] = '<a target="_blank" href="'.$this->getLink('blog',array('id_post'=>$poll['id_post'])).'" title="'.$poll['title'].'">'.$poll['title'].'</a>';
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_polls',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=polls',
                'identifier' => 'id_polls',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Polls'),
                'fields_list' => $fields_list,
                'field_values' => $polls,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' => trim(Tools::getValue('id_polls'))!='' || trim(Tools::getValue('feedback'))!=''|| trim(Tools::getValue('name'))!='' || trim(Tools::getValue('polls'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'show_add_new' => false,
                'sort'=>Tools::getValue('sort','id_polls'),
                'sort_type'=>Tools::getValue('sort_type','desc'),
                'link_customer' => $this->context->link->getAdminLink('AdminCustomers').'&updatecustomer'
            );            
            return $this->_html .= $this->renderList($listData);      
        }
        //Form
        
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Manage polls'),				
				),
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Subject'),
						'name' => 'subject',    					 
                        'required' => true,
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'desc' => ($id_comment = (int)Tools::getValue('id_comment')) && ($comment = $this->getCommentById($id_comment)) ? $this->displayCommentInfo($comment,(int)$comment['id_user']?$this->context->link->getAdminLink('AdminCustomers').'&id_customer='.(int)$comment['id_user'].'&updatecustomer':'#',$this->getLink('blog',array('id_post' => (int)$comment['id_post']))) : '',	                    
					), 
                    array(
    					'type' => 'select',
    					'label' => $this->l('Rating'),
    					'name' => 'rating',
                        'options' => array(
                			 'query' => array(                                
                                    array(
                                        'id_option' => '0', 
                                        'name' => $this->l('No ratings')
                                    ),
                                    array(
                                        'id_option' => '1', 
                                        'name' => '1 '. $this->l('rating')
                                    ),
                                    array(
                                        'id_option' => '2', 
                                        'name' => '2 '. $this->l('ratings')
                                    ),
                                    array(
                                        'id_option' => '3', 
                                        'name' => '3 '. $this->l('ratings')
                                    ),
                                    array(
                                        'id_option' => '4', 
                                        'name' => '4 '. $this->l('ratings')
                                    ),
                                    array(
                                        'id_option' => '5', 
                                        'name' => '5 '. $this->l('ratings')
                                    )
                                ),                             
                             'id' => 'id_option',
                			 'name' => 'name'  
                        )                
    				),
                    array(
						'type' => 'textarea',
						'label' => $this->l('Comment'),
						'name' => 'comment',                            
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'required' => true						
					),
                    //array(
//						'type' => 'textarea',
//						'label' => $this->l('Reply to this comment'),
//						'name' => 'reply',                            
//                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'                           					
//					),                        
                    array(
						'type' => 'switch',
						'label' => $this->l('Approved'),
						'name' => 'approved',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Not reported as abused'),
						'name' => 'reported',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveComment';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsValues(Ybc_blog_defines::$commentFields,'id_comment','Ybc_blog_comment_class','saveComment'),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'cancel_url' => $this->baseAdminPath.'&control=comment&list=true'
		);            
        if(Tools::isSubmit('id_comment') && $this->itemExists('comment','id_comment',(int)Tools::getValue('id_comment')))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_comment');                
        }
        
		$helper->override_folder = '/'; 
        $this->_html .= $helper->generateForm(array($fields_form));			
    }
   public function renderCommentsForm()
   {
        //List 
        if(trim(Tools::getValue('list'))=='true')
        {
            $fields_list = array(
                'id_comment' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'subject' => array(
                    'title' => $this->l('Subject'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,                        
                ),                    
                'rating' => array(
                    'title' => $this->l('Rating'),
                    //'width' => 100,
                    'type' => 'select',
                    'sort' => true,
                    'filter' => true,
                    'rating_field' => true,
                    'filter_list' => array(
                        'id_option' => 'rating',
                        'value' => 'stars',
                        'list' => array(
                            0 => array(
                                'rating' => 0,
                                'stars' => $this->l('No reviews')
                            ),
                            1 => array(
                                'rating' => 1,
                                'stars' => '1 '.$this->l('star')
                            ),
                            2 => array(
                                'rating' => 2,
                                'stars' => '2 '.$this->l('stars')
                            ),
                            3 => array(
                                'rating' => 3,
                                'stars' => '3 '.$this->l('stars')
                            ),
                            4 => array(
                                'rating' => 4,
                                'stars' => '4 '.$this->l('stars')
                            ),
                            5 => array(
                                'rating' => 5,
                                'stars' => '5 '.$this->l('stars')
                            ),
                        )
                    )
                ),
                'name' => array(
                    'title' => $this->l('Customer'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'title'=>array(
                    'title'=>$this->l('Blog post'),
                    'type' => 'text',
                    'filter' => true,  
                    'strip_tag'=>false,
                ),
                'count_reply'=>array(
                    'title'=>$this->l('Replies'),
                    'type' => 'text',
                ),
                'approved' => array(
                    'title' => $this->l('Status'),
                    //'width' => 50,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Approved')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('Pending')
                            )
                        )
                    )
                ),
                'reported' => array(
                    'title' => $this->l('Not reported as abused'),
                    //'width' => 50,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                )
            );
            //Filter
            $filter = "";
            if(trim(Tools::getValue('id_comment'))!='')
                $filter .= " AND bc.id_comment = ".(int)trim(urldecode(Tools::getValue('id_comment')));
            if(trim(Tools::getValue('comment'))!='')
                $filter .= " AND bc.comment like '%".addslashes(trim(urldecode(Tools::getValue('comment'))))."%'";
            if(trim(Tools::getValue('subject'))!='')
                $filter .= " AND (bc.subject like '%".addslashes(trim(urldecode(Tools::getValue('subject'))))."%' OR bc.comment like '%".addslashes(trim(urldecode(Tools::getValue('subject'))))."%')";
            if(trim(Tools::getValue('rating'))!='')
                $filter .= " AND bc.rating = ".(int)trim(urldecode(Tools::getValue('rating')));                
            if(trim(Tools::getValue('name'))!='')
                $filter .= " AND bc.name like '%".addslashes(trim(urldecode(Tools::getValue('name'))))."%'";
            if(trim(Tools::getValue('approved'))!='')
                $filter .= " AND bc.approved = ".(int)trim(urldecode(Tools::getValue('approved')));
            if(trim(Tools::getValue('reported'))!='')
                $filter .= " AND bc.reported = ".(int)trim(urldecode(Tools::getValue('reported')));
            if(trim(Tools::getValue('title'))!='')
                $filter .= " AND pl.title like '%".pSQL(Tools::getValue('title'))."%'";
            //Sort
            
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort = trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = 'bc.id_comment desc,';
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countCommentsWithFilter($filter,false);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $comments = $this->getCommentsWithFilter($filter, $sort, $start, $paggination->limit,false);
            if($comments)
            {
                foreach($comments as &$comment)
                {
                    $comment['view_url'] = $this->getLink('blog', array('id_post' => $comment['id_post'])).'#blog_comment_line_'.$comment['id_comment'];
                    $comment['view_text'] = $this->l('View in post');
                    $comment['child_view_url'] = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment_reply&id_comment='.(int)$comment['id_comment'];
                    $replies = Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_comment='.(int)$comment['id_comment']);
                    $replies_no_approved = Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_reply WHERE approved=0 AND id_comment='.(int)$comment['id_comment']);
                    if($replies)
                        $comment['count_reply'] = $replies. ($replies_no_approved ? ' ('.$replies_no_approved.' '.$this->l('pending').')':'');
                    else
                        $comment['count_reply']=0;
                    $comment['title'] = '<a target="_blank" href="'.$this->getLink('blog',array('id_post'=>$comment['id_post'])).'" title="'.$comment['title'].'">'.$comment['title'].'</a>';
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_comment',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment',
                'identifier' => 'id_comment',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Comments'),
                'fields_list' => $fields_list,
                'field_values' => $comments,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' => trim(Tools::getValue('id_comment'))!='' || trim(Tools::getValue('comment'))!='' || trim(Tools::getValue('rating'))!='' || trim(Tools::getValue('subject'))!='' || trim(Tools::getValue('customer'))!='' || trim(Tools::getValue('approved'))!='' || trim(Tools::getValue('reported'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'show_add_new' => false,
                'sort'=>Tools::getValue('sort','id_comment'),
                'sort_type'=>Tools::getValue('sort_type','desc'),
            );            
            return $this->_html .= $this->renderList($listData);      
        }
        //Form
        
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Manage Comments'),				
				),
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Subject'),
						'name' => 'subject',    					 
                        'required' => true,
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'desc' => ($id_comment = (int)Tools::getValue('id_comment')) && ($comment = $this->getCommentById($id_comment)) ? $this->displayCommentInfo($comment,(int)$comment['id_user']?$this->context->link->getAdminLink('AdminCustomers').'&id_customer='.(int)$comment['id_user'].'&updatecustomer':'#',$this->getLink('blog',array('id_post' => (int)$comment['id_post']))) : '',	                    
					), 
                    array(
    					'type' => 'select',
    					'label' => $this->l('Rating'),
    					'name' => 'rating',
                        'options' => array(
                			 'query' => array(                                
                                    array(
                                        'id_option' => '0', 
                                        'name' => $this->l('No ratings')
                                    ),
                                    array(
                                        'id_option' => '1', 
                                        'name' => '1 '. $this->l('rating')
                                    ),
                                    array(
                                        'id_option' => '2', 
                                        'name' => '2 '. $this->l('ratings')
                                    ),
                                    array(
                                        'id_option' => '3', 
                                        'name' => '3 '. $this->l('ratings')
                                    ),
                                    array(
                                        'id_option' => '4', 
                                        'name' => '4 '. $this->l('ratings')
                                    ),
                                    array(
                                        'id_option' => '5', 
                                        'name' => '5 '. $this->l('ratings')
                                    )
                                ),                             
                             'id' => 'id_option',
                			 'name' => 'name'  
                        )                
    				),
                    array(
						'type' => 'textarea',
						'label' => $this->l('Comment'),
						'name' => 'comment',                            
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'required' => true						
					),
                    //array(
//						'type' => 'textarea',
//						'label' => $this->l('Reply to this comment'),
//						'name' => 'reply',                            
//                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'                           					
//					),                        
                    array(
						'type' => 'switch',
						'label' => $this->l('Approved'),
						'name' => 'approved',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Not reported as abused'),
						'name' => 'reported',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveComment';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsValues(Ybc_blog_defines::$commentFields,'id_comment','Ybc_blog_comment_class','saveComment'),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'cancel_url' => $this->baseAdminPath.'&control=comment&list=true'
		);            
        if(Tools::isSubmit('id_comment') && $this->itemExists('comment','id_comment',(int)Tools::getValue('id_comment')))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_comment');                
        }
        
		$helper->override_folder = '/'; 
        $this->_html .= $helper->generateForm(array($fields_form));			
    }
    public function displayCommentInfo($comment, $customerLink, $postLink)
    {
        $this->smarty->assign(array(
            'comment' => $comment,
            'customerLink' => $customerLink,
            'postLink' => $postLink,
        ));
        return $this->display(__FILE__,'comment_info.tpl');
    }
    public function renderAuthorForm()
    {
        return $this->_html .= $this->displayTabAuthor().$this->renderCustomerForm(true).$this->renderEmployeeFrom(true).$this->renderSettingCustomer();
    }
    public function renderCustomerForm($list=false)
    {
        if(!Configuration::get('YBC_BLOG_ALLOW_CUSTOMER_AUTHOR'))
            return false;
        //List 
        if(trim(Tools::getValue('list'))=='true' || $list)
        {
            $fields_list = array(
                'id_customer' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'avata' => array(
                    'title' => $this->l('Avatar'),
                    //'width' => 100,
                    'type' => 'text',
                    'strip_tag' => false,       
                ),                     
                'name' => array(
                    'title' => $this->l('Name'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'strip_tag' => false,
                    'filter' => true
                    
                ), 
                'email' => array(
                    'title' => $this->l('Email'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ), 
                'description' => array(
                    'title' => $this->l('Introduction'),
                    //'width' => 140,
                    'type' => 'text',
                    'strip_tag' => false,
                    'filter'=>true  
                ),
                'has_post'=> array(
                    'title' => $this->l('Have posts'),
                    'type' => 'active',
                    'filter'=>true, 
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => '',
                                'title' => '--'
                            ),
                            1 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            2 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                ),
                'total_post'=> array(
                    'title' => $this->l('Total posts'),
                    //'width' => 140,
                    'sort' => true,
                    'type' => 'int',
                    'filter'=>true, 
                ), 
                'status' => array(
                    'title'=> $this->l('Status'),
                    //'width' => 80,
                    'type' => 'active',
                    'filter'=>true,
                    'sort' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Activated')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('Suspended')
                            ),
                            2 => array(
                                'enabled' => -1,
                                'title' => $this->l('Suspended and hide posts')
                            )
                        )
                    )
                )              
            );
            //Filter
            $filter = "";
            $sort = "";
            $having='';
            if(Tools::getValue('control')=='customer')
            {
                if(trim(Tools::getValue('id_customer'))!='')
                $filter .= " AND c.id_customer = ".(int)trim(urldecode(Tools::getValue('id_customer')));
                if(trim(Tools::getValue('name'))!='')
                    $filter .= " AND (CONCAT(c.firstname,' ',c.lastname) like '".pSQL(Tools::getValue('name'))."%' OR be.name like'".pSQL(Tools::getValue('name'))."%')";                
                if(trim(Tools::getValue('email'))!='')
                    $filter .= " AND c.email like '".pSQL(Tools::getValue('email'))."%'";
                if(trim(Tools::getValue('description'))!='')
                    $filter .= ' AND bel.description like "%'.pSQL(Tools::getValue('description')).'%"';
                if(trim(Tools::getValue('total_post_min'))!='')
                    $having .= ' AND total_post >="'.(int)Tools::getValue('total_post_min').'"';
                if(trim(Tools::getValue('total_post_max'))!='')
                    $having .= ' AND total_post <="'.(int)Tools::getValue('total_post_max').'"';
                if(Tools::isSubmit('status') && trim(Tools::getValue('status'))!='')
                    $filter .= " AND (be.status= '".(int)Tools::getValue('status')."'".((int)Tools::getValue('status')==1 ? ' or be.status is null':'' )." )";
                //Sort
                if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
                {
                    $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')."";
                }
                else
                    $sort = false;
            }
            if(!Tools::isSubmit('has_post') || Tools::getValue('has_post')==1)
                $having .= ' AND total_post >=1';
            elseif(Tools::isSubmit('has_post') && Tools::getValue('has_post')!='')
                $having .= ' AND total_post <=0';
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page')> 0  && Tools::getValue('control')=='customer' ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countCustomersFilter($filter,$having);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $customers = $this->getCustomersFilter($filter, $sort, $start, $paggination->limit,$having);
            if($customers)
            {
                foreach($customers as &$customer)
                {
                    if(!$customer['name'])
                        $customer['name']=$customer['firstname'].' '.$customer['lastname'];
                    if($customer['avata'])
                        $customer['avata']='<div class="avata_img"><img src="'.$this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.$customer['avata'].'" style="width:40px;"/></div>';
                    else
                        $customer['avata']='<div class="avata_img"><img src="'.$this->getBaseLink().'modules/'.$this->name.'/views/img/avata/default_customer.png" style="width:40px;"/></div>';
                    $customer['name'] ='<a href="'.$this->context->link->getAdminLink('AdminCustomers').'&updatecustomer&id_customer='.(int)$customer['id_customer'].'" title="'.$customer['name'].'">'.$customer['name'].'</a>';
                    $customer['view_post_url'] = $this->getLink('blog',array('id_author'=> $customer['id_customer'],'is_customer'=>1,'alias'=> Tools::link_rewrite($customer['name'],true)));
                    $customer['delete_post_url'] = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer&list=true&deleteAllPostCustomer&id_author='.(int)$customer['id_customer'];
                    if($customer['total_post']==0)
                        $customer['has_post']=0;
                    else
                        $customer['has_post']=1;
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_blog_customer',
                'class' =>'customer',
                'actions' => array('edit', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer',
                'identifier' => 'id_customer',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => '',
                'fields_list' => $fields_list,
                'field_values' => $customers,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' => $filter || Tools::isSubmit('total_post_min') || Tools::isSubmit('total_post_max') || Tools::isSubmit('has_post') ? true : false,
                'show_add_new' => false,
                'sort' => $sort ? Tools::getValue('sort'):'',
                'sort_type' => $sort ? Tools::getValue('sort_type'):'',
            ); 
            if($list)
               return $this->renderList($listData);            
            return $this->_html .= $this->displayTabAuthor().$this->renderList($listData).$this->renderEmployeeFrom(true).$this->renderSettingCustomer();      
        }
        //Form
        $customer= new Customer(Tools::getValue('id_customer'));
        $fields_form = array(
			'form' => array(
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Name'),
						'name' => 'name',  
                        'required' => true,                
					), 
                    array(
						'type' => 'textarea',
						'label' => $this->l('Introduction'),
						'name' => 'description',
                        'lang'=>true,
                        'autoload_rte'=>true,
                    ),                         
                    array(
						'type' => 'file',
						'label' => $this->l('Avatar photo'),
						'name' => 'avata',
                        'desc'=> $this->l('Avatar photo should be a square image. Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH',300).'x'.Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT',300),                 						
					),
                    array(
                        'type'=>'select',
                        'label'=>$this->l('Status'),
                        'name'=>'status',
                        'options' => array(
                            'query' => array( 
                                    array(
                                        'id_option' => 1, 
                                        'name' => $this->l('Activated')
                                    ),        
                                    array(
                                        'id_option' => 0, 
                                        'name' => $this->l('Suspended')
                                    ),
                                    array(
                                        'id_option' => -1, 
                                        'name' => $this->l('Suspended and hide posts')
                                    ),
                                ),                             
                             'id' => 'id_option',
                			 'name' => 'name'  
                        ),
                    ),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveBlogEmployee';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=customer&id_customer='.(int)Tools::getValue('id_customer');
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsCustomerValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'post_key' => 'id_customer',
            'cancel_url' => $this->baseAdminPath.'&control=customer&list=true',
            'name_controller' => 'ybc-blog-panel-customer',
		);
        if(Tools::isSubmit('id_customer') && Tools::getValue('id_customer'))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_customer');
            $blog_employee = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)Tools::getValue('id_customer').' AND is_customer=1');
            if($blog_employee['avata'])
            {             
                $helper->tpl_vars['display_img'] = $this->_path.'views/img/avata/'.$blog_employee['avata'];
                $helper->tpl_vars['img_del_link'] = $this->baseAdminPath.'&id_employee='.Tools::getValue('id_employee').'&delemployeeimage=true&control=customer';                
            }
        }
		$helper->override_folder = '/';      
        $this->_html .= $this->displayTabAuthor().$helper->generateForm(array($fields_form)).$this->renderEmployeeFrom(true).$this->renderSettingCustomer();
    }
    public function renderEmployeeFrom($list=false)
    {
        //List 
        if(trim(Tools::getValue('list'))=='true' || $list)
        {
            $fields_list = array(
                'id_employee' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'avata' => array(
                    'title' => $this->l('Avatar'),
                    //'width' => 100,
                    'type' => 'text',
                    'strip_tag' => false,       
                ),                     
                'name' => array(
                    'title' => $this->l('Name'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,   
                ), 
                'email' => array(
                    'title' => $this->l('Email'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ), 
                'description' => array(
                    'title' => $this->l('Introduction'),
                    'type' => 'text',
                    'strip_tag' => false ,
                    'filter'=>true, 
                ),  
                'profile_name'=>array(
                    'title' => $this->l('Profile'),
                    //'width' => 140,
                    'type' => 'select',
                    'filter'=>true,
                    'filter_list'=>array(
                        'list'=> $this->getProfiles(),
                        'id_option' => 'id_profile',
                        'value' => 'name',
                    )
                ),                 
                'profile_employee' =>array(
                    'title'=> $this->l('Accessible tabs'),
                    'width'=>'140',
                    'type'=>'select',
                    'strip_tag' => false,
                    'filter'=> true,
                    'filter_list'=>array(
                        'list'=> array(
                            array(
                                'title'=>$this->l('All tabs'),
                                'id'=>'All tabs'
                            ),
                            array(
                                'title'=>$this->l('Blog posts and blog categories'),
                                'id'=>'Blog posts and blog categories'
                            ),
                            array(
                                'title'=>$this->l('Blog comments'),
                                'id'=>'Blog comments'
                            ),
                            array(
                                'title'=>$this->l('Blog slider'),
                                'id'=>'Blog slider'
                            ),
                            array(
                                'title'=>$this->l('Blog gallery'),
                                'id'=>'Blog gallery'
                            ),
                            array(
                                'title'=>$this->l('Rss feed'),
                                'id'=>'Rss feed'
                            ),
                            array(
                                'title'=>$this->l('Seo'),
                                'id'=>'Seo'
                            ),
                            array(
                                'title'=>$this->l('Socials'),
                                'id'=>'Socials'
                            ),
                            array(
                                'title'=>$this->l('Sitemap'),
                                'id'=>'Sitemap'
                            ),
                            array(
                                'title'=>$this->l('Email'),
                                'id'=>'Email'
                            ),
                            array(
                                'title'=>$this->l('Image'),
                                'id'=>'Image'
                            ),
                            array(
                                'title'=>$this->l('Sidebar'),
                                'id'=>'Sidebar'
                            ),
                            array(
                                'title'=>$this->l('Home page'),
                                'id'=>'Home page'
                            ),
                            array(
                                'title'=>$this->l('Post detail page'),
                                'id'=>'Post detail page'
                            ),
                            array(
                                'title'=>$this->l('Post listing pages'),
                                'id'=>'Post listing pages'
                            ),
                            array(
                                'title'=>$this->l('Category page'),
                                'id'=>'Category page'
                            ),
                            array(
                                'title'=>$this->l('Product detail page'),
                                'id'=>'Product detail page'
                            ),
                            array(
                                'title'=>$this->l('Authors'),
                                'id'=>'Authors'
                            ),
                            array(
                                'title'=>$this->l('Import/Export'),
                                'id'=>'Import/Export'
                            ),
                            array(
                                'title'=>$this->l('Statistics'),
                                'id'=>'Statistics'
                            ),
                            array(
                                'title'=>$this->l('Global settings'),
                                'id'=>'Global settings'
                            ),
                        ),  
                        'id_option' => 'id',
                        'value' => 'title',
                    )  
                ),
                'total_post' =>array(
                    'title'=> $this->l('Total posts'),
                    'width'=>'140',
                    'type'=>'int',
                    'filter'=>true,
                    'sort' => true,
                ),
                'status' => array(
                    'title'=> $this->l('Status'),
                    //'width' => 80,
                    'type' => 'active',
                    'strip_tag' => false,
                    'filter'=>true,
                    'sort' => true,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 0,
                                'title' => $this->l('Activated')
                            ),
                            1 => array(
                                'enabled' => 1,
                                'title' => $this->l('Suspended')
                            ),
                            2 => array(
                                'enabled' => -1,
                                'title' => $this->l('Suspended and hide posts')
                            )
                        )
                    )
                )
            );
            //Filter
            $filter = "";
            $sort = "";
            $having="";
            if(Tools::getValue('control')=='employees')
            {
                if(trim(Tools::getValue('id_employee'))!='')
                    $filter .= " AND e.id_employee = ".(int)trim(urldecode(Tools::getValue('id_employee')));
                if(trim(Tools::getValue('name'))!='')
                    $filter .= " AND (CONCAT(e.firstname,' ',e.lastname) like '".pSQL(Tools::getValue('name'))."%' OR be.name like'".pSQL(Tools::getValue('name'))."%')";                
                if(trim(Tools::getValue('email')))
                    $filter .= " AND e.email like '".pSQL(Tools::getValue('email'))."'";
                if(trim(Tools::getValue('description')))
                    $filter .= " AND bel.description like '%".pSQL(Tools::getValue('description'))."%'";
                if(trim(Tools::getValue('id_profile')))
                    $filter .= " AND pl.id_profile = '".(int)Tools::getValue('id_profile')."'";
                if(trim(Tools::getValue('profile_employee')))
                    $filter .= " AND (be.profile_employee like '%".Tools::getValue('profile_employee')."%' OR p.id_profile=1 or be.profile_employee like '%All tabs%')  ";
                if(trim(Tools::getValue('total_post_min'))!='')
                    $having .= ' AND total_post >="'.(int)Tools::getValue('total_post_min').'"';
                if(trim(Tools::getValue('total_post_max'))!='')
                    $having .= ' AND total_post <="'.(int)Tools::getValue('total_post_max').'"';
                if(Tools::isSubmit('status') && trim(Tools::getValue('status'))!='')
                    $filter .= " AND (be.status= '".(int)Tools::getValue('status')."'".(!(int)Tools::getValue('status') ? ' or be.status is null':'' )." )";
                //Sort
                //die('xx'.$filter);
                if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
                {
                    
                    $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')."";
                }
                else
                    $sort = false;
            }
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 && Tools::getValue('control')=='employees'? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countEmployeesFilter($filter,$having);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=employees&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $employees = $this->getEmployeesFilter($filter, $sort, $start, $paggination->limit,$having);
            if($employees)
            {
                foreach($employees as &$employee)
                {
                    if(!$employee['name'])
                        $employee['name']=$employee['employee'];
                    if($employee['avata'])
                        $employee['avata']='<div class="avata_img"><img src="'.$this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.$employee['avata'].'" style="width:40px;"/></div>';
                    else
                        $employee['avata']='<div class="avata_img"><img src="'.$this->getBaseLink().'modules/'.$this->name.'/views/img/avata/default_customer.png" style="width:40px;"/></div>';
                    $employee['name'] = '<a href ="'.$this->context->link->getAdminLink('AdminEmployees').'&updateemployee&id_employee='.(int)$employee['id_employee'].'" title="'.$employee['name'].'">'.$employee['name'].'</a>';
                    if($employee['id_profile']==1 || Tools::strpos($employee['profile_employee'],'All tabs')!==false)
                        $employee['profile_employee'] = 'All tabs';
                    else
                        $employee['profile_employee'] = str_replace(',','<br/>',$employee['profile_employee']);
                    $employee['view_post_url'] = $this->getLink('blog',array('id_author'=> $employee['id_employee'],'alias'=> Tools::link_rewrite($employee['name'],true)));
                    $employee['delete_post_url'] = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=employees&list=true&deleteAllPostEmployee&id_author='.(int)$employee['id_employee'];
                    //$employee['total_post'] =(int)Db::getInstance()->getValue('SELECT COUNT(id_post) FROM '._DB_PREFIX_.'ybc_blog_post WHERE added_by="'.(int)$employee['id_employee'].'" AND is_customer=0'); 
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');                
            $listData = array(
                'name' => 'ybc_blog_employee',
                'actions' => array('edit', 'view'),
                'class' =>'employee',
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=employees',
                'identifier' => 'id_employee',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => '',
                'fields_list' => $fields_list,
                'field_values' => $employees,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' => $filter || $having ? true : false,
                'show_add_new' => false,
                'sort' => $sort ? Tools::getValue('sort'):'',
                'sort_type' => $sort ? Tools::getValue('sort_type'):'',
                
            ); 
            if($list)
               return $this->renderList($listData);        
            return $this->_html .= $this->displayTabAuthor().$this->renderList($listData).$this->renderCustomerForm(true).$this->renderSettingCustomer();      
        }
        
        //Form
        $employee_class= new Employee(Tools::getValue('id_employee'));
        $fields_form = array(
			'form' => array(
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Name'),
						'name' => 'name',         
                        'required' => true,         
					), 
                    array(
						'type' => 'textarea',
						'label' => $this->l('Introduction'),
						'name' => 'description',
                        'lang'=>true,
                        'autoload_rte'=>true,
                    ),                         
                    array(
						'type' => 'file',
						'label' => $this->l('Avatar photo'),
						'name' => 'avata',
                        'desc'=> $this->l('Avatar photo should be a square image. Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH',300).'x'.Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT',300),                 						
					),
                    array(
                        'type'=>'select',
                        'label'=>$this->l('Status'),
                        'name'=>'status',
                        'form_group_class'=> 'status'.($employee_class->id_profile==1?' hide':''),
                        'options' => array(
                            'query' => array( 
                                    array(
                                        'id_option' => 1, 
                                        'name' => $this->l('Activated')
                                    ),        
                                    array(
                                        'id_option' => 0, 
                                        'name' => $this->l('Suspended')
                                    ),
                                    array(
                                        'id_option' => -1, 
                                        'name' => $this->l('Suspended and hide posts')
                                    ),
                                ),                             
                             'id' => 'id_option',
                			 'name' => 'name'  
                        ),
                    ),
                    array(
                        'type' => 'profile_employee',
    					'label' => $this->l('Accessible tabs'),
                        'form_group_class'=> 'profile'.($employee_class->id_profile==1?' hide':''),
    					'profiles' => array(
                            array(
                                'title'=>$this->l('All tabs'),
                                'id'=>'All tabs'
                            ),
                            array(
                                'title'=>$this->l('Blog posts and blog categories'),
                                'id'=>'Blog posts and blog categories'
                            ),
                            array(
                                'title'=>$this->l('Blog comments'),
                                'id'=>'Blog comments'
                            ),
                            array(
                                'title'=>$this->l('Blog slider'),
                                'id'=>'Blog slider'
                            ),
                            array(
                                'title'=>$this->l('Blog gallery'),
                                'id'=>'Blog gallery'
                            ),
                            array(
                                'title'=>$this->l('Rss feed'),
                                'id'=>'Rss feed'
                            ),
                            array(
                                'title'=>$this->l('Seo'),
                                'id'=>'Seo'
                            ),
                            array(
                                'title'=>$this->l('Socials'),
                                'id'=>'Socials'
                            ),
                            array(
                                'title'=>$this->l('Sitemap'),
                                'id'=>'Sitemap'
                            ),
                            array(
                                'title'=>$this->l('Email'),
                                'id'=>'Email'
                            ),
                            array(
                                'title'=>$this->l('Image'),
                                'id'=>'Image'
                            ),
                            array(
                                'title'=>$this->l('Sidebar'),
                                'id'=>'Sidebar'
                            ),
                            array(
                                'title'=>$this->l('Home page'),
                                'id'=>'Home page'
                            ),
                            array(
                                'title'=>$this->l('Post detail page'),
                                'id'=>'Post detail page'
                            ),
                            array(
                                'title'=>$this->l('Post listing pages'),
                                'id'=>'Post listing pages'
                            ),
                            array(
                                'title'=>$this->l('Category page'),
                                'id'=>'Category page'
                            ),
                            array(
                                'title'=>$this->l('Product detail page'),
                                'id'=>'Product detail page'
                            ),
                            array(
                                'title'=>$this->l('Authors'),
                                'id'=>'Authors'
                            ),
                            array(
                                'title'=>$this->l('Import/Export'),
                                'id'=>'Import/Export'
                            ),
                            array(
                                'title'=>$this->l('Statistics'),
                                'id'=>'Statistics'
                            ),
                            array(
                                'title'=>$this->l('Global settings'),
                                'id'=>'Global settings'
                            ),
                        ),
    					'name' => 'profile_employee',
                        'selected_profile' => $this->getProfileEmployee((int)Tools::getValue('id_employee'))                                           
    				),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveBlogEmployee';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsEmployeeValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'post_key' => 'id_employee',
            'cancel_url' => $this->baseAdminPath.'&control=employees&list=true',
            'name_controller' => 'ybc-blog-panel-employee',                        
		);
        
        if(Tools::isSubmit('id_employee') && Tools::getValue('id_employee'))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_employee');
            $blog_employee = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)Tools::getValue('id_employee').' AND is_customer=0');
            if($blog_employee['avata'])
            {             
                $helper->tpl_vars['display_img'] = $this->_path.'views/img/avata/'.$blog_employee['avata'];
                $helper->tpl_vars['img_del_link'] = $this->baseAdminPath.'&id_employee='.Tools::getValue('id_employee').'&delemployeeimage=true&control=employees';                
            }
        }
        
		$helper->override_folder = '/';   
        $this->_html .= $this->displayTabAuthor().$helper->generateForm(array($fields_form)).$this->renderCustomerForm(true).$this->renderSettingCustomer();
    }
    /**
     * Side 
     */
    public function renderSlideForm()
    {
        //List 
        if(trim(Tools::getValue('list'))=='true')
        {
            $fields_list = array(
                'id_slide' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'image' => array(
                    'title' => $this->l('Image'),
                    //'width' => 100,
                    'type' => 'text',
                    'filter' => false                       
                ),                     
                'caption' => array(
                    'title' => $this->l('Caption'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ), 
                'sort_order' => array(
                    'title' => $this->l('Sort order'),
                    //'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'drag_handle' => true,
                    'filter' => true,
                    'update_position' => true,
                ),                    
                'enabled' => array(
                    'title' => $this->l('Enabled'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                ),
            );
            //Filter
            $filter = "";
            if(trim(Tools::getValue('id_slide'))!='')
                $filter .= " AND s.id_slide = ".(int)trim(urldecode(Tools::getValue('id_slide')));
            if(trim(Tools::getValue('sort_order'))!='')
                $filter .= " AND s.sort_order = ".(int)trim(urldecode(Tools::getValue('sort_order')));                
            if(trim(Tools::getValue('caption'))!='')
                $filter .= " AND sl.caption like '%".addslashes(trim(urldecode(Tools::getValue('title'))))."%'";
            if(trim(Tools::getValue('enabled'))!='')
                $filter .= " AND s.enabled =".(int)Tools::getValue('enabled');
            
            //Sort
            $sort = "";
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = 's.sort_order asc, ';
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countSlidesWithFilter($filter);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=slide&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $slides = $this->getSlidesWithFilter($filter, $sort, $start, $paggination->limit);
            if($slides)
            {
                foreach($slides as &$slide)
                {
                    if($slide['image'] && file_exists(dirname(__FILE__).'/views/img/slide/'.$slide['image']))
                    {
                        $slide['image'] = array(
                            'image_field' => true,
                            'img_url' => $this->_path.'views/img/slide/'.$slide['image'],
                            //'width' => 150
                        );
                    }
                    else
                    $slide['image']=array();
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_slide',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=slide',
                'identifier' => 'id_slide',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Slider'),
                'fields_list' => $fields_list,
                'field_values' => $slides,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' => trim(Tools::getValue('enabled'))!='' || trim(Tools::getValue('id_slide'))!='' || trim(Tools::getValue('description'))!='' || trim(Tools::getValue('title'))!='' || trim(Tools::getValue('sort_order'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'sort' => Tools::getValue('sort','sort_order'),
                'sort_type'=>Tools::getValue('sort_type','asc'),
            );            
            return $this->_html .= $this->renderList($listData);      
        }
        
        //Form
        
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Manage slider'),				
				),
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Caption'),
						'name' => 'caption',
						'lang' => true,    
                        'required' => true,                    
					), 
                    array(
						'type' => 'text',
						'label' => $this->l('Url'),
						'name' => 'url',
                        'lang'=>true,
                    ),                         
                    array(
						'type' => 'file',
						'label' => $this->l('Image'),
						'name' => 'image',
                        'required' => true,    
                         'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_SLIDER_WIDTH',800).'x'.Configuration::get('YBC_BLOG_IMAGE_SLIDER_HEIGHT',470),       						
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'enabled',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveSlide';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsValues(Ybc_blog_defines::$slideFields,'id_slide','Ybc_blog_slide_class','saveSlide'),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'post_key' => 'id_slide',
            'addNewUrl' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=slide',
            'cancel_url' => $this->baseAdminPath.'&control=slide&list=true'
		);
        
        if(Tools::isSubmit('id_slide') && $this->itemExists('slide','id_slide',(int)Tools::getValue('id_slide')))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_slide');
            $slide = new Ybc_blog_slide_class((int)Tools::getValue('id_slide'));
            if($slide->image)
            {             
                $helper->tpl_vars['display_img'] = $this->_path.'views/img/slide/'.$slide->image;
                $helper->tpl_vars['img_del_link'] = $this->baseAdminPath.'&id_slide='.Tools::getValue('id_slide').'&delslideimage=true&control=slide';                
            }
        }
        
		$helper->override_folder = '/';
        $this->_html .= $helper->generateForm(array($fields_form));			
    }
    public function renderExportForm()
    {
        $this->context->smarty->assign(array(
            'errors'=>$this->errors,
            'import_ok'=>$this->import_ok,
        ));
        $this->_html= $this->display(__FILE__,'export.tpl');
    }
    private function _postSlide()
    {
        $errors = array();
        $id_slide = (int)Tools::getValue('id_slide');
        if($id_slide && !$this->itemExists('slide','id_slide',$id_slide) && !Tools::isSubmit('list'))
            Tools::redirectAdmin($this->baseAdminPath);
        /**
         * Change status 
         */
         if(Tools::isSubmit('change_enabled'))
         {
            $status = (int)Tools::getValue('change_enabled') ?  1 : 0;
            $field = Tools::getValue('field');
            $id_slide = (int)Tools::getValue('id_slide');     
            Hook::exec('actionUpdateBlog', array(
                'id_slide' =>(int)$id_slide,
            ));       
            if(($field == 'enabled' && $id_slide))
            {
                $this->changeStatus('slide',$field,$id_slide,$status);
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_slide,
                        'enabled' => $status,
                        'field' => $field,
                        'message' => $this->displaySuccessMessage($this->l('Successful update')),
                        'messageType'=>'success',
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=slide&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_slide='.$id_slide,
                    )));
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=slide&list=true');
            }
         }
        /**
         * Delete image 
         */         
         if($id_slide && $this->itemExists('slide','id_slide',$id_slide) && Tools::isSubmit('delslideimage'))
         {
            Tools::redirectAdmin($this->baseAdminPath);
            $slide = new Ybc_blog_slide_class($id_slide);
            $icoUrl = dirname(__FILE__).'/views/img/slide/'.$slide->image; 
            if($slide->image && file_exists($icoUrl))
            {
                @unlink($icoUrl);
                $slide->image = '';                    
                $slide->update();
                Hook::exec('actionUpdateBlog', array(
                    'id_slide' =>(int)$id_slide,
                )); 
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(
                        array(
                            'messageType' => 'success',
                            'message' => $this->displayConfirmation($this->l('Image deleted')),
                        )
                    ));
                }                     
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_slide='.$id_slide.'&control=slide');
            }
            else
                $errors[] = $this->l('Image does not exist');   
         }
        /**
         * Delete slide 
         */ 
         if(Tools::isSubmit('del'))
         {
            $id_slide = (int)Tools::getValue('id_slide');
            Hook::exec('actionUpdateBlog', array(
                'id_slide' =>(int)$id_slide,
            )); 
            if(!$this->itemExists('slide','id_slide',$id_slide))
                $errors[] = $this->l('Slide does not exist');
            elseif($this->_deleteSlide($id_slide))
            { 
                $slides = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_slide s
                INNER JOIN '._DB_PREFIX_.'ybc_blog_slide_shop ss ON (s.id_slide =ss.id_slide AND ss.id_shop="'.(int)$this->context->shop->id.'")
                ORDER BY sort_order asc');
                if($slides)
                {
                    foreach($slides as $key=>$slide)
                    {
                        $position=$key+1;
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_slide SET sort_order="'.(int)$position.'" WHERE id_slide='.(int)$slide['id_slide']);
                    }
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=slide&list=true');
            }                
            else
                $errors[] = $this->l('Could not delete the slide. Please try again');    
         }
         if(Tools::getValue('action')=='updateSliderOrdering' && $slides=Tools::getValue('slides'))
         {
            $page = Tools::getValue('page',1);
            foreach($slides as $key=> $slide)
            {
                $position=  1+ $key + ($page-1)*20;
                if($key==0)
                {
                    Hook::exec('actionUpdateBlog', array(
                        'id_slide' =>(int)$id_slide,
                    )); 
                }
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_slide SET sort_order="'.(int)$position.'" WHERE id_slide='.(int)$slide);
            }
            die(
                Tools::jsonEncode(
                    array(
                        'page'=>$page,
                    )
                )
            );
        }                  
        /**
         * Save slide 
         */
        if(Tools::isSubmit('saveSlide'))
        {            
            if($id_slide && $this->itemExists('slide','id_slide',$id_slide))
            {
                $slide = new Ybc_blog_slide_class($id_slide);
            }
            else
            {
                $slide = new Ybc_blog_slide_class();
                if(!isset($_FILES['image']['name']) || isset($_FILES['image']['name']) && !$_FILES['image']['name'])
                    $errors[] = $this->l('You need to upload an image');
                $slide->sort_order = 1 + (int)Db::getInstance()->getValue('
                SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_slide s
                INNER JOIN '._DB_PREFIX_.'ybc_blog_slide_shop ss ON (s.id_slide =ss.id_slide AND ss.id_shop="'.(int)$this->context->shop->id.'")
                ORDER BY sort_order asc');
            }                
            $slide->enabled = trim(Tools::getValue('enabled',1)) ? 1 : 0;
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
			{			
			    $slide->caption[$language['id_lang']] = trim(Tools::getValue('caption_'.$language['id_lang'])) != '' ? trim(Tools::getValue('caption_'.$language['id_lang'])) :  trim(Tools::getValue('caption_'.Configuration::get('PS_LANG_DEFAULT')));
                if($slide->caption[$language['id_lang']] && !Validate::isCleanHtml($slide->caption[$language['id_lang']]))
                    $errors[] = $this->l('Caption in '.$language['name'].' is not valid');   
                $slide->url[$language['id_lang']] = trim(Tools::getValue('url_'.$language['id_lang'])) != '' ? trim(Tools::getValue('url_'.$language['id_lang'])) :  trim(Tools::getValue('url_'.Configuration::get('PS_LANG_DEFAULT')));
                if($slide->url[$language['id_lang']] && !Validate::isCleanHtml($slide->url[$language['id_lang']]))
                    $errors[] = $this->l('url in '.$language['name'].' is not valid');                                	
            }
            
            if(Tools::getValue('caption_'.Configuration::get('PS_LANG_DEFAULT'))=='')
                $errors[] = $this->l('You need to set caption');                    
            /**
             * Upload image 
             */  
            $oldImage = false;
            $newImage = false;       
            if(isset($_FILES['image']['tmp_name']) && isset($_FILES['image']['name']) && $_FILES['image']['name'])
            {
                if(file_exists(dirname(__FILE__).'/views/img/slide/'.$_FILES['image']['name']))
                {
                    $_FILES['image']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['image']['name'];
                    //$errors[] = $this->l('Image file name already exists');
                }                                            
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image']['name'], '.'), 1));
    			$imagesize = @getimagesize($_FILES['image']['tmp_name']);
    			if (isset($_FILES['image']) &&				
    				!empty($_FILES['image']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{
    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
    				if ($error = ImageManager::validateUpload($_FILES['image']))
    					$errors[] = $error;
    				elseif (!$temp_name || !move_uploaded_file($_FILES['image']['tmp_name'], $temp_name))
    					$errors[] = $this->l('Can not upload the file');
    				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/slide/'.$_FILES['image']['name'], Configuration::get('YBC_BLOG_IMAGE_SLIDER_WIDTH',null,null,null,800), Configuration::get('YBC_BLOG_IMAGE_SLIDER_HEIGHT',null,null,null,470), $type))
    					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
    				if (isset($temp_name))
    					@unlink($temp_name);
                    if($slide->image)
                        $oldImage = dirname(__FILE__).'/views/img/slide/'.$slide->image;
                    $slide->image = $_FILES['image']['name'];	
                    $newImage = dirname(__FILE__).'/views/img/slide/'.$slide->image;
                }
               
            }			
            
            /**
             * Save 
             */    
             
            if(!$errors)
            {
                if (!Tools::getValue('id_slide'))
    			{
    				if (!$slide->add())
                    {
                        $errors[] = $this->displayError($this->l('The slide could not be added.'));
                        if($newImage && file_exists($newImage))
                        @unlink($newImage);                    
                    }
                    else
                    {
                        Hook::exec('actionUpdateBlogImage', array(
                            'id_slide' =>(int)$slide->id,
                            'image' => $newImage ? $slide->image :false,
                            'thumb' => false,
                        ));
                    }                	                    
    			}				
    			elseif (!$slide->update())
                {
                    if($newImage && file_exists($newImage))
                        @unlink($newImage); 
                    $errors[] = $this->displayError($this->l('The slide could not be updated.'));
                }
                else
                {
                    if($oldImage && file_exists($oldImage))
                    @unlink($oldImage); 
                    Hook::exec('actionUpdateBlogImage', array(
                        'id_slide' =>(int)$slide->id,
                        'image' => $newImage ? $slide->image :false,
                        'thumb' => false,
                    ));
                }
    			Hook::exec('actionUpdateBlog', array(
                    'id_slide' =>(int)$slide->id,
                )); 		                
            }
         }
         $changedImages = array();
         if(isset($newImage) && $newImage && file_exists($newImage) && !$errors && isset($slide)){
            $changedImages[] = array(
                'name' => 'image',
                'url' => $this->_path.'views/img/slide/'.$slide->image,                    
            );
         }
         if (count($errors))
         {
            if($newImage && file_exists($newImage))
                @unlink($newImage); 
            $this->errorMessage = $this->displayError($errors);  
         }
         if(Tools::isSubmit('ajax'))
         {
            die(Tools::jsonEncode(
                array(
                    'messageType' => $errors ? 'error' : 'success',
                    'message' => $errors ? $this->errorMessage : $this->displaySuccessMessage($this->l('Slider saved'),$this->l('View slider on blog page'),$this->getLink('blog')),
                    'images' => isset($changedImages) && $changedImages ? $changedImages : array(),
                    'postUrl' => !$errors && Tools::isSubmit('saveSlide') && !(int)Tools::getValue('id_slide') ? $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_slide='.$this->getMaxId('slide','id_slide').'&control=slide' : 0,
                    'itemKey' => 'id_slide',
                    'itemId' => !$errors && Tools::isSubmit('saveSlide') && !(int)Tools::getValue('id_slide') ? $this->getMaxId('slide','id_slide') : ((int)Tools::getValue('id_slide') > 0 ? (int)Tools::getValue('id_slide') : 0),
                )
            ));
         } 
         if (!$errors && Tools::isSubmit('saveSlide') && Tools::isSubmit('id_slide'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_slide='.Tools::getValue('id_slide').'&control=slide');
		 elseif (!$errors && Tools::isSubmit('saveSlide'))
         {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_slide='.$this->getMaxId('slide','id_slide').'&control=slide');
         }
    }
    public function _postExport()
    {
        if(Tools::isSubmit('submitExportBlog'))
        {
            $import= new Ybc_Blog_ImportExport();
            $import->generateArchive();
        }
        if(Tools::isSubmit('submitImportBlog'))
        {
            $import= new Ybc_Blog_ImportExport();
            $this->context->smarty->assign(
                array(
                    'data_import'=>Tools::getValue('data_import'),
                    'importoverride' => Tools::getValue('importoverride'),
                    'keepauthorid' => Tools::getValue('keepauthorid'),
                    'keepcommenter' => Tools::getValue('keepcommenter'),
                )
            );
            $errors =$import->processImport();

            if($errors)            
                $this->errors=$errors;
            else
                $this->import_ok=true;                                                                  
        }
        if(Tools::isSubmit('submitImportBlogWP'))
        {
            $import= new Ybc_Blog_ImportExport();
            $errors =$import->processImportWordPress();
            if($errors)            
                $this->errors=$errors;
            else
                $this->import_ok=true;                                                                  
        }
    }
    
    /**
     * Gallery 
     */
    public function renderGalleryForm()
    {
        //List 
        if(trim(Tools::getValue('list'))=='true')
        {
            $fields_list = array(
                'id_gallery' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true,
                ),
                'thumb' => array(
                    'title' => $this->l('Thumbnail'),
                    //'width' => 140,
                    'type' => 'text',
                    'required' => true                        
                ), 
                'title' => array(
                    'title' => $this->l('Name'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'description' => array(
                    'title' => $this->l('Description'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => true,
                    'filter' => true
                ),
                'sort_order' => array(
                    'title' => $this->l('Sort order'),
                    //'width' => 40,
                    'type' => 'text',                        
                    'sort' => true,
                    'filter' => true,
                    'update_position' => true,                        
                ),  
                'is_featured' => array(
                    'title' => $this->l('Featured'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                ),                
                'enabled' => array(
                    'title' => $this->l('Enabled'),
                    //'width' => 80,
                    'type' => 'active',
                    'sort' => true,
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                ),
            );
            //Filter
            $filter = "";
            if(trim(Tools::getValue('id_gallery'))!='')
                $filter .= " AND g.id_gallery = ".(int)trim(urldecode(Tools::getValue('id_gallery')));
            if(trim(Tools::getValue('sort_order'))!='')
                $filter .= " AND g.sort_order = ".(int)trim(urldecode(Tools::getValue('sort_order')));                
            if(trim(Tools::getValue('title'))!='')
                $filter .= " AND gl.title like '%".addslashes(trim(urldecode(Tools::getValue('title'))))."%'";
            if(trim(Tools::getValue('description'))!='')
                $filter .= " AND gl.description like '%".addslashes(trim(urldecode(Tools::getValue('description'))))."%'";
            if(trim(Tools::getValue('enabled'))!='')
                $filter .= " AND g.enabled =".(int)Tools::getValue('enabled');
            if(trim(Tools::getValue('is_featured'))!='')
                $filter .= " AND g.is_featured =".(int)Tools::getValue('is_featured');
            
            //Sort
            $sort = "";
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = 'g.sort_order asc,';
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countGalleriesWithFilter($filter);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=gallery&list=true&page=_page_'.$this->getUrlExtra($fields_list);
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $galleries = $this->getGalleriesWithFilter($filter, $sort, $start, $paggination->limit);
            if($galleries)
            {
                foreach($galleries as &$gallery)
                {
                    if($gallery['thumb'] && file_exists(dirname(__FILE__).'/views/img/gallery/thumb/'.$gallery['thumb']))
                    {
                        $gallery['thumb'] = array(
                            'image_field' => true,
                            'img_url' =>  $this->_path.'views/img/gallery/thumb/'.$gallery['thumb'],
                            //'width' => 150
                        );
                    }
                    elseif($gallery['image'] && file_exists(dirname(__FILE__).'/views/img/gallery/'.$gallery['image']))
                    {
                        $gallery['thumb'] = array(
                            'image_field' => true,
                            'img_url' =>  $this->_path.'views/img/gallery/'.$gallery['image'],
                            //'width' => 150
                        );
                    }
                    else
                        $gallery['thumb']=array();
                }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_gallery',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=gallery',
                'identifier' => 'id_gallery',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Blog gallery'),
                'fields_list' => $fields_list,
                'field_values' => $galleries,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParams($fields_list),
                'show_reset' => trim(Tools::getValue('is_featured'))!='' || trim(Tools::getValue('enabled'))!='' || trim(Tools::getValue('id_gallery'))!='' || trim(Tools::getValue('description'))!='' || trim(Tools::getValue('title'))!='' || trim(Tools::getValue('sort_order'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'preview_link' => $this->getLink('gallery'),
                'sort' => Tools::getValue('sort','sort_order'),
                'sort_type'=>Tools::getValue('sort_type','asc')
            );            
            return $this->_html .= $this->renderList($listData);      
        }
        //Form
        
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Blog gallery'),				
				),
				'input' => array(					
					array(
						'type' => 'text',
						'label' => $this->l('Name'),
						'name' => 'title',
						'lang' => true,    
                        'required' => true                    
					),    
                    array(
						'type' => 'textarea',
						'label' => $this->l('Caption'),
						'name' => 'description',
						'lang' => true,  
                        'autoload_rte' => true                      
					),  
                    array(
						'type' => 'file',
						'label' => $this->l('Thumbnail image'),
						'name' => 'thumb',
                        'imageType' => 'thumb',
                        'required' => true,
                        'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_GALLERY_THUHMB_WIDTH',null,null,null,180).'x'.Configuration::get('YBC_BLOG_IMAGE_GALLERY_THUHMB_HEIGHT',null,null,null,180),						
					),                   
                    array(
						'type' => 'file',
						'label' => $this->l('Large Image'),
						'name' => 'image',
                        'required' => true,
                        'desc' => $this->l('Recommended size: ').Configuration::get('YBC_BLOG_IMAGE_GALLERY_WIDTH',null,null,null,600).'x'.Configuration::get('YBC_BLOG_IMAGE_GALLERY_HEIGHT',null,null,null,600),                        						
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Featured'),
						'name' => 'is_featured',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
                        'desc' => $this->l('Enable if you want to display this image in the featured gallery block on the front office')					
					),
                    array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'enabled',
                        'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						)					
					),
                    array(
                        'type' => 'hidden', 
                        'name' => 'control'
                    )
                ),
                'submit' => array(
					'title' => $this->l('Save'),
				)
            ),
		);
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'saveGallery';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
			'fields_value' => $this->getFieldsValues(Ybc_blog_defines::$galleryFields,'id_gallery','Ybc_blog_gallery_class','saveGallery'),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/',
            'link' => $this->context->link,
            'cancel_url' => $this->baseAdminPath.'&control=gallery&list=true',
            'post_key' => 'id_gallery',
            'addNewUrl' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=gallery',                   
            
		);
        
        if(Tools::isSubmit('id_gallery') && $this->itemExists('gallery','id_gallery',(int)Tools::getValue('id_gallery')))
        {
            
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_gallery');
            $gallery = new Ybc_blog_gallery_class((int)Tools::getValue('id_gallery'));
            if($gallery->image)
            {             
                $helper->tpl_vars['display_img'] = $this->_path.'views/img/gallery/'.$gallery->image;
                $helper->tpl_vars['img_del_link'] = $this->baseAdminPath.'&id_gallery='.Tools::getValue('id_gallery').'&delgalleryimage=true&control=gallery';                
            }
            if($gallery->thumb)
            {             
                $helper->tpl_vars['display_thumb'] = $this->_path.'views/img/gallery/thumb/'.$gallery->thumb;
                $helper->tpl_vars['thumb_del_link'] = $this->baseAdminPath.'&id_gallery='.Tools::getValue('id_gallery').'&delgallerythumb=true&control=gallery';                
            }
        }
        
		$helper->override_folder = '/';
        $this->_html .= $helper->generateForm(array($fields_form));			
    }
    private function _postGallery()
    {
        $errors = array();
        $id_gallery = (int)Tools::getValue('id_gallery');
        if($id_gallery && !$this->itemExists('gallery','id_gallery',$id_gallery) && !Tools::isSubmit('list'))
            Tools::redirectAdmin($this->baseAdminPath);
        /**
         * Change status 
         */
         if(Tools::isSubmit('change_enabled'))
         {
            $status = (int)Tools::getValue('change_enabled') ?  1 : 0;
            $field = Tools::getValue('field');
            $id_gallery = (int)Tools::getValue('id_gallery');  
            Hook::exec('actionUpdateBlog', array(
                'id_gallery' =>(int)$id_gallery,
            ));           
            if(($field == 'enabled' || $field=='is_featured') && $id_gallery)
            {
                $this->changeStatus('gallery',$field,$id_gallery,$status);
                if($field=='enabled')
                {
                    if($status==1)
                        $title = $this->l('Click to unmark featured');
                    else
                        $title = $this->l('Click to mark as featured');
                }
                else
                {
                    if($status==1)
                        $title = $this->l('Click to unmark disabled');
                    else
                        $title = $this->l('Click to mark as enabled');
                }
                if(Tools::isSubmit('ajax'))
                {
                    die(Tools::jsonEncode(array(
                        'listId' => $id_gallery,
                        'enabled' => $status,
                        'field' => $field,
                        'message' => $field=='enabled' ? $this->displaySuccessMessage($this->l('The status has been successfully updated')) : $this->displaySuccessMessage($this->l('The feature has been successfully updated')),
                        'messageType'=>'success',
                        'title'=>$title,
                        'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=gallery&change_enabled='.($status ? '0' : '1').'&field='.$field.'&id_gallery='.$id_gallery,
                    )));
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=gallery&list=true');
            }
         }
        /**
         * Delete image 
         */         
         if($id_gallery && $this->itemExists('gallery','id_gallery',$id_gallery) && Tools::isSubmit('delgalleryimage'))
         {
            Tools::redirectAdmin($this->baseAdminPath);
            Hook::exec('actionUpdateBlog', array(
                'id_gallery' =>(int)$id_gallery,
            )); 
            $gallery = new Ybc_blog_gallery_class($id_gallery);
            if($gallery->image)
            {
                $icoUrl = dirname(__FILE__).'/views/img/gallery/'.$gallery->image; 
                $thumbUrl = dirname(__FILE__).'/views/img/gallery/thumb/'.$gallery->thumb; 
                if(file_exists($thumbUrl))
                    @unlink($thumbUrl);
                if(file_exists($icoUrl))
                {
                    @unlink($icoUrl);
                    $gallery->image = '';                    
                    $gallery->update(); 
                    if(Tools::isSubmit('ajax'))
                    {
                        die(Tools::jsonEncode(
                            array(
                                'messageType' => 'success',
                                'message' => $this->displayConfirmation($this->l('Image has been deleted')),
                            )
                        ));
                    }                 
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_gallery='.$id_gallery.'&control=gallery');
                }
                else
                    $errors[] = $this->l('Image does not exist');  
            }
            else
                $errors[] = $this->l('Image is empty'); 
             
         }
        /**
         * Delete gallery 
         */ 
         if(Tools::isSubmit('del'))
         {
            $id_gallery = (int)Tools::getValue('id_gallery');
            Hook::exec('actionUpdateBlog', array(
                'id_gallery' =>(int)$id_gallery,
            )); 
            if(!$this->itemExists('gallery','id_gallery',$id_gallery))
                $errors[] = $this->l('Item does not exist');
            elseif($this->_deleteGallery($id_gallery))
            {   
                $galleries = Db::getInstance()->executeS('
                SELECT * FROM '._DB_PREFIX_.'ybc_blog_gallery g, '._DB_PREFIX_.'ybc_blog_gallery_shop gs
                WHERE g.id_gallery=gs.id_gallery AND gs.id_shop="'.(int)$this->context->shop->id.'" ORDER BY g.sort_order asc');
                if($galleries)
                {
                    foreach($galleries as $key=> $gallery)
                    {
                        $position = $key+1;
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_gallery SET sort_order="'.(int)$position.'" WHERE id_gallery='.(int)$gallery['id_gallery']);
                    }   
                }
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=gallery&list=true');
            }                
            else
                $errors[] = $this->l('Could not delete the item. Please try again');    
         }
         // update sort_order
         if(Tools::getValue('action')=='updateGalleryOrdering' && $galleries=Tools::getValue('galleries'))
         {
            $page = Tools::getValue('page',1);
            foreach($galleries as $key=> $gallery)
            {
                $position=  1+ $key + ($page-1)*20;
                Hook::exec('actionUpdateBlog', array(
                    'id_gallery' =>(int)$gallery,
                )); 
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_gallery SET sort_order="'.(int)$position.'" WHERE id_gallery='.(int)$gallery);
            }
            die(
                Tools::jsonEncode(
                    array(
                        'page'=>$page,
                    )
                )
            );
        }                  
        /**
         * Save gallery 
         */
        if(Tools::isSubmit('saveGallery'))
        {            
            if($id_gallery && $this->itemExists('gallery','id_gallery',$id_gallery))
            {
                $gallery = new Ybc_blog_gallery_class($id_gallery);
            }
            else
            {
                $gallery = new Ybc_blog_gallery_class();  
                $gallery->sort_order = 1 + (int)Db::getInstance()->getValue('
                SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_gallery g, '._DB_PREFIX_.'ybc_blog_gallery_shop gs
                WHERE g.id_gallery=gs.id_gallery AND gs.id_shop="'.(int)$this->context->shop->id.'" ORDER BY g.sort_order asc');                                
            }                
            $gallery->enabled = trim(Tools::getValue('enabled',1)) ? 1 : 0;
            $gallery->is_featured = trim(Tools::getValue('is_featured',1)) ? 1 : 0;
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
			{			
		        $gallery->title[$language['id_lang']] = trim(Tools::getValue('title_'.$language['id_lang'])) != '' ? trim(Tools::getValue('title_'.$language['id_lang'])) :  trim(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT')));
                if($gallery->title[$language['id_lang']] && !Validate::isCleanHtml($gallery->title[$language['id_lang']]))
                    $errors[] = $this->l('Name in '.$language['name'].' is not valid');
                $gallery->description[$language['id_lang']] = trim(Tools::getValue('description_'.$language['id_lang'])) != '' ? trim(Tools::getValue('description_'.$language['id_lang'])) :  trim(Tools::getValue('description_'.Configuration::get('PS_LANG_DEFAULT')));
                if($gallery->description[$language['id_lang']] && !Validate::isCleanHtml($gallery->description[$language['id_lang']], true))
                    $errors[] = $this->l('Description in '.$language['name'].' is not valid');
            }
            
            if(Tools::getValue('title_'.Configuration::get('PS_LANG_DEFAULT'))=='')
                $errors[] = $this->l('Name is required');                    
            /**
             * Upload image 
             */  
            $oldImage = false;
            $newImage = false;       
            $newThumb = false;
            $oldThumb = false;
            if(isset($_FILES['image']['tmp_name']) && isset($_FILES['image']['name']) && $_FILES['image']['name'])
            {
                if(file_exists(dirname(__FILE__).'/views/img/gallery/'.$_FILES['image']['name']))
                {
                    $_FILES['image']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['image']['name'];
                }                    
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image']['name'], '.'), 1));
    			$imagesize = @getimagesize($_FILES['image']['tmp_name']);
    			if (isset($_FILES['image']) &&				
    				!empty($_FILES['image']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{
    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
    				if ($error = ImageManager::validateUpload($_FILES['image']))
    					$errors[] = $error;
    				elseif (!$temp_name || !move_uploaded_file($_FILES['image']['tmp_name'], $temp_name))
    					$errors[] = $this->l('Can not upload the file');
    				elseif(!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/gallery/'.$_FILES['image']['name'], Configuration::get('YBC_BLOG_IMAGE_GALLERY_WIDTH',null,null,null,600), Configuration::get('YBC_BLOG_IMAGE_GALLERY_HEIGHT',null,null,null,600), $type))
    					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
    				
                    if($gallery->image)
                    {
                        $oldImage = dirname(__FILE__).'/views/img/gallery/'.$gallery->image;
                    }                                
                    $gallery->image = $_FILES['image']['name'];
                    $newImage = dirname(__FILE__).'/views/img/gallery/'.$gallery->image;
                    if (isset($temp_name))
    					@unlink($temp_name);		
    			}
                
            }			
            elseif(!$id_gallery)
                $errors[] = $this->l('Image is required');
            if(isset($_FILES['thumb']['tmp_name']) && isset($_FILES['thumb']['name']) && $_FILES['thumb']['name'])
            {
                if(file_exists(dirname(__FILE__).'/views/img/gallery/thumb/'.$_FILES['thumb']['name']))
                {
                    $_FILES['thumb']['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['thumb']['name'];
                }                    
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['thumb']['name'], '.'), 1));
    			$imagesize = @getimagesize($_FILES['thumb']['tmp_name']);
    			if (isset($_FILES['thumb']) &&				
    				!empty($_FILES['thumb']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{
    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
    				if ($error = ImageManager::validateUpload($_FILES['thumb']))
    					$errors[] = $error;
    				elseif (!$temp_name || !move_uploaded_file($_FILES['thumb']['tmp_name'], $temp_name))
    					$errors[] = $this->l('Can not upload the file');
    				elseif(!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/gallery/thumb/'.$_FILES['thumb']['name'], Configuration::get('YBC_BLOG_IMAGE_GALLERY_THUHMB_WIDTH',null,null,null,180), Configuration::get('YBC_BLOG_IMAGE_GALLERY_THUHMB_HEIGHT',null,null,null,180), $type))
    					$errors[] = $this->displayError($this->l('An error occurred during the image thumbnail upload process.'));
    				
                    if($gallery->thumb)
                    {
                        $oldThumb = dirname(__FILE__).'/views/img/gallery/thumb/'.$gallery->thumb;
                    }                                
                    $gallery->thumb = $_FILES['thumb']['name'];
                    $newThumb = dirname(__FILE__).'/views/img/gallery/thumb/'.$gallery->thumb;
                    if (isset($temp_name))
    					@unlink($temp_name);		
    			}
                
            }			
            elseif(!$id_gallery)
                $errors[] = $this->l('Thumbnail is required');
            /**
             * Save 
             */    
             
            if(!$errors)
            {
                if (!Tools::getValue('id_gallery'))
    			{
    				if (!$gallery->add())
                    {
                        $errors[] = $this->displayError($this->l('The item could not be added.'));
                        if($newImage && file_exists($newImage))
                        @unlink($newImage);  
                        if($newThumb && file_exists($newThumb))
                        @unlink($newThumb);                     
                    } 
                    else
                    {
                        Hook::exec('actionUpdateBlogImage', array(
                            'id_gallery' =>(int)$gallery->id,
                            'image' => $newImage ? $gallery->image :false,
                            'thumb' => $newThumb ? $gallery->thumb : false,
                        ));
                    }               	                    
    			}				
    			elseif (!$gallery->update())
                {
                    if($newImage && file_exists($newImage))
                        @unlink($newImage);
                    if($newThumb && file_exists($newThumb))
                        @unlink($newThumb); 
                    $errors[] = $this->displayError($this->l('The item could not be updated.'));
                }
                else
                {
                    if($oldImage && file_exists($oldImage))
                    @unlink($oldImage); 
                    if($oldThumb && file_exists($oldThumb))
                    @unlink($oldThumb); 
                    Hook::exec('actionUpdateBlogImage', array(
                        'id_gallery' =>(int)$gallery->id,
                        'image' => $newImage ? $gallery->image :false,
                        'thumb' => $newThumb ? $gallery->thumb : false,
                    ));
                }
    			Hook::exec('actionUpdateBlog', array(
                    'id_gallery' =>(int)$gallery->id,
                ));		                
            }
         }
         $changedImages = array();
         if(isset($newImage) && $newImage && file_exists($newImage) && !$errors && isset($gallery)){
            $changedImages[] = array(
                'name' => 'image',
                'url' => $this->_path.'views/img/gallery/'.$gallery->image,                    
            );
         }
         if(isset($newThumb) && $newThumb && file_exists($newThumb) && !$errors && isset($gallery)){
            $changedImages[] = array(
                'name' => 'thumb',
                'url' => $this->_path.'views/img/gallery/thumb/'.$gallery->thumb,                    
            );
         }
         if(Tools::isSubmit('ajax'))
         {
            die(Tools::jsonEncode(
                array(
                    'messageType' => $errors ? 'error' : 'success',
                    'message' => $errors ? $this->displayError($errors) : $this->displaySuccessMessage($this->l('Gallery image saved'),$this->l('View blog gallery'),$this->getLink('gallery')),
                    'images' => isset($changedImages) && $changedImages ? $changedImages : array(),
                    'postUrl' => !$errors && Tools::isSubmit('saveGallery') && !(int)Tools::getValue('id_gallery') ? $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_gallery='.$this->getMaxId('gallery','id_gallery').'&control=gallery' : 0,
                    'itemKey' => 'id_gallery',
                    'itemId' => !$errors && Tools::isSubmit('saveGallery') && !(int)Tools::getValue('id_gallery') ? $this->getMaxId('gallery','id_gallery') : ((int)Tools::getValue('id_gallery') > 0 ? (int)Tools::getValue('id_gallery') : 0),
                )
            ));
         } 
         if (count($errors))
         {
            if($newImage && file_exists($newImage))
                @unlink($newImage); 
            if($newThumb && file_exists($newThumb))
                @unlink($newThumb); 
            $this->errorMessage = $this->displayError($errors);  
         }
         elseif (Tools::isSubmit('saveGallery') && Tools::isSubmit('id_gallery'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_gallery='.Tools::getValue('id_gallery').'&control=gallery');
		 elseif (Tools::isSubmit('saveGallery'))
         {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&id_gallery='.$this->getMaxId('gallery','id_gallery').'&control=gallery');
         }
    }
    public function hookModuleRoutes($params) {
        $subfix = (int)Configuration::get('YBC_BLOG_URL_SUBFIX') ? '.html' : '';
        $blogAlias = Configuration::get('YBC_BLOG_ALIAS',$this->context->language->id);
        if(!$blogAlias)
            return array();
        $routes = array(
            'authorall' => array(
                'controller' => 'author',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$this->context->language->id)) ? $subAlias : 'author'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'authorallpage' => array(
                'controller' => 'author',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$this->context->language->id)) ? $subAlias : 'author').'/{page}',
                'keywords' => array(
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'ybcblogmainpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias,
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'ybcblogfeaturedpostspage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/{page}',
                'keywords' => array(
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),  
            'ybcblogpostcomment' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$this->context->language->id)) ? $subAlias : 'post').'/{id_post}-{edit_comment}-{url_alias}'.$subfix,
                'keywords' => array(
                    'id_post' =>    array('regexp' => '[0-9]+', 'param' => 'id_post'),
                    'edit_comment' => array('regexp' => '[0-9]+', 'param' => 'edit_comment'),
                    'url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),  
            'ybcblogpostallcomments' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$this->context->language->id)) ? $subAlias : 'post').'/allcomments/{id_post}-{url_alias}'.$subfix,
                'keywords' => array(
                    'id_post' =>    array('regexp' => '[0-9]+', 'param' => 'id_post'),
                    'url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'all_comment'=>1,
                ),
            ),   
            'ybcblogpost2' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$this->context->language->id)) ? $subAlias : 'post').'/{id_post}-{url_alias}'.$subfix,
                'keywords' => array(
                    'url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'url_alias'),
                    'id_post' =>    array('regexp' => '[0-9]+', 'param' => 'id_post'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),         
            'ybcblogpost' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_POST',$this->context->language->id)) ? $subAlias : 'post').'/{post_url_alias}'.$subfix,
                'keywords' => array(
                    'post_url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'post_url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogpostpage2' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$this->context->language->id)) ? $subAlias : 'category').'/{page}/{id_category}-{url_alias}'.$subfix,
                'keywords' => array(
                    'id_category' =>    array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                    'url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogpostpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$this->context->language->id)) ? $subAlias : 'category').'/{page}/{category_url_alias}'.$subfix,
                'keywords' => array(
                    //'id_category' =>    array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                    'category_url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'category_url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogpost2' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$this->context->language->id)) ? $subAlias : 'category').'/{id_category}-{url_alias}'.$subfix,
                'keywords' => array(
                    'id_category' =>    array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogpost' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$this->context->language->id)) ? $subAlias : 'category').'/{category_url_alias}'.$subfix,
                'keywords' => array(
                    //'id_category' =>    array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'category_url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'category_url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'authorblogpostpage2' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR2',$this->context->language->id)) ? $subAlias : 'community-author').'/{page}/{id_author}-{author_name}',
                'keywords' => array(
                    'id_author' =>    array('regexp' => '[0-9]+', 'param' => 'id_author'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                    'author_name'       =>   array('regexp' => '(.)+','param' => 'author_name'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'is_customer'=>1,
                ),
            ),
            'authorblogpostpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$this->context->language->id)) ? $subAlias : 'author').'/{page}/{id_author}-{author_name}',
                'keywords' => array(
                    'id_author' =>    array('regexp' => '[0-9]+', 'param' => 'id_author'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                    'author_name'       =>   array('regexp' => '(.)+','param' => 'author_name'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'authorblogpost2' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR2',$this->context->language->id)) ? $subAlias : 'community-author').'/{id_author}-{author_name}',
                'keywords' => array(
                    'id_author' =>    array('regexp' => '[0-9]+', 'param' => 'id_author'),
                    'author_name'       =>   array('regexp' => '(.)+','param' => 'author_name'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'is_customer'=>1,
                ),
            ),
            'authorblogpost' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$this->context->language->id)) ? $subAlias : 'author').'/{id_author}-{author_name}',
                'keywords' => array(
                    'id_author' =>    array('regexp' => '[0-9]+', 'param' => 'id_author'),
                    'author_name'       =>   array('regexp' => '(.)+','param' => 'author_name'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogtagpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/{page}/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_TAG',$this->context->language->id)) ? $subAlias : 'tag').'/{tag}',
                'keywords' => array(
                    'tag'       =>   array('regexp' => '.+','param' => 'tag'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogtag' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_TAG',$this->context->language->id)) ? $subAlias : 'tag').'/{tag}',
                'keywords' => array(
                    'tag'       =>   array('regexp' => '.+','param' => 'tag'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categorybloglatestpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_LATEST',$this->context->language->id)) ? $subAlias : 'latest').'/{page}',
                'keywords' => array(                       
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'latest' => 'true'
                ),
            ),
            'categorybloglatest' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_LATEST',$this->context->language->id)) ? $subAlias : 'latest'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'latest' => 'true'
                ),
            ),
            'categoryblogpopulartpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_POPULAR',$this->context->language->id)) ? $subAlias : 'popular').'/{page}',
                'keywords' => array(                       
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'popular' => 'true'
                ),
            ),
            'categoryblogpopular' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_POPULAR',$this->context->language->id)) ? $subAlias : 'popular'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'popular' => 'true'
                ),
            ),
            'categoryblogfeaturedpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_FEATURED',$this->context->language->id)) ? $subAlias : 'featured').'/{page}',
                'keywords' => array(                       
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'featured' => 'true'
                ),
            ),
            'categoryblogfeatured' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_FEATURED',$this->context->language->id)) ? $subAlias : 'featured'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'featured' => 'true'
                ),
            ),
            'categoryblogsearchpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/{page}/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_SEARCH',$this->context->language->id)) ? $subAlias : 'search').'/{search}',
                'keywords' => array(
                    'search'       =>   array('regexp' => '.+','param' => 'search'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogsearch' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_SEARCH',$this->context->language->id)) ? $subAlias : 'search').'/{search}',
                'keywords' => array(
                    'search'       =>   array('regexp' => '.+','param' => 'search'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogyearpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_YEARS',$this->context->language->id)) ? $subAlias : 'year').'/{year}/{page}',
                'keywords' => array(
                    'year'       =>   array('regexp' => '[0-9]+','param' => 'year'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogyear' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_YEARS',$this->context->language->id)) ? $subAlias : 'year').'/{year}',
                'keywords' => array(
                    'year'       =>   array('regexp' => '[0-9]+','param' => 'year'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogmonthpage' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_MONTHS',$this->context->language->id)) ? $subAlias : 'month').'/{month}/{year}/{page}',
                'keywords' => array(
                    'month'       =>   array('regexp' => '[0-9]+','param' => 'month'),
                    'year'       =>   array('regexp' => '[0-9]+','param' => 'year'),
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogmonth' => array(
                'controller' => 'blog',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_MONTHS',$this->context->language->id)) ? $subAlias : 'month').'/{month}/{year}',
                'keywords' => array(
                    'month'       =>   array('regexp' => '[0-9]+','param' => 'month'),
                    'year'       =>   array('regexp' => '[0-9]+','param' => 'year'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categorybloggallerypage' => array(
                'controller' => 'gallery',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_GALLERY',$this->context->language->id)) ? $subAlias : 'gallery').'/{page}',
                'keywords' => array(
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categorybloggallery' => array(
                'controller' => 'gallery',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_GALLERY',$this->context->language->id)) ? $subAlias : 'gallery'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogcommentspage' => array(
                'controller' => 'comment',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_COMMENTS',$this->context->language->id)) ? $subAlias : 'comments').'/{page}',
                'keywords' => array(
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogcategoriespage' => array(
                'controller' => 'category',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORIES',$this->context->language->id)) ? $subAlias : 'categories').'/{page}',
                'keywords' => array(
                    'page' =>    array('regexp' => '[0-9]+', 'param' => 'page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogcomments' => array(
                'controller' => 'comment',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_COMMENTS',$this->context->language->id)) ? $subAlias : 'comments'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogcategories' => array(
                'controller' => 'category',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORIES',$this->context->language->id)) ? $subAlias : 'categories'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogrss' => array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss'),
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogrsscategories'=>array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_CATEGORY',$this->context->language->id)) ? $subAlias : 'category').'/{id_category}-{url_alias}'.$subfix,
                'keywords' => array(
                    'id_category' =>    array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'url_alias'       =>   array('regexp' => '[_a-zA-Z0-9-]+','param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogrssauthors2'=>array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR2',$this->context->language->id)) ? $subAlias : 'community-author').'/{id_author}-{author_name}',
                'keywords' => array(
                    'id_author' =>    array('regexp' => '[0-9]+', 'param' => 'id_author'),
                    'author_name'       =>   array('regexp' => '(.)+','param' => 'author_name'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'is_customer' => 1,
                ),
            ),
            'categoryblogrssauthors'=>array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_AUTHOR',$this->context->language->id)) ? $subAlias : 'author').'/{id_author}-{author_name}',
                'keywords' => array(
                    'id_author' =>    array('regexp' => '[0-9]+', 'param' => 'id_author'),
                    'author_name'       =>   array('regexp' => '(.)+','param' => 'author_name'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                ),
            ),
            'categoryblogrssalatest'=>array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_LATEST',$this->context->language->id)) ? $subAlias : 'latest-posts'),
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'latest' => true,
                ),
            ),
            'categoryblogrsspopular'=>array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_POPULAR',$this->context->language->id)) ? $subAlias : 'popular-posts'),
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'popular' =>    true,
                ),
            ),
            'categoryblogrssfeatured'=>array(
                'controller' => 'rss',
                'rule' => $blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_FEATURED',$this->context->language->id)) ? $subAlias : 'featured-posts'),
                'keywords' => array(
                
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ybc_blog',
                    'featured' =>    true,
                ),
            ),
        );
        //echo '<pre>';
//        print_r(array_keys($routes));
//        echo '</pre>';
//        die('x');
        if(Configuration::get('PS_ROUTE_ybcblogmainpage')) {
            foreach($routes as $key => $r) {
                Configuration::deleteByName('PS_ROUTE_'.$key);
                unset($r);
            }
            
        }
        //die($blogAlias.'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS',$this->context->language->id)) ? $subAlias : 'rss').'/'.(($subAlias = Configuration::get('YBC_BLOG_ALIAS_RSS_LATEST',$this->context->language->id)) ? $subAlias : 'latest_posts'));
        return $routes;
    }
    public function setMetas()
    {
        $meta = array();
        if(trim(Tools::getValue('module'))!='ybc_blog')
            return;
        $id_lang = $this->context->language->id;
        $id_category = (int)Tools::getValue('id_category');
        $id_post = (int)Tools::getValue('id_post');
        if(!$id_post && Tools::getValue('post_url_alias'))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl ,'._DB_PREFIX_.'ybc_blog_post_shop ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.id_lang="'.(int)$this->context->language->id.'" AND pl.url_alias ="'.pSQL(Tools::getValue('post_url_alias')).'"');
        }
        if(!$id_category && Tools::getValue('category_url_alias'))
        {
            $id_category = (int)Db::getInstance()->getValue('SELECT cs.id_category FROM '._DB_PREFIX_.'ybc_blog_category_lang cl,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE cs.id_category=cl.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND cl.url_alias ="'.pSQL(Tools::getValue('category_url_alias')).'"');  
        }
        if($id_category || Tools::getValue('category_url_alias') )
        {
            if($this->itemExists('category','id_category', $id_category))
            {
                $category = $this->getCategoryById($id_category);
                if(trim($category['title']) || trim($category['meta_title']))
                    $meta['meta_title'] = trim($category['meta_title'])? $category['meta_title'] : $category['title'];
                if($category['meta_description'] && trim($category['meta_description']))
                    $meta['meta_description'] = $category['meta_description'];
                else
                    $meta['meta_description'] = trim($category['description']) ? Tools::substr(strip_tags($category['description']),0,300):'';
                $meta['meta_keywords'] = trim($category['meta_keywords']) ? $category['meta_keywords'] :'';
            }
            else
                $meta['meta_title'] = $this->l('Page not found');
                     
        }
        elseif($id_post  || Tools::getValue('post_url_alias'))
        {
            if($this->itemExists('post','id_post', $id_post))
            {
               $post = $this->getPostById($id_post);
                if(trim($post['title']) || trim($post['meta_title']))
                    $meta['meta_title'] = trim($post['meta_title']) ? $post['meta_title'] : $post['title'];
                if(trim($post['meta_description']))
                    $meta['meta_description'] = $post['meta_description'];
                else
                    $meta['meta_description'] = trim($post['short_description']) ? Tools::substr(strip_tags($post['short_description']),0,300):Tools::substr(strip_tags($post['description']),0,300);
                if(trim($post['meta_keywords']))
                    $meta['meta_keywords'] = $post['meta_keywords']; 
            }
            else
                $meta['meta_title'] = $this->l('Page not found');  
        }
        elseif(Tools::getValue('tag'))
        {
            $meta['meta_title'] = $this->l('Tag: ').' "'.Tools::getValue('tag').'"';
        }  
        elseif(Tools::getValue('latest'))
        {
            $meta['meta_title'] = $this->l('Latest posts');
            $meta['meta_description'] = strip_tags(Configuration::get('YBC_BLOG_SEO_LATEST',$id_lang));            
        }
        elseif(Tools::getValue('featured'))
        {
            $meta['meta_title'] = $this->l('Featured posts');
            $meta['meta_description'] = strip_tags(Configuration::get('YBC_BLOG_SEO_FEATURED',$id_lang));            
        }
        elseif(Tools::getValue('popular'))
        {
            $meta['meta_title'] = $this->l('Popular posts');
            $meta['meta_description'] = strip_tags(Configuration::get('YBC_BLOG_SEO_POPULAR',$id_lang));            
        }
        elseif(Tools::getValue('search'))
        {
            $meta['meta_title'] = $this->l('Search:').' "'.str_replace('+',' ',Tools::getValue('search')).'"';
            $meta['meta_description'] = strip_tags(Configuration::get('YBC_BLOG_SEO_SEARCH',$id_lang));            
                        
        } 
        elseif(Tools::getValue('year') && Tools::getValue('month'))
          $meta['meta_title'] = $this->l('Posted in :').' "'.Tools::getValue('year').' - '.$this->getMonthName(Tools::getValue('month')).'"';  
        elseif(Tools::getValue('year'))
          $meta['meta_title'] = $this->l('Posted in :').' "'.Tools::getValue('year').'"';  
        elseif(Tools::getValue('controller')=='gallery')
        {
            $meta['meta_title'] = $this->l('Gallery');
            $meta['meta_description'] = strip_tags(Configuration::get('YBC_BLOG_SEO_GALLERY',$id_lang));           
        }
        elseif(Tools::getValue('controller')=='comment')
        {
            $meta['meta_title'] = $this->l('All comments');
        } 
        elseif(Tools::getValue('id_author'))
        {
            if($employee = $this->getAuthorById(Tools::getValue('id_author'),Tools::getValue('is_customer')))                
            {
                $meta['meta_title'] = $this->l('Author: ').$employee['name'];
                $meta['meta_description'] = strip_tags($employee['description']);
            }
            else
                $meta['meta_title'] = $this->l('Page not found');
        } 
        elseif(Tools::getValue('controller')=='author')
        {
            $meta['meta_title'] = $this->l('Authors');
            $meta['meta_description'] = strip_tags(Configuration::get('YBC_BLOG_SEO_AUTHOR',$id_lang));  
        }
        elseif(Tools::getValue('controller')=='category')
        {
            $meta['meta_title'] = $this->l('All categories');
            $meta['meta_description'] = Configuration::get('YBC_BLOG_SEO_CATEGORIES',$id_lang) ? strip_tags(Configuration::get('YBC_BLOG_SEO_CATEGORIES',$id_lang)):'';                        
                        
        }
        elseif(Tools::getValue('controller')=='rss')
        {
            $meta['meta_title']= $this->l('RSS');
        }
        elseif(Tools::getValue('controller')=='managementblog')
        {
            $meta['meta_title'] = $this->l('My blog posts');
        }
        elseif(Tools::getValue('controller')=='managementcomments')
        {
            $meta['meta_title'] = $this->l('My blog comments');
        }
        elseif(Tools::getValue('controller')=='managementmyinfo')
        {
            $meta['meta_title']= $this->l('My blog info');
        }
        elseif(Tools::getValue('controller')=='blog')
        {
            if($id_author = (int)Tools::getValue('id_author'))
            {
                $employeePost = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$id_author.'" AND is_customer="'.(int)Tools::getValue('is_customer').'"');
                if($employeePost)
                {
                    $meta['meta_title'] = $this->l('Author').' '.$employeePost['name'];                    
                    $meta['meta_description'] = $employeePost['description'];
                                    
                }                
            }
            else
            {
                $meta['meta_title'] = Configuration::get('YBC_BLOG_META_TITLE',$id_lang);
                $meta['meta_description'] = Configuration::get('YBC_BLOG_META_DESCRIPTION',$id_lang);
                $meta['meta_keywords'] = Configuration::get('YBC_BLOG_META_KEYWORDS',$id_lang);
            }
            
        }
        if(!isset($meta['meta_title']))
            $meta['meta_title']='';
        if(!isset($meta['meta_description']))
            $meta['meta_description']='';
        if(!isset($meta['meta_keywords']))
            $meta['meta_keywords']='';
        if(Configuration::get('YBC_BLOG_RTL_MODE')=='auto' && isset($this->context->language->is_rtl) && $this->context->language->is_rtl || Configuration::get('YBC_BLOG_RTL_MODE')=='rtl')
            $rtl = true;
        else
            $rtl = false;            
        if($this->is17)
        {
            $body_classes = array(
                'lang-'.$this->context->language->iso_code => true,
                'lang-rtl' => (bool) $this->context->language->is_rtl,
                'country-'.$this->context->country->iso_code => true,                                   
                'ybc_blog' => true,
                'ybc_blog_rtl' => $rtl,
            );
            $page = array(
                'title' => '',
                'canonical' => '',
                'meta' => array(
                    'title' => $meta['meta_title'],
                    'description' => $meta['meta_description'],
                    'keywords' => $meta['meta_keywords'],
                    'robots' => 'index',
                ),
                'page_name' => 'ybc_blog_page',
                'body_classes' => $body_classes,
                'admin_notifications' => array(),
            ); 
            $this->context->smarty->assign(array('page' => $page)); 
        }    
        else
        {
            $this->context->smarty->assign($meta);
            if($rtl) 
                $this->context->smarty->assign(array(
                    'body_classes' => array('ybc_blog_rtl'),
                ));
        }                
    }
    private function getAuthorById($id_author,$is_customer=0)
    {
        if($is_customer)
        {
            $author= Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'customer c
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee be ON (c.id_customer=be.id_employee AND be.is_customer=1)
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee_lang bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
            WHERE c.id_customer = '.(int)$id_author);
        }
        else
            $author= Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'employee e
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee be ON (e.id_employee=be.id_employee AND be.is_customer=0)
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee_lang bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
            WHERE e.id_employee = '.(int)$id_author);
        $params=array();
        $params['id_author'] = $id_author;
        $params['is_customer']=$is_customer;
        if($author)
        {
            if(!$author['name'])
                $author['name']=trim(Tools::strtolower($author['firstname'].' '.$author['lastname']));
            $params['alias'] = str_replace(' ','-',$author['name']);
            $author['alias'] = $params['alias'];
            $author['author_link']= $this->getLink('blog',$params);
        }
        return $author;    
    }
    public function getBreadCrumb()
    {
        $id_post = (int)Tools::getValue('id_post');
        if(!$id_post && Tools::getValue('post_url_alias'))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl ,'._DB_PREFIX_.'ybc_blog_post_shop ps  WHERE ps.id_shop="'.(int)$this->context->shop->id.'" AND ps.id_post=pl.id_post AND pl.url_alias ="'.pSQL(Tools::getValue('post_url_alias')).'"');
        }
        $id_category = (int)Tools::getValue('id_category');
        if(!$id_category && Tools::getValue('category_url_alias'))
        {
            $id_category = (int)Db::getInstance()->getValue('SELECT cs.id_category FROM '._DB_PREFIX_.'ybc_blog_category_lang cl,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE cs.id_category=cl.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND cl.url_alias ="'.pSQL(Tools::getValue('category_url_alias')).'"');   
        }
        $id_author = (int)Tools::getValue('id_author');
        $is_customer= (int)Tools::getValue('is_customer');
        $nodes = array();
        $nodes[] = array(
            'title' => $this->l('Home'),
            'url' => $this->context->link->getPageLink('index', true),
        );
        $nodes[] = array(
            'title' => $this->l('Blog'),
            'url' => $this->getLink('blog')
        );
        if(Tools::getValue('controller')=='category')
        {
            $nodes[] = array(
                'title' => $this->l('All categories'),
                'url' => $this->getLink('category')
            );
        }
        if(Tools::getValue('controller')=='comment')
        {
            $nodes[] = array(
                'title' => $this->l('All Comments'),
                'url' => $this->getLink('comment')
            );
        }
        if($id_category && $category = $this->getCategoryById($id_category))
        {
            $nodes[] = array(
                'title' => $category['title'],
                'url' => $this->getLink('blog',array('id_category' => $id_category)),                   
            );
        }
        if($id_author && $author = $this->getAuthorById($id_author,$is_customer))
        {
            $nodes[] = array(
                'title' => $this->l('Authors'),
                'url' => $this->getLink('author'),               
            );
            $nodes[] = array(
                'title' => trim(Tools::ucfirst($author['name'])),
                'url' => $this->getLink('blog',array('id_author' => $id_author)),               
            );
        }
        elseif(Tools::getValue('controller')=='author')
        {
             $nodes[] = array(
                    'title' => $this->l('Authors'),
                    'url' => $this->getLink('author'),               
             );
        }
        if($id_post && $post = $this->getPostById($id_post))
        {
            if($post['id_category_default'])
                $id_category_default= $post['id_category_default'];
            else
            {
                $sql = 'SELECT c.id_category FROM '._DB_PREFIX_.'ybc_blog_category c
                INNER JOIN '._DB_PREFIX_.'ybc_blog_category_shop cs ON (c.id_category =cs.id_category)
                INNER JOIN '._DB_PREFIX_.'ybc_blog_post_category pc on (pc.id_category =cs.id_category)
                WHERE pc.id_post="'.(int)$id_post.'" AND cs.id_shop="'.(int)$this->context->shop->id.'" ORDER BY c.sort_order ASC';
                $id_category_default = Db::getInstance()->getValue($sql);
            }
            if($id_category_default && $category = $this->getCategoryById($id_category_default))
            {
                $nodes[] = array(
                    'title' => $category['title'],
                    'url' => $this->getLink('blog',array('id_category' => $id_category_default)),                   
                );
            }
            $nodes[] = array(
                'title' => $post['title'],
                'url' => $this->getLink('blog',array('id_post' => $id_post)),                   
            );
        }
        if(Tools::getValue('controller')=='rss')
        {
            $nodes[] = array(
                'title' => $this->l('Rss'),
                'url' => $this->getLink('rss'),                   
            );
        }
        if(Tools::getValue('controller') == 'gallery')
        {
            $nodes[] = array(
                'title' => $this->l('Gallery'),
                'url' => $this->getLink('gallery'),                   
            );
        }
        if(Tools::getValue('controller') == 'blog' && Tools::getValue('latest'))
        {
            $nodes[] = array(
                'title' => $this->l('Latest posts'),
                'url' => $this->getLink('blog',array('latest' => true)),                   
            );
        }
        if(Tools::getValue('controller') == 'blog' && Tools::getValue('popular'))
        {
            $nodes[] = array(
                'title' => $this->l('Popular posts'),
                'url' => $this->getLink('blog',array('popular' => true)),                   
            );
        }
        if(Tools::getValue('controller') == 'blog' && Tools::getValue('featured'))
        {
            $nodes[] = array(
                'title' => $this->l('Featured posts'),
                'url' => $this->getLink('blog',array('featured' => true)),                   
            );
        }
        if(Tools::getValue('controller') == 'blog' && Tools::getValue('tag'))
        {
            $nodes[] = array(
                'title' => $this->l('Blog tag').': '.Tools::getValue('tag'),
                'url' => $this->getLink('blog',array('tag' => Tools::getValue('tag'))),                    
            );
        }
        if(Tools::getValue('controller') == 'blog' && Tools::getValue('search'))
        {
            $nodes[] = array(
                'title' => $this->l('Blog search').': '.str_replace('+',' ',Tools::getValue('search')),
                'url' => $this->getLink('blog',array('search' => Tools::getValue('search'))),                     
            );
        }
        if(Tools::getValue('controller') == 'blog' && Tools::getValue('month') && Tools::getValue('year'))
        {
            $nodes[] = array(
                'title' => Tools::getValue('month').'-'.Tools::getValue('year'),
                'url' => $this->getLink('blog',array('month' => Tools::getValue('month'),'year'=>Tools::getValue('year'))),                     
            );
        }
        elseif(Tools::getValue('controller') == 'blog' && Tools::getValue('year'))
        {
            $nodes[] = array(
                'title' =>Tools::getValue('year'),
                'url' => $this->getLink('blog',array('year'=>Tools::getValue('year'))),                     
            );
        }
        if($this->is17)
            return array('links' => $nodes,'count' => count($nodes));
        return $this->displayBreadcrumb($nodes);
    }
    public function displayBreadcrumb($nodes)
    {
        $this->smarty->assign(array('nodes' => $nodes));
        return $this->display(__FILE__, 'nodes.tpl');
    }
    private function _installTabs()
    {
        $languages = Language::getLanguages(false);
        $tab = new Tab();
        $tab->class_name = 'AdminYbcBlog';
        $tab->module = 'ybc_blog';
        $tab->id_parent = 0;            
        foreach($languages as $lang){
                $tab->name[$lang['id_lang']] = $this->l('Blog');
        }
        $tab->save();
        $blogTabId = Tab::getIdFromClassName('AdminYbcBlog');
        if($blogTabId)
        {
            $ybc_defines = new Ybc_blog_defines();
            foreach($ybc_defines->subTabs as $tabArg)
            {
                if(!Tab::getIdFromClassName($tabArg['class_name']))
                {
                    $tab = new Tab();
                    $tab->class_name = $tabArg['class_name'];
                    $tab->module = 'ybc_blog';
                    $tab->id_parent = $blogTabId; 
                    $tab->icon=$tabArg['icon'];             
                    foreach($languages as $lang){
                            $tab->name[$lang['id_lang']] = $tabArg['tab_name'];
                    }
                    $tab->save();
                }
            }                
        }            
        return true;
    }
    private function _uninstallTabs()
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
        if($tabId = Tab::getIdFromClassName('AdminYbcBlog'))
        {
            $tab = new Tab($tabId);
            if($tab)
                $tab->delete();
        }
        return true;
    }
    public function getRelatedPosts($id_post, $tags, $id_lang = false)
    {
        if(!Configuration::get('YBC_BLOG_DISPLAY_RELATED_POSTS'))
            return false;
        if(!$id_lang)
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $tagElements = array();
        $tagElements[]=0;
        $limit = (int)Configuration::get('YBC_BLOG_RELATED_POST_NUMBER') > 0 ? (int)Configuration::get('YBC_BLOG_RELATED_POST_NUMBER') : 5;
        if($tags && is_array($tags))
        {
            
            foreach($tags as $tag)
                if($tag)
                    $tagElements[] = $tag['tag'];         
        }
        $sql = "SELECT pl.title, pl.short_description,pl.description, p.*
            FROM "._DB_PREFIX_."ybc_blog_post p
            INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps ON (p.id_post= ps.id_post)
            LEFT JOIN "._DB_PREFIX_."ybc_blog_tag t ON p.id_post = t.id_post
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON pl.id_post = p.id_post AND pl.id_lang = ".(int)$id_lang."
            LEFT JOIN "._DB_PREFIX_."ybc_blog_post_category pc ON (pc.id_post=p.id_post)
            WHERE ps.id_shop='".(int)$this->context->shop->id."' AND  p.enabled=1 AND (t.tag IN ('".implode("','",array_map('pSQL',$tagElements))."') OR pc.id_category IN (SELECT id_category FROM "._DB_PREFIX_."ybc_blog_post_category WHERE id_post=".(int)$id_post.")) AND p.id_post != ".(int)$id_post."
            GROUP BY pl.id_post
            ORDER BY p.sort_order ASC, p.datetime_added DESC
            LIMIT 0,".(int)$limit."
            ";                   
        $posts = Db::getInstance()->executeS($sql);            
        return $posts;
    }
    public function getInternalStyles()
    {
        $color = Configuration::get('YBC_BLOG_CUSTOM_COLOR');
        if(!$color) 
            $color = '#FF4C65';
        $color_hover= Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER');
        if(!$color_hover)
            $color_hover='#FF4C65';
        $css = file_exists(dirname(__FILE__).'/views/css/dynamic_style.css') ? Tools::file_get_contents(dirname(__FILE__).'/views/css/dynamic_style.css') : ''; 
        if($css)
            $css = str_replace(array('[color]','[color_hover]'),array($color,$color_hover),$css);
        $id_category = (int)Tools::getValue('id_category');
        $id_post = (int)Tools::getValue('id_post');
        if(!$id_post && Tools::getValue('post_url_alias'))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl ,'._DB_PREFIX_.'ybc_blog_post_shop ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.id_lang="'.(int)$this->context->language->id.'" AND pl.url_alias ="'.pSQL(Tools::getValue('post_url_alias')).'"');
        }
        if(!$id_category && Tools::getValue('category_url_alias'))
        {
            $id_category = (int)Db::getInstance()->getValue('SELECT cs.id_category FROM '._DB_PREFIX_.'ybc_blog_category_lang cl,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE cs.id_category=cl.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND cl.url_alias ="'.pSQL(Tools::getValue('category_url_alias')).'"');  
        }
        if(Tools::isSubmit('fc') && Tools::getValue('fc') && Tools::getValue('module')=='ybc_blog')
        {
            if($id_category)
                $current_link= $this->getLink(Tools::getValue('controller'),array('id_category' => $id_category));
            elseif($id_post)
                $current_link= $this->getLink(Tools::getValue('controller'),array('id_post' => $id_post));
            elseif($id_author=Tools::getValue('id_author'))
                $current_link=$this->getLink(Tools::getValue('controller'),array('id_author'=>$id_author));
            elseif($tag=Tools::getValue('tag'))
                $current_link=$this->getLink(Tools::getValue('controller'),array('tag'=>$tag));
            elseif($search=Tools::getValue('search'))
                $current_link=$this->getLink(Tools::getValue('controller'),array('search'=>$search));
            elseif($latest=Tools::getValue('latest'))
                $current_link=$this->getLink(Tools::getValue('controller'),array('latest'=>$latest));
            else
                $current_link=$this->getLink(Tools::getValue('controller'));
        }
        $this->smarty->assign(
            array(
                'css' => $css,
                'link_current'=>isset($current_link)?$current_link:false,
                'baseAdminDir' => __PS_BASE_URI__.'/',
                'url_path' => $this->_path,
                'ybc_blog_product_category' => Tools::getValue('id_category'),
            )
        );
        if($id_post && Tools::getValue('module')==$this->name && Tools::getValue('controller')=='blog')
        {
            $post = $this->getPostById($id_post);
            if($post)
            {
                $post['img_name'] = isset($post['image']) ? $post['image'] : '';
                if($post['image'])
                    $post['image'] =(Configuration::get('PS_SSL_ENABLED')? 'https://' : 'http://').$this->context->shop->domain.$this->context->shop->getBaseURI().'modules/'.$this->name.'/views/img/post/'.$post['image'];                            
                if($post['thumb'])
                    $post['thumb'] = (Configuration::get('PS_SSL_ENABLED')? 'https://' : 'http://').$this->context->shop->domain.$this->context->shop->getBaseURI().'modules/'.$this->name.'/views/img/post/thumb/'.$post['thumb'];
                $post['link'] = $this->getLink('blog',array('id_post'=>$post['id_post']));
                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);  
                $post['products'] = $post['products'] ? $this->getRelatedProductByProductsStr($post['products']) : false;  
                $params = array(); 
                $params['id_author'] = (int)$post['added_by'];
                $params['is_customer'] =(int)Tools::getValue('is_customer');
                $employee = $this->getAuthorById($params['id_author'],Tools::getValue('is_customer'));
                
                if($employee)
                    $params['alias'] = str_replace(' ','-',trim(Tools::strtolower($employee['firstname'].' '.$employee['lastname']))); 
                $post['author_link'] = $this->getLink('blog', $params);
                $this->context->smarty->assign(
                    array(
                        'blog_post_header'=>$post,
                    )
                );
            }
        }
        $this->context->smarty->assign(
            array(
                'YBC_BLOG_CAPTCHA_TYPE' => Configuration::get('YBC_BLOG_CAPTCHA_TYPE'),
                'YBC_BLOG_CAPTCHA_SITE_KEY' => Configuration::get('YBC_BLOG_CAPTCHA_TYPE')=='google' ? Configuration::get('YBC_BLOG_CAPTCHA_SITE_KEY') : Configuration::get('YBC_BLOG_CAPTCHA_SITE_KEY3'),
            )
        );
        return $this->display(__FILE__, 'head.tpl');;
    } 
    public function ajaxCustomerSearch()
    {
       if(!Tools::isSubmit('ajaxCustomersearch'))
       {
            return '';
       } 
       $query = Tools::getValue('q', false);
       if (!$query OR $query == '' OR (Tools::strlen($query) < 3 AND !Validate::isUnsignedId($query) ))
        	die();
       $filter ='AND (';
       $filter .= " c.id_customer = ".(int)trim(urldecode($query));
       $filter .= " OR (CONCAT(c.firstname,' ',c.lastname) like '".pSQL($query)."%' OR be.name like'".pSQL($query)."%')";                
       $filter .= " OR c.email like '".pSQL($query)."%'";
       $filter .=')';
       $customers= $this->getCustomersFilter($filter);
        if($customers)
        {
        	foreach ($customers as $customer)
        	{
        	   echo $customer['id_customer'].'|'.($customer['name'] ? $customer['name'] : $customer['customer'] ).'|'.$customer['email'].'|'.$this->context->link->getAdminLink('AdminCustomers').'&id_customer='.(int)$customer['id_customer'].'&updatecustomer'."\n";	
        	}
        }
        die();
    }   
    public function ajaxPostSearch()
    {
        if(!Tools::isSubmit('ajaxpostsearch'))
        {
            return '';
        }
        $query = Tools::getValue('q', false);
        if (!$query OR $query == '' OR (Tools::strlen($query) < 3 AND !Validate::isUnsignedId($query) ))
        	die();
        $posts= $this->getPostsWithFilter(' AND ( p.id_post="'.(int)$query.'" OR pl.title like "%'.pSQL($query).'%")');
        if($posts)
        {
        	foreach ($posts as $post)
        	{
        	   echo $post['title'].'|'.$post['id_post'].'|'.($post['thumb'] ?  $this->getBaseLink().'modules/ybc_blog/views/img/post/thumb/'.$post['thumb'] : $this->getBaseLink().'modules/ybc_blog/views/img/post/'.$post['image'])."\n";	
        	}
        }
        die();
    }    
    public function ajaxProductSearch()
    {
        if(!Tools::isSubmit('ajaxproductsearch'))
            return;
        $query = Tools::getValue('q', false);
        if (!$query OR $query == '' OR Tools::strlen($query) < 1)
        	die();        
        /*
         * In the SQL request the "q" param is used entirely to match result in database.
         * In this way if string:"(ref : #ref_pattern#)" is displayed on the return list, 
         * they are no return values just because string:"(ref : #ref_pattern#)" 
         * is not write in the name field of the product.
         * So the ref pattern will be cut for the search request.
         */
        if($pos = strpos($query, ' (ref:'))
        	$query = Tools::substr($query, 0, $pos);
        
        $excludeIds = Tools::getValue('excludeIds', false);
        //if ($excludeIds && $excludeIds != 'NaN')
//            	$excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));
//            else
//            	$excludeIds = '';
        
        // Excluding downloadable products from packs because download from pack is not supported
        $excludeVirtuals = (bool)Tools::getValue('excludeVirtuals', false);
        $exclude_packs = (bool)Tools::getValue('exclude_packs', false);
        
        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, MAX(image_shop.`id_image`) id_image, il.`legend`
        		FROM `'._DB_PREFIX_.'product` p
        		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('pl').')
        		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
        		Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
        		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)Context::getContext()->language->id.')
        		WHERE (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\' OR p.id_product="'.(int)$query.'")'.
        		(!empty($excludeIds) ? ' AND p.id_product NOT IN ('.implode(',', array_map('intval', explode(',', $excludeIds))).') ' : ' ').
        		($excludeVirtuals ? 'AND p.id_product NOT IN (SELECT pd.id_product FROM `'._DB_PREFIX_.'product_download` pd WHERE (pd.id_product = p.id_product))' : '').
        		($exclude_packs ? 'AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '').
        		' AND p.active =1 GROUP BY p.id_product';
        
        $items = Db::getInstance()->executeS($sql);
        
        $acc = (bool)Tools::isSubmit('excludeIds');
        if(version_compare(_PS_VERSION_, '1.7', '>='))
            $type_image= ImageType::getFormattedName('home');
        else
            $type_image= ImageType::getFormatedName('home');
        if ($items && $acc)
        	foreach ($items AS $item)
        		echo trim(str_replace('|','-',$item['name'])).(!empty($item['reference']) ? ' (ref: '.str_replace('|','-',$item['reference']).')' : '').'|'.(int)($item['id_product']).'|'.str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($item['link_rewrite'], $item['id_image'], $type_image))."\n";
        elseif ($items)
        {
        	// packs
        	$results = array();
        	foreach ($items AS $item)
        	{
        		$product = array(
        			'id' => (int)($item['id_product']),
        			'name' => $item['name'],
        			'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
        			'image' => str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($item['link_rewrite'], $item['id_image'], $type_image)),
        		);
        		array_push($results, $product);
        	}
        	echo Tools::jsonEncode($results);
        }
        else
        	Tools::jsonEncode(new stdClass);    
        die;   
   }
   public function getProfileEmployee($id_employee)
   {
        $profile_employee = Db::getInstance()->getValue('SELECT profile_employee FROM '._DB_PREFIX_.'ybc_blog_employee where id_employee='.(int)$id_employee.' AND is_customer=0');
        return explode(',',$profile_employee);
   }
   public function getBlogCategoriesTreeFontEnd($id_root,$active=true,$id_lang=null,$id_category=0)
   {
        $tree = array();
        if(is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        if($id_root==0)
        {
            $cat = array(
                'id_category' => 0,
                'title' => $this->l('Root'),
            );            
            $children = $this->getChildrenBlogCategoriesFontEnd($id_root, $active, $id_lang,$id_category);
            $temp = array();
            if($children)
            {
                foreach($children as &$child)
                {
                    $arg = $this->getBlogCategoriesTreeFontEnd($child['id_category'], $active, $id_lang,$id_category);
                    if($arg && isset($arg[0]))
                    {
                        $arg[0]['link'] = $this->getLink('blog',array('id_category'=>$child['id_category']));
                        $arg[0]['link_rss'] = $this->context->link->getModuleLink($this->name,'rss',array('id_category'=>$child['id_category']));
                        if($child['thumb'] && file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$child['thumb']))
                            $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/thumb/'.$child['thumb'].'" style="width:10px;"/>';
                        elseif($child['image'] && file_exists(dirname(__FILE__).'/views/img/category/'.$child['image']))
                            $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/'.$child['image'].'" style="width:10px;"/>';
                        if($this->checkCategoryEnabled($child['id_category']))
                            $temp[] = $arg[0];
                    }
                        
                }                    
            }
            $cat['children'] = $temp;
            $tree[] = $cat;
        }
        else
        {
            $sql = "SELECT c.id_category, cl.title,c.image
                FROM "._DB_PREFIX_."ybc_blog_category c
                LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = ".(int)$id_lang."
                WHERE c.id_category = ".(int)$id_root." ".($active ? " AND  c.enabled = 1" : "")." GROUP BY c.id_category";
            if($category = Db::getInstance()->getRow($sql))
            {            
                $cat = array(
                                'id_category' => $id_root,
                                'title' => $category['title'],
                                'count_posts' => $this->countPostsWithFilter(' AND pc.id_category="'.(int)$id_root.'" AND p.enabled=1'),
                            );  
                $children = $this->getChildrenBlogCategoriesFontEnd($id_root, $active, $id_lang,$id_category);
                $temp = array();
                if($children)
                {
                    foreach($children as &$child)
                    {
                        $arg = $this->getBlogCategoriesTreeFontEnd($child['id_category'], $active, $id_lang,$id_category);
                        if($arg && isset($arg[0]))
                        {
                            $arg[0]['link'] = $this->getLink('blog',array('id_category'=>$child['id_category']));
                            $arg[0]['link_rss'] = $this->context->link->getModuleLink($this->name,'rss',array('id_category'=>$child['id_category']));
                            if($child['thumb'] && file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$child['thumb']))
                                $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/thumb/'.$child['thumb'].'" style="width:10px;"/>';
                            elseif($child['image'] && file_exists(dirname(__FILE__).'/views/img/category/'.$child['image']))
                                $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/'.$child['image'].'" style="width:10px;"/>';
                            if($this->checkCategoryEnabled($child['id_category']))
                                $temp[] = $arg[0];
                        }
                            
                    }                    
                }
                $cat['children'] = $temp;
                $tree[] = $cat;
            }
        }
        return $tree; 
   }
   public function checkCategoryEnabled($id_category){
        $categories_enabled= explode(',',Configuration::get('YBC_BLOG_CATEGOGY_CUSTOMER'));
        if(in_array($id_category,$categories_enabled))
            return true;
        elseif($childs= Db::getInstance()->executeS('SELECT id_category FROM '._DB_PREFIX_.'ybc_blog_category WHERE id_parent="'.(int)$id_category.'"'))
        {
            foreach($childs as $child)
                if($this->checkCategoryEnabled($child['id_category']))
                    return true;
        }
        return false; 
   }
   public function getBlogCategoriesTree($id_root,$active=true,$id_lang=null,$id_category=0,$link=true)
   {
        if(is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $tree=array();
        if($id_root==0)
        {
            $cat = array(
                'id_category' => 0,
                'title' => $this->l('Root'),
            );            
            $children = $this->getChildrenBlogCategories($id_root, $active, $id_lang,$id_category);
            $temp = array();
            if($children)
            {
                foreach($children as &$child)
                {
                    $arg = $this->getBlogCategoriesTree($child['id_category'], $active, $id_lang,$id_category,$link);
                    if($arg && isset($arg[0]))
                    {
                        if($link)
                        {
                            $arg[0]['link'] = $this->getLink('blog',array('id_category'=>$child['id_category']));
                            $arg[0]['link_rss'] =$this->context->link->getModuleLink($this->name,'rss',array('id_category'=>$child['id_category']));
                        }
                        else
                        {
                            $arg[0]['link']='#';
                            $arg[0]['link_rss']='#';
                        }
                        if($child['thumb'] && file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$child['thumb']))
                            $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/thumb/'.$child['thumb'].'" style="width:10px;"/>';
                        elseif($child['image'] && file_exists(dirname(__FILE__).'/views/img/category/'.$child['image']))
                            $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/'.$child['image'].'" style="width:10px;"/>';
                        $temp[] = $arg[0];
                    }
                        
                }                    
            }
            $cat['children'] = $temp;
            $tree[] = $cat;
        }
        else
        {
            $sql = "SELECT c.id_category, cl.title,c.image
                FROM "._DB_PREFIX_."ybc_blog_category c
                LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = ".(int)$id_lang."
                WHERE c.id_category = ".(int)$id_root." ".($active ? " AND  c.enabled = 1" : "")." GROUP BY c.id_category";
            if($category = Db::getInstance()->getRow($sql))
            {            
                $cat = array(
                                'id_category' => $id_root,
                                'title' => $category['title'],
                                'count_posts' => $this->countPostsWithFilter(' AND pc.id_category="'.(int)$id_root.'" AND p.enabled=1'),
                            );  
                $children = $this->getChildrenBlogCategories($id_root, $active, $id_lang,$id_category);
                $temp = array();
                if($children)
                {
                    foreach($children as &$child)
                    {
                        $arg = $this->getBlogCategoriesTree($child['id_category'], $active, $id_lang,$id_category,$link);
                        if($arg && isset($arg[0]))
                        {
                            if($link)
                            {
                                $arg[0]['link'] = $this->getLink('blog',array('id_category'=>$child['id_category']));
                                $arg[0]['link_rss'] = $this->context->link->getModuleLink($this->name,'rss',array('id_category'=>$child['id_category']));
                            }
                            else
                            {
                                $arg[0]['link'] ='#';
                                $arg[0]['link_rss']='#';
                            }
                            if($child['thumb'] && file_exists(dirname(__FILE__).'/views/img/category/thumb/'.$child['thumb']))
                                $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/thumb/'.$child['thumb'].'" style="width:10px;"/>';
                            elseif($child['image'] && file_exists(dirname(__FILE__).'/views/img/category/'.$child['image']))
                                $arg[0]['thumb_link'] = '<img src="'.$this->_path.'views/img/category/'.$child['image'].'" style="width:10px;"/>';
                            $temp[] = $arg[0];
                        }
                            
                    }                    
                }
                $cat['children'] = $temp;
                $tree[] = $cat;
            }
        }
        return $tree; 
   }
   public function getChildrenBlogCategoriesFontEnd($id_root, $active=true, $id_lang=null,$id_category=0)
   {
        if(is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $sql = "SELECT c.id_category, cl.title,c.image,c.thumb
                FROM "._DB_PREFIX_."ybc_blog_category c
                LEFT JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category=cs.id_category)
                LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = ".(int)$id_lang."
                WHERE c.id_parent = ".(int)$id_root." ".($active ? " AND  c.enabled = 1" : "").($id_category?' AND c.id_category <'.(int)$id_category :'')." AND cs.id_shop='".(int)$this->context->shop->id."' GROUP BY c.id_category";
        return Db::getInstance()->executeS($sql);
   }
   public function getChildrenBlogCategories($id_root, $active=true, $id_lang=null,$id_category=0)
   {
        if(is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $sql = "SELECT c.id_category, cl.title,c.image,c.thumb
                FROM "._DB_PREFIX_."ybc_blog_category c
                LEFT JOIN "._DB_PREFIX_."ybc_blog_category_shop cs ON (c.id_category=cs.id_category)
                LEFT JOIN "._DB_PREFIX_."ybc_blog_category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = ".(int)$id_lang."
                WHERE c.id_parent = ".(int)$id_root." ".($active ? " AND  c.enabled = 1" : "").($id_category?' AND c.id_category <'.(int)$id_category :'')." AND cs.id_shop='".(int)$this->context->shop->id."' GROUP BY c.id_category";
        return Db::getInstance()->executeS($sql);
   }
   public function getBlogCategoriesDropdown($blogcategories, &$depth_level = -1,$selected_blog_category=0)
   {        
        if($blogcategories)
        {
            $depth_level++;
            foreach($blogcategories as $category)
            {
                if((!$this->depthLevel || $this->depthLevel && (int)$depth_level <= $this->depthLevel))
                {
                    $levelSeparator = '';
                    if($depth_level >= 1)
                    {
                        for($i = 0; $i <= $depth_level-1; $i++)
                        {
                            $levelSeparator .= $this->prefix;
                        }
                    }       
                    if($category['id_category'] >=0)
                        $this->blogCategoryDropDown .= $this->displayBlogOption((int)$selected_blog_category,(int)$category['id_category'],$depth_level,$levelSeparator,$category['title']);
                    if(isset($category['children']) && $category['children'])
                    {                        
                        $this->getBlogCategoriesDropdown($category['children'], $depth_level,$selected_blog_category);
                    }   
                }                                 
            } 
            $depth_level--;           
        }
    }
    public function displayBlogOption($selected_blog_category,$id_category,$depth_level,$levelSeparator,$title)
    {
        $this->context->smarty->assign(array(
            'selected_blog_category' => $selected_blog_category,
            'id_category' => $id_category,
            'depth_level' => $depth_level,
            'levelSeparator' => $levelSeparator,
            'title' => $title,
        ));
        return $this->display(__FILE__,'blogoption.tpl');
    }
    public function checkProfileEmployee($id_employee,$profile)
    {
        $employee = new Employee($id_employee);
        if($employee->id_profile==1)
            return true;
        $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$id_employee.' AND is_customer=0 AND status<=0');
        if($id_employee_post)
            return false;
        $profile_employee= Db::getInstance()->getValue('SELECT profile_employee FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$id_employee.' AND is_customer=0');
        if($profile_employee)
        {   
            $profiles = explode(',',$profile_employee);
            if(in_array('All tabs',$profiles) || in_array($profile,$profiles))
                return true;
            else
                return false;
        }
        return false;
    }
    public function hookDisplayFooterProduct($params)
    {
        if(!Configuration::get('YBC_BLOG_DISPLAY_PRODUCT_PAGE'))
            return '';
        $limit = (int)Configuration::get('YBC_BLOG_NUMBER_POST_IN_PRODUCT') > 0 ? (int)Configuration::get('YBC_BLOG_NUMBER_POST_IN_PRODUCT') : 5;
        $sql ="SELECT * FROM `"._DB_PREFIX_."ybc_blog_post` p
        INNER JOIN "._DB_PREFIX_."ybc_blog_post_shop ps ON (ps.id_post= p.id_post AND id_shop='".(int)$this->context->shop->id."')
        LEFT JOIN "._DB_PREFIX_."ybc_blog_post_lang pl ON (p.id_post=pl.id_post)
        WHERE p.enabled=1 AND FIND_IN_SET('".(int)Tools::getValue('id_product')."', REPLACE(products,'-', ',')) AND pl.id_lang=".(int)$this->context->language->id.' LIMIT 0,'.(int)$limit;
        $posts= Db::getInstance()->executeS($sql);
        if($posts)
        {
            foreach($posts as &$rpost)
                if($rpost['image'])
                {
                    $rpost['image'] = $this->blogDir.'views/img/post/'.$rpost['image'];
                    if($rpost['thumb'])
                        $rpost['thumb'] = $this->blogDir.'views/img/post/thumb/'.$rpost['thumb'];
                    else
                        $rpost['thumb'] =$this->blogDir.'views/img/post/'.$rpost['image'];
                    $rpost['link'] =   $this->getLink('blog',array('id_post'=>$rpost['id_post']));
                    $rpost['categories'] = $this->getCategoriesByIdPost($rpost['id_post'],false,true); 
                    $rpost['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$rpost['id_post'].' AND approved=1');
                    $rpost['liked'] = $this->isLikedPost($rpost['id_post']);                        
                }
                else
                {
                    $rpost['image'] = '';
                    if($rpost['thumb'])
                        $rpost['thumb'] = $this->blogDir.'views/img/post/thumb/'.$rpost['thumb'];
                    else
                        $rpost['thumb'] = '';
                    $rpost['link'] =   $this->getLink('blog',array('id_post'=>$rpost['id_post']));
                    $rpost['categories'] = $this->getCategoriesByIdPost($rpost['id_post'],false,true); 
                    $rpost['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$rpost['id_post'].' AND approved=1');
                    $rpost['liked'] = $this->isLikedPost($rpost['id_post']);  
                }                        
        }
        $this->context->smarty->assign(
            array(
                'posts'=>$posts,
                'image_folder' => $this->blogDir.'views/img/',
                'display_desc' => Configuration::get('YBC_BLOG_PRODUCT_PAGE_DISPLAY_DESC'), 
                'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                'show_featured_post' => (int)Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false, 
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),   
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')),  
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
            )
        );
        return $this->display(__FILE__,'product-post.tpl');
    }
   public function displayBlogCategoryTre($blockCategTree,$selected_categories,$name='',$disabled_categories=array())
    {
        if($id_post = Tools::getValue('id_post'))
        {
            $post = new Ybc_blog_post_class($id_post);
            $id_category_default= $post->id_category_default;
        }
        else
            $id_category_default=0;
        $this->context->smarty->assign(
            array(
                'blockCategTree'=> $blockCategTree,
                'branche_tpl_path_input'=> _PS_MODULE_DIR_.'ybc_blog/views/templates/hook/category-tree-blog.tpl',
                'selected_categories'=>$selected_categories,
                'disabled_categories' => $disabled_categories,
                'id_category_default' => $id_category_default,
                'name'=>$name ? $name :'blog_categories',
            )
        );
        return $this->display(__FILE__, 'categories_blog.tpl');
    }
    public function hookBlogArchivesBlock()
    {
        $sql='SELECT count(*) as total_post,YEAR(p.datetime_added) as year_add 
        FROM '._DB_PREFIX_.'ybc_blog_post p
        LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
        LEFT JOIN '._DB_PREFIX_.'employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
        LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
        WHERE (ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND p.enabled=1 GROUP BY year_add ORDER BY year_add DESC';
        $years = Db::getInstance()->executeS($sql);
        if($years)
        {
            foreach($years as &$year)
            {
                $sql ='SELECT count(*) as total_post, MONTH(p.datetime_added) as month_add 
                FROM '._DB_PREFIX_.'ybc_blog_post p
                LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer=p.added_by AND p.is_customer=1)
                LEFT JOIN '._DB_PREFIX_.'employee e ON (e.id_employee=p.added_by AND p.is_customer=0)
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_employee ybe ON ((ybe.id_employee=c.id_customer AND ybe.is_customer=1) OR (ybe.id_employee=e.id_employee AND ybe.is_customer=0))
                WHERE (ybe.status>=0 OR ybe.status is NULL OR e.id_profile=1) AND enabled=1 AND YEAR(datetime_added)="'.pSQL($year['year_add']).'" GROUP BY month_add ORDER BY month_add DESC';
                $year['months'] = Db::getInstance()->executeS($sql);
                $year['link'] = $this->getLink('blog',array('year'=>$year['year_add']));
                if($year['months'])
                {
                    foreach($year['months'] as &$month)
                    {
                        $month['link'] = $this->getLink('blog',array('month'=>$month['month_add'],'year'=>$year['year_add']));
                        $month['month_add'] = $this->getMonthName($month['month_add']); 
                    }
                }
            }
        }
        $this->context->smarty->assign(
            array(
                'years'=>$years,
            )
        );
        return $this->display(__FILE__,'block_archives.tpl');
    }
    public function getMonthName($month)
    {
        switch ($month) {
            case 1:
                return $this->l('January');
            case 2:
                return $this->l('February');
            case 3:
                return $this->l('March');
            case 4:
                return $this->l('April');
            case 5:
                return $this->l('May');
            case 6:
                return $this->l('June');
            case 7:
                return $this->l('July');
            case 8:
                return $this->l('August');
            case 9:
                return $this->l('September');
            case 10:
                return $this->l('October');
            case 11:
                return $this->l('November');
            case 12:
                return $this->l('December');
        }
    }
    public function _postCustomerSettingAuthor()
    {
        if(Tools::isSubmit('saveCustomerAuthor'))
        {
            $ybc_defines = new Ybc_blog_defines();
            $this->_saveConfiguration($ybc_defines->customer_settings);
            Hook::exec('actionUpdateBlog', array()); 
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=author&conf=4');
        }
    }
    public function _saveConfiguration($configs,$dirImg='',$width_image='',$height_image='')
    {
        $errors = array();
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && trim(Tools::getValue($key.'_'.$id_lang_default) == ''))
                    {
                        $errors[] = $config['label'].' '.$this->l('is required');
                    }                        
                }
                else
                {
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && trim(Tools::getValue($key) == ''))
                    {
                        $errors[] = $config['label'].' '.$this->l('is required');
                    }
                    if(isset($config['validate']) && method_exists('Validate',$config['validate']))
                    {
                        $validate = $config['validate'];
                        if(!Validate::$validate(trim(Tools::getValue($key))))
                            $errors[] = $config['label'].' '.$this->l('is invalid');
                        unset($validate);
                    }
                    elseif(!is_array(Tools::getValue($key)) &&  !Validate::isCleanHtml(trim(Tools::getValue($key))))
                    {
                        $errors[] = $config['label'].' '.$this->l('is invalid');
                    }   
                }                    
            }
        }
        if(!$errors)
        {
            if($configs)
            {
                foreach($configs as $key => $config)
                {
                    if(isset($config['lang']) && $config['lang'])
                    {
                        $valules = array();
                        foreach($languages as $lang)
                        {
                            if($config['type']=='switch')                                                           
                                $valules[$lang['id_lang']] = (int)trim(Tools::getValue($key.'_'.$lang['id_lang'])) ? 1 : 0;                                
                            else
                                $valules[$lang['id_lang']] = trim(Tools::getValue($key.'_'.$lang['id_lang'])) ? trim(Tools::getValue($key.'_'.$lang['id_lang'])) : trim(Tools::getValue($key.'_'.$id_lang_default));
                        }
                        Configuration::updateValue($key,$valules);
                    }
                    else
                    {
                        if($config['type']=='switch')
                        {                           
                            Configuration::updateValue($key,(int)trim(Tools::getValue($key)) ? 1 : 0);
                        }
                        elseif($config['type']=='checkbox' || $config['type']=='blog_categories')
                            Configuration::updateValue($key,implode(',',Tools::getValue($key)));
                        elseif($config['type']=='file')
                        {      
                            if(isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['name']) && $_FILES[$key]['name'])
                            {
                                if(file_exists($dirImg.$_FILES[$key]['name']))
                                {
                                    $_FILES[$key]['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES[$key]['name'];
                                }
                                $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$key]['name'], '.'), 1));
                    			$imagesize = @getimagesize($_FILES[$key]['tmp_name']);
                    			if (isset($_FILES[$key]) &&				
                    				!empty($_FILES[$key]['tmp_name']) &&
                    				!empty($imagesize) &&
                    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
                    			)
                    			{
                    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
                    				if ($error = ImageManager::validateUpload($_FILES[$key]))
                    					$errors[] = $error;
                    				elseif (!$temp_name || !move_uploaded_file($_FILES[$key]['tmp_name'], $temp_name))
                    					$errors[] = $this->l('Can not upload the file');
                    				elseif(!ImageManager::resize($temp_name, $dirImg.$_FILES[$key]['name'], $width_image, $height_image, $type))
                    					$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
                    				if (isset($temp_name))
                    					@unlink($temp_name);
                                    if(Configuration::get($key))
                                    {
                                        @unlink($dirImg.Configuration::get($key));
                                    }
                                    Configuration::updateValue($key,$_FILES[$key]['name']);
			
                    			}
                                
                            }
                        }
                        else
                            Configuration::updateValue($key,trim(Tools::getValue($key)));   
                    }                        
                }
            }
        }
        if (count($errors))
        {
           $this->errorMessage = $this->displayError($errors);  
        }
        //
        if(Tools::isSubmit('ajax'))
        {
            die(Tools::jsonEncode(
                array(
                    'messageType' => $errors ? 'error' : 'success',
                    'message' => $errors ? $this->errorMessage : $this->displayConfirmation($this->l('Configuration saved')),
                    'ybc_link_desc'=>$this->getLink(),
                )
            ));
        }
    }
    public function _postRSS()
    {
        $ybc_defines = new Ybc_blog_defines();
        if(Tools::isSubmit('saveRSS'))
        {
            $this->_saveConfiguration($ybc_defines->rss);
        }
    }
    public function getBaseLink()
    {
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')?'https://':'http://').$this->context->shop->domain.$this->context->shop->getBaseURI();
    }
    public function displayTabAuthor()
    {
        $filter = "";
        $having="";
        if(Tools::getValue('control')=='employee')
        {
            if(trim(Tools::getValue('id_employee'))!='')
                $filter .= " AND e.id_employee = ".(int)trim(urldecode(Tools::getValue('id_employee')));
            if(trim(Tools::getValue('name'))!='')
                $filter .= " AND (CONCAT(e.firstname,' ',e.lastname) like '".pSQL(Tools::getValue('name'))."%' OR be.name like'".pSQL(Tools::getValue('name'))."%')";                
            if(trim(Tools::getValue('email')))
                $filter .= " AND e.email like '".pSQL(Tools::getValue('email'))."'";
            if(trim(Tools::getValue('description')))
                $filter .= " AND bel.description like '%".pSQL(Tools::getValue('description'))."%'";
            if(trim(Tools::getValue('id_profile')))
                $filter .= " AND pl.id_profile = '".(int)Tools::getValue('id_profile')."'";
            if(trim(Tools::getValue('profile_employee')))
                $filter .= " AND (be.profile_employee like '".Tools::getValue('profile_employee')."' OR p.id_profile=1)  ";
            if(trim(Tools::getValue('total_post_min'))!='')
                $having .= ' AND total_post >="'.(int)Tools::getValue('total_post_min').'"';
            if(trim(Tools::getValue('total_post_max'))!='')
                $having .= ' AND total_post <="'.(int)Tools::getValue('total_post_max').'"'; 
            if(Tools::isSubmit('status') && trim(Tools::getValue('status'))!='')
                    $filter .= " AND (be.status= '".(int)Tools::getValue('status')."'".((int)Tools::getValue('status')==1 ? ' or be.status is null':'' )." )";
        }        
        $totalEmployee = (int)$this->countEmployeesFilter($filter,$having);
        $filter = "";
        $having="";
        if(Tools::getValue('control')=='customer')
        {
            if(trim(Tools::getValue('id_customer'))!='')
                $filter .= " AND c.id_customer = ".(int)trim(urldecode(Tools::getValue('id_customer')));
            if(trim(Tools::getValue('name'))!='')
                $filter .= " AND (CONCAT(c.firstname,' ',c.lastname) like '".pSQL(Tools::getValue('name'))."%' OR be.name like'".pSQL(Tools::getValue('name'))."%')";                
            if(trim(Tools::getValue('email'))!='')
                $filter .= " AND c.email like '".pSQL(Tools::getValue('email'))."%'";
            if(trim(Tools::getValue('description'))!='')
                $filter .= ' AND bel.description like "%'.pSQL(Tools::getValue('description')).'%"';
            if(trim(Tools::getValue('total_post_min'))!='')
                $having .= ' AND total_post >="'.(int)Tools::getValue('total_post_min').'"';
            if(trim(Tools::getValue('total_post_max'))!='')
                $having .= ' AND total_post <="'.(int)Tools::getValue('total_post_max').'"'; 
            if(Tools::isSubmit('status') && trim(Tools::getValue('status'))!='')
                    $filter .= " AND (be.status= '".(int)Tools::getValue('status')."'".((int)Tools::getValue('status')==1 ? ' or be.status is null':'' )." )";
        }  
        if(Tools::isSubmit('has_post') && Tools::getValue('has_post')==0)
            $having .= ' AND total_post <=0';
        else
            $having .= ' AND total_post >=1';       
        $totalCustomer = (int)$this->countCustomersFilter($filter,$having);
        $this->context->smarty->assign(
            array(
                'totalCustomer' => $totalCustomer,
                'totalEmployee' => $totalEmployee,
                'control' => Tools::getValue('control'),
                'YBC_BLOG_ALLOW_CUSTOMER_AUTHOR' => Configuration::get('YBC_BLOG_ALLOW_CUSTOMER_AUTHOR'),
            )
        );
        return $this->display(__FILE__,'tab_author.tpl');
    }
    public function getGroups($list_id=false)
    {
        $sql ='SELECT g.id_group as value, gl.name as label FROM '._DB_PREFIX_.'group g
            LEFT JOIN '._DB_PREFIX_.'group_lang gl ON (g.id_group=gl.id_group AND gl.id_lang="'.(int)$this->context->language->id.'")
        WHERE g.id_group !="'.(int)Configuration::get('PS_UNIDENTIFIED_GROUP').'" AND g.id_group !="'.(int)Configuration::get('PS_GUEST_GROUP').'"
        ';
        $groups=Db::getInstance()->executeS($sql);
        if($list_id)
        {
            $ids='';
            foreach($groups as $key=> $group)
            {
                if($key+1 < count($groups))
                    $ids .=$group['value'].',';
                    
            }
            return $ids;
        }    
        return $groups;
    }
    public function checkGroupAuthor()
    {
        if(!Configuration::get('YBC_BLOG_ALLOW_CUSTOMER_AUTHOR'))
            return false;
        if(isset($this->context->customer))
        {
            if($this->context->customer->id && $authorGroups=explode(',',Configuration::get('YBC_BLOG_GROUP_CUSTOMER_AUTHOR')))
            {
                $groups = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'group g 
                INNER JOIN '._DB_PREFIX_.'customer_group cg ON (g.id_group= cg.id_group)
                WHERE cg.id_customer ="'.(int)$this->context->customer->id.'" AND g.id_group !="'.(int)Configuration::get('PS_UNIDENTIFIED_GROUP').'" AND g.id_group !="'.(int)Configuration::get('PS_GUEST_GROUP').'"
                ');
                if($groups)
                {
                    foreach($groups as $group)
                    {
                        if(in_array($group['id_group'],$authorGroups))
                        {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    public function hookCustomerAccount($params)
    {
        $this->context->smarty->assign(
            array(
                'author'=> $this->checkGroupAuthor(),
                'suppened' =>(int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$this->context->customer->id.' AND is_customer=1 AND status<=0'),
            )
        );
        if($this->is17)
    	   return $this->display(__FILE__, 'my-account.tpl');
        else
            return $this->display(__FILE__, 'my-account16.tpl');
    }
    public function hookDisplayMyAccountBlock($params)
    {
    	return $this->hookCustomerAccount($params);
    }
    public function hookDisplayLeftFormManagament()
    {
        $left_tabs= array(
            array(
                'title'=> $this->l('My posts'),
                'link'=> $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','list'=>true)),
                'name'=>'post',
            ),
            array(
                'title'=> $this->l('Comments'),
                'link'=> $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'comment','list'=>true)),
                'name'=>'comment',
            ),
        );
        $this->context->smarty->assign(
            array(
                'left_tabs'=>$left_tabs,  
                'tabmanagament' => Tools::getValue('tabmanagament','post'),
            )
        );
        return $this->display(__FILE__,'blog_management_left.tpl');
    }
    public function hookDisplayLeftFormComments()
    {
        $left_tabs= array(
            array(
                'title'=> $this->l('My comments'),
                'link'=> $this->context->link->getModuleLink('ybc_blog','managementcomments',array('tabmanagament'=>'comment_other','list'=>true)),
                'name'=>'comment_other',
            ),
        );
        $this->context->smarty->assign(
            array(
                'left_tabs'=>$left_tabs,  
                'tabmanagament' => Tools::getValue('tabmanagament','comment_other'),
            )
        );
        return $this->display(__FILE__,'blog_management_left.tpl');
    }
    public function renderCommentOtherListByCustomer()
    {
        if(!(Tools::isSubmit('editcomment') && Tools::getValue('id_comment')))
        {
            $fields_list = array(
                'id_comment' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'id_comment','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'id_comment','sort_type'=>'desc')),
                    'filter' => true,
                ),
                'subject' => array(
                    'title' => $this->l('Subject'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'subject','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'subject','sort_type'=>'desc')),
                    'filter' => true,                        
                ),                    
                'rating' => array(
                    'title' => $this->l('Rating'),
                    //'width' => 100,
                    'type' => 'select',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'rating','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'rating','sort_type'=>'desc')),
                    'filter' => true,
                    'rating_field' => true,
                    'filter_list' => array(
                        'id_option' => 'rating',
                        'value' => 'stars',
                        'list' => array(
                            0 => array(
                                'rating' => 0,
                                'stars' => $this->l('No reviews')
                            ),
                            1 => array(
                                'rating' => 1,
                                'stars' => '1 '.$this->l('star')
                            ),
                            2 => array(
                                'rating' => 2,
                                'stars' => '2 '.$this->l('stars')
                            ),
                            3 => array(
                                'rating' => 3,
                                'stars' => '3 '.$this->l('stars')
                            ),
                            4 => array(
                                'rating' => 4,
                                'stars' => '4 '.$this->l('stars')
                            ),
                            5 => array(
                                'rating' => 5,
                                'stars' => '5 '.$this->l('stars')
                            ),
                        )
                    )
                ),
                'title' => array(
                    'title' => $this->l('Blog post'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'title','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'title','sort_type'=>'desc')),
                    'filter' => true,
                    'strip_tag' => false,
                ),
                'approved' => array(
                    'title' => $this->l('Approved'),
                    //'width' => 50,
                    'type' => 'active',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'approved','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','sort'=>'approved','sort_type'=>'desc')),
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                )
            );
            //Filter comment
            $filter = " AND bc.id_user ='".(int)$this->context->customer->id."'";
            if(Tools::isSubmit('ybc_submit_ybc_comment') && Tools::getValue('tabmanagament')=='comment_other')
            {
                if(trim(Tools::getValue('id_comment'))!='')
                    $filter .= " AND bc.id_comment = ".(int)trim(urldecode(Tools::getValue('id_comment')));
                if(trim(Tools::getValue('comment'))!='')
                    $filter .= " AND bc.comment like '%".addslashes(trim(urldecode(Tools::getValue('comment'))))."%'";
                if(trim(Tools::getValue('subject'))!='')
                    $filter .= " AND (bc.subject like '%".addslashes(trim(urldecode(Tools::getValue('subject'))))."%' OR bc.comment like '%".addslashes(trim(urldecode(Tools::getValue('subject'))))."%' )";
                if(trim(Tools::getValue('rating'))!='')
                    $filter .= " AND bc.rating = ".(int)trim(urldecode(Tools::getValue('rating')));                
                if(trim(Tools::getValue('name'))!='')
                    $filter .= " AND bc.name like '%".addslashes(trim(urldecode(Tools::getValue('name'))))."%'";
                if(trim(Tools::getValue('approved'))!='')
                    $filter .= " AND bc.approved = ".(int)trim(urldecode(Tools::getValue('approved')));
                if(trim(Tools::getValue('reported'))!='')
                    $filter .= " AND bc.reported = ".(int)trim(urldecode(Tools::getValue('reported')));
                if(trim(Tools::getValue('title'))!='')
                    $filter .= " AND pl.title like '%".pSQL(trim(urldecode(Tools::getValue('title'))))."%'";
            }
            //Sort
            $sort = "";
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = 'bc.id_comment desc,';
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page')> 0 && Tools::getValue('tabmanagament')=='comment_other' ? (int)Tools::getValue('page') : 1;
            $totalRecords = (int)$this->countCommentsWithFilter($filter);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getModuleLink('ybc_blog','managementcomments',array('tabmanagament'=>'comment_other','page'=>'_page_',)).$this->getUrlExtraFrontEnd($fields_list,'ybc_submit_ybc_comment');
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $comments = $this->getCommentsWithFilter($filter, $sort, $start, $paggination->limit);
            if($comments)
            {
                foreach($comments as &$comment)
                {
                    $comment['view_url'] = $this->getLink('blog', array('id_post' => $comment['id_post'])).'#blog_comment_line_'.$comment['id_comment'];
                    $comment['view_text'] = $this->l('View in post');
                    $comment['title'] ='<a href="'.$comment['view_url'].'" title="'.$comment['title'].'">'.$comment['title'].'</a>';
                    if($this->checkPermisionComment('edit',$comment['id_comment']))
                        $comment['edit_url'] = $this->getLink('blog',array('id_post'=>$comment['id_post'],'edit_comment'=>$comment['id_comment']));
                    if($this->checkPermisionComment('delete',$comment['id_comment']))
                        $comment['delete_url'] = $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>'comment_other','id_comment'=>$comment['id_comment'],'deletecomment'=>1));
                    
                 }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_comment',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getModuleLink('ybc_blog','managementcomments',array('tabmanagament'=>'comment_other')),
                'identifier' => 'id_comment',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('My comments'),
                'fields_list' => $fields_list,
                'field_values' => $comments,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParamsFontEnd($fields_list,'ybc_submit_ybc_comment'),
                'show_reset' => trim(Tools::getValue('id_comment'))!='' || trim(Tools::getValue('comment'))!='' || trim(Tools::getValue('rating'))!='' || trim(Tools::getValue('subject'))!='' || trim(Tools::getValue('customer'))!='' || trim(Tools::getValue('approved'))!='' || trim(Tools::getValue('reported'))!='' || trim(Tools::getValue('title'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'show_add_new' => false,
                'sort'=>Tools::getValue('sort','id_comment'),
                'sort_type'=>Tools::getValue('sort_type','desc'),
            );            
            return $this->_html .= $this->renderListByCustomer($listData);
        }
        return $this->renderFormCommentByCustomer();
        
    }
    public function sendMailRepyCustomer($id_comment,$replier,$comment_reply=''){
        if(Configuration::get('YBC_BLOG_ENABLE_MAIL_REPLY_CUSTOMER'))
        {
            $comment = new Ybc_blog_comment_class($id_comment);
            $post = new Ybc_blog_post_class($comment->id_post,$this->context->language->id);
            $template_reply_comment=array(
                '{customer_name}' => $comment->name,
                '{customer_email}' => $comment->email,
                '{comment}' =>$comment->comment,
                '{comment_reply}' => $comment_reply ? $comment_reply : Tools::getValue('reply_comwent_text'),
                '{post_link}' => $this->getLink('blog',array('id_post'=>$comment->id_post)),
                '{post_title}'=>$post->title,
                '{replier}' => $replier,
                '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
            );
            Mail::Send(
    			Context::getContext()->language->id,
    			'admin_reply_comment_to_customer',
    			$this->l('New reply to your comment'),
    			$template_reply_comment,
		        $comment->email,
    			$comment->name,
    			null,
    			null,
    			null,
    			null,
    			dirname(__FILE__).'/mails/'
            );
        }
    }
    public function sendMailReplyAdmin($id_comment,$replier,$approved=1,$comment_reply=''){
        if(Configuration::get('YBC_BLOG_ENABLE_MAIL_REPLY'))
        {
            $comment = new Ybc_blog_comment_class($id_comment);
            $post_class = new Ybc_blog_post_class($comment->id_post,$this->context->language->id);
            $template_reply_comment=array(
                '{customer_name}' => $comment->name, 
                '{customer_email}' => $comment->email,
                '{comment}' =>$comment->comment,
                '{comment_reply}' => $comment_reply ? $comment_reply : Tools::getValue('reply_comwent_text'),
                '{post_title}' => $post_class->title,
                '{replier}'=>$replier,
                '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER'),
                '{post_link}' => $this->getLink('blog',array('id_post'=>$post_class->id)),
            );
            if($post_class->is_customer && $id_customer= $post_class->added_by)
            {
                $author= new Customer($id_customer);
                $link_view_comment= $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'comment','list'=>1));
            }
            else
            {
                $author = new Employee($post_class->added_by);
                $link_view_comment= $this->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER');
            }
            if($author->id)
            {
                $template_reply_comment['{author_name}'] = $author->firstname.' '.$author->lastname;
                $template_reply_comment['{link_view_comment}'] = $link_view_comment;
                Mail::Send(
        			Context::getContext()->language->id,
        			'customer_reply_comment_to_admin_'.$approved,
        			$this->l('A customer has replied a comment on ').$post_class->title,
        			$template_reply_comment,
    		        $author->email,
        			$author->firstname.' '.$author->lastname,
        			null,
        			null,
        			null,
        			null,
        			dirname(__FILE__).'/mails/'
                );
            }
            if($emails= explode(',',Configuration::get('YBC_BLOG_ALERT_EMAILS')))
            {
                $link_view_comment= $this->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER');
                foreach($emails as $email)
                {
                    $template_reply_comment['{author_name}'] = Configuration::get('PS_SHOP_NAME');
                    $template_reply_comment['{link_view_comment}'] = $link_view_comment;
                    if(Validate::isEmail($email))
                        Mail::Send(
            			Context::getContext()->language->id,
            			'customer_reply_comment_to_admin_'.$approved,
            			$this->l('A customer has replied a comment on ').$post_class->title,
            			$template_reply_comment,
        		        $email,
            			Configuration::get('PS_SHOP_NAME'),
            			null,
            			null,
            			null,
            			null,
            			dirname(__FILE__).'/mails/'
                    );
                }
            }
        }
    }
    public function renderCommentListByCustomer()
    {
        if(Tools::isSubmit('viewcomment') && $id_comment=Tools::getValue('id_comment'))
        {
            $errors =array();
            $comment= new Ybc_blog_comment_class($id_comment);
            if(Tools::getValue('change_approved_comment'))
            {
                if($this->checkPermisionComment('edit',$id_comment))
                {
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_comment set approved="'.(int)Tools::getValue('approved').'" WHERE id_comment='.(int)$id_comment);
                    Tools::redirectLink($this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'updatedComment'=> 1)));
                }
                else    
                    $errors[]=  $this->l('Sorry, you do not have permission');    
            }
            $replies= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_comment='.(int)$id_comment);
            if(Tools::getValue('addReplyComment'))
            {
                if($this->checkPermisionComment('reply',$id_comment))
                {
                    if(Tools::strlen(Tools::getValue('reply_comwent_text')) < 20)
                        $errors[] = $this->l('Reply need to be at least 20 characters');
                    if(!Validate::isCleanHtml(Tools::getValue('reply_comwent_text'),false))
                        $errors[] = $this->l('Reply need to be clean HTML');
                    if(Tools::strlen(Tools::getValue('reply_comwent_text')) >2000)
                        $errors[] = $this->l('Reply can not be longer than 2000 characters'); 
                    if(!$errors)
                    {
                        $sql= "INSERT INTO "._DB_PREFIX_."ybc_blog_reply(id_comment,id_user,name,email,reply,id_employee,approved,datetime_added,datetime_updated) values('".(int)$id_comment."', '".(int)$this->context->customer->id."','".pSQL($this->context->customer->firstname)." ".pSQL($this->context->customer->lastname)."','".pSQL($this->context->customer->email)."','".pSQL(Tools::getValue('reply_comwent_text'))."','0',1,'".pSQL(date('Y-m-d H:i:s'))."','".pSQL(date('Y-m-d H:i:s'))."')";
                        Db::getInstance()->execute($sql);
                        $this->sendMailRepyCustomer($id_comment,$this->context->customer->firstname.' '.$this->context->customer->lastname);
                        Tools::redirectLink($this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'addedReply'=> 1)));
                    }
                    else
                    {
                        $this->context->smarty->assign(
                            array(
                                'replyCommentsave' => $id_comment,
                                'reply_comwent_text' => Tools::getValue('reply_comwent_text'),
                            )
                        );
                    }
                }
                else
                    $errors[]=$this->l('Sorry, you do not have permission');
                
            }
            if(Tools::isSubmit('delete_reply') && $id_reply=Tools::getValue('delete_reply'))
            {
                if($this->checkPermisionComment('delete',$id_comment))
                {
                    Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_reply='.(int)$id_reply);
                    Tools::redirectLink($this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'deleteddReply'=> 1)));
                }
                else
                    $errors[]=$this->l('Sorry, you do not have permission');
            }
            if(Tools::isSubmit('change_approved_reply') && $id_reply=Tools::getValue('change_approved_reply'))
            {
                if($this->checkPermisionComment('edit',$id_comment))
                {
                    $reply_old = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_reply='.(int)$id_reply);
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_reply SET approved='.(int)Tools::getValue('approved').' WHERE id_reply='.(int)$id_reply);
                    if($reply_old['approved']!=Tools::getValue('approved') && Tools::getValue('approved')==1)
                    {
                        $this->sendMailRepyCustomer($id_comment,$reply_old['name'],$reply_old['reply']);
                    }
                    Tools::redirectLink($this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'updatedReply'=> 1)));
                }
            }
            if($replies)
            {
                foreach($replies as &$reply)
                {
                    if($this->checkPermisionComment('edit',$comment->id))
                        $reply['link_approved'] = $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'change_approved_reply'=> $reply['id_reply'],'approved' => $reply['approved'] ? 0 :1));
                    if($this->checkPermisionComment('delete',$comment->id))
                        $reply['link_delete'] = $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'delete_reply'=> $reply['id_reply']));
                    $reply['reply'] = str_replace("\n",'<'.'b'.'r/'.'>',$reply['reply']);
                    if($reply['id_employee'])
                    {
                        if($name= Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$reply['id_employee'].'" AND is_customer=0'))
                            $reply['name']= $name;
                        elseif($name = Db::getInstance()->getValue('SELECT CONCAT(firstname," ",lastname) FROM '._DB_PREFIX_.'employee WHERE id_employee='.(int)$reply['id_employee']))
                            $reply['name']= $name;
                    }
                    if($reply['id_user'])
                    {
                        if($name= Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$reply['id_user'].'" AND is_customer=1'))
                            $reply['name']= $name;
                        elseif($name = Db::getInstance()->getValue('SELECT CONCAT(firstname," ",lastname) FROM '._DB_PREFIX_.'customer WHERE id_customer='.(int)$reply['id_user']))
                            $reply['name']= $name;
                    }
                }
            }
            $comment->comment = str_replace("\n",'<'.'b'.'r/'.'>',$comment->comment);
            $this->context->smarty->assign(
                array(
                    'comment'=>$comment,
                    'replies'=>$replies,
                    'post_link' => $this->getLink('blog',array('id_post'=>$comment->id_post)),
                    'link_delete' => $this->checkPermisionComment('delete',$comment->id) ? $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','deletecomment'=>1,'id_comment'=>$comment->id)):'',  //http://localhost/ps1742/en/module/ybc_blog/managementblog?tabmanagament=comment&id_comment=46&deletecomment=1
                    'link_approved' => $this->checkPermisionComment('edit',$comment->id) ? $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment->id,'viewcomment'=>1,'change_approved_comment'=>1,'approved' => $comment->approved ? 0 :1)):'',
                    'post_class' => new Ybc_blog_post_class($comment->id_post,$this->context->language->id),
                    'link_back'=> $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment')),
                )
            );
            return $this->_html .=($errors ? $this->displayError($errors): '' ).$this->display(__FILE__,'author_reply_comment.tpl');
        }
        if(!(Tools::isSubmit('editcomment') && Tools::getValue('id_comment')))
        {
            $fields_list = array(
                'id_comment' => array(
                    'title' => $this->l('Id'),
                    'width' => 40,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'id_comment','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'id_comment','sort_type'=>'desc')),
                    'filter' => true,
                ),
                'subject' => array(
                    'title' => $this->l('Subject'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'subject','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'subject','sort_type'=>'desc')),
                    'filter' => true,                        
                ),
                'title' => array(
                    'title' => $this->l('Blog post'),
                    //'width' => 140,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'title','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'title','sort_type'=>'desc')),
                    'filter' => true, 
                    'strip_tag'=>false,                       
                ),                      
                'rating' => array(
                    'title' => $this->l('Rating'),
                    //'width' => 100,
                    'type' => 'select',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'rating','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'rating','sort_type'=>'desc')),
                    'filter' => true,
                    'rating_field' => true,
                    'filter_list' => array(
                        'id_option' => 'rating',
                        'value' => 'stars',
                        'list' => array(
                            0 => array(
                                'rating' => 0,
                                'stars' => $this->l('No reviews')
                            ),
                            1 => array(
                                'rating' => 1,
                                'stars' => '1 '.$this->l('star')
                            ),
                            2 => array(
                                'rating' => 2,
                                'stars' => '2 '.$this->l('stars')
                            ),
                            3 => array(
                                'rating' => 3,
                                'stars' => '3 '.$this->l('stars')
                            ),
                            4 => array(
                                'rating' => 4,
                                'stars' => '4 '.$this->l('stars')
                            ),
                            5 => array(
                                'rating' => 5,
                                'stars' => '5 '.$this->l('stars')
                            ),
                        )
                    )
                ),
                'name' => array(
                    'title' => $this->l('Customer'),
                    //'width' => 100,
                    'type' => 'text',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'name','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'name','sort_type'=>'desc')),
                    'filter' => true
                ),
                'approved' => array(
                    'title' => $this->l('Approved'),
                    //'width' => 50,
                    'type' => 'active',
                    'sort' => $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'approved','sort_type'=>'asc')),
                    'sort_desc'=>$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','sort'=>'approved','sort_type'=>'desc')),
                    'filter' => true,
                    'strip_tag' => false,
                    'filter_list' => array(
                        'id_option' => 'enabled',
                        'value' => 'title',
                        'list' => array(
                            0 => array(
                                'enabled' => 1,
                                'title' => $this->l('Yes')
                            ),
                            1 => array(
                                'enabled' => 0,
                                'title' => $this->l('No')
                            )
                        )
                    )
                )
                
            );
            //Filter
            $filter = " AND p.added_by ='".(int)$this->context->customer->id."' AND p.is_customer=1";
            if(trim(Tools::getValue('id_comment'))!='')
                $filter .= " AND bc.id_comment = ".(int)trim(urldecode(Tools::getValue('id_comment')));
            if(trim(Tools::getValue('comment'))!='')
                $filter .= " AND bc.comment like '%".addslashes(trim(urldecode(Tools::getValue('comment'))))."%'";
            if(trim(Tools::getValue('subject'))!='')
                $filter .= " AND (bc.subject like '%".addslashes(trim(urldecode(Tools::getValue('subject'))))."%' OR bc.comment like '%".addslashes(trim(urldecode(Tools::getValue('subject'))))."%' )";
            if(trim(Tools::getValue('rating'))!='')
                $filter .= " AND bc.rating = ".(int)trim(urldecode(Tools::getValue('rating')));                
            if(trim(Tools::getValue('name'))!='')
                $filter .= " AND bc.name like '%".addslashes(trim(urldecode(Tools::getValue('name'))))."%'";
            if(trim(Tools::getValue('approved'))!='')
                $filter .= " AND bc.approved = ".(int)trim(urldecode(Tools::getValue('approved')));
            if(trim(Tools::getValue('reported'))!='')
                $filter .= " AND bc.reported = ".(int)trim(urldecode(Tools::getValue('reported')));
             if(trim(Tools::getValue('title'))!='')
                $filter .= " AND pl.title like '%".addslashes(trim(urldecode(Tools::getValue('title'))))."%'";
            //Sort
            $sort = "";
            if(trim(Tools::getValue('sort')) && isset($fields_list[Tools::getValue('sort')]))
            {
                $sort .= trim(Tools::getValue('sort'))." ".(Tools::getValue('sort_type')=='asc' ? ' ASC ' :' DESC ')." , ";
            }
            else
                $sort = 'bc.id_comment desc,';
            
            //Paggination
            $page = (int)Tools::getValue('page') && (int)Tools::getValue('page')> 0 && Tools::getValue('tabmanagament')=='comment' ? (int)Tools::getValue('page') : 1;
            
            $totalRecords = (int)$this->countCommentsWithFilter($filter);
            $paggination = new Ybc_blog_paggination_class();            
            $paggination->total = $totalRecords;
            $paggination->url = $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'comment','page'=>'_page_',)).$this->getUrlExtraFrontEnd($fields_list,'ybc_submit_ybc_comment');
            $paggination->limit =  20;
            $totalPages = ceil($totalRecords / $paggination->limit);
            if($page > $totalPages)
                $page = $totalPages;
            $paggination->page = $page;
            $start = $paggination->limit * ($page - 1);
            if($start < 0)
                $start = 0;
            $comments = $this->getCommentsWithFilter($filter, $sort, $start, $paggination->limit);
            if($comments)
            {
                foreach($comments as &$comment)
                {
                    $comment['child_view_url']=$this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment['id_comment'],'viewcomment'=>1));
                    $comment['view_url'] = $this->getLink('blog', array('id_post' => $comment['id_post'])).'#blog_comment_line_'.$comment['id_comment'];
                    $comment['title'] ='<a href="'.$comment['view_url'].'" title="'.$comment['title'].'">'.$comment['title'].'</a>';
                    $comment['view_text'] = $this->l('View in post');
                    if(($privileges= explode(',',Configuration::get('YBC_BLOG_AUTHOR_PRIVILEGES'))) && in_array('manage_comments',$privileges))
                    {
                        $comment['edit_url'] = $this->getLink('blog',array('id_post'=>$comment['id_post'],'edit_comment'=>$comment['id_comment']));
                        $comment['delete_url'] = $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment['id_comment'],'deletecomment'=>1));
                        $comment['edit_approved'] = $this->context->link->getModuleLink($this->name,'managementblog',array('tabmanagament'=>'comment','id_comment'=>$comment['id_comment'],'commentapproved'=>!$comment['approved']));
                    }
                 }
            }
            $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
            $paggination->style_links = $this->l('links');
            $paggination->style_results = $this->l('results');
            $listData = array(
                'name' => 'ybc_comment',
                'actions' => array('edit', 'delete', 'view'),
                'currentIndex' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'comment')),
                'identifier' => 'id_comment',
                'show_toolbar' => true,
                'show_action' => true,
                'title' => $this->l('Customer comments'),
                'fields_list' => $fields_list,
                'field_values' => $comments,
                'paggination' => $paggination->render(),
                'filter_params' => $this->getFilterParamsFontEnd($fields_list,'ybc_submit_ybc_comment'),
                'show_reset' => trim(Tools::getValue('id_comment'))!='' || trim(Tools::getValue('comment'))!='' || trim(Tools::getValue('rating'))!='' || trim(Tools::getValue('subject'))!='' || trim(Tools::getValue('customer'))!='' || trim(Tools::getValue('approved'))!='' || trim(Tools::getValue('reported'))!='' || trim(Tools::getValue('title'))!='' ? true : false,
                'totalRecords' => $totalRecords,
                'show_add_new' => false,
                'sort'=>Tools::getValue('sort','id_comment'),
                'sort_type'=>Tools::getValue('sort_type','desc'),
            );            
            return $this->_html .= $this->renderListByCustomer($listData);
        }
        return $this->renderFormCommentByCustomer();
    }
    public function hookDisplayRightFormManagament()
    {
        $tabmanagament=Tools::getValue('tabmanagament');
        switch ($tabmanagament) {
            case 'post':
                $content_html_right = $this->renderPostListByCustomer();
                break;
            case 'comment':
                $content_html_right =$this->renderCommentListByCustomer();
                break;
            default:
                $content_html_right=$this->renderPostListByCustomer();
        } 
        $this->context->smarty->assign(
            array(
                'content_html_right'=>$content_html_right,
            )
        );  
        return $this->display(__FILE__,'blog_management_right.tpl');
    }
    public function hookDisplayRightFormComments()
    {
        $this->context->smarty->assign(
            array(
                'content_html_right'=>$this->renderCommentOtherListByCustomer(),
            )
        );  
        return $this->display(__FILE__,'blog_management_right.tpl');
    }
    public function renderFormAuthorInformation(){
        $information = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_employee e, '._DB_PREFIX_.'ybc_blog_employee_lang el WHERE id_employee="'.(int)$this->context->customer->id.'" AND id_lang="'.(int)$this->context->language->id.'" AND is_customer=1');
        $this->context->smarty->assign(
            array(
                'name_author'=> isset($information['name']) && $information['name'] ? $information['name'] : $this->context->customer->firstname.' '.$this->context->customer->lastname,
                'author_description' => isset($information['description']) && $information['description']?$information['description'] :'',
                'author_avata' => isset($information['avata']) && $information['avata']?$this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.$information['avata'] :'',
                'avata_default' => $this->getBaseLink().'modules/'.$this->name.'/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') : 'default_customer.png'),
                'link_delete_image' => $this->context->link->getModuleLink('ybc_blog','managementmyinfo',array('delemployeeimage'=>1))
            )
        );
        return $this->display(__FILE__,'form_author.tpl');
    }
    public function displayFormBlog()
    {
        if($id_post=Tools::getValue('id_post'))
        {
            if(!$this->checkPermistionPost($id_post,'edit_blog'))
            {
                return $this->displayError($this->l('Sorry, you do not have permission'));
            }
            $ybc_post= new Ybc_blog_post_class($id_post,$this->context->language->id);
            $this->context->smarty->assign(
                array(
                    'link_delete_thumb' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','editpost'=>1,'deletethumb'=>1,'id_post'=>$id_post)),
                    'link_delete_image' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','editpost'=>1,'deleteimage'=>1,'id_post'=>$id_post)),
                    'link_post' => $this->getLink('blog',array('id_post'=>$id_post)),
                )
            ); 
        }  
        else
        {
            if(!$this->checkPermistionPost(0,'add_new'))
                return $this->displayError($this->l('Sorry, you do not have permission'));
            $ybc_post = new Ybc_blog_post_class();
        }   
        $this->context->smarty->assign(
            array(
                'ybc_post'=>$ybc_post,
                'link'=> $this->context->link,
                'link_back_list' => $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post')),
                'dir_img' => $this->getBaseLink().'modules/ybc_blog/views/img/',
                'html_content' =>$this->displayBlogCategoryTre($this->getBlogCategoriesTreeFontEnd(0),$this->getSelectedCategories((int)Tools::getValue('id_post')),'',$this->getCategoriesDisabled()),
                //'languages'=>Language::getLanguages(false),
            )
        );
        return $this->display(__FILE__,'form_blog.tpl');
    }
    public function getCategoriesDisabled()
    {
        if($categories = explode(',',Configuration::get('YBC_BLOG_CATEGOGY_CUSTOMER')))
        {
            $in = implode(',',array_map('intval',$categories));        
        }    
        $slq="SELECT id_category FROM "._DB_PREFIX_."ybc_blog_category WHERE 1".(isset($in) && $in? ' AND id_category NOT IN ('.$in.')':'')."" ;  
        $categories = Db::getInstance()->executeS($slq);
        if($categories)
        {
            $array=array();
            foreach($categories as $category)
                $array[]=$category['id_category'];
            return $array;
        }
        return array();
    }            
    public function checkPermistionPost($id_post=0,$permistion)
    {
        $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$this->context->customer->id.' AND is_customer=1 AND status<=0');
        if($id_employee_post)
            return false;
       
        if(($privileges = explode(',',Configuration::get('YBC_BLOG_AUTHOR_PRIVILEGES'))) && in_array($permistion,$privileges))
        {
             
            if($permistion=='edit_blog' || $permistion=='delete_blog')
                return Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post WHERE id_post="'.(int)$id_post.'" AND added_by="'.(int)$this->context->customer->id.'" AND is_customer=1');
            else
                return true;
            
        }
        return false;
    }
    public function renderFormCommentByCustomer()
    {
        if(!$this->checkPermisionComment())
            return $this->displayError($this->l('Sorry, you do not have permission'));
        else
        {
            $ybc_comment=new Ybc_blog_comment_class(Tools::getValue('id_comment'));
            $this->context->smarty->assign(
                array(
                    'ybc_comment'=> $ybc_comment,
                    'link_back_list' => $this->context->link->getModuleLink($this->name,'managementcomments',array('tabmanagament'=>Tools::getValue('tabmanagament','comment_other'))),
                    'edit_approved' => $ybc_comment->id_user!=$this->context->customer->id,
                )
            );
            return $this->display(__FILE__,'form_comment_customer.tpl');
        }  
    }
    public function checkPermisionComment($action='edit',$id_comment=0)
    {
        if ($action=='delete' && Configuration::get('YBC_BLOG_ALLOW_DELETE_COMMENT'))
                $ok=true;
        if($id_comment==0)
            $id_comment =(int)Tools::getValue('id_comment');
        if(!isset($this->context->customer) || !$this->context->customer->logged)
            return false;
        
        $privileges = explode(',',Configuration::get('YBC_BLOG_AUTHOR_PRIVILEGES'));
        $ok=true;
        if(!$privileges || !$this->checkGroupAuthor())
            $ok=false ; 
        elseif($this->checkGroupAuthor())
        {
            if(Tools::getValue('tabmanagament')=='comment' || $action!='')
            {
                if($action=='reply' && !in_array('reply_comments',$privileges))
                    $ok=false;
                elseif(!in_array('manage_comments',$privileges) && $action!='reply')
                    $ok=false;
                else
                    $ok= Db::getInstance()->getValue('SELECT p.id_post FROM '._DB_PREFIX_.'ybc_blog_post p, '._DB_PREFIX_.'ybc_blog_comment c WHERE p.id_post=c.id_post AND c.id_comment="'.(int)$id_comment.'" AND p.added_by="'.(int)$this->context->customer->id.'" AND p.is_customer=1');
            }
        }
        if(!$ok && Db::getInstance()->getValue('SELECT id_comment FROM '._DB_PREFIX_.'ybc_blog_comment WHERE id_user="'.(int)$this->context->customer->id.'" AND id_comment='.(int)$id_comment))
        {
            if ($action=='edit' && Configuration::get('YBC_BLOG_ALLOW_EDIT_COMMENT'))
                $ok=true;
            if ($action=='delete') // && Configuration::get('YBC_BLOG_ALLOW_DELETE_COMMENT')
                $ok=true;
            if($action=='reply' && Configuration::get('YBC_BLOG_ALLOW_REPLY_COMMENT'))
                $ok=true;
        } 
        elseif($action=='reply' && Configuration::get('YBC_BLOG_ALLOW_REPLY_COMMENT'))
                $ok=true; 
        return $ok;
    }
    public function getThumbCategory($id_category,&$thumb,&$lever)
    {
        $category = new Ybc_blog_category_class($id_category,$this->context->language->id);
        if($lever>=1)
            $thumb = ' > '.'<a href="'.$this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=category&list=true&id_parent='.(int)$category->id_category.'">'.$category->title.'</a>'.$thumb;
        else
            $thumb = ' > '.$category->title.$thumb;
        $lever++;
        if($category->id_parent)
            $this->getThumbCategory($category->id_parent,$thumb,$lever);
        return $thumb;
    }
    public function getProfiles()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'profile_lang WHERE id_lang='.(int)$this->context->language->id;
        return Db::getInstance()->executeS($sql);
    }
    public function hookBlogCategoryBlock($params)
    {
        if(Configuration::get('YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME'))
        {
            if(Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME') && $limit=(int)Configuration::get('YBC_BLOG_CATEGORY_POST_NUMBER_HOME'))
            {
                $sql='SELECT * FROM '._DB_PREFIX_.'ybc_blog_category c
                INNER JOIN '._DB_PREFIX_.'ybc_blog_category_shop cs ON (c.id_category=cs.id_category)
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_category_lang cl ON (c.id_category=cl.id_category AND cl.id_lang="'.(int)$this->context->language->id.'")
                WHERE c.enabled=1 AND c.id_category IN ('.implode(',',array_map('intval',explode(',',Configuration::get('YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME')))).')
                AND cs.id_shop ="'.(int)$this->context->shop->id.'"';
                $categoires = Db::getInstance()->executeS($sql);
                if($categoires)
                {
                    foreach($categoires as &$category)
                    {
                        if(!Configuration::get('YBC_BLOG_POST_SORT_BY'))
                            $sort = 'p.id_post DESC, ';
                        else
                        {
                            if(Configuration::get('YBC_BLOG_POST_SORT_BY')=='sort_order')
                                $sort = 'pc.position ASC, ';
                            else
                                $sort = 'p.'.Configuration::get('YBC_BLOG_POST_SORT_BY').' DESC, ';
                        }
                        $posts= $this->getPostsWithFilter(" AND p.enabled=1 AND pc.id_category= '".(int)$category['id_category']."'",$sort,0,$limit);
                        if($posts)
                        {
                            foreach($posts as $key => &$post)
                            {
                                $post['link'] = $this->getLink('blog',array('id_post' => $post['id_post']));
                                if($post['thumb'])
                                    $post['thumb'] = $this->_path.'views/img/post/thumb/'.$post['thumb'];
                                $post['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                                $post['liked'] = $this->isLikedPost($post['id_post']);
                                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);
                                
                            }
                            unset($key); 
                        }
                        $category['posts'] = $posts;
                        $category['link_all'] = $this->getLink('blog',array('id_category'=>$category['id_category']));
                    }
                }
                if($categoires)
                {                                  
                    $this->smarty->assign(
                        array(
                            'posts' => $posts,
                            'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                            'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                            'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                            'sidebar_post_type' => Configuration::get('YBC_BLOG_SIDEBAR_POST_TYPE'),
                            'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                            'hook' => 'homeblog',
                            'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                            'page' => isset($params['page']) && $params['page'] ? $params['page'] : false,
                            'categoires' => $categoires,
                        )
                    );
                    return $this->display(__FILE__,'categories_home_block.tpl');
                }
            }
        }
        return '';
    }
    public function getDevice()
    {
      return ($userAgent = new Ybc_browser())? $userAgent->getBrowser().' '.$userAgent->getVersion().' '.$userAgent->getPlatform() : $this->l('Unknown');
    }
    public function isLikedPost($id_post)
    {
        if($this->context->customer->logged)
        {
            if(Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_log_like WHERE id_customer="'.(int)$this->context->customer->id.'" AND id_post="'.(int)$id_post.'"'))
            {
                return true;
            }
        }
        if(!$this->context->cookie->liked_posts)
            $likedPosts = array();
        else
            $likedPosts = @unserialize($this->context->cookie->liked_posts);
        
        if(is_array($likedPosts) && in_array($id_post, $likedPosts))
            $likedPost = true;
        else
            $likedPost = false;
        return $likedPost;
    }
    public function checkCreatedColumn($table,$column)
    {
        $fieldsCustomers = Db::getInstance()->ExecuteS('DESCRIBE '._DB_PREFIX_.pSQL($table));
        $check_add=false;
        foreach($fieldsCustomers as $field)
        {
            if($field['Field']==$column)
            {
                $check_add=true;
                break;
            }    
        }
        return $check_add;
    }
    public function getSelectedRelatedCategories($id_post)
    {
        $categories = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post_related_categories WHERE id_post='.(int)$id_post);
        $relateds= array();
        if($categories)
        {
            foreach($categories as $cat)
            {
                $relateds[]=$cat['id_category'];
            }
        }
        return $relateds;
    }
    public function displayPostRelatedCategories($id_category)
    {
        if(!Configuration::get('YBC_BLOG_DISPLAY_CATEGORY_PAGE') || !Configuration::get('YBC_BLOG_NUMBER_POST_IN_CATEGORY'))
            return '';         
        $posts= $this->getPostsWithFilter(' AND p.enabled=1 AND rpc.id_category='.(int)$id_category,$this->sort,0,Configuration::get('YBC_BLOG_NUMBER_POST_IN_CATEGORY',8),false);
        if($posts)
            foreach($posts as &$post)
            {
                $post['link'] = $this->getLink('blog',array('id_post' => $post['id_post']));
                if($post['thumb'])
                    $post['thumb'] = $this->_path.'views/img/post/thumb/'.$post['thumb'];
                $post['comments_num'] = $this->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                $post['liked'] = $this->isLikedPost($post['id_post']);
                $post['categories'] = $this->getCategoriesByIdPost($post['id_post'],false,true);
            }
        $this->smarty->assign(
            array(
                'posts' => $posts,
                'display_desc' => Configuration::get('YBC_BLOG_CATEGORY_PAGE_DISPLAY_DESC'),
                'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'sidebar_post_type' => Configuration::get('YBC_BLOG_CATEGORY_POST_TYPE'),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'page' => 'home',
            )
        );
        return $this->display(__FILE__, 'related_posts_category.tpl');
    }
    public function displayReplyComment()
    {
        $id_comment = Tools::getValue('id_comment');
        if($id_comment)
        {
            $comment= new Ybc_blog_comment_class($id_comment);
            $comment->viewed=1;
            $comment->update();
            $comment->comment = str_replace("\n",'<'.'b'.'r/'.'>',$comment->comment);
            $replies= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_comment='.(int)$id_comment);
            if($replies)
            {
                foreach($replies as &$reply)
                {
                    $reply['reply'] = str_replace("\n",'<'.'b'.'r/'.'>',$reply['reply']);
                    if($reply['id_employee'])
                    {
                        if($name= Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$reply['id_employee'].'" AND is_customer=0'))
                            $reply['name']= $name;
                        elseif($name = Db::getInstance()->getValue('SELECT CONCAT(firstname," ",lastname) FROM '._DB_PREFIX_.'employee WHERE id_employee='.(int)$reply['id_employee']))
                            $reply['name']= $name;
                    }
                    if($reply['id_user'])
                    {
                        if($name= Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$reply['id_user'].'" AND is_customer=1'))
                            $reply['name']= $name;
                        elseif($name = Db::getInstance()->getValue('SELECT CONCAT(firstname," ",lastname) FROM '._DB_PREFIX_.'customer WHERE id_customer='.(int)$reply['id_user']))
                            $reply['name']= $name;
                    }
                }    
            }
            $this->context->smarty->assign(
                array(
                    'comment'=>$comment,
                    'replies'=>$replies,
                    'post_class' => new Ybc_blog_post_class($comment->id_post,$this->context->language->id),
                    'curenturl' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)$id_comment,
                    'link_back'=> $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&list=true',
                    'post_link' => $this->getLink('blog',array('id_post'=>$comment->id_post)),
                    'link_delete' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=comment&id_comment='.(int)$id_comment.'&del=1',
                )
            );
        }
        $this->_html .= $this->display(__FILE__,'reply_comment.tpl');
    }
    public function _posstReply()
    {
        $errors=array();
        if(Tools::isSubmit('submitBulkActionReply') && Tools::getValue('reply_readed') && $bulk_action_reply =Tools::getValue('bulk_action_reply'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_comment' => (int)Tools::getValue('id_comment'),
            ));
            if($bulk_action_reply=='delete_selected')
            {
                foreach(Tools::getValue('reply_readed') as $id_reply => $value)
                {
                    if($value)
                    {
                        Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_reply='.(int)$id_reply);
                    }
                }
                die(Tools::jsonEncode(
                    array(
                        'url_reload' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)Tools::getValue('id_comment').'&conf=2',
                    )
                ));
            }
            else
            {
                if($bulk_action_reply=='mark_as_approved')
                {
                    $value_field=1;
                    $field='approved';
                }
                else
                {
                    $value_field=0;
                    $field='approved';
                }
                foreach(Tools::getValue('reply_readed') as $id_reply => $value)
                {
                    if($value)
                    {
                        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_reply SET `'.pSQL($field).'`='.(int)$value_field.' WHERE id_reply='.(int)$id_reply);
                    }
                }
                die(Tools::jsonEncode(
                    array(
                        'url_reload' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)Tools::getValue('id_comment').'&conf=4',
                    )
                ));
            }
        }
        if(Tools::isSubmit('change_approved') && $id_reply=Tools::getValue('id_reply'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_comment' => (int)Tools::getValue('id_comment'),
            ));
            $reply_old = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_reply='.(int)$id_reply);
            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_reply SET approved="'.(int)Tools::getValue('change_approved').'",datetime_updated="'.pSQL(date('Y-m-d H:i:s')).'" WHERE id_reply='.(int)$id_reply);
            if(Tools::getValue('change_approved'))
                $title = $this->l('Click to mark as unapproved');
            else
                $title = $this->l('Click to mark as approved');
            if($reply_old['approved']!=Tools::getValue('change_approved') && Tools::getValue('change_approved')==1)
            {
                $this->sendMailRepyCustomer($reply_old['id_comment'],$reply_old['name'],$reply_old['reply']);
            }
            if(Tools::isSubmit('ajax'))
            {
                die(Tools::jsonEncode(array(
                    'listId' => $id_reply,
                    'enabled' => Tools::getValue('change_approved'),
                    'field' => 'approved',
                    'message' => $this->displaySuccessMessage($this->l('The status has been successfully updated')) ,
                    'messageType'=>'success',
                    'title'=>$title,
                    'href' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)Tools::getValue('id_comment').'&change_approved='.(Tools::getValue('change_approved') ? '0' : '1').'&id_reply='.$id_reply,
                )));
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control=post&list=true');
        } 
        if(Tools::isSubmit('change_comment_approved') && $id_comment=Tools::getValue('id_comment'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_comment' => (int)Tools::getValue('id_comment'),
            ));
            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_comment SET approved="'.(int)Tools::getValue('change_comment_approved').'" WHERE id_comment='.(int)$id_comment);
            if(Tools::isSubmit('ajax'))
            {
                die(Tools::jsonEncode(array(
                    'listId' => $id_reply,
                    'enabled' => Tools::getValue('change_comment_approved'),
                    'field' => 'approved',
                    'href' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)Tools::getValue('id_comment').'&change_comment_approved='.(Tools::getValue('change_comment_approved') ? '0' : '1'),
                )));
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)Tools::getValue('id_comment'));
        }
        if(Tools::isSubmit('delreply') && $id_reply=Tools::getValue('id_reply'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_comment' => (int)Tools::getValue('id_comment'),
            ));
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_reply='.(int)$id_reply);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&control='.Tools::getValue('control').'&id_comment='.(int)Tools::getValue('id_comment').'&conf=2');
        } 
        if(Tools::isSubmit('addReplyComment') && $id_comment=Tools::getValue('id_comment'))
        {
            Hook::exec('actionUpdateBlog', array(
                'id_comment' => (int)Tools::getValue('id_comment'),
            ));
            if(Tools::strlen(Tools::getValue('reply_comwent_text')) < 20)
                $errors[] = $this->l('Reply need to be at least 20 characters');
            if(!Validate::isCleanHtml(Tools::getValue('reply_comwent_text'),false))
                $errors[] = $this->l('Reply need to be clean HTML');
            if(Tools::strlen(Tools::getValue('reply_comwent_text')) >2000)
                $errors[] = $this->l('Reply can not be longer than 2000 characters'); 
            if(!$errors)
            {
                $sql= "INSERT INTO "._DB_PREFIX_."ybc_blog_reply(id_comment,id_user,name,email,reply,id_employee,approved,datetime_added,datetime_updated) values('".(int)$id_comment."', '0','".pSQL($this->context->employee->firstname)." ".pSQL($this->context->employee->lastname)."','".pSQL($this->context->employee->email)."','".pSQL(Tools::getValue('reply_comwent_text'))."','".(int)$this->context->employee->id."',1,'".pSQL(date('Y-m-d H:i:s'))."','".pSQL(date('Y-m-d H:i:s'))."')";
                Db::getInstance()->execute($sql);
                $this->sendMailRepyCustomer($id_comment,$this->context->employee->firstname.' '.$this->context->employee->lastname);
                $this->sendMailReplyAdmin($id_comment,$this->context->employee->firstname.' '.$this->context->employee->lastname,1,Tools::getValue('reply_comwent_text'));
                $this->_html .= $this->displaySuccessMessage($this->l('Reply has been submitted'));
            }
            else
            {
                $this->context->smarty->assign(
                    array(
                        'replyCommentsave' => $id_comment,
                        'reply_comwent_text' => Tools::getValue('reply_comwent_text'),
                    )
                );
                $this->_html .= $this->displayError($errors);
            }
        }
    }
    public function displayError($error)
    {
        $output = '
        <div class="bootstrap">
        <div class="module_error alert alert-danger" >
            <button type="button" class="close" data-dismiss="alert">&times;</button>';

        if (is_array($error)) {
            $output .= '<ul>';
            foreach ($error as $msg) {
                $output .= '<li>'.$msg.'</li>';
            }
            $output .= '</ul>';
        } else {
            $output .= $error;
        }

        // Close div openned previously
        $output .= '</div></div>';

        $this->error = true;
        if($error)
        {
            $this->context->smarty->assign(
                array(
                    'errors_blog'=>$error
                )
            );
            return $this->display(__FILE__,'errors.tpl');
        }
        return '';
    }
    public function hookDisplayBackOfficeFooter()
    {
        if(version_compare(_PS_VERSION_, '1.6', '<'))
            return '';
        $this->context->smarty->assign(
            array(
                'link_ajax' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name,
            )
        );
        return $this->display(__FILE__,'admin_footer.tpl');
    }
    
    public function hookDisplayFooterYourAccount(){
        $this->context->smarty->assign(
            array(
                'is_17' => $this->is17,
                'my_account_link' => $this->context->link->getPageLink('my-account',Configuration::get('PS_SSL_ENABLED'),$this->context->language->id),
                'home_link' => $this->context->link->getPageLink('index',Configuration::get('PS_SSL_ENABLED'),$this->context->language->id),
            )
        );
        return $this->display(__FILE__,'your_account_footer.tpl');
    }
    public function redirect($url)
    {
        header("HTTP/1.1 301 Moved Permanently"); 
        call_user_func('header',"Location: $url");
        exit; 
    }
    public static function checkIframeHTML($content)
    {
        if(!Configuration::get('PS_ALLOW_HTML_IFRAME') && (Tools::strpos($content,'<iframe')!==false || Tools::strpos($content,'<source')!==false) )
            return false;
        else
            return true;
    }
    public function displayErrorIframe()
    {
        $this->context->smarty->assign(
            array(
                'link' => $this->context->link,
            )
        );
        return $this->display(__FILE__,'iframe.tpl');
    }
    public static function checkIsLinkRewrite($link)
    {
        if (Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL')) {
            return preg_match(Tools::cleanNonUnicodeSupport('/^[_a-zA-Z\x{0600}-\x{06FF}\pL\pS-]{1}[_a-zA-Z0-9\x{0600}-\x{06FF}\pL\pS-]+$/u'), $link);
        }
        return preg_match('/^[_a-zA-Z\-]{1}[_a-zA-Z0-9\-]+$/', $link);
    }
}