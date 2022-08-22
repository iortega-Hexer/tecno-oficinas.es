<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2019 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_8_28($module)
{
    $module->upgradeVersion('1.8.28');
    
    $result = true;
    
    // Upgrade database
    $result &= $module->upgradeDB();
    
    return $result;
}
