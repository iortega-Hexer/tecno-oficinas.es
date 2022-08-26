{**
* Multi Accessories Pro
*
* @author PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<script type="text/javascript">
    $(document).ready(function () {
        $(".hsma_scroll").jScroll();
    });
</script>
<hr>
<div class="col-xs-9 hsma_display_products">
    <div class="col-xs-11 hsma_border">
        <h4>{$hs_i18n.products|escape:'html':'UTF-8'}</h4>
        {if $products}
            <table class="table product_list">
                <thead>
                    <tr>
                        <th>{$hs_i18n.image|escape:'html':'UTF-8'}</th>
                        <th>{$hs_i18n.product_name|escape:'html':'UTF-8'}</th>
                        <th>{$hs_i18n.price|escape:'html':'UTF-8'}</th>
                        <th class="product_check_box">
                            {$hs_i18n.select_all|escape:'html':'UTF-8'} <input type="checkbox" name="checkAllProducts[]">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$products item=product}
                        <tr class="product_{$product.id_product|intval}">
                            <td>
                                <a title="{$product.pname|escape:'htmlall':'UTF-8'}" href="index.php?controller=adminproducts&id_product={$product.id_product|intval}&updateproduct&token={getAdminToken tab='AdminProducts'}" target="_blank">
                                    <img src="{$link->getImageLink($product.link_rewrite|escape:'htmlall':'UTF-8', $product.id_image|escape:'htmlall':'UTF-8', 'small_default')}" alt="{$product.pname|escape:'htmlall':'UTF-8'}" title="{$product.pname|escape:'htmlall':'UTF-8'}"/>
                                </a>
                            </td>
                            <td>
                                {$product.pname|escape:'htmlall':'UTF-8'} {if $product.reference}({$product.reference|escape:'html':'UTF-8'}){/if}
                            </td>
                            <td>
                                {convertPrice price=$product.price|escape:'quotes':'UTF-8'}
                            </td>
                            <td class="product_check_box">
                                <input type="checkbox" class="product_item" value="{$product.id_product|intval}" name="id_product" >
                            </td>
                        </tr>
                        {if isset($product.accessories) && !empty($product.accessories)}
                            {assign var=product_accessories value=$product.accessories}
                            {assign var=id_main_product value=$product.id_product}
                            {include file="./setting_display_accessory.tpl"}
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        {else}
            <hr>
            <p class="no_product">{$hs_i18n.there_is_no_product|escape:'htmlall':'UTF-8'}</p>
        {/if}
    </div>
    {if $products && $accessories}
        <div class="col-xs-1 hsma_display_button hsma_scroll">
            <button type="button" class="btn btn-default assign_accessories_to_products">{$hs_i18n.assign|escape:'html':'UTF-8'}</button>
        </div>
        <div class="clearfix"><!-- clear --></div>
    {/if}
</div>
<div class="col-xs-3 hsma_display_accessories hsma_border">
    <h4>{$hs_i18n.accessories|escape:'html':'UTF-8'}</h4>
    {if $accessories}
        <table class="table accessory_list">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" name="checkAllAccessories[]">
                        {$hs_i18n.select_all|escape:'html':'UTF-8'}
                    </th>
                    <th>{$hs_i18n.image|escape:'html':'UTF-8'}</th>
                    <th>{$hs_i18n.product_name|escape:'html':'UTF-8'}</th>
                    <th>{$hs_i18n.price|escape:'html':'UTF-8'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$accessories item=accessory}
                    <tr>
                        <td>
                            <input type="checkbox" class="accessory_item" value="{$accessory.id_product|intval}" name="id_accessory">
                        </td>
                        <td>
                            <a title="{$accessory.pname|escape:'htmlall':'UTF-8'}" href="index.php?controller=adminproducts&id_product={$accessory.id_product|intval}&updateproduct&token={getAdminToken tab='AdminProducts'}" target="_blank">
                                <img src="{$link->getImageLink($accessory.link_rewrite|escape:'htmlall':'UTF-8', $accessory.id_image|escape:'htmlall':'UTF-8', 'small_default')}" alt="{$accessory.pname|escape:'htmlall':'UTF-8'}" title="{$accessory.pname|escape:'htmlall':'UTF-8'}"/>
                            </a>
                        </td>
                        <td>
                            {$accessory.pname|escape:'htmlall':'UTF-8'} {if $accessory.reference}({$accessory.reference|escape:'html':'UTF-8'}){/if}
                        </td>
                        <td>
                            {convertPrice price=$accessory.price|escape:'quotes':'UTF-8'}
                        </td>
                    </tr>
                {/foreach}
        </table>
    {else}
        <hr>
        <p class="no_product">{$hs_i18n.there_is_no_accessory|escape:'htmlall':'UTF-8'}</p>
    {/if}
</div>