// Show/hide pos switch on category change
$('#product_category').on('change', function()
{
    var $this = $(this),
        $form = $this.closest('form'),
        $isPos = $form.find('#product_isPos');

    if (parseInt($this.val()) === 1) {
        $isPos.closest('.form-group').show();
    } else {
        if ($isPos.is(':checked') && $isPos.closest('.form-group').css('display') !== 'none') {
            $isPos.click();
        }

        $isPos.closest('.form-group').hide();
    }
});

// When pos switch changes -> add/remove plant/weight/pay by fields
$('#product_isPos').on('change', function()
{
    var $this = $(this),
        $form = $this.closest('form');

    $.ajax({
        url: $form.attr('action'),
        type: 'post',
        data: $form.serialize(),
        success: function(html) {
            var $plantField = $form.find('#product_plant'),
                $weightField = $form.find('#product_weight'),
                $payByField = $form.find('#product_payByItem_0'),
                $template = $(html.template),
                $returnedPlant = $template.find('#product_plant'),
                $returnedWeight = $template.find('#product_weight'),
                $returnedPayBy = $template.find('#product_payByItem_0');

            // If plant field was added to the form -> append it, else -> remove it
            if (!$plantField.length && $returnedPlant.length) {
                $returnedPlant.selectpicker({'title' : ''});
                $this.closest('.form-group').after($returnedPlant.closest('.form-group'));
                $this.closest('.panel-body').find('#product_description').closest('.form-group').after($returnedWeight.closest('.form-group'));

                $payByField.closest('.form-group').remove();
            } else if ($plantField.length && !$returnedPlant.length) {
                $plantField.closest('.form-group').remove();
                $weightField.closest('.form-group').remove();

                $returnedPayBy.closest('.form-group').find('input').uniform({radioClass: 'choice'});
                $this.closest('.panel-body').find('#product_deliveryPrice').closest('.form-group').after($returnedPayBy.closest('.form-group'));
            }
        },
        error: function (response) { }
    });
});


// Image uploader
var fileUploaderOptions = {
    browseLabel: 'Browse',
    browseIcon: '<i class="icon-file-plus"></i>',
    uploadIcon: '<i class="icon-file-upload2"></i>',
    removeIcon: '<i class="icon-cross3"></i>',
    showUpload: false,
    showCaption: false,
    browseClass: 'btn btn-action',
    fileActionSettings: {
        showZoom: false,
        indicatorNew: ''
    }
};

var $preloadImage = $('#preloadImageUrl');

if ($preloadImage) {
    var imageProperties = {
        initialPreview: $preloadImage.val(),
        initialPreviewConfig: {
            caption: $preloadImage.data('name'),
            url: $preloadImage.val()
        },
        initialPreviewAsData: true
    };

    Object.assign(fileUploaderOptions, imageProperties);
}

$('body').on('click','.fileinput-remove-button, .btn-file', function () {
    $("#product_image_unlink").prop('checked', $(this).hasClass('fileinput-remove-button'));
});

$("#product_image_binaryContent").fileinput(fileUploaderOptions);