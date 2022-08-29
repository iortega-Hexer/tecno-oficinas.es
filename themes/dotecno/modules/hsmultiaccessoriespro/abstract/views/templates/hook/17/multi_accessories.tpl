{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div id="multiAccessoriesTab">
{if !empty($accessory_groups) && !empty($accessories_groups)}
    <script type="text/javascript">
        changeMainPrice = {$change_main_price|escape:'htmlall':'UTF-8'};
        accessoriesTablePrice = {$accessories_table_price nofilter},
        randomMainProductId = '{$random_main_product_id|escape:'htmlall':'UTF-8'}',
        subTotal = '{$sub_total|escape:'htmlall':'UTF-8'}',
        showTablePrice = {$show_table_price|escape:'htmlall':'UTF-8'},
        jsTranslateText =  {$js_translate_text nofilter},
        showCombination = {$show_combination|intval},
        syncAccessoryQuantity = {$sync_accessory_quantity|intval},
        showOptionImage = {$accessory_configuration_keys.HSMA_SHOW_IMAGES|escape:'htmlall':'UTF-8'},
        isScrollingToTablePrice = {$accessory_configuration_keys.HSMA_SCROLL_TO_TABLE_PRICE|intval},
        warningOutOfStock = '{$hs_i18n.accessory_is_out_of_stock|escape:'htmlall':'UTF-8'}',
        warningNotEnoughProduct = '{$hs_i18n.there_is_not_enough_product_in_stock|escape:'htmlall':'UTF-8'}';
        warningCustomQuantity = '{$hs_i18n.quantity_must_be_greater_than_or_equal_to_minimum_quantity|escape:'htmlall':'UTF-8'}';
    </script>
    {if isset($is_prestashop_16)}
        {if !empty($tab_name)}<h3 class="page-product-heading"> {$tab_name|escape:'htmlall':'UTF-8'}</h3>{/if}
    {/if}    
    <div class="accessories_table_price">
        <table class="accessories_table_price_content">
        </table>
    </div>
    <div id="group_accessories">
        {if empty($tab_name)}<h3>{if !empty($accessory_block_title)} {$accessory_block_title|escape:'htmlall':'UTF-8'} {/if}</h3>{/if}
        {assign var=is_expand value=0}
        {foreach from=$accessory_groups item=group}
            {if isset($accessories_groups[$group.id_accessory_group]) &&  !empty($accessories_groups[$group.id_accessory_group])}
                <div class="option-row clearfix">
                    {assign var=class_expand value=""}
                    {assign var=class_collapse value=""}
                    {if $group.collapse_expand == HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND}
                        {assign var=class_expand value="remove"}
                        {assign var=class_collapse value=""}
                    {elseif $group.collapse_expand == HsMaDisplayStyle::DISPLAY_GROUPS_COLLAPSE}
                        {assign var=class_expand value=""}
                        {assign var=class_collapse value="add"}
                    {/if}
                    {assign var=icon_utf8 value=0}
                    {assign var=is_show_group value="block"}
                    {if !$class_expand}
                        {assign var=icon_utf8 value=1}
                    {/if}
                    {if !$class_expand && $class_collapse}
                        {assign var=is_show_group value="none"}
                    {/if}
                    <h4>
                    {if $group.collapse_expand != HsMaDisplayStyle::DISPLAY_GROUPS_NONE}
                    <i class="material-icons {$class_collapse|escape:'htmlall':'UTF-8'} {$class_expand|escape:'htmlall':'UTF-8'}">{if $icon_utf8 == 1}&#xE145;{else}&#xE15B;{/if}</i>
                    {/if}
                    {if $group.show_name}
                        {$group.name|escape:'html':'UTF-8'}
                    {/if}
                    </h4>
                    <div class="content_group" style="display: {$is_show_group|escape:'htmlall':'UTF-8'}">
                    {if $group.display_style == HsMaDisplayStyle::DROPDOWN}{*Display style = Dropdown*}
                        {include file="./display_style_dropdown.tpl"}
                    {else}{*Display style = Checkbox & radio*}
                        {include file="./display_style_checkbox_radio.tpl"}
                    {/if}
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
{/if}
</div>