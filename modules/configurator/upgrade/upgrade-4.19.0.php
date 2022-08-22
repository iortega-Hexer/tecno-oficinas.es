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
 * Function used to update your module from previous versions to the version 4.18.0
 * Don't forget to create one file per version.
 *
 * @param ModuleCore $module
 * @return boolean
 */
function upgrade_module_4_19_0($module)
{
    $sql = array();

    if (!$module->existColumnInTable('configurator_cart_detail', 'id_tax_rules_group')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` 
            ADD `id_tax_rules_group` INT(11) NULL;";
    }

    if (!$module->existColumnInTable('configurator_step_option', 'id_tax_rules_group_product')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` 
            ADD `id_tax_rules_group_product` INT(11) NULL;";
    }

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }

    Configuration::updateValue('CONFIGURATOR_CACHE_PS', 1);
    return $module->uninstallOverrides() && $module->installOverrides();
}
