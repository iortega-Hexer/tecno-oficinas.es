<div class="modal fade" id="configuratorRalModal-{$step->id}-{$option->id}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title">{l s='Select your RAL for' mod='configurator'} {$option->option.name|escape:'html':'UTF-8'}</h5>
            </div>
            <div class="modal-body">        
                <ul class="configurator-ral-list">
                    {foreach $ral_attributes as $attribute}
                        <li class="configurator-ral-list-option" data-id="{$attribute.id_attribute}" data-type="ral_input" data-ref="{$attribute.ref_ral}">
                            <div class="configurator-ral-attribute" style="background: {$attribute.color}" data-type="ral_input"></div>
                            {$attribute.ref_ral}
                        </li>
                    {/foreach}
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-sm btn-primary pull-right" data-dismiss="modal">
                                {l s='Return to product' mod='configurator'}
                        </button>
                    </div>
                </div>
            </div>     
        </div>
    </div>
</div>
