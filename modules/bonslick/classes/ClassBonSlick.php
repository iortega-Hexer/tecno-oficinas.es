<?php
/**
 * 2015-2017 Bonpresta
 *
 * Bonpresta Awesome Image Slider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 *  @author    Bonpresta
 *  @copyright 2015-2017 Bonpresta
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ClassBonSlick extends ObjectModel
{
    public $id_tab;
    public $id_shop;
    public $title;
    public $image;
    public $description;
    public $status;
    public $sort_order;
    public $url;

    public static $definition = array(
        'table' => 'bonslick',
        'primary' => 'id_tab',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'image' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
            'url' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'status' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'sort_order' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
        ),
    );

    public function delete()
    {
        $res = true;
        $images = $this->image;

        if ($images) {
            foreach ($images as $image) {
                if ($image && file_exists(_PS_MODULE_DIR_.'bonslick/images/'.$image)) {
                    $res &= @unlink(_PS_MODULE_DIR_.'bonslick/images/'.$image);
                }
            }
        }

        $res &= parent::delete();
        return $res;
    }

    public static function getSlickList()
    {
        $sql = 'SELECT bons.*, bonsl.`title`, bonsl.`url`, bonsl.`image`
                FROM '._DB_PREFIX_.'bonslick bons
                JOIN '._DB_PREFIX_.'bonslick_lang bonsl
                ON (bons.`id_tab` = bonsl.`id_tab`)
                AND bons.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND bonsl.`id_lang` = '.(int)Context::getContext()->language->id.'
                ORDER BY bons.`sort_order`';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    public static function getMaxSortOrder($id_shop)
    {
        $result = Db::getInstance()->ExecuteS('
            SELECT MAX(sort_order) AS sort_order
            FROM `'._DB_PREFIX_.'bonslick`
            WHERE id_shop = '.(int)$id_shop);

        if (!$result) {
            return false;
        }

        foreach ($result as $res) {
            $result = $res['sort_order'];
        }

        $result = $result + 1;

        return $result;
    }

    public function getTopFrontItems($id_shop, $only_active = false)
    {
        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'bonslick bons
                JOIN ' . _DB_PREFIX_ . 'bonslick_lang bonsl
                ON bons.id_tab = bonsl.id_tab
                AND bonsl.id_lang = '.(int)Context::getContext()->language->id.'
                AND bons.id_shop ='.(int)$id_shop;

        if ($only_active) {
            $sql .= ' AND `status` = 1';
        }

        $sql .= ' ORDER BY `sort_order`';

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return array();
        }

        return $result;
    }
}
