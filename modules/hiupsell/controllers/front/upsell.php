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

class HIUpSellUpsellModuleFrontController extends ModuleFrontController
{

    public function __construct()
    {
        parent::__construct();
        $this->display_column_left = $this->module->upsell_left_col;
        $this->display_column_right = $this->module->upsell_right_col;
    }

    public function setMedia()
    {
        parent::setMedia();
        if ($this->module->psv == 1.6 && !$this->module->default_template) {
            $this->addCSS(_MODULE_DIR_.'/'.$this->module->name.'/views/css/front.css', 'all');
        } else {
            $this->addCSS(_THEME_CSS_DIR_.'product_list.css', 'all');
        }
    }

    public function initContent()
    {
        parent::initContent();
        if ($this->module->upsell_on) {
            $link = new Link();
            $products_id = array();
            if ($this->module->upsell_content_type == 'products') {
                $pr_ids = unserialize(Configuration::get('HI_UPSELL_PRODUCTS'));
                foreach ($pr_ids as $key => $product) {
                    $products_id[$key]['id_product'] = $product;
                }
            } elseif ($this->module->upsell_content_type == 'accessories') {
                $pr_ids = Context::getContext()->cart->getProducts();
                foreach ($pr_ids as $key => $product) {
                    $accessories_obj = new Product((int)$product['id_product']);
                    $accessories = $accessories_obj->getAccessories((int)$this->context->language->id, true);
                    if (!empty($accessories)) {
                        foreach ($accessories as $key => $id) {
                            $products_id[$key]['id_product'] = $id['id_product'];
                        }
                    }
                }
            } elseif ($this->module->upsell_content_type == 'cross_sells') {
                $cart_products = Context::getContext()->cart->getProducts();
                $products_id = array();
                foreach ($cart_products as $c_id) {
                    $cross_sell = OrderDetail::getCrossSells((int)$c_id['id_product'], $this->context->language->id);
                    if (!empty($cross_sell)) {
                        foreach ($cross_sell as $i => $id) {
                            $products_id[$i]['id_product'] = $id['id_product'];
                        }
                    }
                }
            }

            if ($this->module->psv >= 1.7) {
                $page_link = $link->getPageLink('cart').'?action=show';
            } else {
                $page_link = $link->getPageLink('order');
            }
            if (count($this->module->{'getUpsellProduct'.$this->module->psv_part}($products_id)) == 0) {
                Tools::redirect($page_link);
            }
            $this->context->smarty->assign(array(
                'psv' => $this->module->psv,
                'upsell_module_tpl_dir' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/front',
                'default_template' => $this->module->default_template,
                'page_link' => $page_link,
                'products' => $this->module->{'getUpsellProduct'.$this->module->psv_part}($products_id),
                'upsell_description' => Configuration::get('HI_UPSELL_DESCRIPTION', $this->context->language->id)
            ));
            if ($this->module->psv >= 1.7) {
                $this->setTemplate('module:'.$this->module->name.'/views/templates/front/products17.tpl');
            } else {
                $this->setTemplate('products.tpl');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('index'));
        }
    }
}
