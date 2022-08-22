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

{extends file='page.tpl'}
{block name='page_content'}
    {if isset($products) && $products}
        <section class="featured-products clearfix">
            <div class="checkout cart-detailed-actions card-block">
                  <div class="text-xs-center">
                     <a href="{$page_link|escape:'htmlall':'UTF-8'}" class="btn btn-primary standard-checkout">
                        {l s='Proceed to cart' mod='hiupsell'}
                    </a>
                </div>
                 <div class="upsell-description">
                    {$upsell_description nofilter}
                </div>
            </div>
            <div class="products">
                {foreach from=$products item="product"}
                    {include file="catalog/_partials/miniatures/product.tpl" product=$product}
                {/foreach}
            </div>
            <div class="checkout cart-detailed-actions card-block">
                  <div class="text-xs-center">
                     <a href="{$page_link|escape:'htmlall':'UTF-8'}" class="btn btn-primary standard-checkout">
                        {l s='Proceed to cart' mod='hiupsell'}
                    </a>
                </div>
            </div>
        </section>
    {/if}
{/block}
