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
class Ybc_blogSitemapModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public function __construct()
	{
	   parent::__construct();
	   $this->display_column_left=false;
       $this->display_column_right=false;
       $this->context = Context::getContext();
       $this->module= new Ybc_blog();
    }
    public function init()
	{
		parent::init();
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
       $pages_sitemap= explode(',',Configuration::get('YBC_BLOC_SITEMAP_PAGES'));
       $xml ='<?xml version="1.0" encoding="UTF-8"?>';
       $xml .='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
       if(in_array('main_blog',$pages_sitemap))
       {
            $xml .='<url>'."\n";
           $xml .='<loc><![CDATA['.$this->module->getLink('blog').']]></loc>'."\n";
           $xml .='<priority>1.0</priority>'."\n"; 
           $xml .='<changefreq>daily</changefreq>';
           $xml .='</url>'."\n";
       }
       if(in_array('single_post',$pages_sitemap))
       {
            
            $posts = $this->module->getPostsWithFilter(' AND p.enabled=1',$this->module->sort);
            if($posts)
            {
                foreach($posts as $post)
                {
                    $xml .='<url>'."\n";
                    $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('id_post'=>$post['id_post'])).']]></loc>'."\n";
                    $xml .='<priority>0.9</priority>'."\n"; 
                    $xml .='<lastmod>'.date('Y-m-d').'</lastmod>'."\n";
                    $xml .='<changefreq>daily</changefreq>'."\n";
                    if($post['thumb'] || $post['image'])
                    {
                        $xml .='<image:image>'."\n";
                        $xml .='<image:loc><![CDATA['.($post['thumb'] ? $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/thumb/'.$post['thumb']) : $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/'.$post['image'])).']]></image:loc>'."\n";
                        $xml .='<image:caption><![CDATA['.$post['title'].']]></image:caption>'."\n";
                        $xml .='<image:title><![CDATA['.$post['title'].']]></image:title>'."\n";
                        $xml .='</image:image>'."\n";   
                    }
                    $xml .='</url>'."\n";
                }
           }
       }
       if(in_array('category',$pages_sitemap))
       {
            $req = "SELECT c.*, cl.title, cl.description,cl.image
                FROM `"._DB_PREFIX_."ybc_blog_category` c
                INNER JOIN `"._DB_PREFIX_."ybc_blog_category_shop` cs ON (c.id_category=cs.id_category AND cs.id_shop='".(int)$this->context->shop->id."')
                LEFT JOIN `"._DB_PREFIX_."ybc_blog_category_lang` cl ON c.id_category = cl.id_category
                WHERE c.enabled=1 AND cl.id_lang = ".(int)$this->context->language->id." 
                ORDER BY  datetime_added ASC ";      
           $categories =Db::getInstance()->executeS($req);
           foreach($categories as $category)
           {
                $xml .='<url>'."\n";
                $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('id_category'=>$category['id_category'])).']]></loc>'."\n";
                $xml .='<priority>0.8</priority>'."\n"; 
                $xml .='<lastmod>'.date('Y-m-d').'</lastmod>'."\n";
                $xml .='<changefreq>daily</changefreq>'."\n";
                if($category['image'] || $category['image'])
                {
                    $xml .='<image:image>'."\n";
                    $xml .='<image:loc><![CDATA['.$this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'category/'.$category['image']).']]></image:loc>'."\n";
                    $xml .='<image:caption><![CDATA['.$category['title'].']]></image:caption>'."\n";
                    $xml .='<image:title><![CDATA['.$category['title'].']]></image:title>'."\n";
                    $xml .='</image:image>'."\n";   
                }
                $xml .='</url>'."\n";
           }
       }
       if(in_array('authors',$pages_sitemap))
       {
            $req  = 'SELECT * FROM `'._DB_PREFIX_.'employee` e
            LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee` be ON (e.id_employee=be.id_employee AND be.is_customer=0)
           ';
           $employees = Db::getInstance()->executeS($req);
           foreach($employees as $employee)
           {
                $alias = $employee['name'] ? Tools::link_rewrite($employee['name']): Tools::link_rewrite($employee['firstname'].' '.$employee['lastname']);
                $xml .='<url>'."\n";
                $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('id_author'=>$employee['id_employee'],'alias'=>$alias,'is_customer'=>0)).']]></loc>'."\n";
                $xml .='<priority>0.7</priority>'."\n"; 
                $xml .='<lastmod>'.date('Y-m-d').'</lastmod>'."\n";
                $xml .='<changefreq>daily</changefreq>'."\n";
                if($employee['avata'] || $employee['avata'])
                {
                    $xml .='<image:image>'."\n";
                    $xml .='<image:loc><![CDATA['.$this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'avata/'.$employee['avata']).']]></image:loc>'."\n";
                    $xml .='<image:caption><![CDATA['.($employee['name'] ? $employee['name']: $employee['firstname'].' '.$employee['lastname']).']]></image:caption>'."\n";
                    $xml .='<image:title><![CDATA['.($employee['name'] ? $employee['name']: $employee['firstname'].' '.$employee['lastname']).']]></image:title>'."\n";
                    $xml .='</image:image>'."\n";   
                }
                $xml .='</url>'."\n";
           }
           if(Configuration::get('YBC_BLOG_ALLOW_CUSTOMER_AUTHOR'))
           {
                $customers = $this->module->getCustomersFilter();
                foreach($customers as $customer)
                {
                    $alias = $customer['name'] ? Tools::link_rewrite($customer['name']): Tools::link_rewrite($customer['firstname'].' '.$customer['lastname']);
                    $xml .='<url>'."\n";
                    $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('id_author'=>$customer['id_customer'],'alias'=>$alias,'is_customer'=>1)).']]></loc>'."\n";
                    $xml .='<priority>0.6</priority>'."\n"; 
                    $xml .='<lastmod>'.date('Y-m-d').'</lastmod>'."\n";
                    $xml .='<changefreq>daily</changefreq>'."\n";
                    if($customer['avata'] || $customer['avata'])
                    {
                        $xml .='<image:image>'."\n";
                        $xml .='<image:loc><![CDATA['.$this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'avata/'.$customer['avata']).']]></image:loc>'."\n";
                        $xml .='<image:caption><![CDATA['.($customer['name'] ? $customer['name']: $customer['firstname'].' '.$customer['lastname']).']]></image:caption>'."\n";
                        $xml .='<image:title><![CDATA['.($customer['name'] ? $customer['name']: $customer['firstname'].' '.$customer['lastname']).']]></image:title>'."\n";
                        $xml .='</image:image>'."\n";   
                        
                    }
                    $xml .='</url>'."\n";
               }
           }
           
       }
       if(in_array('latest_post',$pages_sitemap))
       {
            $xml .='<url>'."\n";
            $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('latest'=>true)).']]></loc>'."\n";
            $xml .='<priority>1.0</priority>'."\n"; 
            $xml .='<changefreq>daily</changefreq>';
            $xml .='</url>'."\n";
       }
       if(in_array('featured_post',$pages_sitemap))
       {
            $xml .='<url>'."\n";
            $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('featured'=>true)).']]></loc>'."\n";
            $xml .='<priority>1.0</priority>'."\n"; 
            $xml .='<changefreq>daily</changefreq>';
            $xml .='</url>'."\n";
       }
       if(in_array('popular_post',$pages_sitemap))
       {
            $xml .='<url>'."\n";
            $xml .='<loc><![CDATA['.$this->module->getLink('blog',array('popular'=>true)).']]></loc>'."\n";
            $xml .='<priority>1.0</priority>'."\n"; 
            $xml .='<changefreq>daily</changefreq>';
            $xml .='</url>'."\n";
       }
       $xml .='</urlset>';
       if (ob_get_length() > 0) {
            ob_end_clean();
       }
       header("Content-Type: application/xml; charset=UTF-8");
       mb_internal_encoding('UTF-8');
       die($xml);
    }
}
