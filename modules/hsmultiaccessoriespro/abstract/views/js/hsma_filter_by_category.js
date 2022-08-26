/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * HsmaFilterByCategory form controller
 * 
 * @param {json} params
 * @param {json} selectors
 * @returns {HsmaFilterByCategory}
 */
var HsmaFilterByCategory = function (params, selectors)
{
    this._params = {
        ajaxUrls: null,
        lang: null,
        removeProduct: true // Remove product from filtered list after adding to shopping cart
    };

    /**
     * Declare all values
     */
    this._selectors = {
        productCategoryRow: '#container_category_tree_products input[name="id-category[]"]',
        accessoryCategoryRow: '#container_category_tree_accessories input[name="id-category[]"]',
        displayProductsAccessories: '.display_products_accessories',
        buttonCheckAllProduct: '#check-all-is_category_filter_product',
        buttonCheckAllAccessory: '#check-all-is_category_filter_accessory',
        buttonUnCheckAllProduct: '#uncheck-all-is_category_filter_product',
        buttonUnCheckAllAccessory: '#uncheck-all-is_category_filter_accessory',
        selectedIdGroup: '#hsma_id_group',
        blockWarning: '.alert_warning_hsma'
    };

    /**
     * assign PosCart to object
     */
    $.extend(this._selectors, selectors);
    $.extend(this._params, params);

    HsmaFilterByCategory.instance = this;

    /**
     * constructor all event
     */
    this.init = function ()
    {
        // Event click on check box category tree
        $(document).on('click', '.get_products_accessories', function () {
            HsmaFilterByCategory.instance._processFilterByCategory();
        });
    };
    
    /**
     * Get selected category and call ajax filter process
     */
    this._processFilterByCategory = function ()
    {
        var productIdCategories = $(HsmaFilterByCategory.instance._selectors.productCategoryRow + ':checked').map(function () {
            return this.value;
        }).get();
        var accessoryIdCategories = $(HsmaFilterByCategory.instance._selectors.accessoryCategoryRow + ':checked').map(function () {
            return this.value;
        }).get();
        var idGroup = parseInt($(HsmaFilterByCategory.instance._selectors.selectedIdGroup).val());
        if (productIdCategories.length > 0 && accessoryIdCategories.length > 0 && idGroup > 0) {
            HsmaFilterByCategory.instance._filterByCategory(productIdCategories, accessoryIdCategories, idGroup);
        } else {
            $(HsmaFilterByCategory.instance._selectors.displayProductsAccessories).html('');
            if (productIdCategories.length < 1) {
                alert(HsmaFilterByCategory.instance._params.lang.please_select_at_least_1_product_category);
                return;
            }
            if (accessoryIdCategories.length < 1){
                alert(HsmaFilterByCategory.instance._params.lang.please_select_at_least_1_accessory_category);
                return;
            }
            if (idGroup < 1){
                alert(HsmaFilterByCategory.instance._params.lang.please_select_a_group_accessory);
                return;
            }
        }
    };

    /**
     * Ajax process filter by category
     * 
     * @param {array} productIdCategories
     * @param {array} accessoryIdCategories
     * @param {int} idGroup
     */
    this._filterByCategory = function (productIdCategories, accessoryIdCategories, idGroup)
    {
        $.ajax({
            type: 'POST',
            url: HsmaFilterByCategory.instance._params.ajaxUrls.ajaxProcessFilterByCategories,
            dataType: 'json',
            data: {
                'product_id_categories' : productIdCategories,
                'accessory_id_categories' : accessoryIdCategories,
                'id_group' : idGroup
            },
            beforeSend: function () {
                $(HsmaFilterByCategory.instance._selectors.displayProductsAccessories).html('');
                $('.alert_warning_hsma').html('');
                $(HsmaFilterByCategory.instance._selectors.blockWarning).removeClass('show');
                $(HsmaFilterByCategory.instance._selectors.blockWarning).addClass('hide');
            },
            success: function (json)
            {
                if (json.success) {
                    $(HsmaFilterByCategory.instance._selectors.displayProductsAccessories).html(json.data.html);
                    AdminMultiAccessories.instance._onChangeAccessoryProductPosition('.accessory_group_product');
                } else {
                    alert(HsmaFilterByCategory.instance._params.lang.error);
                }
            },
            error: function (jqXHR, exception)
            {
                HsmaFilterByCategory.instance._showErrorException(jqXHR, exception);
            }
        });
    };
    /**
     * @param {object} jqXHR
     * @param {string} exception
     */
    this._showErrorException = function (jqXHR, exception)
    {
        var message = '';
        if (jqXHR.status === 0) {
            message = HsmaFilterByCategory.instance._params.lang.there_was_a_connecting_problem;
        } else if (jqXHR.status === 404) {
            message = HsmaFilterByCategory.instance._params.lang.requested_page_not_found;
        } else if (jqXHR.status === 500) {
            message = HsmaFilterByCategory.instance._params.lang.internal_server_error;
        } else if (exception === 'timeout') {
            message = HsmaFilterByCategory.instance._params.lang.request_time_out;
        } else if (exception === 'abort') {
            message = HsmaFilterByCategory.instance._params.lang.ajax_request_is_aborted;
        } else if (exception === 'parsererror') {
            var msg = jqXHR.responseText + HsmaFilterByCategory.instance._params.lang.please_uncheck_all_categories_after_that_select_1_or_2_categories_and_filter_again;
            $(HsmaFilterByCategory.instance._selectors.blockWarning).html(msg);
            $(HsmaFilterByCategory.instance._selectors.blockWarning).addClass('show');
            $(HsmaFilterByCategory.instance._selectors.blockWarning).removeClass('hide');
        } else {
            message = jqXHR.responseText + exception;
        }
        if (message.length > 0) {
            alert(message);
        }
    };
};
