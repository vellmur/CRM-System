function initAutocomplete($fields, country)
{
    var geocoder = new google.maps.Geocoder();

    var options = {
        types: ['address']
    };

    if (country !== null) options.componentRestrictions = {country: country};

    $fields.each(function () {
        var input = this;
        var autocomplete = new google.maps.places.Autocomplete(input, options);

        autocomplete.addListener('place_changed',function () {
            placeChange(autocomplete, input);
        });
    });
}

var placeChange = function (autocomplete, input)
{
    var place = autocomplete.getPlace();
    updateAddress({
        input: input,
        address_components: place.address_components
    });
};

var updateAddress = function (args)
{
    var $fieldset = $(args.input).closest('.collection_item');

    if (!$fieldset.length) $fieldset = $(args.input).closest('.address');

    var $street = $fieldset.find('[id$=street]');
    var $postalCode = $fieldset.find('[id$=postalCode]');
    var $region = $fieldset.find('[id$=region]');
    var $city = $fieldset.find('[id$=city]');

    $postalCode.val('');

    var streetNumber = '';
    var route = '';

    for (var i = 0; i < args.address_components.length; i++) {
        var component = args.address_components[i];
        var addressType = component.types[0];

        switch (addressType) {
            case 'street_number':
                streetNumber = component.long_name;
                break;
            case 'route':
                route = component.short_name;
                break;
            case 'postal_code':
                $postalCode.val(component.long_name);
                $postalCode.trigger('keyup');
                $postalCode.click();
                break;
            case 'administrative_area_level_1':
                $region.val(component.long_name);
                $region.trigger('click');
                break;
            case 'locality':
                $city.val(component.long_name);
                $city.trigger('click');
                break;
        }
    }

    if (route) {
        $street.val(streetNumber && route
            ? streetNumber + ' ' + route
            : route);
    }
};