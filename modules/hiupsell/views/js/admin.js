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

$.fn.ret_parent = function(count) {
    if (count == 1) {
        return $(this).parent();
    } else if (count == 2) {
        return $(this).parent().parent();
    } else if (count == 3){
        return $(this).parent().parent().parent();
    }
};

function change_products_type(content_type){
    if (psv >= 1.6) {
        var closest = '.form-group';
    } else {
        var closest = '.margin-form';
    }
    if (content_type == 'accessories' || content_type == 'cross_sells' || content_type == 'selected_product') {
        $('#upsell_block_products').closest(closest).hide();
    } else {
        $('#upsell_block_products').closest(closest).show();
    }
};

function change_offer_discount_type(offer_type){
    if (psv >= 1.6) {
        var closest = '.form-group';
    } else {
        var closest = '.margin-form';
    }
    if (offer_type == 0) {
        if (language_count <= 1) {
            $('.block-disable-name').closest(closest).hide();
        } else {
            $('.block-disable-name').closest(closest).ret_parent(2).hide();
        }
        $('.block-disable').closest(closest).hide();
    } else {
        if (language_count <= 1) {
            $('.block-disable-name').closest(closest).show();
        } else {
            $('.block-disable-name').closest(closest).ret_parent(2).show();
        }
        $('.block-disable').closest(closest).show();
    }
};

function change_apply_discount_type(applay_type){
    if (psv >= 1.6) {
        var closest = '.form-group';
    } else {
        var closest = '.margin-form';
    }
    if (applay_type == 'percent') {
         $('.percent-disable').closest(closest).show();
         $('.amount-disable').closest(closest).hide();
    } else if (applay_type == 'amount') {
        $('.percent-disable').closest(closest).hide();
        $('.amount-disable').closest(closest).show();
    }
    
};

/*Applay discount change*/ 


function update_upsell_helper_list(action, type, id, status){
    $.ajax({
        type: 'POST',
        dataType: "JSON",
        url: upsell_module_controller_dir,
        data: {
            ajax : true,
            action : action,
            secure_key : upsell_secure_key,
            id : id,
            status : status,
            psv: psv,
        },
        success: function(response){
            if (type == 'upsell_block') {
                $("#form-upsellblock").replaceWith(response.content);
            }
            
        }
    });
};
/*Show Add*/
function upsell_show_form_add_action(action){
    $.ajax({
        type: 'POST',
        dataType: "JSON",
        url: upsell_module_controller_dir,
        data: {
            ajax : true,
            action : action,
            secure_key : upsell_secure_key,
            psv: psv,
        },
        success: function(response){
            $("#block_modal_form .content").html(response.content);
            $('#block_modal_form').modal('show');
            change_products_type($('#block_products_type').val());
            var offer_type = $('[name="block_offer_discount"]:checked').val();
            change_offer_discount_type(offer_type);
            if (offer_type == 1) {
                change_apply_discount_type($('[name="apply_discount"]:checked').val());
            }
            
        }
    });
};
/*Show edit*/
function upsell_show_form_edit_action(action, id){
    $.ajax({
        type: 'POST',
        dataType: "JSON",
        url: upsell_module_controller_dir,
        data: {
            ajax : true,
            action : action,
            secure_key : upsell_secure_key,
            psv: psv,
            id: id,
        },
        success: function(response){
            $("#block_modal_form .content").html(response.content);
            $('#block_modal_form').modal('show');
            change_products_type($('#block_products_type').val());
            var offer_type = $('[name="block_offer_discount"]:checked').val();
            change_offer_discount_type(offer_type);
            if (offer_type == 1) {
                change_apply_discount_type($('[name="apply_discount"]:checked').val());
            }
        }
    });
}
/*Add and edit*/
function upsell_form_action(action, form, type, helper_action){
    var formdata = new FormData($(form)[0])
    formdata.append("action", action);
    formdata.append("ajax", true);
    formdata.append("secure_key", upsell_secure_key);
    formdata.append("psv", psv);
    $.ajax({
        type: 'POST',
        dataType: "JSON",
        url: upsell_module_controller_dir,
        data: formdata,
        contentType: false,
        processData: false,
        success: function(response){
            if (response.error != '') {
                showErrorMessage(response.error);
            } else {
                showSuccessMessage('Successful Save');
                $('#block_modal_form').modal('hide');
                update_upsell_helper_list(helper_action, type);
            }
        }
    });
}
/*Delete*/
function upsell_form_delete_action(action, id, type, helper_action){
    $.ajax({
        type: 'POST',
        dataType: "JSON",
        url: upsell_module_controller_dir,
        data: {
            ajax : true,
            action : action,
            secure_key : upsell_secure_key,
            psv: psv,
            id: id,
        },
        success: function(response){
            showSuccessMessage('Successful delete');
            update_upsell_helper_list(helper_action, type);
        }
    });
}

$(document).ready(function(){
    $('.fake_desc').closest('form').hide();

    if (psv >= 1.6) {
        var closest = '.form-group';
    } else {
        var closest = '.margin-form';
    }
    var content_type = $('#upsell_content_type').val();
    if (content_type == 'accessories' || content_type == 'cross_sells' || content_type == 'selected_product') {
        $('.product-content').closest(closest).hide();
    } else {
        $('.product-content').closest(closest).show();
    }

    $('#upsell_content_type').change(function(){
        if ($(this).val() == 'accessories' || $(this).val() == 'cross_sells' || $(this).val() == 'selected_product') {
            $('.product-content').closest(closest).hide();
        } else {
            $('.product-content').closest(closest).show();
        }
    });
    /*Seaarch product*/
    $('#reductionProductFilter').autocomplete(upsell_module_controller_dir+"&ajax=1", 
    {
        minChars: 2,
        max: 50,
        width: 500,
        formatItem: function (data) {
            return data[0]+ '. '+data[2] + '-' + data[1];
        },
        scroll: false,
        multiple: false,
        extraParams: {
            action : 'product_search',
            id_lang : id_lang,
            secure_key : upsell_secure_key,
        }
    });

    /*Product add*/
    $(document).on('click', '#add-product-item', function(){
        var product_id = $("#reductionProductFilter").val();
        $.ajax({
            type: 'POST',
            dataType : "json",
            url: upsell_module_controller_dir,
            data: {
                ajax : true,
                action : "add_product",
                secure_key : upsell_secure_key,
                product_id: product_id,
                psv: psv
            },
            success: function(response){
                if(response.error){
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage('Successfully add ');
                    $(".products-list").replaceWith(response.content);
                    $("#reductionProductFilter").val("");
                }
                $('.errors').hide();
            }
        });
    })

    /*Product delete*/
    $(document).on('click', '.upsellproduct .delete-product', function(){
        var product_id = $(this).attr("data-delete");
        $.ajax({
            type: "POST",
            dataType : "json",
            url: upsell_module_controller_dir,
            data: {
                ajax : true,
                action : "delete_product",
                product_id : product_id,
                secure_key : upsell_secure_key,
                psv: psv,
            },
            success: function(response){
                $(".products-list").replaceWith(response.content);
                showSuccessMessage('Successfully delete');
                $('.errors').hide();
            }
        }); 
        return false;
    });
    /*End*/

/**
**Up sell blocks*/

    // Cancel
    $(document).on('click', '[name=submit_cancel_upsellblock]', function(){
        $('#block_modal_form').modal('hide');
        return false;
    });

    /*Status*/
    $(document).on('click', '.upsellblock .status', function(e){
        e.preventDefault();
        var id = $(this).attr("data-id");
        var status = $(this).attr("data-status");
        update_upsell_helper_list('update_block_status', 'upsell_block',  id, status);
    });

    /*Products type change*/
    $(document).on('change', '#block_products_type', function(e){
        change_products_type($(this).val());
    });

    $(document).on('change', '[name="block_offer_discount"]', function(e){
        change_offer_discount_type($(this).val());
        if ($(this).val() == 1) {
             change_apply_discount_type($('[name="apply_discount"]:checked').val());
        }
       
    });
    /*Offer discount  change*/
    $(document).on('change', '[name="block_offer_discount"]', function(e){
        change_offer_discount_type($(this).val());
        if ($(this).val() == 1) {
             change_apply_discount_type($('[name="apply_discount"]:checked').val());
        }
       
    });
    /*Applay discount change*/ 
    $(document).on('change', '[name="apply_discount"]', function(e){
        change_apply_discount_type($(this).val());
    });

    /*Show add*/
    $(document).on('click', '#desc-upsellblock-new', function(e){
        e.preventDefault();
        upsell_show_form_add_action('show_add_form');
    });

    /*Add*/
    $(document).on('click', '[name=submit_upsellblock_add]', function(e){
        e.preventDefault();
        var form = $('[name=submit_upsellblock_add]').closest('form');
        upsell_form_action('add_block', form, 'upsell_block', 'update_block_helperlist');
    });

    /*Show edit*/
    $(document).on('click', '.upsellblock .edit', function(e){
        e.preventDefault();
        var id_block = $(this).attr("href").match(/id_block=([0-9]+)/)[1];
        upsell_show_form_edit_action('show_update_form', id_block);
    });

    /*Edit*/
     $(document).on('click', '[name=submit_upsellblock_update]', function(e){
        e.preventDefault();
        var form = $('[name=submit_upsellblock_update]').closest('form');
        upsell_form_action('update_block', form, 'upsell_block', 'update_block_helperlist');
    });

    /*Delete*/
     $(document).on('click', '.upsellblock .delete', function(e){
        e.preventDefault();
        var id_block = $(this).attr("href").match(/id_block=([0-9]+)/)[1];
        upsell_form_delete_action('delete_block', id_block, 'upsell_block', 'update_block_helperlist')
    });

    /*Block Product add*/
    $(document).on('click', '#add-upsell-block-product', function(){
        var id_product = $("#upsell_block_product_search").val();
        var product_ids = $("#inputBlockProducts").val();
        $.ajax({
            type: 'POST',
            dataType : "json",
            url: upsell_module_controller_dir,
            data: {
                ajax : true,
                action : "add_block_product",
                secure_key : upsell_secure_key,
                id_product: id_product,
                product_ids: product_ids,
                psv: psv
            },
            success: function(response){
                if(response.error){
                    showErrorMessage(response.error);
                } else {
                    
                    $('#upsellproducts').append("<div class='form-control-static'><button type='button' class='btn btn-default deleteblockproduct' data-id-product='"+response.id_product+"'><i class='icon-remove text-danger'></i></button>"+response.product_name+"</div>");
                    $("#inputBlockProducts").val(response.ids);
                    $("#upsell_block_product_search").val("");
                    showSuccessMessage('Successfully add ');
                }
            }
        });
    });

    // Block product delete
    $(document).on('click', '.deleteblockproduct', function(){
        var  th = $(this);
        var id_product = $(this).attr('data-id-product');
        var product_ids = $("#inputBlockProducts").val();
        $.ajax({
            type: 'POST',
            dataType : "json",
            url: upsell_module_controller_dir,
            data: {
                ajax : true,
                action : "delete_block_product",
                secure_key : upsell_secure_key,
                id_product: id_product,
                product_ids: product_ids,
                psv: psv
            },
            success: function(response){
                th.parent().remove();
                $("#inputBlockProducts").val(response.ids);
               
                showSuccessMessage('Successfully delete ');
            }
        });
    });

    /*Extra product Add*/
    $(document).on('click', '#add-upsell-product-extra', function(){
        var id_parent = $("#id_parent").val();
        var id_children = $("#upsell_product_extra_search").val();
        $.ajax({
            type: 'POST',
            dataType : "json",
            url: upsell_module_controller_dir,
            data: {
                ajax : true,
                action : "add_product_extra",
                secure_key : upsell_secure_key,
                id_parent: id_parent,
                id_children: id_children,
                psv: psv
            },
            success: function(response){
                if(response.error){
                    showErrorMessage(response.error);
                } else {
                    showSuccessMessage('Successfully add ');
                    $("#upsellproductextra").html(response.content);
                    $("#upsell_product_extra_search").val("");
                }
                $('.errors').hide();
            }
        });
    })

    /*Extra Product delete*/
    $(document).on('click', '#upsellproductextra .delete-product', function(){
        var id_parent = $("#id_parent").val();
        var id_children = $(this).attr("data-delete");
        $.ajax({
            type: "POST",
            dataType : "json",
            url: upsell_module_controller_dir,
            data: {
                ajax : true,
                action : "delete_extra_product",
                id_parent : id_parent,
                id_children : id_children,
                secure_key : upsell_secure_key,
                psv: psv,
            },
            success: function(response){
                $("#upsellproductextra").html(response.content);
                showSuccessMessage('Successfully delete');
                $('.errors').hide();
            }
        }); 
        return false;
    });
    /*End*/

});
