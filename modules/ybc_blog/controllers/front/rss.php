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
class Ybc_blogRssModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public function __construct()
	{
		parent::__construct();
        $this->display_column_right=false;
        $this->display_column_left =false;
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
           $this->module->setMetas();
           if(Configuration::get('YBC_BLOG_ENABLE_RSS'))
           {
                if(Tools::isSubmit('id_category') && $id_category=(int)Tools::getValue('id_category'))
                {
                    $ybc_category= new Ybc_blog_category_class($id_category,$this->context->language->id);
                    $posts = $this->module->getPostsByIdCategory($id_category);
                    if($posts)
                    {
                        foreach($posts as &$post)
                        {
                            if($post['thumb'])
                                $post['thumb'] =trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/thumb/'.$post['thumb'];
                            if($post['image'])
                                $post['image'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/'.$post['image'];
                            $post['link'] = $this->module->getLink('blog',array('id_post'=>$post['id_post']));
                        }
                    }
                    $xml ='<?xml version="1.0" encoding="UTF-8"?>';
                    $xml .='<rss xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="2.0">'."\n";
                    $xml .='<channel>'."\n";
                        $xml .='<title>'.$this->cleanUTF8($ybc_category->title).'</title>'."\n";
                        $xml .='<description>'.($ybc_category->description ? strip_tags($this->cleanUTF8($ybc_category->description)):'').'</description>'."\n";
                        if($ybc_category->image)
                        {
                            $xml .='<image>'."\n";
                            $xml .='<url>'.trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/category/'.$ybc_category->image.'</url>'."\n";
                            $xml .='<title>'.$this->cleanUTF8($ybc_category->title).'</title>'."\n";
                            $xml .='<link>'.$this->module->getLink().'</link>'."\n";
                            $xml .='</image>'."\n";
                        }  
                        $xml .='<pubDate>'.date('r',strtotime($ybc_category->datetime_added)).'</pubDate>'."\n";
                        $xml .='<generator>'.$this->context->shop->domain.'</generator>'."\n";
                        $xml .='<link>'.$this->module->getLink('blog',array('id_category'=>$ybc_category->id)).'</link>'."\n";
                        if($posts)
                        {
                            $xml .= $this->getXml($posts);
                        }
                    $xml .= '</channel>';
                    $xml .='</rss>';
                    if (ob_get_length() > 0) {
                        ob_end_clean();
                    }
                    header("Content-Type: application/rss+xml; charset=UTF-8");
                    mb_internal_encoding('UTF-8');
                    die(utf8_encode($xml));
               }
               if(Tools::isSubmit('latest'))
               {
                    $posts = $this->module->getPostsWithFilter(' AND p.enabled=1','p.id_post DESC, ');
                    if($posts)
                    {
                        foreach($posts as &$post)
                        {
                            if($post['thumb'])
                                $post['thumb'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/thumb/'.$post['thumb'];
                            if($post['image'])
                                $post['image'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/'.$post['image'];
                            $post['link'] = $this->module->getLink('blog',array('id_post'=>$post['id_post']));
                        }
                    }
                    $xml ='<?xml version="1.0" encoding="UTF-8"?>';
                    $xml .='<rss xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="2.0">'."\n";
                    $xml .='<channel>'."\n";
                        $xml .='<title>'.$this->module->l('Latest posts','rss').'</title>'."\n";
                        $xml .='<description></description>'."\n";
                        $xml .='<generator>'.$this->context->shop->domain.'</generator>'."\n";
                        $xml .='<link>'.$this->module->getLink('blog',array('latest'=>1)).'</link>'."\n";
                        if($posts)
                        {
                            $xml .= $this->getXml($posts);
                        }
                    $xml .= '</channel>';
                    $xml .='</rss>';
                    if (ob_get_length() > 0) {
                        ob_end_clean();
                    }
                    header("Content-Type: application/rss+xml; charset=UTF-8");
                    mb_internal_encoding('UTF-8');
                    die(utf8_encode($xml));
                    
               }     
               if(Tools::isSubmit('popular'))
               {
                    $posts = $this->module->getPostsWithFilter(' AND p.enabled=1','p.click_number desc,');
                    if($posts)
                    {
                        foreach($posts as &$post)
                        {
                            if($post['thumb'])
                                $post['thumb'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/thumb/'.$post['thumb'];
                            if($post['image'])
                                $post['image'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/'.$post['image'];
                            $post['link'] = $this->module->getLink('blog',array('id_post'=>$post['id_post']));
                        }
                    }
                    $xml ='<?xml version="1.0" encoding="UTF-8"?>';
                    $xml .='<rss xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="2.0">'."\n";
                    $xml .='<channel>'."\n";
                    $xml .='<title>'.$this->module->l('Featured posts','rss').'</title>'."\n";
                    $xml .='<description></description>'."\n";
                    $xml .='<generator>'.$this->context->shop->domain.'</generator>'."\n";
                    $xml .='<link>'.$this->module->getBaseLink().'</link>'."\n";
                    if($posts)
                    {
                        $xml .= $this->getXml($posts);
                    }
                    $xml .= '</channel>';
                    $xml .='</rss>';
                    if (ob_get_length() > 0) {
                        ob_end_clean();
                    }
                    header("Content-Type: application/rss+xml; charset=UTF-8");
                    mb_internal_encoding('UTF-8');
                    die(utf8_encode($xml)); 
               }
               if(Tools::isSubmit('featured'))
               {
                    $posts = $this->module->getPostsWithFilter(' AND p.enabled=1 AND p.is_featured=1',$this->module->sort);
                    if($posts)
                    {
                        foreach($posts as &$post)
                        {
                            if($post['thumb'])
                                $post['thumb'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/thumb/'.$post['thumb'];
                            if($post['image'])
                                $post['image'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/'.$post['image'];
                            $post['link'] = $this->module->getLink('blog',array('id_post'=>$post['id_post']));
                        }
                    }
                    $xml ='<?xml version="1.0" encoding="UTF-8"?>';
                    $xml .='<rss xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="2.0">'."\n";
                    $xml .='<channel>'."\n";
                    $xml .='<title>'.$this->module->l('Featured posts','rss').'</title>'."\n";
                    $xml .='<description></description>'."\n";
                    $xml .='<generator>'.$this->context->shop->domain.'</generator>'."\n";
                    $xml .='<link>'.$this->module->getBaseLink().'</link>'."\n";
                    if($posts)
                    {
                        $xml .= $this->getXml($posts);
                    }
                    $xml .= '</channel>';
                    $xml .='</rss>';
                    if (ob_get_length() > 0) {
                        ob_end_clean();
                    }
                    header("Content-Type: application/rss+xml; charset=UTF-8");
                    mb_internal_encoding('UTF-8');
                    die(utf8_encode($xml));   
                }
                if($id_author=(int)Tools::getValue('id_author'))
                {
                    $posts = $this->module->getPostsWithFilter(' AND p.added_by="'.(int)$id_author.'" AND p.is_customer="'.(int)Tools::getValue('is_customer').'"',$this->module->sort);
                    if($posts)
                    {
                        foreach($posts as &$post)
                        {
                            if($post['thumb'])
                                $post['thumb'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/thumb/'.$post['thumb'];
                            if($post['image'])
                                $post['image'] = trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/post/'.$post['image'];
                            $post['link'] = $this->module->getLink('blog',array('id_post'=>$post['id_post']));
                        }
                    }
                    $xml ='<?xml version="1.0" encoding="UTF-8"?>';
                    $xml .='<rss xmlns:slash="http://purl.org/rss/1.0/modules/slash/" version="2.0">'."\n";
                    $xml .='<channel>'."\n";
                    $xml .='<title>'.$this->module->l('Featured posts','rss').'</title>'."\n";
                    $xml .='<description></description>'."\n";
                    $xml .='<generator>'.$this->context->shop->domain.'</generator>'."\n";
                    $xml .='<link>'.$this->module->getBaseLink().'</link>'."\n";
                    if($posts)
                    {
                        $xml .= $this->getXml($posts);
                    }
                    $xml .= '</channel>';
                    $xml .='</rss>';
                    if (ob_get_length() > 0) {
                        ob_end_clean();
                    }
                    header("Content-Type: application/rss+xml; charset=UTF-8");
                    mb_internal_encoding('UTF-8');
                    die(utf8_encode($xml));   
                }         
                $prettySkin = Configuration::get('YBC_BLOG_GALLERY_SKIN');
                $randomcode = time();
                $this->context->smarty->assign(
                    array(
                        'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT'),
                        'allowGuestsComments' => (int)Configuration::get('YBC_BLOG_ALLOW_GUEST_COMMENT') ? true : false,
                        'blogCommentAction' => $this->module->getLink('blog',array('id_post'=>(int)Tools::getValue('id_post'))),
                        'hasLoggedIn' => $this->context->customer->isLogged(true), 
                        'YBC_BLOC_RSS_TYPE' => Configuration::get('YBC_BLOC_RSS_TYPE')? explode(',',Configuration::get('YBC_BLOC_RSS_TYPE')):array(),
                        'link_latest_posts' => $this->module->getLink('rss',array('latest_posts'=>1)),
                        'link_popular_posts' => $this->module->getLink('rss',array('popular_posts'=>1)),
                        'link_featured_posts' => $this->module->getLink('rss',array('featured_posts'=>1)),
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
                        'prettySkin' => in_array($prettySkin, array('dark_square','dark_rounded','default','facebook','light_rounded','light_square')) ? $prettySkin : 'dark_square', 
                        'prettyAutoPlay' => (int)Configuration::get('YBC_BLOG_GALLERY_AUTO_PLAY') ? 1 : 0,
                        'path' => $this->module->getBreadCrumb(),
                        'show_author' => (int)Configuration::get('YBC_BLOG_SHOW_POST_AUTHOR') ? 1 : 0,
                        'blog_random_code' => $randomcode,
                        'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                        'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')), 
                        'blog_related_product_type' => Tools::strtolower(Configuration::get('YBC_RELATED_PRODUCTS_TYPE')),
                        'blog_related_posts_type' => Tools::strtolower(Configuration::get('YBC_RELATED_POSTS_TYPE')),
                        'blog_template_dir' => dirname(__FILE__).'/../../views/templates/front',
                        'breadcrumb' => $this->module->is17 ? $this->module->getBreadCrumb() : false,
                        'blog_dir' => $this->module->blogDir,
                        'image_folder' => trim($this->module->getBaseLink(),'/') .'/modules/'.$this->module->name.'/views/img/',
                    )
                );
           }           
           if($this->module->is17)
                $this->setTemplate('module:ybc_blog/views/templates/front/rss.tpl');      
           else         
                $this->setTemplate('rss_16.tpl'); 
    }
    public function cleanUTF8($some_string)
    {
        $some_string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.'|[\x00-\x7F][\x80-\xBF]+'.'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S','?', $some_string );
        $some_string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.'|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $some_string );
        return $some_string;
    }
    public function getXML($posts)
    {
        $xml ='';
        foreach($posts as $post)
        {
            $xml .='<item>'."\n";
                $xml .='<title>'.$post['title'].'</title>'."\n";
                $xml .='<description><![CDATA[';
                $xml.='<a href="'.($post['thumb'] ? $post['thumb'] : ($post['image']?$post['image']:'')).'"><img width=130 height=100 src="'.($post['thumb'] ? $post['thumb'] : ($post['image']?$post['image']:'')).'" ></a>';
                $xml .='</br>'.strip_tags($this->cleanUTF8($post['short_description'])); 
                $xml.=']]></description>'."\n";
                $xml .='<pubDate>'.date('r',strtotime($post['datetime_added'])).'</pubDate>'."\n";
                $xml .='<link>'.$post['link'].'</link>'."\n";;
                $xml .='<guid>'.$post['link'].'</guid>'."\n";;
                $xml .='<slash:comments>0</slash:comments>'."\n";;
            $xml .='</item>'."\n";;
        }
        return $xml;
    }
}