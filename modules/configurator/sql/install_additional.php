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

$sql = array();

/* * ********************
 * EDIT EXISTING PS TABLE
 * ******************** */
if (!DMTools::existColumnInTable('product', 'is_configurated')) {
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'product` 
    ADD `is_configurated` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT "0";';
}
if (!DMTools::existColumnInTable('attribute', 'texture_image')) {
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'attribute` 
    ADD `texture_image` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT "0" AFTER `color`;';
}

if (!DMTools::existColumnInTable('attribute', 'ref_ral')) {
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'attribute` 
    ADD `ref_ral` VARCHAR(25) AFTER `texture_image`;';
}

/* * ********************
 * EDIT NATURE OF ORDER DETAIL FIELD    
 * ******************** */
$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'order_detail` 
    CHANGE `product_name` `product_name` TEXT CHARACTER SET utf8 NOT NULL';
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "customized_data` 
    CHANGE `value` `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
