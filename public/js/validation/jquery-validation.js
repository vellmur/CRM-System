let validationForm = $('.jquery-validation'),
    validatedForm = null;

$.validator.setDefaults({
    ignore: 'input[type=hidden], .select2-search__field, .skip-field-validation', // ignore hidden fields
    errorClass: 'validation-error-label',
    successClass: 'validation-valid-label',
    highlight: function(element, errorClass) {
        $(element).removeClass(errorClass);
    },
    unhighlight: function(element, errorClass) {
        $(element).removeClass(errorClass);
    },
    // Different components require proper error label placement
    errorPlacement: function(error, element) {
        // Validated hidden element must have container for the error
        if (element.hasClass('hidden')) {
            var errorHolder = element.closest('.row').find('.error-feedback');
            if (errorHolder === undefined || !errorHolder.is(':visible')) errorHolder = element.closest('.row');

            // Show alternative alert
            if (!errorHolder.find('.alert').length) {
                error.attr('class', 'alert alert-danger no-border full-width-error validation-error-label');
                errorHolder.append(error);
            }

            return;
        }

        // Styled checkboxes, radios, bootstrap switch
        if (element.closest('div').hasClass("choice") || element.parent().hasClass('bootstrap-switch-container')) {
            if (element.closest('label').hasClass('checkbox-inline') || element.closest('label').hasClass('radio-inline')) {
                error.appendTo(element.parent().parent().parent().parent());
            } else {
                if (element.closest('.panel-group').length) {
                    var parent = element.closest('.panel-group');

                    if (!parent.find('.validation-error-label').length) {
                        error.attr('class', 'alert alert-danger no-border full-width-error validation-error-label');
                        error.appendTo(parent);
                    }
                } else if (element.closest('.form-group')) {
                    error.appendTo(element.closest('.radio').parent());
                } else {
                    error.appendTo(element.closest('label').parent());
                }
            }
        }
        // Unstyled checkboxes, radios
        else if (element.closest('div').hasClass('checkbox') || element.closest('div').hasClass('radio')) {
            error.appendTo(element.parent().parent().parent());
        }
        // Input with icons and Select2
        else if (element.closest('div').hasClass('has-feedback') || element.hasClass('select2-hidden-accessible')) {
            error.appendTo(element.parent());
        }
        // Inline checkboxes, radios
        else if (element.closest('label').hasClass('checkbox-inline') || element.closest('label').hasClass('radio-inline') || element.hasClass('select')) {
            error.appendTo(element.parent().parent());
        }
        // Input group, styled file input
        else if (element.parent().hasClass('uploader') || element.parent().hasClass('input-group')) {
            error.appendTo(element.parent().parent());
        } else {
            error.insertAfter(element);
        }
    },
    validClass: "validation-valid-label",
    success: function(label) {
        label.remove();
    },
    rules: {
        password: {
            minlength: 5
        },
        repeat_password: {
            equalTo: "#password"
        },
        email: {
            email: true
        },
        repeat_email: {
            equalTo: "#email"
        },
        url: {
            url: true
        },
        date: {
            date: true
        },
        date_iso: {
            dateISO: true
        },
        numbers: {
            number: true
        },
        digits: {
            digits: true
        },
        creditcard: {
            creditcard: true
        }
    },
    messages: {
        custom: {
            required: "This is a custom error message"
        },
        agree: "Please accept our policy"
    }
});

$.validator.addMethod("checkRequired", function(value, element) {
    let $elem = $(element),
        form = $elem.closest('form');
    return form.find('input[data-rule-checkrequired="true"][data-empty-error="' + $elem.data('empty-error') + '"]').is(':checked');
}, function (value, element) {
    return $(element).data('empty-error');
});

$.validator.addMethod("exactLength", function(value, element, param) {
    return this.optional(element) || value.trim().length === param;
}, function (value, element) {
    return $(element).data('length-message');
});

$.validator.addMethod("phoneOrEmailRequired", function(value, element) {
    let form = $(element).closest('form');
    return form.find('input[id$="phone"]').val() || form.find('input[id$="email"]').val();
}, function () {
    return jQuery.validator.messages.required;
});

$.validator.addMethod("cardValidation", function(value, element) {
    let $elem = $(element),
        $form = $elem.closest('form'),
        $checkedMethod = $form.find('input[name="' + $elem.attr('name') + '"]:checked'),
        $cardInputs = $form.find('input[data-type="card"]').closest('.panel').find('.panel-body').find('input, select');

    $checkedMethod.data('type') === 'card' ? $cardInputs.removeClass('skip-field-validation') : $cardInputs.addClass('skip-field-validation');
}, '');

validationForm.each(function()
{
    let form = $(this);
    validatedForm = form.validate();

    // If validation happens on ajax form
    if (form.hasClass('ajaxUpdate')) {
        form.submit(function(e) {
            let $form = $(this);

            if ($form.valid()) {
                e.preventDefault();
                ajaxUpdate($form.find('button[type="submit"]'));
            }
        });
    }
});