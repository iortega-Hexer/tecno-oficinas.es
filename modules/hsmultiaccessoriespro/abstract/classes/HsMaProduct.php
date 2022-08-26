<?php
/**
 * HsMaProduct for Multi Accessories Override 2 function of class Product: getPriceStatic and priceCalculation
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaProduct extends Product
{
    /**
     * Contain array combination.
     *
     * @var array
     */
    protected static $hsma_product_combination = array();
    protected static $hsma_accessories_combination = array();

    /**
     * Get all combinations of product.
     *
     * @param int  $id_product
     * @param int  $id_shop
     * @param int  $id_lang
     * @param bool $hiden_attribute_name
     *
     * @return array
     *               <pre/>
     *               array (
     *               [id_product_attribute] => array (
     *               [id_product_attribute] => int
     *               [quantity] => int
     *               [combination] => string
     *               )
     *               [id_product_attribute] => array ()
     *               )
     */
    public static function getCombinations($id_product, $id_shop, $id_lang = null, $hiden_attribute_name = false)
    {
        $context = Context::getcontext();
        
        if (is_null($id_lang)) {
            $id_lang = (int) $context->language->id;
        }
        
        $id_cache = __CLASS__ . '::' . __FUNCTION__ . '_' . $id_product . '_' . $id_shop . '_' . $id_lang;
        if (isset(self::$hsma_product_combination[$id_cache]) && self::$hsma_product_combination[$id_cache] !== null) {
            return self::$hsma_product_combination[$id_cache];
        }
       
        $sql = 'SELECT
                        pa.`id_product_attribute`,
                        stock.`quantity` AS `stock_available`,
                        stock.`out_of_stock`,
                        GROUP_CONCAT( DISTINCT CONCAT(ag.`id_attribute_group`, ":" , a.`id_attribute`) SEPARATOR "-") AS `id_attributes`,';

        if ($hiden_attribute_name) {
            $sql .= ' GROUP_CONCAT( DISTINCT CONCAT(agl.`public_name`, " : " , al.`name`) ORDER BY agl.`public_name` SEPARATOR " - ") AS `name` ';
        } else {
            $sql .= ' GROUP_CONCAT( DISTINCT CONCAT( al.`name`) ORDER BY agl.`public_name`, al.`name` SEPARATOR " - ") AS `name` ';
        }
        
        $sql .=    '
                    FROM `'._DB_PREFIX_.'product_attribute` pa
                   '.Shop::addSqlAssociation('product_attribute', 'pa').'
                    LEFT JOIN
                            `'._DB_PREFIX_.'product_attribute_shop` pas
                        ON
                            pas.`id_product_attribute` = pa.`id_product_attribute`
                            AND pas.`id_shop` = '.(int) $id_shop.'

                    LEFT JOIN
                            `'._DB_PREFIX_.'product_attribute_combination` pac
                        ON
                            pac.`id_product_attribute` = pa.`id_product_attribute`
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute` a
                        ON
                            a.`id_attribute` = pac.`id_attribute`
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute_group` ag
                        ON
                            ag.`id_attribute_group` = a.`id_attribute_group`
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute_lang` al
                        ON
                            a.`id_attribute` = al.`id_attribute`
                            AND al.`id_lang` = '.(int) $id_lang.'
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute_group_lang` agl
                        ON
                            ag.`id_attribute_group` = agl.`id_attribute_group`
                            AND agl.`id_lang` = '.(int) $id_lang.'
                    '.Product::sqlStock('pa', false, $context->shop).'
                    WHERE
                            pa.`id_product` = '.(int) $id_product.'
                            AND stock.`id_product_attribute` = pa.`id_product_attribute`
                    GROUP BY
                            pa.`id_product`, pa.`id_product_attribute`
                    ORDER BY  `name` ASC';
        $combinations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        self::$hsma_product_combination[$id_cache] = self::formatCombinations($id_product, $combinations);
        return self::$hsma_product_combination[$id_cache];
    }
    
    /**
     *
     * @param array $id_products
     * @param int $id_shop
     * @param int $id_lang
     * @param boolean $hiden_attribute_name
     * @return array
     */
    public static function getAccessoriesCombinations($id_products, $id_shop, $id_lang = null, $hiden_attribute_name = false)
    {
        $context = Context::getcontext();
        if (is_null($id_lang)) {
            $id_lang = (int) $context->language->id;
        }
        $id_cache = __CLASS__ . '::' . __FUNCTION__ . '_' . implode('_', $id_products) . '_' . $id_shop . '_' . $id_lang;
        if (isset(self::$hsma_accessories_combination[$id_cache]) && self::$hsma_accessories_combination[$id_cache] !== null) {
            return self::$hsma_accessories_combination[$id_cache];
        }
        $sql = 'SELECT
                        pa.`id_product_attribute`,
                        pa.`id_product`,
                        stock.`quantity` AS `stock_available`,
                        stock.`out_of_stock`,
                        GROUP_CONCAT( DISTINCT CONCAT(ag.`id_attribute_group`, ":" , a.`id_attribute`) SEPARATOR "-") AS `id_attributes`,';

        if ($hiden_attribute_name) {
            $sql .= ' GROUP_CONCAT( DISTINCT CONCAT(agl.`public_name`, " : " , al.`name`) ORDER BY agl.`public_name` SEPARATOR " - ") AS `name` ';
        } else {
            $sql .= ' GROUP_CONCAT( DISTINCT CONCAT( al.`name`) ORDER BY agl.`public_name`, al.`name` SEPARATOR " - ") AS `name` ';
        }
        
        $sql .=    '
                    FROM `'._DB_PREFIX_.'product_attribute` pa
                   '.Shop::addSqlAssociation('product_attribute', 'pa').'
                    LEFT JOIN
                            `'._DB_PREFIX_.'product_attribute_shop` pas
                        ON
                            pas.`id_product_attribute` = pa.`id_product_attribute`
                            AND pas.`id_shop` = '.(int) $id_shop.'

                    LEFT JOIN
                            `'._DB_PREFIX_.'product_attribute_combination` pac
                        ON
                            pac.`id_product_attribute` = pa.`id_product_attribute`
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute` a
                        ON
                            a.`id_attribute` = pac.`id_attribute`
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute_group` ag
                        ON
                            ag.`id_attribute_group` = a.`id_attribute_group`
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute_lang` al
                        ON
                            a.`id_attribute` = al.`id_attribute`
                            AND al.`id_lang` = '.(int) $id_lang.'
                    LEFT JOIN
                            `'._DB_PREFIX_.'attribute_group_lang` agl
                        ON
                            ag.`id_attribute_group` = agl.`id_attribute_group`
                            AND agl.`id_lang` = '.(int) $id_lang.'
                    '.Product::sqlStock('pa', false, $context->shop).'
                    WHERE
                            pa.`id_product` IN (' . implode(', ', array_map('intval', $id_products)) . ')
                            AND stock.`id_product_attribute` = pa.`id_product_attribute`
                    GROUP BY
                            pa.`id_product`, pa.`id_product_attribute`
                    ORDER BY  `name` ASC';
        $combinations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        self::$hsma_accessories_combination[$id_cache] = self::formatAccessoriesCombinations($id_products, $combinations);
        return self::$hsma_accessories_combination[$id_cache];
    }
    
    /**
     *
     * @param array $id_products
     * @param array $combinations
     * @return array
     */
    protected static function formatAccessoriesCombinations(array $id_products, array $combinations)
    {
        if (empty($id_products) || empty($combinations)) {
            return array();
        }
        $formatted_accessories_combinations = array();
        require_once dirname(__FILE__) . '/HsMaImage.php';
        foreach ($id_products as $id_product) {
            $images = HsMaImage::getMaImages($id_product);
            $formatted_combinations = array();
            foreach ($combinations as $combination) {
                if ($id_product == $combination['id_product']) {
                    if (!empty($images[$combination['id_product_attribute']])) {
                        $combination['id_image'] = $images[$combination['id_product_attribute']];
                    } else {
                        $combination['id_image'] = null;
                    }
                    $formatted_combinations[(int)$combination['id_product_attribute']] = $combination;
                }
            }
            $formatted_accessories_combinations[$id_product] = $formatted_combinations;
        }
        return $formatted_accessories_combinations;
    }
    protected static function formatCombinations($id_product, array $combinations)
    {
        if (empty($combinations)) {
            return array();
        }
        require_once dirname(__FILE__) . '/HsMaImage.php';
        $images = HsMaImage::getMaImages($id_product);
        $formatted_combinations = array();
        foreach ($combinations as $combination) {
            if (!empty($images[$combination['id_product_attribute']])) {
                $combination['id_image'] = $images[$combination['id_product_attribute']];
            } else {
                $combination['id_image'] = null;
            }
            $formatted_combinations[(int)$combination['id_product_attribute']] = $combination;
        }
        return $formatted_combinations;
    }

    /**
     * Get stock status of product.
     *
     * @param int  $id_product
     * @param int  $id_product_attribute
     * @param Shop $shop
     *
     * @return array
     *               array(<pre>
     *               'out_of_stock' => int,
     *               'quantity' => int
     *               );</pre>
     */
    public static function getStockStatus($id_product, $id_product_attribute, $shop)
    {
        $sql = 'SELECT stock.`out_of_stock`, IFNULL(stock.`quantity`, 0) as `quantity`
				FROM `'._DB_PREFIX_.'product` p
				'.Product::sqlStock('p', (int) $id_product_attribute, true, $shop).'
				WHERE p.`id_product` = '.(int) $id_product;

        return Db::getInstance()->getRow($sql);
    }
    
    /**
     *
     * @return boolean
     */
    public function deleteAccessories()
    {
        return Db::getInstance()->execute('DELETE 
                                                `'._DB_PREFIX_.'accessory_group_product`, `'._DB_PREFIX_.'accessory_group_product_lang`
                                            FROM
                                                `'._DB_PREFIX_.'accessory_group_product`
                                                INNER JOIN 
                                                    `'._DB_PREFIX_.'accessory_group_product_lang`  ON `'._DB_PREFIX_.'accessory_group_product`.`id_accessory_group_product` = `'._DB_PREFIX_.'accessory_group_product_lang`.`id_accessory_group_product`
                                            WHERE
                                                `'._DB_PREFIX_.'accessory_group_product`.`id_product` = '.(int) $this->id.' OR `'._DB_PREFIX_.'accessory_group_product`.`id_accessory` = '.(int) $this->id);
    }
    
    /**
     * @see http://php.net/manual/en/function.array-diff.php
     * @param HsMaProduct $from_product
     * @param HsMaProduct $to_product
     * @return array
     * array(
     * <pre>
     *  HsAccessoriesGroupProductAbstract,
     *  HsAccessoriesGroupProductAbstract,
     *  .................................
     * )<pre />
     */
    public static function getAccessoriesDiff($from_product, $to_product)
    {
        $sql = new DbQuery();
        $sql->from('accessory_group_product', 'agp');
        $sql->leftJoin('accessory_group_product_lang', 'agpl', 'agp.`id_accessory_group_product` = agpl.`id_accessory_group_product`');
        $sql->where('agp.`id_product` = '.(int) $from_product->id.' OR agp.`id_product` = '.(int) $to_product->id); // get all accessories in from product and to product
        $sql->where('agp.`id_accessory` <> '.(int) $to_product->id); // removing accessories are the same to product
        $sql->groupBy('agp.`id_accessory`, agpl.`id_lang`, agp.`id_accessory_group`');
        $sql->having('COUNT(agp.`id_accessory`) <= 1'); // removing accessories are the same from product and to product
        $sql->having('agp.`id_product` <> '.(int) $to_product->id); // removing accessories of to product
        $accessories_diff = Db::getInstance()->executeS($sql);
        return self::hydrateCollection('HsAccessoriesGroupProductAbstract', $accessories_diff);
    }
    
    /**
     * Get default products accessories
     * @return array
     */
    public static function getDefaultPsProductsAccessories()
    {
        $sql = 'SELECT DISTINCT `id_product_1`, GROUP_CONCAT( DISTINCT CONCAT(`id_product_2`) SEPARATOR "_") AS `id_accessories` FROM `'._DB_PREFIX_.'accessory` GROUP BY `id_product_1`';
        return Db::getInstance()->executeS($sql);
    }
}
