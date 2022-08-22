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

class AdminUpsellController extends ModuleAdminController
{
    public function __construct()
    {
        $this->secure_key = Tools::getValue('secure_key');
        parent::__construct();
    }

    public function init()
    {
        parent::init();
        if ($this->ajax) {
            if ($this->secure_key == $this->module->secure_key) {
                switch (Tools::getValue('action')) {
                    case 'product_search':
                        $this->productSearch(urldecode(Tools::getValue('q')));
                        break;
                    case 'add_product':
                        $this->searchProductActions(Tools::getValue('product_id'), 'add');
                        break;
                    case 'delete_product':
                        $this->searchProductActions(Tools::getValue('product_id'), 'delete');
                        break;
                 /*Helper list*/
                    case 'update_block_status':
                        if (Tools::getValue('status') == '0') {
                            $active = 1;
                        } else {
                            $active = 0;
                        }
                        $upsellblock = new UpsellBlock(Tools::getValue('id'));
                        $upsellblock->active = $active;
                        $upsellblock->update();
                        die(Tools::jsonEncode(array(
                            'content' => $this->module->renderUpsellList(),
                        )));
                /*Helper list*/
                    case 'update_block_helperlist':
                        die(Tools::jsonEncode(array(
                            'content' => $this->module->renderUpsellList(),
                        )));
                /*Add new block*/
                    case 'show_add_form':
                        die(Tools::jsonEncode(array(
                            'content' => $this->module->renderUpsellBlockAddForm('add', null),
                        )));
                    case 'add_block':
                        if (Tools::getValue('block_title_'.Configuration::get('PS_LANG_DEFAULT')) == '') {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for title', 'AdminUpsellontroller'),
                            )));
                        } elseif (!Validate::isInt(Tools::getValue('block_products_count')) && Tools::getValue('block_products_count') != '') {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for products count', 'AdminUpsellontroller'),
                            )));
                        } elseif (Tools::getValue('block_hook') == 'none') {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for hook', 'AdminUpsellontroller'),
                            )));
                        } elseif (Tools::getValue('block_offer_discount') && Tools::getValue('apply_discount') == 'percent' && (Tools::getValue('reduction_percent') == '' || !Validate::isFloat(Tools::getValue('reduction_percent')))) {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for reduction percent', 'AdminUpsellontroller'),
                            )));
                        } elseif (Tools::getValue('block_offer_discount') && Tools::getValue('apply_discount') == 'amount' && (Tools::getValue('reduction_amount') == '' || !Validate::isFloat(Tools::getValue('reduction_amount')))) {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for reduction amount', 'AdminUpsellontroller'),
                            )));
                        } else {
                            $this->module->addorupdateUpsellBlock('add', null);
                            die(Tools::jsonEncode(array(
                                'error' => false,
                            )));
                        }
                /*Update block*/
                    case 'show_update_form':
                        die(Tools::jsonEncode(array(
                            'content' => $this->module->renderUpsellBlockAddForm('update', Tools::getValue('id')),
                        )));
                    case 'update_block':
                        if (Tools::getValue('block_title_'.Configuration::get('PS_LANG_DEFAULT')) == '') {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for title', 'AdminUpsellontroller'),
                            )));
                        } elseif (!Validate::isInt(Tools::getValue('block_products_count')) && Tools::getValue('block_products_count') != '') {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for products count', 'AdminUpsellontroller'),
                            )));
                        } elseif (Tools::getValue('block_hook') == 'none') {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for hook', 'AdminUpsellontroller'),
                            )));
                        } elseif (Tools::getValue('block_offer_discount') && Tools::getValue('apply_discount') == 'percent' && (Tools::getValue('reduction_percent') == '' || !Validate::isFloat(Tools::getValue('reduction_percent')))) {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for reduction percent', 'AdminUpsellontroller'),
                            )));
                        } elseif (Tools::getValue('block_offer_discount') && Tools::getValue('apply_discount') == 'amount' && (Tools::getValue('reduction_amount') == '' || !Validate::isFloat(Tools::getValue('reduction_amount')))) {
                            die(Tools::jsonEncode(array(
                                'error' => $this->l('Invalid value for reduction amount', 'AdminUpsellontroller'),
                            )));
                        } else {
                            $this->module->addorupdateUpsellBlock('update', Tools::getValue('block_row_id'));
                            die(Tools::jsonEncode(array(
                                'error' => false,
                            )));
                        }
                /*Delete block*/
                    case 'delete_block':
                        $block_delete = new UpsellBlock(Tools::getValue('id'));
                        $block_delete->delete();
                        die();
                /*Add block product*/
                    case 'add_block_product':
                        $id_product = Tools::getValue('id_product');
                        $product_ids = Tools::getValue('product_ids');
                        if ($product_ids == '') {
                            $ids = $id_product.',';
                        } else {
                            $ids = $product_ids.$id_product.',';
                        }
                        $product_load = new Product($id_product);
                        if (strpos($product_ids, $id_product) !== false) {
                            die(Tools::jsonEncode(array(
                                'error' => $this->module->l(' You already have this product', 'AdminUpsellontroller'),
                            )));
                        } elseif (!Validate::isLoadedObject($product_load)) {
                            die(Tools::jsonEncode(array(
                                'error' => $this->module->l('The product ID is invalid', 'AdminUpsellontroller'),
                            )));
                        } else {
                            die(Tools::jsonEncode(array(
                                'id_product' => $id_product,
                                'product_name' => Product::getProductName($id_product),
                                'ids' => $ids,
                            )));
                        }
                /*Delete Block product*/
                    case 'delete_block_product':
                        $id_product = Tools::getValue('id_product');
                        $product_ids = Tools::getValue('product_ids');
                        $ids = str_replace($id_product.',', "", $product_ids);
                        die(Tools::jsonEncode(array(
                            'id_product' => $id_product,
                            'product_name' => Product::getProductName($id_product),
                            'ids' => $ids,
                        )));
                    /*Add extra*/
                    case 'add_product_extra':
                        $this->searchProductExtraActions(Tools::getValue('id_parent'), Tools::getValue('id_children'), 'add');
                        break;
                    /*Delete esxtra*/
                    case 'delete_extra_product':
                        $this->searchProductExtraActions(Tools::getValue('id_parent'), Tools::getValue('id_children'), 'delete');
                        break;
                }
            } else {
                die();
            }
        } else {
            Tools::redirectAdmin($this->module->HiPrestaClass->getModuleUrl());
        }
    }

    protected function productSearch($search_val)
    {
        $search_res = '';
        if ($search_val && !is_array($search_val)) {
            $search = Search::find((int)Tools::getValue('id_lang'), $search_val, 1, 10, 'position', 'desc', true, false);
            if (!empty($search)) {
                foreach ($search as $product) {
                    $search_res .= $product['id_product'].'|'.$product['pname'].'|'.$product['cname']."\n";
                }
            }
        }
        die($search_res);
    }

    protected function searchProductActions($product_id, $action_type)
    {
        $error = '';
        if ($action_type == 'add') {
            $product_load = new Product($product_id);
            if (!Validate::isLoadedObject($product_load)) {
                $error = $this->module->l('The product ID is invalid', 'AdminUpsellontroller');
            } else {
                $product_ids = unserialize(Configuration::get('HI_UPSELL_PRODUCTS'));
                if (!is_array($product_ids)) {
                    $product_ids = array();
                }
                if (isset($product_id) && $product_id != '' && !in_array($product_id, $product_ids)) {
                    array_push($product_ids, $product_id);
                    Configuration::updateValue('HI_UPSELL_PRODUCTS', serialize($product_ids));
                } else {
                    $error = $this->module->l('You already have this product', 'AdminUpsellontroller');
                }
            }
        }
        if ($action_type == 'delete') {
            $id_product = Tools::getValue('product_id');
            $product_ids = unserialize(Configuration::get('HI_UPSELL_PRODUCTS'));
            unset($product_ids[array_search($id_product, $product_ids)]);
            Configuration::updateValue('HI_UPSELL_PRODUCTS', serialize($product_ids));
        }
        die(Tools::jsonEncode(array('content' => $this->module->renderSearchProductList(), 'error' => $error)));
    }

    protected function searchProductExtraActions($id_parent, $id_children, $action_type)
    {
        $error = '';
        if ($action_type == 'add') {
            $product_load = new Product($id_children);
            if (!Validate::isLoadedObject($product_load)) {
                $error = $this->module->l('The product ID is invalid', 'AdminUpsellontroller');
            } elseif ($id_parent == $id_children) {
                $error = $this->module->l('You can not add the same product', 'AdminUpsellontroller');
            } else {
                $isset_children = UpsellExtraProduct::getIssetChildren($id_parent, $id_children);
                if (!empty($isset_children)) {
                    $error = $this->module->l('You already have this product', 'AdminUpsellontroller');
                } else {
                    $extra_product = new UpsellExtraProduct();
                    $extra_product->id_parent = $id_parent;
                    $extra_product->id_children = $id_children;
                    $extra_product->add();
                }
            }
        }
        if ($action_type == 'delete') {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
                DELETE FROM '._DB_PREFIX_.'upsellextraproduct
                WHERE id_parent = '.(int)$id_parent.'
                AND id_children = '.(int)$id_children);
        }
        die(Tools::jsonEncode(array('content' => $this->module->renderExtraProductContent($id_parent), 'error' => $error)));
    }
}
