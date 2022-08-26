/**
 * Multi Accessories Pro for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * handle envent 
 * @param {json} selectors 
 * @returns {HsmaCustomizationPopUp}
 */

var HsmaCustomizationPopUp = function (selectors)
{
    /**
     * Define all selectors default of class
     */
    this._selectors = {
        layerAccessory: '#layer_accessory_customization',
        layerAccessoryOverlay: '.layer_accessories_overlay',
        closeAccessoryCustomizePopUp: '#layer_accessory_customization .close, #layer_accessory_customization .cancel_accessory_customization',
        buttonSubmintAccessoryCustomization: '#layer_accessory_customization .submit_accessory_customization',
        blockErrors: '#layer_accessory_customization .hsma_show_error',
        inputFile: '#layer_accessory_customization input[type="file"]',
        inputText: '#layer_accessory_customization .hsma_accessory_text_field',
        modalTitle: '.layer_accessory .modal-title',
        accessoryCustomizationInfo: '.layer_accessory_customization_info',
        blockAccessoryCustomization: '.layer_accessory_customization_info .hsma_block_customization',
        buttonDeleteImage: '#layer_accessory_customization .hsma_remove_image',
        buttonSubmitCustomization: '#hsma_add_accessory_customization'

    };

    /**
     * Contain the current id accessory
     */
    this.idAccessory;
    this.idGroup;
    this.ajaxUrl;
    this.contentPopup;
    this.titlePopup;
    this.randomId;

    $.extend(this._selectors, selectors);
    HsmaCustomizationPopUp.instance = this;
    /**
     * show popup accessory customization
     */
    this.show = function () {
        var heightop = parseInt($(window).scrollTop());
        $(this._selectors.modalTitle).html(HsmaCustomizationPopUp.instance.getTitlePopup());
        $(this._selectors.accessoryCustomizationInfo).html(HsmaCustomizationPopUp.instance.getContentPopup());
        $(this._selectors.layerAccessoryOverlay).css('width', '100%');
        $(this._selectors.layerAccessoryOverlay).css('height', '100%');
        $(this._selectors.blockAccessoryCustomization).css('display', 'block');
        $(this._selectors.layerAccessoryOverlay).show();
        $(this._selectors.layerAccessory).css({'top': heightop}).fadeIn('fast');
        this.handleEvent();
    };

    /**
     * Define handle Event
     */
    this.handleEvent = function ()
    {
        /* Event click button close popup*/
        $(document).on('click', this._selectors.closeAccessoryCustomizePopUp, function () {
            window.parent.hsmaCustomizationPopUp.close();
        });
        /* Event click out side popup */
        $(document).on('click', this._selectors.layerAccessoryOverlay, function () {
            window.parent.hsmaCustomizationPopUp.close();
        });
        
        $(document).on('keyup', this._selectors.inputText, function () {
            $(this).removeClass('error');
            $(this).addClass('valid');
            $(HsmaCustomizationPopUp.instance._selectors.blockErrors).html('');
            var value = $(this).val();
            if (value.length > 1) {
                if (HsmaCustomizationPopUp.instance._isMessage(value)) {
                    $(this).removeClass('error');
                    $(this).addClass('valid');
                } else {
                    $(this).removeClass('valid');
                    $(this).addClass('error');
                }
            }
        });
        HsmaCustomizationPopUp.instance._initUniform();
        /* Event click on the button submit form */
        $(this._selectors.buttonSubmitCustomization).on('submit', (function (e) {
            e.preventDefault();
            if (HsmaCustomizationPopUp.instance._isEmptyForm() && typeof msgEmptyForm !== 'undefined') {
                HsmaCustomizationPopUp.instance._showError([msgEmptyForm]);
                return;
            }
            var isValidate = HsmaCustomizationPopUp.instance._validate();
            if (isValidate) {
                HsmaCustomizationPopUp.instance._submitAccessoryCustomization(this);
            }
        }));
        
        $(HsmaCustomizationPopUp.instance._selectors.buttonDeleteImage).on('click', (function () {
            var idCustomizationField = parseInt($(this).data('idcustomizationfield'));
            var randomId = HsmaCustomizationPopUp.instance.getRandomId();
            var idAccessory = parseInt($(this).data('idaccessory'));
            var ajaxUrl = HsmaCustomizationPopUp.instance.getAjaxUrl();
            if (typeof randomId === 'undefined' || typeof ajaxUrl === 'undefined' || typeof idCustomizationField === 'undefined' || typeof idAccessory === 'undefined' || idCustomizationField < 1 || idAccessory < 1) {
                alert('Error!');
                return;
            }
            var dataPost = new FormData();
            dataPost.append('id_product', idAccessory);
            dataPost.append('ajax', true);
            dataPost.append('id_customization_field', idCustomizationField);
            dataPost.append('action', 'DeleteCustomizationImage');
            $.ajax({
                url: ajaxUrl,
                type: "POST",
                data: dataPost,
                contentType: false,
                cache: false,
                processData: false,
                dataType: "json",
                success: function (jsonData)
                {
                    if (jsonData.success) {
                        /* Update customization of current accessory*/
                        HsmaCustomizationPopUp.instance._updateAccessoryCustomizationEverywhere(jsonData, randomId);
                        if (typeof PriceTable.instance.products !== 'undefined') {
                            PriceTable.instance.products[randomId]['id_customization'] = jsonData.id_customization;
                        }
                        $(HsmaCustomizationPopUp.instance._selectors.blockAccessoryCustomization).css('display', 'block');
                    } else {
                        HsmaCustomizationPopUp.instance._showError(jsonData.errors);
                    }
                }
            });
        }));

        /* Get file name after selected file*/
        $(this._selectors.inputFile).change(function (e) {
            var fileName = e.target.files[0].name;
            $(this).parent().find('.filename').html(fileName);
            $(this).parent().find('.filename').removeClass('error');
            $(this).parent().find('.filename').addClass('valid');
            $(HsmaCustomizationPopUp.instance._selectors.blockErrors).html('');
        });

    };
    this.setIdAccessory = function (idAccessory)
    {
        if (typeof idAccessory !== 'undefined') {
            this.idAccessory = parseInt(idAccessory);
        }
    };
    this._initUniform = function ()
    {
        if (!!$.prototype.uniform) {
            $("#layer_accessory_customization input[type='file'], #layer_accessory_customization .hsma_accessory_text_field").uniform();
        }
    };
    this.setIdGroup = function (idGroup)
    {
        if (typeof idGroup !== 'undefined') {
            this.idGroup = parseInt(idGroup);
        }
    };
    this.setAjaxUrl = function (ajaxUrl)
    {
        if (typeof ajaxUrl !== 'undefined') {
            this.ajaxUrl = this.synUrl(ajaxUrl);
        }
    };
    this.setContentPopup = function (contentPopup)
    {
        if (typeof contentPopup !== 'undefined') {
            this.contentPopup = contentPopup;
        }
    };
    this.setTitlePopup = function (titlePopup)
    {
        if (typeof titlePopup !== 'undefined') {
            this.titlePopup = titlePopup;
        }
    };
    this.setRandomId = function (randomId)
    {
        if (typeof randomId !== 'undefined') {
            this.randomId = randomId;
        }
    };
    this.getIdAccessory = function ()
    {
        return this.idAccessory;
    };
    this.getIdGroup = function ()
    {
        return this.idGroup;
    };

    this.getContentPopup = function ()
    {
        return this.contentPopup;
    };
    this.getTitlePopup = function ()
    {
        return this.titlePopup;
    };
    this.getRandomId = function ()
    {
        return this.randomId;
    };
    /**
     * Get ajax url of QuoteButton
     */
    this.getAjaxUrl = function ()
    {
        return this.ajaxUrl;
    };
    this._toggleClassError = function (element, isError) {
        if (isError) {
            $(element).removeClass('valid');
            $(element).addClass('error');
        } else {
            $(element).removeClass('error');
            $(element).addClass('valid');
    }
    };
    this._validate = function () {
        var result = true;
        $(this._selectors.inputText).each(function (i) {
            var value = $(this).val();
            if ($(this).hasClass('is_required')) {
                if (value.length < 1) {
                    HsmaCustomizationPopUp.instance._toggleClassError(this, true);
                    result = false;
                }
            }
            if (value.length > 0) {
                if (!HsmaCustomizationPopUp.instance._isMessage(value)) {
                    HsmaCustomizationPopUp.instance._toggleClassError(this, true);
                    result = false;
                }
            }
        });
        $(this._selectors.inputFile).each(function (i) {
            if ($(this).hasClass('is_required')) {
                var value = $(this).val();
                if (value.length < 1) {
                    $(this).parent().find('.filename').addClass('error');
                    result = false;
                }
            }
        });
        return result;
    };
    this._isEmptyForm = function () {
        var isEmptyFields = true;
        $(this._selectors.inputText).each(function (i) {
            if ($(this).val().length > 0) {
                isEmptyFields = false;
            }
        });
        $(this._selectors.inputFile).each(function (i) {
            if ($(this).val().length > 0) {
                isEmptyFields = false;
            }
        });
        return isEmptyFields;
    };
    this._isMessage = function (s)
    {
        var reg = /^[^<>{}]+$/;
        return reg.test(s);
    };
    this._submitAccessoryCustomization = function (element) {
        var idGroup = HsmaCustomizationPopUp.instance.getIdGroup();
        var idAccessory = HsmaCustomizationPopUp.instance.getIdAccessory();
        var randomId = HsmaCustomizationPopUp.instance.getRandomId();
        if (!this.getAjaxUrl() || !idAccessory || !idGroup) {
            return;
        }
        var dataPost = new FormData(element);
        dataPost.append('id_product', idAccessory);
        dataPost.append('ajax', true);
        dataPost.append('action', 'AddAccessoryCustomization');
        $.ajax({
            url: HsmaCustomizationPopUp.instance.getAjaxUrl(),
            type: "POST",
            data: dataPost,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            success: function (jsonData)
            {
                if (jsonData.success) {
                    HsmaCustomizationPopUp.instance._updateAccessoryCustomizationEverywhere(jsonData, randomId);
                    HsmaCustomizationPopUp.instance.close();
                } else {
                    HsmaCustomizationPopUp.instance._showError(jsonData.errors);
                }
            }
        });
    };
    this._showError = function (errors)
    {
        var messageError = '<article class="alert alert-danger" role="alert" data-alert="danger"><ul>';
        $.each(errors, function (key, msg) {
            messageError += '<li>' + msg + '</li>';
        });
        messageError += '</ul></article>';
        $(HsmaCustomizationPopUp.instance._selectors.blockErrors).html(messageError);
    };
    /**
     * syn url with current location protocol
     * @param url string
     * @returns string
     */
    this.synUrl = function (url)
    {
        var synUrl = '';
        if (typeof url !== 'undefined')
            synUrl = url.indexOf('https:') > -1 ? url.replace("https:", document.location.protocol) : url.replace("http:", document.location.protocol);
        return synUrl;
    };
    
    
    this._updateAccessoryCustomizationEverywhere = function (jsonData, randomId) {
        if (typeof PriceTable.instance.products !== 'undefined') {
            PriceTable.instance.products[randomId]['customizations'] = jsonData.customizations;
            PriceTable.instance.products[randomId]['is_enough_customization'] = jsonData.is_enough_customization;
            HsmaCustomizationPopUp.instance.setContentPopup(PriceTable.instance._getCustomzationPopupContent(randomId));
        }
        $(HsmaCustomizationPopUp.instance._selectors.accessoryCustomizationInfo).html(HsmaCustomizationPopUp.instance.getContentPopup());
        HsmaCustomizationPopUp.instance.handleEvent();
        var idGroup = HsmaCustomizationPopUp.instance.getIdGroup();
        var idAccessory = HsmaCustomizationPopUp.instance.getIdAccessory();
        var inputCustomizationData = '<input type="hidden" name="hsma_id_customization" class="hsma_id_customization" data-isenoughcustomization="'+jsonData.is_enough_customization+'" value="'+jsonData.id_customization+'">';
        var currentElement = $('#product_list_accessory_' + idGroup + ' .accessory_customization_' + idAccessory);
        currentElement.find('.hsma_id_customization').replaceWith(inputCustomizationData);
        if (jsonData.is_enough_customization) {
            currentElement.find('.hsma_warning_red').addClass('hide');
        } else {
            currentElement.find('.hsma_warning_red').removeClass('hide');
        }
    };
    /**
     *  Close popup
     */
    this.close = function () {
        $(this._selectors.layerAccessoryOverlay).hide();
        $(this._selectors.layerAccessory).fadeOut('fast');
    };
};
