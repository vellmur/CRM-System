// Add recipients to a list form checked checkboxes
addRecipients();

// Enable ckeditor on textarea
CKEDITOR.replace('#editor', {
    language: 'en',
    'skin' : 'moono-lisa',
    'height' : 300,
    'extraPlugins' : 'macro,autogrow',
    'customConfig': ''
});

// Load previews to iframes from ckeditor
$('#tab2, #tab4').on('click', function () {
    var content =  CKEDITOR.instances['#editor'].getData();

    $('.frame').each(function() {
        var $frame = $(this).find('iframe');

        setTimeout( function() {
            var doc = $frame[0].contentWindow.document;
            var $body = $('body', doc);
            $body.html('');
            $body.html(content);
        }, 1 );
    });

    $('.review-frame').trigger('change');
});

// Check all recipients
$('#all_recipients').on('click', function () {
    var checkboxes =  $('input[name^="email[recipients]"]');

    if ($(this).is(':checked')) {
        checkboxes.each(function(){
            $(this).prop('checked', true);
            $(this).parent().prop('class', 'checked');
        });
    } else {
        checkboxes.each(function(){
            $(this).prop('checked', false);
            $(this).parent().prop('class', '');
        });
    }
});

// Event on add recipients button
$('#addRecipients').on('click', function () {
    addRecipients();
});

// Function write to a list checked recipients from recipients checkboxes in pop-up window
function addRecipients()
{
    var checkboxes = $('input[name^="email[recipients]"]:checked'),
        membersList = $('#members-list'),
        list = '',
        selectedNum = checkboxes.length,
        noRecipientsLabel = $('#no_recipients_label').text();

    membersList.empty();

    if (selectedNum === 0) {
        list = '<span class="red-text">' + noRecipientsLabel + '</span>';
    } else {
        checkboxes.each(function (key, value) {
            if (key !== 0 && key !== selectedNum) {
                list += '<span>' + ', ' + $(this).data('name') + '</span>';
            } else {
                list += '<span>' + $(this).data('name') + '</span>';
            }

        });
    }

    membersList.append(list);

    $('#recipients_modal').find('#delete_modal_close').click();
}

// Event on clear all recipients button
$('#clear_recipients').on('click', function () {
    var checkboxes = $('input[name^="email[recipients]"]:checked'),
        noRecipientsLabel = $('#no_recipients_label').text();

    checkboxes.each(function (key, value) {
        $(this).prop('checked', false);
        $(this).parent().removeAttr('class');
    });

    var allButton = $('#all_recipients');
    allButton.prop('checked', false);
    allButton.parent().removeAttr('class');

    var membersList = $('#members-list');
    membersList.empty();
    membersList.append('<span class="red-text">' + noRecipientsLabel + '</span>');
});

$('.review-frame').on('change', function () {
    this.style.height = $('#design').height() - 100 +'px';
});

var savedSubject = null,
    savedText = null,
    savedRecipients = [];

// autoSave email to database
function autoSave()
{
    var $form = $('form[name="email"]'),
        draftPath = $form.data('draft'),
        data = $form.serializeArray(),
        subject = null,
        text = null,
        recipients = [];

    // Get saved data
    for (var item in data) {
        var field = data[item].name;

        // Get email text, subject and recipients
        if (field === 'email[subject]') subject = data[item].value;
        if (field.indexOf('recipients') !== -1) recipients.push(data[item].value);

        if (field === 'email[text]') {
            data[item].value = CKEDITOR.instances['#editor'].getData();
            text = data[item].value;
        }
    }

    // If subject and text are not empty and one of data was changed (subject, text, recipients)
    if (subject && text &&
        (subject !== savedSubject || text !== savedText || JSON.stringify(recipients) !== JSON.stringify(savedRecipients))) {
        $.ajax({
            url: draftPath,
            type: "POST",
            data: data,
            success: function (response) {
                savedSubject = subject;
                savedText = text;
                savedRecipients = recipients;

                $form.data('draft', response.data.draftPath);
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

    return draftPath;
}

// Run email autoSave each N seconds(now 10 sec)
window.setInterval(autoSave, 5000);