<?php
/**
* 2013 - 2020 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2020
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*/

function upgrade_module_1_0_1($module)
{
    $module->registerHook('displayAdminProductsExtra');
    $module->registerHook('backOfficeHeader');
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'upsellextraproduct` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `id_parent` int(10) unsigned NOT NULL,
            `id_children` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
    ');

    $module->HiPrestaClass->deleteTableColumn('upsellblock_lang', 'cart_rule_name');
    $module->HiPrestaClass->deleteTableColumn('upsellblock', 'cart_rule_desc');

    DB::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'upsellCartRuleCart');

    return true;
}
