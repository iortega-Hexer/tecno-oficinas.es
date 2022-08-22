<?php
/**
 * FMM PrettyURLs
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright Copyright 2019 © Fmemodules All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   PrettyURLs
*/

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class ProductController extends ProductControllerCore
{
	protected $quantity_discounts;
	public function init()
	{
		//Clear the cookie value of id_unique_ipa
		$this->context->cookie->id_unique_ipa = 0;
        $this->context->cookie->write();
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('product_rewrite')));
		$prod_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)\.html/';
		preg_match($prod_pattern, $_SERVER['REQUEST_URI'], $url_array);

		if (isset($url_array[2]) && $url_array[2] != '') {
			$link_rewrite = $url_array[2];
		}
		
		if ($link_rewrite) {
			$id_lang = $this->context->language->id;
			$id_shop = $this->context->shop->id;
			$sql = 'SELECT id_product
					FROM '._DB_PREFIX_.'product_lang
					WHERE link_rewrite = \''.pSQL($link_rewrite).'\' AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
			$id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_product > 0) {
				$_POST['id_product'] = $id_product;
				$_GET['product_rewrite'] = '';
			} else {
				$prod_pattern_sec = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*\-[0-9\pL]*)\.html/';
				preg_match($prod_pattern_sec, $_SERVER['REQUEST_URI'], $url_array_sec);
			
				if (isset($url_array_sec[2]) && $url_array_sec[2] != '') {
					$segments = explode('-', $url_array_sec[2]);
					array_pop($segments);
					$link_rewrite = implode('-', $segments);
				}
				$sql = 'SELECT id_product
					FROM '._DB_PREFIX_.'product_lang
					WHERE link_rewrite = \''.pSQL($link_rewrite).'\' AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
				$id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
				if ($id_product > 0) {
					$_POST['id_product'] = $id_product;
					$_GET['product_rewrite'] = '';
				}
			}
		}
		//Fix for accented chars URLs
		$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
		if ($allow_accented_chars > 0) {
			$id_product = (int)Tools::getValue('id_product');
			if ($id_product <= 0) {
				$id = (int)$this->crawlDbForId($_GET['product_rewrite']);
				if ($id > 0) {
					$_POST['id_product'] = $id;
				}
			}
		}
		parent::init();
	}
	
	public function getTemplateVarProduct()
    {
        $productSettings = $this->getProductPresentationSettings();
        // Hook displayProductExtraContent
        $extraContentFinder = new ProductExtraContentFinder();
		$_id_ipa = (int)Context::getContext()->cookie->__get('id_unique_ipa');
		$requestedIdProductAttribute = 0;
		$group = Tools::getValue('group');
		$id_product = (int)Tools::getValue('id_product');
		if (isset($_POST['ajax']) && !empty($group)) {
			$requestedIdProductAttribute = (int) Product::getIdProductAttributesByIdAttributes(
                    $id_product,
                    $group
                );
		}
        $product = $this->objectPresenter->present($this->product);
        $product['id_product'] = (int) $this->product->id;
        $product['out_of_stock'] = (int) $this->product->out_of_stock;
        $product['new'] = (int) $this->product->new;
		if (Tools::version_compare(_PS_VERSION_, '1.7.4.0', '<')) {
			$product['id_product_attribute'] = ($_id_ipa > 0) ? $_id_ipa : $this->getIdProductAttribute();
		}
		else {
			$product['id_product_attribute'] = ($requestedIdProductAttribute > 0) ? $requestedIdProductAttribute : $this->getIdProductAttribute();
		}
        $product['minimal_quantity'] = $this->getProductMinimalQuantity($product);
        $product['quantity_wanted'] = $this->getRequiredQuantity($product);
        $product['extraContent'] = $extraContentFinder->addParams(array('product' => $this->product))->present();

        $product_full = Product::getProductProperties($this->context->language->id, $product, $this->context);

        $product_full = $this->addProductCustomizationData($product_full);

        $product_full['show_quantities'] = (bool) (
            Configuration::get('PS_DISPLAY_QTIES')
            && Configuration::get('PS_STOCK_MANAGEMENT')
            && $this->product->quantity > 0
            && $this->product->available_for_order
            && !Configuration::isCatalogMode()
        );
        $product_full['quantity_label'] = ($this->product->quantity > 1) ? $this->trans('Items', array(), 'Shop.Theme.Catalog') : $this->trans('Item', array(), 'Shop.Theme.Catalog');
        $product_full['quantity_discounts'] = $this->quantity_discounts;

        if ($product_full['unit_price_ratio'] > 0) {
            $unitPrice = ($productSettings->include_taxes) ? $product_full['price'] : $product_full['price_tax_exc'];
            $product_full['unit_price'] = $unitPrice / $product_full['unit_price_ratio'];
        }

        $group_reduction = GroupReduction::getValueForProduct($this->product->id, (int) Group::getCurrent()->id);
        if ($group_reduction === false) {
            $group_reduction = Group::getReduction((int) $this->context->cookie->id_customer) / 100;
        }
        $product_full['customer_group_discount'] = $group_reduction;

        $presenter = $this->getProductPresenter();
		Context::getContext()->cookie->__unset('id_unique_ipa');
        return $presenter->present(
            $productSettings,
            $product_full,
            $this->context->language
        );
    }
	
	private function getIdProductAttribute()
    {
        $requestedIdProductAttribute = (int)Tools::getValue('id_product_attribute');

        if (!Configuration::get('PS_DISP_UNAVAILABLE_ATTR')) {
            $productAttributes = array_filter(
                $this->product->getAttributeCombinations(),
                function ($elem) {
                    return $elem['quantity'] > 0;
                });
            $productAttribute = array_filter(
                $productAttributes,
                function ($elem) use ($requestedIdProductAttribute) {
                    return $elem['id_product_attribute'] == $requestedIdProductAttribute;
                });
            if (empty($productAttribute) && !empty($productAttributes)) {
                return (int)array_shift($productAttributes)['id_product_attribute'];
            }
        }
        return $requestedIdProductAttribute;
    }
	
	public function displayAjaxRefresh()
    {
		$requestedIdProductAttribute = 0;
		$isPreview = ('1' === Tools::getValue('preview'));
		$group = Tools::getValue('group');
		$id_product = (int)Tools::getValue('id_product');
		if (!empty($group)) {
		$requestedIdProductAttribute = (int) Product::getIdProductAttributesByIdAttributes(
                    $id_product,
                    $group
                );
		}
		$_id_ipa = (int)Context::getContext()->cookie->__get('id_unique_ipa');
		$product = $this->getTemplateVarProduct();
		if (Tools::version_compare(_PS_VERSION_, '1.7.4.0', '<')) {
			$product['id_product_attribute'] = ($_id_ipa > 0) ? $_id_ipa : $product['id_product_attribute'];
		}
		else {
			$product['id_product_attribute'] = ($requestedIdProductAttribute > 0) ? $requestedIdProductAttribute : $product['id_product_attribute'];
			$this->context->cookie->id_unique_ipa = $product['id_product_attribute'];
			$this->context->cookie->write();
		}
        
        $minimalProductQuantity = $this->getMinimalProductOrDeclinationQuantity($product);
        ob_end_clean();
        header('Content-Type: application/json');
        echo Tools::jsonEncode(array(
            'product_prices' => $this->render('catalog/_partials/product-prices'),
            'product_cover_thumbnails' => $this->render('catalog/_partials/product-cover-thumbnails'),
            'product_customization' => $this->render(
                'catalog/_partials/product-customization',
                array(
                    'customizations' => $product['customizations'],
                )
            ),
            'product_details' => $this->render('catalog/_partials/product-details'),
            'product_variants' => $this->render('catalog/_partials/product-variants'),
            'product_discounts' => $this->render('catalog/_partials/product-discounts'),
            'product_add_to_cart' => $this->render('catalog/_partials/product-add-to-cart'),
            'product_additional_info' => $this->render('catalog/_partials/product-additional-info'),
            'product_images_modal' => $this->render('catalog/_partials/product-images-modal'),
            'product_url' => $this->context->link->getProductLink(
                $product['id_product'],
                null,
                null,
                null,
                $this->context->language->id,
                null,
                $product['id_product_attribute'],
                false,
                false,
                true,
                $isPreview ? array('preview' => '1') : array()
            ),
            'product_minimal_quantity' => $minimalProductQuantity,
            'product_has_combinations' => !empty($this->combinations),
            'id_product_attribute' => $product['id_product_attribute'],
        ));
        die();
    }

	protected function assignPriceAndTax()
    {
        $id_customer = (isset($this->context->customer) ? (int) $this->context->customer->id : 0);
        $id_group = (int) Group::getCurrent()->id;
        $id_country = $id_customer ? (int) Customer::getCurrentCountry($id_customer) : (int) Tools::getCountry();

        // Tax
        $tax = (float) $this->product->getTaxesRate(new Address((int) $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
        $this->context->smarty->assign('tax_rate', $tax);

        $product_price_with_tax = Product::getPriceStatic($this->product->id, true, null, 6);
        if (Product::$_taxCalculationMethod == PS_TAX_INC) {
            $product_price_with_tax = Tools::ps_round($product_price_with_tax, 2);
        }

        $id_currency = (int) $this->context->cookie->id_currency;
        $id_product = (int) $this->product->id;
        $id_product_attribute = Tools::getValue('id_product_attribute', null);
        $id_shop = $this->context->shop->id;

        $quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, false, (int) $this->context->customer->id);
        foreach ($quantity_discounts as &$quantity_discount) {
            if ($quantity_discount['id_product_attribute']) {
                $combination = new Combination((int) $quantity_discount['id_product_attribute']);
                $attributes = $combination->getAttributesName((int) $this->context->language->id);
                foreach ($attributes as $attribute) {
                    $quantity_discount['attributes'] = $attribute['name'].' - ';
                }
                $quantity_discount['attributes'] = rtrim($quantity_discount['attributes'], ' - ');
            }
            if ((int) $quantity_discount['id_currency'] == 0 && $quantity_discount['reduction_type'] == 'amount') {
                $quantity_discount['reduction'] = Tools::convertPriceFull($quantity_discount['reduction'], null, Context::getContext()->currency);
            }
        }

        $product_price = $this->product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false);
        $this->quantity_discounts = $this->formatQuantityDiscounts($quantity_discounts, $product_price, (float) $tax, $this->product->ecotax);

        $this->context->smarty->assign(array(
            'no_tax' => Tax::excludeTaxeOption() || !$tax,
            'tax_enabled' => Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'),
            'customer_group_without_tax' => Group::getPriceDisplayMethod($this->context->customer->id_default_group),
        ));
    }
	
	protected function crawlDbForId($rew)
	{
		$id_lang = $this->context->language->id;
		$id_shop = $this->context->shop->id;
		$sql = new DbQuery();
        $sql->select('`id_product`');
        $sql->from('product_lang');
		$sql->where('`id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop.' AND `link_rewrite` = "'.pSQL($rew).'"');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}
}