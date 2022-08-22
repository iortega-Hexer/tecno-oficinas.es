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

if (!class_exists('ConfiguratorStepDisplayConditionModel')) {
    require_once(dirname(__FILE__) . '/ConfiguratorStepDisplayConditionModel.php');
    require_once(dirname(__FILE__) . '/../DmCache.php');

    /**
     * Class configuratorStepDisplayConditionGroupModel
     */
    class ConfiguratorStepDisplayConditionGroupModel extends ObjectModel
    {
        private static $_type_conditions = array(
            'step' => 'id_configurator_step',
            'option' => 'id_configurator_step_option'
        );
        public $id_configurator_step;
        public $id_configurator_step_option;
        public $negative_condition;

        /**
         * Conditions collection
         */
        public $conditions = array();
        public static $definition = array(
            'table' => 'configurator_step_display_condition_group',
            'primary' => 'id_configurator_step_display_condition_group',
            'fields' => array(
                /* Classic fields */
                'id_configurator_step' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_configurator_step_option' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'negative_condition' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            )
        );

        public function delete()
        {
            $result = parent::delete();
            if ($result) {
                $conditions = ConfiguratorStepDisplayConditionModel::getConditions((int)$this->id);
                foreach ($conditions as $condition) {
                    $condition->delete();
                }
            }
            return $result;
        }

        public function duplicate($type, $foreignkey)
        {
            $field = self::$_type_conditions[$type];
            $new_condition_group = $this->duplicateObject();
            if (!Validate::isLoadedObject($new_condition_group)) {
                return false;
            }

            $new_condition_group->{$field} = (int)$foreignkey;
            if (!$new_condition_group->save()) {
                return false;
            }

            return (int)$new_condition_group->id;
        }

        public static function deleteConditions($type, $foreignkey)
        {
            $groups = self::getConditionGroups($type, $foreignkey);
            foreach ($groups as $group) {
                $group->delete();
            }
            Configurator::cleanCache();
        }

        public static function getConditionGroups($type, $foreignkey)
        {
            $key = 'configurator::getConditionGroups' . $type . '-' . $foreignkey;
            if ( DmCache::getInstance()->isStored($key)) {
                $groups = DmCache::getInstance()->retrieve($key);
            } else {
                $field = self::$_type_conditions[$type];
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csdcg';
                $sql .= ' WHERE csdcg.`' . $field . '`=' . (int)$foreignkey;
                $result = Db::getInstance()->executeS($sql);
                $group = new ConfiguratorStepDisplayConditionGroupModel();
                $groups = array();
                if (!empty($result)) {
                    $groups = $group->hydrateCollection(get_class(), $result);
                }
                DmCache::getInstance()->store($key, $groups);
            }
            return $groups;
        }

        public static function getConditions($type, $foreignkey)
        {
            $key = 'configuratorstepdisplaycondition-getConditions-' . $type . '-' . $foreignkey;
            if ( DmCache::getInstance()->isStored($key)) {
                $groups = DmCache::getInstance()->retrieve($key);
            } else {
                $groups = self::getConditionGroups($type, $foreignkey);
                foreach ($groups as &$group) {
                    $group->conditions = ConfiguratorStepDisplayConditionModel::getConditions((int)$group->id);
                }
                DmCache::getInstance()->store($key, $groups);
            }

            return $groups;
        }

        public static function saveConditions($type, $foreignkey, $condition_groups)
        {
            if ($foreignkey && isset(self::$_type_conditions)) {
                $field = self::$_type_conditions[$type];

                foreach ($condition_groups as $condition_group) {
                    $group = new ConfiguratorStepDisplayConditionGroupModel();
                    $group->{$field} = (int)$foreignkey;
                    if (isset($condition_group['negative_condition'])) {
                        $group->negative_condition = (int)$condition_group['negative_condition'];
                    }
                    if (isset($condition_group['datas']) && $group->save()) {
                        foreach ($condition_group['datas'] as $condition) {
                            $conditionModel = new ConfiguratorStepDisplayConditionModel();
                            $conditionModel->id_configurator_step_display_condition_group = (int)$group->id;
                            $conditionModel->value = (int)$condition['id'];
                            $conditionModel->min = (float)$condition['min'];
                            $conditionModel->max = (float)$condition['max'];
                            $conditionModel->formula = isset($condition['formula']) ? str_replace("\n", "", $condition['formula']) : null;
                            $conditionModel->save();
                        }
                    }
                }
            }
            Configurator::cleanCache();
        }
    }
}
