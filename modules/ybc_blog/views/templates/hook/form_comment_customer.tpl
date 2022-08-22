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
<form id="form_comment" class="defaultForm form-horizontal" novalidate="" enctype="multipart/form-data" method="post" action="">
    <div class="panel ybc-blog-panel">
        <div class="panel-heading">
            {l s='Edit customer comment' mod='ybc_blog'}
        </div>
    </div>
    <section class="form-fields">
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="subject">{l s='Subject' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <input id="subject" class="form-control" type="text" value="{if isset($smarty.post.subject)}{$smarty.post.subject|escape:'html':'UTF-8'}{else}{if $ybc_comment->id}{$ybc_comment->subject|escape:'html':'UTF-8'}{/if}{/if}" name="subject" title="{if $ybc_comment->id}{$ybc_comment->subject|escape:'html':'UTF-8'}{/if}" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-3 form-control-label" for="rating">{l s='Rating' mod='ybc_blog'}</label>
            <div class="col-md-9">
                <select id="rating" class=" fixed-width-xl" name="rating">
                    <option value="0" {if $ybc_comment->rating==0}selected="selected"{/if}>{l s='No rating' mod='ybc_blog'}</option>
                    <option value="1" {if $ybc_comment->rating==1}selected="selected"{/if}>{l s='1 rating' mod='ybc_blog'}</option>
                    <option value="2" {if $ybc_comment->rating==2}selected="selected"{/if}>{l s='2 ratings' mod='ybc_blog'}</option>
                    <option value="3" {if $ybc_comment->rating==3}selected="selected"{/if}>{l s='3 ratings' mod='ybc_blog'}</option>
                    <option value="4" {if $ybc_comment->rating==4}selected="selected"{/if}>{l s='4 ratings' mod='ybc_blog'}</option>
                    <option value="5" {if $ybc_comment->rating==5}selected="selected"{/if}>{l s='5 ratings' mod='ybc_blog'}</option>
                </select>
            </div>
        </div>
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="comment">{l s='Comment' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <textarea  name="comment" id="comment">{if isset($smarty.post.comment)}{$smarty.post.comment|escape:'html':'UTF-8'}{else}{if $ybc_comment->id}{$ybc_comment->comment nofilter}{/if}{/if}</textarea>
            </div>
        </div>
        {if in_array('reply_comments',$blog_config.YBC_BLOG_AUTHOR_PRIVILEGES) && $smarty.get.tabmanagament=='comment'}
            <div class="form-group row ">
                <label class="col-md-3 form-control-label" for="reply">{l s='Reply' mod='ybc_blog'}</label>
                <div class="col-md-9">
                    <textarea  name="reply" id="reply">{if isset($smarty.post.reply)}{$smarty.post.reply|escape:'html':'UTF-8'}{else}{if $ybc_comment->id}{$ybc_comment->reply nofilter}{/if}{/if}</textarea>
                </div>
            </div>
        {/if}
        {if $edit_approved}
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="approved">{l s='Approved' mod='ybc_blog'}</label>
            <div class="col-md-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input id="approved_on" type="radio" {if isset($smarty.post.approved)}{if $smarty.post.approved}checked="checked"{/if}{else}{if $ybc_comment->id && $ybc_comment->approved}checked="checked"{/if}{/if} value="1" name="approved" />
                    <label for="approved_on">{l s='Yes' mod='ybc_blog'}</label>
                    <input id="approved_off" type="radio" value="0" name="approved" {if isset($smarty.post.approved)}{if !$smarty.post.approved}checked="checked"{/if}{else}{if !$ybc_comment->approved}checked="checked"{/if}{/if} />
                    <label for="approved_off">{l s='No' mod='ybc_blog'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        {/if}
        <input name="id_comment" value="{if $ybc_comment->id}{$ybc_comment->id|intval}{/if}" type="hidden"/>
    </section>
    <a class="btn btn-primary float-xs-left" href="{$link_back_list|escape:'html':'UTF-8'}">
        {l s='Back to list' mod='ybc_blog'}
    </a>
    <button class="btn btn-primary float-xs-right" name="submitComment" type="submit">{l s='Save' mod='ybc_blog'}</button>
    <button class="btn btn-primary float-xs-right" name="submitCommentStay" type="submit">{l s='Save and stay' mod='ybc_blog'}</button>
</form>