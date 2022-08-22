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
class Ybc_blog_slide_class extends ObjectModel
{
    public $id_slide;
    public $caption;
	public $enabled;
	public $image;
    public $sort_order;
    public $url;
    public static $definition = array(
		'table' => 'ybc_blog_slide',
		'primary' => 'id_slide',
		'multilang' => true,
		'fields' => array(
			'enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool',),
            'sort_order' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'image' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 1000),            
            // Lang fields
            'url' =>	array('type' => self::TYPE_STRING,'lang' => true, 'validate' => 'isCleanHtml', 'size' => 1000),
            'url' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml','lang'=>true, 'size' => 1000),
            'caption' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 900000),            
        )
	);
    public	function __construct($id_item = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_item, $id_lang, $id_shop);
	}
    public function add($autodate = true, $null_values = false)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$res = parent::add($autodate, $null_values);
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'ybc_blog_slide_shop` (`id_shop`, `id_slide`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}
    public function duplicate()
    {
        $this->id = null; 
        $oldImage= $this->image;
        if($this->image)
            $this->image = time().pathinfo($this->image, PATHINFO_BASENAME);
        if($this->add())
        {
            if($this->image)
                @copy(dirname(__FILE__).'/../views/img/slide/'.$oldImage,dirname(__FILE__).'/../views/img/slide/'.$this->image);
            return $this->id;
        }
        return false;        
    }
}