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
class Ybc_blog_comment_class extends ObjectModel
{
    public $id_comment;
    public $id_user;
    public $name;
    public $email;
    public $id_post;
    public $subject;
    public $comment;
    public $reply;
    public $customer_reply;
	public $approved;
	public $datetime_added;
	public $reported;
    public $rating;
    public $viewed;
    public $replied_by;
    public static $definition = array(
		'table' => 'ybc_blog_comment',
		'primary' => 'id_comment',
		'multilang' => false,
		'fields' => array(			
            'id_comment' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => false),
            'replied_by' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'customer_reply'=> array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_user' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'name' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 5000),
            'email' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 5000),
            'rating' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'id_post' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'approved' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'reported' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'subject' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 5000),
            'comment' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 99000),
            'reply' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 99000),
            'viewed' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'datetime_added' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),  
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