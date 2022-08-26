/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * 
 * Handle all events in Admin >> Products >> Product page >> Tab Multi Accessories >> Block Advanced settings
 *
 * @param {json} selectors
 * @param {json} params
 * Copyright (c) 2015 PrestaMonster
 * @returns {Object} adminProductSetting
 */

var AdminProductSetting = function (selectors, params)
{
    /**
     * Buy product & accessory together is not required
     * @var int
     */
    this.BUY_TOGETHER_NO = 0;

    /*
     * Buy product & accessory together is required
     * @var int
     */
    this.BUY_TOGETHER_YES = 1;

    /*
     * Use global setting
     * In this case buy together will be equal 0 or 1.
     * @var int
     */
    this.BUY_TOGETHER_USE_DEFAULT = 2;

    /**
     * Admin will be choose accessories which are buy together with main product.
     * @var int
     */
    this.BUY_TOGETHER_REQUIRED = 3;

    /**
     * Callback change setting buy main product & accessory together
     */
    this._toggleColumnRequiredHandler;

    /**
     * Define all params default of this class
     */
    this._params = {
        ajaxUrls: null, // list of ajax urls
        messageError: 'error', // Message error
        customDisplayedName: 0, // Store setting buy main product & accessory together
        buyMainTogether: 0 // Store setting use custom displayed names for this product
    };

    /**
     * Define all selectors default of class
     */
    this._selectors = {
        buyTogether: '.hsma_product_setting select.hsma_buy_together', // Select box setting buy main product & accessory together
        productSetting: '.hsma_product_setting',
        customDisplayedNames: '.hsma_product_setting input[name="custom_displayed_name"]' // Input use custom displayed names for this product
    };

    $.extend(this._params, params);
    $.extend(this._selectors, selectors);
    AdminProductSetting.instance = this;

    this.init = function ()
    {
        if (typeof this._toggleColumnRequiredHandler === 'function') {
            $(document).on('change', AdminProductSetting.instance._selectors.buyTogether, function () {
                AdminProductSetting.instance._toggleColumnRequiredHandler(this);
                AdminProductSetting.instance._changeBuyTogether(this);
            });
        }

        // Event change setting use cutom displayed names for this product
        $(document).on('change', AdminProductSetting.instance._selectors.customDisplayedNames, AdminProductSetting.instance._onChangeCustomDisplayedNames);
    };

    /**
     * Change product setting buy together
     * @param {Object} handler
     */
    this.onChangeBuyTogether = function (handler)
    {
        if (typeof handler === 'function') {
            this._toggleColumnRequiredHandler = handler;
        }
    };

    this._changeBuyTogether = function (element)
    {
        var buyTogether = parseInt($(element).val());
        var idProduct = AdminProductSetting.instance._getIdProduct(element);
        if (!idProduct)
            return;
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminProductSetting.instance._params.ajaxUrls.ajaxChangeProductSettingBuyTogether,
            async: true,
            cache: false,
            dataType: "json",
            data: 'buy_together=' + buyTogether + '&id_product=' + idProduct,
            success: function (data)
            {
                if (data.success) {
                    AdminProductSetting.instance._params.buyMainTogether = buyTogether;
                    showSuccessMessage(data.message);
                } else {
                    alert(AdminProductSetting.instance._params.messageError);
                }

            },
            error: function ()
            {
                alert(AdminProductSetting.instance._params.messageError);
            }
        });
    };

    /**
     * Change setting use custom displayed names for this product
     * @param {Object} element
     */
    this._onChangeCustomDisplayedNames = function (element)
    {
        var customDisplayedName = parseInt($(element.target).val());
        var idProduct = AdminProductSetting.instance._getIdProduct(element.target);
        if (!idProduct)
            return;
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminProductSetting.instance._params.ajaxUrls.ajaxChangeCustomDisplayedName,
            async: true,
            cache: false,
            dataType: "json",
            data: 'custom_displayed_name=' + customDisplayedName + '&id_product=' + idProduct,
            success: function (data)
            {
                if (data.success) {
                    AdminProductSetting.instance._params.customDisplayedName = customDisplayedName;
                    showSuccessMessage(data.message);
                } else {
                    alert(AdminProductSetting.instance._params.messageError);
                }
            },
            error: function ()
            {
                alert(AdminProductSetting.instance._params.messageError);
            }
        });
    };

    /**
     * Get id main product
     * @param {object} element
     * @returns {int}
     */
    this._getIdProduct = function (element)
    {
        return parseInt($(element).parents(AdminProductSetting.instance._selectors.productSetting).data('id-product'));
    };

};