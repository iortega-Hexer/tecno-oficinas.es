<?php
/**
 * Multi Accessories for Prestashop
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaSearch extends Search
{
    /**
     * Search accessories.
     *
     * @param string $exclude_ids
     * @param string $keyword
     * @param string $exclude_virtuals
     * @param string $exclude_packs
     *
     * @return array
     *               array<pre>
     *               (
     *               [0] => array
     *               (
     *               [id_product] => 44
     *               [link_rewrite] => paypal-fee
     *               [reference] =>
     *               [name] => Paypal fee
     *               )
     *               [1] => array
     *               (
     *               [id_product] => 47
     *               [link_rewrite] => faded-short-sleeve-tshirts
     *               [reference] => demo_1
     *               [name] => Faded Short Sleeve T-shirts1
     *               )
     *               ...
     *               )</pre>
     */
    public static function searchAccessories($exclude_ids, $keyword, $exclude_virtuals, $exclude_packs)
    {
        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '.(int) Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('pl').')
		LEFT JOIN `'._DB_PREFIX_.'product_shop` ps ON (ps.`id_product` = p.`id_product`)
		WHERE (pl.`name` LIKE \'%'.pSQL($keyword).'%\' OR p.`reference` LIKE \'%'.pSQL($keyword).'%\')'.
            (!empty($exclude_ids) ? ' AND p.`id_product` NOT IN ('.$exclude_ids.') ' : ' ').
            ($exclude_virtuals ? 'AND p.`id_product` NOT IN (SELECT pd.`id_product` FROM `'._DB_PREFIX_.'product_download` pd WHERE (pd.`id_product` = p.`id_product`))' : '').
            ($exclude_packs ? 'AND (p.`cache_is_pack` IS NULL OR p.`cache_is_pack` = 0)' : '').
            'AND ps.`id_shop` IN ('.implode(',', Shop::getContextListShopID()).') GROUP BY p.`id_product`';

        return Db::getInstance()->executeS($sql);
    }
    
    /**
     * Get product by id categories
     * @param array $id_categories
     * @param Context $context
     * @return array
     */
    public static function searchProductsByCategories(array $id_categories = array(), Context $context = null)
    {
        $id_lang = (int) $context->language->id;
        $id_shop = (int) $context->shop->id;
        $cache_key = __CLASS__ . __FUNCTION__ . implode('_', $id_categories).'_'.$id_lang.'_'.$id_shop;
        if (!Cache::isStored($cache_key)) {
            if (!$context) {
                $context = Context::getcontext();
            }
            $eligible_products = self::getIdProducts($id_categories);
            if (empty($eligible_products)) {
                return array();
            }
            $sql = 'SELECT p.`id_product`,
                            p.`reference`,
                            pl.`link_rewrite`,
                            product_shop.`id_shop`,
                            pl.`name` AS `pname`,
                            i.`id_image`
                    FROM `'._DB_PREFIX_.'product` p
                    '.Shop::addSqlAssociation('product', 'p').'
                    INNER JOIN `'._DB_PREFIX_.'product_lang` pl
                    ON p.`id_product` = pl.`id_product`
                        AND pl.`id_lang` = '.(int) $id_lang.'
                    LEFT JOIN `'._DB_PREFIX_.'image` i
                        ON (i.`id_product` = p.`id_product` AND `i`.`cover` = 1)
                    LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                        ON (image_shop.`id_image` = i.`id_image`
                            AND image_shop.cover=1 AND image_shop.id_shop='.(int) $id_shop.')
                    LEFT JOIN `'._DB_PREFIX_.'image_lang` il
                        ON (image_shop.`id_image` = il.`id_image`
                            AND il.`id_lang` = '.(int) $id_lang.')
                    WHERE p.`id_product` IN ('.implode(',', $eligible_products).')
                    GROUP BY p.`id_product`
                    ORDER BY `pname` ASC';
            $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $use_tax = Configuration::get('PS_TAX') && !Product::getTaxCalculationMethod((int) $context->cookie->id_customer);
            foreach ($products as &$product) {
                $product['price'] = Product::getPriceStatic((int) $product['id_product'], $use_tax, null, 2);
            }
            Cache::store($cache_key, $products);
        } else {
            $products = Cache::retrieve($cache_key);
        }
        return $products;
    }
    
    /**
     * Get id products by id categories
     * @param array $id_categories
     * @return array
     */
    public static function getIdProducts(array $id_categories = array())
    {
        $cache_key = __CLASS__ . __FUNCTION__ . implode('_', $id_categories);
        if (!Cache::isStored($cache_key)) {
            $query = new DbQuery();
            $query->select('DISTINCT cp.`id_product`');
            $query->from('category_product', 'cp');
            $query->where(!empty($id_categories) ? 'cp.`id_category` IN ('.implode(',', $id_categories).')' : null);
            $products = Db::getInstance()->executeS($query);
            $id_products = array();
            if (!empty($products)) {
                foreach ($products as $product) {
                    $id_products[] = $product['id_product'];
                }
            }
            Cache::store($cache_key, $id_products);
        } else {
            $id_products = Cache::retrieve($cache_key);
        }
        return $id_products;
    }
}
