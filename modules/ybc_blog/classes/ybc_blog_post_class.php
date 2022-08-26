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
class Ybc_blog_post_class extends ObjectModel
{
    public $id_post;
    public $is_featured;
    public $title;
    public $description;
    public $short_description;
	public $enabled;
	public $url_alias;
    public $meta_description;
    public $meta_keywords;
    public $products;
	public $image;
    public $sort_order;
    public $datetime_added;
    public $datetime_modified;
    public $datetime_active;
    public $added_by;
    public $is_customer;
    public $modified_by;
    public $click_number;
    public $likes;
    public $thumb;
    public $meta_title;
    public $id_category_default;
    public static $definition = array(
		'table' => 'ybc_blog_post',
		'primary' => 'id_post',
		'multilang' => true,
		'fields' => array(
			'enabled' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'sort_order' => array('type' => self::TYPE_INT), 
            'click_number' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),           
            'likes' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'is_featured' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_category_default' => array('type' => self::TYPE_INT),
            'added_by' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'is_customer' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'modified_by' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'products' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'datetime_added' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'datetime_modified' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'datetime_active' =>array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500,'allow_null'=>true),
            // Lang fields
            'image' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500,'lang'=>true),            
            'thumb' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500,'lang'=>true),
            'url_alias' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true,'size' => 500,),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true,'validate' => 'isCleanHtml', 'size' => 700),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'lang' => true,'validate' => 'isCleanHtml', 'size' => 700),            
			'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 700),			
            'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 700),
            'description' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 9999999),
            'short_description' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 9999999)
            
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
			INSERT INTO `'._DB_PREFIX_.'ybc_blog_post_shop` (`id_shop`, `id_post`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}
    public function duplicate()
    {
        $this->id = null; 
        $oldImages= $this->image;
        $oldthumbs = $this->thumb;
        if($this->image)
        {
            foreach($this->image as $id_lang => $image)
            {
                if($image)
                    $this->image[$id_lang] = time().pathinfo($image, PATHINFO_BASENAME);
            }
        }
        if($this->thumb)
        {
            foreach($this->thumb as $id_lang=>$thumb)
            {
                if($thumb)
                    $this->thumb[$id_lang] = time().pathinfo($thumb, PATHINFO_BASENAME);
            }
        }
        if($this->add())
        {
            if($this->image)
            {
                foreach($this->image as $id_lang=>$image)
                {
                    if($image)
                        @copy(_PS_YBC_BLOG_IMG_DIR_.'post/'.$oldImages[$id_lang],_PS_YBC_BLOG_IMG_DIR_.'post/'.$image);
                }
            }
            if($this->thumb)
            {
                foreach($this->thumb as $id_lang=>$thumb)
                {
                    if($thumb)
                        @copy(_PS_YBC_BLOG_IMG_DIR_.'post/thumb/'.$oldthumbs[$id_lang],_PS_YBC_BLOG_IMG_DIR_.'post/thumb/'.$thumb);
                }
                
            }
            return $this->id;
        }
        return false;        
    }
}
