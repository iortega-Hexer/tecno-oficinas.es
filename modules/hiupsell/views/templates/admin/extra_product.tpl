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

<script type="text/javascript" src="{$upsell_path|escape:'htmlall':'UTF-8'}views/js/admin.js"></script>

<div class="panel product-tab {if $psv >= 1.7}product-tab-17{elseif $psv < 1.6}product-tab-15{/if} clearfix" id='upsell_product_extra'>
    <h3>{l s='Add Upsell products' mod='hiupsell'}</h3>
    <div  class='form-group'>
        <label class="{if $psv < 1.7}control-label col-lg-2{/if}">{l s='Search product' mod='hiupsell'}</label>
        <div class="{if $psv < 1.7}col-lg-9{else}row mb-2{/if}">
            <input type="hidden" name="id_parent" id="id_parent" value="{$id_parent}" />
            <div id="ajax_choose_upsell_product_extra" class="{if $psv < 1.7}col-lg-6{else}col-md-4{/if}">
                    <input type="text" id="upsell_product_extra_search" name="upsell_product_extra_search" value="" autocomplete="off" class="form-control ac-input">
            </div>
            <div class="{if $psv < 1.7}col-lg-2{else}col-md-2{/if}">
                <button type="button" id="add-upsell-product-extra" class="btn btn-default btn-primary {if $psv >= 1.7}btn-action {/if}" name="add-upsell-product-extra">
                    {if $psv >= 1.7}
                        <i class="material-icons">add_circle</i>
                    {else}
                        <i class="icon-plus-sign-alt"></i>
                    {/if}
                    {l s='Add' mod='hiupsell'}
                </button>
            </div>
            <div id="upsellproductextra" class="col-lg-12">
                {$product_content}
            </div>
        </div>
        {if $psv >= 1.7}
            <script>
                $( document ).ready(function() {
                    // define source
                    this['upsell_product_extra_search_source'] = new Bloodhound({
                        datumTokenizer: Bloodhound.tokenizers.whitespace,
                        queryTokenizer: Bloodhound.tokenizers.whitespace,
                        identify: function(obj) {
                            return obj.id;
                        },
                        remote: {
                            url: upsell_remote_url,
                            cache: false,
                            wildcard: '%QUERY',
                            transform: function(response){
                                var newResponse = [];
                                if(!response){
                                    return newResponse;
                                }
                                var excludeIds = [];
                                $.each(response, function(key, item){
                                    if(excludeIds.indexOf(item.id + '-' + item.id_product_attribute) === -1){
                                        newResponse.push(item);
                                    }
                                });
                                return newResponse;
                            }
                        }
                    });

                    //define typeahead
                    $('#upsell_product_extra_search').typeahead({
                        minLength: 2,
                        highlight: true,
                        hint: false
                    }, {
                        display: 'id',
                        source: this['upsell_product_extra_search_source'],
                        templates: {
                            suggestion: function(item){
                                return '<div>' +
                                       '<table><tr>' +
                                       '<td rowspan="2"><img src="'+ item.image +'" style="width:50px; margin-right: 7px;" /></td>' +
                                       '<td>' + item.name + '</td></tr>' +
                                       '<tr><td>REF: ' + item.ref + '</td></tr>' +
                                       '</table></div>'
                            }
                        }
                    });
                });
            </script>
        {else}
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#upsell_product_extra_search').autocomplete(upsell_module_controller_dir+"&ajax=1", 
                    {
                        minChars: 2,
                        max: 50,
                        width: 500,
                        formatItem: function (data) {
                            return data[0]+ '. '+data[2] + '-' + data[1];
                        },
                        scroll: false,
                        multiple: false,
                        extraParams: {
                            action : 'product_search',
                            id_lang : id_lang,
                            secure_key : upsell_secure_key,
                        }
                    });
                });
            </script>
        {/if}
        
    </div>
</div>

