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

if (!class_exists('ConfiguratorModel')) {
    require_once(dirname(__FILE__) . '/step/ConfiguratorStepAbstract.php');
    require_once(dirname(__FILE__) . '/ConfiguratorStepTabModel.php');
    require_once(dirname(__FILE__) . '/../DmCache.php');

    // Module Viewer 2D
    if (Module::isInstalled('dm_viewer2d') && Module::isEnabled('dm_viewer2d')) {
        require_once(dirname(__FILE__) . '/../../dm_viewer2d/classes/dmconcept/DmImageCover.php');
        require_once(dirname(__FILE__) . '/../../dm_viewer2d/classes/configurator/ConfiguratorCoverModel.php');
    }
    // /Module Viewer 2D

    /**
     * Class configuratorModel
     */
    class ConfiguratorModel extends ObjectModel
    {
        public $id_product;
        public $active;
        public $id_customization_field;
        public $use_base_price = 1;
        public $hide_qty_product = 0;
        public $tab_type = 'tab'; // tab | accordion
		public $hide_product_price = 0;
		public $hide_button_add_to_cart = 0;
		public $tab_force_require_step = 0;

        /**
         * Collections
         */
        public $steps;

        // Module Viewer 2D
        public $visual_rendering = false;
        public $has_cover = false;
        public $cover;
        public $covers;
        // /Module Viewer 2D

        public static $definition = array(
            'table' => 'configurator',
            'primary' => 'id_configurator',
            'fields' => array(
                /* Classic fields */
                'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'id_customization_field' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
                'use_base_price' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'hide_qty_product' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
                'tab_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                // Module Viewer 2D
                'visual_rendering' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
				'hide_product_price' => array('type' => self::TYPE_INT),
				'hide_button_add_to_cart' => array('type' => self::TYPE_INT),
				'tab_force_require_step' => array('type' => self::TYPE_INT),
            )
        );

        public function __construct($id = null, $id_lang = null, $id_shop = null)
        {
            Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
            parent::__construct($id, $id_lang, $id_shop);
            if (Validate::isLoadedObject($this)) {
                $this->fillCustomizationField();

                // Module Viewer 2D
                $this->fillCover();
                // /Module Viewer 2D
            }
        }

        public function update($null_values = false)
        {
            $new_customization_field = $this->checkCustomizationField();

            $return = parent::update($null_values);

            $customization_field = $this->saveChangeCustomizationField($new_customization_field);
            if (!$new_customization_field && !$this->active && Validate::isLoadedObject($customization_field)) {
                $customization_field->delete();
                $product = new Product($this->id_product);
                if ($product->text_fields > 0) {
                    $product->text_fields -= 1;
                }
                $product->customizable = ($product->text_fields > 0) ? 1 : 0;
                $product->save();
            }

            return $return;
        }

        public function checkCustomizationField()
        {
            $customization_field = new CustomizationField($this->id_customization_field);
            if ($this->active && !Validate::isLoadedObject($customization_field)) {
                // Création du champ personnalisé
                $customization_field = new CustomizationField();
                $customization_field->id_product = (int)$this->id_product;
                $customization_field->type = Product::CUSTOMIZE_TEXTFIELD;
                $customization_field->required = 0;
                $customization_field->name = unserialize(Configuration::get('CONFIGURATOR_CUSTOMIZATION_FIELD_NAME'));
                $customization_field->save();

                $this->id_customization_field = $customization_field->id;
                Configuration::updateValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', 1);

                return true;
            }
            return false;
        }

        /**
         * A exécuter après l'enregistrement d'un Configurator, sinon on tombe dans une boucle infinie
         */
        public function saveChangeCustomizationField($new_customization_field)
        {
            $customization_field = new CustomizationField($this->id_customization_field);
            if ($new_customization_field) {
                // Association du champ personnalisé au produit
                $product = new Product((int)$this->id_product);

                // FIX PRODUCT WITHOUT NAME
                /*$languages = Language::getLanguages();
                foreach ($languages as $language) {
                    if (!$product->name[$language['id_lang']]) {
                        $product->name[$language['id_lang']] = 'Product ' . $this->id_product;
                    }
                }
                $product->modifierWsLinkRewrite();*/

                // Signale au produit qu'il possède un champ personnalisé (généré par le configurator)
                $product->customizable = 1;
                $product->text_fields += 1;

                // FIX PRODUCT PRICE
                if (empty($product->price) || $product->price == 0) {
                    $product->price = 0.0;
                }
                $product->save();
            }

            return $customization_field;
        }

        public function hydrate(array $data, $id_lang = null)
        {
            parent::hydrate($data, $id_lang);
            if (Validate::isLoadedObject($this)) {
                $this->fillCustomizationField();
            }
        }

        public function add($autodate = true, $null_values = false)
        {
            $result = parent::add($autodate, $null_values);

            // Product customizable (2) disable the cart button in listing
            $product = new Product($this->id_product, true);
            if (Validate::isLoadedObject($product)) {
                //$product->available_for_order = 0;
                $product->save();
            }

            return $result;
        }

        public function delete()
        {
            $id = (int)$this->id;
            $result = parent::delete();
            // Vide le cache avant la suppression
            configurator::cleanCache();
            if ($result) {
                ConfiguratorStepAbstract::deleteStepsByIdConfigurator($id);
                ConfiguratorStepTabModel::deleteTabsByIdConfigurator($id);
                // Set customizable 0
                $product = new Product($this->id_product, true);
                if (Validate::isLoadedObject($product)) {
                    /**
                     * Evite de tourner en boucle lors de la duplication
                     * avec le Hook after update sur product
                     */
                    //$product->available_for_order = 0;
                    //$product->save();
                    $sSQL = 'UPDATE `' . _DB_PREFIX_ . 'product` '
                        . 'SET `available_for_order` = 0 '
                        . 'WHERE `id_product` = ' . (int)$this->id_product;
                    Db::getInstance()->execute($sSQL);

                    if ($product->text_fields > 0) {
                        $product->text_fields -= 1;
                    }

                    $sSQL = ' UPDATE `' . _DB_PREFIX_ . 'product` '
                        . ' SET `customizable` = ' . (($product->text_fields > 0) ? 1 : 0)
                        . ' WHERE `id_product` = ' . (int)$this->id_product;
                    Db::getInstance()->execute($sSQL);
                }

                $customization_field = new CustomizationField($this->id_customization_field);
                $customization_field->delete();
            }
            return $result;
        }

        protected function fillCollections()
        {
            $this->steps = $this->getSteps();
            foreach ($this->steps as &$step) {
                $step->options = $step->getOptions((int)Context::getContext()->language->id);

                $key = 'ConfiguratorModel::fillCollections-' . $step->id_option_group;
                if (DmCache::getInstance()->isStored($key)) {
                    $step->option_group = DmCache::getInstance()->retrieve($key);
                } else {
                    $option_group = new AttributeGroup((int)$step->id_option_group);
                    DmCache::getInstance()->store($key, $option_group);
                    $step->option_group = $option_group;
                }
            }
        }

        protected function fillCustomizationField()
        {
            $new_customization_field = $this->checkCustomizationField();
            if ($new_customization_field) {
                $this->save();
                $this->saveChangeCustomizationField($new_customization_field);
            }
        }

        /**
         * Méthode pour dupliquer un configurateur de manière récursive
         * entre les différents objets
         * @return boolean
         */
        public function duplicate($id_product)
        {
            $new_configurator = $this->duplicateObject();
            if (!Validate::isLoadedObject($new_configurator)) {
                return false;
            }

            $new_configurator->id_product = (int)$id_product;
            if (!$new_configurator->save()) {
                return false;
            }

            /**
             * Duplication des étapes
             */
            $steps = $this->getSteps();

            /* @var $step ConfiguratorStepAbstract */
            foreach ($steps as $step) {
                if (!$step->duplicate((int)$new_configurator->id)) {
                    // This will delete in cascade the new configurator
                    $new_configurator->delete();
                    return false;
                }
            }

            /**
             * Duplication des tabs
             */
            $tabs = $this->getTabs();
            foreach ($tabs as $tab) {
                if (!$tab->duplicate((int)$new_configurator->id)) {
                    // Same as for the steps
                    $new_configurator->delete();
                    return false;
                }
            }

            /**
             * Duplication du champs personnaliasé
             */
            $customization_field = new CustomizationField();
            $customization_field->id_product = $new_configurator->id_product;
            $customization_field->type = Product::CUSTOMIZE_TEXTFIELD;
            $customization_field->required = 0;
            $customization_field->name = unserialize(Configuration::get('CONFIGURATOR_CUSTOMIZATION_FIELD_NAME'));
            $customization_field->save();

            $new_configurator->id_customization_field = (int)$customization_field->id;
            $new_configurator->save();

            $product = new Product($id_product);
            $product->customizable = 1;
            $product->text_fields += 1;
            $product->save();

            return true;
        }

        public function getSteps()
        {
            $key = 'ConfiguratorModel::getSteps-' . (int)$this->id;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $steps = ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->id);

                DmCache::getInstance()->store($key, $steps);
                return $steps;
            }
        }

        public function getTabs()
        {
            $key = 'ConfiguratorModel::getTabs-' . (int)$this->id;
            if (DmCache::getInstance()->isStored($key)) {
                return DmCache::getInstance()->retrieve($key);
            } else {
                $tabs = ConfiguratorStepTabModel::getTabsByIdConfigurator((int)$this->id);
                DmCache::getInstance()->store($key, $tabs);
                return $tabs;
            }
        }

        public static function getByIdProduct($id_product, $active = false)
        {
            $key = 'ConfiguratorModel::getByIdProduct' . $id_product . ($active ? '-1' : '-0');
            if (DmCache::getInstance()->isStored($key)) {
                $configurator_model = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` c
                        WHERE c.id_product=' . ((int)$id_product) . ($active ? ' AND c.active= 1' : '');
                $result = Db::getInstance()->getRow($sql);
                $configurator_model = new ConfiguratorModel();
                if (!empty($result)) {
                    $configurator_model->hydrate($result);
                }
                DmCache::getInstance()->store($key, $configurator_model);
            }

            return $configurator_model;
        }

        public static function getWithoutCustomizationField()
        {
            $key = 'ConfiguratorModel::getWithoutCustomizationField';
            if (DmCache::getInstance()->isStored($key)) {
                $return = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` c
                        WHERE c.active=1 AND c.id_customization_field=0';
                $results = Db::getInstance()->executeS($sql);
                $configurator_model = new ConfiguratorModel();
                $return = $configurator_model->hydrateCollection(get_class(), $results);
                DmCache::getInstance()->store($key, $return);
            }

            return $return;
        }

        public static function findAllActive()
        {
            $key = 'ConfiguratorModel::findAllIds';
            if (DmCache::getInstance()->isStored($key)) {
                $return = DmCache::getInstance()->retrieve($key);
            } else {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` c
                        WHERE c.active=1';
                $results = Db::getInstance()->executeS($sql);
                $configurator_model = new ConfiguratorModel();
                $return = $configurator_model->hydrateCollection(get_class(), $results);
                DmCache::getInstance()->store($key, $return);
            }

            return $return;
        }

        public static function deleteByIdProduct($id_product)
        {
            $configurator_model = self::getByIdProduct($id_product);
            $configurator_model->delete();
        }

        public static function productHasConfigurator($id_product, $active = false, $return_object = false)
        {
            $configurator = self::getByIdProduct((int)$id_product, $active);
            if ($return_object) {
                if (Validate::isLoadedObject($configurator)) {
                    return $configurator;
                } else {
                    return null;
                }
            } else {
                return Validate::isLoadedObject($configurator);
            }
        }

        public static function isConfiguratedProduct($id_product)
        {
            $sql = 'SELECT id_product FROM `' . _DB_PREFIX_ . 'product`';
            $sql .= ' WHERE `id_product` = ' . (int)$id_product . ' AND `is_configurated` = 1';
            $result = Db::getInstance()->getRow($sql);
            return !empty($result);
        }

        public static function findFirstConfiguratedProductId()
        {
            $sql = 'SELECT id_product FROM `' . _DB_PREFIX_ . 'product` WHERE `is_configurated` = 1';
            $result = Db::getInstance()->getRow($sql);
            return (isset($result['id_product'])) ? (int)$result['id_product'] : 0;
        }

        public static function disableConfiguratedProduct()
        {
            $sSQL = 'UPDATE `' . _DB_PREFIX_ . 'product` '
                . 'SET `active` = 0 '
                . 'WHERE `is_configurated` = 1';
            return Db::getInstance()->execute($sSQL);
        }

        public static function getFullConfiguratorById($id_configurator)
        {
            $configurator = new ConfiguratorModel((int)$id_configurator);
            $configurator->fillCollections();
            return $configurator;
        }

        public static function getFullConfigurator($id_product)
        {
            $configurator = ConfiguratorModel::getByIdProduct((int)$id_product);
            $configurator->fillCollections();
            return $configurator;
        }

        public static function getDefaultImageInCart($id_product, $id_customization, $default_image)
        {
            if (self::productHasConfigurator($id_product)) {
                $cart_detail = ConfiguratorCartDetailModel::getByIdProductAndIdCustomization($id_product, $id_customization);
                if (count($cart_detail) > 0 && Validate::isLoadedObject($cart_detail[0]) && $cart_detail[0]->visual_rendering) {
                    $default_image = $cart_detail[0]->visual_rendering;
                }
            }
            return $default_image;
        }

        public function getIdTaxRulesGroup()
        {
            $product = new Product($this->id_product);
            return $product->id_tax_rules_group;
        }


        // Module Viewer 2D
        /**
         * Fill properties has_layer and layer
         */
        public function fillCover()
        {
            if (Module::isInstalled('dm_viewer2d') && Module::isEnabled('dm_viewer2d')) {
                $this->covers = ConfiguratorCoverModel::getByConfigurator($this->id);
                if (count($this->covers) > 0) {
                    $this->cover = $this->covers[0];
                    $this->has_cover = true;
                }
            }
        }
        // /Module Viewer 2D
    }
}
