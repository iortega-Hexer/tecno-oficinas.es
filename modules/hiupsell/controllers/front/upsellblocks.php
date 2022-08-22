<?php
/**
* 2013 - 2017 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2017
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*/

class HIUpSellUpsellBlocksModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        parent::init();
        if (Tools::getValue('action') == 'change_attributs') {
            $link = new Link();
            $id_product =Tools::getValue('id_product');
            $id_block =Tools::getValue('id_block');
            $product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);

            /*Combination*/
            $combination = array();
            if (!empty(Tools::getValue('group'))) {
                foreach (Tools::getValue('group') as $group) {
                    array_push($combination, $group);
                }
            }
            $id_combination = $this->module->getIdProductAttributesByIdAttributes($id_product, $combination);

            /*Image*/
            $image = Image::getImages($this->context->language->id, $id_product, $id_combination);
            if (!empty($image)) {
                $current_image = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink($product->link_rewrite, $image[0]['id_image'], $this->module->HiPrestaClass->getImageType('home'));
            } else {
                $cover_image = $product->getCover($id_product);
                $current_image = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                    $product->link_rewrite,
                    $cover_image['id_image'],
                    $this->module->HiPrestaClass->getImageType('home')
                );
            }

            /*Price*/
            $combination_specific_price = null;
            $price = Product::getPriceStatic((int)$id_product, false, $id_combination, 6, null, false, true, 1, false, null, null, null, $combination_specific_price);

            $price_without_reduction = Tools::displayPrice((float)Tools::convertPrice($product->getPriceWithoutReduct())) ;
            $isset_specific_price = $this->module->getProductSpecificPriceByProductId($id_product, $id_combination);
            $price_static = '';
            $show_reduction = false;
            $reduction = '';
            $remove_block = false;
            if (!empty($isset_specific_price)) {
                if ($isset_specific_price['reduction_type'] == 'percentage') {
                    $reduction = '-'.($isset_specific_price['reduction']*100).'%';
                } else {
                    $reduction = '-'.Tools::displayPrice((float)Tools::convertPrice((int)$isset_specific_price['reduction'])).'';
                }
                $show_reduction = true;
                $price_static .= Tools::displayPrice((float)Tools::convertPrice($price));
            } else {
                $upsell_blocks = new UpsellBlock($id_block);
                if ($upsell_blocks && $upsell_blocks->offer_discount) {
                    if ($upsell_blocks->apply_discount == 'percent') {
                        $reduction = '-'.($upsell_blocks->reduction_percent).'%';
                        $price_upsell = $product->getPriceWithoutReduct() - ($product->getPriceWithoutReduct()*$upsell_blocks->reduction_percent/100);
                    } else {
                        $reduction = '-'.Tools::displayPrice((float)Tools::convertPrice((int)$upsell_blocks->reduction_amount)).'';
                        $price_upsell = $product->getPriceWithoutReduct() - (int)$upsell_blocks->reduction_amount;
                    }
                    $show_reduction = true;
                    $price_static = Tools::displayPrice((float)Tools::convertPrice($price_upsell));
                    $remove_block = true;
                } else {
                    $price_static .= Tools::displayPrice((float)Tools::convertPrice($price));
                }
            }

            /*Stock*/
            $in_stock = true;
            if (Product::getQuantity($id_product, $id_combination) <= 0 && !Product::isAvailableWhenOutOfStock($product->out_of_stock)) {
                $in_stock = false;
            }
            die(Tools::jsonEncode(
                array(
                    'id_combination' => $id_combination,
                    'current_image' => $current_image,
                    'price_static' => $price_static,
                    'in_stock' => $in_stock,
                    'price_without_reduction' => $price_without_reduction,
                    'show_reduction' => $show_reduction,
                    'reduction' => $reduction,
                    'remove_block' => $remove_block,
                )
            ));
        } else if (Tools::getValue('action') == 'updateCartSummary') {
            $odd = 0;
            $products = array();
            foreach ($this->context->cart->getProducts() as $product) {
                $this->context->smarty->assign(array(
                    'product' => $product,
                    'customizedDatas' => Product::getAllCustomizedDatas($this->context->cart->id, null, true),
                    'quantityDisplayed' => 0,
                    'productId' => $product['id_product'],
                    'productAttributeId' => $product['id_product_attribute'],
                    'odd' => ($odd+1)%2,
                    'token_cart' => md5(_COOKIE_KEY_.'recover_cart_'.$this->context->cart->id)
                ));
                $id = 'product_'.$product['id_product'].'_'.$product['id_product_attribute'].'_0_'.$product['id_address_delivery'];
                if (!empty($product['gift'])) {
                    $id .= '_gift';
                }
                // print_r($products[$id]);
                $products[$id] = $this->context->smarty->fetch(_PS_THEME_DIR_.'shopping-cart-product-line.tpl');
            }
            die(Tools::jsonEncode(
                array(
                    'summary' => $this->context->cart->getSummaryDetails(null, true),
                    'products' => $products
                )
            ));
        } else if (Tools::getValue('action') == 'apply_discount') {
            $id_product = (int)Tools::getValue('id_product');
            $product = new Product($id_product, true);
            if ($product->specificPrice) {
                return;
            }
            $upsell_blocks = new UpsellBlock(Tools::getValue('id_block'));
            if ($upsell_blocks->offer_discount) {
                if ($upsell_blocks->apply_discount == 'percent') {
                    $reduction_type = 'percentage';
                    $reduction = $upsell_blocks->reduction_percent / 100;
                } else {
                    $reduction_type = 'amount';
                    $reduction = $upsell_blocks->reduction_amount;
                }
                // check if special price already exists
                $special_price = Db::getInstance()->ExecuteS('
                    SELECT * FROM `'._DB_PREFIX_.'specific_price`
                    WHERE id_cart = '.(int)$this->context->cart->id.'
                    AND id_product = '.(int)$id_product.'
                ');
                if (!$special_price) {
                    Configuration::updateGlobalValue('PS_SPECIFIC_PRICE_FEATURE_ACTIVE', 1);

                    Db::getInstance()->execute('
                        INSERT INTO `'._DB_PREFIX_.'specific_price` (
                            `id_specific_price_rule`,
                            `id_cart`,
                            `id_product`,
                            `id_shop`,
                            `id_shop_group`,
                            `id_currency`,
                            `id_country`,
                            `id_group`,
                            `id_customer`,
                            `id_product_attribute`,
                            `price`,
                            `from_quantity`,
                            `reduction`,
                            `reduction_tax`,
                            `reduction_type`,
                            `from`,
                            `to`
                        ) VALUES (
                            0,
                            '.(int)$this->context->cart->id.',
                            '.(int)$id_product.',
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            \'-1.000000\',
                            1,
                            '.(float)$reduction.',
                            1,
                            \''.pSQL($reduction_type).'\',
                            \'0000-00-00 00:00:00\',
                            \'0000-00-00 00:00:00\'
                        )
                    ');
                }

                die(Tools::jsonEncode(array('hasError' => false)));
            }
        } else {
            die();
        }
    }
}
