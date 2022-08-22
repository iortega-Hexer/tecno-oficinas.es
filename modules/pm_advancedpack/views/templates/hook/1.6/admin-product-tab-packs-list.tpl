<div id="product-packs" class="panel product-tab">
	<h3>{l s='Pack list with:' mod='pm_advancedpack'} {$currentProduct->name|escape:'html':'UTF-8'}</h3>

	<div id="ap5-pack-list" class="row">
	{if empty($currentProductIsPack)}
		<div class="col-lg-3 col-sm-4 col-xs-6">
			<div id="ap5-pack-row-new" class="ap5-pack-row panel text-center">
				<a class="btn btn-primary text-center" href="{$link->getAdminLink('AdminProducts')}&addproduct&newpack&source_id_product={$currentProduct->id|intval}"><i class="icon-plus-sign"></i><br />{l s='Create a new pack from this product' mod='pm_advancedpack'}</a>
			</div>
		</div>
	{else}
		<script type="text/javascript">
			$(document).ready(function() {
				$('#link-ModulePm_advancedpack').addClass('hide');
			});
		</script>
	{/if}
{if $packList|count}
	{foreach from=$packList item='packContent' name='packLoop' key='idPack'}
		{assign var=packPrice value=AdvancedPack::getPackPrice($idPack, false)}
		{assign var=packOldPrice value=AdvancedPack::getPackPrice($idPack, false, false)}
		<div class="col-lg-3 col-sm-4 col-xs-6">
			<div id="ap5-pack-row-{$idPack|intval}" class="ap5-pack-row panel">
				<header class="ap5-pack-name panel-heading">
					<a target="_blank" href="{$link->getAdminLink('AdminProducts')}&updateproduct&id_product={$idPack|intval}&key_tab=ModulePm_advancedpack">{$packObjects[$idPack]->name|escape:'html':'UTF-8'}</a>
					<span class="panel-heading-action">
						<a class="list-toolbar-btn text-center" href="{$link->getAdminLink('AdminProducts')}&updateproduct&id_product={$idPack|intval}&key_tab=ModulePm_advancedpack"><i class="icon-edit"></i>
						</a>
					</span>
				</header>
				{assign var=imageCoverId value=Product::getCover($idPack)}
				<div class="ap5-pack-image text-center">
					<a target="_blank" href="{$link->getAdminLink('AdminProducts')}&updateproduct&id_product={$idPack|intval}&key_tab=ModulePm_advancedpack">
						{AdvancedPackCoreClass::getThumbnailImageHTML($idPack, $imageCoverId.id_image)}
					</a>
				</div>
				<div class="ap5-pack-action-buttons"></div>
				<div class="ap5-pack-content">
					<p>{l s='Pack content:' mod='pm_advancedpack'}</p>
					<ul class="list-unstyled">
				{foreach from=$packContent item='packProduct' name='packProductLoop'}
					<li{if $currentProduct->id == $packProduct['productObj']->id} class="ap5-current-product"{/if}>
						{$packProduct['quantity']|intval}x <a target="_blank" href="{$link->getProductLink($packProduct['productObj'])}">{$packProduct['productObj']->name|escape:'html':'UTF-8'}</a> ({$packProduct['productObj']->reference|escape:'html':'UTF-8'})
					</li>
				{/foreach}
					</ul>
				</div>
				<hr />
				<div class="ap5-pack-price-container text-center">
					{if $packPrice != $packOldPrice}
						<strong class="ap5-pack-price">{convertPrice price=$packPrice}</strong> - <s class="ap5-pack-old-price">{convertPrice price=$packOldPrice}</s>
					{else}
						<strong class="ap5-pack-price">{convertPrice price=$packPrice}</strong>
					{/if}
				</div>
			</div>
		</div>
	{/foreach}
{/if}
	</div>
</div>