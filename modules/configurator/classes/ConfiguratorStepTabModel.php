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

if (!class_exists('ConfiguratorStepTabModel')) {

    /**
     * Class configuratorStepTabModel
     */
    class ConfiguratorStepTabModel extends ObjectModel
    {

        public $id_configurator;
        public $name;
        public $position;
        public static $definition = array(
            'table' => 'configurator_step_tab',
            'primary' => 'id_configurator_step_tab',
            'multilang' => true,
            'fields' => array(
                /* Classic fields */
                'id_configurator' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
                'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
                /* Lang fields */
                'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString')
            )
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            parent::__construct($id, $id_lang, $id_shop);
        }

        public function hydrate(array $data, $id_lang = null)
        {
            parent::hydrate($data, $id_lang);
        }

        public function delete()
        {
            $result = parent::delete();
            if ($result && self::existsInDatabase($this->id_configurator, "configurator_step")) {
                //self::updateStepByIdTab($this->id);
            }
            return $result;
        }

        public function duplicate($id_configurator)
        {
            $new_step_tab = $this->duplicateObject();
            if (!Validate::isLoadedObject($new_step_tab)) {
                return false;
            }

            $new_step_tab->id_configurator = (int)$id_configurator;
            if (!$new_step_tab->save()) {
                return false;
            }

            // Update step tab id
            $this->changeStepTabId($id_configurator, $this->id, $new_step_tab->id);

            // On vide le cache à ce moment pour que les modifications soient bien prise en compte pour la suite
            // Notamment le cache des étapes
            configurator::cleanCache();

            if (!$new_step_tab) {
                return false;
            }

            return true;
        }

        private function changeStepTabId($id_configurator, $last_id_tab, $new_id_tab)
        {
            $sql = "UPDATE `" . _DB_PREFIX_ . "configurator_step`" .
                " SET `id_configurator_step_tab` = " . (int)$new_id_tab .
                " WHERE `id_configurator_step_tab` = " . (int)$last_id_tab .
                " AND `id_configurator` = " . (int)$id_configurator;

            Db::getInstance()->execute($sql);
        }

        public static function updateStepByIdTab($id_tab)
        {
            $sql = "UPDATE `" . _DB_PREFIX_ . "configurator_step` SET `id_configurator_step_tab` = 0" .
                " WHERE `id_configurator_step_tab` = " . $id_tab;

            Db::getInstance()->execute($sql);
        }


        public static function getTabsByIdConfigurator($id_configurator)
        {
            $key = 'ConfiguratorStepTabModel::getTabsByIdConfigurator-' . $id_configurator;
            if ( DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $sql = ' SELECT cst.*, cstl.* '
                    . ' FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` cst '
                    . ' LEFT JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` cstl'
                    . ' ON cst.`' . self::$definition['primary'] . '` = cstl.`' . self::$definition['primary'] . '`'
                    . ' WHERE cst.id_configurator=' . (int)$id_configurator
                    . ' ORDER BY cst.position ASC, cst.id_configurator_step_tab ASC';
                $results = Db::getInstance()->executeS($sql);

                $configurator_step_tab = new ConfiguratorStepTabModel();
                $return = $configurator_step_tab->hydrateCollection(get_class(), $results);
                DmCache::getInstance()->store($key, $return);

                return $return;
            }
        }

        public static function deleteTabsByIdConfigurator($id_configurator)
        {
            $objects = self::getTabsByIdConfigurator($id_configurator);
            foreach ($objects as $object) {
                $object->delete();
            }
        }
    }
}
