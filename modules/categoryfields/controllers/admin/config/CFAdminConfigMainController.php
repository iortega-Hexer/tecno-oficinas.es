<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2017 Musaffar Patel
 * @license   LICENSE.txt
 */

class CFAdminConfigMainController extends CFControllerCore
{
    protected $sibling;

    public function __construct(&$sibling = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    public function setMedia()
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == 'categoryfields') {
            Context::getContext()->controller->addCSS($this->sibling->_path.'views/css/lib/tools.css');
            Context::getContext()->controller->addCSS($this->sibling->_path.'views/css/lib/popup.css');
            Context::getContext()->controller->addCSS($this->sibling->_path.'views/css/admin/admin.css');
            Context::getContext()->controller->addCSS($this->getAdminWebPath().'/themes/new-theme/public/theme.css');

            Context::getContext()->controller->addJquery();
            //Context::getContext()->controller->addJS($this->getAdminWebPath().'/themes/new-theme/public/bundle.js');
            Context::getContext()->controller->addJS($this->sibling->_path.'views/js/lib/Popup.js');
            Context::getContext()->controller->addJS($this->sibling->_path.'views/js/lib/Tools.js');
            Context::getContext()->controller->addJS($this->sibling->_path.'views/js/admin/config/CFAdminConfigMainController.js');
        }
    }

    public function render()
    {
        Context::getContext()->smarty->assign(array(
            'module_config_url' => $this->module_config_url,
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/main.tpl');
    }

    public function renderList()
    {
        $categoryfied_model = new CFCategoryFieldModel();
        $categoryfields = $categoryfied_model->getList();

        Context::getContext()->smarty->assign(array(
            'categoryfields' => $categoryfields
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/list.tpl');
    }

    public function renderAddForm()
    {
        Context::getContext()->smarty->assign(array(
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/add.tpl');
    }

    /**
     * Process add new field form
     */
    public function processAddForm()
    {
        if (Tools::getValue('id_categoryfield') == '') {
            $categoryfield_model = new CFCategoryFieldModel();
        } else {
            $categoryfield_model = new CFCategoryFieldModel((int)Tools::getValue('id_categoryfield'));
        }

        $categoryfield_model->name = pSQL(Tools::getValue('name'));
        $categoryfield_model->id_shop = Context::getContext()->shop->id;
        $categoryfield_model->collapsible = (int)Tools::getValue('collapsible');
        $categoryfield_model->save();
    }

    public function processDelete()
    {
        CFCategoryFieldContentModel::deleteByCategoryField((int)Tools::getValue('id_categoryfield'));
        $category_field_model = new CFCategoryFieldModel((int)Tools::getValue('id_categoryfield'));
        $category_field_model->delete();
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            case 'renderlist':
                die($this->renderList());

            case 'renderaddform':
                die($this->renderAddForm());

            case 'processaddform':
                die($this->processAddForm());

            case 'processdelete':
                die($this->processDelete());

            default:
                return($this->render());
        }
    }
}
