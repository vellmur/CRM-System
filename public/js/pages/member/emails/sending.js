
// Update sending progress bar
function updateProgress(perc) {
    perc = perc + '%';
    $('#progressBar').css('width', perc);
    $('#percents').text(perc);
}

// Request to controller that checks sending process
function pollLatestResponse(checkPath)
{
    var result = $.ajax({type: "GET",url: checkPath, async:false}).responseText;

    return parseInt(result);
}

// Init checking of email sending
function initSendingCheck(checkPath, redirectPath, failPath) {
    var checkNums = 0;

    var pollTimer = setInterval(function () {
        var perc = pollLatestResponse(checkPath);
        updateProgress(perc);

        checkNums++;

        if (checkNums > 30 && perc === 0) {
            clearInterval(pollTimer);

            setTimeout(function (){
                window.location.href = failPath;
            }, 3000);
        } else if (perc === 100) {
            clearInterval(pollTimer);

            setTimeout(function (){
                window.location.href = redirectPath;
            }, 3000);
        }
    }, 2000);
}