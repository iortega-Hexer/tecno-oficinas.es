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

class CFCategoryFieldModel extends ObjectModel
{
    /** @var integer Unique ID */
    public $id_categoryfield;

    /** @var string Name */
    public $name;

    /** @var integer Shop ID */
    public $id_shop;

    /** @var boolean collapsible */
    public $collapsible;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'cf_categoryfield',
        'primary' => 'id_categoryfield',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT),
            'name' => array('type' => self::TYPE_STRING),
            'collapsible' => array('type' => self::TYPE_INT),
        )
    );

    public function getList()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->orderBy('name');
        $results = Db::getInstance()->executeS($sql);

        if (!empty($results)) {
            return $this->hydrateCollection('CFCategoryFieldModel', $results);
        } else {
            return array();
        }
    }

    public static function getIDByName($name)
    {
        $sql = new DbQuery();
        $sql->select('id_categoryfield');
        $sql->from(self::$definition['table']);
        $sql->where('name  LIKE "'.pSQL($name).'"');
        $id_categoryfield = Db::getInstance()->getValue($sql);

        if (!empty($id_categoryfield)) {
            return $id_categoryfield;
        } else {
            return 0;
        }
    }

    public function loadByName($name)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('name  LIKE "'.pSQL($name).'"');
        $row = Db::getInstance()->getRow($sql);
        if (!empty($row['id_categoryfield'])) {
            $this->hydrate($row);
        }
    }
}
