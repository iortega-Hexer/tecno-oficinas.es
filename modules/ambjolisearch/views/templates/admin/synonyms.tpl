{*
* @module       Advanced search (AmbJoliSearch)
* @file         synonyms.tpl
* @subject      template pour paramétrage du module sur le 'back office'
* @copyright    Copyright (c) 2013-2019 Ambris Informatique SARL (http://www.ambris.com/)
* @author       Richard Stefan (@RicoStefan)
* @license      Commercial license
* Support by mail: support@ambris.com
*}

{if $compat}
<form action="{$request_uri|escape:'quotes':'UTF-8'}" method="post">
<div style="clear:both;">&nbsp;</div>
    <fieldset>
        <legend><img src="{$path|escape:'urlpathinfo':'UTF-8'}logo.gif" alt="" title="" />{l s='Reset synonyms' mod='ambjolisearch'}</legend>
        <div style="clear:both;">
            <p class="clear">{l s='If searches become slow, you may try to reset synonyms to improve performances' mod='ambjolisearch'}</p>
        </div>
        <label>{$nbSynonyms|escape:'quotes':'UTF-8'} {l s=' synonyms stored' mod='ambjolisearch'}</label>
        <div class="margin-form">
            <input type="submit" name="submitResetSynonyms" value="{l s='RESET' mod='ambjolisearch'}" class="button" />
        </div>
    </fieldset>
</form>
{else}
<div class="container-fluid">
<form action="{$request_uri|escape:'quotes':'UTF-8'}" method="post" class="form-horizontal">

<div class="row">
    <div class="panel">
        <div class="panel-heading">{l s='Reset synonyms' mod='ambjolisearch'}</div>
        <div class="form-group">
            <label class="control-label col-lg-3">
            <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='If searches become slow, you may try to reset synonyms to improve performances' mod='ambjolisearch'}" data-html="true">
            {$nbSynonyms|escape:'quotes':'UTF-8'} {l s=' synonyms stored' mod='ambjolisearch'}
            </span>
            </label>
            <div class="col-lg-9">
                <input type="submit" name="submitResetSynonyms" value="{l s='RESET' mod='ambjolisearch'}" class="button btn btn-danger" />
            </div>
        </div>
    </div>
</div>
</form>
</div>

{/if}

<div class="container-fluid">
<form class="form-horizontal">

<div class="row">
    <div class="panel">
        <div class="panel-heading">{l s='Reset indexation' mod='ambjolisearch'}</div>
        <div class="form-group">
            <label class="control-label col-lg-3">
            <span>
                    {l s='Products indexed : ' mod='ambjolisearch'}<span class="indexed-products">{$indexed} / {$total}</span>
            </span>
            </label>
            <div class="col-lg-9">
                <button class="rebuild-index button btn btn-primary" data-url="{$rebuild_index_url}" data-done="{l s='Indexation complete !' mod='ambjolisearch'}" data-processing="{l s='Indexing products...' mod='ambjolisearch'}" data-starting="{l s='Starting indexation of products...' mod='ambjolisearch'}">
                    <span class="spinner"><i class="icon-refresh icon-spin icon-fw"></i></span> {l s='Rebuild index' mod='ambjolisearch'}
                </button>
                <br /><br />
                <div class="status" style="display:none;">
                    <ul>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
</div>
