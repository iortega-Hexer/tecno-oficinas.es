{**
* Multi Accessories Pro
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<tr class="has_accessory">
    <td colspan="4">
        {foreach from=$groups item=group name=group_accessory}
            <table class="table tableDnD accessory_group_product {if ($is_ps17)}table-no-bordered{/if}" data-id-group="{$group.id_accessory_group|intval}" data-id-main-product="{$id_main_product|intval}">
                <thead>
                    <tr>
                        <th class="center">{$hs_i18n.accessory|escape:'html':'UTF-8'}</th>
                        <th class="center name">{$hs_i18n.accessory_name|escape:'html':'UTF-8'}</th>
                        {if $show_custom_quantity == 1}
                            <th class="center">{$hs_i18n.default_quantity|escape:'html':'UTF-8'}</th>
                        {/if}
                        <th class="center">{$hs_i18n.min_qty|escape:'html':'UTF-8'}</th>
                        <th class="center">{$hs_i18n.price|escape:'html':'UTF-8'}</th>
                        <th class="center">
                            {$hs_i18n.discount|escape:'html':'UTF-8'} ({$hs_i18n.percent|escape:'htmlall':'UTF-8'})<br/>
                        </th>
                        <th class="center">{$hs_i18n.final_price|escape:'html':'UTF-8'}</th>
                        <th class="center position-heading">{$hs_i18n.position|escape:'html':'UTF-8'}</th>
                        <th class="center">{$hs_i18n.action|escape:'html':'UTF-8'}</th>
                    </tr>
                </thead>
                <tbody class="accessory_list" id="accessory_list_{$group.id_accessory_group|intval}_{$id_main_product|intval}">
                    {if $show_custom_quantity}
                        {assign var=colspan value=8}{*+1 when enabling price column*}
                    {else}
                        {assign var=colspan value=9}{*+1 when enabling price column*}
                    {/if}
                    {foreach from=$product_accessories item=accessory name=accessory_name}
                        <tr id="{$group.id_accessory_group|intval}_{$accessory.id_accessory_group_product|intval}" data-id-accessory-group-product="{$accessory.id_accessory_group_product|intval}" class="accessory_row" data-colspan="{$colspan|intval}" >
                            <td class="center image">
                                <a title="{$accessory.name|escape:'html':'UTF-8'}" href="index.php?controller=adminproducts&amp;id_product={$accessory.id_accessory|intval}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}" target="_blank">
                                    <img src="{$accessory.image|escape:'htmlall':'UTF-8'}">
                                </a>
                            </td>
                            <td>
                                <div class="col-lg-8">
                                    {$accessory.name|escape:'html':'UTF-8'} ({$accessory.reference|escape:'html':'UTF-8'})
                                </div>
                                {include file="./display_accessory_combinations.tpl"}
                            </td>
                            {if $show_custom_quantity == 1}
                                <td class="hsma_quantity center default_quantity">
                                    <span>
                                        <a title="up" class="qty_up" href="javascript:void(0);">+</a>
                                        <a title="down" class="qty_down" href="javascript:void(0);">-</a>
                                    </span>
                                    <span class="" style="cursor: pointer;">
                                        <input type="text" name="default_quantity" value="{$accessory.default_quantity|intval}" data-quantity="{$accessory.default_quantity|intval}">
                                    </span>
                                </td>
                            {/if}
                            <td class="hsma_quantity center minimum_quantity">
                                <span>
                                    <a title="up" class="qty_up" href="javascript:void(0);">+</a>
                                    <a title="down" class="qty_down" href="javascript:void(0);">-</a>
                                </span>
                                <span class="">
                                    <input type="text" name="minimum_quantity" value="{$accessory.min_quantity|intval}" data-min-quantity="{$accessory.min_quantity|intval}" data-default-quantity="{$accessory.default_quantity|intval}">
                                </span>
                            </td>
                            <td class="hsma_price center price" data-old-price="{$accessory.old_price|floatval}">
                                {convertPrice price=$accessory.old_price|escape:'quotes':'UTF-8'}
                            </td>    
                            <td class="hsma_discount center discount">
                                {if isset($accessory.cart_rule) && !empty($accessory.cart_rule)}
                                    {if $accessory.cart_rule['reduction_percent'] > 0}
                                        {assign var=discount_value value=$accessory.cart_rule['reduction_percent']}
                                        {assign var=discount_type value=0}
                                    {else}    
                                        {assign var=discount_value value=$accessory.cart_rule['reduction_amount']}
                                        {assign var=discount_type value=1}
                                    {/if}
                                {else}
                                    {assign var=discount_value value=0}
                                    {assign var=discount_type value=0}
                                {/if}
                                <div class="discount_block">
                                    <input class="discount_value accessory_discount_{$accessory.id_accessory|intval}" type="text" name="discount_value" value="{if isset($discount_value)}{$discount_value|floatval}{else}0{/if}">
                                    {if isset($discount_type) && $discount_type == 1}({$currency->sign|escape:'htmlall':'UTF-8'}){/if}
                                </div>
                            </td> 
                            <td class="hsma_final_price center final_price accessory_final_price_{$accessory.id_accessory|intval}">
                                {convertPrice price=$accessory.final_price|escape:'quotes':'UTF-8'}
                            </td>
                            <td id="td_{$group.id_accessory_group|intval}_{$accessory.id_accessory_group_product|intval}" class="pointer dragHandle center position-value">
                                <div class="dragGroup">
                                    <div class="positions">
                                        {$accessory.position|escape:'htmlall':'UTF-8'}
                                    </div>
                                </div>
                            </td>
                            <td class="center">
                                <span class="delete" name="accessory_{$group.id_accessory_group|escape:'html':'UTF-8'}_{$accessory.id_accessory|escape:'html':'UTF-8'}" style="cursor: pointer;">
                                    <img class="hsma_icon" src="{$img_path|escape:'html':'UTF-8'}delete.png" width="30"/>
                                </span>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        {/foreach}
    </td>
</tr>