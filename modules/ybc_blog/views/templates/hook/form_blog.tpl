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
<form id="form_blog" class="defaultForm form-horizontal" novalidate="" enctype="multipart/form-data" method="post" action="">
    <div class="panel ybc-blog-panel">
        <div class="panel-heading">
            {if $ybc_post->id}
                {l s='Edit blog post' mod='ybc_blog'}
                <a class="edit_view_post btn btn-primary float-xs-right" href="{$link_post|escape:'html':'UTF-8'}">{l s='View post' mod='ybc_blog'}</a>
            {else}
                <h3>{l s='Submit new post' mod='ybc_blog'}</h3>
            {/if}
        </div>
    </div>
    <section class="form-fields">
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="post_title">{l s='Post title' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <input id="post_title" class="form-control" type="text" value="{if isset($smarty.post.title)}{$smarty.post.title|escape:'html':'UTF-8'}{else}{if $ybc_post->id}{$ybc_post->title|escape:'html':'UTF-8'}{/if}{/if}" name="title" title="{if $ybc_post->id}{$ybc_post->title|escape:'html':'UTF-8'}{/if}" />
            </div>
        </div>
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="short_description">{l s='Short description' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <textarea class="ets_blog_autoload_rte" name="short_description" id="short_description">{if isset($smarty.post.short_description)}{$smarty.post.short_description nofilter}{else}{if $ybc_post->id}{$ybc_post->short_description nofilter}{/if}{/if}</textarea>
            </div>
        </div>
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="description">{l s='Post content' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <textarea class="ets_blog_autoload_rte" name="description" id="description">{if isset($smarty.post.description)}{$smarty.post.description nofilter}{else}{if $ybc_post->id}{$ybc_post->description nofilter}{/if}{/if}</textarea>
            </div>
        </div>
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="category">{l s='Categories' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <ul style="float: left; padding: 0; margin-top: 5px;">
                    {$html_content_category_block nofilter}
                </ul>
            </div>
        </div>
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="thumb">{l s='Post thumbnail' mod='ybc_blog'}<span class="required">*</span></label>
            <div class="col-md-9">
                <div class="upload_form_custom">
        			<span class="input-group-addon"><i class="fa fa-file"></i></span>
        			<span class="input-group-btn">
        				<i class="fa fa-folder-open"></i>{l s='Add file' mod='ybc_blog'}
				    </span>
                    <input class="form-control" type="file" value="" name="thumb" id="thumb" />
        		</div>
                <p class="help-block">{l s='Accepted formats: jpg, jpeg, png, gif. Limit: ' mod='ybc_blog'}{Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')|intval}Mb. {l s='Recommended size:' mod='ybc_blog'}&nbsp;{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH',null,null,null,260)|intval}x{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT',null,null,null,180)|intval}</p>
                {if $ybc_post->id && $ybc_post->thumb}
                    <div class="thumb_post">
                        <img style="max-width: 200px;display: inline-block;" src="{$dir_img|escape:'html':'UTF-8'}post/thumb/{$ybc_post->thumb|escape:'html':'UTF-8'}" title="{$ybc_post->title|escape:'html':'UTF-8'}" alt="{$ybc_post->title|escape:'html':'UTF-8'}" />
                        {*<a class="delete_url" href="{$link_delete_thumb|escape:'html':'UTF-8'}" style="display: inline-block; text-decoration: none!important;">
                            <span style="color: #666">
                                <i class="fa fa-trash" style="font-size: 20px;"></i>
                            </span>
                        </a>  *}  
                    </div>
                {/if}
            </div>
        </div>
        <div class="form-group row ">
            <label class="col-md-3 form-control-label" for="post_image">{l s='Blog post main image' mod='ybc_blog'}</label>
            <div class="col-md-9">
                <div class="upload_form_custom">
        			<span class="input-group-addon"><i class="fa fa-file"></i></span>
        			<span class="input-group-btn">
        				<i class="fa fa-folder-open"></i>{l s='Add file' mod='ybc_blog'}
				    </span>
                    <input class="form-control" type="file" value="" name="image" id="post_image" />
        		</div>
                <p class="help-block">{l s='Accepted formats: jpg, jpeg, png, gif. Limit: ' mod='ybc_blog'}{Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')|intval}Mb. {l s='Recommended size: ' mod='ybc_blog'}&nbsp;{Configuration::get('YBC_BLOG_IMAGE_BLOG_WIDTH',null,null,null,1920)|intval}x{Configuration::get('YBC_BLOG_IMAGE_BLOG_HEIGHT',null,null,null,750)|intval}</p>
                {if $ybc_post->id && $ybc_post->image}
                    <div class="thumb_post">
                        <img style="max-width: 200px;" src="{$dir_img|escape:'html':'UTF-8'}post/{$ybc_post->image|escape:'html':'UTF-8'}" title="{$ybc_post->title|escape:'html':'UTF-8'}" alt="{$ybc_post->title|escape:'html':'UTF-8'}" />
                        <a onclick="return confirm('{l s='Do you want to delete blog post main image?' mod='ybc_blog'}');" class="delete_url" href="{$link_delete_image|escape:'html':'UTF-8'}" style="display: inline-block; text-decoration: none!important;">
                            <span style="color: #666">
                                <i class="fa fa-trash" style="font-size: 20px;"></i>
                            </span>
                        </a>
                    </div>
                {/if}
            </div>
        </div>
        <input name="id_post" value="{if $ybc_post->id}{$ybc_post->id|intval}{/if}" type="hidden"/>
    </section>
    <a class="btn btn-primary float-xs-left ybc_button_backtolist" href="{$link_back_list|escape:'html':'UTF-8'}">
        {l s='Back to list' mod='ybc_blog'}
    </a>
    <button class="btn btn-primary float-xs-right" name="submitPostStay" type="submit">{if $ybc_post->id}{l s='Save' mod='ybc_blog'}{else}{l s='Submit' mod='ybc_blog'}{/if}</button>
</form>
