/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * This is an equivalent of AjaxCart.add() and only in use if module "blockCart" or disabled, or (enabled + ajax mode is disabled)
 * After adding product and accessories to cart => redirect to order page
 * @param {int} idProduct
 * @param {int} idCombination
 * @param {int} quantity
 */
function addToCart(idProduct, idCombination, quantity, mainPorduct)
{
    var isEnoughtAccessory = true;
    var groupAccessories = $('#group_accessories').find('select');
    var idAccesories = [];
    var idAccesoriesAttributes = [];
    var customQties = [];
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
                if ($(parentElement).find('select').hasClass('product-combination'))
                    idAccesoriesAttributes.push(parseInt($(parentElement).find('.product-combination').val()));
                else
                    idAccesoriesAttributes.push(parseInt($(this).data('id-product-attribute')));

                if ($(parentElement).find('input').hasClass('custom_quantity')) {
                    customQty = parseInt($(parentElement).find('.custom_quantity').val());
                    if (customQty > 0) {
                        customQties.push(customQty);;
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
        if (mainPorduct)
        {
            $('#group_accessories .accessory_item' + ':checked').each(function (i)
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
                    } else {
                        idAccesoriesAttributes.pop();
                        idAccesories.pop();
                    }
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
    if (!isEnoughtAccessory)
    {
        alert(alertMessage);
        return false;
    }
    var dataPost = 'controller=cart&add=1&ajax=true&qty=' + ((quantity && quantity !== null) ? quantity : '1') + '&id_product=' + idProduct + '&token=' + static_token + ((parseInt(idCombination) && idCombination !== null) ? '&ipa=' + parseInt(idCombination) : '');
    if (typeof idAccesories !== 'undefined' && idAccesories !== '') {
        dataPost += '&id_accessories=' + idAccesories;
        if (typeof customQties !== 'undefined' && customQties !== '') {
            dataPost += '&custom_qty=' + customQties;
        }
        if (typeof idAccesoriesAttributes !== 'undefined' && idAccesoriesAttributes !== '') {
            dataPost += '&id_accesories_attributes=' + idAccesoriesAttributes;
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
        success: function ()
        {
            window.location.href = orderUrl;
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

        }
    });
}