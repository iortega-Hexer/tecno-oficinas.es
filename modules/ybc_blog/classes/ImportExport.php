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

include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_category_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_post_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_list_helper_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_paggination_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_comment_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_slide_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_gallery_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_link_class.php');
include_once(_PS_MODULE_DIR_.'ybc_blog/ybc_blog_defines.php');
class Ybc_Blog_ImportExport extends Module
{
	public	function __construct()
	{
		$this->name = 'ybc_blog';
		parent::__construct();
        $this->module= new Ybc_blog();
        $this->defines = new Ybc_blog_defines();
	}
    public function getPostAllLanguage($id_post)
    {
        $sql = 'SELECT p.id_post,pl.title,pl.meta_title,pl.description,pl.short_description,pl.meta_keywords,pl.meta_description,pl.url_alias,l.iso_code,l.id_lang FROM '._DB_PREFIX_.'ybc_blog_post p
            LEFT JOIN '._DB_PREFIX_.'ybc_blog_post_lang pl on (p.id_post = pl.id_post)
            LEFT JOIN '._DB_PREFIX_.'lang l on (pl.id_lang=l.id_lang)
            WHERE p.id_post ="'.(int)$id_post.'"
        ';
        return Db::getInstance()->executeS($sql);
    }
    public function getPosts()
    {
        $sql ='SELECT * FROM '._DB_PREFIX_.'ybc_blog_post p,'._DB_PREFIX_.'ybc_blog_post_shop ps WHERE p.id_post=ps.id_post AND ps.id_shop='.(int)Context::getContext()->shop->id;
        return Db::getInstance()->executeS($sql);
    }
    public function getCategories($id_root,&$categories)
    {
        $sql ='SELECT * FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_category ="'.(int)$id_root.'" AND  c.id_category=cs.id_category AND cs.id_shop="'.(int)Context::getContext()->shop->id.'"';
        $category= Db::getInstance()->getRow($sql);
        if($category)
            $categories[]=$category;
        $childs= $this->getChildCategory($id_root);
        if($childs)
        {
            foreach($childs as $child)
            {
                $this->getCategories($child['id_category'],$categories);
            }
        }
        return $categories;
    }
    public function getChildCategory($id_parent)
    {
        $sql ='SELECT * FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_parent ="'.(int)$id_parent.'" AND  c.id_category=cs.id_category AND cs.id_shop="'.(int)Context::getContext()->shop->id.'"';
        return  Db::getInstance()->executeS($sql);
    }
    public function getCategoryByIDPost($id_post)
    {
        $sql='SELECT c.* FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_post_category pc WHERE c.id_category =pc.id_category AND pc.id_post= "'.(int)$id_post.'"';
        return Db::getInstance()->executeS($sql);
    }
    public function getCategoryAllLanguage($id_category)
    {
        $sql ='SELECT c.id_category, cl.title,cl.description,cl.meta_keywords,cl.meta_description,cl.url_alias,cl.meta_title,l.iso_code,l.id_lang FROM '._DB_PREFIX_.'ybc_blog_category c
                LEFT JOIN '._DB_PREFIX_.'ybc_blog_category_lang cl on (c.id_category = cl.id_category)
                LEFT JOIN '._DB_PREFIX_.'lang l ON (l.id_lang= cl.id_lang)
                WHERE c.id_category="'.(int)$id_category.'"';
        return Db::getInstance()->executeS($sql);
    }
    public function getTags($id_post)
    {
        $sql = 'SELECT t.*,l.iso_code FROM '._DB_PREFIX_.'ybc_blog_tag t
        LEFT JOIN '._DB_PREFIX_.'lang l ON (t.id_lang=l.id_lang)
        WHERE t.id_post="'.(int)$id_post.'"
        '; 
        return Db::getInstance()->executeS($sql);
    }
    public function getSlides()
    {
        $sql ='SELECT * FROM '._DB_PREFIX_.'ybc_blog_slide s,'._DB_PREFIX_.'ybc_blog_slide_shop ss WHERE s.id_slide=ss.id_slide AND ss.id_shop='.(int)Context::getContext()->shop->id;
        return Db::getInstance()->executeS($sql);
    }
    public function getSlideAllLanguage($id_slide)
    {
        $sql='SELECT sl.*,l.iso_code FROM '._DB_PREFIX_.'ybc_blog_slide_lang sl,'._DB_PREFIX_.'lang l WHERE sl.id_lang= l.id_lang AND sl.id_slide='.(int)$id_slide;
        return Db::getInstance()->executeS($sql);
    }
    public function getGalleries()
    {
        $sql= 'SELECT * FROM '._DB_PREFIX_.'ybc_blog_gallery g,'._DB_PREFIX_.'ybc_blog_gallery_shop gs WHERE g.id_gallery=gs.id_gallery AND gs.id_shop='.(int)Context::getContext()->shop->id;
        return Db::getInstance()->executeS($sql);
    }
    public function getGalleryAllLanguage($id_gallery)
    {
        $sql='SELECT gl.*,l.iso_code FROM '._DB_PREFIX_.'ybc_blog_gallery_lang gl,'._DB_PREFIX_.'lang l WHERE gl.id_lang=l.id_lang AND gl.id_gallery='.(int)$id_gallery;
        return Db::getInstance()->executeS($sql);
    }
    private function archiveThisFile($obj, $file, $server_path, $archive_path)
    {
        if (is_dir($server_path.$file)) {
            $dir = scandir($server_path.$file);
            foreach ($dir as $row) {
                if ($row[0] != '.') {
                    $this->archiveThisFile($obj, $row, $server_path.$file.'/', $archive_path.$file.'/');
                }
            }
        } else $obj->addFile($server_path.$file, $archive_path.$file);
    }
    public function generateArchive()
    {
        $errors = array();
        $zip = new ZipArchive();
        $cacheDir = dirname(__FILE__).'/../cache/';
        $zip_file_name = 'ybc_blog_'.date('dmYHis').'.zip';
        if ($zip->open($cacheDir.$zip_file_name, ZipArchive::OVERWRITE | ZipArchive::CREATE) === true) {
            if (!$zip->addFromString('blog-data.xml', $this->exportBlog())) {
               $errors[] = $this->l('Cannot create blog-data.xml');
            }
            $this->archiveThisFile($zip,'category', dirname(__FILE__).'/../views/img/', 'img/');
            $this->archiveThisFile($zip,'gallery', dirname(__FILE__).'/../views/img/', 'img/');
            $this->archiveThisFile($zip,'post', dirname(__FILE__).'/../views/img/', 'img/');
            $this->archiveThisFile($zip,'slide', dirname(__FILE__).'/../views/img/', 'img/');
            $this->archiveThisFile($zip,'temp', dirname(__FILE__).'/../views/img/', 'img/');
            $zip->close();
            if (!is_file($cacheDir.$zip_file_name)) {
                $errors[] = $this->l(sprintf('Could not create %1s', _PS_CACHE_DIR_.$zip_file_name));
            }
            if (!$errors) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
    
                ob_start();
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');
                header('Content-Transfer-Encoding: binary');
                ob_end_flush();
                readfile($cacheDir.$zip_file_name);
                @unlink($cacheDir.$zip_file_name);
                exit;
            }
        }
        return $errors;
    }
    public function getComments($id_post)
    {
        $sql='SELECT * FROM '._DB_PREFIX_.'ybc_blog_comment WHERE id_post='.(int)$id_post.' AND subject!="" AND comment!=""';
        return Db::getInstance()->executeS($sql);
    }
    public function getPolls($id_post)
    {
        $sql= 'SELECT * FROM '._DB_PREFIX_.'ybc_blog_polls WHERE id_post='.(int)$id_post;
        return Db::getInstance()->executeS($sql);
    }
	public function exportBlog() 
	{		
		$xml_output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml_output .= '<entity_profile>'."\n";	
        $categories=array();
        $categories =$this->getCategories(0,$categories);
        if($categories)
        {
            foreach($categories as $category)
            {
                $xml_output .='<category id_parent="'.$category['id_parent'].'" id_category="'.$category['id_category'].'" image="'.$category['image'].'" thumb="'.$category['thumb'].'" added_by="'.$category['added_by'].'" modified_by="'.$category['modified_by'].'" enabled="'.$category['enabled'].'" datetime_added="'.$category['datetime_added'].'" datetime_modified="'.$category['datetime_modified'].'" sort_order="'.$category['sort_order'].'">'."\n";
                $categoryLanguages = $this->getCategoryAllLanguage($category['id_category']);
                    if($categoryLanguages)
                    {
                       foreach($categoryLanguages as $categoryLanguage)
                       {
                            $xml_output .='<categorylanguage iso_code="'.$categoryLanguage['iso_code'].'" default="'.($categoryLanguage['id_lang']==Configuration::get('PS_LANG_DEFAULT') ? 1 :0).'">'."\n";
                                $xml_output .='<title><![CDATA['.$categoryLanguage['title'].']]></title>'."\n";
                                $xml_output .='<meta_title><![CDATA['.$categoryLanguage['meta_title'].']]></meta_title>'."\n";
                                $xml_output .='<url_alias><![CDATA['.$categoryLanguage['url_alias'].']]></url_alias>'."\n";
                                $xml_output .='<description><![CDATA['.$categoryLanguage['description'].']]></description>'."\n";
                                $xml_output .='<meta_keywords><![CDATA['.$categoryLanguage['meta_keywords'].']]></meta_keywords>'."\n";
                                $xml_output .='<meta_description><![CDATA['.$categoryLanguage['meta_description'].']]></meta_description>'."\n";
                            $xml_output .='</categorylanguage>'."\n";
                       } 
                    }
                $xml_output .='</category>'."\n";
            }
        }  
        $posts = $this->getPosts();
        if($posts)
            foreach($posts as $post)
            {
                $id_post= $post['id_post'];
                $xml_output .= '<post id_post="'.$post['id_post'].'" is_featured="'.$post['is_featured'].'" products="'.$post['products'].'" thumb = "'.$post['thumb'].'" image ="'.$post['image'].'" added_by ="'.(int)$post['added_by'].'" modified_by="'.(int)$post['modified_by'].'" enabled="'.$post['enabled'].'" datetime_added ="'.$post['datetime_added'].'" datetime_modified="'.$post['datetime_modified'].'" datetime_active="'.$post['datetime_active'].'" sort_order="'.$post['sort_order'].'" click_number="'.(int)$post['click_number'].'" likes ="'.$post['likes'].'" is_customer="'.(int)$post['is_customer'].'" >'."\n";					
                $postAllLanguage = $this->getPostAllLanguage($id_post);
                if($postAllLanguage)
                {
                    foreach($postAllLanguage as $language)
                    {
                        $xml_output .='<language iso_code ="'.$language['iso_code'].'" default="'.($language['id_lang']==Configuration::get('PS_LANG_DEFAULT')?1 :0 ).'">'."\n";
                            $xml_output .='<title><![CDATA['.$language['title'].']]></title>'."\n";
                            $xml_output .='<meta_title><![CDATA['.$language['meta_title'].']]></meta_title>'."\n";
                            $xml_output .='<url_alias><![CDATA['.$language['url_alias'].']]></url_alias>'."\n";
                            $xml_output .='<description><![CDATA['.$language['description'].']]></description>'."\n";
                            $xml_output .='<short_description><![CDATA['.$language['short_description'].']]></short_description>'."\n";
                            $xml_output .='<meta_keywords><![CDATA['.$language['meta_keywords'].']]></meta_keywords>'."\n";
                            $xml_output .='<meta_description><![CDATA['.$language['meta_description'].']]></meta_description>'."\n";
                        $xml_output .='</language>'."\n";
                    }
                }
                $categories = $this->getCategoryByIDPost($id_post);
                if($categories)
                    foreach($categories as $category)
                    {
                       $xml_output .='<category id_category="'.$category['id_category'].'">'."\n";
                       $xml_output .='</category>'."\n"; 
                    }
                $tags =$this->getTags($id_post);
                if($tags)
                    foreach($tags as $tag)
                    {
                        $xml_output .='<tags iso_code="'.$tag['iso_code'].'" tag="'.$tag['tag'].'" click_number="'.$tag['click_number'].'"></tags>'."\n";
                    }
                $comments = $this->getComments($id_post);
                if($comments)
                    foreach($comments as $comment)
                    {
                        $xml_output .='<comment id_user="'.$comment['id_user'].'" name="'.$comment['name'].'" email="'.$comment['email'].'" subject="'.$comment['subject'].'" replied_by="'.$comment['replied_by'].'" rating="'.$comment['rating'].'" approved="'.$comment['approved'].'" datetime_added="'.$comment['datetime_added'].'" reported="'.$comment['reported'].'">'."\n";
                            $xml_output .='<comment_text><![CDATA['.$comment['comment'].']]></comment_text>'."\n";
                            $replies= Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'ybc_blog_reply WHERE id_comment='.(int)$comment['id_comment']);
                            if($replies)
                            {
                                foreach($replies as $reply)
                                {
                                    $xml_output .='<reply id_comment="'.$reply['id_comment'].'" id_user="'.$comment['id_user'].'" name="'.$reply['name'].'" email="'.$reply['email'].'" id_employee="'.$reply['id_employee'].'" approved="'.$reply['approved'].'" datetime_added="'.$reply['datetime_added'].'" datetime_updated="'.$reply['datetime_updated'].'">'."\n";
                                        $xml_output .='<reply_text><![CDATA['.$reply['reply'].']]></reply_text>';
                                    $xml_output .='</reply>'."\n";
                                }
                            }
                            
                        $xml_output .='</comment>'."\n";
                    }
                $polls = $this->getPolls($id_post);
                if($polls)
                {
                    foreach($polls as $poll)
                    {
                        $xml_output .='<post_polls id_user="'.(int)$poll['id_user'].'" name="'.$poll['name'].'" email ="'.$poll['email'].'" id_post="'.(int)$poll['id_post'].'" polls="'.$poll['polls'].'" dateadd="'.(int)$poll['dateadd'].'" >'."\n";
                        $xml_output .='<post_polls_text><![CDATA['.$poll['feedback'].']]></post_polls_text>'."\n";
                        $xml_output .="</post_polls>". "\n";
                    }
                }
                $xml_output .= '</post>'."\n"; 
            }
        $slides = $this->getSlides();
        if($slides)
            foreach($slides as $slide)
            {
                $xml_output .='<slide id_slide="'.(int)$slide['id_slide'].'" image="'.$slide['image'].'" enabled="'.$slide['enabled'].'" sort_order="'.$slide['sort_order'].'">'."\n";
                    $slideLanguages= $this->getSlideAllLanguage($slide['id_slide']);
                    if($slideLanguages)
                        foreach($slideLanguages as $slideLanguage)
                        {
                            $xml_output .='<slidelanguage iso_code="'.$slideLanguage['iso_code'].'" default="'.($slideLanguage['id_lang']==Configuration::get('PS_LANG_DEFAULT')?1:0).'">'."\n";
                                $xml_output .='<caption><![CDATA['.$slideLanguage['caption'].']]></caption>'."\n";
                                $xml_output .='<url><![CDATA['.$slideLanguage['url'].']]></url>'."\n";
                            $xml_output .='</slidelanguage>'."\n";
                        }
                $xml_output .='</slide>'."\n";
            }
        $galleries= $this->getGalleries();
        if($galleries)
            foreach($galleries as $gallery)
            {
                $xml_output .='<gallery id_gallery="'.(int)$gallery['id_gallery'].'" image="'.$gallery['image'].'" thumb="'.$gallery['thumb'].'" is_featured="'.$gallery['is_featured'].'" enabled="'.$gallery['enabled'].'" sort_order="'.$gallery['sort_order'].'">'."\n";
                    $galleryLanguages= $this->getGalleryAllLanguage($gallery['id_gallery']);
                    if($galleryLanguages)
                        foreach($galleryLanguages as $galleryLanguage)
                        {
                            $xml_output .='<gallerylanguage iso_code="'.$galleryLanguage['iso_code'].'" default="'.($galleryLanguage['id_lang']==Configuration::get('PS_LANG_DEFAULT') ? 1:0).'">'."\n";
                                $xml_output .='<title><![CDATA['.$galleryLanguage['title'].']]></title>'."\n";
                                $xml_output .='<description><![CDATA['.$galleryLanguage['description'].']]></description>'."\n";
                            $xml_output .='</gallerylanguage>'."\n";
                        }
                $xml_output .='</gallery>'."\n";
            }
        $xml_output .='<configuration>'."\n";
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_seo);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_sitemap);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_homepage);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_categorypage);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_productpage);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_sidebar);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_email);
        $xml_output .= $this->exportXmlConfiguration($this->defines->socials);
        $xml_output .= $this->exportXmlConfiguration($this->defines->rss);
        $xml_output .= $this->exportXmlConfiguration($this->defines->customer_settings);
        $xml_output .= $this->exportXmlConfiguration($this->defines->configs_image);
        $xml_output .='</configuration>'."\n";
		$xml_output .= '</entity_profile>'."\n";
        return str_replace('&','and',$xml_output);	
	}
    public function exportXmlConfiguration($configs)
    {
        $languages = Language::getLanguages(false);
        $id_lang_default= Configuration::get('PS_LANG_DEFAULT');
        $xml_output ='';
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $xml_output .= '<'.$key.'>'."\n";
                    foreach($languages as $language)
                    {
                        $xml_output .= '<language iso_code="'.$language['iso_code'].'"'.($language['id_lang']==$id_lang_default ? ' default="1"':' default="0"').'>'."\n";
                            $xml_output .='<content><![CDATA['.Configuration::get($key,$language['id_lang'],null,null,false).']]></content>'."\n";
                        $xml_output .= '</language>'."\n";
                    }
                    $xml_output .='</'.$key.'>'."\n";
                }
                else
                {
                    $xml_output .='<'.$key.'><![CDATA['.Configuration::get($key).']]></'.$key.'>'."\n";
                }
            }
        }
        return $xml_output;
    }
    public function importXmlConfiguration($configs,$xml)
    {
        $languages = Language::getLanguages(false);
        foreach($configs as $key => $config)
        {
            if(isset($config['lang']) && $config['lang'])
            {
                $values = array();
                $xmlKey = $xml->{$key};
                $default= '';
                $languageimporteds= array();
                if($xmlKey->language)
                {
                    foreach($xmlKey->language as $language_xml)
                    {
                        if((int)$language_xml['default'])
                            $default= (string)$language_xml->content;
                        if($id_lang= Language::getIdByIso((string)$language_xml['iso_code']))
                        {
                            $languageimporteds[]=$id_lang;
                            $values[$id_lang] = (string)$language_xml->content;
                        }
                    }
                }
                foreach($languages as $lang)
                {
                    if(!in_array($lang['id_lang'],$languageimporteds))
                    {
                        $values[$lang['id_lang']] = $default;
                    }
                }
                Configuration::updateValue($key, $values);
            }
            else
            {
                if(isset($xml->{$key}))
                {
                    Configuration::updateValue($key,(string)$xml->{$key});
                }
            }
                
        }
    }
    public function processImportWordPress()
    {
        $languages = Language::getLanguages(false);
        $errors = array();
        $content = Tools::file_get_contents($_FILES['blogdatawordpress']['tmp_name']);
        $content = str_replace('wp:','',$content);
        $file_xml = time().'.xml';
        file_put_contents(dirname(__FILE__).'/../cache/'.$file_xml,$content);
        $xml =simplexml_load_file(dirname(__FILE__).'/../cache/'.$file_xml);
        if(isset($xml->channel) && isset($xml->channel->item) && $xml->channel->item)
        {
            if(isset($xml->channel->category) && $xml->channel->category)
            {
                foreach($xml->channel->category as $category_xml)
                {
                    if(Tools::getValue('importoverridewp') && $id_category = $this->getCategoryByLinkRewrite((string)$category_xml->category_nicename))  
                        $category= new Ybc_blog_category_class($id_category);
                    else
                        $category = new Ybc_blog_category_class();
                    $category->enabled=1;
                    foreach($languages as $language)
                    {
                        $category->url_alias[$language['id_lang']] = (string)$category_xml->category_nicename;
                        $category->title[$language['id_lang']] = (string)$category_xml->cat_name;
                        if(isset($category_xml->category_description))
                            $category->description[$language['id_lang']] = (string)$category_xml->category_description;
                    }
                    $id_parent =(int)$this->getCategoryByLinkRewrite((string)$category_xml->category_parent);
                    if(!$category->id || $category->id_parent!=$id_parent)
                    {
                         $category->sort_order = 1+(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_category =cs.id_category AND c.id_parent="'.(int)$id_parent.'" AND cs.id_shop='.(int)$this->context->shop->id);
                    }
                    if($category->id)
                        $category->update();
                    else
                        $category->add();
                }
            }
            foreach($xml->channel->item as $item)
            {
                $oldImage='';
                $oldThumb='';
                if((string)$item->post_type=='post' && (string)$item->status!='trash')
                {
                    $link_riwite = Tools::link_rewrite((string)$item->title);
                    if(Tools::getValue('importoverridewp') && $id_post = $this->getPostByLinkRewrite($link_riwite))
                    {
                        $post = new Ybc_blog_post_class((int)$id_post);
                        $post->datetime_modified = (string)$item->datetime_modified;
                        $post->modified_by = (int)$this->context->employee->id;
                    } 
                    else
                    {
                        $post = new Ybc_blog_post_class();
                        $post->datetime_added = (string)$item->post_date;
                        $post->datetime_modified = (string)$item->datetime_modified;
                        $post->datetime_active = date('Y-m-d H:i:s');
                        $post->modified_by = (int)$this->context->employee->id;
                        $post->added_by = (int)$this->context->employee->id;
                        $post->is_customer=0;
                        $post->sort_order =1+ (int)Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_post_shop WHERE id_shop='.(int)$this->context->shop->id);
                    }
                    $content = $item->children('http://purl.org/rss/1.0/modules/content/');
                    $excerpt = $item->children('http://wordpress.org/export/1.2/excerpt/');
                   
                    if(isset($item->postmeta) && $item->postmeta)
                    {
                        $context = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
                        foreach($item->postmeta as $meta)
                        {
                            if((string)$meta->meta_key=='_thumbnail_id' && ($url=$this->getLinkGuidPost($xml,(int)$meta->meta_value)))
                            {
                                $newImage= time().(int)$meta->meta_value.'.jpg';
                                Ybc_Blog_ImportExport::copy($url,dirname(__FILE__).'/../views/img/post/'.$newImage,$context);
                                Ybc_Blog_ImportExport::copy($url,dirname(__FILE__).'/../views/img/post/thumb/'.$newImage,$context);
                                if(file_exists(dirname(__FILE__).'/../views/img/post/'.$newImage))
                                {
                                    $oldImage = $post->image;
                                    $post->image=$newImage;
                                }
                                if(file_exists(dirname(__FILE__).'/../views/img/post/thumb/'.$newImage))
                                {
                                    $oldThumb = $post->thumb;
                                    $post->thumb=$newImage;
                                }
                            }
                        }
                    }
                    foreach($languages as $language)
                    {
                        $post->title[$language['id_lang']]=(string)$item->title;
                        $post->url_alias[$language['id_lang']] = $link_riwite;
                        $post->short_description[$language['id_lang']] = (string)$excerpt->encoded ? (string)$excerpt->encoded : '';
                        $post->description[$language['id_lang']] = (string)$content->encoded ? (string)$content->encoded : '';
                    }
                    if((string)$item->status=='publish')
                        $post->enabled=1;
                    else
                        $post->enabled=0;
                    if($post->id)
                        $post->update();
                    else
                        $post->add();
                    if($post->id && $oldImage)
                    {
                        @unlink(dirname(__FILE__).'/../views/img/post/'.$oldThumb);
                    }
                    if($post->id && $oldThumb)
                    {
                        @unlink(dirname(__FILE__).'/../views/img/post/thumb/'.$oldThumb);
                    }
                    if($item->category && $post->id)
                    {
                        $tagStr='';
                        $tags=array();
                        foreach($item->category as $category_item)
                        {
                            if($category_item['domain']=='category')
                            {
                                if($id_category= $this->getCategoryByLinkRewrite($category_item['nicename']))
                                {
                                    if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_post="'.(int)$post->id.'" AND id_category="'.(int)$id_category.'"'))
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_post_category (id_post,id_category) VALUES("'.(int)$post->id.'","'.(int)$id_category.'")');
                                }
                                else
                                {
                                    $category_class = new Ybc_blog_category_class();
                                    foreach($languages as $language)
                                    {
                                        $category_class->title[$language['id_lang']] = (string)$category_item;
                                        $category_class->url_alias = (string)$category_item['nicename'];
                                    }
                                    $category_class->enabled=1;
                                    $category_class->id_parent=0;
                                    if(!$category_class->id)
                                    {
                                         $category_class->sort_order = 1+(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_category =cs.id_category AND c.id_parent="0" AND cs.id_shop='.(int)$this->context->shop->id);
                                    }
                                    if($category_class->add())
                                    {
                                        if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_category="'.(int)$category_class->id.'" AND id_post="'.(int)$post->id.'"'))
                                            Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_post_category (id_post,id_category) VALUES("'.(int)$post->id.'","'.(int)$category_class->id.'")');
                                    }
                                }
                            }
                            elseif($category_item['domain']=='post_tag')
                            {
                                $tagStr .=(string)$category_item.',';
                            }
                        }
                        $tagStr=trim($tagStr,',');
                        if($tagStr && Validate::isTagsList($tagStr))
                        {
                            $tagStr = explode(',',$tagStr);
                            
                            foreach($languages as $language)
                            {
                                $tags[$language['id_lang']]= $tagStr ? $tagStr:array();
                            }
                            $this->module->updateTags($post->id,$tags);
                        }
                        
                    }
                    if(isset($item->comment) && $item->comment)
                    {
                        foreach($item->comment as $comment)
                        {
                            $comment_class = new Ybc_blog_comment_class();
                            $comment_class->id_user=0;
                            $comment_class->name=(string)$comment->comment_author;
                            $comment_class->email =(string)$comment->email;
                            $comment_class->datetime_added =(string)$comment->comment_date;
                            $comment_class->comment =(string)$comment->comment_content;
                            $comment_class->id_post =$post->id;
                            $comment_class->subject = (string)$comment->comment_author;
                            if((int)$comment->comment_approved)
                                $comment_class->approved=1;
                            else
                                $comment_class->approved=0;
                            $comment_class->rating=5;
                            $comment_class->reported=0;    
                            $comment_class->add();
                        }
                    }
                }
            }
        }
        else
        {
            $errors[]=$this->l('Data null');
        }
        @unlink(dirname(__FILE__).'/../cache/'.$file_xml);
        return $errors;
    }
    public function getCategoryPost($name)
    {
        $sql ='SELECT c.id_category FROM '._DB_PREFIX_.'ybc_blog_category c
        INNER JOIN '._DB_PREFIX_.'ybc_blog_category_shop cs ON (c.id_category=cs.id_category)
        INNER JOIN '._DB_PREFIX_.'ybc_blog_category_lang cl ON (c.id_category=cl.id_category)
        WHERE cs.id_shop ="'.(int)$this->context->shop->id.'" AND cl.id_lang="'.(int)$this->context->language->id.'" AND title="'.pSQL($name).'"
        ';
        return Db::getInstance()->getValue($sql);
    }
    public function getPostByLinkRewrite($link_riwite)
    {
        $sql ='SELECT p.id_post FROM '._DB_PREFIX_.'ybc_blog_post p
        INNER JOIN '._DB_PREFIX_.'ybc_blog_post_shop ps ON (p.id_post=ps.id_post)
        INNER JOIN '._DB_PREFIX_.'ybc_blog_post_lang pl ON (p.id_post=pl.id_post)
        WHERE pl.id_lang="'.(int)$this->context->language->id.'" AND pl.url_alias="'.pSQL($link_riwite).'" AND ps.id_shop="'.(int)$this->context->shop->id.'"';
        return Db::getInstance()->getValue($sql);
    }
    public function getCategoryByLinkRewrite($link_riwite)
    {
        $sql ='SELECT c.id_category FROM '._DB_PREFIX_.'ybc_blog_category c
        INNER JOIN '._DB_PREFIX_.'ybc_blog_category_shop cs ON (c.id_category=cs.id_category)
        INNER JOIN '._DB_PREFIX_.'ybc_blog_category_lang cl ON (c.id_category=cl.id_category)
        WHERE cl.id_lang="'.(int)$this->context->language->id.'" AND cl.url_alias="'.pSQL($link_riwite).'" AND cs.id_shop="'.(int)$this->context->shop->id.'" ORDER BY c.id_category DESC';
        return (int)Db::getInstance()->getValue($sql);
    }
    public function processImport($zipfile = false)
    {
        $errors = array();
        if(Tools::getValue('data_import'))
        {
            if(!$zipfile)
            {
                $savePath = dirname(__FILE__).'/../cache/';
                if(@file_exists($savePath.'ybc_blog.data.zip'))
                    @unlink($savePath.'ybc_blog.data.zip');
                $uploader = new Uploader('blogdata');
                $uploader->setMaxSize(1048576000);
                $uploader->setAcceptTypes(array('zip'));        
                $uploader->setSavePath($savePath);
                $file = $uploader->process('ybc_blog.data.zip'); 
                if ($file[0]['error'] === 0) {
                    if (!Tools::ZipTest($savePath.'ybc_blog.data.zip')) 
                        $errors[] = $this->l('Zip file seems to be broken');
                } else {
                    $errors[] = $file[0]['error'];
                }
                $extractUrl = $savePath.'ybc_blog.data.zip';
            }
            else      
                $extractUrl = $zipfile;
            if(!@file_exists($extractUrl))
                $errors[] = $this->l('Zip file doesn\'t exist');
            if(!$errors)
            {
                $zip = new ZipArchive();
                if($zip->open($extractUrl) === true)
                {
                    if ($zip->locateName('blog-data.xml') === false)
                    {
                        $errors[] = $this->l('blog-data.xml doesn\'t exist');                    
                        if($extractUrl && !$zipfile)
                        {
                            @unlink($extractUrl);                        
                        }                      
                    }
                }
                else
                    $errors[] = $this->l('Cannot open zip file. It might be broken or damaged');
            } 
            if(!$errors)
            {
                if(!Tools::ZipExtract($extractUrl, dirname(__FILE__).'/../cache/'))
                    $errors[] = $this->l('Cannot extract zip data');
                if(!@file_exists(dirname(__FILE__).'/../cache/blog-data.xml'))
                    $errors[] = $this->l('Neither blog-data.xml exist');
            }        
            if(!$errors)
            {            
                if(@file_exists(dirname(__FILE__).'/../cache/blog-data.xml'))
                {
                    $this->importData(dirname(__FILE__).'/../cache/blog-data.xml');
                    @unlink(dirname(__FILE__).'/../cache/blog-data.xml');
                    $this->removeForderImgCache(dirname(__FILE__).'/../cache/img');
                }
                $zip->close();
                if(@file_exists($extractUrl))
                    @unlink($extractUrl);              
            }
        }
        else
        {
            $errors[]= $this->l('Data for import is null');
        }
        return $errors;        
    }
    public function importData($file_xml)
    {
        if (file_exists($file_xml))	
		{	
    		$xml = simplexml_load_file($file_xml);
            $categories=array();
            if($data_imports=Tools::getValue('data_import'))
            {
                if(in_array('posts_categories',$data_imports))
                {
                    if(isset($xml->category) && $xml->category)
                    {
                        foreach($xml->category as $category_xml)
                        {
                            if(Tools::getValue('importoverride') && $this->itemExists('category','id_category',(int)$category_xml['id_category']))
                                 $category = new Ybc_blog_category_class((int)$category_xml['id_category']);
                            else
                            {
                                $category = new Ybc_blog_category_class();
                                if(isset($category_xml['id_parent'])&&(int)$category_xml['id_parent']!=0)
                                    $id_parent = isset($categories[(int)$category_xml['id_parent']]) ? $categories[(int)$category_xml['id_parent']] : 0;
                                else
                                    $id_parent=0;
                                $category->id_parent =$id_parent;
                                $category->sort_order = 1+(int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_category c,'._DB_PREFIX_.'ybc_blog_category_shop cs WHERE c.id_category =cs.id_category AND c.id_parent="'.(int)$id_parent.'" AND cs.id_shop='.(int)$this->context->shop->id);
                            }   
                            $category->enabled = (int)$category_xml['enabled'];
                            $category->image = (string)$category_xml['image'];
                            $category->thumb =(string)$category_xml['thumb'];
                            $category->datetime_added = (string)$category_xml['datetime_added'];;
                            $category->datetime_modified = (string)$category_xml['datetime_modified'];;
                            $category->added_by = (int)Context::getContext()->employee->id;
                            $category->modified_by = (int)Context::getContext()->employee->id;
                            $languageCategoryImported=array();
                            if($category_xml->categorylanguage)
                            {
                                foreach($category_xml->categorylanguage as $categorylanguage)
                                {
                                    if((string)$categorylanguage['iso_code'])
                                    {
                                        $id_lang= Language::getIdByIso((string)$categorylanguage['iso_code']);
                                        if(isset($categorylanguage['default']) && (int)$categorylanguage['default'])
                                            $categoryLanguageDefault=$categorylanguage;
                                        if($id_lang)
                                        {
                                            $category->title[$id_lang] = (string)$categorylanguage->title;
                                            $category->meta_title[$id_lang] = (string)$categorylanguage->meta_title;
                                            $category->url_alias[$id_lang] =(string)$categorylanguage->url_alias;
                                            $category->description[$id_lang] = (string)$categorylanguage->description;
                                            $category->meta_description[$id_lang] = (string)$categorylanguage->meta_description;
                                            $category->meta_keywords[$id_lang] = (string)$categorylanguage->meta_keywords;
                                            $languageCategoryImported[]=$id_lang;
                                        }
                                    }
                                }
                            }
                            if(isset($categoryLanguageDefault))
                            {
                                foreach(Language::getLanguages(false) as $lang)
                                {
                                    if(!in_array($lang['id_lang'],$languageCategoryImported))
                                    {
                                        $category->title[$lang['id_lang']] = (string)$categoryLanguageDefault->title;
                                        $category->meta_title[$lang['id_lang']] = (string)$categoryLanguageDefault->meta_title;
                                        $category->url_alias[$lang['id_lang']] =(string)$categoryLanguageDefault->url_alias;
                                        $category->description[$lang['id_lang']] = (string)$categoryLanguageDefault->description;
                                        $category->meta_description[$lang['id_lang']] = (string)$categoryLanguageDefault->meta_description;
                                        $category->meta_keywords[$lang['id_lang']] = (string)$categoryLanguageDefault->meta_keywords;
                                    }    
                                }
                            }
                            $category->save();
                            if($category->image && file_exists(dirname(__FILE__).'/../cache/img/category/'.$category->image))
                                copy(dirname(__FILE__).'/../cache/img/category/'.$category->image,dirname(__FILE__).'/../views/img/category/'.$category->image);
                            if($category->thumb && file_exists(dirname(__FILE__).'/../cache/img/category/thumb/'.$category->thumb))
                                copy(dirname(__FILE__).'/../cache/img/category/thumb/'.$category->thumb,dirname(__FILE__).'/../views/img/category/thumb/'.$category->thumb);
                            $categories[(int)$category_xml['id_category']] = $category->id;     
                        }
                    }
                    if(isset($xml->post) && $xml->post)
                    {
                        foreach ($xml->post as $post_xml)
                		{
                		    if(Tools::getValue('importoverride') && $this->itemExists('post','id_post',(int)$post_xml['id_post']))  
                                $post = new Ybc_blog_post_class((int)$post_xml['id_post']);
                            else
                            {
                                $post = new Ybc_blog_post_class();
                                $post->sort_order =1+ (int)Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_post_shop WHERE id_shop='.(int)$this->context->shop->id);
                            } 
                            $post->enabled = (int)$post_xml['enabled'];
                            $post->datetime_added = (string)$post_xml['datetime_added'];
                            $post->datetime_modified = (string)$post_xml['datetime_modified'];
                            if(Tools::getValue('keepauthorid'))
                            {
                                $ok=false;
                                if((int)$post_xml['is_customer'])
                                {
                                    if(Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'customer WHERE id_customer='.(int)$post_xml['added_by']))
                                    {
                                        $ok=true;
                                    }
                                }
                                else{
                                    if(Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'employee WHERE id_employee='.(int)$post_xml['added_by']))
                                    {
                                        $ok=true;
                                    }
                                }
                                if($ok)
                                {
                                    $post->added_by = (int)$post_xml['added_by'];
                                    $post->modified_by = (int)$post_xml['modified_by'];
                                    $post->is_customer =(int)$post_xml['is_customer'];
                                }
                                else
                                {
                                    $post->added_by = (int)Context::getContext()->employee->id;
                                    $post->modified_by = (int)Context::getContext()->employee->id;
                                    $post->is_customer =0;
                                }
                            }
                            else
                            {
                                $post->added_by = (int)Context::getContext()->employee->id;
                                $post->modified_by = (int)Context::getContext()->employee->id;
                                $post->is_customer=0;
                            }
                            $post->click_number = (int)$post_xml['click_number'];
                            $post->likes = (int)$post_xml['likes'];
                            $post->products = (string)$post_xml['products'];
                            $post->thumb = (string)$post_xml['thumb'];
                            $post->image = (string)$post_xml['image'];
                            $post->is_featured = (int)$post_xml['is_featured'];
                            $post->datetime_active = (string)$post_xml['datetime_active'];
                            $languagePostImported = array();
                            if($post_xml->language)       
                                foreach($post_xml->language as $language)
                                {
                                    if((string)$language['iso_code'])
                                    {
                                        $id_lang = Language::getIdByIso((string)$language['iso_code']);
                                        if(isset($language['default']) && (int)$language['default'])
                                            $languagePostDefault=$language;
                                        if($id_lang)
                                        {
                                            $post->title[$id_lang] =(string)$language->title;
                                            $post->url_alias[$id_lang] = (string)$language->url_alias;
                                            $post->short_description[$id_lang] = (string)$language->short_description;
                                            $post->description[$id_lang] = (string)$language->description;
                                            $post->meta_description[$id_lang] =(string)$language->meta_description;
                                            $post->meta_keywords[$id_lang] = (string)$language->meta_keywords;
                                            $languagePostImported[]=$id_lang;
                                        }
                                    }  
                                }
                            if(isset($languagePostDefault))
                            {
                                foreach(Language::getLanguages(false) as $lang)
                                {
                                    if(!in_array($lang['id_lang'],$languagePostImported))
                                    {
                                        $post->title[$lang['id_lang']] =(string)$languagePostDefault->title;
                                        $post->meta_title[$lang['id_lang']] =(string)$languagePostDefault->meta_title;
                                        $post->url_alias[$lang['id_lang']] = (string)$languagePostDefault->url_alias;
                                        $post->short_description[$lang['id_lang']] = (string)$languagePostDefault->short_description;
                                        $post->description[$lang['id_lang']] = (string)$languagePostDefault->description;
                                        $post->meta_description[$lang['id_lang']] =(string)$languagePostDefault->meta_description;
                                        $post->meta_keywords[$lang['id_lang']] = (string)$languagePostDefault->meta_keywords;
                                    }
                                }
                            }
                            $post->save();
                            if($post->image && file_exists(dirname(__FILE__).'/../cache/img/post/'.$post->image))
                                copy(dirname(__FILE__).'/../cache/img/post/'.$post->image,dirname(__FILE__).'/../views/img/post/'.$post->image);
                            if($post->thumb && file_exists(dirname(__FILE__).'/../cache/img/post/thumb/'.$post->thumb))
                                copy(dirname(__FILE__).'/../cache/img/post/thumb/'.$post->thumb,dirname(__FILE__).'/../views/img/post/thumb/'.$post->thumb);
                            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_post='.(int)$post->id);
                            if(isset($post_xml->category)&&$post_xml->category)
                            {
                                foreach($post_xml->category as $category_xml)
                                {
                                    $id_category = isset($categories[(int)$category_xml['id_category']])?$categories[(int)$category_xml['id_category']]:0;
                                    if(!Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_category="'.(int)$id_category.'" AND id_post="'.(int)$post->id.'"'))
                                    {
                                        $position = 1+ (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ybc_blog_post_category WHERE id_category='.(int)$id_category);
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_post_category (id_post,id_category,position) values("'.(int)$post->id.'","'.(int)$id_category.'","'.(int)$position.'")');
                                    }
                                }
                            }
                            if(Tools::getValue('importoverride'))
                            {
                                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_tag WHERE id_post='.(int)$post->id);
                            }
                            if(isset($post_xml->tags)&& $post_xml->tags)
                            {
                                foreach($post_xml->tags as $tag_xml)
                                {
                                    if((string)$tag_xml['iso_code'])
                                    {
                                        $id_lang = Language::getIdByIso((string)$tag_xml['iso_code']);
                                        $tag= (string)$tag_xml['tag'];
                                        $click_number= (int)$tag_xml['click_number'];
                                        Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'ybc_blog_tag (id_post,id_lang,tag,click_number) values("'.(int)$post->id.'","'.(int)$id_lang.'","'.pSQL($tag).'","'.(int)$click_number.'")');
                                    }
                                }
                            }
                            if(in_array('posts_comments',$data_imports))
                            {
                                if(Tools::getValue('importoverride'))
                                {
                                    Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_comment WHERE id_post='.(int)$post->id);
                                }
                                if(isset($post_xml->comment) && $post_xml->comment)
                                {
                                    foreach($post_xml->comment as $comment_xml)
                                    {
                                        $comment= new Ybc_blog_comment_class();
                                        if(Tools::getValue('keepcommenter'))                                          
                                            $comment->id_user= (int)$comment_xml['id_user'];
                                        else
                                            $comment->id_user=0;
                                        $comment->name =(string)$comment_xml['name'];
                                        $comment->email =(string)$comment_xml['email'];
                                        $comment->id_post=(int)$post->id;
                                        $comment->subject = (string)$comment_xml['subject'];
                                        $comment->comment = (string)$comment_xml->comment_text;
                                        $comment->reply= (string)$comment_xml->reply;
                                        $comment->replied_by=(int)Context::getContext()->employee->id;
                                        $comment->rating=(int)$comment_xml['rating'];
                                        $comment->approved =(int)$comment_xml['approved'];
                                        $comment->datetime_added =(string)$comment_xml['datetime_added'];
                                        $comment->reported=(int)$comment_xml['reported'];
                                        if($comment->subject && $comment->name && $comment->comment)
                                        {
                                            $comment->save();
                                            if($comment->id && isset($comment_xml->reply) && $comment_xml->reply)
                                            {
                                                foreach($comment_xml->reply as $reply_xml)
                                                {
                                                    $sql= "INSERT INTO "._DB_PREFIX_."ybc_blog_reply(id_comment,id_user,name,email,reply,id_employee,approved,datetime_added,datetime_updated) values('".(int)$comment->id."', '".(int)$reply_xml['id_user']."','".pSQL((string)$reply_xml['name'])."','".pSQL((string)$reply_xml['email'])."','".pSQL((string)$reply_xml->reply_text)."','".(int)$reply_xml['id_employee']."','".(int)$reply_xml['approved']."','".pSQL((string)$reply_xml['datetime_added'])."','".pSQL((string)$reply_xml['datetime_updated'])."')";
                                                    Db::getInstance()->execute($sql);
                                                }
                                            }
                                        }
                                        
                                    }
                                }
                            }
                            if(in_array('posts_polls',$data_imports))
                            {
                                if(Tools::getValue('importoverride'))
                                {
                                    Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_polls WHERE id_post='.(int)$post->id);
                                }
                                if(isset($post_xml->post_polls) && $post_xml->post_polls)
                                {
                                    foreach($post_xml->post_polls as $post_polls_xml)
                                    {
                                        $polls_class= new Ybc_blog_polls_class();
                                        if(Tools::getValue('keepcommenter'))                                          
                                            $polls_class->id_user= (int)$post_polls_xml['id_user'];
                                        else
                                            $polls_class->id_user=0;
                                        $polls_class->name =(string)$post_polls_xml['name'];
                                        $polls_class->email =(string)$post_polls_xml['email'];
                                        $polls_class->id_post=(int)$post->id;
                                        $polls_class->feedback = (string)$post_polls_xml->post_polls_text;
                                        $polls_class->dateadd = (string)$post_polls_xml['dateadd'];
                                        $polls_class->polls =(int)$post_polls_xml['polls'];
                                        $polls_class->save();
                                    }
                                }
                            }
                        }
                    }
                }
                if(in_array('slider_images',$data_imports))
                {
                    if(isset($xml->slide) && $xml->slide)
                    {
                        foreach ($xml->slide as $slide_xml)
                		{
                		    if(Tools::getValue('importoverride') && $this->itemExists('slide','id_slide',(int)$slide_xml['id_slide']))
                                $slide = new Ybc_blog_slide_class((int)$slide_xml['id_slide']);
                            else
                            {
                                $slide = new Ybc_blog_slide_class();
                                $slide->sort_order = 1+ (int)count($this->getSlides());
                            }  
                            $slide->enabled = $slide_xml['enabled'];
                            $slide->image = $slide_xml['image'];
                            $languageSlideImported= array();
                            if($slide_xml->slidelanguage)
                                foreach($slide_xml->slidelanguage as $slidelanguage)
                                {
                                    if((string)$slidelanguage['iso_code'])
                                    {
                                        $id_lang = Language::getIdByIso((string)$slidelanguage['iso_code']);
                                        if(isset($slidelanguage['default']) && $slidelanguage['default'])
                                            $languageSlideDefault=$slidelanguage;
                                        if($id_lang)
                                        {
                                            $slide->caption[$id_lang] = (string)$slidelanguage->caption;
                                            $slide->url[$id_lang] = (string)$slidelanguage->url;
                                            $languageSlideImported[]=$id_lang;
                                        }
                                    }
                                }
                            if(isset($languageSlideDefault))
                            {
                                foreach(Language::getLanguages(false) as $lang)
                                {
                                    if(!in_array($lang['id_lang'],$languageSlideImported))
                                    {
                                        $slide->caption[$lang['id_lang']] = (string)$languageSlideDefault->caption;
                                        $slide->url[$lang['id_lang']] = (string)$languageSlideDefault->url;
                                    }
                                }
                            }
                            $slide->save();
                            if($slide->image && file_exists(dirname(__FILE__).'/../cache/img/slide/'.$slide->image))
                                copy(dirname(__FILE__).'/../cache/img/slide/'.$slide->image,dirname(__FILE__).'/../views/img/slide/'.$slide->image);
              		    }
                    }
                }
                if(in_array('gallery_images',$data_imports))
                {
                    if(isset($xml->gallery)&& $xml->gallery)
                    {
                        foreach($xml->gallery as $gallery_xml)
                        {
                            if(Tools::getValue('importoverride') && $this->itemExists('gallery','id_gallery',(int)$gallery_xml['id_gallery']))
                                $gallery = new Ybc_blog_gallery_class((int)$gallery_xml['id_gallery']);
                            else
                            {
                                $gallery = new Ybc_blog_gallery_class();
                                $gallery->sort_order = 1+ count($this->getGalleries());
                            }    
                            $gallery->enabled = (int)$gallery_xml['enabled'];
                            $gallery->image = (string)$gallery_xml['image'];
                            $gallery->thumb = (string)$gallery_xml['thumb'];
                            $gallery->is_featured = (int)$gallery_xml['is_featured'];
                            $languageGalleryImported=array();
                            if($gallery_xml->gallerylanguage)
                            {
                                foreach($gallery_xml->gallerylanguage as $gallerylanguage)
                                {
                                    if((string)$gallerylanguage['iso_code'])
                                    {
                                        $id_lang = Language::getIdByIso((string)$gallerylanguage['iso_code']);
                                        if(isset($gallerylanguage['default']) && $gallerylanguage['default'])
                                            $languageGalleryDefault = $gallerylanguage;
                                        if($id_lang)
                                        {
                                            $gallery->title[$id_lang] = (string)$gallerylanguage->title;
                                            $gallery->description[$id_lang] =(string)$gallerylanguage->description;
                                            $languageGalleryImported[]=$id_lang;
                                        }
                                    }
                                }
                            }
                            if(isset($languageGalleryDefault))
                            {
                                foreach(Language::getLanguages(false) as $lang)
                                {
                                    if(!in_array($lang['id_lang'],$languageGalleryImported))
                                    {
                                        $gallery->title[$lang['id_lang']] = (string)$gallerylanguage->title;
                                        $gallery->description[$lang['id_lang']] =(string)$gallerylanguage->description;
                                    }
                                }
                            }
                            $gallery->save();
                            if($gallery->image && file_exists(dirname(__FILE__).'/../cache/img/gallery/'.$gallery->image))
                                copy(dirname(__FILE__).'/../cache/img/gallery/'.$gallery->image,dirname(__FILE__).'/../views/img/gallery/'.$gallery->image);
                            if($gallery->thumb && file_exists(dirname(__FILE__).'/../cache/img/gallery/thumb/'.$gallery->thumb))
                                copy(dirname(__FILE__).'/../cache/img/gallery/thumb/'.$gallery->thumb,dirname(__FILE__).'/../views/img/gallery/thumb/'.$gallery->thumb);
                        }
                    }
                }
                if(in_array('module_configuration',$data_imports))
                {
                    if(isset($xml->configuration) && $config_xml = $xml->configuration)
                    {
                        $this->importXmlConfiguration($this->defines->configs,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_seo,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_sitemap,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_homepage,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_categorypage,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_productpage,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_sidebar,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_email,$config_xml);
                        $this->importXmlConfiguration($this->defines->socials,$config_xml);
                        $this->importXmlConfiguration($this->defines->rss,$config_xml);
                        $this->importXmlConfiguration($this->defines->customer_settings,$config_xml);
                        $this->importXmlConfiguration($this->defines->configs_image,$config_xml);
                    }
                }
            }
        }
    }
    public function itemExists($tbl, $primaryKey, $id)
	{
		$req = 'SELECT tbl.'.pSQL($primaryKey).'
				FROM `'._DB_PREFIX_.'ybc_blog_'.pSQL($tbl).'` tbl,`'._DB_PREFIX_.'ybc_blog_'.pSQL($tbl).'_shop` tbls 
				WHERE tbl.`'.pSQL($primaryKey).'` = '.(int)$id.' AND tbls.id_shop='.(int)$this->context->shop->id.' AND tbl.'.pSQL($primaryKey).'=tbls.'.pSQL($primaryKey);
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);        
		return ($row);
	}
    public function getLinkGuidPost($xml,$id_post)
    {
        if(isset($xml->channel) && isset($xml->channel->item) && $xml->channel->item)
        {
            foreach($xml->channel->item as $item)
            {
                if((int)$item->post_id==$id_post)
                {
                    return $item->guid;
                }
            }
        }
        return false;
    }
    public static function copy($source, $destination, $stream_context = null)
    {
        if (is_null($stream_context) && !preg_match('/^https?:\/\//', $source)) {
            return @copy($source, $destination);
        }
        return @file_put_contents($destination, self::file_get_contents($source, false, $stream_context));
    }
    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'cURL Request',
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        }
        elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }
    public function removeForderImgCache($forder)
    {
        $files = glob($forder.'/*'); 
        foreach($files as $file){ 
          if(is_file($file))
            @unlink($file); 
          else
            $this->removeForderImgCache($file);
        }
        rmdir($forder);
    }
}