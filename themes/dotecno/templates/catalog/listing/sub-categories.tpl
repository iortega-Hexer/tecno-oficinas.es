{if isset($subcategories)}
	<!-- Subcategories -->
	<div class="subcategories card card-block" id="subcategories">
		<h2 class="subcategory-heading">{l s='Subcategories'}</h2>
		<ul class="clearfix">
		{foreach from=$subcategories item=subcategory}
			<li>
				<div class="subcategory-image">
					<a href="{url entity='category' id=$subcategory.id_category id_lang=1}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
					{if $subcategory.id_image}
						<img class="replace-2x" src="{url entity='categoryImage' id=$subcategory.id_image name='medium_default'}" alt="" />
					{else}
						<img class="replace-2x" src="{$img_cat_dir}{$lang_iso}-default-medium_default.jpg" alt="" />
					{/if}
				</a>
				</div>
				<h5><a class="subcategory-name" href="{url entity='category' id=$subcategory.id_category id_lang=1}">{$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}</a></h5>
				{if $subcategory.description}
					{*** Comment subcategory.description ****}
					{*<div class="cat_desc">{$subcategory.description nofilter}</div>*}
				{/if}
			</li>
		{/foreach}
		</ul>
	</div>
	<div class="clearfix"></div>
{/if}
<style type="text/css">
	#subcategories ul li {
		float: left;
		width: 145px;
		margin: 0 0 13px 33px;
		text-align: center;
		min-height: 202px;
	}
	#subcategories ul li .subcategory-image {
		padding: 0 0 8px 0;
	}
	#subcategories ul li .subcategory-image a {
		display: block;
		padding: 9px;
		border: 1px solid #d6d4d4;
	}
	#subcategories ul li .subcategory-image a img {
		max-width: 100%;
		vertical-align: top;
	}
	#subcategories ul li .subcategory-name {
		font: 600 18px/22px "Open Sans", sans-serif;
		color: #555454;
		text-transform: uppercase;
	}
</style>
