/**
 * Separation Degrees Media
 *
 * Ellison's custom product taxonomy implementation.
 *
 * @category  SDM
 * @package   SDM_Taxonomy
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

jQuery.noConflict();
jQuery(function($){$(document).ready(function(){

	var pageType = $('#page_type')

	var hideField = function(id){
		$('#' + id).removeClass('required-entry').parents('tr').first().hide();
		$('[for="' + id + '"] .required').remove();
	}

	var showField = function(id, required, msg){
		$('#' + id).parents('tr').first().show();
		if (required) {
			$('[for="' + id + '"]')
				.find('.required')
				.remove()
				.end()
			.append(' <span class="required">*</span>');
			$('#' + id).addClass('required-entry');
		}
		if (msg && msg.length) {
			$('#' + id).parents('tr').first().find('.note span').text(msg);
		}
	}

	var redrawPage = function(){
		switch(pageType.val()){
			case 'designer':
				hideField('page_content_excerpt');
				showField('page_taxonomy_id', true);
				showField('page_hero_image', true, "Used on listing and article page. Min width: 1200px");
				showField('page_publish_time');
				showField('page_publish_author');
			break;
			case 'news':
				hideField('page_taxonomy_id');
				showField('page_content_excerpt', true);
				showField('page_hero_image', false, "Used on listing page ONLY. Min width: 800px");
				showField('page_publish_time', true);
				showField('page_publish_author', true);
			break;
			case 'press':
				hideField('page_content_excerpt');
				hideField('page_taxonomy_id');
				showField('page_hero_image', false, "Used on listing page ONLY. Min width: 800px");
				showField('page_publish_time', true);
				showField('page_publish_author', true);
			break;
			default:
				hideField('page_content_excerpt');
				hideField('page_hero_image');
				hideField('page_taxonomy_id');
				hideField('page_publish_time');
				hideField('page_publish_author');
			break;
		}
	}

	pageType.on('change', redrawPage);
	redrawPage();

});}(jQuery));