<?php
/**
 * 2015-2017 Bonpresta
 *
 * Bonpresta Awesome Image Slider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 *  @author    Bonpresta
 *  @copyright 2015-2017 Bonpresta
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'bonslick/classes/ClassBonSlick.php');

class Bonslick extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'bonslick';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Bonpresta';
        $this->module_key = '84b7bfd541e0a2fa1b782b3db2a4ab3b';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->id_shop = Context::getContext()->shop->id;
        $this->displayName = $this->l('Awesome Image Slider');
        $this->description = $this->l('Display awesome slick image slider');
        $this->confirmUninstall = $this->l('This module Uninstall');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        if (Configuration::get('PS_SSL_ENABLED')) {
            $this->ssl = 'https://';
        } else {
            $this->ssl = 'http://';
        }
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'bonslick';
            }
        }
        $tab->class_name = 'AdminAjaxBonSlick';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        return (bool)$tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminAjaxBonSlick')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }

        return true;
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        $settings = $this->getModuleSettings();

        foreach ($settings as $name => $value) {
            Configuration::updateValue($name, $value);
        }

        return parent::install() &&
        $this->registerHook('header') &&
        $this->createAjaxController() &&
        $this->registerHook('displayCustomSlick') &&
        $this->registerHook('displayBackOfficeHeader') &&
        $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        $settings = $this->getModuleSettings();

        foreach (array_keys($settings) as $name) {
            Configuration::deleteByName($name);
        }

        return parent::uninstall()
        && $this->removeAjaxContoller();
    }

    protected function getModuleSettings()
    {
        $settings = array(
            'BON_SLICK_CAROUSEL_LOOP' => true,
            'BON_SLICK_CAROUSEL_NAV' => true,
            'BON_SLICK_CAROUSEL_DOTS' => true,
            'BON_SLICK_CAROUSEL_DRAG' => true,
            'BON_SLICK_CAROUSEL_AUTOPLAY' => true,
            'BON_SLICK_CAROUSEL_TIME' => 5000,
        );
        return $settings;
    }

    public function getContent()
    {

        $output = '';
        $result ='';

        if (((bool)Tools::isSubmit('submitBonslickSettingModule')) == true) {
            if (!$errors = $this->validateSettings()) {
                $this->postProcess();
                $output .= $this->displayConfirmation($this->l('Settings updated successful.'));
            } else {
                $output .= $errors;
            }
        }

        if ((bool)Tools::isSubmit('submitUpdateSlick')) {
            if (!$result = $this->preValidateForm()) {
                $output .= $this->addSlick();
            } else {
                $output = $result;
                $output .= $this->renderSlickForm();
            }
        }

        if ((bool)Tools::isSubmit('statusbonslick')) {
            $output .= $this->updateStatusTab();
        }

        if ((bool)Tools::isSubmit('deletebonslick')) {
            $output .= $this->deleteSlick();
        }

        if (Tools::getIsset('updatebonslick') || Tools::getValue('updatebonslick')) {
            $output .= $this->renderSlickForm();
        } elseif ((bool)Tools::isSubmit('addbonslick')) {
            $output .= $this->renderSlickForm();
        } elseif (!$result) {
            $output .= $this->renderSlickList();
            $output .= $this->renderFormSettings();
        }
        $out = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $out.$output;
    }

    protected function renderFormSettings()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBonslickSettingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'image_path' => $this->_path.'views/img',
            'fields_value' => $this->getConfigFormValuesSettings(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }


    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Autoplay:'),
                        'name' => 'BON_SLICK_CAROUSEL_AUTOPLAY',
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
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Autoplay Speed:'),
                        'name' => 'BON_SLICK_CAROUSEL_TIME',
                        'col' => 2,
                        'required' => true,
                        'suffix' => 'milliseconds',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Navigation:'),
                        'name' => 'BON_SLICK_CAROUSEL_NAV',
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
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Pagination:'),
                        'name' => 'BON_SLICK_CAROUSEL_DOTS',
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
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Loop:'),
                        'name' => 'BON_SLICK_CAROUSEL_LOOP',
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
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Mouse drag:'),
                        'name' => 'BON_SLICK_CAROUSEL_DRAG',
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
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function validateSettings()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('BON_SLICK_CAROUSEL_TIME'))) {
            $errors[] = $this->l('Animation speed is required.');
        } else {
            if (!Validate::isUnsignedInt(Tools::getValue('BON_SLICK_CAROUSEL_TIME'))) {
                $errors[] = $this->l('Bad animation speed format');
            }
        }

        if ($errors) {
            return $this->displayError(implode('<br />', $errors));
        } else {
            return false;
        }
    }

    protected function getConfigFormValuesSettings()
    {
        $filled_settings = array();
        $settings = $this->getModuleSettings();

        foreach (array_keys($settings) as $name) {
            $filled_settings[$name] = Configuration::get($name);
        }

        return $filled_settings;
    }

    protected function getStringValueType($string)
    {
        if (Validate::isInt($string)) {
            return 'int';
        } elseif (Validate::isFloat($string)) {
            return 'float';
        } elseif (Validate::isBool($string)) {
            return 'bool';
        } else {
            return 'string';
        }
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValuesSettings();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function getSlickSettings()
    {
        $settings = $this->getModuleSettings();
        $get_settings = array();
        foreach (array_keys($settings) as $name) {
            $data = Configuration::get($name);
            $get_settings[$name] = array('value' => $data, 'type' => $this->getStringValueType($data));
        }

        return $get_settings;
    }

    protected function renderSlickForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => ((int)Tools::getValue('id_tab') ? $this->l('Update slider') : $this->l('Add slider')),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'title',
                        'lang' => true,
                        'required' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'files_lang',
                        'label' => $this->l('Image'),
                        'name' => 'image',
                        'lang' => true,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Content'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status'),
                        'name' => 'status',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'sort_order',
                        'class' => 'hidden'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
            ),
        );

        if ((bool)Tools::getIsset('updatebonslick') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new ClassBonSlick((int)Tools::getValue('id_tab'));
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_tab', 'value' => (int)$tab->id);
            $fields_form['form']['images'] = $tab->image;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUpdateSlick';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigSlickFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'image_baseurl' => $this->_path.'images/'
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getConfigSlickFormValues()
    {
        if ((bool)Tools::getIsset('updatebonslick') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new ClassBonSlick((int)Tools::getValue('id_tab'));
        } else {
            $tab = new ClassBonSlick();
        }

        $fields_values = array(
            'id_tab' => Tools::getValue('id_tab'),
            'title' => Tools::getValue('title', $tab->title),
            'url' => Tools::getValue('url', $tab->url),
            'image' => Tools::getValue('image', $tab->image),
            'description' => Tools::getValue('description', $tab->description),
            'status' => Tools::getValue('status', $tab->status),
            'sort_order' => Tools::getValue('sort_order', $tab->sort_order),
        );

        return $fields_values;
    }

    public function renderSlickList()
    {
        if (!$tabs = ClassBonSlick::getSlickList()) {
            $tabs = array();
        }

        $fields_list = array(
            'id_tab' => array(
                'title' => $this->l('Id'),
                'type' => 'text',
                'col' => 6,
                'search' => false,
                'orderby' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'images' => array(
                'title' => $this->l('Label'),
                'type'  => 'box_image',
                'align' => 'center',
                'search' => false,
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'search' => false,
                'orderby' => false,
            ),
            'sort_order' => array(
                'title' => $this->l('Position'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'class' => 'pointer dragHandle'
            )
        );

        $helper = new HelperList();

        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_tab';
        $helper->table = 'bonslick';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->title = $this->displayName;
        $helper->listTotal = count($tabs);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex
                .'&configure='.$this->name.'&add'.$this->name
                .'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new item')
        );
        $helper->currentIndex = AdminController::$currentIndex
            .'&configure='.$this->name.'&id_shop='.(int)$this->context->shop->id;

        $helper->tpl_vars = array(
            'link' => new Link(),
            'base_dir' => $this->ssl,
            'ps_version' => _PS_VERSION_,
            'lang_iso' => $this->context->language->iso_code,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'image_baseurl' => $this->_path.'images/',
        );

        return $helper->generateList($tabs, $fields_list);
    }

    protected function addSlick()
    {
        $errors = array();

        if ((int)Tools::getValue('id_tab') > 0) {
            $item = new ClassBonSlick((int)Tools::getValue('id_tab'));
        } else {
            $item = new ClassBonSlick();
        }

        $item->id_shop = (int)$this->context->shop->id;
        $item->status = (int)Tools::getValue('status');

        if ((int)Tools::getValue('id_tab') > 0) {
            $item->sort_order = Tools::getValue('sort_order');
        } else {
            $item->sort_order = $item->getMaxSortOrder((int)$this->id_shop);
        }

        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            $item->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $item->description[$language['id_lang']] = Tools::getValue('description_'.$language['id_lang']);
            $item->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);

            $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_'.$language['id_lang']]['name'], '.'), 1));
            $imagesize = @getimagesize($_FILES['image_'.$language['id_lang']]['tmp_name']);
            if (isset($_FILES['image_'.$language['id_lang']])
                && isset($_FILES['image_'.$language['id_lang']]['tmp_name'])
                && !empty($_FILES['image_'.$language['id_lang']]['tmp_name'])
                && !empty($imagesize)
                && in_array(
                    Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)),
                    array('jpg', 'gif', 'jpeg', 'png')
                )
                && in_array($type, array('jpg', 'gif', 'jpeg', 'png'))) {
                $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $salt = sha1(microtime());
                if ($error = ImageManager::validateUpload($_FILES['image_'.$language['id_lang']])) {
                    $errors[] = $error;
                } elseif (!$temp_name || !move_uploaded_file($_FILES['image_'.$language['id_lang']]['tmp_name'], $temp_name)) {
                    return false;
                } elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/images/'.$salt.'_'.$_FILES['image_'.$language['id_lang']]['name'], null, null, $type)) {
                    $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
                }

                if (isset($temp_name)) {
                    @unlink($temp_name);
                }
                $item->image[$language['id_lang']] = $salt.'_'.$_FILES['image_'.$language['id_lang']]['name'];
            } elseif (Tools::getValue('image_old_'.$language['id_lang']) != '') {
                $item->image[$language['id_lang']] = Tools::getValue('image_old_'.$language['id_lang']);
            }
        }

        if (!$errors) {
            if (!Tools::getValue('id_tab')) {
                if (!$item->add()) {
                    return $this->displayError($this->l('The item could not be added.'));
                }
            } elseif (!$item->update()) {
                return $this->displayError($this->l('The item could not be updated.'));
            }

            return $this->displayConfirmation($this->l('The item is saved.'));
        } else {
            return $this->displayError($this->l('Unknown error occurred.'));
        }
    }

    protected function preValidateForm()
    {
        $errors = array();
        $languages = Language::getLanguages(false);

        $class = new ClassBonSlick((int)Tools::getValue('id_tab'));
        $imageexists = @getimagesize($_FILES['image_'.$this->default_language['id_lang']]['tmp_name']);
        $old_image = $class->image;

        if (!$old_image && !$imageexists) {
            $errors[] = $this->l('The image is required.');
        }

        foreach ($languages as $lang) {
            if (!empty($_FILES['image_' . $lang['id_lang']]['type'])) {
                if (ImageManager::validateUpload($_FILES['image_' . $lang['id_lang']], 4000000)) {
                    $errors[] = $this->l('Image format not recognized, allowed format is: .gif, .jpg, .png');
                }
            }
        }

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The title is required.');
        } elseif (!Validate::isGenericName(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('Bad title format.');
        }

        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The url is required.');
        } elseif (!Validate::isUrl(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('Bad url format.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }
        return false;
    }

    protected function deleteSlick()
    {
        $tab = new ClassBonSlick(Tools::getValue('id_tab'));
        $res = $tab->delete();

        if (!$res) {
            return $this->displayError($this->l('Error occurred when deleting the tab'));
        }

        return $this->displayConfirmation($this->l('The tab is successfully deleted'));
    }

    protected function updateStatusTab()
    {
        $tab = new ClassBonSlick(Tools::getValue('id_tab'));

        if ($tab->status == 1) {
            $tab->status = 0;
        } else {
            $tab->status = 1;
        }

        if (!$tab->update()) {
            return $this->displayError($this->l('The tab status could not be updated.'));
        }

        return $this->displayConfirmation($this->l('The tab status is successfully updated.'));
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }
        Media::addJsDefL('ajax_theme_url', $this->context->link->getAdminLink('AdminAjaxBonSlick'));
        $this->context->smarty->assign('ajax_theme_url', $this->context->link->getAdminLink('AdminAjaxBonSlick'));
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJS($this->_path.'views/js/bonslick_back.js');
        $this->context->controller->addCSS($this->_path.'views/css/bonslick_back.css');
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/bonslick_front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/bonslick_front.css');
        $this->context->controller->addJS($this->_path.'views/js/slick.js');
        $this->context->controller->addCSS($this->_path.'views/css/slick.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/slick-theme.css', 'all');

        $this->context->smarty->assign('settings', $this->getSlickSettings());

        return $this->display($this->_path, '/views/templates/hook/bonslick-header.tpl');
    }

    public function hookDisplayHome()
    {
        $slider_front = new ClassBonSlick();
        $tabs = $slider_front->getTopFrontItems($this->id_shop, true);
        $result = array();

        foreach ($tabs as $key => $tab) {
            $result[$key]['title'] = $tab['title'];
            $result[$key]['description'] = $tab['description'];
            $result[$key]['image'] = $tab['image'];
            $result[$key]['url'] = $tab['url'];
        }

        $this->context->smarty->assign('image_baseurl', $this->_path.'images/');
        $this->context->smarty->assign('items', $result);

        return $this->display(__FILE__, 'views/templates/hook/bonslick-front.tpl');
    }

    public function hookdisplayTop()
    {
        return $this->hookDisplayHome();
    }

    public function hookdisplayTopColumn()
    {
        return $this->hookDisplayHome();
    }

    public function hookdisplayCustomSlick()
    {
        return $this->hookDisplayHome();
    }
}
