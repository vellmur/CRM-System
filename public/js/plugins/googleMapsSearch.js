function initAutocomplete($fields, $holder, country = null)
{
    new google.maps.Geocoder();

    let options = {
        types: ['address']
    };

    if (country !== null)  {
        options.strictBounds = true;
        options.componentRestrictions = { country: country };
    }

    $fields.each(function () {
        let input = this,
            autocomplete = new google.maps.places.Autocomplete(input, options);

        $(input).on('click keydown',function () {
            let countryFieldValue = $holder.find('[id$=country]').val(),
                countryCode = countryFieldValue ? countryFieldValue : null;

            autocomplete.setStrictBounds = countryCode !== null;
            autocomplete.setComponentRestrictions({'country': countryCode !== null ? countryCode : []});
        });

        autocomplete.addListener('place_changed',function () {
            placeChange(autocomplete, input, $holder);
        });
    });
}

let placeChange = function (autocomplete, input, $holder)
{
    let place = autocomplete.getPlace();

    updateAddress($holder,{
        input: input,
        address_components: place.address_components
    });
};

let updateAddress = function ($holder, args)
{
    let $street = $holder.find('[id$=street]'),
        $postalCode = $holder.find('[id$=postalCode]'),
        $region = $holder.find('[id$=region]'),
        $city = $holder.find('[id$=city]'),
        streetNumber = '',
        route = '';

    $postalCode.val('');

    for (let i = 0; i < args.address_components.length; i++) {
        let component = args.address_components[i],
            addressType = component.types[0];

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