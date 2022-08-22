{**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{extends file='catalog/product.tpl'}

{if $content_only}
    {if $use_custom_left_column}
        {block name='page_content'}
            {$HOOK_CONFIGURATOR_DISPLAY_FRONT_PRODUCT_LEFT_COLUMN nofilter}{* HTML comment, no escape necessary *}
        {/block}
    {/if}

    {block name='breadcrumb'}
        {* remove block *}
    {/block}
    {block name='product_description_short'}
        {* remove block *}
    {/block}

    {block name='page_header_container'}
        {* remove block *}
    {/block}
    {block name='product_prices'}
        {* remove block *}
    {/block}
    {block name='product_customization'}
        {* remove block *}
    {/block}
    
    {block name='header'}
        {* remove block *}
    {/block}
    
    {block name='hook_display_reassurance'}
        {* remove block *}
    {/block}
    
    {block name='product_tabs'}
        {* remove block *}
    {/block}
    
    {block name='product_accessories'}
        {* remove block *}
    {/block}
    
     {block name='product_footer'}
     {* remove block *}
    {/block}
    
    {block name='product_images_modal'}
     {* remove block *}
    {/block}
   
    {block name='page_footer_container'}
       {* remove block *}
    {/block}
    
    {block name='product_buy'}
        {$configuratorHtml nofilter}{* HTML comment, no escape necessary *}
        {if isset($configuratorCartDetail)}
            <script>
                {strip}
                var ERROR_LIST = {$ERROR_LIST|json_encode nofilter};{* JSON comment, no escape necessary *}
                var none = '{l s='None' mod='configurator' js=1}';
                var total_price_i18n = '{l s='Final price:' mod='configurator' js=1}';
                var tax_i18n = '{if $priceDisplay == 1}{l s='tax excl.' mod='configurator' js=1}{else}{l s='tax incl.' mod='configurator' js=1}{/if}';
                var detail = {Tools::jsonEncode($configuratorCartDetail->getDetail(true)) nofilter};{* JSON comment, no escape necessary *}
                var tabs_status = {Tools::jsonEncode($tabs_status) nofilter};{* JSON comment, no escape necessary *}
                var configuratorInfoText = {Tools::jsonEncode($configuratorInfoText) nofilter};
                var image_format = 'large_default';
                var fancybox_image_format='thickbox_default';
                var progress_data = {
                    'start':0,
                    'end':{$configuratorCartDetail->progress},
                    'start_color':"{$PROGRESS_START_COLOR}",
                    'end_color': "{$PROGRESS_END_COLOR}"
                };
                var progressive_display = {$PROGRESSIVE_DISPLAY|intval};
                var tooltip_display = {$TOOLTIP_DISPLAY|intval};
                var action = '{$link->getProductLink($productObject)|escape:'html':'UTF-8'}';
                var popover_trigger = '{$CONFIGURATOR_POPOVER_TRIGGER|escape:'html':'UTF-8'}';
                {/strip}
            </script>
        {/if}
        <style>
            #configurator_preview .configurator-add { display: none; }
            #configurator_preview .row > div:first-child { display:none; }
            #configurator_preview .breadcrumb { display:none; }
            .product-description-short { display: none;}
            #footer { display: none;}
            #content{ width: inherit !important; position: inherit !important; bottom: inherit !important;}

        </style>
    {/block}
{else}
    {if $use_custom_left_column}
        {block name='page_content'}
            {$HOOK_CONFIGURATOR_DISPLAY_FRONT_PRODUCT_LEFT_COLUMN nofilter}{* HTML comment, no escape necessary *}
        {/block}
    {/if}

    {block name='product_description_short'}
        {* remove block *}
    {/block}

    {block name='page_header_container'}
        {* remove block *}
    {/block}
    {block name='product_prices'}
        {* remove block *}
    {/block}
    {block name='product_customization'}
        {* remove block *}
    {/block}

    {block name='product_buy'}
        {$configuratorHtml nofilter}{* HTML comment, no escape necessary *}
        {if isset($configuratorCartDetail)}
            <script>
                {strip}
                var ERROR_LIST = {$ERROR_LIST|json_encode nofilter};{* JSON comment, no escape necessary *}
                var none = '{l s='None' mod='configurator' js=1}';
                var total_price_i18n = '{l s='Final price:' mod='configurator' js=1}';
                var tax_i18n = '{if $priceDisplay == 1}{l s='tax excl.' mod='configurator' js=1}{else}{l s='tax incl.' mod='configurator' js=1}{/if}';
                var detail = {Tools::jsonEncode($configuratorCartDetail->getDetail(true)) nofilter};{* JSON comment, no escape necessary *}
                var tabs_status = {Tools::jsonEncode($tabs_status) nofilter};{* JSON comment, no escape necessary *}
                var configuratorInfoText = {Tools::jsonEncode($configuratorInfoText) nofilter};
                var image_format = 'large_default';
                var fancybox_image_format='thickbox_default';
                var progress_data = {
                    'start':0,
                    'end':{$configuratorCartDetail->progress},
                    'start_color':"{$PROGRESS_START_COLOR}",
                    'end_color': "{$PROGRESS_END_COLOR}"
                };
                var progressive_display = {$PROGRESSIVE_DISPLAY|intval};
                var tooltip_display = {$TOOLTIP_DISPLAY|intval};
                var action = '{$link->getProductLink($productObject)|escape:'html':'UTF-8'}';
                var popover_trigger = '{$CONFIGURATOR_POPOVER_TRIGGER|escape:'html':'UTF-8'}';
                {/strip}
            </script>
        {/if}
    {/block}
{/if}