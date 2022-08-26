/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Handle all events in Admin >> Catalog >> Multi Accessories
 * @param {json} params
 * @returns {AdminSettingMultiAccessories}
 */
var AdminSettingMultiAccessories = function (params)
{

    /**
     * Define all params default of this class
     */
    this._params = {
        ajaxUrls: null // list of ajax urls
    };

    /**
     * Define all default selectors of class
     */
    this._selectors = {
        selectAllProducts: 'input[name="checkAllProducts[]"]',
        classProductList: '.product_list',
        selectAllAccessories: 'input[name="checkAllAccessories[]"]',
        classAccessorytList: '.accessory_list',
        buttonCopyAccessories: '.copy_default_accessories',
        buttonDeleteOldAccessories: '.delete_old_accessories',
        selectectCopyIdGroup: '#hsma_copy_id_group',
        buttonAssignAccessories: '.assign_accessories_to_products',
        selectedAccessoryItems: '.accessory_list .accessory_item:checked',
        selectedProductItems: '.product_list .product_item:checked',
        buttonRenderAccessoryImage: '.hsma_render_accessory_image',
        buttonShowBlockSetting: '.hsma_show_block_setting',
        selectectIdGroup: '#hsma_id_group'
    };

    $.extend(this._params, params);
    /**
     * Resovle conflic pointer
     */
    AdminSettingMultiAccessories.instance = this;
    AdminSettingMultiAccessories.autocompleteXhr = [];

    this.init = function ()
    {
        /**
         * All event of tab add accessories for products
         */
        $(document)
                .on('click', AdminSettingMultiAccessories.instance._selectors.selectAllProducts, AdminSettingMultiAccessories.instance._checkAllProducts)
                .on('click', AdminSettingMultiAccessories.instance._selectors.selectAllAccessories, AdminSettingMultiAccessories.instance._checkAllAccessories)
                .on('click', AdminSettingMultiAccessories.instance._selectors.buttonAssignAccessories, AdminSettingMultiAccessories.instance._assignAccessories)
                .on('click', AdminSettingMultiAccessories.instance._selectors.buttonCopyAccessories, AdminSettingMultiAccessories.instance._copyAccessories)
                .on('click', AdminSettingMultiAccessories.instance._selectors.buttonDeleteOldAccessories, AdminSettingMultiAccessories.instance._deleteOldAccessories)
                .on('click', AdminSettingMultiAccessories.instance._selectors.buttonRenderAccessoryImage, AdminSettingMultiAccessories.instance._renderAccessoryImages)
                .on('click', AdminSettingMultiAccessories.instance._selectors.buttonShowBlockSetting, AdminSettingMultiAccessories.instance._renderBlockModuleSetting)
                ;
         $(window).scrollTop(0);

    };

    this._assignAccessories = function()
    {
        var idAccesories = [];
        var idProducts = [];
        $(AdminSettingMultiAccessories.instance._selectors.selectedAccessoryItems).each(function (){
            if (parseInt($(this).val()) > 0) {
                idAccesories.push(parseInt($(this).val()));
            }
        });
        
        $(AdminSettingMultiAccessories.instance._selectors.selectedProductItems).each(function (){
            if (parseInt($(this).val()) > 0) {
                idProducts.push(parseInt($(this).val()));
            }
        });
        var idGroup = $(AdminSettingMultiAccessories.instance._selectors.selectectIdGroup).val();
        if (idAccesories.length < 1) {
            alert(AdminSettingMultiAccessories.instance._params.lang.please_select_at_least_1_accessory);
            return;
        }
        if (idProducts.length < 1){
            alert(AdminSettingMultiAccessories.instance._params.lang.please_select_at_least_1_product);
            return;
        }
        if (idGroup < 1){
            alert(AdminSettingMultiAccessories.instance._params.lang.please_select_a_group_accessory);
            return;
        }
        AdminSettingMultiAccessories.instance._assignAccessoriesToProducts(idAccesories, idProducts, idGroup);
    };
    
    this._assignAccessoriesToProducts = function(idAccesories, idProducts, idGroup)
    {
        var stringIdAccesories = idAccesories.join(",");
        var stringIdProducts = idProducts.join(",");
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminSettingMultiAccessories.instance._params.ajaxUrls.ajaxAssignAccessories,
            async: true,
            cache: false,
            dataType: "json",
            data: 'id_group=' + idGroup + '&id_products=' + stringIdProducts + '&id_accessories=' + stringIdAccesories,
            success: function (dataJson)
            {
                if (dataJson.success){
                    for (var i = 0; i < idProducts.length; i++) {
                        if (typeof dataJson.content[idProducts[i]] !== 'undefined') {
                            var hasAccessory = $('.product_'+ idProducts[i]).next('tr').hasClass('has_accessory');
                            if (hasAccessory) {
                                $('.product_'+ idProducts[i]).next('tr').remove();
                            }
                            $(dataJson.content[idProducts[i]]).insertAfter('.product_'+ idProducts[i]);
                        }
                    }
                    showSuccessMessage(dataJson.message);
                }
            },
            complete: function () {
                AdminMultiAccessories.instance._onChangeAccessoryProductPosition('.accessory_group_product');
            },
            error: function (jqXHR, exception)
            {
                alert(AdminSettingMultiAccessories.instance._showErrorException(jqXHR, exception));
            }
        });
    };
    
    this._checkAllProducts = function(element)
    {
        AdminSettingMultiAccessories.instance._checkAll(element.target, AdminSettingMultiAccessories.instance._selectors.classProductList);
    };
    
    this._checkAllAccessories = function(element)
    {
        AdminSettingMultiAccessories.instance._checkAll(element.target, AdminSettingMultiAccessories.instance._selectors.classAccessorytList);
    };
    
    this._checkAll = function (element, classBlock)
    {
        if (element.checked) {
            $(classBlock).find("input[type=checkbox]").each(function (){
                $(this).prop("checked", true);
            });
        } else {
            $(classBlock).find("input[type=checkbox]").each(function (){
                $(this).prop("checked", false);
            });
        }
    };


    /**
     * @param {object} jqXHR
     * @param {string} exception
     */
    this._showErrorException = function (jqXHR, exception)
    {
        var message = '';
        if (jqXHR.status === 0) {
            message = AdminSettingMultiAccessories.instance._params.lang.there_was_a_connecting_problem;
        } else if (jqXHR.status === 404) {
            message = AdminSettingMultiAccessories.instance._params.lang.requested_page_not_found;
        } else if (jqXHR.status === 500) {
            message = AdminSettingMultiAccessories.instance._params.lang.internal_server_error;
        } else if (exception === 'timeout') {
            message = AdminSettingMultiAccessories.instance._params.lang.request_time_out;
        } else if (exception === 'abort') {
            message = AdminSettingMultiAccessories.instance._params.lang.ajax_request_is_aborted;
        } else if (exception === 'parsererror') {
            window.location.reload();
        } else {
            message = jqXHR.responseText + exception;
        }
        alert(message);
    };
    this._copyAccessories = function()
    {
        var idGroup = $(AdminSettingMultiAccessories.instance._selectors.selectectCopyIdGroup).val();
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminSettingMultiAccessories.instance._params.ajaxUrls.ajaxCopyAccessories,
            async: true,
            cache: false,
            dataType: "json",
            data: 'id_group=' + idGroup,
            success: function (dataJson)
            {
                if (dataJson.success){
                    showSuccessMessage(dataJson.message);
                }
            },
            error: function (jqXHR, exception)
            {
                alert(AdminSettingMultiAccessories.instance._showErrorException(jqXHR, exception));
            }
        });
    };
    this._deleteOldAccessories = function()
    {
        if (!confirm(AdminSettingMultiAccessories.instance._params.lang.do_you_want_to_delete_all_added_accessories)) {
            return;
        }
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminSettingMultiAccessories.instance._params.ajaxUrls.ajaxDeleteOldAccessories,
            async: true,
            cache: false,
            dataType: "json",
            success: function (dataJson)
            {
                if (dataJson.success){
                    showSuccessMessage(dataJson.message);
                }
            },
            error: function (jqXHR, exception)
            {
                alert(AdminSettingMultiAccessories.instance._showErrorException(jqXHR, exception));
            }
        });
    };
    this._renderAccessoryImages = function()
    {
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminSettingMultiAccessories.instance._params.ajaxUrls.ajaxRenderAccessoryImages,
            async: true,
            cache: false,
            dataType: "json",
            success: function (dataJson)
            {
                if (dataJson.success){
                    showSuccessMessage(dataJson.message);
                }
            },
            error: function (jqXHR, exception)
            {
                alert(AdminSettingMultiAccessories.instance._showErrorException(jqXHR, exception));
            }
        });
    };
    this._renderBlockModuleSetting = function()
    {
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminSettingMultiAccessories.instance._params.ajaxUrls.ajaxRenderBlockModuleSetting,
            async: true,
            cache: false,
            dataType: "json",
            success: function (dataJson)
            {
                if (dataJson.success){
                    showSuccessMessage(dataJson.message);
                    location.reload();
                }
            },
            error: function (jqXHR, exception)
            {
                alert(AdminSettingMultiAccessories.instance._showErrorException(jqXHR, exception));
            }
        });
    };
};

(function ($) {
    // Public: jScroll Plugin
    $.fn.jScroll = function (options) {
        var opts = $.extend({}, $.fn.jScroll.defaults, options);
        return this.each(function () {
            var $element = $(this);
            var $window = $(window);
            var locator = new location($element);
            $window.scroll(function () {
                $element
                        .stop()
                        .animate(locator.getMargin($window), opts.speed);
            });
        });
        // Private 
        function location($element)
        {
            this.min = $element.offset().top;
            this.originalMargin = parseInt($element.css("margin-top"), 10) || 0;
            this.getMargin = function ($window)
            {
                var max = $element.parent().height() - $element.outerHeight();
                var margin = this.originalMargin;
                if ($window.scrollTop() >= this.min) {
                    margin = margin + opts.top + $window.scrollTop() - this.min;
                }
                if (margin > max){
                    margin = max;
                }
                return ({"marginTop": margin + 'px'});
            };
        }
    };
    // Public: Default values
    $.fn.jScroll.defaults = {
        speed: "fast",
        top: 250
    };
})(jQuery);