{**
* Multi Accessories Pro
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $hsma_pack_items}
    {foreach from=$hsma_pack_items item="hsma_pack_item"}
        <article>
            <div class="hsma_pack_card card">
                <div class="pack-product-container">
                    <div class="thumb-mask">
                        <div class="mask">
                            <a class="product_img_link" href="{$hsma_pack_item.link|escape:'html':'UTF-8'}" title="{$hsma_pack_item.name|escape:'html':'UTF-8'}" itemprop="url">
                                <img class="replace-2x img-responsive" src="{$link->getImageLink($hsma_pack_item.link_rewrite, $hsma_pack_item.id_image, 'cart_default')|escape:'html':'UTF-8'}" alt="{if !empty($hsma_pack_item.legend)}{$hsma_pack_item.legend|escape:'html':'UTF-8'}{else}{$hsma_pack_item.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($hsma_pack_item.legend)}{$hsma_pack_item.legend|escape:'html':'UTF-8'}{else}{$hsma_pack_item.name|escape:'html':'UTF-8'}{/if}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} itemprop="image" />
                            </a>
                        </div>
                    </div>
                    <div class="pack-product-name">
                        <a href="{$hsma_pack_item.link|escape:'html':'UTF-8'}" title="{$hsma_pack_item.name|escape:'html':'UTF-8'}">
                            {$hsma_pack_item.name|escape:'html':'UTF-8'}
                        </a>
                    </div>
                    <div class="pack-product-price">
                        <strong>{Tools::displayPrice($hsma_pack_item.price)|escape:'html':'UTF-8'}</strong>
                    </div>
                    <div class="pack-product-quantity">
                        <span>x {$hsma_pack_item.pack_quantity * $qty|intval}</span>
                    </div>
                </div>
            </div>
        </article>
    {/foreach}
{/if}

