/**
 * Event after changing of order product
 */
$(document).on('change', "select[name='share_product[product]']", function ()
{
    var $parent = $(this).closest('.parent');

    var $selected = $(this).find(':selected');

    var $weight = $parent.find('#share_product_weight');
    var weight = $selected.data('weight');
    $weight.val(weight);

    var $qty = $parent.find('#share_product_qty');
    var qty = $parent.find('#share_product_qty').val();

    if (qty.length === 0) {
        $qty.val(1);
    } else if (qty > 1) {
        weight = weight * qty;
    }

    $weight.val(weight).change();
});


/**
 * Calculate total product weight after changing of qty and trigger weight change event for calculate total price
 */
$(document).on('keyup change', "input[name='share_product[qty]']", function ()
{
    var $parent = $(this).closest('.parent');
    var $weight = $parent.find('#share_product_weight');

    var qty = $(this).val();

    if (parseInt(qty) <= 0) {
        $(this).val(1);
        $weight.removeClass('custom-read-only');
    }

    var perWeight = $parent.find('#share_product_product').find(':selected').data('weight');

    $weight.addClass('custom-read-only');

    if (perWeight !== undefined) {
        if (qty > 1) {
            var newWeight = qty * perWeight;
            $weight.val(newWeight).change();
        } else {
            $weight.removeClass('custom-read-only');
            $weight.val(perWeight).change();
        }
    }
});


/**
 * Calculate total price of product and order after changing on weight field (triggered by changing qty field)
 */
$(document).on('keyup change', "input[name='share_product[weight]']", function ()
{
    var $parent = $(this).closest('.parent');

    // Get all data about product(weight, weight format, price)
    var $product = $parent.find('#share_product_product').find(':selected');
    var $productPriceField = $parent.find('#share_product_price');
    var productPrice = getTotalPrice($product.data('price'), $product.data('weight'), $(this).data('format'), $(this).val());

    // Update total price of order
    var $totalPriceField = $parent.parent().find('#totalPrice');
    var totalPrice = (parseFloat($totalPriceField.text()) - parseFloat($productPriceField.val())) + productPrice;
    $totalPriceField.text(totalPrice.toFixed(2));

    // Update product price
    $productPriceField.val(productPrice);
});

/**
 * Calculate total price using weight and weight format
 *
 * @param price
 * @param weight
 * @param format
 * @param newWeight
 * @returns {Number}
 */
function getTotalPrice(price, weight, format, newWeight)
{
    var gramsInOne = format === 'Lbs' ? 453.592 : 1000;
    var gramPrice = price / (weight * gramsInOne);

    var totalPrice = gramPrice * (newWeight * gramsInOne);

    return parseFloat(totalPrice.toFixed(2));
}

// Do not allow to type letters (allow only digits) on qty field
$(document).on('keyup', "input[name='share_product[qty]'], input[name='share_product[weight]']", function(e)
{
    var val = $(this).val();

    if (isNaN(val)) {
        val = val.replace(/[^0-9\.]/g,'');

        if (val.split('.').length > 2) {
            val = val.replace(/\.+$/,"");
        }
    }

    $(this).val(val);
});


/**
 * Event after click on save order product button
 */
$(document).on('click', '.btn-add-ajax', function (e)
{
    e.preventDefault();
    e.stopImmediatePropagation();

    var form = $(this).closest('form');
    var inputs = form.find('input, select, textarea');

    if(!validate(inputs)) throw "Error on validating inputs";
    form.find('.form-group').removeClass('has-error');

    var $this = $(this);

    var buttonLabel = $this.text();
    var buttonHolder = $this.parent();
    buttonHolder.html('<button class="btn btn-default btn-action btn-icon"><i class="icon-spinner3 spinner"></i></button>');

    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: inputs.serialize(),
        success: function (response) {
            // Enable the button
            buttonHolder.html('<button type="submit" class="btn btn-action btn-add-ajax">' + buttonLabel + '</button>');

            var formsHolder = form.parent().parent().find('.order-products');
            var price = form.find("#share_product_price").val();
            var $totalPrice = formsHolder.find('#total').find('#totalPrice');
            var totalPrice = parseFloat($totalPrice.text()) + parseFloat(price);
            $totalPrice.text(totalPrice.toFixed(2));

            console.log(formsHolder.find('tbody'))

            formsHolder.find('tbody').find('#total').before(response.template);

            // Reset form fields
            form.find('input, select').not(':button, :submit, :reset, :hidden').val('');
        },
        error: function (response) {
            var formErrors = jQuery.parseJSON(response.responseJSON).error.children;
            var errNum = 0;
            //For each input win form
            $.each(formErrors, function(name, input) {
                // If form has errors
                if (!$.isEmptyObject(input)) {
                    var inputId = form.attr("name") + "_" + name;
                    var field = row.find('#' + inputId);

                    field.tooltip({
                        trigger: 'manual',
                        placement: 'top',
                        title: input.errors[0]
                    }).attr('data-original-title', input.errors[0])
                        .tooltip('fixTitle')
                        .tooltip('show');

                    field.parent().addClass('has-error');
                } else {
                    errNum += 1;
                }
            });

            // That means that updating have undefined error
            if (errNum === Object.keys(formErrors).length) {
                alert(errors['undefinedErrors'][locale]);
            }

            // Enable the button button
            buttonHolder.empty();
            buttonHolder.append('<button type="submit" class="btn btn-success btn-icon btn-save"> <i class="icon-floppy-disk" ></i></button>');
        }
    });
});

/**
 * Count total price after deleting of share product
 */
$(document).on('mouseup', "#delete_modal_yes", function ()
{
    var deletePath = $(document).find('#data-area').attr('data-path');
    var formHolder = $("button[data-path='" + deletePath + "']").parent().parent().find('form').parent();

    var price = formHolder.find("#share_product_price").val();
    var $totalPrice = formHolder.parent().find('#total').find('#totalPrice');
    var totalPrice = parseFloat($totalPrice.text()) - parseFloat(price);

    $totalPrice.text(totalPrice.toFixed(2));
});