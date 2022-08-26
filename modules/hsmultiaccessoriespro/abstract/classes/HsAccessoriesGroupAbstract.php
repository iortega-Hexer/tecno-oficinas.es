<?php
/**
 * An abstract model accessories group of the module
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsAccessoriesGroupAbstract extends ObjectModel
{
    /**
     * Name of group.
     *
     * @var varchar
     */
    public $name;

    /**
     * id of accessory group.
     *
     * @var int(10)
     */
    public $id_accessory_group;

    /**
     * group active or not.
     *
     * @var tinyint(1)
     */
    public $active;

    /**
     * position accessory group.
     *
     * @var tinyint(1)
     */
    public $position;

    /**
     * display style of group.
     *
     * @var int // 0: Check-box, 1: Drop-down, 2: Radio, 3: use default of setting
     */
    public $display_style;
    
    /**
     * Buy main product & accessories together based on accessory group
     * @var int // 0: No, 1: Yes, 2: Use default
     */
    public $buy_together;
    
    /**
     * Show or hide group name at the front end.
     * @var int
     */
    public $show_name;
    
    /**
     * Expand or collapse a group accessory
     * @var int
     */
    public $collapse_expand;

    /**
     * Cache store status selected of accessories.
     *
     * @var int
     */
    protected static $cache_selected_accessories = array();

    /**
     * define all field of Group.
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'accessory_group',
        'primary' => 'id_accessory_group',
        'multilang' => true,
        'fields' => array(
            'active' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_style' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'show_name' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'collapse_expand' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'buy_together' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
        ),
    );

    /**
     * Add new accessory group.
     *
     * @see parent::add()
     *
     * @param bool $autodate
     * @param bool $nullValues
     *
     * @return type
     */
    public function add($autodate = true, $nullValues = false)
    {
        if ($this->position <= 0) {
            $this->position = self::getHeighestPosition() + 1;
        }

        return parent::add($autodate, $nullValues);
    }

    /**
     * Get accessory groups.
     *
     * @param int  $id_lang language id
     * @param bool $active  status of groups (1 = get active, 0 = get not active , null = get all)
     *
     * @return array
     *               <pre>
     *               array (
     *               0 => array (
     *               'id_accessory_group' => int,
     *               'name' => varchar
     *               ),
     *               1 => array (
     *               'id_accessory_group' => int,
     *               'name' => varchar
     *               )
     *               )
     */
    public static function getGroups($id_lang, $active = null)
    {
        $sql_where = array();
        $sql_where[] = '`agl`.`id_lang` = '.(int) $id_lang;
        if ($active !== null) {
            $sql_where[] = '`ag`.`active` = '.(int) $active;
        }
        $sql = 'SELECT  
                    ag.`id_accessory_group`,
                    ag.`show_name`,
                    ag.`collapse_expand`,
                    ag.`buy_together`,
                    IF(ag.`display_style` = '.(int)HsMaDisplayStyle::USE_DEFAULT.', '.(int)Configuration::get('HSMA_DISPLAY_STYLE').' , ag.`display_style`) as display_style,
                    agl.`name`
                FROM
                    `'._DB_PREFIX_.'accessory_group` ag
                LEFT JOIN
                    `'._DB_PREFIX_.'accessory_group_lang` `agl` ON agl.`id_accessory_group` = ag.`id_accessory_group`
                WHERE
                    '.implode(' AND ', $sql_where).'
                ORDER BY ag.`position` ASC';

        $cache_id = 'HsAccessoriesGroupAbstract::getGroups_'.md5($sql);

        if (!Cache::isStored($cache_id)) {
            $groups = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $groups);
        } else {
            $groups = Cache::retrieve($cache_id);
        }
        return $groups;
    }

    /**
     * Get all accessories of one or more groups.
     *
     * @param array  $id_groups            List of group ids
     * @param array  $id_products          Looking for n specific products
     * @param bolean $active               Looking for active product
     * @param bool   $include_out_of_stock Get products with or without stock
     * @param bool   $buy_together_check   check if buy together product or not
     *
     * @return array
     *               <pre>
     *               array(
     *               [id_group] => Array(
     *               [0] => Array (
     *               [id_accessory_group] => int
     *               [short_name] => varchar
     *               [name] => varchar
     *               [id_product] => int
     *               [id_accessory] => int
     *               ),
     *               [1] => Array (
     *               [id_accessory_group] => int
     *               [short_name] => varchar
     *               [name] => varchar
     *               [id_product] => int
     *               [id_accessory] => int
     *               )
     *               ),
     *               [id_group] => Array(
     *               [0] => Array (
     *               [id_accessory_group] => int
     *               [short_name] => varchar
     *               [name] => varchar
     *               [id_product] => int
     *               [id_accessory] => int
     *               ),
     *               [1] => Array (
     *               [id_accessory_group] => int
     *               [short_name] => varchar
     *               [name] => varchar
     *               [id_product] => int
     *               [id_accessory] => int,
     *               [combinations] = array(
     *               id_product_attribute  => array(
     *               'id_product_attribute' => int,
     *               'stock_available' => int,
     *               'out_of_stock' => int,
     *               'id_image' => int,
     *               'combination' => varchar,
     *               'image' => varchar,
     *               ),
     *               )
     *               )
     *               )
     */
    public static function getAccessoriesByGroups(array $id_groups, array $id_products, $active = false, $id_lang = null, $include_out_of_stock = true, $buy_together_check = false)
    {
        $context = Context::getcontext();
        $records = self::getAccessories($id_groups, $id_products, $active, $id_lang);
        $use_tax = Product::getTaxCalculationMethod() ? false : true;
        $accessories_groups = array();
        if (!empty($records)) {
            // this case apply in product page only
            if ($buy_together_check) {
                $buy_together = HsMaProductSetting::getBuyTogetherCurrentValue($id_products[0]);
                if (!empty($buy_together)) {
                    $buy_together_groups = $buy_together[$id_products[0]];
                }
            }
            $accessories_combinations = HsMaProduct::getAccessoriesCombinations(self::getIdAccessories($records), $context->shop->id, $context->language->id);
            foreach ($records as $key => &$record) {
                $specific_price_output = null;
                $record['old_price'] = Product::getPriceStatic($record['id_product'], $use_tax, $record['id_product_attribute'], (int)_PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, $context);
                $accessory_names = HsAccessoriesGroupProduct::getAccessoryName($record['id_accessory_group_product']);
                $record['name'] = (!empty($id_lang) ? $accessory_names[$id_lang] : $accessory_names);
                $record['combinations'] = array();
                if (!empty($accessories_combinations) && !empty($accessories_combinations[$record['id_product']])) {
                    $record['combinations'] = $accessories_combinations[$record['id_product']];
                }
                self::setAvailableStock($record);
                if ($buy_together_check) {
                    $record['is_available_buy_together'] = self::isAvailableBuyTogether($buy_together_groups[$record['id_accessory_group']], $record['required'], $record['is_available_for_order'], $record['id_accessory_group'], $record['id_product']);
                } else {
                    $record['is_available_buy_together'] = false;
                }
                if (!empty($record['combinations']) && !$include_out_of_stock) {
                    foreach ($record['combinations'] as $combination) {
                        if ($combination['stock_available'] == 0 || $combination['stock_available'] < $record['default_quantity']) {
                            unset($record['combinations'][$combination['id_product_attribute']]);
                        }
                    }
                }
                if (empty($record['combinations'])) {
                    $id_image = (int) $record['id_image'];
                } else {
                    foreach ($record['combinations'] as &$new_combination) {
                        $new_combination['image'] = self::getCombinationImage($record, $new_combination);
                    }
                    $default_id_product_attribute = (int) $record['id_product_attribute'];
                    if ($default_id_product_attribute == 0) {
                        $default_id_product_attribute = (int) Product::getDefaultAttribute($record['id_accessory'], 1);
                    }
                    $id_image = (int) $record['combinations'][$default_id_product_attribute]['id_image'];
                }
                $record['image'] = HsMaLink::getProductImageLink($record['link_rewrite'], $id_image, HsMaImageType::getFormatedNameByPsVersion('small'));
                $record['image_fancybox'] = HsMaLink::getProductImageLink($record['link_rewrite'], $id_image, Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX'));
                $record['cart_rule'] = HsMaCartRule::getCartRule($record, $id_products[0]);
                $record['final_price'] = self::getFinalPrice($record['old_price'], $record['cart_rule']);
                $record['is_stock_available'] = (int) self::isStockAvailable($record);
                if (!isset($accessories_groups[$record['id_accessory_group']])) {
                    $accessories_groups[$record['id_accessory_group']] = array();
                }
                if (!$include_out_of_stock) {
                    $record['qty'] = Product::getQuantity($record['id_accessory']);
                    if ($record['qty'] == 0 || !$record['is_stock_available']) {
                        unset($records[$key]);
                    } else {
                        $accessories_groups[$record['id_accessory_group']][] = $record;
                    }
                } else {
                    $accessories_groups[$record['id_accessory_group']][] = $record;
                }
            }
        }
        return $accessories_groups;
    }
    
    /**
     *
     * @param array $accessories
     * @return array
     */
    protected static function getIdAccessories($accessories)
    {
        $id_accessories = array();
        if (!empty($accessories)) {
            foreach ($accessories as $accessory) {
                $id_accessories[] = $accessory['id_product'];
            }
        }
        return $id_accessories;
    }

    /**
     *
     * @param array $accessory
     * @param array $combination
     * @return type
     */
    protected static function getCombinationImage(array $accessory, array &$combination)
    {
        // In case product has combination, but combination's image does not check.
        if ((int) $combination['id_image'] === 0) {
            $combination['id_image'] = $accessory['id_image'];
        }
        return HsMaLink::getProductImageLink($accessory['link_rewrite'], $combination['id_image'], HsMaImageType::getFormatedNameByPsVersion('small'));
    }
    
    /**
     *
     * @param float $price
     * @param array $cart_rule
     * @return float Final price
     */
    public static function getFinalPrice($price, $cart_rule)
    {
        $final_price = $price;
        if (!empty($cart_rule)) {
            if ($cart_rule['reduction_percent'] > 0) {
                $final_price = $price - ($price * $cart_rule['reduction_percent']) / 100;
            } else {
                $final_price = $price - $cart_rule['reduction_amount'];
            }
        }
        return $final_price;
    }
    
    /**
     *
     * @param array $id_groups
     * @param array $id_products
     * @param boolean $active
     * @param int $id_lang
     * @return string
     */
    protected static function buildQuery(array $id_groups, array $id_products, $active = false, $id_lang = null)
    {
        $context = Context::getcontext();
        $sql_where = array();
        if (!empty($id_groups)) {
            $sql_where[] = 'apg.`id_accessory_group` IN ('.implode(',', $id_groups).')';
        }
        $sql_where[] = 'apg.`id_product` IN('.implode(',', $id_products).')';
        $sql_where[] = 'p.`id_product` NOT IN ('.implode(',', $id_products).')';
        if (!Configuration::get('HSMA_SHOW_NW_VISIBILITY_PRODUCTS')) {
            $sql_where[] = 'p.`visibility` != \'none\'';
        }
        $sql = 'SELECT
                    DISTINCT apg.`id_accessory_group`,
                    apg.`id_accessory`,
                    apg.`default_quantity`,
                    apg.`min_quantity`,
                    apg.`required`,
                    apg.`position`,
                    pl.`link_rewrite`,
                    pl.`description_short`,
                    pl.`available_later`,
                    p.`id_product` AS `id_product`,
                    p.`is_virtual`,
                    stock.`quantity` AS `stock_available`,
                    stock.`out_of_stock`,
                    apg.`id_accessory_group_product`,		
                    apg.`id_product_attribute`,
                    i.`id_image`,
                    IF(`is`.`id_image`, `is`.`id_image`, i.`id_image`) `id_image`                    
            FROM
                    `'._DB_PREFIX_.'accessory_group_product` apg ';
        if ($active) {
            $sql .= 'INNER JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = apg.`id_accessory` AND p.active =1)';
        } else {
            $sql .= 'LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = apg.`id_accessory`';
        }
        $sql .= 'LEFT JOIN
                        `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = apg.`id_accessory` AND pl.`id_shop` = '.(int) $context->shop->id . (!empty($id_lang) ? ' AND pl.`id_lang` = '.(int) $id_lang : '').')
                LEFT JOIN
                        `'._DB_PREFIX_.'accessory_group_product_lang` agpl ON (agpl.`id_accessory_group_product` = apg.`id_accessory_group_product` '.(!empty($id_lang) ? ' AND agpl.`id_lang` = '.(int) $id_lang : '').')
                LEFT JOIN
                        `'._DB_PREFIX_.'image` i ON (i.`id_product` = apg.`id_accessory` AND `i`.`cover` = 1)
                LEFT JOIN
                   `'._DB_PREFIX_.'image_shop` `is` ON ( `is`.`id_image` = i.`id_image` AND `is`.id_shop = '.(int) $context->shop->id.' AND `is`.`cover` = 1)
                LEFT JOIN
                                `'._DB_PREFIX_.'image_lang` il ON (il.`id_image` = i.`id_image` '.(!empty($id_lang) ? ' AND il.`id_lang` = '.(int) $id_lang : '').')
                '.Product::sqlStock('p', 0, false, $context->shop).'
                '.Shop::addSqlAssociation('product', 'p').'    
                WHERE
                        '.implode(' AND ', $sql_where).'
                GROUP BY
                        apg.`id_accessory_group`,
                        apg.`id_accessory`
                ORDER BY apg.`position` ASC';
        return $sql;
    }
    
    /**
     *
     * @param array $product_row a current accessory record
     * <pre>
     * array(
     *   'id_accessory_group' => int,
     *   'id_accessory' => int,
     *   'default_quantity' => int,
     *   'required' => int,
     *   'position' => int,
     *   'link_rewrite' => string,
     *   'description_short' => html,
     *   'available_later' => string,
     *   'id_product' => int,
     *   'is_virtual' => int,
     *   'stock_available' => int,
     *   'out_of_stock' => int,
     *   'id_accessory_group_product' => int,
     *   'id_product_attribute' => int,
     *   'id_image' => int,
     *   'name' => string,
     *   'combinations' => array // @see HsMaProduct::getCombinations()
     * )
     */
    protected static function setAvailableStock(array &$product_row)
    {
        if ($product_row['is_virtual']) {
            $product_row['is_available_when_out_of_stock'] = 0;
            $product_row['is_available_for_order'] = 0;
        } else {
            $product_row['is_available_when_out_of_stock'] = (Product::isAvailableWhenOutOfStock($product_row['out_of_stock']) && (int) $product_row['stock_available'] < (int) $product_row['default_quantity']) ? 1 : 0;
            $product_row['is_available_for_order'] = (!Product::isAvailableWhenOutOfStock($product_row['out_of_stock']) && (int) $product_row['stock_available'] < (int) $product_row['default_quantity']) ? 1 : 0;
        }
    }

    /**
     * Count accessories by id product and id group.
     *
     * @param int $id_product
     * @param array $id_groups
     *
     * @return int
     */
    public static function countAccessories($id_product, $id_groups = array())
    {
        $query = new DbQuery();
        $query->select('COUNT(apg.`id_accessory_group_product`)');
        $query->leftJoin('product', 'p', 'p.`id_product` = apg.`id_accessory`');
        $query->from('accessory_group_product', 'apg');
        $query->where('apg.`id_product` = ' . (int) $id_product);
        if (!Configuration::get('HSMA_SHOW_NW_VISIBILITY_PRODUCTS')) {
            $query->where('p.`visibility` != \'none\'');
        }
        if (!empty($id_groups)) {
            $query->where('apg.`id_accessory_group` IN (' . implode(', ', array_map('intval', $id_groups)) . ')');
        }
        return Db::getInstance()->getValue($query);
    }

    /**
     * Check available buy accessory & main product together.
     *
     * @param int $buy_together           // Product setting buy together
     * @param int $required               // Customer checked accessory & product buy together
     * @param int $is_available_for_order // Allow customer to order if product is out of stock.
     * @param int $id_accessory_group
     *
     * @return bool
     */
    public static function isAvailableBuyTogether($buy_together, $required, $is_available_for_order, $id_accessory_group, $id_product)
    {
        $is_available_buy_together = false;
        switch ($buy_together) {
            case HsMaProductSetting::BUY_TOGETHER_YES:
                if (empty(self::$cache_selected_accessories[$id_accessory_group]) && empty(self::$cache_selected_accessories[$id_accessory_group][$id_product]) && !$is_available_for_order) {
                    $is_available_buy_together = true;
                    self::$cache_selected_accessories[$id_accessory_group][$id_product] = 1;
                }
                break;

            case HsMaProductSetting::BUY_TOGETHER_REQUIRED:
                if (!$is_available_for_order) {
                    $is_available_buy_together = (int) $required;
                }
                break;

            default:
                break;
        }

        return $is_available_buy_together;
    }

    /**
     * Move a group accessories.
     *
     * @param bool $way      Up (1)  or Down (0)
     * @param int  $position
     * return boolean Update result
     */
    public function updatePosition($way, $position)
    {
        $this->updateDefaultPositionAccessoryGroups();
        $sql1 = 'UPDATE `'._DB_PREFIX_.'accessory_group`
                    SET `position`= `position` '.($way ? '- 1' : '+ 1').'
                    WHERE `position` '.($way ?'> '.(int) $this->position.' AND `position` <= '.(int) $position : '< '.(int) $this->position.' AND `position` >= '.(int) $position);
        $sql2 = 'UPDATE `'._DB_PREFIX_.'accessory_group`
                    SET `position` = '.(int) $position.'
                    WHERE `id_accessory_group`='.(int) $this->id;
        return (Db::getInstance()->execute($sql1) && Db::getInstance()->execute($sql2));
    }

    /**
     * Update default position for accessory group.
     *
     * @return bool
     */
    protected function updateDefaultPositionAccessoryGroups()
    {
        $query = new DbQuery();
        $query->select('`id_accessory_group`');
        $query->from('accessory_group');
        $query->orderBy('`position` ASC');
        $accessory_groups = Db::getInstance()->executeS($query);
        $success = array();
        if (!empty($accessory_groups)) {
            foreach ($accessory_groups as $index => $accessory_group) {
                $success[] = Db::getInstance()->update('accessory_group', array('position' => (int)$index), 'id_accessory_group = '.(int)$accessory_group['id_accessory_group']);
            }
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * get higher position
     * Get the higher accessory group  position.
     *
     * @return int
     */
    public static function getHeighestPosition()
    {
        $sql = 'SELECT MAX(`position`)
				FROM `'._DB_PREFIX_.'accessory_group`';
        $position = DB::getInstance()->getValue($sql);

        return (is_numeric($position)) ? $position : -1;
    }
    
    /**
     *
     * @param array $id_products
     * @param int $id_lang
     * @return boolean
     */
    public static function haveAccessories(array $id_products, $id_lang)
    {
        $id_groups = self::getIdGroups($id_lang, true);
        $have_accessories = false;
        if (!empty($id_groups)) {
            $accessories = self::getAccessories($id_groups, $id_products, true, $id_lang);
            if (!empty($accessories)) {
                $have_accessories = true;
            }
        }
        return $have_accessories;
    }
    
    /**
     * Get accessories for count, list
     * @param array $id_groups
     * @param array $id_products
     * @param boolean $active
     * @param int $id_lang
     * @return array
     */
    protected static function getAccessories($id_groups, $id_products, $active, $id_lang)
    {
        $string_id_group = implode('_', $id_groups);
        $string_id_product = implode('_', $id_products);
        $cache_id = __CLASS__ . '::' . __FUNCTION__ . '_' . $string_id_group . '_' . $string_id_product . '_' . $active.'_' . $id_lang;
        if (!Cache::isStored($cache_id)) {
            $sql = self::buildQuery($id_groups, $id_products, $active, $id_lang);
            $accessories = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $accessories);
        } else {
            $accessories = Cache::retrieve($cache_id);
        }
        return $accessories;
    }

    /**
     *
     * @param int $id_lang
     * @return array
     * <pre>
     *  Array (
     *      [0] => int,
     *      [1] => int,
     * )
     */
    public static function getIdGroups($id_lang, $active = null)
    {
        $groups    = self::getGroups($id_lang, $active);
        $id_groups = array();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $id_groups[] = $group['id_accessory_group'];
            }
        }
        return $id_groups;
    }
    /**
     * Checking product is out of stock.
     * @param array $product
     * @return boolean
     */
    public static function isStockAvailable($product)
    {
        $flag = false;
        if (!empty($product)) {
            if (Product::isAvailableWhenOutOfStock($product['out_of_stock']) || (!Product::isAvailableWhenOutOfStock($product['out_of_stock']) && $product['stock_available'] >= (int) $product['default_quantity'])) {
                $flag = true;
            }
        }
        return $flag;
    }
    
    /**
     * Get all accessories of one group.
     * @param int $id_group
     * @param array $id_products
     * @param boolean $active
     * @param int $id_lang
     * @param boolean $include_out_of_stock
     * @return array
     */
    public static function getAccessoriesByIdGroup($id_group, array $id_products, $active = false, $id_lang = null, $include_out_of_stock = true)
    {
        $context = Context::getcontext();
        if (is_null($id_lang)) {
            $id_lang = (int) $context->language->id;
        }
        $sql = self::buildQueryGetAccessories($id_group, $id_products, $active, $id_lang);
        $string_id_product = implode('_', $id_products);
        $id_shop = (int) $context->shop->id;
        $cache_id = __CLASS__ . '::' . __FUNCTION__ . '_' . $id_group . '_' . $string_id_product . '_' . $active.'_' . $id_lang.'_' . $id_shop;
        if (!Cache::isStored($cache_id)) {
            $records = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $records);
        } else {
            $records = Cache::retrieve($cache_id);
        }

        $use_tax = Product::getTaxCalculationMethod() ? false : true;
        $accessories_groups = array();
        if (!empty($records)) {
            foreach ($records as $key => &$record) {
                $specific_price_output = null;
                $record['old_price'] = Product::getPriceStatic($record['id_accessory'], $use_tax, $record['id_product_attribute'], (int)_PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, $context);
                $accessory_names = HsAccessoriesGroupProduct::getAccessoryName($record['id_accessory_group_product']);
                $record['name'] = (!empty($id_lang) ? $accessory_names[$id_lang] : $accessory_names);
                $record['combinations'] = HsMaProduct::getCombinations($record['id_accessory'], $context->shop->id, $id_lang);
                self::setAvailableStock($record);
                if (empty($record['combinations'])) {
                    $record['image'] = HsMaLink::getProductImageLink($record['link_rewrite'], $record['id_image'], HsMaImageType::getFormatedNameByPsVersion('small'));
                } else {
                    $default_id_product_attribute = (int) Product::getDefaultAttribute($record['id_accessory']);
                    foreach ($record['combinations'] as &$combination) {
                        $combination['image'] = self::getCombinationImage($record, $combination);
                    }
                    $record['image'] = HsMaLink::getProductImageLink($record['link_rewrite'], $record['combinations'][$default_id_product_attribute]['id_image'], HsMaImageType::getFormatedNameByPsVersion('small'));
                }
               
                $record['cart_rule'] = HsMaCartRule::getCartRule($record, $record['id_product']);
                $record['final_price'] = self::getFinalPrice($record['old_price'], $record['cart_rule']);
                $record['is_stock_available'] = (int) self::isStockAvailable($record);
                if (!isset($accessories_groups[$record['id_accessory_group']])) {
                    $accessories_groups[$record['id_accessory_group']] = array();
                }
                if (!$include_out_of_stock) {
                    $record['qty'] = Product::getQuantity($record['id_accessory']);
                    if ($record['qty'] == 0) {
                        unset($records[$key]);
                    }
                } else {
                    $accessories_groups[$record['id_accessory_group']][] = $record;
                }
            }
        }
        return $accessories_groups;
    }

    /**
     *
     * @param int $id_group
     * @param array $id_products
     * @param boolean $active
     * @param int $id_lang
     * @return string
     */
    protected static function buildQueryGetAccessories($id_group, array $id_products, $active = false, $id_lang = null)
    {
        $context = Context::getcontext();
        $sql_where = array();
        if (!empty($id_group)) {
            $sql_where[] = 'apg.`id_accessory_group` = '. (int) $id_group;
        }
        $sql_where[] = 'apg.`id_product` IN('.implode(',', $id_products).')';
        $sql = 'SELECT
                    DISTINCT apg.`id_accessory_group`,
                    apg.`id_accessory`,
                    apg.`default_quantity`,
                    apg.`min_quantity`,
                    apg.`required`,
                    apg.`position`,
                    pl.`link_rewrite`,
                    pl.`description_short`,
                    pl.`available_later`,
                    apg.`id_product` AS `id_product`,
                    p.`is_virtual`,
                    p.`reference`,
                    stock.`quantity` AS `stock_available`,
                    stock.`out_of_stock`,
                    apg.`id_accessory_group_product`,		
                    apg.`id_product_attribute`,
                    i.`id_image`,
                    IF(`is`.`id_image`, `is`.`id_image`, i.`id_image`) `id_image`                 
            FROM `'._DB_PREFIX_.'accessory_group_product` apg ';
        if ($active) {
            $sql .= 'INNER JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = apg.`id_accessory` AND p.active =1)';
        } else {
            $sql .= 'LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = apg.`id_accessory`';
        }
        $sql .= 'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = apg.`id_accessory` AND pl.`id_shop` = '.(int) $context->shop->id.(!empty($id_lang) ? ' AND pl.`id_lang` = '.(int) $id_lang : '').')
                LEFT JOIN `'._DB_PREFIX_.'accessory_group_product_lang` agpl ON (agpl.`id_accessory_group_product` = apg.`id_accessory_group_product` '.(!empty($id_lang) ? ' AND agpl.`id_lang` = '.(int) $id_lang : '').')
                LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = apg.`id_accessory` AND `i`.`cover` = 1)
                LEFT JOIN `'._DB_PREFIX_.'image_shop` `is` ON ( `is`.`id_image` = i.`id_image` AND `is`.id_shop = '.(int) $context->shop->id.' AND `is`.`cover` = 1)
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (il.`id_image` = i.`id_image` '.(!empty($id_lang) ? ' AND il.`id_lang` = '.(int) $id_lang : '').')
                '.Product::sqlStock('p', 0, false, $context->shop).'
                '.Shop::addSqlAssociation('product', 'p').'    
                WHERE '.implode(' AND ', $sql_where).'
                ORDER BY apg.`position` ASC';
        return $sql;
    }
    
    /**
     * Get accessory group.
     * @param int $id_group
     * @param int $id_lang
     * @param boolean $active
     * @return array
     */
    public static function getGroupById($id_group, $id_lang, $active = null)
    {
        $sql_where = array();
        $sql_where[] = '`agl`.`id_accessory_group` = '.(int) $id_group;
        $sql_where[] = '`agl`.`id_lang` = '.(int) $id_lang;
        if ($active !== null) {
            $sql_where[] = '`ag`.`active` = '.(int) $active;
        }
        $sql = 'SELECT  
                    ag.`id_accessory_group`,
                    agl.`name`
                FROM
                    `'._DB_PREFIX_.'accessory_group` ag
                LEFT JOIN
                    `'._DB_PREFIX_.'accessory_group_lang` `agl` ON agl.`id_accessory_group` = ag.`id_accessory_group`
                WHERE
                    '.implode(' AND ', $sql_where).'
                ORDER BY ag.`position` ASC';

        $cache_id = 'HsAccessoriesGroupAbstract::getGroupById_'.md5($sql);
        if (!Cache::isStored($cache_id)) {
            $group = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $group);
        } else {
            $group = Cache::retrieve($cache_id);
        }
        return $group;
    }
    
    /**
     * Get the setting Buy main product & accessories of all groups.
     * @param boolean $active
     * @return array
     */
    public static function getSettingBuyTogethers($active = null)
    {
        $sql = new DbQuery();
        $sql->select('`buy_together`,`id_accessory_group`');
        $sql->from('accessory_group');
        if ($active !== null) {
            $sql->where('`active` = '.(int) $active);
        }
        $cache_id = __CLASS__ . '::' . __FUNCTION__ . '_' .md5($sql);
        if (!Cache::isStored($cache_id)) {
            $groups_buy_together = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $groups_buy_together);
        } else {
            $groups_buy_together = Cache::retrieve($cache_id);
        }
        return $groups_buy_together;
    }
}
