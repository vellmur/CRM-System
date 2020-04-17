var errorMessages = {
    'name' : {
        'empty' : 'Please enter your name'
    },
    'message' : {
        'empty' : 'Please enter your message',
        'minlength' : 'Your message must consist of at least 5 characters'
    },
    'email' : {
        'type' : 'Please enter a valid email address'
    }
};

function setMessage(field, error, value) {
    errorMessages[field][error] = value;
}

function getMessages(){
    return errorMessages;
}


function validateContactForm(errorMessages)
{
    $("#contactForm").validate({
        rules: {
            'website_contact[name]' : "required",
            'website_contact[message]' : {
                required: true,
                minlength: 5
            },
            'website_contact[email]' : {
                required: true,
                email: true
            }
        },
        messages: {
            'website_contact[name]': errorMessages['name']['empty'],
            'website_contact[message]' : {
                required: errorMessages['message']['empty'],
                minlength: errorMessages['name']['minlength']
            },
            'website_contact[email]' : errorMessages['email']['type']
        },
        errorElement: "em",
        errorPlacement: function ( error, element ) {
            // Add the `help-block` class to the error element
            error.addClass("help-block");

            if ( element.prop( "type" ) === "checkbox" ) {
                error.insertAfter( element.parent( "label" ) );
            } else {
                error.insertAfter( element );
            }
        },
        highlight: function ( element, errorClass, validClass ) {
                $( element ).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );
        },
        unhighlight: function (element, errorClass, validClass) {
            $( element ).parents( ".form-group" ).addClass( "has-success" ).removeClass( "has-error" );
        }
    });
}