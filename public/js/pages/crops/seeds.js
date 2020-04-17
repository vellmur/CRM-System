// Initialize steps container
var $formSteps = $(".form-steps");

/**
 * Function for adding of top forms in Seeds page. Works for 3 forms (Companies, Companies orders, Seeds orders)
 * Helps to send top form to the controller and show errors/append added form to the list of forms in a table
 *
 * When Company/Company Order form added, helps to update Companies/Orders lists in select fields on the following forms
 * When Company added, updates companies list in Company order forms
 * When Company order added, updates orders list on Seeds orders forms
 */
$formSteps.on('click', '.add-seed-form', function () {
    var $this = $(this),
        $button = $this.parent(),
        $form = $button.closest('form'),
        data = serializeCollectionItem($form, $button.closest('.panel-body')),
        buttonHTML = $button.html();

    // Replace the button to show spinner and remove errors from previous submission
    $button.html('<button class="btn btn-action btn-icon"><i class="icon-spinner3 spinner"></i></button>');
    $form.find('.form-group').removeClass('has-error');
    $form.find('.tooltip').tooltip('destroy');

    $.ajax({
        url: $form.attr('action'),
        type: "POST",
        data: data,
        success: function (response) {
            // Append added form to the forms list inside table (with update/delete buttons)
            var addedForm = $(response.form);
            addedForm.find('select').selectpicker({'title' : ''});
            $($this.data('list')).append(addedForm);

            var fieldToUpdate = $this.data('field-update');

            // If field data have field to update value, find and replace field with updated data from the response
            if (fieldToUpdate) {
                // Get new field from response (with added entity value) and replace current field
                var $fieldsToUpdate = $('select[name$="' + fieldToUpdate + '"]'),
                    $newOptions = $(response.updatedForm).find('select[name$="' + fieldToUpdate + '"] option'),
                    options = '';

                // Get new options as string
                $newOptions.each(function() {
                    options += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
                });

                // Run through each field in a list and update options to include new (added) value
                for (var i = 0; i < $fieldsToUpdate.length; i++) {
                    var $updateField = $($fieldsToUpdate[i]),
                        value = $updateField.val();

                    // Empty previous selects and append new
                    $updateField.empty().html(options);
                    $($updateField.find('option[value="' + value + '"]')).attr('selected', 'selected');
                    $updateField.selectpicker('refresh');
                }
            }

            $form.trigger("reset");
            $form.find('select').selectpicker('refresh');

            buttonHTML = $(buttonHTML).filter(".btn").removeClass('btn-danger');
        },
        error: function (response) {
            console.log(response);
            showTableCollectionErrors($button.closest('.panel-body'), JSON.parse(response.responseJSON));
            buttonHTML = $(buttonHTML).filter(".btn").addClass('btn-danger');
        },
        complete: function () {
            $button.html(buttonHTML);
        }
    });
});

/**
 * Validate and Save single form collection inside table row
 */
$('body').on('click', '.form-collection-save', function ()
{
    var $this = $(this),
        $button = $(this).parent(),
        $form = $this.closest('form').first(),
        $tr = $this.closest('tr'),
        action = $tr.data('action'),
        buttonHTML = $button.html();

    // Replace the button to show spinner and remove errors from previous submission
    $button.html('<button class="btn btn-success btn-icon"><i class="icon-spinner3 spinner"></i></button>');
    $form.find('.form-group').removeClass('has-error');
    $form.find('.tooltip').tooltip('destroy');

    var $inputs = serializeCollectionItem($form, $button.closest('tr'));

    $.ajax({
        url: action,
        type: 'post',
        data: $inputs,
        success: function (response) {
            buttonHTML = $(buttonHTML).filter(".btn").removeClass('btn-danger');
        },
        error: function (response) {
            showTableCollectionErrors($tr, JSON.parse(response.responseJSON));
            buttonHTML = $(buttonHTML).filter(".btn").addClass('btn-danger');
        },
        complete: function () {
            $button.html(buttonHTML);
        }
    });
});

/**
 * Serialize fields from one (clicked) form collection item and validate/save just this form.
 *
 * @param $form
 * @param $collectionParent
 * @returns {string}
 */
function serializeCollectionItem($form, $collectionParent)
{
    // Get all collection item fields + collection form token
    var $token = $form.find('input[name$="[_token]"]').first();
    var $fields = $collectionParent.find('input, select, textarea');

    // Replace id of collection item prototype to 0 inside name tag (to pass collection validation)
    for (var i = 0; i < $fields.length; i++) {
        $($fields[i]).attr('name', $($fields[i]).attr('name').replace(/[0-9]+/, '0').replace('__name__', '0'));
    }

    // Serialize form fields + token
    return $($fields).serialize() + '&' + $token.serialize();
}

/**
 * Show form errors for s symfony form collection.
 *
 * @param $tr
 * @param response
 */
function showTableCollectionErrors($tr, response)
{
    // If exists common form error, show it to the first field in a row
    if (response.errors) {
        var $firstField = $tr.find('input, select, textarea').filter(':visible:first');
        showTooltipError($firstField, response.errors[0]);

        return;
    }

    var collectionType = Object.keys(response.children)[0];
    var collection = response.children[collectionType].children;

    for (var i = 0; i < collection.length; i++) {
        var fields = collection[i].children;

        for (var field in fields) {
            if (fields.hasOwnProperty(field) && fields[field].hasOwnProperty('errors')) {
                var fieldError = fields[field]['errors'][0];
                var fieldName = '[' + collectionType + ']' + '[' + i + ']' + '[' + field + ']';

                var $field = $tr.find('input[name$="' + fieldName + '"], select[name$="' + fieldName + '"]');
                showTooltipError($field, fieldError);
            }
        }
    }
}


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

// If first step have added forms, start from second step
var startIndex = $('#seed-companies').children().length > 0 ? 1 : 0;

// Basic wizard setup
$formSteps.steps({
    headerTag: "h6",
    titleTemplate: '#title#',
    bodyTag: "fieldset",
    transitionEffect: "fade",
    enablePagination: false,
    startIndex: startIndex
});

// Manually add buttons actions
$('.btn-step-next').on('click', function () {
    $formSteps.steps('next');
});

$('.btn-step-previous').on('click', function () {
    $formSteps.steps('previous');
});

/**
 * After the click on add items to company order, move step forward and pre-select order id
 */
$formSteps.on('click', '.add-order-seeds', function () {
    var $this = $(this),
        $seeds = $('#seed-orders').children();

    // Pre-select value of hidden text field to clicked order id
    $('input[id$="' + $this.data('target-id') + '"]').first().val($this.data('order-id').toString());

    for (var i = 0; i < $seeds.length; i++) {
        var $seedRow = $($seeds[i]);
        $seedRow.data('order-id') === $this.data('order-id') ? $seedRow.show() : $seedRow.hide();
    }

    $formSteps.steps('next');
});

/**
 * Event of deleting of company name.
 * Get deleted company name from data-name attr of delete button and remove company from a drop-downs.
 */
$('.modal').on('click', "#delete_modal_yes", function (e) {
    // Get delete button path for finding of clicked delete button in a table
    var deletePath = $(this).parent().find('#data-area').attr('data-path');
    var $clickedButton = $('button[data-path="' + deletePath + '"]');

    // Get field name to update after form removing (update drop-down list) from field-update and value-remove of button
    var fieldNameToUpdate = $clickedButton.data('field-update');

    // If field name to update was found, replace drop-down list of given field name
    if (fieldNameToUpdate) {
        var $fieldsToUpdate = $('select[name$="' + fieldNameToUpdate + '"]');
        var removeValue = $clickedButton.data('value-remove');

        // Find and delete value from drop-downs after deleting of this value from a form
        var options = $fieldsToUpdate.find('option').filter(function(){return $(this).html()===removeValue.toString();});
        options.remove();

        // Refresh selectPicker elements
        $fieldsToUpdate.attr('disabled', false);
        $fieldsToUpdate.selectpicker('refresh');
    }
});