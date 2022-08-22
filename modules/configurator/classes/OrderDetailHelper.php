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

/**
 * @since 1.5.0
 */
if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!class_exists('OrderDetailHelper')) {

    class OrderDetailHelper
    {
        const DECIMAL_PRECISION = 4;

        public static $tax = null;
        public static $orderDetails = null;

        public static function duplicateCart(Cart $cart, $id_order)
        {
            
            // Get original Cart
            /* @var $order OrderCore */
            $order = new Order((int) $id_order);

            if (Validate::isLoadedObject($cart) && Validate::isLoadedObject($order)) {
                /* @var $old_cart CartCore */
                $old_cart = new Cart((int) $order->id_cart);

                foreach ($old_cart->getProducts() as $product) {
                    $id_product = (int) $product['id_product'];

                    /* @var $configurator ConfiguratorModel */
                    $configurator = ConfiguratorModel::productHasConfigurator($product['id_product'], true, true);

                    if (Validate::isLoadedObject($configurator)) {
                        /* @var $configuratorCartDetail ConfiguratorCartDetailModel */
                        $configuratorCartDetails = ConfiguratorCartDetailModel::getMultipleByIdConfiguratorAndIdCart(
                            $configurator->id,
                            $old_cart->id
                        );
                        foreach ($configuratorCartDetails as $configuratorCartDetail) {
                            /* @var $newConfiguratorCartDetail ConfiguratorCartDetailModel */
                            $newConfiguratorCartDetail = $configuratorCartDetail->duplicateObject();
                            $newConfiguratorCartDetail->id_cart = $cart->id;
                            $newConfiguratorCartDetail->id_order_detail = 0;
                            $newConfiguratorCartDetail->id_order = 0;
                            $newConfiguratorCartDetail->id_customization = 0; //force calculate a new id_customization

                            $customization = new Customization($newConfiguratorCartDetail->id_customization);
                            if (!Validate::isLoadedObject($customization)) {
                                $customization = new Customization();
                                $customization->id_product_attribute = ($newConfiguratorCartDetail->id_product_attribute)
                                    ? $newConfiguratorCartDetail->id_product_attribute
                                    : 0;

                                $customization->id_cart = $cart->id;
                                $customization->id_address_delivery = $cart->id_address_delivery;


                                $customization->id_product = $id_product;
                                $customization->quantity = 1;
                                $customization->quantity_refunded = 0;
                                $customization->quantity_returned = 0;
                                $customization->in_cart = 1;
                                $customization->save();

                                $newConfiguratorCartDetail->id_customization = $customization->id;
                            }

                            $newConfiguratorCartDetail->setCustomization(
                                $configuratorCartDetail->price,
                                $configuratorCartDetail->weight
                            );
                            $newConfiguratorCartDetail->save();
                        }
                    }
                }
            }
        }

        public static function generateConfiguratorOrderDetail($params)
        {
            $order_detail = $params['object'];
            $product = new Product($order_detail->product_id, false);
            $order = new Order($order_detail->id_order);
            $configurationsCartDetail = ConfiguratorCartDetailModel::getMultipleByIdCartAndIdProduct(
                (int)$order->id_cart,
                (int)$product->id
            );
            if ($configurationsCartDetail) {
                $insert = false;
                foreach ($configurationsCartDetail as $configurationCartDetail) {
                    $customization = new Customization($configurationCartDetail->id_customization);
                    if (Validate::isLoadedObject($configurationCartDetail)
                        && Validate::isLoadedObject($customization)
                        && Validate::isLoadedObject($order)
                        && Validate::isLoadedObject($product)
                    ) {
                        if (DMTools::getVersionMajor() >= 17) {
                            // Only extract products for Prestashop 1.7
                            if ((int)$order_detail->id_customization === (int)$configurationCartDetail->id_customization) {
                                // TAX
                                self::saveTaxCalculator($order, $order_detail, $configurationCartDetail);

                                // Update order detail
                                if (isset($product->name[Context::getContext()->language->id])) {
                                    $order_detail->product_name = $product->name[Context::getContext()->language->id];
                                }
                                $order_detail->original_product_price = $order_detail->unit_price_tax_excl;
                                $order_detail->product_price = $order_detail->unit_price_tax_excl;

                                // Update reference
                                $order_detail->product_reference = $configurationCartDetail->reference;

                                // CONFIGURATOR HOOK
                                Hook::exec('configuratorActionExtractFromOrderDetailHelper', array(
                                    'order_detail' => &$order_detail,
                                    'configurationCartDetail' => $configurationCartDetail,
                                    ''
                                ));

                                $order_detail->save();

                                // Update id_order
                                $configurationCartDetail->id_order = (int)$order->id;
                                $configurationCartDetail->id_order_detail = (int)$order_detail->id;
                                $configurationCartDetail->save();
                            }
                        } else {
                            // Update product_name
                            $customization_field_names = unserialize(
                                Configuration::get('CONFIGURATOR_CUSTOMIZATION_FIELD_NAME')
                            );
                            $customization_field_name = isset(
                                $customization_field_names[Context::getContext()->language->id]
                            ) ? $customization_field_names[Context::getContext()->language->id] : 'Configurator';
                            $product_name = addslashes(
                                $product->name[Context::getContext()->language->id]
                                . ' - ' . $customization_field_name
                                . ' : <br/> ' . $configurationCartDetail->getDetailFormated()
                            );
                            // Save order detail & extract products & delete customization for Prestashop 1.6
                            $order_detail = self::saveOrderDetail(
                                $order,
                                $order_detail,
                                $insert,
                                $product_name,
                                $product,
                                $configurationCartDetail,
                                $customization
                            );
                            $customization->delete();
                            $insert = true;

                            // Update id_order
                            $configurationCartDetail->id_order = (int)$order->id;
                            $configurationCartDetail->id_order_detail = (int)$order_detail->id;
                            $configurationCartDetail->save();
                        }
                    }
                }
            }
        }

        private static function saveTaxCalculator(Order $order, OrderDetail &$orderDetail, ConfiguratorCartDetailModel $configurationCartDetail)
        {
            // @TODO: usetax verification ?

            self::$orderDetails[] = $orderDetail->id;
            $taxes = $configurationCartDetail->getTaxesDetail();

            $values = '';
            foreach ($taxes as $tax) {
                $idOrderDetail = (int)$orderDetail->id;
                $idTax = self::getTaxFromTaxRulesGroup($order, (int)$tax['id_tax_rules_group']);
                $unitTaxAmount = (float)($tax['price_tax_incl'] - $tax['price_tax_excl']);
                $totalTaxAmount = $unitTaxAmount * $orderDetail->product_quantity;

                $values .= '(' . $idOrderDetail . ',' . $idTax . ',' . $unitTaxAmount . ',' . $totalTaxAmount . '),';
            }
            $values = rtrim($values, ',');
            $sql = 'INSERT INTO `'._DB_PREFIX_.'order_detail_tax` (id_order_detail, id_tax, unit_amount, total_amount)
                VALUES '.$values;

            return Db::getInstance()->execute($sql);
        }

        private static function getTaxFromTaxRulesGroup($order, $id_tax_rules_group)
        {
            $invoiceAddress = new Address($order->id_address_invoice);
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'tax_rule` 
                WHERE `id_tax_rules_group` = ' . (int)$id_tax_rules_group . '
                AND `id_country` = ' . (int)$invoiceAddress->id_country;
            $result = Db::getInstance()->getRow($sql);
            return (int)$result['id_tax'];
        }

        /**
         * OrderDetail
         */

        private static function saveOrderDetail(
            Order $order,
            OrderDetail $order_detail,
            $insert,
            $product_name,
            Product $product,
            $configurationCartDetail,
            $customization
        ) {
            // Price
            $original_product_price = $configurationCartDetail->getPriceInCart($order->id_cart, false, 1, false);
            $unit_price_tax_excl = $configurationCartDetail->getPriceInCart($order->id_cart, true, $customization->quantity, false);
            $unit_price_tax_incl = $configurationCartDetail->getPriceInCart($order->id_cart, true, $customization->quantity, true);
            $total_price_tax_excl = $configurationCartDetail->getPriceInCart(
                $order->id_cart,
                true,
                $customization->quantity,
                false
            ) * $customization->quantity;
            $total_price_tax_incl = $configurationCartDetail->getPriceInCart(
                $order->id_cart,
                true,
                $customization->quantity,
                true
            ) * $customization->quantity;

            // Generate order detail
            $new_order_detail = ($insert) ? $order_detail->duplicateObject() : $order_detail;
            $new_order_detail->product_name = $product_name;
            $new_order_detail->product_weight = (float)($product->weight + $configurationCartDetail->weight);
            $new_order_detail->product_quantity = (int)$customization->quantity;
            $new_order_detail->original_product_price = Tools::ps_round(
                $original_product_price,
                self::DECIMAL_PRECISION
            );
            $new_order_detail->product_price = Tools::ps_round($original_product_price, self::DECIMAL_PRECISION);
            $new_order_detail->unit_price_tax_excl = Tools::ps_round($unit_price_tax_excl, self::DECIMAL_PRECISION);
            $new_order_detail->unit_price_tax_incl = Tools::ps_round($unit_price_tax_incl, self::DECIMAL_PRECISION);
            $new_order_detail->total_price_tax_excl = Tools::ps_round($total_price_tax_excl, self::DECIMAL_PRECISION);
            $new_order_detail->total_price_tax_incl = Tools::ps_round($total_price_tax_incl, self::DECIMAL_PRECISION);

            // Update reference
            $new_order_detail->product_reference = $configurationCartDetail->reference;

            /**
             * @deprecated: #CONFIGURAT-266
             */
            if (Validate::isLoadedObject(self::$tax)) {
                $new_order_detail->tax_name = isset(self::$tax->name[Context::getContext()->language->id])
                    ? self::$tax->name[Context::getContext()->language->id]
                    : '';
                $new_order_detail->tax_rate = self::$tax->rate;
            }

            // CONFIGURATOR HOOK
            Hook::exec('configuratorActionExtractFromOrderDetailHelper', array(
                'order_detail' => &$new_order_detail,
                'configurationCartDetail' => $configurationCartDetail
            ));

            // Save order detail
            $new_order_detail->save();

            // Save order detail tax
            self::saveOrderDetailTax($new_order_detail, (bool)($insert));

            return $new_order_detail;
        }

        /**
         * OrderDetailTax
         */

        public static function saveOrderDetailTax($new_order_detail, $insert = true)
        {
            $orderDetailTaxArray = array(
                'id_tax' => Validate::isLoadedObject(self::$tax) ? self::$tax->id : 0,
                'unit_amount' => $new_order_detail->unit_price_tax_incl - $new_order_detail->unit_price_tax_excl,
                'total_amount' => $new_order_detail->total_price_tax_incl - $new_order_detail->total_price_tax_excl,
            );
            if ($insert) { // Update Tax
                self::insertOrderDetailTax($orderDetailTaxArray, $new_order_detail);
            } else { // Insert Tax
                self::updateOrderDetailTax($orderDetailTaxArray, $new_order_detail);
            }
        }

        private static function insertOrderDetailTax($orderDetailTaxArray, $new_order_detail)
        {
            $orderDetailTaxArray['id_order_detail'] = (int)$new_order_detail->id;
            $values = '\'' . implode('\',\'', $orderDetailTaxArray) . '\'';
            $fields = implode(',', array_keys($orderDetailTaxArray));
            $sql_order_detail_tax = 'INSERT INTO `' . _DB_PREFIX_ . 'order_detail_tax` (' . $fields . ')';
            $sql_order_detail_tax .= ' VALUES(' . $values . ')';
            Db::getInstance()->execute($sql_order_detail_tax);
        }

        private static function updateOrderDetailTax($orderDetailTaxArray, $new_order_detail)
        {
            $sql_order_detail_tax = 'UPDATE `' . _DB_PREFIX_ . 'order_detail_tax` SET ';
            $i = 0;
            foreach ($orderDetailTaxArray as $field => $value) {
                $sql_order_detail_tax .= (($i) ? ', ' : '') . $field . ' = ' . '\'' . $value . '\'';
                $i++;
            }
            $sql_order_detail_tax .= 'WHERE `id_order_detail` = ' . (int)$new_order_detail->id;
            Db::getInstance()->execute($sql_order_detail_tax);
        }
    }
}
