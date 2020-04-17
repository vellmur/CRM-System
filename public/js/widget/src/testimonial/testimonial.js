var $body = $('body'),
    $quotes = $body.find('.quote');

function fade($ele) {
    $ele.fadeIn(1000).delay(3000).fadeOut(1000, function() {
        var $this = $(this),
            $next = $quotes.filter(':visible').length ? $quotes.filter(':visible').next() : $ele.next('.quote');

        $quotes.hide();

        fade($next.length > 0 ? $next : $this.parent().children().first());
    });
}

fade($body.find('.testimonial-widget > .quote').first());

$body.on('click','.testimonial-control', function () {
    var $current = $quotes.filter( ':visible'),
        $next = $(this).data('slide') === 'next' ? $current.next() : $current.prev();

    $next = $next.length > 0 ? $next : $quotes.parent().children().first();

    $quotes.hide();
    $next.show();
});

$body.on('click','.read-more-btn', function () {
    var $this = $(this),
        $textBlock = $this.parent('.text-read-block'),
        $dots = $textBlock.find('.text-dots'),
        $moreText = $textBlock.find('.read-more');

    if ($dots.css('display') === 'none') {
        $dots.show();
        $this.html("Show more");
        $moreText.hide();
    } else {
        $dots.hide();
        $this.html("Show less");
        $moreText.show();
    }
});