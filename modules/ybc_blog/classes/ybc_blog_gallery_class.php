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
class Ybc_blog_gallery_class extends ObjectModel
{
    public $id_gallery;
    public $title;
    public $description;
	public $enabled;
	public $image;
    public $thumb;
    public $sort_order;
    public $is_featured;
    public static $definition = array(
		'table' => 'ybc_blog_gallery',
		'primary' => 'id_gallery',
		'multilang' => true,
		'fields' => array(
			'enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'sort_order' => array('type' => self::TYPE_INT),
            'image' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'thumb' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'is_featured' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),            
            // Lang fields
            'title' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 900000),            
            'description' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 900000),
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
    public function add($autodate = true, $null_values = false)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$res = parent::add($autodate, $null_values);
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'ybc_blog_gallery_shop` (`id_shop`, `id_gallery`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}
    public function duplicate()
    {
        $this->id = null; 
        $oldImage= $this->image;
        $oldThumb = $this->thumb;
        if($this->image)
            $this->image = time().pathinfo($this->image, PATHINFO_BASENAME);
        if($this->thumb)
            $this->thumb= time().pathinfo($this->thumb, PATHINFO_BASENAME);
        if($this->add())
        {
            if($this->image)
                @copy(dirname(__FILE__).'/../views/img/gallery/'.$oldImage,dirname(__FILE__).'/../views/img/gallery/'.$this->image);
            if($this->thumb)
                @copy(dirname(__FILE__).'/../views/img/gallery/thumb/'.$oldThumb,dirname(__FILE__).'/../views/img/gallery/thumb/'.$this->thumb);
            return $this->id;
        }
        return false;        
    }
}