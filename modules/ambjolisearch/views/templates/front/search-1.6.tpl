{*
* 20013-2019 Ambris

*   @author    Ambris Informatique
*   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
*   @license   Commercial license
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l mod='ambjolisearch' s='Search'}{/capture}

<h1
{if isset($instant_search) && $instant_search}id="instant_search_results"{/if}
class="page-heading {if !isset($instant_search) || (isset($instant_search) && !$instant_search)} product-listing{/if}">
    {l s='Search' mod='ambjolisearch'}
    {if $nbProducts > 0}
        <span class="lighter">
            "{if isset($real_query) && $real_query}{$real_query|escape:'html':'UTF-8'}{elseif $search_tag}{$search_tag|escape:'html':'UTF-8'}{elseif $ref}{$ref|escape:'html':'UTF-8'}{/if}"
        </span>
        {if isset($amb_search_context) && !empty($amb_search_context)}
            {l s='in' mod='ambjolisearch'}
            <span class="lighter">"{$amb_search_context|escape:'html':'UTF-8'}"</span>
        {/if}
    {/if}
    {if isset($instant_search) && $instant_search}
        <a href="#" class="close">
            {l s='Return to the previous page' mod='ambjolisearch' }
        </a>
    {elseif !isset($no_suitable_words) || !$no_suitable_words}
        <span class="heading-counter">
            {if $nbProducts == 1}{l s='%d result has been found.' mod='ambjolisearch'  sprintf=$nbProducts|intval}{else}{l s='%d results have been found.' mod='ambjolisearch'  sprintf=$nbProducts|intval}{/if}
        </span>
    {/if}

</h1>

{include file="$tpl_dir./errors.tpl"}
{if isset($no_suitable_words) && $no_suitable_words}

      <p class="alert alert-warning">
       {l s='Searches only work with at least one word longer than %d characters. Please try again.' mod='ambjolisearch'  sprintf=$min_length|intval}
    </p>


{elseif !$nbProducts}
    <p class="alert alert-warning">
        {if isset($real_query) && $real_query}
            {l s='No results were found for your search' mod='ambjolisearch' }&nbsp;"{if isset($real_query)}{$real_query|escape:'html':'UTF-8'}{/if}"
        {elseif isset($search_tag) && $search_tag}
            {l s='No results were found for your search' mod='ambjolisearch'}&nbsp;"{$search_tag|escape:'html':'UTF-8'}"
        {else}
            {l s='Please enter a search keyword' mod='ambjolisearch' }
        {/if}
    </p>
{else}
    {if isset($instant_search) && $instant_search}
        <p class="alert alert-info">
            {if $nbProducts == 1}{l s='%d result has been found.' mod='ambjolisearch' sprintf=$nbProducts|intval}{else}{l s='%d results have been found.' mod='ambjolisearch' sprintf=$nbProducts|intval}{/if}
        </p>
    {/if}

    {if isset($subcategories) && count($subcategories)>0}
    <div id="subcategories">
            <p class="subcategory-heading">{l s='Categories' mod='ambjolisearch'}</p>
            <ul class="clearfix">
            {foreach from=$subcategories item=subcategory}
                <li>
                    <div class="subcategory-image">
                        <a href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                        {if $subcategory.id_image}
                            <img class="replace-2x" src="{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image, 'medium_default')|escape:'html':'UTF-8'}" alt="" width="{$mediumSize.width|escape:'html':'UTF-8'}" height="{$mediumSize.height|escape:'html':'UTF-8'}" />
                        {else}
                            <img class="replace-2x" src="{$img_cat_dir|escape:'htmlall':'UTF-8'}default-medium_default.jpg" alt="" width="{$mediumSize.width|escape:'html':'UTF-8'}" height="{$mediumSize.height|escape:'html':'UTF-8'}" />
                        {/if}
                    </a>
                    </div>
                    <h5><a class="subcategory-name" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}">{$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'|truncate:350}</a></h5>
                    {if $subcategory.description && $show_cat_desc==1}
                        <div class="cat_desc">{$subcategory.description|escape:'htmlall':'UTF-8'}</div>
                    {/if}
                </li>
            {/foreach}
            </ul>
    </div>
    {/if}



    <div class="content_sortPagiBar">
        <div class="sortPagiBar clearfix {if isset($instant_search) && $instant_search} instant_search{/if}">
            {include file="$tpl_dir./product-sort.tpl"}
            {if !isset($instant_search) || (isset($instant_search) && !$instant_search)}
                {include file="$tpl_dir./nbr-product-page.tpl"}
            {/if}
        </div>
        <div class="top-pagination-content clearfix">
            {include file="$tpl_dir./product-compare.tpl"}
            {if !isset($instant_search) || (isset($instant_search) && !$instant_search)}
                {include file="$tpl_dir./pagination.tpl"}
            {/if}
        </div>
    </div>
    {include file="$tpl_dir./product-list.tpl" products=$search_products}
    <div class="content_sortPagiBar">
        <div class="bottom-pagination-content clearfix">
            {include file="$tpl_dir./product-compare.tpl"}
            {if !isset($instant_search) || (isset($instant_search) && !$instant_search)}
                {include file="$tpl_dir./pagination.tpl" paginationId='bottom'}
            {/if}
        </div>
    </div>
{/if}
