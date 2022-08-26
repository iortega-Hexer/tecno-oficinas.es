/**
 * Handle all events in Admin >> Products >> Product page >> Tab Multi Accessories >> Block Groups Accessories
 *
 * @param {json} selectors
 * @param {json} params
 * Copyright (c) 2015 PrestaMonster
 * @returns {object} AdminAccessories
 */

var AdminMultiAccessories = function (selectors, params)
{
    /**
     * An instance of object AdminProductSetting
     * {AdminProductSetting}
     */
    this.setting = null;

    /**
     * Define all params default of this class
     */
    this._params = {
        ajaxUrls: null, // list of ajax urls
        excludedAccessoryIds: null, // id product exclude for auto search
        messageError: 'error', // Message error
        msgOutOfStock: '', // Message error
        productSettingBuyTogetherRequired: 0,
    };

    /**
     * Define all default selectors of class
     */
    this._selectors = {
        autosearchAccessories: '.hsma_accessory_group .autocomplete_search_accessories', // Input search accessory
        autosearchProduct: '.advanced_setting .autocomplete_search_product', // Input search product
        name: '.name', // Block contain accessory name
        editName: '.edit_name', // Block contain accessory name
        saveName: '.save_name', // Button save name of accessory
        imageEditName: '.img_edit_name', // Button save name of accessory
        blockEditName: '.edit_name, .save_name',
        combinations: '.hsma_accessory_group .dropdown_combination', // Selectbox change combination
        defaultQuantity: 'input[name="default_quantity"]', // Input default quantity
        minQuantity: 'input[name="minimum_quantity"]', // Input min quantity
        iconChangeDefaultQuantity: '.default_quantity a', // Button change default quantity
        position: '.hsma_accessory_group .dragGroup', // Input minimum quantity
        iconChangeMinimumQuantity: '.minimum_quantity a', // Button change minimum quantity
        buyToGetherRequired: '.hsma_accessory_group .buy_together_required', // Selectbox required product & accessory buy together
        delete: '.hsma_accessory_group .delete', // Button delete accessory
        iconShowBlockGroup: '#hsma-accessories h4', // Icon collapse block group accessories
        columnRequiredBuyTogether: '.table .buy_together_required', // Column required buy product & accessory together
        hide: 'hide', // Name of class hide
        show: 'show', // Name of class show
        accessoryName: 'name', // Name of class show
        iconExpand: 'icon-expand-alt', // Name of icon expand +
        iconCollapse: 'icon-collapse-alt', // Name of icon collapse -
        contentGroup: 'content_group', // Name of class content group
        expand: 'expand', // Name of class expand
        accessoryGroup: '.group', // Name of class accessory group
        idAccessories: 'id_accessories_', // Input contain id accessories
        idOfBlockAccessories: 'div_accessories_',
        accessoryList: '.accessory_list',
        idAccessoryList: '#accessory_list_',
        accessoryRow: '.accessory_row',
        globalAccessoryName: '.global_accessory_',
        image: '.image',
        tableAccessoryGroupProduct: '.accessory_group_product',
        xxItemsInside: '.xx-items-inside', // a subtitle of accessory group
        noAccessory: '.no_accessory',
        discountValue: '.discount_value', // input discount value
        discountType: '.discount_type', // select option change discount type
        finalPrice: '.hsma_final_price', // final price column
        oldPrice: '.hsma_price', // price column
        autocompleteSearchNewAccessory: '.autocomplete_search_new_accessory',
        autocompleteSearchOldAccessory: '.autocomplete_search_old_accessory',
        buttonReplaceAccessory: '.replace_accessory',
        inputOldAccessory: 'input[name="id_old_accessory"]',
        inputNewAccessory: 'input[name="id_new_accessory"]',
        errorClass: 'error'
    };

    $.extend(this._params, params);
    $.extend(this._selectors, selectors);
    /**
     * Resovle conflic pointer
     */
    AdminMultiAccessories.instance = this;
    AdminMultiAccessories.autocompleteXhr = [];
    this.init = function ()
    {
        /**
         * All event of tab accessories
         */
        $(document)
                .on('click', AdminMultiAccessories.instance._selectors.saveName, AdminMultiAccessories.instance._onClickButtonSave)
                .on('change', AdminMultiAccessories.instance._selectors.combinations, AdminMultiAccessories.instance._onChangeCombination)
                .on('change', AdminMultiAccessories.instance._selectors.defaultQuantity, AdminMultiAccessories.instance._onChangeDefaultQuantity)
                .on('click', AdminMultiAccessories.instance._selectors.iconChangeDefaultQuantity, AdminMultiAccessories.instance._onClickButtonChangeDefaultQuantity)
                .on('change', AdminMultiAccessories.instance._selectors.minQuantity, AdminMultiAccessories.instance._onChangeMinQuantity)
                .on('click', AdminMultiAccessories.instance._selectors.iconChangeMinimumQuantity, AdminMultiAccessories.instance._onClickButtonChangeMinQuantity)
                .on('click', AdminMultiAccessories.instance._selectors.delete, AdminMultiAccessories.instance._onClickDelete)
                .on('change', AdminMultiAccessories.instance._selectors.buyToGetherRequired, AdminMultiAccessories.instance._onChangeBuyTogether)
                .on('click', AdminMultiAccessories.instance._selectors.iconShowBlockGroup, AdminMultiAccessories.instance._onClickBlockGroup)
                .on('click', AdminMultiAccessories.instance._selectors.name, AdminMultiAccessories.instance._onClickShowBlockEditName)
                .on('click', AdminMultiAccessories.instance._selectors.imageEditName, AdminMultiAccessories.instance._onClickShowBlockEditName)
                .on('click', AdminMultiAccessories.instance._selectors.buttonReplaceAccessory, AdminMultiAccessories.instance._onClickButtonReplaceAccessory)
                .on('change', AdminMultiAccessories.instance._selectors.discountValue, AdminMultiAccessories.instance._onChangeDiscountValue)
                .on('change', AdminMultiAccessories.instance._selectors.discountType, AdminMultiAccessories.instance._onChangeDiscountValue)
                ;
        $(document).on('focus', AdminMultiAccessories.instance._selectors.discountValue, function () {
            if (!$(this).hasClass('error')) {
                previousValueOfDiscountValue = $(this).val();
            }
        });
        if (parseInt(isProductPage) > 0) {
            AdminMultiAccessories.instance._onChangeAccessoryProductPosition(AdminMultiAccessories.instance._selectors.tableAccessoryGroupProduct);
            if (isPrestashop17) {
                AdminMultiAccessories.instance._autoCompleteSearchAccessories17(AdminMultiAccessories.instance._selectors.autosearchAccessories);
                AdminMultiAccessories.instance._autoCompleteSearchProduct17(AdminMultiAccessories.instance._selectors.autosearchProduct);
            } else {
                AdminMultiAccessories.instance._autoCompleteSearchAccessories(AdminMultiAccessories.instance._selectors.autosearchAccessories);
                AdminMultiAccessories.instance._autoCompleteSearchProduct(AdminMultiAccessories.instance._selectors.autosearchProduct);
            }
        }
        
        $(document).on('click', AdminMultiAccessories.instance._selectors.autocompleteSearchOldAccessory, function () {
            $(this).select();
        });
        $(document).on('click', AdminMultiAccessories.instance._selectors.autocompleteSearchNewAccessory, function () {
            $(this).select();
        });
        
        /* Only call functions on the setting module page*/
        if (!isProductPage) {
            AdminMultiAccessories.instance._autoCompleteSearchOldAccessory(AdminMultiAccessories.instance._selectors.autocompleteSearchOldAccessory);
            AdminMultiAccessories.instance._autoCompleteSearchNewAccessory(AdminMultiAccessories.instance._selectors.autocompleteSearchNewAccessory);
        }
    };

    /**
     * Set value of product setting
     * @param {array} productSetting
     * {buy_together:int, custom_displayed_name:boolean}
     */
    this.setSetting = function (productSetting)
    {
        AdminMultiAccessories.instance.setting = productSetting;
    };
    
    /**
     * Auto search accessories
     * @param {string} element
     */
    this._autoCompleteSearchAccessories = function (element)
    {
        $(element).each(function () {
            var idGroup = AdminMultiAccessories.instance._getIdAccessoryGroup(this);
            AdminMultiAccessories.autocompleteXhr[idGroup] = $(this).autocomplete(AdminMultiAccessories.instance._params.ajaxUrls.ajaxAutoCompleteSearch, {
                minChars: 1,
                autoFill: true,
                max: 20,
                matchContains: true,
                mustMatch: true,
                scroll: false,
                cacheLength: 0,
                extraParams: {
                   excludeIds: AdminMultiAccessories.instance.getExcludedAccessoryIds(idGroup)
                },
                formatItem: function (item) {
                    return item[1] + ' - ' + item[0];
                }
            }).result(AdminMultiAccessories.instance._add);
        });

    };
    
    /**
     * Auto search product
     * @param {string} element
     */
    this._autoCompleteSearchProduct = function (element)
    {
        $(element).autocomplete(AdminMultiAccessories.instance._params.ajaxUrls.ajaxAutoCompleteSearch, {
            minChars: 1,
            autoFill: true,
            max: 20,
            matchContains: true,
            mustMatch: true,
            scroll: false,
            cacheLength: 0,
            extraParams: {
                excludeIds: AdminMultiAccessories.instance._params.excludedAccessoryIds
            },
            formatItem: function (item) {
                return item[1] + ' - ' + item[0];
            }
        }).result(AdminMultiAccessories.instance._confirm);
    };

    /**
     * Confirm YES NO CANCEL
     * @param {String} event
     * @param {array} data [idProduct,name]
     * @param {String} formatted
     */
    this._confirm = function (event, data, formatted)
    {
        $.confirm({
            title  : AdminMultiAccessories.instance._params.confirmTitle,
            content: AdminMultiAccessories.instance._params.confirmMessage,
            type: 'blue',
            boxWidth: '500px',
            useBootstrap: false,
            draggable: true,
            closeIcon: true,
            buttons: {
                yes: {
                    btnClass: 'btn-green',
                    text: AdminMultiAccessories.instance._params.yes,
                    action: function ()
                    {
                        if (data instanceof Array && data.length > 1) {
                            var productId = data.length === 2 ? data[1] : data[2];
                            AdminMultiAccessories.instance._copyAccessories(productId, 1);
                        }
                    }
                },
                no: {
                    btnClass: 'btn-blue',
                    text: AdminMultiAccessories.instance._params.no,
                    action: function ()
                    {
                       if (data instanceof Array && data.length > 1) {
                            var productId = data.length === 2 ? data[1] : data[2];
                            AdminMultiAccessories.instance._copyAccessories(productId, 0);
                        }
                    }
                },
                cancel: {
                    class: 'btn-gray',
                    text: AdminMultiAccessories.instance._params.cancel,
                    action: function ()
                    {
                       return;
                    }
                }
            }
        });
    };
    
    /**
     * Add a new accessory
     * @param {String} event
     * @param {array} data
     * [idProduct,name]
     * @param {Array} data
     * @param {String} formatted
     */
    this._add = function (event, data, formatted)
    {
        if (typeof data === 'undefined' || data === null)
        {
            return false;
        }
        if (data instanceof Array && data.length > 1)
        {
            var productId = data.length === 2 ? data[1] : data[2];
            var idGroup = AdminMultiAccessories.instance._getIdAccessoryGroup(event.target);
            $(this).val('');
            var idMainProduct = $("#id_main_product").val();
            var newStringId = $("#" + AdminMultiAccessories.instance._selectors.idAccessories + idGroup).val() + productId + ':0-';
            $("#" + AdminMultiAccessories.instance._selectors.idAccessories + idGroup).val(newStringId);
            var colspan = $(AdminMultiAccessories.instance._selectors.accessoryRow).data('colspan');
            var self = this;
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxAddAccessory,
                async: true,
                cache: false,
                dataType: "json",
                data: 'id_group=' + idGroup + '&id_product=' + productId + '&id_main_product=' + idMainProduct + '&colspan=' + colspan,
                success: function (data)
                {
                    if (data.is_stock_available === 0)
                        alert(AdminMultiAccessories.instance._params.msgOutOfStock);
                    var tableBody = "#" + AdminMultiAccessories.instance._selectors.idOfBlockAccessories + idGroup + ' ' + AdminMultiAccessories.instance._selectors.accessoryList;
                    $(tableBody).append(data.content);
                    AdminMultiAccessories.instance._changeSubtitleOfAccessoryGroup(tableBody, data.xx_items_inside);
                    if (parseInt(data.count_accessory) === 1)
                        $(self).parent().find(AdminMultiAccessories.instance._selectors.noAccessory).remove();
                    $(AdminMultiAccessories.autocompleteXhr[idGroup]).setOptions({
                        extraParams: {excludeIds: AdminMultiAccessories.instance.getExcludedAccessoryIds(idGroup)}
                    });
                    AdminMultiAccessories.instance._onChangeAccessoryProductPosition((AdminMultiAccessories.instance._selectors.tableAccessoryGroupProduct));
                    showSuccessMessage(data.message);
                },
                error: function ()
                {
                    alert(AdminMultiAccessories.instance._params.messageError);
                }
            });
        }
        else
        {

            // Something goes wrong.
        }
    };
    /**
     * Auto search accessories
     * @param {string} element
     */
    this._autoCompleteSearchAccessories17 = function (element)
    {
        $(element).each(function () {
            var idGroup = AdminMultiAccessories.instance._getIdAccessoryGroup(this);
            AdminMultiAccessories.autocompleteXhr[idGroup] = $(this).autocomplete({
            minLength: 1,
            source: function (request, response) {
                $.ajax({
                    url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxAutoCompleteSearch,
                    data: {q: request.term, excludeIds: AdminMultiAccessories.instance.getExcludedAccessoryIds(idGroup)},
                    dataType: "json",
                    success: function (jsonData)
                    {
                        
                        if (typeof jsonData !== 'undefined' && jsonData.length > 0){
                            response($.map(jsonData, function (item)
                            {
                                var accessoriesReference = item.ref ? ' (ref: '+item.ref+')' : '';
                                return {
                                    label: item.id + " - " + item.name + accessoriesReference,
                                    value: item.id
                                };
                            }));
                        } else {
                            $(element).val('');
                            $(element).focus();
                        }
                    },
                    error: function (jqXHR, exception)
                    {
                        AdminMultiAccessories.instance._showErrorException(jqXHR, exception);
                    }

                });
            },
            select: function (event, ui)
            {
                if (typeof (ui) !== undefined)
                {
                    $(this).val('');
                    if (parseInt(ui.item.value) > 0)
                        AdminMultiAccessories.instance._add17(event, ui.item);// add customer
                }
                return false;
            }
        });
           
        });

    };

    /**
     * Auto search product
     * @param {string} element
     */
    this._autoCompleteSearchProduct17 = function (element)
    {
        $(element).autocomplete({
            minLength: 1,
            source: function (request, response) {
                $.ajax({
                    url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxAutoCompleteSearch,
                    data: {q: request.term, excludeIds: AdminMultiAccessories.instance._params.excludedAccessoryIds},
                    dataType: "json",
                    success: function (jsonData)
                    {
                        
                        if (typeof jsonData !== 'undefined' && jsonData.length > 0){
                            response($.map(jsonData, function (item)
                            {
                                var accessoriesReference = item.ref ? ' (ref: '+item.ref+')' : '';
                                return {
                                    label: item.id + " - " + item.name + accessoriesReference,
                                    value: item.id
                                };
                            }));
                        } else {
                            $(element).val('');
                            $(element).focus();
                        }
                    },
                    error: function (jqXHR, exception)
                    {
                        AdminMultiAccessories.instance._showErrorException(jqXHR, exception);
                    }

                });
            },
            select: function (event, ui)
            {
                if (typeof (ui) !== undefined)
                {
                    $(this).val('');
                    if (parseInt(ui.item.value) > 0)
                        AdminMultiAccessories.instance._confirm17(event, ui.item);
                }
                return false;
            }
        });
        
    };

    /**
     * Confirm YES NO CANCEL
     * @param {String} event
     * @param {array} data [idProduct,name]
     */
    this._confirm17 = function (event, data)
    {
        $.confirm({
            title  : AdminMultiAccessories.instance._params.confirmTitle,
            content: AdminMultiAccessories.instance._params.confirmMessage,
            type: 'blue',
            boxWidth: '500px',
            useBootstrap: false,
            draggable: true,
            closeIcon: true,
            buttons: {
                yes: {
                    btnClass: 'btn-green',
                    text: AdminMultiAccessories.instance._params.yes,
                    action: function ()
                    {
                        if (parseInt(data.value) > 0) {
                            var productId = data.value;
                            AdminMultiAccessories.instance._copyAccessories(productId, 1);
                        }
                    }
                },
                no: {
                    btnClass: 'btn-blue',
                    text: AdminMultiAccessories.instance._params.no,
                    action: function ()
                    {
                       if (parseInt(data.value) > 0) {
                            var productId = data.value;
                            AdminMultiAccessories.instance._copyAccessories(productId, 0);
                        }
                    }
                },
                cancel: {
                    btnClass: 'btn-gray',
                    text: AdminMultiAccessories.instance._params.cancel,
                    action: function ()
                    {
                       return;
                    }
                }
            }
        });
    };
    
    /**
     * Copy accessories form product
     * @param {int} idProduct
     * @param {boolean} keepAccessories
     */
    this._copyAccessories = function (idProduct, keepAccessories )
    {
        $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxCopyAccessories,
                async: true,
                cache: false,
                dataType: "json",
                data: 'from_id_product=' + idProduct + '&keep_accessories=' + keepAccessories + '&to_id_product=' + AdminMultiAccessories.instance._params.excludedAccessoryIds,
                success: function (data)
                {
                    if (data.success) {
                        if (data.is_prestashop17) {
                            window.location.reload();
                        } else {
                            window.location.href = data.product_link;
                        }
                    } else {
                        alert(data.message);
                    }
                }
            });
    };
    
    /**
     * Add a new accessory
     * @param {String} event
     * @param {array} data
     */
    this._add17 = function (event, data)
    {     
        if (typeof data === 'undefined' || data === null)
        {
            return false;
        }
        if (parseInt(data.value) > 0)
        {
            var productId = data.value;
            var idGroup = AdminMultiAccessories.instance._getIdAccessoryGroup(event.target);
            $(this).val('');
            var idMainProduct = $("#id_main_product").val();
            var newStringId = $("#" + AdminMultiAccessories.instance._selectors.idAccessories + idGroup).val() + productId + ':0-';
            $("#" + AdminMultiAccessories.instance._selectors.idAccessories + idGroup).val(newStringId);
            var colspan = $(AdminMultiAccessories.instance._selectors.accessoryRow).data('colspan');
            var self = this;
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxAddAccessory,
                async: true,
                cache: false,
                dataType: "json",
                data: 'id_group=' + idGroup + '&id_product=' + productId + '&id_main_product=' + idMainProduct + '&colspan=' + colspan,
                success: function (data)
                {
                    if (data.is_stock_available === 0)
                        alert(AdminMultiAccessories.instance._params.msgOutOfStock);
                    var tableBody = "#" + AdminMultiAccessories.instance._selectors.idOfBlockAccessories + idGroup + ' ' + AdminMultiAccessories.instance._selectors.accessoryList;
                    $(tableBody).append(data.content);
                    AdminMultiAccessories.instance._changeSubtitleOfAccessoryGroup(tableBody, data.xx_items_inside);
                    if (parseInt(data.count_accessory) === 1){
                        $(tableBody).find(AdminMultiAccessories.instance._selectors.noAccessory).remove();
                    }
                    AdminMultiAccessories.instance._onChangeAccessoryProductPosition((AdminMultiAccessories.instance._selectors.tableAccessoryGroupProduct));
                    showSuccessMessage(data.message);
                },
                error: function ()
                {
                    alert(AdminMultiAccessories.instance._params.messageError);
                }
            });
        }
        else
        {

            // Something goes wrong.
        }
    };

    /**
     * Change combination
     * @param {Object} element
     */
    this._onChangeCombination = function (element)
    {
        var id = $(element.target).val();
        var arrayIds = id.split("_");
        var groupId = arrayIds[0];
        var idProduct = arrayIds[1];
        var idProductAttribute = arrayIds[2];
        var idMainProduct = 0;
        if (isProductPage) {
            idMainProduct = $("#id_main_product").val();
        } else {
            idMainProduct = $(element.target).parents('table').data('id-main-product');
        }
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxChangeProductCombination,
            async: true,
            cache: false,
            dataType: "json",
            data: 'id_group=' + groupId + '&id_product=' + idProduct + '&id_main_product=' + idMainProduct + '&id_product_attribute=' + idProductAttribute,
            success: function (data)
            {
                if (data.success) {
                    if (data.is_stock_available === 0) {
                        alert(AdminMultiAccessories.instance._params.msgOutOfStock);
                    }
                    if(typeof data.old_price !== 'undefined'){
                        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.oldPrice).html(data.old_price);
                    }
                    if(typeof data.final_price !== 'undefined'){
                        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.finalPrice).html(data.final_price);
                    }
                    showSuccessMessage(data.message);
                }
                else
                    alert(AdminMultiAccessories.instance._params.messageError);

            },
            error: function ()
            {
                alert(AdminMultiAccessories.instance._params.messageError);
            }
        });
        AdminMultiAccessories.instance.showImage($(element.target));
    };

    /**
     * Delete an accessory
     * @param {Object} element
     */
    this._onClickDelete = function (element)
    {
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var colspan = parseInt($(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).data('colspan'));
        if (typeof idAccessoryGroupProduct === 'undefined' || idAccessoryGroupProduct === null || idAccessoryGroupProduct < 1)
            return false;
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxDeleteAccessory,
            async: true,
            cache: false,
            dataType: "json",
            data: 'id_accessory_group_product=' + idAccessoryGroupProduct + '&colspan=' + colspan,
            success: function (data)
            {
                if (data.success)
                {
                    if (isProductPage) {
                        AdminMultiAccessories.instance._changeSubtitleOfAccessoryGroup(element.target, data.xx_items_inside);
                        AdminMultiAccessories.instance._removeExcludedAccessoryIds(element.target, data.ids_accessory, data.id_group);
                        var self = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).parent();
                        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).remove();
                        if (!isPrestashop17) {
                            $(AdminMultiAccessories.autocompleteXhr[data.id_group]).setOptions({
                                extraParams: {excludeIds: AdminMultiAccessories.instance.getExcludedAccessoryIds(data.id_group)}
                            });
                        }
                        if (parseInt(data.count_accessory) < 1)
                            self.html(data.content);
                    } else {
                        var self = $(element.target).parents(AdminMultiAccessories.instance._selectors.hasAccessory);
                        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).remove();
                        if (parseInt(data.count_accessory) < 1) {
                            self.remove();
                        }
                    }
                    showSuccessMessage(data.message);
                }
                else
                    alert(AdminMultiAccessories.instance._params.messageError);
            },
            error: function ()
            {
                alert(AdminMultiAccessories.instance._params.messageError);
            }
        });
    };

    this._changeSubtitleOfAccessoryGroup = function (element, subtitle)
    {
        $(element)
                .parents(AdminMultiAccessories.instance._selectors.accessoryGroup)
                .find(AdminMultiAccessories.instance._selectors.xxItemsInside)
                .html(subtitle)
                ;
    };

    this._removeExcludedAccessoryIds = function (element, idsAccessory, idGroup)
    {
        var listAccessoriesIds = $(element).parents('.' + AdminMultiAccessories.instance._selectors.contentGroup).find('#' + AdminMultiAccessories.instance._selectors.idAccessories + parseInt(idGroup)).val();
        var excludedAccessoryIds = listAccessoriesIds.replace(idsAccessory + '-', '');
        $('#' + AdminMultiAccessories.instance._selectors.idAccessories + idGroup).val(excludedAccessoryIds);
    };
    /**
     * Change required buy product and accessory together
     * @param {Object} element
     */
    this._onChangeBuyTogether = function (element)
    {
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var requiredBuyTogether = $(element.target).val();
        if (!idAccessoryGroupProduct)
            return;
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxChangeAccessorySettingBuyTogether,
            async: true,
            cache: false,
            dataType: "json",
            data: 'id_accessory_group_product=' + idAccessoryGroupProduct + '&required=' + requiredBuyTogether,
            success: function (data)
            {
                if (!data.success) {
                    alert(AdminMultiAccessories.instance._params.messageError);
                } else {
                    showSuccessMessage(data.message);
                }
            },
            error: function ()
            {
                alert(AdminMultiAccessories.instance._params.messageError);
            }
        });
    };
    
    /**
     * 
     * @param {Object} element
     */
    this._onChangeDiscountValue = function(element){
        
        var discountValue = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountValue).val();
        var discountType = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountType).val();
        if (typeof discountType === 'undefined') {
            discountType = 0;// means '%'
        }
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var idMainProduct = 0;
        if (isProductPage) {
            idMainProduct = $("#id_main_product").val();
        } else {
            idMainProduct =$(element.target).parents('table').data('id-main-product');
        }
        
        if (parseFloat(discountValue) < 0 || isNaN(discountValue))
        {
            if (typeof previousValueOfDiscountValue !== 'undefined') {
                $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountValue).val(previousValueOfDiscountValue);
            } else {
                $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountValue).val(0)
            }
            return;
        }
        if (parseFloat(discountValue) > 100 && parseInt(discountType) === 0) {
            $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountValue).addClass(AdminMultiAccessories.instance._selectors.errorClass);
            $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountType).addClass(AdminMultiAccessories.instance._selectors.errorClass);
            return;
        } else {
            $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountValue).removeClass(AdminMultiAccessories.instance._selectors.errorClass);
            $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.discountType).removeClass(AdminMultiAccessories.instance._selectors.errorClass);
        }
        AdminMultiAccessories.instance._confirmChangeMultiDiscount(discountValue, discountType, idAccessoryGroupProduct, idMainProduct, element);

    };
    
    this._processAddDiscountValue = function(discountValue, discountType, idAccessoryGroupProduct, idMainProduct, multiAdding, element) {
        $.ajax({
                url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxChangeDiscountValue,
                data: {discount_value: discountValue, discount_type: discountType,id_accessory_group_product: idAccessoryGroupProduct, id_main_product: idMainProduct, multi_adding: multiAdding},
                type: 'POST',
                dataType: "json",
                success: function (jsonData) {
                    if (!jsonData.success) {
                        showErrorMessage(jsonData.message);
                    } else if(typeof jsonData.final_price !== undefined){
                        if (multiAdding == 1) {
                            $('.accessory_final_price_'+jsonData.id_accessory).html(jsonData.final_price);
                            $('.accessory_discount_'+jsonData.id_accessory).val(discountValue);
                        } else {
                            $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.finalPrice).html(jsonData.final_price);
                        }
                        showSuccessMessage(jsonData.message);
                    }
                },
                error: function ()
                {
                    alert(AdminMultiAccessories.instance._params.messageError);
                }
            });
    }
    this._confirmChangeMultiDiscount = function(discountValue, discountType, idAccessoryGroupProduct, idMainProduct, element)
    {
        $.confirm({
            title  : AdminMultiAccessories.instance._params.confirmTitleAddDiscount,
            content: AdminMultiAccessories.instance._params.confirmMessageAddDiscount,
            type: 'blue',
            boxWidth: '500px',
            useBootstrap: false,
            draggable: true,
            closeIcon: function(){
                if (typeof previousValueOfDiscountValue !== 'undefined') {
                    $(element.target).val(previousValueOfDiscountValue);
                }
                return;
            },
            buttons: {
                yes: {
                    btnClass: 'btn-green',
                    text: AdminMultiAccessories.instance._params.yes,
                    action: function () {
                        AdminMultiAccessories.instance._processAddDiscountValue(discountValue, discountType, idAccessoryGroupProduct, idMainProduct, 1, element);
                    }
                },
                no: {
                    btnClass: 'btn-blue',
                    text: AdminMultiAccessories.instance._params.no,
                    action: function () {
                        AdminMultiAccessories.instance._processAddDiscountValue(discountValue, discountType, idAccessoryGroupProduct, idMainProduct, 0, element);
                    }
                },
                cancel: {
                    class: 'btn-gray',
                    text: AdminMultiAccessories.instance._params.cancel,
                    action: function ()
                    {
                        if (typeof previousValueOfDiscountValue !== 'undefined') {
                            $(element.target).val(previousValueOfDiscountValue);
                        }
                        return;
                    }
                }
            }
        });
    };
    
    /**
     * Show column required buy product & accessory together
     * @param {Object} element
     */
    this.toggleColumnRequired = function (element)
    {
        $(AdminMultiAccessories.instance._selectors.columnRequiredBuyTogether).toggleClass(AdminMultiAccessories.instance._selectors.hide, parseInt($(element).val()) !== parseInt(AdminMultiAccessories.instance._params.productSettingBuyTogetherRequired));
    };

    /**
     * Action click button save names of accessory
     * @param {type} element
     */
    this._onClickButtonSave = function (element)
    {
        var parent = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow);
        var productUpdate = AdminMultiAccessories.instance.setting._params.customDisplayedName;

        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var $input = parent.find(AdminMultiAccessories.instance._selectors.editName);

        var names = {};
        var stringID = '';
        $.each($input, function () {
            if (this.name in names) {
                if (!$.isArray(names[this.name])) {
                    names[this.alt] = [names[this.name]];
                }
                names[this.alt].push(this.value);
            } else {
                names[this.alt] = this.value;
            }
            stringID = this.id;
        });
        var arrayId = stringID.split('_');
        var idAccessory = arrayId[1];
        var idAccessoryAttribute = typeof arrayId[2] !== 'undefined' ? arrayId[2] : 0;
        AdminMultiAccessories.instance.saveName(idAccessoryGroupProduct, element, idAccessory, idAccessoryAttribute, productUpdate, names);
    };

    /**
     * Event enter quantity into input default quantity
     * @param {Object} element
     */
    this._onChangeDefaultQuantity = function (element)
    {
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var oldQuantity = parseInt($(element.target).data('quantity'));
        var newQuantity = parseInt($(element.target).val());
        var minQuantity = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('min-quantity');
        AdminMultiAccessories.instance._changeDefaultQuantity(idAccessoryGroupProduct, newQuantity, oldQuantity, element.target, minQuantity);
    };

    /**
     * Event click on button up|down default quantity
     * @param {Object} element
     */
    this._onClickButtonChangeDefaultQuantity = function (element)
    {
        var operator = $(element.target).attr('title').trim();
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var oldQuantity = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.defaultQuantity).data('quantity');
        var minQuantity = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('min-quantity');
        var newQuantity = operator === 'up' ? parseInt(oldQuantity) + 1 : parseInt(oldQuantity) - 1;
        var inputQuantity = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.defaultQuantity);
        AdminMultiAccessories.instance._changeDefaultQuantity(idAccessoryGroupProduct, newQuantity, oldQuantity, inputQuantity, minQuantity);
    };

    /**
     * Change default quantity of accessory
     * @param {int} idAccessoryGroupProduct
     * @param {int} newQuantity
     * @param {int} oldQuantity
     * @param {Object} inputQuantity
     * @param {int} minQuantity
     */
    this._changeDefaultQuantity = function (idAccessoryGroupProduct, newQuantity, oldQuantity, inputQuantity, minQuantity)
    {
        if (parseInt(newQuantity) < 0)
            newQuantity = 0;
        if (!idAccessoryGroupProduct || !AdminMultiAccessories.instance._validateQuantity(newQuantity) || !AdminMultiAccessories.instance._validateQuantity(oldQuantity) || parseInt(newQuantity) === parseInt(oldQuantity))
        {
            $(inputQuantity).val(oldQuantity);
            return;
        }
        
        if (parseInt(newQuantity) < parseInt(minQuantity)) {
            alert(AdminMultiAccessories.instance._params.msgDefaultQuantity);
            return;
        }
        
        $.ajax({
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxChangeDefaultQuantity,
            data: {quantity: newQuantity, id_accessory_group_product: idAccessoryGroupProduct},
            type: 'POST',
            dataType: "json",
            success: function (jsonData) {
                if (jsonData.success)
                {
                    $(inputQuantity).data('quantity', newQuantity);
                    $(inputQuantity).val(newQuantity);
                    $(inputQuantity).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('default-quantity', newQuantity);
                    showSuccessMessage(jsonData.message);
                }
                else
                {
                    if (!jsonData.is_stock_available)
                        alert(AdminMultiAccessories.instance._params.msgOutOfStock);
                    else
                        alert(AdminMultiAccessories.instance._params.messageError);
                    $(inputQuantity).val(oldQuantity);
                }
            },
            error: function ()
            {
                alert(AdminMultiAccessories.instance._params.messageError);
            }
        });
    };

    /**
     * Change min quantity of accessory
     * @param {int} idAccessoryGroupProduct
     * @param {int} defaultQuantity
     * @param {int} minQuantity
     * @param {Object} element
     */
    this._changeMinQuantity = function (idAccessoryGroupProduct, defaultQuantity, minQuantity, element)
    {
        if (parseInt(defaultQuantity) < parseInt(minQuantity)) {
           return;
        }
        $.ajax({
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxChangeMinQuantity,
            data: {default_quantity: defaultQuantity, min_quantity: minQuantity, id_accessory_group_product: idAccessoryGroupProduct},
            type: 'POST',
            dataType: "json",
            success: function (jsonData) {
                if (jsonData.success)
                {
                    AdminMultiAccessories.instance._updateNewQuantity(element, defaultQuantity, minQuantity);
                    showSuccessMessage(jsonData.message);
                } else {
                   showErrorMessage(jsonData.message);
                }
            },
            error: function(jqXHR, exception)
            {
                AdminMultiAccessories.instance._showErrorException(jqXHR, exception);
            }
        });
        
    };

    /**
     * Change min quantity of accessory
     * @param {Object} element
     */
    this._onChangeMinQuantity = function (element)
    {
        var defaultQty = $(element.target).data('default-quantity');
        var newMinQty = $(element.target).val();
        var availableQuantity = $(element.target).data('available-quantity');
        var oldMinQty = $(element.target).data('min-quantity');
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);;
        
        if (AdminMultiAccessories.instance._validateMinQuanity(element,newMinQty, oldMinQty, availableQuantity, defaultQty)) {
            if (parseInt(newMinQty) > parseInt(defaultQty)) {
                defaultQty = newMinQty;
            }
            AdminMultiAccessories.instance._changeMinQuantity(idAccessoryGroupProduct, defaultQty, newMinQty, element);
        }
    };
    
    /**
     * 
     * @param {Object} element
     * @param {int} newMinQty
     * @param {int} oldMinQty
     * @param {int} availableQuantity
     * @param {int} defaultQty
     * @returns {boolean}
     */
    this._validateMinQuanity = function(element,newMinQty, oldMinQty, availableQuantity, defaultQty) 
    {
        var flag = true;
        if (!AdminMultiAccessories.instance._validateQuantity(newMinQty))
        {
            $(element.target).val(oldMinQty);
            flag = false;
        } else if (parseInt(newMinQty) > parseInt(availableQuantity)) {
            alert(AdminMultiAccessories.instance._params.msgAvailableQuantity + ' ' + availableQuantity);
            $(element.target).val(oldMinQty);
            flag = false;
        }
        return flag;
    };

    /**
     * Change accessory product position
     * @param {object} element
     */
    this._onChangeAccessoryProductPosition = function (element)
    {
        $(element).each(function () {
            var idGroup = AdminMultiAccessories.instance._getIdAccessoryGroup(this);
            var idMainProduct = AdminMultiAccessories.instance._getIdMainProduct(this);
            var idAccessoryList = AdminMultiAccessories.instance._selectors.idAccessoryList + idGroup;
            if (typeof idMainProduct !== 'undefined' && idMainProduct > 0) {
                idAccessoryList = idAccessoryList + '_' + idMainProduct;
            }
            var originalOrder = false;
                $(this).tableDnD(
                    {
                        dragHandle: 'dragHandle',
                        onDragClass: 'myDragClass',
                        onDragStart: function (table, row) {
                            originalOrder = $.tableDnD.serialize();
                            reOrder = ':even';
                            if (table.tBodies[0].rows[1] && $('#' + table.tBodies[0].rows[1].id).hasClass('alt_row'))
                                reOrder = ':odd';
                            $(table).find('#' + row.id).parent('tr').addClass('myDragClass');
                        },
                        onDrop: function (table, row) {
                            if (originalOrder != $.tableDnD.serialize()) {
                                current = $(row).attr("id");
                                stop = false;
                                accessoriesPositions = "{";
                                $(idAccessoryList).find("tr").each(function (i) {
                                    $("#td_" + $(this).attr("id")).html('<div class="dragGroup"><div class="positions">' + (i + 1) + '</div></div>');
                                    if (!stop || (i + 1) == 2)
                                        accessoriesPositions += '"' + $(this).attr("id") + '" : ' + (i + 1) + ',';
                                });
                                accessoriesPositions = accessoriesPositions.slice(0, -1);
                                accessoriesPositions += "}";
                                AdminMultiAccessories.instance._ajaxChangeAccessoryProductPosition(accessoriesPositions);
                            }
                        }
                    });
        });
    };

    /**
     * Ajax change accessory product prosition
     * @param {json} accessoriesPositions
     */
    this._ajaxChangeAccessoryProductPosition = function (accessoriesPositions)
    {
        $.ajax(
                {
                    url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxProcessUpdateAccessoryProductPosition,
                    data: {accessories_positions: accessoriesPositions},
                    type: 'POST',
                    success: function (data) {
                        data = $.parseJSON(data);
                        if (typeof data.message !== 'undefined' && data.message)
                            showSuccessMessage(data.message);
                        else
                            showErrorMessage(data.error);
                    },
                    error: function (data) {
                        alert(AdminMultiAccessories.instance._params.messageError);
                    }
                });
    };
    /**
     * Event click on button up|down minimum quantity
     * @param {Object} element
     */
    this._onClickButtonChangeMinQuantity = function (element)
    {
        var defaultQty = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('default-quantity');
        var availableQuantity = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('available-quantity');
        var oldMinQty = $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('min-quantity');
        var idAccessoryGroupProduct = AdminMultiAccessories.instance._getIdAccessoryGroupProduct(element);
        var operator = $(element.target).attr('title').trim();
        var newMinQty = operator === 'up' ? parseInt(oldMinQty) + 1 : parseInt(oldMinQty) - 1;
        
        if (AdminMultiAccessories.instance._validateMinQuanity(element,newMinQty, oldMinQty, availableQuantity, defaultQty)) {
            if (parseInt(newMinQty) > parseInt(defaultQty)) {
                defaultQty = newMinQty;
            }
            AdminMultiAccessories.instance._changeMinQuantity(idAccessoryGroupProduct, defaultQty, newMinQty, element);
        }
    };
    
    /**
     * @param {Object} element
     * @param {int} defaultQty
     * @param {int} minQty
     */
    this._updateNewQuantity = function (element, defaultQty, minQty) 
    {
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('default-quantity', defaultQty);
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.defaultQuantity).data('quantity', defaultQty);
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.defaultQuantity).val(defaultQty);
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).data('min-quantity', minQty);
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.minQuantity).val(minQty);
    };

    /**
     * @param {int} quantity
     * @returns {Boolean}
     */
    this._validateQuantity = function (quantity)
    {
        return !isNaN(quantity) && quantity > 0;
    };

    /**
     * Show image when admin change combination
     * @param {Object} element the current target
     */
    this.showImage = function (element)
    {
        imagePath = $(element).find(':selected').data('image');
        $(element).parents(AdminMultiAccessories.instance._selectors.accessoryRow)
                .find(AdminMultiAccessories.instance._selectors.image).find('img')
                .attr('src', imagePath);
    };

    /**
     * Display edit accessory short name field
     * @param {Object} element
     */
    this._onClickShowBlockEditName = function (element)
    {
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.blockEditName).removeClass(AdminMultiAccessories.instance._selectors.hide);
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.name).hide();
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.imageEditName).hide();
    };

    /**
     * Display edit accessory short name field
     * @param {Object} element
     */
    this._hideBlockEditName = function (element)
    {
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.blockEditName).addClass(AdminMultiAccessories.instance._selectors.hide);
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.name).show();
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.imageEditName).show();
    };

    /**
     * Save name of accessory
     * @param {int} idAccessoryGroupProduct
     * @param {object} element
     * @param {int} idAccessory
     * @param {int} idAccessoryAttribute
     * @param {int} productUpdate
     * @param {Array} names
     */
    this.saveName = function (idAccessoryGroupProduct, element, idAccessory, idAccessoryAttribute, productUpdate, names)
    {
        $.ajax({
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxSaveNameUrl,
            data: {id_accessory_group_product: idAccessoryGroupProduct, product_update: productUpdate, names: names},
            type: 'POST',
            dataType: "json",
            success: function (data) {
                if (data.success)
                {
                    if (productUpdate)
                    {
                        $.each(names, function (idLang, name) {
                            $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).find(AdminMultiAccessories.instance._selectors.globalAccessoryName + idAccessory + '_' + idAccessoryAttribute + '_' + idLang).html(name);
                        });
                    }
                    else
                    {
                        $.each(names, function (idLang, name) {
                            $(AdminMultiAccessories.instance._selectors.accessoryGroup).find(AdminMultiAccessories.instance._selectors.globalAccessoryName + idAccessory + '_' + idAccessoryAttribute + '_' + idLang).html(name);
                        });
                    }
                    showSuccessMessage(data.message);
                }
                else
                    alert(AdminMultiAccessories.instance._params.messageError);
                AdminMultiAccessories.instance._hideBlockEditName(element);

            }
        });
    };

    /**
     * Display or hide block contain group accessories
     * @param {Object} element
     */
    this._onClickBlockGroup = function (element)
    {
        var targetElement = $(element.target).parent();
        if (isPrestashop17) {
            
            if (targetElement.find('i').hasClass('add')){
                  targetElement.find('i').html('');
                  targetElement.find('i').html('&#xE15B;');
                  targetElement.find('i').removeClass('add');
                  targetElement.find('i').addClass('remove');
               } else {
                  targetElement.find('i').html('');
                  targetElement.find('i').html('&#xE145;');
                  targetElement.find('i').removeClass('remove');
                  targetElement.find('i').addClass('add');
               }
        } else {
            targetElement.find('i').toggleClass(AdminMultiAccessories.instance._selectors.iconExpand);
        };
        $(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryGroup).find('.' + AdminMultiAccessories.instance._selectors.contentGroup).toggle("slow");
    };

    /**
     * Get list accessory ids already isset in group.
     * @param {int} groupId
     * @returns {Array}
     */
    this.getExcludedAccessoryIds = function (groupId)
    {
        if ($("#" + AdminMultiAccessories.instance._selectors.idAccessories + groupId).val() === 'undefined')
        {
            return '';
        }
        var ids = $("#" + AdminMultiAccessories.instance._selectors.idAccessories + groupId).val().replace(/-/g, ',');
        // remove "," at the end of string
        ids = ids.substring(0, ids.length - 1);
        ids = ids + ',' + AdminMultiAccessories.instance._params.excludedAccessoryIds;
        return ids;
    };

    /**
     * Get id accssory group product
     * @param {object} element
     * @returns {int}
     */
    this._getIdAccessoryGroupProduct = function (element)
    {
        return parseInt($(element.target).parents(AdminMultiAccessories.instance._selectors.accessoryRow).data('id-accessory-group-product'));
    };

    /**
     * Get id accssory group
     * @param {object} element
     * @returns {int}
     */
    this._getIdAccessoryGroup = function (element)
    {
        var idGroup = parseInt($(element).parents(AdminMultiAccessories.instance._selectors.accessoryGroup).data('id-group'));
        if (isNaN(idGroup)){
            idGroup = parseInt($(element).data('id-group'));
        }
        return idGroup;
    };
    
    /**
     * Get id main product
     * @param {object} element
     * @returns {int}
     */
    this._getIdMainProduct = function (element)
    {
        var idMainProduct = parseInt($(element).parents(AdminMultiAccessories.instance._selectors.accessoryGroup).data('id-main-product'));
        if (isNaN(idMainProduct)){
            idMainProduct = parseInt($(element).data('id-main-product'));
        }
        return idMainProduct;
    };
    
    /**
     * @param {object} jqXHR
     * @param {string} exception
     */
    this._showErrorException = function (jqXHR, exception)
    {
        var message = '';
        if (jqXHR.status === 0) {
            message = AdminMultiAccessories.instance._params.msgNoInternet;
        } else if (jqXHR.status == 404) {
            message = AdminMultiAccessories.instance._params.msgPageNotFound;
        } else if (jqXHR.status == 500) {
            message = AdminMultiAccessories.instance._params.msgInternalServerError;
        } else if (exception === 'timeout') {
            message = AdminMultiAccessories.instance._params.msgRequestTimeOut;
        } else if (exception === 'abort') {
            message = AdminMultiAccessories.instance._params.msgAjaxRequestIsAborted;
        } else {
            message = jqXHR.responseText + exception;
        }
        alert(message);
    };
    
    /**
     * Auto search old accessory
     * @param {string} element
     */
    this._autoCompleteSearchOldAccessory = function (element)
    {
        $(element).autocomplete(AdminMultiAccessories.instance._params.ajaxUrls.ajaxAutoCompleteSearch, {
            minChars: 1,
            autoFill: true,
            max: 20,
            matchContains: true,
            mustMatch: true,
            scroll: false,
            cacheLength: 0,
            extraParams: {
                is_setting_page: true
            },
            formatItem: function (item) {
                return item[1] + ' - ' + item[0];
            }
        }).result(AdminMultiAccessories.instance._selectOldAccessory);
    };

    /**
     * Select an old accessory
     * @param {String} event
     * @param {array} data
     * [idProduct,name]
     * @param {Array} data
     * @param {String} formatted
     */
    this._selectOldAccessory = function (event, data, formatted)
    {
        if (typeof data === 'undefined' || data === null) {
            return false;
        }
        if (data instanceof Array && data.length > 1) {
            var productId = data.length === 2 ? data[1] : data[2];
            var productName = data.length === 2 ? data[0] : data[1];
            $(AdminMultiAccessories.instance._selectors.inputOldAccessory).val(productId);
            $(this).val(productName);
        }
    };
    /**
     * Auto search new accessory
     * @param {string} element
     */
    this._autoCompleteSearchNewAccessory = function (element)
    {
        $(element).autocomplete(AdminMultiAccessories.instance._params.ajaxUrls.ajaxAutoCompleteSearch, {
            minChars: 1,
            autoFill: true,
            max: 20,
            matchContains: true,
            mustMatch: true,
            scroll: false,
            cacheLength: 0,
            extraParams: {
                is_setting_page: true
            },
            formatItem: function (item) {
                return item[1] + ' - ' + item[0];
            }
        }).result(AdminMultiAccessories.instance._selectNewAccessory);
    };

    /**
     * Select a new accessory
     * @param {String} event
     * @param {array} data
     * [idProduct,name]
     * @param {Array} data
     * @param {String} formatted
     */
    this._selectNewAccessory = function (event, data, formatted)
    {
        if (typeof data === 'undefined' || data === null) {
            return false;
        }
        if (data instanceof Array && data.length > 1) {
            var productId = data.length === 2 ? data[1] : data[2];
            var productName = data.length === 2 ? data[0] : data[1];
            $(AdminMultiAccessories.instance._selectors.inputNewAccessory).val(productId);
            $(this).val(productName);
        }
    };
    
    /**
     * Event click on the button replace accessory
     * @param {object} event
     */
    this._onClickButtonReplaceAccessory = function (event)
    {
        var oldIdAccessories = parseInt($(AdminMultiAccessories.instance._selectors.inputOldAccessory).val());
        var newIdAccessories = parseInt($(AdminMultiAccessories.instance._selectors.inputNewAccessory).val());
        if (oldIdAccessories < 0 || isNaN(oldIdAccessories) || newIdAccessories < 0 || isNaN(newIdAccessories)) {
            alert(stHsMultiAccessories.lang.please_search_select_a_new_old_accessory);
            return;
        }
        if (oldIdAccessories == newIdAccessories) {
            alert(stHsMultiAccessories.lang.old_new_accessories_should_be_different);
            return;
        }
        $.ajax({
            url: AdminMultiAccessories.instance._params.ajaxUrls.ajaxReplaceAccessory,
            data: {old_id_accessory: oldIdAccessories, new_id_accessory: newIdAccessories},
            type: 'POST',
            dataType: "json",
            success: function (jsonData) {
                if (jsonData.success) {
                    showSuccessMessage(jsonData.message);
                    $(AdminMultiAccessories.instance._selectors.inputNewAccessory).val('');
                    $(AdminMultiAccessories.instance._selectors.inputOldAccessory).val('');
                    $(AdminMultiAccessories.instance._selectors.autocompleteSearchNewAccessory).val('');
                    $(AdminMultiAccessories.instance._selectors.autocompleteSearchOldAccessory).val('');
                } else {
                   showErrorMessage(jsonData.message);
                }
            },
            error: function(jqXHR, exception) {
                AdminMultiAccessories.instance._showErrorException(jqXHR, exception);
            }
        });
    };
};
