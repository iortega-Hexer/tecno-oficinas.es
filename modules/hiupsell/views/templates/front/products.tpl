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

{capture name=path}{l s='Accessories' mod='hiupsell'}{/capture}
{if isset($products) && $products}
    <div class="clearfix upsell_container">
        <div class="block">
            <div class="upsell-checkout clearfix" style="margin-bottom: 10px">
                <a href="{$page_link|escape:'htmlall':'UTF-8'}" class="button btn btn-default standard-checkout button-medium">
                    <span>  {l s='Proceed to checkout' mod='hiupsell'}<i class="icon-chevron-right right"></i> </span>
                </a>
                <div class="upsell-description">
                    {$upsell_description}
                </div>
            </div>
            <div class="clearfix">
                {if $default_template}
                    {include file="{$tpl_dir}./product-list.tpl" products=$products}
                {else}
                    <ul class="product_list">
                        {include file="{$upsell_module_tpl_dir}/products_version.tpl" products=$products psv=$psv}
                    </ul>
                {/if}
            </div>
            <div class="upsell-checkout clearfix">
                <a href="{$page_link|escape:'htmlall':'UTF-8'}" class="button btn btn-default standard-checkout button-medium">
                    <span>  {l s='Proceed to checkout' mod='hiupsell'}<i class="icon-chevron-right right"></i> </span>
                </a>
            </div>
        </div>
    </div>
{/if}
