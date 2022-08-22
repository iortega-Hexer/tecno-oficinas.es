{**
* 2013 - 2017 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2017
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*}

{if $psv >= 1.6}
	<div class="col-lg-2">
		<div class="list-group">
			{foreach from=$tabs key=action item=name}
				<a {if $action == 'version'} style="margin-top:30px;" {/if} class="list-group-item {if {$upsell_key|escape:'htmlall':'UTF-8'} == "{$action}" || ($upsell_key == '' && $action == 'settings')} active{/if}"
				{if $action != 'version'} href="{$module_url|escape:'htmlall':'UTF-8'}&hiupsell={$action|escape:'htmlall':'UTF-8'}"{/if}>
					{if $action != 'version'}
						{$name|escape:'htmlall':'UTF-8'}
					{else}
						{$name|escape:'htmlall':'UTF-8'} - {$module_version|escape:'html':'UTF-8'}
					{/if}
				</a>
			{/foreach}
		</div>
	</div>
{else}
	<div class="productTabs">
		<ul class="tab">
			{foreach from=$tabs key=action item=name}
				<li class="tab-row">
					<a {if $action == 'version'} style="margin-top:30px;" {/if} class="tab-page 
						{if {$upsell_key|escape:'htmlall':'UTF-8'} == "{$action}" || ($upsell_key == '' && $action == 'settings')} selected{/if}"
						{if $action != 'version'} href="{$module_url|escape:'htmlall':'UTF-8'}&hiupsell={$action|escape:'htmlall':'UTF-8'}"{/if}>
						{if $action != 'version'}
							{$name|escape:'htmlall':'UTF-8'}
						{else}
							{$name|escape:'htmlall':'UTF-8'} - {$module_version|escape:'html':'UTF-8'}
						{/if}
					</a>
				</li>
			{/foreach}
		</ul>
	</div>
{/if}
