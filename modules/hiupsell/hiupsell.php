<?php
/**
* 2013 - 2020 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2020
* @license   Addons PrestaShop license limitation
* @version   1.0.1
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once(dirname(__FILE__).'/classes/HiPrestaModule.php');
include_once(dirname(__FILE__).'/classes/upsellblock.php');
include_once(dirname(__FILE__).'/classes/upsellextraproduct.php');

class HiUpSell extends Module
{
    public $psv;
    public $psv_part;
    public $errors = array();
    public $success = array();
    public $clean_db;
    public $default_template;
    public $upsell_on;
    public $upsell_url;
    public $upsell_description;
    public $upsell_left_col;
    public $upsell_right_col;
    public $upsell_content_type;

    public function __construct()
    {
        $this->name = 'hiupsell';
        $this->tab  = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'HiPresta';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        if ((float)Tools::substr(_PS_VERSION_, 0, 3) >= 1.6) {
            $this->bootstrap = true;
        }
        $this->module_key = '8ad0c1223f71ca408109d117148038a1';
        parent::__construct();

        $this->globalVars();
        $this->displayName = $this->l('Upsell');
        $this->description = $this->l('Offer customized discounts');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->HiPrestaClass = new HiPrestaHiUpsellModule($this);
    }
    
    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('ModuleRoutes')
            || !$this->registerHook('displayHome')
            || !$this->registerHook('displayLeftColumn')
            || !$this->registerHook('displayRightColumn')
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('displayFooterProduct')
            || !$this->registerHook('displayShoppingCart')
            || !$this->registerHook('displayShoppingCartFooter')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('hiupsell')
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('backOfficeHeader')
            || !$this->installDb()
            || !$this->HiPrestaClass->createTabs('AdminUpsell', 'AdminUpsell', 'CONTROLLER_TABS_HI_UPSELL', 0)
            ) {
            return false;
        }
        $this->proceedDb();
        return true;
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        $this->HiPrestaClass->deleteTabs('CONTROLLER_TABS_HI_UPSELL');
        if (Configuration::get('HI_UPSELL_CLEAN_DB')) {
            $this->proceedDb(true);
        }
        return true;
    }

    /* Create Db Tabls*/
    private function installDb()
    {
        $res = (bool)Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'upsellblock` (
                `id_block` INT NOT NULL AUTO_INCREMENT ,
                `active` TINYINT NOT NULL,
                `products_type` VARCHAR(100) NOT NULL,
                `products_count` VARCHAR(100) NOT NULL,
                `products` VARCHAR(255) NOT NULL,
                `hook` VARCHAR(100) NOT NULL,
                `block_layout` VARCHAR(100) NOT NULL,
                `offer_discount` TINYINT NOT NULL,
                `apply_discount` VARCHAR(100) NOT NULL,
                `reduction_percent` VARCHAR(100) NOT NULL,
                `reduction_amount` VARCHAR(100) NOT NULL,
                `reduction_currency` VARCHAR(100) NOT NULL,
                `reduction_tax` VARCHAR(100) NOT NULL,
                PRIMARY KEY (`id_block`)
                ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
        ');
        $res &= (bool)Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'upsellblock_lang` (
                `id_block` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `title` VARCHAR(255) NOT NULL ,
              PRIMARY KEY (`id_block`,`id_lang`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
        ');
        $res &= (bool)Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'upsellextraproduct` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `id_parent` int(10) unsigned NOT NULL,
                `id_children` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
        ');
        return $res;
    }

    public function proceedDb($drop = false)
    {
        if (!$drop) {
            Configuration::updateValue('HI_UPSELL_ON', true);
            Configuration::updateValue('HI_UPSELL_URL', 'upsell');
            Configuration::updateValue('HI_UPSELL_LEFT_COL', true);
            Configuration::updateValue('HI_UPSELL_RIGHT_COL', false);
            Configuration::updateValue('HI_UPSELL_PRODUCTS', serialize(array()));
            Configuration::updateValue('HI_UPSELL_DEFAULT_TEMPLATE', false);
            Configuration::updateValue('HI_UPSELL_CLEAN_DB', false);
            Configuration::updateValue('HI_UPSELL_CONTENT_TYPE', 'products');
        } else {
            Configuration::deleteByName('HI_UPSELL_ON');
            Configuration::deleteByName('HI_UPSELL_URL');
            Configuration::deleteByName('HI_UPSELL_LEFT_COL');
            Configuration::deleteByName('HI_UPSELL_RIGHT_COL');
            Configuration::deleteByName('HI_UPSELL_PRODUCTS');
            Configuration::deleteByName('HI_UPSELL_DEFAULT_TEMPLATE');
            Configuration::deleteByName('HI_UPSELL_CLEAN_DB');
            $db_drop = array(
                'upsellblock',
                'upsellblock_lang',
                'upsellextraproduct',
            );
            foreach ($db_drop as $value) {
                DB::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.pSQL($value));
            }
        }
    }

    private function globalVars()
    {
        $this->psv = (float)Tools::substr(_PS_VERSION_, 0, 3);
        $this->psv_part = trim($this->psv, "1.") == '7' ? '17' : '156';
        $this->clean_db = (bool)Configuration::get('HI_UPSELL_CLEAN_DB');
        $this->default_template = (bool)Configuration::get('HI_UPSELL_DEFAULT_TEMPLATE');
        $this->upsell_on = (bool)Configuration::get('HI_UPSELL_ON');
        $this->upsell_url = trim(Configuration::get('HI_UPSELL_URL'));
        foreach (Language::getLanguages(false) as $language) {
            $this->upsell_description[$language['id_lang']] = Configuration::get('HI_UPSELL_DESCRIPTION', $language['id_lang']);
        }
        $this->upsell_left_col = (bool)Configuration::get('HI_UPSELL_LEFT_COL');
        $this->upsell_right_col = (bool)Configuration::get('HI_UPSELL_RIGHT_COL');
        $this->upsell_content_type = Configuration::get('HI_UPSELL_CONTENT_TYPE');
    }


    public function renderShopGroupError()
    {
        $this->context->smarty->assign(
            array('psv' => $this->psv,)
        );
        return $this->display(__FILE__, 'views/templates/admin/shop_group_error.tpl');
    }

    public function renderMenuTabs()
    {
        $tabs = array(
            'settings' => $this->l('General Settings'),
            'upsell_product' => $this->l('Upsell Page'),
            'upsell_blocks' => $this->l('Upsell Blocks'),
            'version' => $this->l('Version'),
            'more_module' => $this->l('More Modules'),
            // 'documentation' => $this->l('Documentation'),
        );
        $this->context->smarty->assign(
            array(
                'psv' => $this->psv,
                'tabs' => $tabs,
                'module_version' => $this->version,
                'module_url' => $this->HiPrestaClass->getModuleUrl(),
                'upsell_key' => Tools::getValue($this->name),
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/menu_tabs.tpl');
    }

    public function renderModuleAdminVariables()
    {
        $this->context->smarty->assign(
            array(
                'psv' => $this->psv,
                'id_lang' => $this->context->language->id,
                'upsell_secure_key' => $this->secure_key,
                'language_count' => count(Language::getLanguages(false)),
                'upsell_module_controller_dir' => $this->context->link->getAdminLink('AdminUpsell'),
                'upsell_remote_url' => $this->context->link->getAdminLink('', false).'ajax_products_list.php?token='.Tools::getAdminTokenLite('AdminProducts').'&q=%QUERY',
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/variables.tpl');
    }

    public function renderDisplayForm($content)
    {
        $this->context->smarty->assign(
            array(
                'psv' => $this->psv,
                'errors' => $this->errors,
                'success' => $this->success,
                'content' => $content
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/display_form.tpl');
    }

    public function renderModalTpl()
    {
        $this->context->smarty->assign(
            array(
                'psv' => $this->psv,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/modal.tpl');
    }

    public function renderModuleAdvertisingForm()
    {
        $this->HiPrestaClass->getModuleContent('A_UPL');
        return $this->display(__FILE__, 'views/templates/admin/moduleadvertising.tpl');
    }

    /**
     * Renders and adds color list HTML for each product in a list
     *
     * @param int  $id_product
     */
    public function getColorlist($id_product = '')
    {
        if ($id_product == '' || !file_exists(_PS_THEME_DIR_.'product-list-colors.tpl')) {
            return;
        }
        $products_need_cache = array();
        $products_need_cache[] = (int)$id_product;
        $colors = false;
        if (count($products_need_cache)) {
            $colors = Product::getAttributesColorList($products_need_cache);
        }
        $tpl = $this->context->smarty->createTemplate(_PS_THEME_DIR_.'product-list-colors.tpl', Product::getColorsListCacheId($id_product));
        if (isset($colors[$id_product])) {
            $tpl->assign(array(
                'id_product'  => $id_product,
                'colors_list' => $colors[$id_product],
                'link'        => Context::getContext()->link,
                'img_col_dir' => _THEME_COL_DIR_,
                'col_img_dir' => _PS_COL_IMG_DIR_
            ));
        }

        if (isset($colors[$id_product])) {
            $color_list_html = $tpl->fetch(_PS_THEME_DIR_.'product-list-colors.tpl', Product::getColorsListCacheId($id_product));
        } else {
            $color_list_html = '';
        }
        return $color_list_html;
    }

    /**
    * getUpsellProduct156 get product info fron ps 1.5 and ps 1.6 versions
    * @param object $result (product type object)
    * @return object
    */
    public function getUpsellProduct156($result, $block = array())
    {
        $link = new Link();
        $products = array();
        $product_details = array();
        $i = 0;
        $id_language = $this->context->language->id;
        if (!empty($result)) {
            foreach ($result as $res) {
                $products[$i] = new Product($res['id_product'], true, $id_language, Shop::getContextShopID());
                if (Validate::isLoadedObject($products[$i])) {
                    if ($products[$i]->active) {
                        $product_details[$i]['name'] = $products[$i]->name;
                        $product_details[$i]['reference'] = $products[$i]->reference;
                        $product_details[$i]['description_short'] = $products[$i]->description_short;
                        $product_details[$i]['minimal_quantity'] = $products[$i]->minimal_quantity;
                        $product_details[$i]['id_product'] = $res['id_product'];
                        $product_details[$i]['link_rewrite'] = $products[$i]->link_rewrite;
                        $product_details[$i]['link'] = $link->getProductLink($res['id_product']);
                        $product_details[$i]['available_for_order'] = $products[$i]->available_for_order;
                        $product_details[$i]['show_price'] = $products[$i]->show_price;
                        $product_details[$i]['specific_prices'] = $products[$i]->specificPrice;
                        $product_details[$i]['customizable'] = $products[$i]->customizable;
                        $product_details[$i]['available_later'] = $products[$i]->available_later;
                        $product_details[$i]['available_now'] = $products[$i]->available_now;
                        $product_details[$i]['color_list'] = $this->getColorlist($res['id_product']);
                        $product_details[$i]['allow_oosp'] = $products[$i]->isAvailableWhenOutOfStock(
                            StockAvailable::outOfStock($res['id_product'])
                        );
                        $product_details[$i]['quantity'] = $products[$i]->getQuantity(
                            $res['id_product'],
                            $products[$i]->getDefaultAttribute($res['id_product'])
                        );
                        $product_details[$i]['quantity_all_versions'] = $products[$i]->getQuantity($res['id_product']);
                        $price_tax_exc = $products[$i]->getPrice(false);
                        $product_details[$i]['price_tax_exc'] = Tools::displayPrice((float)Tools::convertPrice($price_tax_exc));
                        $product_details[$i]['price_without_reduction'] = $products[$i]->getPriceWithoutReduct();
                        $price = $products[$i]->getPriceStatic(trim($res['id_product']), true, null, 2);
                        $product_details[$i]['price_static'] = $price;
                        $product_details[$i]['price'] = Tools::displayPrice((float)Tools::convertPrice($price));
                        $def_attr = $products[$i]->getDefaultAttribute($res['id_product']);
                        if (!empty($def_attr)) {
                            $product_details[$i]['id_product_attribute'] = $def_attr;
                        } else {
                            $product_details[$i]['id_product_attribute'] = 0;
                        }
                        $cover_image = $products[$i]->getCover($res['id_product']);
                        $legend = $products[$i]->getImages($id_language);
                        foreach ($legend as $leg) {
                            if ($leg['cover'] == 1) {
                                $product_details[$i]['legend'] = $leg['legend'];
                            }
                        }
                        /*img_link for upsell_product_list.tpl*/
                        $avalibale_image = Image::getImages($id_language, $res['id_product']);
                        if (isset($avalibale_image) && !empty($avalibale_image)) {
                            $product_details[$i]['id_image'] = $cover_image['id_image'];
                            $product_details[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                                $products[$i]->link_rewrite,
                                $cover_image['id_image'],
                                $this->HiPrestaClass->getImageType('home')
                            );
                        } else {
                            $product_details[$i]['id_image'] = $products[$i]->defineProductImage(
                                $products[$i]->getImages(
                                    $id_language
                                ),
                                $id_language
                            );
                            $product_details[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                                $products[$i]->link_rewrite,
                                $products[$i]->defineProductImage(
                                    $products[$i]->getImages(
                                        $id_language
                                    ),
                                    $id_language
                                ),
                                $this->HiPrestaClass->getImageType('home')
                            );
                        }
                        $i++;
                    }
                }
            }
        }
        return $product_details;
    }

    /**
    * getUpsellProduct17 get product info from ps v1.7
    * @param object $result (product type object)
    * @return object
    */
    public function getUpsellProduct17($result, $block = array())
    {
        if (!is_array($result) || count($result) == 0) {
            return;
        }

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = array();

        foreach ($result as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }
        if ($block && $block['offer_discount']) {
            foreach ($products_for_template as $key => $p) {
                if (!$p['has_discount']) {
                    $priceFormatter = new PriceFormatter();

                    $specific_price = array();
                    $specific_price['id_product'] = $p['id_product'];
                    $specific_price['id_product_attribute'] = 0;
                    $specific_price['id_shop'] = $this->context->shop->id;
                    $specific_price['id_currency'] = $this->context->currency->id;
                    $specific_price['price'] = -1.000000;
                    $specific_price['from_quantity'] = 1;
                    $specific_price['reduction_tax'] = 1;
                    if ($block['apply_discount'] == 'percent') {
                        $specific_price['reduction_type'] = 'percentage';
                        $specific_price['reduction'] = $block['reduction_percent'] / 100;
                        $products_for_template[$key]['price'] = $products_for_template[$key]['price_amount'] - $products_for_template[$key]['price_amount'] * $specific_price['reduction'];
                        $products_for_template[$key]['price_amount'] -= $products_for_template[$key]['price_amount'] * $specific_price['reduction'];
                        $products_for_template[$key]['price_tax_exc'] -= $products_for_template[$key]['price_tax_exc'] * $specific_price['reduction'];
                        $products_for_template[$key]['reduction'] = $products_for_template[$key]['price'] * $specific_price['reduction'];
                        $products_for_template[$key]['reduction_without_tax'] = $products_for_template[$key]['price_tax_exc'] * $specific_price['reduction'];
                    } else {
                        $specific_price['reduction_type'] = 'amount';
                        $specific_price['reduction'] = $block['reduction_amount'];

                        $products_for_template[$key]['price'] -= $specific_price['reduction'];
                        $products_for_template[$key]['price_amount'] -= $specific_price['reduction'];
                        $products_for_template[$key]['price_tax_exc'] -= $specific_price['reduction'];
                        $products_for_template[$key]['reduction'] = $specific_price['reduction'];
                        $products_for_template[$key]['reduction_without_tax'] = $specific_price['reduction'];
                    }

                    $products_for_template[$key]['specific_prices'] = $specific_price;

                    if ($presentationSettings->include_taxes) {
                        $price = $regular_price = $products_for_template[$key]['price'];
                    } else {
                        $price = $regular_price = $products_for_template[$key]['price_tax_exc'];
                    }

                    $products_for_template[$key]['has_discount'] = 1;
                    $products_for_template[$key]['discount_type'] = $specific_price['reduction_type'];

                    $absoluteReduction = new Number((string)$specific_price['reduction']);
                    $absoluteReduction = $absoluteReduction->times(new Number('100'));
                    $negativeReduction = $absoluteReduction->toNegative();
                    $presAbsoluteReduction = $absoluteReduction->round(2, Rounding::ROUND_HALF_UP);
                    $presNegativeReduction = $negativeReduction->round(2, Rounding::ROUND_HALF_UP);

                    // TODO: add percent sign according to locale preferences
                    $products_for_template[$key]['discount_percentage'] = Tools::displayNumber($presNegativeReduction) . '%';
                    $products_for_template[$key]['discount_percentage_absolute'] = Tools::displayNumber($presAbsoluteReduction) . '%';

                    if ($presentationSettings->include_taxes) {
                        $regular_price = $products_for_template[$key]['price_without_reduction'];
                        $products_for_template[$key]['discount_amount'] = $priceFormatter->format(
                            $products_for_template[$key]['reduction']
                        );
                    } else {
                        $regular_price = $products_for_template[$key]['price_without_reduction_without_tax'];
                        $products_for_template[$key]['discount_amount'] = $priceFormatter->format(
                            $products_for_template[$key]['reduction_without_tax']
                        );
                    }

                    $products_for_template[$key]['discount_amount_to_display'] = '-' . $products_for_template[$key]['discount_amount'];
                    $products_for_template[$key]['price_amount'] = $price;
                    $products_for_template[$key]['price'] = $priceFormatter->format($price);
                    $products_for_template[$key]['regular_price_amount'] = $regular_price;
                    $products_for_template[$key]['regular_price'] = $priceFormatter->format($regular_price);

                    if ($products_for_template[$key]['reduction'] < $products_for_template[$key]['price_without_reduction']) {
                        $products_for_template[$key]['discount_to_display'] = $products_for_template[$key]['discount_amount'];
                    } else {
                        $products_for_template[$key]['discount_to_display'] = $products_for_template[$key]['regular_price'];
                    }

                    if (isset($products_for_template[$key]['unit_price']) && $products_for_template[$key]['unit_price']) {
                        $products_for_template[$key]['unit_price'] = $priceFormatter->format($products_for_template[$key]['unit_price']);
                        $products_for_template[$key]['unit_price_full'] = $priceFormatter->format($products_for_template[$key]['unit_price'])
                            . ' ' . $products_for_template[$key]['unity'];
                    } else {
                        $products_for_template[$key]['unit_price'] = $products_for_template[$key]['unit_price_full'] = '';
                    }

                    $products_for_template[$key]['discount_flag'] = array();
                    if ($p['show_price'] && $products_for_template[$key]['reduction']) {
                        if ($products_for_template[$key]['discount_type'] === 'percentage') {
                            $products_for_template[$key]['discount_flag'] = array(
                                'type' => 'discount',
                                'label' => $products_for_template[$key]['discount_percentage'],
                            );
                        } elseif ($products_for_template[$key]['discount_type'] === 'amount') {
                            $products_for_template[$key]['discount_flag'] = array(
                                'type' => 'discount',
                                'label' => $products_for_template[$key]['discount_amount_to_display'],
                            );
                        }
                    }
                }
            }
        }

        return $products_for_template;
    }

    public function getAdminProducts156($result)
    {
        if (!$result || !is_array($result)) {
            return array();
        }

        $link = new Link();
        $product_details = array();
        $i = 0;
        $id_language = $this->context->language->id;
        foreach ($result as $res) {
            $product = new Product($res['id_product'], true, $id_language, Shop::getContextShopID());
            if (Validate::isLoadedObject($product)) {
                $product_details[$i]['name'] = $product->name;
                $product_details[$i]['reference'] = $product->reference;
                $product_details[$i]['id_product'] = $res['id_product'];
                $cover_image = $product->getCover($res['id_product']);

                $avalibale_image = Image::getImages($id_language, $res['id_product']);

                if ($avalibale_image) {
                    $product_details[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                        $product->link_rewrite,
                        $cover_image['id_image'],
                        $this->HiPrestaClass->getImageType('home')
                    );
                } else {
                    $product_details[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                        $product->link_rewrite,
                        $product->defineProductImage(
                            $product->getImages(
                                $id_language
                            ),
                            $id_language
                        ),
                        $this->HiPrestaClass->getImageType('home')
                    );
                }
                $i++;
            }
        }

        return $product_details;
    }

    public function getAdminProducts17($result)
    {
        if (!$result || !is_array($result)) {
            return array();
        }

        $link = new Link();
        $products = array();
        $i = 0;
        $id_language = $this->context->language->id;
        foreach ($result as $res) {
            $product_obj = new Product($res['id_product'], true, $id_language, Shop::getContextShopID());
            if (Validate::isLoadedObject($product_obj)) {
                $products[$i]['id_product'] = $res['id_product'];
                $id_product_attribute = $product_obj->getDefaultAttribute($res['id_product']);
                $avalibale_image = Image::getImages($id_language, $res['id_product']);
                $combination_image = $product_obj->getCombinationImageById($id_product_attribute, $id_language);
                if (!$combination_image) {
                    $image_id = $product_obj->getCover($res['id_product']);
                } else {
                    $image_id = $combination_image;
                }
                if ($avalibale_image) {
                    $products[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                        $product_obj->link_rewrite,
                        $image_id['id_image'],
                        $this->HiPrestaClass->getImageType('home')
                    );
                } else {
                    $products[$i]['img_link'] = Tools::getProtocol(Tools::usingSecureMode()).$link->getImageLink(
                        $product_obj->link_rewrite,
                        $product_obj->defineProductImage(
                            $product_obj->getImages(
                                $id_language
                            ),
                            $id_language
                        ),
                        $this->HiPrestaClass->getImageType('home')
                    );
                }

                $products[$i]['name'] = $product_obj->name;
                $products[$i]['reference'] = $product_obj->reference;
                $i++;
            }
        }
        return $products;
    }

    /**
    * addorupdateUpsellBlock add or update Upsell Block form
    * @param string $type (type update or add)
    * @param int $id (Block id)
    * @return bool true or false
    */
    public function addorupdateUpsellBlock($type, $id_block = null)
    {
        $languages = Language::getLanguages(false);
        $upsellblock = new UpsellBlock($id_block);
        $upsellblock->active = Tools::getValue('block_active');
        foreach ($languages as $lang) {
            $upsellblock->title[$lang['id_lang']] = Tools::getValue('block_title_'.$lang['id_lang']);
        }
        $upsellblock->products_type = Tools::getValue('block_products_type');
        $upsellblock->products_count = Tools::getValue('block_products_count');
        $upsellblock->products = trim(Tools::getValue('inputBlockProducts'));
        $upsellblock->hook = Tools::getValue('block_hook');
        $upsellblock->block_layout = Tools::getValue('block_layout');
        $upsellblock->offer_discount = Tools::getValue('block_offer_discount');
        $upsellblock->apply_discount = Tools::getValue('apply_discount');
        if (Tools::getValue('apply_discount') == 'percent') {
            $upsellblock->reduction_percent = Tools::getValue('reduction_percent');
            $upsellblock->reduction_amount = '0';
        } else {
            $upsellblock->reduction_percent = '0';
            $upsellblock->reduction_amount = Tools::getValue('reduction_amount');
        }
        $upsellblock->reduction_currency = Tools::getValue('reduction_currency');
        $upsellblock->reduction_tax = Tools::getValue('reduction_tax');
        if ($type == 'add') {
            $upsellblock->add();
        } else {
            $upsellblock->update();
        }
    }

    public function renderSettingsForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Clean Database when module uninstalled'),
                        'name' => 'clean_db',
                        'class' => 't',
                        'is_bool' => true,
                        'desc' => $this->l('Not recommended, use this only when you’re not going to use the module'),
                        'values' => array(
                            array(
                                'id' => 'clean_db_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'clean_db_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'upsell_settings_submit',
                    'class' => $this->psv >= 1.6 ? 'btn btn-default pull-right':'button',
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->fields_form = array();
        $helper->submit_action = 'submitBlockSettings';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&'.$this->name.'=settings';
         $helper->module = $this;
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues()
        );
        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'clean_db' => $this->clean_db,
        );
    }

    public function getUpSellProductForm()
    {
        $fields_form = array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Enable Upsell Page'),
                        'name' => 'upsell_on',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'upsell_on_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'upsell_on_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Page URL'),
                        'name' => 'upsell_url',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Page Description'),
                        'name' => 'upsell_description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Enable Left Column'),
                        'name' => 'upsell_left_col',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'upsell_left_col_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'upsell_left_col_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Enable Right Column'),
                        'name' => 'upsell_right_col',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'upsell_right_col_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'upsell_right_col_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Load the layout from your theme.'),
                        'name' => 'default_template',
                        'class' => 't',
                        'is_bool' => true,
                        'desc' => $this->l('Disable this if your theme is highly modified and the module does not work.'),
                        'values' => array(
                            array(
                                'id' => 'default_template_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'default_template_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Content Type'),
                        'name' => 'upsell_content_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'products', 'name' => $this->l('Custom Products')),
                                array('id' => 'accessories', 'name' => $this->l('Product Accessories')),
                                array('id' => 'cross_sells', 'name' => $this->l('Cross sells')),
                                array('id' => 'selected_product', 'name' => $this->l('Selected Products'))
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'search_product',
                        'form_group_class' => 'upsellproduct',
                        'label' => $this->l('Search Product'),
                        'name' => 'product_search',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => $this->psv < 1.6 ? 'button':'btn btn-default pull-right',
                    'name' => 'submit_upsell_product',
                ),
            ),
        );

        if ($this->psv >= 1.7) {
            unset($fields_form['form']['input'][3]);
            unset($fields_form['form']['input'][4]);
            unset($fields_form['form']['input'][5]);
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->fields_form = array();
        $helper->module = $this;
        $helper->submit_action = 'submitBlockSettings';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&'.$this->name.'=upsell_product';

        $products = array();
        $product_info = array();
        $product_ids = unserialize(Configuration::get('HI_UPSELL_PRODUCTS'));
        if (!is_array($product_ids)) {
            $product_ids = array();
        }
        if (!Configuration::get('PS_REWRITING_SETTINGS')) {
            unset($fields_form['form']['input']['1']);
        }
        foreach ($product_ids as $key => $product) {
            $products[$key]['id_product'] = $product;
        }
        $product_info = $this->{'getAdminProducts'.$this->psv_part}($products);


        $helper->tpl_vars = array(
            'psv' => $this->psv,
            'products' => $product_info,
            'id_language' => $this->context->language->id,
            'upload_icon_path' => Tools::getProtocol(Tools::usingSecureMode()).$_SERVER['HTTP_HOST'].$this->getPathUri().'uploads/',
            'fields_value' => $this->getUpsellProductFormValues()
        );
        $helper->override_folder = '/';
        return $helper->generateForm(array($fields_form));
    }

    public function getUpsellProductFormValues()
    {
        return array(
            'upsell_on' => $this->upsell_on,
            'upsell_url' => $this->upsell_url,
            'upsell_description' => $this->upsell_description,
            'upsell_left_col' => $this->upsell_left_col,
            'upsell_right_col' => $this->upsell_right_col,
            'default_template' => $this->default_template,
            'upsell_content_type' => $this->upsell_content_type,
        );
    }

    public function renderSearchProductList()
    {
        $products = array();
        $product_ids = unserialize(Configuration::get('HI_UPSELL_PRODUCTS'));
        if (!is_array($product_ids)) {
            $product_ids = array();
        }
        foreach ($product_ids as $key => $product) {
            $products[$key]['id_product'] = $product;
        }
        $this->context->smarty->assign(array(
            'locations' => 'module',
            'psv' => $this->psv,
            'products' => $this->{'getAdminProducts'.$this->psv_part}($products),
        ));
        return $this->display(__FILE__, 'views/templates/admin/upsell_product_list.tpl');
    }

    /*Upsell Blocks*/
    public function renderUpsellList()
    {
        $fields_list = array(
            'id_block' => array(
                'title' => $this->l('ID'),
                'width' => 60,
                'type' => 'text',
                'search' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ),
            'type' => array(
                'title' => $this->l('Products type'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ),
            'hook' => array(
                'title' => $this->l('Hook'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ),
            'custom_hook' => array(
                'title' => $this->l('Custom Hook'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
            ),
            
        );
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->no_link = true;
        $helper->actions = array('edit', 'delete');
        $helper->identifier = 'id_block';
        $helper->show_toolbar = $this->psv >= 1.6? false : true;
        $helper->title = 'Blocks';
        $helper->table = 'upsellblock';
        $helper->module = $this;
        $helper->toolbar_btn['new'] = array(
            'href' => '#',
            'desc' => $this->l('Add new Block')
        );
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&'.$this->name.'=upsell_blocks';
        $sql_result = UpsellBlock::getAllBlocks();
        $helper->listTotal = count($sql_result);
        $page = ($page = Tools::getValue('submitFilter'.$helper->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper->table.'_pagination')) ? $pagination : 50;
        $sql_result = $this->HiPrestaClass->pagination($sql_result, $page, $pagination);
        return $helper->generateList($sql_result, $fields_list);
    }

    /*
    *
    Add block form
    *
    */
    public function renderUpsellBlockAddForm($type = '', $id_row = null)
    {

        $currencies = array();
        foreach (Currency::getCurrencies(false, true, true) as $carr) {
            array_push($currencies, array(
                'id' => $carr['id_currency'],
                'name' => $carr['iso_code'],
            ));
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'block_row_id',
                    ),
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Enable'),
                        'name' => 'block_active',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'block_active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'block_active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'block_title',
                        'lang' => true,
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Products type'),
                        'name' => 'block_products_type',
                        'options' => array(
                            'query' => array(
                                array('id' => 'products', 'name' => $this->l('Custom Products')),
                                array('id' => 'accessories', 'name' => $this->l('Product Accessories')),
                                array('id' => 'cross_sells', 'name' => $this->l('Cross sells')),
                                array('id' => 'selected_product', 'name' => $this->l('Selected Products'))
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'block_search_product',
                        'label' => $this->l('Products'),
                        'name' => 'block_search_product',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Products count'),
                        'name' => 'block_products_count',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Hook'),
                        'name' => 'block_hook',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'none',
                                    'name' => $this->l('Choose Hook')
                                ),
                                array(
                                    'id' => 'displayHome',
                                    'name' => $this->l('Home')
                                ),
                                array(
                                    'id' => 'displayLeftColumn',
                                    'name' => $this->l('Left')
                                ),
                                array(
                                    'id' => 'displayRightColumn',
                                    'name' => $this->l('Right')
                                ),
                                array(
                                    'id' => 'displayFooter',
                                    'name' => $this->l('Footer')
                                ),
                                array(
                                    'id' => 'displayFooterProduct',
                                    'name' => $this->l('Footer Product')
                                ),
                                array(
                                    'id' => 'displayShoppingCart',
                                    'name' => $this->l('Shopping Cart')
                                ),
                                array(
                                    'id' => 'custom',
                                    'name' => $this->l('Custom'),
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Block layout'),
                        'name' => 'block_layout',
                        'options' => array(
                            'query' => array(
                                array('id' => 'list', 'name' => $this->l('List')),
                                array('id' => 'grid', 'name' => $this->l('Grid'))
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => $this->psv >= 1.6 ? 'switch':'radio',
                        'label' => $this->l('Offer Discount'),
                        'name' => 'block_offer_discount',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'block_offer_discount_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'block_offer_discount_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Apply a discount'),
                        'name' => 'apply_discount',
                        'class' => 't block-disable',
                        'values' => array(
                            array(
                                'id' => 'apply_discount_percent',
                                'value' => 'percent',
                                'label' => $this->l('Percent (%)')
                            ),
                            array(
                                'id' => 'apply_discount_amount',
                                'value' => 'amount',
                                'label' => $this->l('Amount')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Reduction percent'),
                        'name' => 'reduction_percent',
                        'class' => 'percent-disable block-disable',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Reduction amount'),
                        'name' => 'reduction_amount',
                        'class' => 'amount-disable block-disable',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Currency'),
                        'name' => 'reduction_currency',
                        'class' => 'amount-disable block-disable',
                        'options' => array(
                            'query' => $currencies,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Tax'),
                        'name' => 'reduction_tax',
                        'class' => 'amount-disable block-disable',
                        'options' => array(
                            'query' => array(
                                array('id' => '0', 'name' => $this->l('Tax excluded')),
                                array('id' => '1', 'name' => $this->l('Tax included'))
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => ($type == 'update') ? $this->l('Update') : $this->l('Add'),
                    'name' => ($type == 'update') ? 'submit_upsellblock_update' : 'submit_upsellblock_add',
                ),
                'buttons' => array(
                    array(
                        'title' =>  $this->l('Cancel'),
                        'name' => 'submit_cancel_upsellblock',
                        'type' => 'submit',
                        'icon' => 'process-icon-cancel',
                        'class' => 'btn btn-default pull-left',
                    )
                )
            ),
        );

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $this->fields_form = array();
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&'.$this->name.'=upsell_blocks';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $products_id = '';
        $product_content = array();
        if ($id_row !=  null) {
            $products = UpsellBlock::getBlockProductsById($id_row);
            if (!empty($products) && $products['products'] != '') {
                foreach (array_filter(explode(",", $products['products'])) as $key => $id) {
                    $product_content[$key]['id_product'] = $id;
                    $product_content[$key]['name'] = Product::getProductName($id);
                }
                $products_id .= $products['products'];
            }
        }
        $this->context->smarty->assign(array(
            'products_id' => $products_id,
            'product_content' => $product_content,
        ));
        $helper->module = $this;
        $helper->tpl_vars = array(
            'psv' => $this->psv,
            'fields_value' => $this->getAddFieldsValues($type, $id_row)
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getAddFieldsValues($type = '', $id_row = null)
    {
        if ($type == 'update') {
            $upsellblock = new UpsellBlock($id_row);
            return array(
                'block_row_id' => $id_row,
                'block_active' => $upsellblock->active,
                'block_title' => $upsellblock->title,
                'block_products_type' => $upsellblock->products_type,
                'block_products_count' => $upsellblock->products_count,
                'block_hook' => $upsellblock->hook,
                'block_layout' => $upsellblock->block_layout,
                'block_offer_discount' => $upsellblock->offer_discount,
                'apply_discount' => $upsellblock->apply_discount,
                'reduction_percent' => $upsellblock->reduction_percent,
                'reduction_amount' => $upsellblock->reduction_amount,
                'reduction_currency' => $upsellblock->reduction_currency,
                'reduction_tax' => $upsellblock->reduction_tax,
            );
        } else {
            $empty_myltilang = array();
            foreach (Language::getLanguages(false) as $lang) {
                $empty_myltilang[$lang['id_lang']] = '';
            }
            return array(
                'block_row_id' => '',
                'block_active' => true,
                'block_title' => $empty_myltilang,
                'block_products_type' => 'products',
                'block_products_count' => '',
                'block_hook' => 'none',
                'block_layout' => 'list',
                'block_offer_discount' => false,
                'apply_discount' => 'percent',
                'reduction_percent' => '',
                'reduction_amount' => '',
                'reduction_currency' => '1',
                'reduction_tax' => '0',
            );
        }
    }

    public function displayForm()
    {
        $html = '';
        $content = '';
        if (!$this->HiPrestaClass->isSelectedShopGroup()) {
            $html .= $this->renderMenuTabs();
            switch (Tools::getValue($this->name)) {
                case 'settings':
                    $content .= $this->renderSettingsForm();
                    break;
                case 'upsell_product':
                    $content .= $this->getUpSellProductForm();
                    break;
                case 'upsell_blocks':
                    $content .= $this->renderFakeForm();
                    $content .= $this->renderModalTpl();
                    $content .= $this->renderUpsellList();
                    break;
                case 'more_module':
                    $content .= $this->renderModuleAdvertisingForm();
                    break;
                default:
                    $content .= $this->renderSettingsForm();
                    break;
            }
            $html .= $this->renderDisplayForm($content);
            $html .= $this->renderModuleAdminVariables();
        } else {
            $html .= $this->renderShopGroupError();
        }
        $this->context->controller->addCSS($this->_path.'views/css/admin.css', 'all');
        $this->context->controller->addJS(($this->_path).'views/js/admin.js');
        return $html;
    }

    public function creatVersionCategoryTree($id, $category_tree = array())
    {
        if ($this->psv >= 1.6) {
            return array(
                 'id' => $id,
                 'use_checkbox' => true,
                 'selected_categories' => $category_tree,
            );
        } else {
            $root_category = Category::getRootCategory();
            $root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);
            return array(
                'trads' => array(
                    'Root' => $root_category,
                    'selected' => $this->l('Selected'),
                    'Collapse All' => $this->l('Collapse All'),
                    'Expand All' => $this->l('Expand All'),
                    'Check All' => $this->l('Check All'),
                    'Uncheck All' => $this->l('Uncheck All'),
                ),
                'selected_cat' => $category_tree,
                'input_name' => 'categoryBox[]',
                'use_radio' => false,
                'use_search' => false,
                'disabled_categories' => array(),
                'top_category' => Category::getTopCategory(),
                'use_context' => true,
            );
        }
    }

    public function renderFakeForm()
    {
        $cat = array();

        if ($this->psv >= 1.6) {
            $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Fake'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Description'),
                            'name' => 'fake_description',
                            'class' => 'fake_desc',
                            'autoload_rte' => true,
                            'lang' => true,
                            'cols' => 100,
                            'rows' => 10
                        ),
                        array(
                            'type' => 'categories',
                            'label' => $this->l('Select Categories'),
                            'name' => $this->psv >= 1.6 ? 'iroot_category':'categoryBox[]',
                            ''.$this->psv >= 1.6 ? 'tree':'values' => $this->creatVersionCategoryTree('fake_cat', $cat),
                        ),
                    ),
                    
                ),
            );
        } else {
            $fields_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Fake'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Description'),
                            'name' => 'fake_description',
                            'class' => 'fake_desc',
                            'autoload_rte' => true,
                            'lang' => true,
                            'cols' => 100,
                            'rows' => 10
                        )
                    ),
                    
                ),
            );
        }

        $helper = new HelperForm();
        $languages = Language::getLanguages(false);
        foreach ($languages as $key => $language) {
            $languages[$key]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
        }
        $helper->languages = $languages;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $this->fields_form = array();
        $helper->submit_action = 'submitBlockSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $empty_array = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $empty_array[$lang['id_lang']] = '';
        }
        $helper->tpl_vars = array(
            'name_controller' => 'fake_form',
            'fields_value' => array(
                'fake_description' => $empty_array,
            )
        );

        return $helper->generateForm(array($fields_form));
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('upsell_settings_submit')) {
            Configuration::updateValue('HI_UPSELL_CLEAN_DB', (bool)Tools::getValue('clean_db'));
            $this->success[] = $this->l('Successful Save');
        }
        if (Tools::isSubmit('submit_upsell_product')) {
            Configuration::updateValue('HI_UPSELL_ON', (bool)(Tools::getValue('upsell_on')));
            Configuration::updateValue('HI_UPSELL_URL', trim((Tools::getValue('upsell_url'))));
            foreach (Language::getLanguages(false) as $lang) {
                Configuration::updateValue(
                    'HI_UPSELL_DESCRIPTION',
                    array($lang['id_lang'] => Tools::getValue('upsell_description_'.$lang['id_lang'])),
                    true
                );
            }
            Configuration::updateValue('HI_UPSELL_LEFT_COL', (bool)(Tools::getValue('upsell_left_col')));
            Configuration::updateValue('HI_UPSELL_RIGHT_COL', (bool)(Tools::getValue('upsell_right_col')));
            Configuration::updateValue('HI_UPSELL_DEFAULT_TEMPLATE', (bool)Tools::getValue('default_template'));
            Configuration::updateValue('HI_UPSELL_CONTENT_TYPE', Tools::getValue('upsell_content_type'));
            $this->success[] = $this->l('Successful Save');
        }
    }
    
    public function getContent()
    {
        if (Tools::isSubmit('upsell_settings_submit') || Tools::isSubmit('submit_upsell_product')) {
            $this->postProcess();
        }
        $this->globalVars();
        return $this->displayForm();
    }

    public function returnHookListContent($params, $hook)
    {
        $upsell_blocks_product_info = UpsellBlock::getAllBlocksByHook('active', $hook);
        if ($hook == '' || $hook == 'custom') {
            $id_block = isset($params['id'])?$params['id']: null;
            $upsell_blocks_product_info = UpsellBlock::getAllBlocksByIdBlock('active', $id_block);
        } else {
            $upsell_blocks_product_info = UpsellBlock::getAllBlocksByHook('active', $hook);
        }
        if (empty($upsell_blocks_product_info)) {
            return false;
        }
        if (!empty(UpsellBlock::getAllBlocksByHookAndLayout('active', $hook, 'grid'))) {
            $this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
        }
        $cart_ids = array();
        foreach ($this->context->cart->getProducts() as $product) {
            $cart_ids[] = $product['id_product'];
        }
        foreach ($upsell_blocks_product_info as $key => $block) {
            if ($block['products_type'] == 'products') {
                $products_ids = array();
                foreach (array_filter(explode(",", $block['products'])) as $k => $id) {
                    if (!in_array($id, $cart_ids)) {
                        $products_ids[$k]['id_product'] = $id;
                    }
                }
                if ($block['products_count']) {
                    array_splice($products_ids, $block['products_count']);
                }
                $upsell_blocks_product_info[$key]['products'] = $this->{'getUpsellProduct'.$this->psv_part}($products_ids, $block);
                foreach ($upsell_blocks_product_info[$key]['products'] as $t => $product) {
                    $upsell_blocks_product_info[$key]['products'][$t]['all_groups'] = $this->assignAttributesGroups($product['id_product']);
                }
            } elseif ($block['products_type'] == 'accessories') {
                $cart_products = Context::getContext()->cart->getProducts();
                $accessories_id = array();
                foreach ($cart_products as $c_id) {
                    $accessories_obj = new Product((int)$c_id['id_product']);
                    $accessories = $accessories_obj->getAccessories((int)$this->context->language->id, true);
                    if (!empty($accessories)) {
                        foreach ($accessories as $i => $id) {
                            if (!in_array($id['id_product'], $cart_ids)) {
                                $accessories_id[$i]['id_product'] = $id['id_product'];
                            }
                        }
                    }
                }
                if ($block['products_count']) {
                    array_splice($accessories_id, $block['products_count']);
                }
                $upsell_blocks_product_info[$key]['products'] = $this->{'getUpsellProduct'.$this->psv_part}($accessories_id, $block);
                foreach ($upsell_blocks_product_info[$key]['products'] as $t => $product) {
                    $upsell_blocks_product_info[$key]['products'][$t]['all_groups'] = $this->assignAttributesGroups($product['id_product']);
                }
            } elseif ($block['products_type'] == 'cross_sells') {
                $cart_products = Context::getContext()->cart->getProducts();
                $cross_sells_id = array();
                $sells_id = array();
                foreach ($cart_products as $c_id) {
                    $cross_sell = OrderDetail::getCrossSells((int)$c_id['id_product'], $this->context->language->id);
                    if (!empty($cross_sell)) {
                        foreach ($cross_sell as $i => $id) {
                            if (!in_array($id['id_product'], $cart_ids)) {
                                $sells_id[] = $id['id_product'];
                            }
                        }
                    }
                }

                foreach (array_unique($sells_id) as $i => $id) {
                    $cross_sells_id[$i]['id_product'] = $id;
                }
                if ($block['products_count']) {
                    array_splice($cross_sells_id, $block['products_count']);
                }
                $upsell_blocks_product_info[$key]['products'] = $this->{'getUpsellProduct'.$this->psv_part}($cross_sells_id, $block);
                foreach ($upsell_blocks_product_info[$key]['products'] as $t => $product) {
                    $upsell_blocks_product_info[$key]['products'][$t]['all_groups'] = $this->assignAttributesGroups($product['id_product']);
                }
            } elseif ($block['products_type'] == 'selected_product') {
                $selected_product_id = array();
                $selected_id = array();
                $id_product = Tools::getValue('id_product');
                if ($id_product) {
                    $selected_product = UpsellExtraProduct::getAllChildren((int)$id_product);
                    if (!empty($selected_product)) {
                        foreach ($selected_product as $i => $id) {
                            if (!in_array($id['id_children'], $cart_ids)) {
                                $selected_id[] = $id['id_children'];
                            }
                        }
                    }
                } else {
                    $cart_products = Context::getContext()->cart->getProducts();
                    foreach ($cart_products as $c_id) {
                        $selected_product = UpsellExtraProduct::getAllChildren((int)$c_id['id_product']);
                        if (!empty($selected_product)) {
                            foreach ($selected_product as $i => $id) {
                                if (!in_array($id['id_children'], $cart_ids)) {
                                    $selected_id[] = $id['id_children'];
                                }
                            }
                        }
                    }
                }

                foreach (array_unique($selected_id) as $i => $id) {
                    $selected_product_id[$i]['id_product'] = $id;
                }
                if ($block['products_count']) {
                    array_splice($selected_product_id, $block['products_count']);
                }
                $upsell_blocks_product_info[$key]['products'] = $this->{'getUpsellProduct'.$this->psv_part}($selected_product_id, $block);
                foreach ($upsell_blocks_product_info[$key]['products'] as $t => $product) {
                    $upsell_blocks_product_info[$key]['products'][$t]['all_groups'] = $this->assignAttributesGroups($product['id_product']);
                }
            }
        }
        $this->context->smarty->assign(array(
            'psv' => $this->psv,
            'hook' => $hook,
            'cart_controller_name' => $this->context->link->getPageLink('cart'),
            'col_img_dir' => _PS_COL_IMG_DIR_,
            'upsell_module_tpl_dir' => _PS_MODULE_DIR_.$this->name.'/views/templates/front',
            'upsell_products' => $upsell_blocks_product_info,
            'module_tpl_dir' => _PS_MODULE_DIR_.$this->name.'/views/templates/hook',
            'add_to_cart_token' => Tools::getToken(false)
        ));
        return $this->display(__FILE__, 'upsell_blocks.tpl');
    }

    public function hookHeader()
    {
        if ($this->upsell_on) {
            if (Configuration::get('PS_REWRITING_SETTINGS') && $this->upsell_url != '') {
                $upsell_controller = $this->context->link->getPageLink('upsell');
            } else {
                $upsell_controller = $this->context->link->getModuleLink('hiupsell', 'upsell', array());
            }

            $controller = Dispatcher::getInstance()->getController();
            $redirect_pages = array('cart', 'order', 'orderopc');
            if (!$this->context->cookie->__get('upsell_redirected') &&
                in_array($controller, $redirect_pages) &&
                !Tools::getValue('ajax') &&
                !Tools::getValue('token')
            ) {
                $this->context->cookie->__set('upsell_redirected', 1);
                Tools::redirect($upsell_controller);
            } elseif ($controller == 'product' || $controller == 'category' || $controller == 'index') {
                $this->context->cookie->__unset('upsell_redirected');
            }
        }

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocol  = "https:";
        } else {
            $protocol  = "http:";
        }
        $this->context->controller->addCSS($this->_path.'views/css/front_list.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/front_grid.css', 'all');
        $this->context->controller->addJS(($this->_path).'views/js/front.js');
        $this->context->smarty->assign(array(
            'psv' => $this->psv,
            'upsell_order_controller_link' => $protocol.$this->context->link->getModuleLink('hiupsell', 'upsellblocks', array(), $protocol, null, null, true).(Configuration::get('PS_REWRITING_SETTINGS') ? '?' : '&' ).'content_only=1',
        ));
        return $this->display(__FILE__, 'header.tpl');
    }

    public function hookDisplayHome($params)
    {
        return $this->returnHookListContent($params, 'displayHome');
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->returnHookListContent($params, 'displayLeftColumn');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->returnHookListContent($params, 'displayRightColumn');
    }

    public function hookDisplayFooter($params)
    {
        return $this->returnHookListContent($params, 'displayFooter');
    }

    public function hookDisplayFooterProduct($params)
    {
        return $this->returnHookListContent($params, 'displayFooterProduct');
    }

    public function hookDisplayShoppingCart($params)
    {
        if ($this->psv < 1.7) {
            return $this->returnHookListContent($params, 'displayShoppingCart');
        }
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        if ($this->psv >=  1.7) {
            return $this->returnHookListContent($params, 'displayShoppingCart');
        }
    }

    public function hookhiupsell($params)
    {
        return $this->returnHookListContent($params, 'custom');
    }

    public function hookModuleRoutes($params)
    {
        $return = array();
        $return_1 = array(
            'controller' => 'upsell',
            'rule' => ($this->upsell_url ? $this->upsell_url : ''),
            'params' => array(
                'fc' => 'module',
                'module' => 'hiupsell',
            ),
            'keywords' => array()
        );
        $return['upsell'] =  $return_1;
        return $return;
    }


    /*******************************************************************Function block************************************************************/
    /*******************************************************************Function block************************************************************/

    public function getIdProductAttributesByIdAttributes($id_product, $id_attributes)
    {
        if (!is_array($id_attributes)) {
            return 0;
        }

        return Db::getInstance()->getValue('
        SELECT pac.`id_product_attribute`
        FROM `'._DB_PREFIX_.'product_attribute_combination` pac
        INNER JOIN `'._DB_PREFIX_.'product_attribute` pa ON pa.id_product_attribute = pac.id_product_attribute
        WHERE id_product = '.(int)$id_product.' AND id_attribute IN ('.implode(',', array_map('intval', $id_attributes)).')
        GROUP BY id_product_attribute
        HAVING COUNT(id_product) = '.count($id_attributes));
    }

    public function getProductSpecificPriceByProductId($id_product, $id_product_attribute = false, $id_cart = false)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getrow('
            SELECT *
            FROM `'._DB_PREFIX_.'specific_price`
            WHERE `id_product` = '.(int)$id_product.
            ' AND (id_product_attribute = 0 OR id_product_attribute = '.(int)$id_product_attribute.')
            AND id_cart = '.(int)$id_cart);
    }


    /**
     * Assign template vars related to attribute groups and colors
     */
    public function assignAttributesGroups($id_product = null)
    {
        $colors = array();
        $groups = array();
        $combinations = array();
        $product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
        // @todo (RM) should only get groups and not all declination ?
        $attributes_groups = $product->getAttributesGroups($this->context->language->id);
        if (is_array($attributes_groups) && $attributes_groups) {
            $combination_images = $product->getCombinationImages($this->context->language->id);
            $getCover = $product->getCover($id_product);
            $combination_prices_set = array();
            foreach ($attributes_groups as $row) {
                // Color management
                if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) {
                    $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                    $colors[$row['id_attribute']]['name'] = $row['attribute_name'];
                    if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                        $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                    }
                    $colors[$row['id_attribute']]['attributes_quantity'] += (int)$row['quantity'];
                }
                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = array(
                        'group_name' => $row['group_name'],
                        'name' => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default' => -1,
                    );
                }

                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int)$row['id_attribute'];
                }
                if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                }
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];

                $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
                $combinations[$row['id_product_attribute']]['attributes'][] = (int)$row['id_attribute'];
                $combinations[$row['id_product_attribute']]['price'] = (float)Tools::convertPriceFull($row['price'], null, Context::getContext()->currency, false);

                // Call getPriceStatic in order to set $combination_specific_price
                if (!isset($combination_prices_set[(int)$row['id_product_attribute']])) {
                    $combination_specific_price = null;
                    Product::getPriceStatic((int)$product->id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
                    $combination_prices_set[(int)$row['id_product_attribute']] = true;
                    $combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
                }
                $combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
                $combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
                $combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
                $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
                $combinations[$row['id_product_attribute']]['unit_impact'] = Tools::convertPriceFull($row['unit_price_impact'], null, Context::getContext()->currency, false);
                $combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
                if ($row['available_date'] != '0000-00-00' && Validate::isDate($row['available_date'])) {
                    $combinations[$row['id_product_attribute']]['available_date'] = $row['available_date'];
                    $combinations[$row['id_product_attribute']]['date_formatted'] = Tools::displayDate($row['available_date']);
                } else {
                    $combinations[$row['id_product_attribute']]['available_date'] = $combinations[$row['id_product_attribute']]['date_formatted'] = '';
                }

                if (!isset($combination_images[$row['id_product_attribute']][0]['id_image'])) {
                    $combinations[$row['id_product_attribute']]['id_image'] = -1;
                } else {
                    $combinations[$row['id_product_attribute']]['id_image'] = (int)$combination_images[$row['id_product_attribute']][0]['id_image'];
                    if ($row['default_on']) {
                        if (is_array($combination_images[$row['id_product_attribute']])) {
                            foreach ($combination_images[$row['id_product_attribute']] as $tmp) {
                                if ($tmp['id_image'] == $getCover['id_image']) {
                                    $combinations[$row['id_product_attribute']]['id_image'] = (int)$tmp['id_image'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            // wash attributes list (if some attributes are unavailables and if allowed to wash it)
            if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                foreach ($groups as &$group) {
                    foreach ($group['attributes_quantity'] as $key => &$quantity) {
                        if ($quantity <= 0) {
                            unset($group['attributes'][$key]);
                        }
                    }
                }

                foreach ($colors as $key => $color) {
                    if ($color['attributes_quantity'] <= 0) {
                        unset($colors[$key]);
                    }
                }
            }
            foreach ($combinations as $id_product_attribute => $comb) {
                $attribute_list = '';
                foreach ($comb['attributes'] as $id_attribute) {
                    $attribute_list .= '\''.(int)$id_attribute.'\',';
                }
                $attribute_list = rtrim($attribute_list, ',');
                $combinations[$id_product_attribute]['list'] = $attribute_list;
            }

            $this->context->smarty->assign(array(
                'groups' => $groups,
                'colors' => (count($colors)) ? $colors : false,
                'combinations' => $combinations,
                'combinationImages' => $combination_images
            ));
            $all_attr = array(
                'groups' => $groups,
                'colors' => (count($colors)) ? $colors : false,
                'combinations' => $combinations,
                'combinationImages' => $combination_images,
            );
            return $all_attr;
        }
    }

    public function renderExtraProductContent($id_parent)
    {
        $products = array();
        $product_ids = UpsellExtraProduct::getAllChildren($id_parent);
        if (!is_array($product_ids)) {
            $product_ids = array();
        }
        foreach ($product_ids as $key => $product) {
            $products[$key]['id_product'] = $product['id_children'];
        }
        $this->context->smarty->assign(array(
            'locations' => 'product_tab',
            'psv' => $this->psv,
            'products' => $this->{'getAdminProducts'.$this->psv_part}($products),
        ));
        return $this->display(__FILE__, 'views/templates/admin/upsell_product_list.tpl');
    }

    public function hookBackOfficeHeader()
    {
        if (Dispatcher::getInstance()->getController() == 'AdminProducts') {
            $this->context->controller->addCSS(($this->_path).'views/css/adminextratab.css');
            return $this->renderModuleAdminVariables();
        }
    }


    public function hookDisplayAdminProductsExtra($params)
    {

        if ($this->psv >= 1.7) {
            $id_product = $params['id_product'];
        } else {
            $id_product = Tools::getValue('id_product');
        }
        if ($id_product) {
            $this->context->smarty->assign(array(
                'psv' => $this->psv,
                'id_parent' => $id_product,
                'product_content' => $this->renderExtraProductContent($id_product),
                'upsell_path' => Tools::safeOutput($this->_path)
            ));
            return $this->display(__FILE__, 'views/templates/admin/extra_product.tpl');
        }
    }
}
