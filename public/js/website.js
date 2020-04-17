$(function() {
    $('.carousel').carousel({
        interval: 5000 //changes the speed
    })

    $("#contactForm").submit(function(event) {
        // cancels the form submission
        event.preventDefault();

        if($(this).valid()) {
            var form = $(this).serialize();

            $.ajax({
                type: "POST",
                url: "/message",
                data: form,
                success : function(text){
                    console.log(text)
                    $('#captcha_error').empty();
                    $('#success_modal').modal('toggle');
                },
                error: function(response) {
                    console.log(response);
                    var responseErrors = jQuery.parseJSON(response.responseText);
                    var error = jQuery.parseJSON(responseErrors);
                    if (error.captcha !== undefined) {
                        $('#captcha_error').empty();
                        $('#captcha_error').append(error.captcha);
                    }
                }
            });
        }
    });
});