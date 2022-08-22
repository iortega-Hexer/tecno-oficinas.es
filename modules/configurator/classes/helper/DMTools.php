<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__) . '/../../DmCache.php');
require_once(dirname(__FILE__) . '/../../configurator.php');

class DMTools extends Helper
{

    /* Static prices cache */
    protected static $_prices = array();

    public static function getVersionMajor()
    {
        static $version = null;

        if ($version == null) {
            $version_with_point = Tools::substr(_PS_VERSION_, 0, 3);
            $version = str_replace('.', '', $version_with_point);
        }

        return $version;
    }

    public static function needToolsUpdate()
    {
        return Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID') !== false || (bool)self::countConfiguratedProduct();
    }

    public static function updateToolsParameters()
    {
        // Products
        Configuration::updateValue('CONFIGURATOR_DELETE_PRODUCT_TOTAL', (int)self::countConfiguratedProduct());
        Configuration::updateValue('CONFIGURATOR_DELETE_PRODUCT_CURRENT', 0);

        // Attributes
        Configuration::updateValue('CONFIGURATOR_DELETE_ATTRIBUTE_TOTAL', (int)self::countConfiguratorAttribute());
        Configuration::updateValue('CONFIGURATOR_DELETE_ATTRIBUTE_CURRENT', 0);
    }

    public static function resetOverrides()
    {
        $module = new Configurator();
        try {
            $module->uninstallOverrides();
            $module->installOverrides();
        } catch (Exception $e) {
            $error = Context::getContext()->getTranslator()->trans('Unable to install override: %s', array($e->getMessage()), 'Admin.Modules.Notification');
            $module->uninstallOverrides();
            return $error;
        }
        return true;
    }

    public static function countConfiguratedProduct()
    {
        $sql = 'SELECT COUNT(*) as counter FROM `' . _DB_PREFIX_ . 'product` WHERE `is_configurated` = 1';
        $result = Db::getInstance()->getRow($sql);
        return (isset($result['counter'])) ? (int)$result['counter'] : 0;
    }

    public static function countConfiguratorAttribute()
    {
        $sql = 'SELECT COUNT(*) as counter FROM `' . _DB_PREFIX_ . 'attribute`';
        $sql .= ' WHERE `id_attribute_group` = ' . (int)Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID');
        $result = Db::getInstance()->getRow($sql);
        return (isset($result['counter'])) ? (int)$result['counter'] : 0;
    }

    public static function findFirstAttributeId()
    {
        $sql = 'SELECT id_attribute FROM `' . _DB_PREFIX_ . 'attribute`';
        $sql .= ' WHERE `id_attribute_group` = ' . (int)Configuration::get('CONFIGURATOR_ATTRIBUTEGROUP_ID');
        $result = Db::getInstance()->getRow($sql);
        return (isset($result['id_attribute'])) ? (int)$result['id_attribute'] : 0;
    }
    
    // Gestion du prix

    public static function getPrice($price, $id_product)
    {
        $context = Context::getContext();
        $address = self::getCustomerAddress();
        $usetax = self::useTax();

        $cache_id = (float)$price . '-' . (int)$context->shop->id . '-' . $id_product . '-' . ($usetax ? '1' : '0');
        if (isset(self::$_prices[$cache_id])) {
            return self::$_prices[$cache_id];
        }

        self::$_prices[$cache_id] = $price;
        if ($usetax && $address) {
            // Tax
            $tax_manager = TaxManagerFactory::getManager(
                $address,
                Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context)
            );
            $product_tax_calculator = $tax_manager->getTaxCalculator();
            self::$_prices[$cache_id] = $product_tax_calculator->addTaxes($price);
        }

        return self::$_prices[$cache_id];
    }

    public static function convertPriceTaxExclToTaxIncl($priceTaxExcl, $idTaxRulesGroup)
    {
        $address = self::getCustomerAddress();
        if ($address === null) {
            return null;
        }
        $tax_manager = TaxManagerFactory::getManager($address, (int)$idTaxRulesGroup);
        $product_tax_calculator = $tax_manager->getTaxCalculator();

        return $product_tax_calculator->addTaxes($priceTaxExcl);
    }

    public static function getCustomerAddress()
    {
        $context = Context::getContext();
        $cart = (isset($context->cart) && Validate::isLoadedObject($context->cart)) ? $context->cart : new Cart();
        $customer = (isset($context->customer) && Validate::isLoadedObject($context->customer)) ? $context->customer : new Customer();
        $key = 'DMTools::getCustomerAddress-' . $cart->id;
        if ( DmCache::getInstance()->isStored($key)) {
            $address = DmCache::getInstance()->retrieve($key);
        } else {
            $address = new Address((int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
            $id_address = (int)$address->id;
            $id_country = (int)$context->country->id;
            $id_state = 0;
            $zipcode = 0;

            if (!$id_address && Validate::isLoadedObject($cart)) {
                $id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            }

            if ($id_address) {
                $address_infos = Address::getCountryAndState($id_address);
                if ($address_infos['id_country']) {
                    $id_country = (int)$address_infos['id_country'];
                    $id_state = (int)$address_infos['id_state'];
                    $zipcode = $address_infos['postcode'];
                }
            } elseif (isset($customer->geoloc_id_country)) {
                $id_country = (int)$customer->geoloc_id_country;
                $id_state = (int)$customer->id_state;
                $zipcode = $customer->postcode;
            }

            // Address
            $address->id_country = $id_country;
            $address->id_state = $id_state;
            $address->postcode = $zipcode;

            DmCache::getInstance()->store($key, $address);
        }

        return $address;
    }

    public static function useTax()
    {
        $context = Context::getContext();

        $id_customer = 0;
        if (!Validate::isLoadedObject($context->customer)) {
            $id_customer = $context->customer->id;
        }
        $key = 'DMTools::useTax-' . $id_customer;
        if (DmCache::getInstance()->isStored($key)) {
            $usetax = DmCache::getInstance()->retrieve($key);
        } else {
            $usetax = (int)Configuration::get('PS_TAX');
            if (Product::getTaxCalculationMethod() === 1) {
                $usetax = false;
            }

            if (Validate::isLoadedObject(Context::getContext()->customer)) {
                $groups = Context::getContext()->customer->getGroups();
                if (isset($groups[0])) {
                    $group = new Group((int)$groups[0]);
                    if (Validate::isLoadedObject($group)) {
                        // 1 = H.T autre TTC
                        $usetax = (int)($group->price_display_method != 1);
                    }
                }
            }

            $address = self::getCustomerAddress();
            if (Validate::isLoadedObject($address)) {
                $address_infos = Address::getCountryAndState($address->id);
                if ($usetax != false
                    && !empty($address_infos['vat_number'])
                    && $address_infos['id_country'] != Configuration::get('VATNUMBER_COUNTRY')
                    && Configuration::get('VATNUMBER_MANAGEMENT')) {
                    $usetax = false;
                }
            }

            if (Tax::excludeTaxeOption()) {
                $usetax = false;
            }

            DmCache::getInstance()->store($key, $usetax);
        }

        return (bool)$usetax;
    }

    public static function displayPrice($price ,ConfiguratorModel $configurator = null)
    {
    	$key = 'currentGroupDisplayPrice-' . Context::getContext()->cookie->id_guest;
    	$currentGroupDisplayPrice = true;
        $currentDisplayAmount = true;
    	if (DmCache::getInstance()->isStored($key)) {
			$currentGroupDisplayPrice = DmCache::getInstance()->retrieve($key);
		} else {
			$grp = Group::getCurrent();                
			if(Validate::isLoadedObject($grp) && Validate::isLoadedObject($configurator)) {
				$currentGroupDisplayPrice = (bool)$grp->show_prices;
                $currentDisplayAmount = !(bool)$configurator->hide_button_add_to_cart;
				DmCache::getInstance()->store($key, $currentGroupDisplayPrice ,$currentDisplayAmount);
			}
                        
                        
		}
        $priceConversion = Tools::convertPrice($price);
        
        if ($currentGroupDisplayPrice && $currentDisplayAmount){
            return Tools::displayPrice($priceConversion);
        }
        else{
            return 0;
            
        }
        
        
    }

    public static function getTaxDetails($priceTaxExcl, $idTaxRulesGroup)
    {
        $details = array(
            'id_tax_rules_group' => $idTaxRulesGroup,
            'price_tax_excl' => $priceTaxExcl,
            'price_tax_incl' => self::convertPriceTaxExclToTaxIncl($priceTaxExcl, $idTaxRulesGroup),
            'tax' => 0
        );
        $details['tax'] = $details['price_tax_incl'] - $details['price_tax_excl'];
        return $details;
    }

    public static function getDiscountPrice($price, $id_product)
    {
        $id_cart = null;
        $cart = Context::getContext()->cart;
        if(Validate::isLoadedObject($cart)) {
            $id_cart = (int)$cart->id;
        }

        $key = 'DMTools::getDiscountPrice-2-' . $id_cart;
        if (DmCache::getInstance()->isStored($key)) {
            $group_reduction = DmCache::getInstance()->retrieve($key);
        } else {
            $id_group = 0;
            if (Validate::isLoadedObject(Context::getContext()->customer)) {
                $id_group = (int)Context::getContext()->customer->id_default_group;
            }
            // Group reduction
            $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
            if ($reduction_from_category !== false) {
                $group_reduction = (float)$reduction_from_category;
            } else { // apply group reduction if there is no group reduction for this category
                $group_reduction = (($reduc = Group::getReductionByIdGroup($id_group)) != 0) ? ($reduc / 100) : 0;
            }
            DmCache::getInstance()->store($key, $group_reduction);
        }
        $price -= $price * $group_reduction;

        return $price;
    }


    // Gestion de la base de données

    public static function existColumnInTable($table_name, $column_name)
    {
        $sql = 'DESCRIBE ' . _DB_PREFIX_ . $table_name;
        $columns = Db::getInstance()->executeS($sql);
        $found = false;

        foreach ($columns as $col) {
            if ($col['Field'] == $column_name) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    public static function existTableInDatabase($table_name)
    {
        $sql = 'SHOW TABLES';
        $tables = Db::getInstance()->executeS($sql);

        foreach ($tables as $table) {
            $table_values = array_values($table);
            $table_find = isset($table_values[0]) ? $table_values[0] : '';

            if ($table_find == _DB_PREFIX_ . $table_name) {
                return true;
            }
        }

        return false;
    }

    public static function existIndexInTable($table_name, $column_name)
    {
        $sql = 'SHOW INDEX FROM `' . _DB_PREFIX_ . $table_name . '`';
        $indexes = Db::getInstance()->executeS($sql);

        foreach ($indexes as $index) {
            if (isset($index['Column_name']) && $index['Column_name'] == $column_name) {
                return true;
            }
        }

        return false;
    }

    public static function deleteUnusedCartDetail($number = 1, $day = 2)
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . (int)$day . 'D'));

        // CLEAN CART
        $sql = 'SELECT id_cart FROM `' . _DB_PREFIX_ . 'configurator_cart_detail`';
        $sql .= ' WHERE id_order = 0';
        $sql .= ' AND added_in_cart = 0';
        $sql .= ' AND EXISTS ( SELECT id_cart FROM `' . _DB_PREFIX_ . 'cart` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_cart_detail`.`id_cart` = c.`id_cart`';
        $sql .= ' AND c.`date_upd` < "' . $date->format('Y-m-d H:i:s') . '"';
        $sql .= ' AND NOT EXISTS ( SELECT id_cart FROM `' . _DB_PREFIX_ . 'cart_product` cp';
        $sql .= ' WHERE cp.id_cart = c.id_cart ))';
        $sql .= ' LIMIT ' . (int)$number;
        $carts = Db::getInstance()->executeS($sql);

        foreach ($carts as $cart) {
            $cart_model = new Cart($cart['id_cart']);
            $cart_model->delete();

            // Non utile car on utilise déjà un Hook pour supprimer le cart_detail lié à un cart supprimé
            // ConfiguratorCartDetailModel::deleteByIdCart((int)$cart['id_cart']);
        }

        return true;
    }

    public static function countUnusedCartDetail($day = 2)
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . (int)$day . 'D'));

        // CLEAN CART
        $sql = 'SELECT id_cart FROM `' . _DB_PREFIX_ . 'configurator_cart_detail`';
        $sql .= ' WHERE id_order = 0';
        $sql .= ' AND added_in_cart = 0';
        $sql .= ' AND EXISTS ( SELECT id_cart FROM `' . _DB_PREFIX_ . 'cart` c';
        $sql .= ' WHERE `' . _DB_PREFIX_ . 'configurator_cart_detail`.`id_cart` = c.`id_cart`';
        $sql .= ' AND c.`date_upd` < "' . $date->format('Y-m-d H:i:s') . '"';
        $sql .= ' AND NOT EXISTS ( SELECT id_cart FROM `' . _DB_PREFIX_ . 'cart_product` cp';
        $sql .= ' WHERE cp.id_cart = c.id_cart ))';
        $carts = Db::getInstance()->executeS($sql);

        return count($carts);
    }
}
