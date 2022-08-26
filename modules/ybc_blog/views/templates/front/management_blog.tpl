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
{extends file="page.tpl"}
{block name="content"}
    <script type="text/javascript">
        var number_product_related_per_row ={$blog_config.YBC_BLOG_RELATED_PRODUCT_ROW|intval};
        var number_post_related_per_row ={$blog_config.YBC_BLOG_RELATED_POST_ROW|intval};
        var ybc_blog_invalid_file="{l s='Invalid file' mod='ybc_blog'}";
    </script>
    <div id="content-wrapper">
        <div id="main">
            <header class="page-header">
            <h1>{l s='My blog posts' mod='ybc_blog'}</h1>
            </header>
            <section id="content">
                <div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-wrapper-form-managament">
                    {if $ok_author}
                        <div class="left-form-managament">
                            {hook h='displayLeftFormManagament'}
                        </div>
                        <div class="form-managament">
                            {hook h='displayRightFormManagament'}
                        </div>
                    {else}
                        {$errors_html nofilter}
                    {/if}
                </div>
            </section>
            {hook h='displayFooterYourAccount'}
        </div>
    </div>
{/block}
