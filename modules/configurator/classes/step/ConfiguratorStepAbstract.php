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
if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!class_exists('ConfiguratorStepAbstract')) {
    require_once(dirname(__FILE__) . '/../PricelistHelper.php');
    require_once(dirname(__FILE__) . '/../option/ConfiguratorStepOptionAbstract.php');
    require_once(dirname(__FILE__) . '/../ConfiguratorStepGroupModel.php');
    require_once(dirname(__FILE__) . '/../ConfiguratorStepTabModel.php');
    require_once(dirname(__FILE__) . '/../ConfiguratorStepDisplayConditionGroupModel.php');
    require_once(dirname(__FILE__) . '/../filter/ConfiguratorStepFilterGroupModel.php');
    require_once(dirname(__FILE__) . '/../helper/DMHelperUploader.php');
    require_once(dirname(__FILE__) . '/../../DmCache.php');

    // Module Advanced Formula
    if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
        require_once(dirname(__FILE__) . '/../../../dm_advancedformula/classes/helper/AdvancedformulaHelper.php');
    }
    // /Module Advanced Formula

    /**
     * Class ConfiguratorStepAbstract
     */
    abstract class ConfiguratorStepAbstract extends ObjectModel
    {
        const TYPE_STEP_ATTRIBUTES = 'attributes';
        const TYPE_STEP_FEATURES = 'features';
        const TYPE_STEP_PRODUCTS = 'products';
        const TYPE_STEP_UPLOAD = 'upload';
        const TYPE_STEP_DESIGNER = 'designer';

        /**
         *  Type of display conditions
         */
        const TYPE_CONDITION_OPTION = 'option';
        const TYPE_CONDITION_STEP = 'step';

        /**
         *  Pricelist types for a step
         */
        const PRICE_LIST_TYPE_AMOUNT = 0;
        const PRICE_LIST_TYPE_PERCENT = 1;
        const PRICE_LIST_DISPLAY_INPUT = 'input';
        const PRICE_LIST_DISPLAY_SELECT = 'select';
        const PRICE_LIST_DISPLAY_TABLE = 'table';

        protected static $mymetypes = array(
            '.txt' => 'text/plain',
            '.doc' => 'application/msword',
            '.docx' => 'application/msword',
            '.pdf' => 'application/pdf',
            '.zip' => 'multipart/x-zip',
            '.png' => 'image/png',
            '.jpeg' => 'image/jpeg',
            '.gif' => 'image/gif',
            '.jpg' => 'image/jpeg',
            '.ai' => 'application/postscript',
            '.eps' => 'application/postscript',
            '.psd' => 'application/postscript'
        );

        private $old_id_option_group;
        public $id_configurator;
        public $id_option_group;
        public $type = self::TYPE_STEP_ATTRIBUTES;
        public $required = 0;
        public $multiple = 0;
        public $max_options = 0;
        public $min_options = 0;
        public $displayed_by_yes = 0;
        public $displayed_in_preview = 0;
        public $displayed_in_order = 1;
        public $use_input = 0;
        public $use_qty = 0;
        public $min_qty = 0;
        public $max_qty = 0;
        public $max_qty_step_option_id = 0;
        public $max_qty_coef = 0;
        public $step_qty = 0;
        public $display_total = 0;
        public $unique_price = 0;
        public $unique_price_value = 0.0;
        public $nb_files = 1;
        public $max_weight_total = 0;
        public $extensions;
        public $position;
        public $name;
        public $public_name;
        public $invoice_name;
        public $input_suffix;
        public $content;
        public $header_names;

        public $use_pricelist = 0;
        public $price_list;
        public $price_list_name;
        public $price_list_type;
        public $price_list_display;
        public $price_list_coeff = 0;
        public $determine_base_price = 0;

        public $use_division = 0;
        public $use_custom_template = 0;
        public $custom_template;
        public $css = '';
        public $class = '';

        public $id_configurator_step_tab = 0;

        public $upload_display_progress = 0;
        public $use_upload_camera = 0;
        public $show_upload_image = 1;
        public $use_shared = 0;
        public $dropzone;

        public $formula;
        public $formula_surface;
        public $weight;
        public $dimension_width;
        public $dimension_height;
        public $dimension_depth;

        public $default_value_select;
        public $info_text;

        public $id_tax_rules_group = 0;
        public $ignored_if_empty = 1;

        public $use_combination_as_default_value = 0;
        public $use_url_as_default_value = 0;

        /**
         *
         * @var ConfiguratorModel 
         */
        protected $configurator= null;

        /**
         * Collections
         */
        public $options;
        public $option_group;

        public static $definition = array(
            'table' => 'configurator_step',
            'primary' => 'id_configurator_step',
            'multilang' => true,
            'fields' => array(
                /* Classic fields */
                'id_configurator' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_option_group' => array(
                    'type' => self::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true
                ),
                'type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
                'required' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'multiple' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'max_options' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'min_options' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'displayed_by_yes' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'displayed_in_preview' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'displayed_in_order' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'use_input' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'use_qty' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'min_qty' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'max_qty' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'max_qty_step_option_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'max_qty_coef' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'step_qty' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'display_total' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'unique_price' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'unique_price_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
                'nb_files' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'max_weight_total' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'extensions' => array('type' => self::TYPE_STRING),
                'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
                'price_list' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'price_list_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
                'price_list_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'price_list_display' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
                'price_list_coeff' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
                'determine_base_price' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
                'use_division' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'use_custom_template' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'custom_template' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'css' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'class' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'id_configurator_step_tab' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'use_upload_camera' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'upload_display_progress' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'show_upload_image' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'use_shared' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'dropzone' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'formula' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'formula_surface' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'weight' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'dimension_width' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'dimension_height' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'dimension_depth' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'ignored_if_empty' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'use_combination_as_default_value' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'use_url_as_default_value' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                /* Lang fields */
                'info_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
                'default_value_select' => array('type' => self::TYPE_STRING,'lang' => true, 'validate' => 'isString'),
                
                'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
                'public_name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
                'invoice_name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
                'input_suffix' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
                'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
                'header_names' => array('type' => self::TYPE_STRING, 'lang' => true)
            )
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
            $this->old_id_option_group = (int)$this->id_option_group;
        }

        
        public function getMaxQty($cart_detail_model)
        {
            if (Module::isInstalled('dm_advancedformula')
                && Module::isEnabled('dm_advancedformula')
                && $this->max_qty
            ) {
                return (int) AdvancedformulaHelper::loadFormula(
                    $cart_detail_model,
                    $this,
                    $cart_detail_model->getDetail(),
                    $this->max_qty
                );
            } else {
                return (int) $this->max_qty;
            }
        }

        public function getMinQty($cart_detail_model)
        {
            if (Module::isInstalled('dm_advancedformula')
                && Module::isEnabled('dm_advancedformula')
                && $this->min_qty
            ) {
                return (int) AdvancedformulaHelper::loadFormula(
                    $cart_detail_model,
                    $this,
                    $cart_detail_model->getDetail(),
                    $this->min_qty
                );
            } else {
                return (int) $this->min_qty;
            }
        }

        public function getWeight($cart_detail_model)
        {
            if (Module::isInstalled('dm_advancedformula')
                && Module::isEnabled('dm_advancedformula')
                && $this->weight
            ) {
                return (float) AdvancedformulaHelper::loadFormula(
                    $cart_detail_model,
                    $this,
                    $cart_detail_model->getDetail(),
                    $this->weight
                );
            } else {
                return (float) $this->weight;
            }
        }

        /**
         * @param $cart_detail_model
         * @param $reference
         * @return string
         */
        public function getReference($reference, $cart_detail_model)
        {
            if (Module::isInstalled('dm_advancedformula')
                && Module::isEnabled('dm_advancedformula')
                && !empty($reference)
            ) {
                return (float) AdvancedformulaHelper::loadFormula(
                    $cart_detail_model,
                    $this,
                    $cart_detail_model->getDetail(),
                    $reference
                );
            } else {
                return $reference;
            }
        }

        public function getDimension($type, $cart_detail_model)
        {
            $property = 'dimension_' . $type;
            if (Module::isInstalled('dm_advancedformula')
                && Module::isEnabled('dm_advancedformula')
                && $this->$property
            ) {
                return (int)AdvancedformulaHelper::loadFormula(
                    $cart_detail_model,
                    $this,
                    $cart_detail_model->getDetail(),
                    $this->$property
                );
            } else {
                return (int)$this->$property;
            }
        }

        public static function getGroupsAvailable($lang_id)
        {
            return array();
        }

        abstract public function getOptions($lang_id, $only_used = true);

        abstract public function getOptionsFromOptionGroup($id_lang);

        private function getForeignKeyCondition($type, $foreignkey)
        {
            if ($type === self::TYPE_CONDITION_STEP) {
                $obj = ConfiguratorStepFactory::newObject((int)$foreignkey);
            } else {
                $obj = ConfiguratorStepOptionFactory::newObject((int)$foreignkey);
            }
            $foreignkey = -1;
            if (Validate::isLoadedObject($obj)) {
                $foreignkey = (int)$obj->id;
            }

            return (int)$foreignkey;
        }

        private function checkExtensions()
        {
            if (is_array($this->extensions)) {
                $this->extensions = implode(',', $this->extensions);
            } elseif (!Validate::isCleanHtml($this->extensions)) {
                $this->extensions = '';
            }
        }

        /**
         * When adding a step, saving options
         */
        public function add($autodate = true, $null_values = false)
        {
            $this->uploadPriceList();
            $this->checkExtensions();

            if ($this->position <= 0) {
                $this->position = self::getHigherPosition($this->id_configurator) + 1;
            }

            $languages = Language::getLanguages();
            foreach ($languages as $language) {
                if (empty($this->name[$language['id_lang']])) {
                    $option_group = new AttributeGroup(
                        (int)Tools::getValue('id_option_group'),
                        (int)$language['id_lang']
                    );
                    $this->name[$language['id_lang']] = $option_group->public_name;
                }
                if (empty($this->public_name[$language['id_lang']])) {
                    $option_group = new AttributeGroup(
                        (int)Tools::getValue('id_option_group'),
                        (int)$language['id_lang']
                    );
                    $this->public_name[$language['id_lang']] = $option_group->public_name;
                }
            }
            $result = parent::add($autodate, $null_values);
            if ($result) {
                $this->addOptions();
            }

            return $result;
        }

        public function update($null_values = false)
        {
            $old_step = ConfiguratorStepFactory::newObject($this->id);
            $this->uploadPriceList();
            $this->checkExtensions();

            /**
             * @todo finish condition
             */
            if ($this->unique_price) {
                $this->use_pricelist = 0;
                $this->use_input = 0;
            } else {
                $this->unique_price_value = 0.0;
            }

            if (!$this->multiple || $this->max_options == 1) {
                $this->max_options = 0;
                $this->multiple = 0;
            }
            
            if (!$this->multiple || $this->min_options == 1) {
                $this->min_options = 0;
                $this->multiple = 0;
            }

            if (!$this->use_qty || $this->max_qty == 1) {
                $this->max_qty = 0;
                $this->use_qty = 0;
                $this->max_qty_step_option_id = 0;
                $this->max_qty_coef = 0;
            }
            
            /*if (!$this->use_qty || $this->min_qty == 1) {
                $this->min_qty = 0;
                $this->use_qty = 0;
            }*/

            if ($this->max_qty_step_option_id > 0) {
                $this->max_qty = 0;
            }

            if (!$this->use_pricelist) {
                $this->price_list = '';
            }

            if ($this->display_total || $this->multiple || $this->use_qty) {
                $this->price_list = '';
                $this->use_input = 0;
            }

            if (!empty($this->price_list)) {
                $this->display_total = 0;
            }

            if (!$this->use_custom_template) {
                $this->custom_template = '';
            }

            Hook::exec('actionObjectConfiguratorStepModelBeforeAfter', array(
                'configurator_step' => &$this
            ));

            // ---
            $old_id_option_group = (int)$this->old_id_option_group;
            $result = parent::update($null_values);

            // When updating a step, if group's options or step type changed, remove olds and add news options
            if ($result && ($old_id_option_group !== (int)$this->id_option_group || $old_step->type !== $this->type)) {
                $this->deleteOptions();
                $this->addOptions();
            }

            return $result;
        }

        public function delete()
        {
            $this->deleteOptions();
            $this->deleteConditions(self::TYPE_CONDITION_STEP, (int)$this->id);
            ConfiguratorStepGroupModel::deleteByStep((int)$this->id);
            return parent::delete();
        }

        public function duplicate($id_configurator)
        {
            // Duplication de l'étape
            $new_step = $this->duplicateObject();
            if (!Validate::isLoadedObject($new_step)) {
                return false;
            }

            $new_step->id_configurator = (int)$id_configurator;
            // Evite de vider le pricelist lors de la copie
            $new_step->use_pricelist = true;
            if (!$new_step->save()) {
                return false;
            }
            configurator::cleanCache();

            // Duplication options
            $options = $this->getOptions((int)Context::getContext()->language->id, true);
            foreach ($options as $configurator_step_option) {
                if (!$configurator_step_option->duplicate((int)$id_configurator, (int)$new_step->id)) {
                    return false;
                }
            }

            // Duplication conditions d'affichage
            $conditions_group = $this->getConditions(ConfiguratorStepAbstract::TYPE_CONDITION_STEP, (int)$this->id);

            // Lister tous les groupes de conditions lié à l'étape courante
            foreach ($conditions_group as $condition_group) {
                if (!$id_condition_group = $condition_group->duplicate(
                    ConfiguratorStepAbstract::TYPE_CONDITION_STEP,
                    (int)$new_step->id
                )) {
                    // Impossible de dupliquer le groupe de condition
                    return false;
                }

                /* @var $condition ConfiguratorStepDisplayConditionModel */
                foreach ($condition_group->conditions as $condition) {
                    $old_step = self::getByIdOption((int)$condition->value);
                    if (!Validate::isLoadedObject($old_step)) {
                        return false;
                    }

                    $position_option_in_db = ConfiguratorStepOptionAbstract::getPositionIdentifier(
                        (int)$condition->value
                    );
                    $new_step_related_to_old_step = self::getByIdentifierPosition(
                        (int)$id_configurator,
                        (int)$old_step->position
                    );
                    if (!Validate::isLoadedObject($new_step_related_to_old_step)) {
                        return false;
                    }

                    $new_condition_value = ConfiguratorStepOptionAbstract::getIdByIdentifierPosition(
                        (int)$new_step_related_to_old_step->id,
                        (int)$position_option_in_db
                    );
                    if (!$condition->duplicate((int)$id_condition_group, $new_condition_value)) {
                        return false;
                    }
                }
            }

            // Duplication des groupes
            ConfiguratorStepGroupModel::duplicate((int)$this->id, (int)$new_step->id);

            return true;
        }

        public function isType($type)
        {
            return $this->type === $type;
        }

        public function getUploader($url = null, array $attachments = array())
        {
            $files = array();
            foreach ($attachments as $a) {
                $files[] = array(
                    'link' => Context::getContext()->link->getModuleLink(
                        'configurator',
                        'attachment',
                        array('token' => $a['token'])
                    ),
                    'delete_url' => Context::getContext()->link->getModuleLink(
                        'configurator',
                        'attachment',
                        array('token' => $a['token'], 'action' => 'delete')
                    ),
                    'name' => $a['file_name'],
                );
            }

            $upload_helper = new DMHelperUploader('step_' . (int)$this->id);
            $upload_helper->setMultiple(true)
                ->setUseAjax(true)
                ->setMaxFiles($this->nb_files)
                ->setFiles($files);

            $exts = $this->getUsedExtensionsListNameOnly();
            if (!empty($exts)) {
                $upload_helper->setAcceptTypes($exts);
            }

            if (!is_null($url) && Validate::isUrl($url)) {
                $upload_helper->setUrl($url);
            }

            $upload_helper->setUseUploadCamera($this->use_upload_camera);
            $upload_helper->setShowUploadImage($this->show_upload_image);
            $upload_helper->setDisplayProgress($this->upload_display_progress);

            return $upload_helper;
        }

        public function getUploaderTemplate($url = null, array $attachments = array())
        {
            if (!$this->isType(self::TYPE_STEP_UPLOAD)) {
                return '';
            }

            $uploader = $this->getUploader($url, $attachments);

            return $uploader->render();
        }

        public function updatePosition($position)
        {
            if (!$id_configurator = (int)Tools::getValue('id_configurator')) {
                $id_configurator = (int)$this->id_configurator;
            }

            $sql = 'SELECT ag.`position`, ag.`id_configurator_step`, ag.`id_configurator`
					FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ag
					WHERE ag.`id_configurator` = ' . (int)$id_configurator . '
					ORDER BY ag.`position` ASC';
            if (!$res = Db::getInstance()->executeS($sql)) {
                return false;
            }

            foreach ($res as $configurator_step) {
                if ((int)$configurator_step['id_configurator_step'] === (int)$this->id) {
                    $moved_configurator_step = $configurator_step;
                }
            }

            if (!isset($moved_configurator_step) || !isset($position)) {
                return false;
            }

            $sql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`'
                . 'SET `position`=' . $position
                . ' WHERE `id_configurator` = ' . (int)$id_configurator
                . ' AND `id_configurator_step`=' . (int)$moved_configurator_step['id_configurator_step'];
            return Db::getInstance()->execute($sql);
        }

        public function updateOptionsPositions()
        {
            $options = $this->getOptions(Context::getContext()->language->id, true);

            foreach ($options as $p => $option) {
                $option->position = $p;
                $option->save();
            }

            return true;
        }

        /**
         * Auto adding all options of a group in database
         */
        public function addOptions()
        {
            $options = $this->getOptions(Context::getContext()->language->id, false);
            foreach ($options as $i => $option) {
                $configurator_step_option = new ConfiguratorStepOptionAbstract();
                $configurator_step_option->position = $i;
                $configurator_step_option->id_configurator_step = (int)$this->id;
                $configurator_step_option->id_option = (int)$option->id_option;
                $configurator_step_option->ipa = (int)$option->ipa;
                $configurator_step_option->save();
            }
        }

        /**
         * Auto deleting all options in database
         */
        public function deleteOptions()
        {
            $options = $this->getOptionsToDelete(Context::getContext()->language->id);
            foreach ($options as $option) {
                $option->delete();
            }
        }

        private function getOptionsToDelete($lang_id)
        {
            // Get active options step
            $query = new DbQuery();
            $query->select('*')
                ->from('configurator_step_option', 'cso')
                ->innerJoin(
                    'configurator_step_option_lang',
                    'csol',
                    'cso.id_configurator_step_option = csol.id_configurator_step_option'
                    . ' AND csol.id_lang = ' . (int)$lang_id
                )
                ->where('id_configurator_step = ' . (int)$this->id)
                ->orderBy('position ASC');
            $results = Db::getInstance()->executeS($query, true, false);
            return ConfiguratorStepOptionFactory::hydrateCollection($results);
        }

        private function getOptionGroup($id_option_group)
        {
            $key = 'ConfiguratorStepAbstract::getOptionGroup-' . $id_option_group;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $option_group = new AttributeGroup((int)$id_option_group);
                DmCache::getInstance()->store($key, $option_group);
                return $option_group;
            }
        }

        public function getOptionFromOptionId($id_lang, $id_option, $ipa = 0)
        {
            $key = 'ConfiguratorStepAbstract::getOptionFromOptionId-' . (int)$id_lang;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $option = Db::getInstance()->getRow('
                    SELECT *
                    FROM `' . _DB_PREFIX_ . 'attribute` a
                    ' . Shop::addSqlAssociation('attribute', 'a') . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                    ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)$id_lang . ')
                    WHERE a.`id_attribute` = ' . (int)$id_option . '
                    ORDER BY `position` ASC
                ');
                if (!isset($option['color'])) {
                    $option['color'] = '';
                }
                $configuratorStepOptionModel = ConfiguratorStepOptionAbstract::getByIdOptionAndIdConfiguratorStep(
                    (int)$this->id,
                    (int)$option['id_attribute'],
                    $ipa
                );
                if ($configuratorStepOptionModel !== false) {
                    $option['configuratorStepOption'] = $configuratorStepOptionModel;
                } elseif ($configuratorStepOptionModel === false) {
                    $option['configuratorStepOption'] = new ConfiguratorStepOptionModel();
                }
                DmCache::getInstance()->store($key, $option);
                return $option;
            }
        }

        /**
         * Function which add or remove option from database
         * @param int $id_option
         * @return boolean Enable status, true => added in databse, false => removed from database
         */
        public function updateOptionUsed($id_option, $ipa = 0)
        {
            // CONFIGURATOR HOOK
            $result = false;
            Hook::exec('configuratorActionStepUpdateOptionUsed', array(
                'configuratorStepModel' => $this,
                'id_option' => $id_option,
                'ipa' => $ipa,
                'result' => &$result
            ));

            if ($result !== false) {
                if ($result === 0) {
                    return 0;
                } else {
                    $configurator_step_option = $result;
                }
            } else {
                $configurator_step_option = ConfiguratorStepOptionAbstract::getByIdOptionAndIdConfiguratorStep(
                    (int)$this->id,
                    (int)$id_option,
                    $ipa
                );
                if (Validate::isLoadedObject($configurator_step_option)) {
                    $configurator_step_option->delete();
                    return 0;
                } else {
                    $position = (int)ConfiguratorStepOptionAbstract::getHigherPosition($this->id) + 1;
                    $configurator_step_option = new ConfiguratorStepOptionAbstract();
                    $configurator_step_option->id_configurator_step = (int)$this->id;
                    $configurator_step_option->id_option = (int)$id_option;
                    $configurator_step_option->ipa = (int)$ipa;
                    $configurator_step_option->position = $position;
                    $configurator_step_option->save();
                }
            }

            return (int)$configurator_step_option->id;
        }

        /**
         * Remove selected default of options
         */
        public function removeSelectedDefault($id_configurator_step_option = 0)
        {
            ConfiguratorStepOptionAbstract::removeSelectedDefault((int)$this->id, $id_configurator_step_option);
        }

        /**
         * Remove selected default of options and add selected default to the new one
         * @param int $id_option
         * @deprecated
         */
        public function updateSelectedDefault($id_option, $ipa = 0)
        {
            $this->removeSelectedDefault();
            $configurator_step_option = ConfiguratorStepOptionAbstract::getByIdOptionAndIdConfiguratorStep(
                (int)$this->id,
                (int)$id_option,
                $ipa
            );
            if (Validate::isLoadedObject($configurator_step_option)) {
                $configurator_step_option->selected_by_default = 1;
                $configurator_step_option->update();
            }
        }

        public function updateSelectedDefaultByIdStepOption($id_step_option)
        {
            if (!$this->multiple) {
                $this->removeSelectedDefault();
            }
            $configurator_step_option = ConfiguratorStepOptionFactory::newObject((int)$id_step_option);
            if (Validate::isLoadedObject($configurator_step_option)) {
                $configurator_step_option->selected_by_default = 1;
                $configurator_step_option->update();
            }
        }

        public function cleanSelectedDefault()
        {
            if (!$this->multiple) {
                ConfiguratorStepOptionAbstract::cleanSelectedDefault($this->id);
            }
        }

        public function existOption($id_option, $ipa = 0)
        {
            $configurator_step_option = ConfiguratorStepOptionAbstract::getByIdOptionAndIdConfiguratorStep(
                (int)$this->id,
                (int)$id_option,
                (int)$ipa
            );
            return Validate::isLoadedObject($configurator_step_option);
        }

        /**
         * Get steps before $this
         */
        public function getPreviousSteps()
        {
            $objects = self::getStepsByIdConfigurator(
                (int)$this->id_configurator,
                'AND cs.position < ' . $this->position
            );
            return $objects;
        }

        /**
         * Conditions
         */
        public function getConditions($type, $foreignkey)
        {
            $foreignkey = $this->getForeignKeyCondition($type, $foreignkey);
            return ConfiguratorStepDisplayConditionGroupModel::getConditions($type, $foreignkey);
        }

        public function deleteConditions($type, $foreignkey)
        {
            $foreignkey = $this->getForeignKeyCondition($type, $foreignkey);
            ConfiguratorStepDisplayConditionGroupModel::deleteConditions($type, $foreignkey);
        }

        public function saveConditions($type, $foreignkey, $condition_groups)
        {
            $foreignkey = $this->getForeignKeyCondition($type, $foreignkey);
            ConfiguratorStepDisplayConditionGroupModel::saveConditions($type, $foreignkey, $condition_groups);
        }

        /**
         * Filters
         */
        public function getFilters($type, $foreignkey)
        {
            $foreignkey = $this->getForeignKeyCondition($type, $foreignkey);
            return ConfiguratorStepFilterGroupModel::getFilters($type, $foreignkey);
        }

        public function deleteFilters($type, $foreignkey)
        {
            $foreignkey = $this->getForeignKeyCondition($type, $foreignkey);
            ConfiguratorStepFilterGroupModel::deleteFilters($type, $foreignkey);
        }

        public function saveFilters($type, $foreignkey, $condition_groups)
        {
            $foreignkey = $this->getForeignKeyCondition($type, $foreignkey);
            ConfiguratorStepFilterGroupModel::saveFilters($type, $foreignkey, $condition_groups);
        }

        public function isValidMaxOptions($value)
        {
            return $value <= $this->getNumberOfOptions();
        }

        public function getNumberOfOptions()
        {
            $options = array();

            // CONFIGURATOR HOOK
            $options = false;
            Hook::exec('configuratorActionStepGetNumberOfOptions', array(
                'configuratorStepModel' => $this,
                'options' => $options
            ));

            if ($options === false
                && ($this->type === self::TYPE_STEP_ATTRIBUTES || $this->type === self::TYPE_STEP_PRODUCTS)
            ) {
                $options = $this->getOptions((int)Context::getContext()->language->id);
            }
            
            return count($options);
        }

        public function uploadPriceList()
        {
            $input = 'pricelist_file';
            if (isset($_FILES[$input])) {
                $pricelist_helper = new PricelistHelper();
                if ($load = $pricelist_helper->load($_FILES[$input])) {
                    $this->price_list = Tools::jsonEncode($pricelist_helper->getPricelist());
                    $this->price_list_name = $_FILES[$input]['name'];
                }
                return $load;
            }
            return false;
        }

        public function isValidPricelistValue($value, $dimension = PricelistHelper::ONE_DIMENSION)
        {
            // In case where we use input text without pricelist
            if (empty($this->price_list)) {
                return true;
            }

            $min_max = $this->getMinMaxPriceList($dimension);
            return (float)$value >= (float)$min_max['min'] && (float)$value <= (float)$min_max['max'];
        }

        public function getMinMaxPriceList($dimension = PricelistHelper::ONE_DIMENSION)
        {
            $pricelist_helper = new PricelistHelper();
            $pricelist_helper->setPricelist(Tools::jsonDecode($this->price_list, true));
            return $pricelist_helper->getMinMax($dimension);
        }

        public function getDisplayExtensions()
        {
            return str_replace(',', ', ', $this->extensions);
        }

        public function getExtensionsList()
        {
            return array_keys(self::$mymetypes);
        }

        public function getUsedExtensions()
        {
            return explode(',', $this->extensions);
        }

        public function getUsedExtensionsListNameOnly()
        {
            if (empty($this->extensions)) {
                return array();
            }
            return explode(',', str_replace('.', '', $this->extensions));
        }

        public function getUsedMymeTypes()
        {
            $exts = $this->getUsedExtensions();
            $mymetypes = array();
            foreach ($exts as $ext) {
                if (isset(self::$mymetypes[$ext])) {
                    $mymetypes[] = self::$mymetypes[$ext];
                }
            }
            return $mymetypes;
        }

        public static function getStepsByIdConfigurator($id_configurator, $where_clause = '', $filter_nb_options = '')
        {
            $key = 'ConfiguratorStepAbstract::getStepsByIdConfigurator-' . Context::getContext()->language->id
                . '-' . $id_configurator . '-' . $where_clause . '-' . $filter_nb_options;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT cs.*, csl.* FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` cs '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` csl
                            ON (cs.`' . self::$definition['primary'] . '` = csl.`' . self::$definition['primary'] . '`
                            AND csl.`id_lang` = ' . (int)Context::getContext()->language->id . ')'
                    . ' LEFT JOIN `' . _DB_PREFIX_ . 'configurator_step_option` cso
                            ON (cs.`' . self::$definition['primary'] . '` = cso.`' . self::$definition['primary'] . '`)'
                    . ' WHERE cs.id_configurator=' . (int)$id_configurator
                    . ' ' . $where_clause
                    . ' GROUP BY cs.id_configurator_step'
                    . (empty($filter_nb_options) ? '' : ' HAVING COUNT(cso.id_configurator_step) ' . $filter_nb_options)
                    . ' ORDER BY position ASC';
                $steps = Db::getInstance()->executeS($sql);

                foreach ($steps as $key => $step) {
                    if (is_array($step)) {
                        $steps[$key] = ConfiguratorStepFactory::hydrate($step);
                    }
                }

                DmCache::getInstance()->store($key, $steps);

                return $steps;
            }
        }

        public static function getRequiredSteps($id_configurator)
        {
            return self::getStepsByIdConfigurator((int)$id_configurator, 'AND cs.required = 1');
        }

        public static function getStepsByIdOptionGroup($id_option_group, $where_clause = '')
        {
            $key = 'ConfiguratorStepAbstract::getStepsByIdOptionGroup-' . $id_option_group . '-' . $where_clause;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` cs '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` csl'
                    . ' ON (cs.`' . self::$definition['primary'] . '` = csl.`' . self::$definition['primary'] . '`'
                    . ' AND csl.`id_lang` = ' . (int)Context::getContext()->language->id . ')'
                    . ' WHERE cs.id_option_group=' . (int)$id_option_group
                    . ' ' . ($where_clause ? 'AND ' : '') . $where_clause
                    . ' ORDER BY position ASC';
                $results = Db::getInstance()->executeS($sql);

                $return = ConfiguratorStepFactory::hydrateCollection(
                    $results,
                    (int)Context::getContext()->language->id
                );
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public static function deleteStepsByIdConfigurator($id_configurator)
        {
            /* @var $objects ConfiguratorStepAbstract */
            $objects = self::getStepsByIdConfigurator($id_configurator);
            foreach ($objects as $object) {
                $object->delete();
            }
        }

        public static function deleteStepsByIdOptionGroup($id_option_group, $where_clause = '')
        {
            $objects = self::getStepsByIdOptionGroup($id_option_group, $where_clause);
            foreach ($objects as $object) {
                $object->delete();
            }
        }

        public static function getHigherPosition($id_configurator)
        {
            $sql = 'SELECT MAX(`position`)
					FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
					WHERE id_configurator = ' . (int)$id_configurator;

            $position = DB::getInstance()->getValue($sql);

            return (is_numeric($position)) ? $position : (-1);
        }

        public static function getByIdentifierPosition($id_configurator, $position)
        {
            $steps = self::getStepsByIdConfigurator((int)$id_configurator);
            foreach ($steps as $step) {
                if ((int)$step->position === (int)$position) {
                    return $step;
                }
            }
            return new ConfiguratorStepTypeAttributeModel();
        }

        /**
         *
         * @param type $id_option
         * @todo: Optimlization needed
         */
        public static function getIdStepByIdOption($id_option)
        {
            $key = 'ConfiguratorStepAbstract::getIdStepByIdOption';
            if (DmCache::getInstance()->isStored($key)) {
                $return = DmCache::getInstance()->retrieve($key);
            } else {
                $query = new DbQuery();
                $query->select('cs.id_configurator_step, cso.id_configurator_step_option')
                    ->from('configurator_step', 'cs')
                    ->leftJoin(
                        'configurator_step_option',
                        'cso',
                        'cs.id_configurator_step = cso.id_configurator_step'
                    )
                    //->where('cso.id_configurator_step_option = '.(int)$id_option)
                ;
                $results = Db::getInstance()->executeS($query);

                $return = array();
                foreach ($results as $result) {
                    $return[$result['id_configurator_step_option']] = $result['id_configurator_step'];
                }
                DmCache::getInstance()->store($key, $return);
            }

            $key = 'ConfiguratorStepAbstract::getIdStepByIdOption-' . $id_option;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                if (isset($return[$id_option])) {
                    $return = (int)$return[$id_option];
                    DmCache::getInstance()->store($key, $return);
                    return $return;
                }

                return false;
            }
        }

        public static function getByIdOption($id_option)
        {
            $key = 'ConfiguratorStepAbstract::getByIdOption';
            if (DmCache::getInstance()->isStored($key)) {
                $return = DmCache::getInstance()->retrieve($key);
            } else {
                $query = new DbQuery();
                $query->select('cs.id_configurator_step, cso.id_configurator_step_option')
                    ->from('configurator_step', 'cs')
                    ->leftJoin(
                        'configurator_step_option',
                        'cso',
                        'cs.id_configurator_step = cso.id_configurator_step'
                    )
                    //->where('cso.id_configurator_step_option = '.(int)$id_option)
                ;
                $results = Db::getInstance()->executeS($query);

                $return = array();
                foreach ($results as $result) {
                    $return[$result['id_configurator_step_option']] = $result['id_configurator_step'];
                }
                DmCache::getInstance()->store($key, $return);
            }

            $key = 'ConfiguratorStepAbstract::getByIdOption-' . $id_option;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $return = isset($return[$id_option])
                    ? ConfiguratorStepFactory::newObject((int)$return[$id_option])
                    : null;
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public function getPriceListWithTax($id_product)
        {
            $return = array();
            if (!empty($this->price_list)) {
                $pricelist = json_decode($this->price_list);
                foreach ($pricelist as $row_value => $row) {
                    foreach ($row as $col_value => $col) {
                        $price = $col * (($this->price_list_coeff > 0) ? $this->price_list_coeff : 1);
                        $price = $price * Context::getContext()->currency->conversion_rate;
                        $return[$row_value][$col_value] = DMTools::getPrice($price, $id_product);
                    }
                }
            }
            return $return;
        }

        public function getIdTaxRulesGroup()
        {
            if ((int)$this->id_tax_rules_group > 0) {
                return (int)$this->id_tax_rules_group;
            }

            $configurator = new ConfiguratorModel($this->id_configurator);
            return $configurator->getIdTaxRulesGroup();
        }

        public function addTaxes(&$stepDetail, $idTaxRulesGroupProduct) {
            $stepDetail['taxes'] = array();
            $stepDetail['price']['tax_incl'] = 0;
            $stepDetail['display_price'] = array(
                'value' => 0,
                'formatted' => null
            );

            $idTaxRulesGroup = ($this->id_tax_rules_group === 0) ? $idTaxRulesGroupProduct : $this->id_tax_rules_group;
            if ($this->unique_price || !empty($this->price_list)) {
                // Prix unique de l'étape OU Grille tarifaire
                $stepDetail['taxes'][$this->id_tax_rules_group] = array(
                    'id_tax_rules_group' => $idTaxRulesGroup,
                    'price_tax_excl' => $stepDetail['price']['tax_excl'],
                    'price_tax_incl' => DMTools::convertPriceTaxExclToTaxIncl(
                        $stepDetail['price']['tax_excl'],
                        $idTaxRulesGroup
                    )
                );
                $stepDetail['price']['tax_incl'] = $stepDetail['taxes'][$this->id_tax_rules_group]['price_tax_incl'];
                if (!DMTools::useTax()) {
                    $stepDetail['display_price']['value'] = DMTools::getDiscountPrice($stepDetail['price']['tax_excl'], $this->getConfigurator()->id_product);
                } else {
                    $stepDetail['display_price']['value'] = DMTools::getDiscountPrice($stepDetail['price']['tax_incl'], $this->getConfigurator()->id_product);
                }
            } else {
                // Calcul des options
                foreach ($stepDetail['options'] as &$optionDetail) {
                    $option = ConfiguratorStepOptionFactory::newObject((int)$optionDetail['id']);
                    if (!Validate::isLoadedObject($option)) {
                        continue;
                    }
                    $option->addTaxes($optionDetail, $idTaxRulesGroupProduct);

                    if ($optionDetail['selected']) {
                        if (isset($optionDetail['taxes'])) {
                            foreach ($optionDetail['taxes'] as $k => $tax) {
                                if (!isset($stepDetail['taxes'][$k])) {
                                    $stepDetail['taxes'][$k] = $tax;
                                    $stepDetail['taxes'][$k]['price_tax_excl'] = $stepDetail['taxes'][$k]['price_tax_excl'] * (int)$optionDetail['qty'];
                                } else {
                                    $stepDetail['taxes'][$k]['price_tax_excl'] += $tax['price_tax_excl'] * (int)$optionDetail['qty'];
                                }
                            }
                            // $stepDetail['taxes'][] = $optionDetail['taxes'][$k];
                        }
                        $stepDetail['display_price']['value'] += $optionDetail['display_price']['value'] * (int)$optionDetail['qty'];
                    }
                }
            }

            if (isset($stepDetail['taxes']) && count($stepDetail['taxes']) > 0) {
                $stepDetail['price']['tax_incl'] = 0;
                foreach ($stepDetail['taxes'] as &$tax) {
                    $tax['id_tax_rules_group'] = ($tax['id_tax_rules_group'] > 0) ? $tax['id_tax_rules_group'] : $idTaxRulesGroup;
                    $tax['price_tax_incl'] = DMTools::convertPriceTaxExclToTaxIncl(
                        $tax['price_tax_excl'],
                        $tax['id_tax_rules_group']
                    );
                    $stepDetail['price']['tax_incl'] += $tax['price_tax_incl'];
                }
            } else {
                if ($stepDetail['price']['tax_excl'] > 0) {
                    $stepDetail['taxes'][$this->id_tax_rules_group] = array(
                        'id_tax_rules_group' => $idTaxRulesGroup,
                        'price_tax_excl' => $stepDetail['price']['tax_excl'],
                        'price_tax_incl' => DMTools::convertPriceTaxExclToTaxIncl(
                            $stepDetail['price']['tax_excl'],
                            $idTaxRulesGroup
                        )
                    );
                    $stepDetail['price']['tax_incl'] += $stepDetail['taxes'][$this->id_tax_rules_group]['price_tax_incl'];
                    if (!DMTools::useTax()) {
                        $stepDetail['display_price']['value'] = DMTools::getDiscountPrice($stepDetail['price']['tax_excl'], $this->getConfigurator()->id_product);
                    } else {
                        $stepDetail['display_price']['value'] = DMTools::getDiscountPrice($stepDetail['price']['tax_incl'], $this->getConfigurator()->id_product);
                    }
                } else {
                    $stepDetail['taxes'][$this->id_tax_rules_group] = array(
                        'id_tax_rules_group' => $idTaxRulesGroup,
                        'price_tax_excl' => 0,
                        'price_tax_incl' => 0
                    );
                }
            }

            $stepDetail['display_price']['formatted'] = DMTools::displayPrice($stepDetail['display_price']['value'], $this->getConfigurator());

            $stepDetail['total_step_amount'] = $stepDetail['display_price']['value'];
            $stepDetail['display_step_amount'] = $stepDetail['display_price']['formatted'];
        }

        public function getConfigurator()
        {
            if($this->configurator === null) {
                $this->configurator = new ConfiguratorModel($this->id_configurator);
            }
            
            return $this->configurator; 
        }

        public static function getAll()
        {
            $key = 'ConfiguratorStepAbstract::getAll';
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT cs.*, csl.* FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` cs '
                    . 'LEFT JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` csl
                            ON (cs.`' . self::$definition['primary'] . '` = csl.`' . self::$definition['primary'] . '`
                            AND csl.`id_lang` = ' . (int)Context::getContext()->language->id . ')'
                    . ' LEFT JOIN `' . _DB_PREFIX_ . 'configurator_step_option` cso
                            ON (cs.`' . self::$definition['primary'] . '` = cso.`' . self::$definition['primary'] . '`)'
                    . ' GROUP BY cs.id_configurator_step';
                $steps = Db::getInstance()->executeS($sql);

                foreach ($steps as $key => $step) {
                    if (is_array($step)) {
                        $steps[$key] = ConfiguratorStepFactory::hydrate($step);
                    }
                }

                DmCache::getInstance()->store($key, $steps);

                return $steps;
            }
        }
    }
}
