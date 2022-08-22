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
 * Function used to update your module from previous versions to the version 4.0.0
 * Don't forget to create one file per version.
 *
 * @param ModuleCore $module
 * @return boolean
 */
function upgrade_module_4_0_0($module)
{
    $sqlTable = array();

    // RENAME TABLES

    if (!$module->existTableInDatabase('configurator_step_option')) {
        $sqlTable[] = "RENAME TABLE `" . _DB_PREFIX_ . "configurator_step_attribute` 
            TO `" . _DB_PREFIX_ . "configurator_step_option`";
    }

    if (!$module->existTableInDatabase('configurator_step_option_lang')) {
        $sqlTable[] = "RENAME TABLE `" . _DB_PREFIX_ . "configurator_step_attribute_lang` 
            TO `" . _DB_PREFIX_ . "configurator_step_option_lang`";
    }
    
    // First execute the update of table
    foreach ($sqlTable as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }
    
    // After the colums
    $sql = array();
    
    // RENAME COLUMNS

    if ($module->existColumnInTable('configurator_step', 'id_attribute_group')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step` 
            CHANGE `id_attribute_group` `id_option_group` INT(10) UNSIGNED NOT NULL";
    }

    if ($module->existColumnInTable('configurator_step', 'max_qty_step_attribute_id')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step` 
            CHANGE `max_qty_step_attribute_id` `max_qty_step_option_id` INT(10) UNSIGNED NULL DEFAULT '0'";
    }
    
    $sql[] = "UPDATE `" . _DB_PREFIX_ . "configurator_step` SET `type` = 'attributes' WHERE `type` = 'options'";
    
    if ($module->existColumnInTable('configurator_step_option', 'id_configurator_step_attribute')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            CHANGE `id_configurator_step_attribute` `id_configurator_step_option` INT(10) UNSIGNED 
            NOT NULL AUTO_INCREMENT";
    }

    if ($module->existColumnInTable('configurator_step_option', 'id_attribute')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            CHANGE `id_attribute` `id_option` INT(10) UNSIGNED NOT NULL";
    }

    if ($module->existColumnInTable('configurator_step_option', 'id_configurator_step_attribute_division')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            CHANGE `id_configurator_step_attribute_division` `id_configurator_step_option_division` INT(10) UNSIGNED 
            NULL DEFAULT NULL";
    }

    if ($module->existColumnInTable('configurator_step_option', 'id_impact_step_attribute_x')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            CHANGE `id_impact_step_attribute_x` `id_impact_step_option_x` INT(10) UNSIGNED NULL DEFAULT NULL";
    }

    if ($module->existColumnInTable('configurator_step_option', 'id_impact_step_attribute_y')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            CHANGE `id_impact_step_attribute_y` `id_impact_step_option_y` INT(10) UNSIGNED NULL DEFAULT NULL";
    }

    if (!$module->existColumnInTable('configurator_step_option', 'ipa')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            ADD `ipa` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `id_option`";
        $sql[] = "UPDATE `" . _DB_PREFIX_ . "configurator_step_option` 
            SET `ipa` = `id_option` WHERE `id_product` > 0";
        $sql[] = "UPDATE `" . _DB_PREFIX_ . "configurator_step_option` 
            SET `id_option` = `id_product` WHERE `id_product` > 0";
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` DROP `id_product`";
    }

    if ($module->existColumnInTable('configurator_step_option_lang', 'id_configurator_step_attribute')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option_lang` 
            CHANGE `id_configurator_step_attribute` `id_configurator_step_option` INT(10) UNSIGNED 
            NOT NULL AUTO_INCREMENT";
    }

    if ($module->existColumnInTable('configurator_step_display_condition_group', 'id_configurator_step_attribute')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_display_condition_group` 
            CHANGE `id_configurator_step_attribute` `id_configurator_step_option` INT(10) UNSIGNED NOT NULL";
    }

    // ADD COLUMNS

    if (!$module->existColumnInTable('configurator_step', 'use_shared')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step` 
            ADD `use_shared` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
    }

    
    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }

    return true;
}
