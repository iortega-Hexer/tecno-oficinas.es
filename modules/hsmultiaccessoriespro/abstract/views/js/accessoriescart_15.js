
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
if (typeof ajaxCart === 'undefined')
    ajaxCart = {};
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
                        idCustomizations.push(selectedIdCustomization);
                        if (idAccesoryAttributes > 0)
                        {
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

                if (parseInt($(this).val()) > 0)
                {
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
                            ;
                        } else {
                            idAccesoriesAttributes.pop();
                            idAccesories.pop();
                        }
                    }
                }
            });
        }
        if ($('#group_accessories').find('input').hasClass('accessory_item'))
        {
            if (addedFromProductPage)
            {

                $.each($('#group_accessories .accessory_item:checked'), function ()
                {
                    idAccesories.push(parseInt($(this).val()));
                    var parentElement = $(this).parents('tr');

                    if ($(parentElement).find('select').hasClass('product-combination'))
                        idAccesoriesAttributes.push(parseInt($(parentElement).find('.product-combination').val()));
                    else
                        idAccesoriesAttributes.push(parseInt($(this).data('id-product-attribute')));

                    if ($(parentElement).find('input').hasClass('custom_quantity')) {
                        customQty = parseInt($(parentElement).find('.custom_quantity').val());
                        if (customQty > 0) {
                            customQties.push(customQty);
                            ;
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
            alert(alertMessage);
            return false;
        }

        if (addedFromProductPage && (!checkCustomizations() || !isEnoughtCustomizations))
        {
            alert(fieldRequired);
            return;
        }
        emptyCustomizations();
        //disabled the button when adding to not double add if user double click
        if (addedFromProductPage)
        {
            $('#add_to_cart input').attr('disabled', true).removeClass('exclusive').addClass('exclusive_disabled');
            $('.filled').removeClass('filled');
        } else
            $(callerElement).attr('disabled', true);

        if ($('#cart_block_list').hasClass('collapsed'))
            this.expand();
    }
    if (typeof idProduct === 'undefined')
        return;
    var dataPost = 'controller=cart&add=1&ajax=true&qty=' + ((quantity && quantity !== null) ? quantity : '1') + '&id_product=' + idProduct + '&token=' + static_token + ((parseInt(idCombination) && idCombination !== null) ? '&ipa=' + parseInt(idCombination) : '');
    if (typeof idAccesories !== 'undefined' && idAccesories !== '' && idAccesories.length > 0) {
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
        success: function (jsonData, textStatus, jqXHR)
        {
            if (!jsonData.hasError)
            {
                // add appliance to whishlist module
                if (wishlist && typeof WishlistAddProductCart === 'function') {
                    WishlistAddProductCart(wishlist[0], idProduct, idCombination, wishlist[1]);
                }
            }
            if (typeof PriceTable.instance !== 'undefined' && PriceTable.instance.products !== 'undefined') {
                PriceTable.instance.emptyAccessoryCustomizations();
            }
            // add the picture to the cart
            //var $element = $(callerElement).parent().parent().find('a.product_image img,a.product_img_link img');
            var $element = $(callerElement).parents('.accessorygroup').find('.accessory_image');
            if (!$element.length) {
                $element = $('#bigpic');
                if (!$element.length) {
                    $element = $(callerElement).parents('li').find('img');
                }
            }
            var $picture = $element.clone();
            var pictureOffsetOriginal = $element.offset();
            pictureOffsetOriginal.right = $(window).innerWidth() - pictureOffsetOriginal.left - $element.width();

            if ($picture.length)
            {
                $picture.css({
                    position: 'absolute',
                    top: pictureOffsetOriginal.top,
                    right: pictureOffsetOriginal.right
                });
            }

            var pictureOffset = $picture.offset();
            var cartBlock = $('#cart_block');
            if (!$('#cart_block')[0] || !$('#cart_block').offset().top || !$('#cart_block').offset().left)
                cartBlock = $('#shopping_cart');
            var cartBlockOffset = cartBlock.offset();
            cartBlockOffset.right = $(window).innerWidth() - cartBlockOffset.left - cartBlock.width();

            // Check if the block cart is activated for the animation
            if (cartBlockOffset !== undefined && $picture.length)
            {
                $picture.appendTo('body');
                $picture
                        .css({
                            position: 'absolute',
                            top: pictureOffsetOriginal.top,
                            right: pictureOffsetOriginal.right,
                            zIndex: 4242
                        })
                        .animate({
                            width: $element.attr('width') * 0.66,
                            height: $element.attr('height') * 0.66,
                            opacity: 0.2,
                            top: cartBlockOffset.top + 30,
                            right: cartBlockOffset.right + 15
                        }, 1000)
                        .fadeOut(100, function () {
                            ajaxCart.updateCartInformation(jsonData, addedFromProductPage);
                            $(this).remove();
                        });
            } else
                ajaxCart.updateCartInformation(jsonData, addedFromProductPage);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown)
        {
            alert("Impossible to add the product to the cart.\n\ntextStatus: '" + textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText);
            //reactive the button when adding has finished
            if (addedFromProductPage)
                $('#add_to_cart input').removeAttr('disabled').addClass('exclusive').removeClass('exclusive_disabled');
            else
                $(callerElement).removeAttr('disabled');
        }
    });
};

