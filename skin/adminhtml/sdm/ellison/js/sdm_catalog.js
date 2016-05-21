/**
 * Separation Degrees One
 *
 * Magento catalog customizations
 *
 * @category  SDM
 * @package   SDM_Catalog
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

jQuery.noConflict();
jQuery(function($){$(document).ready(function(){
	var storeId = $('#store_switcher').val();

	// Simplify "create product" settings
	var newProductSetup = jQuery("#product_info_tabs_set_content");
	if (newProductSetup.length) {
		var attSet = jQuery('#attribute_set_id');
		var prodType = jQuery('#product_type');

		// Remove default option
		attSet.find('option').each(function(){
			var thisOption = jQuery(this);
			if (thisOption.text() === 'Default'){
				thisOption.remove();
			}
		});

		// Remove all but simple and grouped options
		prodType.find('option').each(function(){
			var thisOption = jQuery(this);
			if (thisOption.val() !== 'simple' && thisOption.val() !== 'grouped'){
				thisOption.remove();
			}
		});

		// Disable the product type from being changed manually
		prodType.parent()
			.attr('style','position: relative;opacity: 0.5')
			.append("<div style='width: 100%;height: 100%;position: absolute;top:0px;left:0px;'></div>");

		// Change the product type when the attribute set changes
		attSet.change(function(){
			var thisVal = attSet.find('option[value="'+$(this).val()+'"]').text().toLowerCase();
			if (thisVal == 'idea'){
				prodType.val('grouped');
			}else{
				prodType.val('simple');
			}
		}).val(9);
	}

	// Hide unused tabs
	$('#product_info_tabs_customer_options, #product_info_tabs_customers_tags, #product_info_tabs_tags, #product_info_tabs_crosssell, #product_info_tabs_upsell, .tab-item-link[title="Gift Options"], .tab-item-link[title="Recurring Profile"]')
		.attr('title', 'This tab was hidden by /skin/adminhtml/sdm/ellison/js/sdm_catalog.js')
		.hide();

	// Get head text
	var headText = $('.head-products').text();

	// Instructions text
	if (headText.indexOf('(Idea) (ID') !== -1) {
		jQuery(".inst-products").remove();
	} else if (headText.indexOf('(Product) (ID') !== -1) {
		jQuery(".inst-ideas").remove();
	} else {
		jQuery(".inst-products, .inst-ideas").remove();
	}

	// Tweak other fields, mainly lifecycle related
	if (headText.indexOf('(Idea) (ID') !== -1 || headText.indexOf('(Product) (ID') !== -1){

		/**
		 * JS TWEAKS --> Product, Idea
		 * Disable lifecycle fields and add appropriate descriptions
		 */

		$("#allow_preorder, #allow_quote, #allow_cart_backorder, #allow_checkout_backorder, #visibility, #inventory_backorders, #inventory_stock_availability, #allow_cart, #allow_checkout, #button_display_logic")
			.attr('disabled','disabled')
			.css('cursor', 'not-allowed');

		$('#availability_message')
			.parent()
			.parent()
			.after("<tr><td colspan='3'><div style='width:100%;float:left;margin: 40px 0px;border-top:1px solid #ccc;'></div></td></tr>");

		$('#button_display_logic')
			.css('height', '4em');

		$("#inventory_backorders, #inventory_stock_availability")
			.after('<p class="note">This value is controlled by the current lifecycle settings and cannot be modified manually.</p>');
		$('#inventory_use_config_backorders, label[for="inventory_use_config_backorders"]')
			.hide();
		$('#visibility_default, label[for="visibility_default"]')
			.hide();
		$('#allow_preorder_default, label[for="allow_preorder_default"]')
			.hide();
		$('#allow_quote_default, label[for="allow_quote_default"]')
			.hide();
		$('#allow_cart_backorder_default, label[for="allow_cart_backorder_default"]')
			.hide();
		$('#allow_checkout_backorder_default, label[for="allow_checkout_backorder_default"]')
			.hide();
		$('#allow_cart_default, label[for="allow_cart_default"]')
			.hide();
		$('#allow_checkout_default, label[for="allow_checkout_default"]')
			.hide();
		$('#button_display_logic_default, label[for="button_display_logic_default"]')
			.hide();

		// Update product weights to only allow 0.00001 or great
		$('#weight').removeClass('number-range-0-99999999.9999')
			.addClass('number-range-0.001-99999999.9999');

	} else if (headText.indexOf('(Print Catalog)') !== -1) {

		/**
		 * JS TWEAKS --> Print Catalogs
		 * Display logic for print catalog
		 */

		 $("#visibility, #button_display_logic, #price, #weight")
			.attr('disabled','disabled')
			.css('cursor', 'not-allowed');

		$('#button_display_logic')
			.css('height', '4em');

		$('#price, #weight')
			.val('0')
			.after('<p class="note">This value is controlled by the current lifecycle settings and cannot be modified manually.</p>');

		$('#visibility_default, #button_display_logic_default, #price_default, #weight_default, label[for="visibility_default"], label[for="button_display_logic_default"], label[for="price_default"], label[for="weight_default"]').hide();

		$('#inventory_use_config_manage_stock').removeAttr('checked');

		$('#inventory_manage_stock').val(0);

	}

	// Inventory
	if (window.location.href.split('admin/catalog_product/new').length > 1) {
		$('#inventory_stock_availability').val('1');
	}

	// Price Display
	// Note: store IDs are hard-coded
	if (storeId != 5) {
		$("label[for=msrp]").closest('tr').hide();
		$("label[for=price]").text('Price/MSRP');
	} else {
		$("label[for=price]").text('Wholesale Price');
	}

	if (storeId == 4) {
		$("label[for=price]").closest('tr').hide();
		$("label[for=special_price]").closest('tr').hide();
		$("#price_euro").next('strong').text('[EUR]');
		$("#special_price_euro").next('strong').text('[EUR]');
	} else {
		$("label[for=price_euro]").closest('tr').hide();
		$("label[for=special_price_euro]").closest('tr').hide();
	}

	if (storeId == 7) {
		$("label[for=price]").text('Price/MSRP (GBP)');
		$("label[for=special_price]").text('Special Price (GBP)');
	} else if (storeId == 4) {
		$("label[for=price]").text('Price/MSRP (Euro)');
		$("label[for=special_price]").text('Special Price (Euro)');
	}

	// Attribute: enforcing is-required
	if (storeId == 5) {
		// Make MSRP required
		$('#msrp').addClass('required-entry');
	}

	// Width fix
	jQuery("#display_start_date, #display_end_date")
		.attr('style','width: 255px !important;');

	$('#tag_special').attr('disabled', 'disabled').css({cursor:'not-allowed', opacity:'0.5'});

});}(jQuery));