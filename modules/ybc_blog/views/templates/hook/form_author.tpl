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
<div class="blog-managament-information">
    <div class="panel ybc-blog-panel">
        <div class="panel-heading">
        {l s='My information' mod='ybc_blog'}
        </div>
    </div>
    <form class="defaultForm form-horizontal" novalidate="" enctype="multipart/form-data" method="post" action="{$action_link|escape:'html':'UTF-8'}">
        <section class="form-fields">
            <div class="form-group row ">
                <label class="col-md-3 form-control-label" for="author_name">{l s='Name' mod='ybc_blog'}</label>
                <div class="col-md-9">
                    <input id="author_name" class="form-control" readonly="true" type="text" value="{if isset($smarty.post.author_name)}{$smarty.post.author_name|escape:'html':'UTF-8'}{else}{$name_author|escape:'html':'UTF-8'}{/if}" name="author_name" />
                    <p class="help-block"> <a href="{$link->getPageLink('identity')|escape:'html':'UTF-8'}" title="{l s='Update my name' mod='ybc_blog'}">{l s='Update my name' mod='ybc_blog'}</a> </p>
                </div>
            </div>
            <div class="form-group row ">
                <label class="col-md-3 form-control-label" for="author_description">{l s='Introduction info' mod='ybc_blog'}</label>
                <div class="col-md-9">
                    <textarea name="author_description" id="author_description">{if isset($smarty.post.author_description)}{$smarty.post.author_description nofilter}{else}{$author_description nofilter}{/if}</textarea>
                </div>
            </div>
            <div class="form-group row ">
                <label class="col-md-3 form-control-label" for="author_avata">{l s='Avatar' mod='ybc_blog'}</label>
                <div class="col-md-9">
                    <div class="upload_form_custom">
            			<span class="input-group-addon"><i class="fa fa-file"></i></span>
            			<span class="input-group-btn">
            				<i class="fa fa-folder-open"></i>{l s='Add file' mod='ybc_blog'}
    				    </span>
                        <input class="form-control" type="file" value="" name="author_avata" id="author_avata" />
            		</div>
                    <p class="help-block">{l s='Recommended size: ' mod='ybc_blog'}&nbsp;{Configuration::get('YBC_BLOG_IMAGE_AVATA_WIDTH',null,null,null,300)|intval}x{Configuration::get('YBC_BLOG_IMAGE_AVATA_HEIGHT',null,null,null,300)|intval} </p>
                    {if $author_avata}
                        <div class="thumb_post">
                            <img style="max-width: 200px;" src="{$author_avata|escape:'html':'UTF-8'}" title="{$name_author|escape:'html':'UTF-8'}" alt="{$name_author|escape:'html':'UTF-8'}" />
                            <a onclick="return confirm('{l s='Do you want to delete avatar image?' mod='ybc_blog'}');" class="delete_url" href="{$link_delete_image|escape:'html':'UTF-8'}" style="display: inline-block; text-decoration: none!important;">
                                <span style="color: #666">
                                    <i class="fa fa-trash" style="font-size: 20px;"></i>{l s='Delete' mod='ybc_blog'}
                                </span>
                            </a>
                        </div>
                    {else}
                        <div class="thumb_post">
                            <img style="max-width: 200px;" src="{$avata_default|escape:'html':'UTF-8'}" title="{l s='Default avatar' mod='ybc_blog'}" />
                        </div>
                    {/if}
                </div>
            </div>
        </section>
        <button class="btn btn-primary float-xs-right" type="submit" name="submitAuthorManagement">{l s='Save' mod='ybc_blog'}</button>
    </form>
</div>