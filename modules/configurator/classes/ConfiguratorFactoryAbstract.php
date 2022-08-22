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

if (!class_exists('ConfiguratorFactoryAbstract')) {

    /**
     * Class ConfiguratorFactoryAbstract
     */
    class ConfiguratorFactoryAbstract
    {

        public static function getType($step_array)
        {
            if (isset($step_array['type'])) {
                return $step_array['type'];
            } else {
                if (isset($step_array['id_configurator_step'])) {
                    $step = ConfiguratorStepFactory::newObject($step_array['id_configurator_step']);
                    if (Validate::isLoadedObject($step)) {
                        return $step->type;
                    }
                }
            }
            return null;
        }

        public static function hydrate($step_array, $id_lang = null)
        {
            $type = self::getType($step_array);
            $step_object = self::createObject($type);

            if ($step_object) {
                $step_object->hydrate($step_array, $id_lang);
                if (method_exists($step_object, 'init')) {
                    $step_object->init();
                }
            }

            return $step_object;
        }

        public static function hydrateCollection($steps_array, $id_lang = null)
        {
            $steps_object = array();

            foreach ($steps_array as $step_array) {
                $steps_object[] = self::hydrate($step_array, $id_lang);
            }

            return $steps_object;
        }

        protected static function createObject($type)
        {
            $object_name = self::getObjectName($type);
            if ($object_name) {
                return new $object_name();
            }
            return null;
        }

        public static function getObjectName($type)
        {
            $types_available = static::getTypesAvailable();
            if (isset($types_available[$type]) && class_exists($types_available[$type])) {
                return $types_available[$type];
            }
            return null;
        }

        public static function getTypesAvailable()
        {
            return null;
        }

        public static function newObject($id_configurator_step, $id_lang = null)
        {
            return null;
        }
    }
}
