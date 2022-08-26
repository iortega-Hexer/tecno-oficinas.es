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
class Ybc_blog_list_helper_class extends Module
{
    public $actions = array();
    public $currentIndex = '';
    public $identifier = '';
    public $show_toolbar = true;
    public $title = '';
    public $fields_list = array();
    public function __construct()
    {
        if($this->fields_list)
        {
            foreach($this->fields_list as $id => &$field)
            {
                $field['active'] = Tools::getValue($field[$id]);
            }
        }
    }
    public function render()
    {
        if($this->fields_list)
        {
            $this->context->smarty->assign(
                array(                    
                    'actions' => $this->actions,
                    'currentIndex' => $this->currentIndex,
                    'identifier' => $this->identifier,
                    'show_toolbar' => $this->show_toolbar,
                    'title' => $this->title,
                    'fields_list' => $this->fields_list,
                )
            );
            return $this->display(__FILE__.'../', 'list_helper.tpl');
        }
    }
}