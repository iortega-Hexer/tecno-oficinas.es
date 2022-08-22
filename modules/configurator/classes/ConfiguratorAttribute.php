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
 * @license   http://opensource.org/licenses/afl-3.0.phpAcademic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @since 1.5.0
 */
if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!class_exists('ConfiguratorAttribute')) {

    /**
     * Class configuratorCartDetailModel
     * Override AttributeCore of Prestashop
     * We define new properties use in the module
     */
    class ConfiguratorAttribute extends Attribute
    {
        public $texture_image = 0;
        public $ref_ral = "";

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            self::$definition['fields']['texture_image'] = array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            );

            self::$definition['fields']['ref_ral']= array(
                'type' => self::TYPE_STRING
            );
            
            parent::__construct($id, $id_lang, $id_shop);
        }

        /**
         * Get Attribute by name
         *
         * @param string $name
         * @param string $id_lang
         * @return \AttributeCore
         */
        public static function getAttributeByName($name, $id_lang)
        {
            $sql = "SELECT * "
                . "FROM `" . _DB_PREFIX_ . "attribute_lang` "
                . "WHERE `name` = '" . pSQL($name) . "' "
                . "AND `id_lang` = " . (int)$id_lang;
            $return = Db::getInstance()->getRow($sql);

            if ($return !== false && $return['id_attribute'] !== '') {
                $id_attribute = (int)$return['id_attribute'];

                return new Attribute($id_attribute);
            }

            return new Attribute();
        }
    }
}
