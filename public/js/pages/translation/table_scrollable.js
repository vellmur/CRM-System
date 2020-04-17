// Setting datatable defaults
$.extend($.fn.dataTable.defaults, {
    autoWidth: false,
    dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
    language: {
        paginate: {'next': '&rarr;', 'previous': '&larr;' }
    },
    bLengthChange: false,
    bSort: false
});

/**
 *
 * @param id
 * @param options
 * @param searchableColumns
 */
function dataTableInit(id, options, searchableColumns = null)
{
    if (searchableColumns !== null) {
        $.fn.dataTableExt.ofnSearch['html-input'] = function(value) {
            return $(value).text();
        };

        options['columnDefs'] = [{
            "type": "html-input",
            "targets": searchableColumns
        }];
    }

    options['autoWidth'] = true;

    $(id).DataTable(options);
}