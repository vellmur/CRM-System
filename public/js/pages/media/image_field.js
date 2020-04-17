let $body = $('body');

/**
 * Initialize image uploading with fileinput.js
 *
 * @param $fileInput
 * @param thumbOptions
 */
function initImageUploader($fileInput, thumbOptions = null)
{
    let fileOptions = {
        removeIcon: '<i class="icon-cross3"></i>',
        showUpload: false,
        showCaption: false,
        showBrowse: false,
        showRemove: false,
        fileActionSettings: {
            showZoom: false,
            indicatorNew: ''
        },
        initialPreviewAsData: true,
        initialPreviewShowDelete: false
    };

    if (thumbOptions !== null) {
        Object.assign(fileOptions, thumbOptions);
        $fileInput.fileinput('destroy').fileinput(fileOptions);
    } else {
        $fileInput.fileinput(fileOptions);
    }
}

/**
 * Add image options to file uploader
 *
 * @param $fileInput
 * @param name
 * @param src
 */
function initImageUploaderWithPreload($fileInput, name, src)
{
    let thumbOptions = {
        initialPreview: src,
        initialPreviewConfig: [{
            caption: name,
            url: src,
        }]
    };

    initImageUploader($fileInput, thumbOptions);
}

/**
 * Open image gallery in new window
 *
 * @param path
 */
function openImageGallery(path)
{
    window.open(path)
}

// Set selected image to the field (that was chosen at new tab in image manager)
function setImageField(name, src, updateFormPath, imageSelectId, imagePreloadId)
{
    let $imageField = updateImagesSelectField(updateFormPath, imageSelectId);

    $imageField.find('option').filter(function() {
        return $(this).text() === name;
    }).prop('selected', true);

    initImageUploaderWithPreload($('#' + imagePreloadId), name, src);
}

// Update hidden images list in a form in order if new image was uploaded so it can be saved in a entity
function updateImagesSelectField(path, imageSelectId)
{
    let $image = null;

    $.ajax({
        url: path,
        type: "POST",
        async: false,
        success: function (response) {
            let $updatedForm = $(response),
                $imagesList = $updatedForm.find('#' + imageSelectId);

            $image = $body.find('#' + imageSelectId);
            $image.html($imagesList.html());
        },
        error: function (response) {
            console.log(response)
        }
    });

    return $image;
}

$body.on('click', '.fileinput-remove', function () {
    $(this).closest('.form-group').find('select').val(0);
});

// Show image on startup in file uploader or initialize without image
function initImageField($imagePreloader)
{
    if ($imagePreloader.data('name') !== '' && $imagePreloader.data('image-url') !== '') {
        initImageUploaderWithPreload($imagePreloader, $imagePreloader.data('name'), $imagePreloader.data('image-url'));
    } else {
        initImageUploader($imagePreloader);
    }
}