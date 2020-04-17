/**
 * Member can have only one address of Delivery type and one address of Billing type(two addresses).
 * Or just one address of Billing & Delivery type.
 *
 * Here we control this process by selected options values.
 * So this script works like front-end validation.
 */

var $addressCollection = $('.address-collection');
var $addAddress = $('.add-address');

// Check selects right after page loaded
setTimeout(checkTypes, 300);

// Check address types selects each time as Add address button is clicked
$addAddress.on('click', function () {
    setTimeout(checkTypes, 300);
});

/**
 *
 * If member choose Delivery type from first address, after click on add button, we remove selected type from
 * second address and remove not selected address type from the first address.
 *
 * Scenario:
 * Click Add -> Choose Delivery -> Click Add -> First address haven't Billing type, Second address haven't Delivery type
 * If Billing & Delivery type selected, don't do nothing, just hide add button.
 *
 */
function checkTypes()
{
    var collectionsNum = $addressCollection.find('.collection_item').length;

    if (collectionsNum > 0) {
        var firstAddress = $($addressCollection.find('select[id$="_type"]')[0]);
        var firstValue = parseInt(firstAddress.val());

        if (collectionsNum > 1) {
            // If first value is not Billing & Delivery re-set types, else hide add button
            if (firstValue !== 2) {
                var notSelectedType = firstValue === 1 ? 3 : 1;

                // Here we removing not selected option from first address type
                firstAddress.find('option[value="' + notSelectedType + '"]').remove();

                var secondAddress = $($addressCollection.find('select[id$="_type"]')[1]);

                // Remove selected address type from first address choice
                secondAddress.find('option[value="' + (firstValue === 1 ? 1 : 3) + '"]').remove();

                // Set not selected address type in the first choice, as selecetd in second address type
                secondAddress.find('option[value="' + notSelectedType + '"]').prop("selected", true);

                updateSelects($('.select'));
            } else {
                // Remove second address if was added after Billing & Delivery type
                removeCollection($($addressCollection.find('select[id$="_type"]')[1]).closest('.collection_item'));
            }

            $addAddress.hide();
        } else if (firstValue === 2) {
            $addAddress.hide();
        }
    }
}

/**
 * Events on delete buttons from address collections
 * After removing one of addresses we need to return back removed address type, to select that not lefts
 */
$addressCollection.on('click', '.delete-collection', function (e)
{
    if ($addressCollection.find('.collection_item').length > 1) {
        // Save removed address type
        var removedType = $(this).parent().find('select[id$="_type"]');

        var types = $('select[id$="_type"]');
        var index = types.index(removedType);

        var leftAddress = null;

        // Find select element that left after removing current address
        types.each(function (i, type) {
            if (index !== i) {
                leftAddress = $(type);
            }
        });

        // Adding removed address type to type of address that left
        if (leftAddress) {
            var addOption = removedType.find("option[value='3']").length > 0 ? removedType.find("option[value='3']")[0] : removedType.find("option[value='1']")[0];
            addAddressType(leftAddress, addOption);
        }
    }

    $addAddress.show();
});

/**
 * Check if client set some address to Delivery & Billing, don`t allow to add more addresses
 * After choosing Delivery & Billing type, function removes all another address collections
 */
$addressCollection.on('change', 'select[id$="_type"]', function ()
{
    // If client selected 3 option hide add button and delete another collections
    if (parseInt($(this).val()) === 2) {
        var types = $('select[id$="_type"]');
        var index = types.index($(this));

        // Remove all addresses items, besides current where type is Billing & Delivery
        types.each(function (i) {
            if (index !== i) {
                removeCollection($(this).closest('.collection_item'));
            }
        });

        $addAddress.hide();
    } else {
        $addAddress.show();
    }
});

/**
 * Add address type to select and sort values by value
 *
 * @param select
 * @param option
 */
function addAddressType(select, option)
{
    // Save previously selected value
    var selected = select.val();

    // Add option to options array
    var selectList = select.find('option');
    selectList.push(option);

    // Sort by value
    selectList.sort(function(a, b){
        a = a.value;
        b = b.value;

        return a-b;
    });

    // Append, update select and check previously checked option
    select.html(selectList);
    select.val(selected);

    updateSelects($('.select'));
}

/**
 * Removing collection by imitation of clicking of delete button
 * Because all functions after deleting must runs to,
 * So we trigger click event of delete collection button
 *
 * @param collection
 */
function removeCollection(collection)
{
    var deleteButton = collection.find('.btn-danger');

    if (deleteButton.hasClass('delete-collection-modal')) {
        deleteButton.removeClass('delete-collection-modal').addClass('delete-collection');
    }

    deleteButton.click();
}