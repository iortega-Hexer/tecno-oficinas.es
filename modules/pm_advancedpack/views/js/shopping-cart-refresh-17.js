$(document).ready(function() {
	$('.js-cart').data('refresh-url', ap5_cartRefreshUrl);
	if (!(new String(window.location).match(/updatedTransaction/))) {
		// Lineven - additionalproductsorder
		if (typeof(lineven_apo) == 'object' && typeof(lineven_apo.datas) == 'object' && typeof(lineven_apo.datas.refresh_mode) == 'string') {
			lineven_apo.datas.refresh_mode_bkp = lineven_apo.datas.refresh_mode;
			lineven_apo.datas.refresh_mode = 'NOTHING';
		}
		prestashop.emit('updateCart', { reason: { cart: null } });
	}
});
$(document).ajaxSuccess(function(e, ajaxOptions, ajaxData) {
	$('.js-cart').data('refresh-url', ap5_cartRefreshUrl);
	// Lineven - additionalproductsorder
	if (typeof(lineven_apo) == 'object' && typeof(lineven_apo.datas) == 'object' && typeof(lineven_apo.datas.refresh_mode_bkp) == 'string') {
		lineven_apo.datas.refresh_mode = lineven_apo.datas.refresh_mode_bkp;
	}
});