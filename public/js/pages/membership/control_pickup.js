$('body').on('click', '.btn-pickup', function ()
{
    let $btn = $(this),
        $pickupButtonHtml = $($btn[0].outerHTML),
        $pickupsBlock = $btn.closest('.pickups-list'),
        skipLabel = $('#pickup-label-skip').val(),
        receiveLabel = $('#pickup-label-receive').val(),
        suspendedLabel = $('#pickup-label-suspended').val(),
        ajaxRunning = false;

    if (ajaxRunning === false && !$btn.hasClass('custom-read-only')) {
        $btn.addClass('custom-read-only disabled');

        let ajaxRunning = true;

        $.ajax({
            url : $btn.data('path'),
            type: 'post',
            data: { pickup: $btn.data('pickup') },
            success: function(response) {
                let pickups = response.pickups,
                    changedPickup = pickups[0];

                // Add new or remove last pickups list row. Add to list all added pickups (active/suspended)
                if ($btn.hasClass('btn-success')) {
                    $btn.attr('class', 'btn btn-pickup btn-danger')
                        .text(receiveLabel);

                    for (let i = 0; i < pickups.length; i++){
                        let pickup = pickups[i];

                        $pickupButtonHtml.attr('data-pickup', pickup.id);

                        // Append suspended or active button
                        if (pickup.isSuspended === false) {
                            $pickupButtonHtml.text(skipLabel);
                        } else {
                            $pickupButtonHtml.removeClass('btn-success').addClass('btn-dark custom-read-only');
                            $pickupButtonHtml.text(suspendedLabel);
                        }

                        $pickupsBlock.append('<tr id="pickup-' + pickup.id + '">' +
                            '<td class="pickup-date">' + pickup.date + '</td>' +
                            '<td></td>' +
                            '<td>' + $pickupButtonHtml[0].outerHTML + '</td></tr>'
                        );
                    }

                    // Hide customize feature for skipped share, if current week was skipped
                    if ($('#customize') && $btn.closest('tr').index() === 0) {
                        controlCustomize(changedPickup.shareId, false);
                    }
                } else {
                    $btn.attr('class', 'btn btn-pickup btn-success');
                    $btn.text(skipLabel);

                    // Remove needed number of pickups to last pickup date (renewal date)
                    for (let i = $pickupsBlock.children().length; i > 0; i--) {
                        let $pickup = $($pickupsBlock.children()[i - 1]);

                        if ($pickup.find('.pickup-date').text() !== changedPickup.renewalDate) {
                            $pickup.remove();
                        } else {
                            break;
                        }
                    }

                    // Show customize feature for received share, if current week was received
                    if ($('#customize') && $btn.closest('tr').index() === 0) {
                        controlCustomize(changedPickup.shareId, true);
                    }
                }

                // Update renewal date field
                let $renewalDate = $pickupsBlock.closest('table').parent().parent().parent().find('input[id$="renewalDate"]');
                $renewalDate.val(changedPickup.renewalDate);
            },
            error: function(response, status, error){
                console.log(jQuery.parseJSON(response.responseJSON).error)
            },
            complete: function () {
                ajaxRunning = false;
            }
        });
    }
});

/**
 * @param shareId
 * @param show
 */
function controlCustomize(shareId, show)
{
    var $customShare = $('#custom-' + shareId);

    if (show) {
        $customShare.removeClass('hidden');
        $customShare.parent().find('p').addClass('hidden');
    } else {
        $customShare.addClass('hidden');
        $customShare.parent().find('p').removeClass('hidden');
    }
}

/** Enable countdowns */
$('span[id^="pickup_timer"]').each(function ()
{
    var pickupMidnight = moment.tz($(this).data('date') + ' 20:00', "Europe/Paris").subtract(1,'d');

    // Commented test date to see how feature works (5 seconds from now)
    //var todayMidnight = moment().tz("Europe/Paris").add('3', 'seconds');

    $(this).countdown(pickupMidnight.toDate(), function(event) {
        $(this).text(event.strftime('%D:%H:%M:%S'));
    }).on('finish.countdown', function () {
        // Disable pickup button
        var $pickupButton = $(this).closest('tr').find('.btn-pickup');
        $pickupButton.removeAttr('data-pickup');
        $pickupButton.attr('class', 'btn btn-pickup btn-success bg-grey custom-read-only');

        // Change timer and add text
        $(this).parent().append('<span class="red-text">The time to skip this week has past.</span>')
        $(this).hide();

        // Get next row in a pickups list
        var nextPickup = $($(this).closest('.pickups-list').find('tr')[$(this).parent().index() + 1]);

        // Append timer to next skip week feature after disabling current week skip feature
        if (nextPickup.length) {
            var nextTimer = nextPickup.find('.timer-place').append(
                '<span class="skip_countdown" id="pickup_timer_' + $(this).data('date') + '" style="font-weight: 500;"></span>'
                ),
                timeToNext = moment.tz($(this).data('date') + ' 00:00', "Europe/Paris").add('6', 'days');

            // Append timer to the next pickup date
            nextTimer.countdown(timeToNext.toDate(), function(event) {
                $(this).text(event.strftime('%D:%H:%M:%S'));
            });
        }
    });
});