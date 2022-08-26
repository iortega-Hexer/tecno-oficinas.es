
// Override trigger change idCombination triggerCombination()
// search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
function findCombination(firstTime)
{
    $('#minimal_quantity_wanted_p').fadeOut();
    if (typeof $('#minimal_quantity_label').text() === 'undefined' || $('#minimal_quantity_label').html() > 1)
        $('#quantity_wanted').val(1);

    //create a temporary 'choice' array containing the choices of the customer
    var choice = [];
    var radio_inputs = parseInt($('#attributes .checked > input[type=radio]').length);
    if (radio_inputs)
        radio_inputs = '#attributes .checked > input[type=radio]';
    else
        radio_inputs = '#attributes input[type=radio]:checked';

    $('#attributes select, #attributes input[type=hidden], ' + radio_inputs).each(function () {
        choice.push(parseInt($(this).val()));
    });

    if (typeof combinations === 'undefined' || !combinations)
        combinations = [];

    //testing every combination to find the conbination's attributes' case of the user
    for (var combination = 0; combination < combinations.length; ++combination)
    {
        //verify if this combinaison is the same that the user's choice
        var combinationMatchForm = true;
        $.each(combinations[combination]['idsAttributes'], function (key, value)
        {
            if (!in_array(parseInt(value), choice))
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
            $('#idCombination').val(combinations[combination]['idCombination']);

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

            if (typeof (firstTime) !== 'undefined' && firstTime)
                refreshProductImages(0);
            else
                refreshProductImages(combinations[combination]['idCombination']);
            //leave the function because combination has been found
            return;
        }
    }

    //this combination doesn't exist (not created in back office)
    selectedCombination['unavailable'] = true;
    if (typeof (selectedCombination['available_date']) !== 'undefined')
        delete selectedCombination['available_date'];

    updateDisplay();
}

/**
 * Multi accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Detect change combination value
 * @param {string} value
 */
function triggerCombination(value)
{
    $('#idCombination').val(value).trigger('change');
}

/**
 * Override function updatePrice of product.js
 * - Disable update subtotal price when customer change combination
 */
function updatePrice()
{

    // Get combination prices
    var idCombination = $('#idCombination').val();
    var combination = combinationsFromController[idCombination];
    if (typeof combination == 'undefined')
        return;

    // Set product (not the combination) base price
    var basePriceWithoutTax = productPriceTaxExcluded;
    var priceWithGroupReductionWithoutTax = 0;

    // Apply combination price impact
    // 0 by default, +x if price is inscreased, -x if price is decreased
     basePriceWithoutTax = basePriceWithoutTax + +combination.price;

    // If a specific price redefine the combination base price
    if (combination.specific_price && combination.specific_price.price > 0)
    {
        if (combination.specific_price.id_product_attribute === 0)
            basePriceWithoutTax = +combination.specific_price.price;
        else
            basePriceWithoutTax = +combination.specific_price.price + +combination.price;
    }
    
    // Apply group reduction
    if (typeof groupReduction !== 'undefined')
        priceWithGroupReductionWithoutTax = basePriceWithoutTax * (1 - groupReduction);
    else
        priceWithGroupReductionWithoutTax = basePriceWithoutTax;

    var priceWithDiscountsWithoutTax = priceWithGroupReductionWithoutTax;

    // Apply specific price (discount)
    // We only apply percentage discount and discount amount given before tax
    // Specific price give after tax will be handled after taxes are added
    if (combination.specific_price && combination.specific_price.reduction > 0)
    {
        if (combination.specific_price.reduction_type == 'amount')
        {
            if (typeof combination.specific_price.reduction_tax !== 'undefined' && combination.specific_price.reduction_tax === "0")
            {
                var reduction = +combination.specific_price.reduction / currencyRate;
                priceWithDiscountsWithoutTax -= reduction;
            }
        }
        else if (combination.specific_price.reduction_type == 'percentage')
        {
            priceWithDiscountsWithoutTax = priceWithDiscountsWithoutTax * (1 - +combination.specific_price.reduction);
        }
    }

    // Apply Tax if necessary
    if (noTaxForThisProduct || customerGroupWithoutTax)
    {
        basePriceDisplay = basePriceWithoutTax;
        priceWithDiscountsDisplay = priceWithDiscountsWithoutTax;
    }
    else
    {
        basePriceDisplay = basePriceWithoutTax * (taxRate / 100 + 1);
        priceWithDiscountsDisplay = priceWithDiscountsWithoutTax * (taxRate / 100 + 1);

    }

    if (default_eco_tax)
    {
        // combination.ecotax doesn't modify the price but only the display
        basePriceDisplay = basePriceDisplay + default_eco_tax * (1 + ecotaxTax_rate / 100);
        priceWithDiscountsDisplay = priceWithDiscountsDisplay + default_eco_tax * (1 + ecotaxTax_rate / 100);
    }

    // If the specific price was given after tax, we apply it now
    if (combination.specific_price && combination.specific_price.reduction > 0)
    {
        if (combination.specific_price.reduction_type == 'amount')
        {
            if (typeof combination.specific_price.reduction_tax === 'undefined'
                    || (typeof combination.specific_price.reduction_tax !== 'undefined' && combination.specific_price.reduction_tax === '1'))
            {
                var reduction = +combination.specific_price.reduction / currencyRate;
                priceWithDiscountsDisplay -= reduction;
                // We recalculate the price without tax in order to keep the data consistency
                priceWithDiscountsWithoutTax = priceWithDiscountsDisplay - reduction * (1 / (1 + taxRate / 100));
            }
        }
    }

    // Compute discount value and percentage
    // Done just before display update so we have final prices
    if (basePriceDisplay != priceWithDiscountsDisplay)
    {
        var discountValue = basePriceDisplay - priceWithDiscountsDisplay;
        var discountPercentage = (1 - (priceWithDiscountsDisplay / basePriceDisplay)) * 100;
    }

    var unit_impact = +combination.unit_impact;
    if (productUnitPriceRatio > 0 || unit_impact)
    {
        if (unit_impact)
        {
            baseUnitPrice = productBasePriceTaxExcl / productUnitPriceRatio;
            unit_price = baseUnitPrice + unit_impact;

            if (!noTaxForThisProduct || !customerGroupWithoutTax)
                unit_price = unit_price * (taxRate / 100 + 1);
        }
        else
            unit_price = priceWithDiscountsDisplay / productUnitPriceRatio;
    }

    /*  Update the page content, no price calculation happens after */

    // Hide everything then show what needs to be shown
    $('#reduction_percent').hide();
    $('#reduction_amount').hide();
    $('#old_price,#old_price_display,#old_price_display_taxes').hide();
    $('.price-ecotax').hide();
    $('.unit-price').hide();

    triggerCombination(idCombination);
    if (typeof changeMainPrice !== 'undefined' && !changeMainPrice) {
        $('#our_price_display').text(formatCurrency(priceWithDiscountsDisplay * currencyRate, currencyFormat, currencySign, currencyBlank)).trigger('change');
    }
    
    // If the calculated price (after all discounts) is different than the base price
    // we show the old price striked through
    if (priceWithDiscountsDisplay.toFixed(2) != basePriceDisplay.toFixed(2))
    {
        $('#old_price_display').text(formatCurrency(basePriceDisplay * currencyRate, currencyFormat, currencySign, currencyBlank));
        $('#old_price,#old_price_display,#old_price_display_taxes').show();

        // Then if it's not only a group reduction we display the discount in red box
        if (priceWithDiscountsWithoutTax != priceWithGroupReductionWithoutTax)
        {
            if (combination.specific_price.reduction_type == 'amount')
            {
                $('#reduction_amount_display').html('-' + formatCurrency(+discountValue * currencyRate, currencyFormat, currencySign, currencyBlank));
                $('#reduction_amount').show();
            }
            else
            {
                $('#reduction_percent_display').html('-' + parseFloat(discountPercentage).toFixed(0) + '%');
                $('#reduction_percent').show();
            }
        }
    }

    // Green Tax (Eco tax)
    // Update display of Green Tax
    if (default_eco_tax)
    {
        ecotax = default_eco_tax;

        // If the default product ecotax is overridden by the combination
        if (combination.ecotax)
            ecotax = +combination.ecotax;

        if (!noTaxForThisProduct)
            ecotax = ecotax * (1 + ecotaxTax_rate / 100)

        $('#ecotax_price_display').text(formatCurrency(ecotax * currencyRate, currencyFormat, currencySign, currencyBlank));
        $('.price-ecotax').show();
    }

    // Unit price are the price per piece, per Kg, per m²
    // It doesn't modify the price, it's only for display
    if (productUnitPriceRatio > 0)
    {
        $('#unit_price_display').text(formatCurrency(unit_price * currencyRate, currencyFormat, currencySign, currencyBlank));
        $('.unit-price').show();
    }

    // If there is a quantity discount table,
    // we update it according to the new price
    updateDiscountTable(priceWithDiscountsDisplay);
}

