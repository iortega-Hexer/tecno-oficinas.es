{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="hsma_product_setting group cleafix" data-id-product="{$id_product|escape:'html':'UTF-8'}">
    <h4 class="title">
        {if ($is_ps17)}
            <i class="material-icons remove">&#xE15B;</i>
        {else}
            <i class="icon icon-collapse-alt">&nbsp;</i>
        {/if}
        {$hs_i18n.advanced_settings_for_this_product_only|escape:'html':'UTF-8'}
    </h4>
    <div class="content_group expand cleafix">
        <div class="form-group cleafix">
            <label class="control-label col-lg-4" for="simple_product">
                <span class="label-tooltip" data-toggle="tooltip" title="{$hs_i18n.tell_your_customers_that_they_need_to_buy_main_product_and_accessories_together|escape:'html':'UTF-8'}">
                    {$hs_i18n.buy_main_product_accessories_together|escape:'quotes':'UTF-8'}
                </span>
            </label>
            <div class="col-lg-3">
                <div class="row">
                    <select name='hsma_buy_together' class="hsma_buy_together" data-id-product="{$id_product|escape:'html':'UTF-8'}">
                        {foreach from=$buy_together_options key=id item=buy_together}
                            <option value="{$buy_together['id']|escape:'html':'UTF-8'}" {if $buy_together_default == $buy_together['id']} selected="selected" {/if}>{$buy_together['name']|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-4" for="simple_product">
                <span class="label-tooltip" data-toggle="tooltip" title="{$hs_i18n.otherwise_wherever_that_accessory_is_displayed|escape:'html':'UTF-8'}">
                    {$hs_i18n.only_use_custom_displayed_names_for_this_product|escape:'html':'UTF-8'}
                </span>
            </label>
            <div class="col-lg-3">
                <div class="row">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="custom_displayed_name" id="custom_displayed_name_on" value="1" {if $product_setting->custom_displayed_name == 1}checked="checked"{/if}/>
                        <label for="custom_displayed_name_on" class="radioCheck">
                            {$hs_i18n.yes|escape:'html':'UTF-8'}
                        </label>
                        <input type="radio" name="custom_displayed_name" id="custom_displayed_name_off" value="0" {if $product_setting->custom_displayed_name == 0}checked="checked"{/if}/>
                        <label for="custom_displayed_name_off" class="radioCheck">
                            {$hs_i18n.no|escape:'html':'UTF-8'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group advanced_setting">
            <label class="control-label col-lg-4" for="simple_product">
                <span class="label-tooltip" data-toggle="tooltip" title="{$hs_i18n.copy_accessories_from|escape:'html':'UTF-8'}">
                    {$hs_i18n.copy_accessories_from|escape:'html':'UTF-8'}
                </span>
            </label>
            <div class="col-lg-3">
                <div class="row">
                    <input type="text" placeholder="{$hs_i18n.search_for_a_product|escape:'htmlall':'UTF-8'}" class="autocomplete_search_product" name="searchForProduct" autocomplete="off">
                </div>
            </div>
        </div>
    </div>
</div>