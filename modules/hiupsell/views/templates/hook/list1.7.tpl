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

<table class="list_{$products_info.id_block} table table-bordered upsell_block_product upsell_block_product_list list_1_7 featured-products">
    <tr><th colspan="3" class="title_block">{$products_info.title}</th></tr>
      {foreach from = $all_products item=product}
            <tr class="block_product" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
                <td class="left-block">
                    <div class="product-image-container">
                        <a href="{$product.url}" class="thumbnail product-thumbnail">
                            <img class="img-responsive" src = "{$product.cover.bySize.home_default.url}" alt = "{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}" data-full-size-image-url = "{$product.cover.large.url}">
                        </a>
                    </div>
                </td>
                <td class="center-block">
                    <div>
                        <p itemprop="name">
                            <a href="{$product.url}">{$product.name|truncate:30:'...'}</a>
                        </p>
                    </div>
                    <div class="color_to_pick_list clearfix">
                          {foreach from=$product.all_groups.groups key=id_attribute_group item=group}
                              {if $group.attributes|@count}
                                  <div class="clearfix product-variants-item">
                                      <span class="control-label">{$group.name}</span>
                                      {assign var="groupName" value="group_{$product.id_product|intval}"}
                                      <div class="attribute_list">
                                          {if ($group.group_type == 'select')}
                                              <select data-id-block="{$products_info.id_block|intval}" data-id-product={$product.id_product|intval} name="group[{$id_attribute_group}]" id="group_{$product.id_product|intval}" class="attribute_select group_attr form-control form-control-select attribute_select no-print">
                                                  {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                      <option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'html':'UTF-8'}">{$group_attribute|escape:'html':'UTF-8'}</option>
                                                  {/foreach}
                                              </select>
                                          {elseif ($group.group_type == 'color')}
                                              <ul class="color_to_pick_list clearfix">
                                                  {assign var="default_colorpicker" value=""}
                                                  {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                      {assign var='img_color_exists' value=file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
                                                      <li class="{if $group.default == $id_attribute}selected{/if}">
                                                          <a href="#" data-id-block="{$products_info.id_block|intval}" data-id-color="{$id_attribute|intval}" data-id-product={$product.id_product|intval} id="color_{$id_attribute|intval}" name="{$product.all_groups.colors.$id_attribute.name|escape:'html':'UTF-8'}" class="color_pick{if ($group.default == $id_attribute)} selected{/if}"{if !$img_color_exists && isset($product.all_groups.colors.$id_attribute.value) && $product.all_groups.colors.$id_attribute.value} style="background:{$product.all_groups.colors.$id_attribute.value|escape:'html':'UTF-8'};"{/if} title="{$product.all_groups.colors.$id_attribute.name|escape:'html':'UTF-8'}">
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
                                  </div>
                              {/if}
                          {/foreach}
                      </div>
                </td>
                <td class="right-block">
                    {if $product.show_price}
                        <div class="product-price-and-shipping">
                            {if $product.has_discount}
                              <div class="product-discount">
                                {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                <span class="regular-price">{$product.regular_price}</span>
                              </div>
                            {/if}

                            <div
                              class="product-price h5 {if $product.has_discount}has-discount{/if}"
                              itemprop="offers"
                              itemscope
                              itemtype="https://schema.org/Offer"
                            >
                              <link itemprop="availability" href="{$product.seo_availability}"/>
                              <meta itemprop="priceCurrency" content="{$currency.iso_code}">

                              <div class="current-price">
                                <span itemprop="price" content="{$product.price_amount}">{$product.price}</span>

                                {if $product.has_discount}
                                  {if $product.discount_type === 'percentage'}
                                    <span class="discount discount-percentage">{l s='Save %percentage%' d='Shop.Theme.Catalog' sprintf=['%percentage%' => $product.discount_percentage_absolute]}</span>
                                  {else}
                                    <span class="discount discount-amount">
                                        {l s='Save %amount%' d='Shop.Theme.Catalog' sprintf=['%amount%' => $product.discount_to_display]}
                                    </span>
                                  {/if}
                                {/if}
                              </div>
                            </div>

                            {hook h='displayProductPriceBlock' product=$product type="before_price"}
                            {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                            {hook h='displayProductPriceBlock' product=$product type='weight'}
                        </div>
                    {/if}
                    <form action="{$cart_controller_name}" method="post">
                        <input type="hidden" name="token" value="{$add_to_cart_token}">
                        <input type="hidden" name="id_product" value="{$product.id_product}">
                        <input type="hidden" name="id_customization" value="0">
                        <button
                          class="btn btn-primary add-to-cart"
                          {if !$product.add_to_cart_url}
                            disabled
                          {/if}
                          data-id-block="{$products_info.id_block|intval}"
                          data-id-product-attribute="{$product.id_product_attribute|intval}"
                          data-id-product="{$product.id_product|intval}"
                          data-button-action="add-to-cart"
                          type="submit">
                            <i class="material-icons shopping-cart">&#xE547;</i>
                            {l s='Add to cart' mod='hiupsell'}
                        </button>
                    </form>
                </td>
            </tr>
      {/foreach}
</table>
