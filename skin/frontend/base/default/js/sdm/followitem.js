/**
 * Separation Degrees One
 *
 * Allows customers to follow an item
 *
 * @category  SDM
 * @package   SDM_FollowItem
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

(function($) {
    $.noConflict();
    $(document).ready(function() {
        var activeFollow = {};
        $('.follow').on('click', '.follow-product', function(){
            var thisLink = $(this);
            var theUrl = thisLink.attr('data-follow-url');

            if (typeof activeFollow[theUrl] === 'undefined'){
                activeFollow[theUrl] = false;
            }

            if (!activeFollow[theUrl]) {
                thisLink.animate({
                    opacity: '0.25',
                }, 300).css(
                    'cursor', 'progress'
                );
                // We have an active Ajax request beginning
                activeFollow[theUrl] = true;
                $.ajax({
                    'url' : theUrl
                }).done(function(result){
                    // Show error messages
                    if (result.status === 'error') {
                        alert(result.message);
                    }
                    // Replace LINK html if available
                    if (typeof result.link !== 'undefined'){
                        thisLink.replaceWith($(result.link).find('a'));
                    }
                }).fail(function(){
                    alert("An error has occured saving your followed item. Please try again.");
                }).always(function(){
                    // We no longer have an active Ajax request
                    activeFollow[theUrl] = false;
                });
            }
            return false;
        });
    });
})(jQuery);