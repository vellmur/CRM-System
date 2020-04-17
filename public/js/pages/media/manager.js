/**
 * Init image uploader
 *
 * @param uploaderId
 * @param uploadPath
 * @param listId
 * @param labels
 */
function initManager(uploaderId, uploadPath, listId, labels)
{
    var $body = $('body'),
        $uploader = $("#" + uploaderId);

    $uploader.fileinput({
        uploadUrl: uploadPath,
        browseLabel: '',
        browseIcon: labels.addBtn,
        uploadIcon: '<i class="icon-file-upload2"></i>',
        uploadLabel: labels.uploadBtn,
        removeIcon: '<i class="icon-cross3"></i>',
        removeLabel: labels.removeBtn,
        cancelLabel: labels.cancelBtn,
        showCaption: false,
        uploadAsync: false,
        maxFileSize: '20000',
        allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
        browseClass: 'btn btn-success',
        dropZoneTitle: labels.dropTitle,
        layoutTemplates: {
            icon: '<i class="icon-file-check"></i>'
        },
        fileActionSettings: {
            dragClass: 'btn btn-link btn-xs btn-icon',
            dragIcon: '<i class="icon-three-bars"></i>',
            removeClass: 'btn btn-link btn-icon btn-xs',
            removeIcon: '<i class="icon-cross3"></i>',
            showUpload: false,
            showCaption: false,
            indicatorSuccess: '<i class="icon-checkmark3 file-icon-large text-success"></i>',
            indicatorError: '<i class="icon-cross2 text-danger"></i>',
            indicatorLoading: '<i class="icon-spinner2 spinner text-muted"></i>',
            showZoom: false,
            indicatorNew: ''
        }
    });

    $uploader.on('filebatchuploadsuccess', function (event, data) {
        let $list = $(data.response.list).find('#' + listId);
        $body.find('#' + listId).html($list.html());

        let $images = $body.find('.view-image');

        if ($images.length && typeof $.fancybox == 'function') {
            $images.fancybox({padding: 3});
        }
    });
}