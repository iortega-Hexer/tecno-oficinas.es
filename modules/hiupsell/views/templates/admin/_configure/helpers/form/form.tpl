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

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'search_product'}
		<div class="product-content">
			{if $psv >= 1.6}
				<div class="col-lg-9">
					<div class="form-wrapper">
						<div class="form-group">
							<div class="col-lg-6">
								<input type="text" id="reductionProductFilter" name="product_filter" value="" autocomplete="off" class="ac-input">
							</div>
							<div class="col-lg-2">
								<button type="button" id="add-product-item" class="btn btn-default" name="add_product_item">
									<i class="icon-plus-sign-alt"></i> {l s='Add' mod='hiupsell'}
								</button>
							</div>
						</div>
					</div>
					<div class="products-list">
						{if $products}
								<div class="form-group">
									<ul id="divPackItems" class="list-unstyled">
											{foreach from=$products item=product}
												<li class="product-pack-item media-product-pack" style="width: 125px">
													<img class="media-product-pack-img" src="{$product['img_link']|escape:'htmlall':'UTF-8'}"/>
													<span class="media-product-pack-title">
														{l s='Name' mod='hiupsell'}: {$product['name']|truncate:20:'..'|escape:'html':'UTF-8'}
													</span>
													<span class="media-product-pack-ref">
														{l s='REF' mod='hiupsell'}: {$product['reference']|escape:'html':'UTF-8'}
													</span>
													<a href="#" class="btn btn-default media-product-pack-action delete-product" data-delete="{$product['id_product']|escape:'htmlall':'UTF-8'}">
														<i class="icon-trash"></i>
													</a>
												</li>
											{/foreach}
									</ul>
								</div>
						{/if}
					</div>
				</div>
			{else}
				<fieldset id="fieldset_0">
				<div id="custom-product">
					<div>
						<input type="text" id="reductionProductFilter" name="product_filter" value="" autocomplete="off" class="ac-input">
						<button type="button" id="add-product-item" class="btn btn-default" name="add_product_item">
							<img src="../img/admin/add.gif"> {l s='Add' mod='hiupsell'}
						</button>
						<div class="products-list">
							<div class="listOfPack">
								<ul>
									{if $products}
										{foreach from=$products item=product}
											<li>
												<img class="media-product-pack-img" src="{$product['img_link']|escape:'htmlall':'UTF-8'}"/>
												<span class="product-pack-title">
													{l s='Name'  mod='hiupsell'}: {$product['name']|truncate:14:'..'|escape:'html':'UTF-8'}
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
					</div>
				</div>
				</fieldset>
			{/if}
		</div>
	{else if $input.type == 'block_search_product'}
		<div id="upsell_block_products">
			<div>
				<input type="hidden" name="inputBlockProducts" id="inputBlockProducts" value="{$products_id}" />

				<div id="ajax_choose_upsell_block_product" class="col-lg-6">
						<input type="text" id="upsell_block_product_search" name="upsell_block_product_search" value="" autocomplete="off" class="ac-input">
				</div>
				<div class="col-lg-2">
					<button type="button" id="add-upsell-block-product" class="btn btn-default" name="add-upsell-block-products">
						<i class="icon-plus-sign-alt"></i> {l s='Add' mod='hiupsell'}
					</button>
				</div>
				<div id="upsellproducts" class="col-lg-12 col-lg-offset-3">
					{foreach from=$product_content item=product}
						<div class="form-control-static">
							<button type="button" class="btn btn-default deleteblockproduct" data-id-product="{$product['id_product']}">
								<i class="icon-remove text-danger"></i>
							</button>
							{$product['name']|escape:'html':'UTF-8'}
						</div>
					{/foreach}
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function(){
					$('#upsell_block_product_search').autocomplete(upsell_module_controller_dir+"&ajax=1", 
				    {
				        minChars: 2,
				        max: 50,
				        width: 500,
				        formatItem: function (data) {
				            return data[0]+ '. '+data[2] + '-' + data[1];
				        },
				        scroll: false,
				        multiple: false,
				        extraParams: {
				            action : 'product_search',
				            id_lang : id_lang,
				            secure_key : upsell_secure_key,
				        }
				    });
				});
			</script>
		</div>
	{/if}
	{$smarty.block.parent}
{/block}
