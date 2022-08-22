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
{extends file="page.tpl"}
{block name="content_wrapper"}
<div id="content-wrapper">
    <div id="main">
        <header class="page-header">
        <h1>{l s='My blog info' mod='ybc_blog'}</h1>
        </header>
        <section id="content">
              {block name="content"}
                    <div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-author-info ybc-blog-wrapper-form-managament">
                        <div class="form-managament" style="width:100%">
                            {if isset($errors_html)}
                                {$errors_html nofilter} 
                            {/if}
                            {if isset($sucsecfull_html)}
                                {$sucsecfull_html nofilter}
                            {/if}
                            {$form_html_post nofilter}
                        </div>
                    </div>
              {/block}
        </section>
        {hook h='displayFooterYourAccount'}
    </div>
</div>
{/block}
