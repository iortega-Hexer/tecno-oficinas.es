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

class CartController extends CartControllerCore
{
    protected function updateCart()
    {
        parent::updateCart();

        if ((int)Tools::getValue('configurator') === 1) {
            Tools::redirectLink($this->context->link->getPageLink(
                'cart',
                true,
                null,
                'action=show&token=' . Tools::getToken(false),
                false
            ));
        }
    }

    protected function processDeleteProductInCart()
    {
        // FIX : le configurateur créer les "customization" lorsqu'on on personnalise un produit
        // (avant même qu'il soit dans le panier avec le champ "in_cart" à 0)
        // Si on essaye de supprimer un produit du panier et qu'il existe des customization
        // avec le champs "in_cart" à 0, PS les prend quand même en compte et plante

        $add_id = 1000000000;

        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'customization`'
            . ' SET `id_cart` = '. (int)($this->context->cart->id + $add_id)
            . ', `id_product` = '. (int)($this->id_product + $add_id)
            . ' WHERE `id_cart` = ' . (int)$this->context->cart->id
            . ' AND `id_product` = ' . (int)$this->id_product
            . ' AND `id_customization` != ' . (int)$this->customization_id
            . ' AND in_cart = 0'
        );

        parent::processDeleteProductInCart();

        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'customization`'
            . ' SET `id_cart` = '. (int)$this->context->cart->id
            . ', `id_product` = '. (int)$this->id_product
            . ' WHERE `id_cart` = ' . (int)($this->context->cart->id + $add_id)
            . ' AND `id_product` = ' . (int)($this->id_product + $add_id)
            . ' AND `id_customization` != ' . (int)$this->customization_id
            . ' AND in_cart = 0'
        );
    }
}
