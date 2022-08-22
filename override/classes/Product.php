<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    DMConcept <support@dmconcept.fr>
 * @copyright 2015 DMConcept
 * @license   http://opensource.org/licenses/afl-3.0.phpAcademic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
class Product extends ProductCore
{
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function manageCustomizationId($method, $id_customization_tmp = null)
    {
        static $id_customization;
        if ($method == 'get') {
            return (int)$id_customization;
        } else {
            if ($method == 'set') {
                $id_customization = $id_customization_tmp;
            }
        }
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    public static function setCustomizationId($id_customization)
    {
        self::manageCustomizationId('set', $id_customization);
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    public static function getCustomizationId()
    {
        return self::manageCustomizationId('get');
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function isConfigurated($id_product)
    {
        require_once(dirname(__FILE__) . '/../../modules/configurator/classes/ConfiguratorModel.php');
        $configurator = ConfiguratorModel::getByIdProduct((int)$id_product, true, true);
        return (Validate::isLoadedObject($configurator)) ? $configurator : false;
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function getPriceProductConfiguratedAdminCart($id_product, $id_cart)
    {
        $id_order = (int)Order::getOrderByCartId($id_cart);
        $sql = new DbQuery();
        $sql->select('SUM(od.`total_price_tax_excl`)/SUM(od.`product_quantity`)');
        $sql->from('order_detail', 'od');
        $sql->where('od.`product_id` = ' . (int)$id_product);
        $sql->where('od.`id_order` = ' . (int)$id_order);
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function getPriceProductConfigurated($id_product, $id_customization, $id_cart = null)
    {
        if (in_array(Tools::getValue('controller'), array('index', 'category', 'searchresults'))) {
            return null;
        }
        require_once(dirname(__FILE__) . '/../../modules/configurator/classes/ConfiguratorCartDetailModel.php');
        if ($configurator = self::isConfigurated($id_product)) {
            if (Tools::getValue('controller') == 'AdminCarts') {
                $id_cart = Tools::getValue('id_cart');
                return Product::getPriceProductConfiguratedAdminCart($id_product, $id_cart);
            } elseif ((int)$id_customization > 0) { // cart
                $cart_detail = ConfiguratorCartDetailModel::getOneByIdProductAndIdCustomization(
                    (int)$id_product,
                    (int)$id_customization
                );
                if (Validate::isLoadedObject($cart_detail)) {
                    $customization = new Customization($cart_detail->id_customization);
                    if (Validate::isLoadedObject($customization) && (int)$customization->quantity > 0) {
                        $qty = (int)$customization->quantity;
                        $price = $cart_detail->price * $qty;
                        return $price / $qty;
                    }
                }
                return null;
            } elseif ((int)$id_cart > 0) { // cart
                $carts_detail = ConfiguratorCartDetailModel::getMultipleByIdConfiguratorAndIdCart(
                    (int)$configurator->id,
                    (int)$id_cart
                );
                $price = 0;
                $qty = 0;
                $id_customization = (int)self::getCustomizationId();
                if ($carts_detail) {
                    foreach ($carts_detail as $cart_detail) {
                        if (Validate::isLoadedObject($cart_detail)) {
                            $id_customization_cart_detail = (int)$cart_detail->id_customization;
                            if (!$id_customization || $id_customization == $id_customization_cart_detail) {
                                $customization = new Customization($cart_detail->id_customization);
                                $qty_tmp = (int)$customization->quantity;
                                $qty += $qty_tmp;
                                $price += $cart_detail->price * $qty_tmp;
                            }
                        }
                    }
                }
                if ($qty > 0) {
                    return $price / $qty;
                } else {
                    return null;
                }
            } elseif ($id_configurator_cart_detail = Tools::getValue('configurator_update')) { // update
                $cart_detail = new ConfiguratorCartDetailModel((int)$id_configurator_cart_detail);
            } else { // create
                if ((int)$id_customization > 0) {
                    $cart_detail = ConfiguratorCartDetailModel::getOneByIdProductAndIdCustomization(
                        (int)$id_product,
                        (int)$id_customization
                    );
                } else {
                    $cart_detail = ConfiguratorCartDetailModel::getByIdConfiguratorAndIdGuest(
                        (int)$configurator->id,
                        (int)Context::getContext()->cookie->id_guest
                    );
                }
            }
            $product = new Product($id_product);
            if (Validate::isLoadedObject($cart_detail) && Validate::isLoadedObject($product)) {
                $price = $cart_detail->compute($product);
                return $price;
            }
        }
        return null;
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function getTaxesProductConfigurated($id_product, $id_customization)
    {
        if (in_array(Tools::getValue('controller'), array('index', 'category'))) {
            return null;
        }
        require_once(dirname(__FILE__) . '/../../modules/configurator/classes/ConfiguratorCartDetailModel.php');
        if ($configurator = self::isConfigurated($id_product)) {
            if (Tools::getValue('controller') == 'AdminCarts') {
                $id_cart = Tools::getValue('id_cart');
                return Product::getPriceProductConfiguratedAdminCart($id_product, $id_cart);
            } elseif ($id_customization > 0) { // cart
                $cart_detail = ConfiguratorCartDetailModel::getOneByIdProductAndIdCustomization(
                    (int)$id_product,
                    (int)$id_customization
                );
                if (Validate::isLoadedObject($cart_detail)) {
                    $customization = new Customization($cart_detail->id_customization);
                    if (Validate::isLoadedObject($customization) && (int)$customization->quantity > 0) {
                        $qty = (int)$customization->quantity;
                        $price = Tools::convertPrice($cart_detail->taxesCalculation()) * $qty;
                        return $price / $qty;
                    }
                }
                return null;
            } elseif ($id_configurator_cart_detail = Tools::getValue('configurator_update')) { // update
                $cart_detail = new ConfiguratorCartDetailModel((int)$id_configurator_cart_detail);
            } else { // create
                if ((int)$id_customization > 0) {
                    $cart_detail = ConfiguratorCartDetailModel::getOneByIdProductAndIdCustomization(
                        (int)$id_product,
                        (int)$id_customization
                    );
                } else {
                    $cart_detail = ConfiguratorCartDetailModel::getByIdConfiguratorAndIdGuest(
                        (int)$configurator->id,
                        (int)Context::getContext()->cookie->id_guest
                    );
                }
            }
            $product = new Product($id_product);
            if (Validate::isLoadedObject($cart_detail) && Validate::isLoadedObject($product)) {
                $tax = Tools::convertPrice($cart_detail->taxesCalculation());
                return $tax;
            }
        }
        return 0;
    }
    /**
     * Price calculation / Get product price
     *
     * @param int $id_shop Shop id
     * @param int $id_product Product id
     * @param int $id_product_attribute Product attribute id
     * @param int $id_country Country id
     * @param int $id_state State id
     * @param string $zipcode
     * @param int $id_currency Currency id
     * @param int $id_group Group id
     * @param int $quantity Quantity Required for Specific prices : quantity discount application
     * @param bool $use_tax with (1) or without (0) tax
     * @param int $decimals Number of decimals returned
     * @param bool $only_reduc Returns only the reduction amount
     * @param bool $use_reduc Set if the returned amount will include reduction
     * @param bool $with_ecotax insert ecotax in price output.
     * @param null $specific_price If a specific price applies regarding the previous parameters,
     *                               this variable is filled with the corresponding SpecificPrice object
     * @param bool $use_group_reduction
     * @param int $id_customer
     * @param bool $use_customer_price
     * @param int $id_cart
     * @param int $real_quantity
     * @return float Product price
     **/
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    public static function priceCalculation(
        $id_shop,
        $id_product,
        $id_product_attribute,
        $id_country,
        $id_state,
        $zipcode,
        $id_currency,
        $id_group,
        $quantity,
        $use_tax,
        $decimals,
        $only_reduc,
        $use_reduc,
        $with_ecotax,
        &$specific_price,
        $use_group_reduction,
        $id_customer = 0,
        $use_customer_price = true,
        $id_cart = 0,
        $real_quantity = 0,
        $id_customization = 0
    ) {
        require_once(dirname(__FILE__) . '/../../modules/configurator/classes/helper/DMTools.php');
        if (DMTools::getVersionMajor() >= 17) {
            return self::priceCalculation_1_7(
                $id_shop,
                $id_product,
                $id_product_attribute,
                $id_country,
                $id_state,
                $zipcode,
                $id_currency,
                $id_group,
                $quantity,
                $use_tax,
                $decimals,
                $only_reduc,
                $use_reduc,
                $with_ecotax,
                $specific_price,
                $use_group_reduction,
                $id_customer,
                $use_customer_price,
                $id_cart,
                $real_quantity,
                $id_customization
            );
        }
        return self::priceCalculation_1_6(
            $id_shop,
            $id_product,
            $id_product_attribute,
            $id_country,
            $id_state,
            $zipcode,
            $id_currency,
            $id_group,
            $quantity,
            $use_tax,
            $decimals,
            $only_reduc,
            $use_reduc,
            $with_ecotax,
            $specific_price,
            $use_group_reduction,
            $id_customer,
            $use_customer_price,
            $id_cart,
            $real_quantity,
            $id_customization
        );
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function priceCalculation_1_7(
        $id_shop,
        $id_product,
        $id_product_attribute,
        $id_country,
        $id_state,
        $zipcode,
        $id_currency,
        $id_group,
        $quantity,
        $use_tax,
        $decimals,
        $only_reduc,
        $use_reduc,
        $with_ecotax,
        &$specific_price,
        $use_group_reduction,
        $id_customer = 0,
        $use_customer_price = true,
        $id_cart = 0,
        $real_quantity = 0,
        $id_customization = 0
    ) {
        static $address = null;
        static $context = null;
        if ($address === null) {
            $address = new Address();
        }
        if ($context == null) {
            $context = Context::getContext()->cloneContext();
        }
        if ($id_shop !== null && $context->shop->id != (int)$id_shop) {
            $context->shop = new Shop((int)$id_shop);
        }
        if (!$use_customer_price) {
            $id_customer = 0;
        }
        if ($id_product_attribute === null) {
            $id_product_attribute = Product::getDefaultAttribute($id_product);
        }
        $cache_id = (int)$id_product.'-'.(int)$id_shop.'-'.(int)$id_currency.'-'.(int)$id_country.'-'.$id_state.'-'.$zipcode.'-'.(int)$id_group.
            '-'.(int)$quantity.'-'.(int)$id_product_attribute.'-'.(int)$id_customization.
            '-'.(int)$with_ecotax.'-'.(int)$id_customer.'-'.(int)$use_group_reduction.'-'.(int)$id_cart.'-'.(int)$real_quantity.
            '-'.($only_reduc?'1':'0').'-'.($use_reduc?'1':'0').'-'.($use_tax?'1':'0').'-'.(int)$decimals;
        $specific_price = SpecificPrice::getSpecificPrice(
            (int)$id_product,
            $id_shop,
            $id_currency,
            $id_country,
            $id_group,
            $quantity,
            $id_product_attribute,
            $id_customer,
            $id_cart,
            $real_quantity
        );
        if (isset(self::$_prices[$cache_id])) {
            return self::$_prices[$cache_id];
        }
        $cache_id_2 = $id_product.'-'.$id_shop;
        if (!isset(self::$_pricesLevel2[$cache_id_2])) {
            $sql = new DbQuery();
            $sql->select('product_shop.`price`, product_shop.`ecotax`');
            $sql->from('product', 'p');
            $sql->innerJoin('product_shop', 'product_shop', '(product_shop.id_product=p.id_product AND product_shop.id_shop = '.(int)$id_shop.')');
            $sql->where('p.`id_product` = '.(int)$id_product);
            if (Combination::isFeatureActive()) {
                $sql->select('IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute, product_attribute_shop.`price` AS attribute_price, product_attribute_shop.default_on');
                $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.id_product = p.id_product AND product_attribute_shop.id_shop = '.(int)$id_shop.')');
            } else {
                $sql->select('0 as id_product_attribute');
            }
            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            if (is_array($res) && count($res)) {
                foreach ($res as $row) {
                    $array_tmp = array(
                        'price' => $row['price'],
                        'ecotax' => $row['ecotax'],
                        'attribute_price' => (isset($row['attribute_price']) ? $row['attribute_price'] : null)
                    );
                    self::$_pricesLevel2[$cache_id_2][(int)$row['id_product_attribute']] = $array_tmp;
                    if (isset($row['default_on']) && $row['default_on'] == 1) {
                        self::$_pricesLevel2[$cache_id_2][0] = $array_tmp;
                    }
                }
            }
        }
        if (!isset(self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute])) {
            return;
        }
        $result = self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute];
        if (!$specific_price || $specific_price['price'] < 0) {
            $price = (float)$result['price'];
        } else {
            $price = (float)$specific_price['price'];
        }
        if (!$specific_price || !($specific_price['price'] >= 0 && $specific_price['id_currency'])) {
            $price = Tools::convertPrice($price, $id_currency);
            if (isset($specific_price['price']) && $specific_price['price'] >= 0) {
                $specific_price['price'] = $price;
            }
        }
        if (is_array($result) && (!$specific_price || !$specific_price['id_product_attribute'] || $specific_price['price'] < 0)) {
            $attribute_price = Tools::convertPrice($result['attribute_price'] !== null ? (float)$result['attribute_price'] : 0, $id_currency);
            if ($id_product_attribute !== false) {
                $price += $attribute_price;
            }
        }
        if ((int)$id_customization) {
            $price += Tools::convertPrice(Customization::getCustomizationPrice($id_customization));
        }
        $address->id_country = $id_country;
        $address->id_state = $id_state;
        $address->postcode = $zipcode;
        $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
        $product_tax_calculator = $tax_manager->getTaxCalculator();
        if ($use_tax) {
            if (self::isConfigurated($id_product)) {
                $tax = self::getTaxesProductConfigurated($id_product, $id_customization);
                if ($tax === null) {
                    $price = $product_tax_calculator->addTaxes($price);
                } else {
                    $price += $tax;
                }
            } else {
                $price = $product_tax_calculator->addTaxes($price);
            }
        }
        if (($result['ecotax'] || isset($result['attribute_ecotax'])) && $with_ecotax) {
            $ecotax = $result['ecotax'];
            if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0) {
                $ecotax = $result['attribute_ecotax'];
            }
            if ($id_currency) {
                $ecotax = Tools::convertPrice($ecotax, $id_currency);
            }
            if ($use_tax) {
                static $psEcotaxTaxRulesGroupId = null;
                if ($psEcotaxTaxRulesGroupId === null) {
                    $psEcotaxTaxRulesGroupId = (int) Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID');
                }
                $tax_manager = TaxManagerFactory::getManager(
                    $address,
                    $psEcotaxTaxRulesGroupId
                );
                $ecotax_tax_calculator = $tax_manager->getTaxCalculator();
                $price += $ecotax_tax_calculator->addTaxes($ecotax);
            } else {
                $price += $ecotax;
            }
        }
        $specific_price_reduction = 0;
        if (($only_reduc || $use_reduc) && $specific_price) {
            if ($specific_price['reduction_type'] == 'amount') {
                $reduction_amount = $specific_price['reduction'];
                if (!$specific_price['id_currency']) {
                    $reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
                }
                $specific_price_reduction = $reduction_amount;
                if (!$use_tax && $specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->removeTaxes($specific_price_reduction);
                }
                if ($use_tax && !$specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->addTaxes($specific_price_reduction);
                }
            } else {
                $specific_price_reduction = $price * $specific_price['reduction'];
            }
        }
        if ($use_reduc) {
            $price -= $specific_price_reduction;
        }
        if ($use_group_reduction) {
            $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
            if ($reduction_from_category !== false) {
                $group_reduction = $price * (float)$reduction_from_category;
            } else { // apply group reduction if there is no group reduction for this category
                $group_reduction = (($reduc = Group::getReductionByIdGroup($id_group)) != 0) ? ($price * $reduc / 100) : 0;
            }
            $price -= $group_reduction;
        }
        if ($only_reduc) {
            return Tools::ps_round($specific_price_reduction, $decimals);
        }
        $price = Tools::ps_round($price, $decimals);
        if ($price < 0) {
            $price = 0;
        }
        self::$_prices[$cache_id] = $price;
        return self::$_prices[$cache_id];
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    private static function priceCalculation_1_6(
        $id_shop,
        $id_product,
        $id_product_attribute,
        $id_country,
        $id_state,
        $zipcode,
        $id_currency,
        $id_group,
        $quantity,
        $use_tax,
        $decimals,
        $only_reduc,
        $use_reduc,
        $with_ecotax,
        &$specific_price,
        $use_group_reduction,
        $id_customer = 0,
        $use_customer_price = true,
        $id_cart = 0,
        $real_quantity = 0,
        $id_customization = 0
    )
    {
        static $address = null;
        static $context = null;
        if ($address === null) {
            $address = new Address();
        }
        if ($context == null) {
            $context = Context::getContext()->cloneContext();
        }
        if ($id_shop !== null && $context->shop->id != (int)$id_shop) {
            $context->shop = new Shop((int)$id_shop);
        }
        if (!$use_customer_price) {
            $id_customer = 0;
        }
        if ($id_product_attribute === null) {
            $id_product_attribute = Product::getDefaultAttribute($id_product);
        }
        $cache_id = (int)$id_product . '-' . (int)$id_shop . '-' . (int)$id_currency .
            '-' . (int)$id_country . '-' . $id_state . '-' . $zipcode . '-' . (int)$id_group .
            '-' . (int)$quantity . '-' . (int)$id_product_attribute . '-' . self::getCustomizationId() .
            '-' . (int)$with_ecotax . '-' . (int)$id_customer . '-' . (int)$use_group_reduction .
            '-' . (int)$id_cart . '-' . (int)$real_quantity .
            '-' . ($only_reduc ? '1' : '0') . '-' . ($use_reduc ? '1' : '0') .
            '-' . ($use_tax ? '1' : '0') . '-' . (int)$decimals;
        $specific_price = SpecificPrice::getSpecificPrice(
            (int)$id_product,
            $id_shop,
            $id_currency,
            $id_country,
            $id_group,
            $quantity,
            $id_product_attribute,
            $id_customer,
            $id_cart,
            $real_quantity
        );
        if (isset(self::$_prices[$cache_id])) {
            if (isset($specific_price['price']) && $specific_price['price'] > 0) {
                $specific_price['price'] = self::$_prices[$cache_id];
            }
            return self::$_prices[$cache_id];
        }
        $cache_id_2 = $id_product . '-' . $id_shop;
        if (!isset(self::$_pricesLevel2[$cache_id_2])) {
            $sql = new DbQuery();
            $sql->select('product_shop.`price`, product_shop.`ecotax`');
            $sql->from('product', 'p');
            $sql->innerJoin(
                'product_shop',
                'product_shop',
                '(product_shop.id_product=p.id_product AND product_shop.id_shop = ' . (int)$id_shop . ')'
            );
            $sql->where('p.`id_product` = ' . (int)$id_product);
            if (Combination::isFeatureActive()) {
                $sql->select('IFNULL(product_attribute_shop.id_product_attribute,0)
                    id_product_attribute, product_attribute_shop.`price`
                    AS attribute_price, product_attribute_shop.default_on');
                $sql->leftJoin(
                    'product_attribute_shop',
                    'product_attribute_shop',
                    '(product_attribute_shop.id_product = p.id_product
                        AND product_attribute_shop.id_shop = ' . (int)$id_shop . ')'
                );
            } else {
                $sql->select('0 as id_product_attribute');
            }
            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            if (is_array($res) && count($res)) {
                foreach ($res as $row) {
                    $array_tmp = array(
                        'price' => $row['price'],
                        'ecotax' => $row['ecotax'],
                        'attribute_price' => (isset($row['attribute_price']) ? $row['attribute_price'] : null)
                    );
                    self::$_pricesLevel2[$cache_id_2][(int)$row['id_product_attribute']] = $array_tmp;
                    if (isset($row['default_on']) && $row['default_on'] == 1) {
                        self::$_pricesLevel2[$cache_id_2][0] = $array_tmp;
                    }
                }
            }
        }
        if (!isset(self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute])) {
            return;
        }
        $result = self::$_pricesLevel2[$cache_id_2][(int)$id_product_attribute];
        
        $price_configurator = self::getPriceProductConfigurated($id_product, $id_customization, $id_cart);
        if ($price_configurator !== null) {
            $price = (float)$price_configurator;
            
        } elseif (!$specific_price || $specific_price['price'] < 0) {
            $price = (float)$result['price'];
        } else {
            $price = (float)$specific_price['price'];
        }
        if (!$specific_price || !($specific_price['price'] >= 0 && $specific_price['id_currency'])) {
            $price = Tools::convertPrice($price, $id_currency);
            if (isset($specific_price['price']) && $specific_price['price'] >= 0) {
                $specific_price['price'] = $price;
            }
        }
        if (is_array($result)
            && (!$specific_price || !$specific_price['id_product_attribute'] || $specific_price['price'] < 0)
        ) {
            $attribute_price = Tools::convertPrice(
                $result['attribute_price'] !== null ? (float)$result['attribute_price'] : 0,
                $id_currency
            );
            if ($id_product_attribute !== false) {
                $price += $attribute_price;
            }
        }
        $address->id_country = $id_country;
        $address->id_state = $id_state;
        $address->postcode = $zipcode;
        $tax_manager = TaxManagerFactory::getManager(
            $address,
            Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context)
        );
        $product_tax_calculator = $tax_manager->getTaxCalculator();
        if ($use_tax) {
            $price = $product_tax_calculator->addTaxes($price);
        }
        if (($result['ecotax'] || isset($result['attribute_ecotax'])) && $with_ecotax) {
            $ecotax = $result['ecotax'];
            if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0) {
                $ecotax = $result['attribute_ecotax'];
            }
            if ($id_currency) {
                $ecotax = Tools::convertPrice($ecotax, $id_currency);
            }
            if ($use_tax) {
                $tax_manager = TaxManagerFactory::getManager(
                    $address,
                    (int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID')
                );
                $ecotax_tax_calculator = $tax_manager->getTaxCalculator();
                $price += $ecotax_tax_calculator->addTaxes($ecotax);
            } else {
                $price += $ecotax;
            }
        }
        $specific_price_reduction = 0;
        if (($only_reduc || $use_reduc) && $specific_price) {
            if ($specific_price['reduction_type'] == 'amount') {
                $reduction_amount = $specific_price['reduction'];
                if (!$specific_price['id_currency']) {
                    $reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
                }
                $specific_price_reduction = $reduction_amount;
                if (!$use_tax && $specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->removeTaxes($specific_price_reduction);
                }
                if ($use_tax && !$specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->addTaxes($specific_price_reduction);
                }
            } else {
                $specific_price_reduction = $price * $specific_price['reduction'];
            }
        }
        if ($use_reduc) {
            $price -= $specific_price_reduction;
        }
        if ($use_group_reduction) {
            $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
            if ($reduction_from_category !== false) {
                $group_reduction = $price * (float)$reduction_from_category;
            } else { // apply group reduction if there is no group reduction for this category
                $group_reduction = (($reduc = Group::getReductionByIdGroup($id_group)) != 0)
                    ? ($price * $reduc / 100)
                    : 0;
            }
            $price -= $group_reduction;
        }
        if ($only_reduc) {
            return Tools::ps_round($specific_price_reduction, $decimals);
        }
        $price = Tools::ps_round($price, $decimals);
        if ($price < 0) {
            $price = 0;
        }
        self::$_prices[$cache_id] = $price;
        return self::$_prices[$cache_id];
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    public function deleteConfigurator()
    {
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && $this->advanced_stock_management) {
            $stock_manager = StockManagerFactory::getManager();
            $physical_quantity = $stock_manager->getProductPhysicalQuantities($this->id, 0);
            $real_quantity = $stock_manager->getProductRealQuantities($this->id, 0);
            if ($physical_quantity > 0) {
                return false;
            }
            if ($real_quantity > $physical_quantity) {
                return false;
            }
            $warehouse_product_locations = Adapter_ServiceLocator::get('Core_Foundation_Database_EntityManager')
                ->getRepository('WarehouseProductLocation')
                ->findByIdProduct($this->id);
            foreach ($warehouse_product_locations as $warehouse_product_location) {
                $warehouse_product_location->delete();
            }
            $stocks = Adapter_ServiceLocator::get('Core_Foundation_Database_EntityManager')
                ->getRepository('Stock')
                ->findByIdProduct($this->id);
            foreach ($stocks as $stock) {
                $stock->delete();
            }
        }
        $result = ObjectModel::delete();
        StockAvailable::removeProductFromStockAvailable($this->id);
        $result &= ($this->deleteProductAttributes() && $this->deleteImages() && $this->deleteSceneProducts());
        if ($this->hasMultishopEntries()) {
            return true;
        }
        Hook::exec('actionProductDelete', array('id_product' => (int)$this->id, 'product' => $this));
        if (!$result ||
            !GroupReduction::deleteProductReduction($this->id) ||
            !$this->deleteCategories(false) ||
            !$this->deleteProductFeatures() ||
            !$this->deleteTags() ||
            !$this->deleteCartProducts() ||
            !$this->deleteAttributesImpacts() ||
            !$this->deleteAttachments(false) ||
            !$this->deleteCustomization() ||
            !SpecificPrice::deleteByProductId((int)$this->id) ||
            !$this->deletePack() ||
            !$this->deleteProductSale() ||
            !$this->deleteSearchIndexes() ||
            !$this->deleteAccessories() ||
            !$this->deleteFromAccessories() ||
            !$this->deleteFromSupplier() ||
            !$this->deleteDownload() ||
            !$this->deleteFromCartRules()) {
            return false;
        }
        return true;
    }
}
