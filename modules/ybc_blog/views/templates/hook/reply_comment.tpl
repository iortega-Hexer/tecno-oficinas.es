{*
* 2007-2019 ETS-Soft
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
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="panel">
    <div class="comment-content">
        <span class="panel-heading-action">
            <span>
                {l s='Status' mod='ybc_blog'}:&nbsp;{if $comment->approved}{l s='Approved' mod='ybc_blog'}{else}{l s='Pending' mod='ybc_blog'}{/if}
            </span>
            <a class="del_comment" title="{l s='Delete' mod='ybc_blog'}" onclick="return confirm('Do you want to delete this comment?');" href="{$link_delete|escape:'html':'UTF-8'}">
                <i class="icon-trash"></i>
                {l s='Delete' mod='ybc_blog'}
            </a>
            {if $comment->approved}
                <a class="field-approved list-action-enable action-disabled" title="{l s='Click to disapprove' mod='ybc_blog'}" href="{$curenturl|escape:'html':'UTF-8'}&id_comment={$comment->id|intval}&change_comment_approved=0">
                    <i class="icon-check"></i>
                    {*l s='Disapproved' mod='ybc_blog'*}
                </a>
            {else}
                <a class="field-approved list-action-enable action-enabled" title="{l s='Click to mark as approved' mod='ybc_blog'}" href="{$curenturl|escape:'html':'UTF-8'}&id_comment={$comment->id|intval}&change_comment_approved=1">
                    <i class="icon-remove"></i>
                    {*l s='Approved' mod='ybc_blog'*}
                </a>
            {/if}
        </span>
        <h4 class="subject_comment">{$comment->subject|escape:'html':'UTF-8'}</h4>
        {if $comment->name}
            <h4 class="comment_name">
                {l s='By' mod='ybc_blog'}: <span>{$comment->name|escape:'html':'UTF-8'}</span>
            </h4>
        {/if}
        <h4 class="post_title">
            {l s='Post title' mod='ybc_blog'}: <span><a target="_blank" href="{$post_link|escape:'html':'UTF-8'}" title="{$post_class->title|escape:'html':'UTF-8'}">{$post_class->title|escape:'html':'UTF-8'}</a></span>
        </h4> 
        <div class="comment-content">
            <p>{$comment->comment nofilter}</p>
        </div>
        <form method="post" action="">
            <div class="form_reply">
                <textarea id="reply_comwent_text" placeholder="{l s='Reply ...' mod='ybc_blog'}" name="reply_comwent_text">{if isset($reply_comwent_text)}{$reply_comwent_text|escape:'html':'UTF-8'}{/if}</textarea>
                <input class="btn btn-primary btn-default" type="submit" value="{l s='Send' mod='ybc_blog'}" name="addReplyComment"/><br />
            </div>
            {if $replies}
                <h4 class="replies_comment">{l s='Replies' mod='ybc_blog'}:</h4>
                <div class="table-responsive clearfix">
                    <table class="table configuration">
                        <thead>
                            <tr class="nodrag nodrop">
                                <script type="text/javascript">
                                    var detele_confirm ="{l s='Do you want to delete this item?' mod='ybc_blog'}";
                                </script>
                                <th class="fixed-width-xs">
                                    <span class="title_box">
                                        <input value="" class="reply_readed_all" type="checkbox" />
                                    </span>
                                </th>
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
                                    <td class="reply-more-action">
                                        <input type="checkbox" name="reply_readed[{$reply.id_reply|intval}]" class="reply_readed" value="1" data-approved="{if $reply.approved}1{else}0{/if}"/>
                                    </td> 
                                    <td>{$reply.id_reply|intval}</td>
                                    <td>{$reply.name|escape:'html':'UTF-8'}</td>
                                    <td>{$reply.reply nofilter}</td>
                                    <td class="text-center">
                                        {if $reply.approved}
                                            <a class="list-action field-approved list-action-enable action-enabled list-item-{$reply.id_reply|intval}" data-id="{$reply.id_reply|intval}" title="{l s='Unapproved' mod='ybc_blog'}" href="{$curenturl|escape:'html':'UTF-8'}&id_reply={$reply.id_reply|intval}&change_approved=0">
                                                <i class="icon-check"></i>
                                            </a>
                                        {else}
                                            <a class="list-action field-approved list-action-enable action-disabled list-item-{$reply.id_reply|intval}" data-id="{$reply.id_reply|intval}" title="{l s='Approved' mod='ybc_blog'}" href="{$curenturl|escape:'html':'UTF-8'}&id_reply={$reply.id_reply|intval}&change_approved=1">
                                                <i class="icon-remove"></i>
                                            </a>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <a class="del_reply" href="{$curenturl|escape:'html':'UTF-8'}&delreply=1&id_reply={$reply.id_reply|intval}" onclick="return confirm('{l s='Do you want to delete this item?' mod='ybc_blog'}');" title="{l s='Delete' mod='ybc_blog'}"><i class="icon-trash"></i>{l s='Delete' mod='ybc_blog'}</a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <select id="bulk_action_reply" name="bulk_action_reply" style="display:none;width: 200px;">
                        <option value="">{l s='Bulk actions' mod='ybc_blog'}</option>
                        <option value="mark_as_approved">{l s='Approved' mod='ybc_blog'}</option>
                        <option value="mark_as_unapproved">{l s='Unapproved' mod='ybc_blog'}</option>
                        <option value="delete_selected">{l s='Delete selected' mod='ybc_blog'}</option>
                    </select>
                </div>
            {/if}
            <div class="panel-footer">
                <a class="btn btn-default" href="{$link_back|escape:'html':'UTF-8'}" title="{l s='Back' mod='ybc_blog'}">
                <i class="process-icon-cancel"></i>
                {l s='Back' mod='ybc_blog'}
            </a>
            </div>
        </form>
    </div>
</div>