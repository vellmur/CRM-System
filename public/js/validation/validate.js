var errors = [];

function setMessage(field, error, message) {
    errors.push({
        field: field,
        error: error,
        message: message
    });
}

function createMessages() {
    var messages = {};
    var errorObj = {};

    errors.forEach(function (message, key) {
        if (key > 0 && message.field !== errors[key-1].field) {
            errorObj = {};
            errorObj[message.error] = message.message;
            messages[message.field] = errorObj;
        } else {
            errorObj[message.error] = message.message;
            messages[message.field] = errorObj;
        }
    });

    return messages;
}

function validateForm(form, rules)
{
    var messages = createMessages();

    $(form).validate({
        rules: rules,
        messages: messages,
        errorPlacement: function(error, element) {
            $(element).tooltip({
                trigger: 'manual',
                placement: 'top',
                title: error.text()
            }).attr('data-original-title', error.text())
                .tooltip('fixTitle')
                .tooltip('show');
        },
        highlight: function ( element, errorClass, validClass ) {
            $(element).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );

        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).tooltip('destroy');
            $( element ).parents( ".form-group" ).removeClass( "has-error" );
        }
    });
}