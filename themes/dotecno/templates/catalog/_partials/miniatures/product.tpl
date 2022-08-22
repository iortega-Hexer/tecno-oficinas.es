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
{block name='product_miniature_item'}
  <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
    {if !$product.show_price || $product.grouped_features['Consultar']['value'] == 'si' }
    <div class="thumbnail-container sinprecio">
    {else}
    <div class="thumbnail-container">
    {/if}
      {block name='product_thumbnail'}
        <div class="imagen-producto">
          {if $product.cover}
            <a href="{$product.canonical_url}" class="thumbnail product-thumbnail">
              <img
                src="{$product.cover.bySize.producto_lista.url}"
                alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
                data-full-size-image-url="{$product.cover.large.url}"
              />
            </a>
          {else}
            <a href="{$product.canonical_url}" class="thumbnail product-thumbnail">
              <img src="{$urls.no_picture_image.bySize.producto_lista.url}" />
            </a>
          {/if}
          <div class="hover-producto">
            <a class="ver-producto btn-secondary" href="{$product.canonical_url}" title="{$product.name|truncate:30:'...'}">
              {l s='See product' d='Shop.Theme.Catalog'}
            </a>
            <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh-{$product.id}">
              <input type="hidden" name="token" value="{$static_token}">
              <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id-{$product.id}">
              <input type="hidden" name="id_customization" value="{$product.id_customization}" {if $product.id_customization } id="product_customization_id-{$product.id_customization}" {/if}>

              {if $product.grouped_features['Consultar']['value'] == 'si' }
                <a class="btn btn-primary" href="{$product.canonical_url}/#consulta-precio" title="{l s='Consultar precio' d='Shop.Theme.Actions'}">
                  {l s='Consultar precio' d='Shop.Theme.Actions'}
                </a>
              {else}
                {if $product.add_to_cart_url }
                  <button class="btn btn-primary add-to-cart" data-button-action="add-to-cart" type="submit" {if !$product.add_to_cart_url }disabled{/if}>
                    {l s='Add to cart' d='Shop.Theme.Actions'}
                  </button>
                {/if}
              {/if}
            </form>
          </div>
        </div>
      {/block}

      <div class="product-description">
        {block name='product_name'}
          <div class="h3 product-title"><a href="{$product.canonical_url}">{$product.name}{*$product.name|truncate:30:'...'*}</a></div>
        {/block}

        {block name='product_price_and_shipping'}
          {if $product.show_price && $product.grouped_features['Consultar']['value'] != 'si' }
            <div class="product-price-and-shipping">
              {if $product.has_discount}
                {hook h='displayProductPriceBlock' product=$product type="old_price"}

                <span class="sr-only">{l s='Regular price' d='Shop.Theme.Catalog'}</span>
                <span class="regular-price">{$product.regular_price}</span>
                {if $product.discount_type === 'percentage'}
                  <span class="discount-percentage discount-product">{$product.discount_percentage}</span>
                {elseif $product.discount_type === 'amount'}
                  <span class="discount-amount discount-product">{$product.discount_amount_to_display}</span>
                {/if}
              {/if}

              {hook h='displayProductPriceBlock' product=$product type="before_price"}

              <span class="sr-only">{l s='Price' d='Shop.Theme.Catalog'}</span>
              <span class="price">{$product.price}</span>

              {hook h='displayProductPriceBlock' product=$product type='unit_price'}

              {hook h='displayProductPriceBlock' product=$product type='weight'}
            </div>
          {/if}
        {/block}

        {block name='product_reviews'}
          {hook h='displayProductListReviews' product=$product}
        {/block}
      </div>

      <!-- @todo: use include file='catalog/_partials/product-flags.tpl'} -->
      {block name='product_flags'}
        <ul class="product-flags">
          {foreach from=$product.flags item=flag}
            {if $flag.type != 'discount'}
              <li class="product-flag {$flag.type}">{$flag.label}</li>
            {/if}
          {/foreach}
        </ul>
      {/block}

    </div>
  </article>
{/block}
