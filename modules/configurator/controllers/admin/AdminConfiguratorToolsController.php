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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__) . '/../../classes/ConfiguratorModel.php');
require_once(dirname(__FILE__) . '/../../classes/helper/DMTools.php');
require_once(dirname(__FILE__) . '/../../DmCache.php');

class AdminConfiguratorToolsController extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    /**
     * translation
     * @param type $string
     * @param type $specific
     * @return type
     */
    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        $module_name = 'configurator';
        $string = str_replace('\'', '\\\'', $string);
        return Translate::getModuleTranslation($module_name, $string, __CLASS__);
    }

    public function initContent()
    {
        $this->display = 'view';
        $this->toolbar_title = 'Tools';
        parent::initContent();
    }

    public function renderView()
    {
        switch (Tools::getValue('view')) {
            case "remove_attributegroup":
                $this->removeAttributeGroup();
                break;
            case "refresh":
                DMTools::updateToolsParameters();
                break;
            case "reset_override":
                $data = array(
                    'reset_override_result' => DMTools::resetOverrides()
                );
                return $this->renderViewTools($data);
                break;
            // PRODUCTS
            case "remove_products":
                $this->renderViewRemoveProducts();
                break;
            case "get_current_product":
                $counters = $this->getProductsCounters();
                $next = $counters['current'] + 1;
                $return = array(
                    'current' => (int)Configuration::get('CONFIGURATOR_DELETE_PRODUCT_CURRENT'),
                    'url' => $this->context->link->getAdminLink('AdminConfiguratorTools')
                        . "&view=remove_products&run=1&total=" . $counters['total'] . "&current=" . $next
                );
                die(json_encode($return));
            // ATTRIBUTES
            case "remove_attributes":
                $this->renderViewRemoveAttributes();
                break;
            case "get_current_attribute":
                $counters = $this->getAttributesCounters();
                $next = $counters['current'] + 1;
                $return = array(
                    'current' => (int)Configuration::get('CONFIGURATOR_DELETE_ATTRIBUTE_CURRENT'),
                    'url' => $this->context->link->getAdminLink('AdminConfiguratorTools')
                        . "&view=remove_attributes&run=1&total=" . $counters['total'] . "&current=" . $next
                );
                die(json_encode($return));
            case "clean_database":
                $this->cleanDatabase();
                break;
            // EMPTY CART
            case "remove_empty_cart":
                $this->renderViewRemoveEmptyCart();
                break;
            case "get_current_empty_cart":
                $counters = $this->getEmptyCartCounters();
                $next = $counters['current'] + 1;
                $return = array(
                    'current' => (int)$counters['current'],
                    'url' => $this->context->link->getAdminLink('AdminConfiguratorTools')
                        . "&view=remove_empty_cart&run=1&total=" . $counters['total'] . "&current=" . $next
                );
                die(json_encode($return));
            case "clean_cache":
                $this->cleanCache();
                break;
        }
        return $this->renderViewTools();
    }

    public function renderViewTools($data = array())
    {
        $tools_link = $this->context->link->getAdminLink('AdminConfiguratorTools');
        $cron_link = str_replace(
            'configurator',
            'modules/configurator',
            $this->context->link->getModuleLink('configurator')
        );
        $cron_link .= '/cron.php?token=' . md5(_COOKIE_KEY_);

        if (!empty($data)) {
            $this->context->smarty->assign($data);
        }

        $this->context->smarty->assign(array(
            'view' => Tools::getValue('view'),
            'refresh_link' => $tools_link . "&view=refresh",
            'reset_override_link' => $tools_link . "&view=reset_override",
            // PRODUCTS
            'products_counters' => $this->getProductsCounters(),
            'remove_products_link' => $tools_link . "&view=remove_products&run=1",
            'current_product_link' => $tools_link . "&view=get_current_product",
            // ATTRIBUTE GROUP
            'attributegroup_id' => (int)(Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID') !== false),
            'remove_attributegroup_link' => $tools_link . "&view=remove_attributegroup",
            // ATTRIBUTES
            'attributes_counters' => $this->getAttributesCounters(),
            'remove_attributes_link' => $tools_link . "&view=remove_attributes&run=1",
            'current_attribute_link' => $tools_link . "&view=get_current_attribute",
            // CLEAN DATABASE
            'clean_database_link' => $tools_link . "&view=clean_database",
            // EMPTY CART
            'empty_cart_counters' => $this->getEmptyCartCounters(),
            'remove_empty_cart_link' => $tools_link . "&view=remove_empty_cart&run=1",
            'current_empty_cart_link' => $tools_link . "&view=get_current_empty_cart",
            'remove_empty_cart_cron' => $cron_link . '&action=delete_empty_cart&day=2&number=50',
            // CACHE
            'clean_cache_link' => $tools_link . '&view=clean_cache',
            'cache_cleaned' => (bool)(Tools::getValue('view') === 'clean_cache'),
            'use_cache_ps' => (bool)Configuration::get('CONFIGURATOR_CACHE_PS')
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'configurator/views/templates/admin/tools/index.tpl');
    }

    public function renderViewRemoveProducts()
    {
        $counters = $this->getProductsCounters();
        $run = (Tools::getValue('run') === '1');
        $end = ($counters['current'] >= $counters['total']);
        $next = ($run) ? $counters['current'] + 1 : 0;

        $run_remove_products_link = $this->context->link->getAdminLink('AdminConfiguratorTools')
            . "&view=remove_products&run=1&total=" . $counters['total'] . "&current=" . $next;

        if ($run && $counters['current'] > 0) {
            // Suppression d'un produit
            $this->deleteFirstConfiguratedProduct();
        } else {
            if (!$run) {
                // En attente de suppression
                $run = false;
            }
        }

        if ($end) {
            $this->cleanCategoriesPositions();
        }

        $this->context->smarty->assign(array(
            'counters' => $counters,
            'run_remove_products_link' => $run_remove_products_link,
            'run' => $run,
            'end' => $end,
            'progress' => floor($counters['current'] / $counters['total'] * 100)
        ));
        echo $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'configurator/views/templates/admin/tools/actions/remove_products.tpl'
        );
        die();
    }

    public function renderViewRemoveAttributes()
    {
        $counters = $this->getAttributesCounters();
        $run = (Tools::getValue('run') === '1');
        $end = ($counters['current'] >= $counters['total']);
        $next = ($run) ? $counters['current'] + 1 : 0;

        $run_remove_attributes_link = $this->context->link->getAdminLink('AdminConfiguratorTools')
            . "&view=remove_attributes&run=1&total=" . $counters['total'] . "&current=" . $next;

        if ($run && $counters['current'] > 0) {
            // Suppression d'un produit
            $this->deleteFirstAttribute();
        } else {
            if (!$run) {
                // En attente de suppression
                $run = false;
            }
        }

        if ($end) {
            $this->cleanCategoriesPositions();
        }

        $this->context->smarty->assign(array(
            'counters' => $counters,
            'run_remove_attributes_link' => $run_remove_attributes_link,
            'run' => $run,
            'end' => $end,
            'progress' => floor($counters['current'] / $counters['total'] * 100)
        ));
        echo $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'configurator/views/templates/admin/tools/actions/remove_attributes.tpl'
        );
        die();
    }

    public function renderViewRemoveEmptyCart()
    {
        $cadence = (int)Tools::getValue('cadence', 5);
        $counters = $this->getEmptyCartCounters();
        $run = (Tools::getValue('run') === '1');
        $end = ($counters['current'] >= $counters['total']);
        $next = ($run) ? $counters['current'] + $cadence : $cadence - 1;

        $run_remove_empty_cart_link = $this->context->link->getAdminLink('AdminConfiguratorTools');
        $run_remove_empty_cart_link .= "&view=remove_empty_cart&run=1&total=" . $counters['total'];
        $run_remove_empty_cart_link .= "&current=" . $next . "&cadence=" . $cadence;

        if ($run && $counters['current'] > 0) {
            // Suppression d'un produit
            DMTools::deleteUnusedCartDetail($cadence);
        } else {
            if (!$run) {
                // En attente de suppression
                $run = false;
            }
        }

        $this->context->smarty->assign(array(
            'counters' => $counters,
            'run_remove_empty_cart_link' => $run_remove_empty_cart_link,
            'run' => $run,
            'end' => $end,
            'progress' => floor($counters['current'] / $counters['total'] * 100)
        ));
        echo $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'configurator/views/templates/admin/tools/actions/remove_empty_cart.tpl'
        );
        die();
    }

    private function getProductsCounters()
    {
        if (Tools::getValue('current') !== false) {
            Configuration::updateValue('CONFIGURATOR_DELETE_PRODUCT_CURRENT', (int)Tools::getValue('current'));
        }
        return array(
            'total' => (int)Configuration::get('CONFIGURATOR_DELETE_PRODUCT_TOTAL'),
            'current' => (int)Configuration::get('CONFIGURATOR_DELETE_PRODUCT_CURRENT')
        );
    }

    private function getAttributesCounters()
    {
        if (Tools::getValue('current') !== false) {
            Configuration::updateValue('CONFIGURATOR_DELETE_ATTRIBUTE_CURRENT', (int)Tools::getValue('current'));
        }
        return array(
            'total' => (int)Configuration::get('CONFIGURATOR_DELETE_ATTRIBUTE_TOTAL'),
            'current' => (int)Configuration::get('CONFIGURATOR_DELETE_ATTRIBUTE_CURRENT')
        );
    }

    private function getEmptyCartCounters()
    {
        if (Tools::getValue('current') !== false || Tools::getValue('first')) {
            Configuration::updateValue('CONFIGURATOR_DELETE_EMPTY_CART_CURRENT', (int)Tools::getValue('current'));
        }
        $current = (int)Configuration::get('CONFIGURATOR_DELETE_EMPTY_CART_CURRENT', null, null, null, 0);
        $total = (int)DMTools::countUnusedCartDetail(2) + $current - 1;
        return array(
            'total' => ($total > 0) ? $total : 0,
            'current' => $current
        );
    }

    private function deleteFirstConfiguratedProduct()
    {
        $id_product = ConfiguratorModel::findFirstConfiguratedProductId();
        $product = new Product($id_product);
        if (Validate::isLoadedObject($product)) {
            $product->deleteConfigurator();
        }
    }

    private function deleteFirstAttribute()
    {
        $id_attribute = DMTools::findFirstAttributeId();
        $attribute = new Attribute($id_attribute);
        if (Validate::isLoadedObject($attribute)) {
            $attribute->delete();
        }
    }

    private function cleanCategoriesPositions()
    {
        return;
    }

    public function removeAttributeGroup()
    {
        $id_attribute_group = (int)Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID');
        $attributeGroup = new AttributeGroup($id_attribute_group);
        if (Validate::isLoadedObject($attributeGroup)) {
            $attributeGroup->delete();
        }
        Configuration::deleteByName('CONFIGURATOR_ATTRIBUTEGROUP_ID');
    }

    private function cleanDatabase()
    {
        // CLEAN SHOP
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_shop`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_shop`.`id_configurator` = c.`id_configurator` )';
        Db::getInstance()->execute($sql);

        // CLEAN STEP
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step`.`id_configurator` = c.`id_configurator` )';
        Db::getInstance()->execute($sql);

        // CLEAN CART DETAIL WITHOUT CONFIGURATOR
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_cart_detail`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_cart_detail`.`id_configurator` = c.`id_configurator` )';
        Db::getInstance()->execute($sql);

        // CLEAN CART DETAIL WITHOUT CART
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_cart_detail`';
        $sql .= ' WHERE id_order = 0';
        $sql .= ' AND NOT EXISTS ( SELECT id_cart FROM `' . _DB_PREFIX_ . 'cart` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_cart_detail`.`id_cart` = c.`id_cart` )';
        Db::getInstance()->execute($sql);

        // CLEAN CART DETAIL ATTACHMENT
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_cartdetail_attachment`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_cart_detail` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_cartdetail_attachment`.`id_configurator_cart_detail` =';
        $sql .= ' c.`id_configurator_cart_detail` )';
        Db::getInstance()->execute($sql);

        // CLEAN ATTACHMENT
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_attachment`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_cartdetail_attachment` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_attachment`.`id_configurator_attachment` =';
        $sql .= ' c.`id_configurator_attachment` )';
        Db::getInstance()->execute($sql);

        // CLEAN STEP OPTION
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_option`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_option`.`id_configurator_step` =';
        $sql .= ' c.`id_configurator_step` )';
        Db::getInstance()->execute($sql);

        // CLEAN STEP OPTION LANG
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_option_lang`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step_option` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_option_lang`.`id_configurator_step_option` =';
        $sql .= ' c.`id_configurator_step_option` )';
        Db::getInstance()->execute($sql);

        // CLEAN STEP DISPLAY CONDITION GROUP
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_display_condition_group`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step` cs';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_display_condition_group`.`id_configurator_step` =';
        $sql .= ' cs.`id_configurator_step` ) AND `'
            . _DB_PREFIX_ . 'configurator_step_display_condition_group`.id_configurator_step > 0';
        Db::getInstance()->execute($sql);
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_display_condition_group`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step_option` cso';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_display_condition_group`.`id_configurator_step_option` =';
        $sql .= ' cso.`id_configurator_step_option` ) AND `'
            . _DB_PREFIX_ . 'configurator_step_display_condition_group`.id_configurator_step_option > 0';
        Db::getInstance()->execute($sql);

        // CLEAN STEP DISPLAY CONDITION
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_display_condition`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step_display_condition_group` c';
        $sql .= ' WHERE `' . _DB_PREFIX_;
        $sql .= 'configurator_step_display_condition`.`id_configurator_step_display_condition_group` =';
        $sql .= ' c.`id_configurator_step_display_condition_group` )';
        Db::getInstance()->execute($sql);

        // CLEAN STEP GROUP
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_group`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_group`.`id_configurator_step` =';
        $sql .= ' c.`id_configurator_step` )';
        Db::getInstance()->execute($sql);

        // CLEAN STEP LANG
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_lang`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_lang`.`id_configurator_step`';
        $sql .= ' = c.`id_configurator_step` )';
        Db::getInstance()->execute($sql);

        // CLEAN TAB
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_tab`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_tab`.`id_configurator` = c.`id_configurator` )';
        Db::getInstance()->execute($sql);

        // CLEAN TAB LANG
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_step_tab_lang`';
        $sql .= ' WHERE NOT EXISTS ( SELECT NULL FROM `' . _DB_PREFIX_ . 'configurator_step_tab` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_step_tab_lang`.`id_configurator_step_tab` =';
        $sql .= ' c.`id_configurator_step_tab` )';
        Db::getInstance()->execute($sql);

        return true;
    }

    private function cleanCache()
    {
        return DmCache::getInstance()->clean();
    }
}
