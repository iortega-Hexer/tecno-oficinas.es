/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Override function add of cart object to add accessory to cart with ogrinal product
 * Copyright (c) 2014 PrestaMonster
 * @param {int} idProduct
 * @param {int} idCombination
 * @param {string} addedFromProductPage
 * @param {string} callerElement
 * @param {float} quantity
 * @param {array} wishlist
 */
ajaxCart.add = function (idProduct, idCombination, addedFromProductPage, callerElement, quantity, wishlist)
{
    if ($(callerElement).hasClass('ajax_add_to_cart_button')) {
        var idAccesories = $(callerElement).data('id-accessories');
        var idAccesoriesAttributes = $(callerElement).data('id-accessories-combination');
        var customQties = $(callerElement).data('custom-qty');
    } else {

        if (!addedFromProductPage) {
            addedFromProductPage = $('body#product').length > 0;// test if this is triggered from product page
        }    
        if ($('.vouchers').length <= 0) {
            $('<table class="vouchers"><tbody></tbody></table>').insertAfter('.cart_block_list .cart_block_no_products');
        }
        var isEnoughtAccessory = true;
        var groupAccessories = $('#group_accessories').find('select');
        var idAccesories = [];
        var idAccesoriesAttributes = [];
        var customQties = [];
        var idCustomizations = [];
        var isEnoughtCustomizations = true;
        // in case option image for dropdown is enable    
        if ($('#group_accessories').find('div').hasClass('dd-container'))
        {
            if ($('#group_accessories').find('.dd-selected-value'))
            {
                $('#group_accessories input.dd-selected-value').each(function () {
                    // only get attribute id value, not id product
                    var idSelectedElement = $(this).parent().parent().attr('id');
                    var idAccesoryAttributes = $(this).val();
                    if (idSelectedElement.indexOf('accessories_group') >= 0)
                    {
                        var selectedIdCustomization = 0;
                        if ($('#'+idSelectedElement).parent().find('input').hasClass('hsma_id_customization')) {
                            selectedIdCustomization = parseInt($('#'+idSelectedElement).parent().find('.hsma_id_customization').val());
                            if (parseInt($('#'+idSelectedElement).parent().find('.hsma_id_customization').data('isenoughcustomization')) === 0) {
                                isEnoughtCustomizations = false;
                            }
                        }
                        if (idAccesoryAttributes > 0)
                        {
                            idCustomizations.push(selectedIdCustomization);
                            idAccesories.push(parseInt(idAccesoryAttributes));
                            var divCombination = $(this).parent().parent().next();
                            var idAccesoryAttribute = 0;
                            if ($(divCombination).find('input').hasClass('dd-selected-value')){
                                idAccesoryAttribute = $(divCombination).find('.dd-selected-value').val();
                            }
                            idAccesoriesAttributes.push(parseInt(idAccesoryAttribute));
                            if (idAccesoryAttributes > 0 && $('#'+idSelectedElement).parent().find('input').hasClass('custom_quantity')) {
                                customQty = parseInt($('#'+idSelectedElement).parent().find('.custom_quantity').val());
                                if (customQty > 0) {
                                    customQties.push(customQty);
                                } else {
                                    idAccesoriesAttributes.pop();
                                    idAccesories.pop();
                                }
                            }
                        }
                    }
                });   
            }        
        }
        if (groupAccessories.hasClass('accessories_group'))
        {
            $('.accessories_group option:selected').each(function (i) {            
                if (parseInt($(this).val()) > 0) {
                    var parentElement = $(this).parents('.option-row');
                    idAccesories.push(parseInt($(this).val()));
                    var selectedIdCustomization = 0;
                    if ($(parentElement).find('input').hasClass('hsma_id_customization')) {
                        selectedIdCustomization = $(parentElement).find('.hsma_id_customization').val();
                        if (parseInt($(parentElement).find('.hsma_id_customization').data('isenoughcustomization')) === 0) {
                            isEnoughtCustomizations = false;
                        }
                    }
                    idCustomizations.push(parseInt(selectedIdCustomization));
                    if ($(parentElement).find('select').hasClass('product-combination'))
                        idAccesoriesAttributes.push(parseInt($(parentElement).find('.product-combination').val()));
                    else
                        idAccesoriesAttributes.push(parseInt($(this).data('id-product-attribute')));

                    if ($(parentElement).find('input').hasClass('custom_quantity')) {
                        customQty = parseInt($(parentElement).find('.custom_quantity').val());
                        if (customQty > 0) {
                            customQties.push(customQty);
                        } else {
                            idAccesoriesAttributes.pop();
                            idAccesories.pop();
                        }                    
                    }

                }
            });
        }
        if ($('#group_accessories').find('input').hasClass('accessory_item')) {
            if (addedFromProductPage) {    
                $('#group_accessories .accessory_item' + ':checked').each(function (i) {
                    var idItem = parseInt($(this).val());
                    if (idItem > 0) {
                         idAccesories.push(idItem);
                        var parentElement = $(this).parents('tr');

                        if ($(parentElement).find('select').hasClass('product-combination'))
                            idAccesoriesAttributes.push(parseInt($(parentElement).find('.product-combination').val()));                
                        else
                            idAccesoriesAttributes.push(parseInt($(this).data('id-product-attribute')));

                        if ($(parentElement).find('input').hasClass('custom_quantity')) {
                            customQty = parseInt($(parentElement).find('.custom_quantity').val());
                            if (customQty > 0) {
                                customQties.push(customQty);
                            } else {
                                idAccesoriesAttributes.pop();
                                idAccesories.pop();
                            }
                        }
                        var selectedIdCustomization = 0;
                        if ($(parentElement).find('input').hasClass('hsma_id_customization')) {
                            selectedIdCustomization = $(parentElement).find('.hsma_id_customization').val();
                            if (parseInt($(parentElement).find('.hsma_id_customization').data('isenoughcustomization')) === 0) {
                                isEnoughtCustomizations = false;
                            }
                            
                        }
                        idCustomizations.push(parseInt(selectedIdCustomization));
                    }

                });
                
                
                $('#group_accessories .accessorygroup').each(function () {
                    var idGroup = $(this).find('.accessory_item').data('groupid');
                    if (typeof idGroup !== 'undefined' && parseInt(window.buyTogetherOption[idGroup]) === parseInt(adminProductSetting.BUY_TOGETHER_YES)) {
                        var numberCheckedAccessory = $(this).find('.accessory_item' + ':checked').length;
                        if (!numberCheckedAccessory) {
                            isEnoughtAccessory = false;
                        }
                    }
                });
            }
        }
        if (idAccesories.length > 0)
            idAccesories = idAccesories.join(",");
        if (idAccesoriesAttributes.length > 0)
            idAccesoriesAttributes = idAccesoriesAttributes.join(",");
        if (customQties.length > 0)
            customQties = customQties.join(",");
        if (idCustomizations.length > 0) {
            idCustomizations = idCustomizations.join(",");
        }
        if (!isEnoughtAccessory)
        {
            $('#group_accessories .accessorygroup').each(function () {
                var idGroup = $(this).find('.accessory_item').data('groupid');
                if (typeof idGroup !== 'undefined' && parseInt(window.buyTogetherOption[idGroup]) === parseInt(adminProductSetting.BUY_TOGETHER_YES)) {
                    if (!$(this).find('.accessory_item' + ':checked').length){
                        priceTable._onClickExpandGroup(this);
                        if (($(this).parent().find('.message_error')).length == 0) {
                            $('<span class="message_error">'+ alertMessage +'</span>').insertBefore($(this));
                        }
                    }
                }
            });
            if ($('.message_error').length) {
                $('html, body').animate({
                    scrollTop: $('.message_error').offset().top - 100
                }, 500);
            }
            return false;
        }

        if (addedFromProductPage && (!checkCustomizations() || !isEnoughtCustomizations))
        {
            if (!!$.prototype.fancybox)
                $.fancybox.open([
                    {
                        type: 'inline',
                        autoScale: true,
                        minHeight: 30,
                        content: '<p class="fancybox-error">' + fieldRequired + '</p>'
                    }
                ], {
                    padding: 0
                });
            else
                alert(fieldRequired);
            return;
        }
        emptyCustomizations();

        //disabled the button when adding to not double add if user double click
        if (addedFromProductPage)
        {
            $('#add_to_cart button').prop('disabled', 'disabled').addClass('disabled');
            $('.filled').removeClass('filled');

        }
        else
            $(callerElement).prop('disabled', 'disabled');

        if ($('.cart_block_list').hasClass('collapsed'))
            this.expand();
    }
    if (typeof idProduct === 'undefined')
        return;
    
    var dataPost = 'controller=cart&add=1&ajax=true&qty=' + ((quantity && quantity !== null) ? quantity : '1') + '&id_product=' + idProduct + '&token=' + static_token + ((parseInt(idCombination) && idCombination !== null) ? '&ipa=' + parseInt(idCombination) : '');
    if (typeof idAccesories !== 'undefined' && idAccesories !== '') {
        dataPost += '&id_accessories=' + idAccesories;
        if (typeof customQties !== 'undefined' && customQties !== '') {
            dataPost += '&custom_qty=' + customQties;
        }
        if (typeof idAccesoriesAttributes !== 'undefined' && idAccesoriesAttributes !== '') {
            dataPost += '&id_accesories_attributes=' + idAccesoriesAttributes;
        }
        if (typeof idCustomizations !== 'undefined' && idCustomizations !== '') {
            dataPost += '&id_customizations=' + idCustomizations;
        }
    }
    //send the ajax request to the server
    $.ajax({
        type: 'POST',
        headers: {"cache-control": "no-cache"},
        url: baseUri + '?rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType: "json",
        data: dataPost,
        success: function (jsonData)
        {
            if (!jsonData.hasError)
            {
                if (typeof PriceTable.instance !== 'undefined' && PriceTable.instance.products !== 'undefined') {
                    PriceTable.instance.emptyAccessoryCustomizations();
                }
                // add appliance to whishlist module
                if (wishlist && typeof WishlistAddProductCart === 'function')
                    WishlistAddProductCart(wishlist[0], idProduct, idCombination, wishlist[1]);                
                window.parent.ajaxCart.updateCartInformation(jsonData, addedFromProductPage);

                if (jsonData.crossSelling)
                    $('.crossseling').html(jsonData.crossSelling);

                if (idCombination)
                    $(jsonData.products).each(function () {
                        if (this.id !== undefined && parseInt(this.id) === parseInt(idProduct) && parseInt(this.idCombination) === parseInt(idCombination))
                            if (typeof window.parent.ajaxCart.updateLayer !== 'undefined' && $.isFunction(window.parent.ajaxCart.updateLayer))
                                window.parent.ajaxCart.updateLayer(this);
                    });
                else
                    $(jsonData.products).each(function () {
                        if (this.id !== undefined && parseInt(this.id) === parseInt(idProduct))
                            if (typeof window.parent.ajaxCart.updateLayer !== 'undefined' && $.isFunction(window.parent.ajaxCart.updateLayer))
                                window.parent.ajaxCart.updateLayer(this);
                    });
                if (typeof window.contentOnly !== 'undefined')
                    parent.$.fancybox.close();
            }
            else
            {
                if (addedFromProductPage)
                    $('#add_to_cart button').removeProp('disabled').removeClass('disabled');
                else
                    $(callerElement).removeProp('disabled');

                alert(jsonData.errors);
            }

        },
        error: function (XMLHttpRequest, textStatus, errorThrown)
        {
            var error = "Impossible to add the product to the cart.<br/>textStatus: '" + textStatus + "'<br/>errorThrown: '" + errorThrown + "'<br/>responseText:<br/>" + XMLHttpRequest.responseText;
            if (!!$.prototype.fancybox)
                $.fancybox.open([
                    {
                        type: 'inline',
                        autoScale: true,
                        minHeight: 30,
                        content: '<p class="fancybox-error">' + error + '</p>'
                    }],
                        {
                            padding: 0
                        });
            else
                alert(error);
            //reactive the button when adding has finished
            if (addedFromProductPage)
                $('#add_to_cart button').removeProp('disabled').removeClass('disabled');
            else
                $(callerElement).removeProp('disabled');
        }
    });


};
