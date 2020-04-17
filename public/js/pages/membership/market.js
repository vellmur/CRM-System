$(function () {
    function SelectorCache() {
        var elementCache = {};

        var get_from_cache = function (selector, $ctxt, reset) {
            if ('boolean' === typeof $ctxt) {
                reset = $ctxt;
                $ctxt = false;
            }
            var cacheKey = $ctxt ? $ctxt.selector + ' ' + selector : selector;

            if (undefined === elementCache[cacheKey] || reset) {
                elementCache[cacheKey] = $ctxt ? $ctxt.find(selector) : jQuery(selector);
            }

            return elementCache[cacheKey];
        };

        get_from_cache.elementCache = elementCache;
        return get_from_cache;
    }

    var cache = new SelectorCache();

    var translatedLabels = {
        'en': {
            'expired_order_time': 'The time to order on this date has past.',
            'delete': 'Delete',
            'delivery': 'Delivery',
            'subtotal_plus_delivery': 'Subtotal + Delivery',
            'billing_address': 'Billing address',
            'delivery_address': 'Delivery address',
            'billing_and_delivery_address': 'Billing and Delivery address',
            'delivery_date': 'Delivery date',
            'delivery_day': 'Delivery day',
            'pickup_day': 'Pickup day',
            'pickup_date': 'Pickup date'
        },
        'ru': {
            'expired_order_time': 'Время для заказа на эту дату истекло.',
            'delete': 'Удалить',
            'delivery': 'Доставка',
            'subtotal_plus_delivery': 'Подытог + Доставка',
            'billing_address': 'Платежный адрес',
            'delivery_address': 'Адрес доставки',
            'billing_and_delivery_address': 'Платежный и адрес доставки',
            'delivery_date': 'Дата доставки',
            'delivery_day': 'День доставки',
            'pickup_day': 'День получения',
            'pickup_date': 'Дата получения'
        },
        'ua': {
            'expired_order_time': 'Час для замовлення на цю дату вийшов.',
            'delete': 'Видалити',
            'delivery': 'Доставка',
            'subtotal_plus_delivery': 'Підсумок + Доставка',
            'billing_address': 'Платіжна адреса',
            'delivery_address': 'Адреса доставки',
            'billing_and_delivery_address': 'Платіжна і адреса доставки',
            'delivery_date': 'Дата доставки',
            'delivery_day': 'День доставки',
            'pickup_day': 'День отримання',
            'pickup_date': 'Дата отримання'
        }
    };

    var trans = translatedLabels[cache('#client-language').val()];

    $('.styled').uniform({
        radioClass: 'choice'
    });

    // Credit Card masks
    $('#cardNumber').mask('9999 9999 9999 9999', {placeholder: ' ', autoclear: false});
    $('#cardExp').mask('99 / 99', {placeholder: ' ', autoclear: false});
    $('#csc').mask('9999', {placeholder: ' ', autoclear: false});

    if (window.innerWidth < 620) {
        cache('.list-style-buttons').hide();
    }

    cache(window).resize(function () {
        var $listModeButtons = cache('.list-style-buttons');

        if (window.innerWidth < 620) {
            if (cache('#market-products-list').hasClass('grid-mode')) {
                $listModeButtons.find('.list-view').click().trigger('click');
            }

            $listModeButtons.hide();
        } else {
            $listModeButtons.show();
        }
    });

    // Set position of market header and cart after page load
    setTimeout(function () {
        var pageHeader = document.getElementById('header'),
            headerHeight = 0;

        // Get height of global page header if its fixed in order to set correct distance for market header
        if (pageHeader) {
            var headerStyles = getComputedStyle(pageHeader);

            if (headerStyles.position === 'fixed' || headerStyles.position === 'sticky' || headerStyles.position === '-webkit-sticky') {
                headerHeight = pageHeader.offsetHeight;
            }
        }

        cache('#market-header-nav').css('top', headerHeight);
        cache('#cart-block').css('top', getHiddenElementHeight($('#market-header-nav')) + headerHeight + 5);
    }, 2000);

    // Hack in order to get element height if its hidden
    function getHiddenElementHeight(element) {
        var tempId = 'tmp-' + Math.floor(Math.random() * 99999);

        $(element).clone()
            .css('position','absolute')
            .css('height','auto')
            .appendTo($('body'))
            .css('left','-10000em')
            .attr('id', tempId)
            .show();

        var $clone = $('#' + tempId),
            height = $clone.outerHeight();

        $clone.remove();

        return height;
    }

    // Filter products by a selected product type, hide unselected products and remove already selected from the cart
    cache('.product-filter').on('click', function (e) {
        e.preventDefault();

        var $this = $(this),
            filterBy = $this.data('filter-by');

        if (filterBy === 'all') {
            cache('.product-item').show();
        } else {
            $('.product-item[data-filters*="' + filterBy + '"]').show();
            cache('.product-item').not('[data-filters*="' + filterBy + '"]').hide();
        }

        cache('.product-filter').removeClass('label-action').addClass('label-default');
        $this.removeClass('label-default').addClass('label-action');

        if (cache('#cart-block').css('position') === 'fixed' && cache('#market-products-list').height() < cache('#cart-block').height()) {
            cache('#cart-block').css({'position': 'relative', 'top': 0});
            cache('#market-header-nav').css({'position': 'relative', 'top': '0', 'width': 'none'});
        }

        fixGridStyles();
    });

    // Switch between grid and list mode
    $('.list-view, .grid-view').on('click', function (e) {
        e.preventDefault();

        var $this = $(this),
            $sections = cache('#market-products-list').find('.products-section');

        if ($this.hasClass('list-view') && cache('#market-products-list').hasClass('grid-mode')) {
            $sections.removeClass('grid-items').addClass('list-items');
            cache('button.grid-view').removeClass('btn-action').addClass('btn-default');
            cache('button.list-view').removeClass('btn-default').addClass('btn-action');
            cache('#market-products-list').removeClass('col-md-12').addClass('col-md-8 col-xs-6');
            cache('#market-products-list').removeClass('grid-mode').addClass('list-mode');
            cache('#cart-block').show();
            cache('#grid-continue-btn').hide();
        } else if ($this.hasClass('grid-view') && cache('#market-products-list').hasClass('list-mode')) {
            $sections.removeClass('list-items').addClass('grid-items');
            cache('button.grid-view').removeClass('btn-default').addClass('btn-action');
            cache('button.list-view').removeClass('btn-action').addClass('btn-default');
            cache('#market-products-list').removeClass('col-md-8 col-xs-6').addClass('col-md-12');
            cache('#market-products-list').removeClass('list-mode').addClass('grid-mode');
            cache('#cart-block').hide();
            cache('#grid-continue-btn').show();
        }

        fixGridStyles();
    });

    // Fix grid styles and show/hide products sections labels
    function fixGridStyles() {
        cache('#market-market').find('.products-section').each(function () {
            var $this = $(this),
                productsItems = $this.find('.product-item:visible'),
                $productsSectionTitle = $this.parent().find('.products-section-title');

            if (productsItems.length) {
                if (cache('#market-market').hasClass('grid-mode')) {
                    productsItems.each(function (i) {
                        i++;

                        if (i % 3 === 0) {
                            $(this).css({'margin-right': '0'});
                        } else {
                            $(this).css({'margin-right': '2%'});
                        }
                    });
                }

                $productsSectionTitle.show();
            } else {
                $productsSectionTitle.hide();
            }
        });
    }

    // Event on clicking -/+ weeks of some share or product
    $('.product-plus, .product-minus').on('click', function (e) {
        var $this = $(this),
            $productQty = $this.closest('.product-item').find('.product-qty'),
            value = $this.hasClass('product-plus') ? parseFloat($productQty.val()) + 1 : parseFloat($productQty.val()) - 1;

        $productQty.val(value).trigger('change');
    });

    // Event on clicking -/+ weeks of some share
    $('.product-qty').on('change', function (e) {
        e.preventDefault();

        // Get product elements with all info (price, value, name, qty)
        var $productQty = $(this),
            $productItem = $productQty.closest('.product-item').children().first(),
            $product = cache('#' + $productQty.data('product-id')),
            $cartSection = cache('#' + $productQty.data('cart-section')),
            $productInCart = $cartSection.find('#' + $product.attr('id') + '_total'),
            price = parseFloat($product.data('price')).toFixed(2),
            productTotalPrice = parseFloat(price * $productQty.val()).toFixed(2);

        // Product qty can be only in a range from 0 to 40
        if ($productQty.val() > 0) {
            if ($productQty.val() > 40) {
                $productQty.val(40);
            } else {
                if (!$product.is(':checked')) {
                    $product.prop('checked', true);
                    $productItem.addClass('product-tile-is-in-basket');
                    $productItem.find('.product-add-count').removeClass('is-transparent');
                }

                // If product already exists in a cart, just update it
                if ($productInCart.length > 0) {
                    $productInCart.find('.productQty').text($productQty.val());
                    $productInCart.find('.productPrice').text(productTotalPrice);
                } else {
                    var weightLabel = !$productQty.data('paybyitem') ? cache('#weight-format').val() : '';

                    // Create description of product for the cart
                    var productInfo = '<p id="' + $product.attr('id') + '_total"><span class="productQty">' + $productQty.val() + ' </span>' + ' ' + weightLabel + ' - '
                        + $product.data('description') + '<span class="pull-right">' + cache('#currency-format').val() + '<span class="productPrice">'
                        + productTotalPrice + '</span></span></p>';

                    $cartSection.append(productInfo);
                }
            }
        } else {
            if ($productQty.val() < 0) $productQty.val(0);

            if ($product.is(':checked')) {
                $product.prop('checked', false);
                $productInCart.remove();
                $productItem.removeClass('product-tile-is-in-basket');
                $productItem.find('.product-add-count').addClass('is-transparent');
            }
        }

        $productItem.find('.product-spinner').text($productQty.val());
        countTotalPrice();
    });

    // Count total price for all shares and products in a cart
    function countTotalPrice() {
        var productPrices = cache('.cart-list').find('.productPrice'),
            totalPrice = 0;

        for (var i = 0; i < productPrices.length; i++) {
            totalPrice += parseFloat(productPrices[i].textContent);
        }

        var deliveryPrice = cache('#client-delivery-price').val(),
            deliveryAmount = 0;

        // Count delivery amount
        if (totalPrice >= deliveryPrice || totalPrice === 0) {
            cache('#deliveryPrice').text('0.00');
            cache('#summary-delivery').html('0.00');
        } else {
            deliveryAmount = deliveryPrice - totalPrice;
            cache('#deliveryPrice').text(deliveryAmount.toFixed(2));
            cache('#summary-delivery').html((totalPrice + deliveryAmount).toFixed(2));
        }

        cache('#subTotal').text(totalPrice.toFixed(2));
        cache('#total').text((totalPrice + deliveryAmount).toFixed(2));

        // Append total prices to the summary (last tab)
        cache('#summary-subTotal').html(cache('#subTotal').text());
        cache('#summary-total').html(cache('#total').text());
    }

    // Event on clicking of steps next button
    $('.button-next').on('click', function () {
        var $step = $(this).closest('.panel'),
            $checkedMethod = $('input[name="renew[method]"]:checked'),
            skipPayment = cache('input[name="renew[method]"]').length === 1 && $checkedMethod.data('type') !== 'card' ? 1 : 0;

        if ($checkedMethod.data('type') !== 'card') {
            cache('#renew_isNeedBilling').hide();
            cache('#renew_isNeedBilling_0').prop('checked', false).trigger('change').parent().removeClass('checked');
        } else {
            cache('#renew_isNeedBilling').show();
        }

        try {
            switch ($step.data('step')) {
                case 'market':
                    var skipCustomer = $step.next().find('.validation-error-label').length === 0 && cache('#renew_member_firstname').val()
                    && cache('#renew_member_lastname').val() && (cache('#renew_member_email').val() || cache('#renew_member_phone').val()) ? 1 : 0;

                    if (skipCustomer === 1 && skipPayment === 1) {
                        skipCustomer += 1;
                    }

                    goForwardStep($step, skipCustomer);

                    break;
                case 'customer':
                    goForwardStep($step, skipPayment);
                    break;
                case 'payment':
                    goForwardStep($step);

                    break;
                case 'location':
                    // Skip addresses step if payment method is not credit card and delivery not to customer home
                    var $location = $('input[name$="[location]"]:checked'),
                        isFilledAddress = cache('#renew_locationAddress_street').val() && cache('#renew_locationAddress_postalCode').val()
                            && cache('#renew_locationAddress_region').val() && cache('#renew_locationAddress_city').val() && cache('#renew_isNeedBilling_0').is(':visible');

                    var skipAddresses = ($location.data('location') !== 'delivery' && $checkedMethod.data('type') !== 'card') || isFilledAddress ? 1 : 0;
                    goForwardStep($step, skipAddresses);

                    break;
                case 'addresses':
                    goForwardStep($step);
                    break;
            }
        } catch (error) {
            console.error(error)
        }
    });

    // Remove previous validation errors and re-validate form, if valid go forward to the next step
    function goForwardStep($step, skipStep) {
        skipStep || (skipStep = 0);

        $step.find('.validation-error-label').remove();
        $step.find('.error-feedback').empty();
        $step.find('.has-error').removeClass('has-error');

        var $inputs = $step.find('input');

        // Run jQuery validation on a current market purchase step
        if ($inputs.length) {
            $inputs.valid();

            var stepErrors = $step.find('.validation-error-label');

            // If step has validation errors, set focus to the first field with error inside a step
            if (stepErrors.length) {
                var $firstErrorLabel = $(stepErrors[0]);
                cache('#' + $firstErrorLabel.attr('for')).focus();
                throw 'Validation error! ' + $firstErrorLabel.attr('for') + ': ' + $firstErrorLabel.text();
            }
        }

        // Get next step or next step after N skipped steps, if 'skipSteps' > 0
        var $nextStep = skipStep !== 0 ? $step.nextAll().slice(0, skipStep + 1).last() : $step.next();

        goToMarketTab($nextStep.data('step'));
    }

    // Change step by using links on navigation
    function goToMarketTab(step) {
        var $stepBlock = cache('a[href="#market-' + step + '"]').closest('.panel');

        if ($stepBlock.length > 0) {
            var $steps = cache('div[data-step]'),
                index = $stepBlock.index();

            // Hide `CHANGE` button of active step and next steps, show button for all previous steps
            for (var i = 0; i <= $steps.length; i++) {
                var $step = $($steps[i]),
                    $stepBtn = $step.find('.btn-step');

                if (i !== index) {
                    if (i < index) {
                        $step.show();
                    } else {
                        $step.hide();
                    }

                    $stepBtn.show();
                } else {
                    $stepBtn.hide();
                }
            }

            $stepBlock.show();
            cache('a[href="#market-' + step + '"]').click();

            savePurchaseStepViewing(step);

            // Append summary
            if (step === 'summary') {
                appendSummaryData();
            }
        }
    }

    // Append data from all market tabs to summary tab
    function appendSummaryData() {
        var $shares = $('input[id^="renew_shares"]:checked'),
            $products = $('input[id^="renew_products"]:checked'),
            $location = $('input[name$="[location]"]:checked'),
            $paymentMethod = $('input[name="renew[method]"]:checked');

        cache('#summary-shares').html(getProductsItemsTemplate($shares));
        cache('#summary-products').html(getProductsItemsTemplate($products));

        cache('#delivery-label').text(parseFloat(cache('#subTotal').text()) < cache('#client-delivery-price').val()
            ? trans['subtotal_plus_delivery'] : trans['delivery']);

        cache('#customer-data').html(
            '<p>' + cache('#renew_member_email').val() + '</p><p>' + cache('#renew_member_phone').val() + '</p>' +
            '<p>' + cache('#renew_member_firstname').val().toUpperCase() + ' ' + cache('#renew_member_lastname').val().toUpperCase() + '</p>'
        );

        cache('#payment-data').html($paymentMethod.closest('.choice').next().find('label').text());

        var $pickupDay = $location.closest('.panel').find('table').find('span[class="checked"]');

        cache('#summary-location').html($location.closest('.panel-heading').find('label').text());
        cache('#summary-shareDay-label').html($location.data('location') === 'delivery' ? trans['delivery_day'] : trans['pickup_day']);
        cache('#summary-shareDay').html($pickupDay.closest('label').text().replace(/\s/g, ""));
        cache('#summary-shareDate-label').html($location.data('location') === 'delivery' ? trans['delivery_date'] : trans['pickup_date']);
        cache('#summary-shareDate').html($pickupDay.find('input').val());

        // Show/Hide addresses section in the summary, if addresses step shows/hidden
        if ($location.data('location') === 'delivery' || $paymentMethod.data('type') === 'card') {
            var region = cache('#renew_locationAddress_region').val(),
                city = cache('#renew_locationAddress_city').val(),
                isNeedBilling = cache('#renew_isNeedBilling_0').is(':checked');

            cache('#location-data').html(
                '<p class="bold-text">' + (isNeedBilling ? trans['delivery_address'] : trans['billing_and_delivery_address']) + ':</p>' +
                '<p>' + cache('#renew_locationAddress_street').val() + ' ' + cache('#renew_locationAddress_apartment').val() + '</p>' +
                '<p>' + cache('#renew_locationAddress_postalCode').val() + '</p>' +
                '<p>' + region + (region && city ? ', ' : '') + city + '</p>'
            );

            var $billAddress = cache('#billAddress');

            // If billing address added, check validation for billing address and append billing
            if (isNeedBilling) {
                var billRegion = cache('#renew_billingAddress_region').val(),
                    billCity = cache('#renew_billingAddress_city').val();

                cache('#billing-address-data').html(
                    '<p class="bold-text">' + trans['billing_address'] + ':</p><p>' + cache('#renew_billingAddress_street').val() + ' ' + cache('#renew_billingAddress_apartment').val() + '</p>' +
                    '<p>' + cache('#renew_billingAddress_postalCode').val() + '</p><p>' + billRegion + (billRegion && billCity ? ', ' : '') + billCity + '</p>'
                );

                $billAddress.show();
            } else {
                cache('#billing-address-data').empty();
                $billAddress.hide();
            }

            cache('#summary-addresses').show();
        } else {
            cache('#summary-addresses').hide()
        }
    }

    function getProductsItemsTemplate($products) {
        var productsItems = '',
            currencyFormat = cache('#currency-format').val(),
            weightFormat = cache('#weight-format').val();

        for (var i = 0; i < $products.length; i++) {
            var $product = cache('#' + $products[i].id),
                qty = parseFloat($product.next().val()),
                totalPrice = parseFloat($product.data('price')) * qty;

            productsItems += '<div class="row">' +
                '<div class="summary-product-item col-md-12">' +
                '<div class="col-md-4 summary-label"><span>' + $product.data('description') + '</span></div> ' +
                '<div class="col-md-3"> ' +
                '<div class="input-group"> ' +
                '<input type="text" class="summary-product" value="' + qty + '" data-product-qty-id="' + $product.next().attr('id') + '" onkeydown="return false"> ' +
                '</div></div> ' +
                '<div class="col-md-2 summary-label">' +
                '<a type="button" class="red-text btn-product-delete">' + trans['delete'] + '</a> ' +
                '</div>' +
                '<div class="col-md-3 text-right summary-label">'
                + currencyFormat +
                '<span class="summaryProductTotal">' + totalPrice.toFixed(2) + '</span>' +
                '</div></div></div>';
        }

        var $productsItems = $(productsItems);

        $productsItems.find('.summary-product').TouchSpin({
            buttondown_class: "btn btn-default btn-summary-down",
            buttonup_class: "btn btn-default btn-summary-up",
            postfix: weightFormat,
            min: 1,
            max: 40
        });

        return $productsItems;
    }

    // Save market view, if page is'nt profile (in profiles, viewing is saved only after clicking on renewal tab)
    if (window.location.pathname.indexOf('/membership/member/profile/') === -1) {
        savePurchaseStepViewing('market');
    }

    // Save viewing of purchase steps for the market statistics
    function savePurchaseStepViewing(step) {
        var clientId = $("#client-id").val(),
            customerId = $("#customer-id").val(),
            path = $("#save_view_path").val();

        $.ajax({
            url: path,
            dataType: "jsonp",
            data: {'clientId': clientId, 'customerId': customerId, 'step': step},
            success: function () {
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

    cache('.btn-step').on('click', function () {
        goToMarketTab($(this).closest('.panel').data('step'));
    });

    // Open accordion when clicking on radio buttons
    $('input[name$="[location]"]').on('click', function () {
        var $parent = $(this).closest('.panel-heading');
        $parent.find('a').click();

        var $daysPanel = $parent.next(),
            $shareDates = $daysPanel.find('input[id^="order_date"]');

        if ($shareDates.length === 1) {
            $shareDates[0].click();
        } else {
            var $checkedBefore = $('#locationAccordion').find('input[id^="order_date"]:checked');
            $checkedBefore.prop('checked', false);
            $checkedBefore.parent().removeClass('checked');
            cache('#renew_shareDate').val('');
        }
    });

    // Send email about not configured merchant to client just once
    var isMethodEmailSent = 'false';

    /**
     * Events on clicking on payment methods:
     * 1. Open accordion when clicking on radio buttons.
     * 2. Check if selected payment method configured by client and disable/enable purchase button, show/remove error msg
     */
    $('input[name$="[method]"]').on('click', function () {
        var $parent = $(this).closest('.panel-heading'),
            path = cache('#market-payment').data('check-path') + '/' + $(this).val() + '/' + isMethodEmailSent,
            button = cache('#renew_renewSubmit');

        $parent.find('a').click();

        // If customer selected credit card payment, check if merchant configured for the client
        if ($(this).data('type') === 'card') {
            /*$.ajax({
             url: path,
             type: "POST",
             success: function (response) {
             },
             error: function (response) {
             // Disable purchase button and show error message, if merchant or key isn`t configured
             button.attr('disabled', 'disabled');
             button.closest('.row').prepend('<div class="alert alert-danger no-border">' + response.responseJSON.error + '</div>');

             // If error appended, email to client about not configured merchant was sent
             if (isMethodEmailSent === 'false') isMethodEmailSent = 'true';
             }
             });*/
        } else {
            // Enable purchase button and remove error message
            button.closest('.row').find('.alert').remove();
            button.removeAttr('disabled');
        }
    });

    // Enable countdowns for a pickup dates
    $('span[id^="pickup_nextOrderDate_timer"]').each(function () {
        var $this = $(this),
            pickupTime = moment.tz($this.data('datetime'), cache('#client-timezone').val());

        if (!cache('#client-same-day-orders-allowed').val()) pickupTime = pickupTime.subtract(1, 'days');

        $this.countdown(pickupTime.toDate(), function (event) {
            $this.text(event.strftime('%D:%H:%M:%S'));
        }).on('finish.countdown', function () {
            // Disable cu
            disableChoice($this.closest('tr').find('input[id^="order_date"]'));

            var $nextDay = $this.closest('tr').next();

            // Append timer to next location pickup/delivery date
            if ($nextDay.length) {
                var $nextTimer = $nextDay.find('.location-countdown'),
                    timeToNext = moment.tz($nextTimer.data('datetime'), cache('#client-timezone').val());

                // Disable next choice with timer if customer can order products only for the next day
                if (!cache('#client-same-day-orders-allowed').val()) {
                    timeToNext = timeToNext.subtract(1, 'days');
                }

                $nextTimer.countdown(timeToNext.toDate(), function (event) {
                    $nextTimer.text(event.strftime('%D:%H:%M:%S'));
                });
            }

            var timeExpiredText = trans['expired_order_time'];
            $this.parent().html('<span class="red-text">' + timeExpiredText + '</span>');
        });
    });

    // Disable, un-check and update styles for given choice element
    function disableChoice($choice) {
        if (!$choice.is(':disabled')) {
            $choice.prop('disabled', true);

            if ($choice.is(':checked')) {
                $choice.prop('checked', false);
            }

            $.uniform.update('#' + $choice.attr('id'));
        }
    }

    // Show/Hide billing address and skip/load jquery validation
    if (!cache('#renew_isNeedBilling_0').is(':checked')) cache('#billAddress').find('input').addClass('skip-field-validation');

    cache('#renew_isNeedBilling_0').on('change', function () {
        var $billingAddress = cache('#billAddress');

        if ($(this).is(':checked')) {
            $billingAddress.show();
            $billingAddress.find('input').removeClass('skip-field-validation');
        } else {
            $billingAddress.hide();
            $billingAddress.find('input').addClass('skip-field-validation');
        }
    });

    // Save share day value to a hidden field
    cache('input[id^="order_date"]').on('click', function () {
        cache('input[id$="orderDate"]').val($(this).val());
    });

    // Edit product qty on summary tab
    cache('body').on('change', '.summary-product', function (e) {
        var $this = $(this),
            $productQty = cache('#' + $this.data('product-qty-id'));

        $productQty.val($this.val()).trigger('change');

        var totalProductValue = cache('#' + $productQty.data('product-id') + '_total').find('.productPrice').text();
        $this.closest('.summary-product-item').find('.summaryProductTotal').text(totalProductValue);
    });

    // Delete product from cart and summary
    cache('body').on('click', '.btn-product-delete', function () {
        var $this = $(this),
            $summaryItem = $this.closest('.summary-product-item'),
            $product = $summaryItem.find('.summary-product');

        cache('#' + $product.data('product-qty-id')).val(0).trigger('change');
        $summaryItem.remove();
    });

    // Prevent double sending of invoice
    cache('#renew_renewSubmit').on('click', function () {
        var $button = $(this);

        $button.closest('form').submit(function () {
            $button.attr('disabled', 'disabled').addClass('custom-read-only');
        });
    });
});