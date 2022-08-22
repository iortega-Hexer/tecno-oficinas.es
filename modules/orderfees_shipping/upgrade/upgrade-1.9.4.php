<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2020 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_9_4($module)
{
    $module->upgradeVersion('1.9.4');
    
    $result = true;
    
    // Upgrade database
    $result &= $module->upgradeDB();
    
    return $result;
}
