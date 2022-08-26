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
<div id="ybc_blog_email_preview">
    <div class="panel email_preview">
        <div class="panel-heading">
            <i class="icon-email"></i>
            {l s='Email preview' mod='ybc_blog'}
        </div>
        <div class="form-wrapper">
            <div class="ybc_blog_template_type template_html">
                {foreach $languages item='lang'}
                    <div class="translatable-field lang-{$lang.id_lang|intval}" style="{if $lang.id_lang!=$id_curent_lang} display:none;{/if}">
                        <iframe id="preview_template_html_{$lang.id_lang|intval}">
                        </iframe>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var PS_SHOP_LOGO ='{$PS_SHOP_LOGO|escape:'html':'UTF-8'}';
var PS_SHOP_NAME ='{$PS_SHOP_NAME|escape:'html':'UTF-8'}';
var PS_SHOP_URL ='{$PS_SHOP_URL|escape:'html':'UTF-8'}';
{literal}
$(document).ready(function(){
    var templateHtml = $('.ybc_blog_template_type.template_html')
     $('[id^=content_html]:not([id^=content_html_full])').each(function () {
        var lang_id = $(this).attr('id').replace('content_html_', '');
        ybc_blog_loadIframe(lang_id);
    });
    $('[id^=content_html]').keyup(function(){
        var lang_id = $(this).attr('id').replace('content_html_', '');
        ybc_blog_loadIframe(lang_id);
    });
});
function ybc_blog_loadIframe(lang_id)
{
    var templateHtml = $('.ybc_blog_template_type.template_html');
    createIframe = null
    if (templateHtml.find('#preview_template_html_' + lang_id).length < 1) {
        createIframe = $('<iframe id="preview_template_html_' + lang_id + '" class="translatable-field lang-' + lang_id + '"></iframe>');
        templateHtml.append(createIframe);
    } else {
        createIframe = templateHtml.find('#preview_template_html_' + lang_id);
    }
    var contentIFrame = createIframe[0].contentDocument || createIframe[0].contentWindow.document;
    contentIFrame.write(ybc_blog_doShortCode($('#content_html_' + lang_id).val()));
    contentIFrame.close();
}
function ybc_blog_doShortCode(html)
{
    html = html.replace(/{shop_name}/gi, PS_SHOP_NAME);
    html = html.replace(/{shop_url}/gi, PS_SHOP_URL);
    html = html.replace(/{shop_logo}/gi, PS_SHOP_LOGO);
    return html;
}
{/literal}
</script>