function copyToClipboard(element, msg) {
    element.select();

    try {
        var successful = document.execCommand('copy');

        element.tooltip({
            trigger: 'manual',
            placement: 'top',
            title: msg
        });
        element.tooltip('show');

        setTimeout(function() { element.tooltip('destroy') }, 4000); //but invoke me after 3 secs

        var text = successful ? 'successful' : 'unsuccessful';
    } catch (err) {
    }
}