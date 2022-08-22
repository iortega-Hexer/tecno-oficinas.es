<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
class Carrier extends CarrierCore
{
    /*
    * module: configurator
    * date: 2021-04-19 09:03:16
    * version: 4.31.0
    */
    public static function getAvailableCarrierList(
        Product $product,
        $id_warehouse,
        $id_address_delivery = null,
        $id_shop = null,
        $cart = null,
        &$error = array()
    ) {
        if ($cart) {
            $width = ConfiguratorCartDetailModel::getProductWidth($cart, $product);
            $height = ConfiguratorCartDetailModel::getProductHeight($cart, $product);
            $depth = ConfiguratorCartDetailModel::getProductDepth($cart, $product);
            $product->width = $width ? $width : $product->width;
            $product->height = $height ? $height : $product->height;
            $product->depth = $depth ? $depth : $product->depth;
        }
        return parent::getAvailableCarrierList($product, $id_warehouse, $id_address_delivery, $id_shop, $cart, $error);
    }
}
