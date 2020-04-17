$('#btn-submit').on('click', function (e) {
    e.preventDefault();

    var $this = $(this),
        $form = $this.closest('form'),
        token = '{{ token }}';

    // Disable submit button and remove previous errors
    $this.attr('disabled', 'true');
    $form.find('.error-body').empty();
    $form.find('.has-error').removeClass('has-error');

    // Remove all success and error messages
    $form.find('.success-message').remove();
    $form.find('.validation-error-label').remove();

    $.ajax({
        url: $('#send_path').data('path'),
        type : "POST",
        data: $form.serialize(),
        success: function (response) {
            console.log(response);
            // Reset the form and add successful message
            $form.html('<p class="text-success">Your message has been submitted.</p>');
        },
        error: function (response) {
            console.log(response);
            response = jQuery.parseJSON(response.responseText);

            if (response.formErrors !== undefined) {
                var errors = response.formErrors;

                for (var field in errors) {
                    var $field = $form.find('*[name$="[' + field + ']"]');

                    // If error related to the field and field found, show error below field
                    if ($field.length) {
                        $field.closest('.form-group').append('<label class="validation-error-label">' + errors[field][0] + '</label>');
                        $field.addClass('has-error');
                    } else {
                        $form.find('.form-errors').html('<p class="validation-error-label">' + errors[field] + '</p>');
                    }
                }
            } else {
                $form.find('.form-errors').html('<p class="validation-error-label">' + response.error + '</p>');
            }
        },
        complete: function () {
            $this.removeAttr('disabled');
        }
    });
});