/**
 * Separation Degrees Media
 *
 * catalog.js
 *
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @category  Separation Degrees Media
 * @package   sdm
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

jQuery(function(){
    jQuery(".filter-list dt").click(function(){
        jQuery(this).parent().toggleClass("closed");
    });
});
