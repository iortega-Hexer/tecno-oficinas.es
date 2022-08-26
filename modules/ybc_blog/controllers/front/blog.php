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
class Ybc_blogBlogModuleFrontController extends ModuleFrontController
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
        $id_post = (int)Tools::getValue('id_post');
        
        if(!$id_post && ($post_url_alias = Tools::getValue('post_url_alias')) && Validate::isLinkRewrite($post_url_alias))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM `'._DB_PREFIX_.'ybc_blog_post_lang` pl ,`'._DB_PREFIX_.'ybc_blog_post_shop` ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.id_lang="'.(int)$this->context->language->id.'" AND pl.url_alias ="'.pSQL($post_url_alias).'"');
        }
        if(Tools::isSubmit('polls_submit') && $id_post)
        {
            $errors=array(); 
            $post_class = new Ybc_blog_post_class($id_post,$this->context->language->id);
            if(!$ybc_blog_polls = $this->getPollsCurrent($id_post))
                $ybc_blog_polls = new Ybc_blog_polls_class();
            if($this->context->customer->logged)
            {
                $ybc_blog_polls->id_user=$this->context->customer->id;
                $ybc_blog_polls->name =$this->context->customer->firstname. ' '.$this->context->customer->lastname;
                $ybc_blog_polls->email = $this->context->customer->email;
            }
            else
            {
                $ybc_blog_polls->id_user=0;
                if(($polls_name = trim(Tools::getValue('polls_name')))=='')
                    $errors[] = $this->module->l('Name is required','blog');
                elseif(!Validate::isName($polls_name))
                    $errors[] = $this->module->l('Name is not valid','blog');
                else
                    $ybc_blog_polls->name = $polls_name;
                if(($polls_email = trim(Tools::getValue('polls_email')))=='')
                    $errors[]=$this->module->l('Email is required','blog');
                elseif(!Validate::isEmail($polls_email))
                    $errors[]=$this->module->l('Email is invalid','blog');
                else
                    $ybc_blog_polls->email= $polls_email;
            }
            $polls_feedback = Tools::getValue('polls_feedback');
            $YBC_BLOG_POLLS_TEXT_MAXIMUM = (int)Configuration::get('YBC_BLOG_POLLS_TEXT_MAXIMUM') ? : 500;
            if(Configuration::get('YBC_BLOG_POLLS_FEEDBACK_NEED') && trim($polls_feedback)=='')
               $errors[] = $this->module->l('Feedback is required','blog');
            elseif(trim($polls_feedback) && Tools::strlen(trim($polls_feedback))<20) 
                $errors[] = $this->module->l('Feedback needs to be at least 20 characters','blog');
            elseif(trim($polls_feedback) && Tools::strlen(trim($polls_feedback)) > $YBC_BLOG_POLLS_TEXT_MAXIMUM)
                $errors[] = sprintf($this->module->l('Feedback cannot be longer than %s characters','blog'),$YBC_BLOG_POLLS_TEXT_MAXIMUM);
            if(!Validate::isCleanHtml($polls_feedback,false))
                $errors[] = $this->module->l('Feedback needs to be clean HTML','blog');
            else
                $ybc_blog_polls->feedback = $polls_feedback;
            if(Configuration::get('YBC_BLOG_ENABLE_POLLS_CAPCHA'))
            {
                if(Configuration::get('YBC_BLOG_CAPTCHA_TYPE')=='google' || Configuration::get('YBC_BLOG_CAPTCHA_TYPE')=='google3')
                {
                    $g_recaptcha = Tools::getValue('g-recaptcha-response');
                    if(!$g_recaptcha)
                    {
                        $errors[] = $this->module->l('reCAPTCHA is invalid','blog');
                    }
                    else
                    {
                        $recaptcha = $g_recaptcha ?  : false;
                        if ($recaptcha) {
                            $response = json_decode(Tools::file_get_contents($this->module->link_capcha), true);
                            if ($response['success'] == false) {
                                $errors[] = $this->module->l('reCAPTCHA is invalid');
                            }
                        }
                    }
                }
                else
                {
                    $security_polls_capcha_code = call_user_func('ets_getCookie','security_polls_capcha_code');
                    $polls_capcha_code = Tools::getValue('polls_capcha_code');
                    if(trim($polls_capcha_code)=='')
                        $errors[] = $this->module->l('Captcha is required','blog');
                    elseif(trim($polls_capcha_code)!=$security_polls_capcha_code)
                        $errors[]=$this->module->l('Captcha is invalid','blog');
                }
                
            }
            if(!$errors)
            {
                $ybc_blog_polls->id_post = (int)$id_post;
                $ybc_blog_polls->polls =(int)Tools::getValue('polls_post');
                if($ybc_blog_polls->save())
                {
                    Hook::exec('actionUpdateBlog', array(
                        'id_post' =>(int)$id_post,
                    ));
                    if(!$this->context->customer->logged)
                    {
                        if($this->context->cookie->id_post_polls)
                        {
                            $id_post_polls= Tools::jsonDecode($this->context->cookie->id_post_polls,true); 
                        }
                        else
                            $id_post_polls=array();
                        if(!isset($id_post_polls[$id_post]))
                        {
                            $id_post_polls[$id_post]=$ybc_blog_polls->id;
                            $this->context->cookie->id_post_polls = Tools::jsonEncode($id_post_polls);
                            $this->context->cookie->write();
                        }
                        
                    }
                    $this->sendMailAdminVoteNew($ybc_blog_polls,$post_class);
                    die(
                        Tools::jsonEncode(
                            array(
                                'sussec' => $this->module->displaySuccessMessage($this->module->l('You have submitted your feedback successfully. Thank you!','blog')),
                                'polls_post_helpful_no' => $this->module->countPollsWithFilter(' AND po.polls=0 AND p.id_post='.(int)$id_post),
                                'polls_post_helpful_yes' => $this->module->countPollsWithFilter(' AND po.polls=1 AND p.id_post='.(int)$id_post),
                            )
                        )
                    );
                    
                }
                else
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'error' => $this->module->displayError($this->module->l('Feedback submitting failed','blog')),
                            )
                        )
                    );
                }
            }
            else
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'error' => $this->module->displayError($errors),
                        )
                    )
                );
            }
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
        $id_post = (int)Tools::getValue('id_post');
        if(!$id_post && ($post_url_alias =  Tools::getValue('post_url_alias')) && Validate::isLinkRewrite($post_url_alias))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM `'._DB_PREFIX_.'ybc_blog_post_lang` pl ,`'._DB_PREFIX_.'ybc_blog_post_shop` ps  WHERE ps.id_shop="'.(int)$this->context->shop->id.'" AND ps.id_post=pl.id_post AND pl.url_alias ="'.pSQL($post_url_alias).'"');
            if($id_post && $this->module->friendly && !Configuration::get('YBC_BLOG_URL_NO_ID'))
            {
                $this->module->redirect($this->module->getLink('blog',array('id_post'=>$id_post)));
            }
            if(!$id_post)
            {
                header("HTTP/1.0 404 Not Found");  
                $this->context->smarty->assign(
                    array(
                        'no_post' => true,
                    )
                );           
            }    
        }
        elseif($id_post && Configuration::get('YBC_BLOG_URL_NO_ID') && !Tools::isSubmit('edit_comment') && !Tools::isSubmit('all_comment') && $this->module->friendly)
        {
            
            $this->module->redirect($this->module->getLink('blog',array('id_post'=>$id_post)));
        }
        if($id_post && $this->module->friendly && (Tools::strpos($_SERVER['REQUEST_URI'],'post_url_alias') !==false || Tools::strpos($_SERVER['REQUEST_URI'],'url_alias')!==false))
        {
             $this->module->redirect($this->module->getLink('blog',array('id_post'=>$id_post)));
        }
        $context = Context::getContext();
        $post_url_alias = Tools::getValue('post_url_alias');
        if($id_post || ($post_url_alias && Validate::isLinkRewrite($post_url_alias)) )
        {
            if(!$id_post && $post_url_alias)
            {
                $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM `'._DB_PREFIX_.'ybc_blog_post_lang` pl ,`'._DB_PREFIX_.'ybc_blog_post_shop` ps WHERE ps.id_post= pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.id_lang="'.(int)$this->context->language->id.'" AND pl.url_alias ="'.pSQL($post_url_alias).'"');
            }
            if($module->itemExists('post','id_post',$id_post))
            {
                
                $ip = Tools::getRemoteAddr();
                $browser= $this->module->getDevice();
                if(Tools::strpos($browser,'unknown')!==false)
                    $browser= $this->module->l('Unknown','blog');
                if(!Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ybc_blog_log_view` WHERE '.($this->context->customer->id ? 'id_customer="'.(int)$this->context->customer->id.'"' :'ip="'.pSQL($ip).'" AND id_customer=0').' AND DAY(datetime_added) ="'.pSQL(date('d')).'" AND MONTH(datetime_added) ="'.pSQL(date('m')).'" AND YEAR(datetime_added) ="'.pSQL(date('Y')).'" AND id_post='.(int)$id_post))
                {
                    Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ybc_blog_log_view`(ip,id_post,browser,id_customer,datetime_added) VALUES ("'.pSQL($ip).'","'.(int)$id_post.'","'.pSQL($browser).'","'.(int)Context::getContext()->customer->id.'","'.pSQL(date('Y-m-d H:i:s')).'")');
                    Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_post` SET click_number= click_number+1 WHERE id_post='.(int)$id_post);
                }
                
                $post = $this->getPost((int)$id_post);                                      
                $errors = array();
                $success = false;
                $success_reply = false;
                if(Tools::isSubmit('bcsubmit') && (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT'))
                {
                    $justAdded=false;
                    if(($id_comment = (int)Tools::getValue('id_comment')) && $module->itemExists('comment','id_comment',$id_comment))
                    {
                        $comment = new Ybc_blog_comment_class($id_comment);
                        if(!$this->module->checkPermisionComment('edit',$id_comment))
                            $errors[] = $this->module->l('Sorry, you do not have permission');
                    }       
                    else
                    {
                        $comment = new Ybc_blog_comment_class();
                        if($post['is_customer'] && $post['added_by']== $this->context->customer->id)
                            $comment->approved=1;
                        else
                            $comment->approved = (int)Configuration::get('YBC_BLOG_COMMENT_AUTO_APPROVED') ? 1 : 0;
                    }
                    $comment->subject = trim(Tools::getValue('subject'));
                    $comment_old = $comment->comment;
                    $comment->comment = trim(Tools::getValue('comment'));
                    $comment->id_post = (int)$id_post;
                    $comment->datetime_added = date('Y-m-d H:i:s');
                    $comment->viewed=0;
                    $name_customer = Tools::getValue('name_customer');
                    $email_customer = Tools::getValue('email_customer');
                    if((int)$this->context->cookie->id_customer)
                    {
                        $comment->id_user = (int)$this->context->cookie->id_customer;
                        $comment->name = $this->context->customer->firstname.' '.$this->context->customer->lastname;
                        $comment->email = $this->context->customer->email;
                    }
                    else
                    {
                       $comment->name = $name_customer;
                       $comment->email = $email_customer; 
                    }
                    $comment->rating = (int)Tools::getValue('rating');
                    $comment->reported = 1;
                    if(!$this->context->cookie->id_customer)
                    {
                        if(!$name_customer)
                            $errors[] = $this->module->l('Name is required','blog');
                        elseif($name_customer && !Validate::isCleanHtml($name_customer))
                        {
                            $errors[] = $this->module->l('Name is required','blog');
                        }
                        if($email_customer && !Validate::isEmail($email_customer))
                        {
                            $errors[] = $this->module->l('Invalid email address','blog');
                        }
                    }
                    if(Tools::strlen($comment->subject) < 10)
                        $errors[] = $this->module->l('Subject needs to be at least 10 characters','blog');
                    if(Tools::strlen($comment->subject) >300)
                        $errors[] = $this->module->l('Subject cannot be longer than 300 characters','blog');
                    if(!Validate::isCleanHtml($comment->subject,false))
                        $errors[] = $this->module->l('Subject needs to be clean HTML','blog');
                    if(Tools::strlen($comment->comment) < 20)
                        $errors[] = $this->module->l('Comment needs to be at least 20 characters','blog');
                    if(!Validate::isCleanHtml($comment->comment,false))
                        $errors[] = $this->module->l('Comment needs to be clean HTML','blog');
                    if(Tools::strlen($comment->comment) >2000)
                        $errors[] = $this->module->l('Subject cannot be longer than 2000 characters','blog');
                    if(!$comment->id_user && !(int)Configuration::get('YBC_BLOG_ALLOW_GUEST_COMMENT'))
                        $errors[] = $this->module->l('You need to log in before posting a comment','blog');
                    if((int)Configuration::get('YBC_BLOG_ALLOW_RATING'))
                    {
                        if($comment->rating > 5 || $comment->rating < 1)
                            $errors[] = $this->module->l('Rating needs to be from 1 to 5','blog');
                    }
                    else
                        $comment->rating = 0;                
                    if(!$module->itemExists('post','id_post',$comment->id_post))
                        $errors[] = $this->module->l('This blog post does not exist','blog');
                    if((int)Configuration::get('YBC_BLOG_USE_CAPCHA'))
                    {                    
                        if(Configuration::get('YBC_BLOG_CAPTCHA_TYPE')=='google' || Configuration::get('YBC_BLOG_CAPTCHA_TYPE')=='google3')
                        {
                            $g_recaptcha = Tools::getValue('g-recaptcha-response');
                            if(!$g_recaptcha)
                            {
                                $errors[] = $this->module->l('reCAPTCHA is invalid','blog');
                            }
                            else
                            {
                                $recaptcha = $g_recaptcha ? $g_recaptcha : false;
                                if ($recaptcha) {
                                    $response = json_decode(Tools::file_get_contents($this->module->link_capcha), true);
                                    if ($response['success'] == false) {
                                        $errors[] = $this->module->l('reCAPTCHA is invalid');
                                    }
                                }
                            }
                        }
                        else
                        {
                            $savedCode = call_user_func('ets_getCookie','ybc_security_capcha_code');
                            $capcha_code = trim(Tools::getValue('capcha_code'));   
                            if(!$capcha_code)
                                $errors[] = $this->module->l('Security code is required','blog');                  
                            elseif($savedCode && Tools::strtolower($capcha_code)!=Tools::strtolower($savedCode))
                            {
                                $errors[] = $this->module->l('Security code is invalid','blog');
                            }
                        }
                    }
                    if(!count($errors))
                    {
                        if($comment->id)
                        {
                            $comment->update();
                            if((int)$this->context->cookie->id_customer)
                            {
                                $customer = new Customer((int)$this->context->cookie->id_customer);
                                if(Ybc_blog_email_template_class::getSubjectByTemplate('edit_comment'))
                                    $this->sendCommentNotificationEmail(
                                        trim($customer->firstname.' '.$customer->lastname),
                                        $customer->email,
                                        $comment->subject,
                                        $comment->comment,
                                        $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')),
                                        $module->getLink('blog', array('id_post' => $comment->id_post)),
                                        'edit_comment',
                                        $comment_old
                                    );
                                if(($subject = Ybc_blog_email_template_class::getSubjectByTemplate('edit_comment_customer')))
                                {
                                    Mail::Send(
                                        $this->context->language->id, 
                                        'edit_comment_customer',
                                        $subject,
                                        array('{customer}' => $customer->firstname.' '.$customer->lastname, '{email}' => $customer->email,'{rating}' => $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')), '{subject}' => $comment->subject, '{comment}'=>$comment->comment,'{post_title}'=>$post['title'],'{post_link}' => $module->getLink('blog', array('id_post' => $comment->id_post)),'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                                        $customer->email, null, null, null, null, null, 
                                        dirname(__FILE__).'/../../mails/', 
                                        false, $this->context->shop->id
                                    );
                                }
                            }
                            else
                            {
                                if(Ybc_blog_email_template_class::getSubjectByTemplate('edit_comment'))
                                   $this->sendCommentNotificationEmail(
                                        trim($name_customer),
                                        $email_customer,
                                        $comment->subject,
                                        $comment->comment,
                                        $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')),
                                        $module->getLink('blog', array('id_post' => $comment->id_post)),
                                        'edit_comment',
                                        $comment_old
                                   ); 
                                if(($subject = Ybc_blog_email_template_class::getSubjectByTemplate('edit_comment_customer')))
                                {
                                    Mail::Send(
                                        $this->context->language->id, 
                                        'edit_comment_customer',
                                        $subject,
                                        array('{customer}' => trim($name_customer), '{email}' => $email_customer,'{rating}' => $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')), '{subject}' => $comment->subject, '{comment}'=>$comment->comment,'{post_title}'=>$post['title'], '{post_link}' => $module->getLink('blog', array('id_post' => $comment->id_post)),'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                                        $email_customer, null, null, null, null, null, 
                                        dirname(__FILE__).'/../../mails/', 
                                        false, $this->context->shop->id
                                    );
                                }

                            }
                            $justAdded = true;
                            $success = $this->module->l('Comment has been updated ','blog');
                        }
                        else
                        {
                            $comment->add();
                            if((int)$this->context->cookie->id_customer)
                            {
                                $customer = new Customer((int)$this->context->cookie->id_customer);
                                $this->sendCommentNotificationEmail(
                                    trim($customer->firstname.' '.$customer->lastname),
                                    $customer->email,
                                    $comment->subject,
                                    $comment->comment,
                                    $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')),
                                    $module->getLink('blog', array('id_post' => $comment->id_post)),
                                    'new_comment'.($comment->approved==1?'_1':'_0')
                                );
                                if(($subjectMail = Ybc_blog_email_template_class::getSubjectByTemplate('new_comment_customer'.($comment->approved==1?'_1':'_0'))))
                                    Mail::Send(
                                        $this->context->language->id, 
                                        'new_comment_customer'.($comment->approved==1?'_1':'_0'),
                                        $subjectMail,
                                        array('{customer_name}' => $customer->firstname.' '.$customer->lastname, '{email}' => $customer->email,'{rating}' => ' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')), '{subject}' => $comment->subject, '{comment}'=>$comment->comment, '{post_link}' => $module->getLink('blog', array('id_post' => $comment->id_post)),'{post_title}'=>$post['title'],'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                                        $customer->email, null, null, null, null, null, 
                                        dirname(__FILE__).'/../../mails/', 
                                        false, $this->context->shop->id
                                    );
                            }
                            else
                            {
                               $this->sendCommentNotificationEmail(
                                    trim($name_customer),
                                    $email_customer,
                                    $comment->subject,
                                    $comment->comment,
                                    $comment->rating.' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')),
                                    $module->getLink('blog', array('id_post' => $comment->id_post)),
                                    'new_comment'.($comment->approved==1?'_1':'_0')
                                ); 
                                if(($subjectMail = Ybc_blog_email_template_class::getSubjectByTemplate('new_comment_customer'.($comment->approved==1?'_1':'_0'))))
                                    Mail::Send(
                                        $this->context->language->id, 
                                        'new_comment_customer'.($comment->approved==1?'_1':'_0'),
                                        $subjectMail,
                                        array('{customer_name}' => $name_customer, '{email}' => $email_customer,'{rating}' => ' '.($comment->rating != 1 ? $this->module->l('stars','blog') : $this->module->l('star','blog')), '{subject}' => $comment->subject, '{comment}'=>$comment->comment, '{post_link}' => $module->getLink('blog', array('id_post' => $comment->id_post)),'{post_title}'=>$post['title'],'{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),'{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')),  
                                        $email_customer, null, null, null, null, null, 
                                        dirname(__FILE__).'/../../mails/', 
                                        false, $this->context->shop->id
                                    );
                            }
                              
                            $justAdded = true;
                            $success = $this->module->l('Comment has been submitted ','blog');
                            if($comment->approved)
                                $success .= $this->module->l('and approved','blog');
                            else
                                $success .= $this->module->l('and is waiting for approval','blog');
                        }
                        Hook::exec('actionUpdateBlog', array(
                            'id_post' =>(int)$id_post,
                        ));
                        
                    }       
                }
                if(($id_comment=(int)Tools::getValue('replyCommentsave')))
                {
                    if($this->module->checkPermisionComment('reply',$id_comment))
                    {
                        $reply_comwent_text = Tools::getValue('reply_comwent_text');
                        if(Tools::strlen($reply_comwent_text) < 20)
                            $errors[] = $this->module->l('Reply needs to be at least 20 characters','blog');
                        if(!Validate::isCleanHtml($reply_comwent_text,false))
                            $errors[] = $this->module->l('Reply needs to be clean HTML','blog');
                        if(Tools::strlen($reply_comwent_text) >2000)
                            $errors[] = $this->module->l('Reply cannot be longer than 2000 characters','blog');
                    }
                    else
                        $errors[]= $this->module->l('Sorry, you do not have permission','blog');   
                    if(!$errors)
                    {
                        $comment = new Ybc_blog_comment_class($id_comment);
                        $post_class= new Ybc_blog_post_class($comment->id_post,$this->context->language->id);
                        if($post_class->is_customer && $post_class->added_by ==$this->context->customer->id)
                        {
                            $approved=1;
                        }
                        else
                            $approved=0;
                        $sql= "INSERT INTO `"._DB_PREFIX_."ybc_blog_reply`(id_comment,id_user,name,email,reply,id_employee,approved,datetime_added,datetime_updated) values('".(int)$id_comment."', '".(int)$this->context->customer->id."','".pSQL($this->context->customer->firstname)." ".pSQL($this->context->customer->lastname)."','".pSQL($this->context->customer->email)."','".pSQL($reply_comwent_text)."',0,'".(int)$approved."','".pSQL(date('Y-m-d H:i:s'))."','".pSQL(date('Y-m-d H:i:s'))."')";
                        Db::getInstance()->execute($sql);
                        if($approved)
                            $success_reply = $this->module->l('Reply has been submitted','blog');
                        else
                            $success_reply = $this->module->l('Reply has been submitted and is waiting for approval','blog');
                        $comment->viewed=0;
                        $comment->update();
                        if($approved)
                        {
                            if($this->context->customer->email!=$comment->email)
                            {
                                $this->module->sendMailRepyCustomer($id_comment,$this->context->customer->firstname.' '.$this->context->customer->lastname);
                            }
                        }
                        $this->module->sendMailReplyAdmin($id_comment,$this->context->customer->firstname.' '.$this->context->customer->lastname,$approved);
                        Hook::exec('actionUpdateBlog', array(
                            'id_post' =>(int)$id_post,
                        )); 
                        $this->context->smarty->assign(
                            array(
                                'replyCommentsave' => $id_comment,
                                'reply_comwent_text' => $reply_comwent_text,
                                'replyCommentsaveok' => true,
                            )
                        );
                    }
                    else
                    {
                        $this->context->smarty->assign(
                            array(
                                'replyCommentsave' => $id_comment,
                                'reply_comwent_text' => $reply_comwent_text
                            )
                        );
                    }
                }
                $id_customer = ($this->context->customer->id) ? (int)($this->context->customer->id) : 0;
                $id_group = null;
                if ($id_customer) {
                    $id_group = Customer::getDefaultGroupId((int)$id_customer);
                }
                if (!$id_group) {
                    $id_group = (int)Group::getCurrent()->id;
                }
                $group= new Group($id_group);
                if($post)
                {
                    
                    $urlAlias = Tools::strtolower(trim(Tools::getValue('url_alias')));
                    $edit_comment = (int)Tools::getValue('edit_comment');
                    $idComment = (int)Tools::getValue('id_comment');
                    if($urlAlias && !$edit_comment &&  $urlAlias != Tools::strtolower(trim($post['url_alias'])))
                       $this->module->redirect($module->getLink('blog',array('id_post' => $post['id_post'])));               
                    //check if liked post
                    $likedPost = $this->module->isLikedPost($post['id_post']);
                    if((int)Tools::getValue('all_comment'))
                        $climit=false;
                    else
                        $climit = (int)Configuration::get('YBC_BLOG_MAX_COMMENT') ? (int)Configuration::get('YBC_BLOG_MAX_COMMENT') : false;  
                    $cstart = $climit ? 0 : false;
                    $countComment= $this->module->countCommentsWithFilter(' AND bc.approved = 1 AND bc.id_post='.(int)$id_post);
                    if($climit && $countComment > (int)$climit )
                        $this->context->smarty->assign('link_view_all_comment', $module->getLink('blog',array('id_post' => $post['id_post'],'all_comment'=>1)).'#blog-comments-list');
                    $prettySkin = Configuration::get('YBC_BLOG_GALLERY_SKIN');
                    $randomcode = time();
                    $pollrandomcode= $randomcode;
                    if($edit_comment && $module->itemExists('comment','id_comment',$edit_comment) && (!Tools::isSubmit('bcsubmit') || (Tools::isSubmit('bcsubmit') && $idComment == $edit_comment) ) && $this->module->checkPermisionComment('edit',$edit_comment))
                    {
                        $comment_edit = new Ybc_blog_comment_class($edit_comment);
                        $this->context->smarty->assign(
                            array(
                                'comment_edit' => $comment_edit,
                            )
                        );
                    }

                    if(isset($justAdded) && !$justAdded)
                    {
                        $email_customer = Tools::getValue('email_customer');
                        $name_customer = Tools::getValue('name_customer');
                        $subject = Tools::getValue('subject');
                        $comment = Tools::getValue('comment');
                        $this->context->smarty->assign(
                            array(
                                'comment' => !$justAdded && Validate::isCleanHtml($comment) ? $comment : '',
                                'subject' => !$justAdded && Validate::isCleanHtml($subject) ? $subject : '',
                                'name_customer' => !$justAdded && Validate::isCleanHtml($name_customer) ? $name_customer : '',
                                'email_customer' => !$justAdded && Validate::isCleanHtml($email_customer) ? $email_customer : '',
                                ''
                            )
                        );
                    }
                    if($success)
                    {
                        $this->context->cookie->success=$success;
                        $this->context->cookie->write();
                        $this->module->redirect($this->module->getLink('blog',array('id_post'=>$id_post)));
                    }
                    if($this->context->cookie->success)
                    {

                        $success = $this->context->cookie->success;
                        $this->context->cookie->success='';
                        $this->context->cookie->write();
                    }
                    if($success_reply)
                    {
                        $this->context->cookie->success_reply=$success_reply;
                        $this->context->cookie->replyCommentsave = (int)$id_comment;
                        $this->context->cookie->write();
                        $this->module->redirect($this->module->getLink('blog',array('id_post'=>$id_post)));
                    }
                    if($this->context->cookie->success_reply)
                    {
                        $success_reply = $this->context->cookie->success_reply;
                        $this->context->cookie->success_reply='';
                        $this->context->smarty->assign(
                            array(
                                'replyCommentsave' => $this->context->cookie->replyCommentsave,
                            )
                        );
                        $this->context->cookie->replyCommentsave =0;
                        $this->context->cookie->write();
                    }
                    $comments= $module->getCommentsWithFilter(' AND bc.approved = 1 AND bc.id_post='.(int)$id_post,' bc.id_comment desc, ',$cstart,$climit);
                    if($comments)
                        foreach($comments as &$comment)
                            $comment['reply'] = $module->checkPermisionComment('reply',$comment['id_comment']);
                    if($this->context->customer->logged)
                    {
                        $allow_report_comment = (int)Configuration::get('YBC_BLOG_ALLOW_REPORT') ? true : false;
                    }
                    elseif((int)Configuration::get('YBC_BLOG_ALLOW_REPORT') && (int)Configuration::get('YBC_BLOG_ALLOW_GUEST_REPORT'))
                        $allow_report_comment=true;
                    else
                        $allow_report_comment=false;
                    $polls_name = Tools::getValue('polls_name');
                    $polls_email = Tools::getValue('polls_email');
                    $polls_feedback = Tools::getValue('polls_feedback');
                    $rating = (int)Tools::getValue('rating');
                    $this->context->smarty->assign(
                        array(
                            'blog_post' => $post,
                            'display_desc'=>Configuration::get('YBC_BLOG_POST_PAGE_DISPLAY_DESC'),
                            'allowComments' => (int)Configuration::get('YBC_BLOG_ALLOW_COMMENT'),
                            'langLocale' => $this->module->is17 ? $this->context->language->locale:$this->context->language->language_code,
                            'allowGuestsComments' => (int)Configuration::get('YBC_BLOG_ALLOW_GUEST_COMMENT') ? true : false,
                            'blogCommentAction' => $module->getLink('blog',array('id_post'=>(int)$id_post)),  
                            'hasLoggedIn' => $this->context->customer->isLogged(true), 
                            'blog_errors' => $errors,
                            'replyCommentsaveok' => $success_reply ? true :false,
                            'comments' => $comments,
                            'reportedComments' => $context->cookie->reported_comments ? @unserialize($context->cookie->reported_comments) : false,
                            'blog_success' => $success ? $success :$success_reply,
                            'allow_report_comment' =>$allow_report_comment,
                            'allow_reply_comment' => Configuration::get('YBC_BLOG_ALLOW_REPLY_COMMENT') ? $this->context->customer->logged :false,
                            'display_related_products' =>(int)Configuration::get('YBC_BLOG_SHOW_RELATED_PRODUCTS') ? true : false,
                            'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                            'default_rating' => (int)$rating > 0 && (int)$rating <=5 ? (int)$rating  :(int)Configuration::get('YBC_BLOG_DEFAULT_RATING'),
                            'everage_rating' => $module->getEverageReviews($post['id_post']),
                            'total_review' =>(int)$module->countTotalReviewsWithRating($post['id_post']),
                            'use_capcha' => (int)Configuration::get('YBC_BLOG_USE_CAPCHA') ? true : false,
                            'polls_post_helpful_no' => $this->module->countPollsWithFilter(' AND po.polls=0 AND p.id_post='.(int)$id_post),
                            'polls_post_helpful_yes' => $this->module->countPollsWithFilter(' AND po.polls=1 AND p.id_post='.(int)$id_post),
                            'use_facebook_share' => (int)Configuration::get('YBC_BLOG_ENABLE_FACEBOOK_SHARE') ? true : false,
                            'use_google_share' => (int)Configuration::get('YBC_BLOG_ENABLE_GOOGLE_SHARE') ? true : false,
                            'use_twitter_share' => (int)Configuration::get('YBC_BLOG_ENABLE_TWITTER_SHARE') ? true : false,
                            'post_url' => $module->getLink('blog',array('id_post'=>(int)$id_post)),
                            'report_url' => $module->getLink('report'),
                            'likedPost' => $likedPost,                        
                            'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                            'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                            'show_tags' => (int)Configuration::get('YBC_BLOG_SHOW_POST_TAGS') ? true : false,
                            'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false,
                            'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                            'enable_slideshow' => (int)Configuration::get('YBC_BLOG_ENABLE_POST_SLIDESHOW') ? true : false,
                            'prettySkin' => in_array($prettySkin, array('dark_square','dark_rounded','default','facebook','light_rounded','light_square')) ? $prettySkin : 'dark_square', 
                            'prettyAutoPlay' => (int)Configuration::get('YBC_BLOG_GALLERY_AUTO_PLAY') ? 1 : 0,
                            'path' => $module->getBreadCrumb(),
                            'show_author' => (int)Configuration::get('YBC_BLOG_SHOW_POST_AUTHOR') ? 1 : 0,
                            'blog_random_code' => $randomcode,
                            'capcha_image' => $module->getLink('capcha',array('randcode'=>$randomcode)),
                            'blog_poll_random_code'=>$pollrandomcode,
                            'allowPolls' => $this->context->customer->logged || Configuration::get('YBC_BLOG_ENABLE_POLLS_GUESTS'),
                            'polls_customer' => $this->context->customer->logged ? $this->context->customer :false,
                            'polls_feedback' => Validate::isCleanHtml($polls_feedback) ? $polls_feedback :'',
                            'polls_email' => Validate::isCleanHtml($polls_email) ? $polls_email :'',
                            'polls_name' => Validate::isCleanHtml($polls_name) ? $polls_name :'',
                            'polls_class' => $this->getPollsCurrent($id_post),
                            'polls_capcha_image' => $this->context->link->getModuleLink('ybc_blog','capcha',array('randcode'=>$pollrandomcode,'type'=>'polls')),
                            'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                            'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')), 
                            'blog_related_product_type' => Tools::strtolower(Configuration::get('YBC_RELATED_PRODUCTS_TYPE')),
                            //'blog_related_posts_type' => Tools::strtolower(Configuration::get('YBC_RELATED_POSTS_TYPE')),
                            'blog_template_dir' => dirname(__FILE__).'/../../views/templates/front',
                            'breadcrumb' => $module->is17 ? $module->getBreadCrumb() : false,
                            'show_price'=>$group->show_prices,
                            'blog_dir' => $module->blogDir,
                            'justAdded' => isset($justAdded) ? $justAdded :false,
                            'image_folder' => _PS_YBC_BLOG_IMG_,
                            'id_lang'=> $this->context->language->id,
                            'text_gdpr' => Configuration::get('YBC_BLOG_TEXT_GDPR_NOTIFICATION',$this->context->language->id,null,null,$this->module->l('I agree with the use of cookie and personal data according to EU GDPR','blog')),
                        )
                    );   
                }
                else
                {
                    header("HTTP/1.0 404 Not Found");
                    $this->context->smarty->assign(
                        array(
                            'blog_post' => false
                    ));
                }
            }
            else
            {
                $this->context->smarty->assign(
                    array(
                        'blog_post' => false
                ));
            }                        
            if($module->is17)
                $this->setTemplate('module:ybc_blog/views/templates/front/single_post.tpl');      
            else         
                $this->setTemplate('single_post_16.tpl');             
        }
        else
        {
            $postData = $this->getPosts();            
            $this->context->smarty->assign(
                array(
                    'blog_posts' => $postData['posts'],
                    'blog_paggination' => $postData['paggination'],
                    'blog_category' => $postData['category'],
                    'blog_latest' => $postData['latest'],
                    'blog_featured' => $postData['featured'],
                    'blog_popular' => $postData['popular'],
                    'blog_dir' => $postData['blogDir'],
                    'blog_tag' => $postData['tag'],
                    'month' => $postData['month'],
                    'year' => $postData['year'],
                    'blog_search' => $postData['search'],
                    'is_main_page' => !$postData['category'] && !$postData['tag'] && !$postData['search'] && !Tools::isSubmit('latest') && !Tools::isSubmit('id_author') && !Tools::isSubmit('popular') && !Tools::isSubmit('featured') && !$postData['month'] && !$postData['year'] ? true : false,
                    'allow_rating' => (int)Configuration::get('YBC_BLOG_ALLOW_RATING') ? true : false,
                    'show_featured_post' => (int)Configuration::get('YBC_BLOG_SHOW_FEATURED_BLOCK') ? true : false,
                    'allow_like' => (int)Configuration::get('YBC_BLOG_ALLOW_LIKE') ? true : false,
                    'show_date' => (int)Configuration::get('YBC_BLOG_SHOW_POST_DATE') ? true : false,
                    'show_views' => (int)Configuration::get('YBC_BLOG_SHOW_POST_VIEWS') ? true : false,
                    'path' => $module->getBreadCrumb(),
                    'date_format' => trim((string)Configuration::get('YBC_BLOG_DATE_FORMAT')),
                    'show_categories' => (int)Configuration::get('YBC_BLOG_SHOW_POST_CATEGORIES') ? true : false, 
                    'blog_layout' => Tools::strtolower(Configuration::get('YBC_BLOG_LAYOUT')),   
                    'blog_skin' => Tools::strtolower(Configuration::get('YBC_BLOG_SKIN')),
                    'author' => $postData['author'],     
                    'breadcrumb' => $module->is17 ? $module->getBreadCrumb() : false, 
                    'image_folder' => _PS_YBC_BLOG_IMG_,             
                )
            );
            if(Tools::isSubmit('loadajax'))
            {
                $this->module->loadMoreBlog($postData);
            }
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'/module/ybc_blog') !==false)
            {
                 $this->module->redirect($this->module->getLink('blog'));
            }
            if($module->is17)
                $this->setTemplate('module:ybc_blog/views/templates/front/blog_list.tpl');      
            else        
                $this->setTemplate('blog_list_16.tpl'); 
        }               
	}
    public function getPost($id_post)
    {
        $module = new Ybc_blog();
        $post = $module->getPostById($id_post);
        if($post)
        {
            $post['id_category'] = $module->getCategoriesStrByIdPost($post['id_post']);
            $post['tags'] = $module->getTagsByIdPost($post['id_post']);
            $post['related_posts'] = (int)Configuration::get('YBC_BLOG_DISPLAY_RELATED_POSTS') ? $module->getRelatedPosts($id_post, $post['tags'], $this->context->language->id) : false; 
            if($post['related_posts'])
            {
                foreach($post['related_posts'] as &$rpost)
                    if($rpost['image'])
                    {
                        $rpost['image'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/'.$rpost['image']);
                        if($rpost['thumb'])
                            $rpost['thumb'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/thumb/'.$rpost['thumb']);
                        else
                            $rpost['thumb'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/'.$rpost['image']);            
                        $rpost['link'] =   $module->getLink('blog',array('id_post'=>$rpost['id_post']));
                        $rpost['categories'] = $module->getCategoriesByIdPost($rpost['id_post'],false,true); 
                        $rpost['comments_num'] = $module->countCommentsWithFilter(' AND bc.id_post='.$rpost['id_post'].' AND approved=1');
                        $rpost['liked'] = $this->module->isLikedPost($rpost['id_post']);                        
                    }
                    else
                    {
                        $rpost['image'] = '';
                        if($rpost['thumb'])
                            $rpost['thumb'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/thumb/'.$rpost['thumb']);
                        else
                            $rpost['thumb'] = '';
                        $rpost['link'] =   $module->getLink('blog',array('id_post'=>$rpost['id_post']));
                        $rpost['categories'] = $module->getCategoriesByIdPost($rpost['id_post'],false,true); 
                        $rpost['comments_num'] = $module->countCommentsWithFilter(' AND bc.id_post='.$rpost['id_post'].' AND approved=1');
                        $rpost['liked'] = $this->module->isLikedPost($rpost['id_post']);  
                    }                        
            }               
            $post['img_name'] = isset($post['image']) ? $post['image'] : '';
            if($post['image'])
                $post['image'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/'.$post['image']);                            
            $post['link'] = $module->getLink('blog',array('id_post'=>$post['id_post']));
            $post['categories'] = $module->getCategoriesByIdPost($post['id_post'],false,true);  
            $post['products'] = $post['products'] ? $module->getRelatedProductByProductsStr($post['products']) : false;  
            $params = array(); 
            $params['id_author'] = (int)$post['added_by'];
            $params['is_customer'] = (int)$post['is_customer'];
            $employee = $this->getAuthorById($params['id_author'],$params['is_customer']);
            if($employee)
            {
                if(!isset($employee['name'])|| !$employee['name'])
                    $employee['name']=$employee['firstname'].' '.$employee['lastname'];
            }
            $params['alias'] = str_replace(' ','-',trim($employee['name'])); 
            $post['author_link'] = $module->getLink('blog', $params);
            $post['employee'] =$employee;
            if($post['is_customer'] && $this->module->checkPermistionPost($post['id_post'],'edit_blog') )
            {
                $post['link_edit'] = $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'post','editpost'=>1,'id_post'=>$post['id_post']));
            }
            return $post;
        }
        return false;
    }
    public function getPosts()
    {
        $context = Context::getContext();
        $params = array('page'=>"_page_");
        $module = new Ybc_blog();
        $filter = ' AND p.enabled =1';
        $featurePage = false;
        $id_category = (int)trim(Tools::getValue('id_category'));
        $year = (int)Tools::getValue('year');
        $month = (int)Tools::getValue('month');
        if(!$id_category && ($category_url_alias = Tools::getValue('category_url_alias')) && Validate::isLinkRewrite($category_url_alias))
        {
            $id_category = (int)Db::getInstance()->getValue('SELECT cs.id_category FROM `'._DB_PREFIX_.'ybc_blog_category_lang` cl,`'._DB_PREFIX_.'ybc_blog_category_shop` cs WHERE cs.id_category=cl.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND cl.url_alias ="'.pSQL($category_url_alias).'"');
            if(!Configuration::get('YBC_BLOG_URL_NO_ID') && $id_category)
                $this->module->redirect($this->module->getLink('blog',array('id_category'=>$id_category)));
                
        }
        elseif($id_category && Configuration::get('YBC_BLOG_URL_NO_ID') && $this->module->friendly)
             $this->module->redirect($this->module->getLink('blog',array('id_category'=>$id_category)));
        if($id_category && $this->module->friendly && (Tools::strpos($_SERVER['REQUEST_URI'],'category_url_alias') !==false || Tools::strpos($_SERVER['REQUEST_URI'],'url_alias')!==false))
        {
             $this->module->redirect($this->module->getLink('blog',array('id_category'=>$id_category)));
        }                  
        if($id_category)
        {
            if($module->itemExists('category','id_category',$id_category))
            {
                $category = new Ybc_blog_category_class($id_category,$this->context->language->id);
                $urlAlias = Tools::strtolower(trim(Tools::getValue('url_alias')));
                if($urlAlias && $urlAlias != Tools::strtolower(trim($category->url_alias)))
                    $this->module->redirect($module->getLink('blog',array('id_category' => $id_category)));
            }
            $filter .= " AND p.id_post IN (SELECT id_post FROM `"._DB_PREFIX_."ybc_blog_post_category` WHERE id_category = ".(int)trim($id_category).") ";
            $params['id_category'] = (int)trim($id_category);
        }
        elseif(($latest = trim(Tools::getValue('latest'))) && Validate::isCleanHtml($latest))
        {
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'latest') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                $this->module->redirect($module->getLink('blog',array('latest'=>true)));
            $params['latest'] = 'true';
        } 
        elseif(($featured = trim(Tools::getValue('featured'))) && Validate::isCleanHtml($featured))
        {            
            $params['featured'] = 'true';
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'featured') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                $this->module->redirect($module->getLink('blog',array('featured'=>true)));
            $filter .=' AND p.is_featured=1';
        }
        elseif(($popular = trim(Tools::getValue('popular'))) && Validate::isCleanHtml($popular))
        {            
            $params['popular'] = 'true';
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'popular') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                $this->module->redirect($module->getLink('blog',array('popular'=>true)));
        }                
        elseif(($tag = trim(Tools::getValue('tag')))!=''&& ($tag= urldecode(trim($tag))) && Validate::isCleanHtml($tag))
        {    
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'tag') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                 $this->module->redirect($this->module->getLink('blog',array('tag'=>$tag)));
            $md5tag = md5(urldecode(trim(Tools::strtolower($tag))));            
            $filter .= " AND p.id_post IN (SELECT id_post FROM `"._DB_PREFIX_."ybc_blog_tag` WHERE tag = '$tag' AND id_lang = ".$this->context->language->id.")";
            //Increase views          
            if(!$context->cookie->tags_viewed)
               $tagsViewed = array();
            else
               $tagsViewed = @unserialize($context->cookie->tags_viewed);
                     
            if(is_array($tagsViewed) && !in_array($md5tag, $tagsViewed))
            {   
                if($module->increasTagViews($tag))
                {
                    $tagsViewed[] = $md5tag;
                    $context->cookie->tags_viewed = @serialize($tagsViewed);
                    $context->cookie->write();    
                }                              
            }
            $params['tag'] = $tag;
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'tag') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                $this->module->redirect($module->getLink('blog',array('tag'=>$tag)));
        }  
        elseif(($search = trim(Tools::getValue('search')))!='' && Validate::isCleanHtml($search))
        {
            $filter .= " AND p.id_post IN (SELECT id_post FROM `"._DB_PREFIX_."ybc_blog_post_lang` WHERE (title like '%".pSQL(str_replace('+',' ',$search))."%' OR description like '%".pSQL(str_replace('+',' ',$search))."%') AND id_lang = ".$this->context->language->id.")";
            $params['search'] = $search;
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'search') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                $this->module->redirect($module->getLink('blog',array('search'=>$search)));
        }
        elseif($id_author = (int)Tools::getValue('id_author'))
        {
            $is_customer = (int)Tools::getValue('is_customer');
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'author_name') !==false)
            {
                $this->module->redirect($this->module->getLink('blog',array('id_author'=>$id_author,'is_customer'=> $is_customer)));
            }
                           
            $filter .= " AND p.added_by = ".$id_author.' AND p.is_customer="'.(int)$is_customer.'"';
            $params['id_author'] = $id_author;
            $params['is_customer'] = $is_customer;
           
            $employee = $this->getAuthorById($id_author,$is_customer);
            if($employee)
                $params['alias'] = $employee['alias'];
            else
            {
                header("HTTP/1.0 404 Not Found");
                $employee['disabled']=true;
            }
            
        }  
        elseif($year && $month)
        {
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'month') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                $this->module->redirect($this->module->getLink('blog',array('year'=>$year,'month'=>$month)));
            $filter .=' AND MONTH(p.datetime_added) ="'.pSQL($month).'" AND YEAR(p.datetime_added)="'.pSQL($year).'"';
            $params['year']=$year;
            $params['month']=$month;
        }
        elseif($year)
        {
            if($this->module->friendly && Tools::strpos($_SERVER['REQUEST_URI'],'year') !==false && Tools::strpos($_SERVER['REQUEST_URI'],'ybc_blog')!==false)
                 $this->module->redirect($this->module->getLink('blog',array('year'=>$year)));
            $filter .=' AND YEAR(p.datetime_added)="'.pSQL($year).'"';
            $params['year']=$year;
        }              
        else
        {
            if(Configuration::get('YBC_BLOG_MAIN_PAGE_POST_TYPE') == 'featured')
            {
                $filter .= ' AND p.is_featured = 1';
                $featurePage = true;    
            }                        
        }
        $popular = Tools::getValue('popular');
        $latest = Tools::getValue('latest');
        if($latest)            
            $sort = 'p.id_post DESC, ';
        if(isset($id_category) && $id_category)            
            $sort = 'pc.position ASC,';
        elseif($popular) 
            $sort= 'p.click_number desc,';
        else
        {
            $sort = $this->module->sort;
        }
        if(($ybc_sort_by_posts =Tools::getValue('ybc_sort_by_posts')) && in_array($ybc_sort_by_posts,array('id_post','sort_order','click_number')))
        {
            if($ybc_sort_by_posts=='sort_order')
                $sort = 'p.sort_order ASC, ';
            else
                $sort = 'p.'.$ybc_sort_by_posts.' DESC, ';
        }
        //Paggination
        $page = (int)Tools::getValue('page');
        if($page <1)
            $page=1;
        $totalRecords = (int)$module->countPostsWithFilter($filter);
        $paggination = new Ybc_blog_paggination_class();            
        $paggination->total = $totalRecords;
        
        $paggination->url = $module->getLink('blog', $params);
        if(!Tools::isSubmit('id_category') && !Tools::isSubmit('search') && !Tools::isSubmit('tag') && !Tools::isSubmit('latest') && !Tools::isSubmit('id_author'))
            $paggination->limit =  (int)Configuration::get('YBC_BLOG_ITEMS_PER_PAGE') > 0 ? (int)Configuration::get('YBC_BLOG_ITEMS_PER_PAGE') : 20;
        else
            $paggination->limit =  (int)Configuration::get('YBC_BLOG_ITEMS_PER_PAGE_INNER') > 0 ? (int)Configuration::get('YBC_BLOG_ITEMS_PER_PAGE_INNER') : 20;
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        if(!$featurePage)
            $posts = $module->getPostsWithFilter($filter, $sort, $start, $paggination->limit);
        else
            $posts = $module->getPostsWithFilter($filter, $sort, 0, false); 
           
        if($posts)
        {
            foreach($posts as &$post)
            {
                $post['id_category'] = $module->getCategoriesStrByIdPost($post['id_post']);
                $post['tags'] = $module->getTagsByIdPost($post['id_post']);
                if($post['thumb'])
                    $post['thumb'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/thumb/'.$post['thumb']);
                if($post['image'])
                    $post['image'] = $this->context->link->getMediaLink(_PS_YBC_BLOG_IMG_.'post/'.$post['image']);
                $post['link'] = $module->getLink('blog',array('id_post'=>$post['id_post']));
                $post['categories'] = $module->getCategoriesByIdPost($post['id_post'],false,true);
                $post['everage_rating'] = $module->getEverageReviews($post['id_post']);
                $post['total_review'] = $module->countTotalReviewsWithRating($post['id_post']);
                $post['liked'] = $this->module->isLikedPost($post['id_post']);
            }                
        }
        elseif(isset($params['id_author']) && $params['id_author'] && !$employee)
        {
            Tools::redirect($this->module->getLink('author'));
        }
        $category = (int)$id_category ? (($cat = $module->getCategoryById((int)$id_category)) ? $cat : array('enabled' => false)) : false;
        if($category && !$category['enabled'])
            header("HTTP/1.0 404 Not Found");
        $featured = Tools::getValue('featured');
        $search = Tools::getValue('search');
        $tag = Tools::getValue('tag');
        return array(
            'posts' => $posts, 
            'paggination' => $featurePage ? '' : $paggination->render(), 
            'category' => $category,
            'blogDir' => $module->blogDir,
            'tag' => trim($tag) && Validate::isCleanHtml($tag) !='' ? urldecode(trim($tag)) : false,
            'search' => trim($search)!='' && Validate::isCleanHtml($search) ? urldecode(trim($search)) : false,
            'latest' => trim($latest)=='true' ? true : false,
            'popular' => trim($popular)=='true' ? true : false,
            'featured' => trim($featured)=='true' ? true : false,
            'author' => isset($employee) && $employee ? $employee:false,
            'month' => $month && $year ? $year.' - '.$this->module->getMonthName($month) :false,
            'year' => $year ? $year :false,
        );
    }
    public function sendCommentNotificationEmail($customer, $bemail, $subject, $comment, $rating, $postLink,$team_mail='new_comment',$comment_old='')
    {
        $id_post= (int)Tools::getValue('id_post');
        if(!$id_post && ($post_url_alias = Tools::getValue('post_url_alias')) && Validate::isLinkRewrite($post_url_alias))
        {
            $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM `'._DB_PREFIX_.'ybc_blog_post_lang` pl,`'._DB_PREFIX_.'ybc_blog_post_shop` ps WHERE ps.id_post=pl.id_post AND ps.id_shop="'.(int)$this->context->shop->id.'" AND pl.id_lang="'.(int)$this->context->language->id.'" AND pl.url_alias ="'.pSQL($post_url_alias).'"');
        }
        $mailDir = dirname(__FILE__).'/../../mails/';
        $post = new Ybc_blog_post_class($id_post);
        if($post->is_customer)
        {
            $author= new Customer($post->added_by);
            $emails=array($author->email);
            $link_view_comment= $this->context->link->getModuleLink('ybc_blog','managementblog',array('tabmanagament'=>'comment','list'=>1));
        }
        else
        {
            $author= new Employee($post->added_by);
            $emails=array($author->email);
            $link_view_comment= $this->module->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER');
        }
        $lang = new Language((int)$author->id_lang);
        $mail_lang_id = (int)$lang->id;
        if(!is_dir($mailDir.$lang->iso_code))
           $mail_lang_id = (int)Configuration::get('PS_LANG_DEFAULT'); 
        $post = new Ybc_blog_post_class($id_post,$mail_lang_id);
        if($emails && ($subjectMail= Ybc_blog_email_template_class::getSubjectByTemplate($team_mail,$mail_lang_id)))
        {
            foreach($emails as $email)
            {    
                if(Validate::isEmail(trim($email)))
                {
                    if($team_mail=='edit_comment')
                    {
                        $mail_val =array(
                            '{customer_name}' => $customer,
                            '{email}' => $bemail,
                            '{rating}' => $rating,
                            '{subject}' => $subject,
                            '{comment}'=>$comment,
                            '{comment_link}' => '#',
                            '{post_title}'=>$post->title,
                            '{author_name}' => $author->firstname.' '.$author->lastname,
                            '{link_view_comment}'=>$link_view_comment,
                            '{post_link}'=>$postLink,
                            '{comment_old}'=>$comment_old,
                            '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                            '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                        );
                        Mail::Send(
                            $mail_lang_id, 
                            $team_mail,
                            $subjectMail, 
                            $mail_val,  
                            trim($email), null, null, null, null, null, 
                            $mailDir, 
                            false, $this->context->shop->id
                        );  
                    }
                    else
                    {
                        $mail_val=array(
                            '{customer_name}' => $customer,
                            '{email}' => $bemail,
                            '{rating}' => $rating, 
                            '{subject}' => $subject, 
                            '{comment}'=>$comment, 
                            '{comment_link}' => '#',
                            '{author_name}' => $author->firstname.' '.$author->lastname,
                            '{link_view_comment}'=>$link_view_comment,
                            '{post_link}'=>$postLink,
                            '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                            '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER'),
                            '{post_title}'=>$post->title
                        );
                        Mail::Send(
                            $mail_lang_id, 
                            $team_mail, 
                            $subjectMail, 
                            $mail_val,  
                            trim($email), null, null, null, null, null, 
                            $mailDir, 
                            false, $this->context->shop->id
                        );
                    }   
                }                
            }
        }
        if(Configuration::get('YBC_BLOG_ALERT_EMAILS'))
        {
            $emails = explode(',',Configuration::get('YBC_BLOG_ALERT_EMAILS'));
            $link_view_comment= $this->module->getBaseLink().Configuration::get('YBC_BLOG_ADMIN_FORDER');
            if($emails)
            {
                foreach($emails as $email)
                {    
                    if(Validate::isEmail(trim($email)))
                    {
                        $employeeObj = new Employee();
                        if(($employee = $employeeObj->getByEmail($email)) && ($lang = new Language($employee->id_lang)) && Validate::isLoadedObject($lang))
                            $mail_lang_id = $lang->id;
                        else
                            $mail_lang_id = $this->context->language->id;
                        if(($subjectMail = Ybc_blog_email_template_class::getSubjectByTemplate($team_mail,$mail_lang_id)))
                        {
                            $mail_val=array(
                                '{customer_name}' => $customer,
                                '{email}' => $bemail,
                                '{rating}' => $rating, 
                                '{subject}' => $subject, 
                                '{comment}'=>$comment, 
                                '{comment_link}' => '#',
                                '{author_name}' => Configuration::get('PS_SHOP_NAME'),
                                '{link_view_comment}'=>$link_view_comment,
                                '{post_link}'=>$postLink,
                                '{post_title}'=>$post->title,
                                '{comment_old}'=>$comment_old,
                                '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
                                '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
                            );
                            if($team_mail=='edit_comment')
                            {
                                Mail::Send(
                                    $mail_lang_id, 
                                    $team_mail, 
                                    $subjectMail, 
                                    $mail_val,  
                                    trim($email), null, null, null, null, null, 
                                    $mailDir, 
                                    false, $this->context->shop->id
                                );
                            }
                            else
                            {
                                Mail::Send(
                                    $mail_lang_id, 
                                    $team_mail,
                                    $subjectMail, 
                                    $mail_val,  
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
    private function getAuthorById($id_author,$is_customer=0)
    {
        if($is_customer)
        {
            $author= Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'customer` c
            LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee` be ON (c.id_customer=be.id_employee AND be.is_customer=1)
            LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee_lang` bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
            WHERE c.id_customer = '.(int)$id_author);
        }
        else
            $author= Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'employee` e
            LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee` be ON (e.id_employee=be.id_employee AND be.is_customer=0)
            LEFT JOIN `'._DB_PREFIX_.'ybc_blog_employee_lang` bel ON (be.id_employee_post=bel.id_employee_post AND bel.id_lang="'.(int)$this->context->language->id.'")
            WHERE e.id_employee = '.(int)$id_author);
        $params=array();
        $module = new Ybc_blog();
        $params['id_author'] = $id_author;
        $params['is_customer']=$is_customer;
        if($author)
        {
            if(!$author['name'])
                $author['name']=trim(Tools::strtolower($author['firstname'].' '.$author['lastname']));
            $params['alias'] = str_replace(' ','-',$author['name']);
            $author['alias'] = $params['alias'];
            $author['author_link']= $module->getLink('blog',$params);
            if(!$author['avata'])
                $author['avata']=  (Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT')? Configuration::get('YBC_BLOG_IMAGE_AVATA_DEFAULT') :'default_customer.png');
                
        }
        return $author;    
    }
    public function getPollsCurrent($id_post)
    {
        if($this->context->customer->logged)
        {
            $id_polls = Db::getInstance()->getValue('SELECT id_polls FROM `'._DB_PREFIX_.'ybc_blog_polls` WHERE id_post="'.(int)$id_post.'" AND id_user='.(int)$this->context->customer->id);
        }
        else
        {
            if($this->context->cookie->id_post_polls)
            {
                $id_post_polls= Tools::jsonDecode($this->context->cookie->id_post_polls,true);
                $id_polls = isset($id_post_polls[$id_post]) ? $id_post_polls[$id_post] :0;
            }
            else
                $id_polls=0;
        }
        if($id_polls)
        {
            $polls_class= new Ybc_blog_polls_class($id_polls);
            return $polls_class;
        }
        else
            return false;
    }
    public function sendMailAdminVoteNew($ybc_blog_polls,$post_class)
    {
        if($post_class->is_customer)
        {
            $author = new Customer($post_class->added_by);
        }
        else
        {
            $author = new Employee($post_class->added_by);
        }
        
        $mail_template= array(
        '{feedback}'=>$ybc_blog_polls->feedback, 
        '{post_link}' => $this->module->getLink('blog', array('id_post' => $post_class->id)),
        '{polls_helpful}' => $ybc_blog_polls->polls? $this->module->l('Yes','blog'): $this->module->l('No','blog'),
        '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
        '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER'),
        '{post_title}'=>$post_class->title);
        if($email = $author->email)
        {
            if(($lang  = new Language($author->id_lang)) && $lang->active )
                $idLang = $lang->id;
            else
                $idLang = $this->context->language->id;
            if(($subject = Ybc_blog_email_template_class::getSubjectByTemplate('new_vote_admin',$idLang)))
            {
                Mail::Send(
                    $this->context->language->id, 
                    'new_vote_admin',
                    $subject,
                    $mail_template,  
                    $email, null, null, null, null, null, 
                    dirname(__FILE__).'/../../mails/', 
                    false, $this->context->shop->id
                );
            }
        }
        if(($emails = Configuration::get('YBC_BLOG_ALERT_EMAILS')) &&  $emails = explode(',',$emails))
        {
            foreach($emails as $email)
            {
                $employeeobj  = new Employee();
                if(Validate::isEmail($email))
                {
                    if(($employee = $employeeobj->getByEmail($email)) && ($lang = new Language($employee->id)) && $lang->active)
                    {
                        $idLang = $lang->id;
                    }
                    else
                        $idLang = $this->context->language->id;
                    if(($subject = Ybc_blog_email_template_class::getSubjectByTemplate('new_vote_admin',$idLang)))
                    {
                        Mail::Send(
                            $idLang, 
                            'new_vote_admin',
                            $subject,
                            $mail_template,  
                            $email, null, null, null, null, null, 
                            dirname(__FILE__).'/../../mails/', 
                            false, $this->context->shop->id
                        );
                    }
                    
                }
            }
        }
        if(($subject = Ybc_blog_email_template_class::getSubjectByTemplate('new_vote_customer')))
        {
            $mail_template= array(
            '{feedback}'=>$ybc_blog_polls->feedback, 
            '{customer_name}' => $ybc_blog_polls->name,
            '{post_link}' => $this->module->getLink('blog', array('id_post' => $post_class->id)),
            '{polls_helpful}' => $ybc_blog_polls->polls? $this->module->l('Yes','blog'): $this->module->l('No','blog'),
            '{post_title}'=>$post_class->title,
            '{color_main}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR'),
            '{color_hover}'=>Configuration::get('YBC_BLOG_CUSTOM_COLOR_HOVER')
            );
            Mail::Send(
                $this->context->language->id, 
                'new_vote_customer',
                $subject,
                $mail_template,  
                $ybc_blog_polls->email, null, null, null, null, null, 
                dirname(__FILE__).'/../../mails/', 
                false, $this->context->shop->id
            );
        }
        
    }
}