{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author DMConcept <support@dmconcept.fr>
*  @copyright 2015 DMConcept
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $type === 'price'}
    <span class="from_price from_price_{$product->id|escape:'htmlall':'UTF-8'}">{l s='From' mod='configurator'}</span>

    <script type="text/javascript">
		// For product listing ... No hooks to overriding button :(
		productPriceBlockHandler.processSetHtmlLinkToConfigurator({
			'link': '{$configurator_link|escape:'htmlall':'UTF-8'}',
			'l_configure': '{l s='Calculate price' mod='configurator'}',
			'id_product': '{$product->id|escape:'htmlall':'UTF-8'}'
		});
    </script>
{/if}