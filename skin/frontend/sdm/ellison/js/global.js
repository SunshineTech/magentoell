/**
 * Separation Degrees Media
 *
 * global.js
 *
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @category  Separation Degrees Media
 * @package   sdm
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */
var hoverCartPreventClose = false;
var hoverCartKey = 0;
var hoverCartOnAction;
var hoverCartOffAction;
var newEasyModal = function(){};
(function($) {
    $.noConflict();
    $(document).ready(function() {
        //
        // Header
        $(window).on("scroll touchmove", function () {
            $('.page-header-container').toggleClass(
                'tiny',
                $(document).scrollTop() > 30
            );
        });
        //
        // Navigation
        var navResetKey = 0;
        var resetNavigationChildPositioning = function(left) {
            if (!currentNavResetKey) {
                var currentNavResetKey = ++navResetKey;
            }
            setTimeout(function(){
                if (left <= 0 || navResetKey !== currentNavResetKey) {
                    return false;
                }
                var masterWidth = $('#header .page-header-container').width();
                var currentLeft = masterWidth - jQuery('#nav').width();
                var isShowingMenu = $(window).width() > 770;
                $('#nav li.level0').each(function(){
                    var menuItem = $(this).css('left', '0px');
                    if (isShowingMenu) {
                        var menuItemSub = menuItem.find('ul.level0');
                        var thisWidth = menuItem.width();
                        var thisSubWidth = menuItemSub.width();
                        var calcLeft = masterWidth - thisSubWidth;
                        calcLeft = currentLeft > calcLeft ? calcLeft : currentLeft;
                        if (calcLeft > 0) {
                            menuItem.find('ul.level0').css('left', calcLeft + "px");
                        }
                        currentLeft += thisWidth;
                    }
                });
                resetNavigationChildPositioning(--left);
            }, 200);
        }
        $(window).on("resize scroll", function(){resetNavigationChildPositioning(3)});
        resetNavigationChildPositioning(1);
        //
        // Mini Cart
        hoverCartOnAction = function() {
            $('#header-cart').addClass('skip-active');
        }
        hoverCartOffAction = function() {
            if (!hoverCartPreventClose) {
                $('#header-cart').removeClass('skip-active');
            }
        }
        $('.header-minicart, #header-cart')
            .on('mouseenter', function(e){
                hoverCartKey++;
                hoverCartOnAction();
            });
        $('.header-minicart, #header-cart')
            .on('mouseleave', function(e){
                hoverCartKey++;
                var thisHoverCartKey = hoverCartKey;
                setTimeout(function(){
                    if (hoverCartKey === thisHoverCartKey) {
                        hoverCartOffAction();
                    }
                }, 300);
            });
        //
        // Footer Mobile Menu
        $('.footer .links .block-title').append('<span class="toggle"></span>');
        $('.footer .links .block-title').click(function() {
            if ($(this).find('span').attr('class') === 'toggle opened') {
                $(this)
                    .find('span')
                    .removeClass('opened')
                    .parents('.links')
                    .find('ul')
                    .slideToggle();
            }
            else {
                $(this)
                    .find('span')
                    .addClass('opened')
                    .parents('.links')
                    .find('ul')
                    .slideToggle();
            }
        });
        //
        // Footer on Resize
        $(window).resize(function(){
            if($(window).width() < 771) {
                if($('span.toggle').length) {
                    return false;
                } else {
                    $('.footer .links .block-title')
                        .append('<span class="toggle"></span>');
                    $('.footer .links ul').hide();
                }
            } else {
                $('span.toggle').remove();
                $('.footer .links ul').show();
            }
        });
        //
        // Footer on size onload
        $(window).load(function() {
            if($(window).width() < 771) {
                if($('span.toggle').length) {
                    return false;
                } else {
                    $('.footer .links .block-title')
                        .append('<span class="toggle"></span>');
                }
            } else {
                $('span.toggle').remove();
            }
        });
        //
        // 4 Blocks Accordions
        $('#homepage-widget .widget-box .title-accordion').click(function() {
            var allPanels = $('.widget-box #hidden-box').hide();
            var titleAccordion = $('.title-accordion').show();
            allPanels.slideUp();
            titleAccordion.slideDown();
            $(this).parents('.widget-box')
                .find('.title-accordion')
                .show();
            $(this).parents('.widget-box')
                .find('#hidden-box')
                .slideToggle();
            if($(this).parents('.widget-box')
                .find('#hidden-box')
                .css('display') == "block") {
                    $(this).parents('.widget-box')
                        .find('.title-accordion')
                        .hide();
            }
        });
        //
        // Wrap Selector Dropdown
        DropDown();
        $('.filter-list dt').prepend('<span class="toggle"></span>');
        //
        // Selected dropdown
        OnChangeSelected();
        //
        // Password Strength
        var strongPassReg = /^(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/;
        var weakPassReg = /^[a-z]+$/;
        $('#socialogin\\.pass').keydown(function(obj) {
            if($('#socialogin\\.pass').val().length < 4 ) {
                $('#password-strength-text').html('Bad');
                $('#strength-indicator').css({
                    'background-color' : '#FF0000',
                    'width' : '15%'
                });
            } else if($('#socialogin\\.pass').val().length < 7 && $('#socialogin\\.pass').val().match(weakPassReg))
            {
                $('#password-strength-text').html('Weak');
                $('#strength-indicator').css({
                    'background-color' : '#C17A2D',
                    'width' : '30%'
                });
            } else if($('#socialogin\\.pass').val().length > 7 && $('#socialogin\\.pass').val().match(strongPassReg))
            {
                $('#password-strength-text').html('Strong');
                $('#strength-indicator').css({
                    'background-color' : '#47C965',
                    'width' : '100%'
                });
            } else {
                $('#password-strength-text').html('Average');
                $('#strength-indicator').css({
                    'background-color' : '#C69D03',
                    'width' : '50%'
                });
            }
        });
        //
        // Hide double load icons
        $('#magestore-button-sociallogin-forgot, #magestore-button-sociallogin, #magestore-button-sociallogin-create').click(function() {
            $(this).parent().parent()
                .find('.ajax-login-image')
                .attr('style', 'display: block !important');
            setTimeout(function(){
                $('.ajax-login-image')
                    .attr('style', 'display: none !important');
            }, 3000);
        });
        /*Minimum & Maximum font Size*/
        var minFontSize = 12;
        var maxFontSize = 20;

        /*Finding all the links inside a Div*/
        $('.tags-list').find('a').each(function(e) {
            /*Applying font size*/
            $(this).css("fontSize", randomNumberGenerator(minFontSize, maxFontSize));
        });

        /* eClips */
        $('.download-links .close').click(function(){
            $('.download-links').hide();
            return false;
        });

        //
        // Lyris
        $('.newsletter-account-edit #thumb-image').owlCarousel({
            navigation : false, // Show next and prev buttons
            slideSpeed : 300,
            paginationSpeed : 400,
            singleItem: true,
            pagination: true
        });

        //
        // Easy Modal
        $('body').on('click', '.easy-modal-wrap, .easy-modal-close', function(event){
            $('.easy-modal, .easy-modal-wrap').remove();
            return false;
        });
        newEasyModal = function(content) {
            jQuery('body')
                .append("<div class='easy-modal-wrap'></div><div class='easy-modal'><a href='#' class='easy-modal-close'>&times;</a>"+content+"</div>");
        }
    });
})(jQuery);

/*Random Number Generator function*/
function randomNumberGenerator(min,max) {
    return Math.floor(Math.random()*(max-min+1)+min);
}
function DropDown() {
    jQuery('select').wrap('<div class="custom-dropdown"></div>');
}
function OnChangeSelected() {
    jQuery('#billing\\:country_id').change(function() {
        if(jQuery('#billing\\:country_id').val() == 'US' || 'CA') {
            jQuery('#billing\\:region_id').parents('.custom-dropdown').show();
        } else {
            jQuery('#billing\\:region_id').parents('.custom-dropdown').hide();
        }
    });
    jQuery('#shipping\\:country_id').change(function() {
        if(jQuery('#shipping\\:country_id').val() == 'US' || 'CA') {
            jQuery('#shipping\\:region_id').parents('.custom-dropdown').show();
        } else {
            jQuery('#shipping\\:region_id').parents('.custom-dropdown').hide();
        }
    });
    jQuery('#country').change(function() {
        if(jQuery('#country').val() == 'US' || 'CA') {
            jQuery('#region_id').parents('.custom-dropdown').show();
        } else {
            jQuery('#region_id').parents('.custom-dropdown').hide();
        }
    });
}

// Set user agent to brwoser HTMl
var doc = document.documentElement;
doc.setAttribute('data-useragent', navigator.userAgent);
jQuery(function(){
    if ("ActiveXObject" in window) {
        jQuery('html').addClass('is-ie');
    }
});

