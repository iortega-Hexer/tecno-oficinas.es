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
	<div class="products-list {if $locations == 'product_tab' && $psv >= 1.7} extratab17 {/if}" style="margin-top: 10px">
		{if $products}
			<div class="form-group">
				<ul id="divPackItems" class="list-unstyled">
						{foreach from=$products item=product}
							<li class="product-pack-item media-product-pack" style="width: 125px">
								<img class="media-product-pack-img" src="{$product['img_link']|escape:'htmlall':'UTF-8'}" style="max-width: 100%" />
								<span class="media-product-pack-title">
									{l s='Name' mod='hiupsell'}: {$product['name']|truncate:20:'..'|escape:'html':'UTF-8'}
								</span>
								<span class="media-product-pack-ref">
									{l s='REF' mod='hiupsell'}: {$product['reference']|escape:'html':'UTF-8'}
								</span>
								<a href="#" class="btn btn-default btn-primary media-product-pack-action delete-product" data-delete="{$product['id_product']|escape:'htmlall':'UTF-8'}">
									{if $locations == 'product_tab' && $psv >= 1.7}
										<i class="material-icons">delete</i>
									{else}
										<i class="icon-trash"></i>
									{/if}
								</a>
							</li>
						{/foreach}
				</ul>
			</div>
		{/if}
	</div>
{else}
	<div class="products-list {if $locations == 'product_tab' && $psv < 1.6} extratab15 {/if}">
		<div class="listOfPack">
			<ul>
				{if $products}
					{foreach from=$products item=product}
						<li>
							<img class="media-product-pack-img" src="{$product['img_link']|escape:'htmlall':'UTF-8'}"/>
							<span class="product-pack-title">
								{l s='Name'  mod='hiupsell'}: {$product['name']|truncate:12:'..'|escape:'html':'UTF-8'}
							</span>
							<span class="product-pack-ref">
								{l s='REF'  mod='hiupsell'}: {$product['reference']|escape:'html':'UTF-8'}
							</span>
							<a href="#" class="delete-product" data-delete="{$product['id_product']|escape:'htmlall':'UTF-8'}">
								<img src="../img/admin/delete.gif" alt="{l s='Deletel'  mod='hiupsell'}">
							</a>
						</li>
					{/foreach}
				{/if}
			</ul>
		</div>
	</div>
{/if}
