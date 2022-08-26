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
function upgrade_module_2_4($module)
{
    return $module->upgrade('2.4');
}
