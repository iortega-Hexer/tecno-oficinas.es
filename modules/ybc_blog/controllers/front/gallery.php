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
class Ybc_blogGalleryModuleFrontController extends ModuleFrontController
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
        if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'module/ybc_blog/') !==false)
        {
            Tools::redirect($this->module->getLink('gallery'));
        }
	}
	public function initContent()
	{
        parent::initContent();
	    $module = new Ybc_blog();
        $galleryData = $this->getGalleries();
        $prettySkin = Configuration::get('YBC_BLOG_GALLERY_SKIN');
        $this->context->smarty->assign(
            array(
                'blog_galleries' => $galleryData['galleries'],
                'blog_paggination' => $galleryData['paggination'],
                'prettySkin' => in_array($prettySkin, array('dark_square','dark_rounded','default','facebook','light_rounded','light_square')) ? $prettySkin : 'dark_square', 
                'prettyAutoPlay' => (int)Configuration::get('YBC_BLOG_GALLERY_AUTO_PLAY') ? 1 : 0,
                'per_row'=> Configuration::get('YBC_BLOG_GALLERY_PER_ROW') ? Configuration::get('YBC_BLOG_GALLERY_PER_ROW'):12,
                'path' => $module->getBreadCrumb(),
                'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),                 
                'breadcrumb' => $module->is17 ? $module->getBreadCrumb() : false,
                'image_folder' => $module->blogDir.'views/img/',
                
            )
        );
        if($module->is17)
            $this->setTemplate('module:ybc_blog/views/templates/front/gallery.tpl');      
        else  
            $this->setTemplate('gallery_16.tpl');                
	}    
    public function getGalleries()
    {
        $filter = ' AND g.enabled = 1';            
        $sort = ' g.sort_order asc, g.id_gallery asc, ';
        $module = new Ybc_blog();
        //Paggination
        $page = (int)Tools::getValue('page') && (int)Tools::getValue('page') > 0 ? (int)Tools::getValue('page') : 1;
        $totalRecords = (int)$module->countGalleriesWithFilter($filter);
        $paggination = new Ybc_blog_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $module->getLink('gallery', array('page'=>"_page_"));
        $paggination->limit =  (int)Configuration::get('YBC_BLOG_GALLERY_PER_PAGE') > 0 ? (int)Configuration::get('YBC_BLOG_GALLERY_PER_PAGE') : 24;
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        $galleries = $module->getGalleriesWithFilter($filter, $sort, $start, $paggination->limit);
        if($galleries)
        {
            foreach($galleries as &$gallery)
            {
                if($gallery['thumb'])
                    $gallery['thumb'] =  $module->blogDir.'views/img/gallery/thumb/'.$gallery['thumb'];   
                else
                     $gallery['thumb']=$module->blogDir.'views/img/gallery/'.$gallery['image']; 
                if($gallery['image'])
                {                       
                    $gallery['image'] =  $module->blogDir.'views/img/gallery/'.$gallery['image'];    
                }                     
            }                
        }        
        return array(
            'galleries' => $galleries , 
            'paggination' => $paggination->render()
        );
    }
}