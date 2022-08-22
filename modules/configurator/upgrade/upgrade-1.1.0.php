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
 * Function used to update your module from previous versions to the version 1.1.0
 * Don't forget to create one file per version.
 */
function upgrade_module_1_1_0($module)
{
    $sql = array();
    /**
     * Ajout fonctionnalité min-max pricelist
     */
    if (!$module->existColumnInTable('configurator_step_display_condition', 'min')) {
        $sql[] = "ALTER TABLE  `" . _DB_PREFIX_ . "configurator_step_display_condition` 
            ADD `min` DECIMAL(20,6) UNSIGNED DEFAULT '0.000000', 
            ADD `max` DECIMAL(20,6) UNSIGNED DEFAULT '0.000000' ;";
    }
    /**
     * Correction TEXT => MEDIUMTEXT
     */
    $sql[] = "ALTER TABLE  `" . _DB_PREFIX_ . "configurator_step` 
        CHANGE `price_list` `price_list` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
    $sql[] = "ALTER TABLE  `" . _DB_PREFIX_ . "configurator_step_attribute` 
        CHANGE `price_list` `price_list` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) === false) {
            return false;
        }
    }
    /**
     * New hook pour l'affichage de la configuration dans le bloc panier
     * + Email contenu de la configuration depuis 1.6.1.0
     */
    $module->registerHook('displayFooter');
    $module->registerHook('actionGetExtraMailTemplateVars');
    return true;
}
