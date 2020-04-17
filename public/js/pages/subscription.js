var $formSteps = $(".form-steps");

$formSteps.steps({
    headerTag: "h6",
    titleTemplate: '#title#',
    bodyTag: "fieldset",
    transitionEffect: "fade",
    enablePagination: false
});

// Manually add buttons actions
$('.btn-step-next').on('click', function () {
    $formSteps.steps('next');
    $('#summary-module').text($(this).data('module-name'));
    $('#subscription_module').val($(this).data('module-id'));
});

/**
 * Events on clicking of payment methods: Open accordion when clicking on radio buttons.
 */
$('input[name$="[method]"]').on('click', function ()
{
    var $parent = $(this).closest('.panel-heading');
    $parent.find('a').click();
});

$('input[name$="[donations]"]').on('click', function ()
{
    $('#summary-price').text('$' + $(this).val());
    $('#summary-total').text('$' + $(this).val());
    $('#subscription_amount').val($(this).val());
});

$('#subscription_purchase').on('click', function (e) {
    e.preventDefault();

    var $form = $(this).closest('form'),
        formName = $form.attr('name'),
        confirmedText = $('#payment_confirmed').val(),
        confirmationMessage = $('#confirmation_message').val();

    $form.find('.validation-error-label').remove();
    $form.find('.form-group').removeClass('has-error');
    $form.find('.tooltip').tooltip('destroy');

    $.ajax({
        type: "POST",
        data: $form.serialize(),
        success: function (response) {
            $('#payment-body').html('<p>' + confirmedText + '</p><p>' + confirmationMessage + '</p>')
        },
        error: function (response) {
            var errors = response.responseJSON.errors;

            if (errors) {
                for (var field in errors) {
                    if (Object.keys(errors[field]).length > 1) {
                        for (var childError in errors[field]) {
                            var $childField = $form.find('input[name="' + formName + '[' + field + '][' + childError + ']' + '"]');
                            $childField.closest('.form-group').append('<label class="validation-error-label">' + errors[field][childError] + '</label>');
                        }
                    } else {
                        var $field = $form.find('input[name="' + formName + '[' + field + ']' + '"]'),
                            $error = '<label class="validation-error-label">' + errors[field][0] + '</label>';

                        if (!$field.hasClass('hidden')) $field.closest('.col-md-12').append($error);
                    }
                }
            }
        }
    });
});


/**
 * Show tooltip error above the $field and add red border to the form group
 *
 * @param $field
 * @param errorMsg
 */
function showTooltipError($field, errorMsg)
{
    $field.tooltip({trigger: 'hover', placement: 'top', title: errorMsg})
        .attr('data-original-title', errorMsg)
        .tooltip('fixTitle')
        .tooltip('show');

    $field.closest('.form-group').addClass('has-error');
}