<div>
    <br>
    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalConfigurator_{{$customization['id_customization']}}">
        {l s='Edit' mod='configurator'}
    </button>
    <div class="modal fade" id="modalConfigurator_{{$customization['id_customization']}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="width: 80vw;">
            <div class="modal-content">
                <div class="modal-body">
                    <iframe style="width: 100%; height: 70vh;" src="" data-src="{{$iframe_link}}"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer la fenêtre</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#modalConfigurator_{{$customization['id_customization']}}').on('hide.bs.modal', function (e) {
            const id_product = 0, id_product_attribute = 0, id_customization = 0, qty = 0;
            $.ajax({
                type:"POST",
                url: "{{Context::getContext()->link->getAdminLink('AdminCarts')|addslashes}}",
                async: true,
                dataType: "json",
                data : {
                    ajax: "1",
                    token: "{{Tools::getAdminTokenLite('AdminCarts')}}",
                    tab: "AdminCarts",
                    action: "updateQty",
                    id_product: id_product,
                    id_product_attribute: id_product_attribute,
                    id_customization: id_customization,
                    qty: qty,
                    id_customer: id_customer,
                    id_cart: id_cart,
                }, success : function(res) {
                    displaySummary(res);
                    showSuccessMessage("{l s='Your configuration has been updated.' mod='configurator'}");
                }
            });
            $('#products_err').addClass('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });

        $('#modalConfigurator_{{$customization['id_customization']}}').on('show.bs.modal', function (e) {
            $('#modalConfigurator_{{$customization['id_customization']}} iframe').attr('src', $('#modalConfigurator_{{$customization['id_customization']}} iframe').attr('data-src'));
        });
    });
</script>