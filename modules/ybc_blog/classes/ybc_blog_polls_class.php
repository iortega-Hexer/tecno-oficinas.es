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
class Ybc_blog_polls_class extends ObjectModel
{
    public $id_user;
    public $name;
    public $email;
    public $id_post;
    public $feedback;
    public $polls;
    public $dateadd;
    public static $definition = array(
		'table' => 'ybc_blog_polls',
		'primary' => 'id_polls',
		'multilang' => false,
		'fields' => array(			
            'id_user' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'name' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 5000),
            'email' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 5000),
            'id_post' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'polls' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'feedback' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 99000),
            'dateadd' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),  
        )
	);
    public	function __construct($id_item = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_item, $id_lang, $id_shop);
	}
    public function duplicate()
    {
        $this->id = null; 
        if($this->add())
        {
            return $this->id;
        }
        return false;        
    }
}