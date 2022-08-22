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

/**
 * @since 1.5.0
 */
require_once(dirname(__FILE__) . '/../../classes/ConfiguratorModel.php');
require_once(dirname(__FILE__) . '/../../classes/helper/DMTools.php');

class AdminConfiguratorController extends ModuleAdminController
{
    const ADMIN_STEPS_CONTROLLER = 'AdminConfiguratorSteps';

    public function __construct()
    {
        $this->bootstrap = true;

        $this->table = 'configurator';
        $this->className = 'configuratorModel';

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        parent::__construct();
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

    public function renderList()
    {
        $this->addRowAction('viewstep');
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = array(
            'id_configurator' => array(
                'title' => $this->l('ID')
            ),
            'id_product' => array(
                'title' => $this->l('Product'),
                'callback' => 'getProductName'
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status'
            )
        );

        $this->context->smarty->assign(array(
            'configurator_tools_link' => $this->context->link->getAdminLink('AdminConfiguratorTools'),
            'need_tools_update' => DMTools::needToolsUpdate()
        ));

        $output = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'configurator/views/templates/admin/need_tools_update.tpl'
        );

        return $output . parent::renderList();
    }

    public function postProcess()
    {
        parent::postProcess();

        Hook::exec('configuratorAdminActionControllerPostProcess');

        if (Tools::getIsset('duplicate_configurator')) {
            $this->duplicateConfigurator();
        }

        // When you come from button actions of product edition (Add configurator, activation, etc...)
        if (Validate::isLoadedObject(($product = new Product(Tools::getValue('id_product'))))) {
            if ($this->display === 'add') {
                $this->object = new ConfiguratorModel();
                $this->object->id_product = (int)$product->id;
                $this->object->save();
            }

            if (DMTools::getVersionMajor() == 16) {
                $this->redirect_after = $this->context->link->getAdminLink('AdminProducts') .
                    '&updateproduct&conf=4' .
                    '&id_product=' . (int)$product->id .
                    '&key_tab=ModuleConfigurator';

                /**
                 * New clean the cache of configurator !
                 */
                Cache::clean("Configurator*");

                $this->redirect();
            } else {
                global $kernel;
                if (Tools::getIsset('id_product')) {
                    $sfRouter = $kernel->getContainer()->get('router');
                    Tools::redirectAdmin(
                        $sfRouter->generate(
                            'admin_product_form',
                            array('id' => Tools::getValue('id_product'))
                        ) . "#tab-hooks"
                    );
                }
            }
        }
    }

    public function processStatus()
    {
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            if ($object->toggleStatus()) {
                if (DMTools::getVersionMajor() < 17) {
                    $url = $this->context->link->getAdminLink('AdminProducts') .
                        '&updateproduct&conf=4' .
                        '&id_product=' . (int)$object->id_product .
                        '&key_tab=ModuleConfigurator';
                } else {
                    global $kernel;
                    $sfRouter = $kernel->getContainer()->get('router');
                    $url = $sfRouter->generate(
                        'admin_product_form',
                        array('id' => (int)$object->id_product)
                    ) . "#tab-hooks";
                }
                $this->redirect_after = $url;
            } else {
                $this->errors[] = $this->trans(
                    'An error occurred while updating the status.',
                    array(),
                    'Admin.Notifications.Error'
                );
            }
        } else {
            $error_msg = $this->trans(
                'An error occurred while updating the status for an object.',
                array(),
                'Admin.Notifications.Error'
            );
            $error_msg .= ' <b>' . $this->table . '</b> ' . $this->trans(
                '(cannot load object)',
                array(),
                'Admin.Notifications.Error'
            );

            $this->errors[] = $error_msg;
        }

        return $object;
    }

    public function renderForm()
    {
        if (!($this->loadObject(true))) {
            return;
        }

        $this->display = Validate::isLoadedObject($this->object) ? 'edit' : 'add';
        $title = Validate::isLoadedObject($this->object)
            ? $this->l('Edit a configurator')
            : $this->l('Add a new configurator for a specific product');

        $hint = 'Commencez à saisir les premières lettres du nom du produit';
        $hint .= ' puis sélectionnez le produit dans le menu déroulant.';

        $this->fields_form = array(
            'legend' => array(
                'title' => $title
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Product to configure'),
                    'hint' => $this->l($hint),
                    'required' => true,
                    'name' => 'id_product',
                    'col' => '2'
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        if (Shop::isFeatureActive()) {
            $sql = 'SELECT id_attribute_group, id_shop FROM ' . _DB_PREFIX_ . 'attribute_group_shop';
            $associations = array();
            foreach (Db::getInstance()->executeS($sql) as $row) {
                $associations[$row['id_attribute_group']][] = $row['id_shop'];
            }

            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
                'values' => Shop::getTree()
            );
        } else {
            $associations = array();
        }

        $this->fields_form['shop_associations'] = Tools::jsonEncode($associations);

        $parent = parent::renderForm();
        $this->addJqueryPlugin(array('autocomplete', 'fancybox', 'typewatch'));
        return $parent;
    }

    public function getProductName($echo, $row)
    {
        $echo = true; // Validator
        $product = new Product((int)$row['id_product'], false, $this->context->language->id);
        return Tools::htmlentitiesUTF8($product->name);
    }

    public function displayViewstepLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        if (!array_key_exists('ViewStep', self::$cache_lang)) {
            self::$cache_lang['ViewStep'] = $this->l('See steps');
        }

        $name = self::$cache_lang['ViewStep']; // Validator

        $tpl->assign(array(
            // Use dispatcher cause we need to set a id_configurator in params
            'href' => Dispatcher::getInstance()->createUrl(
                self::ADMIN_STEPS_CONTROLLER,
                $this->context->language->id,
                array(
                    'id_configurator' => (int)$id,
                    'token' => Tools::getAdminTokenLite(self::ADMIN_STEPS_CONTROLLER)
                ),
                false
            ),
            'action' => $name,
            'id' => $id,
            'token' => $token
        ));

        return $tpl->fetch();
    }

    /**
     * Override de ControllerCore::ajaxDie()
     * Cette méthode n'existe pas pour des boutiques < 1.6.0.13
     * @param type $value
     * @param string $controller
     * @param string $method
     */
    protected function ajaxDie($value = null, $controller = null, $method = null)
    {
        if (version_compare(_PS_VERSION_, '1.6.0.13', '>=') === true) {
            parent::ajaxDie($value, $controller, $method);
        }

        if ($controller === null) {
            $controller = get_class($this);
        }

        if ($method === null) {
            $bt = debug_backtrace();
            $method = $bt[1]['function'];
        }

        Hook::exec('actionBeforeAjaxDie', array('controller' => $controller, 'method' => $method, 'value' => $value));
        Hook::exec('actionBeforeAjaxDie' . $controller . $method, array('value' => $value));

        die($value);
    }

    private function duplicateConfigurator()
    {
        $product = new Product(Tools::getValue('id_product'));
        if (Validate::isLoadedObject($product)) {
            $has_configurator = ConfiguratorModel::productHasConfigurator((int)$product->id);
            $id_configurator_to_duplicate = Tools::getValue('duplicate_configurator');
            $configurator = new ConfiguratorModel((int)$id_configurator_to_duplicate);
            if (!$has_configurator && $id_configurator_to_duplicate && !$configurator->duplicate((int)$product->id)) {
                $this->context->controller->errors[] = $this->l('An error occured during configuration duplication');
            }
        }
    }
}
