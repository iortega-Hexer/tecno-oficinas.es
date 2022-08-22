<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

class Cart extends CartCore
{
    
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null
    ) {
        $total = 0;
        $return = false;
        
        Hook::exec('actionCartGetPackageShippingCost', array(
            'object' => &$this,
            'id_carrier' => &$id_carrier,
            'use_tax' => &$use_tax,
            'default_country' => &$default_country,
            'product_list' => &$product_list,
            'id_zone' => &$id_zone,
            'total' => &$total,
            'return' => &$return
        ));
        
        if ($return) {
            return ($total !== false ? (float) Tools::ps_round((float) $total, 2) : false);
        }
        
        $shipping_cost = parent::getPackageShippingCost(
            $id_carrier,
            $use_tax,
            $default_country,
            $product_list,
            $id_zone
        );
        
        if ($shipping_cost !== false) {
            return $shipping_cost + (float) Tools::ps_round((float) $total, 2);
        }
        
        return false;
    }
    
    public function getTotalWeight($products = null)
    {
        $total_weight = 0;
        $return = false;
        
        Hook::exec('actionCartGetTotalWeight', array(
            'object' => &$this,
            'products' => &$products,
            'total_weight' => &$total_weight,
            'return' => &$return
        ));
        
        if ($return) {
            return $total_weight;
        }
        
        return parent::getTotalWeight($products) + $total_weight;
    }
}
