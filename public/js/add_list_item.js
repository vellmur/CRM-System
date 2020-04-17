$(function () {
    $('.btn-add-async').on('click', function () {
        let $form = $(this).closest('form');

        // Add new item to db and to the list
        if ($form.valid()) {
            let $button = $form.find('button[type="submit"]'),
                inputs = $form.find('input, select, textarea'),
                buttonLabel = $button.text();

            clearFormErrors($form, inputs);
            spinButton($button);

            $.ajax({
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: inputs.serialize(),
                success: function (response) {
                    addItemToList($form, response);
                    resetFormFields($form);
                },
                error: function (response) {
                    showFormErrors($form, response.responseJSON);
                },
                complete: function () {
                    restoreOriginalButton($button, buttonLabel);
                }
            });
        }
    });

    function clearFormErrors($form, inputs) {
        $form.find('.has-error').removeClass('has-error');
        $form.find('.alert-danger').remove();
        inputs.tooltip('destroy');
    }

    function spinButton($button) {
        $button.addClass('btn-icon').html('<i class="icon-spinner3 spinner"></i>');
    }

    function restoreOriginalButton($button, buttonLabel) {
        $button.removeClass('btn-icon').html(buttonLabel);
    }

    function addItemToList($form, response) {
        if ($form.data('items-holder') && 'item' in response) {
            let $body = $('body'),
                itemsHolderId = $form.data('items-holder'),
                $itemsHolder = $body.find(itemsHolderId);

            $itemsHolder.append(response.item);
        }
    }

    function resetFormFields($form) {
        $form.find('input, select, textarea').not(':button, :submit, :reset, :hidden').val('');
    }

    function showFormErrors($form, responseJSON) {
        if ('form' in responseJSON) {
            showFieldsErrors($form, responseJSON.form.children);
        } else if ('error' in responseJSON) {
            showFormAlert($form, responseJSON.error);
        } else {
            showFormAlert($form,'Undefined error. Please check your inputs.');
        }
    }

    function showFieldsErrors($form, errors) {
        let fields = Object.keys(errors);

        for (let i = 0; i < fields.length; i++) {
            let field = errors[fields[i]];

            if ('errors' in field) {
                let error = field.errors[0],
                    $field = $form.find('input[name="' + $form.attr('name') + '[' + fields[i] + ']"]');

                showTooltipError($field, error);
            }
        }
    }

    function showTooltipError($field, error) {
        $field.tooltip({
            trigger: 'manual',
            placement: 'top',
            title: error
        }).attr('data-original-title', error)
            .tooltip('fixTitle')
            .tooltip('show');

        $field.parent().addClass('has-error');
    }

    function showFormAlert($form, error) {
        $form.find('.form-group').last().after(
            "<div class='alert alert-danger'>" +
            "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + error + "</div>"
        );
    }
});