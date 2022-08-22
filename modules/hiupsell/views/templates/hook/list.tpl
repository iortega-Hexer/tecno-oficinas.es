{**
* 2013 - 2017 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2017
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*}

<table class="list_{$products_info.id_block} block table table-bordered upsell_block_product upsell_block_product_list list_1_6">
    <tr><th colspan="3" class="title_block">{$products_info.title}</th></tr>
	{foreach from=$all_products item=product name=products}
        <tr class="block_product">
            <td class="left-block">
                <div class="product-image-container">
                    <a class="product_img_link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
                        <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" {if isset($homeSize)} width="{$homeSize.width|escape:'htmlall':'UTF-8'}" height="{$homeSize.height|escape:'htmlall':'UTF-8'}"{/if} itemprop="image" />
                    </a>
                    {if isset($product.new) && $product.new == 1}
                        <a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
                            <span class="new-label">{l s='New' mod='hiupsell'}</span>
                        </a>
                    {/if}
                    {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                        <a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
                            <span class="sale-label">{l s='Sale!' mod='hiupsell'}</span>
                        </a>
                    {/if}
                </div>
                {if isset($product.is_virtual) && !$product.is_virtual}{hook h="displayProductDeliveryTime" product=$product}{/if}
                {hook h="displayProductPriceBlock" product=$product type="weight"}
            </td>
            <td class="center-block">
                <h5 itemprop="name">
                    {if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x '}{/if}
                    <a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
                        {$product.name|truncate:45:'...'|escape:'html':'UTF-8'}
                    </a>
                </h5>
                {capture name='displayProductListReviews'}{hook h='displayProductListReviews' product=$product}{/capture}
                {if $smarty.capture.displayProductListReviews}
                    <div class="hook-reviews">
                    {hook h='displayProductListReviews' product=$product}
                    </div>
                {/if}
                <p class="product-desc" itemprop="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:50:'...' nofilter}
                </p>
                <div class="attributes-content">
                    {if isset($product.all_groups)}
                        <form>
                            <!-- attributes -->
                            <div class="attributes">
                                <div class="clearfix"></div>
                                {foreach from=$product.all_groups.groups key=id_attribute_group item=group}
                                    {if $group.attributes|@count}
                                        <fieldset class="attribute_fieldset">
                                            <label class="attribute_label" {if $group.group_type != 'color' && $group.group_type != 'radio'}for="group_{$product.id_product|intval}"{/if}>{$group.name|escape:'html':'UTF-8'}&nbsp;</label>
                                            {assign var="groupName" value="group_{$product.id_product|intval}"}
                                            <div class="attribute_list">
                                                {if ($group.group_type == 'select')}
                                                    <select data-id-block="{$products_info.id_block|intval}" data-id-product={$product.id_product|intval} name="group[{$id_attribute_group}]" id="group_{$product.id_product|intval}" class="group_attr form-control attribute_select no-print">
                                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                            <option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'html':'UTF-8'}">{$group_attribute|escape:'html':'UTF-8'}</option>
                                                        {/foreach}
                                                    </select>
                                                {elseif ($group.group_type == 'color')}
                                                    <ul class="color_to_pick_list clearfix">
                                                        {assign var="default_colorpicker" value=""}
                                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                            {assign var='img_color_exists' value=file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
                                                            <li{if $group.default == $id_attribute} class="selected"{/if}>
                                                                <!-- <a href="{$link->getProductLink($product)|escape:'html':'UTF-8'}" data-id-color=" -->
                                                                <a href="#" data-id-block="{$products_info.id_block|intval}" data-id-color="{$id_attribute|intval}" data-id-product={$product.id_product|intval} id="color_{$id_attribute|intval}" name="{$product.all_groups.colors.$id_attribute.name|escape:'html':'UTF-8'}" class=" color_pick{if ($group.default == $id_attribute)} selected{/if}"{if !$img_color_exists && isset($product.all_groups.colors.$id_attribute.value) && $product.all_groups.colors.$id_attribute.value} style="background:{$product.all_groups.colors.$id_attribute.value|escape:'html':'UTF-8'};"{/if} title="{$product.all_groups.colors.$id_attribute.name|escape:'html':'UTF-8'}">
                                                                    {if $img_color_exists}
                                                                        <img src="{$img_col_dir}{$id_attribute|intval}.jpg" alt="{$product.all_groups.colors.$id_attribute.name|escape:'html':'UTF-8'}" title="{$product.all_groups.colors.$id_attribute.name|escape:'html':'UTF-8'}" width="20" height="20" />
                                                                    {/if}
                                                                </a>
                                                            </li>

                                                            {if ($group.default == $id_attribute)}
                                                                {$default_colorpicker = $id_attribute}
                                                            {/if}
                                                        {/foreach}
                                                        <input class="group_attr" type="hidden" name="group[{$id_attribute_group}]" value="{$default_colorpicker|intval}">
                                                    </ul>
                                                {elseif ($group.group_type == 'radio')}
                                                    <ul>
                                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                            <li>
                                                                <input data-id-block="{$products_info.id_block|intval}" data-id-product={$product.id_product|intval} type="radio" class="group_attr attribute_radio" name="group[{$id_attribute_group}]" value="{$id_attribute}" {if ($group.default == $id_attribute)} checked="checked"{/if} />
                                                                <span>{$group_attribute|escape:'html':'UTF-8'}</span>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {/if}
                                            </div> <!-- end attribute_list -->
                                        </fieldset>
                                    {/if}
                                {/foreach}
                            </div> <!-- end attributes -->
                        </form>
                    {/if}
                </div>
            </td>
            <td class="right-block">
                {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                    <div class="content_price">
                        {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                            {hook h="displayProductPriceBlock" product=$product type='before_price'}
                            {if !$product.specific_prices && !$product.specific_prices.reduction}
                                {if $products_info.offer_discount}
                                    <span class="price product-price">
                                        {if $products_info.apply_discount == 'percent'}
                                            {convertPrice price=$product.price_without_reduction|floatval - ($product.price_without_reduction * $products_info.reduction_percent / 100)}
                                        {else}
                                            {convertPrice price=$product.price_without_reduction|floatval - $products_info.reduction_amount|floatval}
                                        {/if}
                                    </span>
                                {else}
                                    <span class="price product-price">
                                        {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                                    </span>
                                {/if}
                            {else}
                                <span class="price product-price">
                                    {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                                </span>
                            {/if}
                            {if $product.price_without_reduction > 0 && isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                <span class="old-price product-price">
                                    {displayWtPrice p=$product.price_without_reduction}
                                </span>
                                {hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
                                <span class="price-reduction">
                                    {if $product.specific_prices.reduction_type == 'percentage'}
                                        -{$product.specific_prices.reduction * 100|escape:'htmlall':'UTF-8'}%
                                    {/if}
                                    {if $product.specific_prices.reduction_type == 'amount'}
                                        -{convertPrice price=$product.price_without_reduction|floatval - $product.price_static|floatval}
                                    {/if}
                                </span>
                            {else}
                                {if $products_info.offer_discount}
                                    <span class="old-price product-price">
                                        {displayWtPrice p=$product.price_without_reduction}
                                    </span>
                                    <span class="price-reduction">
                                        {if $products_info.apply_discount == 'percent'}
                                        <!-- <div class="percentage">-{$product.specific_prices.reduction * 100|escape:'htmlall':'UTF-8'}%</div> -->
                                            <div class="percentage">-{$products_info.reduction_percent|escape:'htmlall':'UTF-8'}%</div>
                                        {/if}
                                        <!-- <div class="amount">-{convertPrice price=$product.price_without_reduction|floatval - $product.price_static|floatval}</div> -->
                                        {if $products_info.apply_discount == 'amount'}
                                            <div class="amount">-{convertPrice price=$products_info.reduction_amount}</div>
                                        {/if}
                                    </span>
                                {/if}
                            {/if}
                            {hook h="displayProductPriceBlock" product=$product type="price"}
                            {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                            {hook h="displayProductPriceBlock" product=$product type='after_price'}
                        {/if}
                    </div>
                {/if}
                <div class="button-container">
                    {if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.customizable != 2 && !$PS_CATALOG_MODE}
                        {if (!isset($product.customization_required) || !$product.customization_required) && ($product.allow_oosp || $product.quantity > 0)}
                            {capture}add=1&amp;id_product={$product.id_product|intval}{if isset($product.id_product_attribute) && $product.id_product_attribute}&amp;ipa={$product.id_product_attribute|intval}{/if}{if isset($static_token)}&amp;token={$static_token|escape:'htmlall':'UTF-8'}{/if}{/capture}
                            <a class="button btn btn-default add-to-cart button-medium" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='hiupsell'}" data-id-product-attribute="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-id-block="{$products_info.id_block|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
                                <span>{l s='Add to cart' mod='hiupsell'}</span>
                            </a>
                        {else}
                            <span class="button btn btn-default add-to-cart disabled button-medium">
                                <span>{l s='Add to cart' mod='hiupsell'}</span>
                            </span>
                        {/if}
                    {/if}
{*****************
                    <a class="button lnk_view btn btn-default" href="{$product.link|escape:'html':'UTF-8'}" title="{l s='View' mod='hiupsell'}">
                        <span>{if (isset($product.customization_required) && $product.customization_required)}{l s='Customize' mod='hiupsell'}{else}{l s='More' mod='hiupsell'}{/if}</span>
                    </a>
*****************}
                </div>

{*********
                {if isset($product.color_list)}
                    <div class="color-list-container">{$product.color_list nofilter}</div>
                {/if}
*********}
                <div class="product-flags">
                    {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                        {if isset($product.online_only) && $product.online_only}
                            <span class="online_only">{l s='Online only' mod='hiupsell'}</span>
                        {/if}
                    {/if}
                    {if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                        {elseif isset($product.reduction) && $product.reduction && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
                            <span class="discount">{l s='Reduced price!' mod='hiupsell'}</span>
                        {/if}
                </div>
{*********
                {if (!$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                    {if isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                        <span class="availability" style="min-height: 45px; display: block;">
                            {if ($product.allow_oosp || $product.quantity > 0)}
                                <span class="{if $product.quantity <= 0 && isset($product.allow_oosp) && !$product.allow_oosp} label-danger{elseif $product.quantity <= 0} label-warning{else} label-success{/if}">
                                    {if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later|escape:'htmlall':'UTF-8'}{else}{l s='In Stock' mod='hiupsell'}{/if}{else}{l s='Out of stock' mod='hiupsell'}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now|escape:'htmlall':'UTF-8'}{else}{l s='In Stock' mod='hiupsell'}{/if}{/if}
                                </span>
                            {elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
                                <span class="label-warning">
                                    {l s='Product available with different options' mod='hiupsell'}
                                </span>
                            {else}
                                <span class="label-danger">
                                    {l s='Out of stock' mod='hiupsell'}
                                </span>
                            {/if}
                        </span>
                    {/if}
                {/if}
*********}
            </td>
        </tr>
    {/foreach}
</table>