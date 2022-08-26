/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Override trigger change idCombination triggerCombination()
 * Search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
 * @param {string} firstTime
 */
function findCombination(firstTime)
{
	$('#minimal_quantity_wanted_p').fadeOut();
	$('#quantity_wanted').val(1);
	//create a temporary 'choice' array containing the choices of the customer
	var choice = [];
	$('#attributes select, #attributes input[type=hidden], #attributes input[type=radio]:checked').each(function () {
		choice.push($(this).val());
	});

	//testing every combination to find the conbination's attributes' case of the user
	for (var combination = 0; combination < combinations.length; ++combination)
	{
		//verify if this combinaison is the same that the user's choice
		var combinationMatchForm = true;
		$.each(combinations[combination]['idsAttributes'], function (key, value)
		{
			if (!in_array(value, choice))
				combinationMatchForm = false;
		});

		if (combinationMatchForm)
		{
			if (combinations[combination]['minimal_quantity'] > 1)
			{
				$('#minimal_quantity_label').html(combinations[combination]['minimal_quantity']);
				$('#minimal_quantity_wanted_p').fadeIn();
				$('#quantity_wanted').val(combinations[combination]['minimal_quantity']);
				$('#quantity_wanted').bind('keyup', function () {
					checkMinimalQuantity(combinations[combination]['minimal_quantity']);
				});
			}
			//combination of the user has been found in our specifications of combinations (created in back office)
			selectedCombination['unavailable'] = false;
			selectedCombination['reference'] = combinations[combination]['reference'];
			//$('#idCombination').val(combinations[combination]['idCombination']);

			triggerCombination(combinations[combination]['idCombination']);

			//get the data of product with these attributes
			quantityAvailable = combinations[combination]['quantity'];
			selectedCombination['price'] = combinations[combination]['price'];
			selectedCombination['unit_price'] = combinations[combination]['unit_price'];
			selectedCombination['specific_price'] = combinations[combination]['specific_price'];
			if (combinations[combination]['ecotax'])
				selectedCombination['ecotax'] = combinations[combination]['ecotax'];
			else
				selectedCombination['ecotax'] = default_eco_tax;

			//show the large image in relation to the selected combination
			if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
				displayImage($('#thumb_' + combinations[combination]['image']).parent());

			//show discounts values according to the selected combination
			if (combinations[combination]['idCombination'] && combinations[combination]['idCombination'] > 0)
				displayDiscounts(combinations[combination]['idCombination']);

			//get available_date for combination product
			selectedCombination['available_date'] = combinations[combination]['available_date'];

			//update the display
			updateDisplay();

			if (typeof (firstTime) != 'undefined' && firstTime)
				refreshProductImages(0);
			else
				refreshProductImages(combinations[combination]['idCombination']);
			//leave the function because combination has been found
			return;
		}
	}
	//this combination doesn't exist (not created in back office)
	selectedCombination['unavailable'] = true;
	if (typeof (selectedCombination['available_date']) != 'undefined')
		delete selectedCombination['available_date'];
	updateDisplay();
}

// detect change value commbination
function triggerCombination(value)
{
	$('#idCombination').val(value).trigger('change');
}

/**
 * Override global productPriceDisplay
 * update display of the availability of the product AND the prices of the product
 * Disable update subtotal price when customer change combination
 */
function updateDisplay()
{
	var productPriceDisplay = productPrice;

	var productPriceWithoutReductionDisplay = productPriceWithoutReduction;

	if (!selectedCombination['unavailable'] && quantityAvailable > 0 && productAvailableForOrder == 1)
	{
		//show the choice of quantities
		$('#quantity_wanted_p:hidden').show('slow');

		//show the "add to cart" button ONLY if it was hidden
		$('#add_to_cart:hidden').fadeIn(600);

		//hide the hook out of stock
		$('#oosHook').hide();

		$('#availability_date').fadeOut();

		//availability value management
		if (availableNowValue != '')
		{
			//update the availability statut of the product
			$('#availability_value').removeClass('warning_inline');
			$('#availability_value').text(availableNowValue);
			if (stock_management == 1)
				$('#availability_statut:hidden').show();
		}
		else
			$('#availability_statut:visible').hide();

		//'last quantities' message management
		if (!allowBuyWhenOutOfStock)
		{
			if (quantityAvailable <= maxQuantityToAllowDisplayOfLastQuantityMessage)
				$('#last_quantities').show('slow');
			else
				$('#last_quantities').hide('slow');
		}

		if (quantitiesDisplayAllowed)
		{
			$('#pQuantityAvailable:hidden').show('slow');
			$('#quantityAvailable').text(quantityAvailable);

			if (quantityAvailable < 2) // we have 1 or less product in stock and need to show "item" instead of "items"
			{
				$('#quantityAvailableTxt').show();
				$('#quantityAvailableTxtMultiple').hide();
			}
			else
			{
				$('#quantityAvailableTxt').hide();
				$('#quantityAvailableTxtMultiple').show();
			}
		}
	}
	else
	{
		//show the hook out of stock
		if (productAvailableForOrder == 1)
		{
			$('#oosHook').show();
			if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
				oosHookJsCode();
		}

		//hide 'last quantities' message if it was previously visible
		$('#last_quantities:visible').hide('slow');

		//hide the quantity of pieces if it was previously visible
		$('#pQuantityAvailable:visible').hide('slow');

		//hide the choice of quantities
		if (!allowBuyWhenOutOfStock)
			$('#quantity_wanted_p:visible').hide('slow');

		//display that the product is unavailable with theses attributes
		if (!selectedCombination['unavailable'])
			$('#availability_value').text(doesntExistNoMore + (globalQuantity > 0 ? ' ' + doesntExistNoMoreBut : '')).addClass('warning_inline');
		else
		{
			$('#availability_value').text(doesntExist).addClass('warning_inline');
			$('#oosHook').hide();
		}
		if (stock_management == 1 && !allowBuyWhenOutOfStock)
			$('#availability_statut:hidden').show();

		if (typeof (selectedCombination['available_date']) != 'undefined' && selectedCombination['available_date']['date'].length != 0)
		{
			var available_date = selectedCombination['available_date']['date'];
			var tab_date = available_date.split('-');
			var time_available = new Date(tab_date[0], tab_date[1], tab_date[2]);
			time_available.setMonth(time_available.getMonth() - 1);
			var now = new Date();
			if (now.getTime() < time_available.getTime() && $('#availability_date_value').text() != selectedCombination['available_date']['date_formatted'])
			{
				$('#availability_date').fadeOut('normal', function () {
					$('#availability_date_value').text(selectedCombination['available_date']['date_formatted']);
					$(this).fadeIn();
				});
			}
			else if (now.getTime() < time_available.getTime())
				$('#availability_date').fadeIn();
		}
		else
			$('#availability_date').fadeOut();

		//show the 'add to cart' button ONLY IF it's possible to buy when out of stock AND if it was previously invisible
		if (allowBuyWhenOutOfStock && !selectedCombination['unavailable'] && productAvailableForOrder == 1)
		{
			$('#add_to_cart:hidden').fadeIn(600);

			if (availableLaterValue !== '')
			{
				$('#availability_value').text(availableLaterValue);
				if (stock_management == 1)
					$('#availability_statut:hidden').show('slow');
			}
			else
				$('#availability_statut:visible').hide('slow');
		}
		else
		{
			$('#add_to_cart:visible').fadeOut(600);
			if (stock_management == 1)
				$('#availability_statut:hidden').show('slow');
		}

		if (productAvailableForOrder == 0)
			$('#availability_statut:visible').hide();
	}

	if (selectedCombination['reference'] || productReference)
	{
		if (selectedCombination['reference'])
			$('#product_reference span').text(selectedCombination['reference']);
		else if (productReference)
			$('#product_reference span').text(productReference);
		$('#product_reference:hidden').show('slow');
	}
	else
		$('#product_reference:visible').hide('slow');

	//update display of the the prices in relation to tax, discount, ecotax, and currency criteria
	if (!selectedCombination['unavailable'] && productShowPrice == 1)
	{
		var priceTaxExclWithoutGroupReduction = '';

		// retrieve price without group_reduction in order to compute the group reduction after
		// the specific price discount (done in the JS in order to keep backward compatibility)
		priceTaxExclWithoutGroupReduction = ps_round(productPriceTaxExcluded, 6) * (1 / group_reduction);

		var tax = (taxRate / 100) + 1;
		var taxExclPrice = priceTaxExclWithoutGroupReduction + (selectedCombination['price'] * currencyRate);

		if (selectedCombination.specific_price && selectedCombination.specific_price['id_product_attribute'])
		{
			if (selectedCombination.specific_price['price'] && selectedCombination.specific_price['price'] >= 0)
				var taxExclPrice = (specific_currency ? selectedCombination.specific_price['price'] : selectedCombination.specific_price['price'] * currencyRate);
			else
				var taxExclPrice = productBasePriceTaxExcluded * currencyRate + (selectedCombination['price'] * currencyRate);
		}
		else if (product_specific_price.price && product_specific_price.price >= 0)
			var taxExclPrice = (specific_currency ? product_specific_price.price : product_specific_price.price * currencyRate) + (selectedCombination['price'] * currencyRate);

		if (!displayPrice && !noTaxForThisProduct)
			productPriceDisplay = ps_round(taxExclPrice * tax, 2); // Need to be global => no var
		else
			productPriceDisplay = ps_round(taxExclPrice, 2); // Need to be global => no var

		productPriceWithoutReductionDisplay = productPriceDisplay * group_reduction;
		var reduction = 0;
		if (selectedCombination['specific_price'].reduction_price || selectedCombination['specific_price'].reduction_percent)
		{
			reduction_price = (specific_currency ? selectedCombination['specific_price'].reduction_price : selectedCombination['specific_price'].reduction_price * currencyRate);
			reduction = productPriceDisplay * (parseFloat(selectedCombination['specific_price'].reduction_percent) / 100) + reduction_price;
			if (reduction_price && (displayPrice || noTaxForThisProduct))
				reduction = ps_round(reduction / tax, 6);

		}
		else if (product_specific_price && product_specific_price.reduction && !selectedCombination.specific_price)
		{
			if (product_specific_price.reduction_type == 'amount')
				reduction_price = (specific_currency ? product_specific_price.reduction : product_specific_price.reduction * currencyRate);
			else
				reduction_price = 0;

			if (product_specific_price.reduction_type == 'percentage')
				reduction_percent = productPriceDisplay * parseFloat(product_specific_price.reduction);

			reduction = reduction_price + reduction_percent;
			if (reduction_price && (displayPrice || noTaxForThisProduct))
				reduction = ps_round(reduction / tax, 6);
		}

		if (selectedCombination.specific_price)
		{
			if (selectedCombination['specific_price'] && selectedCombination['specific_price'].reduction_type == 'percentage')
			{
				$('#reduction_amount').hide();
				$('#reduction_percent_display').html('-' + parseFloat(selectedCombination['specific_price'].reduction_percent) + '%');
				$('#reduction_percent').show();
			} else if (selectedCombination['specific_price'].reduction_type == 'amount' && selectedCombination['specific_price'].reduction_price != 0) {
				$('#reduction_amount_display').html('-' + formatCurrency(reduction_price, currencyFormat, currencySign, currencyBlank));
				$('#reduction_percent').hide();
				$('#reduction_amount').show();
			} else {
				$('#reduction_percent').hide();
				$('#reduction_amount').hide();
			}
		}

		if (product_specific_price['reduction_type'] != '' || selectedCombination['specific_price'].reduction_type != '')
			$('#discount_reduced_price,#old_price').show();
		else
			$('#discount_reduced_price,#old_price').hide();
		if ((product_specific_price['reduction_type'] == 'percentage' && selectedCombination['specific_price'].reduction_type == 'percentage') || selectedCombination['specific_price'].reduction_type == 'percentage')
			$('#reduction_percent').show();
		else
			$('#reduction_percent').hide();
		if (product_specific_price['price'] || (selectedCombination.specific_price && selectedCombination.specific_price['price']))
			$('#not_impacted_by_discount').show();
		else
			$('#not_impacted_by_discount').hide();

		productPriceDisplay -= reduction;
		productPriceDisplay = ps_round(productPriceDisplay * group_reduction, 2);

		var ecotaxAmount = !displayPrice ? ps_round(selectedCombination['ecotax'] * (1 + ecotaxTax_rate / 100), 2) : selectedCombination['ecotax'];

		if (ecotaxAmount != default_eco_tax)
			productPriceDisplay += ecotaxAmount - default_eco_tax;
		else
			productPriceDisplay += ecotaxAmount;

		if (ecotaxAmount != default_eco_tax)
			productPriceWithoutReductionDisplay += ecotaxAmount - default_eco_tax;
		else
			productPriceWithoutReductionDisplay += ecotaxAmount;

		var our_price = '';
		if (productPriceDisplay > 0) {
			our_price = formatCurrency(productPriceDisplay, currencyFormat, currencySign, currencyBlank);
		} else {
			our_price = formatCurrency(0, currencyFormat, currencySign, currencyBlank);
		}
		//$('#our_price_display').text(our_price);
		$('#old_price_display').text(formatCurrency(productPriceWithoutReductionDisplay, currencyFormat, currencySign, currencyBlank));

		if (productPriceWithoutReductionDisplay > productPriceDisplay)
			$('#old_price,#old_price_display,#old_price_display_taxes').show();
		else
			$('#old_price,#old_price_display,#old_price_display_taxes').hide();
		// Special feature: "Display product price tax excluded on product page"
		var productPricePretaxed = '';
		if (!noTaxForThisProduct)
			productPricePretaxed = productPriceDisplay / tax;
		else
			productPricePretaxed = productPriceDisplay;
		$('#pretaxe_price_display').text(formatCurrency(productPricePretaxed, currencyFormat, currencySign, currencyBlank));
		// Unit price
		productUnitPriceRatio = parseFloat(productUnitPriceRatio);
		if (productUnitPriceRatio > 0)
		{
			newUnitPrice = (productPriceDisplay / parseFloat(productUnitPriceRatio)) + selectedCombination['unit_price'];
			$('#unit_price_display').text(formatCurrency(newUnitPrice, currencyFormat, currencySign, currencyBlank));
		}

		// Ecotax
		ecotaxAmount = !displayPrice ? ps_round(selectedCombination['ecotax'] * (1 + ecotaxTax_rate / 100), 2) : selectedCombination['ecotax'];
		$('#ecotax_price_display').text(formatCurrency(ecotaxAmount, currencyFormat, currencySign, currencyBlank));

		priceWithDiscountsDisplay = productPriceDisplay;

	}
}

