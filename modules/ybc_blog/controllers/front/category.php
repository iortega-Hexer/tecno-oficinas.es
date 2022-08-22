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
class Ybc_blogCategoryModuleFrontController extends ModuleFrontController
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
        $this->module= new Ybc_blog();
	}
	public function init()
	{
		parent::init();
	}
	public function initContent()
	{
		parent::initContent();
        $module = new Ybc_blog();
        $categoryData = $this->getCategories();
        if(isset($categoryData['categories']) && $categoryData['categories'])
        {
            foreach($categoryData['categories'] as &$category)
            {
                $category['link']=$this->module->getLink('blog',array('id_category'=>$category['id_category'])); 
                if($category['image'])
                     $category['image'] = $module->blogDir.'views/img/category/'.$category['image'];  
                if($category['thumb'])
                    $category['thumb'] = $module->blogDir.'views/img/category/thumb/'.$category['thumb'];
                $category['count_posts'] =(int) Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_post p, '._DB_PREFIX_.'ybc_blog_post_category pc WHERE pc.id_post=p.id_post AND pc.id_category="'.(int)$category['id_category'].'" AND p.enabled=1');
                $category['sub_categogires'] = $this->module->getCategoriesWithFilter(' AND c.enabled=1',false,false,false,$category['id_category']);
                if($category['sub_categogires'])
                {
                    foreach($category['sub_categogires'] as &$sub)
                    {
                        $sub['link'] = $this->module->getLink('blog',array('id_category'=>$sub['id_category']));
                    }
                }    
            }
        }
        $this->context->smarty->assign(
            array(
                'blog_categories' => $categoryData['categories'],
                'blog_paggination' => $categoryData['paggination'],
                'path' => $module->getBreadCrumb(),
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),                 
                'breadcrumb' => $module->is17 ? $module->getBreadCrumb() : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                'image_folder' => $module->blogDir.'views/img/category/',
            )
        );
        if(Tools::isSubmit('loadajax') && Tools::getValue('loadajax'))
        {
            $this->module->loadMoreCategories($categoryData);
        }
        if($module->is17)
            $this->setTemplate('module:ybc_blog/views/templates/front/list-category.tpl');      
        else  
            $this->setTemplate('list-category-16.tpl');                
	}    
    public function getCategories()
    {
        $filter = ' AND c.enabled = 1 AND id_parent=0';            
        $sort = ' c.sort_order asc, c.id_category asc, ';
        $module = new Ybc_blog();
        
        $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
        $totalRecords = (int)$module->countCategoriesWithFilter($filter);
        $paggination = new Ybc_blog_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $module->getLink('category', array('page'=>"_page_"));
        $paggination->limit =  8;//(int)Configuration::get('YBC_BLOG_CATEGORY_PER_PAGE') > 0 ? (int)Configuration::get('YBC_BLOG_CATEGORY_PER_PAGE') : 8;
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        $categories = $module->getCategoriesWithFilter($filter, $sort, $start, $paggination->limit);       
        return array(
            'categories' => $categories , 
            'paggination' => $paggination->render()
        );
    }
}