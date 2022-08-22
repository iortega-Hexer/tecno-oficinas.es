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
 * @license   http://opensource.org/licenses/afl-3.0.phpAcademic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!class_exists('ConfiguratorCartDetailModel')) {
    require_once(dirname(__FILE__) . '/step/ConfiguratorStepAbstract.php');
    require_once(dirname(__FILE__) . '/step/ConfiguratorStepFactory.php');
    require_once(dirname(__FILE__) . '/option/ConfiguratorStepOptionAbstract.php');
    require_once(dirname(__FILE__) . '/option/ConfiguratorStepOptionFactory.php');
    require_once(dirname(__FILE__) . '/ConfiguratorAttachment.php');
    require_once(dirname(__FILE__) . '/helper/DMTools.php');
    require_once(dirname(__FILE__) . '/../DmCache.php');
    
    /**
     * Class configuratorCartDetailModel
     */
    class ConfiguratorCartDetailModel extends ObjectModel
    {
        const PRODUCT_CART_DETAIL_MAX_QTY = 10000;

        public $id_configurator;
        public $id_product;
        public $id_product_attribute;
        public $id_guest;
        public $id_cart;
        public $id_order = 0;
        public $id_order_detail = 0;
        public $detail;
        public $added_in_cart = 0;
        public $price;
        public $weight;
        public $id_customization;
        public $id_tax_rules_group = 0;
        public $reference = '';

        /**
         * Custom field
         */
        public $product;
        /**
         * Progress percent
         * @var float
         */
        public $progress = 0;
        public $steps_errors = array();
        public $steps_infos = array();
        public $option_ids_errors = array();

        public $pricelist_helper;

        protected $detailJSON = false;
        
        public $steps_info_text = [];

        // Module Viewer 2D
        public $visual_rendering;
        // /Module Viewer 2D

        public static $definition = array(
            'table' => 'configurator_cart_detail',
            'primary' => 'id_configurator_cart_detail',
            'fields' => array(
                /* Classic fields */
                'id_configurator' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
                'id_order_detail' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
                'detail' => array('type' => self::TYPE_STRING),
                'added_in_cart' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
                'weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
                'id_customization' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_tax_rules_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'reference' => array('type' => self::TYPE_STRING),
                // Module Viewer 2D
                'visual_rendering' => array('type' => self::TYPE_STRING),
            )
        );

        /**
         * Extra datas for an option, used on template
         * @var array
         */
        public static $extra_options = array(
            /* Multiple option's choice */
            'multiple' => false,
            /* Value writted by customer */
            'value' => false,
            /* Option selected by customer */
            'selected' => false,
            /* Amount computed and formatted for option */
            /* Not used for pricelist */
            'display_amount' => 0,
            /* Total of configuration and formatted per option on a step which use "display_total" */
            'total_price' => 0,
            /* Reduc */
            'display_reduc' => 0,
            'qty' => 1,
            // Selecte by defaut
            'selected_by_default' => false,
            // Reference
            'reference' => null,
            'reference_position' => null,
            // Position
            'position' => 0
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
            $this->pricelist_helper = new PricelistHelper();
            $this->setProduct();
            if (Validate::isLoadedObject($this)) {
                $this->updateProgress();
            }
        }

        public function add($autodate = true, $null_values = false)
        {
            $result = parent::add($null_values, $autodate);
            if ($result) {
                $this->setProduct();
            }

            return $result;
        }

        public function update($null_values = false)
        {
            $result = parent::update($null_values);
            $this->updateProgress();
            return $result;
        }

        public function hydrate(array $data, $id_lang = null)
        {
            parent::hydrate($data, $id_lang);
            $this->setProduct();
            $this->updateProgress();
        }

        private function canBeAdded($type, $id_configurator_step)
        {
            return $this->canBeAddedConditions($type, $id_configurator_step)
                && $this->canBeAddedFilters($type, $id_configurator_step);
        }

        private function canBeAddedConditions($type, $id_configurator_step)
        {
            $condition_groups = ConfiguratorStepDisplayConditionGroupModel::getConditions(
                $type,
                $id_configurator_step
            );

            $or_boolean = false;
            if (empty($condition_groups)) {
                $or_boolean = true;
            } else {
                foreach ($condition_groups as $condition_group) {
                    $and_boolean = true;
                    foreach ($condition_group->conditions as $condition) {
                        switch ($condition->getType()) {
                            case 'option':
                                // Récupérer une step via un de ses options
                                $configuratorStep = ConfiguratorStepAbstract::getByIdOption((int)$condition->value);
                                $found = $this->foundOption((int)$condition->value);
                                $and_boolean = $and_boolean
                                    && $found
                                    && (
                                    $found
                                    && (!empty($configuratorStep->price_list) || $configuratorStep->use_input || $configuratorStep->use_qty)
                                        ? $this->checkOptionValueCondition((int)$configuratorStep->id, $condition)
                                        : true
                                    );
                                break;
                            case 'formula':
                                $and_boolean = $and_boolean && $this->isValidConditionFormula($type, $id_configurator_step, $condition);
                                break;
                        }
                    }
                    if ($condition_group->negative_condition == 1) {
                        $and_boolean = !$and_boolean;
                    }
                    $or_boolean = $or_boolean || $and_boolean;
                }
            }
            return $or_boolean;
        }

        private function isValidConditionFormula($type, $id_configurator_step, $condition)
        {
            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                require_once(dirname(__FILE__) . '/../../dm_advancedformula/classes/helper/AdvancedformulaHelper.php');
                if ($type === ConfiguratorStepAbstract::TYPE_CONDITION_STEP) {
                    $step = ConfiguratorStepFactory::newObject($id_configurator_step);
                } else {
                    $option = ConfiguratorStepOptionFactory::newObject($id_configurator_step);
                    $step = $option->getStep();
                }
                // This is a link with the module "dm_advancedformula"
                // We check before this module is installed
                (float)$value_formula = AdvancedformulaHelper::loadFormula(
                    $this,
                    $step,
                    $this->getDetail(),
                    $condition->formula
                );

                return (bool)$value_formula;
            }
            return false;
        }

        private function canBeAddedFilters($type, $id_configurator_step_option)
        {
            if ($type === ConfiguratorStepAbstract::TYPE_CONDITION_OPTION) {
                $filter_groups = ConfiguratorStepFilterGroupModel::getFilters(
                    $type,
                    $id_configurator_step_option
                );

                $or_boolean = false;
                if (empty($filter_groups)) {
                    $or_boolean = true;
                } else {
                    foreach ($filter_groups as $filter_group) {
                        $and_boolean = true;
                        foreach ($filter_group->filters as $filter) {
                            if (!$filter->isValid($id_configurator_step_option, $this)) {
                                $and_boolean = false;
                                break;
                            }
                        }
                        $or_boolean = $or_boolean || $and_boolean;
                    }
                }
                return $or_boolean;
            }
            return true;
        }

        protected function l($string)
        {
            $string = str_replace('\'', '\\\'', $string);
            return Translate::getModuleTranslation('Configurator', $string, __CLASS__);
        }

        public function checkOptionValueCondition($id_step, ConfiguratorStepDisplayConditionModel $condition)
        {
            // Si grille tarifaire et au moins min ou max de remplie (supérieur à 0)
            // Chercher la valeur saisie dans cette option dans le detail => detail[id_step][options][id_option][value]
            $detail = $this->getDetail();
            if (isset($detail[(int)$id_step]['options'][(int)$condition->value]['value'])) {
                if ($detail[(int)$id_step]['use_qty']) {
                    $stepValue = $detail[(int)$id_step]['options'][(int)$condition->value]['qty'];
                } else {
                    $stepValue = $detail[(int)$id_step]['options'][(int)$condition->value]['value'];
                }
                if ((int)$condition->min > 0
                    && (int)$condition->max === 0
                    && (float)$stepValue >= (int)$condition->min
                ) {
                    return true;
                } elseif ((int)$condition->min >= 0
                    && (int)$condition->max >= 0
                    && (float)$stepValue >= (float)$condition->min
                    && (float)$stepValue <= (float)$condition->max
                ) {
                    return true;
                }

                // Prise en compte si la valeur est supérieure à 0 cela doit déclencher la condition
                if ($stepValue >= 0 && (int)$condition->min === 0 && (int)$condition->max === 0 ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Update progress percent value
         */
        protected function updateProgress()
        {
            if (Configuration::get('CONFIGURATOR_PROGRESS_COMPENENT') || Configuration::get('CONFIGURATOR_DISABLE_ADDTOCART_BTN')) {
                $detail = $this->getDetail();
                $total_steps = 0;
                $total_steps_with_selected_option = 0;
                foreach ($detail as $step_detail) {
                    if (isset($step_detail['required'])
                        && $step_detail['required']
                        && ((isset($step_detail['ignored_if_empty']) && !$step_detail['ignored_if_empty']) || count($step_detail['options']) > 0)
                    ) {
                        $step = $this->getConfiguratorStepModel((int)$step_detail['id']);
                        if (Validate::isLoadedObject($step)) {
                            if ($step->displayed_by_yes && !(isset($step_detail['yes_no_value']) && $step_detail['yes_no_value'] === true)) {
                                continue;
                            }
                            $selected = (bool)(count($step_detail['options']) > 0);
                            $total_steps++;
                            foreach ($step_detail['options'] as $option) {
                                $selected = ($step->use_input ? $selected : true)
                                    && $this->foundOption((int)$option['id'], !$step->use_input, $step->use_input);
                                if (!$step->use_input && $selected) {
                                    break;
                                }
                            }
                            if ($step_detail['is_upload'] && $step_detail['file_has_been_uploaded']) {
                                $selected = count($step_detail['attachments']) > 0;
                            }
                            if ($selected) {
                                $total_steps_with_selected_option++;
                            }
                        }
                    }
                }
                if ($total_steps) {
                    $this->progress = Tools::ps_round($total_steps_with_selected_option * 100 / $total_steps, 0);
                }
            }
        }

        /**
         *
         * @param ConfiguratorStepOptionAbstract $option
         * @param float $base_price
         * @return float
         */
        protected function getAmount(ConfiguratorStepOptionAbstract $option, $base_price)
        {
            $stepQty = $option->findStepQty($this->getDetail());

            // CONFIGURATOR HOOK
            $result = false;
            Hook::exec('configuratorActionCartDetailGetAmount', array(
                'cart_detail_model' => &$this,
                'option' => $option,
                'base_price' => $base_price,
                'result' => &$result
            ));

            if ($result !== false) {
                return $result;
            }

            switch ($option->impact_type) {
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PERCENT:
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_NEGATIVE_PERCENT:
                    return (
                        (float)$base_price
                        * ((float)$option->impact_value / 100)
                        * ($option->impact_type === ConfiguratorStepOptionAbstract::IMPACT_TYPE_PERCENT ? 1 : -1)
                        * $stepQty
                    );
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_AREA:
                    if ((int)$option->impact_step_id) {
                        // Surface par formule de calcul
                        $step = ConfiguratorStepFactory::newObject((int)$option->impact_step_id);
                        $xy_values = $this->findPriceListValuesFromStepId((int)$option->impact_step_id);
                        if (!empty($xy_values['x']) && !empty($xy_values['y'])) {
                            return ((float)$option->impact_value * ((float)$xy_values['x'] * (float)$xy_values['y'])) * $stepQty;
                        }
                    } else {
                        if (!empty($option->id_impact_step_option_x) && !empty($option->id_impact_step_option_y)) {
                            $xy_values = $this->findPriceListValuesFromXYStepId(
                                $option->id_impact_step_option_x,
                                $option->id_impact_step_option_y
                            );
                            if (!empty($xy_values['x']) && !empty($xy_values['y'])) {
                                return (
                                    (float)$option->impact_value * ((float)$xy_values['x'] * (float)$xy_values['y']) * $stepQty
                                );
                            }
                        }
                    }
                    return 0;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_AREA_MULTIPLE:
                    if ($option->impact_multiple_step_id) {
                        $impact_multiple_step_id = explode(',', $option->impact_multiple_step_id);
                        foreach ($impact_multiple_step_id as $id) {
                            // Surface par formule de calcul
                            $step = ConfiguratorStepFactory::newObject($id);

                            if ($this->existStep((int)$step->id)) {
                                $xy_values = $this->findPriceListValuesFromStepId((int)$step->id);
                                if (!empty($xy_values['x']) && !empty($xy_values['y'])) {
                                    return (
                                        (float)$option->impact_value * ((float)$xy_values['x'] * (float)$xy_values['y']) * $stepQty
                                    );
                                }
                            }
                        }
                    }
                    return 0;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST:
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_QUANTITY:
                    if ((int)$option->impact_step_id && !empty($option->price_list)) {
                        $xy_values = $this->findPriceListValuesFromStepId((int)$option->impact_step_id);
                        if (!empty($xy_values['x'])) {
                            $this->pricelist_helper->setPricelist(Tools::jsonDecode($option->price_list, true));
                            return ((float)$this->pricelist_helper->getValue($xy_values['x'], $xy_values['y']) * $stepQty);
                        }
                    } else {
                        if (!empty($option->impact_step_option_id)) {
                            $xy_values = $this->findPriceListValuesFromXYStepId($option->impact_step_option_id);
                            if (!empty($xy_values['x']) && !empty($xy_values['y'])) {
                                return (
                                    (float)$option->impact_value * ((float)$xy_values['x'] * (float)$xy_values['y'])
                                );
                            }
                        }
                    }
                    return 0;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA_SQUARE:
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA:
                    if ((int)$option->impact_step_id && !empty($option->price_list)) {
                        $xy_values_pricelist = $this->findPriceListValuesFromStepId((int)$option->impact_step_id);

                        /**
                         * Récupération de la surface de l'étape
                         */
                        $step = ConfiguratorStepFactory::newObject((int)$option->impact_step_id);
                        $xy_values = $this->findPriceListValuesFromStepId((int)$option->impact_step_id);
                        if (!empty($xy_values['x']) && !empty($xy_values['y'])) {
                            $surface = (float)$xy_values['x'] * (float)$xy_values['y'];
                        }

                        /**
                         * @todo: Voir comment traiter les bornes extérieures
                         */
                        $this->pricelist_helper->setPricelist(Tools::jsonDecode($option->price_list, true));
                        $price = $this->pricelist_helper->getValue($surface);

                        if (ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_AREA) {
                            $surface = $price * $surface;
                        } else {
                            $surface = $price;
                        }

                        return $surface * $stepQty;
                    }
                    return 0;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_PRICELIST_MULTIPLE:
                    if ($option->impact_multiple_step_id) {
                        $impact_multiple_step_id = explode(',', $option->impact_multiple_step_id);
                        foreach ($impact_multiple_step_id as $id) {
                            $step = ConfiguratorStepFactory::newObject($id);
                            if ($this->existStep((int)$step->id)) {
                                if ((int)$id && !empty($option->price_list)) {
                                    $xy_values = $this->findPriceListValuesFromStepId((int)$id);
                                    if (!empty($xy_values['x']) && !empty($xy_values['y']) ) {
                                        $this->pricelist_helper->setPricelist(
                                            Tools::jsonDecode($option->price_list, true)
                                        );
                                        return ((float)$this->pricelist_helper->getValue(
                                            $xy_values['x'],
                                            $xy_values['y']
                                        ) * $stepQty);
                                    }
                                }
                            }
                        }
                    }
                    return 0;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_REDUCTION_AMOUNT:
                    return -((float)$option->impact_value * $stepQty);
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER:
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_NEGATIVE_MULTIPLIER:
                    if ((int)$option->impact_step_id) {
                        $xy_values = $this->findPriceListValuesFromStepId((int)$option->impact_step_id);
                        if (!empty($xy_values['x'])) {
                            return (float)$xy_values['x']
                                * (float)$option->impact_value
                                * ($option->impact_type === ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER
                                    ? 1
                                    : -1
                                )
                                * $stepQty;
                        }
                    }
                    return 0;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_MULTIPLIER_PRICE:
                    return (float)$this->getTotalStepPrice((int)$option->impact_step_id) * (float)$option->impact_value * $stepQty;
                case ConfiguratorStepOptionAbstract::IMPACT_TYPE_AMOUNT_PERIOD:
                    $value = $option->getImpactValuePeriod();
                    if ($value === false) {
                        return (float)$option->impact_value * $stepQty;
                    }
                    // Valeur sur une période
                    return (float)$value * $stepQty;
                default:
                    return (float)$option->impact_value * $stepQty;
            }
        }

        public function getPrice(&$step_detail, $base_price)
        {
            $step = ConfiguratorStepFactory::newObject($step_detail['id']);
            $stepPriceTaxExl = 0;

            foreach ($step_detail['options'] as &$option_detail) {
                $key = '-' . $option_detail['id'];
                if (DmCache::getInstance()->isStored($key)) {
                    $option = DmCache::getInstance()->retrieve($key);
                } else {
                    $option = ConfiguratorStepOptionFactory::newObject((int)$option_detail['id']);
                    DmCache::getInstance()->store($key, $option);
                }

                if (!Validate::isLoadedObject($option)) {
                    continue;
                }

                // CONFIGURATOR HOOK
                $result = false;
                Hook::exec('configuratorActionCartDetailGetPrice', array(
                    'configuratorCartDetailModel' => $this,
                    'step' => $step,
                    'option' => $option,
                    'option_detail' => $option_detail,
                    'result' => &$result
                ));

                if ($result !== false) {
                    // Si le prix est surchargé par un autre module
                    $displayPrice = (float)$result['display'];
                    $optionPriceTaxExcl = $result['amount'];
                    $reducTaxExcl = $result['reduc'];
                    $stepPriceTaxExl += $result['add_price'];
                } else {
                    // Calcul su prix HT d'une option
                    $optionPriceTaxExcl = (float)$this->getAmount($option, $base_price);
                    $reducTaxExcl = 0;
                    $displayPrice = null;

                    if ($option_detail['selected']) {
                        $stepPriceTaxExl += $optionPriceTaxExcl * (int)$option_detail['qty'];
                        if ($option->id_tax_rules_group_product > 0) {
                            $this->id_tax_rules_group = (int)$option->id_tax_rules_group_product;
                        }
                    }
                }

                // Le calcul des taxes est fait à la fin
                $option_detail['taxes'][$option->id_tax_rules_group] = array(
                    'id_tax_rules_group' => 0,
                    'price_tax_excl' => $optionPriceTaxExcl,
                    'price_tax_incl' => 0
                );
                $option_detail['price'] = array(
                    'tax_excl' => $optionPriceTaxExcl,
                    'tax_incl' => 0
                );
                $option_detail['display_price'] = array(
                    'value' => $displayPrice,
                    'formatted' => null
                );

                // Used on template to display real amount added. Case of certain impact_price
                $option_detail['display_amount'] = '';
                $option_detail['reduc_tax_excl'] = $reducTaxExcl;
                $option_detail['display_reduc'] = '';
            }
            return $stepPriceTaxExl;
        }

        /**
         * Get the x and y values writted by customer
         * @param array $step_detail
         * @return array('x'=>x value,'y'=>y value)
         */
        protected function findPriceListValues($step_detail, $id_option = null)
        {
            $k = 0;
            $x = '';
            $y = '';
            // Find x and y
            foreach ($step_detail['options'] as $option) {
                if ($id_option) {
                    if ($option['id'] == $id_option) {
                        return !empty($option['value']) ? $option['value'] : 0;
                    }
                } else if ($step_detail['use_qty']) {
                    if ($option['selected'] && !empty($option['qty'])) {
                        $x = (int)$x + (int)$option['qty'];
                    }
                } else if ($k < 2) {
                    if ($k === 0) {
                        if (!empty($option['value'])) {
                            $x = $option['value'];
                        }
                    } else {
                        if (!empty($option['value'])) {
                            $y = $option['value'];
                        }
                    }
                    $k++;
                }
            }
            return array('x' => $x, 'y' => $y);
        }

        protected function getValueFromPriceList($step_detail, array $price_list)
        {
            // Find x and y
            $xy_values = $this->findPriceListValues($step_detail);
            $x = $xy_values['x'];
            $y = $xy_values['y'];
            $this->pricelist_helper->setPricelist($price_list);
            return $this->pricelist_helper->getValue($x, $y);
        }

        /**
         * Calcule pour chaque option de l'étape le total de la configuration
         * @param array $step_detail avec sa référence pour modifier le détail de l'option
         * @param float $base_price
         * @param float $final_price
         * @deprecated since version 2.0.0
         */
        protected function computeTotalPerOption(array &$step_detail, $base_price, $final_price)
        {
            $old_price = $this->product->price;
            foreach ($step_detail['options'] as &$option_detail) {
                $key = '-compute-' . $option_detail['id'];
                if (DmCache::getInstance()->isStored($key)) {
                    $option = DmCache::getInstance()->retrieve($key);
                } else {
                    $option = ConfiguratorStepOptionFactory::newObject((int)$option_detail['id']);
                    DmCache::getInstance()->store($key, $option);
                }
                $amount = $this->getAmount($option, $base_price);
                $this->product->price = Configurator::normalizeFloat(
                    (float)$final_price + ((float)$amount * (int)$option_detail['qty'])
                );
                $this->product->save();
                // Important
                Product::flushPriceCache();
                $option_detail['total_price'] = Tools::displayPrice(
                    $this->product->getPrice((Product::getTaxCalculationMethod() !== 1), null)
                );
                // Important
                Product::flushPriceCache();
            }
            $this->product->price = $old_price;
            $this->product->save();
            // Important
            Product::flushPriceCache();
        }

        public function priceListIsTwoDimension(ConfiguratorStepAbstract $step)
        {
            $pricelist = Tools::jsonDecode($step->price_list, true);
            if (empty($pricelist)) {
                return false;
            }

            $this->pricelist_helper->setPricelist($pricelist);
            return (int)$this->pricelist_helper->getDimension() === PricelistHelper::TWO_DIMENSION;
        }

        public function getTotalStepPrice($step_id)
        {
            $detail = $this->getDetail();

            if (isset($detail[(int)$step_id]) && isset($detail[(int)$step_id]['total_step_amount'])) {
                return (float)$detail[(int)$step_id]['total_step_amount'];
            }

            return 0.0;
        }

        /**
         * Get the x and y values from a step_id
         * @param type $step_id
         */
        public function findPriceListValuesFromStepId($step_id, $id_option = null)
        {
            $detail = $this->getDetail();
            foreach ($detail as $step_detail) {
                if ((int)$step_id === (int)$step_detail['id']) {
                    return $this->findPriceListValues($step_detail, $id_option);
                }
            }
            return array('x' => '', 'y' => '');
        }

        /**
         * Get the x and y values from a step_id
         * @param type $step_id
         */
        public function findPriceListValuesFromXYStepId($id_option_x, $id_option_y)
        {
            $option_x = ConfiguratorStepOptionFactory::newObject($id_option_x);
            $option_y = ConfiguratorStepOptionFactory::newObject($id_option_y);
            $x = '';
            $y = '';
            $x = $this->findPriceListValuesFromStepId((int)$option_x->id_configurator_step, (int)$id_option_x);
            $y = $this->findPriceListValuesFromStepId((int)$option_y->id_configurator_step, (int)$id_option_y);
            return array('x' => $x, 'y' => $y);
        }

        /**
         * Get price value at ($x, $y)
         * @param ConfiguratorStepAbstract $step Step where the price list is stored
         * @param type $x value for x axis
         * @param type $y value for y axis
         * @return type null if not found, value otherwise
         */
        public function getPriceListValue(ConfiguratorStepAbstract $step, $x, $y)
        {
            $price_list = Tools::jsonDecode($step->price_list, true);
            $this->pricelist_helper->setPricelist($price_list);
            return $this->pricelist_helper->getValue($x, $y);
        }


        public function valueFromPriceListIsValid(ConfiguratorStepAbstract $step)
        {
            if (empty($step->price_list)) {
                return false;
            }
            $price_list = Tools::jsonDecode($step->price_list, true);
            foreach ($this->getDetail() as $step_detail) {
                if ((int)$step->id !== (int)$step_detail['id']) {
                    continue;
                }
                return !is_null($this->getValueFromPriceList($step_detail, $price_list));
            }
            return false;
        }

        public function getPriceFromPricelist($step_detail, array $price_list, $base_price, $type, $coeff)
        {
            $price = (float)$this->getValueFromPriceList($step_detail, $price_list);
            $new_price = ((int)$type === ConfiguratorStepAbstract::PRICE_LIST_TYPE_PERCENT)
                ? (float)$base_price * ((float)$price / 100)
                : $price;
            return (float)$coeff > 0 ? $new_price * (float)$coeff : $new_price;
        }

        public function getUniquePriceIfSelected(ConfiguratorStepAbstract $step)
        {
            $details = $this->getDetail();
            $return = false;
            if ($step->unique_price) {
                $uniquePriceTaxExcl = $step->unique_price_value;
                foreach ($details[(int)$step->id]['options'] as $detail_option) {
                    if ((bool)$detail_option['selected']) {
                        $return = $uniquePriceTaxExcl;

                        $option = ConfiguratorStepOptionFactory::newObject((int)$detail_option['id']);
                        if ($option->id_tax_rules_group_product > 0) {
                            $this->id_tax_rules_group = (int)$option->id_tax_rules_group_product;
                        }
                    }
                }
            }

            return $return;
        }

        /**
         * Compute current configuration
         */
        public function compute($base_product)
        {
            $price = 0.0;
            if (Validate::isLoadedObject($this->product) && Validate::isLoadedObject($base_product)) {
                $detail = $this->getDetail();
                $key = $this->id . '-' . md5(serialize($detail));
                if (DmCache::getInstance()->isStored($key)) {
                    return DmCache::getInstance()->retrieve($key);
                }

                // Remove product price to prevent some problems
                Product::flushPriceCache();
                $configurator = new ConfiguratorModel($this->id_configurator);
                $base_price = ($configurator->use_base_price) ? (float)$base_product->price : 0;
                $base_price_tmp = (float)$base_product->price;
                $finalPriceTaxExcl = $base_price;
                $reference = array();
                //$weight = (float)$base_product->weight;
                $weight = 0.00;

                $this->id_tax_rules_group = $this->product->id_tax_rules_group;

                foreach ($detail as &$step_detail) {
                    $step = $this->getConfiguratorStepModel((int)$step_detail['id']);
                    if (!Validate::isLoadedObject($step)) {
                        continue;
                    }

                    // CONFIGURATOR HOOK
                    $stepPriceTaxExclToAdd = Hook::exec('configuratorActionCartDetailComputeAddingPrice', array(
                        'cart_detail_model' => &$this,
                        'step' => $step,
                        'detail' => $detail
                    ));

                    if ($stepPriceTaxExclToAdd === false || $stepPriceTaxExclToAdd === "") {
                        if ($step->unique_price) {
                            $stepPriceTaxExclToAdd = (float)$this->getUniquePriceIfSelected($step);
                        } elseif (empty($step->price_list)) {
                            $stepPriceTaxExclToAdd = (float)$this->getPrice($step_detail, $base_price);
                        } else {
                            $stepPriceTaxExclToAdd = (float)$this->getPriceFromPricelist(
                                $step_detail,
                                Tools::jsonDecode($step->price_list, true),
                                $base_price,
                                $step->price_list_type,
                                $step->price_list_coeff
                            );
                        }
                    }

                    // Le calcul des taxes se fait à la fin
                    $step_detail['price'] = array(
                        'tax_excl' => $stepPriceTaxExclToAdd,
                        'tax_incl' => 0
                    );
                    $step_detail['total_step_amount'] = $stepPriceTaxExclToAdd;
                    $step_detail['display_step_amount'] = '';

                    $finalPriceTaxExcl += $stepPriceTaxExclToAdd;

                    $base_price = $step->determine_base_price ? $finalPriceTaxExcl : $base_price;

                    $step_detail['weight'] = $step->getWeight($this);
                    if (isset($step_detail['weight']) && $step_detail['weight']) {
                        $weight += (float)$step_detail['weight'];
                    }

                    $step_detail['dimension_width'] = $step->getDimension('width', $this);
                    $step_detail['dimension_height'] = $step->getDimension('height', $this);
                    $step_detail['dimension_depth'] = $step->getDimension('depth', $this);

                    foreach ($step_detail['options'] as $opt) {
                        if (isset($opt['selected'])
                            && $opt['selected']
                            && isset($opt['reference'])
                            && $opt['reference']
                        ) {
                            $reference[$opt['reference_position']] = $step->getReference($opt['reference'] ,$this);
                        }
                        if (isset($opt['selected']) && $opt['selected'] && isset($opt['weight']) && $opt['weight']) {
                            $qty = (isset($opt['qty']) && (int)$opt['qty'] > 1) ? $opt['qty'] : 1;
                            $weight += (float)((int)$qty)*((float)$opt['weight']);
                        }
                    }
                }
                ksort($reference);
                $this->reference = $base_product->reference . implode('', $reference);

                // Prevent problem when price is negative, price must not be negative
                $finalPriceTaxExcl = ((float)$finalPriceTaxExcl < 0) ? 0 : (float)$finalPriceTaxExcl;

                // Calcul les taxes
                $this->refreshTaxes($detail);

                $this->setDetail($detail);
                $this->price = Configurator::normalizeFloat($finalPriceTaxExcl);
                $this->weight = Configurator::normalizeFloat($weight);
                if (DMTools::getVersionMajor() >= 17) {
                    $stepsPriceTaxExcl = $finalPriceTaxExcl - $base_price_tmp;
                    $this->setCustomization($stepsPriceTaxExcl, $weight);
                } else {
                    $this->setCustomization();
                }
                $this->save();

                $price = Configurator::normalizeFloat($finalPriceTaxExcl);
            }

            return $price;
        }

        private function refreshTaxes(&$detail)
        {
            $idTaxRulesGroupProduct = (int)$this->id_tax_rules_group;
            foreach ($detail as &$stepDetail) {
                $step = $this->getConfiguratorStepModel((int)$stepDetail['id']);
                if (!Validate::isLoadedObject($step)) {
                    continue;
                }
                $step->addTaxes($stepDetail, $idTaxRulesGroupProduct);
            }
        }

        public function getTaxesDetail() {
            $taxes = array();

            $configurator = new ConfiguratorModel($this->id_configurator);
            $base_price = ($configurator->use_base_price) ? (float)$this->product->price : 0;
            $taxes[$this->id_tax_rules_group] = array(
                'id_tax_rules_group' => $this->id_tax_rules_group,
                'price_tax_excl' => $base_price,
                'price_tax_incl' => DMTools::convertPriceTaxExclToTaxIncl(
                    $base_price,
                    $this->id_tax_rules_group
                )
            );

            $detail = $this->getDetail();
            foreach ($detail as $stepDetail) {
                if (isset($stepDetail['taxes']) && $stepDetail['taxes']) {
                    foreach ($stepDetail['taxes'] as $tax) {
                        if (isset($taxes[(int)$tax['id_tax_rules_group']])) {
                            $taxes[(int)$tax['id_tax_rules_group']]['price_tax_excl'] += $tax['price_tax_excl'];
                            $taxes[(int)$tax['id_tax_rules_group']]['price_tax_incl'] += $tax['price_tax_incl'];
                        } else {
                            $taxes[(int)$tax['id_tax_rules_group']] = $tax;
                        }
                    }
                }
            }

            return $taxes;
        }

        public function taxesCalculation()
        {
            $priceTaxExcl = 0;
            $priceTaxIncl = 0;
            $detail = $this->getDetail();
            foreach ($detail as $stepDetail) {
                if (isset($stepDetail['price'])) {
                    $priceTaxExcl += $stepDetail['price']['tax_excl'];
                    $priceTaxIncl += $stepDetail['price']['tax_incl'];
                }
            }
            $tax = $priceTaxIncl - $priceTaxExcl;

            $configurator = new ConfiguratorModel($this->id_configurator);
            $base_price = ($configurator->use_base_price) ? (float)$this->product->price : 0;
            $productTax = DMTools::convertPriceTaxExclToTaxIncl(
                    $base_price,
                $this->id_tax_rules_group
            ) - $base_price;
            return (float)($tax + $productTax);
        }

        public function setCustomization($price = 0, $weight = 0)
        {
            $customization = new Customization($this->id_customization);
            if (Validate::isLoadedObject($customization)) {
                /** @var ConfiguratorModel $configurator * */
                $configurator = new ConfiguratorModel($this->id_configurator);
                $customization_field = new CustomizationField($configurator->id_customization_field);
                $data = array(
                    'id_customization' => (int)$this->id_customization,
                    'type' => (int)Product::CUSTOMIZE_TEXTFIELD,
                    'index' => (int)$customization_field->id,
                    'value' => str_replace("'", "\\'", $this->generateCustomizedDatas())
                );
				// CONFIGURATOR HOOK
				Hook::exec('configuratorActionCartDetailModelSetCustomization', array(
					'cart_detail' => $this,
					'data' => &$data,
					'price' => &$price
				));
                if (DMTools::getVersionMajor() >= 17) {
                    $data['price'] = $price;
                    $data['weight'] = $weight;
                    $id_module = Module::getModuleIdByName('configurator');
                    $data['id_module'] = $id_module;
                }
                Db::getInstance()->insert('customized_data', $data, false, true, Db::REPLACE);
            } else {
                $customization = new Customization();
                $customization->id_product_attribute = ($this->id_product_attribute) ? $this->id_product_attribute : 0;
                $customization->id_cart = (Context::getContext()->cart) ? (int)Context::getContext()->cart->id : 0;
                $customization->id_address_delivery = (Context::getContext()->cart)
                    ? (int)Context::getContext()->cart->id_address_delivery
                    : 0;
                $customization->id_product = $this->id_product;
                $customization->quantity = 0;
                $customization->quantity_refunded = 0;
                $customization->quantity_returned = 0;
                $customization->in_cart = 0;
                $customization->save();

                $this->id_customization = $customization->id;

                $this->setCustomization($price, $weight);
            }
        }

        public function generateCustomizedDatas()
        {
            $return = $this->getDetailFormated(false, true);

            // CONFIGURATOR HOOK
            Hook::exec('configuratorActionCartDetailModelGenerateCustomizedDatas', array(
                'cart_detail' => $this,
                'return' => &$return
            ));

            return $return;
        }

        public function setProduct()
        {
            $this->product = new Product((int)$this->id_product, false);
            if ((int)$this->id_tax_rules_group === 0) {
                $this->id_tax_rules_group = $this->product->id_tax_rules_group;
            };
        }

        public function getStepInfosByIdStep($id_step)
        {
            if (count($this->steps_infos) > 0) {
                foreach ($this->steps_infos as $detail_id_step => $infos) {
                    if ((int)$detail_id_step === (int)$id_step) {
                        return $infos;
                    }
                }
            }

            return false;
        }

        public function getDetail($unset_empty_step = false)
        {
            if ($this->detailJSON === false) {
                $this->detailJSON = Tools::jsonDecode($this->detail, true);
            }
            $detail = (empty($this->detailJSON) ? array() : $this->detailJSON);

            if ($unset_empty_step) {
                foreach ($detail as $k => $step) {
                    if (!$step['options'] && !$step['is_upload']) {
                        if (isset($step['ignored_if_empty']) && $step['ignored_if_empty']) {
                            unset($detail[$k]);
                        }
                    }
                }
            }

            return $detail;
        }

        public function setDetail($detail)
        {
            $this->detail = Tools::jsonEncode($detail);
            $this->detailJSON = $detail;
        }

        public function getDetailFormated(
            $text = false,
            $with_files_link = false,
            $array = false,
            $array_of_array = false
        )
        {
            $list_detail = array();
            $list_detail_array = array();
            $detail = $this->getDetail();
            foreach ($detail as $step_detail) {
                $array_options = array();
                $array_option_array = array();
                $display = false;

                if ($step_detail['is_upload'] && $step_detail['file_has_been_uploaded']) {
                    $display = true;
                    if ($with_files_link) {
                        $attachments = $this->getAttachments((int)$step_detail['id']);
                        foreach ($attachments as $a) {
                            $link = Context::getContext()->link->getModuleLink(
                                'configurator',
                                'attachment',
                                array('token' => $a['token'])
                            );
                            $array_options[] = "<a href='{$link}'>{$a['file_name']}</a>";
                        }
                    } else {
                        $array_options[] = count($step_detail['attachments']) . ' ' . $this->l('files uploaded');
                    }
                } elseif (!$step_detail['is_upload']) {
                    foreach ($step_detail['options'] as $option_detail) {
                        if (!empty($option_detail['value']) && $option_detail['value'] !== false) {
                            $array_options[] = $option_detail['name'] . ' : '
                                . $option_detail['value'] . $step_detail['input_suffix'];
                            $array_option_array[$option_detail['name']] = $option_detail['value']
                                . $step_detail['input_suffix'];
                            $display = true;
                        } elseif ($option_detail['selected']) {
                            $label = $option_detail['name'];
                            if ($step_detail['use_qty']) {
                                $label = (int)$option_detail['qty'] . 'x ' . $label;
                            }
                            $array_options[] = $text ? $label : ('<span class="configurator' . ($option_detail['selected_by_default'] ? '-default' : '') . '-option">' . $label . '</span>');
                            $array_option_array[] = $label;
                            $display = true;
                        }
                    }
                }

                /**
                 * @since 3.3.0
                 */
                if (isset($step_detail['displayed_in_order'])) {
                    $display = $display && (bool)$step_detail['displayed_in_order'];
                }

                /**
                 * Si affiché
                 */
                $step_name = (!empty($step_detail['invoice_name']))
                    ? $step_detail['invoice_name']
                    : $step_detail['name'];
                if ($display) {
                    $list_detail_array[$step_name] = ($array_of_array
                        ? $array_option_array
                        : implode(', ', $array_options)
                    );
                    $separator_option = ($text ? ', ' : Configuration::get('CONFIGURATOR_CUSTOMIZATION_SEPARATOR_OPTION_HTML'));
                    $list_detail[] = ($text
                            ? $step_name.' : '
                            : '<span class="configurator-detail-step configurator-detail-step-' . $step_detail['id'] . '"><span class="configurator-detail-step-name">' . $step_name . '</span> : <span class="configurator-detail-step-values">'
                        ) . implode($separator_option, $array_options) . ($text
                            ? ''
                            : '</span></span>'
                        );
                }
            }

            if ($array) {
                return $list_detail_array;
            }

            return implode(($text ? "\n" : "<br />"), $list_detail);
        }

        /**
         * Check if step can be added to details (display conditions)
         */
        public function stepCanBeAdded($step)
        {
            if (Validate::isLoadedObject($step)) {
                $display = $this->canBeAdded(ConfiguratorStepAbstract::TYPE_CONDITION_STEP, (int)$step->id);
                return (int)$step->position === 0 || $display;
            }
            return false;
        }

        /**
         * @param $id
         * @return ConfiguratorStepAbstract
         */
        public function getConfiguratorStepModel($id)
        {
            $key = 'configurator::getConfiguratorStepModel-' . $id;
            if (DmCache::getInstance()->isStored($key)) {
                $model = DmCache::getInstance()->retrieve($key);
            } else {
                $model = ConfiguratorStepFactory::newObject((int)$id);
                DmCache::getInstance()->store($key, $model);
            }
            return $model;
        }

        /**
         * Check if option can be added to details (display conditions)
         */
        public function optionCanBeAdded(ConfiguratorStepOptionAbstract $configurator_step_option)
        {
            $step = $this->getConfiguratorStepModel((int)$configurator_step_option->id_configurator_step);
            if (Validate::isLoadedObject($step)
                && $this->stepCanBeAdded($step)
                && Validate::isLoadedObject($configurator_step_option)
            ) {
                $display = $this->canBeAdded(
                    ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
                    (int)$configurator_step_option->id
                );
                return $display;
            }
            return false;
        }

        /**
         * Test if option is found
         */
        public function existOption($value)
        {
            return $this->foundOption($value, false);
        }

        /**
         * Test if option is found and selected
         */
        public function foundOption($value, $check_selected = true, $check_value = false)
        {
            $found = false;
            $detail = $this->getDetail();
            foreach ($detail as $step_detail) {
                if (isset($step_detail['options']) && $step_detail['options']) {
                    foreach ($step_detail['options'] as $option_detail) {
                        if (((int)$value === (int)$option_detail['id'])
                            && ($check_selected ? $option_detail['selected'] : true)
                            && ($check_value ? $option_detail['value'] : true)
                        ) {
                            $found = true;
                            break 2;
                        }
                    }
                }
            }
            return $found;
        }

        /**
         * Test if step is found
         */
        public function existStep($id_step)
        {
            $detail = $this->getDetail();
            foreach ($detail as $step_detail) {
                if ($id_step == $step_detail['id']) {
                    return true;
                }
            }
            return false;
        }

        public function addStep(ConfiguratorStepAbstract $step, $extras = array())
        {
            $detail = $this->getDetail();
            // Key by position, we need to sort steps by his position
            $detail_resorted = array();
            foreach ($detail as $step_detail) {
                $detail_resorted[$step_detail['position']] = $step_detail;
            }

            $isUploadType = $step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD);
            $detail_resorted[(int)$step->position] = array(
                'id' => (int)$step->id,
                'name' => $step->public_name,
                'invoice_name' => $step->invoice_name,
                'displayed_in_preview' => $step->displayed_in_preview,
                'displayed_in_order' => $step->displayed_in_order,
                'input_suffix' => $step->input_suffix,
                'use_qty' => $step->use_qty,
                'required' => $step->required,
                'ignored_if_empty' => $step->ignored_if_empty,
                'options' => array(),
                'position' => $step->position,
                'price_list' => ($step->price_list != '') ? true : false,
                'is_upload' => $isUploadType,
                'file_has_been_uploaded' => $isUploadType ? ($this->getNumberOfAttachments((int)$step->id) > 0) : false,
                'attachments' => array(),
                'disabled' => 0,
                'dropzone' => ($step->dropzone) ? json_decode($step->dropzone) : null,
                'yes_no_value' => isset($extras['yes_no_value']) ? (bool)$extras['yes_no_value'] : null
            );

            // CONFIGURATOR HOOK
            Hook::exec('configuratorActionCartDetailAddStep', array(
                'cart_detail_model' => &$this,
                'detail_resorted' => &$detail_resorted
            ));

            ksort($detail_resorted);
            // Reset keys with stepID, we need this structure
            $detail = array();
            foreach ($detail_resorted as $step_detail) {
                $detail[$step_detail['id']] = $step_detail;
            }

            $this->setDetail($detail);
            //$this->save();
        }

        public function removeStep($value)
        {
            $detail = $this->getDetail();
            foreach ($detail as &$step_detail) {
                if (((int)$value === (int)$step_detail['id'])) {
                    unset($detail[(int)$step_detail['id']]);
                    $this->setDetail($detail);
                    //$this->save();
                    break;
                }
            }
        }

        public function addOption($id_step, $configurator_step_option, $extras = array(), $erase_selection = false, $dropzone_positions = null)
        {
            /**
             * @todo: Vérifier si pertinant
             */
            // FIX : update option position inside step
            $step = ConfiguratorStepFactory::newObject($id_step);
            $options = $step->getOptions(Context::getContext()->language->id, true);
            foreach ($options as $option) {
                if ($option->id === $configurator_step_option->id) {
                    $configurator_step_option->position = $option->position;
                }
            }
            // /FIX

            if ($step->use_qty) {
                $default_qty = $this->getDefaultQty($configurator_step_option);
                $extras['selected'] = !isset($extras['qty']) ? (bool)($default_qty > 0) : $extras['selected'];
                $extras['qty'] = !isset($extras['qty']) ? $default_qty : $extras['qty'];
            }
            $extras = array_merge(self::$extra_options, $extras);
            $detail = $this->getDetail();

            if (Validate::isLoadedObject($configurator_step_option)
                && $this->optionCanBeAdded($configurator_step_option)
            ) {
                // If not multiple and not pricelist, reinit selected
                $configuratorStep = $this->getConfiguratorStepModel($id_step);
                if (!$extras['multiple']
                    && $erase_selection
                    && empty($configuratorStep->price_list)
                    && !$configuratorStep->use_input
                ) {
                    foreach ($detail[(int)$id_step]['options'] as &$detail_option) {
                        $detail_option['selected'] = false;
                    }
                }

                $valueIsInvalid = !is_string($extras['value']) && !is_numeric($extras['value'])
                    && $extras['value'] !== "" && !$this->checkMinMaxValue($extras['value'], $configurator_step_option);

                /*
                 *  Store old detail
                 */
                $oldDetail = $detail;

                $selected = $valueIsInvalid ? (int)$extras['selected'] : true;
                if ($configuratorStep->use_shared) {
                    $cart_details = self::getMultipleByIdConfiguratorAndIdCart(
                        $configuratorStep->id_configurator,
                        Context::getContext()->cart->id
                    );
                    foreach ($cart_details as $cart_detail) {
                        if ($cart_detail->added_in_cart && $cart_detail->id !== $this->id) {
                            $detail_in_cart = $cart_detail->getDetail();
                            if (isset($detail_in_cart[(int)$id_step]['options'][(int)$configurator_step_option->id])) {
                                $detail_in_cart_options = $detail_in_cart[(int)$id_step]['options'];
                                $detail_in_cart_option = $detail_in_cart_options[(int)$configurator_step_option->id];
                                $selected = $detail_in_cart_option['selected'];
                            }
                        }
                    }
                }

                if ($configurator_step_option->check_value) {
                    $selected = true;
                }

                $default_value = $configurator_step_option->getDefaultValue();
                $configurator_step_option->min_value = $this->getMinValue($configurator_step_option);
                $configurator_step_option->max_value = $this->getMaxValue($configurator_step_option);

                if ($dropzone_positions !== null && isset($detail[(int)$id_step]) && isset($detail[(int)$id_step]['dropzone'])) {
                    $dropzone = json_decode(json_encode($detail[(int)$id_step]['dropzone']), true);
                    foreach ($dropzone_positions as $position) {
                        if(isset($dropzone[$position['position']])) {
                            $dropzone[$position['position']]['option'] = $position['option'];
                        }
                    }
                    $detail[(int)$id_step]['dropzone'] = $dropzone;
                }

                $detail[(int)$id_step]['options'][(int)$configurator_step_option->id] = array(
                    'id' => (int)$configurator_step_option->id,
                    'id_option' => (int)$configurator_step_option->id_option,
                    'name' => $configurator_step_option->option['name'],
                    'selected_by_default' => $configurator_step_option->selected_by_default,
                    'check_default' => $configurator_step_option->check_value,
                    'position' => $configurator_step_option->position,

                    'min' => $configurator_step_option->min_value,
                    'max' => $configurator_step_option->max_value,
                    'default' => $default_value,
                    'weight' => (float)$configurator_step_option->weight,

                    // Pricelist value writted by user
                    'value' => ($valueIsInvalid ? $default_value : $extras['value']),
                    // If customer have writted a value, selected is true
                    'selected' => (int)($selected),
                    'display_amount' => $extras['display_amount'],
                    'display_reduc' => $extras['display_reduc'],
                    'total_price' => $extras['total_price'],
                    'qty' => (int)$extras['qty'] ? (int)$extras['qty'] : 1,
                    'reference' => $configurator_step_option->reference,
                    'reference_position' => $configurator_step_option->reference_position,

                    'layer' => $configurator_step_option->layer,
                    'layers' => $configurator_step_option->layers,
                    'used_for_dimension' => $configurator_step_option->used_for_dimension,
                    'dimension_coeff' => $configurator_step_option->dimension_coeff,
                    'qty_coeff' => $configurator_step_option->qty_coeff,
                );

                // CONFIGURATOR HOOK
                Hook::exec('configuratorActionCartDetailAddOption', array(
                    'cart_detail_model' => &$this,
                    'detail' => $detail,
                    'option' => &$detail[(int)$id_step]['options'][(int)$configurator_step_option->id],
                    'configurator_step_option' => $configurator_step_option,
                    'valueIsInvalid' => $valueIsInvalid,
                    'extras' => $extras,
                    'configuratorStep' => $configuratorStep
                ));

                /**
                 * check if selection is ok or not and set the corresponding details
                 * if selection is not ok throw an error
                 */
                if ($this->computeOptions($detail, $oldDetail, $configuratorStep, $extras)) {
                    $this->computeMaxQty($detail, $oldDetail, $configuratorStep);
                }

                // $this->save();
                $this->computeDivision($configurator_step_option, $configuratorStep);
            }
        }

        
        /**
         * @return int max_value
         */
        public function getMaxValue($configurator_step_option){
            $max_value = $configurator_step_option->max_value;
            $configuratorStep = $this->getConfiguratorStepModel($configurator_step_option->id_configurator_step);

            if(!empty($configuratorStep->price_list)) {
                $return = $configuratorStep->getMinMaxPriceList((int)$configurator_step_option->position+1);
                return $return['max'];
            }

            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                if ($configuratorStep !== null) {
                    $max_value = AdvancedformulaHelper::loadFormula(
                                    $this, $configuratorStep, $this->getDetail(), $max_value
                    );
                    if ($max_value === false && $configurator_step_option->max_value_if_null !== null) {
                        $max_value = $configurator_step_option->max_value_if_null;
                    }
                }
            }
            return $max_value;
        }
        
        /**
         * @return int min_value
         */
        public function getMinValue($configurator_step_option) {
            $min_value = $configurator_step_option->min_value;
            $configuratorStep = $this->getConfiguratorStepModel($configurator_step_option->id_configurator_step);

            if(!empty($configuratorStep->price_list)) {
                $return = $configuratorStep->getMinMaxPriceList((int)$configurator_step_option->position+1);
                return $return['min'];
            }

            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                if ($configuratorStep !== null) {
                    $min_value = AdvancedformulaHelper::loadFormula(
                                    $this, $configuratorStep, $this->getDetail(), $min_value
                    );
                    if ($min_value == false && $configurator_step_option->min_value_if_null !== null) {
                        $min_value = $configurator_step_option->min_value_if_null;
                    }
                }
            }
            return $min_value;
        }

        /**
         * @return int min_value
         */
        public function getDefaultQty($configurator_step_option) {
            $default_qty = $configurator_step_option->default_qty;
            $configuratorStep = $this->getConfiguratorStepModel($configurator_step_option->id_configurator_step);

            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                if ($configuratorStep !== null) {
                    $default_qty = AdvancedformulaHelper::loadFormula($this, $configuratorStep, $this->getDetail(), $default_qty);
                }
            }
            return (int)$default_qty;
        }

        /**
         * Permet de faire une répartition
         *
         * @param ConfiguratorStepOptionAbstract $configurator_step_option
         * @param ConfiguratorStepAbstract $configuratorStep
         */
        public function computeDivision(
            ConfiguratorStepOptionAbstract $configurator_step_option,
            ConfiguratorStepAbstract $configuratorStep
        )
        {
            $detail = $this->getDetail();

            $id_step = $configuratorStep->id;
            $existDivision = ConfiguratorStepOptionAbstract::getDivisionIdsByIdStepOption(
                (int)$configurator_step_option->id,
                (int)$configuratorStep->id_configurator
            );
            if (!empty($existDivision)) {
                foreach ($existDivision as $div) {
                    $option = &$detail[(int)$id_step]['options'][(int)$configurator_step_option->id];
                    $option['division_' . $div['id_configurator_step']]['counter_division_total'] = $div['counter'];
                    $option['division_' . $div['id_configurator_step']]['counter_division'] = 0;
                    $option['division_' . $div['id_configurator_step']]['value_division'] = 0;
                }
            }

            // Si l'étape est de type division il est nécessaire d'agir sur les options de cette étape
            if ($configurator_step_option->id_configurator_step_option_division) {
                $division = ConfiguratorStepOptionAbstract::getByIdStepOptionDivision(
                    (int)$configurator_step_option->id_configurator_step_option_division
                );

                /**
                 * Cette option est reliée à une répartition il faut effectuer les premiers calculs
                 */
                if (!empty($division)) {
                    $options = $detail[$division['id_configurator_step']]['options'];
                    $target_option = $options[$division['id_configurator_step_option']];

                    $target_option_division = $target_option['division_' . $configurator_step_option->id_configurator_step];
                    $current_option = $detail[(int)$id_step]['options'][(int)$configurator_step_option->id];

                    $target_option_division['counter_division']++;

                    if (isset($target_option['value']) &&
                        $target_option_division['counter_division'] >= $target_option_division['counter_division_total']
                    ) {
                        // C'est la dernière valeur on lui demande de répartir le reste de la division
                        $current_option['value'] = $target_option['value'] - $target_option_division['value_division'];
                    } elseif ($current_option['value'] > 0) {
                        // Supérieur et supérieur = grande question....
                        // Une valeur est définie par une saisie précédente pour l'option en cours
                        // valeur courante + valeur restante des divisions précédentes > valeur cible
                        $value_tmp = $current_option['value'] + $target_option_division['value_division'];
                        if ($value_tmp >= $target_option['value']) {
                            // Valeur courante = valeur cible - restant à répartir
                            $current_option['value'] = $target_option['value']
                                - $target_option_division['value_division'];
                        } elseif ($current_option['min'] == $current_option['max']
                            && $current_option['min'] != '0'
                            && $current_option['max'] != '0'
                            && $current_option['min'] != ''
                            && $current_option['max'] != ''
                        ) {
                            // Cette valeur ne pourra jamais changer car elle est fixe on force le système
                            $current_option['value'] = $current_option['min'];
                        }
                    } else {
                        if ($target_option['value']) {
                            // Une valeur sur lka cible est définie il fait donc répartir avec les valeurs correctes
                            // On travaille sur la division entière
                            // Le reste de la division sera pris dans la dernière étape
                            $current_option['value'] = floor(
                                $target_option['value'] / $target_option_division['counter_division_total']
                            );
                        } else {
                            // Ce n'est pas la dernière valeur, la valeur courante de cette option n'est pas définie
                            // la valeur cible n'a pas de valeur
                            $current_option['value'] = '';
                        }
                    }

                    if (!$current_option['value']) {
                        $current_option['value'] = '0';
                    }

                    $target_option_division['value_division'] += $current_option['value'];

                    $target_option['division_' . $configurator_step_option->id_configurator_step] = $target_option_division;
                    $detail[$division['id_configurator_step']]['options'][$division['id_configurator_step_option']] = $target_option;
                    $detail[(int)$id_step]['options'][(int)$configurator_step_option->id] = $current_option;
                }
            }
            // END DIVISION

            $this->setDetail($detail);
        }

        /**
         * Remove a option from configuration
         * @param int $value Option id
         * @param boolean $remove unset option or just unselect it
         */
        public function removeOption($value, $remove = true, $extra = array())
        {
            $detail = $this->getDetail();
            $oldDetail = $detail;
            foreach ($detail as &$step_detail) {
                foreach ($step_detail['options'] as &$option_detail) {
                    if (((int)$value === (int)$option_detail['id'])) {
                        // DropZone - Viewer2D
                        if(isset($step_detail['dropzone'])) {
                            $dropzone = json_decode(json_encode($step_detail['dropzone']), true);
                            foreach ($dropzone as $key => $position) {
                                /**
                                 * @todo: refactor me: Explode pas propre
                                 */
                                if (isset($position['option']) && $position['option'] !== '') {
                                    $id_option_in_dropzone = explode("_", $position['option'])[3];
                                    if ((int)$value === (int)$id_option_in_dropzone) {
                                        $step_detail['dropzone'][$key]['option'] = '';
                                    }
                                }
                            }
                        }

                        if ($remove) {
                            unset($step_detail['options'][$option_detail['id']]);
                        } elseif (isset($option_detail['value'])) {
                            $option_detail['value'] = false;
                            $option_detail['selected'] = false;
                        } elseif ($option_detail['selected']) {
                            $option_detail['selected'] = false;
                        }
                        if ($option_detail['check_default'] === "1") {
                            $option_detail['selected'] = true;
                        }
                        $this->setDetail($detail);
                        //$this->save();

                        $configuratorStep = $this->getConfiguratorStepModel($step_detail['id']);
                        if ($this->computeOptions(
                            $detail,
                            $oldDetail,
                            $configuratorStep,
                            array_merge(self::$extra_options, $extra)
                        )) {
                            $this->computeMaxQty($detail, $oldDetail, $configuratorStep);
                        }

                        break 2;
                    }
                }
            }
        }

        /**
         * Update qty wanted to an option
         * @param int $id
         * @param int $qty
         */
        public function updateOptionQty($id, $qty)
        {
            $detail = $this->getDetail();
            foreach ($detail as &$step_detail) {
                foreach ($step_detail['options'] as &$option_detail) {
                    if (((int)$id === (int)$option_detail['id'])) {
                        $option_detail['qty'] = (int)$qty;
                        $this->setDetail($detail);
                        //$this->save();
                        break 2;
                    }
                }
            }
        }

        public function deleteAttachements()
        {
            $attachments = ConfiguratorAttachment::getAttachments((int)$this->id);
            foreach ($attachments as $a) {
                $attachment = new ConfiguratorAttachment((int)$a['id_configurator_attachment']);
                $attachment->delete();
            }
        }

        public function getNumberOfAttachments($id_step)
        {
            $query = new DbQuery();
            $query->select('count(*)')
                ->from('configurator_cartdetail_attachment')
                ->where('id_configurator_cart_detail = ' . (int)$this->id)
                ->where('id_step = ' . (int)$id_step);

            return (int)Db::getInstance()->getValue($query);
        }

        public function getAttachments($id_step)
        {
            return ConfiguratorAttachment::getAttachments((int)$this->id, (int)$id_step);
        }

        public function getAttachmentsWithLink($id_step)
        {
            $attachments = $this->getAttachments((int)$id_step);

            foreach ($attachments as &$a) {
                $a['link'] = Context::getContext()->link->getModuleLink(
                    'configurator',
                    'attachment',
                    array('token' => $a['token'])
                );
            }

            return $attachments;
        }

        public function createNewProduct(Product $product, $duplicate_images = true)
        {
            /**
             * Duplicate :
             * - Product
             * - Specific prices
             * - Images
             * - Customization
             */
            /* @var $new_product ProductCore */
            /*
            $new_product = $product->duplicateObject();
            Product::duplicateSpecificPrices((int)$product->id, (int)$new_product->id);
            Product::duplicateCustomizationFields((int)$product->id, (int)$new_product->id);
            if ($duplicate_images) {
                Image::duplicateProductImages($product->id, $new_product->id, null);
            }

            $carriers = $product->getCarriers();
            $new_carriers = array();
            foreach ($carriers as $c) {
                $new_carriers[] = $c['id_reference'];
            }
            $new_product->setCarriers($new_carriers);

            $new_product->visibility = 'none';
            $new_product->id_tax_rules_group = (int)$product->id_tax_rules_group;
            $new_product->price = (float)$product->price;
            $new_product->id_category_default = (int)$product->id_category_default;
            $new_product->active = 1;
            $new_product->show_price = 1;
            $new_product->quantity = (int)self::PRODUCT_CART_DETAIL_MAX_QTY;
            $new_product->available_for_order = 1;
            $new_product->customizable = (int)$product->customizable;
            $new_product->text_fields = (int)$product->text_fields;
            $new_product->uploadable_files = (int)$product->uploadable_files;
            $new_product->id_manufacturer = (int)$product->id_manufacturer;
            $new_product->save();
            // Link to parent category
            $new_product->updateCategories($product->getCategories());
            // Specify that this product is special
            $sql = 'UPDATE `'._DB_PREFIX_.'product` SET is_configurated = 1
                WHERE id_product = \''.(int)$new_product->id.'\'';
            Db::getInstance()->execute($sql);
            // Create new attribute used for display the configuration when order the product
            $attribute = new ConfiguratorAttribute();
            $attribute->id_option_group = (int)Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID');
            $languages = Language::getLanguages();

            foreach ($languages as $language) {
                $attribute->name[(int)$language['id_lang']] = $this->attribute_key;
            }

            $attribute->save();
            // Set combination and attribute
            $id_product_attribute = $new_product->addCombinationEntity(
                0,
                0,
                0,
                0,
                0,
                0,
                array(),
                $new_product->reference,
                null,
                $new_product->ean13,
                1,
                $new_product->location,
                $new_product->upc,
                1,
                array(),
                null
            );
            StockAvailable::setProductDependsOnStock(
                (int)$new_product->id,
                $new_product->depends_on_stock,
                null,
                (int)$id_product_attribute
            );
            StockAvailable::setProductOutOfStock(
                (int)$new_product->id,
                $new_product->out_of_stock,
                null,
                (int)$id_product_attribute
            );
            $combination = new Combination((int)$id_product_attribute);
            $combination->setAttributes(array((int)$attribute->id));
            // Set quantity
            StockAvailable::setQuantity(
                $new_product->id,
                (int)$id_product_attribute,
                (int)self::PRODUCT_CART_DETAIL_MAX_QTY
            );
            // Set properties
            $this->id_product = (int)$new_product->id;
            $this->product = $new_product;*/

            $this->id_product = (int)$product->id;
            $this->product = $product;
        }

        public static function getByIdConfiguratorAndIdCart($id_configurator, $id_cart, $is_in_cart = false)
        {
            $key = 'ConfiguratorCartDetailModel::getByIdConfiguratorAndIdCart-' . (int)$id_configurator
                . '-' . (int)$id_cart . '-' . (int)$is_in_cart;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                        WHERE ccd.id_configurator=' . (int)$id_configurator . '
                        AND ccd.id_cart = ' . (int)$id_cart;

                if (!$is_in_cart) {
                    $sql .= ' AND ccd.added_in_cart = 0';
                }
                $result = Db::getInstance()->getRow($sql);
                $configurator_cart_detail = new ConfiguratorCartDetailModel();
                if (!empty($result)) {
                    $configurator_cart_detail->hydrate($result);
                }

                DmCache::getInstance()->store($key, $configurator_cart_detail);
                return $configurator_cart_detail;
            }
        }

        public static function getByIdConfiguratorAndIdGuest($idConfigurator, $idGuest)
        {
            $key = 'ConfiguratorCartDetailModel::getByIdConfiguratorAndIdGuest-' . (int)$idConfigurator . '-' . (int)$idGuest;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_configurator=' . (int)$idConfigurator . '
                    AND ccd.id_guest = ' . (int)$idGuest . ' 
                    AND ccd.added_in_cart = 0';
                $result = Db::getInstance()->getRow($sql);
                $configuratorCartDetail = new ConfiguratorCartDetailModel();
                if (!empty($result)) {
                    $configuratorCartDetail->hydrate($result);
                }

                DmCache::getInstance()->store($key, $configuratorCartDetail);
                return $configuratorCartDetail;
            }
        }

        public static function getMultipleByIdConfiguratorAndIdCart($id_configurator, $id_cart)
        {
            $key = 'ConfiguratorCartDetailModel::getMultipleByIdConfiguratorAndIdCart-' . (int)$id_configurator
                . '-' . (int)$id_cart;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT ccd.* FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                        LEFT JOIN `' . _DB_PREFIX_ . 'customization` c ON ccd.id_customization = c.id_customization
                        WHERE ccd.id_configurator=' . (int)$id_configurator . '
                        AND ccd.id_cart = ' . (int)$id_cart . '
                        AND ccd.added_in_cart = 1
                        AND c.id_customization IS NOT NULL';

                $results = Db::getInstance()->executeS($sql);
                $configurator_cart_detail = new ConfiguratorCartDetailModel();
                $configurator_cart_details = $configurator_cart_detail->hydrateCollection(
                    get_class(),
                    $results,
                    (int)Context::getContext()->language->id
                );

                DmCache::getInstance()->store($key, $configurator_cart_details);
                return $configurator_cart_details;
            }
        }

        public static function getByIdProduct($id_product)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_product = ' . (int)$id_product . '
                    AND ccd.added_in_cart = 1';
            $result = Db::getInstance()->getRow($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            if (!empty($result)) {
                $configurator_cart_detail->hydrate($result);
            }

            return $configurator_cart_detail;
        }

        public static function getByIdCartAndIdProduct($id_cart, $id_product, $added_in_cart_only = true)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_cart = ' . (int)$id_cart . '
                    AND ccd.id_product = ' . (int)$id_product . '
                    AND ccd.id_order = 0';
            if ($added_in_cart_only) {
                $sql .= ' AND ccd.added_in_cart = 1';
            }
            $result = Db::getInstance()->getRow($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            if (!empty($result)) {
                $configurator_cart_detail->hydrate($result);
            }

            return $configurator_cart_detail;
        }

        public static function getByOrderDetail($id_order_detail)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_order_detail = ' . (int)$id_order_detail;
            $result = Db::getInstance()->getRow($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            if (!empty($result)) {
                $configurator_cart_detail->hydrate($result);
            }

            return $configurator_cart_detail;
        }

        public static function getByIdCartAndIdProductAndIdCustomization($id_cart, $id_product, $id_customization)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_cart = ' . (int)$id_cart . '
                    AND ccd.id_product = ' . (int)$id_product . '
                    AND ccd.id_customization = ' . (int)$id_customization . '
                    AND ccd.id_order = 0
                    AND ccd.added_in_cart = 1';
            $results = Db::getInstance()->executeS($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            return $configurator_cart_detail->hydrateCollection(
                get_class(),
                $results,
                (int)Context::getContext()->language->id
            );
        }

        public static function getByIdProductAndIdCustomization($id_product, $id_customization)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_product = ' . (int)$id_product . '
                    AND ccd.id_customization = ' . (int)$id_customization . '
                    AND ccd.id_order = 0
                    AND ccd.added_in_cart = 1';
            $results = Db::getInstance()->executeS($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            return $configurator_cart_detail->hydrateCollection(
                get_class(),
                $results,
                (int)Context::getContext()->language->id
            );
        }

        public static function getOneByIdProductAndIdCustomization($id_product, $id_customization)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_product = ' . (int)$id_product . '
                    AND ccd.id_customization = ' . (int)$id_customization;
            $result = Db::getInstance()->getRow($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            if (!empty($result)) {
                $configurator_cart_detail->hydrate($result);
            }
            return $configurator_cart_detail;
        }

        public static function getMultipleByIdCartAndIdProduct($id_cart, $id_product, $added_in_cart_only = true)
        {
            $key = 'ConfiguratorCartDetailModel::getMultipleByIdCartAndIdProduct-' . $id_cart
                . '-' . $id_product . '-' . ($added_in_cart_only ? 1 : 0);
            if (DmCache::getInstance()->isStored($key)) {
                $return = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT ccd.* FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                        LEFT JOIN `' . _DB_PREFIX_ . 'customization` c ON ccd.id_customization = c.id_customization
						WHERE ccd.id_cart = ' . (int)$id_cart . '
						AND ccd.id_product = ' . (int)$id_product . '
						AND ccd.id_order = 0
						AND c.id_customization IS NOT NULL';
                if ($added_in_cart_only) {
                    $sql .= ' AND ccd.added_in_cart = 1';
                }
                $results = Db::getInstance()->executeS($sql);
                $configurator_cart_detail = new ConfiguratorCartDetailModel();
                $return = $configurator_cart_detail->hydrateCollection(
                    get_class(),
                    $results,
                    (int)Context::getContext()->language->id
                );
                DmCache::getInstance()->store($key, $return);
            }

            return $return;
        }

        public static function getProductsCartInformations()
        {
            $cart = Context::getContext()->cart;
            $cartDetails = array();
            $links = array();
            $customizations = array();
            if (Validate::isLoadedObject($cart)) {
                $products = array();
                foreach ($cart->getProducts() as $product) {
                    $products[$product['id_product']] = $product;
                }
                $cartDetails = ConfiguratorCartDetailModel::getByIdCart((int)$cart->id);
                if ($cartDetails) {
                    foreach ($cartDetails as &$cartDetail) {
                        // FIX : problème avec les double quotes
                        $cartDetail->detail = json_decode($cartDetail->detail);

                        if (isset($products[$cartDetail->id_product])) {
                            $customization = new Customization($cartDetail->id_customization);
                            $price = $cartDetail->getPriceInCart($cart->id, true, $customization->quantity);
                            $price_no_reduc = $cartDetail->getPriceInCart($cart->id, false, $customization->quantity);
                            if ($price === $price_no_reduc) {
                                $unit_price = Tools::displayPrice($price);
                            } else {
                                $unit_price = '<ul class="price text-right"><li class="price special-price">'
                                    . Tools::displayPrice($price) . '</li><li class="old-price">'
                                    . Tools::displayPrice($price_no_reduc) . '</li></ul>';
                            }
                            $customizations[] = array(
                                'id_product' => $cartDetail->id_product,
                                'id_product_attribute' => $customization->id_product_attribute,
                                'id_customization' => $cartDetail->id_customization,
                                'quantity' => $customization->quantity,
                                'unit_price' => $unit_price,
                                'total_price' => Tools::displayPrice($price * $customization->quantity),
                                'id_cart_detail' => $cartDetail->id
                            );
                            $links[$cartDetail->id_customization] =
                                Context::getContext()->link->getProductLink($cartDetail->id_product)
                                . '?configurator_update=' . $cartDetail->id;
                        }
                    }
                }
            }

            $cart_detail_tmp = new ConfiguratorCartDetailModel();

            return array(
                'cartDetails' => $cartDetails,
                'configurator_update_urls' => $links,
                'configurator_update_label' => $cart_detail_tmp->l('Update my personalization'),
                'prestashopVersion' => (int)DMTools::getVersionMajor(),
                'customizations' => $customizations
            );
        }

        public static function getByIdCart($id_cart)
        {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ccd
                    WHERE ccd.id_cart = ' . (int)$id_cart . '
                    AND ccd.added_in_cart = 1';
            $results = Db::getInstance()->executeS($sql);
            $configurator_cart_detail = new ConfiguratorCartDetailModel();
            return $configurator_cart_detail->hydrateCollection(
                get_class(),
                $results,
                (int)Context::getContext()->language->id
            );
        }

        public static function deleteAllProductCartDetail()
        {
            $sql = 'SELECT p.id_product FROM `' . _DB_PREFIX_ . 'product` p 
                    WHERE p.is_configurated=1';
            $results = Db::getInstance()->executeS($sql);
            foreach ($results as $result) {
                $product = new Product((int)$result['id_product']);
                $product->delete();
            }
        }

        public static function resetSharedStepInCartByIdProduct($id_product = 0)
        {
            $configuratorCarts = self::getMultipleByIdCartAndIdProduct(Context::getContext()->cart->id, $id_product);
            foreach ($configuratorCarts as $configuratorCart) {
                Context::getContext()->cart->deleteProduct(
                    $configuratorCart->id_product,
                    $configuratorCart->id_product_attribute,
                    $configuratorCart->id_customization
                );
                $configuratorCart->delete();
            }
        }

        /*
         * Get price option with tax if enable
         * @return float price of option
         */
        public static function getPriceOption($price, $id_product)
        {
            return DMTools::getPrice($price, $id_product);
        }

        public function checkMinMaxValue($value, $configurator_step_option)
        {
            /**
             * Cas des choix multiples avec quantité
             */
            if ($value === false) {
                return false;
            }

            if ($configurator_step_option->min_value != "" && !is_bool($value)) {
                if ((int)$value <= (int)$configurator_step_option->min_value) {
                    return false;
                }
            }
            if ($configurator_step_option->max_value != "" && !is_bool($value)) {
                if ((int)$value >= $configurator_step_option->max_value) {
                    return false;
                }
            }
            return true;
        }

        /*
         * Check the number of options selected
         * 
         * @param ConfiguratorStepAbstract $configuratorStep
         * @param array $detail
         * @return int
         */
        public static function checkOptionSelected(ConfiguratorStepAbstract $step, $detail)
        {
            $count = 0;
            if (isset($detail[(int)$step->id]['options'])) {
                foreach ($detail[(int)$step->id]['options'] as $detail_option) {
                    if ((bool)$detail_option['selected']) {
                        $count++;
                    }
                }
            }
            return (int)$count;
        }

        /**
         * Check if the number of selected options is not greater than max_options
         *
         * @param ConfiguratorStepAbstract $configuratorStep
         * @param array $detail
         * @return boolean
         */
        public function checkMaxOptionsSelected($count, $step)
        {
            if ($step->max_options == 0 || $count <= $step->max_options) {
                return true;
            }
            return false;
        }

        /**
         * Set current details if new details are ok
         * if new details are not ok set old details and throw an error
         *
         * @param array $detail
         * @param array $oldDetail
         * @param ConfiguratorStepAbstract $configuratorStep
         * @param type $extras
         * @return boolean
         */
        public function computeOptions($detail, $oldDetail, ConfiguratorStepAbstract $configuratorStep, $extras)
        {
            /**
             *  if multiple options is not selected, always setDetail to $detail and return true
             */
            if ($extras['multiple']) {
                $count = $this->checkOptionSelected($configuratorStep, $detail);

                if ($count > 0 && $configuratorStep->min_options > $count) {
                    $this->steps_errors[$configuratorStep->id] = array(
                        'key' => Configurator::ERROR_MINOPTIONS_REACHED,
                        'args' => $configuratorStep->min_options
                    );
                }
                if (!$this->checkMaxOptionsSelected($count, $configuratorStep)) {
                    $this->setDetail($oldDetail);
                    $this->steps_errors[$configuratorStep->id] = array(
                        'key' => Configurator::ERROR_MAXOPTIONS_REACHED,
                        'args' => $configuratorStep->max_options
                    );
                    return false;
                }
            }
            //$this->setDetail($detail);
            return true;
        }

        /**
         * @param ConfiguratorStepAbstract $step
         * @param array $detail
         * @return int
         */
        public function getCurrentQty(ConfiguratorStepAbstract $step, $detail)
        {
            $count = 0;
            if (isset($detail[(int)$step->id])) {
                foreach ($detail[(int)$step->id]['options'] as $detail_option) {
                    if ((bool)$detail_option['selected']) {
                        $qty_coeff = (isset($detail_option['qty_coeff']) && (int)$detail_option['qty_coeff'] !== 0) ? (int)$detail_option['qty_coeff'] : 1;
                        $count += $detail_option['qty'] * $qty_coeff;
                    }
                }
            }

            return $count;
        }

        /**
         *
         * @param ConfiguratorStepAbstract $step
         * @param array $detail
         * @return boolean
         */
        public function checkMinCurrentQty(ConfiguratorStepAbstract $step, $detail)
        {
            if ((bool)$step->use_qty) {
                $step_qty = (int)$this->getCurrentQty($step, $detail);
                return !((int)$step_qty >= 1 && (int)$step->getMinQty($this) > $step_qty);
            }

            return true;
        }

        /**
         *
         * @param ConfiguratorStepAbstract $step
         * @param array $detail
         * @return boolean
         */
        public function checkMaxCurrentQty(ConfiguratorStepAbstract $step, $detail)
        {
            $count = $this->getCurrentQty($step, $detail);
            $max_qty = $step->getMaxQty($this);

            // A partir du $id_configurator_step_option retrouver $id_step + $id_option
            if ($step->max_qty_step_option_id !== 0) {
                $configurator_step_option = ConfiguratorStepOptionFactory::newObject(
                    (int)$step->max_qty_step_option_id
                );
                if (Validate::isLoadedObject($configurator_step_option)) {
                    $id_step = $configurator_step_option->id_configurator_step;
                    $id_option = $configurator_step_option->id;

                    $max_qty = (isset($detail[(int)$id_step]['options'][(int)$id_option])
                        && (bool)$detail[(int)$id_step]['options'][(int)$id_option]['selected'])
                        ? (int)$detail[(int)$id_step]['options'][(int)$id_option]['value']
                        : 0;

                    if ($step->max_qty_coef !== 0) {
                        $max_qty *= $step->max_qty_coef;
                    }

                    $step->max_qty = $max_qty;
                }
            }

            if ($max_qty == 0 || $count == 0 || $count <= $max_qty) {
                return true;
            }

            return false;
        }

        public function checkStepCurrentQty(ConfiguratorStepAbstract $step, $detail)
        {
            $count = (int)$this->getCurrentQty($step, $detail);
            $step_qty = (int)$step->step_qty;

            if ($step_qty === 0 || $count === 0 || ($count % $step_qty === 0)) {
                return true;
            }

            return false;
        }

        /**
         *
         * @param array $detail
         * @param array $oldDetail
         * @param ConfiguratorStepAbstract $configuratorStep
         * @return boolean
         */
        public function computeMaxQty($detail, $oldDetail, ConfiguratorStepAbstract $configuratorStep)
        {
            if (!$this->checkMaxCurrentQty($configuratorStep, $detail)) {
                $this->setDetail($oldDetail);
                $max_qty = $configuratorStep->getMaxQty($this);
                $this->steps_errors[$configuratorStep->id] = array(
                    'key' => Configurator::ERROR_MAXQTY_REACHED,
                    'args' => ($max_qty < 0) ? 0 : $max_qty
                );
                return false;
            }
            $this->setDetail($detail);
            return true;
        }

        public static function getTotalWeight($id_cart)
        {
            if ($id_cart) {
                $query = new DbQuery();
                $query->select('SUM(ccd.weight * c.quantity)')
                    ->from('configurator_cart_detail', 'ccd')
                    ->join('LEFT JOIN ' . _DB_PREFIX_ . 'customization c ON ccd.id_customization = c.id_customization')
                    ->where('ccd.id_cart = ' . (int)$id_cart)
                    ->where('ccd.added_in_cart = 1')
                    ->where('c.id_customization IS NOT NULL');

                return (float)Db::getInstance()->getValue($query);
            }
            return 0.00;
        }

        public function getPriceInCart($id_cart, $use_reduc = true, $qty = 1, $tax = null)
        {
            // Precision
            $priceDisplayPrecision = _PS_PRICE_DISPLAY_PRECISION_;
            if (!isset($priceDisplayPrecision)) {
                $priceDisplayPrecision = 2;
            }
            // Use tax
            if (is_null($tax)) {
                $priceDisplay = Product::getTaxCalculationMethod((int)Context::getContext()->cookie->id_customer);
                if (!$priceDisplay || $priceDisplay == 2) {
                    $tax = true;
                } else {
                    if ($priceDisplay == 1) {
                        $tax = false;
                    }
                }
            }
            // Price
            $specific_price_output = null;
            Product::setCustomizationId($this->id_customization);
            $price = Product::getPriceStatic(
                (int)$this->id_product,
                $tax,
                null,
                $priceDisplayPrecision,
                null,
                false,
                $use_reduc,
                $qty,
                false,
                null,
                (int)$id_cart,
                null,
                $specific_price_output,
                true,
                true,
                null,
                true,
                $this->id_customization
            );
            Product::setCustomizationId(null);
            return $price;
        }

        public static function deleteByIdCart($id_cart = 0)
        {
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configurator_cart_detail`';
            $sql .= ' WHERE id_cart = ' . (int)$id_cart;
            Db::getInstance()->execute($sql);
        }

        // Get custom height, width, depth

        /**
         * @param Cart $cart
         * @param Product $product
         * @return float|int
         */
        public static function getProductWidth(Cart $cart, Product $product)
        {
            return self::getProductDimension('width', $cart, $product);
        }

        /**
         * @param Cart $cart
         * @param Product $product
         * @return float|int
         */
        public static function getProductHeight(Cart $cart, Product $product)
        {
            return self::getProductDimension('height', $cart, $product);
        }

        /**
         * @param Cart $cart
         * @param Product $product
         * @return float|int
         */
        public static function getProductDepth(Cart $cart, Product $product)
        {
            return self::getProductDimension('depth', $cart, $product);
        }

        /**
         * @param string $type
         * @param $id_cart
         * @param $id_product
         * @return float|int
         */
        public static function getProductDimensionWithId($type = '', $id_cart, $id_product) {
            $cart = new Cart((int)$id_cart);
            $product = new Product((int)$id_product);

            return self::getProductDimension($type, $cart, $product);
        }

        /**
         * @param string $type
         * @param Cart|null $cart
         * @param Product|null $product
         * @return float|int
         */
        public static function getProductDimension($type = '', Cart $cart = null, Product $product = null)
        {
            $carts_detail = self::getMultipleByIdCartAndIdProduct($cart->id, $product->id, true);
            $dim = 0;
            $coeff = 1;

            // Find option override first
            foreach ($carts_detail as $cart_detail) {
                foreach (json_decode($cart_detail->detail, true) as $step) {
                    foreach ($step['options'] as $option) {
                        if ($option['used_for_dimension'] === $type) {
                            $dim = ($dim < (int)$option['value']) ? (int)$option['value'] : $dim;
                            if ($dim > 0
                                && isset($option['dimension_coeff'])
                                && (float)$option['dimension_coeff'] !== 0.0
                            ) {
                                $coeff = (float)$option['dimension_coeff'];
                            }
                        }
                    }
                }
            }

            // If not option override, find step override
            if ($dim === 0) {
                foreach ($carts_detail as $cart_detail) {
                    foreach (json_decode($cart_detail->detail, true) as $step) {
                        if (isset($step['dimension_' . $type])) {
                            $dim = ($dim < (int)$step['dimension_' . $type]) ? (int)$step['dimension_' . $type] : $dim;
                        }
                    }
                }
            }

            return (float)$dim * $coeff;
        }

        public static function getReferenceByIdCustomization($id_product = 0, $id_customization = 0, $default_reference = null) {
            if (ConfiguratorModel::productHasConfigurator($id_product)) {
                $cartDetailModel = self::getByIdProductAndIdCustomization($id_product, $id_customization);
                if (isset($cartDetailModel[0]) && Validate::isLoadedObject($cartDetailModel[0]) && $cartDetailModel[0]->reference) {
                    return $cartDetailModel[0]->reference;
                }
            }
            return $default_reference;
        }

        public function getProductName()
        {
            $configurator = new ConfiguratorModel($this->id_configurator);
            if (Validate::isLoadedObject($configurator)) {
                $product = new Product($configurator->id_product);
                if (Validate::isLoadedObject($product) && isset($product->name[Context::getContext()->language->id])) {
                    return $product->name[Context::getContext()->language->id];
                }
            }
            return '-';
        }
    }
}
