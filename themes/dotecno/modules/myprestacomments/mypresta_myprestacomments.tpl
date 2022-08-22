{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
*  MODIFIED BY MYPRESTA.EU FOR PRESTASHOP 1.7 PURPOSES !
*
*}

<script type="text/javascript">
    var myprestacomments_controller_url = '{$myprestacomments_controller_url nofilter}';
    var confirm_report_message = '{l s='Are you sure that you want to report this comment?' mod='myprestacomments' js=1}';
    var secure_key = '{$secure_key}';
    var myprestacomments_url_rewrite = '{$myprestacomments_url_rewriting_activated}';
    var MyprestaComment_added = '{l s='Your comment has been added!' mod='myprestacomments' js=1}';
    var MyprestaComment_added_moderation = '{l s='Your comment has been submitted and will be available once approved by a moderator.' mod='myprestacomments' js=1}';
    var MyprestaComment_title = '{l s='New comment' mod='myprestacomments' js=1}';
    var MyprestaComment_ok = '{l s='OK' mod='myprestacomments' js=1}';
    var moderation_active = {$moderation_active};
</script>

<div id="myprestacommentsBlock">
    {hook h='displayValoration'}
    <div class="tabs">
        <div class="clearfix pull-right">
            {if ($too_early == false AND ($logged OR $allow_guests))}
                <a class="open-comment-form btn btn-primary" href="#new_comment_form">{l s='Write your review' d='Shop.Theme.Catalog'}</a>
            {/if}
        </div>
        <div id="new_comment_form_ok" class="alert alert-success" style="display:none;padding:15px 25px"></div>
        <div id="product_comments_block_tab">
            {if $comments}
                {foreach from=$comments item=comment}
                    {if $comment.content}
                        <div class="comment clearfix" itemprop="review" itemscope itemtype="https://schema.org/Review">
                            <div class="comment_author" >
                                <div class="star_content clearfix" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                                    {section name="i" start=0 loop=5 step=1}
                                        {if $comment.grade le $smarty.section.i.index}
                                            <div class="star"></div>
                                        {else}
                                            <div class="star star_on"></div>
                                        {/if}
                                    {/section}
                                    <meta itemprop="worstRating" content = "0" />
                                    <meta itemprop="ratingValue" content = "{$comment.grade}" />
                                    <meta itemprop="bestRating" content = "5" />
                                </div>
                                {if $PRODUCT_COMMENTS_DGRADE}
                                <div class="criterions_grade">
                                    {foreach MyprestaCommentCriterion::getByProductAndByComment($comment.id_product_comment) AS $criterion}
                                        {$criterion.name}<br/>
                                        {section name="i" start=0 loop=5 step=1}
                                            {if $criterion.grade le $smarty.section.i.index}
                                                <div class="criterion star"></div>
                                            {else}
                                                <div class="criterion star star_on"></div>
                                            {/if}
                                        {/section}
                                        <br/>
                                    {/foreach}
                                </div>
                                {/if}
                                <div class="comment_author_infos" >
                                    <strong itemprop="author">{$comment.customer_name|escape:'html':'UTF-8'}</strong><br/>
                                    <em>{dateFormat date=$comment.date_add|escape:'html':'UTF-8' full=0}</em>
                                    <meta itemprop="datePublished" content="{dateFormat date=$comment.date_add|escape:'html':'UTF-8' full=0}" />
                                </div>
                            </div>
                            <div class="comment_details">
                                <h4 class="title_block" itemprop="name">{$comment.title}</h4>
                                <p itemprop="reviewBody">{$comment.content|escape:'html':'UTF-8'|nl2br nofilter}</p>
                                <ul>
                                    {if $comment.total_advice > 0}
                                        <li>{l s='%1$d out of %2$d people found this review useful.' sprintf=[$comment.total_useful,$comment.total_advice] mod='myprestacomments'}</li>
                                    {/if}
                                    {if $logged}
                                        {if !$comment.customer_advice}
                                            <li>{l s='Was this comment useful to you?' mod='myprestacomments'}
                                                <button class="usefulness_btn" data-is-usefull="1" data-id-product-comment="{$comment.id_product_comment}">{l s='yes' mod='myprestacomments'}</button>
                                                <button class="usefulness_btn" data-is-usefull="0" data-id-product-comment="{$comment.id_product_comment}">{l s='no' mod='myprestacomments'}</button>
                                            </li>
                                        {/if}
                                        {if !$comment.customer_report}
                                            <li><span class="report_btn" data-id-product-comment="{$comment.id_product_comment}">{l s='Report abuse' mod='myprestacomments'}</span></li>
                                        {/if}
                                    {/if}
                                </ul>
                                {hook::exec('displayMyprestaComment', $comment) nofilter}
                            </div>
                        </div>
                    {/if}
                {/foreach}
            {else}
                {if (!$too_early AND ($logged OR $allow_guests))}
                    <p class="align_center alert alert-info">
                        <a id="new_comment_tab_btn" class="open-comment-form" href="#new_comment_form">{l s='Be the first to write your review' mod='myprestacomments'} !</a>
                    </p>
                {else}
                    <p class="align_center">{l s='No customer reviews for the moment.' mod='myprestacomments'}</p>
                {/if}
            {/if}
        </div>
    </div>

    {if isset($myprestacomments_product) && $myprestacomments_product}
        <!-- Fancybox -->
        <div style="display:none">
            <div id="new_comment_form">
                <form id="id_new_comment_form" action="#">
                    <div class="title">{l s='Write your review' mod='myprestacomments'}</div>
                    {if isset($myprestacomments_product) && $myprestacomments_product}
                        <div class="product clearfix">
                            <div class="product_desc">
                                <p class="product_name"><strong>{if isset($myprestacomments_product->name)}{$myprestacomments_product->name}{elseif isset($myprestacomments_product.name)}{$myprestacomments_product.name}{/if}</strong></p>
                                {if isset($myprestacomments_product->description_short)}{$myprestacomments_product->description_short nofilter}{elseif isset($myprestacomments_product.description_short)}{$myprestacomments_product.description_short nofilter}{/if}
                            </div>
                        </div>
                    {/if}
                    <div class="new_comment_form_content">
                        <div class="h4">{l s='Write your review' mod='myprestacomments'}</div>
                        <div id="new_comment_form_error" class="error" style="display:none;padding:15px 25px">
                            <ul></ul>
                        </div>
                        {if $criterions|@count > 0}
                            <ul id="criterions_list">
                                {foreach from=$criterions item='criterion'}
                                    <li>
                                        <label>{l s='Quality' d='Shop.Theme.Catalog'}{* $criterion.name|escape:'html':'UTF-8' *}</label>
                                        <div class="star_content">
                                            <input class="star" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="1"/>
                                            <input class="star" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="2"/>
                                            <input class="star" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="3"/>
                                            <input class="star" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="4"/>
                                            <input class="star" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="5" checked="checked"/>
                                        </div>
                                        <div class="clearfix"></div>
                                    </li>
                                {/foreach}
                            </ul>
                        {/if}
                        <label for="comment_title">{l s='Title for your review' mod='myprestacomments'}<sup class="required">*</sup></label>
                        <input id="comment_title" name="title" type="text" value=""/>

                        <label for="content">{l s='Your review' mod='myprestacomments'}<sup class="required">*</sup></label>
                        <textarea id="content" name="content"></textarea>

                        {if $allow_guests == true && !$logged}
                            <label>{l s='Your name' mod='myprestacomments'}<sup class="required">*</sup></label>
                            <input id="commentCustomerName" name="customer_name" type="text" value=""/>
                            <label>{l s='Your email' mod='myprestacomments'}<sup class="required">*</sup></label>
                            <input id="commentCustomerEmail" name="customer_email" type="text" value=""/>
                        {/if}

                        <div id="new_comment_form_footer">
                            <input id="id_product_comment_send" name="id_product" type="hidden" value='{$id_product_comment_form}'/>
                            <p class="row required"><sup>*</sup> {l s='Required fields' mod='myprestacomments'}</p>
                            <p class="fr">
                                {if $PRODUCT_COMMENTS_GDPR == 1}
                                {literal}
                                    <input onchange="if($(this).is(':checked')){$('#submitNewMessage').removeClass('gdpr_disabled'); $('#submitNewMessage').removeAttr('disabled'); rebindClickButton();}else{$('#submitNewMessage').addClass('gdpr_disabled'); $('#submitNewMessage').off('click'); $('#submitNewMessage').attr('disabled', 1); }" id="gdpr_checkbox" type="checkbox" >
                                {/literal}
                                    {l s='I accept ' mod='myprestacomments'} <a target="_blank" href="{$link->getCmsLink($PRODUCT_COMMENTS_GDPRCMS)}">{l s='privacy policy' mod='myprestacomments'}</a> {l s='rules' mod='myprestacomments'}
                                {/if}

                                <button {if $PRODUCT_COMMENTS_GDPR == 1}disabled{/if} class="btn btn-primary {if $PRODUCT_COMMENTS_GDPR == 1}gdpr_disabled{/if}" id="submitNewMessage" name="submitMessage" type="submit">{l s='Send' mod='myprestacomments'}</button>&nbsp;
                                {l s='or' mod='myprestacomments'}&nbsp;<a href="#" onclick="$.fancybox.close();">{l s='Cancel' mod='myprestacomments'}</a>
                            </p>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </form><!-- /end new_comment_form_content -->
            </div>
        </div>
        <!-- End fancybox -->
    {/if}
</div>
