/*-------------------------------------Working with validation-----------------------------------*/

var fieldNames = {
    'en' : {
        'number' : 'number',
        'string' : 'text',
        'date' : 'date'
    },
    'ru' : {
        'number' : 'число',
        'string' : 'текст',
        'date' : 'дата'
    }
};

var errorMessages = {
    'en' : {
        'empty_field' : 'This field is a required.',
        'not_valid_type' : 'This field must have a valid type: ',
        'choose_value' : 'Please choose some value.',
        'member_status_error' : {
            'active' : 'Customer status must be ACTIVE, because renewal date is in future.',
            'lapsed' : 'Customer status must be LAPSED, because renewal date is in past.'
        }
    },
    'ru' : {
        'empty_field' : 'Это поле не может быть пустым.',
        'not_valid_type' : 'Это поле должно быть соответствующего типа: ',
        'choose_value' : 'Пожалуйста, выберите один из вариантов.',
        'member_status_error' : {
            'active' : 'Статус пользователя должен быть АКТИВЕН, потому что дата окончания членства в будущем.',
            'lapsed' : 'Статус пользователя должен быть ПРОСРОЧЕН, потому что дата окончания членства в прошлом.'
        }
    }
};

var memberStatuses = {
    'en' : {
        1 : 'ACTIVE',
        2 : 'PENDING',
        3 : 'LAPSED'
    },
    'ru' : {
        1 : 'АКТИВНЫЙ',
        2 : 'ОЖИДАЕМЫЙ',
        3 : 'ПРОСРОЧЕН'
    }
};

var locale = $('#locale').val();

/**
 *
 * @param inputs
 * @returns {boolean|*}
 */
function validate(inputs)
{
    var valid = true;

    // Start validate inputs and selects fields
    $.each(inputs, function (index, val)
    {
        var input = $(val);

        if (input.attr('type') !== 'hidden')
        {
            if (input.is('select')) {
                val = input.children(':selected').text();
            } else {
                val = input.val();
            }

            var nullable = input.attr('data-empty');
            var validType = input.attr('data-type');

            if (input.is(':radio')) {
                var radio = $('input[name="' + input.attr('name') + '"]');

                if (radio.is(':checked')) {
                    val = radio.val();
                } else {
                    val = '';
                }

                input = input.parent();
            }

            var msg = false;

            if (nullable !== undefined && val.length === 0) {
                if (!locale) locale = 'en';
                msg = errorMessages[locale]['empty_field'];

            } else if (checkTypeError(input, validType, val)) {

                msg = errorMessages[locale]['not_valid_type'] + fieldNames[locale][validType] + '.';
            }

            // If input field not valid create errors in page (if input is empty or not required type)
            if (msg !== false) {
                showError(input, msg);
                valid = false;
            } else {
                destroyError(input);
            }
        }
    });

    return valid;
}

/**
 *
 * @param input
 * @param validType
 * @param value
 * @returns {boolean}
 */
function checkTypeError(input, validType, value)
{
    var error = false;

    if (validType !== undefined && value.length !== 0)
    {
        // If input is date, try to convert it to normal full date
        if (validType === 'date' && value.length > 8) {
            value = stringToDate(value, input.attr('date-format'));

            if (Date.parse(value)) var realType = 'date';
        }

        if ((!/[^a-zA-Z]+$/.test(value) || (!/[^а-яА-Я]+$/.test(value)))) {
            realType = 'string';
        } else if ($.isNumeric(value)) {
            realType = 'number';
        }
        
        if (validType !== realType) {
            error = true;
        }
    }

    return error;
}


/**
 *
 * @param element
 * @param message
 */
function showError(element, message)
{
    element.tooltip({
        trigger: 'hover',
        placement: 'top',
        title: message
    }).attr('data-original-title', message)
        .tooltip('fixTitle')
        .tooltip('show');

    element.closest('.form-group').addClass('has-error');
}

/**
 *
 * @param element
 */
function destroyError(element)
{
    element.tooltip('destroy');
    element.closest('.form-group').removeClass('has-error');
}

$(function()
{
    /*--------------- Add member validation --------------------- */
    $('#member_status, #member_renewDue').on('change', function ()
    {
        var memberStatus = $('#member_status');
        var renewDate = $('#member_renewDue');

        var status = memberStatus.children(':selected').text();

        // If status chose and it is not pending, member renew date must be filled
        if (status.length > 0 && status !== memberStatuses[locale][2]) {
            renewDate.attr('data-empty', 'false');
        } else {
            renewDate.removeAttr('data-empty');
        }

        var now = new Date().setHours(0,0,0,0);
        var renewDateLength = renewDate.val().length;
        var renewalDate = stringToDate(renewDate.val(), renewDate.attr('date-format'));

        var error = false;

        // If renew date filled and status is not correct, show error message
        if (renewDateLength > 8 && renewalDate > now && status !== memberStatuses[locale][1]) {
            error = errorMessages[locale]['member_status_error']['active'];
        } else if (renewDateLength > 8 && renewalDate < now && status !== memberStatuses[locale][3]) {
            error = errorMessages[locale]['member_status_error']['lapsed'];
        }

        if (error) {
            showError(memberStatus, error);
        } else {
            destroyError(memberStatus, error);
        }
    });
});
/*-----------------------------------------------------------------------------------------------*/