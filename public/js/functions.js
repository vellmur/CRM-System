$(function() {
    $('.custom-read-only').attr('tabindex', '-1');

    // Enable TAB button controls for all form controls without skipping of styled elements
    /*$('form').on('keydown click',function(e) {
        var $this = $(this),
            $focused = $(':focus');

        // If clicked button is TAB - code 9
        if (e.keyCode === 9 || e.type === 'click') {
            var inputs = $this.find('input:not([type="hidden"]):not([class="form-control custom-read-only"]), textarea, .dropdown-toggle, button');

            // Remove tab indexes and already tabbed controls styles
            inputs.find('a').removeAttr('tabindex');
            inputs.removeClass('tabbed-control');
            $this.find('.choice-border').removeClass('choice-border');

            if (e.keyCode === 9) {
                var nextElement = inputs.eq(inputs.index($focused[0]) + 1);
                nextElement.addClass('tabbed-control');
                nextElement.focus();

                // For the choices elements click event needed to help with tab choosing
                if (nextElement.hasClass('styled')) {
                    nextElement.parent().addClass('choice-border');
                }

                // Set focus to the first element of the form, if previous control was submit button
                if (inputs.eq(inputs.index($focused[0])).is('[type="submit"]')) {
                    inputs.eq(0).focus();
                    inputs.eq(0).addClass("tabbed-control");
                }

                // If tab event after date field - hide calendar manually
                if (e.target.hasAttribute('date-format')) {
                    $(e.target).next().removeClass('picker--opened')
                }

                e.stopPropagation();
                e.preventDefault();

                return false;
            } else {
                $focused.addClass("tabbed-control");
            }
        }
    });*/
});

function enableSwitches($switchers) {
    if ($switchers && Array.isArray($switchers)) {
        $switchers.forEach(function(html) {
            new Switchery(html, { color: '#00BCD4', secondaryColor: '#FF5722'});
        });
    }
}

// Append style to a select control elements
function enableSelects($selects) {
    $selects.selectpicker({'title': ''});
}

// Updated selects that was changed in a live mode (without refreshing page)
function updateSelects($selects) {
    $selects.selectpicker('refresh');
}

// Enable calendar with needed formats for datepicker fields
function enableDatepickers(dateFormat, $pickers)
{
    if (!$pickers) {
        $pickers = $('.datepicker');
    }

    $pickers.attr('date-format', dateFormat);

    for (var i = 0; i < $pickers.length; i++) {
        var $datepicker = $($pickers[i]);

        $datepicker.pickadate({
            format: dateFormat,
            min: $datepicker.data('min') ? $datepicker.data('min') : false
        });
    }
}

// Activate phone masks
function enablePhoneFormats(phones)
{
    if (!phones) {
        phones = $('[id$="phone"]');
    }

    if (phones.length > 0) {
        if ($(phones[0]).data('mask')) {
            phones.mask($(phones[0]).data('mask'), {
                placeholder: ' ',
                autoclear: false
            }).bind('paste', function () {
                $(this).val('');
            });
        }
    }

}

function changePassword(id, username) {
    $('#user_name').text(username);
    $('#userId').val(id);
}

/* ---------------- Adds live validation -----------------------*/
$("form[class='form-horizontal'] :input").on('keyup change', function()
{
    var $this = $(this);

    if (!$this.closest('form').hasClass('jquery-validation')) {
        if ($this.attr('data-original-title') !== undefined && $this.val().length !== 0) {
            valid = validate($this);
        }
    }
});

$("form[class='form-horizontal']").on('submit', function (e)
{
    var $this = $(this);

    if (!$this.hasClass('jquery-validation')) {
        var inputs = $this.find('input, select');

        valid = validate(inputs);
        if (!valid) {
            return false;
        }
    }
});

$("body").on("click", ".styled", function () {
    $('#' + $(this).data('id')).click();
});

/* ---------------------------- Date functions -------------------------------------- */

// Show calendar always on the screen
$(document).on('click','.datepicker', function ()
{
    // Get datepicker calendar
    var picker = $(this).next(),
        domRect = this.getBoundingClientRect(),
        distanceToBottom = window.innerHeight - domRect.bottom,
        distanceToTop = domRect.top,
        pickerHeight = parseInt(picker.find('.picker__holder').height());

    // If distance to bottom less than picker height -> show calendar above the field, else show it below field
    if (distanceToBottom < pickerHeight && distanceToTop > pickerHeight) {
        var newPosition = pickerHeight + 210;
        picker.css({'top': '-' + newPosition + '%'});
    } else {
        picker.css({'top': '100%'});
    }
});

// Convert date in string format to date Object(UTC time)
function stringToDate(date, format)
{
    var year = null,
        month = null,
        day = null,
        dateParts = date.split('-');

    switch (format) {
        case 'yyyy-mm-dd' :
            year = dateParts[0];
            month = dateParts[1];
            day = dateParts[2];

            break;
        case 'dd-mm-yyyy' :
            year = dateParts[2];
            month = dateParts[1];
            day = dateParts[0];

            break;
        case 'mm-dd-yyyy' :
            year = dateParts[2];
            month = dateParts[0];
            day = dateParts[1];

            break;
        case 'dd-mmm-yyyy' :
            var months = {
                'Jan' : '01',
                'Feb' : '02',
                'Mar' : '03',
                'Apr' : '04',
                'May' : '05',
                'Jun' : '06',
                'Jul' : '07',
                'Aug' : '08',
                'Sep' : '09',
                'Oct' : '10',
                'Nov' : '11',
                'Dec' : '12'
            };

            year = dateParts[2];
            month = months[dateParts[1]];
            day = dateParts[0];

            break;
    }

    // yyyy-mm-dd format
    var newDate = year + '-' + month + '-' + day;

    return stringToUTC(newDate);
}

// Convert date string to Date object UTC time
function stringToUTC(dateString)
{
    var date = new Date(dateString),
        userTimezoneOffset = date.getTimezoneOffset() * 60000;

    return new Date(date.getTime() + userTimezoneOffset);
}

// Convert Date object to string format
function dateToString(date, format)
{
    var dd = date.getDate(),
        mm = date.getMonth() + 1,
        yyyy = date.getFullYear();

    if (dd < 10) {
        dd = '0' + dd;
    }

    if (mm < 10) {
        mm = '0' + mm;
    }

    var formattedDate = null;

    switch (format) {
        case 'mm-dd-yyyy' :
            formattedDate = mm + '-' + dd + '-' + yyyy;
            break;
        case 'yyyy-mm-dd' :
            formattedDate = yyyy + '-' + mm + '-' + dd;
            break;
        case 'dd-mm-yyyy' :
            formattedDate = dd + '-' + mm + '-' + yyyy;
            break;
        case 'dd-mmm-yyyy' :
            var monthes = {
                '01' : 'Jan',
                '02' : 'Feb',
                '03' : 'Mar',
                '04' : 'Apr',
                '05' : 'May',
                '06' : 'Jun',
                '07' : 'Jul',
                '08' : 'Aug',
                '09' : 'Sep',
                '10' : 'Oct',
                '11' : 'Nov',
                '12' : 'Dec'
            };
            formattedDate = dd + '-' + monthes[mm] + '-' + yyyy;
            break;
    }

    return formattedDate;
}

$.fn.equalizeHeights = function(){
    return this.height( Math.max.apply(this, $(this).map(function(i,e){ return $(e).height() }).get() ) )
};

var byTimeout = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

$.fn.extend({
    donetyping: function(callback,timeout){
        timeout = timeout || 1e3; // 1 second default timeout
        var timeoutReference,
            doneTyping = function(el){
                if (!timeoutReference) return;
                timeoutReference = null;
                callback.call(el);
            };
        return this.each(function(i,el){
            var $el = $(el);
            // Chrome Fix (Use keyup over keypress to detect backspace)
            // thank you @palerdot
            $el.is(':input') && $el.on('keyup paste keydown',function(e){
                // This catches the backspace button in chrome, but also prevents
                // the event from triggering too preemptively. Without this line,
                // using tab/shift+tab will make the focused element fire the callback.
                if (e.type=='keyup' && e.keyCode!=8) return;
                    // Check if timeout has been set. If it has, "reset" the clock and
                    // start over again.
                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function(){
                        // if we made it here, our timeout has elapsed. Fire the
                        // callback
                        doneTyping(el);
                    }, timeout);
                }).on('blur',function(){
                    // If we can, fire the event since we're leaving the field
                    doneTyping(el);
                });
        });
    },
    focusTextToEnd: function(){
        this.focus();
        var $thisVal = this.val();
        this.val('').val($thisVal);
        return this;
    }
});

function setCookie(cname, cvalue, exdays)
{
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname)
{
    var name = cname + "=",
        decodedCookie = decodeURIComponent(document.cookie),
        ca = decodedCookie.split(';');

    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];

        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }

        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }

    return "";
}

function isMobile() {
    return navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i);
}