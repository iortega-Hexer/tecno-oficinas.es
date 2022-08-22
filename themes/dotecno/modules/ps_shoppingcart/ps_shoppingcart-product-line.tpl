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
 {block name='cart_summary_product_line'}
  <div class="media-left">
    <a href="{$product.url}" title="{$product.name}">
      <img class="media-object" src="{$product.cover.small.url}" alt="{$product.name}">
    </a>
  </div>
  <div class="media-body">
    <span class="product-name">{$product.name}</span>
    <a  class="remove-from-cart" rel="nofollow" href="{$product.remove_from_cart_url}" data-link-action="remove-from-cart" title="{l s='remove from cart' d='Shop.Theme.Actions'}">
      <i class="material-icons">close</i>
    </a>
    <div class="cantidad-editable">
      {if isset($product.is_gift) && $product.is_gift}
        <span class="gift-quantity">{$product.quantity}</span>
      {else}
        <input
          class="js-cart-line-product-quantity"
          data-down-url="{$product.down_quantity_url}"
          data-up-url="{$product.up_quantity_url}"
          data-update-url="{$product.update_quantity_url}"
          data-product-id="{$product.id_product}"
          type="number"
          value="{$product.quantity}"
          name="product-quantity-spin"
          min="{$product.minimal_quantity}"
        />
      {/if}
    </div>
    <div class="cantidad-pr-unitario">
      <span class="product-quantity">{$product.quantity} x </span>
      <span class="product-price">{$product.price}</span>
    </div>
    {hook h='displayProductPriceBlock' product=$product type="unit_price"}
  </div>
{/block}
