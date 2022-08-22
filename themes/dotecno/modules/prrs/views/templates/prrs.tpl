<div {if $prrs_inc_pr}itemscope="" itemtype="http://schema.org/product"{/if} class="total-rating-items-block-footer card">
    <div class="clearfix row">
        <div class="col-md-6">
            <span class="h3reviews">
                {l s='Reviews' mod='prrs'}
            </span>
        </div>
        {if Configuration::get('prrs_addr') == 1 && $prrs_hide_add_review != true}
            <div class="col-md-6 addcomment">
                <a onclick="$('.open-comment-form').click();" class="btn btn-small btn-primary">{l s='Add review' mod='prrs'}</a>
            </div>
        {/if}
    </div>
    {if $prrs_ratings_counter > 0}
        <meta itemprop="name" content="{$prrs_product->name}">
        <meta itemprop="url" content="{Context::getContext()->link->getProductLink($prrs_product)}">
        <div itemtype="http://schema.org/AggregateRating" itemscope="" itemprop="aggregateRating">
            <span class="ratingStars">
            {for $foo=1 to 5}
                {if $foo<=$ratings.avg}
                    <img src="{$urls.base_url}/modules/prrs/views/img/star.png"/>
                {elseif $foo-0.5<=$ratings.avg}
                    <img src="{$urls.base_url}/modules/prrs/views/img/star-half.png"/>
                {else}
                    <img src="{$urls.base_url}/modules/prrs/views/img/star-empty.png"/>
                {/if}
            {/for}
            </span>
            <span class="ratingValue" itemprop="ratingValue">{$ratings.avg|string_format:"%.2f"}</span>
            {if $prrs_nbc}
                <span class="ratingValue" >({if $ratings_counter>0}{$prrs_ratings_counter}{else}0{/if})</span>
            {/if}
            <meta itemprop="worstRating" content="0">
            <meta itemprop="ratingCount" content="{$ratings_counter}">
            <meta itemprop="bestRating" content="5">
        </div>
    {else}
        {l s='No reviews at the moment' mod='prrs'}
    {/if}
</div>
