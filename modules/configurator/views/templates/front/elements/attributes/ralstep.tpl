<div id="step_option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
     class="option_input option_group col-md-12 form-group" style="display:none;">
    
    <div class="input-group">
        <span class="input-group-addon">{$option->option.name|escape:'html':'UTF-8'} : </span>
    
        <input type="text"
            class="form-control grey" 
            data-step='{$step->id|escape:'htmlall':'UTF-8'}'
            data-option='{$option->id|escape:'htmlall':'UTF-8'}'
            data-force="{$option->force_value|escape:'htmlall':'UTF-8'}"
            id="option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
            data-type="ral_input"
            value=""
            readonly="readonly"

            data-toggle="modal"
            data-target="#configuratorRalModal-{$step->id}-{$option->id}"
        >
            <span class="input-group-addon btn-configurator-ral"
                    data-toggle="modal"
                    data-target="#configuratorRalModal-{$step->id}-{$option->id}">
                <i class="material-icons ">color_lens </i>
                {l s='Chose your RAL' mod='configurator'}
            </span>
    </div>
    

</div>

{assign var="ral_attributes" value=$option->getRalAttributes()}
{if $ral_attributes neq false}
    {include file='./ral_modal.tpl' ral_option=$ral_attributes option=$option step=$step lang_id=$lang_id}
{/if}
