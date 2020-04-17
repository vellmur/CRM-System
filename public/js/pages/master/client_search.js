/*-----------------Ajax search in master/clients page and Member summary page ---------------------*/

$('#search_text, #search_filter').on('keyup change', function ()
{
    var $this = $(this),
        $searchBy = $('#search_filter'),
        $searchField = $('#search_text');

    if ($this.attr('id') === 'search_text') {
        searchField = $this;
        searchBy = $searchBy;
    } else {
        var searchField = $searchField,
            searchBy = $this;
    }

    // Save customers filter by to a cookie
    setCookie('customersFilterBy', searchBy.val(), 7);

    var url = searchField.attr('data-action'),
        block = $('#' + searchField.attr('data-block')),
        counter = $('#' + searchField.attr('data-counter'));

    url += '?searchBy=' + (searchBy.length ? searchBy.val() : 'all');

    if (searchField.length && searchField.val().length > 0) url += '&search=' + searchField.val();

    $.ajax({
        url: url,
        type: "POST",
        success: function (response) {
            block.html(response.template);
            counter.html(response.counter);
        },
        error: function (response) {
            counter.html(0);
        }
    });
});

$('.summary-search').on('click', function ()
{
    var $this = $(this),
        block = $('#content_list');

    $.ajax({
        url: $this.data('path'),
        type: 'POST',
        success: function (response) {
            block.html(response.template);
        },
        error: function (response) {
            console.log(response);
        }
    });
});