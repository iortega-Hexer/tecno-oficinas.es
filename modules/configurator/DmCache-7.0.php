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

require __DIR__ . '/vendor/autoload.php';

//namespace phpFastCache\CacheManager;
//use phpFastCache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!class_exists('DmCache')) {

    
    // https://code.tutsplus.com/tutorials/boost-your-website-performance-with-phpfastcache--cms-31031
    
    /**
     * Class configuratorCartDetailModel
     * Override AttributeCore of Prestashop
     * We define new properties use in the module
     */
    class DmCache {

        private $isPrestashopCache;
        
        private static $_instance = null;
            
        protected $fastcache;
        
        private function __construct() {
            $this->isPrestashopCache = (bool)Configuration::get('CONFIGURATOR_CACHE_PS');
            if (!$this->isPrestashopCache) {
                \Phpfastcache\CacheManager::setDefaultConfig(new ConfigurationOption([
                    "path" => __DIR__ . "\cache"
                ]));
                
                $this->fastcache = \Phpfastcache\CacheManager::getInstance('files');
            }
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
            
            if($this->isPrestashopCache) {
                return Cache::store($key, $value);
            }
           
            $cache = $this->fastcache->getItem($key);
            $cache->set($value)->expiresAfter(86400); // 1 journée
                        
            return $this->fastcache->save($cache);
        }

        /**
         * @param string $key
         *
         * @return mixed
         */
        public function retrieve($key) {
            $key = (string)$key;
            
            if($this->isPrestashopCache) {
                return Cache::getInstance()->retrieve($key);
            }
            $cache = $this->fastcache->getItem($key);
            return $cache->get();             
        }

        /**
         * @param string $key
         *
         * @return bool
         */
        public function isStored($key) {
            $key = (string)$key;
            
            if($this->isPrestashopCache) {
                return Cache::isStored($key);
            }
           
            $cache = $this->fastcache->getItem($key);
           
            return !is_null($cache->get());
        }
        
        /**
         * @param string $key
         */
        public function clean($key = null) {
            if($this->isPrestashopCache) {
                return Cache::clean($key);
            }
            
            // fastcache deleteall
            // A tester 
            $this->fastcache->clear();
        }

    }

}
