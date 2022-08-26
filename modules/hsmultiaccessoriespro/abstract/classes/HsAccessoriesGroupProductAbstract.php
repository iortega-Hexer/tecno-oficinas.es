<?php
/**
 * An abstract accessories group product of the module
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsAccessoriesGroupProductAbstract extends ObjectModel
{
    /**
     * id of accessory group product.
     *
     * @var int(10)
     */
    public $id_accessory_group_product;

    /**
     * id of product short name.
     *
     * @var int(10)
     */
    public $id_accessory_group;

    /**
     * id of product short name.
     *
     * @var int(10)
     */
    public $id_product;

    /**
     * default quantity of accessory.
     *
     * @var int(10)
     */
    public $default_quantity;

    /**
     * minimum quantity of accessory.
     *
     * @var int(10)
     */
    public $min_quantity;

    /**
     * id of product short name.
     *
     * @var int(10)
     */
    public $id_product_attribute;

    /**
     * id of product short name.
     *
     * @var int(10)
     */
    public $id_accessory;

    /**
     * short name of a accessory.
     *
     * @var varchar(50)
     */
    public $name;

    /**
     * Required buy product & accessory together.
     *
     * @var int(1)
     */
    public $required;

    /**
     * Current possition of this accessory product.
     *
     * @var int
     */
    public $position;
    
    protected static $global_total = array();
    protected static $global_total_without_discount = array();

    /**
     * define field.
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'accessory_group_product',
        'primary' => 'id_accessory_group_product',
        'multilang' => true,
        'fields' => array(
            'id_accessory_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'default_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'min_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_accessory' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'required' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
        ),
    );

    /**
     * Get id accessory group product.
     *
     * @param int $id_accessory
     * @param int $id_product
     * @param int $id_accessory_group
     * @param int $id_product_attribute
     *
     * @return int
     */
    public static function getIdAccessoryGroupProduct($id_accessory, $id_product, $id_accessory_group, $id_product_attribute = 0)
    {
        $sql = 'SELECT `id_accessory_group_product` FROM `' . _DB_PREFIX_ . 'accessory_group_product` WHERE 1 = 1
					AND `id_accessory` = ' . (int) $id_accessory . '
					AND `id_accessory_group` = ' . (int) $id_accessory_group . '
					AND `id_product` = ' . (int) $id_product .
                (!empty($id_product_attribute) ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '');

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Get all records of table accessory_group_product.
     *
     * @return array()
     *                 array(<pre>
     *                 'id_accessory_group' => int
     *                 'id_accessory_group_product' => int
     *                 'id_product' => int
     *                 'id_product_attribute' => int
     *                 'id_accessory' => int
     *                 )</pre>
     */
    public static function getAccessoryGroupProducts()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'accessory_group_product`';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get add records of table product_short_name_lang.
     *
     * @return array
     *               array(<pre>
     *               'id_product_short_name' => int
     *               'id_lang' => int
     *               'name' => string
     *               )</pre>
     */
    public static function getShortNames()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'product_short_name_lang`';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get add records of table product_short_name_lang.
     *
     * @return array
     *               array(<pre>
     *               'id_product_short_name' => int
     *               'id_lang' => int
     *               'name' => string
     *               )</pre>
     */
    public static function getAccessoryName($id_product_short_name)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'accessory_group_product_lang` WHERE `id_accessory_group_product` =' . (int) $id_product_short_name;
        $results = Db::getInstance()->executeS($sql);
        $languages = array();
        foreach ($results as $result) {
            $languages[$result['id_lang']] = $result['name'];
        }

        return $languages;
    }

    /**
     *
     * @return float
     */
    public function getAccessoryPrice()
    {
        $specific_price_output = null;
        return Product::getPriceStatic($this->id_accessory, true, $this->id_product_attribute, (int) _PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, null);
    }

    /**
     * Get all id accessory group products by id_accessory.
     *
     * @param int $id_accessory
     *
     * @return int
     */
    protected static function getIdAccessoryGroupProductsByIdAccessory($id_accessory)
    {
        $sql = 'SELECT `id_accessory_group_product` FROM `' . _DB_PREFIX_ . 'accessory_group_product` WHERE `id_accessory` = ' . (int) $id_accessory;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Update global name of accessory.
     *
     * @param int   $id_accessory
     * @param array $names
     *
     * @return bool
     */
    public static function updateGlobalName($id_accessory, $names)
    {
        $flage = true;
        $id_accessories = self::getIdAccessoryGroupProductsByIdAccessory($id_accessory);
        if (!empty($id_accessories)) {
            $values = array();
            foreach ($id_accessories as $id_accessory) {
                foreach ($names as $id_lang => $name) {
                    $values[] = '(' . (int) $id_accessory['id_accessory_group_product'] . ',"' . pSQL($name) . '",' . (int) $id_lang . ')';
                }
            }
            if (!empty($values)) {
                $sql = 'REPLACE INTO `' . _DB_PREFIX_ . 'accessory_group_product_lang`(`id_accessory_group_product`,`name`,`id_lang`) VALUES' . implode(',', $values);
                $flage = Db::getInstance()->execute($sql);
            }
        }

        return $flage;
    }

    /**
     * Get the higher accessory product position from a group accessory.
     * @param int $id_accessory_group
     * @param int $id_product
     * @return int $position
     */
    public static function getHighestPosition($id_accessory_group, $id_product)
    {
        $sql = 'SELECT MAX(`position`)
                FROM `' . _DB_PREFIX_ . 'accessory_group_product`
                WHERE `id_accessory_group` = ' . (int) $id_accessory_group . '
                    AND `id_product` = ' . (int) $id_product;
        $position = DB::getInstance()->getValue($sql);
        return (is_numeric($position)) ? $position : 0;
    }

    /**
     *
     * @param array $id_products
     * @return array
     * array<pre>
     * (
     *    [int: id_product] => array
     *        (
     *            [id_accessories] => string
     *            [id_accessories_combination] => string
     *            [custom_qty] => string
     *            [id_product] => int
     *        )
     *
     *    [11] => array
     *        (
     *            [id_accessories] => 2,1,2,1
     *            [id_accessories_combination] => 0,0,0,0
     *            [custom_qty] => 1,1,1,1
     *            [id_product] => 11
     *        )
     * ....
     * )</pre>
     */
    public static function getAccessoriesByIdProducts($id_products, $use_tax = true, $decimals = 6)
    {
        $cache_key = __CLASS__ . __FUNCTION__ . implode('_', $id_products);
        if (!Cache::isStored($cache_key)) {
            $query = new DbQuery();
            $query->select('IF(ag.`display_style` = ' . (int) HsMaDisplayStyle::USE_DEFAULT . ', ' . (int) Configuration::get('HSMA_DISPLAY_STYLE') . ' , ag.`display_style`) as `display_style`, agp.`id_accessory_group_product`, agp.`id_accessory_group`, agp.`id_product`, agp.`default_quantity`, agp.`id_accessory`, agp.`id_product_attribute`, agp.`required`, p.`available_for_order`, stock.`out_of_stock`, stock.`quantity` AS `stock_available`');
            $query->innerJoin('product', 'p', 'p.`id_product` = agp.`id_accessory`');
            $query->leftJoin('accessory_group', 'ag', 'ag.`id_accessory_group` = agp.`id_accessory_group`');
            $query->join(Product::sqlStock('p', 0));
            $query->from('accessory_group_product', 'agp');
            $query->where('agp.`id_product` IN (' . implode(',', $id_products) . ')');
            $query->where('ag.`active` = 1');
            $query->orderBy('agp.`position` ASC');
            $products_accessories = Db::getInstance()->executeS($query);

            $list_group_product_accessory_together = array();
            $required_accessories = array();
            $added = array();
            foreach ($products_accessories as &$products_accessory) {
                $temp_required_accessories = array();
                $group_buy_together = HsMaProductSetting::getBuyTogetherCurrentValue($products_accessory['id_product']);
                $products_accessory['buy_together'] = $group_buy_together[$products_accessory['id_product']][$products_accessory['id_accessory_group']];
                $products_accessory['is_available_for_order'] = (!Product::isAvailableWhenOutOfStock($products_accessory['out_of_stock']) && (int) $products_accessory['stock_available'] < (int) $products_accessory['default_quantity']) ? 0 : 1;
                if (HsAccessoriesGroupAbstract::isAvailableBuyTogether($products_accessory['buy_together'], $products_accessory['required'], !$products_accessory['is_available_for_order'], $products_accessory['id_accessory_group'], $products_accessory['id_product'])) {
                    $temp_accessories = array(
                        'id_accessory' => (int) $products_accessory['id_accessory'],
                        'id_accessory_combination' => $products_accessory['id_product_attribute'] > 0 ? (int) $products_accessory['id_product_attribute'] : (int) Product::getDefaultAttribute($products_accessory['id_accessory']),
                        'custom_qty' => (int) $products_accessory['default_quantity']
                    );
                    // Case add each group 1 accessories if display style is dropdown
                    if ((int) $products_accessory['display_style'] === (int) HsMaDisplayStyle::DROPDOWN || (int) $products_accessory['display_style'] === (int) HsMaDisplayStyle::RADIO) {
                        if (!isset($added[$products_accessory['id_product']][$products_accessory['id_accessory_group']])) {
                            $temp_required_accessories[$products_accessory['id_product']][$products_accessory['id_accessory_group']] = $temp_accessories;
                            $required_accessories[$products_accessory['id_product']][] = $temp_accessories;
                            $added[$products_accessory['id_product']][$products_accessory['id_accessory_group']] = true;
                        }
                    } else {
                        $temp_required_accessories[$products_accessory['id_product']][$products_accessory['id_accessory_group']] = $temp_accessories;
                        $required_accessories[$products_accessory['id_product']][] = $temp_accessories;
                        $added[$products_accessory['id_product']][$products_accessory['id_accessory_group']] = true;
                    }
                }
                if (!empty($temp_required_accessories)) {
                    $list_group_product_accessory_together[] = $temp_required_accessories;
                }
            }
            // get accessories has required is yes
            foreach ($products_accessories as $products_accessory) {
                $temp_accessory = array();
                if ($products_accessory['buy_together'] !== HsMaProductSetting::BUY_TOGETHER_REQUIRED && $products_accessory['buy_together'] !== HsMaProductSetting::BUY_TOGETHER_NO && $products_accessory['is_available_for_order'] && !isset($added[$products_accessory['id_product']][$products_accessory['id_accessory_group']])) {
                    $temp_accessories = array(
                        'id_accessory' => (int) $products_accessory['id_accessory'],
                        'id_accessory_combination' => $products_accessory['id_product_attribute'] > 0 ? (int) $products_accessory['id_product_attribute'] : (int) Product::getDefaultAttribute($products_accessory['id_accessory']),
                        'custom_qty' => (int) $products_accessory['default_quantity']
                    );
                    $temp_accessory[$products_accessory['id_product']][$products_accessory['id_accessory_group']] = $temp_accessories;
                    $required_accessories[$products_accessory['id_product']][] = $temp_accessories;
                    // aeccoresies have been added.
                    $added[$products_accessory['id_product']][$products_accessory['id_accessory_group']] = true;
                }
                if (!empty($temp_accessory)) {
                    $list_group_product_accessory_together[] = $temp_accessory;
                }
            }
            // get accessories in the case enable option "Buy product & accessories togeth but not select requied is no"
            $list_accessories_required = array();
            $product_prices = array();
            $product_prices_without_discount = array();
            $is_showing_total_price = (int)Configuration::get('HSMA_SHOW_TOTAL_PRICE');
            if (!empty($required_accessories)) {
                foreach ($required_accessories as $id_product => $accessories) {
                    $id_accessories = array();
                    $id_accessories_combination = array();
                    $custom_qty = array();
                    if ($is_showing_total_price) {
                        $total = Product::getPriceStatic($id_product, $use_tax, null, $decimals);
                        $total_without_discount = Product::getPriceStatic($id_product, $use_tax, null, $decimals, null, false, false);
                    }
                    foreach ($accessories as $accessory) {
                        $id_accessories[] = (int) $accessory['id_accessory'];
                        $id_accessories_combination[] = (int) $accessory['id_accessory_combination'];
                        $custom_qty[] = (int) $accessory['custom_qty'];
                        if ($is_showing_total_price) {
                            $cart_rule = HsMaCartRule::getCartRule($accessory, $id_product);
                            $price = Product::getPriceStatic($accessory['id_accessory'], $use_tax, $accessory['id_accessory_combination'], $decimals);
                            $discount_price = HsAccessoriesGroupAbstract::getFinalPrice($price, $cart_rule);
                            $total += $discount_price * $accessory['custom_qty'];
                            $total_without_discount += $price * $accessory['custom_qty'];
                        }
                    }
                    $list_accessories_required[$id_product]['id_accessories'] = implode(',', $id_accessories);
                    $list_accessories_required[$id_product]['id_accessories_combination'] = implode(',', $id_accessories_combination);
                    $list_accessories_required[$id_product]['custom_qty'] = implode(',', $custom_qty);
                    $list_accessories_required[$id_product]['id_product'] = (int) $id_product;
                    $list_accessories_required[$id_product]['has_customization'] = self::getCustomizationInfo($id_accessories);
                    if ($is_showing_total_price) {
                        $product_prices[$id_product] = Tools::displayPrice($total);
                        if ($total_without_discount > $total) {
                            $product_prices_without_discount[$id_product] = Tools::displayPrice($total_without_discount);
                        }
                    }
                }
            }
            Cache::store($cache_key, $list_accessories_required);
            self::$global_total = $product_prices;
            self::$global_total_without_discount = $product_prices_without_discount;
        } else {
            $list_accessories_required = Cache::retrieve($cache_key);
        }
        return $list_accessories_required;
    }
    
    /**
     * @return array
     */
    public static function getTotalPrice()
    {
        return self::$global_total;
    }
    
    /**
     * @return array
     */
    public static function getTotalPriceWithOutDiscount()
    {
        return self::$global_total_without_discount;
    }
    
    /**
     * Get id accessories by id_product, id_group, id_product_attribute
     * @param int $id_group
     * @param int $id_product
     * @param int $id_product_attribute
     * @return array
     */
    public static function getIdAccessoriesByGroupProduct($id_group, $id_product, $id_product_attribute = 0)
    {
        $cache_key = __CLASS__ . __FUNCTION__ . '_' . $id_group . '_' . $id_product . '_' . $id_product_attribute;
        if (!Cache::isStored($cache_key)) {
            $query = new DbQuery();
            $query->select('agp.`id_accessory`');
            $query->from('accessory_group_product', 'agp');
            $query->where('agp.`id_product` = ' . (int) $id_product);
            $query->where('agp.`id_accessory_group` = ' . (int) $id_group);
            if ((int) $id_product_attribute > 0) {
                $query->where('agp.`id_product_attribute` = ' . (int) $id_product_attribute . ')');
            }
            $accessories = Db::getInstance()->executeS($query);
            $id_accessories = array();
            if (!empty($accessories)) {
                foreach ($accessories as $id_accessory) {
                    $id_accessories[] = $id_accessory['id_accessory'];
                }
            }
            Cache::store($cache_key, $id_accessories);
        } else {
            $id_accessories = Cache::retrieve($cache_key);
        }
        return $id_accessories;
    }
    
    /**
     * Delete all accessories in the group when delete accessory group
     * @param int $id_accessory_group
     * @return boolean
     */
    public static function deteleteProductAccessoriesByIdGroup($id_accessory_group)
    {
        $sql = 'DELETE `agp`, `agpl`
                FROM `' . _DB_PREFIX_ . 'accessory_group_product` agp
                LEFT JOIN `' . _DB_PREFIX_ . 'accessory_group_product_lang` agpl ON agpl.`id_accessory_group_product` = agp.`id_accessory_group_product`
                WHERE agp.`id_accessory_group` = ' . (int) $id_accessory_group;
        return Db::getInstance()->execute($sql);
    }
    
    /**
     * Delete accessory in the group when delete product attribute
     * @param int $id_product
     * @param int $id_product_attribute
     * @return boolean
     */
    public static function deteleteProductAccessoriesByIdProduct($id_product, $id_product_attribute)
    {
        $sql = 'DELETE `agp`, `agpl`
                FROM `' . _DB_PREFIX_ . 'accessory_group_product` agp
                LEFT JOIN `' . _DB_PREFIX_ . 'accessory_group_product_lang` agpl ON agpl.`id_accessory_group_product` = agp.`id_accessory_group_product`
                WHERE agp.`id_accessory` = ' . (int) $id_product.' AND agp.`id_product_attribute` = ' . (int) $id_product_attribute;
        return Db::getInstance()->execute($sql);
    }
    
    /**
     * Get required accessories customizations fields of product: 2: no customization, 0 has customization but not required, 1 has customization and required
     * @param array $id_accessories
     * @return int
     */
    public static function getCustomizationInfo($id_accessories)
    {
        $has_required_field = 2;
        if (Customization::isFeatureActive()) {
            $query = new DbQuery();
            $query->select('cf.`id_customization_field`, cf.`required`');
            $query->from('customization_field', 'cf');
            $query->where('cf.`id_product` IN (' . implode(',', $id_accessories) . ')');
            $query->orderBy('cf.`id_customization_field` ASC');
            $customizations = Db::getInstance()->executeS($query);
            if (!empty($customizations)) {
                $has_required_field = 0;
                foreach ($customizations as $customization) {
                    if ((int) $customization['required'] == 1) {
                        $has_required_field = 1;
                        break;
                    }
                }
            }
        }
        return $has_required_field;
    }
    
    /**
     * Truncate table contains accessories
     * @return boolean
     */
    public static function deteleteAllAccesories()
    {
        $sql1 = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'accessory_group_product`';
        $sql2 = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'accessory_group_product_lang`';
        return Db::getInstance()->execute($sql1) && Db::getInstance()->execute($sql2);
    }
    
    /**
     * Replace accessory
     * @param int $old_id_accessory
     * @param int $new_id_accessory
     * @return boolean
     */
    public static function replaceAccessory($old_id_accessory, $new_id_accessory)
    {
        return Db::getInstance()->update('accessory_group_product', array('id_accessory' => (int)$new_id_accessory, 'id_product_attribute' => 0), 'id_accessory = '.(int)$old_id_accessory);
    }
    
    /**
     * Get all added accessories
     * @return array
     */
    public static function getAllAccessories()
    {
        $query = new DbQuery();
        $query->select('DISTINCT `id_accessory`');
        $query->from('accessory_group_product');
        /*$query->where('id_accessory_group_product > 1000');
        $query->where('id_accessory_group_product < 2001');*/
        return Db::getInstance()->executeS($query);
    }
}
