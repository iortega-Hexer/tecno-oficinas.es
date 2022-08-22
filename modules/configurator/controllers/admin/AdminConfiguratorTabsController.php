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
class AdminConfiguratorTabsController extends ModuleAdminController
{

    private $_id_configurator;

    public function __construct()
    {
        $this->bootstrap = true;

        $this->table = 'configurator_step_tab';
        $this->className = 'configuratorStepTabModel';
        $this->lang = true;

        parent::__construct();

        $this->_id_configurator = (int)Tools::getValue('id_configurator');
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

    public function ajaxProcessDelete()
    {
        $id = Tools::getValue('id');
        $tab = new ConfiguratorStepTabModel((int)$id);

        $return = array(
            'success' => 0,
            'message' => ""
        );

        if (Validate::isLoadedObject($tab)) {
            $return['success'] = (int)$tab->delete();
            if ((bool)$return['success']) {
                $return['message'] = $this->l('The tab has been successfully deleted.');
            } else {
                $return['message'] = $this->l('An error occurred, the tab hasn\'t been deleted');
            }
        } else {
            $return['message'] = $this->l('An error occurred, the tab doesn\'t exist.');
        }

        $this->ajaxDie(Tools::jsonEncode($return));
    }

    public function ajaxProcessAdd()
    {
        $id_configurator = Tools::getValue('id_configurator');
        $return = array(
            'success' => 0,
            'message' => ""
        );
        $tab = new ConfiguratorStepTabModel();
        $tab->id_configurator = $id_configurator;
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang']);
        }
        $return['success'] = (int)$tab->add();
        if ((bool)$return['success']) {
            $return['message'] = $this->l('The tab has been successfully added.');
            $return['tab'] = $tab;
        } else {
            $return['message'] = $this->l('An error occurred, the tab hasn\'t been added');
        }

        $this->ajaxDie(Tools::jsonEncode($return));
    }

    public function ajaxProcessUpdate()
    {
        $id = Tools::getValue('id');

        $return = array(
            'success' => 0,
            'message' => ""
        );
        $tab = new ConfiguratorStepTabModel($id);
        if (Validate::isLoadedObject($tab)) {
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang']);
            }
            $return['success'] = (int)$tab->update();
            if ((bool)$return['success']) {
                $return['message'] = $this->l('The tab has been successfully updated.');
                $return['tab'] = $tab;
            } else {
                $return['message'] = $this->l('An error occurred, the tab hasn\'t been updated');
            }
        } else {
            $return['message'] = $this->l('An error occurred, the tab doesn\'t exist.');
        }

        $this->ajaxDie(Tools::jsonEncode($return));
    }

    public function ajaxProcessPosition()
    {
        $id = Tools::getValue('id');
        $type = Tools::getValue('type');
        $tab = new ConfiguratorStepTabModel((int)$id);

        $return = array(
            'success' => 0,
            'message' => ""
        );

        if (Validate::isLoadedObject($tab)) {
            $tabs = ConfiguratorStepTabModel::getTabsByIdConfigurator($tab->id_configurator);
            $pos = 0;
            foreach ($tabs as $k => $t) {
                $tabs[$k]->position = (int)$pos;
                if ((int)$t->id === (int)$tab->id) {
                    if ($type === 'down') {
                        $tabs[$k]->position++;
                    } elseif ($type === 'up') {
                        $tabs[$k]->position--;
                    }
                    $p = $tabs[$k]->position;
                }
                $pos++;
            }
            foreach ($tabs as $k => $t) {
                if ((int)$p === $tabs[$k]->position && (int)$t->id !== (int)$tab->id) {
                    if ($type === 'down') {
                        $tabs[$k]->position--;
                    } elseif ($type === 'up') {
                        $tabs[$k]->position++;
                    }
                }
                $t->save();
            }

            $return['success'] = (int)true;
            if ((bool)$return['success']) {
                $return['message'] = $this->l('The tab has been successfully updated.');
            } else {
                $return['message'] = $this->l('An error occurred, the tab hasn\'t been updated');
            }
        } else {
            $return['message'] = $this->l('An error occurred, the tab doesn\'t exist.');
        }

        $this->ajaxDie(Tools::jsonEncode($return));
    }
}
