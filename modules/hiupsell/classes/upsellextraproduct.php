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

class UpsellExtraProduct extends ObjectModel
{
    public $id;
    public $id_parent;
    public $id_children;

    public static $definition = array(
        'table' => 'upsellextraproduct',
        'primary' => 'id',
        'fields' => array(
            'id_parent' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_children' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );

    public static function getAllChildren($id_parent)
    {
        $query = new DbQuery();
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('upe.*')
                ->from('upsellextraproduct', 'upe')
                ->where('upe.`id_parent` = '.(int)$id_parent)
                ->build()
        );
    }

    public static function getIssetChildren($id_parent, $id_children)
    {
        $query = new DbQuery();
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            $query
                ->select('upe.*')
                ->from('upsellextraproduct', 'upe')
                ->where('upe.`id_parent` = '.(int)$id_parent)
                ->where('upe.`id_children` = '.(int)$id_children)
                ->build()
        );
    }
}
