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

if (!class_exists('ConfiguratorStepFilterTypeFeatureModel')) {
    require_once(dirname(__FILE__) . '/ConfiguratorStepFilterAbstract.php');

    /**
     * Class ConfiguratorStepFilterTypeFeatureModel
     */
    class ConfiguratorStepFilterTypeFeatureModel extends ConfiguratorStepFilterAbstract
    {

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
        }

        public function getOption($lang_id)
        {
            return new Feature($this->id_option);
        }

        public function isValid($id_configurator_step_option, $cart_detail)
        {
            switch ($this->operator) {
                case self::TYPE_OPERATOR_EQUAL:
                case self::TYPE_OPERATOR_CONTAINS:
                case self::TYPE_OPERATOR_CONTAINED:
                case self::TYPE_OPERATOR_UPPER:
                case self::TYPE_OPERATOR_UPPER_OR_EQUAL:
                case self::TYPE_OPERATOR_LOWER:
                case self::TYPE_OPERATOR_LOWER_OR_EQUAL:
                case self::TYPE_OPERATOR_EQUAL_NUMBER:
                case self::TYPE_OPERATOR_UPPER_NUMBER:
                case self::TYPE_OPERATOR_UPPER_OR_EQUAL_NUMBER:
                case self::TYPE_OPERATOR_LOWER_NUMBER:
                case self::TYPE_OPERATOR_LOWER_OR_EQUAL_NUMBER:
                    $option_selected = $this->findCartDetailOptionSelected($cart_detail);
                    return $this->isValidFeatures($id_configurator_step_option, $option_selected);
                case self::TYPE_OPERATOR_CONTAINS_AT_LEAST:
                    $options_selected = $this->findCartDetailOptionsSelected($cart_detail);
                    return $this->isValidFeaturesMultiple($id_configurator_step_option, $options_selected);
                case self::TYPE_OPERATOR_EQUAL_FORMULA:
                case self::TYPE_OPERATOR_UPPER_FORMULA:
                case self::TYPE_OPERATOR_UPPER_OR_EQUAL_FORMULA:
                case self::TYPE_OPERATOR_LOWER_FORMULA:
                case self::TYPE_OPERATOR_LOWER_OR_EQUAL_FORMULA:
                    $option_selected = $this->findCartDetailOptionSelected($cart_detail);
                    return $this->isValidFormula($id_configurator_step_option, $option_selected, $cart_detail);
            }

            return false;
        }

        private function isValidFeatures($id_configurator_step_option, $option_selected)
        {
            if (!$option_selected) {
                return false;
            }

            $features = $this->getFeaturesByIdStepOption($id_configurator_step_option);
            $features_target = $this->getFeaturesByIdStepOption($option_selected->id);

            $step_selected = ConfiguratorStepFactory::newObject($option_selected->id_configurator_step);
            if ($step_selected->type !== ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS) {
                $this->id_target_option = $step_selected->id_option_group;
            }

            foreach ($features_target as $feature_target) {
                if ((int)$feature_target['id_group'] === (int)$this->id_target_option) {
                    foreach ($features as $feature) {
                        if ((int)$feature['id_group'] === (int)$this->id_option) {
                            if ($this->type_value === self::TYPE_VALUE_ID) {
                                $val_1 = $feature['id_option'];
                                $val_2 = $feature_target['id_option'];
                            } elseif ($this->type_value === self::TYPE_VALUE_NAME) {
                                $val_1 = $feature['name'];
                                $val_2 = $feature_target['name'];
                            }

                            switch ($this->operator) {
                                case self::TYPE_OPERATOR_EQUAL:
                                    return (bool)($val_1 === $val_2);
                                case self::TYPE_OPERATOR_CONTAINS:
                                    $pos = strpos(Tools::strtolower($val_1), Tools::strtolower($val_2));
                                case self::TYPE_OPERATOR_CONTAINS_AT_LEAST:
                                    $pos = strpos(Tools::strtolower($val_1), Tools::strtolower($val_2));
                                    return (bool)($pos !== false);
                                case self::TYPE_OPERATOR_CONTAINED:
                                    $pos = strpos(Tools::strtolower($val_2), Tools::strtolower($val_1));
                                    return (bool)($pos !== false);
                                case self::TYPE_OPERATOR_UPPER:
                                    return (bool)($val_1 > $val_2);
                                case self::TYPE_OPERATOR_UPPER_OR_EQUAL:
                                    return (bool)($val_1 >= $val_2);
                                case self::TYPE_OPERATOR_LOWER:
                                    return (bool)($val_1 < $val_2);
                                case self::TYPE_OPERATOR_LOWER_OR_EQUAL:
                                    return (bool)($val_1 <= $val_2);
                                case self::TYPE_OPERATOR_EQUAL_NUMBER:
                                    return (bool)((float)$val_1 === (float)$val_2);
                                case self::TYPE_OPERATOR_UPPER_NUMBER:
                                    return (bool)((float)$val_1 > (float)$val_2);
                                case self::TYPE_OPERATOR_UPPER_OR_EQUAL_NUMBER:
                                    return (bool)((float)$val_1 >= (float)$val_2);
                                case self::TYPE_OPERATOR_LOWER_NUMBER:
                                    return (bool)((float)$val_1 < (float)$val_2);
                                case self::TYPE_OPERATOR_LOWER_OR_EQUAL_NUMBER:
                                    return (bool)((float)$val_1 <= (float)$val_2);
                            }
                        }
                    }
                }
            }

            return false;
        }

        private function isValidFeaturesMultiple($id_configurator_step_option, $options_selected)
        {
            if (empty($options_selected)) {
                return false;
            }

            $features = $this->getFeaturesByIdStepOption($id_configurator_step_option);
            $features_targets = [];
            foreach ($options_selected as $option_selected) {
                $features_targets[] = $this->getFeaturesByIdStepOption($option_selected->id);
            }

            $step_selected = ConfiguratorStepFactory::newObject($option_selected->id_configurator_step);
            if ($step_selected->type !== ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS) {
                $this->id_target_option = $step_selected->id_option_group;
            }

            foreach ($features_targets as $features_target) {
                foreach ($features_target as $feature_target) {
                    if ((int)$feature_target['id_group'] === (int)$this->id_target_option) {
                        foreach ($features as $feature) {
                            if ((int)$feature['id_group'] === (int)$this->id_option) {
                                if ($this->type_value === self::TYPE_VALUE_ID) {
                                    $val_1 = $feature['id_option'];
                                    $val_2 = $feature_target['id_option'];
                                } elseif ($this->type_value === self::TYPE_VALUE_NAME) {
                                    $val_1 = $feature['name'];
                                    $val_2 = $feature_target['name'];
                                }

                                switch ($this->operator) {
                                    case self::TYPE_OPERATOR_CONTAINS_AT_LEAST:
                                        $pos = strpos(Tools::strtolower($val_1), Tools::strtolower($val_2));
                                        if ($pos !== false) {
                                            return true;
                                        }
                                }
                            }
                        }
                    }
                }
            }

            return false;
        }

        private function isValidFormula($id_configurator_step_option, $option_selected, $cart_detail)
        {
            if (!$option_selected) {
                return false;
            }

            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                require_once(dirname(__FILE__) . '/../../../dm_advancedformula/classes/helper/AdvancedformulaHelper.php');
                $step = ConfiguratorStepFactory::newObject($option_selected->id_configurator_step);
                // This is a link with the module "dm_advancedformula"
                // We check before this module is installed
                (float)$value_formula = AdvancedformulaHelper::loadFormula(
                    $cart_detail,
                    $step,
                    $cart_detail->getDetail(),
                    $this->formula
                );

                $features = $this->getFeaturesByIdStepOption($id_configurator_step_option);
                foreach ($features as $feature) {
                    if ((int)$feature['id_group'] === (int)$this->id_option) {
                        $value = (float)$feature['name'];
                        switch ($this->operator) {
                            case self::TYPE_OPERATOR_EQUAL_FORMULA:
                                return (bool)($value = $value_formula);
                            case self::TYPE_OPERATOR_UPPER_FORMULA:
                                return (bool)($value > $value_formula);
                            case self::TYPE_OPERATOR_UPPER_OR_EQUAL_FORMULA:
                                return (bool)($value >= $value_formula);
                            case self::TYPE_OPERATOR_LOWER_FORMULA:
                                return (bool)($value < $value_formula);
                            case self::TYPE_OPERATOR_LOWER_OR_EQUAL_FORMULA:
                                return (bool)($value <= $value_formula);
                        }
                    }
                }
            }
            return false;
        }

        private function getFeaturesByIdStepOption($id_step_option)
        {
            $id_lang = (int)Context::getContext()->language->id;
            $option = ConfiguratorStepOptionFactory::newObject($id_step_option);
            $step = ConfiguratorStepFactory::newObject($option->id_configurator_step);
            switch ($step->type) {
                case ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS:
                    $features_product = Product::getFeaturesStatic($option->id_option);
                    $features_target = array();
                    foreach ($features_product as $feature_product) {
                        $feature_value = new FeatureValue($feature_product['id_feature_value'], $id_lang);
                        $features_target[] = array(
                            'id_group' => (int)$feature_value->id_feature,
                            'id_option' => (int)$feature_value->id,
                            'name' => $feature_value->value
                        );
                    }
                    return $features_target;
                case ConfiguratorStepAbstract::TYPE_STEP_FEATURES:
                    $feature_target = new FeatureValue($option->id_option, $id_lang);
                    $features_target = array();
                    $features_target[] = array(
                        'id_group' => (int)$feature_target->id_feature,
                        'id_option' => (int)$feature_target->id,
                        'name' => $feature_target->value
                    );
                    return $features_target;
                case ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES:
                    $attribute_target = new Attribute($option->id_option, $id_lang);
                    $attributes_target = array();
                    $attributes_target[] = array(
                        'id_group' => (int)$attribute_target->id_attribute_group,
                        'id_option' => (int)$attribute_target->id,
                        'name' => $attribute_target->name
                    );
                    return $attributes_target;
                default:
                    return array();
            }
        }
        
        private function findCartDetailOptionSelected($cart_detail)
        {
            // @todo: cache
            $step_detail = $this->findCartDetailStep($cart_detail);
            if ($step_detail) {
                foreach ($step_detail['options'] as $option_detail) {
                    if ($option_detail['selected']) {
                        return ConfiguratorStepOptionFactory::newObject($option_detail['id']);
                    }
                }
            }
        }

        private function findCartDetailOptionsSelected($cart_detail)
        {
            $return = [];
            $step_detail = $this->findCartDetailStep($cart_detail);
            if ($step_detail) {
                foreach ($step_detail['options'] as $option_detail) {
                    if ($option_detail['selected']) {
                        $return[] = ConfiguratorStepOptionFactory::newObject($option_detail['id']);
                    }
                }
            }
            return $return;
        }

        private function findCartDetailStep($cart_detail)
        {
            $detail = $cart_detail->getDetail();
            foreach ($detail as $step_detail) {
                if ((int)$step_detail['id'] === (int)$this->id_target_step) {
                    return $step_detail;
                }
            }
        }
    }
}
