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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Function used to update your module from previous versions to the version 4.23.0
 * Don't forget to create one file per version.
 *
 * @param ModuleCore $module
 * @return boolean
 */
function upgrade_module_4_23_0($module)
{
    $sql = array();

    if (!$module->existColumnInTable('configurator_cart_detail', 'id_guest')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD `id_guest` INT(11) DEFAULT '0';";
    }

    if (!$module->existIndexInTable('configurator_cart_detail', 'id_guest')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_guest`);";
    }

    $sql[] = "UPDATE `" . _DB_PREFIX_ . "configurator_cart_detail` ccd, " . _DB_PREFIX_ . "cart c SET ccd.id_guest = c.id_guest WHERE ccd.id_cart = c.id_cart AND ccd.id_cart > 0;";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }

    $success = $module->uninstallOverrides();
    $success &= $module->installOverrides();

    /**
     * FIX: réinititalisation des produits "fantomes" pour les clients ayant eux un soucis car les overrides n'avaient pas été appliqués
     */
    // Récupération paniers comprenant des produits fantomes
    $cartDetails = Db::getInstance()->executeS("SELECT ccd.* FROM `" . _DB_PREFIX_ . "configurator_cart_detail` ccd LEFT JOIN `" . _DB_PREFIX_ . "customization` c ON ccd.id_customization = c.id_customization WHERE c.id_cart <> ccd.id_cart;");
    $idCarts = array();
    if (is_array($cartDetails)) {
        foreach ($cartDetails as $cartDetail) {
            if (!in_array((int)$cartDetail['id_cart'], $idCarts)) {
                $idCarts[] = (int)$cartDetail['id_cart'];
            }
        }
    }
    // Suppression des produits fantomes
    Db::getInstance()->execute("DELETE c FROM `" . _DB_PREFIX_ . "customization` c LEFT JOIN `" . _DB_PREFIX_ . "configurator_cart_detail` ccd ON ccd.id_customization = c.id_customization WHERE c.id_cart <> ccd.id_cart;");
    Db::getInstance()->execute("DELETE ccd FROM `" . _DB_PREFIX_ . "configurator_cart_detail` ccd LEFT JOIN `" . _DB_PREFIX_ . "customization` c ON ccd.id_customization = c.id_customization WHERE c.id_customization IS NULL;");
    // Réinitialisation des quantités dans le panier
    foreach ($idCarts as $idCart) {
        $qtyProducts = Db::getInstance()->executeS("SELECT id_product, id_product_attribute, SUM(quantity) as qty FROM `" . _DB_PREFIX_ . "customization` WHERE id_cart = " . $idCart . " AND in_cart = 1 GROUP BY id_product, id_product_attribute;");
        if (is_array($qtyProducts)) {
            foreach ($qtyProducts as $qtyProduct) {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "cart_product` SET quantity = " . (int)$qtyProduct['qty'] . " WHERE id_cart = " . $idCart . " AND id_product = " . (int)$qtyProduct['id_product'] . " AND id_product_attribute = " . (int)$qtyProduct['id_product_attribute'] . ";");
            }
        }
    }

    return $success;
}
