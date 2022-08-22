<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_8_5($module)
{
    $module->upgradeVersion('1.8.5');
    
    $result = true;
    
    // Upgrade database
    $result &= $module->upgradeDB();
    
    return $result;
}
