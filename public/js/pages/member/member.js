// When share day gets selected in profile page
$(document).on('change', "select[id$='_pickUpDay']", function()
{
    var $this = $(this);
    var $form = $this.parent().parent().closest('form');

    var $parent = $this.closest('.collection_item');

    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : $form.serialize(),
        success: function(html) {
            var $locations = $parent.find('div[id$="_location"]');

            var $label = $locations.parent().parent().find('label');
            if ($label.hasClass('hidden')) $label.removeClass('hidden');

            var $newLocations = $(html).find('div[id="' + $locations.attr('id') + '"]');
            $locations.replaceWith($newLocations);
        },
        error: function (response) {
            console.log(response)
        }
    });
});

/**
 * Calculate renewal date by change of value shares remaining field
 *
 * Selected value (active pickups num) and number of active (not skipped) pickups in a list helps to calculate renewal date.
 *
 */
$('select[id$="sharesRemaining"]').on('change', function ()
{
    var parent = $(this).closest('.collection_item');
    var $renewalDate = parent.find('input[name$="[renewalDate]"]');

    var pickupsNum = parseInt($(this).val());

    // Get pickups from pickups list with buttons and count number of active pickups
    var $pickups = $(parent.find('.pickups-list').children());
    var activePickupsNum = $pickups.find('.btn-success').length;

    // If selected value is not zero -> count renewal date
    if (pickupsNum !== 0) {
        // If number of existed active pickups in a list more or equal to selected number, set date from existed active pickups
        if (activePickupsNum >= pickupsNum) {
            var activeNum = 0;

            // Loop by all pickups buttons for find needed new renewal date in existed active (not skipped) pickups
            for (var i = 0; i < $pickups.length; i++) {
                var $pickup = $($pickups[i]);

                // If pickup is active (not skipped), increase index of active pickups
                if (!$pickup.find('button').hasClass('btn-danger')) activeNum++;

                // If loop now on needed pickup number index (by active, not skipped shares) -> set renewal date
                if (activeNum === parseInt($(this).val())) {
                    $renewalDate.val($pickup.find('.pickup-date').text());

                    break;
                }
            }
        } else {
            // Get renewal date by latest pickup on a share pickups buttons list
            var renewalDate = $($pickups[$pickups.length - 1]).find('.pickup-date').text();

            // Increase renewal date to needed days amount
            var date = stringToDate(renewalDate, $renewalDate.attr('date-format'));
            date.setDate(date.getDate() + (pickupsNum - activePickupsNum) * 7);

            // Update renewal date an set new text value
            $renewalDate.val(dateToString(date, $renewalDate.attr('date-format')));
        }
    } else {
        // Just set renewal date to start date if pickups number is 0
        $renewalDate.val(parent.find('input[name$="[startDate]"]').val());
    }
});

$('body').on('change', "select[name$='[share]']", function ()
{
    var $this = $(this);
    var $form = $this.parent().parent().closest('form');

    var $parent = $this.closest('.collection_item');

    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : $form.serialize(),
        success: function(response) {
            // Get new template of share collection
            var $collectionItem = $(response).find('#' + $this.attr('id')).closest('.collection_item');

            // Enable select fields styles
            enableSelects($collectionItem.find('.select'));

            // Enable datepicker's fields (styles and formats)
            enableDatepickers(dateFormat, $collectionItem.find('.datepicker'));

            // Remove front-end validation errors
            $collectionItem.find('.validation-error-label').remove();
            $collectionItem.find('.has-error').attr('class','form-group');

            // Update collection item template
            $parent.replaceWith($collectionItem);
        },
        error: function (response) {
            console.log(response)
        }
    });
});