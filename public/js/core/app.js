// Allow CSS transitions when page is loaded
$(window).on('load', function() {
    $('body').removeClass('no-transitions');
});

$(function() {
    // Highlight active menu element by 3 levels of nesting - module name, menu item, submenu item.
    let $body = $('body'),
        $navigationMain = $('.navigation-main');

    // Disable CSS transitions on page load
    $body.addClass('no-transitions');

    // Calculate min height
    function containerHeight() {
        var $container = $('.page-container');

        if ($container.length) {
            var availableHeight = $(window).height() - $container.offset().top - $('.navbar-fixed-bottom').outerHeight();
            $container.attr('style', 'min-height:' + availableHeight + 'px');
        }
    }

    containerHeight();

    // Add control button toggler to breadcrumbs if has elements
    $('.breadcrumb-line').has('.breadcrumb-elements').prepend('<a class="breadcrumb-elements-toggle"><i class="icon-menu-open"></i></a>');

    // Toggle visible state of breadcrumb elements
    $('.breadcrumb-elements-toggle').on('click', function() {
        $(this).parent().children('.breadcrumb-elements').toggleClass('visible-elements');
    });

    // Navbar navigation
    // Prevent dropdown from closing on click
    $(document).on('click', '.dropdown-content', function (e) {
        e.stopPropagation();
    });

    // Disabled links
    $('.navbar-nav .disabled a').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // Add active state to all dropdown parent levels
    $('.dropdown-menu:not(.dropdown-content), .dropdown-menu:not(.dropdown-content) .dropdown-submenu').has('li.active').addClass('active')
        .parents('.navbar-nav .dropdown:not(.language-switch), .navbar-nav .dropup:not(.language-switch)').addClass('active');

    // Main navigation tooltips positioning
    // -------------------------
    // Left sidebar
    $('.navigation-main > .navigation-header > i').tooltip({
        placement: 'right',
        container: 'body'
    });

    // Collapsible functionality
    // -------------------------
    // Main navigation
    $navigationMain.find('li').has('ul').children('a').on('click', function (e) {
        e.preventDefault();
        // Collapsible
        $(this).parent('li').not('.disabled').not($('.sidebar-xs').not('.sidebar-xs-indicator')
            .find('.navigation-main').children('li')).toggleClass('active').children('ul').slideToggle(250);

        // Accordion
        if ($('.navigation-main').hasClass('navigation-accordion')) {
            $(this).parent('li').not('.disabled').not($('.sidebar-xs').not('.sidebar-xs-indicator')
                .find('.navigation-main').children('li')).siblings(':has(.has-ul)').removeClass('active').children('ul').slideUp(250);
        }
    });

    // ========================================
    // Sidebars
    // ========================================
    // Mini sidebar
    // -------------------------
    // Toggle mini sidebar
    $('.sidebar-main-toggle').on('click', function (e) {
        e.preventDefault();
        // Toggle min sidebar class
        $body.toggleClass('sidebar-xs');
    });

    // Sidebar controls
    // -------------------------
    // Disable click in disabled navigation items
    $(document).on('click', '.navigation .disabled a', function (e) {
        e.preventDefault();
    });

    // Adjust page height on sidebar control button click
    $(document).on('click', '.sidebar-control', function (e) {
        containerHeight();
    });

    // Mobile sidebar controls
    // -------------------------
    // Toggle main sidebar
    $('.sidebar-mobile-main-toggle').on('click', function (e) {
        e.preventDefault();
        $body.toggleClass('sidebar-mobile-main').removeClass('sidebar-mobile-secondary sidebar-mobile-opposite');
    });

    // Mobile sidebar setup
    // -------------------------
    $(window).on('resize', function() {
        setTimeout(function() {
            containerHeight();

            if($(window).width() <= 768) {
                // Add mini sidebar indicator
                $body.addClass('sidebar-xs-indicator');

                // Place right sidebar before content
                $('.sidebar-opposite').prependTo('.page-content');

                // Add mouse events for dropdown submenus
                $('.dropdown-submenu').on('mouseenter', function() {
                    $(this).children('.dropdown-menu').addClass('show');
                }).on('mouseleave', function() {
                    $(this).children('.dropdown-menu').removeClass('show');
                });
            } else {
                // Remove mini sidebar indicator
                $body.removeClass('sidebar-xs-indicator');

                // Revert back right sidebar
                $('.sidebar-opposite').insertAfter('.content-wrapper');

                // Remove all mobile sidebar classes
                $body.removeClass('sidebar-mobile-main sidebar-mobile-secondary sidebar-mobile-opposite');

                // Remove visibility of heading elements on desktop
                $('.page-header-content, .panel-heading, .panel-footer').removeClass('has-visible-elements');
                $('.heading-elements').removeClass('visible-elements');

                // Disable appearance of dropdown submenus
                $('.dropdown-submenu').children('.dropdown-menu').removeClass('show');
            }
        }, 100);
    }).resize();

    // Plugins
    // Popover
    $('[data-popup="popover"]').popover();

    // Tooltip
    $('[data-popup="tooltip"]').tooltip();

    $('.styled').uniform({radioClass: 'choice'});
    $('.tip').tooltip();
    $('.tip-open').tooltip('show');

    $('.price-field').on('keyup', function () {
        var $this = $(this),
            value = parseFloat($this.val());

        if (value && value > 0) {
            var start = this.selectionStart,
                end = this.selectionEnd;

            this.value = value.toFixed(2);
            this.setSelectionRange(start, end);
        }
    });

    enableSwitches(Array.prototype.slice.call(document.querySelectorAll('.switchery')));
    enableSelects($('.select'));
    enablePhoneFormats();
});
