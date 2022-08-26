<?php
/**
 * HsMaSpecificPrice for Multi Accessories
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaSpecificPrice extends SpecificPrice
{
    /**
     * @param int  $id_product           list id product of multiaccessorier
     * @param int  $id_customer
     * @param int  $id_group
     * @param int  $id_country
     * @param int  $id_currency
     * @param int  $id_shop
     * @param bool $is_overriding        override function getSpecificPrice for ps 1.5
     * @param int  $id_product_attribute
     *
     * @return array
     *               <pre>
     *               Array
     *               (
     *               [id_product] => array
     *               (
     *               array
     *               (
     *               int => float // quantity => price
     *               int => float // quantity => price
     *               )
     *
     *            array
     *                (
     *                    int => float // quantity => price
     *                    int => float // quantity => price
     *                    int => float // quantity => price
     *                )
     *...
     *        )
     * </pre>
     * )
     */
    public static function getSpecificPrices($id_product, $id_customer, $id_group, $id_country, $id_currency, $id_shop, $is_overriding, $id_product_attribute = null)
    {
        $specific_prices = array();
        $quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, false, (int) $id_customer);
        $use_tax = Product::getTaxCalculationMethod($id_customer) ? false : true;
        foreach ($quantity_discounts as $quantity_discount) {
            $specific_price = array();
            if (!$is_overriding) {
                $price = Product::getPriceStatic(
                    (int) $id_product,
                    $use_tax,
                    (int) $id_product_attribute,
                    2,
                    null,
                    false,
                    true,
                    $quantity_discount['from_quantity'],
                    false,
                    (int) $id_customer,
                    null,
                    null,
                    $specific_price,
                    false,
                    true
                );
            } else {
                $price = HsMaProduct::getPriceStatic(
                    (int) $id_product,
                    $use_tax,
                    (int) $id_product_attribute,
                    2,
                    null,
                    false,
                    true,
                    $quantity_discount['from_quantity'],
                    false,
                    (int) $id_customer,
                    null,
                    null,
                    $specific_price,
                    false,
                    true
                );
            }
            $specific_prices[$quantity_discount['from_quantity']] = $price;
        }

        return $specific_prices;
    }

    /**
     * Override function getSpecificPrice on PS 1.5.
     *
     * @param int $id_product
     * @param int $id_shop
     * @param int $id_currency
     * @param int $id_country
     * @param int $id_group
     * @param int $quantity
     * @param int $id_product_attribute
     * @param int $id_customer
     * @param int $id_cart
     * @param int $real_quantity
     *
     * @return float
     */
    public static function getSpecificPrice($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute = null, $id_customer = 0, $id_cart = 0, $real_quantity = 0)
    {
        if (!self::isFeatureActive()) {
            return array();
        }
        /*
        ** The date is not taken into account for the cache, but this is for the better because it keeps the consistency for the whole script.
        ** The price must not change between the top and the bottom of the page
        */

        $key = ((int) $id_product.'-'.(int) $id_shop.'-'.(int) $id_currency.'-'.(int) $id_country.'-'.(int) $id_group.'-'.(int) $quantity.'-'.(int) $id_product_attribute.'-'.(int) $id_cart.'-'.(int) $id_customer.'-'.(int) $real_quantity);
        if (!array_key_exists($key, self::$_specificPriceCache)) {
            $now = date('Y-m-d H:i:s');
            $query = '
			SELECT *, '.self::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer).'
				FROM `'._DB_PREFIX_.'specific_price`
				WHERE `id_product` IN (0, '.(int) $id_product.')
				AND `id_product_attribute` IN (0, '.(int) $id_product_attribute.')
				AND `id_shop` IN (0, '.(int) $id_shop.')
				AND `id_currency` IN (0, '.(int) $id_currency.')
				AND `id_country` IN (0, '.(int) $id_country.')
				AND `id_group` IN (0, '.(int) $id_group.')
				AND `id_customer` IN (0, '.(int) $id_customer.')
				AND
				(
					(`from` = \'0000-00-00 00:00:00\' OR \''.$now.'\' >= `from`)
					AND
					(`to` = \'0000-00-00 00:00:00\' OR \''.$now.'\' <= `to`)
				)
				AND id_cart IN (0, '.(int) $id_cart.') ';

            if ($real_quantity != 0 && !Configuration::get('PS_QTY_DISCOUNT_ON_COMBINATION')) {
                $query .= ' AND IF(`from_quantity` > 1, `from_quantity`, 0) <= IF(id_product_attribute=0,'.(int) $real_quantity.' ,'.(int) $quantity.')';
            } else {
                $qty_to_use = $id_cart ? (int) $quantity : (int) $real_quantity;
                $query .= 'AND `from_quantity` <= '.max(1, $qty_to_use);
            }

            $query .= ' ORDER BY `id_product_attribute` DESC, `from_quantity` DESC, `id_specific_price_rule` ASC, `score` DESC';

            self::$_specificPriceCache[$key] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        }

        return self::$_specificPriceCache[$key];
    }
}
