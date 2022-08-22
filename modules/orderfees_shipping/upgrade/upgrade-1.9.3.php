<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2020 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_9_3($module)
{
    $module->upgradeVersion('1.9.3');
    
    $result = true;
    
    // Replace Cart.php override
    $result &= $module->upgradeOverride('Cart');
    
    // Register actionCartGetTotalWeight hook
    $module->registerHook('actionCartGetTotalWeight');

    return $result;
}
