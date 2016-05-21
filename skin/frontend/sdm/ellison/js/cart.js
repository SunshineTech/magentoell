/**
 * Separation Degrees Media
 *
 * cart.js
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
        // Crosssell
        $('#crosssell-products-list').owlCarousel({
            items: 5,
            itemsDesktop : [1000,5],
            pagination: false,
            navigation: true,
            navigationText : ["prev","next"]
        });
        //
        // Drop Down
        // if($('#region_id').css('display') == 'none'){
        //     $(this).parents()
        // }
    });
})(jQuery);
