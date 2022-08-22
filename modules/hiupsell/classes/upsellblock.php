<?php
/**
* 2013 - 2017 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2017
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*/

class UpsellBlock extends ObjectModel
{
    public $id_block;
    public $active;
    public $title;
    public $products_type;
    public $products_count;
    public $products;
    public $hook;
    public $block_layout;
    public $offer_discount;
    public $apply_discount;
    public $reduction_percent;
    public $reduction_amount;
    public $reduction_currency;
    public $reduction_tax;

    public static $definition = array(
        'table' => 'upsellblock',
        'primary' => 'id_block',
        'multilang' => true,
        'fields' => array(
            'active' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'title' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'lang' => true, 'size' => 255, 'required' => true),
            'products_type' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'products_count' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'products' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
            'hook' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'block_layout' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'offer_discount' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'apply_discount' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'reduction_percent' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'reduction_amount' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'reduction_currency' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
            'reduction_tax' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
        ),
    );

    public static function getAllBlocks($active = '')
    {
        $id_lang = Context::getContext()->language->id;
        $query = new DbQuery();
        $active_in = $active != '' ? '("1")' : '("0","1")';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('up.*')
                ->select('up_l.*')
                ->from('upsellblock', 'up')
                ->leftJoin('upsellblock_lang', 'up_l', 'up_l.`id_block` = up.`id_block`')
                ->where('up_l.`id_lang` = '.(int)$id_lang)
                ->where('up.active IN '.$active_in)
                ->build()
        );
    }

    public static function getAllBlocksByHook($active = '', $hook = '')
    {
        $id_lang = Context::getContext()->language->id;
        $query = new DbQuery();
        $active_in = $active != '' ? '("1")' : '("0","1")';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('up.*')
                ->select('up_l.*')
                ->from('upsellblock', 'up')
                ->leftJoin('upsellblock_lang', 'up_l', 'up_l.`id_block` = up.`id_block`')
                ->where('up_l.`id_lang` = '.(int)$id_lang)
                ->where('up.`hook` = \''.pSQL($hook).'\'')
                ->where('up.active IN '.$active_in)
                ->build()
        );
    }

    public static function getAllBlocksByIdBlock($active = '', $id_block = '')
    {
        $id_lang = Context::getContext()->language->id;
        $query = new DbQuery();
        $active_in = $active != '' ? '("1")' : '("0","1")';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('up.*')
                ->select('up_l.*')
                ->from('upsellblock', 'up')
                ->leftJoin('upsellblock_lang', 'up_l', 'up_l.`id_block` = up.`id_block`')
                ->where('up_l.`id_lang` = '.(int)$id_lang)
                ->where('up.`id_block` = '.(int)$id_block)
                ->where('up.active IN '.$active_in)
                ->build()
        );
    }

    public static function getAllBlocksByHookAndLayout($active = '', $hook = '', $block_layout = 'grid')
    {
        $query = new DbQuery();
        $active_in = $active != '' ? '("1")' : '("0","1")';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            $query
                ->select('up.*')
                ->from('upsellblock', 'up')
                ->where('up.`hook` = \''.pSQL($hook).'\'')
                ->where('up.`block_layout` = \''.pSQL($block_layout).'\'')
                ->where('up.active IN '.$active_in)
                ->build()
        );
    }

    public static function getBlockProductsById($id_block = null)
    {
        $query = new DbQuery();
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            $query
                ->select('up.products')
                ->from('upsellblock', 'up')
                ->where('up.`id_block` = '.(int)$id_block)
                ->build()
        );
    }
}
