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

if (!class_exists('ConfiguratorStepGroupModel')) {

    /**
     * Class ConfiguratorStepGroupModel
     */
    class ConfiguratorStepGroupModel extends ObjectModel
    {
        public $id_configurator_step_group;
        public $id_group;
        public $id_configurator_step;

        private static $_duplicate = array();

        public static $definition = array(
            'table' => 'configurator_step_group',
            'primary' => 'id_configurator_step_group',
            'multilang' => false,
            'fields' => array(
                /* Classic fields */
                'id_configurator_step_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'id_configurator_step' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            )
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
        }

        public static function getLinkedStepById($id_configurator_step)
        {
            $id_group = self::getIdGroupByStepId($id_configurator_step);
            $key = 'ConfiguratorStepGroupModel::getLinkedStepById-' . $id_group;
            if ( DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` csg '
                    . ' WHERE csg.id_group=' . (int)$id_group;
                $results = Db::getInstance()->executeS($sql);
                $return = array();
                if (is_array($results) && count($results) > 0) {
                    foreach ($results as $result) {
                        $return[] = $result['id_configurator_step'];
                    }
                }
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public static function deleteLinkedStepById($id_configurator_step)
        {
            $id_group = self::getIdGroupByStepId($id_configurator_step);
            Db::getInstance()->delete(
                self::$definition['table'],
                'id_group=' . (int)$id_group
            );
        }

        public static function deleteByStep($id_configurator_step)
        {
            Db::getInstance()->delete(
                self::$definition['table'],
                'id_configurator_step=' . (int)$id_configurator_step
            );
        }

        public static function addLinkedStepById($linked_steps)
        {
            if (is_array($linked_steps)) {
                $id_group = self::getHigherIdGroup() + 1;
                foreach ($linked_steps as $linked_step) {
                    $configurator_step_group = new ConfiguratorStepGroupModel();
                    $configurator_step_group->id_group = $id_group;
                    $configurator_step_group->id_configurator_step = $linked_step;
                    $configurator_step_group->save();
                }
            }
            return true;
        }

        public static function getHigherIdGroup()
        {
            $sql = 'SELECT MAX(`id_group`)
					FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`';

            $id_group = DB::getInstance()->getValue($sql);

            return (is_numeric($id_group)) ? $id_group : (0);
        }

        public static function getIdGroupByStepId($id_configurator_step)
        {
            $sql = 'SELECT `id_group`'
                . ' FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`'
                . ' WHERE id_configurator_step=' . (int)$id_configurator_step;

            $id_group = DB::getInstance()->getValue($sql);

            return (is_numeric($id_group)) ? $id_group : (0);
        }

        public static function duplicate($last_id_step, $new_id_step)
        {
            $last_id_group = self::getIdGroupByStepId($last_id_step);
            if ($last_id_group > 0) {
                $new_id_group = (isset(self::$_duplicate[$last_id_group]))
                    ? self::$_duplicate[$last_id_group]
                    : self::getHigherIdGroup() + 1;
                self::$_duplicate[$last_id_group] = $new_id_group;
                $new_step_group = new ConfiguratorStepGroupModel();
                $new_step_group->id_group = (int)$new_id_group;
                $new_step_group->id_configurator_step = (int)$new_id_step;
                $new_step_group->save();
            }
        }
    }
}
