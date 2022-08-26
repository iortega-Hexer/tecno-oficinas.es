{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{foreach from=$groups item=group name=group_accessory}
    {if isset($accessories_groups[$group.id_accessory_group])}
        {assign var=count_accessories value=count($accessories_groups[$group.id_accessory_group])}
    {else}
        {assign var=count_accessories value=0}
    {/if}
    <div class="hsma_accessory_group group cleafix" data-id-group="{$group.id_accessory_group|escape:'html':'UTF-8'}">
        <h4 class="title">
            {if ($is_ps17)}
            <i class="material-icons add">&#xE145;</i>
            {else}
            <i class="icon icon-collapse-alt icon-expand-alt">&nbsp;</i>
            {/if}
        {$group.name|escape:'html':'UTF-8'} (<span class="xx-items-inside">{if $count_accessories > 1}{sprintf($hs_i18n.items_inside|escape:'html':'UTF-8', $count_accessories|intval)}{else}{sprintf($hs_i18n.item_inside|escape:'html':'UTF-8', $count_accessories|intval)}{/if}</span>)</h4>
        <div id="div_accessories_{$group.id_accessory_group|escape:'html':'UTF-8'}" class="content_group expand" style="display:none;">
            <input type="text" name="group_{$group.id_accessory_group|intval}" class="autocomplete_search_accessories" placeholder="{$hs_i18n.search_for_items|escape:'htmlall':'UTF-8'}">
            <table class="table tableDnD accessory_group_product {if ($is_ps17)}table-no-bordered{/if}">
                <thead>
                    <tr>
                        <th class="center">{$hs_i18n.accessory|escape:'html':'UTF-8'}</th>
                        <th class="center name">{$hs_i18n.displayed_name|escape:'html':'UTF-8'}</th>
                        <!--th class="center">{$hs_i18n.price|escape:'html':'UTF-8'}</th-->{*when enabling, see the comments below*}
                        {if $show_custom_quantity == 1}
                            <th class="center">{$hs_i18n.default_quantity|escape:'html':'UTF-8'}</th>
                            {/if}                        
                        <th class="center">{$hs_i18n.min_qty|escape:'html':'UTF-8'}</th>
                        <th class="center">{$hs_i18n.price|escape:'html':'UTF-8'}</th>
                        <th class="center">
                            {$hs_i18n.discount|escape:'html':'UTF-8'} ({$hs_i18n.percent|escape:'htmlall':'UTF-8'})<br/>
                        </th>
                        <th class="center">{$hs_i18n.final_price|escape:'html':'UTF-8'}</th>
                        <th class="center buy_together_required {if $buy_together_default != HsMaProductSetting::BUY_TOGETHER_REQUIRED}hide{/if}">{$hs_i18n.required|escape:'html':'UTF-8'}</th>
                        <th class="center {if (!$is_ps17)}position-heading{/if}">{$hs_i18n.position|escape:'html':'UTF-8'}</th>
                        <th class="center">{$hs_i18n.action|escape:'html':'UTF-8'}</th>
                    </tr>
                </thead>
                <tbody class="accessory_list" id="accessory_list_{$group.id_accessory_group|intval}">
                    {if $show_custom_quantity}
                        {assign var=colspan value=9}{*+1 when enabling price column*}
                    {else}
                        {assign var=colspan value=10}{*+1 when enabling price column*}
                    {/if}
                    {if isset($accessories_groups[$group.id_accessory_group]) &&  !empty($accessories_groups[$group.id_accessory_group])}
                        {foreach from=$accessories_groups[$group.id_accessory_group] item=accessory name=accessory_name}
                            {include file="./display_accessory_row.tpl"}
                        {/foreach}
                    {else}
                        {include file="./display_no_accessory.tpl"}
                    {/if}
                </tbody>
            </table>
            <input type="hidden" value="{if !empty($accessories_groups) && isset($accessories_groups[$group.id_accessory_group])}{foreach from=$accessories_groups[$group.id_accessory_group] item=accessory}{$accessory.id_accessory|intval}:{$accessory.id_product_attribute|intval}-{/foreach}{/if}" name="id_accessories[{$group.id_accessory_group|intval}]" id="id_accessories_{$group.id_accessory_group|escape:'html':'UTF-8'}" />
        </div>
    </div>
{/foreach}
