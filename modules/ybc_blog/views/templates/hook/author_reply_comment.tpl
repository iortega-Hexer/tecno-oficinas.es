{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
* 
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="panel">
    <div class="comment-content">
        <span class="panel-heading-action">
            <span>
                {l s='Status' mod='ybc_blog'}:&nbsp;{if $comment->approved}{l s='Approved' mod='ybc_blog'}{else}{l s='Pending' mod='ybc_blog'}{/if}
            </span>
            {if isset($link_delete) && $link_delete}
                <a class="del_comment" title="{l s='Delete' mod='ybc_blog'}" onclick="return confirm('{l s='Do you want to delete this comment?' mod='ybc_blog'}');" href="{$link_delete|escape:'html':'UTF-8'}">
                    <i class="icon-trash"></i>
                    {l s='Delete' mod='ybc_blog'}
                </a>
            {/if}
            {if isset($link_approved) &&$link_approved}
                {if $comment->approved}         
                    <a class="field-approved list-action-enable action-disabled" title="{l s='Click to disapprove' mod='ybc_blog'}" href="{$link_approved|escape:'html':'UTF-8'}">
                       <i class="icon-check"></i>
                    </a>
                {else}
                    <a class="field-approved list-action-enable action-enabled" title="{l s='Click to mark as approved' mod='ybc_blog'}" href="{$link_approved|escape:'html':'UTF-8'}">
                    <i class="icon-remove"></i>
                    </a>
                {/if}
            {/if} 
        </span>
        <h4 class="subject_comment">{$comment->subject|escape:'html':'UTF-8'}</h4>
        {if $comment->name}
            <h4 class="comment_name">
                {l s='By' mod='ybc_blog'}: <span>{$comment->name|escape:'html':'UTF-8'}</span>
            </h4>
        {/if}
        <h4 class="post_title">
            {l s='Post title' mod='ybc_blog'}: <span><a href="{$post_link|escape:'html':'UTF-8'}" title="{$post_class->title|escape:'html':'UTF-8'}">{$post_class->title|escape:'html':'UTF-8'}</a></span>
        </h4>
        <div class="comment-content">
            <p>{$comment->comment nofilter}</p>
        </div>
        <form method="post" action="">
            <div class="form_reply">
                {if in_array('reply_comments',explode(',',Configuration::get('YBC_BLOG_AUTHOR_PRIVILEGES')))}
                    <textarea id="reply_comwent_text" placeholder="{l s='Reply ...' mod='ybc_blog'}" name="reply_comwent_text">{if isset($reply_comwent_text)}{$reply_comwent_text|escape:'html':'UTF-8'}{/if}</textarea>
                    <input class="btn btn-primary btn-default" type="submit" value="{l s='Send' mod='ybc_blog'}" name="addReplyComment"/>
                {/if}
                {if !$replies}
                    <a class="btn btn-default back_list_comment" href="{$link_back|escape:'html':'UTF-8'}" title="{l s='Back' mod='ybc_blog'}">
                        <i class="process-icon-cancel"></i>
                        {l s='Back' mod='ybc_blog'}
                    </a>
                {/if}
            </div>
            {if $replies}
                <h4 class="replies_comment">{l s='Replies' mod='ybc_blog'}:</h4>
                <div class="table-responsive clearfix">
                    <table class="table configuration">
                        <thead>
                            <tr class="nodrag nodrop">
                                <td>{l s='Id' mod='ybc_blog'}</td>
                                <td>{l s='Name' mod='ybc_blog'}</td>
                                <td>{l s='Reply content' mod='ybc_blog'}</td>
                                <td class="text-center">{l s='Approved' mod='ybc_blog'}</td>
                                <td class="text-center">{l s='Action' mod='ybc_blog'}</td>
                            </tr>
                        </thead>
                        <tbody id="list-ybc_reply">
                            {foreach from=$replies item='reply'}
                                <tr>
                                    <td>{$reply.id_reply|intval}</td>
                                    <td>{$reply.name|escape:'html':'UTF-8'}</td>
                                    <td>{$reply.reply nofilter}</td>
                                    <td class="text-center">
                                        {if $reply.approved}
                                            {if isset($reply.link_approved) && $reply.link_approved}
                                                <a class="list-action field-approved list-action-enable action-enabled list-item-{$reply.id_reply|intval}" data-id="{$reply.id_reply|intval}" title="{l s='Click to unapprove' mod='ybc_blog'}" href="{$reply.link_approved|escape:'html':'UTF-8'}">
                                            {else}
                                                <span title="{l s='Approved' mod='ybc_blog'}">
                                            {/if}
                                                <i class="icon-check"></i>
                                            {if isset($reply.link_approved) && $reply.link_approved}
                                            </a>
                                            {else}
                                            </span>
                                            {/if}
                                        {else}
                                            {if isset($reply.link_approved) && $reply.link_approved}
                                                <a class="list-action field-approved list-action-enable action-disabled list-item-{$reply.id_reply|intval}" data-id="{$reply.id_reply|intval}" title="{l s='Click to mark as approved' mod='ybc_blog'}" href="{$reply.link_approved|escape:'html':'UTF-8'}">
                                            {else}
                                                <span title="{l s='unapproved' mod='ybc_blog'}">
                                            {/if}
                                                <i class="icon-remove"></i>
                                            {if isset($reply.link_approved) && $reply.link_approved}
                                            </a>
                                            {else}
                                            </span>
                                            {/if}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if isset($reply.link_delete) && $reply.link_delete}
                                            <a class="del_reply" href="{$reply.link_delete|escape:'html':'UTF-8'}" onclick="return confirm('{l s='Do you want to delete this item?' mod='ybc_blog'}');" title="{l s='Delete' mod='ybc_blog'}"><i class="icon-trash"></i>{l s='Delete' mod='ybc_blog'}</a>
                                        {else}
                                            --
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <a class="btn btn-default back_list_comment" href="{$link_back|escape:'html':'UTF-8'}" title="{l s='Back' mod='ybc_blog'}">
                        <i class="process-icon-cancel"></i>
                        {l s='Back' mod='ybc_blog'}
                    </a>
                </div>
            {/if}
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
   $('#reply_comwent_text').focus(); 
});
</script>