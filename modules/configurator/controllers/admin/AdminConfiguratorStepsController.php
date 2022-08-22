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
require_once(dirname(__FILE__) . '/../../classes/step/ConfiguratorStepAbstract.php');
require_once(dirname(__FILE__) . '/../../classes/filter/ConfiguratorStepFilterAbstract.php');
require_once(dirname(__FILE__) . '/../../classes/ConfiguratorStepGroupModel.php');
require_once(dirname(__FILE__) . '/../../classes/option/ConfiguratorStepOptionAbstract.php');
require_once(dirname(__FILE__) . '/../../configurator.php');

class AdminConfiguratorStepsController extends ModuleAdminController
{
    const ADMIN_CONFIGURATOR_CONTROLLER = 'AdminConfigurator';

    private $_id_configurator;

    public $available_tabs = array();
    public $conditions_choices = array();
    public $filters_choices = array();
    public $find_division_step = false;
    public $id_tab_selected = 'ConfigurationStep';

    public static $types = array();
    public static $filter_types = array();
    public static $price_list_types = array();
    public static $price_list_display = array();
    public static $impact_types = array();
    public static $price_calculation_types = array();
    public static $price_calculation_default = null;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'configurator_step';
        $this->className = 'ConfiguratorStepTypeAttributeModel';
        $this->lang = true;

        /**
         * @todo: FIX bug de la pagination
         * Sans doutes utiliser en natif le helperList
         */
        $this->_default_pagination = 1000;
        $this->_pagination = array(1000);

        // Administration tabs
        $this->available_tabs = array();
        $this->available_tabs['ConfigurationStep'] = $this->l('Configuration');
        $this->available_tabs['Attributes'] = $this->l('Attributes');
        $this->available_tabs['Features'] = $this->l('Features');
        if (Module::isInstalled('dm_upack') && Module::isEnabled('dm_upack')) {
            $this->available_tabs['Products'] = $this->l('Products');
        }
        if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
            $this->available_tabs['Formula'] = $this->l('Pricing formula');
            $this->available_tabs['FormulaSurface'] = $this->l('Surface formula');
        }
        if (Module::isInstalled('dm_designer') && Module::isEnabled('dm_designer')) {
            $this->available_tabs['Designer'] = $this->l('Designer');
        }

        // Steps types
        self::$types = array();
        self::$types[ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES] = $this->l('With attributes');
        self::$types[ConfiguratorStepAbstract::TYPE_STEP_FEATURES] = $this->l('With features');
        if (Module::isInstalled('dm_upack') && Module::isEnabled('dm_upack')) {
            self::$types[ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS] = $this->l('With products');
        }
        self::$types[ConfiguratorStepAbstract::TYPE_STEP_UPLOAD] = $this->l('File upload');
        if (Module::isInstalled('dm_designer') && Module::isEnabled('dm_designer')) {
            self::$types[ConfiguratorStepAbstract::TYPE_STEP_DESIGNER] = $this->l('With designer');
        }

        // Filter types
        self::$filter_types = array();
        self::$filter_types[ConfiguratorStepFilterAbstract::TYPE_FILTER_FEATURES] = $this->l('Features');

        // Price list types
        self::$price_list_types = array(
            ConfiguratorStepAbstract::PRICE_LIST_TYPE_AMOUNT => $this->l('Capital gain'),
            ConfiguratorStepAbstract::PRICE_LIST_TYPE_PERCENT => $this->l('Percentage')
        );

        // Price list display
        self::$price_list_display = array(
            ConfiguratorStepAbstract::PRICE_LIST_DISPLAY_INPUT => $this->l('Text entry field'),
            ConfiguratorStepAbstract::PRICE_LIST_DISPLAY_SELECT => $this->l('Dropdown'),
            ConfiguratorStepAbstract::PRICE_LIST_DISPLAY_TABLE => $this->l('Table')
        );

        // Impact types
        self::$impact_types = array(
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_AMOUNT => $this->l('Capital gain'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_AMOUNT_PERIOD => $this->l('Capital gain with period'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_REDUCTION_AMOUNT => $this->l('Reduction gain'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_PERCENT => $this->l('Percent'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_NEGATIVE_PERCENT => $this->l('Negative percent'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER => $this->l('Multiplier'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_NEGATIVE_MULTIPLIER => $this->l('Negative multiplier'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER_PRICE => $this->l('Multiplier step price'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_AREA => $this->l('Area'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_AREA_MULTIPLE => $this->l('Area multiple'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST => $this->l('Pricelist'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_MULTIPLE => $this->l('Pricelist multiple'),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA => $this->l(
                'Area pricelist (find surface x price by square)'
            ),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA_SQUARE => $this->l(
                'Area pricelist (price depending of finding square)'
            ),
            ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_QUANTITY => $this->l('Pricelist with quantity'),
        );
        if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
            self::$impact_types[ConfiguratorStepOptionAbstract::IMPACT_TYPE_AMOUNT_FORMULA] = $this->l(
                'Capital gain with formula'
            );
        }

        // Price calculation types
        self::$price_calculation_types = array(
            ConfiguratorStepOptionAbstract::PRICE_CALCULATION_TYPE_WITH_REDUC => $this->l('With reduction'),
            ConfiguratorStepOptionAbstract::PRICE_CALCULATION_TYPE_WITHOUT_REDUC => $this->l('Without reduction')
        );
        self::$price_calculation_default = ConfiguratorStepOptionAbstract::PRICE_CALCULATION_TYPE_WITH_REDUC;

        Configurator::cleanCache();
        parent::__construct();

        $this->_id_configurator = (int)Tools::getValue('id_configurator');
        if (!$this->_id_configurator && !Tools::getValue('deleteconfigurator_step')) {
            Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_CONFIGURATOR_CONTROLLER));
        }
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

    public function initProcess()
    {
        parent::initProcess();

        // Download
        if (Tools::getIsset('price_list_download')) {
            $this->downloadPriceList();
            die();
        }

        // Uniquement dans le cas d'une modification
        if (!Tools::isSubmit('submitAddconfigurator_step')
            && !Tools::isSubmit('submitAddconfigurator_stepAndStay')
            && $this->display === 'edit'
            && $this->loadObject()
        ) {
            // Process setting previous steps and options
            $this->processGetPreviousSteps();
        }
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::getIsset('ajax') && Tools::getIsset('save_option') && Tools::getIsset('step_id')) {
            // Enregistrer une option en AJAX
            $this->saveOptionFormAjax();
        } else {
            if (Tools::getIsset('ajax') && Tools::getIsset('id_option_html')) {
                // Récupérer le formulaire d'une option en AJAX
                $this->displayOptionFormAjax();
            }
        }

        // CONFIGURATOR HOOK
        Hook::exec('configuratorAdminActionStepsControllerAfterPostProcess', array(
            'controller' => &$this
        ));
    }

    public function initHeader()
    {
        self::$currentIndex = self::$currentIndex . '&id_configurator=' . $this->_id_configurator;
        parent::initHeader();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addjQueryPlugin(array(
            'chosen',
            'tablednd',
            'select2',
            'autocomplete'
        ));
        $this->addJS(array(
            /**
             * Core Scripts
             */
            _PS_JS_DIR_ . 'tiny_mce/tinymce.min.js',
            _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            _PS_JS_DIR_ . 'admin/admin-dnd.js',
            /**
             * Service Scripts
             */
            _MODULE_DIR_ . $this->module->name . '/views/js/services/price-list-header.js',
            /**
             * jQuery Plugin
             */
            _MODULE_DIR_ . $this->module->name . '/views/js/jquery.typewatch.js',
            /**
             * Module Scripts
             */
            _MODULE_DIR_ . $this->module->name . '/views/js/handlebars-v3.0.0.js',
            _MODULE_DIR_ . $this->module->name . '/views/js/adminConfigurator.js',
            /**
             * RETRO COMPATIBILITY WITH STORES
             * WHICH INCLUDE THESES FILES DIRECTLY
             * IN /JS/ DIRECTORY
             */
            _PS_JS_DIR_ . 'tinymce.inc.js',
            _PS_JS_DIR_ . 'admin-dnd.js'
        ));

        // CONFIGURATOR HOOK
        Hook::exec('configuratorAdminActionStepsControllerSetMedia', array(
            'controller' => &$this
        ));
    }

    public function initPageHeaderToolbar()
    {
        $configurator = new ConfiguratorModel((int)$this->_id_configurator);


        $product = new Product((int)$configurator->id_product);
        if (Validate::isLoadedObject($product)) {
            if (DMTools::getVersionMajor() < 17) {
                $this->page_header_toolbar_btn['preview_product'] = array(
                    'href' => $this->context->link->getProductLink($product),
                    'desc' => $this->l('Preview the product'),
                    'icon' => 'process-icon-preview',
                    'target' => '_blank'
                );
            } else {
                $category = new Category($product->id_category_default);
                if (isset($category->link_rewrite[$this->context->language->id])) {
                    $category_name = $category->link_rewrite[$this->context->language->id];
                } else {
                    $category_name = '';
                }
                $this->page_header_toolbar_btn['preview_product'] = array(
                    'href' => $this->context->link->getProductLink($product, null, $category_name),
                    'desc' => $this->l('Preview the product'),
                    'icon' => 'process-icon-preview',
                    'target' => '_blank'
                );
            }
        }

        if (DMTools::getVersionMajor() < 17) {
            $this->page_header_toolbar_btn['back_product'] = array(
                'href' => $this->context->link->getAdminLink('AdminProducts') .
                    '&updateproduct' .
                    '&id_product=' . (int)$configurator->id_product .
                    '&key_tab=ModuleConfigurator',
                'desc' => $this->l('Back to the product'),
                'icon' => 'process-icon-back'
            );
        } else {
            global $kernel;
            $sfRouter = $kernel->getContainer()->get('router');
            $url = $sfRouter->generate(
                'admin_product_form',
                array('id' => (int)$configurator->id_product)
            ) . "#tab-hooks";
            $this->page_header_toolbar_btn['back_product'] = array(
                'href' => $url,
                'desc' => $this->l('Back to the product'),
                'icon' => 'process-icon-back'
            );
        }

        $url = $this->context->link->getAdminLink('AdminConfigurator');
        if ($this->display === 'edit' || $this->display === 'add') {
            $url = $this->context->link->getAdminLink('AdminConfiguratorSteps') .
                '&id_configurator=' . $this->_id_configurator;
        }

        $this->page_header_toolbar_btn['back_previous_page'] = array(
            'href' => $url,
            'desc' => $this->l('Back to previous page'),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
    }

    /**
     * Override to set id_configurator
     */
    public function initToolbar()
    {
        parent::initToolbar();
        if (isset($this->toolbar_btn['new'])) {
            //$this->toolbar_btn['new']['href'] .= '&id_configurator='.$this->_id_configurator;
        }
    }

    /**
     * Override to set id_configurator
     */
    public function displayEditLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');
        if (!array_key_exists('Edit', self::$cache_lang)) {
            self::$cache_lang['Edit'] = $this->l('Edit', 'Helper');
        }

        // Validator
        $name = self::$cache_lang['Edit'];

        $tpl->assign(array(
            'href' => self::$currentIndex
                . '&' . $this->identifier . '=' . $id
                . '&update' . $this->table
                . '&token=' . ($token != null ? $token : $this->token),
            //.'&id_configurator='.$this->_id_configurator,
            'action' => $name,
            'id' => $id
        ));

        return $tpl->fetch();
    }

    /**
     * Override to set id_configurator
     */
    public function displayDeleteLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');

        if (!array_key_exists('Delete', self::$cache_lang)) {
            self::$cache_lang['Delete'] = $this->l('Delete', 'Helper');
        }

        if (!array_key_exists('DeleteItem', self::$cache_lang)) {
            self::$cache_lang['DeleteItem'] = $this->l('Delete selected item?', 'Helper', true, false);
        }

        if (!array_key_exists('Name', self::$cache_lang)) {
            self::$cache_lang['Name'] = $this->l('Name:', 'Helper', true, false);
        }

        if (!is_null($name)) {
            $name = addcslashes('\n\n' . self::$cache_lang['Name'] . ' ' . $name, '\'');
        }

        $data = array(
            $this->identifier => $id,
            'href' => self::$currentIndex
                . '&' . $this->identifier . '=' . $id
                . '&delete' . $this->table
                . '&token=' . ($token != null ? $token : $this->token),
            //.'&id_configurator='.$this->_id_configurator,
            'action' => self::$cache_lang['Delete'],
        );

        if ($this->specificConfirmDelete !== false) {
            $data['confirm'] = !is_null($this->specificConfirmDelete)
                ? '\r' . $this->specificConfirmDelete
                : Tools::safeOutput(self::$cache_lang['DeleteItem'] . $name);
        }

        $tpl->assign(array_merge($this->tpl_delete_link_vars, $data));

        return $tpl->fetch();
    }

    /**
     * Override to set id_configurator
     */
    public function processSave()
    {
        // @TODO: à modifier
        $_POST['id_option_group'] = Tools::getValue('id_option_group_' . Tools::getValue('type'));

        $this->className = ConfiguratorStepFactory::getObjectName(Tools::getValue('type'));

        Configurator::cleanCache();
        $object = parent::processSave();

        // CONFIGURATOR HOOK
        Hook::exec('configuratorAdminStepsControllerProcessSave', array(
            'controller' => &$this,
            'configurator_step' => $object
        ));

        if (($object->type === ConfiguratorStepAbstract::TYPE_STEP_UPLOAD || $object->type === ConfiguratorStepAbstract::TYPE_STEP_DESIGNER) && $object->ignored_if_empty) {
            $object->ignored_if_empty = false;
            $object->save();
        }

        if (count($this->errors) || Tools::isSubmit('submitAddconfigurator_stepAndStay')) {
            $this->redirect_after = self::$currentIndex
                . (Tools::isSubmit('submitAddconfigurator_stepAndStay')
                    ? '&' . $this->identifier . '=' . $this->object->id
                    : '')
                . (Tools::isSubmit('submitAddconfigurator_stepAndStay') ? '&update' . $this->table : '')
                . '&token=' . $this->token
                . '&id_configurator=' . $this->_id_configurator;
        }
        $this->generateGroupLink();
        return $object;
    }

    public function generateGroupLink()
    {
        $id_configurator_step = Tools::getValue('id_configurator_step');
        $step_groups = Tools::getValue('stepgroups');
        ConfiguratorStepGroupModel::deleteLinkedStepById($id_configurator_step);
        if ($id_configurator_step > 0 && is_array($step_groups)) {
            $step_groups[] = $id_configurator_step;
            ConfiguratorStepGroupModel::addLinkedStepById($step_groups);
        }
    }

    /**
     * Override to set id_configurator
     */
    public function processDelete()
    {
        $object = parent::processDelete();
        Configurator::cleanCache();
        $this->redirect_after = self::$currentIndex
            . '&token=' . $this->token
            . '&id_configurator=' . $this->_id_configurator;
        return $object;
    }

    public function afterUpdate($step)
    {
        // clean cache models
        Configurator::cleanCache();

        $step->cleanSelectedDefault();

        // Process update impact price

        $options = $this->object->getOptions((int)Context::getContext()->language->id, true);

        foreach ($options as $option) {
            $this->saveOption($step, $option);
        }
        // Process update conditions rules
        $this->saveConditions();
        // Process update conditions rules
        $this->saveFilters();
        // Process update max_options
        if ($step->multiple && !$step->isValidMaxOptions($step->max_options)) {
            $options = $step->getNumberOfOptions();
            $error_msg = 'An error occurred while updating Maximum number of choices.';
            $error_msg .= ' You indicated a number of choices greater than the number of options that is %s.';
            $this->errors[] = sprintf($error_msg, $options);

            // Reset max_options value
            $step->max_options = 0;
            $step->save();
        }

        // clean cache models
        Configurator::cleanCache();

        return parent::afterUpdate($step);
    }

    private function saveOption($step, $configurator_step_option)
    {
        if (Validate::isLoadedObject($configurator_step_option)) {
            $configurator_step_option = ConfiguratorStepOptionFactory::newObject($configurator_step_option->id);
            // CONFIGURATOR HOOK
            Hook::exec('configuratorAdminActionStepsBeforeSaveOption', array(
                'configuratorStepOption' => &$configurator_step_option
            ));

            $input_id = $configurator_step_option->id;

            if (!empty($step->weight) || $step->weight) {
                $configurator_step_option->weight = 0;
            }

            if (!Tools::getIsset('reference_' . $input_id)) {
                $configurator_step_option->save();
                return;
            }

            // General informations
            $configurator_step_option->content = array();
            foreach ($this->getLanguages() as $language) {
                $configurator_step_option->content[(int)$language['id_lang']] = Tools::getValue(
                    'content_' . $input_id . '_' . (int)$language['id_lang']
                );
            }
            $configurator_step_option->reference = Tools::getValue('reference_' . $input_id);
            $configurator_step_option->reference_position = Tools::getValue('reference_position_' . $input_id);
            $configurator_step_option->default_value = Tools::getValue('default_value_' . $input_id);
            $configurator_step_option->slider = Tools::getValue('slider_' . $input_id);
            $configurator_step_option->slider_step = Tools::getValue('slider_step_' . $input_id);
            $configurator_step_option->textarea = Tools::getValue('textarea_' . $input_id);
            $configurator_step_option->default_qty = Tools::getValue('default_qty_' . $input_id);
            $configurator_step_option->qty_coeff = Tools::getValue('qty_coeff_' . $input_id);
            $configurator_step_option->email = Tools::getValue('email_' . $input_id);
            $configurator_step_option->is_date = Tools::getValue('is_date_' . $input_id);
            $configurator_step_option->is_ralstep = Tools::getValue('is_ralstep_' . $input_id);
            $configurator_step_option->id_atribute_ral = Tools::getValue('id_atribute_ral_' . $input_id);
            $configurator_step_option->force_value = Tools::getValue('force_value_' . $input_id);
            $configurator_step_option->check_value = Tools::getValue('check_value_' . $input_id);
            $configurator_step_option->min_value = Tools::getValue('min_value_' . $input_id);
            $configurator_step_option->min_value_if_null = Tools::getValue('min_value_if_null_' . $input_id);
            $configurator_step_option->max_value = Tools::getValue('max_value_' . $input_id);
            $configurator_step_option->max_value_if_null = Tools::getValue('max_value_if_null_' . $input_id);
            if (empty($step->weight) || !$step->weight) {
                $configurator_step_option->weight = Tools::getValue('weight_' . $input_id);
            }
            $configurator_step_option->used_for_dimension = Tools::getValue('used_for_dimension_' . $input_id);
            $configurator_step_option->dimension_coeff = (float)Tools::getValue('dimension_coeff_' . $input_id);
            $configurator_step_option->price_calculation = Tools::getValue('price_calculation_' . $input_id);
            $configurator_step_option->display_price_calculation = Tools::getValue('display_price_calculation_' . $input_id);

            // Impact price
            if ($impact = Tools::getValue('impact_' . $input_id)) {
                $impact_step_id = Tools::getValue('select_step_impact_' . $input_id);
                $impact_multiple_step_id = Tools::getValue('select_step_impact_multiple_' . $input_id);
                $impact_step_id_all = Tools::getValue('select_step_impact_all_' . $input_id);
                $impact_step_singleinput_id = Tools::getValue('select_step_impact_singleinput_' . $input_id);
                $pricelist_helper = new PricelistHelper();
                $impact_exploded = explode(',', $impact);
                $file = isset($_FILES['pricelist_file_' . $input_id])
                    ? $_FILES['pricelist_file_' . $input_id]
                    : array('error' => 0);
                $price_list = Tools::getValue('price_list_' . $input_id);

                $configurator_step_option->impact_type = $impact_exploded[0];
                $configurator_step_option->impact_value = (float)$impact_exploded[1];

                // Verify period values
                $impact_value_period_tmp = Tools::getValue('impact_value_period');
                if ($impact_value_period_tmp) {
                    $impact_value_period_tmp = json_decode($impact_value_period_tmp, true);
                    $impact_value_period = array();
                    if (is_array($impact_value_period_tmp)) {
                        foreach ($impact_value_period_tmp as $i => $period_value) {
                            if (isset($period_value['id']) && isset($period_value['values'])) {
                                if (isset($period_value['values']['date_start'])
                                    && isset($period_value['values']['date_end'])
                                    && isset($period_value['values']['specific_value'])
                                ) {
                                    if (Validate::isDate($period_value['values']['date_start'])
                                        && Validate::isDate($period_value['values']['date_end'])
                                    ) {
                                        $impact_value_period[(int)$i] = array(
                                            'id' => (int)$i,
                                            'values' => array(
                                                'date_start' => (string)$period_value['values']['date_start'],
                                                'date_end' => (string)$period_value['values']['date_end'],
                                                'specific_value' => (float)$period_value['values']['specific_value']
                                            )
                                        );
                                    }
                                }
                            }
                        }
                    }
                    $configurator_step_option->impact_value_period = json_encode($impact_value_period);
                } else {
                    $configurator_step_option->impact_value_period = '';
                }
                $configurator_step_option->unity = Tools::getValue('unity_' . $input_id);
                $configurator_step_option->conversion_factor = (float)Tools::getValue('conversion_factor_' . $input_id);

                switch ($configurator_step_option->impact_type) {
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST:
                        if ($impact_step_id) {
                            $configurator_step_option->impact_step_id = (int)$impact_step_id;
                        }
                        $configurator_step_option->impact_value = 0;
                        if (!$file['error'] && $pricelist_helper->load($file)) {
                            $configurator_step_option->price_list = Tools::jsonEncode(
                                $pricelist_helper->getPricelist()
                            );
                        } else {
                            $configurator_step_option->price_list = $price_list;
                        }
                        // X/Y option
                        $is_xy_option = Tools::getValue('use_step_option_' . $input_id);
                        if ($is_xy_option) {
                            $step_x = Tools::getValue('select_step_x_impact_step_option_' . $input_id);
                            $option_x = Tools::getValue('select_option_x_impact_step_option_' . $input_id);
                            $step_y = Tools::getValue('select_step_y_impact_step_option_' . $input_id);
                            $option_y = Tools::getValue('select_option_y_impact_step_option_' . $input_id);
                            $configurator_step_option->impact_step_option_id = $step_x . '-' . $option_x
                                . ',' . $step_y . '-' . $option_y;
                            $configurator_step_option->id_impact_step_option_x = $option_x;
                            $configurator_step_option->id_impact_step_option_y = $option_y;
                            $configurator_step_option->impact_step_id = 0;
                        } else {
                            $configurator_step_option->impact_step_option_id = null;
                        }
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_QUANTITY:
                        if ($impact_step_id) {
                            $configurator_step_option->impact_step_id = (int)$impact_step_id;
                        }
                        $configurator_step_option->impact_value = 0;
                        if (!$file['error'] && $pricelist_helper->load($file)) {
                            $configurator_step_option->price_list = Tools::jsonEncode(
                                $pricelist_helper->getPricelist()
                            );
                        } else {
                            $configurator_step_option->price_list = $price_list;
                        }
                        $configurator_step_option->impact_step_option_id = null;
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA_SQUARE:
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA:
                        if ($impact_step_id) {
                            $configurator_step_option->impact_step_id = (int)$impact_step_id;
                        }
                        $configurator_step_option->impact_value = 0;
                        if (!$file['error'] && $pricelist_helper->load($file)) {
                            $configurator_step_option->price_list = Tools::jsonEncode(
                                $pricelist_helper->getPricelist()
                            );
                        } else {
                            $configurator_step_option->price_list = $price_list;
                        }
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_MULTIPLE:
                        if (count($impact_multiple_step_id) > 0 && isset($impact_multiple_step_id[0])) {
                            $configurator_step_option->impact_step_id = (int)$impact_multiple_step_id[0];
                            $impact_multiple_step_id = implode(',', $impact_multiple_step_id);
                            $configurator_step_option->impact_multiple_step_id = $impact_multiple_step_id;
                        }
                        //$configuratorStepOption->impact_value = 0;
                        if (!$file['error'] && $pricelist_helper->load($file)) {
                            $configurator_step_option->price_list = Tools::jsonEncode(
                                $pricelist_helper->getPricelist()
                            );
                        } else {
                            $configurator_step_option->price_list = $price_list;
                        }
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_AREA:
                        if ($impact_step_id) {
                            $configurator_step_option->impact_step_id = (int)$impact_step_id;
                        }
                        $configurator_step_option->price_list = null;
                        // X/Y option
                        $is_xy_option = Tools::getValue('use_step_option_' . $input_id);
                        if ($is_xy_option) {
                            //$step_x = Tools::getValue('select_step_x_impact_step_option_'.$input_id);
                            $option_x = Tools::getValue('select_option_x_impact_step_option_' . $input_id);
                            //$step_y = Tools::getValue('select_step_y_impact_step_option_'.$input_id);
                            $option_y = Tools::getValue('select_option_y_impact_step_option_' . $input_id);
                            $configurator_step_option->id_impact_step_option_x = $option_x;
                            $configurator_step_option->id_impact_step_option_y = $option_y;
                            $configurator_step_option->impact_step_id = 0;
                        } else {
                            $configurator_step_option->impact_step_option_id = null;
                        }
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_AREA_MULTIPLE:
                        if (count($impact_multiple_step_id) > 0 && isset($impact_multiple_step_id[0])) {
                            $configurator_step_option->impact_step_id = (int)$impact_multiple_step_id[0];
                            $impact_multiple_step_id = implode(',', $impact_multiple_step_id);
                            $configurator_step_option->impact_multiple_step_id = $impact_multiple_step_id;
                        }
                        $configurator_step_option->price_list = null;
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER:
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_NEGATIVE_MULTIPLIER:
                        $configurator_step_option->price_list = null;
                        if ($impact_step_singleinput_id) {
                            $configurator_step_option->impact_step_id = (int)$impact_step_singleinput_id;
                        }
                        break;
                    case ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER_PRICE:
                        $configurator_step_option->price_list = null;
                        if ($impact_step_id_all) {
                            $configurator_step_option->impact_step_id = (int)$impact_step_id_all;
                        }
                        break;
                    default:
                        $configurator_step_option->impact_step_id = null;
                        $configurator_step_option->impact_multiple_step_id = null;
                        $configurator_step_option->price_list = null;
                        break;
                }
            }

            // Quantity impact
            if (Tools::getValue('use_impact_qty_' . $input_id)) {
                $configurator_step_option->id_step_impact_qty = (int)Tools::getValue('select_impact_qty_step_' . $input_id);
                $configurator_step_option->id_step_option_impact_qty = (int)Tools::getValue('select_impact_qty_step_option_' . $input_id);
            } else {
                $configurator_step_option->id_step_impact_qty = 0;
                $configurator_step_option->id_step_option_impact_qty = 0;
            }

            // Impact tax
            $configurator_step_option->id_tax_rules_group_product = (int)Tools::getValue('select_taximpact_' . $input_id);

            // Division
            $divisions = Tools::getValue('id_configurator_step_option_division');
            $configurator_step_option->id_configurator_step_option_division =
                (int)$divisions[$configurator_step_option->id];

            // CONFIGURATOR HOOK
            Hook::exec('configuratorAdminActionStepsAfterSaveOption', array(
                'controller' => &$this,
                'option' => $configurator_step_option->option,
                'configuratorStepOption' => &$configurator_step_option,
                'input_id' => $input_id,
                'step' => $step
            ));

            if (!$configurator_step_option->save()) {
                $this->errors[] = sprintf(
                    $this->l('An error occurred while updating impact for %s'),
                    $configurator_step_option->option['name']
                );
            }
        }
    }

    private function saveConditions()
    {
        if (($conditions = Tools::getValue('conditions'))) {
            foreach ($conditions as $type => $condition) {
                foreach ($condition as $foreignkey => $jsonGroups) {
                    $groups = Tools::jsonDecode($jsonGroups, true);
                    if (!is_null($groups) && !empty($groups)) {
                        $groups = $this->checkConditions($groups, (int)$foreignkey);
                        $this->object->deleteConditions($type, (int)$foreignkey);
                        $this->object->saveConditions($type, (int)$foreignkey, $groups);
                    } elseif (is_null($groups)) {
                        $error_msg = 'An error occurred while updating display Conditions for %s foreignKey.';
                        $error_msg .= ' JSON STRING RECEIVE : %s';
                        $this->errors[] = sprintf(
                            $this->l($error_msg),
                            $foreignkey,
                            $jsonGroups
                        );
                    }
                }
            }
        }
    }

    private function saveFilters()
    {
        if (($filters = Tools::getValue('filters'))) {
            foreach ($filters as $type => $filter) {
                foreach ($filter as $foreignkey => $jsonGroups) {
                    $groups = Tools::jsonDecode($jsonGroups, true);
                    if (!is_null($groups) && !empty($groups)) {
                        $this->object->deleteFilters($type, (int)$foreignkey);
                        $this->object->saveFilters($type, (int)$foreignkey, $groups);
                    } elseif (is_null($groups)) {
                        $error_msg = 'An error occurred while updating display Filters for %s foreignKey.';
                        $error_msg .= ' JSON STRING RECEIVE : %s';
                        $this->errors[] = sprintf(
                            $this->l($error_msg),
                            $foreignkey,
                            $jsonGroups
                        );
                    }
                }
            }
        }
    }

    /**
     * check if condition has min/max ans step has a price_list
     */
    public function checkConditions($condition_groups, $foreignkey)
    {
        foreach ($condition_groups as &$condition_group) {
            if (isset($condition_group['datas'])) {
                foreach ($condition_group['datas'] as &$condition) {
                    if (!isset($condition['formula']) && (int)$condition['id'] > 0) {
                        $configuratorStep = ConfiguratorStepAbstract::getByIdOption((int)$condition['id']);
                        if (empty($configuratorStep->price_list) && (bool)$configuratorStep->use_input === false) {
                            $condition['min'] = $condition['max'] = 0;
                        }
                    }
                }
            }
        }
        return $condition_groups;
    }

    public function ajaxProcessUpdateOptionUsed()
    {
        if ($this->tabAccess['edit'] === '1' || DMTools::getVersionMajor() >= 17) {
            $id_option = (int)Tools::getValue('id_option');
            $ipa = (int)Tools::getValue('ipa', 0);
            $configuratorStep = ConfiguratorStepFactory::newObject((int)Tools::getValue('id_configurator_step'));

            if (Validate::isLoadedObject($configuratorStep)) {
                $new_id = $configuratorStep->updateOptionUsed($id_option, $ipa);
                $configuratorStep->updateOptionsPositions();
                $json = array(
                    'status' => 'ok',
                    'message' => $this->_conf[4],
                    'enable' => (bool)$new_id,
                    'new_id' => $new_id
                );
            } else {
                $json = array(
                    'status' => 'error',
                    'message' => $this->l('You cannot enable/disable this option.')
                );
            }

            die(Tools::jsonEncode($json));
        }
    }

    public function ajaxProcessUpdateSelectedDefaultOption()
    {
        if ($this->tabAccess['edit'] === '1' || DMTools::getVersionMajor() >= 17) {
            $id_step_option = (int)Tools::getValue('id_step_option');
            $configuratorStep = ConfiguratorStepFactory::newObject((int)Tools::getValue('id_configurator_step'));
            if (Validate::isLoadedObject($configuratorStep)) {
                if (Tools::getValue('deletedefaultoption')) {
                    $configuratorStep->removeSelectedDefault($id_step_option);
                } else {
                    $configuratorStep->updateSelectedDefaultByIdStepOption($id_step_option);
                }

                $json = array(
                    'status' => 'ok',
                    'multiple' => (int)$configuratorStep->multiple,
                    'message' => $this->_conf[4]
                );
            } else {
                $json = array(
                    'status' => 'error',
                    'message' => $this->l('You cannot select by default this option.')
                );
            }

            die(Tools::jsonEncode($json));
        }
    }

    public function ajaxProcessUpdatePositions()
    {
        if (Tools::getValue('configurator_step')) {
            $this->ajaxProcessUpdateStepsPositions();
        } else {
            if (Tools::getValue('step_option')) {
                $this->ajaxProcessUpdateOptionsPositions();
            }
        }
    }

    public function ajaxProcessUpdateStepsPositions()
    {
        $id_configurator_step = (int)Tools::getValue('id');
        $positions = Tools::getValue('configurator_step');
        $updated_positions = true;
        $new_positions = array();

        foreach ($positions as $v) {
            if (count(explode('_', $v)) == 4) {
                $new_positions[] = $v;
            }
        }

        foreach ($new_positions as $position => $value) {
            $pos = explode('_', $value);
            $configuratorStep = ConfiguratorStepFactory::newObject((int)$pos[2]);
            $updated_positions = $updated_positions && $configuratorStep->updatePosition($position);
        }
        if ($configuratorStep) {
            if (isset($position) && $updated_positions) {
                echo 'ok position ' . (int)$position . ' for configurator step ' . (int)$pos[2] . '\r\n';
            } else {
                echo '{"hasError" : true, "errors" : "Can not update the '
                    . (int)$id_configurator_step . ' configurator step to position ' . (int)$position . ' "}';
            }
        } else {
            echo '{"hasError" : true, "errors" : "The ('
                . (int)$id_configurator_step . ') configurator step cannot be loaded."}';
        }
    }

    public function renderList()
    {
        $this->initToolbar();
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->position_identifier = 'id_configurator_step';

        $this->fields_list = array(
            'id_configurator_step' => array(
                'title' => $this->l('ID')
            ),
            'name' => array(
                'title' => $this->l('Step name'),
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'callback' => 'getStepType'
            ),
            'id_configurator_step_tab' => array(
                'title' => $this->l('Tab'),
                'callback' => 'getStepTab'
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'position' => 'position'
            )
        );

        $this->_orderBy = 'position';
        $this->_where = 'AND a.`id_configurator` = ' . (int)$this->_id_configurator;

        //self::$currentIndex = self::$currentIndex.'&id_configurator='.(int)$this->_id_configurator;

        return parent::renderList();
    }

    public function getStepType($echo, $row)
    {
        switch ($row['type']) {
            case ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES:
                return $this->l('Attributes');
            case ConfiguratorStepAbstract::TYPE_STEP_FEATURES:
                return $this->l('Features');
            case ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS:
                return $this->l('Products');
            case ConfiguratorStepAbstract::TYPE_STEP_UPLOAD:
                return $this->l('Upload');
            case ConfiguratorStepAbstract::TYPE_STEP_DESIGNER:
                return $this->l('Designer');
            default:
                return $row['type'];
        }
    }

    public function getStepTab($echo, $row)
    {
        $configurator_step_tab = new ConfiguratorStepTabModel(
            (int)$row['id_configurator_step_tab'],
            Context::getContext()->language->id
        );
        return (Validate::isLoadedObject($configurator_step_tab)) ? $configurator_step_tab->name : null;
    }

    public function renderForm()
    {
        if (!($this->loadObject(true))) {
            return;
        }

        $this->fields_form = array('');

        // General form vars initialisation
        if (Validate::isLoadedObject(($this->object))) {
            $id_configurator_step = (int)$this->object->id;
        } else {
            $id_configurator_step = (int)Tools::getvalue('id_configurator_step', 0);
        }

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        $this->tpl_form_vars['id_configurator'] = $this->_id_configurator;
        $this->tpl_form_vars['id_configurator_step'] = $id_configurator_step;

        $this->tpl_form_vars['form_action'] = $this->context->link->getAdminLink('AdminConfiguratorSteps')
            . '&' . ($id_configurator_step
                ? 'id_configurator_step=' . (int)$id_configurator_step
                : 'addconfigurator_step')
            . '&id_configurator=' . $this->_id_configurator;

        // Getting tabs content
        foreach (array_keys($this->available_tabs) as $id) {
            if (method_exists($this, 'initForm' . $id)) {
                $this->tpl_form = Tools::strtolower($id) . '.tpl';
                $this->{'initForm' . $id}($this->object);
            } else {
                // Use Hook to generate form
                Hook::exec('configuratorAdminActionStepsControllerInitForm', array(
                    'id' => $id,
                    'controller' => &$this,
                    'currentIndex' => self::$currentIndex
                ));
            }
        }

        $this->tpl_form_vars['configurator_step'] = $this->object;
        $this->tpl_form_vars['default_form_language'] = $this->default_form_language;
        $this->tpl_form_vars['id_lang_default'] = Configuration::get('PS_LANG_DEFAULT');
        return parent::renderForm();
    }

    public function processGetPreviousSteps()
    {
        if (Validate::isLoadedObject(($this->object))) {
            // Getting previous steps and options for display conditions system
            $this->conditions_choices = array(
                'block_option' => array(
                    'name' => $this->l('Option'),
                    'groups' => array(),
                )
            );
            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                $this->conditions_choices['block_formula'] = array(
                    'name' => $this->l('Formula'),
                    'groups' => array(
                        array(
                            'class' => 'col-lg-8',
                            'type' => 'formula',
                        )
                    )
                );
            }
            $previousSteps = $this->object->getPreviousSteps();
            $options_step = array();
            foreach ($previousSteps as $configuratorStep) {
                // Exclude upload type
                if ($configuratorStep->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)) {
                    continue;
                }

                if ($configuratorStep->use_input || $configuratorStep->price_list) {
                    $this->find_division_step = true;
                }

                $options_step[$configuratorStep->id] = array(
                    'option' => $configuratorStep->name,
                    'classname' => get_class($configuratorStep),
                    'object' => $configuratorStep,
                    'attrs' => array(
                        'data-pricelist' => (empty($configuratorStep->price_list) ? 0 : 1)
                            || $configuratorStep->use_input,
                        'data_suffix' => $configuratorStep->input_suffix,
                    )
                );

                $options = $configuratorStep->getOptions((int)$this->context->language->id, true);

                $options_list = array();
                foreach ($options as $configurator_step_option) {
                    if (Validate::isLoadedObject($configurator_step_option)) {
                        $options_list[$configurator_step_option->id] = array(
                            'option' => $configurator_step_option->option['name'],
                            'classname' => get_class($configurator_step_option),
                            'object' => $configurator_step_option,
                            'attrs' => array()
                        );
                    }
                }
                if (!empty($options_list)) {
                    $this->conditions_choices['block_option']['groups'][1]['class'] = 'col-lg-4';
                    $this->conditions_choices['block_option']['groups'][1]['type'] = 'select';
                    $this->conditions_choices['block_option']['groups'][1]['selects'][] = array(
                        'params' => array(
                            'data-parentid' => $configuratorStep->id,
                            'class' => 'select_option'
                        ),
                        'options' => $options_list,
                        'parent_step' => $configuratorStep
                    );
                }
            }

            if (!empty($options_step)) {
                $this->conditions_choices['block_option']['groups'][0]['class'] = 'col-lg-4';
                $this->conditions_choices['block_option']['groups'][0]['type'] = 'select';
                $this->conditions_choices['block_option']['groups'][0]['selects'][] = array(
                    'params' => array(
                        'class' => 'select_step'
                    ),
                    'options' => $options_step
                );
                ksort($this->conditions_choices['block_option']['groups']);
            }

            $this->getFiltersChoices($previousSteps);
        }
    }

    private function getFiltersChoices($previousSteps)
    {
        if (!$previousSteps) {
            return;
        }

        // Type
        $filters_options = self::$filter_types;

        // Option
        $features = Feature::getFeatures($this->context->language->id);
        $features_options = array();
        foreach ($features as $feature) {
            $features_options[$feature['id_feature']] = $feature['name'];
        }
        asort($features_options);

        // Operator
        $selectors_options = array(
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_EQUAL => $this->l('equal to') . ' (=)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_CONTAINS => $this->l('contains'),
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_CONTAINS_AT_LEAST => $this->l('contains at least'),
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_CONTAINED => $this->l('contained'),
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_UPPER => $this->l('upper to') . ' (>)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_UPPER_OR_EQUAL => $this->l('upper or equal to') . ' (>=)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_LOWER => $this->l('lower to') . ' (<)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_LOWER_OR_EQUAL => $this->l('lower or equal to') . ' (<=)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_EQUAL_NUMBER => $this->l('equal to number') . ' (=)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_UPPER_NUMBER => $this->l('upper to number') . ' (>)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_UPPER_OR_EQUAL_NUMBER => $this->l('upper or equal to number') . ' (>=)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_LOWER_NUMBER => $this->l('lower to number') . ' (<)',
            ConfiguratorStepFilterAbstract::TYPE_OPERATOR_LOWER_OR_EQUAL_NUMBER => $this->l('lower or equal to number') . ' (<=)',
        );
        if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
            $selectors_options[ConfiguratorStepFilterAbstract::TYPE_OPERATOR_EQUAL_FORMULA] = $this->l('equal to formula') . ' (=)';
            $selectors_options[ConfiguratorStepFilterAbstract::TYPE_OPERATOR_UPPER_FORMULA] = $this->l('upper to formula') . ' (>)';
            $selectors_options[ConfiguratorStepFilterAbstract::TYPE_OPERATOR_UPPER_OR_EQUAL_FORMULA] = $this->l('upper or equal to formula') . ' (>=)';
            $selectors_options[ConfiguratorStepFilterAbstract::TYPE_OPERATOR_LOWER_FORMULA] = $this->l('lower to formula') . ' (<)';
            $selectors_options[ConfiguratorStepFilterAbstract::TYPE_OPERATOR_LOWER_OR_EQUAL_FORMULA] = $this->l('lower or equal to formula') . ' (<=)';
        }

        // Target step
        $steps_options = array();
        foreach ($previousSteps as $previousStep) {
            $steps_options[$previousStep->id] = $previousStep;
        }

        // Value
        $value_option = array(
            ConfiguratorStepFilterAbstract::TYPE_VALUE_ID => $this->l('ID'),
            ConfiguratorStepFilterAbstract::TYPE_VALUE_NAME => $this->l('Name')
        );

        $this->filters_choices['filters_options'] = $filters_options;
        $this->filters_choices['features_options'] = $features_options;
        $this->filters_choices['selectors_options'] = $selectors_options;
        $this->filters_choices['steps_options'] = $steps_options;
        $this->filters_choices['value_option'] = $value_option;
    }

    public function initModal()
    {
        if (!$this->ajax
            //&& Tools::getValue('addconfigurator_step', false) !== false
            && Tools::getValue('updateconfigurator_step', false) !== false
        ) {
            parent::initModal();

            $data = $this->createTemplate('modal/price_list_header.tpl');

            $this->modals[] = array(
                'modal_id' => 'modal_configurator_header',
                'modal_class' => 'modal-md',
                'modal_title' => '<i class="icon-cog"></i> ' . $this->l('Header configuration'),
                'modal_content' => $data->fetch(),
            );

            // CONFIGURATOR HOOK
            Hook::exec('configuratorAdminActionStepsControllerInitModal', array(
                'controller' => &$this
            ));
        }
    }

    public function initContent()
    {
        if ($this->display == 'edit' || $this->display == 'add') {
            // Getting tabs infos
            $tabs = array();
            foreach ($this->available_tabs as $id => $tab) {
                // Init tabs
                $tabs[$id] = array(
                    'id' => $id,
                    'selected' => $this->id_tab_selected === $id,
                    'name' => $tab,
                    'step_type' => ''
                );
                switch ($id) {
                    case 'Attributes':
                        $tabs[$id]['step_type'] = ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES;
                        break;
                    case 'Features':
                        $tabs[$id]['step_type'] = ConfiguratorStepAbstract::TYPE_STEP_FEATURES;
                        break;
                    case 'Products':
                        $tabs[$id]['step_type'] = ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS;
                        break;
                    case 'Designer':
                        $tabs[$id]['step_type'] = ConfiguratorStepAbstract::TYPE_STEP_DESIGNER;
                        break;
                    default:
                        $tabs[$id]['step_type'] = '';
                        break;
                }
            }

            $this->tpl_form_vars['tabs'] = $tabs;
            // TinyMCE form basic initialisations
            $iso = $this->context->language->iso_code;
            $this->tpl_form_vars['iso'] = file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js')
                ? $iso
                : 'en';
            $this->tpl_form_vars['path_css'] = _THEME_CSS_DIR_;
            $this->tpl_form_vars['ad'] = __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_);
            $this->tpl_form_vars['tinymce'] = true;
        }
        parent::initContent();
    }

    /**
     * Vars for configurationstep tpl
     */
    public function initFormConfigurationStep(ConfiguratorStepAbstract $configurator_step)
    {
        $data = $this->createTemplate($this->tpl_form);
        // Languages
        $data->assign('languages', $this->_languages);
        $data->assign('default_form_language', $this->default_form_language);
        $data->assign('id_lang', $this->context->language->id);
        // Configurator
        $data->assign('configurator_step', $configurator_step);
        $configurator = new ConfiguratorModel($this->_id_configurator);
        if (Validate::isLoadedObject($configurator)) {
            $data->assign('id_configurator', $configurator->id);
            $data->assign('id_product', $configurator->id_product);
        }
        $data->assign('display', $this->display);
        $data->assign('price_list_types', self::$price_list_types);
        $data->assign('price_list_display', self::$price_list_display);
        $data->assign('stepTypes', self::$types);
        $data->assign('extensions', $configurator_step->getExtensionsList());
        $data->assign('custom_template_list', array_diff(
            scandir(_PS_MODULE_DIR_ . 'configurator/views/templates/front/elements/custom'),
            array('..', '.')
        ));
        // Max qty by id_step_option
        $step_option = ConfiguratorStepOptionFactory::newObject((int)$configurator_step->max_qty_step_option_id);
        $data->assign('max_qty_step_id', 0);
        if (Validate::isLoadedObject($step_option)) {
            $data->assign('max_qty_step_id', $step_option->id_configurator_step);
        }
        // Attributes group
        $data->assign(
            'attributesGroup',
            ConfiguratorStepTypeAttributeModel::getGroupsAvailable($this->context->language->id)
        );
        // Features group
        $data->assign(
            'featuresGroup',
            ConfiguratorStepTypeFeatureModel::getGroupsAvailable($this->context->language->id)
        );
        // Conditions
        $data->assign('conditions_choices', $this->conditions_choices);
        $data->assign(
            'conditions_step',
            $this->getJsonConditions(ConfiguratorStepAbstract::TYPE_CONDITION_STEP, $this->object->id)
        );
        // Fiters
        $data->assign('filters_choices', $this->filters_choices);
        $data->assign(
            'filters_step',
            $this->getJsonFilters(ConfiguratorStepAbstract::TYPE_CONDITION_STEP, $this->object->id)
        );
        // Tabs
        $data->assign(
            'configurator_step_tab',
            ConfiguratorStepTabModel::getTabsByIdConfigurator($this->_id_configurator)
        );
        // Steps
        $data->assign('steps_group', ConfiguratorStepAbstract::getStepsByIdConfigurator($this->_id_configurator));
        $data->assign('steps_group_selected', ConfiguratorStepGroupModel::getLinkedStepById($configurator_step->id));

        // Price list download link
        $price_list_download_link = $this->context->link->getAdminLink('AdminConfiguratorSteps')
            . '&price_list_download' . '&id_configurator=' . (int)$this->_id_configurator
            . '&id_configurator_step=' . (int)$this->object->id;
        $data->assign('price_list_download_link', $price_list_download_link);

        // Rendering
        $this->tpl_form_vars['tabs']['ConfigurationStep']['form_content_html'] = $data->fetch();
    }

    /**
     * Vars for options (options) tpl
     */
    public function initFormAttributes(ConfiguratorStepAbstract $configurator_step)
    {
        $data = $this->createTemplate($this->tpl_form);

        $this->context->smarty->assign('type', 'Attributes');

        // Languages
        $data->assign('languages', $this->_languages);
        $data->assign('default_form_language', $this->default_form_language);
        $data->assign('id_lang', $this->context->language->id);
        // Currency
        $data->assign('currency', $this->context->currency);
        // Configurator
        $data->assign('configurator_step', $configurator_step);
        $data->assign('id_configurator', (int)$this->_id_configurator);
        $data->assign('impact_types', self::$impact_types);
        // @TODO: vérifier l'utlité de formula_surface (à déplacer dans Advanced Formula ?)
        $data->assign(
            'pricelist_steps_withsuffix',
            ConfiguratorStepAbstract::getStepsByIdConfigurator(
                (int)$this->_id_configurator,
                'AND (cs.price_list <> \'\' OR cs.use_input = 1 OR cs.use_qty = 1)'
                // 'AND (cs.price_list <> \'\' OR cs.formula_surface <> \'\' OR cs.use_input = 1)'
            )
        );

        $data->assign(
            'pricelist_steps_singleinput',
            ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->_id_configurator, '', '= 1')
        );
        $data->assign(
            'steps_counter',
            count(ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->_id_configurator))
        );
        $data->assign(
            'steps_all',
            ConfiguratorStepAbstract::getStepsByIdConfigurator(
                (int)$this->_id_configurator,
                ' AND cs.position < ' . (int)$configurator_step->position
            )
        );
        // Options
        $options = false;
        $options_conditions = array();
        if ($this->display === 'edit' && $configurator_step->type === ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES) {
            $options = $this->object->getOptions($this->context->language->id, false);
            // Get option conditions
            foreach ($options as $option) {
                $options_conditions[$option->id] = $this->getJsonConditions(
                    ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
                    $option->id
                );
            }
            $data->assign('listAttributes_html', $this->getListAttributes($options));
        }
        $configurator = new ConfiguratorModel((int)$this->_id_configurator);
        $data->assign('configurator', $configurator);
        $data->assign('options', $options);
        $data->assign('options_conditions', $options_conditions);
        // Division
        $data->assign('find_division_step', $this->find_division_step);
        // Conditions
        $data->assign('conditions_choices', $this->conditions_choices);
        $data->assign('filters_choices', $this->filters_choices);

        // All Steps && Options before current step
        $steps_before = ConfiguratorStepAbstract::getStepsByIdConfigurator(
            (int)$this->_id_configurator,
            ' AND cs.position < ' . (int)$configurator_step->position
        );
        $data->assign('steps_before', $steps_before);
        $options_before = null;
        $matching_step_option = array();

        if ($steps_before) {
            foreach ($steps_before as $step_before) {
                $options_before[$step_before->id] = $step_before->getOptions($this->context->language->id, true);
                foreach (ConfiguratorStepOptionAbstract::getByIdConfiguratorStep($step_before->id) as $option_temp) {
                    $matching_step_option[$option_temp->id] = $option_temp->id_configurator_step;
                }
            }
        }
        $data->assign('options_before', $options_before);
        $data->assign('matching_step_option', $matching_step_option);
        // Rendering
        $this->tpl_form_vars['tabs']['Attributes']['form_content_html'] = $data->fetch();
    }

    public function initFormFeatures(ConfiguratorStepAbstract $configurator_step)
    {
        $data = $this->createTemplate($this->tpl_form);

        $this->context->smarty->assign('type', 'Features');

        // Languages
        $data->assign('languages', $this->_languages);
        $data->assign('default_form_language', $this->default_form_language);
        $data->assign('id_lang', $this->context->language->id);
        // Currency
        $data->assign('currency', $this->context->currency);
        // Configurator
        $data->assign('configurator_step', $configurator_step);
        $data->assign('id_configurator', (int)$this->_id_configurator);
        $data->assign('impact_types', self::$impact_types);
        // @TODO: vérifier l'utlité de formula_surface (à déplacer dans Advanced Formula ?)
        $data->assign(
            'pricelist_steps_withsuffix',
            ConfiguratorStepAbstract::getStepsByIdConfigurator(
                (int)$this->_id_configurator,
                'AND (cs.price_list <> \'\' OR cs.use_input = 1 OR cs.use_qty = 1)'
                // 'AND (cs.price_list <> \'\' OR cs.formula_surface <> \'\' OR cs.use_input = 1)'
            )
        );

        $data->assign(
            'pricelist_steps_singleinput',
            ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->_id_configurator, '', '= 1')
        );
        $data->assign(
            'steps_counter',
            count(ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->_id_configurator))
        );
        $data->assign(
            'steps_all',
            ConfiguratorStepAbstract::getStepsByIdConfigurator(
                (int)$this->_id_configurator,
                ' AND cs.position < ' . (int)$configurator_step->position
            )
        );
        // Options
        $options = false;
        $options_conditions = array();
        if ($this->display === 'edit' && $configurator_step->type === ConfiguratorStepAbstract::TYPE_STEP_FEATURES) {
            $options = $this->object->getOptions($this->context->language->id, false);
            // Get option conditions
            foreach ($options as $option) {
                $options_conditions[$option->id] = $this->getJsonConditions(
                    ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
                    $option->id
                );
            }
            $data->assign('listFeatures_html', $this->getListFeatures($options));
        }
        $configurator = new ConfiguratorModel((int)$this->_id_configurator);
        $data->assign('configurator', $configurator);
        $data->assign('options', $options);
        $data->assign('options_conditions', $options_conditions);
        // Division
        $data->assign('find_division_step', $this->find_division_step);
        // Conditions
        $data->assign('conditions_choices', $this->conditions_choices);
        $data->assign('filters_choices', $this->filters_choices);

        // All Steps && Options before current step
        $steps_before = ConfiguratorStepAbstract::getStepsByIdConfigurator(
            (int)$this->_id_configurator,
            ' AND cs.position < ' . (int)$configurator_step->position
        );
        $data->assign('steps_before', $steps_before);
        $options_before = null;
        $matching_step_option = array();

        if ($steps_before) {
            foreach ($steps_before as $step_before) {
                $options_before[$step_before->id] = $step_before->getOptions($this->context->language->id, true);
                foreach (ConfiguratorStepOptionAbstract::getByIdConfiguratorStep($step_before->id) as $option_temp) {
                    $matching_step_option[$option_temp->id] = $option_temp->id_configurator_step;
                }
            }
        }
        $data->assign('options_before', $options_before);
        $data->assign('matching_step_option', $matching_step_option);
        // Rendering
        $this->tpl_form_vars['tabs']['Features']['form_content_html'] = $data->fetch();
    }

    public function saveOptionFormAjax()
    {
        $result = null;
        $step = ConfiguratorStepFactory::newObject((int)Tools::getValue('step_id'));
        if (Validate::isLoadedObject($step)) {
            $options = $step->getOptions($this->context->language->id, true);
            foreach ($options as $option) {
                $this->saveOption($step, $option);
            }
            $this->saveConditions();
            $result = json_encode(array(
                'message' => $this->l('Option saved!'),
                'POST' => $_POST
            ));
        }
        die($result);
    }

    public function displayOptionFormAjax()
    {
        $configurator_step_option = ConfiguratorStepOptionFactory::newObject((int)Tools::getValue('id_option_html'));
        $configurator_step_option->fillOption();

        $html = $this->getOptionHtml($configurator_step_option);
        die($html);
    }

    public function getOptionHtml($configurator_step_option)
    {
        $tpl = $this->context->smarty->createTemplate(
            _PS_MODULE_DIR_ . 'configurator/views/templates/admin/configurator_steps/option.tpl',
            $this->context->smarty
        );
        $configurator = new ConfiguratorModel((int)$this->_id_configurator);
        $id_template = $configurator_step_option->id;
        $options_conditions = array();
        $options_conditions[$id_template] = $this->getJsonConditions(
            ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
            $id_template
        );
        $configurator_step = ConfiguratorStepFactory::newObject((int)$configurator_step_option->id_configurator_step);

        // All Steps && Options before current step
        $steps_before = ConfiguratorStepAbstract::getStepsByIdConfigurator(
            (int)$this->_id_configurator,
            ' AND cs.position < ' . (int)$this->object->position
        );
        $options_before = null;
        $matching_step_option = array();
        if ($steps_before) {
            foreach ($steps_before as $step_before) {
                $options_before[$step_before->id] = $step_before->getOptions($this->context->language->id, true);
                foreach (ConfiguratorStepOptionAbstract::getByIdConfiguratorStep($step_before->id) as $option_temp) {
                    $matching_step_option[$option_temp->id] = $option_temp->id_configurator_step;
                }
            }
        }

        // CONFIGURATOR HOOK
        $HOOK_CONFIGURATOR_DISPLAY_ADMIN_OPTION_SETTINGS = Hook::exec('configuratorAdminDisplayOptionSettings', array(
            'configurator' => $configurator,
            'option' => $configurator_step_option,
            'configurator_step' => $this->object
        ));

        $tax_impact_types = TaxRulesGroupCore::getTaxRulesGroups();
        array_unshift($tax_impact_types, array(
            'id_tax_rules_group' => 0,
            'name' => $this->l('No tax impact')
        ));

        $tpl->assign(array(
            'option' => $configurator_step_option,
            'configurator' => $configurator,
            'attributesGroup' => ConfiguratorStepTypeAttributeModel::getGroupsAvailable($this->context->language->id),
            'id_lang' => $this->context->language->id,
            'currency' => $this->context->currency,
            'languages' => $this->getLanguages(),
            'configurator_step' => $this->object,
            'conditions_choices' => $this->conditions_choices,
            'filters_choices' => $this->filters_choices,
            'options_conditions' => $options_conditions,
            'impact_types' => self::$impact_types,
            'tax_impact_types' => $tax_impact_types,
            'price_calculation_types' => self::$price_calculation_types,
            'price_calculation_default' => self::$price_calculation_default,
            'find_division_step' => $this->find_division_step,
            'steps_before' => $steps_before,
            'options_before' => $options_before,
            'matching_step_option' => $matching_step_option,
            'HOOK_CONFIGURATOR_DISPLAY_ADMIN_OPTION_SETTINGS' => $HOOK_CONFIGURATOR_DISPLAY_ADMIN_OPTION_SETTINGS
        ));

        // @TODO: vérifier formula_surface
        $tpl->assign(
            'pricelist_steps_withsuffix',
            ConfiguratorStepAbstract::getStepsByIdConfigurator(
                (int)$this->_id_configurator,
                'AND (cs.price_list <> \'\' OR cs.use_input = 1 OR cs.use_qty = 1)'
                // 'AND (cs.price_list <> \'\' OR cs.formula_surface <> \'\' OR cs.use_input = 1)'
            )
        );
        $tpl->assign(
            'pricelist_steps_singleinput',
            ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->_id_configurator, '', '= 1')
        );
        $tpl->assign(
            'steps_counter',
            count(ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->_id_configurator))
        );

        $tpl->assign('configurator_step', $configurator_step);

        $tpl->assign('steps_all', ConfiguratorStepAbstract::getStepsByIdConfigurator(
            (int)$this->_id_configurator,
            ' AND cs.position < ' . (int)$configurator_step->position
        ));
        $steps_before = ConfiguratorStepAbstract::getStepsByIdConfigurator(
            (int)$this->_id_configurator,
            ' AND cs.position < ' . (int)$configurator_step->position
        );
        $tpl->assign('steps_before', $steps_before);
        $options_before = null;
        $matching_step_option = array();
        if ($steps_before) {
            foreach ($steps_before as $step_before) {
                $options_before[$step_before->id] = $step_before->getOptions($this->context->language->id, true);
                foreach (ConfiguratorStepOptionAbstract::getByIdConfiguratorStep($step_before->id) as $option_temp) {
                    $matching_step_option[$option_temp->id] = $option_temp->id_configurator_step;
                }
            }
        }
        $tpl->assign('options_before', $options_before);
        $tpl->assign('matching_step_option', $matching_step_option);

        return $tpl->fetch();
    }

    public function getJsonConditions($type, $foreignkey)
    {
        $jsonConditions = array();
        $conditionGroups = $this->object->getConditions($type, $foreignkey);
        foreach ($conditionGroups as $k => $conditionGroup) {
            foreach ($conditionGroup->conditions as $condition) {
                $jsonConditions[$k]['datas'][] = array(
                    "value" => $condition->value,
                    "min" => $condition->min,
                    "max" => $condition->max,
                    "formula" => $condition->formula,
                );
            }
            $jsonConditions[$k]['negative_condition'] = (bool)$conditionGroup->negative_condition;
        }

        return Tools::jsonEncode($jsonConditions);
    }

    public function getJsonFilters($type, $foreignkey)
    {
        $jsonFilters = array();
        $filterGroups = $this->object->getFilters($type, $foreignkey);
        foreach ($filterGroups as $k => $filterGroup) {
            foreach ($filterGroup->filters as $filter) {
                $jsonFilters[$k]['datas'][] = array(
                    "type" => $filter->type,
                    "option" => $filter->id_option,
                    "operator" => $filter->operator,
                    "target_step" => $filter->id_target_step,
                    "target_type" => $filter->target_type,
                    "target_option" => $filter->id_target_option,
                    "value" => $filter->type_value,
                    "formula" => $filter->formula
                );
            }
        }
        return Tools::jsonEncode($jsonFilters);
    }

    public function getListAttributes($options)
    {
        $options_array = array();
        foreach ($options as $configurator_step_option) {
            $options_array[] = array(
                'id_option' => $configurator_step_option->option['id_attribute'],
                'ipa' => $configurator_step_option->ipa,
                'id_step_option' => $configurator_step_option->id,
                'name' => $configurator_step_option->option['name'],
                'selected_by_default' => $configurator_step_option->selected_by_default,
                'min_value' => $configurator_step_option->min_value,
                'max_value' => $configurator_step_option->max_value,
                'default_value' => $configurator_step_option->default_value,
                'position' => $configurator_step_option->position,
                'weight' => ((float)$configurator_step_option->weight) . ' ' . Configuration::get('PS_WEIGHT_UNIT'),
            );
        }

        $options_fields_list = array(
            'id_option' => array(
                'title' => $this->l('Used'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'getOptionStatus'
            ),
            'id_step_option' => array(
                'title' => $this->l('#ID'),
                'align' => 'text-center',
                'class' => 'id-step-option'
            ),
            'name' => array(
                'title' => 'Option',
                'align' => 'text-center'
            ),
            'selected_by_default' => array(
                'title' => $this->l('Selected by default'),
                'class' => 'default-selected',
                'align' => 'text-center',
                'callback' => 'getIconSelectedDefault'
            ),
            'min_value' => array(
                'title' => $this->l('Minimum value'),
                'align' => 'text-center'
            ),
            'max_value' => array(
                'title' => $this->l('Maximum value'),
                'align' => 'text-center'
            ),
            'default_value' => array(
                'title' => $this->l('Default value'),
                'align' => 'text-center'
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'class' => 'fixed-width-xs',
                'position' => 'position',
                'align' => 'center'
            ),
            'weight' => array(
                'title' => $this->l('Weight'),
                'align' => 'text-center'
            ),
        );
        $helper = new HelperList();
        $helper->identifier = 'id_step_option';
        $helper->table = 'step_option';
        $helper->title = $this->l('Attributes list');
        //$helper->_defaultOrderBy = 'position';
        //$helper->explicitSelect = true;
        $helper->orderBy = 'position';
        $helper->orderWay = 'asc';
        $helper->position_identifier = self::$currentIndex;
        $helper->currentIndex = self::$currentIndex;
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->token = $this->token;
        $helper->simple_header = true;
        $helper->show_toolbar = false;
        $helper->colorOnBackground = true;
        $helper->override_folder = 'configurator_steps/';
        // Custom list links
        $helper->actions = array('editImpactPrice', 'selectedByDefault');

        return $helper->generateList($options_array, $options_fields_list);
    }

    public function getListFeatures($options)
    {
        $options_array = array();
        foreach ($options as $configurator_step_option) {
            $options_array[] = array(
                'id_option' => $configurator_step_option->option['id_feature_value'],
                'ipa' => $configurator_step_option->ipa,
                'id_step_option' => $configurator_step_option->id,
                'name' => $configurator_step_option->option['name'],
                'selected_by_default' => $configurator_step_option->selected_by_default,
                'min_value' => $configurator_step_option->min_value,
                'max_value' => $configurator_step_option->max_value,
                'default_value' => $configurator_step_option->default_value,
                'position' => $configurator_step_option->position,
                'weight' => ((float)$configurator_step_option->weight) . ' ' . Configuration::get('PS_WEIGHT_UNIT'),
            );
        }

        $options_fields_list = array(
            'id_option' => array(
                'title' => $this->l('Used'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'getOptionStatus'
            ),
            'id_step_option' => array(
                'title' => $this->l('#ID'),
                'align' => 'text-center',
                'class' => 'id-step-option'
            ),
            'name' => array(
                'title' => 'Option',
                'align' => 'text-center'
            ),
            'selected_by_default' => array(
                'title' => $this->l('Selected by default'),
                'class' => 'default-selected',
                'align' => 'text-center',
                'callback' => 'getIconSelectedDefault'
            ),
            'min_value' => array(
                'title' => $this->l('Minimum value'),
                'align' => 'text-center'
            ),
            'max_value' => array(
                'title' => $this->l('Maximum value'),
                'align' => 'text-center'
            ),
            'default_value' => array(
                'title' => $this->l('Default value'),
                'align' => 'text-center'
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'class' => 'fixed-width-xs',
                'position' => 'position',
                'align' => 'center'
            ),
            'weight' => array(
                'title' => $this->l('Weight'),
                'align' => 'text-center'
            ),
        );
        $helper = new HelperList();
        $helper->identifier = 'id_step_option';
        $helper->table = 'step_option';
        $helper->title = $this->l('Features list');
        //$helper->_defaultOrderBy = 'position';
        //$helper->explicitSelect = true;
        $helper->orderBy = 'position';
        $helper->orderWay = 'asc';
        $helper->position_identifier = self::$currentIndex;
        $helper->currentIndex = self::$currentIndex;
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->token = $this->token;
        $helper->simple_header = true;
        $helper->show_toolbar = false;
        $helper->colorOnBackground = true;
        $helper->override_folder = 'configurator_steps/';
        // Custom list links
        $helper->actions = array('editImpactPrice', 'selectedByDefault');

        return $helper->generateList($options_array, $options_fields_list);
    }

    public function getProductName($echo, $row)
    {
        if (DMTools::getVersionMajor() < 17) {
            $url = Context::getContext()->link->getAdminLink('AdminProducts') .
                '&updateproduct' .
                '&id_product=' . (int)$row['id_option'];
        } else {
            global $kernel;
            $sfRouter = $kernel->getContainer()->get('router');
            $url = $sfRouter->generate(
                'admin_product_form',
                array('id' => (int)$row['id_option'])
            ) . "#tab-hooks";
        }
        return '<a href="' . $url . '" target="_blank">' . $echo . '</a>';
    }

    public function getOptionStatus($echo, $row)
    {
        // Validator
        $echo = true;
        $tpl = $this->createTemplate('helpers/list/list_action_enable.tpl');
        $tpl->assign(array(
            'url_enable' => $this->context->link->getAdminLink('AdminConfiguratorSteps')
                . '&id_configurator=' . (int)$this->_id_configurator
                . '&id_configurator_step=' . (int)$this->object->id
                . '&id_option=' . (int)$row['id_option']
                . '&ipa=' . (int)$row['ipa'],
            'enabled' => $this->object->existOption((int)$row['id_option'], (int)$row['ipa'])
        ));
        return $tpl->fetch();
    }

    public function getIconSelectedDefault($echo, $row)
    {
        // Validator
        $echo = true;
        if ($row['selected_by_default']) {
            return '<i class="icon-asterisk"></i>';
        }
        return '';
    }

    /**
     * Custom list link
     */
    public function displayEditImpactPriceLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');
        if (!array_key_exists('EditImpactPrice', self::$cache_lang)) {
            self::$cache_lang['EditImpactPrice'] = $this->l('Edit option');
        }

        // Validator
        $name = self::$cache_lang['EditImpactPrice'];

        $tpl->assign(array(
            'href' => $id,
            'action' => $name,
            'id' => $id,
            'token' => $token,
            'disabled' => !(bool)$id
        ));

        return $tpl->fetch();
    }

    /**
     * Custom list link
     */
    public function displaySelectedByDefaultLink($token = null, $id = null, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_default.tpl');
        if (!array_key_exists('SelectedByDefault', self::$cache_lang)) {
            self::$cache_lang['SelectedByDefault'] = $this->l('Selected by default');
        }

        // Validator
        $name = self::$cache_lang['SelectedByDefault'];

        $tpl->assign(array(
            'href' => $this->context->link->getAdminLink('AdminConfiguratorSteps')
                . '&id_configurator=' . (int)$this->_id_configurator
                . '&id_configurator_step=' . (int)$this->object->id
                . '&id_step_option=' . (int)$id,
            'action' => $name,
            'token' => $token,
            'disabled' => !(bool)$id
        ));

        return $tpl->fetch();
    }


    // GETTERS

    protected function loadObject($opt = false)
    {
        $object = parent::loadObject($opt);
        if (Validate::isLoadedObject($object)) {
            $this->object = ConfiguratorStepFactory::newObject($object->id);
            return $this->object;
        }
        return $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getIdConfigurator()
    {
        return $this->_id_configurator;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function ajaxProcessUpdateOptionsPositions()
    {
        $id_configurator_step_option = (int)Tools::getValue('id_option');
        $positions = Tools::getValue('step_option');
        $updated_positions = true;
        $new_positions = array();

        foreach ($positions as $v) {
            if (count(explode('_', $v)) == 4) {
                $new_positions[] = $v;
            }
        }

        foreach ($new_positions as $position => $value) {
            $pos = explode('_', $value);
            $configuratorStepOption = ConfiguratorStepOptionFactory::newObject((int)$pos[2]);
            if (Validate::isLoadedObject($configuratorStepOption)) {
                $updated_positions = $updated_positions && $configuratorStepOption->updatePosition($position);
            }
        }
        if ($configuratorStepOption) {
            if (isset($position) && $updated_positions) {
                echo 'ok position ' . (int)$position . ' for configurator step ' . (int)$pos[2] . '\r\n';
            } else {
                echo '{"hasError" : true, "errors" : "Can not update the ' .
                    (int)$id_configurator_step_option . ' configurator step to position ' . (int)$position . ' "}';
            }
        } else {
            echo '{"hasError" : true, "errors" : "The (' .
                (int)$id_configurator_step_option . ') configurator step cannot be loaded."}';
        }
    }

    private function downloadPriceList()
    {
        $array_to_csv = array();
        $filename = 'configurator_pricelist.csv';

        if (Tools::getIsset('example')) {
            if ((int)Tools::getValue('example') === 1) {
                $filename = 'configurator_pricelist_example_1.csv';
                $array_to_csv = array(
                    array('', 100, 200, 300, 400, 500),
                    array('', 100, 190, 270, 340, 400)
                );
            } else {
                $filename = 'configurator_pricelist_example_2.csv';
                $array_to_csv = array(
                    array('', 100, 200, 300, 400, 500),
                    array(5, 100, 190, 270, 340, 400),
                    array(10, 110, 200, 280, 350, 410),
                    array(15, 130, 220, 300, 370, 430),
                    array(20, 150, 240, 320, 390, 450),
                );
            }
        } else {
            $configurator_step = ConfiguratorStepFactory::newObject(Tools::getValue('id_configurator_step'));

            if (!empty($configurator_step->price_list)) {
                $pricelist = (array)json_decode($configurator_step->price_list);
                foreach ($pricelist as $key => $pricelist_line) {
                    if (empty($array_to_csv)) {
                        $array_to_csv[0][0] = '';
                        foreach ($pricelist_line as $k => $value) {
                            $array_to_csv[0][] = $k;
                        }
                    }
                    $array_to_csv[] = array_merge(array($key), (array)$pricelist_line);
                }
            }
            $filename = 'configurator_pricelist_' . $configurator_step->id . '.csv';
            if (!empty($configurator_step->price_list_name)) {
                $filename = $configurator_step->price_list_name;
            }
        }

        $f = fopen('php://memory', 'w');
        foreach ($array_to_csv as $line) {
            fputcsv($f, (array)$line, ';');
        }
        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);
    }
}
