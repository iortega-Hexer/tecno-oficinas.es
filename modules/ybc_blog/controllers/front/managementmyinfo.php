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
class Ybc_blogManagementMyinfoModuleFrontController extends ModuleFrontController
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
        $this->module->setMetas();
        if (!$this->context->customer->isLogged())   
            Tools::redirect('index.php?controller=authentication');
        if(Tools::isSubmit('submitAuthorManagement') || Tools::isSubmit('delemployeeimage'))
        {
                $this->_postAuthor();
        }
        if(Tools::isSubmit('updated'))
        {
            $this->_sussecfull = $this->module->l('Information updated','managementmyinfo');
        }
        if(Tools::isSubmit('delemployeeimage'))
            $this->_sussecfull = $this->module->l('Image deleted','managementmyinfo');
        $this->context->smarty->assign(
            array(
                'errors_html'=>$this->_errros ? $this->module->displayError($this->_errros) : false,
                'form_html_post'=>$this->module->renderFormAuthorInformation(),
                'sucsecfull_html' => $this->_sussecfull ? $this->module->displaySuccessMessage($this->_sussecfull):'',
                'breadcrumb' => $this->module->is17 ? $this->getBreadCrumb() : false, 
                'path' => $this->getBreadCrumb(),
            )
        );
        if($this->module->is17)
            $this->setTemplate('module:ybc_blog/views/templates/front/my_blog_info.tpl');      
        else         
            $this->setTemplate('my_blog_info16.tpl');  
    }
    public function _postAuthor()
    {
        if(Tools::isSubmit('delemployeeimage'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM `'._DB_PREFIX_.'ybc_blog_employee` WHERE id_employee='.(int)$this->context->customer->id.' AND is_customer="1"');
            $employeePost = new Ybc_blog_post_employee_class($id_employee_post);
            @unlink(_PS_YBC_BLOG_IMG_DIR_.'avata/'.$employeePost->avata);
            $employeePost->avata='';
            $employeePost->update();     
            Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementmyinfo',array('deletedimage'=>1)));      
            if(Tools::isSubmit('deletedimage'))
                $this->_sussecfull = $this->module->l('Image deleted','managementmyinfo');   
        }
        if(Tools::isSubmit('submitAuthorManagement'))
        {
            $id_employee_post= (int)Db::getInstance()->getValue('SELECT id_employee_post FROM `'._DB_PREFIX_.'ybc_blog_employee` WHERE id_employee='.(int)$this->context->customer->id);
            if($id_employee_post)
            {
                $employeePost= new Ybc_blog_post_employee_class($id_employee_post);
            }
            else
            {
                $employeePost = new Ybc_blog_post_employee_class();
                $employeePost->status=1;
            }
            $employeePost->id_employee=$this->context->customer->id;
            $employeePost->is_customer=1;
            $author_description = Tools::getValue('author_description');
            if($author_description && !Validate::isCleanHtml($author_description,true))
                $this->_errros[] = $this->l('Introduction info is not valid');
            if($id_employee_post)
            {
                $employeePost->description[$this->context->language->id] = $author_description;
            }
            else
            {
                $languages= Language::getLanguages(false);
                foreach($languages as $language)
                {
                    $employeePost->description[$language['id_lang']] = $author_description;
                } 
            }
            $oldImage = false;
            $newImage = false;
            if(isset($_FILES['author_avata']['tmp_name']) && isset($_FILES['author_avata']['name']) && $_FILES['author_avata']['name'])
            {
                $_FILES['author_avata']['name'] = str_replace(' ','-',$_FILES['author_avata']['name']);
                if(file_exists(_PS_YBC_BLOG_IMG_DIR_.'avata/'.$_FILES['author_avata']['name']))
                {
                    $file_name = $this->module->createNewFileName(_PS_YBC_BLOG_IMG_DIR_.'avata/',$_FILES['author_avata']['name']);
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
    					$this->_errros[] = $this->module->l('Cannot upload the file','managementmyinfo');
    				elseif (!ImageManager::resize($temp_name, _PS_YBC_BLOG_IMG_DIR_.'avata/'.$file_name, Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH'), Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT'), $type))
    					$this->_errros[] = $this->module->displayError($this->module->l('An error occurred during the image upload process.','managementmyinfo'));
    				if (isset($temp_name))
    					@unlink($temp_name);
                    if($employeePost->avata)
                        $oldImage = _PS_YBC_BLOG_IMG_DIR_.'avata/'.$employeePost->avata;
                    $employeePost->avata = $file_name;	
                    $newImage = _PS_YBC_BLOG_IMG_DIR_.'avata/'.$employeePost->avata;		
    			}
                elseif(isset($_FILES['author_avata']) &&				
    				!empty($_FILES['author_avata']['tmp_name']) &&
    				!empty($imagesize) &&
    				in_array($type, array('jpg', 'gif', 'jpeg', 'png')
    			))
                    $this->_errros[] = $this->module->l('Avatar is invalid','managementmyinfo'); 
                              
            }
            if(!$this->_errros)
            {
                if($id_employee_post)
                {
                    if(!$employeePost->update())
                    {
                        if ($newImage)
                            @unlink($newImage);
                        $this->_errros[] = $this->module->displayError($this->module->l('The author info could not be updated.','managementmyinfo'));
                    }
                    else
                    {
                        if (!count($this->_errros) && $oldImage)
                            @unlink($oldImage);
                        Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementmyinfo',array('updated'=>1)));
                    }
                }
                else
                    if(!$employeePost->add())
                    {
                        if ($newImage)
                            @unlink($newImage);
                        $this->_errros[] = $this->module->displayError($this->module->l('The author info could not be updated.','managementmyinfo'));
                    } 
                    else
                    {
                        if (!count($this->_errros) && $oldImage)
                            @unlink($oldImage);
                        Tools::redirectLink($this->context->link->getModuleLink($this->module->name,'managementmyinfo',array('updated'=>1)));
                    }  
            }
            elseif($newImage)
                @unlink($newImage);
            
        }
    }
    public function getBreadCrumb()
    {
        $nodes=array();
        $nodes[] = array(
            'title' => $this->module->l('Home','managementmyinfo'),
            'url' => $this->context->link->getPageLink('index', true),
        );
        $nodes[] = array(
            'title' => $this->module->l('Your account','managementmyinfo'),
            'url' => $this->context->link->getPageLink('my-account'),
        );
        $nodes[] = array(
            'title' => $this->module->l('My blog info','managementmyinfo'),
            'url' => $this->context->link->getModuleLink('ybc_blog','managementmyinfo'),
        );
        if($this->module->is17)
                return array('links' => $nodes,'count' => count($nodes));
        return $this->module->displayBreadcrumb($nodes);
    }
}