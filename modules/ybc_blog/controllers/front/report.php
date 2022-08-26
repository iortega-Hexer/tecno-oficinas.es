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
class Ybc_blogReportModuleFrontController extends ModuleFrontController
{
    public function init()
	{ 	  
	     $json = array();
	     $id_comment = (int)Tools::getValue('id_comment');
         $module = new Ybc_blog();
         if(!$module->itemExists('comment','id_comment',$id_comment))
         {
            $json['error'] = $this->module->l('This comment does not exist');
            die(Tools::jsonEncode($json));
         }
         if(!isset($this->context->cookie->id_customer) || isset($this->context->cookie->id_customer) && !$this->context->cookie->id_customer)
         {
            $json['error'] = $this->module->l('Please log in to report this comment');
            die(Tools::jsonEncode($json));
         }
         $context = Context::getContext();
         if($context->customer->logged)
         {
            $allow_report_comment = (int)Configuration::get('YBC_BLOG_ALLOW_REPORT') ? true : false;
         }
         elseif((int)Configuration::get('YBC_BLOG_ALLOW_REPORT') && (int)Configuration::get('YBC_BLOG_ALLOW_GUEST_REPORT'))
            $allow_report_comment=true;
         else
            $allow_report_comment=false;
         if(!$allow_report_comment)
         {
            $json['error'] = $this->module->l('You are not allowed to report this comment');
            die(Tools::jsonEncode($json));
         }
         if(!$context->cookie->reported_comments)
            $reportedComments = array();
         else
            $reportedComments = @unserialize($context->cookie->reported_comments); 
         
         if(is_array($reportedComments) && !in_array($id_comment, $reportedComments))
         {
             $reportedComments[] = $id_comment;
             $context->cookie->reported_comments = @serialize($reportedComments);
             $context->cookie->write();	
             $customer = new Customer((int)$this->context->cookie->id_customer);             
             $comment = new Ybc_blog_comment_class($id_comment);
             $comment->reported = 0;
             $comment->update();             
             $json['success'] = $this->module->l('Successfully reported');
             $this->sendNotification(
                $comment->id,
                $comment->subject,
                $comment->comment,
                $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars') : $this->module->l('star')),
                $module->getLink('blog', array('id_post' => $comment->id_post)),
                trim($customer->firstname.' '.$customer->lastname),
                $customer->email,
                $comment->id_post,
                $comment->name
             );
             if(($subject = Ybc_blog_email_template_class::getSubjectByTemplate('reported_comment_customer')))
             {
                $post = new Ybc_blog_post_class($comment->id_post,$this->context->language->id);
                $template_mail=array(
                    '{reporter}' => $customer->firstname.' '.$customer->lastname, 
                    '{email}' =>$customer->email,
                    '{rating}' => $comment->rating, 
                    '{subject}' => $comment->subject, 
                    '{comment}'=>$comment->comment, 
                    '{post_link}' => $module->getLink('blog', array('id_post' => $comment->id_post)),
                    '{id_comment}' => $comment->id,
                    '{post_title}'=>$post->title,
                    '{customer_name}'=>$comment->name,
                    '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                    '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER'),
                );
                Mail::Send(
                    $this->context->language->id, 
                    'reported_comment_customer',
                    $subject,
                    $template_mail,  
                    $customer->email, null, null, null, null, null, 
                    dirname(__FILE__).'/../../mails/', 
                    false, $this->context->shop->id
                );
             }
             die(Tools::jsonEncode($json));
         }
         $json['error'] = $this->module->l('This comment has been reported');
         die(Tools::jsonEncode($json));
	}
    public function sendNotification($id_comment, $subject, $comment, $rating, $postLink, $reporter, $remail,$id_post,$comment_author)
    {
        $post= new Ybc_blog_post_class($id_post,$this->context->language->id);
        $mailDir = dirname(__FILE__).'/../../mails/';
        $template_mail =array(
            '{reporter}' => $reporter, 
            '{email}' => $remail,
            '{rating}' => $rating, 
            '{subject}' => $subject, 
            '{comment}'=>$comment, 
            '{post_link}' => $postLink,
            '{id_comment}' => $id_comment,
            '{comment_author}'=>$comment_author,
            '{post_title}'=>$post->title,
            '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
            '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
        );
        if($post->is_customer)
        {
            $author= new Customer($post->added_by);
            $link_view_comment= $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'comment','list'=>1));
        }
        else
        {
            $author = new Employee($post->added_by);
            $link_view_comment= $this->module->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER');
        }
        if($author->email)
        {
            $mail_lang_id = $author->id_lang;
            if($subject = Ybc_blog_email_template_class::getSubjectByTemplate('report_comment',$mail_lang_id))
            {
                $template_mail['{author_name}'] = $author->firstname.' '.$author->lastname;
                $template_mail['{link_view_comment}'] = $link_view_comment;
                Mail::Send(
                    $mail_lang_id, 
                    'report_comment', $subject, 
                    $template_mail,  
                    trim($author->email), null, null, null, null, null, 
                    $mailDir, 
                    false, $this->context->shop->id
                );
            }
            
        }
        if(Configuration::get('YBC_BLOG_ALERT_EMAILS'))
        {
            $emails = explode(',',Configuration::get('YBC_BLOG_ALERT_EMAILS'));
            if($emails)
            {
                $link_view_comment= $this->module->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER');
                foreach($emails as $email)
                {
                    if(Validate::isEmail($email))
                    {
                        $employeeObj = new Employee();
                        if(($employee = $employeeObj->getByEmail($email)) && ($lang = new Language($employee->id_lang)) && $lang->active)
                            $mail_lang_id = $lang->id;
                        else
                            $mail_lang_id = Context::getContext()->language->id;
                        if($subject = Ybc_blog_email_template_class::getSubjectByTemplate('report_comment',$mail_lang_id))
                        {
                            $template_mail['{author_name}'] = Configuration::get('PS_SHOP_NAME');
                            $template_mail['{link_view_comment}'] = $link_view_comment;
                            Mail::Send(
                                $mail_lang_id, 
                                'report_comment', 
                                $subject, 
                                $template_mail,  
                                trim($email), null, null, null, null, null, 
                                $mailDir, 
                                false, $this->context->shop->id
                            );
                        }
                    }
                }
            }
        }
    }
}