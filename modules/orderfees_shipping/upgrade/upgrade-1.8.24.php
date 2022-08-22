<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2018 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_8_24($module)
{
    $module->upgradeVersion('1.8.24');
    
    $result = true;
    
    // Replace Cart.php override
    $result &= $module->upgradeOverride('Cart');

    return $result;
}
