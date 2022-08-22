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
class Ybc_blog_post_employee_class extends ObjectModel
{
    public $id_employee;
    public $name;
    public $is_customer;
    public $avata;
    public $profile_employee;
    public $description;
    public $status;
    public static $definition = array(
		'table' => 'ybc_blog_employee',
		'primary' => 'id_employee_post',
		'multilang' => true,
		'fields' => array(
			'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'name' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500), 
            'is_customer' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'avata' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'status' =>array('type' => self::TYPE_INT),
            'profile_employee' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500), 
            'description' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 9999999),
        )
	);
    public	function __construct($id_item = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
		parent::__construct($id_item, $id_lang, $id_shop);
        $languages = Language::getLanguages(false);        
        foreach($languages as $lang)
        {
            foreach(self::$definition['fields'] as $field => $params)
            {   
                $temp = $this->$field; 
                if(isset($params['lang']) && $params['lang'] && !isset($temp[$lang['id_lang']]))
                {                      
                    $temp[$lang['id_lang']] = '';                        
                }
                $this->$field = $temp;
            }
        }
        unset($context);
	}
    public function duplicate()
    {
        $this->id = null; 
        $oldImage= $this->avata;
        if($this->avata)
            $this->avata = time().pathinfo($this->avata, PATHINFO_BASENAME);
        if($this->add())
        {
            if($this->avata)
                @copy(dirname(__FILE__).'/../views/img/avata/'.$oldImage,dirname(__FILE__).'/../views/img/avata/'.$this->avata);
            return $this->id;
        }
        return false;        
    }
}