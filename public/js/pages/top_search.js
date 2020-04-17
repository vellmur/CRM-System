var result = [];

$('#customer-search').typeahead({
    highlight: true,
    minLength: 1
}, {
    name: 'myMatches',
    limit: 20,
    source: function(query, syncResults, asyncResults) {
        var path = Routing.generate('customer_search');

        $.ajax({
            url: path,
            type: "POST",
            data: {'search': query},
            success: function (response) {
                var customers = jQuery.parseJSON(response).customers;

                result = customers.values;
                asyncResults(customers.names);
            },
            error: function (response) {
                console.log(response)
            }
        });
    }
}).on('typeahead:selected', function (e, search) {
    // Go to found customer edit page
    window.location.href = Routing.generate('member_edit', {id : result[search]});
}).on('keyup', function (e) {
    // Number 13 is the "Enter" key on the keyboard
    if (e.keyCode === 13) {
        var search = $(this).val();
        var customerId = result[search.trim().toUpperCase()];

        // If value found, go to customer/edit page, else show alert
        if (customerId !== undefined) {
            window.location.href = Routing.generate('member_edit', {id : customerId});
        } else {
            alert("Sorry, there are no matching result for: " + search + '.');
        }
    }
});