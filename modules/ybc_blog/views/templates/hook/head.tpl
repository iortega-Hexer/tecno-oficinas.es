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
{if isset($add_tmce) && $add_tmce}
<script type="text/javascript" src="{$url_path|escape:'html':'UTF-8'}views/js/tinymce/tinymce.min.js"></script>
{/if}   
<script type="text/javascript">
var unlike_text ="{l s='Unlike this post' mod='ybc_blog'}";
var like_text ="{l s='Like this post' mod='ybc_blog'}";
var baseAdminDir ='{$baseAdminDir|escape:'html':'UTF-8'}';
var ybc_blog_product_category ='{$ybc_blog_product_category|intval}';
var ybc_blog_polls_g_recaptcha = false;
</script>
{if $link_current}
<link href="{$link_current|escape:'html':'UTF-8'}" rel="canonical" />
{/if}
{if isset($css) && $css}
    <style>{$css nofilter}</style>
{/if}
{if isset($blog_post_header)}
    <meta property="og:app_id"        content="id_app" />
    <meta property="og:type"          content="article" />
    <meta property="og:title"         content="{$blog_post_header.title|escape:'html':'UTF-8'}" />
    <meta property="og:image"         content="{if $blog_post_header.image}{$blog_post_header.image|escape:'html':'UTF-8'}{else}{$blog_post_header.thumb|escape:'html':'UTF-8'}{/if}" />
    <meta property="og:description"   content="{$blog_post_header.short_description|strip_tags|escape:'html':'UTF-8'}" />
    <meta itemprop="author"           content="{ucfirst($blog_post_header.firstname)|escape:'html':'UTF-8'} {ucfirst($blog_post_header.lastname)|escape:'html':'UTF-8'}"/>
    <meta property="og:url"           content="{$blog_post_header.link|escape:'quotes'}" />
    <meta name="twitter:card"         content="summary_large_image" />
    {if $blog_post_header.enabled==-2}
        <meta name="robots" content="noindex, follow" />
    {/if}
{/if} 
{if $YBC_BLOG_CAPTCHA_TYPE=='google'}
    <script type="text/javascript">
        var YBC_BLOG_CAPTCHA_SITE_KEY = '{$YBC_BLOG_CAPTCHA_SITE_KEY|escape:'html':'UTF-8'}';
        var ybc_polls_lonloadCallback = function() {
            ybc_blog_polls_g_recaptcha = grecaptcha.render(document.getElementById('ybc_blog_polls_g_recaptcha'), {
                'sitekey':YBC_BLOG_CAPTCHA_SITE_KEY,
                'theme':'light'
            });
        };
        var ybc_comment_lonloadCallback = function() {
            grecaptcha.render(document.getElementById('ybc_blog_comment_g_recaptcha'), {
                'sitekey':YBC_BLOG_CAPTCHA_SITE_KEY,
                'theme':'light'
            });
        };
    </script>
{/if}
{if $YBC_BLOG_CAPTCHA_TYPE=='google3'}
    <script type="text/javascript">
        var YBC_BLOG_CAPTCHA_SITE_KEY = '{$YBC_BLOG_CAPTCHA_SITE_KEY3|escape:'html':'UTF-8'}';
        {literal}
        var ybc_polls_lonloadCallback = function() {
             grecaptcha.ready(function() {
                grecaptcha.execute(YBC_BLOG_CAPTCHA_SITE_KEY, {action: 'homepage'}).then(function(token) {
                    $('#ybc_blog_polls_g_recaptcha').val(token);
             });
          });
        };
        var ybc_comment_lonloadCallback = function() {
            grecaptcha.ready(function() {
                grecaptcha.execute(YBC_BLOG_CAPTCHA_SITE_KEY, {action: 'homepage'}).then(function(token) {
                    $('#ybc_blog_comment_g_recaptcha').val(token);
                });
            });
        };
        {/literal}
    </script>
    
{/if}