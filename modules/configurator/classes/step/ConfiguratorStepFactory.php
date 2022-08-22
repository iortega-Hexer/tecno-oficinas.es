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

if (!class_exists('ConfiguratorStepFactory')) {
    require_once(dirname(__FILE__) . '/../ConfiguratorFactoryAbstract.php');

    require_once(dirname(__FILE__) . '/ConfiguratorStepAbstract.php');
    require_once(dirname(__FILE__) . '/ConfiguratorStepTypeAttributeModel.php');
    require_once(dirname(__FILE__) . '/ConfiguratorStepTypeFeatureModel.php');

    if (Module::isInstalled('dm_upack') && Module::isEnabled('dm_upack')) {
        require_once(dirname(__FILE__) . '/../../../dm_upack/classes/step/ConfiguratorStepTypeProductModel.php');
    }
    if (Module::isInstalled('dm_designer') && Module::isEnabled('dm_designer')) {
        require_once(dirname(__FILE__) . '/../../../dm_designer/classes/configurator/step/ConfiguratorStepTypeDesignerModel.php');
    }

    /**
     * Class ConfiguratorStepFactory
     */
    class ConfiguratorStepFactory extends ConfiguratorFactoryAbstract
    {

        public static function getTypesAvailable()
        {
            return array(
                ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES => 'ConfiguratorStepTypeAttributeModel',
                ConfiguratorStepAbstract::TYPE_STEP_FEATURES => 'ConfiguratorStepTypeFeatureModel',
                ConfiguratorStepAbstract::TYPE_STEP_PRODUCTS => 'ConfiguratorStepTypeProductModel',
                ConfiguratorStepAbstract::TYPE_STEP_UPLOAD => 'ConfiguratorStepTypeAttributeModel',
                ConfiguratorStepAbstract::TYPE_STEP_DESIGNER => 'ConfiguratorStepTypeDesignerModel'
            );
        }

        public static function newObject($id_configurator_step, $id_lang = null)
        {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('configurator_step', 'cs');

            if ((int)$id_lang > 0) {
                $sql->innerJoin(
                    'configurator_step_lang',
                    'csl',
                    'cs.id_configurator_step = csl.id_configurator_step AND csl.id_lang = ' . (int)$id_lang
                );
            } else {
                $sql->innerJoin('configurator_step_lang', 'csl', 'cs.id_configurator_step = csl.id_configurator_step');
            }

            $sql->where('cs.id_configurator_step = ' . (int)$id_configurator_step);
            $results = Db::getInstance()->executeS($sql);

            if (isset($results[0])) {
                $step_array = $results[0];
                if ($id_lang === null) {
                    $step_array['name'] = array();
                    $step_array['public_name'] = array();
                    $step_array['invoice_name'] = array();
                    $step_array['input_suffix'] = array();
                    $step_array['content'] = array();
                    $step_array['info_text'] = array();
                    $step_array['header_names'] = array();
                    foreach ($results as $result) {
                        $step_array['name'][$result['id_lang']] = $result['name'];
                        $step_array['public_name'][$result['id_lang']] = $result['public_name'];
                        $step_array['invoice_name'][$result['id_lang']] = $result['invoice_name'];
                        $step_array['input_suffix'][$result['id_lang']] = $result['input_suffix'];
                        $step_array['content'][$result['id_lang']] = $result['content'];
                        $step_array['info_text'][$result['id_lang']] = $result['info_text'];
                        $step_array['header_names'][$result['id_lang']] = $result['header_names'];
                    }
                }

                return self::hydrate($step_array, $id_lang);
            }

            return null;
        }
    }
}
