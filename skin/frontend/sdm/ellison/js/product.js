/**
 * Separation Degrees Media
 *
 * product.js
 *
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @category  Separation Degrees Media
 * @package   sdm
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */
(function($) {
    $.noConflict();
    $(document).ready(function() {
        //
        // Sticky Project Block (non simple product)
        $('.pinned').pin({
            containerSelector: '.pinned-wrap',
            minWidth: 760
        });
        $('.product-shop').fadeIn(300);

        //
        // Product Page
        $('.toggle-tabs li, .toggle-tabs li span').click(function(e) {
            if($('.tabs .toggle-tabs').find('li').hasClass('current')) {
                var hideTabContent = $('.detailed-content .tab-container .tab-content, .zoomContainer').hide();
                if($(this).find('span').attr('class') === 'Overview') {
                    hideTabContent;
                    $('.tab-container #product-description, .zoomContainer').show();
                } else if($(this).find('span').attr('class') === 'Compatibility') {
                    hideTabContent;
                    $('.tab-container #compatibility').show();
                } else if($(this).find('span').attr('class') === 'Sizzix 101') {
                    hideTabContent;
                    $('.tab-container #sizzix101').show();
                } else if($(this).find('span').attr('class') === 'Projects') {
                    hideTabContent;
                    $('.tab-container #related-projects').show();
                } else if($(this).find('span').attr('class') === 'Designers') {
                    hideTabContent;
                    $('.tab-container #designer-product').show();
                } else if($(this).find('span').attr('class') === 'Instructions') {
                    hideTabContent;
                    $('.tab-container #instructions').show();
                } else if($(this).find('span').attr('class') === 'Accessories') {
                    hideTabContent;
                    $('.tab-container #related-accessories').show();
                }
            }
        });

        //
        // Product Image thumbnail
        $('.product-image-thumbs').owlCarousel({
            items: 6,
            itemsTablet: [770, 5],
            itemsMobile: [500, 4],
            pagination: false,
            navigation: true,
            navigationText : ["prev","next"]
        });

        //
        // Product Banner
        $('.accessories-slider, .designers-slider, .instruction-slider').owlCarousel({
            navigation : false, // Show next and prev buttons
            slideSpeed : 300,
            paginationSpeed : 400,
            singleItem:true,
            pagination: true
        });

        //
        // Machine Compatibility
        $('#machine-compatibility, #block-related').owlCarousel({
            items: 5,
            itemsDesktop : [1000,5],
            pagination: false,
            navigation: true,
            navigationText : ["prev","next"]
        });

        //
        // Instruction Images
        $('a.instruction-img').click(function(){
            var url = $(this).attr('href');
            newEasyModal("<img src='"+url+"' />");
            return false;
        });
    });
})(jQuery);
