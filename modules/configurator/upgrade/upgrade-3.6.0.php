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
 * Function used to update your module from previous versions to the version 3.3.0
 * Don't forget to create one file per version.
 *
 * @param ModuleCore $module
 * @return boolean
 */
function upgrade_module_3_6_0($module)
{
    $sql = array();

    // INDEXES CONFIGURATOR
    if (!$module->existIndexInTable('configurator', 'id_product')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator` ADD INDEX(`id_product`);";
    }

    // INDEXES CONFIGURATOR CART DETAIL
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_configurator')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_configurator`);";
    }
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_product')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_product`);";
    }
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_product_attribute')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_product_attribute`);";
    }
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_cart')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_cart`);";
    }
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_order')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_order`);";
    }
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_order_detail')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_order_detail`);";
    }
    if (!$module->existIndexInTable('configurator_cart_detail', 'id_customization')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_customization`);";
    }

    // INDEXES CONFIGURATOR STEP
    if (!$module->existIndexInTable('configurator_step', 'id_configurator')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step` ADD INDEX(`id_configurator`);";
    }

    // INDEXES CONFIGURATOR STEP ATTRIBUTE
    if (!$module->existIndexInTable('configurator_step_attribute', 'id_configurator_step')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_attribute` ADD INDEX(`id_configurator_step`);";
    }
    if (!$module->existIndexInTable('configurator_step_attribute', 'id_attribute')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_attribute` ADD INDEX(`id_attribute`);";
    }
    if (!$module->existIndexInTable('configurator_step_attribute', 'id_product')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_attribute` ADD INDEX(`id_product`);";
    }

    // INDEXES CONFIGURATOR STEP CONDITION GROUP
    if (!$module->existIndexInTable(
        'configurator_step_display_condition_group',
        'id_configurator_step'
    )) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_display_condition_group` 
            ADD INDEX(`id_configurator_step`);";
    }

    // INDEXES CONFIGURATOR STEP CONDITION
    if (!$module->existIndexInTable(
        'configurator_step_display_condition',
        'id_configurator_step_display_condition_group'
    )) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_display_condition` 
            ADD INDEX(`id_configurator_step_display_condition_group`);";
    }

    // INDEXES CONFIGURATOR STEP TAB
    if (!$module->existIndexInTable('configurator_step_tab', 'id_configurator')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_tab` ADD INDEX(`id_configurator`);";
    }

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }

    $success = $module->registerHook('displayAdminOrder');

    return $success;
}
