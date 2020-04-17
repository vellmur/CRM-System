$(function() {
    let $collectionHolder = null,
        $addCollectionLink = $('.add_collection_link');

    $addCollectionLink.on('click', function (e) {
        e.preventDefault();

        let $this = $(this);
        $collectionHolder = $this.parent('.collection');
        $collectionHolder.data('index', $collectionHolder.find('.collection_item').length);
        addCollectionForm($collectionHolder, $this);

        // Change add collection button label if second-button-label data exists
        if (typeof $this.data('second-button-label') !== 'undefined') {
            $this.text($this.data('second-button-label'));
        }
    });
});

/**
 *
 * @param $collectionHolder
 * @param $addCollectionLink
 */
function addCollectionForm($collectionHolder, $addCollectionLink)
{
    let prototype = $collectionHolder.data('prototype'),
        index = $collectionHolder.data('index'),
        newForm = prototype.replace(/__name__/g, index),
        $newFormDiv = $(newForm);

    $collectionHolder.data('index', index + 1);

    enableSelects($newFormDiv.find('.select'));
    enableSwitches(Array.prototype.slice.call($newFormDiv.find('.switchery')));

    $collectionHolder.append($newFormDiv);

    // If delete function exist
    if ($addCollectionLink !== null) {
        $addCollectionLink.before($newFormDiv);
        $($newFormDiv).find('.collection_num').text(index + 1);
    } else {
        $('.delete-collection').remove();
    }

    // Trick with date field, because datepicker field not works with collection, dont show calendar
    let dateField = $collectionHolder.find(".datepicker");
    if (dateField[0]) { enableDatepickers(dateFormat, dateField); }

    let phoneFields = $collectionHolder.find("input[id$='phone']");
    if (phoneFields[0]) { enablePhoneFormats(phoneFields); }

    let streetFields = $collectionHolder.find("input[name$='[street]']");
    if (streetFields[0]) { initAutocomplete(streetFields, country); }
}

var $body = $('body');

/**
 * Event on click delete collection button with class delete-collection-modal
 * We show modal and saves collection delete button id to a modal "Yes button"
 */
$body.on('click', '.delete-collection-modal', function (e)
{
    // stop next element events, push to a modal id of a button and open modal
    e.preventDefault();

    let deleteModal = $('#' + $(this).attr('id').split('-')[0] + '-modal');
    deleteModal.data('delete-button-id', $(this).attr('id'));
    deleteModal.find('.btn-continue').addClass('delete-collection-yes');
    deleteModal.modal('toggle');
});

/**
 * Event on click "Yes" button of modal with collection data
 *
 */
$body.on('click', '.delete-collection-yes', function (e)
{
    e.preventDefault();

    // get id of button that must be clicked for deleting a collection
    var modalWindow = $(this).closest('.modal');
    var deleteButton = $('#' + modalWindow.data('delete-button-id'));

    // Delete collection and close modal
    deleteButton.removeClass('delete-collection-modal').addClass('delete-collection');
    deleteButton.trigger('keyup');
    deleteButton.click();

    modalWindow.modal('toggle');
});

/**
 * Event on clicking remove collection button
 */
$body.on('click', '.delete-collection', function (e)
{
    e.preventDefault();
    var $this = $(this),
        $parent = $this.closest('.collection');

    $this.parent().remove();

    var $addButton = $parent.find('.add_collection_link');

    // Change add collection button label, if all items removed and first-button-label data exists
    if (typeof $addButton.data('first-button-label') !== 'undefined' && $parent.find('.collection_item').length === 0) {
        $addButton.text($addButton.data('first-button-label'));
    }
});

/**
 *
 * Event on click "No" of deleting modal. We just clear save delete collection button id
 *
 */
$('.btn-break').on('click', function () {
    var $modal = $(this).closest('.modal');

    // Clearing delete collection button id
    if ($modal.data('delete-button-id')) {
        $modal.removeData('delete-button-id');
    }
});
