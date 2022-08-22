<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2017 Musaffar Patel
 * @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CFCategoryFieldContentModel extends ObjectModel
{
    /** @var integer Unique ID */
    public $id_categoryfield_content;

    /** @var integer Category Field ID */
    public $id_categoryfield;

    /** @var integer Category ID */
    public $id_category;

    /** @var integer Language ID */
    public $id_lang;

    /** @var integer Shop ID */
    public $id_shop;

    /** @var string Content ID */
    public $content;

    /** @var string Excerpt */
    public $excerpt;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'cf_categoryfield_content',
        'primary' => 'id_categoryfield_content',
        'fields' => array(
            'id_categoryfield' => array('type' => self::TYPE_INT),
            'id_category' => array('type' => self::TYPE_INT),
            'id_lang' => array('type' => self::TYPE_INT),
            'id_shop' => array('type' => self::TYPE_INT),
            'content' => array('type' => self::TYPE_HTML),
            'excerpt' => array('type' => self::TYPE_HTML)
        )
    );

    /**
     * Get Category Field Content
     * @param $id_categoryfield
     * @param $id_category
     * @param $id_lang
     * @param $id_shop
     */
    public function getContent($id_categoryfield, $id_category, $id_lang, $id_shop)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_categoryfield='.(int)$id_categoryfield);
        $sql->where('id_category='.(int)$id_category);
        $sql->where('id_lang='.(int)$id_lang);
        $sql->where('id_shop='.(int)$id_shop);
        $row = Db::getInstance()->getRow($sql);

        if (!empty($row)) {
            return $this->hydrate($row);
        } else {
            return array();
        }
    }

    public function getContentByName($name, $id_category, $id_lang, $id_shop)
    {
        $id_categoryfield = CFCategoryFieldModel::getIDByName($name);
        return $this->getContent($id_categoryfield, $id_category, $id_lang, $id_shop);
    }

    /**
     * Delete all cntent belonging to a category field
     * @param $id_categoryfield
     */
    public static function deleteByCategoryField($id_categoryfield)
    {
        DB::getInstance()->delete(self::$definition['table'], 'id_categoryfield='.(int)$id_categoryfield);
    }
}
