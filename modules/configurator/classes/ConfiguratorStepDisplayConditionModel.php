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

require_once(dirname(__FILE__) . '/../DmCache.php');
if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!class_exists('ConfiguratorStepDisplayConditionModel')) {

    /**
     * Class configuratorStepDisplayConditionModel
     */
    class ConfiguratorStepDisplayConditionModel extends ObjectModel
    {
        public $id_configurator_step_display_condition_group;
        public $value;
        public $min;
        public $max;
        public $formula;
        public static $definition = array(
            'table' => 'configurator_step_display_condition',
            'primary' => 'id_configurator_step_display_condition',
            'fields' => array(
                /* Classic fields */
                'id_configurator_step_display_condition_group' => array(
                    'type' => self::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true
                ),
                'value' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
                'min' => array('type' => self::TYPE_FLOAT),
                'max' => array('type' => self::TYPE_FLOAT),
                'formula' => array('type' => self::TYPE_STRING),
            )
        );

        public function duplicate($id_condition_group, $new_value)
        {
            if (!$new_value) {
                return false;
            }

            $new_condition = $this->duplicateObject();
            if (!Validate::isLoadedObject($new_condition)) {
                return false;
            }

            $new_condition->id_configurator_step_display_condition_group = (int)$id_condition_group;
            $new_condition->value = (int)$new_value;

            if (!$new_condition->save()) {
                return false;
            }

            return (int)$new_condition->id;
        }

        public static function getConditions($group_id)
        {

            $key = 'configurator::getConditions' . $group_id;
            if ( DmCache::getInstance()->isStored($key)) {
                $conditions = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csdc';
                $sql .= ' WHERE csdc.id_configurator_step_display_condition_group=' . (int)$group_id;
                $result = Db::getInstance()->executeS($sql);
                $condition = new ConfiguratorStepDisplayConditionGroupModel();
                $conditions = array();
                if (!empty($result)) {
                    $conditions = $condition->hydrateCollection(get_class(), $result);
                }

                DmCache::getInstance()->store($key, $conditions);
            }

            /**
             * @todo: Revoir encore une fois cette fonction
             *
             * $key = 'configuratorstepconditionmodel::getConditions-'.$group_id;
             * if (Cache::isStored($key)) {
             * return DmCache::getInstance()->retrieve($key);
             * }
             *
             * $key = 'configuratorstepconditionmodel::getConditions';
             * if (Cache::isStored($key)) {
             * $result_conditions = DmCache::getInstance()->retrieve($key);
             * } else {
             * $sql = 'SELECT * FROM `'._DB_PREFIX_.self::$definition['table'].'` csdc ';
             * //. 'WHERE csdc.id_configurator_step_display_condition_group='.(int)$group_id;
             * $results = Db::getInstance()->executeS($sql);
             *
             * $result_conditions = array();
             * foreach ($results as $result) {
             * $result_conditions[$result['id_configurator_step_display_condition_group']] = $result;
             * }
             * DmCache::getInstance()->store($key, $result_conditions);
             * }
             *
             * $key = 'configuratorstepconditionmodel::getConditions-'.$group_id;
             * if (!Cache::isStored($key)) {
             * $condition = new ConfiguratorStepDisplayConditionGroupModel();
             * $conditions = array();
             * if (!empty($result_conditions)) {
             * $conditions = $condition->hydrateCollection(get_class(), array(0 =>$result_conditions[$group_id]));
             * }
             * DmCache::getInstance()->store($key, $conditions);
             * }*/

            return $conditions;
        }

        public static function deleteByValue($value)
        {
            $conditions = self::getConditionsByValue($value);
            foreach ($conditions as $condition) {
                $condition->delete();
            }
        }

        public static function getConditionsByValue($value)
        {
            $key = 'configurator::getConditionsByValue' . $value;
            if ( DmCache::getInstance()->isStored($key)) {
                $conditions = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csdc';
                $sql .= ' WHERE csdc.value=' . (int)$value;
                $result = Db::getInstance()->executeS($sql);
                $condition = new ConfiguratorStepDisplayConditionGroupModel();
                $conditions = array();
                if (!empty($result)) {
                    $conditions = $condition->hydrateCollection(get_class(), $result);
                }

                DmCache::getInstance()->store($key, $conditions);
            }
            return $conditions;
        }

        public function getType()
        {
            if (!$this->formula && (int)$this->value > 0) {
                return 'option';
            } else {
                return 'formula';
            }
        }
    }
}
