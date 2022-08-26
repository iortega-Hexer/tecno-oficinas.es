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
class Ybc_blog_category_class extends ObjectModel
{
    public $id_category;
    public $id_parent;
    public $title;
    public $meta_title;
    public $description;
    public $meta_description;
    public $meta_keywords;
	public $enabled;
	public $url_alias;
	public $image;
    public $thumb;
    public $sort_order;
    public $datetime_added;
    public $datetime_modified;
    public $added_by;
    public $modified_by;
    public static $definition = array(
		'table' => 'ybc_blog_category',
		'primary' => 'id_category',
		'multilang' => true,
		'fields' => array(
            'id_parent' => array('type' => self::TYPE_INT),
			'enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'sort_order' => array('type' => self::TYPE_INT),
            'added_by' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'modified_by' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'image' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500,'lang' => true), 
            'thumb' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500,'lang' => true),             
            'datetime_added' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            'datetime_modified' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
            // Lang fields
            'url_alias' =>	array('type' => self::TYPE_STRING, 'lang' => true,'validate' => 'isCleanHtml', 'size' => 500,),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true,'validate' => 'isCleanHtml', 'size' => 700),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'lang' => true,'validate' => 'isCleanHtml', 'size' => 700),   
            'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 700),         
			'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml',  'size' => 700),			
            'description' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 900000),
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
			INSERT INTO `'._DB_PREFIX_.'ybc_blog_category_shop` (`id_shop`, `id_category`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}
    public function delete()
    {
        $id_parent= $this->id_parent;
        Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_category` SET id_parent="'.(int)$id_parent.'" WHERE id_parent="'.(int)$this->id.'"');
        return parent::delete();
    }
    public function duplicate()
    {
        $this->id = null; 
        $oldImages= $this->image;
        $oldThumbs = $this->thumb;
        if($this->image)
        {
            foreach($this->image as $id_lang=>$image)
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
                    $this->thumb[$id_lang] = time().pathinfo($thumb,PATHINFO_BASENAME);
            }
        }    
        if($this->add())
        {
            if($this->image)
            {
                foreach($this->image as $id_lang=>$image)
                {
                    if($image)
                        @copy(_PS_YBC_BLOG_IMG_DIR_.'category/'.$oldImages[$id_lang],_PS_YBC_BLOG_IMG_DIR_.'category/'.$image);
                }
            }
            if($this->thumb)
            {
                foreach($this->thumb as $id_lang=>$thumb)
                {
                    if($thumb)
                        @copy(_PS_YBC_BLOG_IMG_DIR_.'category/thumb/'.$oldThumbs[$id_lang],_PS_YBC_BLOG_IMG_DIR_.'category/thumb/'.$thumb);
                }
            }
            return $this->id;
        }
        return false;        
    }
}