/**
 * Separation Degrees One
 *
 * Handles designer page and designer article rendering
 *
 * @category  SDM
 * @package   SDM_Designer
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

jQuery(function($){$(document).ready(function(){

	// Opening and closing designer tabs
	jQuery('.scrollto').click(function(){
		var topBuffer = jQuery('.logo').offset().top + jQuery('.logo').height();
		topBuffer = isNaN(topBuffer) ? 0 : topBuffer;
		var scrollTo = $('#' + $(this).attr('data-scrollto'));
		console.log(scrollTo.offset(), topBuffer);
		$('html, body').scrollTop(scrollTo.offset().top - topBuffer);
		return false;
	});

})}(jQuery));
