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
class Ybc_blogManagementblogModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $_errros= array();
    public $_sussecfull;
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
        //Sorry, you do not have permission');
	}
	public function initContent()
	{
	    parent::initContent();
        $this->module->setMetas();
        if ($this->context->customer->isLogged())
		{
            if($this->module->checkGroupAuthor())
            {
                $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$this->context->customer->id.' AND is_customer=1 AND status<=0');
                if($id_employee_post)
                {
                    $this->context->smarty->assign(
                        array(
                            'ok_author' =>false,
                        )
                    );
                    $this->_errros[]= $this->module->l('Your account has been suspended. Please contact webmaster for more information');
                }
                else
                {
                    $this->context->smarty->assign(
                        array(
                            'ok_author' =>true,
                        )
                    );
                }
                
            }
            else
            {
                $this->context->smarty->assign(
                    array(
                        'ok_author'=>false,
                    )
                );
                $this->_errros[] = $this->module->l('Sorry, you do not have permission','managementblog');
            }
        }
        else    
            Tools::redirect('index.php?controller=authentication');
        
        $form_html_post ='';
        if(Tools::isSubmit('submitComment') || Tools::isSubmit('submitCommentStay'))
            $this->_saveComment();
        if(Tools::isSubmit('submitAuthorManagement'))
        {
            if(!$this->module->checkPermistionPost(0,'upload_avatar_information'))
                $this->_errros[] = $this->module->l('Sorry, you do not have permission','managementblog');
            else
                $this->_postAuthor();
        }
        if(Tools::isSubmit('submitPost') || Tools::isSubmit('submitPostStay'))
        {
            $this->_savePost();
        }
        if(Tools::isSubmit('commentapproved') && $id_comment=Tools::getValue('id_comment'))
        {
               if($this->module->checkPermisionComment())
               {
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_comment SET approved="'.(int)Tools::getValue('commentapproved').'" WHERE id_comment='.(int)$id_comment);
                    if(Tools::getValue('commentapproved') && Configuration::get('YBC_BLOG_ENABLE_MAIL_APPROVED'))
                    {
                        $ybc_comment= new Ybc_blog_comment_class($id_comment);
                        $post = new Ybc_blog_post_class($ybc_comment->id_post);
                        Mail::Send(
                            $this->context->language->id, 
                            'approved_comment',
                            Mail::l('Your comment has been approved'),
                            array('{customer_name}' => $ybc_comment->name, '{email}' => $ybc_comment->email,'{rating}' => ' '.($ybc_comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')), '{subject}' => $ybc_comment->subject, '{comment}'=>$ybc_comment->comment,'{post_title}'=>$post->title,'{post_link}' => $this->module->getLink('blog', array('id_post' => $ybc_comment->id_post)),'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                            $ybc_comment->email, null, null, null, null, null, 
                            dirname(__FILE__).'/../../mails/', 
                            false);
                    }
                    Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementblog',array('tabmanagament'=>Tools::getValue('tabmanagament'),'updateComment'=>1)));
               }
               else
                    $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementblog');
        }
        
        if(Tools::isSubmit('deletethumb') && $id_post=Tools::getValue('id_post'))
        {
            if($this->module->checkPermistionPost($id_post,'edit_blog'))
            {
                $thumb = Db::getInstance()->getValue('SELECT thumb FROM '._DB_PREFIX_.'ybc_blog_post WHERE id_post='.(int)$id_post);
                @unlink(dirname(__FILE__).'/../../views/img/post/thumb/'.$thumb);
                Db::getInstance()->getValue('UPDATE '._DB_PREFIX_.'ybc_blog_post set thumb="" WHERE id_post='.(int)$id_post);
                Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementblog',array('tabmanagament'=>'post','deletedthumb'=>1,'editpost'=>1,'id_post'=>$id_post)));
            }
            else
               $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementblog');
        }
        if(Tools::isSubmit('deleteimage') && $id_post=Tools::getValue('id_post'))
        {
            if($this->module->checkPermistionPost($id_post,'edit_blog'))
            {
                $image = Db::getInstance()->getValue('SELECT image FROM '._DB_PREFIX_.'ybc_blog_post WHERE id_post='.(int)$id_post);
                @unlink(dirname(__FILE__).'/../../views/img/post/'.$image);
                Db::getInstance()->getValue('UPDATE '._DB_PREFIX_.'ybc_blog_post set image="" WHERE id_post='.(int)$id_post);
                Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementblog',array('tabmanagament'=>'post','deletedimage'=>1,'editpost'=>1,'id_post'=>$id_post)));
            }
            else
               $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementblog');
        }
        if(Tools::isSubmit('deletepost') && $id_post=Tools::getValue('id_post'))
        {
            if($this->module->checkPermistionPost($id_post,'delete_blog'))
            {
                if($this->module->_deletePost($id_post))
                    Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementblog',array('tabmanagament'=>'post','deletedpost'=>1)));
                else
                    $this->_errros[] = $this->module->l('Delete failed','managementblog');
            }
            else
               $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementblog');
        }
        if(Tools::isSubmit('deletecomment') && $id_comment=(int)Tools::getValue('id_comment'))
        {
            if($this->module->checkPermisionComment('delete'))
            {
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_comment WHERE id_comment="'.(int)$id_comment.'"');
                Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementblog',array('tabmanagament'=>Tools::getValue('tabmanagament'),'deletedcomment'=>1)));
            }
            else
               $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementblog');
        }
        if(Tools::isSubmit('deletedpost'))
            $this->_sussecfull = $this->module->l('You have just deleted the blog post successfully','managementblog');    
        if(Tools::isSubmit('deletedcomment'))
            $this->_sussecfull = $this->module->l('You have just deleted the comment successfully','managementblog');
        if(Tools::isSubmit('added'))
        {
            if(Configuration::get('YBC_BLOG_STATUS_POST')=='waiting_approval')
                $this->_sussecfull = $this->module->l('Your new blog post has just been added successfully. It is waiting to be approved by Administrator','managementblog');
            else
                $this->_sussecfull = $this->module->l('Your new blog post has just been added successfully','managementblog');
        }
        if(Tools::isSubmit('updated'))
        {
            $this->_sussecfull = $this->module->l('Updated successfully','managementblog');
        }
        if(Tools::isSubmit('addedReply'))
            $this->_sussecfull = $this->module->l('Reply has been submitted','managementblog');
        if(Tools::isSubmit('updateComment'))
            $this->_sussecfull = $this->module->l('Comment updated','managementblog');
        if(Tools::isSubmit('updatedReply'))
            $this->_sussecfull = $this->module->l('Reply updated','managementblog');
        if(Tools::isSubmit('updatedComment'))
            $this->_sussecfull = $this->module->l('Comment updated','managementblog');
        if(Tools::isSubmit('deleteddReply'))
            $this->_sussecfull = $this->module->l('Delete reply successfully','managementblog');
        if(Tools::isSubmit('deletedthumb'))
            $this->_sussecfull = $this->module->l('Delete thumbnail image successfully','managementblog');
        if(Tools::isSubmit('deletedimage'))
            $this->_sussecfull = $this->module->l('Delete image successfully','managementblog');
        $this->context->smarty->assign(
            array(
                'errors_html'=>$this->_errros ? $this->module->displayError($this->_errros) : false,
                'form_html_post'=>$form_html_post,
                'sucsecfull_html' => $this->_sussecfull ? $this->module->displaySuccessMessage($this->_sussecfull):'',
                'breadcrumb' => $this->module->is17 ? $this->getBreadCrumb() : false, 
                'path' => $this->getBreadCrumb(),
            )
        );
        if($this->module->is17)
            $this->setTemplate('module:ybc_blog/views/templates/front/management_blog.tpl');      
        else         
            $this->setTemplate('management_blog16.tpl');  
    }
    public function uploadFile($file)
    {
        $width_image='';
        $height_image='';
        if($file=='thumb')
        {
            $dir_img = dirname(__FILE__).'/../../views/img/post/thumb/';
            $width_image =Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH',null,null,null,260);
            $height_image =Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT',null,null,null,180);        
        }
        elseif($file=='image')
        {
            $width_image =Configuration::get('YBC_BLOG_IMAGE_BLOG_WIDTH',null,null,null,1920);
            $height_image =Configuration::get('YBC_BLOG_IMAGE_BLOG_HEIGHT',null,null,null,750);
            $dir_img = dirname(__FILE__).'/../../views/img/post/';   
        }
        elseif($file=='avata')
        {
            $dir_img = dirname(__FILE__).'/../../views/img/avata/';
            $width_image =Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH',null,null,null,300);
            $height_image =Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT',null,null,null,300);
        }
            
        if(file_exists($dir_img.$_FILES[$file]['name']))
        {
            $_FILES[$file]['name'] = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES[$file]['name'];
        }                
        $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$file]['name'], '.'), 1));
		$thumbsize = @getimagesize($_FILES[$file]['tmp_name']);
		if (isset($_FILES[$file]) &&				
			!empty($_FILES[$file]['tmp_name']) &&
			!empty($thumbsize) &&
			in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
		)
		{
			$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
			if ($error = ImageManager::validateUpload($_FILES[$file]))
				$this->_errros[] = $error;
			elseif (!$temp_name || !move_uploaded_file($_FILES[$file]['tmp_name'], $temp_name))
				$this->_errros[] = $this->l('Can not upload the file');
			elseif (!ImageManager::resize($temp_name, $dir_img.$_FILES[$file]['name'], $width_image, $height_image, $type))
				$this->_errros[] = $this->module->displayError($this->l('An error occurred during the thumbnail upload process.'));
			if (isset($temp_name))
				@unlink($temp_name);
             ;
            return $_FILES[$file]['name'];			
		}
        elseif(isset($_FILES[$file]) &&!in_array($type, array('jpg', 'gif', 'jpeg', 'png')))
            $this->_errros[] = $this->l('Image file is invalid');
        return false;
    }
    public function _saveComment()
    {
        if($this->module->checkPermisionComment())
        {
            $ybc_comment= new Ybc_blog_comment_class(Tools::getValue('id_comment'));
            if(!Tools::getValue('subject'))
                $this->_errros[]= $this->module->l('Subject is required','managementblog');
            else
                $ybc_comment->subject=Tools::getValue('subject');
            if(!Tools::getValue('comment'))
                $this->_errros[] = $this->module->l('Comment is required','managementblog');
            elseif(Tools::strlen(Tools::getValue('comment'))<20)
                $this->_errros[]=$this->module->l('Comment needs to be at least 20 characters','managementblog');
            else
                $ybc_comment->comment = Tools::getValue('comment');
            if(Tools::isSubmit('reply'))
            {
                $ybc_comment->reply = Tools::getValue('reply');
                if(Tools::getValue('reply'))
                {
                    $ybc_comment->replied_by= $this->context->customer->id;
                    $ybc_comment->customer_reply=1;
                }
                else
                    $ybc_comment->customer_reply=0;
            }
            $apdate_approved=false;
            if(Tools::isSubmit('approved'))
            {
                if($ybc_comment->approved!=Tools::getValue('approved') && Tools::getValue('approved')==1)
                    $apdate_approved=true;
                $ybc_comment->approved =Tools::getValue('approved');
            }
            if(!$this->_errros)
            {
                $ybc_comment->update();
                $post = new Ybc_blog_post_class($ybc_comment->id_post,$this->context->language->id);
                if($apdate_approved && Configuration::get('YBC_BLOG_ENABLE_MAIL_APPROVED'))
                {
                       Mail::Send(
                            $this->context->language->id, 
                            'approved_comment',
                            Mail::l('Your comment has been approved'),
                            array('{customer_name}' => $ybc_comment->name, '{email}' => $ybc_comment->email,'{rating}' => ' '.($ybc_comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')), '{subject}' => $ybc_comment->subject, '{comment}'=>$ybc_comment->comment,'{post_title}'=>$post->title,'{post_link}' => $this->module->getLink('blog', array('id_post' => $ybc_comment->id_post)),'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                            $ybc_comment->email, null, null, null, null, null, 
                            dirname(__FILE__).'/../../mails/', 
                            false
                            );
                }
                if(Tools::isSubmit('submitComment'))
                    Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementblog',array('tabmanagament'=>Tools::getValue('tabmanagament'),'updateComment'=>1)));
                else
                    $this->_sussecfull = $this->l('Comment updated');
            }   
        }
        else
            $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementblog');
    }
    public function _savePost()
    {
        $categories = Tools::getValue('blog_categories');
        $disabled_categories = $this->module->getCategoriesDisabled();
        $category_vaid=true;
        if($categories && $disabled_categories)
        {
            foreach($categories as $category)
                if(in_array($category,$disabled_categories))
                    $category_vaid=false;
        }
        $id_lang= $this->context->language->id;
        $languages = Language::getLanguages(false);
        if($id_post=Tools::getValue('id_post'))
        {
            if($this->module->checkPermistionPost($id_post,'edit_blog'))
            {
                $ybc_post= new Ybc_blog_post_class($id_post);
                if(trim(Tools::getValue('title')))
                {
                    $ybc_post->title[$id_lang] =trim(Tools::getValue('title'));
                    $ybc_post->url_alias[$id_lang] = Tools::link_rewrite(Tools::getValue('title'));
                    if(str_replace(array('0','1','2','3','4','5','6','7','8','9'),'',Tools::substr($ybc_post->title[$id_lang],0,1))=='')
                        $this->_errros[] = $this->module->l('Post title cannot have number on the start position because it will cause error when you enable "Remove post ID" option');
                    if($ybc_post->url_alias[$id_lang] && Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl,'._DB_PREFIX_.'ybc_blog_post_shop ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.url_alias ="'.pSQL($ybc_post->url_alias[$id_lang]).'" AND ps.id_post!="'.(int)$ybc_post->id.'"'))
                    {
                        $ybc_post->url_alias[$id_lang] = $ybc_post->url_alias[$id_lang].'-'.$ybc_post->id;
                    }
                }
                else
                    $this->_errros[] = $this->module->l('Title is required','managementblog');
                if(!$categories || !is_array($categories))
                    $this->_errros [] = $this->l('You need to choose at least 1 category');
                elseif(!$category_vaid)
                     $this->_errros [] = $this->l('Categories are not valid');
                if(Tools::getValue('short_description'))
                {
                    if(Validate::isCleanHtml(Tools::getValue('short_description')))
                        $ybc_post->short_description[$id_lang] = Tools::getValue('short_description');
                    else
                        $this->_errros[] = $this->module->l('Short description is not valid','managementblog');
                }
                else
                    $this->_errros[] = $this->module->l('Short description is required','managementblog'); 
                if(Tools::getValue('description'))
                    if(!Validate::isCleanHtml(Tools::getValue(Tools::getValue('description'))))
                          $this->_errros[] = $this->module->l('Post content is not valid','managementblog');
                    else
                        $ybc_post->description[$id_lang] = Tools::getValue('description');
                else
                    $this->_errros[] = $this->module->l('Post content is required','managementblog'); 
                $ybc_post->datetime_modified=date('Y-m-d H:i:s');
                if($_FILES['thumb']['name'])
                {
                    $oldthumb= $ybc_post->thumb; 
                    $ybc_post->thumb= $this->uploadFile('thumb');
                }
                elseif(!$ybc_post->id)
                    $this->_errros[]= $this->module->l('Post thumbnail is required','managementblog');    
                if($_FILES['image']['name'])
                {
                    $oldimage= $ybc_post->image;
                    $ybc_post->image= $this->uploadFile('image');
                }
            }
            else
                $this->_errros[] = $this->module->l('Sorry, you do not have permission','managementblog');
        }
        else
        {
            if($this->module->checkPermistionPost(0,'add_new'))
            {
                $ybc_post= new Ybc_blog_post_class();
                if(trim(Tools::getValue('title')))
                {
                    if(str_replace(array('0','1','2','3','4','5','6','7','8','9'),'',Tools::substr(Tools::getValue('title'),0,1))=='')
                        $this->_errros[] = $this->module->l('Post title cannot have number on the start position because it will cause error when you enable "Remove post ID" option');
                    else
                    {
                        foreach($languages as $language)
                        {
                            $ybc_post->title[$language['id_lang']]= trim(Tools::getValue('title'));
                            $ybc_post->url_alias[$language['id_lang']] = Tools::link_rewrite(Tools::getValue('title'));
                            if($ybc_post->url_alias[$language['id_lang']] && Db::getInstance()->getValue('SELECT ps.id_post FROM '._DB_PREFIX_.'ybc_blog_post_lang pl,'._DB_PREFIX_.'ybc_blog_post_shop ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.url_alias ="'.pSQL($ybc_post->url_alias[$language['id_lang']]).'" AND ps.id_post!="'.(int)$ybc_post->id.'"'))
                            {
                                $maxId = 1+(int)Db::getInstance()->getValue('SELECT MAX(id_post) FROM '._DB_PREFIX_.'ybc_blog_post');
                                $ybc_post->url_alias[$language['id_lang']] = $ybc_post->url_alias[$language['id_lang']].'-'.$maxId;
                            }
                        }
                    }
                }
                else
                    $this->_errros[] = $this->module->l('Title is required','managementblog');
                if(!$categories || !is_array($categories))
                    $this->_errros [] = $this->l('You need to choose at least 1 category');
                elseif(!$category_vaid)
                     $this->_errros [] = $this->l('Categories are not valid');
                if(Tools::getValue('short_description'))
                {
                    if(Validate::isCleanHtml(Tools::getValue('short_description')))
                    {
                        foreach($languages as $language)
                            $ybc_post->short_description[$language['id_lang']] = Tools::getValue('short_description');
                    }
                    else
                        $this->_errros[] = $this->module->l('Short description is not valid','managementblog');
                }
                else
                    $this->_errros[] = $this->module->l('Short description is required','managementblog'); 
               if(Tools::getValue('description'))
               {
                    if( !Validate::isCleanHtml(Tools::getValue(Tools::getValue('description'))))
                          $this->_errros[] = $this->module->l('Post content is not valid','managementblog');
                    else
                    {
                        foreach($languages as $language)
                            $ybc_post->description[$language['id_lang']] = Tools::getValue('description');
                    }
               }
               else
                    $this->module->l('Post content is required','managementblog'); 
               $ybc_post->datetime_modified=date('Y-m-d H:i:s');
               $ybc_post->datetime_added=date('Y-m-d H:i:s');
               $ybc_post->sort_order =1+ (int)Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'ybc_blog_post_shop WHERE id_shop='.(int)$this->context->shop->id);
                if($_FILES['thumb']['name'])
                {
                    $ybc_post->thumb= $this->uploadFile('thumb');
                }
                elseif(!$ybc_post->id)
                    $this->_errros[]= $this->module->l('Post thumbnail is required','managementblog');      
                if($_FILES['image']['name'])
                {
                    $ybc_post->image= $this->uploadFile('image');
                }
                $ybc_post->added_by = $this->context->customer->id;
                $ybc_post->is_customer = 1;
                if(Configuration::get('YBC_BLOG_STATUS_POST')=='active')
                    $ybc_post ->enabled=1;
                else
                    $ybc_post ->enabled=-1;
            }
            else
                $this->_errros[] = $this->module->l('Sorry, you do not have permission','managementblog');
            
        }
        if(!$this->_errros)
        {
            if($ybc_post->id)
            {
                if($ybc_post->update())
                {
                    if(isset($oldimage) && $oldimage && file_exists(dirname(__FILE__).'/../../views/img/post/thumb/'.$oldimage))
                        @unlink(dirname(__FILE__).'/../../views/img/post/thumb/'.$oldimage);
                    if(isset($oldthumb) && $oldthumb  && file_exists(dirname(__FILE__).'/../../views/img/post/thumb/'.$oldthumb))
                        @unlink(dirname(__FILE__).'/../../views/img/post/thumb/'.$oldthumb);
                    $this->module->updateCategories($categories, $ybc_post->id);
                    if(Tools::isSubmit('submitPostStay'))
                        Tools::redirect($this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','updated'=>1,'editpost'=>1,'id_post'=>$ybc_post->id)));
                    else
                        Tools::redirect($this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','updated'=>1)));
                }
                else
                    $this->_errros[]= $this->module->l('Update failed','managementblog');
            }
            else
            {
                if($ybc_post->add())
                {
                    $this->module->updateCategories($categories, $ybc_post->id);
                    if(Configuration::get('YBC_BLOG_ADMIN_EMAIL_NEW_POST') && $emails= explode(',',Configuration::get('YBC_BLOG_ALERT_EMAILS')))
                    {
                        $template_admin_vars=array(
                            '{customer_name}' => $this->context->customer->firstname .' '.$this->context->customer->lastname,
                            '{post_title}' => $ybc_post->title[$this->context->language->id],
                            '{post_link}'=> $this->module->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER'),
                            '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                            '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                        );
                        foreach($emails as $email)
                        {
                            if(Validate::isEmail($email))
                            {
                                Mail::Send(
                    			Context::getContext()->language->id,
                    			'new_blog_admin'.($ybc_post ->enabled==1 ? '_1':'_0'),
                    			$this->module->l('Community author submitted a new post','managementblog'),
                    			$template_admin_vars,
            			        $email,
                    			Configuration::get('PS_SHOP_NAME'),
                    			null,
                    			null,
                    			null,
                    			null,
                    			dirname(__FILE__).'/../../mails/'
                            );
                            }
                        }
                    }
                    if(Configuration::get('YBC_BLOG_CUSTOMER_EMAIL_NEW_POST'))
                    {
                        $template_customer_vars=array(
                            '{customer_name}' => $this->context->customer->firstname .' '.$this->context->customer->lastname,
                            '{post_title}' => $ybc_post->title[$this->context->language->id],
                            '{post_link}'=> $this->module->getLink('blog',array('id_post'=>$ybc_post->id)),
                            '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                            '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                        );
                        Mail::Send(
                			Context::getContext()->language->id,
                			'new_blog_customer'.($ybc_post->enabled==1 ? '_1':'_0'),
                			$this->module->l('Your post has been submitted','managementblog'),
                			$template_customer_vars,
        			        $this->context->customer->email,
                			$this->context->customer->firstname .' '.$this->context->customer->lastname,
                			null,
                			null,
                			null,
                			null,
                			dirname(__FILE__).'/../../mails/'
                        );
                    }
                    if(Tools::isSubmit('submitPostStay'))
                        Tools::redirect($this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','added'=>1,'editpost'=>1,'id_post'=>$ybc_post->id)));
                    else
                        Tools::redirect($this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','added'=>1)));
                }
                else
                    $this->_errros[]= $this->module->l('Adding blog post failed','managementblog');
            }
        }
    }
    public function _postAuthor()
    {
        if(Tools::isSubmit('delemployeeimage'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$this->context->custoemr->id.' AND is_customer="1"');
            $employeePost = new Ybc_blog_post_employee_class($id_employee_post);
            @unlink(dirname(__FILE__).'/../views/img/avata/'.$employeePost->avata);
            $employeePost->avata='';
            $employeePost->update();           
            $this->_sussecfull = $this->module->l('Delete image successfully','managementblog');   
        }
        if(Tools::isSubmit('submitAuthorManagement'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM '._DB_PREFIX_.'ybc_blog_employee WHERE id_employee='.(int)$this->context->customer->id);
            if($id_employee_post)
            {
                $employeePost= new Ybc_blog_post_employee_class($id_employee_post);
            }
            else
                $employeePost = new Ybc_blog_post_employee_class();
            $employeePost->id_employee=$this->context->customer->id;
            $employeePost->is_customer=1;
            if(!Tools::getValue('author_name'))
            {
                $this->_errros[]=$this->l('Name is required');
            }
            else
                $employeePost->name=Tools::getValue('author_name');
            if($id_employee_post)
            {
                $employeePost->description[$this->context->language->id]= Tools::getValue('author_description');
            }
            else
            {
               $languages= Language::getLanguages(false);
                foreach($languages as $language)
                {
                    $employeePost->description[$language['id_lang']]= Tools::getValue('author_description');
                } 
            }
            $oldImage = false;
            if(isset($_FILES['author_avata']['tmp_name']) && isset($_FILES['author_avata']['name']) && $_FILES['author_avata']['name'])
            {
                if(file_exists(dirname(__FILE__).'/../../views/img/avata/'.$_FILES['author_avata']['name']))
                {
                    $file_name = Tools::substr(sha1(microtime()),0,10).'-'.$_FILES['author_avata']['name'];
                } 
                else
                   $file_name = $_FILES['author_avata']['name'];                
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['author_avata']['name'], '.'), 1));
    			$imagesize = @getimagesize($_FILES['author_avata']['tmp_name']);
    			if (isset($_FILES['author_avata']) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
    			)
    			{
    				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');    				
    				if ($error = ImageManager::validateUpload($_FILES['author_avata']))
    					$this->_errros[] = $error;
    				elseif (!$temp_name || !move_uploaded_file($_FILES['author_avata']['tmp_name'], $temp_name))
    					$this->_errros[] = $this->l('Can not upload the file');
    				elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/../../views/img/avata/'.$file_name, null, null, $type))
    					$this->_errros[] = $this->module->displayError($this->l('An error occurred during the image upload process.'));
    				if (isset($temp_name))
    					@unlink($temp_name);
                    if($employeePost->avata)
                        $oldImage = dirname(__FILE__).'/../../views/img/avata/'.$employeePost->avata;
                    $employeePost->avata = $file_name;			
    			}
                elseif(isset($_FILES['author_avata']) &&				
    				!empty($_FILES['author_avata']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png')
    			))
                    $this->_errros[] = $this->l('Avatar is invalid'); 
                              
            }
            if(!$this->_errros)
            {
                if($id_employee_post)
                {
                    if(!$employeePost->update())
                        $this->_errros[] = $this->module->displayError($this->module->l('The author could not be updated.','managementblog'));
                    else
                        $this->_sussecfull = $this->module->l('Information updated','managementblog');
                }
                else
                    if(!$employeePost->add())
                        $this->_errros[] = $this->module->displayError($this->l('The author could not be updated.'));
                    else
                        $this->_sussecfull = $this->module->l('Information updated');
                        
            }
            if (!count($this->_errros) && $oldImage)
                @unlink($oldImage);
        }
    }
    public function getBreadCrumb()
    {
        $nodes=array();
        $nodes[] = array(
            'title' => $this->module->l('Home','managementblog'),
            'url' => $this->context->link->getPageLink('index', true),
        );
        $nodes[] = array(
            'title' => $this->module->l('Your account','managementblog'),
            'url' => $this->context->link->getPageLink('my-account'),
        );
        $nodes[] = array(
            'title' => $this->module->l('My blog posts','managementblog'),
            'url' => $this->context->link->getModuleLink('ybc_blog','managementblog'),
        );
        if($this->module->is17)
                return array('links' => $nodes,'count' => count($nodes));
        return $this->module->displayBreadcrumb($nodes);
    }
}