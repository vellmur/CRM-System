var $body = $('body'),
    $form = $('.form-horizontal'),
    $customerSearch = $('#customer-find'),
    customersList = [],
    customerTimeout,
    currency = $('#currency_format').val();

$customerSearch.typeahead({
    highlight: true,
    minLength: 1
}, {
    name: 'myMatches',
    limit: 20,
    source: function(query, syncResults, asyncResults) {
        if (customerTimeout) {
            clearTimeout(customerTimeout);
        }

        customerTimeout = setTimeout(function() {
            var path = $('#customer_search_path').val(),
                $searchIcon = $('#customer-find').closest('.has-feedback').find('.icon-search4');

            $searchIcon.attr('class', 'icon-spinner2 spinner');

            $.ajax({
                url: path,
                type: 'POST',
                data: {'search': query},
                success: function (response) {
                    customersList = jQuery.parseJSON(response).customers;
                    asyncResults(customersList.names);
                    $searchIcon.attr('class', 'icon-search4');
                },
                error: function (response) {}
            });
        }, 1000);
    }
}).on('typeahead:selected', function (e, search) {
    var customer = customersList.values[search.trim().toUpperCase()];

    if (customer) {
        $('#pos_customer_id').val(customer.id);
        $('#pos_customer_firstname').val(customer.firstName);
        $('#pos_customer_lastname').val(customer.lastName);
        $('#pos_customer_email').val(customer.email);
        $('#pos_customer_phone').val(customer.phone);
    }
});

var $productSearch = $('#product-search'),
    products = [],
    productTimeout;

$productSearch.typeahead({
    highlight: true,
    minLength: 1
}, {
    name: 'myMatches',
    limit: 20,
    source: function(query, syncResults, asyncResults) {
        if (productTimeout) {
            clearTimeout(productTimeout);
        }

        productTimeout = setTimeout(function() {
            var path = $('#product_search_path').val(),
                $searchIcon = $('#product-search').closest('.has-feedback').find('.icon-search4');

            $searchIcon.attr('class', 'icon-spinner2 spinner');

            $.ajax({
                url: path,
                type: "POST",
                data: {'search': query},
                success: function (response) {
                    products = jQuery.parseJSON(response).products;
                    asyncResults(products.results);

                    $searchIcon.attr('class', 'icon-search4');

                    var $results = $('#products').find(".tt-menu .tt-suggestion");

                    if ($results.length === 1) {
                        $results.first().click();

                        var $lastResult = $('#order-collection').children().last().find('.product-weight');

                        // Focus on weight field
                        if (!$lastResult.hasClass('hidden')) {
                            setTimeout(function () {
                                $lastResult.first().focus();
                            }, 200);
                        }
                    }
                },
                error: function (response) {
                },
                complete: function () {
                    $productSearch.removeAttr('disabled');
                    $productSearch.focus();
                }
            });
        }, 500);
    }
}).on('typeahead:selected', function (e, selected) {
    var $this = $(this),
        id = products.values[selected],
        name = products.names[id],
        price = parseFloat(products.prices[id]),
        payByQty = products.byQty[id],
        $errors = $this.closest('form').find('.has-error');

    addProduct(name, id, price, payByQty);

    $this.val('');
    $this.typeahead('val', '');

    $errors.removeClass('has-error');
    $errors.find('.validation-error-label').remove();

}).on('keyup', function (e) {
    // Number 13 is the "Enter" key on the keyboard
    if (e.keyCode === 13) {
        e.preventDefault();
        $productSearch.attr('disabled', true);
    }
}).focus();

$productSearch.focus();

function addProduct(name, id, price, payByQty)
{
    var $collectionHolder = $('#order-collection');
    $collectionHolder.data('index', $collectionHolder.find('.product_item').length);

    var prototype = $collectionHolder.data('prototype'),
        index = $collectionHolder.data('index'),
        newForm = prototype.replace(/__name__/g, index),
        $newFormDiv = $(newForm),
        $productQty = $newFormDiv.find(".product-qty"),
        $productWeight = $newFormDiv.find(".product-weight");

    $collectionHolder.append($newFormDiv);
    $collectionHolder.data('index', index + 1);

    var $productItem = $collectionHolder.children().last(),
        $product = $productItem.find('select[id$="product"]');

    $productItem.find('.product-name').append(name);

    $product.val(id).trigger('change');
    $product.find('option[value="' + id + '"]').attr('selected', true);
    $product.data('price', price);
    $product.data('name', name);

    // Show qty or weight field
    if (payByQty) {
        $productQty.removeClass('hidden');
        $productQty.TouchSpin({
            min: 1,
            max: 40,
            buttonup_class: 'btn btn-default',
            buttondown_class: 'btn btn-default',
            initval: 1
        });

        $body.find($productQty).trigger('change');
    } else {
        $productWeight = $body.find($productWeight);
        $productWeight.removeClass('hidden');
        $productWeight.trigger('change');
        $productWeight.click();
        $productWeight.focus();
    }
}

// Event on clicking -/+ weeks of some share
$body.on('change keyup', '.product-qty, .product-weight', function ()
{
    // Get single product element with all info (price, value, name)
    var $this = $(this),
        $product = $this.closest('.product-info').find('select[id$="product"]'),
        qty = parseFloat($(this).val()) > 0 ? parseFloat($this.val()) : 0,
        $cart = $('#products-list'),
        $cartProductId = $product.attr('id') + '_cart',
        $productInCart = $cart.find('#' + $cartProductId),
        price = parseFloat($product.data('price')).toFixed(2),
        productTotalPrice = parseFloat(price * qty).toFixed(2);

    // If product already exists in a cart, just update it, else - append it
    if ($productInCart.length > 0) {
        $this.hasClass('product-qty') ? $productInCart.find('.productQty').text(qty) : $productInCart.find('.productWeight').text(qty);
        $productInCart.find('.productPrice').text(productTotalPrice);
    } else {
        var productInfo = '<tr id="' + $cartProductId + '">' +
            '<td>' + $product.data('name') + '</td>' +
            '<td class="productQty">' + ($this.hasClass('product-qty') ? qty : '') + '</td>' +
            '<td><span class="productWeight">' + ($this.hasClass('product-weight') ? qty : '') + '</span>' + ($this.hasClass('product-weight') ? ' Kg' : '') + '</td>' +
            '<td>' + currency + '<span class="productPrice">' + productTotalPrice + '</span></td>' +
            '</tr>';

        $cart.find('tbody > tr:last-child').before(productInfo);
    }

    countTotalPrice();
});

// Focus on product search after pressing on ENTER
$body.on('keyup', '.product-weight', function (e) {
    if (e.keyCode === 13) {
        $productSearch.focus();
    }
});

/**
 * Event on clicking remove product button
 */
$body.on('click', '.delete-product', function (e)
{
    e.preventDefault();

    var $this = $(this),
        $product = $this.closest('.product-info').find('select[id$="product"]'),
        $productInCart = $('#' + $product.attr('id') + '_cart');

    $productInCart.remove();
    $this.closest('.product_item').remove();

    countTotalPrice();
});

/**
 * Count total price for all products
 */
function countTotalPrice()
{
    var productPrices = $('.cart-list').find('.productPrice'),
        totalPrice = 0,
        $createButton = $('#create-order');

    for (var i = 0; i < productPrices.length; i++) {
        totalPrice += parseFloat($(productPrices[i]).text());
    }

    $('#total').text(totalPrice.toFixed(2));
    $('#pos_total').val(totalPrice.toFixed(2));
    $('#pos_receivedAmount').trigger('change');

    if (totalPrice > 0) {
        $createButton.removeClass('custom-read-only').removeAttr('disabled');
    } else {
        $createButton.addClass('custom-read-only').attr('disabled', true);
    }
}

$('#pos_receivedAmount').on('keyup change', function () {
    var totalPrice = parseFloat($('#total').text()),
        $returnedField = $('#pos_returnedAmount'),
        returnedValue = $(this).val() - totalPrice;

    $returnedField.val(parseFloat(returnedValue).toFixed(2));
});

$('#cancel-order').on('click', function () {
    $(this).closest('form').find('input[type=text], input[type=email], input[type=hidden]').val('');
    $('.delete-product').click();
    $('#customer-fields').hide();
    $productSearch.focus();
});

$('#new-customer').on('click', function () {
    var $customerFields = $('#customer-fields');
    $customerFields.find('input[type=text], input[type=email], input[type=hidden]').val('');
    $customerFields.show();
});

// Prevent submission by enter click
function checkEnter(e) {
    e = e || event;
    var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
    return txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
}

$form[0].onkeypress = checkEnter;

$('.product-qty').TouchSpin({
    min: 1,
    max: 40,
    buttonup_class: 'btn btn-default',
    buttondown_class: 'btn btn-default',
    initval: 1
});