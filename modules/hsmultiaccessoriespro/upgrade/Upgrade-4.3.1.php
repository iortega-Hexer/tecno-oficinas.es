<?php
/**
 * Multi Accessories Pro
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 *
 * @param HsMultiAccessoriesPro $module
 * @return boolean
 */
function upgrade_module_4_3_1($module)
{
    return $module->upgrade('4.3.1');
}
