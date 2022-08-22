{extends file='page.tpl'}
{block name="page_content"}
    <script type="text/javascript">
        var myprestacomments_controller_url = '{$myprestacomments_controller_url nofilter}';
        var confirm_report_message = '{l s='Are you sure that you want to report this comment?' mod='myprestacomments' js=1}';
        var secure_key = '{$secure_key}';
        var myprestacomments_url_rewrite = '{$myprestacomments_url_rewriting_activated}';
        var MyprestaComment_added = '{l s='Your comment has been added!' mod='myprestacomments' js=1}';
        var MyprestaComment_added_moderation = '{l s='Your comment has been submitted and will be available once approved by a moderator.' mod='myprestacomments' js=1}';
        var MyprestaComment_title = '{l s='New comment' mod='myprestacomments' js=1}';
        var MyprestaComment_ok = '{l s='OK' mod='myprestacomments' js=1}';
        var moderation_active = {$moderation_active|intval};
    </script>
    <div id="myprestacommentsBlock">
        <div id="product_comments_block_tab" class="product_comment_feedback">
            {if $comments}
                {foreach from=$comments item=comment}
                    {if $comment.content}
                        <div class="comment clearfix">
                            <div class="comment_author">
                                <div class="comment_product">
                                    {assign var="pImages" value=myprestacomments::getImagesByID($comment.id_product, 1)}
                                    {if $pImages}
                                        {foreach from=$pImages item=image name=images}
                                            <a href="{Context::getContext()->link->getProductLink($comment.id_product)}">
                                                <img src="{Context::getContext()->link->getImageLink($comment.link_rewrite, $image, 'cart_default')}"
                                                     {if $smarty.foreach.images.first}class="current img_{$smarty.foreach.images.index}"{else}
                                                     class="img_{$smarty.foreach.images.index}"
                                                     style="display:none;"{/if}
                                                     alt="{$comment.name|escape:'htmlall':'UTF-8'}"/>
                                            </a>
                                        {/foreach}
                                    {/if}
                                    <div class="star_content clearfix">
                                        {section name="i" start=0 loop=5 step=1}
                                            {if $comment.grade le $smarty.section.i.index}
                                                <div class="star"></div>
                                            {else}
                                                <div class="star star_on"></div>
                                            {/if}
                                        {/section}
                                    </div>
                                </div>
                                <div class="comment_author_infos">
                                    <strong>{$comment.customer_name|escape:'html':'UTF-8'}</strong><br/>
                                    <em>{dateFormat date=$comment.date_add|escape:'html':'UTF-8' full=0}</em>
                                </div>
                            </div>
                            <div class="comment_details">
                                <h4 class="title_block">{$comment.title}</h4>
                                <p>{$comment.content|escape:'html':'UTF-8'|nl2br nofilter}</p>
                                <ul>
                                    {if $comment.total_advice > 0}
                                        <li>{l s='%1$d out of %2$d people found this review useful.' sprintf=[$comment.total_useful,$comment.total_advice] mod='myprestacomments'}</li>
                                    {/if}
                                    {if $logged}
                                        {if !$comment.customer_advice}
                                            <li>{l s='Was this comment useful to you?' mod='myprestacomments'}
                                                <button class="usefulness_btn" data-is-usefull="1"
                                                        data-id-product-comment="{$comment.id_product_comment}">{l s='yes' mod='myprestacomments'}</button>
                                                <button class="usefulness_btn" data-is-usefull="0"
                                                        data-id-product-comment="{$comment.id_product_comment}">{l s='no' mod='myprestacomments'}</button>
                                            </li>
                                        {/if}
                                        {if !$comment.customer_report}
                                            <li><span class="report_btn"
                                                      data-id-product-comment="{$comment.id_product_comment}">{l s='Report abuse' mod='myprestacomments'}</span>
                                            </li>
                                        {/if}
                                    {/if}
                                </ul>
                                {hook::exec('displayMyprestaComment', $comment) nofilter}
                            </div>
                        </div>
                    {/if}
                {/foreach}
            {else}
                <div class="alert alert-info">
                    {l s='No comments currently available' mod='myprestacomments'}
                </div>
            {/if}
        </div>
    </div>
    {block name='pagination'}
        {include file='_partials/pagination.tpl' pagination=$pagination}
    {/block}
{/block}