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
class Ybc_blogManagementcommentsModuleFrontController extends ModuleFrontController
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
        if (!$this->context->customer->isLogged())
		{
                
            Tools::redirect('index.php?controller=authentication');
        }
        $form_html_post ='';
        if(Tools::isSubmit('submitComment') || Tools::isSubmit('submitCommentStay'))
            $this->_saveComment();
        if(Tools::isSubmit('commentapproved') && $id_comment=Tools::getValue('id_comment'))
        {
               if($this->module->checkPermisionComment())
               {
                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'ybc_blog_comment SET approved="'.(int)Tools::getValue('commentapproved').'" WHERE id_comment='.(int)$id_comment);
                    Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementcomments',array('tabmanagament'=>Tools::getValue('tabmanagament'),'updateComment'=>1)));
               }
               else
               {
                    if(Tools::isSubmit('ajax'))
                    {
                        die(
                            Tools::jsonEncode(
                                array(
                                    'error' => $this->module->l('Sorry, you do not have permission','managementcomments'),
                                )
                            )
                        );
                    }
                    else
                        $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementcomments');
               }
                    
        }
        if(Tools::isSubmit('deletecomment') && $id_comment=(int)Tools::getValue('id_comment'))
        {
            if($this->module->checkPermisionComment('delete'))
            {
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'ybc_blog_comment WHERE id_comment="'.(int)$id_comment.'"');
                if(Tools::isSubmit('ajax'))
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'success' => $this->module->l('You have just deleted the comment successfully','managementcomments'),
                            )
                        )
                    );
                }
                else
                    Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementcomments',array('tabmanagament'=>Tools::getValue('tabmanagament'),'deletedcomment'=>1)));
            }
            else
            {
                if(Tools::isSubmit('ajax'))
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'error' => $this->module->l('Sorry, you do not have permission','managementcomments'),
                            )
                        )
                    );
                }
                else
                    $this->_errros[]=$this->module->l('Sorry, you do not have permission','managementcomments');
            }
               
        }   
        if(Tools::isSubmit('deletedcomment'))
            $this->_sussecfull = $this->module->l('You have just deleted the comment successfully','managementcomments');
        if(Tools::isSubmit('updateComment'))
            $this->_sussecfull = $this->module->l('Comment updated','managementcomments');
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
            $this->setTemplate('module:ybc_blog/views/templates/front/management_comments.tpl');      
        else         
            $this->setTemplate('management_comments16.tpl');  
    }
    public function _saveComment()
    {
        if($this->module->checkPermisionComment())
        {
            $ybc_comment= new Ybc_blog_comment_class(Tools::getValue('id_comment'));
            if(!Tools::getValue('subject'))
                $this->_errros[]= $this->module->l('Subject is required','managementcomments');
            else
                $ybc_comment->subject=Tools::getValue('subject');
            if(!Tools::getValue('comment'))
                $this->_errros[] = $this->module->l('Comment is requied','managementcomments');
            elseif(Tools::strlen(Tools::getValue('comment'))<20)
                $this->_errros[]=$this->module->l('Comment need to be at least 20 characters','managementcomments');
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
            if(Tools::isSubmit('approved'))
                $ybc_comment->approved =Tools::getValue('approved');
            if(!$this->_errros)
            {
                $ybc_comment->update();
                if(Tools::isSubmit('submitComment'))
                    Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementcomments',array('tabmanagament'=>Tools::getValue('tabmanagament'),'updateComment'=>1)));
                else
                    $this->_sussecfull = $this->l('Comment updated');
            }
                
        }
    }
    public function getBreadCrumb()
    {
        $nodes=array();
        $nodes[] = array(
            'title' => $this->module->l('Home','managementcomments'),
            'url' => $this->context->link->getPageLink('index', true),
        );
        $nodes[] = array(
            'title' => $this->module->l('Your account','managementcomments'),
            'url' => $this->context->link->getPageLink('my-account'),
        );
        $nodes[] = array(
            'title' => $this->module->l('My blog comments','managementcomments'),
            'url' => $this->context->link->getModuleLink('ybc_blog','managementcomments'),
        );
        if($this->module->is17)
                return array('links' => $nodes,'count' => count($nodes));
        return $this->module->displayBreadcrumb($nodes);
    }
}