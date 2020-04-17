/**
 *
 * @param path
 */
function enableEmailSearch(path)
{
    // Check if member data exists by typed email
    $('input[name="renew[member][email]"]').donetyping(function () {
        var $email = $(this);

        if ($email.val().length > 5 && isValidEmail($email.val())) {
            $('body').css('cursor', 'wait');

            $.ajax({
                url: path,
                dataType : "jsonp",
                data: { email: $email.val() },
                success: function (response) {
                    var member = response.member;

                    if (member) {
                        // Set member data that was found by typed email
                        $('#renew_member_firstname').val(member.firstname);
                        $('#renew_member_lastname').val(member.lastname);
                        var $phone = $('#renew_member_phone');
                        $phone.val(member.phone).trigger('input');

                        // If member that was found have saved addresses, auto-fill it to
                        if (member.hasOwnProperty('address')) {
                            if (member.address.hasOwnProperty('Delivery') || member.address.hasOwnProperty('Billing and Delivery')) {
                                var deliveryAddress = member.address.Delivery ? member.address.Delivery : member.address['Billing and Delivery'];
                                var $delAddress = $('#delAddress');

                                // Fill fields with data from ajax result
                                $delAddress.find('input[id$="street"]').val(deliveryAddress.street);
                                $delAddress.find('input[id$="apartment"]').val(deliveryAddress.apartment);
                                $delAddress.find('input[id$="postalCode"]').val(deliveryAddress.postalCode);
                                $delAddress.find('input[id$="region"]').val(deliveryAddress.region);
                                $delAddress.find('input[id$="city"]').val(deliveryAddress.city);
                            }

                            if (member.address.Billing || member.address.hasOwnProperty('Billing and Delivery')) {
                                var billingAddress = member.address.Billing ? member.address.Billing : member.address['Billing and Delivery'];
                                var $billAddress = $('#billAddress');

                                // Fill fields with data from ajax result
                                $billAddress.find('input[id$="street"]').val(billingAddress.street);
                                $billAddress.find('input[id$="apartment"]').val(billingAddress.apartment);
                                $billAddress.find('input[id$="postalCode"]').val(billingAddress.postalCode);
                                $billAddress.find('input[id$="region"]').val(billingAddress.region);
                                $billAddress.find('input[id$="city"]').val(billingAddress.city);
                            }

                            // If data found a Billing&Delivery address, hide the checkbox, else auto-check the checkbox
                            if (member.address.hasOwnProperty('Billing and Delivery')) {
                                $('#renew_isNeedBilling').hide();
                            } else {
                                $('#renew_isNeedBilling').show();
                                $('#renew_isNeedBilling_0').click();
                            }
                        }

                        // Set location and share day
                        if (member.hasOwnProperty('location') && member.hasOwnProperty('shareDay')) {
                            var $location = $('input[name$="[location]"]').filter('[value='+ member.location + ']');
                            $location.trigger('click');
                            $location.parent().addClass('checked');

                            var $shareDay = $location.closest('.panel').find('input[id^="share_day"]').filter('[value='+ member.shareDay + ']');
                            $shareDay.trigger('click');
                            $shareDay.parent().addClass('checked');
                        }
                    }
                },
                error: function (response) {
                    console.log(response)
                },
                complete: function () {
                    $('body').css('cursor', 'default');
                }
            });

        }
    });
}

function isValidEmail(emailAddress)
{
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}