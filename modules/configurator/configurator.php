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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/dmconcept/DmDispatcher.php');
require_once(dirname(__FILE__) . '/classes/ConfiguratorAttribute.php');
require_once(dirname(__FILE__) . '/classes/ConfiguratorModel.php');
require_once(dirname(__FILE__) . '/classes/step/ConfiguratorStepAbstract.php');
require_once(dirname(__FILE__) . '/classes/step/ConfiguratorStepTypeAttributeModel.php');
require_once(dirname(__FILE__) . '/classes/step/ConfiguratorStepTypeFeatureModel.php');
require_once(dirname(__FILE__) . '/classes/ConfiguratorCartDetailModel.php');
require_once(dirname(__FILE__) . '/classes/OrderDetailHelper.php');
require_once(dirname(__FILE__) . '/classes/helper/DMTools.php');
require_once(dirname(__FILE__) . '/DmCache.php');

class Configurator extends Module
{
    const ERROR_STEP_REQUIRED = 'ERROR_STEP_REQUIRED';
    const ERROR_STEP_FILEUPLOAD_REQUIRED = 'ERROR_STEP_FILEUPLOAD_REQUIRED';
    const ERROR_STEP_OCCURED = 'ERROR_STEP_OCCURED';
    const ERROR_PRICELIST_VALUES = 'ERROR_PRICELIST_VALUES';
    const ERROR_PRICELIST_VALUES_FOR = 'ERROR_PRICELIST_VALUES_FOR';
    const ERROR_MAXOPTIONS_REACHED = 'ERROR_MAXOPTIONS_REACHED';
    const ERROR_MINOPTIONS_REACHED = 'ERROR_MINOPTIONS_REACHED';
    const ERROR_MAXQTY_REACHED = 'ERROR_MAXQTY_REACHED';
    const ERROR_STEPQTY_REACHED = 'ERROR_STEPQTY_REACHED';
    const ERROR_FORMULA = 'ERROR_FORMULA';
    const ERROR_FORMULA_VALUE = 'ERROR_FORMULA_VALUE';
    const ERROR_FORMULA_VALUE_PREVIOUS = 'ERROR_FORMULA_VALUE_PREVIOUS';
    const ERROR_FORMULA_VALUE_EMPTY = 'ERROR_FORMULA_VALUE_EMPTY';
    const ERROR_FORMULA_METHOD = 'ERROR_FORMULA_METHOD';
    const ERROR_FORMULA_ADAPTER = 'ERROR_FORMULA_ADAPTER';
    const ERROR_FORMULA_DIVISION_BY_ZERO = 'Division by zero';
    const ERROR_MIN_REQUIRED = 'ERROR_MIN_REQUIRED';

    const EMAIL_ORDER_CONF_TPL = 'order_conf';

    const MAX_FLOAT_PRECISION_MULTIPLIER = 1000000;

    protected static $routes = array(
        'module-configurator-attachment' => array(
            'controller' => 'attachment',
            'rule' => 'configurator-attachment/{token}',
            'keywords' => array(
                'token' => array('regexp' => '[a-z0-9]+', 'param' => 'token')
            ),
            'params' => array(
                'fc' => 'module',
                'module' => 'configurator',
                'controller' => 'attachment'
            )
        )
    );

    public function __construct()
    {
        $this->name = 'configurator';
        $this->tab = 'front_office_features';
        $this->version = '4.31.0';
        $this->ps_versions_compliancy = array('min' => '1.6.0.4', 'max' => _PS_VERSION_);
        $this->module_key = '3a81b352c417ac589bebe18df014530f';
        $this->author = 'DMConcept';
        $this->need_instance = 1;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Configurator of product');
        $this->description = $this->l('Create a configurator for a product ');
        $this->description .= $this->l(
            '(product customizable at will and price calculated according to specific parameters)'
        );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install($keep = false)
    {
        $install_attribute_group = true;
        Configuration::updateValue('CONFIGURATOR_DISPLAY_TOOLTIP_FANCYBOX', 0);
        Configuration::updateValue('CONFIGURATOR_PROGRESS_COMPENENT', 0);
        Configuration::updateValue('CONFIGURATOR_NAME_STEPS', 0);
        Configuration::updateValue('CONFIGURATOR_STEP_PRICE', 0);
        Configuration::updateValue('CONFIGURATOR_PROGRESSIVE_DISPLAY', 0);
        Configuration::updateValue('CONFIGURATOR_PROGRESS_START_COLOR', '');
        Configuration::updateValue('CONFIGURATOR_PROGRESS_END_COLOR', '');
        Configuration::updateValue('CONFIGURATOR_TOKEN', Tools::encrypt('CONFIGURATOR-' . time()));
        Configuration::updateValue('CONFIGURATOR_DISABLE_ADDTOCART_BTN', 0);
        Configuration::updateValue('CONFIGURATOR_TOOLTIP_DISPLAY', 0);
        Configuration::updateValue('CONFIGURATOR_FLOATING_PREVIEW', 0);
        Configuration::updateValue('CONFIGURATOR_MODAL_CONFIRMATION_CART', 0);
        Configuration::updateValue('CONFIGURATOR_MODAL_CONFIRMATION_CART_ACCEPTATION', 0);
        Configuration::updateValue('CONFIGURATOR_CACHE_PS', 1);
        Configuration::updateValue('CONFIGURATOR_CUSTOMIZATION_SEPARATOR_OPTION_HTML', ', ');
        Configuration::updateValue('CONFIGURATOR_POPOVER_TRIGGER', 'hover');
        Configuration::updateValue('CONFIGURATOR_DISPLAY_PRICE', null);

        
        $languages = Language::getLanguages();
        $value = array();
        foreach ($languages as $language) {
            $value[$language['id_lang']] = $this->l('Configurator');
        }
        Configuration::updateValue('CONFIGURATOR_CUSTOMIZATION_FIELD_NAME', serialize($value));

        include(dirname(__FILE__) . '/sql/install.php');
        if (!$keep) {
            $install_attribute_group = true; // $this->_installAttributeGroup();
            include(dirname(__FILE__) . '/sql/install_additional.php');
        }

        DMTools::updateToolsParameters();

        $install = $install_attribute_group
            && $this->_installModuleTab('AdminConfigurator', $this->l('Product configurator'))
            && $this->_installModuleTab('AdminConfiguratorSteps', $this->l('Configurator steps'), 0)
            && $this->_installModuleTab('AdminConfiguratorTabs', $this->l('Configurator tabs'), 0)
            && $this->_installModuleTab('AdminConfiguratorTools', $this->l('Configurator tools'), 0)
            && $this->_installMeta()
            && parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayOrderDetail')
            && $this->registerHook('displayFooter')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('actionDispatcher')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionObjectAttributeDeleteAfter')
            && $this->registerHook('actionObjectFeatureValueDeleteAfter')
            && $this->registerHook('actionObjectAttributeGroupDeleteAfter')
            && $this->registerHook('actionObjectOrderDetailAddAfter')
            && $this->registerHook('actionObjectAttributeUpdateAfter')
            && $this->registerHook('actionObjectAttributeAddAfter')
            && $this->registerHook('actionAdminAttributesGroupsFormModifier')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('displayAdminCartsView')
            && $this->registerHook('actionObjectCartDeleteAfter')
            && $this->registerHook('actionAfterDeleteProductInCart')
            && $this->registerHook('displayCustomization')
            && $this->registerHook('displayAdminOrder');

        if ($install) {
            $this->clearCache();
        }

        return $install;
    }

    public function uninstall($keep = false)
    {
        $uninstall_attribute_group = true;
        Configuration::deleteByName('CONFIGURATOR_DISPLAY_TOOLTIP_FANCYBOX');
        Configuration::deleteByName('CONFIGURATOR_PROGRESS_COMPENENT');
        Configuration::deleteByName('CONFIGURATOR_NAME_STEPS');
        Configuration::deleteByName('CONFIGURATOR_STEP_PRICE');
        Configuration::deleteByName('CONFIGURATOR_PROGRESSIVE_DISPLAY');
        Configuration::deleteByName('CONFIGURATOR_PROGRESS_START_COLOR');
        Configuration::deleteByName('CONFIGURATOR_PROGRESS_END_COLOR');
        Configuration::deleteByName('CONFIGURATOR_CUSTOMIZATION_FIELD_NAME');
        Configuration::deleteByName('CONFIGURATOR_CUSTOMIZATION_SEPARATOR_OPTION_HTML');
        Configuration::deleteByName('CONFIGURATOR_DELETE_PRODUCT_TOTAL');
        Configuration::deleteByName('CONFIGURATOR_DELETE_PRODUCT_CURRENT');
        Configuration::deleteByName('CONFIGURATOR_DELETE_ATTRIBUTE_TOTAL');
        Configuration::deleteByName('CONFIGURATOR_DELETE_ATTRIBUTE_CURRENT');
        Configuration::deleteByName('CONFIGURATOR_DISABLE_ADDTOCART_BTN');
        Configuration::deleteByName('CONFIGURATOR_TOOLTIP_DISPLAY');
        Configuration::deleteByName('CONFIGURATOR_FLOATING_PREVIEW');
        Configuration::deleteByName('CONFIGURATOR_MODAL_CONFIRMATION_CART');
        Configuration::deleteByName('CONFIGURATOR_MODAL_CONFIRMATION_CART_ACCEPTATION');
        Configuration::deleteByName('CONFIGURATOR_CACHE_PS');
        Configuration::deleteByName('CONFIGURATOR_POPOVER_TRIGGER');
        Configuration::deleteByName('CONFIGURATOR_DISPLAY_PRICE');


        if (!$keep) {
            $uninstall_attribute_group = $this->_uninstallAttributeGroup();
            ConfiguratorCartDetailModel::deleteAllProductCartDetail();
            include(dirname(__FILE__) . '/sql/uninstall.php');
        }
        return $uninstall_attribute_group
            && $this->_uninstallModuleTab('AdminConfigurator')
            && $this->_uninstallModuleTab('AdminConfiguratorSteps')
            && $this->_uninstallModuleTab('AdminConfiguratorTabs')
            && $this->_uninstallModuleTab('AdminConfiguratorTools')
            && $this->_uninstallMeta()
            && parent::uninstall();
    }

    public function reset()
    {
        if ($this->uninstall(true)) {
            return $this->install(true);
        }
        return false;
    }

    private function _installAttributeGroup()
    {
        $attributeGroup = new AttributeGroup();
        $languages = Language::getLanguages();

        $attributeGroup->group_type = 'select';
        foreach ($languages as $language) {
            $id_lang = (int)$language['id_lang'];
            if (Validate::isLoadedObject($language)) {
                $id_lang = $language->id;
            }

            $attributeGroup->name[$id_lang] = 'CONFIGURATOR';
            $attributeGroup->public_name[$id_lang] = $this->l('Configuration content');
        }
        $result = $attributeGroup->save();

        if ($result) {
            Configuration::updateValue('CONFIGURATOR_ATTRIBUTEGROUP_ID', (int)$attributeGroup->id);
        }

        return (bool)$result;
    }

    public function _uninstallAttributeGroup()
    {
        $result = true;
        $id_attribute_group = (int)Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID');
        $attributeGroup = new AttributeGroup($id_attribute_group);
        if (Validate::isLoadedObject($attributeGroup)) {
            $result = $attributeGroup->delete();
            Configuration::deleteByName('CONFIGURATOR_ATTRIBUTEGROUP_ID');
        }

        return (bool)$result;
    }

    private function _installMeta()
    {
        // FIX : désinstallation des metas au cas où elles pourraient déjà exister
        $this->_uninstallMeta();

        $languages = Language::getLanguages();
        $route = new Meta();
        $route->page = 'module-configurator-default';
        foreach ($languages as $language) {
            $route->title[(int)$language['id_lang']] = $this->l('Product configurator');
        }

        $route->url_rewrite = 'configurator';
        return $route->save();
    }

    private function _uninstallMeta()
    {
        $routes = Meta::getMetas();
        foreach ($routes as $route) {
            if ($route['page'] === 'module-configurator-default') {
                $r = new Meta((int)$route['id_meta']);
                if (Validate::isLoadedObject($r)) {
                    return $r->delete();
                }
            }
        }
        return true;
    }

    public function _installModuleTab($tabClass, $tabName, $active = 1, $tabParent = 'AdminCatalog')
    {
        // FIX : désinstallation des tabs au cas ou elles pourraient déjà exister
        $this->_uninstallModuleTab($tabClass);

        $idTabParent = Tab::getIdFromClassName($tabParent);
        $tab = new Tab();
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $tab->name[(int)$language['id_lang']] = $tabName;
        }

        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idTabParent;
        $tab->active = $active;
        if (!$tab->save()) {
            return false;
        }
        return true;
    }

    private function _uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            if (Validate::isLoadedObject($tab)) {
                return $tab->delete();
            }
        }
        return true;
    }

    public function disable($force_all = false)
    {
        if (DMTools::getVersionMajor() < 17) {
            $this->uninstallOverrides();
        }
        return parent::disable($force_all);
    }

    public function enable($force_all = false)
    {
        if (DMTools::getVersionMajor() < 17) {
            $this->uninstallOverrides();
            $this->installOverrides();
        }
        return parent::enable($force_all);
    }

    public function clearCache()
    {
        if (DMTools::getVersionMajor() < 17) {
            if (file_exists(_PS_ROOT_DIR_.'/cache/class_index.php')) {
                unlink(_PS_ROOT_DIR_.'/cache/class_index.php');
            }
            //Cache::getInstance()->flush();
            DmCache::getInstance()->clean();
        } else {
            $cache = new PrestaShop\PrestaShop\Adapter\Cache\CacheClearer();
            $cache->clearAllCaches();
        }
    }

    /**
     * Copy from Dispatcher setRequestUri
     * @see DispatcherCore::setRequestUri()
     * @return string $request_uri
     */
    private function getRequestUri()
    {
        // Get request uri (HTTP_X_REWRITE_URL is used by IIS)
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        $request_uri = rawurldecode($request_uri);

        if (isset(Context::getContext()->shop) && is_object(Context::getContext()->shop)) {
            $request_uri = preg_replace(
                '#^' . preg_quote(Context::getContext()->shop->getBaseURI(), '#') . '#i',
                '/',
                $request_uri
            );
        }

        // If there are several languages, get language from uri
        if (Language::isMultiLanguageActivated()) {
            if (preg_match('#^/([a-z]{2})(?:/.*)?$#', $request_uri, $m)) {
                $_GET['isolang'] = $m[1];
                $request_uri = Tools::substr($request_uri, 3);
            }
        }

        return $request_uri;
    }

    /**
     * get param from product rule route
     * @param array $route (product route)
     * @return mixed param or false
     */
    private function getParamFromRoute(array $route, $param)
    {
        list($uri) = explode('?', $this->getRequestUri());
        $rule = $route['rule'];
        $regexp = preg_quote($rule, '#');
        $keywords = $route['keywords'];

        $transform_keywords = array();
        preg_match_all(
            '#\\\{(([^{}]*)\\\:)?(' . implode('|', array_keys($keywords)) . ')(\\\:([^{}]*))?\\\}#',
            $regexp,
            $m
        );
        for ($i = 0, $total = count($m[0]); $i < $total; $i++) {
            $prepend = $m[2][$i];
            $keyword = $m[3][$i];
            $append = $m[5][$i];
            $transform_keywords[$keyword] = array(
                'required' => isset($keywords[$keyword]['param']),
                'prepend' => Tools::stripslashes($prepend),
                'append' => Tools::stripslashes($append)
            );

            $prepend_regexp = $append_regexp = '';
            if ($prepend || $append) {
                $prepend_regexp = '(' . preg_quote($prepend);
                $append_regexp = preg_quote($append) . ')?';
            }

            if (isset($keywords[$keyword]['param'])) {
                $regexp = str_replace(
                    $m[0][$i],
                    $prepend_regexp . '(?P<' . $keywords[$keyword]['param'] . '>'
                        . $keywords[$keyword]['regexp'] . ')' . $append_regexp,
                    $regexp
                );
            } else {
                $regexp = str_replace(
                    $m[0][$i],
                    $prepend_regexp . '(' . $keywords[$keyword]['regexp'] . ')' . $append_regexp,
                    $regexp
                );
            }
        }

        $keywords = $transform_keywords;
        $regexp = '#^/' . $regexp . '$#u';
        if (preg_match($regexp, $uri, $m) && !empty($m[$param])) {
            return $m[$param];
        }

        return false;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $this->_postProcess();

        $this->context->smarty->assign(array(
            'configurator_link' => $this->context->link->getAdminLink('AdminConfigurator'),
            'module_dir' => $this->_path,
            'configurator_tools_link' => $this->context->link->getAdminLink('AdminConfiguratorTools'),
            'need_tools_update' => DMTools::needToolsUpdate()
        ));

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl')
            . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/need_tools_update.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitConfiguratorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    public function cleanCartConfiguratorRadical()
    {
        ini_set('max_execution_time', 300);
        $carts = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "configurator_cart_detail`");

        foreach ($carts as $cart) {
            $id = (int)$cart['id_configurator_cart_detail'];

            $cart_detail = new ConfiguratorCartDetailModel($id);
            if (Validate::isLoadedObject($cart_detail)) {
                $cart_detail->delete();
            }
        }
    }

    public function cleanProductConfigurator()
    {
        /**
         * Delete product which have no configurator cart details
         */
        $products = Db::getInstance()->executeS(
            'SELECT *'
            . 'FROM `' . _DB_PREFIX_ . 'product` '
            . 'WHERE `is_configurated` = 1 LIMIT 100'
        );

        foreach ($products as $product) {
            $p = new Product((int)$product['id_product']);
            if (Validate::isLoadedObject($p)) {
                $p->delete();
            }
        }
    }

    /**
     * @todo: This funciton will be delete after the rewrite of the process of product creation
     */
    public function cleanCartConfigurator()
    {
        ini_set('max_execution_time', 300);
        $carts = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "configurator_cart_detail`");

        $aCartsExist = array();
        $aOrdersExist = array();
        /**
         * Vérification de plusieurs critères
         */
        foreach ($carts as $cart) {
            $cart_exist = false;
            $order_exist = false;

            // Criteria the cart exist
            if (!isset($aCartsExist[(int)$cart['id_cart']])) {
                $cart_test = new Cart((int)$cart['id_cart']);

                if (Validate::isLoadedObject($cart_test)) {
                    $aCartsExist[(int)$cart['id_cart']] = $cart_test;
                    $cart_exist = true;
                }
            } else {
                $cart_test = $aCartsExist[(int)$cart['id_cart']];
                $cart_exist = true;
            }

            // Criteria the order exist
            if (!isset($aOrdersExist[(int)$cart['id_order']])) {
                $order_test = new Order((int)$cart['id_order']);

                if (Validate::isLoadedObject($order_test)) {
                    $aOrdersExist[(int)$cart['id_order']] = $order_test;
                    $order_exist = true;
                }
            } else {
                $order_exist = true;
            }

            /**
             * Delete unused product
             */
            if (!$cart_exist && !$order_exist) {
                echo "- Delete id_cart: " . $cart['id_cart'] . " + id_product:" . $cart['id_product'] . "<br />";
                // delete product
                $product = new Product((int)$cart['id_product']);
                if (Validate::isLoadedObject($product)) {
                    $product->delete();
                }

                // delete attribute
                $attribute = Db::getInstance()->getRow("SELECT * "
                    . "FROM `" . _DB_PREFIX_ . "attribute_lang` "
                    . "WHERE `name` = '" . pSQL($cart['attribute_key']) . "' "
                    . "AND `id_lang` =  '" . Context::getContext()->language->id . "'");
                if (isset($attribute['id_attribute'])) {
                    $delete_attribute = new Attribute((int)$attribute['id_attribute']);

                    if (Validate::isLoadedObject($delete_attribute)) {
                        $delete_attribute->delete();
                    }
                }

                if (count($cart_test->getProducts()) === 0) {
                    $cart_test->delete();
                    unset($aCartsExist[(int)$cart['id_cart']]);
                }

                // Delete configurator cart detail
                $configurator_detail = new ConfiguratorCartDetailModel((int)$cart['id_configurator_cart_detail']);
                if (Validate::isLoadedObject($configurator_detail)) {
                    $configurator_detail->delete();
                }
            }
            Configurator::cleanCache();
        }


        /**
         * Delete product which have no configurator cart details
         */
        $products = Db::getInstance()->executeS(
            'SELECT `p`.`id_product` as product_id, `cp`.*, `o`.*, `p`.`is_configurated` '
            . 'FROM `' . _DB_PREFIX_ . 'product` `p` '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'cart_product` `cp` ON `p`.`id_product` = `cp`.`id_product` '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` `o` ON `cp`.`id_cart` = `o`.`id_cart` '
            . 'WHERE p.`is_configurated` = 1 '
        );

        foreach ($products as $product) {
            $test_product = new Product((int)$product['product_id']);

            if ($product['id_cart'] == '' || $product['id_order'] != '') {
                echo "- Delete id_product:" . $product['product_id'];
                $test_product->delete();
                echo "<br />";
            }
        }

        /**
         * Delete unused attribute
         */
        $sql = ' SELECT * 
                FROM `' . _DB_PREFIX_ . 'attribute_lang` as `al`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` as `a`
                ON `a`.`id_attribute` = `al`.`id_attribute` 
                LEFT JOIN `' . _DB_PREFIX_ . 'configurator_cart_detail` as `cd`
                ON `cd`.`attribute_key` = `al`.`name` 
                WHERE `a`.`id_attribute_group` = ' . (int)Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID') .
            ' AND `id_configurator` IS NULL';
        $results = Db::getInstance()->executeS($sql);

        foreach ($results as $result) {
            $attribute = new Attribute((int)$result['id_attribute']);
            if (Validate::isLoadedObject($attribute)) {
                $attribute->delete();
            }
        }

        Configurator::cleanCache();
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $color_str = 'Choose a color with the color picker, or enter an HTML color (e.g. "lightblue", "#CC6600").';
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display tooltip with fancybox'),
                        'name' => 'CONFIGURATOR_DISPLAY_TOOLTIP_FANCYBOX',
                        'hint' => $this->l('Tooltip of step or option will be displayed in a fancybox window.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Progressive display steps'),
                        'name' => 'CONFIGURATOR_PROGRESSIVE_DISPLAY',
                        'hint' => $this->l(
                            'The steps will be displayed progressively as and when the customer\'s choices.'
                        ),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display progression of configuration'),
                        'name' => 'CONFIGURATOR_PROGRESS_COMPENENT',
                        'hint' => $this->l('Display total in percent of the current configuration.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display steps\'s name in preview'),
                        'name' => 'CONFIGURATOR_NAME_STEPS',
                        'hint' => $this->l('Display the name of each step in cart preview'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display step\'s price'),
                        'name' => 'CONFIGURATOR_STEP_PRICE',
                        'hint' => $this->l('Display the price of each step'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Progression start color '),
                        'name' => 'CONFIGURATOR_PROGRESS_START_COLOR',
                        'hint' => $this->l($color_str)
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Progression end color '),
                        'name' => 'CONFIGURATOR_PROGRESS_END_COLOR',
                        'hint' => $this->l($color_str)
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Customization field name'),
                        'name' => 'CONFIGURATOR_CUSTOMIZATION_FIELD_NAME',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Disable "Add to cart" button'),
                        'name' => 'CONFIGURATOR_DISABLE_ADDTOCART_BTN',
                        'hint' => $this->l('Disable "Add to cart" button until the progress bar is at 100%.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Display time of a tooltip in millisecond '),
                        'name' => 'CONFIGURATOR_TOOLTIP_DISPLAY',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Display this separator for option when HTML render '),
                        'name' => 'CONFIGURATOR_CUSTOMIZATION_SEPARATOR_OPTION_HTML',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate the floating preview?'),
                        'name' => 'CONFIGURATOR_FLOATING_PREVIEW',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Request confirmation from the customer before adding to cart?'),
                        'name' => 'CONFIGURATOR_MODAL_CONFIRMATION_CART',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Inform the customer about the right of withdrawal of custom products?'),
                        'name' => 'CONFIGURATOR_MODAL_CONFIRMATION_CART_ACCEPTATION',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable the prestashop cache'),
                        'name' => 'CONFIGURATOR_CACHE_PS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Popover trigger'),
                        'name' => 'CONFIGURATOR_POPOVER_TRIGGER',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'hover',
                                    'name' => $this->l('Hover only')
                                ),
                                array(
                                    'id' => 'hover click',
                                    'name' => $this->l('Hover and click')
                                ),
                                array(
                                    'id' => 'click',
                                    'name' => $this->l('Click only')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Display price'),
                        'name' => 'CONFIGURATOR_DISPLAY_PRICE',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'default',
                                    'name' => $this->l('Default')
                                ),
                                array(
                                    'id' => 'both',
                                    'name' => $this->l('With and without tax')
                                ),
                                array(
                                    'id' => 'tax_excl_only',
                                    'name' => $this->l('Without tax only')
                                ),
                                array(
                                    'id' => 'tax_incl_only',
                                    'name' => $this->l('With tax only')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CONFIGURATOR_DISPLAY_TOOLTIP_FANCYBOX' => (int)Configuration::get('CONFIGURATOR_DISPLAY_TOOLTIP_FANCYBOX'),
            'CONFIGURATOR_PROGRESS_COMPENENT' => (int)Configuration::get('CONFIGURATOR_PROGRESS_COMPENENT'),
            'CONFIGURATOR_NAME_STEPS' => (int)Configuration::get('CONFIGURATOR_NAME_STEPS'),
            'CONFIGURATOR_STEP_PRICE' => (int)Configuration::get('CONFIGURATOR_STEP_PRICE'),
            'CONFIGURATOR_PROGRESSIVE_DISPLAY' => (int)Configuration::get('CONFIGURATOR_PROGRESSIVE_DISPLAY'),
            'CONFIGURATOR_PROGRESS_START_COLOR' => Configuration::get('CONFIGURATOR_PROGRESS_START_COLOR'),
            'CONFIGURATOR_PROGRESS_END_COLOR' => Configuration::get('CONFIGURATOR_PROGRESS_END_COLOR'),
            'CONFIGURATOR_CUSTOMIZATION_FIELD_NAME' => $this->getConfigurationLang(
                'CONFIGURATOR_CUSTOMIZATION_FIELD_NAME'
            ),
            
            'CONFIGURATOR_DISABLE_ADDTOCART_BTN' => (int)Configuration::get('CONFIGURATOR_DISABLE_ADDTOCART_BTN'),
            'CONFIGURATOR_CUSTOMIZATION_SEPARATOR_OPTION_HTML' => Configuration::get('CONFIGURATOR_CUSTOMIZATION_SEPARATOR_OPTION_HTML'),
            'CONFIGURATOR_TOOLTIP_DISPLAY' => (int)Configuration::get('CONFIGURATOR_TOOLTIP_DISPLAY'),
            'CONFIGURATOR_FLOATING_PREVIEW' => (int)Configuration::get('CONFIGURATOR_FLOATING_PREVIEW'),
            'CONFIGURATOR_MODAL_CONFIRMATION_CART' => (int)Configuration::get('CONFIGURATOR_MODAL_CONFIRMATION_CART'),
            'CONFIGURATOR_MODAL_CONFIRMATION_CART_ACCEPTATION' => (int)Configuration::get('CONFIGURATOR_MODAL_CONFIRMATION_CART_ACCEPTATION'),
            'CONFIGURATOR_CACHE_PS' => (int)Configuration::get('CONFIGURATOR_CACHE_PS'),
            'CONFIGURATOR_POPOVER_TRIGGER' => Configuration::get('CONFIGURATOR_POPOVER_TRIGGER'),
            'CONFIGURATOR_DISPLAY_PRICE' => Configuration::get('CONFIGURATOR_DISPLAY_PRICE'),
        );
    }

    /**
     * Save form data.
     */
    protected function _postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if ($key == 'CONFIGURATOR_CUSTOMIZATION_FIELD_NAME') {
                $languages = Language::getLanguages();
                $values = $this->getConfigurationLang($key);
                foreach ($languages as $language) {
                    $val = Tools::getValue($key . '_' . $language['id_lang']);
                    if ($val) {
                        $values[$language['id_lang']] = $val;
                    }
                }
                $this->setConfigurationLang($key, $values);
            } else {
                Configuration::updateValue($key, Tools::getValue($key, Configuration::get($key)), true);
            }
        }
    }

    /**
     * get module routes except ourself !
     */
    protected function getModulesRoutes()
    {
        $module_routes = array();
        $module_list = Hook::getHookModuleExecList('moduleRoutes');
        foreach ($module_list as $module) {
            if ($module['module'] === $this->name) {
                continue;
            }
            $module_routes = array_merge(
                $module_routes,
                Hook::exec(
                    'moduleRoutes',
                    array('id_shop' => $this->context->shop->id),
                    (int)$module['id_module'],
                    true,
                    false
                )
            );
        }
        return $module_routes;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $controller_name = $this->context->controller->controller_name;
        $this->context->controller->addJs($this->_path . '/views/js/override-autocomplete.js');
        switch ($controller_name) {
            case 'AdminProducts':
                if (DMTools::getVersionMajor() == 16) {
                    $id_product = Tools::getValue('id_product');
                } else {
                    global $kernel;
                    $sfRouter = $kernel->getContainer()->get('router');
                    $pathInfo = $sfRouter->getContext()->getPathInfo();
                    if ($pathInfo !== '/') {
                        $pathParams = $sfRouter->match($pathInfo);
                    }
                    $id_product = isset($pathParams['id']) ? $pathParams['id'] : 0;
                }
                if (ConfiguratorModel::isConfiguratedProduct((int)$id_product)) {
                    $this->context->controller->addJs($this->_path . 'views/js/product-extra-configurated.js');
                    $this->context->controller->addCSS($this->_path . 'views/css/product-extra-configurated.css');
                } elseif (ConfiguratorModel::productHasConfigurator((int)$id_product)) {
                    $this->context->controller->addJs($this->_path . 'views/js/handlebars-v3.0.0.js');
                    $this->context->controller->addJs($this->_path . 'views/js/adminProductConfigurator.js');
                }
                $this->context->controller->addJs($this->_path . 'views/js/product-extra-configurator.js');
                $this->context->controller->addCSS($this->_path . 'views/css/back.css');
                break;
            case 'AdminConfiguratorSteps':
                $this->context->controller->addCSS($this->_path . 'views/css/back.css');
                break;
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     * + Add cartdetails from current cart to replace attributes in blockcart
     */
    public function hookHeader()
    {
        if (DMTools::getVersionMajor() >= 17) {
            $this->context->controller->addCSS($this->_path . '/views/css/front_17.css');
        } else {
            $this->context->controller->addJqueryUi('ui.widget');
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }
        $this->context->controller->addCSS($this->_path . '/views/css/uniform.default.css');

        $this->context->controller->addJs(__PS_BASE_URI__ . 'js/vendor/spin.js');
        $this->context->controller->addJs(__PS_BASE_URI__ . 'js/vendor/ladda.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/polyfill.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/tools/dispatch.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/dispatch.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/baseElement.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/baseSimpleElement.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/baseGroupElement.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/yesNo.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/fileUpload.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choices/base-choices.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choices/choices-simples.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choices/choices-multiples.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choice/base-choice.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choice/choice-simple.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choice/choice-simple-texture.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choice/choice-multiple.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/choice/choice-multiple-texture.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/inputs/baseInputs.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/quantity/baseQuantity.js');
        $this->context->controller->addJS(
            $this->_path . '/views/js/front/elements/quantity/choice/baseChoiceQuantity.js'
        );
        $this->context->controller->addJS(
            $this->_path . '/views/js/front/elements/quantity/choice/choiceSimpleQuantity.js'
        );
        $this->context->controller->addJS(
            $this->_path . '/views/js/front/elements/quantity/choice/choiceMultipleQuantity.js'
        );
        $this->context->controller->addJS(
            $this->_path . '/views/js/front/elements/quantity/select/selectSimpleQuantity.js'
        );

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/baseInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/textInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/numberInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/numberInputSlider.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/numberQuantityInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/textAreaInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/emailInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/dateInput.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/input/ralInput.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/pricelist/base_pricelist.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/pricelist/priceListSimple.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/pricelist/priceListTable.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/elements/select/select.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/select/option.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/elements/select/option_pricelist.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/modules/dispatch.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/modules/progressBar.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/modules/general.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/modules/base_module.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/modules/display/base_display.js');

        // CONFIGURATOR HOOK
        Hook::exec('configuratorHeader');

        $this->context->controller->addJS($this->_path . '/views/js/front/modules/summary/base_summary.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/errors/dispatch.js');

        $this->context->controller->addJs($this->_path . '/views/js/jquery-scrolltofixed-min.js');
        $this->context->controller->addJs($this->_path . '/views/js/jquery-scrollfix.js');
        $this->context->controller->addJs($this->_path . '/views/js/sticky.min.js');
        $this->context->controller->addJs($this->_path . '/views/js/jquery-regex.js');

        $this->context->controller->addJS($this->_path . '/views/js/front/io.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/io_bridge.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/bridge.js');
        $this->context->controller->addJS($this->_path . '/views/js/front/front.js');
        $this->context->controller->addJS($this->_path . '/views/js/front.js');


        $this->context->controller->addCss(
            _MODULE_DIR_ . 'configurator/views/css/front/modules/fancybox/jquery.fancybox.css'
        );
        $js_files = array(
            30 => array(
                'js/jquery/ui/jquery-ui.min.js',
                'js/vendor/spin.js',
                'js/vendor/ladda.js'
            ),
            100 => array(
                'modules/configurator/views/js/fileupload/jquery.iframe-transport.js',
                'modules/configurator/views/js/fileupload/jquery.fileupload.js',
                'modules/configurator/views/js/fileupload/jquery.fileupload-process.js',
                'modules/configurator/views/js/fileupload/jquery.fileupload-validate.js',
                'modules/configurator/views/js/jquery.unform-modified.js',
            ),
            101 => array(
                'modules/configurator/views/js/circle-progress.js',
                'modules/configurator/views/js/jquery-scrolltofixed-min.js',
                'modules/configurator/views/js/jquery-scrollfix.js',
                'modules/configurator/views/js/sticky.min.js',
                'modules/configurator/views/js/bootstrap.min.js',
                'modules/configurator/views/js/front/modules/fancybox/jquery.fancybox.js'
            ),
            102 => array(
                'modules/configurator/views/js/services/tools.js',
                'modules/configurator/views/js/services/scroll-fix.js',
                'modules/configurator/views/js/services/window-helper.js',
                'modules/configurator/views/js/services/layers-manager.js'
            ),
            150 => array(
                'modules/configurator/views/js/helpers/uploader/ajax.js'
            )
        );
        foreach ($js_files as $priority => $js_file) {
            $this->addJsConfigurator($js_file, $priority);
        }
    }

    private function addJsConfigurator($js_files, $priority)
    {
        foreach ($js_files as $js_file) {
            if (DMTools::getVersionMajor() < 17) {
                // PS 1.6
                $this->context->controller->addJs(__PS_BASE_URI__ . $js_file);
            } else {
                // PS 1.7
                if ($js_file === 'js/jquery/ui/jquery-ui.min.js') {
                    $this->context->controller->unregisterJavascript('jquery-ui');
                }
                $this->context->controller->registerJavascript(
                    'module-configurator' . rand(1000000, 99999999),
                    $js_file,
                    array(
                        'position' => 'bottom',
                        'priority' => $priority
                    )
                );
            }
        }
    }

    public function hookModuleRoutes()
    {
        $dispatcher = DmDispatcher::getInstance();
        // On met à jour les routes par défaut si surchargé par un autre module (ex. Advanced Url)
        $modules_routes = $this->getModulesRoutes();
        if (is_array($modules_routes) && count($modules_routes)) {
            foreach ($modules_routes as $module_route) {
                if (is_array($module_route) && count($module_route)) {
                    foreach ($module_route as $route => $route_details) {
                        if (array_key_exists('controller', $route_details)
                            && array_key_exists('rule', $route_details)
                            && array_key_exists('keywords', $route_details)
                            && array_key_exists('params', $route_details)
                        ) {
                            if (!isset($dispatcher->default_routes[$route])) {
                                $dispatcher->default_routes[$route] = array();
                            }
                            $dispatcher->default_routes[$route] = array_merge(
                                $dispatcher->default_routes[$route],
                                $route_details
                            );
                        }
                    }
                }
            }
        }
        // On récupère la route par défaut du Dispatcher
        $override_product_rule = $dispatcher->default_routes['product_rule'];
        // On regarde si ça n'a pas été surchargé dans la configuration
        $config_rule = Configuration::get('PS_ROUTE_product_rule');
        if ($config_rule) {
            $override_product_rule['rule'] = $config_rule;
        }

        /**
         * COMPATIBILITE MODULE MPCLEANURLS
         */
        if (Module::isInstalled('mpcleanurls')
            && Module::isEnabled('mpcleanurls')
            && Configuration::get('MPCLEANURLS_product_rule', (int)$this->context->language->id)
        ) {
            $override_product_rule['rule'] = Configuration::get(
                'MPCLEANURLS_product_rule',
                (int)$this->context->language->id
            );
        }

        /**
         * COMPATIBILITE MODULE PSSEOBOOSTER
         */
        if (Module::isInstalled('psseobooster')
            && Module::isEnabled('psseobooster')
            && Configuration::get('PSSEO_PRODUCT_RULE')
        ) {
            $override_product_rule['rule'] = Configuration::get('PSSEO_PRODUCT_RULE');
        }

        if (!empty($override_product_rule['keywords']['rewrite'])) {
            $override_product_rule['keywords']['rewrite']['param'] = 'url_rewrite';
        }
        $override_product_rule['params'] = array(
            'fc' => 'module',
            'module' => 'configurator'
        );
        unset($dispatcher);

        if (!$id_product = $this->getParamFromRoute($override_product_rule, 'id_product')) {
            /**
             * Compatibility with Advanced Url module. Case when product url does not contain ID.
             */
            $id_product = (int)Db::getInstance()->getValue(
                'SELECT `id_product` FROM `' . _DB_PREFIX_
                . 'product_lang` WHERE `link_rewrite` = "'
                . pSQL($this->getParamFromRoute($override_product_rule, 'url_rewrite'))
                . '" AND `id_lang` = '
                . (int)$this->context->language->id . ' AND `id_shop` = ' . (int)$this->context->shop->id
            );
        }

        if (!$id_product) {
            return self::$routes;
        }

        if (!ConfiguratorModel::productHasConfigurator((int)$id_product, true)
            && !ConfiguratorModel::isConfiguratedProduct((int)$id_product)
        ) {
            return self::$routes;
        }

        /**
         * COMPATIBILITE MODULE CLEANURLS
         */
        if (Module::isInstalled('cleanurls') && Module::isEnabled('cleanurls')) {
            $_GET['id_product'] = $id_product;
        }

        /**
         * COMPATIBILITE MODULE PRETTYURLS
         */
        if (Module::isInstalled('prettyurls') && Module::isEnabled('prettyurls')) {
            $_GET['fc'] = 'module';
            $_GET['module'] = 'configurator';
        }
        
        /**
         * Override route when necessary
         * Prevent problems when modules
         * doing some test on ProductController classname
         */
        return array_merge(self::$routes, array('product_rule' => $override_product_rule));
    }

    public function getErrorTranslation($key)
    {
        $translations = array(
            self::ERROR_STEP_REQUIRED => $this->l('You must choose an option for step : %s'),
            self::ERROR_STEP_FILEUPLOAD_REQUIRED => $this->l('You must upload at least one file for step : %s'),
            self::ERROR_STEP_OCCURED => $this->l('An error occured at step %s : %s'),
            self::ERROR_PRICELIST_VALUES => $this->l('The values entered are impossible for this step.'),
            self::ERROR_PRICELIST_VALUES_FOR => $this->l('The values entered are impossible for step : %s'),
            self::ERROR_MAXOPTIONS_REACHED => $this->l('You cannot choose more than %s options.'),
            self::ERROR_MINOPTIONS_REACHED => $this->l('You cannot choose a minimum of %s options.'),
            self::ERROR_MAXQTY_REACHED => $this->l('You cannot set a total quantity over %s.'),
            self::ERROR_STEPQTY_REACHED => $this->l('You need to set a step of %s quantity for step: %s'),
            self::ERROR_FORMULA => $this->l('Invalid formula.'),
            self::ERROR_FORMULA_VALUE => $this->l('One of values is not correct. You must write a number.'),
            self::ERROR_FORMULA_VALUE_PREVIOUS => $this->l(
                'One of values in a previous step is not correct and is required. You must write a number.'
            ),
            self::ERROR_FORMULA_VALUE_EMPTY => $this->l('You must fill all the fields.'),
            self::ERROR_FORMULA_METHOD => $this->l('Method in formula cannot be called.'),
            self::ERROR_FORMULA_ADAPTER => $this->l('Value written is not correct.'),
            self::ERROR_FORMULA_DIVISION_BY_ZERO => $this->l('Division by zero impossible.'),
            self::ERROR_MIN_REQUIRED => $this->l('You must choose at least %s options on step %s')
        );

        Hook::exec('configuratorActionGetErrorTranslation', array(
            'translations' => &$translations
        ));

        return (isset($translations[$key])) ? $translations[$key] : false;
    }

    /*public function hookActionDispatcher($params)
    {

    }*/

    public function duplicateConfigurator($id_product, $id_configurator_to_duplicate)
    {
        // Copy configurator
        $configurator = new ConfiguratorModel((int)$id_configurator_to_duplicate);
        return $configurator->duplicate((int)$id_product);
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        ConfiguratorStepOptionAbstract::deleteByIdOption((int)$params['object']->id, 'products');
        ConfiguratorModel::deleteByIdProduct((int)$params['object']->id);
    }

    public function hookActionObjectAttributeDeleteAfter($params)
    {
        ConfiguratorStepOptionAbstract::deleteByIdOption((int)$params['object']->id, 'attributes');
    }

    public function hookActionObjectFeatureValueDeleteAfter($params)
    {
        ConfiguratorStepOptionAbstract::deleteByIdOption((int)$params['object']->id, 'features');
    }

    public function hookActionObjectAttributeGroupDeleteAfter($params)
    {
        ConfiguratorStepAbstract::deleteStepsByIdOptionGroup((int)$params['object']->id, 'cs.type = "attributes"');
    }

    public function hookActionObjectOrderDetailAddAfter($params)
    {
        OrderDetailHelper::generateConfiguratorOrderDetail($params);
    }

    public function hookActionObjectAttributeUpdateAfter($params)
    {
        $attribute = new ConfiguratorAttribute((int)$params['object']->id);
        $attribute->texture_image = (boolean)Tools::getValue('texture_image');
        $attribute->ref_ral = Tools::getValue('ref_ral');
        $attribute->update();
    }

    public function hookActionObjectAttributeAddAfter($params)
    {
        $attribute = new ConfiguratorAttribute((int)$params['object']->id);
        $attribute->texture_image = (boolean)Tools::getValue('texture_image');
        $attribute->ref_ral = Tools::getValue('ref_ral');
        $attribute->update();
    }

    public function hookActionAdminAttributesGroupsFormModifier($params)
    {
        $hint_str = 'This option is only use in configurator module.';
        $hint_str .= ' If you define a great texture it will be displayed differently than classic texture.';
        // Formvars is not empty only in attributes form
        if (!empty($params['form_vars'])) {
            $attribute = new ConfiguratorAttribute((int)Tools::getValue('id_attribute'));
            $params['fields_value']['texture_image'] = $attribute->texture_image;
            $params['fields'][0]['form']['input'][] = array(
                'type' => 'switch',
                'label' => $this->l('Great texture'),
                'name' => 'texture_image',
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'texture_image_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'texture_image_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
                'hint' => $this->l($hint_str)
            );

            $params['fields_value']['ref_ral'] = $attribute->ref_ral;
            $params['fields'][0]['form']['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Reference'),
                'name' => 'ref_ral',
                'required' => false,
                'class' => 't',
                'values' => "",
                'hint' => $this->l($hint_str)
            );
            
            
            
        }
    }

    public function hookActionProductUpdate($params)
    {
        /**
         * Uniquement pour l'administration
         * @todo Changer de hook pour prendre une hook de AdminProductsController
         * qui serait plus pertinent par rapport au fonctionnement
         */
        if (!$this->context->controller instanceof AdminProductsController) {
            return;
        }

        $product = $params['product'];
        if (!Validate::isLoadedObject($product)) {
            return;
        }

        $has_configurator = ConfiguratorModel::productHasConfigurator((int)$product->id);
        $id_configurator_to_duplicate = Tools::getValue('duplicate_configurator');
        if (!$has_configurator
            && $id_configurator_to_duplicate
            && !$this->duplicateConfigurator((int)$product->id, (int)$id_configurator_to_duplicate)
        ) {
            $this->context->controller->errors[] = $this->l('An error occured during configuration duplication');
        }

        if (!$has_configurator) {
            return;
        }

        $configurator = ConfiguratorModel::getByIdProduct((int)$product->id);
        $configurator->use_base_price = (bool)Tools::getValue('use_base_price');
        $configurator->hide_qty_product = (bool)Tools::getValue('hide_qty_product');
        $configurator->hide_button_add_to_cart = (bool)Tools::getValue('hide_button_add_to_cart');
        $configurator->hide_product_price = (bool)Tools::getValue('hide_product_price');
        $configurator->tab_force_require_step = (bool)Tools::getValue('tab_force_require_step');
        $configurator->tab_type = Tools::getValue('tab_type');

        // CONFIGURATOR HOOK
        Hook::exec('configuratorActionProductUpdate', array('configurator' => &$configurator));

        if (!$configurator->save()) {
            $this->context->controller->errors[] = $this->l('Error when saving configurator\'s options');
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (DMTools::getVersionMajor() >= 17) {
            $id_product = (int)$params['id_product'];
        } else {
            $id_product = (int)Tools::getValue('id_product');
        }
        $product = new Product($id_product, false, Context::getContext()->language->id);
        if (!ConfiguratorModel::isConfiguratedProduct((int)$product->id)) {
            if (Validate::isLoadedObject($product)) {
                $configurator = ConfiguratorModel::getByIdProduct((int)$product->id);
                if (!Validate::isLoadedObject($configurator)) {
                    $configurator = false;
                }
                $this->smarty->assign(array('configurator' => $configurator));
            } else {
                $product = false;
            }

            $configurators = array();
            if (!Validate::isLoadedObject($configurator)) {
                $query = new DbQuery();
                $query->select('c.id_configurator, p.name')
                    ->from('configurator', 'c')
                    ->leftJoin('product_lang', 'p', 'c.id_product = p.id_product')
                    ->where('p.id_lang = ' . (int)$this->context->language->id)
                    ->where('p.id_shop = ' . (int)$this->context->shop->id);
                $configurators = Db::getInstance()->executeS($query);
            }

            // CONFIGURATOR HOOK
            $HOOK_CONFIGURATOR_DISPLAY_ADMIN_PRODUCTS_EXTRA = Hook::exec('configuratorDisplayAdminProductsExtra', array(
                'configurator' => $configurator,
                'product' => $product
            ));

            $this->smarty->assign(array(
                'configurators' => $configurators,
                'product' => $product,
                'cancel_link' => AdminProductsController::$currentIndex
                    . '&token=' . Tools::getAdminTokenLite('AdminProducts'),
                // Used for tabs
                'productTabs' => (Validate::isLoadedObject($configurator))
                    ? ConfiguratorStepTabModel::getTabsByIdConfigurator($configurator->id)
                    : array(),
                'default_form_language' => (int)$this->context->language->id,
                'languages' => Language::getLanguages(),
                'languages_json' => Tools::jsonEncode(Language::getLanguages()),
                'HOOK_CONFIGURATOR_DISPLAY_ADMIN_PRODUCTS_EXTRA' => $HOOK_CONFIGURATOR_DISPLAY_ADMIN_PRODUCTS_EXTRA
            ));

            if (DMTools::getVersionMajor() >= 17) {
                return $this->display(__FILE__, 'product-extra_17.tpl');
            } else {
                return $this->display(__FILE__, 'product-extra.tpl');
            }
        } else {
            $configuratorCartDetailModel = ConfiguratorCartDetailModel::getByIdProduct((int)$product->id);
            $this->smarty->assign(array(
                'product' => $product,
                'details' => $configuratorCartDetailModel->getDetailFormated(false, true)
            ));
            return $this->display(__FILE__, 'product-extra-configurated.tpl');
        }
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if (isset($params['product']) && $params['type'] === 'price') {
            // Sometimes its an object, sometimes an array ...
            $product = Validate::isLoadedObject($params['product'])
                ? $params['product']
                : new Product((int)$params['product']['id_product']);
            if (ConfiguratorModel::productHasConfigurator((int)$product->id, true)) {
                $this->smarty->assign(array(
                    'product' => $product,
                    'type' => $params['type'],
                    'configurator_link' => $this->context->link->getProductLink((int)$product->id)
                ));
                return $this->display(__FILE__, 'product-price-block.tpl');
            }
        }
    }

    public function hookDisplayShoppingCartFooter($params)
    {
    }

    public function hookDisplayOrderDetail($params)
    {
        $id_cart = (int)$params['order']->id_cart;
        $cartDetails = ConfiguratorCartDetailModel::getByIdCart($id_cart);
        $this->smarty->assign(array(
            'cartDetails' => $cartDetails
        ));
        return $this->display(__FILE__, 'display-order-detail.tpl');
    }

    public function hookDisplayFooter()
    {
        $cart_details_infos = ConfiguratorCartDetailModel::getProductsCartInformations();

        $this->context->smarty->assign($cart_details_infos);
        if (DMTools::getVersionMajor() >= 17) {
            return $this->display(__FILE__, 'display-footer_17.tpl');
        } else {
            return $this->display(__FILE__, 'display-footer.tpl');
        }
    }

    public function hookDisplayCustomization($params)
    {
        $cart_detail = ConfiguratorCartDetailModel::getByIdProductAndIdCustomization($params['customization']['id_product'], $params['customization']['id_customization']);
        $return = $params['customization']['value'];

        if (isset($cart_detail[0]) && Validate::isLoadedObject($cart_detail[0])) {
            // Display visual rendering from Viewer2D Module
            if ($cart_detail[0]->visual_rendering) {
                $return .= '<img width="100%" src="' . $cart_detail[0]->visual_rendering . '" class="configurator-visual-rendering">';
            }
            // Find id_cart
            $id_cart = $params['cart']->id;
            if (Module::isInstalled('advancedquote')) {
                if ((isset(Context::getContext()->controller->page_name) && Context::getContext()->controller->page_name === 'module-advancedquote-Quote') || get_class(Context::getContext()->controller) == 'AdminAdvancedquoteController') {
                    $id_advancedquote = Tools::getValue('id_advancedquote');
                    $id_cart = Tools::getValue('id_cart');
                    if ($id_cart) {
                        $quote = AdvancedquoteModel::findByIdCart($id_cart);
                    } else {
                        $quote = new AdvancedquoteModel((int)$id_advancedquote);
                    }
                    $id_cart = $quote->id_cart;
                }
            }

            // Generate link to update configurator
            $hash = md5(_COOKIE_KEY_ . '-' . $cart_detail[0]->id) . '-' . md5(_COOKIE_KEY_ . '-' . $id_cart);
            $link = Context::getContext()->link->getProductLink((int)$params['customization']['id_product']) . '?configurator_update=' . $cart_detail[0]->id;
            $link .= '&id_cart=' . $id_cart . '&hash=' . $hash;

            // Add button
            /*if (!isset(Context::getContext()->controller->controller_name) || !in_array(Context::getContext()->controller->controller_name,
                    array('AdminCarts'))) {*/
            if(!Validate::isLoadedObject(Context::getContext()->employee)) {
                // Edit configuration from frontoffice
                $return .= '<br><br><a class="configurator-update-btn" href="' . $link . '">' . $this->l('Update my customization') . '</a>';
            } else {
                // Edit configuration from backoffice

                $return .= Hook::exec(
                	'configuratorDisplayAdminCustomization',
					array(
						'cart_detail' => $cart_detail,
						'iframe_link' => $link . '&content_only=1',
						'customization' => $params['customization']
					)
				);
				//$return .= $this->display(__FILE__, 'display-admin-customization.tpl');
            }
        }

        return $return;
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int)$params['id_order']);
        $id_cart = (int)$order->id_cart;
        $cartDetails = ConfiguratorCartDetailModel::getByIdCart($id_cart);
        $attachements_by_cartdetails = array();
        foreach ($cartDetails as $cartDetail) {
            $attachments = ConfiguratorAttachment::getAttachments((int)$cartDetail->id);
            if ($attachments) {
                $attachements_by_cartdetails[] = array(
                    'cart_detail' => $cartDetail,
                    'attachments' => $attachments
                );
            }
        }

        if (count($attachements_by_cartdetails) == 0) {
            return;
        }

        $products = $order->getProducts();

        $this->smarty->assign(array(
            'attachements_by_cartdetails' => $attachements_by_cartdetails,
            'products' => $products,
            'attachment_link' => $this->context->link->getModuleLink('configurator', 'attachment', array('token' => ''))
        ));
        return $this->display(__FILE__, 'display-admin-order.tpl');
    }

    public static function normalizeFloat($float)
    {
        if (!is_float($float)) {
            return 0.00;
        }

        $explode = explode('.', (string)($float * self::MAX_FLOAT_PRECISION_MULTIPLIER));
        return (float)($explode[0] / self::MAX_FLOAT_PRECISION_MULTIPLIER);
    }

    /**
     * Clean cache of Configurator
     */
    public static function cleanCache()
    {
        //Db::getInstance()->enableCache();
        //Cache::getInstance()->flush();
        DmCache::getInstance()->clean();
       // Cache::clean('Configurator*');
    }

    public function hookDisplayAdminCartsView($params)
    {
        return $this->display(__FILE__, 'display-admin-carts-view.tpl');
    }

    public function hookActionObjectCartDeleteAfter($params)
    {
        $cart = $params['object'];
        if (Validate::isLoadedObject($cart)) {
            $cart_details = ConfiguratorCartDetailModel::getByIdCart($cart->id);
            foreach ($cart_details as $cart_detail) {
                if (Validate::isLoadedObject($cart_detail)) {
                    $cart_detail->delete();
                }
            }
        }
    }

    public function hookActionAfterDeleteProductInCart($params)
    {
        $cart_details = ConfiguratorCartDetailModel::getByIdCartAndIdProductAndIdCustomization(
            $params['id_cart'],
            $params['id_product'],
            $params['customization_id']
        );
        foreach ($cart_details as $cart_detail) {
            if (Validate::isLoadedObject($cart_detail)) {
                $cart_detail->delete();
            }
        }
    }

    public function updateConfiguratorCustomizationField()
    {
        // Créer les champs personnalisés s'ils n'existent pas
        $configurators = ConfiguratorModel::getWithoutCustomizationField();
        foreach ($configurators as $configurator) {
            $configurator->update();
        }
        return true;
    }

    public function cleanConfiguratorCustomizationField()
    {
        // REMOVE CUSTOMIZATION FIELD
        $sql = 'DELETE `' . _DB_PREFIX_ . 'customization_field` FROM `' . _DB_PREFIX_ . 'customization_field`
                LEFT JOIN ' . _DB_PREFIX_ . 'product p 
                ON p.id_product = `' . _DB_PREFIX_ . 'customization_field`.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'configurator c 
                ON c.id_product = p.id_product
                WHERE c.id_customization_field != `' . _DB_PREFIX_ . 'customization_field`.id_customization_field';
        Db::getInstance()->execute($sql);

        // REMOVE CUSTOMIZATION FIELD LANG
        $sql = 'DELETE `' . _DB_PREFIX_ . 'customization_field_lang` FROM `' . _DB_PREFIX_ . 'customization_field_lang`
                LEFT JOIN ' . _DB_PREFIX_ . 'customization_field cf 
                ON cf.id_customization_field = `' . _DB_PREFIX_ . 'customization_field_lang`.id_customization_field
                WHERE cf.id_customization_field IS NULL';
        Db::getInstance()->execute($sql);

        // SET NUMBER OF CUSTOMIZATION FIELD
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'configurator c ON c.id_product = p.id_product
                SET p.text_fields = 1
                WHERE c.id_configurator IS NOT NULL AND c.active = 1';
        Db::getInstance()->execute($sql);
    }

    public function deleteUnusedCartDetail($number = 1, $day = 2)
    {
        return DMTools::deleteUnusedCartDetail($number, $day);
    }

    /**
     * Avoid to have a problem when we execute a upgrade
     *
     * @param String $table_name
     * @param String $column_name
     * @return boolean
     */
    public function existColumnInTable($table_name, $column_name)
    {
        return DMTools::existColumnInTable($table_name, $column_name);
    }

    public function existTableInDatabase($table_name)
    {
        return DMTools::existTableInDatabase($table_name);
    }

    public function existIndexInTable($table_name, $column_name)
    {
        return DMTools::existIndexInTable($table_name, $column_name);
    }

    public function disableConfiguratedProduct()
    {
        ConfiguratorModel::disableConfiguratedProduct();
    }

    public function getConfigurationLang($key = '')
    {
        $return = array();

        $values = Configuration::get($key);
        if ($values) {
            $languages = Language::getLanguages();
            $values = unserialize($values);
            foreach ($languages as $language) {
                $return[$language['id_lang']] = (isset($values[$language['id_lang']]))
                    ? $values[$language['id_lang']]
                    : '';
            }
        }

        return $return;
    }

    public function setConfigurationLang($key = '', $values = array())
    {
        $return = array();

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $return[$language['id_lang']] = (isset($values[$language['id_lang']])) ? $values[$language['id_lang']] : '';
        }
        Configuration::updateValue($key, serialize($return));
    }
}
