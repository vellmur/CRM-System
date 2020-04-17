/**
 * Created by Valentine on 30.06.2016.
 */

/*------------------------------WORKING WITH AJAX TABLES ------------------------------------*/
var errors = {
    'undefinedErrors' : {
        'en' : 'Undefined error, please check your inputs.',
        'ru' : 'Неизвестная ошибка, пожалуйста, проверьте введенные данные .'
    },
    'passwordNotMatches' : {
        'en': 'Passwords must be equals.',
        'ru': 'Пароли должны совпадать.'
    }
};

var valid;

$(function()
{
    var locale = $('#locale').val();

    // Events after clicking on save button
    $(document).on('click', '.btn-save', function (e)
    {
        e.preventDefault();

        var $this = $(this);
        ajaxUpdate($this);
    });

    // Events after clicking on delete button
    $("table, .panel").on('click', '.btn-delete', function () {
        $(document).find('#data-area').attr('data-path', $(this).attr('data-path'));
    });

    // If user wants to delete row
    $(document).on('click', "#delete_modal_yes", function ()
    {
        var deletePath = $(document).find('#data-area').attr('data-path'),
            formBlock = $("button[data-path='" + deletePath + "']").parent().parent();

        if (formBlock.prop('tagName') !== 'TR') {
            formBlock = formBlock.closest('.panel');
        }

        $.ajax({
            url: deletePath,
            type: "DELETE",
            success: function () {
                var child = formBlock.next('.child');

                if (child.attr('class')) {
                    child.remove();
                }

                formBlock.remove();
                $('#small_modal').modal('toggle');
            },
            error: function (response) {
                console.log(response)
            }
        });
    });

    /**
     * Delete entity on click "Yes" button form delete modal window
     */
    $('#delete-modal').on('click', '.btn-continue', function (e) {
        e.preventDefault();

        // Get delete button with delete path
        var deleteButton = $('.form-horizontal').find('button[href="#' + $(this).closest('.modal').attr('id') + '"]');

        // Delete entity end redirect to rounte from response
        $.ajax({
            url: deleteButton.data('delete-path'),
            type: "DELETE",
            success: function (response) {
                location.href = response.redirect;
            },
            error: function (response) {
                console.log(response)
            }
        });
    });

    $('body').on('hidden.bs.modal', '.modal', function () {
        $(".data-area").empty()
    });

    // If user don't want to delete - hide modal and remove id
    $("#delete_modal_no, #delete_modal_close").on('click', function () {
        deletePath = null;
        $(this).closest('.modal').modal('hide');
    });
});

function ajaxUpdate($button)
{
    // Try to find closest parent tag with closest form
    var parent = $button.closest('tr');
    if (!parent.prop("tagName")) parent = $button.closest('.panel');

    parent.find('.form-group').removeClass('has-error');

    var form = parent.find('form'),
        formName = form.attr('name'),
        inputs = parent.find('input[name^="' + formName + '"], select[name^="' + formName + '"], textarea[name^="' + formName + '"]');

    // Validate if form haven`t jquery-validation
    if (!form.hasClass('jquery-validation')) {
        if (!validate(inputs)) throw 'Something wrong. Please check again your inputs';
    }

    var button = $button.parent(),
        buttonLabel = button.text().trim();

    button.html('<button class="btn btn-success btn-icon"><i class="icon-spinner3 spinner"></i></button>');

    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: inputs.serialize(),
        success: function (response) {
            // Get fields on a table row that have empty value
            var empty = form.parent().find("input, select").filter(function() {
                return this.value === "";
            });

            // If rows must be removed after saving and all fields are filled -> just remove table row
            if (form.attr('data-remove') === 'true' && !empty.length) {
                form.parent().remove();
            } else {
                // if page is tray starts (function used in garden plants page(at bottom) twig template)
                if (form.attr('data-stage') === 'tray') calculateTraysLeft(form, parent);

                // Enable the button button
                if (buttonLabel.length > 0) {
                    var newButton = '<button type="submit" class="btn btn-success btn-save">' + buttonLabel + '</button>';
                } else {
                    newButton = '<button type="submit" class="btn btn-success btn-icon btn-save"><i class="icon-floppy-disk"></i></button>';
                }

                // For jquery-validation
                if (form.hasClass('jquery-validation')) {
                    newButton = $(newButton).removeClass('btn-save');
                }

                if ($(button)) button.html(newButton);
            }
        },
        error: function (response) {
            var formErrors = jQuery.parseJSON(response.responseJSON).error.children;

            // Enable the button button
            if (buttonLabel.length > 0) {
                var newButton = '<button type="submit" class="btn btn-danger btn-save">' + buttonLabel + '</button>';
            } else {
                newButton = '<button type="submit" class="btn btn-danger btn-icon btn-save"><i class="icon-floppy-disk"></i></button>';
            }

            // For jquery-validation
            if (form.hasClass('jquery-validation')) {
                newButton = $(newButton).removeClass('btn-save');
            }

            // Enable the button button
            button.html(newButton);

            var errNum = 0;

            //For each input win form
            $.each(formErrors, function(name, input) {
                // If form has errors
                if (input.errors !== undefined) {
                    var inputId = formName + "_" + name;
                    var field = parent.find('#' + inputId);

                    // For jquery validation forms
                    if (form.hasClass('jquery-validation')) {
                        var error = '<label id="' + inputId + '-error" class="validation-error-label" for="' + inputId + '">' + input.errors[0] + '</label>';
                        field.parent().append(error);
                    } else {
                        field.tooltip({trigger: 'hover', placement: 'top', title: input.errors[0], container: 'body'})
                            .attr('data-original-title', input.errors[0])
                            .tooltip('fixTitle')
                            .tooltip('show');
                    }

                    field.closest('.form-group').addClass('has-error');
                } else {
                    errNum += 1;
                }
            });

            // That means that updating have undefined error
            if (errNum === Object.keys(formErrors).length) {
                alert(errors['undefinedErrors'][locale]);
            }
        }
    });
}

function calculateTraysLeft(form, parent)
{
    var formName = form.attr('name');

    var traysField = '#' + formName + '_traysNum';
    var traysNum = parent.find(traysField);
    var allTraysNum = parent.find(traysField + ' option:last').val();

    // if client plant not all trays of some plant in one time
    if (traysNum.val() !== allTraysNum)
    {
        // Else change max options num to all trays - planted trays num
        var traysLeft = allTraysNum - parseInt(traysNum.val());

        for (var i = allTraysNum; i > traysLeft; i--) {
            parent.find(traysField + " option[value='" + i + "']").remove();
        }

        // clear plant date and garden set max value of select to left number of trays for this plant
        var gardenField = parent.find('#' + formName + '_garden');
        // remove garden that is used from all dropdowns in a page
        $('#' + formName + "_garden option[value='" + gardenField.val() + "']").remove();
        gardenField.val('');
        parent.find('input[name="' + formName + '[plantDate]"]').val('');
        traysNum.val(traysLeft);

        $(traysField).selectpicker('refresh');
        $(gardenField).selectpicker('refresh');
    }
}

/*---------- Change referral paid status (if master paid affiliate for referral, change to true) --------*/

var queries = 0;

$("input[name='referral-paid']").on('change', function ()
{
    /* When i use switches for checkboxes, event on change goes 2 times, so i skip 1 time, and do ajax on second */
    queries++;

    if (queries == 2) {
        queries = 0;
        if ($(this).is(':checked')) {
            var isPaid = 1;
        } else {
            isPaid = 0;
        }

        $.ajax({
            url: $(this).attr('data-path'),
            type: "POST",
            data: { isPaid: isPaid },
            success: function (response) {
                console.log(response)
            },
            error: function (response) {
                console.log(response)
            }
        });
    }

});

// When category gets selected in next share page
$(document).on('change', "select[name='next_share[category]']", function()
{
    var $this = $(this);

    var $parent = $(this).closest('.parent');

    $parent.find('#next_share_price').val('');
    $parent.find('#next_share_weight').val('');

    $.ajax({
        url : $this.data('path'),
        type: 'post',
        data : {category : $this.val()},
        success: function(html) {
            $parent.find('#next_share_product').html(html);
        },
        error: function (response) {
            console.log(response)
        }
    });
});