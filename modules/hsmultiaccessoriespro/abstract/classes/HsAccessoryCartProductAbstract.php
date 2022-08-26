<?php
/**
 * An abstract accessories cart product of the module.
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsAccessoryCartProductAbstract extends ObjectModel
{
    /**
     * id of accessory cart product.
     *
     * @var int(10)
     */
    public $id_accessory_cart_product;

    /**
     * id of product
     *
     * @var int(10)
     */
    public $id_product;

    /**
     * Id of current cart
     *
     * @var int(10)
     */
    public $id_cart;

    /**
     *
     * @var int(10)
     */
    public $id_product_attribute;
    
    /**
     * id customization of main product
     * @var int(10)
     */
    public $id_product_customization;
    
    /**
     * id of current accessory customization
     * @var int
     */
    public $id_customization;

    /**
     * id of accessory
     *
     * @var int(10)
     */
    public $id_accessory;

    /**
     * id of combination accessory
     *
     * @var int(10)
     */
    public $id_accessory_attribute;

    /**
     * current quantity of accessory.
     *
     * @var int(10)
     */
    public $quantity;

    /**
     * first quantity of accessory.
     *
     * @var int(10)
     */
    public $prev_quantity;
    protected static $cache_id_accessory_cart_product = array();

    /**
     * define field.
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'accessory_cart_product',
        'primary' => 'id_accessory_cart_product',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product_customization' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customization' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_accessory_cart_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_accessory' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'prev_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_accessory_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    /**
     * Constructor.
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_main_product_attribute
     * @param int $id_accessory
     * @param int $id_accessory_attribute
     * @param int $id_customization
     * @param int $id_product_customization
     */
    public function __construct($id_cart, $id_product, $id_main_product_attribute, $id_accessory, $id_accessory_attribute, $id_customization = 0, $id_product_customization = 0)
    {
        $id_accessory_cart_product = self::getId($id_cart, $id_product, $id_main_product_attribute, $id_accessory, $id_accessory_attribute, $id_customization, $id_product_customization);
        parent::__construct($id_accessory_cart_product);
    }

    /**
     * Get id accessory product setting.
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_main_product_attribute
     * @param int $id_accessory
     * @param int $id_accessory_attribute
     * @param int $id_customization
     * @param int $id_product_customization
     * @return int
     */
    protected static function getId($id_cart, $id_product, $id_main_product_attribute, $id_accessory, $id_accessory_attribute, $id_customization, $id_product_customization)
    {
        $cache_key = implode('_', array($id_cart, $id_product, $id_main_product_attribute, $id_accessory, $id_accessory_attribute, $id_customization, $id_product_customization));
        if (!isset(self::$cache_id_accessory_cart_product[$cache_key])) {
            $query = new DbQuery();
            $query->select('`id_accessory_cart_product`');
            $query->from('accessory_cart_product');
            $query->where('`id_cart` = ' . (int) $id_cart);
            $query->where('`id_product` = ' . (int) $id_product);
            $query->where('`id_product_attribute` = ' . (int) $id_main_product_attribute);
            $query->where('`id_accessory` = ' . (int) $id_accessory);
            $query->where('`id_accessory_attribute` = ' . (int) $id_accessory_attribute);
            if ($id_customization > 0) {
                $query->where('`id_customization` = ' . (int) $id_customization);
            }
            if ($id_product_customization > 0) {
                $query->where('`id_product_customization` = ' . (int) $id_product_customization);
            }
            self::$cache_id_accessory_cart_product[$cache_key] = (int) Db::getInstance()->getValue($query);
        }

        return self::$cache_id_accessory_cart_product[$cache_key];
    }

    /**
     * Get all accessories by id main product & id product attribute & id_cart
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_product_customization
     * @return array
     */
    public static function getAccessoriesByIdCartProduct($id_cart, $id_product, $id_product_attribute, $id_product_customization)
    {
        $query = new DbQuery();
        $query->select('`id_accessory_cart_product`, `id_product`, `id_cart`, `id_product_attribute`, `id_product_customization`, `id_accessory`, `id_accessory_attribute`, `id_customization`, `quantity`, `prev_quantity`');
        $query->from('accessory_cart_product');
        $query->where('`id_cart` = ' . (int) $id_cart);
        $query->where('`id_product` = ' . (int) $id_product);
        $query->where('`id_product_attribute` = ' . (int) $id_product_attribute);
        $query->where('`id_product_customization` = ' . (int) $id_product_customization);
        return Db::getInstance()->executeS($query);
    }

    /**
     * Delete all accessories when delete main product
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_product_customization
     * @return boolean
     */
    public static function deteleteProductAccessories($id_cart, $id_product, $id_product_attribute, $id_product_customization)
    {
        return Db::getInstance()->delete('accessory_cart_product', '`id_cart` = ' . (int) $id_cart . ' AND `id_product` = ' . (int) $id_product . ' AND `id_product_attribute` = ' . (int) $id_product_attribute . ' AND `id_product_customization` = ' . (int) $id_product_customization);
    }

    /**
     * Remove an accessory when remove a product is accessory from cart
     * @param int $id_cart
     * @param int $id_accessory
     * @param int $id_accessory_attribute
     * @return boolean
     */
    public static function deteleteAccessory($id_cart, $id_accessory, $id_accessory_attribute)
    {
        return Db::getInstance()->delete('accessory_cart_product', 'id_cart = ' . (int) $id_cart . ' AND id_accessory = ' . (int) $id_accessory . ' AND id_accessory_attribute = ' . (int) $id_accessory_attribute);
    }
    
    /**
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_accessory
     * @param int $id_accessory_attribute
     * @return boolean
     */
    public static function deteleteAccessoryProduct($id_cart, $id_product, $id_product_attribute, $id_accessory, $id_accessory_attribute)
    {
        return Db::getInstance()->delete('accessory_cart_product', 'id_cart = ' . (int) $id_cart . ' AND id_product = ' . (int) $id_product . ' AND id_accessory = ' . (int) $id_accessory . ' AND id_product_attribute = ' . (int) $id_product_attribute . ' AND id_accessory_attribute = ' . (int) $id_accessory_attribute);
    }
    
    /**
     *
     * @param int $id_cart
     * @param int $id_accessory
     * @param int $id_accessory_attribute
     * @param int $id_customization
     * @param int $quantity
     * @return array
     */
    public static function getProductByIdCartAccessory($id_cart, $id_accessory, $id_accessory_attribute, $id_customization, $quantity = 0)
    {
        $query = new DbQuery();
        $query->select('`id_accessory_cart_product`, `id_product`, `id_cart`, `id_product_attribute`, `id_accessory`, `id_customization`, `id_accessory_attribute`, `quantity`, `id_product_customization`');
        $query->from('accessory_cart_product');
        $query->where('`id_cart` = ' . (int) $id_cart);
        $query->where('`id_accessory` = ' . (int) $id_accessory);
        $query->where('`id_accessory_attribute` = ' . (int) $id_accessory_attribute);
        $query->where('`id_customization` = ' . (int) $id_customization);
        if ($quantity > 0) {
            $query->where('`quantity` = ' . (int) $quantity);
        }
        return Db::getInstance()->executeS($query);
    }
    
    /**
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_product_attribute
     * @return int
     */
    public static function getTotalProductInCart($id_cart, $id_product, $id_product_attribute)
    {
        $query = new DbQuery();
        $query->select('SUM(`quantity`)');
        $query->from('accessory_cart_product');
        $query->where('`id_accessory` = ' . (int) $id_product);
        $query->where('`id_accessory_attribute` = ' . (int) $id_product_attribute);
        $query->where('`id_cart` = ' . (int) $id_cart);
        return (int) Db::getInstance()->getValue($query);
    }
    
    /**
     * Get id main product by id cart, id accessory
     * @param int $id_cart
     * @param int $id_accessory
     * @param int $id_accessory_attribute
     * @param int $id_customization
     * @return array
     */
    public static function getIdMainProduct($id_cart, $id_accessory, $id_accessory_attribute, $id_customization = 0)
    {
        $cache_id = __CLASS__ . '::' . __FUNCTION__ . '_' . $id_cart . '_' . $id_accessory . '_' . $id_accessory_attribute.'_' . $id_customization;
        if (!Cache::isStored($cache_id)) {
            $query = new DbQuery();
            $query->select('`id_product`,`id_product_attribute`, `id_product_customization`');
            $query->from('accessory_cart_product');
            $query->where('`id_accessory` = ' . (int) $id_accessory);
            $query->where('`id_accessory_attribute` = ' . (int) $id_accessory_attribute);
            $query->where('`id_cart` = ' . (int) $id_cart);
            if ($id_customization > 0) {
                $query->where('`id_customization` = ' . (int) $id_customization);
            }
            $query->limit(1);
            $main_product = Db::getInstance()->executeS($query);
            Cache::store($cache_id, $main_product);
        } else {
            $main_product = Cache::retrieve($cache_id);
        }
        return $main_product;
    }
    
    /**
     * Get all accessories by id main product & id product attribute & id_cart
     * @param int $id_cart
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_product_customization
     * @return array
     */
    public static function getAccessoriesInCart($id_cart, $id_product, $id_product_attribute, $id_product_customization)
    {
        $cache_id = __CLASS__ . '::' . __FUNCTION__ . '_' . $id_cart . '_' . $id_product . '_' . $id_product_attribute. '_' . $id_product_customization;
        if (!Cache::isStored($cache_id)) {
            $query = new DbQuery();
            $query->select('DISTINCT acp.`id_accessory_cart_product`, acp.`id_product`, acp.`id_cart`, acp.`id_product_attribute`, acp.`id_product_customization`, acp.`id_accessory`, acp.`id_accessory_attribute`, acp.`id_customization`, acp.`quantity`, acp.`prev_quantity`, agp.`default_quantity`');
            $query->leftJoin('accessory_group_product', 'agp', 'agp.`id_accessory` = acp.`id_accessory` AND agp.`id_product` = acp.`id_product`');
            $query->from('accessory_cart_product', 'acp');
            $query->where('acp.`id_cart` = ' . (int) $id_cart);
            $query->where('acp.`id_product` = ' . (int) $id_product);
            $query->where('acp.`id_product_attribute` = ' . (int) $id_product_attribute);
            $query->where('acp.`id_product_customization` = ' . (int) $id_product_customization);
            $accessories = Db::getInstance()->executeS($query);
            Cache::store($cache_id, $accessories);
        } else {
            $accessories = Cache::retrieve($cache_id);
        }
        return $accessories;
    }
}
