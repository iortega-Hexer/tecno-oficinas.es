{*
* 20013-2019 Ambris

*   @author    Ambris Informatique
*   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
*   @license   Commercial license
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="jolisearch-body">
	{if (isset($categories) && count($categories) > 0) || (isset($manufacturers) && count($manufacturers) > 0)}
	<aside class="col-xs-12-12 col-sm-4-12 col-lg-3-12" role="complementary">
		{if (isset($categories) && count($categories) > 0)}
		<div class="categories">
			<h4>{l s='Categories' mod='ambjolisearch'}</h4>
			<div>
			{foreach from=$categories item=category}
				<li>
					<a href="{$category.link}" class="category-name" data-category-id="{$category.cat_id}" data-parameter-name='ajs_cat' >{$category.cat_name}</a>
					<span class="items-count">({$category.results})</span>
				</li>
			{/foreach}
			</div>
		</div>
		{/if}
		{if (isset($manufacturers) && count($manufacturers) > 0)}
		<div class="manufacturers">
			<h4>{l s='Manufacturers' mod='ambjolisearch'}</h4>
			<div>
			{foreach from=$manufacturers item=manufacturer}
				<li>
					<a href="{$manufacturer.link}" class="manufacturer-name" data-manufacturer-id="{$manufacturer.man_id}" data-parameter-name='ajs_man' >{$manufacturer.man_name}</a>
					<span class="items-count">({$manufacturer.results})</span>
				</li>
			{/foreach}
			</div>
		</div>
		{/if}
	</aside>
	{/if}
	<section class="jolisearch-content {if (isset($categories) && count($categories) > 0) || (isset($manufacturers) && count($manufacturers) > 0)}col-xs-12-12 col-sm-8-12 col-lg-9-12{else}col-xs-12-12{/if}" role="main">
		<h4>{l s='Products' mod='ambjolisearch'} <span class="items-count">{$more_results.0.results}</span></h4>
		<div>
			{foreach from=$products item=product}
				<li class="product col-xs-6-12 col-sm-6-12 col-md-4-12 col-lg-3-12" role="presentation">
					<a href="{$product.link}">
						<img src="{$product.img}" class="product-image">
						<div class="jolisearch-product">{strip}
							<span class="product-name">{strip}
								{$product.pname}
							{/strip}</span>
							{if isset($product.mname) && !empty($product.mname) && isset($settings.display_manufacturer) && $settings.display_manufacturer}
								<span class="product-manufacturer">{$product.mname}</span>
							{/if}
							{if isset($product.cname) && !empty($product.cname) && isset($settings.display_category) && $settings.display_category}
								<span class="product-category">{$product.cname}</span>
							{/if}
							{if isset($product.features) && !empty($product.features) && isset($settings.show_features) && $settings.show_features}
								<span class="product-category">{$product.features}</span>
							{/if}
							<span class="product-price">{$product.price}</span>
							{/strip}
						</div>

					</a>
				</li>
			{/foreach}
		</div>
		{if isset($more_results) && count($more_results) > 0}
			<div class="more-results col-xs-12-12">
				<a href="{$more_results.0.link}" title="{$settings.l_more_results}">{$settings.l_more_results}</a>
			</div>
		{/if}
	</section>
</div>