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

class CFAdminCategoryMainController extends CFControllerCore
{
    protected $sibling;

    protected $id_lang_default;

    protected $request;

    public function __construct(&$sibling = null, Symfony\Component\HttpFoundation\Request $request = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }

        if (!empty($request)) {
            $this->request = $request;
        }

        $this->id_lang_default = Configuration::get('PS_LANG_DEFAULT', null, Context::getContext()->shop->id_shop_group, Context::getContext()->shop->id);
    }

    public function setMedia()
    {
        if (Tools::getValue('controller') == 'AdminCategories') {
            Context::getContext()->controller->addCSS($this->sibling->_path.'views/css/lib/tools.css');
            Context::getContext()->controller->addCSS($this->sibling->_path.'views/css/lib/popup.css');
            Context::getContext()->controller->addCSS($this->sibling->_path.'views/css/admin/admin.css');
            //Context::getContext()->controller->addCSS($this->getAdminWebPath().'/themes/new-theme/public/theme.css');

            Context::getContext()->controller->addJquery();
            //Context::getContext()->controller->addJS($this->getAdminWebPath().'/themes/new-theme/public/bundle.js');
            Context::getContext()->controller->addJS($this->sibling->_path.'views/js/lib/Popup.js');
            Context::getContext()->controller->addJS($this->sibling->_path.'views/js/lib/Tools.js');
            Context::getContext()->controller->addJS($this->sibling->_path.'views/js/admin/category/CFAdminCategoryMainController.js');
        }
    }

    public function render()
    {
        $id_category = (int)Tools::getValue('id_category');

        if ($id_category == 0) {
            $id_category = $this->request->get('categoryId');
        }

        $languages = Language::getLanguages();
        $category_fields_model = new CFCategoryFieldModel();
        $category_fields = $category_fields_model->getList();

        $category_field_content = array();

        foreach ($category_fields as &$category_field) {
            foreach ($languages as $language) {
                $category_fields_content_model = new CFCategoryFieldContentModel();
                $category_fields_content_model->getContent($category_field->id_categoryfield, $id_category, $language['id_lang'], Context::getContext()->shop->id);
                $category_field_content[] = array(
                    'id_categoryfield' => $category_fields_content_model->id_categoryfield,
                    'id_lang' => $category_fields_content_model->id_lang,
                    'content' => $category_fields_content_model->content,
                    'excerpt' => $category_fields_content_model->excerpt
                );
            }
        }

        Context::getContext()->smarty->assign(array(
            'category_fields' => $category_fields,
            'category_field_content' => $category_field_content,
            'languages' => $languages,
            'languages_json' => Tools::jsonEncode($languages),
            'id_lang_default' => $this->id_lang_default,
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/category/main.tpl');
    }

    /**
     * Save category fields content when category is saved
     */
    public function hookActionCategoryUpdate($params)
    {
		$id_category = $params['category']->id;
        $languages = Language::getLanguages();
        $category_fields_model = new CFCategoryFieldModel();
        $category_fields = $category_fields_model->getList();

        foreach ($category_fields as $category_field) {
            foreach ($languages as $language) {
                $key_content = 'cf_content_' . $category_field->id_categoryfield . '_' . $language['id_lang'];
                $key_excerpt = 'cf_excerpt_' . $category_field->id_categoryfield . '_' . $language['id_lang'];

                $categoryfield_content_model = new CFCategoryFieldContentModel();
                $categoryfield_content_model->getContent($category_field->id_categoryfield, $id_category, $language['id_lang'], Context::getContext()->shop->id);
                $categoryfield_content_model->id_categoryfield = (int)$category_field->id_categoryfield;
                $categoryfield_content_model->id_category = (int)$id_category;
                $categoryfield_content_model->id_lang = (int)$language['id_lang'];
                $categoryfield_content_model->id_shop = (int)Context::getContext()->shop->id;

                if ((int)$categoryfield_content_model->id_categoryfield > 0) {
                    if (Tools::getIsset($key_content)) {
                        $categoryfield_content_model->content = Tools::getValue($key_content);
                        $categoryfield_content_model->excerpt = Tools::getValue($key_excerpt);
                    }
                    $categoryfield_content_model->save();
                }
            }
        }
    }

    public function route()
    {
        switch (Tools::getValue('action')) {
            default:
                return($this->render());
        }
    }
}
