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

if (!class_exists('ConfiguratorStepFilterAbstract')) {
    require_once(dirname(__FILE__) . '/ConfiguratorStepFilterFactory.php');
    require_once(dirname(__FILE__) . '/../../DmCache.php');

    /**
     * Class ConfiguratorStepFilterAbstract
     */
    abstract class ConfiguratorStepFilterAbstract extends ObjectModel
    {
        // Filter types
        const TYPE_FILTER_FEATURES = 'features';

        // Value types
        const TYPE_VALUE_ID = 'id';
        const TYPE_VALUE_NAME = 'name';

        // Operator types
        const TYPE_OPERATOR_EQUAL = 'EQUAL'; // '=';
        const TYPE_OPERATOR_CONTAINS = 'CONTAINS'; //'%LIKE%';
        const TYPE_OPERATOR_CONTAINS_AT_LEAST = 'CONTAINS_ONE';
        const TYPE_OPERATOR_CONTAINED = 'CONTAINED'; // '%CONTAINED%';
        const TYPE_OPERATOR_UPPER = 'UPPER'; // '>';
        const TYPE_OPERATOR_UPPER_OR_EQUAL = 'UPPER_OR_EQUAL'; // '>=';
        const TYPE_OPERATOR_LOWER = 'LOWER'; // '<';
        const TYPE_OPERATOR_LOWER_OR_EQUAL = 'LOWER_OR_EQUAL'; // '<=';
        const TYPE_OPERATOR_EQUAL_NUMBER = 'EQUAL_NUMBER'; // '=NUMBER';
        const TYPE_OPERATOR_UPPER_NUMBER = 'UPPER_NUMBER'; // '>NUMBER';
        const TYPE_OPERATOR_UPPER_OR_EQUAL_NUMBER = 'UPPER_OR_EQUAL_NUMBER'; // '>=NUMBER';
        const TYPE_OPERATOR_LOWER_NUMBER = 'LOWER_NUMBER'; // '<NUMBER';
        const TYPE_OPERATOR_LOWER_OR_EQUAL_NUMBER = 'LOWER_OR_EQUAL_NUMBER'; // '<=NUMBER';
        const TYPE_OPERATOR_EQUAL_FORMULA = 'EQUAL_FORMULA'; // '=FORMULA';
        const TYPE_OPERATOR_UPPER_FORMULA = 'UPPER_FORMULA'; // '>FORMULA';
        const TYPE_OPERATOR_UPPER_OR_EQUAL_FORMULA = 'UPPER_OR_EQUAL_FORMULA'; // '>=FORMULA';
        const TYPE_OPERATOR_LOWER_FORMULA = 'LOWER_FORMULA'; // '<FORMULA';
        const TYPE_OPERATOR_LOWER_OR_EQUAL_FORMULA = 'LOWER_OR_EQUAL_FORMULA'; // '<=FORMULA';

        public $id_configurator_step_filter_group;
        public $type = self::TYPE_FILTER_FEATURES;
        public $id_option;
        public $operator;
        public $id_target_step;
        public $target_type = self::TYPE_FILTER_FEATURES;
        public $id_target_option;
        public $type_value = self::TYPE_VALUE_ID;
        public $formula;

        public static $definition = array(
            'table' => 'configurator_step_filter',
            'primary' => 'id_configurator_step_filter',
            'fields' => array(
                /* Classic fields */
                'id_configurator_step_filter_group' => array(
                    'type' => self::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true
                ),
                'type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
                'id_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'operator' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
                'id_target_step' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'target_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
                'id_target_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'type_value' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
                'formula' => array('type' => self::TYPE_STRING, 'validate' => 'isString')
            )
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
        }

        abstract public function getOption($lang_id);

        abstract public function isValid($id_configurator_step_option, $cart_detail);

        public static function getFilters($group_id = 0)
        {
            $key = 'ConfiguratorStepFilterAbstract::getFilters' . (int)$group_id;
            if ( DmCache::getInstance()->isStored($key)) {
                $filters = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csf';
                if ((int)$group_id > 0) {
                    $sql .= ' WHERE csf.id_configurator_step_filter_group=' . (int)$group_id;
                }
                $result = Db::getInstance()->executeS($sql);
                $filters = ConfiguratorStepFilterFactory::hydrateCollection($result);
                DmCache::getInstance()->store($key, $filters);
            }
            return $filters;
        }
    }
}
