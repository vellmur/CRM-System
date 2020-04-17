$(document).ready(function() {
    function goTab(hash) {
        var tab = $('.nav-tabs a[href$="' + hash + '"]');
        tab.trigger('click');
    }

    // If renewal tab have errors, show market tab after load
    if ($('#market').find('.alert').length) {
        goTab('market');
    }

    // Enable tab from link
    var hash = $.trim(window.location.hash);
    if (hash) goTab(hash);
});

// Clear hash
$('#_submit').on('click', function () {
    if (hash) {
        window.location.hash = '';
    }
});

// Saving of customer profile
var $status = $('.save_status'),
    timeoutId;

// Auto-saving of membership profile
$('#profileForm').on('keyup change', function () {
    $status.text($status.data('changes-made'));

    // If a timer was already started, clear it.
    if (timeoutId) clearTimeout(timeoutId);

    var $form = $('#profileForm'),
        $testimonialMailBlock = $('#testimonial-mail'),
        testimonialText = $('#member_testimonial').val();

    // Set timer that will save comment when it fires.
    timeoutId = setTimeout(function () {
        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            dataType: 'json',
            data: $form.serialize(),
            success: function (response) {
                // testimonialText.length ? $testimonialMailBlock.show() : $testimonialMailBlock.hide();

                // Make ajax call to save data.
                $status.text($status.data('autosaved'));

                // Remove errors on fields
                $form.find('.has-error').removeClass('has-error');
                $form.find('.validation-error-label').remove();
            },
            error: function (response) {
                var formErrors = jQuery.parseJSON(response.responseJSON).error.children;

                // For each input win form show errors
                $.each(formErrors, function(name, input) {
                    // If form has errors
                    if (input.errors !== undefined) {
                        var inputId = "member_" + name;
                        var field = $form.find('#' + inputId);

                        field.parent().find('label').remove();

                        // For jquery validation forms
                        if ($form.hasClass('jquery-validation')) {
                            var error = '<label id="' + inputId + '-error" class="validation-error-label" for="' + inputId + '">' + input.errors[0] + '</label>';
                            field.parent().append(error);
                        } else {
                            field.tooltip({trigger: 'manual', placement: 'top', title: input.errors[0]})
                                .attr('data-original-title', input.errors[0])
                                .tooltip('fixTitle')
                                .tooltip('show');
                        }

                        field.closest('.form-group').addClass('has-error');
                    }
                });
            }
        });
    }, 750);
});

$('#button-send-email').on('click', function () {
    var $button = $('#send-testimonial-btn'),
        $firstName = $('#member_testimonialRecipient_firstname'),
        $lastName = $('#member_testimonialRecipient_lastname'),
        $email = $('#member_testimonialRecipient_email'),
        message = $('#member_testimonial').val();

    if ($firstName.val().length && $lastName.val().length && $email.val().length && message.length) {
        $button.addClass('custom-read-only');

        var path = Routing.generate('membership_send_testimonial', {'id': $('#member-id').val() });

        $.ajax({
            url: path,
            type: "POST",
            data: {firstname: $firstName.val(), lastname : $lastName.val(), email: $email.val(), message: message},
            success: function (response) {
                $firstName.val('');
                $lastName.val('');
                $email.val('');

                showInfoModal('Testimonial was sent.');
            },
            error: function (response) {
                showInfoModal(response.responseJSON.error);
            },
            complete: function () {
                $button.removeClass('custom-read-only');
            }
        });
    } else {
        showInfoModal('Testimonial can`t be sent, because fields are not filled.');
    }

    $('#send-testimonial').modal('toggle');
});

function showInfoModal(message) {
   var $infoModal = $('#information-modal'),
       $infoModalTitle = $('#information-modal-title'),
       $infoModalMessage = $('#information-modal-message');

    $infoModalTitle.text(message);
    $infoModalMessage.text(message);
    $infoModal.modal('toggle');
}

/**
 * Update prototype of addresses collection. Change classes of labels and fields to make them fit on each screen
 * This page need this action because only this page have 2 big form in one row
 * */
var collectionPrototype = $('.address-collection');
var prototype = collectionPrototype.data('prototype');
prototype = prototype.replace(new RegExp("col-md-2 col-sm-3", 'g'), 'col-md-3 col-sm-3');
prototype = prototype.replace(new RegExp("col-md-10 col-sm-9 col-xs-7", 'g'), 'col-md-9 col-sm-9 col-xs-7');
collectionPrototype.data('prototype', prototype);

$(document.body).on('click', '.btn-number', function(e) {
    e.preventDefault();
    var fieldName = $(this).attr('data-field');
    var type = $(this).attr('data-type');
    var input = $(this).parent().find("input[name='"+fieldName+"']");
    var currentVal = parseInt(input.val());

    if (!isNaN(currentVal)) {

        var totalPriceBlock = $('#priceTotal').find('span');
        var totalPrice = totalPriceBlock.text();

        if (type == 'minus') {
            if(currentVal > input.attr('min')) {
                totalPrice = parseFloat(totalPrice) - parseFloat(input.attr('data-price'));
                totalPriceBlock.text(formatCurr(totalPrice));
                input.val(currentVal - 1).change();

            } else {
                $(this).attr('disabled', true);
            }

        } else if (type == 'plus') {
            var maxValue = formatCurr(parseFloat($('#maxShareAmount').find('span').text()));
            totalPrice = parseFloat(totalPrice) + parseFloat(input.attr('data-price'));
            if (currentVal < input.attr('max') && totalPrice < maxValue) {
                input.val(currentVal + 1).change();
                totalPriceBlock.text(formatCurr(totalPrice));
            } else if (totalPrice > maxValue) {
                var msg = 'Sorry, but max value of total share price is: ' + '$' + maxValue;

                var fieldBlock = input.parent().closest('td').prev();
                var tooltipBlock = input.parent();

                tooltipBlock.tooltip({ trigger: 'manual', placement: 'top', title: msg }).attr('data-original-title', msg).tooltip('fixTitle').tooltip('show');
                fieldBlock.addClass('has-error');

                setTimeout(function(){
                    fieldBlock.removeClass('has-error');
                    tooltipBlock.tooltip('destroy');
                }, 5000);
            } else {
                $(this).attr('disabled', true);
            }
        }

    } else {
        input.val(0);
    }
});

$(document.body).on('change', "input[id^='sharePlantQty']", function ()
{
    var id = $(this).attr('data-id');

    $.ajax({
        url: $(this).attr('data-path'),
        type: "POST",
        data: { id: id, qty: $(this).val() },
        success: function (response) {
            console.log(response)
        },
        error: function (response) {
            console.log(response)
        }
    });
});

/**
 * Function for customize share products in Membership profile -> Customize tab
 */
$("select[id^='shareProduct']").on('change', function ()
{
    var $this = $(this);

    var customId = $(this).data('custom');
    var shareProductId = $(this).data('product');
    var productId = $(this).val();

    $.ajax({
        url: $(this).attr('data-path'),
        type: "POST",
        data: { productId: productId, shareProductId: shareProductId, customId: customId },
        success: function (response) {
            if ($this.data('custom').length === 0) {
                $this.data('custom', response.customId);
            }
        },
        error: function (response) {
            console.log(response)
        }
    });
});


$("input[id^='plantInShare']").on('change', function ()
{
    var $this = $(this);

    if ($this.is(':checked')) {
        var shareId = null;
        var checked = true;
    } else {
        shareId = $(this).attr('data-share-id');
        checked = false;
    }

    $.ajax({
        url: $(this).attr('data-path'),
        type: "POST",
        data: { id: shareId, memberId: $(this).attr('data-member-id'), productId: $(this).attr('data-id'), checked: checked },
        success: function (response) {
            var totalPrice = $('#priceTotal').find('span').text();

            var tr = $this.closest('tr');
            var tbody = tr.next().parent();
            var tdNum = tr.children().length;

            // If checked add new product to client custom share
            if ($this.is(':checked')) {
                var result = jQuery.parseJSON(response).response;

                if (result.proceed == false) {
                    $this.attr('checked', false);

                    var fieldBlock = $this.parent().closest('.td-buttons');
                    var tooltipBlock = $this.parent().parent().parent();

                    $this.parent().removeAttr('class');

                    tooltipBlock.tooltip({ trigger: 'manual', placement: 'top', title: result.message }).attr('data-original-title', result.message).tooltip('fixTitle').tooltip('show');
                    fieldBlock.addClass('has-error');

                    setTimeout(function(){
                        fieldBlock.removeClass('has-error');
                        tooltipBlock.tooltip('destroy');
                    }, 5000);

                    return;
                } else {
                    var share = result.share;
                }

                // create array of plant names
                var names = [share.plant];

                // push to array all plants form customize share table
                for (var i = 0; i <= tbody.children().length; i++) {
                    var row = tbody.children().eq(i);
                    var plantName = row.children().eq(tdNum - 3);

                    if (plantName.attr('id') == 'nameTotal') break;
                    names.push(plantName.html());
                }

                // sort array of names alphabetically and find position for new row
                names.sort();
                var appendPos = names.indexOf(share.plant);

                // same loop as previous but start form needed row
                for (var k = appendPos; k <= tbody.children().length; k++) {
                    // find needed data for replace
                    var currentName = tbody.children().eq(k).children().eq(tdNum - 3);
                    var currentWeight = currentName.next();
                    var currentPrice = currentWeight.next();

                    if (k == appendPos) {
                        // save this data for the next row
                        var nextNameCode = currentName.prop('outerHTML');
                        var nextWeightCode = currentWeight.prop('outerHTML');
                        var nextPriceCode = currentPrice.prop('outerHTML');

                        // if we at the first step append data from database
                        currentName.html(share.plant);
                        currentName.attr('id', 'share' + share.id);
                        currentWeight.html(share.weight);
                        currentPrice.find('span').text(share.price);

                        if ($(nextPriceCode).find('#priceTotal')) currentPrice.attr('id', 'sharePrice');

                        continue;
                    }

                    // save this data for the next row
                    var nameCode = currentName.prop("outerHTML");
                    var weightCode = currentWeight.prop("outerHTML");
                    var priceCode = currentPrice.prop("outerHTML");

                    // Swap currData with next
                    currentName.replaceWith(nextNameCode);
                    currentWeight.replaceWith(nextWeightCode);
                    currentPrice.replaceWith(nextPriceCode);

                    // Save data for the next loop (row)
                    nextNameCode = nameCode;
                    nextWeightCode = weightCode;
                    nextPriceCode = priceCode;
                }

                totalPrice = parseFloat(totalPrice) + parseFloat(share.price);

                $this.attr('data-share-id', share.id);

                var qtyButtons = '' +
                    '<div class="input-group">' +
                        '<button type="button" class="btn btn-icon btn-icon-mini btn-danger btn-number"  data-type="minus" data-field="qty"><i class="icon-minus"></i></button>' +
                        '<input type="text" name="qty" id="sharePlantQty' + share.id + '" class="input-qty" data-id="' + share.id + '" data-price="' + share.price + '" data-path="' + share.path + '" value="' + share.qty + '" min="1" max="10">' +
                        '<button type="button" class="btn btn-icon btn-icon-mini btn-success btn-number" data-type="plus" data-field="qty"><i class="icon-plus"></i></button>' +
                    '</div>';

                $this.closest('tr').find('#shareQty').html(qtyButtons);
            } else {

                for (var i = 0; i <= tbody.children().length; i++) {
                    var findRow = tbody.children().eq(i);
                    var findName = findRow.children().eq(tdNum - 3);

                    // If we found custom share row with deleted plant
                    if (findName.attr('id') == 'share' + shareId) {
                        var sharePrice = findRow.find('#sharePrice').find('span').text();

                        for (var key = i; key <= tbody.children().length; key++) {
                            var currentRow = tbody.children().eq(key);
                            var nextRow = currentRow.next();

                            var nextName = nextRow.children().eq(tdNum - 3);

                            if (nextName.attr('id') !== undefined) {
                                var nextWeight = nextName.next();
                                var nextPrice = nextWeight.next();

                                // get current data
                                var currName = currentRow.children().eq(tdNum - 3);
                                var currWeight = currName.next();
                                var currPrice = currWeight.next();

                                // Swap currData with next
                                currName.replaceWith(nextName.prop("outerHTML"));
                                currWeight.replaceWith(nextWeight.prop("outerHTML"));
                                currPrice.replaceWith(nextPrice.prop("outerHTML"));

                                // Clear next td
                                nextName.replaceWith('<td></td>');
                                nextWeight.replaceWith('<td></td>');
                                nextPrice.replaceWith('<td></td>');
                            }
                        }

                        break;
                    }
                }
                totalPrice = parseFloat(totalPrice) - (parseFloat(sharePrice) * parseInt(tr.find('#sharePlantQty' + shareId).val()));

                // remove share id and remove share qty buttons
                $this.removeAttr('data-share-id');
                tr.find("#shareQty").empty();
            }

            $('#priceTotal').find('span').text(formatCurr(totalPrice));
        },
        error: function (response) {
            console.log(response)
        }
    });
});

$(document.body).on('change', '.input-qty',  function()
{
    var minValue = parseInt($(this).attr('min'));
    var maxValue = parseInt($(this).attr('max'));
    var valueCurrent = parseInt($(this).val());

    var name = $(this).attr('name');

    if (valueCurrent >= minValue) {
        $(this).parent().find(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
    } else {
        $(this).val($(this).data('oldValue'));
    }

    if (valueCurrent <= maxValue) {
        $(this).parent().find(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
    } else {
        $(this).val($(this).data('oldValue'));
    }

});

function formatCurr(num)
{
    var p = num.toFixed(2).split(".");

    return p[0].split("").reverse().reduce(function(acc, num, i, orig) {
            return  num == "-" ? acc : num + (i && !(i % 3) ? "," : "") + acc;
        }, "") + "." + p[1];
}