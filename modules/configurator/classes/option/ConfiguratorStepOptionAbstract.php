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

if (!class_exists('ConfiguratorStepOptionAbstract')) {
    require_once(dirname(__FILE__) . '/../ConfiguratorAttribute.php');
    require_once(dirname(__FILE__) . '/../step/ConfiguratorStepAbstract.php');
    require_once(dirname(__FILE__) . '/../ConfiguratorStepDisplayConditionGroupModel.php');
    require_once(dirname(__FILE__) . '/../../DmCache.php');

    // Module Viewer 2D
    if (Module::isInstalled('dm_viewer2d') && Module::isEnabled('dm_viewer2d')) {
        require_once(dirname(__FILE__) . '/../../../dm_viewer2d/classes/dmconcept/DmImage.php');
        require_once(dirname(__FILE__) . '/../../../dm_viewer2d/classes/configurator/ConfiguratorStepOptionLayerModel.php');
    }
    // /Module Viewer 2D

    // Module Advanced Formula
    if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
        require_once(dirname(__FILE__) . '/../../../dm_advancedformula/classes/helper/AdvancedformulaHelper.php');
    }
    // /Module Advanced Formula


    /**
     * Class ConfiguratorStepOptionAbstract
     */
    class ConfiguratorStepOptionAbstract extends ObjectModel
    {
        /**
         * Type of impact price for an option
         */
        const IMPACT_TYPE_PERCENT = 'percent';
        const IMPACT_TYPE_NEGATIVE_PERCENT = 'neg_percent';
        const IMPACT_TYPE_AMOUNT = 'amount';
        const IMPACT_TYPE_REDUCTION_AMOUNT = 'reduction';
        const IMPACT_TYPE_AREA = 'area';
        const IMPACT_TYPE_AREA_MULTIPLE = 'area_multiple';
        const IMPACT_TYPE_PRICELIST = 'pricelist';
        const IMPACT_TYPE_PRICELIST_QUANTITY = 'pricelist_quantity';
        const IMPACT_TYPE_PRICELIST_MULTIPLE = 'pricelist_multi';
        const IMPACT_TYPE_PRICELIST_AREA = 'pricelist_area';
        const IMPACT_TYPE_PRICELIST_AREA_SQUARE = 'pricelist_area_square';
        const IMPACT_TYPE_MULTIPLIER = 'multiplier';
        const IMPACT_TYPE_MULTIPLIER_PRICE = 'multiplier_price';
        const IMPACT_TYPE_NEGATIVE_MULTIPLIER = 'neg_multiplier';
        const IMPACT_TYPE_AMOUNT_FORMULA = 'amount_formula';
        const IMPACT_TYPE_AMOUNT_PERIOD = 'amount_period';

        const PRICE_CALCULATION_TYPE_WITH_REDUC = 'with_reduc';
        const PRICE_CALCULATION_TYPE_WITHOUT_REDUC = 'without_reduc';

        public $id_configurator_step;
        public $id_option;
        public $ipa;
        public $id_configurator_step_option_division = false;
        public $impact_type = self::IMPACT_TYPE_AMOUNT;
        public $impact_value = 0;
        public $impact_value_period;
        public $impact_step_id;
        public $id_impact_step_option_x;
        public $id_impact_step_option_y;
        public $impact_multiple_step_id;
        public $price_list;
        public $unity;
        public $conversion_factor = 0;
        public $selected_by_default = 0;
        public $reference;
        public $reference_position;
        public $content;
        public $default_value = false;
        public $min_value;
        public $min_value_if_null = 0;
        public $max_value;
        public $max_value_if_null = 0;
        public $force_value = false;
        public $check_value = false;
        public $weight;
        public $price_calculation = self::PRICE_CALCULATION_TYPE_WITH_REDUC;
        public $display_price_calculation = self::PRICE_CALCULATION_TYPE_WITH_REDUC;
        public $position = 0;
        public $impact_formula;
        public $used_for_dimension;
        public $dimension_coeff;
        public $slider;
        public $slider_step = 1;
        public $textarea;
        public $qty_coeff = 1;
        public $email;
        public $default_qty = 0;
        public $is_date;
        public $id_tax_rules_group = 0;
        public $id_tax_rules_group_product = 0;
        public $is_ralstep = 0;
        public $id_atribute_ral = 0;
        public $id_step_impact_qty = 0;
        public $id_step_option_impact_qty = 0;

        public $option = null;

        // Module Viewer 2D
        public $has_layer = false;
        public $layer;
        public $layers;
        // /Module Viewer 2D
        
         /**
         *
         * @var ConfiguratorStepAbstract
         */
        protected $step= null;

        public static $definition = array(
            'table' => 'configurator_step_option',
            'primary' => 'id_configurator_step_option',
            'multilang' => true,
            'fields' => array(
                /* Classic fields */
                'id_configurator_step' => array(
                    'type' => self::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true
                ),
                'id_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
                'ipa' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
                'id_configurator_step_option_division' => array(
                    'type' => self::TYPE_INT,
                    'validate' => 'isNullOrUnsignedId'
                ),
                'impact_type' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 50),
                'impact_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
                'impact_value_period' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'impact_step_id' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
                'id_impact_step_option_x' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
                'id_impact_step_option_y' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
                'impact_multiple_step_id' => array('type' => self::TYPE_STRING, 'required' => false, 'size' => 250),
                'price_list' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
                'unity' => array('type' => self::TYPE_STRING, 'size' => 10),
                'conversion_factor' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
                'selected_by_default' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'reference_position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'default_value' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'force_value' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'check_value' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'min_value' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'min_value_if_null' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'max_value' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'max_value_if_null' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'weight' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'price_calculation' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 100),
                'display_price_calculation' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 100),
                'position' => array('type' => self::TYPE_INT),
                'impact_formula' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'used_for_dimension' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'dimension_coeff' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
                'qty_coeff' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'default_qty' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                'slider' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'slider_step' => array('type' => self::TYPE_INT),
                'textarea' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'email' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'is_date' => array('type' => self::TYPE_BOOL, 'validate' =>'isBool'),
                'id_tax_rules_group_product' => array('type' => self::TYPE_INT, 'validate' =>'isNullOrUnsignedId'),
                'is_ralstep' => array('type' => self::TYPE_BOOL, 'validate' =>'isBool'),
                'id_atribute_ral' => array('type' => self::TYPE_INT,'validate' => 'isUnsignedId','required' => true),
                'id_step_impact_qty' => array('type' => self::TYPE_INT,'validate' => 'isUnsignedId'),
                'id_step_option_impact_qty' => array('type' => self::TYPE_INT,'validate' => 'isUnsignedId'),

                /* Lang fields */
                'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml')
            )
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
            $this->init();
        }

        public function fillOption()
        {
            return;
        }

        public function getImpactValuePeriod()
        {
            if ($this->impact_value_period !== null) {
                $periods = json_decode($this->impact_value_period, true);
                if (is_array($periods)) {
                    $date = new DateTime();
                    foreach ($periods as $period) {
                        if (isset($period['values']['date_start'])
                            && new DateTime($period['values']['date_start'] . ' 00:00:00') <= $date
                            && isset($period['values']['date_end'])
                            && new DateTime($period['values']['date_end'] . ' 23:59:59') >= $date
                            && isset($period['values']['specific_value'])
                        ) {
                            return (float)$period['values']['specific_value'];
                        }
                    }
                }
            }
            return false;
        }

        public function init()
        {
            // Module Viewer 2D
            if (Module::isInstalled('dm_viewer2d') && Module::isEnabled('dm_viewer2d')) {
                $this->fillLayer();
            }
            // /Module Viewer 2D
        }

        public function delete()
        {
            $result = parent::delete();
            if ($result) {
                ConfiguratorStepDisplayConditionGroupModel::deleteConditions(
                    ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
                    $this->id
                );
                ConfiguratorStepDisplayConditionModel::deleteByValue($this->id);
                self::deleteDivisionByIdOption($this->id);
            }

            return $result;
        }

        public function deleteDivisionByIdOption($id_option)
        {
            $sql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table']
                . '` csa SET csa.id_configurator_step_option_division = NULL '
                . 'WHERE csa.id_configurator_step_option_division=' . (int)$id_option;
            Db::getInstance()->execute($sql);
        }

        public function duplicate($id_configurator, $id_step)
        {
            /* @var $new_step_option ConfiguratorStepOptionAbstract */
            $new_step_option = $this->duplicateObject();
            if (!Validate::isLoadedObject($new_step_option)) {
                return false;
            }

            // Reprise du step dupliqué pour certaines méthodes de calcul
            if ($this->impact_step_id) {
                $old_step = ConfiguratorStepFactory::newObject($this->impact_step_id);
                $new_step_related_to_old_step = ConfiguratorStepAbstract::getByIdentifierPosition(
                    (int)$id_configurator,
                    (int)$old_step->position
                );
                if (!Validate::isLoadedObject($new_step_related_to_old_step)) {
                    return false;
                }
                $new_step_option->impact_step_id = (int)$new_step_related_to_old_step->id;
            }
            if ($this->impact_multiple_step_id) {
                $old_impact_multiple_step_id = explode(',', $this->impact_multiple_step_id);
                $new_impact_multiple_step_id = array();
                foreach ($old_impact_multiple_step_id as $old_impact_step_id) {
                    $old_step = ConfiguratorStepFactory::newObject($old_impact_step_id);
                    $new_step_related_to_old_step = ConfiguratorStepAbstract::getByIdentifierPosition(
                        (int)$id_configurator,
                        (int)$old_step->position
                    );
                    if (!Validate::isLoadedObject($new_step_related_to_old_step)) {
                        return false;
                    }
                    $new_impact_multiple_step_id[] = (int)$new_step_related_to_old_step->id;
                }
                $new_impact_multiple_step_id = implode(',', $new_impact_multiple_step_id);
                $new_step_option->impact_multiple_step_id = $new_impact_multiple_step_id;
            }

            $new_step_option->id_configurator_step = (int)$id_step;
            $new_step_option->save();

            // On vide le cache à ce moment pour que les modifications soient bien prise en compte pour la suite
            // Notamment le cache des étapes
            configurator::cleanCache();

            if (!$new_step_option) {
                return false;
            }

            // Duplication des conditions d'affichage
            $conditions_group = ConfiguratorStepDisplayConditionGroupModel::getConditions(
                ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
                (int)$this->id
            );
            foreach ($conditions_group as $condition_group) {
                /* @var $condition_group ConfiguratorStepDisplayConditionGroupModel */
                if (!$id_condition_group = $condition_group->duplicate(
                    ConfiguratorStepAbstract::TYPE_CONDITION_OPTION,
                    (int)$new_step_option->id
                )) {
                    return false;
                }

                foreach ($condition_group->conditions as $condition) {
                    $old_step = ConfiguratorStepAbstract::getByIdOption((int)$condition->value);
                    if (!Validate::isLoadedObject($old_step)) {
                        return false;
                    }

                    $position_option_in_db = self::getPositionIdentifier((int)$condition->value);
                    $new_step_related_to_old_step = ConfiguratorStepAbstract::getByIdentifierPosition(
                        (int)$id_configurator,
                        (int)$old_step->position
                    );
                    if (!Validate::isLoadedObject($new_step_related_to_old_step)) {
                        return false;
                    }

                    $new_condition_value = self::getIdByIdentifierPosition(
                        (int)$new_step_related_to_old_step->id,
                        (int)$position_option_in_db
                    );
                    if (!$condition->duplicate((int)$id_condition_group, $new_condition_value)) {
                        return false;
                    }
                }
            }

            return true;
        }

        public static function removeSelectedDefault($id_configurator_step, $id_configurator_step_option = 0)
        {
            $sql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '` cso SET cso.selected_by_default = 0 '
                . 'WHERE cso.id_configurator_step=' . (int)$id_configurator_step;
            if ((int)$id_configurator_step_option > 0) {
                $sql .= ' AND cso.id_configurator_step_option = ' . (int)$id_configurator_step_option;
            }
            Db::getInstance()->execute($sql);
        }

        public static function cleanSelectedDefault($id_configurator_step)
        {
            $sql = 'SELECT cso.id_configurator_step_option'
                . ' FROM `' . _DB_PREFIX_ . 'configurator_step_option` cso'
                . ' WHERE cso.id_configurator_step = ' . (int)$id_configurator_step
                . ' AND cso.selected_by_default = 1'
                . ' ORDER BY cso.position ASC';
            $results = Db::getInstance()->executeS($sql, true, false);

            foreach ($results as $result) {
                $id_configurator_step_option = (int)$result['id_configurator_step_option'];

                $sql = 'UPDATE `' . _DB_PREFIX_ . 'configurator_step_option` cso SET cso.selected_by_default = 0'
                    . ' WHERE cso.id_configurator_step = ' . (int)$id_configurator_step
                    . ' AND cso.id_configurator_step_option <> ' . (int)$id_configurator_step_option;
                Db::getInstance()->execute($sql);
                return;
            }
        }

        public static function getOption($id_configurator_step_option)
        {
            $key = 'ConfiguratorStepOptionAbstract::getOption-lang' . Context::getContext()->language->id
                . '-' . $id_configurator_step_option;
            if ( DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $configurator_step_option = ConfiguratorStepOptionFactory::newObject(
                    (int)$id_configurator_step_option
                );
                if (Validate::isLoadedObject($configurator_step_option)) {
                    $configurator_step_option->fillOption();
                }

                DmCache::getInstance()->store($key, $configurator_step_option);

                return $configurator_step_option;
            }
        }

        /**
         * Cette requête est appelé autant de fois qu'il y a d'options.
         * Le mieux, la mettre en cache lors du premier appel, puis utiliser les valeurs en cache.
         *
         * @param type $id_option
         * @param type $id_configurator_step
         * @return \ConfiguratorStepOptionAbstract
         */
        public static function getByIdOptionAndIdConfiguratorStep($id_configurator_step, $id_option, $ipa = 0)
        {
            $key = 'ConfiguratorStepOptionAbstract::getByIdOptionAndIdConfiguratorStep-1-' . $id_configurator_step;
            if ( DmCache::getInstance()->isStored($key)) {
                $return = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT `' . self::$definition['primary'] . '`, `csa`.`id_option`, `csa`.`ipa` '
                    . 'FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csa '
                    . 'WHERE csa.id_configurator_step=' . (int)$id_configurator_step;
                $results = Db::getInstance()->executeS($sql, true, false);

                $return = array();
                foreach ($results as $result) {
                    $return[$result['id_option'] . '-' . $result['ipa']] = $result[self::$definition['primary']];
                }

                DmCache::getInstance()->store($key, $return);
            }

            // Traitement pour plus de cache
            $id_step_option_model = null;
            if (isset($return[$id_option . '-' . $ipa])) {
                $id_step_option_model = $return[$id_option . '-' . $ipa];
            }
            if (!empty($id_step_option_model)) {
                $key = 'ConfiguratorStepOptionAbstract::getByIdOptionAndIdConfiguratorStep-2-' . $id_step_option_model;
                if (DmCache::getInstance()->isStored($key)) {
                    $return = DmCache::getInstance()->retrieve($key);
                } else {
                    $return = ConfiguratorStepOptionFactory::newObject((int)$id_step_option_model);
                    DmCache::getInstance()->store($key, $return);
                }
            } else {
                /**
                 * @todo: Vérifer optimisation de l'explosion mémoire des instanciation attributs
                 */
                $return = false;//new ConfiguratorStepOptionAbstract();
            }

            return $return;
        }

        public static function getByIdConfiguratorStep($id_configurator_step)
        {
            $key = 'ConfiguratorStepOptionAbstract::getByIdConfiguratorStep-' . $id_configurator_step;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csa '
                    . 'WHERE csa.id_configurator_step=' . (int)$id_configurator_step;
                $results = Db::getInstance()->executeS($sql);

                $return = ConfiguratorStepOptionFactory::hydrateCollection($results);
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public static function getByIdOption($id_option)
        {
            $key = 'ConfiguratorStepOptionAbstract::getByIdOption-' . $id_option;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csa '
                    . 'WHERE csa.id_option=' . (int)$id_option;
                $results = Db::getInstance()->executeS($sql);

                $return = ConfiguratorStepOptionFactory::hydrateCollection($results);
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public static function deleteByIdOption($id_option, $step_type = 'attributes')
        {
            $objects = self::getByIdOption($id_option);
            foreach ($objects as $object) {
                $step = $object->getStep();
                if ($step->type === $step_type) {
                    $object->delete();
                    $step->updateOptionsPositions();
                }
            }
        }

        /**
         * Retourne un tableau d'options par rapport à l'id step
         * @param type $id_step
         * @return type
         */
        public static function getIdsByIdStep($id_step)
        {
            $query = new DbQuery();
            $query->select('id_configurator_step_option')
                ->from(self::$definition['table'])
                ->where('id_configurator_step = ' . (int)$id_step)
                ->groupBy('id_option');
            return Db::getInstance()->executeS($query, true, false);
        }

        public static function getDivisionIdsByIdStepOption($id_step_option, $id_configurator)
        {
            $key = 'ConfiguratorStepOptionAbstract::getDivisionIdsByIdStepOption-id_step_option:'
                . $id_step_option . '-id_configurator:' . $id_configurator;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT csa.id_configurator_step, COUNT(csa.id_configurator_step) as counter '
                    . ' FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csa, `'
                    . _DB_PREFIX_ . 'configurator_step` cs '
                    . ' WHERE csa.id_configurator_step_option_division=' . (int)$id_step_option
                    . ' AND csa.id_configurator_step=cs.id_configurator_step'
                    . ' AND cs.id_configurator=' . (int)$id_configurator
                    . ' GROUP BY csa.id_configurator_step';
                $return = Db::getInstance()->executeS($sql);
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public static function getByIdStepOptionDivision($id_step_option_division)
        {
            $key = 'ConfiguratorStepOptionAbstract::getByIdStepOptionDivision-' . $id_step_option_division;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * '
                    . ' FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csa '
                    . ' WHERE csa.id_configurator_step_option=' . (int)$id_step_option_division;
                $return = Db::getInstance()->getRow($sql);
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        /**
         * Retourne la position de l'identifiant d'une option
         * @param int $id_option
         * @return int
         */
        public static function getPositionIdentifier($id_option)
        {
            $step = ConfiguratorStepAbstract::getByIdOption((int)$id_option);
            if (!Validate::isLoadedObject($step)) {
                return -1;
            }

            $ids = self::getIdsByIdStep((int)$step->id);

            foreach ($ids as $k => $row) {
                if ((int)$row['id_configurator_step_option'] === (int)$id_option) {
                    return $k;
                }
            }

            return -1;
        }

        /**
         * Retourne l'identifiant de la position sélectionnée
         * @param int $position
         * @return int
         */
        public static function getIdByIdentifierPosition($id_step, $position)
        {
            $ids = self::getIdsByIdStep((int)$id_step);

            foreach ($ids as $k => $id) {
                if ($k === (int)$position) {
                    return $id['id_configurator_step_option'];
                }
            }

            return 0;
        }

        /**
         * Retourne la quantité actuelle
         * @param int $position
         * @return int
         */
        public function getDefaultQty($cart_detail_model)
        {
            if (Module::isInstalled('dm_advancedformula')
                && Module::isEnabled('dm_advancedformula')
                && $this->max_qty
            ) {
                return (int) AdvancedformulaHelper::loadFormula(
                    $cart_detail_model,
                    $this,
                    $cart_detail_model->getDetail(),
                    $this->default_qty
                );
            } else {
                return (int) $this->max_qty;
            }
        }

        public function getDefaultValue()
        {
            $default_value = false;

            Hook::exec('configuratorActionStepOptionGetDefaultValue', array(
                'configurator_step_option' => &$this,
                'default_value' => &$default_value
            ));

            return ($default_value !== false) ? $default_value : $this->default_value;
        }

        // POSITION

        public function updatePosition($position)
        {
            if (!$id_configurator_step = (int)Tools::getValue('id_configurator_step')) {
                $id_configurator_step = (int)$this->id_configurator_step;
            }

            $sql = 'SELECT `position`, `id_configurator_step_option`, `id_configurator_step`
					FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` ag
					WHERE `id_configurator_step` = ' . (int)$id_configurator_step . '
					ORDER BY `position` ASC';
            if (!$res = Db::getInstance()->executeS($sql)) {
                return false;
            }

            foreach ($res as $configurator_step_option) {
                if ((int)$configurator_step_option['id_configurator_step_option'] === (int)$this->id) {
                    $moved_configurator_step_option = $configurator_step_option;
                }
            }

            if (!isset($moved_configurator_step_option) || !isset($position)) {
                return false;
            }

            $sql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '`'
                . ' SET `position`=' . $position
                . ' WHERE `id_configurator_step` = ' . (int)$id_configurator_step
                . ' AND `id_configurator_step_option`='
                . (int)$moved_configurator_step_option['id_configurator_step_option'];
            return Db::getInstance()->execute($sql);
        }

        public function addTaxes(&$optionDetail, $idTaxRulesGroupProduct) {
            $idTaxRulesGroup = 0;
            $optionDetail['price']['tax_incl'] = 0;
            if (isset($optionDetail['taxes'])) {
                foreach ($optionDetail['taxes'] as &$tax) {
                    $idTaxRulesGroup = ($this->id_tax_rules_group === 0) ? $idTaxRulesGroupProduct : $this->id_tax_rules_group;
                    $tax['id_tax_rules_group'] = $idTaxRulesGroup;
                    $tax['price_tax_incl'] = DMTools::convertPriceTaxExclToTaxIncl(
                        $tax['price_tax_excl'],
                        $tax['id_tax_rules_group']
                    );
                    $optionDetail['price']['tax_incl'] += $tax['price_tax_incl'];
                }
            }

            if(Validate::isLoadedObject($this->getStep()->getConfigurator())){
                if (isset($optionDetail['display_price']['value']) && !is_null($optionDetail['display_price']['value'])) {
                    $optionDetail['display_price']['formatted'] = DMTools::displayPrice($optionDetail['display_price']['value'] , $this->getStep()->getConfigurator());
                } else if (isset($optionDetail['price']['tax_incl']) && $optionDetail['price']['tax_incl'] <> 0) {
                    if (DMTools::useTax()) {
                        $val = DMTools::getDiscountPrice($optionDetail['price']['tax_incl'], $this->getStep()->getConfigurator()->id_product);
                    } else {
                        $val = DMTools::getDiscountPrice($optionDetail['price']['tax_excl'], $this->getStep()->getConfigurator()->id_product);
                    }
                    $optionDetail['display_price'] = array(
                        'value' => $val,
                        'formatted' => DMTools::displayPrice($val,$this->getStep()->getConfigurator())
                    );
                }
                if (!isset($optionDetail['display_price'])) {
                    $optionDetail['display_price']['value'] = 0;
                    $optionDetail['display_price']['formatted'] = '';
                }

                $configurator = $this->getStep()->getConfigurator();
                if(Validate::isLoadedObject($configurator) && !$configurator->hide_product_price) {
                    $optionDetail['display_amount'] = $optionDetail['display_price']['formatted'];
                }

                if (isset($optionDetail['reduc_tax_excl']) && $optionDetail['reduc_tax_excl'] <> 0) {
                    $reducTaxIncl = DMTools::convertPriceTaxExclToTaxIncl(
                        $optionDetail['reduc_tax_excl'],
                        $idTaxRulesGroup
                    );
                    $optionDetail['display_reduc'] = '- ' . DMTools::displayPrice($reducTaxIncl,$this->getStep()->getConfigurator() );
                }
            }
        }

        public function getStep()
        {
            if($this->step === null) {
                $this->step = ConfiguratorStepFactory::newObject($this->id_configurator_step);
            }
            
            return $this->step;
        }

        // Module Viewer 2D
        /**
         * Fill properties has_layer and layer
         */
        public function fillLayer()
        {
            $layers = ConfiguratorStepOptionLayerModel::getByConfiguratorStepOption($this->id);
            if (isset($layers[0])) {
                $this->has_layer = true;
                $this->layer = $layers[0]->layer;
            }
            $this->layers = $layers;
        }
        // /Module Viewer 2D
        
        public function getRalAttributes() {
           // implement cache
           return AttributeGroup::getAttributes(Context::getContext()->language->id, (int)$this->id_atribute_ral);
           
        }

        public static function getHigherPosition($id_configurator_step)
        {
            $sql = 'SELECT MAX(`position`)
					FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
					WHERE id_configurator_step = ' . (int)$id_configurator_step;

            $position = DB::getInstance()->getValue($sql);

            return (is_numeric($position)) ? $position : (-1);
        }

        public function findStepQty($detail)
        {
            $totalQty = null;
            if ((int)$this->id_step_impact_qty > 0 || (int)$this->id_step_option_impact_qty > 0) {
                foreach ($detail as $stepDetail) {
                    if ((int)$stepDetail['id'] === (int)$this->id_step_impact_qty) {
                        $totalQty = 0;
                        foreach ($stepDetail['options'] as $optionDetail) {
                            if ($optionDetail['selected'] && ((int)$this->id_step_option_impact_qty === 0 || (int)$optionDetail['id'] === (int)$this->id_step_option_impact_qty)) {
                                $totalQty += $optionDetail['qty'];
                            }
                        }
                    }
                }
            }
            return ($totalQty === null) ? 1 : $totalQty;
        }
    }

}
