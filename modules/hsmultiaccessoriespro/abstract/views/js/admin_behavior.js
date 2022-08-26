/**
 * Handle all events in Admin >> Catalog >> Multi Accessories
 * Example:
 * window.AdminBehavior =  new AdminBehavior().onLoad();
 * window.AdminBehavior.onLoad();
 *
 * @param {json} options
 * Copyright (c) 2014 PrestaMonster
 * @returns {object} AdminBehavior
 */

var AdminBehavior = function (options)
{
    /**
     * Define all default selectors.
     * @var JSON
     */
    this._options = {
        inputBuyAccessoryAndMainTogether: 'input[name="HSMA_BUY_ACCESSORY_MAIN_TOGETHER"]',
        inputShowQuantity: 'input[name="HSMA_SHOW_CUSTOM_QUANTITY"]',
        inputAllowToChangeQuantity: 'input[name="HSMA_ALLOW_CUSTOMER_CHANGE_QTY"]',
        selectDisplayStyle: 'select[name="HSMA_DISPLAY_STYLE"]',
        inputShowImage: 'input[name="HSMA_SHOW_IMAGES"]',
        inputApplyFancyboxToImage: 'input[name="HSMA_APPLY_FANCYBOX_TO_IMAGE"]',
        inputImageSizeInFancybox: 'select[name="HSMA_IMAGE_SIZE_IN_FANCYBOX"]',
        divApplyFancybox: '.apply_fancybox',
        divImageSize: '.image_size',
        blockShowCombination: '.block_show_combination',
        showPriceTable: 'input[name="HSMA_SHOW_PRICE_TABLE"]',
        inputAddEachAcessoryToBasket: 'input[name="HSMA_EACH_ACCESSORY_TO_BASKET"]',
        inputShowTotalPrice: 'input[name="HSMA_SHOW_TOTAL_PRICE"]',
        alertMessageBlock: '.alertmessage'
    };

    $.extend(this._options, options);

    /**
     * Set default value of add accessory to basket
     */
    this.addAccessoryToBasketDefault = 0;

    /**
     * solve conflict this with autocomplete object
     * @object AdminBehavior
     */
    var AdminBehavior = this;

    /**
     * Hide add accessory to basket if buy together is enable & display style is checkbox.
     * @param {int} buyTogetherOptionValue buy together option value
     * @param {int} displayOptionValue display style option value
     */
    this.hideAddAccessoryToBasket = function (buyTogetherOptionValue, displayOptionValue) {

        if (parseInt(buyTogetherOptionValue) === 1 && (parseInt(displayOptionValue) === HsMaDisplayStyle.CHECKBOX || parseInt(displayOptionValue) === HsMaDisplayStyle.RADIO))
        {
            $(AdminBehavior._options.inputAddEachAcessoryToBasket).each(function () {
                if (parseInt($(this).val()) === 0)
                    $(this).attr('checked', true);
                else
                    $(this).attr('checked', false);
            });
            $(AdminBehavior._options.inputAddEachAcessoryToBasket).attr('disabled', 'disabled');
        }
        else
        {
            $(AdminBehavior._options.inputAddEachAcessoryToBasket).removeAttr('disabled');
            $(AdminBehavior._options.inputAddEachAcessoryToBasket).each(function () {
                if (parseInt($(this).val()) === AdminBehavior.addAccessoryToBasketDefault)
                    $(this).attr('checked', true);

            });

        }
    };

    this.hideShowTotalPrice = function (buyTogetherOptionValue) {

        if (parseInt(buyTogetherOptionValue) === 1) {
            $(AdminBehavior._options.inputShowTotalPrice).removeAttr('disabled');
        } else {
            $(AdminBehavior._options.inputShowTotalPrice).each(function () {
                if (parseInt($(this).val()) === 0) {
                    $(this).attr('checked', true);
                } else {
                    $(this).attr('checked', false);
                }
            });
            $(AdminBehavior._options.inputShowTotalPrice).attr('disabled', 'disabled');
        }
    };
    
    /**
     * Hide alert message block (only work with PS 1.6)
     */
    this.hideAlertMessage = function () {
        var buyTogetherOptionValue = $(AdminBehavior._options.inputBuyAccessoryAndMainTogether + ':checked').val();
        if (parseInt(buyTogetherOptionValue) === parseInt(AdminProductSetting.BUY_TOGETHER_NO))
            $(AdminBehavior._options.alertMessageBlock).slideUp();
        else
            $(AdminBehavior._options.alertMessageBlock).slideDown();
    };

    /**
     * Hide apply fancybox to image block
     */
    this.hideApplyFancyboxToImage = function () {
        var inputShowImageValue = $(AdminBehavior._options.inputShowImage + ':checked').val();
        if (parseInt(inputShowImageValue) === 1)
        {
            $(AdminBehavior._options.divApplyFancybox).removeClass('hide');
            $(AdminBehavior._options.divApplyFancybox).addClass('show');
            $(AdminBehavior._options.divImageSize).removeClass('hide');
            $(AdminBehavior._options.divImageSize).addClass('show');
        }
        else
        {
            $(AdminBehavior._options.divApplyFancybox).removeClass('show');
            $(AdminBehavior._options.divApplyFancybox).addClass('hide');
            $(AdminBehavior._options.divImageSize).removeClass('show');
            $(AdminBehavior._options.divImageSize).addClass('hide');
        }
    };

    /**
     * Hide image size in fancybox
     */
    this.hideImageSizeInFancybox = function () {
        var inputApplyFancyboxToImageValue = $(AdminBehavior._options.inputApplyFancyboxToImage + ':checked').val();
        var inputShowImageValue = $(AdminBehavior._options.inputShowImage + ':checked').val();
        if (parseInt(inputApplyFancyboxToImageValue) === 1 && parseInt(inputShowImageValue) === 1)
        {
            $(AdminBehavior._options.divImageSize).removeClass('hide');
            $(AdminBehavior._options.divImageSize).addClass('show');
        }
        else
        {
            $(AdminBehavior._options.divImageSize).removeClass('show');
            $(AdminBehavior._options.divImageSize).addClass('hide');
        }
    };
    
    this.hideOptionDisplayCombinationInTablePrice = function () {
        var showPriceTable = $(AdminBehavior._options.showPriceTable + ':checked').val();
        if (parseInt(showPriceTable) === 1)
        {
            $(AdminBehavior._options.blockShowCombination).removeClass('hide');
            $(AdminBehavior._options.blockShowCombination).addClass('show');
        }
        else
        {
            $(AdminBehavior._options.blockShowCombination).removeClass('show');
            $(AdminBehavior._options.blockShowCombination).addClass('hide');
        }
    };

    /**
     *
     * Update buy together option depend on display style option
     */
    this.updateBuyTogetherOption = function () {
        // when the page is loaded
        var buyTogetherOptionValue = $(AdminBehavior._options.inputBuyAccessoryAndMainTogether + ':checked').val();
        var displayOptionValue = $(AdminBehavior._options.selectDisplayStyle).val();
        AdminBehavior.hideAddAccessoryToBasket(buyTogetherOptionValue, displayOptionValue);
        AdminBehavior.hideShowTotalPrice(buyTogetherOptionValue);
    };

    this.hideShowOptionAllowToChangeQuantity = function (showQuantityBox) {

        if (parseInt(showQuantityBox) === 1) {
            $(AdminBehavior._options.inputAllowToChangeQuantity).removeAttr('disabled');
        } else {
            $(AdminBehavior._options.inputAllowToChangeQuantity).each(function () {
                if (parseInt($(this).val()) === 0) {
                    $(this).attr('checked', true);
                } else {
                    $(this).attr('checked', false);
                }
            });
            $(AdminBehavior._options.inputAllowToChangeQuantity).attr('disabled', 'disabled');
        }
    };
    this._updateOptionAllowToChangQuantity = function () {
        // when the page is loaded
        var showQuantityBox = $(AdminBehavior._options.inputShowQuantity + ':checked').val();
        AdminBehavior.hideShowOptionAllowToChangeQuantity(showQuantityBox);
    };
    
    
    /**
     * Handle all events
     */
    this.onLoad = function () {
        AdminBehavior.hideAlertMessage();
        AdminBehavior.hideApplyFancyboxToImage();
        AdminBehavior.hideImageSizeInFancybox();
        AdminBehavior.hideOptionDisplayCombinationInTablePrice();
        AdminBehavior.addAccessoryToBasketDefault = parseInt($(AdminBehavior._options.inputAddEachAcessoryToBasket + ':checked').val());
        AdminBehavior.updateBuyTogetherOption();
        AdminBehavior._updateOptionAllowToChangQuantity();

        $(AdminBehavior._options.inputBuyAccessoryAndMainTogether).change(function () {
            AdminBehavior.hideAlertMessage();
            AdminBehavior.updateBuyTogetherOption();
        });
        $(AdminBehavior._options.inputShowQuantity).change(function () {
            AdminBehavior._updateOptionAllowToChangQuantity();
        });
        $(AdminBehavior._options.selectDisplayStyle).change(function () {
            AdminBehavior.updateBuyTogetherOption();
            AdminBehavior.hideApplyFancyboxToImage();
        });
        $(AdminBehavior._options.inputShowImage).change(function () {
            AdminBehavior.hideApplyFancyboxToImage();
        });
        $(AdminBehavior._options.inputApplyFancyboxToImage).change(function () {
            AdminBehavior.hideImageSizeInFancybox();
        });
        $(AdminBehavior._options.showPriceTable).change(function () {
            AdminBehavior.hideOptionDisplayCombinationInTablePrice();
        });
    };

};

$(document).ready(function () {
    window.AdminBehavior = new AdminBehavior();
    window.AdminBehavior.onLoad();
});