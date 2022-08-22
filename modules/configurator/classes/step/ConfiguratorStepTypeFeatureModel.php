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

if (!class_exists('ConfiguratorStepTypeFeatureModel')) {
    require_once(dirname(__FILE__) . '/ConfiguratorStepAbstract.php');

    require_once(dirname(__FILE__) . '/../option/ConfiguratorStepOptionTypeFeatureModel.php');
    require_once(dirname(__FILE__) . '/../../DmCache.php');

    /**
     * Class ConfiguratorStepTypeFeatureModel
     */
    class ConfiguratorStepTypeFeatureModel extends ConfiguratorStepAbstract
    {

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
        }

        public static function getGroupsAvailable($id_lang)
        {
            $groups = array();
            $features_group = Feature::getFeatures($id_lang);
            foreach ($features_group as $feature_group) {
                $groups[] = array(
                    'id_option_group' => $feature_group['id_feature'],
                    'name' => $feature_group['name'],
                );
            }
            return $groups;
        }

        public function getOptions($lang_id, $only_used = true)
        {
            $key = 'ConfiguratorStepTypeFeatureModel::getOptions-' . $lang_id
                . '-' . (int)$this->id . '-' . ($only_used ? 'notall' : 'all');
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                // Get products in the current step category
                $feature_values = $this->getOptionsFromOptionGroup((int)$lang_id);

                // Get active options step
                $query = new DbQuery();
                $query->select('*')
                    ->from('configurator_step_option', 'cso')
                    ->innerJoin(
                        'configurator_step_option_lang',
                        'csol',
                        'cso.id_configurator_step_option = csol.id_configurator_step_option'
                        . ' AND csol.id_lang = ' . (int)$lang_id
                    )
                    ->where('id_configurator_step = ' . (int)$this->id)
                    ->orderBy('position ASC');
                $results = Db::getInstance()->executeS($query, true, false);

                foreach ($results as $k => $result) {
                    $results[$k]['content'] = array($lang_id => $result['content']);
                }

                $options = ConfiguratorStepOptionFactory::hydrateCollection($results);

                foreach ($feature_values as $feature_value) {
                    $used = false;
                    foreach ($options as $key => $option) {
                        if ((int)$option->id_option === (int)$feature_value['id_feature_value']) {
                            $options[$key]->option = $feature_value;
                            $options[$key]->ipa = 0;
                            $used = (int)$key;
                            break;
                        }
                    }

                    if (!$only_used && $used === false) {
                        $configuratorStepOption = new ConfiguratorStepOptionTypeFeatureModel();
                        $configuratorStepOption->option = $feature_value;
                        $configuratorStepOption->id_option = (int)$feature_value['id_feature_value'];
                        $configuratorStepOption->ipa = 0;
                        $options[] = $configuratorStepOption;
                    }
                }

                $pos = 0;
                foreach ($options as $key => $option) {
                    if (is_null($option->option)) {
                        unset($options[$key]);
                    } else {
                        $options[$key]->position = $pos;
                        $pos++;
                    }
                }

                DmCache::getInstance()->store($key, $options);
                return $options;
            }
        }

        public function getOptionsFromOptionGroup($id_lang)
        {
            $key = 'ConfiguratorStepTypeFeatureModel::getOptionsFromOptionGroup-' . (int)$id_lang;
            if (DmCache::getInstance()->isStored($key)) {
                $options = DmCache::getInstance()->retrieve($key);
            } else {
                $options = Db::getInstance()->executeS('
                    SELECT *
                    FROM `' . _DB_PREFIX_ . 'feature_value` fv
                    ' . Shop::addSqlAssociation('feature_value', 'fv') . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON (fv.`id_feature_value` = fvl.`id_feature_value` AND fvl.`id_lang` = ' . (int)$id_lang . ')
                ');
                DmCache::getInstance()->store($key, $options);
            }

            $key = 'ConfiguratorStepTypeFeatureModel::getOptionsFromOptionGroup-' . (int)$id_lang
                . '-' . (int)$this->id_option_group;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $return = array();
                foreach ($options as $option) {
                    if ($option['id_feature'] == $this->id_option_group) {
                        $option['name'] = $option['value'];
                        $return[] = $option;
                    }
                }
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }
    }
}
