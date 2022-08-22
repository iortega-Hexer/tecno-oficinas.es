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
 * Function used to update your module from previous versions to the version 2.19.0
 * Don't forget to create one file per version.
 */
function upgrade_module_2_19_0($module)
{
    $module->unregisterHook('actionAdminProductsListingFieldsModifier');
    $module->unregisterHook('actionAdminAttributesGroupsListingFieldsModifier');
    $module->registerHook('displayAdminCartsView');
    $module->registerHook('actionObjectCartDeleteAfter');
    $module->registerHook('actionAfterDeleteProductInCart');

    $languages = Language::getLanguages();
    $value = array();
    foreach ($languages as $language) {
        $value[$language['id_lang']] = "Configurator";
    }
    Configuration::updateValue('CONFIGURATOR_CUSTOMIZATION_FIELD_NAME', serialize($value));

    $sql = array();
    /**
     * Ajout champ
     */
    if (!$module->existColumnInTable('configurator_cart_detail', 'price')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD `price` DECIMAL(20,6) NOT NULL;";
    }

    if (!$module->existColumnInTable('configurator_cart_detail', 'weight')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD `weight` DECIMAL(20,6) NOT NULL;";
    }

    if (!$module->existColumnInTable('configurator_cart_detail', 'id_customization')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD `id_customization` INT NOT NULL;";
    }

    if (!$module->existColumnInTable('configurator_cart_detail', 'visual_rendering')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD `visual_rendering` LONGTEXT NULL";
    }

    if (!$module->existColumnInTable('configurator', 'id_customization_field')) {
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator` ADD `id_customization_field` INT NOT NULL;";
    }

    $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "customized_data` 
        CHANGE `value` `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }

    $success = true;

    $success &= $module->uninstallOverrides();
    $success &= $module->installOverrides();

    return $success;
}

/**
 * REMOVE id_product OF configurator_cart_detail
 * REMOVE attribute_key OF configurator_cart_detail
 */
