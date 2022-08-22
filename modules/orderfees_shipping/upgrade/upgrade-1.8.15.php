<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2018 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_8_15($module)
{
    (bool)$module;
    
    if (!Configuration::get('MS_ORDERFEES_SHIPPING_TYPES')) {
        Configuration::updateValue('MS_ORDERFEES_SHIPPING_TYPES', 'country,zone,carrier,group,of_shipping_rule,shop');
    }
    
    return true;
}
