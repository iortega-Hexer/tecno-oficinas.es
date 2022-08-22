{*
* motionSeed
*
*  @author    motionSeed <ecommerce@motionseed.com>
*  @copyright 2017 motionSeed. All rights reserved.
*  @license   https://www.motionseed.com/en/license-module.html
*}

{if !empty($this_module->id)}
<div class="alert alert-info">
    {l s=$labels.like sprintf=[$this_module->displayName] tags=['<strong>'] mod='orderfees_shipping'}
    <a href="https://addons.prestashop.com/en/ratings.php?id_product={$this_module->id_product|intval}" target="_blank" class="btn btn-default">
        <i class="icon-thumbs-o-up"></i> <span>{$labels.yes|escape:'quotes':'UTF-8'}</span>
    </a>
    <a href="https://addons.prestashop.com/en/contact-us?id_product={$this_module->id_product|intval}" target="_blank" class="btn btn-default">
        <i class="icon-thumbs-o-down"></i> <span>{$labels.no|escape:'quotes':'UTF-8'}</span>
    </a>
</div>
{/if}

{if !empty($modules)}
<div class="panel">
    <h3>
        <i class="icon-rocket"></i> {$labels.title|escape:'quotes':'UTF-8'}
    </h3>
    <div class="row" style="display: flex;">
        {foreach $modules as $module_item}
        <div class="col-lg-4" style="display: flex;">
            <div class="panel media">
                <div class="col-lg-3">
                    <div class="row text-center">
                        <img src="https://addons.prestashop.com/img/pico/{$module_item.id_product|intval}.jpg">
                    </div>
                    <div class="row text-center">
                        {if $module_item.rate == 'new'}
                            <span class="badge badge-primary">New</span>
                        {else}
                            <img src="https://addons.prestashop.com/themes/prestastore/img/stars-bo/rate_{$module_item.rate|intval}.jpg">
                        {/if}
                    </div>
                </div>
                <div class="col-lg-9">
                    <h4>{$module_item.name|escape:'quotes':'UTF-8'}</h4>
                    <p>
                        {$module_item.description|escape:'quotes':'UTF-8'}
                    </p>
                </div>
                <div class="col-lg-12 text-center">
                    <a href="https://addons.prestashop.com/{$module_item.lang_code|escape:'quotes':'UTF-8'}/product.php?id_product={$module_item.id_product|intval}" target="_blank" class="btn btn-success">
                        {$labels.discover|escape:'quotes':'UTF-8'}
                    </a>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</div>
{/if}