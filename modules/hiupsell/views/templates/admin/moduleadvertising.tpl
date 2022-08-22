{**
* 2013 - 2020 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2020
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*}

<div class="col-lg-12">
	<div class="panel clearfix">
		<div class="panel-heading"> <i class="icon-cogs"></i> {l s='Check our modules' mod='hiupsell'}</div>
		{if $show_module}
			{foreach from=$modules key=k item=module}
				{if $k|substr:0:1 == 'A'}
					<div class="module_info col-lg-6 col-md-6 col-sm-6">
						<a class="addons-style-module-link" href="{$module->link}" target="_blank">
							<div class="media addons-style-module panel">
								<div class="media-body addons-style-media-body">
									<h4 class="media-heading addons-style-media-heading">{$module->display_name}</h4>
								</div>
								<div class="addons-style-theme-preview center-block">
									<img class="addons-style-img_preview-theme" src="{$module->image_link}" style="max-width: 100%">
									<p class="btn btn-default">
										{if $psv >= 1.6}
											<i class="icon-shopping-cart"></i>
										{else}
											<img src="../img/t/AdminParentOrders.gif" alt="">
										{/if}
										{$module->price}
									</p>
								</div>
							</div>
						</a>
					</div>
				{/if}
			{/foreach}
		{/if}
	</div>
</div>
<div class="clearfix"></div>
