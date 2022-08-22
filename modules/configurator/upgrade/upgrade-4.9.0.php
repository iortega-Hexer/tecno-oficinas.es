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
 * Function used to update your module from previous versions to the version 4.8.0
 * Don't forget to create one file per version.
 *
 * @param ModuleCore $module
 * @return boolean
 */
function upgrade_module_4_9_0($module)
{
    $sql = array();
    
    if (!$module->existColumnInTable('configurator_step_lang', 'default_value_select')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_lang` "
            . "ADD `default_value_select` VARCHAR(255) NULL  AFTER `public_name`;";
    }
  
    if (!$module->existColumnInTable('configurator_step_option', 'check_value')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` "
            . "ADD `check_value` tinyint(1) DEFAULT 0  AFTER `force_value`;";
    }
    
    if (!$module->existColumnInTable('configurator', 'hide_qty_product')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator` "
            . "ADD `hide_qty_product` tinyint(1) DEFAULT 0  AFTER `use_base_price`;";
    }
    
    if (!$module->existColumnInTable('configurator_step', 'min_options')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step` "
            . "ADD COLUMN `min_options` INT UNSIGNED NULL DEFAULT '0' AFTER `max_options`;";
    }
 
    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }

    Configuration::updateValue('CONFIGURATOR_FLOATING_PREVIEW', 0);

    return true;
}
