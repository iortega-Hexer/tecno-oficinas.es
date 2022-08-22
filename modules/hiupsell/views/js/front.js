/**
* 2013 - 2017 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2017
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*/

function change_attributs(th){
    var id_product = th.attr('data-id-product');
    var id_block = th.attr('data-id-block');
    var data = th.closest('.block_product').find('.group_attr').serializeArray();
    data.push({name: 'action', value: 'change_attributs'});
    data.push({name: 'id_product', value: id_product});
    data.push({name: 'id_block', value: id_block});
    $.ajax({
        type: 'POST',
        dataType : "json",
        url: upsell_order_controller_link,
        data: data,
        beforeSend: function(){
            th.closest('.block_product').find('.add-to-cart').attr('disabled', true);
        },
        success: function(response){
            th.closest('.block_product').find('img.img-responsive').attr('src', response.current_image);
            th.closest('.block_product').find('span.product-price').html(response.price_static);
            th.closest('.block_product').find('span.old-price.product-price').html(response.price_without_reduction);
            th.closest('.block_product').find('span.price-reduction').html(response.reduction);
            if (response.show_reduction) {
                th.closest('.block_product').find('span.price-reduction').show();
                th.closest('.block_product').find('span.old-price.product-price').show();
            } else {
                 th.closest('.block_product').find('span.price-reduction').hide();
                 th.closest('.block_product').find('span.old-price.product-price').hide();
            }
            th.closest('.block_product').find('.add-to-cart').attr('data-id-product-attribute', response.id_combination);
            if (!response.in_stock) {
                th.closest('.block_product').find('.add-to-cart').attr('disabled', true);
            } else {
                th.closest('.block_product').find('.add-to-cart').attr('disabled', false);
            }
        }
    });
}

function addToCart(idProduct, idCombination, callerElement, quantity, id_block){
    if (psv >= 1.7) {
        var bas_dir = prestashop.urls.base_url;
    } else {
        var bas_dir = baseDir;
    }
    $(callerElement).prop('disabled', 'disabled');

    if ($('.cart_block_list').hasClass('collapsed'))
        this.expand();
    //send the ajax request to the server

    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: bas_dir + '?rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'controller=cart&add=1&ajax=true&qty=' + ((quantity && quantity != null) ? quantity : '1') + '&id_product=' + idProduct + '&token=' + static_token + ( (parseInt(idCombination) && idCombination != null) ? '&ipa=' + parseInt(idCombination): '' + '&id_customization=0'),
        success: function(jsonData,textStatus,jqXHR)
        {
            if (!jsonData.hasError) {
                upsellApplyDiscount(idProduct, id_block);
                ajaxCart.updateCartInformation(jsonData, false);
                if ($('#cart_summary').length > 0) {
                     upsellUpdateCartSummary();
                }

                if (jsonData.crossSelling)
                    $('.crossseling').html(jsonData.crossSelling);

                if (idCombination)
                    $(jsonData.products).each(function(){
                        if (this.id != undefined && this.id == parseInt(idProduct) && this.idCombination == parseInt(idCombination))
                            ajaxCart.updateLayer(this);
                    });
                else
                    $(jsonData.products).each(function(){
                        if (this.id != undefined && this.id == parseInt(idProduct))
                            ajaxCart.updateLayer(this);
                    });
            } else {
                ajaxCart.updateCart(jsonData);
                $(callerElement).removeProp('disabled');
            }

        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
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

            $(callerElement).removeProp('disabled');
        }
    });
}

function upsellUpdateCartSummary() {
    $.ajax({
        type: 'POST',
        dataType : "json",
        url: upsell_order_controller_link,
        data: {
            ajax : true,
            action : "updateCartSummary",
        },
        success: function(jsonData){
            for (k in jsonData.products){
                var $el = $('#'+k);
                if($el.length == 0) {
                    $('#cart_summary tbody').first().append(jsonData.products[k]);
                }
            }
            updateCartSummary(jsonData.summary);
            $('.cart_quantity_delete' ).off('click').on('click', function(e){
                e.preventDefault();
                deleteProductFromSummary($(this).attr('id'));
            });

            $('.cart_quantity_up').off('click').on('click', function(e){
                e.preventDefault();
                upQuantity($(this).attr('id').replace('cart_quantity_up_', ''));
                $('#' + $(this).attr('id').replace('_up_', '_down_')).removeClass('disabled');
            });

            $('.cart_quantity_down').off('click').on('click', function(e){
                e.preventDefault();
                downQuantity($(this).attr('id').replace('cart_quantity_down_', ''));
            });
        }
    });
}

function upsellApplyDiscount(idProduct, id_block, resp){
    $.ajax({
        type: 'POST',
        dataType : 'json',
        url: upsell_order_controller_link,
        data: {
            ajax : true,
            action : 'apply_discount',
            id_product : idProduct,
            id_block : id_block,
        },
        success: function(jsonData){
            if (!jsonData.hasError) {
                var refreshURL = $('.js-cart').data('refresh-url');

                $.post(refreshURL).then(function (resp) {
                    $('.cart-detailed-totals').replaceWith(resp.cart_detailed_totals);
                    $('.cart-summary-items-subtotal').replaceWith(resp.cart_summary_items_subtotal);
                    $('.cart-summary-totals').replaceWith(resp.cart_summary_totals);
                    $('.cart-detailed-actions').replaceWith(resp.cart_detailed_actions);
                    $('.cart-voucher').replaceWith(resp.cart_voucher);
                    $('.cart-overview').replaceWith(resp.cart_detailed);

                    $('.js-cart-line-product-quantity').each(function (index, input) {
                        var $input = $(input);
                        $input.attr('value', $input.val());
                    });

                    $.each($('.js-cart-line-product-quantity'), function (index, spinner) {
                     $(spinner).TouchSpin({
                      verticalbuttons: true,
                      verticalupclass: 'material-icons touchspin-up',
                      verticaldownclass: 'material-icons touchspin-down',
                      buttondown_class: 'btn btn-touchspin js-touchspin js-increase-product-quantity',
                      buttonup_class: 'btn btn-touchspin js-touchspin js-decrease-product-quantity',
                      min: parseInt($(spinner).attr('min'), 10),
                      max: 1000000
                    });
                  });
                }).fail(function (resp) {
                  prestashop.emit('handleError', {eventType: 'updateShoppingCart', resp: resp});
                });
            }
        }
    });
}

$(document).ready(function(){
    /*Product add to cart ps 1.6*/
    $(document).on('click', '.upsell_block_product .add-to-cart ', function(e){
        e.preventDefault();
        var id_product = $(this).attr('data-id-product');
        var id_combination = $(this).attr('data-id-product-attribute');
        var id_block = $(this).attr('data-id-block');
        if (psv == 1.6) {
            addToCart(id_product, id_combination, this, 1, id_block);
        } else {
            prestashop.on(
                'updateCart',
                function (event) {
                    if(event.reason.linkAction == 'add-to-cart'){
                        upsellApplyDiscount(id_product, id_block, event);
                    }
                }
            );
        }
    });

    /*Product color change*/
    $(document).on('click', '.upsell_block_product .color_to_pick_list li a ', function(){
        $(this).closest('.block_product').find('ul.color_to_pick_list li').removeClass('selected');
        $(this).parent().addClass('selected');
        var id_color = $(this).attr('data-id-color');
        $(this).closest('ul').find('input.group_attr').val(id_color);
        change_attributs($(this));
        return false;
    });

    /*Product size change*/
    $(document).on('change', '.upsell_block_product .attribute_select ', function(){
        change_attributs($(this));
    });

    /*Product radio change*/
    $(document).on('change', '.upsell_block_product .attribute_radio ', function(){
        $(this).closest('.block_product').find('input.attribute_radio').attr('checked', false);;
        $(this).attr('checked', true);
        change_attributs($(this));
    });
});