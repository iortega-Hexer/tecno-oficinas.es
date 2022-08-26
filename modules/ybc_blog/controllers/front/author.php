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
class Ybc_blogAuthorModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public function __construct()
	{
        parent::__construct();
        if(Configuration::get('YBC_BLOG_SIDEBAR_POSITION')=='right')
            $this->display_column_right=true;
        if(Configuration::get('YBC_BLOG_SIDEBAR_POSITION')=='left')
            $this->display_column_left =true;
        $this->context = Context::getContext();

    }
    public function init()
	{
		parent::init();
        if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'module/ybc_blog/') !==false)
        {
            Tools::redirect($this->module->getLink('author'));
        }
	}
    public function getAlternativeLangsUrl()
    {
        $alternativeLangs = array();
        $languages = Language::getLanguages(true, $this->context->shop->id);

        if ($languages < 2) {
            // No need to display alternative lang if there is only one enabled
            return $alternativeLangs;
        }

        foreach ($languages as $lang) {
            $alternativeLangs[$lang['language_code']] = $this->module->getLanguageLink($lang['id_lang']);
        }
        return $alternativeLangs;
    }
    public function initContent()
	{
        parent::initContent();
        $module = new Ybc_blog();
        $sql ='SELECT COUNT(p.id_post) as total_post, p.added_by,p.is_customer FROM `'._DB_PREFIX_.'ybc_blog_post` p
                INNER JOIN `'._DB_PREFIX_.'ybc_blog_post_shop` ps ON (p.id_post =ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'")
                LEFT JOIN `'._DB_PREFIX_.'employee` e ON (e.id_employee=p.added_by)
                LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.id_customer =p.added_by)
                WHERE p.enabled=1 AND ((e.id_employee!=0 AND p.is_customer=0) OR (c.id_customer!=0 AND p.is_customer=1))
                GROUP BY p.added_by,p.is_customer ORDER BY total_post DESC';
        $authors= Db::getInstance()->executeS($sql);
        $page = (int)Tools::getValue('page');
        if($page<1)
            $page =1;
        $totalRecords = (int)count($authors);
        $paggination = new Ybc_blog_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $module->getLink('author', array('page'=>"_page_"));
        $paggination->limit =  8;//(int)Configuration::get('YBC_BLOG_CATEGORY_PER_PAGE') > 0 ? (int)Configuration::get('YBC_BLOG_CATEGORY_PER_PAGE') : 8;
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        $sql ='SELECT COUNT(p.id_post) as total_post, p.added_by,p.is_customer FROM `'._DB_PREFIX_.'ybc_blog_post` p
                INNER JOIN `'._DB_PREFIX_.'ybc_blog_post_shop` ps ON (p.id_post =ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'")
                LEFT JOIN `'._DB_PREFIX_.'employee` e ON (e.id_employee=p.added_by)
                LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.id_customer =p.added_by)
                WHERE p.enabled=1 AND ((e.id_employee!=0 AND p.is_customer=0) OR (c.id_customer!=0 AND p.is_customer=1))
                GROUP BY p.added_by,p.is_customer ORDER BY total_post DESC limit '.(int)$start.','.(int)$paggination->limit.'';
        
        $authors = Db::getInstance()->executeS($sql);
        if($authors)
        {
            foreach($authors as &$author)
            {
                if($author['is_customer'])
                {
                    $information = Db::getInstance()->getRow('
                    SELECT * FROM `'._DB_PREFIX_.'customer` c
                    LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee` be ON (be.id_employee=c.id_customer AND be.is_customer=1)
                    LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee_lang` bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
                    WHERE c.id_customer="'.(int)$author['added_by'].'"');
                    if(!$information['name'])
                        $information['name']=$information['firstname'].' '.$information['lastname'];
                    $author['information']=$information;
                    $author['link']=$this->module->getLink('blog',array('id_author'=>$author['added_by'],'is_customer'=>1,'alias'=> Tools::link_rewrite($information['name'])));
                    if($information['avata'])
                        $author['avata'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'avata/'.$information['avata']);
                    else
                       $author['avata']= $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png')); 
                }
                else
                {
                    $information = Db::getInstance()->getRow('
                    SELECT * FROM `'._DB_PREFIX_.'employee` e
                    LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee` be ON (be.id_employee=e.id_employee AND be.is_customer=0)
                    LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee_lang` bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
                    WHERE e.id_employee="'.(int)$author['added_by'].'"');
                    if(!$information['name'])
                        $information['name']=$information['firstname'].' '.$information['lastname'];
                    $author['information']=$information;
                    $author['link']=$this->module->getLink('blog',array('id_author'=>$author['added_by'],'is_customer'=>0,'alias'=> Tools::link_rewrite($information['name'])));
                    if($information['avata'])
                        $author['avata'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'avata/'.$information['avata']);
                    else
                       $author['avata']= $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png')) ; 
                }
                $sql ='SELECT * FROM `'._DB_PREFIX_.'ybc_blog_post` p
                INNER JOIN `'._DB_PREFIX_.'ybc_blog_post_shop` ps ON (p.id_post=ps.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'")
                LEFT JOIN `'._DB_PREFIX_.'ybc_blog_post_lang` pl ON (p.id_post=pl.id_post AND pl.id_lang="'.(int)$this->context->language->id.'")
                WHERE p.enabled=1 AND  p.added_by ="'.(int)$author['added_by'].'" AND p.is_customer="'.(int)$author['is_customer'].'"';
                $author['posts'] = Db::getInstance()->executeS($sql);
                if($author['posts'])
                {
                    foreach($author['posts'] as &$post)
                    {
                        $post['link'] = $this->module->getLink('blog',array('id_post'=>$post['id_post']));
                    }
                }
            }
        }
       $this->context->smarty->assign(
            array(
                'is_main_page' =>false,
                'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                'show_featured_post' => (int)Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') ? true : false,
                'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                'path' => $this->module->getBreadCrumb(),
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false, 
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),   
                'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')), 
                'authors' => $authors,
                'blog_paggination' => $paggination->render(),
                'breadcrumb' => $this->module->is17 ? $this->module->getBreadCrumb() : false, 
            )
       );
       if(Tools::isSubmit('loadajax'))
       {
            $this->module->loadMoreAuhors($authors,$paggination->render());
       }
       if($this->module->is17)
            $this->setTemplate('module:ybc_blog/views/templates/front/list_author.tpl');      
       else         
            $this->setTemplate('list_author_16.tpl');     
       
    }
}
