{**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div id="_desktop_cart">
  <div class="blockcart cart-preview {if $cart.products_count > 0}active{else}inactive{/if}" data-refresh-url="{$refresh_url}">
    <div class="header">
      {if $cart.products_count > 0}
        <a rel="nofollow" href="{$cart_url}">
      {/if}
        <i class="material-icons shopping-cart">shopping_basket</i>
        <span class="cart-products-count hidden-sm-down">({$cart.products_count}
          <span>{l s='Article' d='Shop.Theme.Checkout'}{if $cart.products_count > 1}s{/if}
          </span>) - <span class="value">{$cart.totals.total.value}</span>
        </span>
      {if $cart.products_count > 0}
        </a>
      {/if}

      {block name='cart_summary_products'}
        <div class="cart-summary-products hidden-sm-down">
          <p><a href="#" data-toggle="collapse" data-target="#cart-summary-product-list2">
            <i class="material-icons dropdown-cart">expand_more</i>
          </a></p>
        </div>

        <div class="mini-cart hidden-sm-down">
            <div id="cart-summary-product-list2" class="ht_cart cart-hover-content collapse">
              {if $cart.products_count > 0}
                  <ul class="media-list">
                      {foreach from=$cart.products item=product}
                          <li class="cart-wishlist-item media">
                              {include 'module:ps_shoppingcart/ps_shoppingcart-product-line.tpl' product=$product}
                          </li>
                      {/foreach}
                  </ul>
                  {block name='cart_summary_subtotals'}
                    {include file='checkout/_partials/cart-summary-subtotals.tpl' cart=$cart}
                  {/block}
                  <div class="clearfix"></div>
                  <div class="cart-summary">
                      <div class="cart-total">
                          <span class="label">{$cart.totals.total.label}</span>
                          <span class="value float-xs-right">{$cart.totals.total.value}</span>
                      </div>
                  </div>
                  <div class="cart-wishlist-action">
                      <a class="cart-wishlist-viewcart" href="{$cart_url}">{l s='View cart' d='Shop.Theme.Actions'}</a>
                      <a class="cart-wishlist-checkout" href="{$urls.pages.order}">{l s='Check Out' d='Shop.Theme.Actions'}</a>
                  </div>
              <!-- </div> -->
              {else}
              <!-- <div class="ht_cart cart-hover-content collapse"> -->
                  <p class="no-item">{l s='There is no item in your cart.' d='Shop.Theme.Actions'}</p>
              {/if}
            </div>
        </div>
      {/block}
    </div>
  </div>
</div>
