{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author DMConcept <support@dmconcept.fr>
*  @copyright 2015 DMConcept
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}




<div class="row">

    <div class="col-md-6">

        <div class="panel configurator-steps-tab">

            <h3 class="tab">
                <i class="icon-cog"></i>
                {l s='Migration from Configurator 2.x to 3.x' mod='configurator'}
                <a href="{{$refresh_link}}" class="btn btn-success btn-xs">
                    <i class="icon-refresh"></i>
                    {l s='Refresh' mod='configurator'}
                </a>
            </h3>

            <!-- IS CONFIGURATED -->
            <h4>
                <i class="icon-trash"></i>
                {l s='Deleting configurated products' mod='configurator'}
            </h4>
            {if $products_counters.total > 0 && $products_counters.total > $products_counters.current}
                <div class="form-group">
                    <label class="control-label col-lg-6" for="reference">{l s='Reload deleting after X seconds' mod='configurator'}</label>
                    <input id="refresh_time_product" name="refresh_time_product" value="30" class="control-form">
                </div>
                <div class="alert alert-warning">
                    {l s='You have %s product(s) to delete.' mod='configurator' sprintf=$products_counters.total}
                    <button type="button" id="start_delete_products" class="btn btn-warning btn-xs pull-right">
                        <i class="icon-trash"></i>
                        {l s='Delete' mod='configurator'}
                    </button>
                </div>
                <iframe id="remove_products" width="100%" style="border:none;">
                    <p>Your browser does not support iframes.</p>
                </iframe>
            {else}
                <div class="alert alert-success">
                    {l s='You have no product to delete.' mod='configurator'}
                </div>
            {/if}

            <!-- ATTRIBUTES -->
            <h4>
                <i class="icon-trash"></i>
                {l s='Deleting unused attributes' mod='configurator'}
            </h4>
            {if $attributes_counters.total > 0 && $attributes_counters.total > $attributes_counters.current}
                <div class="form-group">
                    <label class="control-label col-lg-6" for="reference">{l s='Reload deleting after X seconds' mod='configurator'}</label>
                    <input id="refresh_time_attribute" name="refresh_time_attribute" value="30" class="control-form">
                </div>
                <div class="alert alert-warning">
                    {l s='You have %s attribute(s) to delete.' mod='configurator' sprintf=$attributes_counters.total}
                    <button type="button" id="start_delete_attributes" class="btn btn-warning btn-xs pull-right">
                        <i class="icon-trash"></i>
                        {l s='Delete' mod='configurator'}
                    </button>
                </div>
                <iframe id="remove_attributes" width="100%" style="border:none;">
                    <p>Your browser does not support iframes.</p>
                </iframe>
            {else}
                <div class="alert alert-success">
                    {l s='You have no attribute to delete.' mod='configurator'}
                </div>
            {/if}

            <!-- ATTRIBUTE GROUP -->
            <h4>
                <i class="icon-trash"></i>
                {l s='Deleting unused attribute group' mod='configurator'}
            </h4>
            {if $attributegroup_id > 0}
                <div class="alert alert-warning">
                    {l s='You need to delete the attribute group CONFIGURATOR.' mod='configurator'}
                    <a href="{{$remove_attributegroup_link}}" class="btn btn-warning btn-xs pull-right {if $attributes_counters.total > 0 && $attributes_counters.total > $attributes_counters.current}disabled{/if}">
                        <i class="icon-trash"></i>
                        {l s='Delete' mod='configurator'}
                    </a>
                </div>
            {else}
                <div class="alert alert-success">
                    {l s='Nothing to do.' mod='configurator'}
                </div>
            {/if}

        </div>

        {if !$use_cache_ps}
            <div class="panel configurator-steps-tab">

                <h3 class="tab">
                    <i class="icon-cog"></i>
                    {l s='Settings' mod='configurator'}
                </h3>

                <h4>
                    <i class="icon-trash"></i>
                    {l s='Clean the cache' mod='configurator'}
                </h4>
                {if $cache_cleaned}
                    <div class="alert alert-success">
                        {l s='The cache has been cleaned.' mod='configurator'}
                    </div>
                {else}
                    <div class="alert alert-warning">
                        {l s='You can clean the cache to the module.' mod='configurator'}
                        <a href="{{$clean_cache_link}}" class="btn btn-warning btn-xs pull-right {if $attributes_counters.total > 0 && $attributes_counters.total > $attributes_counters.current}disabled{/if}">
                            <i class="icon-trash"></i>
                            {l s='Clean' mod='configurator'}
                        </a>
                    </div>
                {/if}
            </div>
        {/if}

        <div class="panel configurator-steps-tab">

            <h3 class="tab">
                <i class="icon-cog"></i>
                {l s='Installation' mod='configurator'}
            </h3>

            <a href="{{$reset_override_link}}" class="btn btn-warning btn-xs pull-right">
                <i class="icon-refresh"></i>
                {l s='Reset' mod='configurator'}
            </a>
            <h4>
                <i class="icon-refresh"></i>
                {l s='Reset overrides' mod='configurator'}
            </h4>
            {if isset($reset_override_result) && $reset_override_result === true}
                <div class="alert alert-success">
                    {l s='The overrides have been reset.' mod='configurator'}
                </div>
            {elseif isset($reset_override_result)}
                <div class="alert alert-warning">
                    {{$reset_override_result}}
                </div>
            {/if}

        </div>

    </div>
    <div class="col-md-6">

        <div class="panel configurator-steps-tab">

            <h3 class="tab">
                <i class="icon-cog"></i>
                {l s='Clean Configurator Module' mod='configurator'}
            </h3>

            <!-- CLEAN DATABASE -->
            <h4>
                <i class="icon-trash"></i>
                {l s='Clean Configurator tables from the database' mod='configurator'}
            </h4>
            {if $view == 'clean_database'}
                <div class="alert alert-success">
                    {l s='Your Configurator tables are clean.' mod='configurator'}
                </div>
            {else}
                <div class="alert alert-info">
                    {l s='This action will have no impact on your existing configurations' mod='configurator'}
                    <a href="{{$clean_database_link}}" class="btn btn-info btn-xs pull-right">
                        <i class="icon-trash"></i>
                        {l s='Clean' mod='configurator'}
                    </a>
                </div>
            {/if}

            <!-- CLEAN CART DETAIL -->
            <h4>
                <i class="icon-trash"></i>
                {l s='Clean empty cart' mod='configurator'}
            </h4>
            <div class="alert alert-info">
                {l s='You can use this cron to delete empty carts:' mod='configurator'} <b>{{$remove_empty_cart_cron}}</b>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-6" for="reference">{l s='Reload deleting after X seconds' mod='configurator'}</label>
                <input id="refresh_time_empty_cart" name="refresh_time_empty_cart" value="30" class="control-form">
            </div>
            <div class="form-group">
                <label class="control-label col-lg-6" for="reference">{l s='Delete by package of X carts' mod='configurator'}</label>
                <input id="cadence_empty_cart" name="cadence_empty_cart" value="5" class="control-form">
            </div>
            <div class="alert alert-warning">
                {l s='You have %s empty cart(s) to delete.' mod='configurator' sprintf=$empty_cart_counters.total}
                <button type="button" id="start_delete_empty_cart" class="btn btn-warning btn-xs pull-right">
                    <i class="icon-trash"></i>
                    {l s='Delete' mod='configurator'}
                </button>
            </div>
            <iframe id="remove_empty_cart" width="100%" style="border:none;">
                <p>Your browser does not support iframes.</p>
            </iframe>

        </div>

    </div>
</div>

<script>
    var configurator_delete_product_last = null;
    var configurator_delete_attribute_last = null;
    var configurator_delete_empty_cart_last = null;

    $('#start_delete_products').click(function(){

        $('#remove_products').attr('src', "{{$remove_products_link}}");

        var interval = $('#refresh_time_product').val()*1000;
        var run = setInterval(request , interval);

        function request() {
            $.ajax({
                url: "{{$current_product_link}}",
                type: "GET",
                success: function(data){
                    var results = JSON.parse(data);
                    if(configurator_delete_product_last == results.current) {
                        $('#remove_products').attr('src', results.url);
                    }
                    configurator_delete_product_last = results.current;
                }
            });
            clearInterval(run);
            interval = $('#refresh_time_product').val()*1000;
            run = setInterval(request, interval);
        }

    });

    $('#start_delete_attributes').click(function(){

        $('#remove_attributes').attr('src', "{{$remove_attributes_link}}");

        var interval = $('#refresh_time_attribute').val()*1000;
        var run = setInterval(request , interval);

        function request() {
            $.ajax({
                url: "{{$current_attribute_link}}",
                type: "GET",
                success: function(data){
                    var results = JSON.parse(data);
                    if(configurator_delete_attribute_last == results.current) {
                        $('#remove_attributes').attr('src', results.url);
                    }
                    configurator_delete_attribute_last = results.current;
                }
            });
            clearInterval(run);
            interval = $('#refresh_time_attribute').val()*1000;
            run = setInterval(request, interval);
        }

    });

    $('#start_delete_empty_cart').click(function(){

        var interval = parseInt($('#refresh_time_empty_cart').val()) * 1000;
        var cadence = parseInt($('#cadence_empty_cart').val());
        var run = setInterval(request, interval);

        $('#remove_empty_cart').attr('src', "{{$remove_empty_cart_link}}&first=1&cadence=" + cadence);

        function request() {
            $.ajax({
                url: "{{$current_empty_cart_link}}",
                type: "GET",
                success: function(data){
                    var results = JSON.parse(data);
                    if(configurator_delete_empty_cart_last == results.current) {
                        $('#remove_empty_cart').attr('src', results.url + "&cadence=" + cadence);
                    }
                    configurator_delete_empty_cart_last = results.current;
                }
            });
            clearInterval(run);
            interval = parseInt($('#refresh_time_empty_cart').val()) * 1000;
            run = setInterval(request, interval);
        }

    });

</script>