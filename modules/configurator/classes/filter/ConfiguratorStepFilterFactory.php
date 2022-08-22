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

if (!class_exists('ConfiguratorStepFilterFactory')) {
    require_once(dirname(__FILE__) . '/../ConfiguratorFactoryAbstract.php');

    require_once(dirname(__FILE__) . '/ConfiguratorStepFilterAbstract.php');
    require_once(dirname(__FILE__) . '/ConfiguratorStepFilterTypeFeatureModel.php');

    /**
     * Class ConfiguratorStepFilterFactory
     */
    class ConfiguratorStepFilterFactory extends ConfiguratorFactoryAbstract
    {

        public static function getTypesAvailable()
        {
            return array(
                ConfiguratorStepFilterAbstract::TYPE_FILTER_FEATURES => 'ConfiguratorStepFilterTypeFeatureModel',
            );
        }

        public static function newObject($id_configurator_step_filter, $id_lang = 0)
        {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('configurator_step_filter', 'csf');
            $sql->where('csf.id_configurator_step_filter = ' . (int)$id_configurator_step_filter);
            $results = Db::getInstance()->executeS($sql);

            if (isset($results[0])) {
                $step_array = $results[0];
                return self::hydrate($step_array, $id_lang);
            }

            return null;
        }
    }
}
