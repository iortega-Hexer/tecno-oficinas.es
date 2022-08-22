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
<!doctype html>
<html lang="{$language.iso_code|escape:'html':'UTF-8'}">
  <head>
    {block name='head'}
      {include file='_partials/head.tpl'}
    {/block}
  </head>

  <body id="{$page.page_name|escape:'html':'UTF-8'}" class="{$page.body_classes|classnames}" class="page-customer-account">
    <script type="text/javascript">
        var number_product_related_per_row ={$blog_config.YBC_BLOG_RELATED_PRODUCT_ROW|intval};
        var number_post_related_per_row ={$blog_config.YBC_BLOG_RELATED_POST_ROW|intval};
        var ybc_blog_invalid_file="{l s='Invalid file' mod='ybc_blog'}";
    </script>
    {hook h='displayAfterBodyOpeningTag'}

    <main>
      {block name='product_activation'}
        {include file='catalog/_partials/product-activation.tpl'}
      {/block}
      <header id="header">
        {block name='header'}
          {include file='_partials/header.tpl'}
        {/block}
      </header>
      {block name='notifications'}
        {include file='_partials/notifications.tpl'}
      {/block}
      <section id="wrapper">
        <div class="container">
          {block name='breadcrumb'}
            {include file='_partials/breadcrumb.tpl'}
          {/block}
          {block name="content_wrapper"}
            <div id="content-wrapper">
                <div id="main">
                    <header class="page-header">
                    <h1>{l s='My blog posts' mod='ybc_blog'}</h1>
                    </header>
                    <section id="content">
                          {block name="content"}
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
                          {/block}
                    </section>
                    {hook h='displayFooterYourAccount'}
                </div>
            </div>
          {/block}
        </div>
      </section>
        <div class="clearfix"></div>
      <footer id="footer">
        {block name="footer"}
          {include file="_partials/footer.tpl"}
        {/block}
      </footer>

    </main>

    {block name='javascript_bottom'}
      {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}

    {hook h='displayBeforeBodyClosingTag'}

  </body>

</html>
