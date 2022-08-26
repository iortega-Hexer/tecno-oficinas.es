{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if $group.display_style == HsMaDisplayStyle::RADIO}
    {assign var=input_type value='radio'}
    {assign var=flag_checked value=0}
{else}
    {assign var=input_type value='checkbox'}
{/if}
{assign var=image_width value=800}
<table id="product_list_accessory_{$group.id_accessory_group|intval}" class="accessorygroup clear">
    {foreach from=$accessories_groups[$group.id_accessory_group] item=accessory key=key}
        {assign var=is_checked value=0}
        {assign var=is_disabled value=0}
        {if $accessory.is_available_buy_together == 1}
            {if $input_type === 'radio' && !$flag_checked}
                {assign var=is_checked value=1}
                {assign var=flag_checked value=1}
            {else}
                {assign var=is_checked value=0}
            {/if}
            {if $input_type === 'checkbox'}
                {assign var=is_checked value=1}
                {if $buy_main_accessory_together_group[$group.id_accessory_group] == HsMaProductSettingAbstract::BUY_TOGETHER_REQUIRED && $accessory.required == 1}
                    {assign var=is_disabled value=1}
                {/if}
            {/if}
        {/if}
        <tr class="clearfix">
            {if $is_product_page}
                <td class="select_box">
                    <input data-groupid ="{$group.id_accessory_group|intval}" data-id-product-attribute ="{if $accessory.id_product_attribute != 0}{$accessory.id_product_attribute|intval}{else}{$accessory.default_id_product_attribute|intval}{/if}" data-randomId ="{$accessory.random_product_accessories_id|escape:'htmlall':'UTF-8'}" {if $is_checked == 1} checked="checked" {if $is_disabled == 1} disabled='disabled' {/if}{/if} data-required-buy-together ="{$accessory.is_available_buy_together|intval}" type="{$input_type|escape:'htmlall':'UTF-8'}" id='accessories_proudct_{$group.id_accessory_group|intval}_{$accessory.id_accessory|intval}' class="accessory_item" value="{$accessory.id_accessory|intval}" {if $accessory.is_available_for_order} disabled="disabled"{/if} {if $group.display_style == HsMaDisplayStyle::RADIO}name="accessories_{$group.id_accessory_group|intval}"{/if}/>
                </td>
            {/if}
            {if $accessory_configuration_keys.HSMA_SHOW_IMAGES}
                <td class="checkbox_radio_image">
                    <div class="hsma_images-container">

                        <div class="product-cover">
                          {if !$accessory_configuration_keys.HSMA_APPLY_FANCYBOX_TO_IMAGE}
                              <a href="{$accessory.link|escape:'htmlall':'UTF-8'}" target="_blank" class="product_img_link" title="{$accessory.name|escape:'htmlall':'UTF-8'}">
                          {/if}
                                <img class="accessory_image hsma-js-qv-product-cover"  src="{$accessory.image|escape:'htmlall':'UTF-8'}" title="{$accessory.name|escape:'htmlall':'UTF-8'}" alt="{$accessory.name|escape:'htmlall':'UTF-8'}" itemprop="image">
                          {if !$accessory_configuration_keys.HSMA_APPLY_FANCYBOX_TO_IMAGE}
                              </a>
                          {/if}
                          {if $accessory_configuration_keys.HSMA_APPLY_FANCYBOX_TO_IMAGE}
                            <div class="layer hidden-sm-down" data-toggle="modal" data-target="#product-modal_{$group.id_accessory_group}_{$accessory.id_accessory}">
                              <i class="material-icons zoom-in">&#xE8FF;</i>
                            </div>
                          {/if}
                        </div>
                    </div>
                    {if $accessory_configuration_keys.HSMA_APPLY_FANCYBOX_TO_IMAGE}
                    <div class="modal fade hsma_js-product-images-modal" id="product-modal_{$group.id_accessory_group}_{$accessory.id_accessory}">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-body">
                          <figure>
                              <img class="hsma-js-modal-product-cover hsma-product-cover-modal hsma-product_img_link accessory_img_link" width="{$image_width}"  src="{$accessory.image_fancybox|escape:'htmlall':'UTF-8'}" alt="{$accessory.name|escape:'htmlall':'UTF-8'}" title="{$accessory.name|escape:'htmlall':'UTF-8'}" itemprop="image">
                            <figcaption class="image-caption">
                            {block name='product_description_short'}
                              <div id="product-description-short" itemprop="description">{$accessory.description_short nofilter}</div>
                            {/block}
                          </figcaption>
                          </figure>
                          <aside id="thumbnails_{$group.id_accessory_group}_{$accessory.id_accessory}" class="thumbnails js-thumbnails text-xs-center"></aside>
                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  {/if}
                </td>
            {/if}
            <td>
                <p class="block_accessory_name">
                    <a class="ma_accessory_name" href="{$accessory.link|escape:'htmlall':'UTF-8'}" target="{if $accessory_configuration_keys.HSMA_OPEN_ACCESSORIES_IN_NEW_TAB}_blank{/if}" title="{$hs_i18n.click_to_view_details|strip_tags:'UTF-8'}">
                        {$accessory.name|escape:'htmlall':'UTF-8'}
                    </a>
                    {if $accessory.is_available_when_out_of_stock && $accessory_configuration_keys.HSMA_SHOW_ICON_OUT_OF_STOCK}
                        <span class="warning_out_of_stock" title="{$accessory.available_later|escape:'html':'UTF-8'}"></span>
                    {else if $accessory.is_available_for_order && $accessory_configuration_keys.HSMA_SHOW_ICON_OUT_OF_STOCK}
                        <span class="forbidden_ordering" title="{$hs_i18n.out_of_stock|escape:'html':'UTF-8'}"></span>
                    {/if}
                </p>
                <div class="ma_block_qty {if !$accessory_configuration_keys.HSMA_SHOW_CUSTOM_QUANTITY}hide{/if}">
                    <input type="number" class="custom_quantity" {if !$accessory_configuration_keys.HSMA_ALLOW_CUSTOMER_CHANGE_QTY} disabled="disabled" {/if} data-custom-quantity="{$accessory.default_quantity|intval}" name="hsma_quantity" id="quantity_{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}" value="{$accessory.default_quantity|intval}" min='{$accessory.min_quantity|intval}'/>
                    <span class="ma_block_qty_vertical">
                        <button {if !$accessory_configuration_keys.HSMA_ALLOW_CUSTOMER_CHANGE_QTY}disabled="disabled"{/if} class="btn btn-touchspin js-touchspin bootstrap-touchspin-up" type="button">
                            <i class="material-icons touchspin-up"></i>
                        </button>
                        <button {if !$accessory_configuration_keys.HSMA_ALLOW_CUSTOMER_CHANGE_QTY}disabled="disabled"{/if} class="btn btn-touchspin js-touchspin bootstrap-touchspin-down" type="button">
                            <i class="material-icons touchspin-down"></i>
                        </button>
                    </span>
                </div>
                <span class="combination_{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}"></span>
                {if $accessory_configuration_keys.HSMA_SHOW_SHORT_DESCRIPTION}
                    {*<a class="icon-info-sign tooltip accessories-btn" title="{l s='view detail' mod='hsmultiaccessoriespro'}">&nbsp;</a>*}
                    <div class="tooltipster-content" style="display:none;">
                        {if $accessory_configuration_keys.HSMA_SHOW_IMAGES}
                            <img class="accessory_image" src="{$link->getImageLink($accessory.link_rewrite|escape:'htmlall':'UTF-8', $accessory.id_image, {$accessory_image_type})|escape:'html'}" title="{$accessory.name|escape:'htmlall':'UTF-8'}" title="{$hs_i18n.click_to_view_details|escape:'html':'UTF-8'}" />
                        {/if}
                        {$accessory.description_short|escape:'htmlall':'UTF-8'}{*HTML should be kept. PrestaShop accepts html in back office, therefore there is no result to escape here.*}
                    </div>
                {/if}
                <span class="accessory_price">
                    {if $accessory_configuration_keys.HSMA_SHOW_PRICE}
                        {assign var=old_price value=''}
                        {if isset($accessory.cart_rule) && !empty($accessory.cart_rule)}
                            {assign var=old_price value='line_though'}
                        {/if}
                        <span class="{$old_price|escape:'htmlall':'UTF-8'} price_{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}"> {Tools::displayPrice($accessory.price)}</span>
                        {if isset($accessory.cart_rule) && !empty($accessory.cart_rule)}
                            <span class="discount_price final_price_{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}"> {Tools::displayPrice($accessory.final_price)}</span>
                        {/if}
                    {/if}
                    {if $accessory.customizable}
                        {block name='product_customization'}
                            <span class="hsma_customize_group_{$group.id_accessory_group|intval}">
                                <a class="hsma_customize accessory_customization_{$accessory.id_accessory|intval}"  data-id_accessory="{$accessory.id_accessory|intval}" data-randomid ="{$accessory.random_product_accessories_id|escape:'htmlall':'UTF-8'}" title="{$hs_i18n.add_customization_data|escape:'htmlall':'UTF-8'}">
                                    {$hs_i18n.customize|escape:'htmlall':'UTF-8'}
                                    <input type="hidden" name="hsma_id_customization" class="hsma_id_customization" data-isenoughcustomization="{$accessory.is_enough_customization|intval}" value="{$accessory.id_customization|intval}">
                                    <span class="hsma_warning_red {if $accessory.is_enough_customization} hide {/if}" title="{$hs_i18n.please_fill_the_required_custom_fields_to_complete_the_sale|escape:'htmlall':'UTF-8'}"></span>
                                </a>
                            </span>
                        {/block}
                    {/if}
                    {if $accessory.is_package == 1}
                        {block name='product_package'}
                            <span class="hsma_package_group_{$group.id_accessory_group|intval}">
                                <a class="hsma_package accessory_package_{$accessory.id_accessory|intval}" data-idaccessory="{$accessory.id_accessory|intval}" data-randomid ="{$accessory.random_product_accessories_id|escape:'htmlall':'UTF-8'}" title="{$hs_i18n.this_is_a_packaged_product_please_click_here_to_view_the_list_of_products_for_this_pack|escape:'html':'UTF-8'}">
                                    {$hs_i18n.pack_content|escape:'html':'UTF-8'}
                                </a>
                            </span>
                        {/block}
                    {/if}
                    {if $accessory_configuration_keys.HSMA_EACH_ACCESSORY_TO_BASKET && !$accessory.is_available_for_order && $is_enabling_cart_ajax}
                        <a href="{if $utilize_block_cart_ajax}javascript:void(0);{else}{$urls.pages.cart}&amp;add=1&amp;id_product={$accessory.id_accessory|intval}&amp;token={$static_token}{/if}" title="{$hs_i18n.add_to_cart|escape:'html':'UTF-8'}"  rel="{$urls.pages.cart}" class='hs_multi_accessories_add_to_cart' data-product-group="{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}" data-idproduct="{$accessory.id_accessory|intVal}" data-idProductattribute="{if $accessory.id_product_attribute <> 0}{$accessory.id_product_attribute|intVal}{else}{$accessory.default_id_product_attribute|intVal}{/if}"><span></span></a>
                    {/if}
                </span>
            </td>
        </tr>
    {/foreach}
    {if $group.display_style == HsMaDisplayStyle::RADIO}
        {if $buy_main_accessory_together_group[$group.id_accessory_group] == HsMaProductSettingAbstract::BUY_TOGETHER_NO || empty($id_products_buy_together[$group.id_accessory_group])}
            <tr class="clearfix">
                <td width="10%">
                    <input type="radio" name="accessories_{$group.id_accessory_group|intval}" class="accessory_item" value="0"/>
                </td>
                {if $accessory_configuration_keys.HSMA_SHOW_IMAGES}
                    <td>&nbsp;</td>
                {/if}
                <td>
                    <span  class="ma_none_option">{$hs_i18n.none|escape:'html':'UTF-8'}</span>
                </td>
            </tr>
        {/if}
    {/if}
</table>
