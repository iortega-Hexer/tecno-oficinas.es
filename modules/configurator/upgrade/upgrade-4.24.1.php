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

require_once(dirname(__FILE__) . '/../classes/filter/ConfiguratorStepFilterAbstract.php');

/**
 * Function used to update your module from previous versions to the version 4.24.1
 * Don't forget to create one file per version.
 *
 * @param ModuleCore $module
 * @return boolean
 */
function upgrade_module_4_24_1($module)
{
    $convert = array(
        '=' => 'EQUAL',
        '%LIKE%' => 'CONTAINS',
        '%CONTAINED%' => 'CONTAINED',
        '>' => 'UPPER',
        '>=' => 'UPPER_OR_EQUAL',
        '<' => 'LOWER',
        '<=' => 'LOWER_OR_EQUAL',
        '=NUMBER' => 'EQUAL_NUMBER',
        '>NUMBER' => 'UPPER_NUMBER',
        '>=NUMBER' => 'UPPER_OR_EQUAL_NUMBER',
        '<NUMBER' => 'LOWER_NUMBER',
        '<=NUMBER' => 'LOWER_OR_EQUAL_NUMBER',
        '=FORMULA' => 'EQUAL_FORMULA',
        '>FORMULA' => 'UPPER_FORMULA',
        '>=FORMULA' => 'UPPER_OR_EQUAL_FORMULA',
        '<FORMULA' => 'LOWER_FORMULA',
        '<=FORMULA' => 'LOWER_OR_EQUAL_FORMULA'
    );
    $filters = ConfiguratorStepFilterAbstract::getFilters();
    foreach ($filters as $filter) {
        if (!$filter->operator) {
            $filter->delete();
        } else if(isset($convert[$filter->operator])) {
            $filter->operator = $convert[$filter->operator];
            $filter->save();
        }
    }

    $success = $module->registerHook('actionObjectFeatureValueDeleteAfter');
    return $success;
}
