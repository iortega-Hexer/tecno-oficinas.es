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

if (!class_exists('ConfiguratorStepOptionFactory')) {
    require_once(dirname(__FILE__) . '/../ConfiguratorFactoryAbstract.php');

    require_once(dirname(__FILE__) . '/../step/ConfiguratorStepAbstract.php');
    require_once(dirname(__FILE__) . '/../step/ConfiguratorStepFactory.php');
    require_once(dirname(__FILE__) . '/ConfiguratorStepOptionAbstract.php');
    require_once(dirname(__FILE__) . '/ConfiguratorStepOptionTypeAttributeModel.php');

    if (Module::isInstalled('dm_upack') && Module::isEnabled('dm_upack')) {
        require_once(
            dirname(__FILE__) . '/../../../dm_upack/classes/option/ConfiguratorStepOptionTypeProductModel.php'
        );
    }

    /**
     * Class ConfiguratorStepOptionFactory
     */
    class ConfiguratorStepOptionFactory extends ConfiguratorFactoryAbstract
    {

        public static function getTypesAvailable()
        {
            return array(
                ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES => 'ConfiguratorStepOptionTypeAttributeModel',
                ConfiguratorStepAbstract::TYPE_STEP_FEATURES => 'ConfiguratorStepOptionTypeFeatureModel',
                ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS => 'ConfiguratorStepOptionTypeProductModel',
                ConfiguratorStepAbstract::TYPE_STEP_UPLOAD => 'ConfiguratorStepOptionTypeAttributeModel',
            );
        }

        public static function newObject($id_configurator_step_option, $id_lang = null)
        {
            $sql = new DbQuery();
            $sql->select('cso.*, csol.*, cs.type');
            $sql->from('configurator_step_option', 'cso');

            if ($id_lang !== null) {
                $sql->innerJoin(
                    'configurator_step_option_lang',
                    'csol',
                    'cso.id_configurator_step_option = csol.id_configurator_step_option'
                        . ' AND csol.id_lang = ' . (int)$id_lang
                );
            } else {
                $sql->innerJoin(
                    'configurator_step_option_lang',
                    'csol',
                    'cso.id_configurator_step_option = csol.id_configurator_step_option'
                );
            }

            $sql->innerJoin('configurator_step', 'cs', 'cs.id_configurator_step = cso.id_configurator_step');
            $sql->where('cso.id_configurator_step_option = ' . (int)$id_configurator_step_option);
            $results = Db::getInstance()->executeS($sql);

            if (isset($results[0])) {
                $option_array = $results[0];
                if ($id_lang === null) {
                    $option_array['content'] = array();
                    foreach ($results as $result) {
                        $option_array['content'][$result['id_lang']] = $result['content'];
                    }
                }

                return self::hydrate($option_array);
            }

            return null;
        }
    }
}
