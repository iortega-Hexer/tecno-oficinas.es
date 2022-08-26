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
<div class="ybc-form-group ybc-blog-tab-basic active ybc-form-group-author">
    {if isset($author) && $author}
        <div class="form-group">
            <label class="control-label col-lg-3"> {l s='Author' mod='ybc_blog'}: </label>
            <div class="col-lg-9">
                <div class="customer_author_name"><a href="{$author.link|escape:'html':'UTF-8'}">{if $author.name}{$author.name|escape:'html':'UTF-8'}{else}{$author.firstname|escape:'html':'UTF-8'}&nbsp;{$author.lastname|escape:'html':'UTF-8'}{/if}</a></div>
                <button class="ybc_display_form_author btn btn-default"><i class="icon-pencil"></i>{l s='Change' mod='ybc_blog'}</button>
            </div>
        </div>
    {else}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Author' mod='ybc_blog'}:</label>
            <div class="col-lg-9">
                <button class="ybc_display_form_author btn btn-default"><i class="icon-pencil"></i>{l s='Set author' mod='ybc_blog'}</button>
            </div>
        </div>
    {/if}
    {if $YBC_BLOG_ALLOW_CUSTOMER_AUTHOR}
        <div class="form-group form_author">
            <div class="control-label col-lg-3"></div>
            <div class="col-lg-9">
                <label for="is_customer"><input type="radio" name="is_customer" value="0" id="is_customer" {if $post.is_customer==0}checked="checked"{/if} />{l s='Administrator - Authors' mod='ybc_blog'}</label>
                <label for="is_customer_1"><input type="radio" name="is_customer" value="1" id="is_customer_1" {if $post.is_customer==1}checked="checked"{/if} />{l s='Community - Authors' mod='ybc_blog'}</label>
            </div>
        </div>
    {/if}
    <div class="form-group form_author">
        <div class="from_admin_author{if !$YBC_BLOG_ALLOW_CUSTOMER_AUTHOR || $post.is_customer==0} show{/if}">
            <div class="control-label col-lg-3">{l s='Administrator - Author' mod='ybc_blog'}</div>
            <div class="col-lg-9">
                <select id="admin_author" name="admin_author" class="fixed-width-xl">
                    <option value="">{l s='--' mod='ybc_blog'}</option>
                    {foreach from=$admin_authors item='admin_author'}
                        <option data-link="{$admin_author.link|escape:'html':'UTF-8'}" value="{$admin_author.id_employee|intval}" {if $post.is_customer==0 && isset($author) &&  $author['id_employee']==$admin_author.id_employee}selected="selected"{/if}>{if $admin_author.name}{$admin_author.name|escape:'html':'UTF-8'}{else}{$admin_author.firstname|escape:'html':'UTF-8'}&nbsp;{$admin_author.lastname|escape:'html':'UTF-8'}{/if}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    {if $YBC_BLOG_ALLOW_CUSTOMER_AUTHOR}
        <div class="form-group form_author">
            <div class="from_customer_author{if $post.is_customer==1} show{/if}">
                <div class="control-label col-lg-3">{l s='Community - Author' mod='ybc_blog'}</div>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="hidden" value="{if $post.is_customer==1 && isset($author) &&  $author['id_customer']}{$author['id_customer']|intval}{/if}" name="customer_author" id="customer_author"/>
        				{if $post.is_customer==1&& isset($author) &&  $author['id_customer']}
                            <div class="customer_author_name_choose">{if $author.name}{$author.name|escape:'html':'UTF-8'}{else}{$author.firstname|escape:'html':'UTF-8'}&nbsp;{$author.lastname|escape:'html':'UTF-8'}{/if}<span class="close_choose">x</span></div>
                        {/if}
                        <input id="customer_autocomplete_input" name="customer_autocomplete_input" placeholder="{l s='Search Community - Author by ID or name or email' mod='ybc_blog'}" autocomplete="off" class="ac_input" type="text" />
                        <span class="input-group-addon"><i class="icon-search"></i></span>
        			</div>
                </div>
            </div>
        </div>
    {/if}
</div>
<style>
    .from_admin_author,.from_customer_author{
        display:none;
    }
    .from_admin_author.show,.from_customer_author.show{
        display:block;
    }
    .form-group.form_author{
        display:none;
    }
    .form-group.form_author.show{
        display:block;
    }
</style>