<script>
function ap5_setPackContentData() {
	if (typeof(ap5_cartPackProducts) == 'object') {
		for (ap5_uniquePackAttribute in ap5_cartPackProducts) {
			$('.product-line-info span:contains("' + ap5_uniquePackAttribute + '")').each(function (idx, elem) {
				var changed = $(elem).html().replace(ap5_uniquePackAttribute, ap5_cartPackProducts[ap5_uniquePackAttribute].cart);
				$(elem).html(changed);
			});
			$('#blockcart-modal .modal-body span:contains("' + ap5_uniquePackAttribute + '")').each(function (idx, elem) {
				var changed = $(elem).html().replace(ap5_uniquePackAttribute, ap5_cartPackProducts[ap5_uniquePackAttribute].cart);
				$(elem).html(changed);
			});
		}
	}
}
$(document).ready(function() {
	ap5_setPackContentData();
	$(document).ajaxSuccess(function() {
		ap5_setPackContentData();
	});
	$(document).on('ap5-After-AddPackToCart', function() {
		ap5_setPackContentData();
	});
});
</script>

{if isset($ap5_firstExecution) && $ap5_firstExecution}
<script type="text/javascript">
	$(document).ready(function() {
		$('body').addClass('ap5-pack-page-simple-mode');
		ap5Plugin.changeBuyBlock('{pm_advancedpack::getPackAddCartURL($product.id)}', {$ap5_buyBlockPackPriceContainer nofilter});
	});
	prestashop.on('updateProduct', function(e) {
		window.location.reload(true);
	});
</script>
{/if}