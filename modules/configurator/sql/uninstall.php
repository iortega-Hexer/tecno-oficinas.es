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

/**
 * In some cases you should not drop the tables.
 * Maybe the merchant will just try to reset the module
 * but does not want to loose all of the data associated to the module.
 */
$sql = array();
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_shop`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_group`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_attribute`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_option`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_attribute_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_option_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_display_condition`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_display_condition_group`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_cart_detail`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_attachment`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_cartdetail_attachment`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_tab`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_tab_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_filter`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'configurator_step_filter_group`';

if (DMTools::existColumnInTable('attribute', 'texture_image')) {
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'attribute` DROP `texture_image`';
}
if (DMTools::existColumnInTable('product', 'is_configurated')) {
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'product` DROP `is_configurated`';
}

if (DMTools::existColumnInTable('attribute', 'ref_ral')) {
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'attribute` DROP `ref_ral`';
}

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
