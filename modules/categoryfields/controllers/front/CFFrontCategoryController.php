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

class CFFrontCategoryController extends CFControllerCore
{
    public function __construct($sibling, $params = array())
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    public function setMedia()
    {
        $allowed_controllers = array('category');
        $_controller = $this->context->controller;

        if (isset($_controller->php_self) && in_array($_controller->php_self, $allowed_controllers)) {
            $this->context->controller->registerStylesheet('cf_front', 'modules/'.$this->sibling->name.'/views/css/front/front.css');
            $this->context->controller->registerJavascript('cf_front_category_controller', 'modules/'.$this->sibling->name.'/views/js/front/CFFrontCategoryController.js');
        }
    }

    private function _tinymceCleanup($html)
    {
        $html = str_replace('&nbsp;', '', $html);
        return $html;
    }

    /**
     * Front end template hook to render field content
     * @param $params
     * @return mixed
     */
    public function hookCategoryField($params)
    {
        $id_lang = Context::getContext()->language->id;
        if (!isset($params['name']) && $params['name'] == '') {
            return '';
		}
		
        if (Tools::getValue('id_category') == '' && !empty($params['id_category'])) {
            return '';
        }

        $id_category = (int)Tools::getValue('id_category');
        $collapsible = true;
        
        $categoryfield = new CFCategoryFieldModel();
        $categoryfield->loadByName($params['name']);

        $categoryfield_content = new CFCategoryFieldContentModel();
		$categoryfield_content->getContentByName($params['name'], Tools::getValue('id_category'), Context::getContext()->language->id, Context::getContext()->shop->id);
		
        if ($categoryfield_content->content == '' && $categoryfield_content->excerpt == '') {
            return false;
        }

        $this->sibling->smarty->assign(array(
            'cf_content' => $this->_tinymceCleanup($categoryfield_content->content),
            'cf_excerpt' => $this->_tinymceCleanup($categoryfield_content->excerpt),
            'collapsible' => (int)$categoryfield->collapsible,
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/front/hook_categoryfield.tpl');
    }
}
