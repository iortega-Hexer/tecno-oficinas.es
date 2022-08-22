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

if (!class_exists('DmCache')) {

    /**
     * Class configuratorCartDetailModel
     * Override AttributeCore of Prestashop
     * We define new properties use in the module
     */
    class DmCache {

        private $isPrestashopCache = true;

        private static $_instance = null;

        private function __construct() {

        }

        public static function getInstance() {
            if(self::$_instance === null) {
                self::$_instance = new DmCache();
            }

            return self::$_instance;
        }

        /**
         * @param string $key
         * @param string $value
         */
        public function store($key, $value) {
            $key = (string)$key;
            return Cache::store($key, $value);
        }

        /**
         * @param string $key
         *
         * @return mixed
         */
        public function retrieve($key) {
            $key = (string)$key;
            return Cache::getInstance()->retrieve($key);
        }

        /**
         * @param string $key
         *
         * @return bool
         */
        public function isStored($key) {
            $key = (string)$key;
            return Cache::isStored($key);
        }

        /**
         * @param string $key
         */
        public function clean($key = null) {
                return Cache::clean($key);
        }
    }

}
