
/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * handle envent
 * @param {json} options
 * @returns {PriceTable}
 */
var PriceTable = function (options)
{
	/**
	 * contain all products accessories
	 */
	this.products = typeof options.products !== 'undefined' ? options.products : null;
        
        /**
         * Contain all text translate of js price table
         */
	this.jsTranslateText = typeof options.jsTranslateText !== 'undefined' ? options.jsTranslateText : null;

	/**
	 * contain current id product
	 */
	this.randomMainProductId = typeof options.randomMainProductId !== 'undefined' ? options.randomMainProductId : null;

	/**
	 * contain text translate "sub total"
	 */
	this.subTotal = typeof options.subTotal !== 'undefined' ? options.subTotal : null;

	/**
	 * Change the main price when adding or removing accessories
	 */
	this.changeMainPrice = typeof options.changeMainPrice !== 'undefined' ? options.changeMainPrice : 0;

	/**
	 * Show table price
	 * 1 => show
	 * 0 => hide
	 */
	this.showTablePrice = typeof options.showTablePrice !== 'undefined' ? options.showTablePrice : 0;
        
	/**
	 * Show combination info in table price
	 * 1 => show
	 * 0 => hide
	 */
	this.showCombination = typeof options.showCombination !== 'undefined' ? options.showCombination : 0;
        
        /**
	 * Allow change accessory quantity & main product quantity together
	 * 0, 1 => Sync accessory quantity & main product quantity (change together)
	 * 2 => Keep the old logic. change accessory quantity & main product quantity separate
	 */
	this.syncAccessoryQuantity = typeof options.syncAccessoryQuantity !== 'undefined' ? options.syncAccessoryQuantity : 0;
        
        /**
         * Scroll to table price when selected an accessory
         * 1 => Enable scrolling
         * 0 => Disenable scrolling
         */
	this.isScrollingToTablePrice = typeof options.isScrollingToTablePrice !== 'undefined' ? options.isScrollingToTablePrice : 0;

	/**
	 * Define warning accessory is out of stock
	 */
	this.warningOutOfStock = options.warningOutOfStock;
	
	/**
	 * Define warning accessory is out of stock
	 */
	this.warningNotEnoughProduct = options.warningNotEnoughProduct;
        
        /**
         * Define warning accessory is out of stock
         */
        this.warningCustomQuantity = options.warningCustomQuantity;
        
        /**
         * Show Option Image
         * 1 => show
         * 0 => hide
         */
        this.showOptionImage = options.showOptionImage;

	/**
	 * contain all selected products
	 */
	this.accessories = {};

	PriceTable.instance = this;

	/**
	 * Define array selectors
	 */
	this._selectors = {
		idCombination : '#idCombination', // define id combination
		mainProductPrice : '#our_price_display', // the box which contains official price of the main item
		accessoryItem: '.accessory_item', // Class of input which select accessory if you want add this accessory to cart.
		classNameAccessoryItem: 'accessory_item', // Class name of input which select accessory if you want add this accessory to cart.
		accessoriesGroup : '.accessories_group', // define class selecte box accessories
		groupAccessories : '#group_accessories', // define id group accessories
		classNameAccessoriesGroup : 'accessories_group', // define class name accessories group
		accessoriesTablePriceContent : '.accessories_table_price_content', // define class table contain price table
		accessoriesCustomQuantity : '.custom_quantity', // define class name of custom quantity of each accessory
		productAttributeColor: '.color_pick',
		productAttributeSelect: '.attribute_select',
		productAttributeRadio: '.attribute_radio',
		quantityWanted : '#quantity_wanted', // defined class input product quantity
		classChangeQuantity : '#product .product_quantity_down, #product .product_quantity_up', // defined class input change quantity
		productCombination : '#group_accessories .product-combination', // defined class select option change combination
		classProductCombination: '.product-combination', // defined class select option change combination
                classProductDDSlick: 'ddproductslick',                
		classError : 'error-number', // define class name error,
		accessoriesGroupCombination : '#group_accessories .accessories_group_combination', // define class div combination
		classProductImgLink : '.product_img_link', // define class product image link
                idAddToCartButton: '#add_to_cart',
                classMessageError: '.message_error',
                iconShowBlockGroup: '#group_accessories .option-row h4',
                iconExpand: 'icon_expand', // Name of icon expand +
                iconCollapse: 'icon_collapse', // Name of icon collapse -
                contentGroup: 'content_group', // Name of class content group
                accessoryGroup: '.option-row',
                accessoryBlockQty: '.ma_block_qty',
                accessoryQtyUpButton: '.ma_block_qty .bootstrap-touchspin-up',
                accessoryQtyDownButton: '.ma_block_qty .bootstrap-touchspin-down',
	};

	/**
	 * Check a value is interger number or not
	 * @param {string} value
	 * @returns {@exp;reg@call;test}
	 */
	this.isIntegerNumber = function (value) {
		var reg = /^\d+$/;
		return reg.test(value);
	};

	/**
	 * Event load default
	 */
	this.onLoad = function ()
	{
		if ($.isEmptyObject(this.products)) {
                    return;
                }
                var idCombination = parseInt($(PriceTable.instance._selectors.idCombination).val());
                if (idCombination > 0) {
                    idCombination = isNaN(idCombination) ? 0 : idCombination;
                    PriceTable.instance.products[PriceTable.instance.randomMainProductId].default_id_product_attribute = idCombination;
                }
                PriceTable.instance._syncQuantity(PriceTable.instance._getMainProductQty());
                PriceTable.instance.triggerTablePrice();
		
                this._renderCombinations();
                $(".dd-options li").change(function(){          
                    var value = $(".dd-options li option:selected").find('span').hasClass('warning_out_of_stock');
                    if (value)
                        $(".dd-options li option:selected").attr('disabled','disabled');
                });
		// update table price
		$(document).on('change', this._selectors.idCombination, function () {
                        PriceTable.instance.products[PriceTable.instance.randomMainProductId].default_id_product_attribute = $(this).val();
                        PriceTable.instance._syncQuantity(PriceTable.instance._getMainProductQty());
                        PriceTable.instance.scrollToTablePrice();
			PriceTable.instance.triggerTablePrice();

		});
       
		// change combination
		$(document).on('click', this._selectors.iconShowBlockGroup, function () {
                        var element = $(this).find('i');
			PriceTable.instance._onClickBlockGroup(element);
		});
		// change combination
		$(document).on('focus', this._selectors.productCombination, function () {
			previousValueOfCombination = $(this).val();
		});
		$(document).on('click', '.hsma_customize', function (e) {
                    e.preventDefault();
                    var randomId = $(this).data('randomid');
                    if (typeof randomId === 'undefined' || typeof PriceTable.instance.products[randomId] === 'undefined') {
                        alert(PriceTable.instance.jsTranslateText.an_error_occurred_while_showing_the_accessory_customization);
                        return;
                    }
                    hsmaCustomizationPopUp = new HsmaCustomizationPopUp();
                    hsmaCustomizationPopUp.setIdAccessory(PriceTable.instance.products[randomId]['id_accessory']);
                    hsmaCustomizationPopUp.setRandomId(randomId);
                    hsmaCustomizationPopUp.setAjaxUrl(ajaxRenderAccessoriesUrl);
                    hsmaCustomizationPopUp.setTitlePopup(PriceTable.instance.jsTranslateText.accessory_customization);
                    hsmaCustomizationPopUp.setContentPopup(PriceTable.instance._getCustomzationPopupContent(randomId));
                    hsmaCustomizationPopUp.setIdGroup(PriceTable.instance.products[randomId]['id_accessory_group']);
                    hsmaCustomizationPopUp.show();
		});
                
		$(document).on('click', '.hsma_package', function (e) {
                    e.preventDefault();
                    var randomId = $(this).data('randomid');
                    var idAccessories = $(this).data('idaccessory');
                    if (typeof randomId === 'undefined' || typeof idAccessories === 'undefined' || typeof PriceTable.instance.products[randomId] === 'undefined') {
                        alert(PriceTable.instance.jsTranslateText.an_error_occurred_while_showing_the_list_of_products_in_the_package);
                        return;
                    }
                    PriceTable.instance._showPackageContent(idAccessories);
		});
                
                
		$(document).on('change', this._selectors.productCombination, function () {
                        PriceTable.instance.scrollToTablePrice();
			var randomId = $(this).data('randomid');
			var idProductAttribute = parseInt($(this).val());
			customQuantity = $(this).parent().parent().find(PriceTable.instance._selectors.accessoriesCustomQuantity).val();
			customQuantity = typeof customQuantity !== 'undefined' ? customQuantity : PriceTable.instance.products[randomId]['qty'];
			var parentsElement = $(this).parents('tr');
			if (!PriceTable.instance.products[randomId]['combinations'][idProductAttribute]['out_of_stock'] && PriceTable.instance.products[randomId]['combinations'][idProductAttribute]['avaiable_quantity'] < customQuantity)
			{
				$(this).val(previousValueOfCombination);
				alert(PriceTable.instance.warningOutOfStock);
                                window.getSelection().removeAllRanges();
			}
			else
			{
				previousValueOfCombination = $(this).val();
				PriceTable.instance.products[randomId].default_id_product_attribute = idProductAttribute;
				PriceTable.instance._updateMainProductPrice();
				PriceTable.instance._renderTablePrice();
				PriceTable.instance._updateAccessoryPrice(PriceTable.instance.products[randomId]);
				PriceTable.instance._updateProductCombinationImage(parentsElement, PriceTable.instance.products[randomId]);
			}
			

		});

		// Event click on an accessory item
		$(document).on('click', this._selectors.accessoryItem, function() {
                        var selectedValue = $(this).val();
                        if (selectedValue != 0) {
                            PriceTable.instance.scrollToTablePrice();
                        }
                        var idGroup = $(this).data('groupid');
			if (parseInt(window.buyTogetherOption[idGroup]) === parseInt(adminProductSetting.BUY_TOGETHER_YES))
			{
                            if ($(this).parents('table').find(PriceTable.instance._selectors.accessoryItem + ':checked').length === 0) {
                                $('<span class="message_error">'+ alertMessage +'</span>').insertBefore($(this).parents('table'));
                            } else {
                                $(this).parents('table').prev(PriceTable.instance._selectors.classMessageError).remove();
                                PriceTable.instance.scrollToTablePrice();
                            }
			} else if (parseInt(window.buyTogetherOption[idGroup]) === parseInt(adminProductSetting.BUY_TOGETHER_REQUIRED) && $(this).attr('type') === 'checkbox') {
                            if (parseInt($(this).data('required-buy-together')) === 1)
                            {       
                                    alert(alertMessage);
                                    window.getSelection().removeAllRanges();
                                    $(this).parent().addClass('checked');
                                    $(this).attr("checked", "checked");
                            }
                            PriceTable.instance.scrollToTablePrice();
			}
                        if ($(this).is(':checked')) {
                            currentQty = parseInt($(this).parents('tr').find(PriceTable.instance._selectors.accessoriesCustomQuantity).val());
                            // Set custom quantity to 1 if an accessory is selected
                            $(this).parents('tr').find(PriceTable.instance._selectors.accessoriesCustomQuantity).val(currentQty > 0 ? currentQty : 1);
                        }
			PriceTable.instance._initProductAccessories();
			PriceTable.instance._renderTablePrice();                       
		});

		// Change main product's combination
		$(document).on('click', this._selectors.productAttributeColor, function(){PriceTable.instance._renderTablePrice(true);});
		$(document).on('click', this._selectors.productAttributeRadio, function(){PriceTable.instance._renderTablePrice(true);});
		$(document).on('change', this._selectors.productAttributeSelect, function(){PriceTable.instance._renderTablePrice(true);});
                
                /* Event click on the button accessory quantity plus*/
                $(document).on('click', this._selectors.accessoryQtyUpButton, function () {
                    var inputQuantity = $(this).parents(PriceTable.instance._selectors.accessoryBlockQty).find(PriceTable.instance._selectors.accessoriesCustomQuantity);
                    var currentQty = parseInt(inputQuantity.val());
                    inputQuantity.val(currentQty + 1);
                    PriceTable.instance._isStockAvailable(inputQuantity);
                });
                
                /* Event click on the button accessory quantity minus*/
                $(document).on('click', this._selectors.accessoryQtyDownButton, function () {
                    var inputQuantity = $(this).parents(PriceTable.instance._selectors.accessoryBlockQty).find(PriceTable.instance._selectors.accessoriesCustomQuantity);
                    var currentQty = parseInt(inputQuantity.val());
                    var newQty = currentQty - 1 > 1 ? currentQty - 1 : 1;
                    inputQuantity.val(newQty);
                    PriceTable.instance._isStockAvailable(inputQuantity);
                });
                
		//$(this._selectors.accessoriesGroup).data('pre', $(this).val());
		$(this._selectors.accessoriesGroup).on('focus', function() {
			/* Store the current value on focus and on change*/
			previousAccessory = this.value;
		}).change(function() {
                        PriceTable.instance.scrollToTablePrice();
			var randomId = $(this).find(':selected').data('randomid');
			if (typeof randomId !== 'undefined')
			{
				var customQuantity = PriceTable.instance.products[randomId].custom_quantity ? PriceTable.instance.products[randomId].custom_quantity : PriceTable.instance.products[randomId].default_quantity;
                                var idProductAttribute = this.options[this.selectedIndex].getAttribute('data-id-product-attribute');
				var isOutOfStock = false;
				if (!PriceTable.instance.products[randomId]['out_of_stock'] && PriceTable.instance.products[randomId]['avaiable_quantity'] < customQuantity)
					isOutOfStock = true;
				
				if (typeof idProductAttribute !== 'undefined' &&
					typeof PriceTable.instance.products[randomId]['combinations'][idProductAttribute] !== 'undefined' &&
					!PriceTable.instance.products[randomId]['combinations'][idProductAttribute]['out_of_stock'] &&
					PriceTable.instance.products[randomId]['combinations'][idProductAttribute]['avaiable_quantity'] < customQuantity
					)
					isOutOfStock = true;

				if (isOutOfStock)
				{
					$(this).val(previousAccessory);
					alert(PriceTable.instance.warningOutOfStock);
                                        window.getSelection().removeAllRanges();
				}
				else
				{       
                                    if (typeof PriceTable.instance.products[randomId] !== 'undefined' && Object.size(PriceTable.instance.products[randomId]['combinations']) > 1) {
                                        PriceTable.instance._renderCombination(randomId, false);
                                    } else {
                                        $(this).next().html('');
                                    }
                                    previousAccessory = this.value;
				}
			} else {
				$(this).next().html('');
				previousAccessory = this.value;
			}
			PriceTable.instance._initProductAccessories();
			PriceTable.instance._renderTablePrice();
		});

		// Event change the quantity of main product
		$(document).on('keyup', this._selectors.quantityWanted, function () {
                    var qty = $(this).val();
                    isIntegerNumber = PriceTable.instance.isIntegerNumber(qty);
                    if (!isIntegerNumber) {
                        $(this).addClass(PriceTable.instance._selectors.classError);
                        $(this).select();
                    } else {
                        $(this).removeClass(PriceTable.instance._selectors.classError);
                    }
                    PriceTable.instance._syncQuantity(qty);
                    PriceTable.instance.triggerTablePrice();
		});
                
                // up & down qty input type number button on PS 1.6
		$(document).on('click', this._selectors.quantityWanted, function () {
                     PriceTable.instance.triggerTablePrice();
                    
		});

		// up & down qty button on PS 1.6
		$(document).on('click', this._selectors.classChangeQuantity, function () {
                    PriceTable.instance.triggerTablePrice();
                       
		});

		if ($(this._selectors.groupAccessories + ' ' + this._selectors.accessoriesCustomQuantity).length > 0)
		{
                    /* Event change the custom quantity value */
                    $(this._selectors.accessoriesCustomQuantity).on('focus', function() {
                        /* Store the current value on focus and on change */
                        previousAccessoryQuantity = this.value;
                    }).change(function() {
                        PriceTable.instance._isStockAvailable(this);
                    });
		}

		// Event show fancybox when customer click product image
		$(document).on('click', this._selectors.classProductImgLink, function(e) {
			$(PriceTable.instance._selectors.classProductImgLink).fancybox({
				'hideOnContentClick': false
			});
		});
		
	};
        
        /**
        * Check stock available when changing accessory quantity
        * @param {object} element
        */
        this._isStockAvailable = function (element) {
            var newQuantity = parseInt($(element).val());
            var randomId = 0;
            var idCombination = 0;
            /* in calse dropdown + show image are enabled   */
            if ($(element).parent().parent().find('.dd-selected-value').length > 0) {
                randomId = $(element).parents('td').find('.randomid-group').data('randomid');
                var idGroup = $(element).parents('td').find('.randomid-group').data('idgroup');
                idCombination = $('#combination_' + idGroup).find('.dd-selected-value').val();
            } else {
                randomId = typeof $(element).parents('tr').find(':checked').data('randomid') !== 'undefined' ? $(element).parents('tr').find(':checked').data('randomid') : $(element).parents('tr').find(PriceTable.instance._selectors.accessoryItem).data('randomid');
                idCombination = typeof $(element).parents('tr').find(':checked').data('id-product-attribute') !== 'undefined' ? $(element).parents('tr').find(':checked').data('id-product-attribute') : $(element).parents('tr').find(PriceTable.instance._selectors.accessoryItem).data('id-product-attribute');
            }
            if (typeof randomId === 'undefined' || randomId === 0) {
                return;
            }
            var minQuantity = parseInt(PriceTable.instance.products[randomId]['min_quantity']);
            if (newQuantity < minQuantity) {
                $(element).val($(element).data('custom-quantity'));
                alert(PriceTable.instance.warningCustomQuantity.format(minQuantity));
                window.getSelection().removeAllRanges();
                return;
            }
            /*var isStockAvailable = PriceTable.instance.isStockAvailable(randomId, newQuantity, idCombination);*/
            idCombination = typeof idCombination !== 'undefined' && idCombination > 0 ? idCombination : PriceTable.instance.products[randomId].default_id_product_attribute;
            var idAccessory = PriceTable.instance.products[randomId]['id_accessory'];
            if (!idAccessory && typeof window.ajaxRenderAccessoriesUrl === 'undefined') {
                return;
            }
            var currentThis = element;
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: window.ajaxRenderAccessoriesUrl,
                async: true,
                cache: false,
                dataType: "json",
                data: {
                    'ajax': true,
                    'id_accessory': idAccessory,
                    'id_accessory_combination': idCombination,
                    'new_quantity': newQuantity,
                    'action': 'isStockAvailable'
                },
                success: function (jsonData) {
                    if (!jsonData.hasError) {
                        isIntegerNumber = PriceTable.instance.isIntegerNumber(newQuantity);
                        if (!isIntegerNumber) {
                            $(currentThis).addClass(PriceTable.instance._selectors.classError);
                            $(currentThis).select();
                        } else {
                            $(currentThis).removeClass(PriceTable.instance._selectors.classError);
                            PriceTable.instance.products[randomId]['custom_quantity'] = newQuantity;
                            $(currentThis).data('custom-quantity', newQuantity);
                        }
                    } else {
                        var avaiableQuantity = PriceTable.instance.products[randomId]['avaiable_quantity'];
                        if (idCombination > 0) {
                            avaiableQuantity = PriceTable.instance.products[randomId]['combinations'][idCombination]['avaiable_quantity'];
                        }
                        $(currentThis).val(avaiableQuantity);
                        $(currentThis).data('custom-quantity', avaiableQuantity);
                        alert(jsonData.errors);
                        window.getSelection().removeAllRanges();
                    }
                },
                complete: function () {
                    if (parseInt($(currentThis).val()) > 0) {
                        $(currentThis).parents('tbody').find('.radio').find('span').removeClass('checked');
                        $(currentThis).parents('tbody').find('.radio').find('span input').prop('checked', false);
                        $(currentThis).parents('tr').find('.checker, .radio').find('span').addClass('checked');
                        $(currentThis).parents('tr').find('.checker, .radio').find('span input').prop('checked', true);
                        /* For prestashop 1.7 */
                        $(currentThis).parents('tr').find('.accessory_item').prop('checked', true);
                        /*$(currentThis).parents('tbody').find('.accessory_item').prop('checked', true);*/
                    } else {
                        $(currentThis).parents('tr').find('.checker, .radio').find('span').removeClass('checked');
                        $(currentThis).parents('tr').find('.checker, .radio').find('span input').prop('checked', false);
                        /* For prestashop 1.7 */
                        $(currentThis).parents('tr').find('.accessory_item').prop('checked', false);
                    }
                    PriceTable.instance._initProductAccessories();
                    PriceTable.instance._renderTablePrice();
                    PriceTable.instance._updateAccessoryPrice(PriceTable.instance.products[randomId]);
                    PriceTable.instance.scrollToTablePrice();
                }
            });
        };
    
        this._syncQuantity = function (newQuantity)
        {
            if (PriceTable.instance.syncAccessoryQuantity == 2) {
                return;
            }
            $.each(PriceTable.instance.products, function (randomId, product) {
                var idGroup = PriceTable.instance.products[randomId]['id_accessory_group'];
                var idGroup_idAccessories = idGroup + '_' + PriceTable.instance.products[randomId]['id_accessory'];
                var newAccessoryQuantity = newQuantity * product['default_quantity'];
                if (PriceTable.instance.products[randomId].is_available_when_out_of_stock == 0 && newAccessoryQuantity > PriceTable.instance.products[randomId]['avaiable_quantity']){
                    $('#accessories_proudct_' + idGroup_idAccessories).attr('disabled','disabled');
                    $('#accessories_proudct_' + idGroup_idAccessories).parent('div').addClass('disabled');
                } else {
                    $('#accessories_proudct_' + idGroup_idAccessories).removeAttr('disabled');
                    $('#accessories_proudct_' + idGroup_idAccessories).parent('div').removeClass('disabled');
                    PriceTable.instance.products[randomId]['qty'] = newQuantity * product['default_quantity'];
                    $('#quantity_' + idGroup_idAccessories).val(PriceTable.instance.products[randomId]['qty']);
                    $('#quantity_' + idGroup_idAccessories).data('custom-quantity', PriceTable.instance.products[randomId]['qty']);
                }
                PriceTable.instance._updateAccessoryPrice(PriceTable.instance.products[randomId]);
            });
        };
	/**
	 * Get all selected products if block accessories
	 */
	this._initProductAccessories = function ()
	{
		this.accessories = {};
		var selector = this._selectors;
                var qty = PriceTable.instance._getMainProductQty();
                // in case option image for dropdown is enable    
                if ($(selector.groupAccessories).find('.dd-selected-value')) {
                    $(selector.groupAccessories + ' .randomid-group').each(function () {
                        var randomId = $(this).data('randomid');
                        if (typeof PriceTable.instance.products[randomId] !== 'undefined') {
                            var quantity = PriceTable.instance.products[randomId].custom_quantity ? PriceTable.instance.products[randomId].custom_quantity : PriceTable.instance.products[randomId].default_quantity;
                            $(this).parent().find(PriceTable.instance._selectors.accessoriesCustomQuantity).val(quantity);
                            PriceTable.instance.accessories[randomId] = PriceTable.instance.products[randomId];
                            var idGroup = $(this).data('idgroup');
                            /* disable reupdate accessory quantity*/
                            if (PriceTable.instance.syncAccessoryQuantity == 2) {
                                if ($('#product' + ' ' + selector.groupAccessories + ' ' + selector.accessoriesCustomQuantity).length > 0) {
                                    qty = $('#quantity_' + idGroup).val();
                                }
                                if (qty >= 0) {
                                    PriceTable.instance.products[randomId]['qty'] = qty; 
                                }
                            } else {
                                var newAccessoryQuantity = qty * PriceTable.instance.products[randomId]['default_quantity'];
                                if (PriceTable.instance.products[randomId].is_available_when_out_of_stock == 0 && newAccessoryQuantity > PriceTable.instance.products[randomId]['avaiable_quantity']) {
                                    /* Do nothing*/
                                } else {
                                    PriceTable.instance.products[randomId]['qty'] = newAccessoryQuantity;
                                    PriceTable.instance.products[randomId]['custom_quantity'] = PriceTable.instance.products[randomId]['qty'];
                                    
                                }
                            }
                            $('#quantity_' + idGroup).val(PriceTable.instance.products[randomId]['qty']);
                            $('#quantity_' + idGroup).data('custom-quantity', PriceTable.instance.products[randomId]['qty']);
                        }
                    });
                }                        
                
		if ($(selector.groupAccessories).find('select').hasClass(this._selectors.classNameAccessoriesGroup))
		{
			$(PriceTable.instance._selectors.accessoriesGroup).each(function ()
			{
				var randomId = $(this).find(':selected').data('randomid');
				if (typeof PriceTable.instance.products[randomId] !== 'undefined')
				{
					var quantity = PriceTable.instance.products[randomId].custom_quantity ? PriceTable.instance.products[randomId].custom_quantity : PriceTable.instance.products[randomId].default_quantity;
					$(this).prev().val(quantity);
					PriceTable.instance.accessories[randomId] = PriceTable.instance.products[randomId];

					if ($('#product' + ' ' + selector.groupAccessories + ' ' + selector.accessoriesCustomQuantity).length > 0) {
                                            qty = parseInt($('#quantity_' + $(this).attr('name').replace('accessory_', '')).val());
                                        }
                                        if (qty >= 0) {
                                            PriceTable.instance.products[randomId]['qty'] = qty;
                                        }
                                        
                                        $('#quantity_' + $(this).attr('name').replace('accessory_', '')).data('custom-quantity', PriceTable.instance.products[randomId]['qty']);
                                        $('#quantity_' + $(this).attr('name').replace('accessory_', '')).val(PriceTable.instance.products[randomId]['qty']);    
					if (!PriceTable.instance.showOptionImage && Object.size(PriceTable.instance.products[randomId]['combinations']) > 1)
						PriceTable.instance._renderCombination(randomId, false);
                                        if (!PriceTable.instance.showOptionImage) {
                                            PriceTable.instance._renderCustomization(randomId, PriceTable.instance.products[randomId]['id_accessory_group']);
                                        }
				}
				
			});
		}
                
		if ($(selector.groupAccessories).find('input').hasClass(this._selectors.classNameAccessoryItem))
		{
			$(PriceTable.instance._selectors.accessoryItem + ':checked').each(function(i)
			{
				var randomId = $(this).data('randomid');
				if (typeof PriceTable.instance.products[randomId] !== 'undefined')
				{
					PriceTable.instance.accessories[randomId] = PriceTable.instance.products[randomId];
					if ($('#product' + ' ' + selector.groupAccessories + ' ' + selector.accessoriesCustomQuantity).length > 0)
						qty = parseInt($('#quantity_' + $(this).attr('id').replace('accessories_proudct_', '')).val());

					if (qty >= 0)
						PriceTable.instance.products[randomId]['qty'] = qty;
                                            
                                         $('#quantity_' + $(this).attr('id').replace('accessories_proudct_', '')).val(PriceTable.instance.products[randomId]['qty']);        
				}
			});
		}
		
		this.accessories[PriceTable.instance.randomMainProductId] = PriceTable.instance.products[PriceTable.instance.randomMainProductId];

	};

	/**
	 * update main product price when change combination
	 */
        this._updateMainProductPrice = function ()
        {
            if (typeof productPrice === 'undefined') {
                return;
            }
            // in this case, the current product does not have any combination, so no need to update price of the main item
            // refer to product.js::updatePrice()

            if (typeof priceWithDiscountsDisplay === 'undefined') {
                priceWithDiscountsDisplay = productPrice;
            }
            idCombination = PriceTable.instance.products[PriceTable.instance.randomMainProductId]['default_id_product_attribute'];
            PriceTable.instance.products[PriceTable.instance.randomMainProductId]['combinations'][idCombination]['price'] = priceWithDiscountsDisplay;
            var qty = PriceTable.instance._getMainProductQty();
            if (typeof qty !== 'undefined') {
                if (PriceTable.instance.isIntegerNumber(qty) && parseInt(qty) >= 0) {
                    PriceTable.instance.products[PriceTable.instance.randomMainProductId]['qty'] = parseInt(qty);
                }
            }
        };
        
        /**
         * Get main product quantity
         * @returns {Number}
         */
        this._getMainProductQty = function ()
        {
            var quantityWanted = parseInt($(PriceTable.instance._selectors.quantityWanted).val());
            if (isNaN(quantityWanted)) {
                quantityWanted = 1;
            }
            return quantityWanted;
        };

	/**
	 * render price table
	 */
	this._renderTablePrice = function (forceToChangeMainPrice)
	{
		if (typeof forceToChangeMainPrice === 'undefined')
			forceToChangeMainPrice = false;
                    
		if ($.isEmptyObject(this.accessories))
			return;
		var priceTable = '';
		var underline = '';
		var totalPrice = 0;     
		$.each(this.accessories, function (randomid, product)
		{
                        PriceTable.instance._updateAccessoryPrice(PriceTable.instance.products[randomid]);
			var productPrice = 0;
                        var combinationName = '';
			$.each(product.combinations, function (idProductAttribute, combination) {
				if (typeof combination !== 'undefined' && (parseInt(product.default_id_product_attribute) === parseInt(idProductAttribute) || idProductAttribute == 0)) {
					productPrice = product.qty * combination.price;
                                        if (idProductAttribute > 0) {
                                            combinationName = combination.name;
                                        }
                                    }
				if (!$.isEmptyObject(combination.specific_prices))
				{
                                    $.each(combination.specific_prices, function (fromQty, specificPrice) {
                                        if (parseInt(product.qty) >= parseInt(fromQty)) {
                                            productPrice = product.qty * specificPrice;
                                        }
                                    });
				}
                                if (combination.is_cart_rule && (parseInt(product.default_id_product_attribute) === parseInt(idProductAttribute) || idProductAttribute == 0)) {
                                    productPrice = product.qty * combination.final_price;
                                }
			});
			var outOfStockWarningIcon = PriceTable.instance._renderOutOfStockWarningIcon(product);
			underline = randomid === PriceTable.instance.randomMainProductId ? 'style="text-decoration: underline;"' : '';
			totalPrice += productPrice;
                        var blockCombinationName = (combinationName && PriceTable.instance.showCombination) ? '<span class="ma_accessory_combination_name" title="' + combinationName + '">' + combinationName + '</span>' : '';
			priceTable = priceTable + '<tr>' +
					'<td class="left-column" ' + underline + '><span class="ma_accessory_name" title="' + product.name + '">' + product.qty + ' x ' + product.name + ':</span>' + blockCombinationName + '</td>' +
				'<td class="right-column">' + formatCurrency(productPrice, currencyFormat, currencySign, currencyBlank) + outOfStockWarningIcon + '</td>' +
					'</tr>';
		});

		var totals = formatCurrency(totalPrice, currencyFormat, currencySign, currencyBlank);
		priceTable = priceTable + '<tr>' +
				'<td class="left-column-total">' + this.subTotal + ':</td>' +
				'<td class="right-column-total">' + totals + '</td>' +
				'</tr>';

		if (parseInt(this.showTablePrice) === 1)
			$(this._selectors.accessoriesTablePriceContent).html(priceTable);

		if (this.changeMainPrice)
			$(this._selectors.mainProductPrice).html(totals);
	};

	/**
	 * Render out of stock warning icon
	 * @param {object} product
	 * @returns {string}
	 */
	this._renderOutOfStockWarningIcon = function(product)
	{
            var outOfStockWarningIcon = '<span title="'+ product.available_later +'" class="warning_out_of_stock"></span>';
            var idProductAttribute = product.default_id_product_attribute;
            return (product.combinations[idProductAttribute].is_available_when_out_of_stock && isShowIconOutOfStock)? outOfStockWarningIcon : '';
	};

	/**
	 * Render list combinations of products
	 */
	this._renderCombinations = function ()
	{
		if ($(this._selectors.groupAccessories).find('select').hasClass(this._selectors.classNameAccessoriesGroup))
		{       
                        if (this.showOptionImage)
                        {
                            this._renderProductOptionImage();
                        }                        
			$(PriceTable.instance._selectors.accessoriesGroup).each(function ()
			{
				var randomId = $(this).val();
				if (typeof PriceTable.instance.products[randomId] !== 'undefined' && Object.size(PriceTable.instance.products[randomId]['combinations']) > 1)
					PriceTable.instance._renderCombination(randomId, false);
			});

		}
		if ($(this._selectors.groupAccessories).find('input').hasClass(this._selectors.classNameAccessoryItem))
		{   
			$.each(this.products, function (randomId, product) {
                            if (Object.keys(product.combinations).length > 1)
                                PriceTable.instance._renderCombination(randomId, true);
			});
		}
		
	};
        
        /**
         * Render text customization when load page or change accessory
         * Only apply for dropdown type, checkbox + radio defined in html file
         * @param {string} randomId
         * @param {int} idGroup
         * @returns {html}
         */
        this._renderCustomization = function (randomId, idGroup)
        {
            if (typeof randomId === 'undefined' || typeof PriceTable.instance.products[randomId] === 'undefined') {
                return;
            }
            var product = this.products[randomId];
            var blockHasCustomization = '';
            if (typeof product.customization !== 'undefined' && !$.isEmptyObject(product.customization)) {
                var hsmaJsi18n = PriceTable.instance.jsTranslateText;
                var classHide = product.is_enough_customization ? 'hide' : '';
                blockHasCustomization = '<a class="hsma_customize accessory_customization_' + product.id_accessory + '"  data-id_accessory="' + product.id_accessory + '" data-randomid ="' + randomId + '" title="' + hsmaJsi18n.add_customization_data + '">' + hsmaJsi18n.customize + '<input type="hidden" name="hsma_id_customization" class="hsma_id_customization" data-isenoughcustomization="'+product.is_enough_customization+'" value="'+product.id_customization+'"><span class="hsma_warning_red ' + classHide + '" title="' + hsmaJsi18n.please_fill_the_required_custom_fields_to_complete_the_sale + '"></span></a>';
            }
            $('.hsma_customize_group_' + idGroup).html(blockHasCustomization);
        };
        
        this._getCustomzationPopupContent = function (randomId)
        {
            if (typeof randomId === 'undefined') {
                return;
            }
            var product = this.products[randomId];
            if (typeof product.customization === 'undefined' || Object.keys(product.customization).length == 0) {
                return;
            }
            var hsmaJsi18n = PriceTable.instance.jsTranslateText;
            var popupContent = '<form method="post" action="" enctype="multipart/form-data" id="hsma_add_accessory_customization"><section class="product-customization hsma_block_customization accessory_customizations_modal_' + product.id_accessory + '">';
            popupContent += '<div class="hsma_show_error"></div><div class="card card-block"><span class="msg_warning">' + hsmaJsi18n.dont_forget_to_save_your_customization_to_be_able_to_add_to_cart+'</span>';
            popupContent += '<ul class="clearfix">';
            $.each(product.customizations, function (key, fields) {
                if (typeof fields !== 'undefined' && Object.keys(fields).length > 0) {
                    $.each(fields, function (index, field) {
                        var class_required = parseInt(field.required) === 1 ? 'is_required' : '';
                        var is_required = parseInt(field.required) ? 'required' : '';
                        var texRequired = parseInt(field.required) ? '<sup>*</sup>' : '';
                        popupContent += '<li class="product-customization-item">';
                        popupContent += '<label>' + field.label + texRequired + '</label>';
                        if (field.type == 'text') {
                            var fieldText = field.text !== '' ? field.text : '';
                            popupContent += '<textarea placeholder="' + hsmaJsi18n.your_message_here + '" class="product-message hsma_accessory_text_field ' + class_required + '" maxlength="250" ' + is_required + ' name="' + field.input_name + '">' + fieldText + '</textarea>';
                            popupContent += '<small class="float-xs-right">' + hsmaJsi18n.char_max + '</small>';
                        } else if (field.type == 'image') {
                            if (field.is_customized) {
                                popupContent += '<br><img src="' + field.image.small.url + '">';
                                popupContent += '<a class="hsma_remove_image" data-idaccessory="' + product.id_accessory + '" data-idcustomizationfield="' + field.id_customization_field + '" rel="nofollow">' + hsmaJsi18n.remove_image + '</a>';
                            }
                            popupContent += '<input class="hs_ma_file-input hs_ma_js-file-input ' + class_required + ' hsma_accessory_file_input" ' + is_required + ' type="file" name="' + field.input_name + '">';
                            popupContent += '<small class="float-xs-right">' + hsmaJsi18n.png_jpg_gif + '</small>';
                        }
                        popupContent += '</li>';
                    });
                }
            });

            popupContent += '</ul><span class="clear required"><sup>*</sup> ' + hsmaJsi18n.required_fields + '</span><div class="clearfix"><button class="btn btn-primary float-xs-right submit_accessory_customization" type="submit" name="submitAccessoryCustomizedData">' + hsmaJsi18n.save_customization + '</button><button class="btn float-xs-right cancel_accessory_customization" type="button" name="cancel">' + hsmaJsi18n.cancel + '</button></div>';
            popupContent += '</div></section></form>';
            return popupContent;
        };
        
	/**
	 * Render combination of one product
	 * @param {string} randomId
	 * @param {boolean} checkbox
	 */
	this._renderCombination = function (randomId, checkbox)
	{    
                if (typeof randomId === 'undefined')
                    return;                
		var product = this.products[randomId];   
                var hasCombination = true;
		var html = '<select data-randomid="' + randomId + '" name="product-combination" class="product-combination">';                
                var i = 0;
                var defaultSelectedIndex  = 0;
                
		$.each(product.combinations, function (idProductAttribute, combination) {                        
                        var dataImg = '';
                        if (!checkbox) {
                            dataImg = 'data-imagesrc="'+combination.image_default+'"';
                        }
                        var dataAllowOrderingWhenOutOfStock = 'data-alloworderingwhenoutofstock="'+ combination.is_available_when_out_of_stock +'"';
                        var dataStockAvailable = 'data-stockavailable="'+combination.is_stock_available+'"';
			var selected = '';
			if (parseInt(idProductAttribute) === parseInt(product.default_id_product_attribute))
			{                              
                            defaultSelectedIndex = i;
                            selected = 'selected="selected"';
                        }                        
                        html += '<option '+dataImg+' '+dataStockAvailable+' '+dataAllowOrderingWhenOutOfStock+' value="' + idProductAttribute + '"' + selected + '>' + combination.name + '</option>';
                        
                        if (idProductAttribute === 0)
                            hasCombination = false;
                        i++;  
		});

		html += '</select>';
                var classContainCombination = 'combination_'+product.id_accessory_group+'_'+product.id_accessory;
		if (checkbox && hasCombination) {
                    $('.' + classContainCombination).html(html);
                    PriceTable.instance._updateAccessoryPrice(PriceTable.instance.products[randomId]);
                } else {                        
                    var selector = 'combination_'+product.id_accessory_group;                    
                    $('#' + selector).html('');                    
                    if (hasCombination)
                    {   
                        if(this.showOptionImage)
                            $('#' + selector).ddslick('destroy');                           
                        $('#' + selector).html(html);   
                        if(this.showOptionImage)
                            PriceTable.instance._renderCombinationOptionImage(selector, product, defaultSelectedIndex, randomId);                        
                        
                    } 
                    else
                    {
                        //product don't have combination use product's image
                        $('.accessory_image_'+product.id_accessory_group).html('<img src="'+product.combinations[0].image_default+'">');
                        $('.accessory_image_'+product.id_accessory_group).attr('href',product.combinations[0].image_fancybox);
                    }
		}

	};
        
        /**
         * 
         * Render list of product with image beside inside select option list. 
         */
        this._renderProductOptionImage = function () {
            $('.'+PriceTable.instance._selectors.classProductDDSlick).each(function () {
                var idDDSlick = $(this).attr('id');
                $('#' + idDDSlick).ddslick({
                    showSelectedHTML: false,                    
                    background: '#fff',
                    onSelected: function (data) {                           
                        var idGroup = $('#' + idDDSlick).parent().data('idgroup');
                        var randomId = data.selectedData.description;
                        // in case don't select any product then remove combination list
                        if (data.selectedData.value == 0)
                        {
                            var selector = 'combination_'+idGroup; 
                            $('#' + selector).html(''); 
                            /* remove image */
                            $('.accessory_image_'+idGroup).html('');
                            $('#randomid-group-'+idGroup).data('randomid',0);
                            /* Remove customization field*/
                            $('.hsma_customize_group_'+idGroup).html('');
                        } else {
                            $('#randomid-group-'+idGroup).data('randomid',randomId); 
                        }  
                        PriceTable.instance._initProductAccessories();
                        PriceTable.instance._renderTablePrice();
                        PriceTable.instance._renderCombination(randomId, false);    
                        PriceTable.instance._renderCustomization(randomId, idGroup);
                        PriceTable.instance._renderPackageContent(randomId, idGroup);
                        PriceTable.instance.scrollToTablePrice();
                    }
                });
            });
        };
        
        /**
         * @param {selector} selector
         * @param {product} product
         * @param {defaultSelectedIndex} defaultSelectedIndex
         * @param {randomId} randomId
         * Render list of combination with image beside inside select option list. 
         */
        this._renderCombinationOptionImage = function (selector, product, defaultSelectedIndex, randomId) {  
            var previousValueOfCombination = product.default_id_product_attribute;
            $('#' + selector).ddslick({
                showSelectedHTML: false,                    
                background: '#fff', 
                defaultSelectedIndex: defaultSelectedIndex,
                onSelected: function (data) {   
                    var idGroup = product.id_accessory_group;
                    var idProductAttribute = data.selectedData.value;                                      
                    customQuantity = $('#'+selector).parent().find(PriceTable.instance._selectors.accessoriesCustomQuantity).val();
                    customQuantity = typeof customQuantity !== 'undefined' ? customQuantity : PriceTable.instance.products[randomId]['qty'];                    
                    if (!PriceTable.instance.products[randomId]['combinations'][idProductAttribute]['out_of_stock'] && PriceTable.instance.products[randomId]['combinations'][idProductAttribute]['avaiable_quantity'] < customQuantity) {
                        var i = 0;
                        $.each(product.combinations, function (idProductAttribute, combination) {
                            if (idProductAttribute == previousValueOfCombination)
                                $('#' + selector).ddslick('select', {index: i});
                            i++;
                        });
                        alert(PriceTable.instance.warningOutOfStock);
                        window.getSelection().removeAllRanges();
                        return;
                    } else {
                        previousValueOfCombination = idProductAttribute;  
                        var selectedCombination = product.combinations[idProductAttribute];
                        if (selectedCombination.image_default !='') {
                            $('.accessory_image_'+idGroup).html('<img src="'+selectedCombination.image_default+'">');
                        }
                        if (selectedCombination.image_fancybox !='') {
                            $('.accessory_image_'+idGroup).attr('href',selectedCombination.image_fancybox);
                        }
                        PriceTable.instance.products[randomId].default_id_product_attribute = idProductAttribute;
                        PriceTable.instance.triggerTablePrice();
                        PriceTable.instance._updateAccessoryPrice(PriceTable.instance.products[randomId]);                        
                    }
                    PriceTable.instance.scrollToTablePrice();
                }
            });         
        };

	/**
	 * Update price of product accessory
	 * @param {object} product
	 */
        this._updateAccessoryPrice = function (product)
        {
            var price = 0;
            var final_price = 0;
            $.each(product.combinations, function (idProductAttribute, combination) {
                if (typeof combination !== 'undefined' && (parseInt(product.default_id_product_attribute) === parseInt(idProductAttribute) || idProductAttribute == 0)) {
                    price = combination.price;
                }
                if (!$.isEmptyObject(combination.specific_prices)) {
                    $.each(combination.specific_prices, function (fromQty, specificPrice) {
                        if (parseInt(product.qty) >= parseInt(fromQty)) {
                            final_price = specificPrice;
                        }
                    });
                }
                if (combination.is_cart_rule && (parseInt(product.default_id_product_attribute) === parseInt(idProductAttribute) || idProductAttribute == 0)) {
                    final_price = combination.final_price;
                }
            });
            var classContainPrice = 'price_' + product.id_accessory_group + '_' + product.id_accessory;
            var classContainFinalPrice = 'final_price_' + product.id_accessory_group + '_' + product.id_accessory;
            if (price > 0) {
                $('.' + classContainPrice).html(formatCurrency(price, currencyFormat, currencySign, currencyBlank));
            }
            if (final_price > 0) {
                var formatedFinalPrice = formatCurrency(final_price, currencyFormat, currencySign, currencyBlank);
                if ($('.accessory_price').find('span').hasClass(classContainFinalPrice)) {
                    $('.' + classContainFinalPrice).html('&nbsp;' + formatedFinalPrice);
                } else {
                    var htmlFinalprice = '<span class="discount_price ' + classContainFinalPrice + '"> ' + formatedFinalPrice + '</span>';
                    $(htmlFinalprice).insertAfter('.' + classContainPrice);
                    $('.' + classContainPrice).addClass('line_though');
                }
            } else {
                $('.' + classContainFinalPrice).remove();
                $('.' + classContainPrice).removeClass('line_though');
            }
        };

	/**
	 * Update image of product when change combination
	 * @param {Jquery} element
	 * @param {Object} product
	 */
	this._updateProductCombinationImage = function (element, product)
	{
            var selectedCombination = product.combinations[product.default_id_product_attribute];
            $(element).find('.accessory_img_link').attr('href', selectedCombination.image_fancybox);
            $(element).find('.accessory_image').attr('src', selectedCombination.image_default);
	};
	
	/**
	 * Check stock available of accessory when customer change quantity at front end
	 * @param {string} randomId
	 * @param {int} newQuantity
	 * @param {int} idCombination
	 * @returns {Boolean}
	 */
	this.isStockAvailable = function (randomId, newQuantity, idCombination)
	{
            var flag = true;
            idCombination = typeof idCombination !== 'undefined' ? idCombination : 0;
            var product = this.products[randomId].combinations[idCombination];
            var availableQuantity = product.avaiable_quantity;
            var outOfStock = product.out_of_stock;
            if (!outOfStock && availableQuantity < newQuantity) {
                flag = false;
            }
            return flag;
	};
        
        /**
         * Check if button add to cart is visible or not
         * @returns boolean
         */
        this.isAddToCartButtonVisible = function(){
            return $(PriceTable.instance._selectors.idAddToCartButton).visible();
        };
        
        /**
         * Scroll to table price if add to cart button is not visible 
         * (the heigh of accessories too big)
         * 
         */
        this.scrollToTablePrice = function(){
            if (!PriceTable.instance.isAddToCartButtonVisible() && parseInt(PriceTable.instance.isScrollingToTablePrice) === 1)
            {
                if (parseInt(this.showTablePrice) === 1) {
                    $('html, body').animate({
                        scrollTop: $(PriceTable.instance._selectors.accessoriesTablePriceContent).offset().top - 100
                    }, 500);
                }
                
            }   
        };
        
        /**
         * Trigger events of table price
         */
        this.triggerTablePrice = function() {
            PriceTable.instance._updateMainProductPrice();
            PriceTable.instance._initProductAccessories();
            PriceTable.instance._renderTablePrice();
        };
        
       /**
        * Display or hide block contain group accessories
        * @param {Object} element
        */
       this._onClickBlockGroup = function (element)
       {
           
           if ($(element).hasClass(PriceTable.instance._selectors.iconCollapse)){
               $(element).removeClass(PriceTable.instance._selectors.iconCollapse);
               $(element).addClass(PriceTable.instance._selectors.iconExpand);
           } else {
               $(element).removeClass(PriceTable.instance._selectors.iconExpand);
               $(element).addClass(PriceTable.instance._selectors.iconCollapse);
           }
           $(element).parents(PriceTable.instance._selectors.accessoryGroup).find('.' + PriceTable.instance._selectors.contentGroup).toggle("slow");
       };
       this._onClickExpandGroup = function (element)
       {
           if ($(element).parents(PriceTable.instance._selectors.accessoryGroup).find('i').hasClass(PriceTable.instance._selectors.iconCollapse)){
                $(element).parents(PriceTable.instance._selectors.accessoryGroup).find('i').removeClass(PriceTable.instance._selectors.iconCollapse);
                $(element).parents(PriceTable.instance._selectors.accessoryGroup).find('i').addClass(PriceTable.instance._selectors.iconExpand);
                $(element).parents(PriceTable.instance._selectors.accessoryGroup).find('.' + PriceTable.instance._selectors.contentGroup).toggle("slow");
           }
        };
        this.emptyAccessoryCustomizations = function ()
        {
            if ($.isEmptyObject(PriceTable.instance.accessories) || $.isEmptyObject(PriceTable.instance.products)) {
                return;
            }
            $.each(PriceTable.instance.products, function (prandomid, product){
                $.each(PriceTable.instance.accessories, function (arandomid, accessory){
                    if (prandomid == arandomid && product['is_customizable']) {
                        var isRequired = false;
                        $.each(product.customizations, function (key, fields) {
                            if (typeof fields !== 'undefined') {
                                $.each(fields, function (index, field) {
                                    if (field.type == 'text') {
                                        PriceTable.instance.products[prandomid].customizations.fields[index].is_customized = 0;
                                        PriceTable.instance.products[prandomid].customizations.fields[index].text = '';
                                    } else if (field.type == 'image') {
                                       PriceTable.instance.products[prandomid].customizations.fields[index].is_customized = 0;
                                       PriceTable.instance.products[prandomid].customizations.fields[index].image = '';
                                    }
                                    if (parseInt(field.required) === 1) {
                                        isRequired = true;
                                    }
                                });
                                var idGroup = product.id_accessory_group;
                                var currentElement = $('#product_list_accessory_' + idGroup + ' .accessory_customization_' + product.id_accessory);
                                if (isRequired) {
                                    PriceTable.instance.products[prandomid].is_enough_customization = 0;
                                    currentElement.find('.hsma_warning_red').removeClass('hide');
                                }
                                var inputCustomizationData = '<input type="hidden" name="hsma_id_customization" class="hsma_id_customization" data-isenoughcustomization="' + product.is_enough_customization + '" value="' + product.id_customization + '">';
                                currentElement.find('.hsma_id_customization').replaceWith(inputCustomizationData);
                            }
                        });
                    }
                });
            });
        };
        
    /**
     * Render text Pack content
     * @param {string} randomId
     * @param {int} idGroup
     */
    this._renderPackageContent = function (randomId, idGroup)
    {
        if (typeof randomId === 'undefined' || typeof PriceTable.instance.products[randomId] === 'undefined') {
            return;
        }
        var product = this.products[randomId];
        var blockHasPack = '';
        if (typeof product.is_package !== 'undefined' && product.is_package == 1) {
            var hsmaJsi18n = PriceTable.instance.jsTranslateText;
            blockHasPack = '<a class="hsma_package accessory_package_' + product.id_accessory + '"  data-idaccessory="' + product.id_accessory + '" data-randomid ="' + randomId + '" title="' + hsmaJsi18n.this_is_a_packaged_product_please_click_here_to_view_the_list_of_products_for_this_pack + '">' + hsmaJsi18n.pack_content + '</a>';
        }
        $('.hsma_package_group_' + idGroup).html(blockHasPack);
    };
    
    this._showPackageContent = function (idAccessory)
    {
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            url: window.ajaxRenderAccessoriesUrl,
            async: true,
            cache: false,
            dataType: "json",
            data: {
                'ajax' : true,
                'id_accessory' : idAccessory,
                'mQty' : PriceTable.instance._getMainProductQty(),
                'action' : 'displayPackageContent'
            },
            success: function (jsonData) {
                if(!jsonData.hasError) {
                    hsmaCustomizationPopUp = new HsmaCustomizationPopUp();
                    hsmaCustomizationPopUp.setIdAccessory(idAccessory);
                    hsmaCustomizationPopUp.setTitlePopup(jsonData.pack_title);
                    hsmaCustomizationPopUp.setContentPopup(jsonData.pack_content);
                    hsmaCustomizationPopUp.show();
                } else {
                    alert(jsonData.errors);
                }
            }
        });
    };
};

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};
