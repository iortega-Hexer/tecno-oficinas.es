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
class Ybc_blogCommentModuleFrontController extends ModuleFrontController
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
        $totalRecords= $module->countCommentsWithFilter(' AND bc.approved=1');
        $paggination = new Ybc_blog_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $module->getLink('comment', array('page'=>"_page_"));
        $paggination->limit = Configuration::get('YBC_BLOG_COMMENT_PER_PAGE') ? Configuration::get('YBC_BLOG_COMMENT_PER_PAGE'): 8;
        $totalPages = ceil($totalRecords / $paggination->limit);
        $page=Tools::getValue('page',1);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0; 
        $posts = $module->getCommentsWithFilter(' AND bc.approved=1','bc.datetime_added DESC,',$start,$paggination->limit);;
        if($posts)
        {
            foreach($posts as &$post)
            {
                $post['link'] = $this->module->getLink('blog',array('id_post' => $post['id_post']));
                if($post['thumb'])
                    $post['thumb'] = $this->module->blogDir.'views/img/post/thumb/'.$post['thumb'];
                $post['comments_num'] = $this->module->countCommentsWithFilter(' AND bc.id_post='.$post['id_post'].' AND approved=1');
                $post['liked'] = $this->module->isLikedPost($post['id_post']);
                if($post['id_user'] && !$post['name'])
                    $post['name']=  Db::getInstance()->getValue('SELECT CONCAT(firstname, " ", lastname) FROM '._DB_PREFIX_.'customer WHERE id_customer="'.(int)$post['id_user'].'"');
                if($post['id_user'])
                {
                    $customerinfo = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee="'.(int)$post['id_user'].'" AND is_customer=1');
                    if($customerinfo && $customerinfo['avata'])
                    {
                        $post['avata'] = $this->module->getBaseLink().'/modules/'.$this->module->name.'/views/img/avata/'.$customerinfo['avata'];
                    }
                    else
                       $post['avata'] = $this->module->getBaseLink().'/modules/'.$this->module->name.'/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png'); 
                }
                else
                {
                    $post['avata'] = $this->module->getBaseLink().'/modules/'.$this->module->name.'/views/img/avata/'.(Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png');
                }
                $post['categories'] = $this->module->getCategoriesByIdPost($post['id_post'],false,true);
            }
        }
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
                'path' => $module->getBreadCrumb(),            
                'breadcrumb' => $module->is17 ? $module->getBreadCrumb() : false,
                'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                'image_folder' => $module->blogDir.'views/img/avata/',
            )
        );
        if(Tools::isSubmit('loadajax') && Tools::getValue('loadajax'))
        {
            $this->module->loadMoreComments($posts,$paggination);
        }
        if($module->is17)
            $this->setTemplate('module:ybc_blog/views/templates/front/list-comment.tpl');      
        else  
            $this->setTemplate('list-comment-16.tpl');                
	}
}