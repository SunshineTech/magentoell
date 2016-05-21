/**
 * Separation Degrees Media
 *
 * banner.js
 *
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @category  Separation Degrees Media
 * @package   sdm
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */
(function($) {
    $.noConflict();
    $(document).ready(function() {
        // Product Banner Owl Carousel
        $("#banner-slider").owlCarousel({
            navigation      : false, // Show next and prev buttons
            slideSpeed      : 300,
            paginationSpeed : 400,
            singleItem      : true,
            pagination      : false
        });
    });
})(jQuery);