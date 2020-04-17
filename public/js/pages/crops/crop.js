$(function() {
    /* Event on change date field, calculating dates differences with got date and today */
    $("input[name$='[startDate]'], input[name$='[plantDate]'], input[name$='[endDate]']").on('keyup change', function (e)
    {
        var $this = $(this);

        // If change was in ajax table form and field must have count days difference
        if ($this.data('days-count')) {
            var formBlock = $this.parent().parent();

            if (formBlock.prop('tagName') !== 'TR') {
                formBlock = formBlock.closest('.panel');
            }

            var date = stringToDate($this.val(), $this.attr('date-format')),
                today = new Date(),
                fieldName = $this.attr('name'),
                plantDate = formBlock.find("input[name$='[plantDate]']").val();

            // If click was on startDate field
            if (fieldName.indexOf("startDate") >= 0) {
                var daysDifferenceId = "startDateDifference";

                if (!plantDate || plantDate.length <= 0) {
                    var daysDifference = getDaysDifference(date, today);
                }
            } else if (fieldName.indexOf("plantDate") >= 0) {
                // If event on plantDate change and it is empty (user clear it), recalculate startDate and clear plantDate
                if (plantDate.length <= 0) {
                    daysDifferenceId = "startDateDifference";
                    var startDate = stringToDate(formBlock.find("input[id^='startDate']").val(), $this.attr('date-format'));
                    daysDifference = getDaysDifference(startDate, today);

                    formBlock.find('#plantDateDifference').empty();
                } else {
                    daysDifferenceId = "plantDateDifference";
                    daysDifference = getDaysDifference(date, today);

                    formBlock.find('#startDateDifference').empty();
                }
            }

            if (daysDifference === 0 || daysDifference < 0) { daysDifference = ''; }
            formBlock.find('#' + daysDifferenceId).html(daysDifference);
        }
    });

    // Get difference in days between 2 Date objects
    function getDaysDifference(date1, date2)
    {
        var _MS_PER_DAY = 1000 * 60 * 60 * 24,
            utc1 = Date.UTC(date1.getFullYear(), date1.getMonth(), date1.getDate()),
            utc2 = Date.UTC(date2.getFullYear(), date2.getMonth(), date2.getDate());

        return Math.floor((utc2 - utc1) / _MS_PER_DAY);
    }
});
