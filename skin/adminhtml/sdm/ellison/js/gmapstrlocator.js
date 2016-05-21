/**
 * Separation Degrees Media
 *
 * This module handles all the store locator functionality for Ellison.
 *
 * The original code from this module is based off the FME_Gmapstrlocator module. We converted their
 * module to an SDM module rather than extending from it because the amount of modifications and
 * rewrites necessary for it to fit Ellison's spec were extensive, yet we still felt there was value
 * in using FME's module as a starting point.
 *
 * @category  SDM
 * @package   SDM_Gmapstrlocator
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

jQuery(function($){$(document).ready(function(){

	var checkStoreType = function() {
		var storeType = $("#store_type");
		var physicalFields = $("#address, #city, #state, #postal_code, #latitude, #longitude");
		var physicalLabels = $("label[for='address'], label[for='city'], label[for='state'], label[for='postal_code'], label[for='latitude'], label[for='longitude']")
			.find('.required');
		var websiteFields = $("#store_website");
		var websiteLabels = $("label[for='store_website']").find('.required');

		if (storeType.val() !== null) {
			if (storeType.val().indexOf('physical') === -1) {
				physicalFields.removeClass('required-entry');
				physicalLabels.hide();
			} else {
				physicalFields.addClass('required-entry');
				physicalLabels.css('display', 'inline');
			}

			if (storeType.val().indexOf('online') === -1) {
				websiteFields.removeClass('required-entry');
				websiteLabels.hide();
			} else {
				websiteFields.addClass('required-entry');
				websiteLabels.css('display', 'inline');
			}
		}
	}

	$("#store_type").on('change', checkStoreType);
	checkStoreType();

	$('.recalc-lat-lon').on('click', function(){
		var thisLink = $(this);
		var theKey = thisLink.attr('data-key');
		var theFields = $("#address, #address2, #city, #state, #country, #postal_code, #latitude, #longitude");

		var theAddress = [];

		$.each(theFields, function(k, v){
			var v = $(v);
			if (v.val() && v.val().length) {
				theAddress.push(v.val());
			}
		});
		theAddress = theAddress.join(',');

		var url = "https://maps.googleapis.com/maps/api/geocode/json?address="+theAddress+"&key=" + theKey;
		$.get(url)
			.done(function(result){
				if (result.status !== 'OK'){
					alert(result.error_message);
				}else {
					$('#latitude').val(result['results']['0']['geometry']['location']['lat']);
					$('#longitude').val(result['results']['0']['geometry']['location']['lng']);
				}
			});
	});

})}(jQuery))