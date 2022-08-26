{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<tr id="{$group.id_accessory_group|intval}_{$accessory.id_accessory_group_product|intval}" data-id-accessory-group-product="{$accessory.id_accessory_group_product|escape:'htmlall':'UTF-8'}" class="accessory_row" data-colspan="{$colspan|escape:'htmlall':'UTF-8'}" >
    <td class="center image">
        <a title="{$accessory.name[Configuration::get(PS_LANG_DEFAULT)]|escape:'html':'UTF-8'|default:""}" href="index.php?controller=adminproducts&amp;id_product={$accessory.id_accessory|intval}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}" target="_blank">
            <img src="{$accessory.image|escape:'htmlall':'UTF-8'}">
        </a>
    </td>
    <td>
        {if !$is_prestashop16}
            <script type="text/javascript">
                var languages = new Array();
                {foreach from=$languages item=language key=k}
                        languages[{$k|escape:'html':'UTF-8'}] = {
                            id_lang: {$language.id_lang|escape:'html':'UTF-8'},
                            iso_code: '{$language.iso_code|escape:'html':'UTF-8'}',
                    name: '{$language.name|escape:'html':'UTF-8'}'
                };
            {/foreach}
                displayFlags(languages, {$default_form_language|escape:'html':'UTF-8'});
            </script>
        {/if}
        {if $is_prestashop16}
            {include file="./display_accessory_name_16.tpl"}
        {else}
            {include file="./display_accessory_name_15.tpl"}
        {/if}
        <div class="col-lg-2">
            <img class="save_name hide" src="{$img_path|escape:'html':'UTF-8'}save.png" width="30"/>
        </div>
        <div class="col-lg-2">
            <img class="img_edit_name" src="{$img_path|escape:'html':'UTF-8'}edit.png" width="30"/>
        </div>

        {include file="./display_accessory_combinations.tpl"}
    </td>
    <!--td>
        $12.6
    </td-->
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
            <a rel="5_19_3" title="up" class="qty_up" href="javascript:void(0);">+</a>
            <a rel="5_19_3" title="down" class="qty_down" href="javascript:void(0);">-</a>
        </span>
        <span class="">
            <input type="text" name="minimum_quantity" value="{$accessory.min_quantity|intval}" data-min-quantity="{$accessory.min_quantity|intval}" data-default-quantity="{$accessory.default_quantity|intval}">
        </span>
    </td>
    <td class="hsma_price center price" data-old-price="{$accessory.old_price|floatval}">
        {convertPrice price=$accessory.old_price}
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
        {/if}    
        <div class="discount_block">
            <input class="discount_value" type="text" name="discount_value" value="{if isset($discount_value)}{$discount_value|floatval}{else}0{/if}">
            {if isset($discount_type) && $discount_type == 1}({$currency->sign|escape:'htmlall':'UTF-8'}){/if}
            {*<select class="discount_type" name="discount_type">
                <option {if isset($discount_type) && $discount_type == 1} selected="selected"{/if} value="1">{$hs_i18n.amount|escape:'htmlall':'UTF-8'}</option>
                <option {if isset($discount_type) && $discount_type == 0} selected="selected"{/if} value="0">{$hs_i18n.percent|escape:'htmlall':'UTF-8'}</option>
            </select>*}
        </div>
    </td> 
    <td class="hsma_final_price center final_price">
        {convertPrice price=$accessory.final_price}
    </td> 
    <td class="center buy_together_required {if $buy_together_default != HsMaProductSetting::BUY_TOGETHER_REQUIRED}hide{/if}">
        <select name="accessory_buy_together_required">
            <option value="1" {if $accessory.required == 1} selected {/if}>{$hs_i18n.yes|escape:'htmlall':'UTF-8'}</option>
            <option value="0" {if $accessory.required == 0} selected {/if}>{$hs_i18n.no|escape:'htmlall':'UTF-8'}</option>
        </select>
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
            <img src="{$img_path|escape:'html':'UTF-8'}delete.png" width="30"/>
        </span>
    </td>
</tr>