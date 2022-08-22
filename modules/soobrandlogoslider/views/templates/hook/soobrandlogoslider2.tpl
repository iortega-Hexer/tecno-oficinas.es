{*
* 2012-2018 PrestaSOO
*
*  @author PrestaSOO <addons@prestasoo.com>
*  @copyright  2012-2018 PrestaSOO
*  @license    Commercial License. All right reserved.
*  International Registered Trademark & Property of PrestaSOO
*}
<script type="text/javascript">
{literal}
document.addEventListener("DOMContentLoaded", function(event) {
	$(document).ready(function(){
		$('#brand_wrapper').bxSlider({
			minSlides: 3,
			maxSlides: {/literal}{$proinput1}{literal},
			auto: {/literal}{$proradio1}{literal},
			pause: {/literal}{$proinput3}{literal},
			speed: {/literal}{$proinput3}{literal},
			autoHover: {/literal}{$proradio5}{literal},
			randomStart : {/literal}{$proradio2}{literal},
			slideWidth: {/literal}{$imageSize.width}{literal},
			infiniteLoop: {/literal}{$proradio4}{literal},
			slideMargin: 20
		});
	});
})
{/literal}
</script>
<div class="footer-block col-xs-12 col-sm-12" style="clear:both">
<h4>{l s='Our Brands' mod='soobrandlogoslider'}</h4>
	<ul id="brand_wrapper">
		{foreach $brandcarousel as $brand}
		    <li><a href="{$link->getmanufacturerLink($brand.id_manufacturer, $brand.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$brand.name|escape:'htmlall':'UTF-8'}" class="lnk_img">
				<img src="{$link->getManufacturerImageLink($brand.id_manufacturer, $imageName)}" alt="{$brand.name|escape:'htmlall':'UTF-8'}" width="{$imageSize.width}" height="{$imageSize.height}" />
			</a></li>
		{/foreach}
	</ul>
</div>