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
 * @license   http://opensource.org/licenses/afl-3.0.phpAcademic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class Cart extends CartCore
{

    public function getTotalWeight($products = null)
    {
        require_once(dirname(__FILE__) . '/../../modules/configurator/classes/ConfiguratorCartDetailModel.php');
        $total_weight = parent::getTotalWeight($products);
        if (DMTools::getVersionMajor() < 17) {
            $total_weight += ConfiguratorCartDetailModel::getTotalWeight($this->id);
        }
        return $total_weight;
    }
    
    public function duplicate()
    {
        $return = parent::duplicate();
        
        // Valid in Prestashop 1.6 version
        $cart = isset($return['cart']) ? $return['cart'] : false;
        
        if (Tools::getValue('submitReorder') === ''
            && Tools::getValue('id_order')
            && Validate::isLoadedObject($cart)
        ) {
            require_once(dirname(__FILE__) . '/../../modules/configurator/classes/OrderDetailHelper.php');
            
            OrderDetailHelper::duplicateCart($cart, Tools::getValue('id_order'));
        }
        
        return $return;
    }

    public function updateQty($quantity, $id_product,
        $id_product_attribute = NULL, $id_customization = FALSE,
        $operator = 'up', $id_address_delivery = 0, Shop $shop = NULL,
        $auto_add_cart_rule = TRUE, $skipAvailabilityCheckOutOfStock = FALSE
    ) {
        if (Module::isInstalled('AdvancedQuote')) {
			if(Tools::getIsset('id_advancedquote')) {
				require_once(dirname(__FILE__) . '/../../modules/advancedquote/classes/AdvancedquoteModel.php');
				$advancedquote = new AdvancedquoteModel((int)Tools::getValue('id_advancedquote'));
				Context::getContext()->cart = new Cart((int)$advancedquote->id_cart);
			}
        }

        if (!$id_customization) {
            $configurator = ConfiguratorModel::getByIdProduct($id_product);

            if (Validate::isLoadedObject($configurator)) {
                $configuratorCartDetail = new ConfiguratorCartDetailModel();
                $configuratorCartDetail->id_configurator = (int)$configurator->id;
                $configuratorCartDetail->id_cart = (int)Context::getContext()->cart->id;
                $configuratorCartDetail->id_product = (int)$id_product;
                $configuratorCartDetail->id_product_attribute = (int)$id_product_attribute;
                $configuratorCartDetail->product = new Product((int)$id_product);//$this->configurator_product;
                $configuratorCartDetail->setDetail(array());
                $configuratorCartDetail->setCustomization();
                $configuratorCartDetail->setCustomization();
                $configuratorCartDetail->added_in_cart = true;
                $configuratorCartDetail->save();

                $id_customization = $configuratorCartDetail->id_customization;
            }
        }

        return parent::updateQty(
            $quantity, $id_product, $id_product_attribute, $id_customization,
            $operator, $id_address_delivery, $shop, $auto_add_cart_rule,
            $skipAvailabilityCheckOutOfStock
        );
    }

}
