{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<script type="text/javascript">
    var alertMessage = '{$accessory_alert_message|escape:'quotes':'UTF-8'}';
    var buyTogetherOption = {$buy_main_accessory_together|escape:'quotes':'UTF-8'};
    var isEnablingOptionBuyToGether = {$is_enabling_option_buy_to_gether|intval};
    var displayStyleOption = {$display_style|escape:'htmlall':'UTF-8'};
    var utilizeBlockCartAjax = {$utilize_block_cart_ajax|escape:'htmlall':'UTF-8'};
    var msgOutOfStock = '{$msg_accessory_is_out_of_stock|escape:'quotes':'UTF-8'}';
    var msgAvailableLater = '{$msg_available_later|escape:'quotes':'UTF-8'}';
    var messageOutOfStock = '{$msg_out_of_stock|escape:'quotes':'UTF-8'}';
    var msgEmptyForm = '{$msg_empty_form|escape:'quotes':'UTF-8'}';
    var isShowIconOutOfStock = {$is_show_icon_out_of_stock|intval};
    var orderUrl = '{$order_url|escape:'quotes':'UTF-8'}';
    ajaxRenderAccessoriesUrl = '{$ajaxRenderAccessories|escape:'quotes':'UTF-8'}';
    $(document).ready(function () {
        if (isEnablingOptionBuyToGether) {
            new HsmaRenderAccessories(
                    ajaxRenderAccessoriesUrl
                    ).init();
        }
        if (utilizeBlockCartAjax)
        {
            $('.hs_multi_accessories_add_to_cart').on('click', function (e) {
                e.preventDefault();
                var qty = 1;
                var idProduct = parseInt($(this).data('idproduct'));
                var idProductAttribute = parseInt($(this).data('id-product-attribute'));
                var parentElement = $(this).parents('td');
                if (idProduct <= 0)
                    return;
                if ($(parentElement).find('input').hasClass('custom_quantity'))
                    qty = $(parentElement).find('.custom_quantity').val();
                else if ($('#quantity_wanted').length > 0)
                    qty = parseInt($('#quantity_wanted').val());
                else
                    qty = 1;

                if ($(parentElement).find('select').hasClass('product-combination'))
                    idProductAttribute = $(parentElement).find('.product-combination :selected').val();
              
                ajaxCart.add(idProduct, idProductAttribute, false, this, qty, null);
            });
        }
        else
        {
            $(document).on('click', '#add_to_cart button, #add_to_cart input', function (e) {
                var qty = parseInt($('#quantity_wanted').val());
                e.preventDefault();
                addToCart($('#product_page_product_id').val(), $('#idCombination').val(), qty, true);
            });

            $(document).on('click', '.hs_multi_accessories_add_to_cart', function (e) {
                e.preventDefault();
                var qty = 1;
                var idProduct = parseInt($(this).data('idproduct'));
                var idProductAttribute = parseInt($(this).data('id-product-attribute'));
                var parentElemnet = $(this).parent();
                if (idProduct <= 0)
                    return;
                if ($(parentElemnet).find('input').hasClass('custom_quantity'))
                    qty = $(parentElemnet).find('.custom_quantity').val();
                else if ($('#quantity_wanted').length > 0)
                    qty = parseInt($('#quantity_wanted').val());
                else
                    qty = 1;

                if ($(parentElemnet).find('select').hasClass('product-combination'))
                    idProductAttribute = $(parentElemnet).find('.product-combination').val();

                addToCart(idProduct, idProductAttribute, qty, false);
            });
        }
    });
</script>