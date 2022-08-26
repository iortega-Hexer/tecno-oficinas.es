{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if ($is_ps17)}
    {include file="./17/admin_include_media_file.tpl"}
{/if}
<script type="text/javascript">
    var productSettingBuyTogetherRequired = parseInt({HsMaProductSettingAbstract::BUY_TOGETHER_REQUIRED|escape:'htmlall':'UTF-8'});
    var idProduct = parseInt({$id_product|escape:'htmlall':'UTF-8'});
    var isProductPage = parseInt({$is_product_page|intval});
    var stHsMultiAccessories ={$st_hsmultiaccessories|escape:'quotes':'UTF-8'};
    var customDisplayedName = parseInt({$product_setting->custom_displayed_name|escape:'htmlall':'UTF-8'});
    var productBuyToGether = parseInt({$buy_together_default|escape:'htmlall':'UTF-8'});
    isPrestashop17 = parseInt({$is_ps17|intval});
    $(document).ready(function () {
        window.adminProductSetting = new AdminProductSetting(
                {
                    buyTogether: '.hsma_product_setting select', // Select box setting buy main product & accessory together
                    customDisplayedNames: '.hsma_product_setting input[name="custom_displayed_name"]' // Input use custom displayed names for this product
                },
        {
            ajaxUrls: stHsMultiAccessories.url,
            messageError: stHsMultiAccessories.lang.error,
            buyMainTogether: productBuyToGether,
            customDisplayedName: customDisplayedName
        });
        window.adminMultiAccessories = new AdminMultiAccessories(
        {
            completeSearch: '.hsma_accessory_group .autocomplete_group', // Input search accessory
            name: '.name', // Block contain accessory name
            editName: '.edit_name', // Block contain accessory name
            saveName: '.save_name', // Button save name of accessory
            blockEditName: '.edit_name, .save_name',
            combinations: '.hsma_accessory_group .dropdown_combination', // Selectbox change combination
            defaultQuantity: 'input[name="default_quantity"]', // Input default quantity
            iconChangeDefaultQuantity: '.default_quantity a', // Button change default quantity
            minimumQuantity: '.hsma_accessory_group input[name="minimum_quantity"]', // Input minimum quantity
            iconChangeMinimumQuantity: '.minimum_quantity a', // Button change minimum quantity
            buyToGetherRequired: '.hsma_accessory_group .buy_together_required', // Selectbox required product & accessory buy together
            delete: '.hsma_accessory_group .delete', // Button delete accessory
            iconShowBlockGroup: '#hsma-accessories h4', // Icon collapse block group accessories
            columnRequiredBuyTogether: '.table .buy_together_required', // Column required buy product & accessory together
            hide: 'hide', // Name of class hide
            show: 'show', // Name of class show
            accessoryName: 'name', // Name of class edit name accessory
            iconExpand: 'icon-expand-alt', // Name of icon expand +
            iconCollapse: 'icon-collapse-alt', // Name of icon collapse -
            contentGroup: 'content_group', // Name of class content group
            expand: 'expand', // Name of class expand
            accessoryGroup: '.group',
            idAccessories: 'id_accessories_', // Input contain id accessories
            idOfBlockAccessories: 'div_accessories_',
            tableRows: '.table tbody',
            image: '.image',
            xxItemsInside: '.xx-items-inside'
        },
        {
            excludedAccessoryIds: idProduct,
            ajaxUrls: stHsMultiAccessories.url,
            messageError: stHsMultiAccessories.lang.error,
            msgOutOfStock: stHsMultiAccessories.lang.accessory_is_out_of_stock,
            msgAvailableQuantity: stHsMultiAccessories.lang.min_quantity_must_be_less_than_available_quantity,
            msgDefaultQuantity: stHsMultiAccessories.lang.default_quantity_should_be_greater_than_or_equal_to_minimum_quantity,
            productSettingBuyTogetherRequired: productSettingBuyTogetherRequired,
            confirmTitle: stHsMultiAccessories.lang.copy_accessories,
            confirmMessage: stHsMultiAccessories.lang.you_are_about_to_copy_accessories_from_another_product_to_this_product,
            confirmTitleAddDiscount: stHsMultiAccessories.lang.add_a_discount,
            confirmMessageAddDiscount: stHsMultiAccessories.lang.you_are_about_to_add_a_discount_for_the_product_and_accessory,
            yes: stHsMultiAccessories.lang.yes,
            no: stHsMultiAccessories.lang.no,
            cancel: stHsMultiAccessories.lang.cancel,
            msgNoInternet: stHsMultiAccessories.lang.there_was_a_connecting_problem,
            msgPageNotFound: stHsMultiAccessories.lang.requested_page_not_found,
            msgInternalServerError: stHsMultiAccessories.lang.internal_server_error,
            msgRequestTimeOut: stHsMultiAccessories.lang.request_time_out,
            msgAjaxRequestIsAborted: stHsMultiAccessories.lang.ajax_request_is_aborted,
        });
        adminProductSetting.onChangeBuyTogether(adminMultiAccessories.toggleColumnRequired);
        adminProductSetting.init();
        adminMultiAccessories.setSetting(adminProductSetting);
        adminMultiAccessories.init();
    });
</script>
<div id="hsma-accessories" class="panel product-tab {if $is_ps17}hsma_accessories_17{/if}">
    <input type="hidden" name="id_main_product" id="id_main_product" value="{$id_product|intval}">
    {if !$is_ps17}
        <h3 class="tab"> <i class="icon_hsma_accessories"></i> {$hs_i18n.multi_accessories|escape:'htmlall':'UTF-8'}</h3>
    {/if}
    {include file="./display_accessory_advance_settings.tpl"}
    {include file="./display_groups_and_accessories.tpl"}
</div>
