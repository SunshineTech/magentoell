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

    var taxonomyType = $('#type')

    var hideField = function(id)
    {
        $('#' + id).parents('tr').first().hide();
    }

    var showField = function(id)
    {
        $('#' + id).parents('tr').first().show();
    }

    var renameField = function(id, name)
    {
        $('#' + id).parents('tr').first().find('label').text(name);
    }

    var showSpecialProducts = function()
    {
        $('#special-products').show();
    }

    var hideSpecialProducts = function()
    {
        $('#special-products').hide();
    }

    var setSpecialProductsType = function(isEvent)
    {
        $('#special-products h3')
            .text(isEvent ? "Calendar Event Products" : "Promotional Products");

        var requiredLabels = $(".special-product-table")
            .find(".discount-type span, .discount-value span");

        if (isEvent) {
            requiredLabels.hide();
            $('.product-discount-type-field, .product-discount-value-field')
                .attr('disabled', 'disabled')
                .attr('style', 'cursor: not-allowed;');
        } else {
            requiredLabels.show();
            $('.product-discount-type-field, .product-discount-value-field')
                .removeAttr('disabled')
                .attr('style', '');
        }
    }

    var redrawPage = function()
    {
        hideField('rich_description');
        hideField('swatch');
        hideSpecialProducts();
        setSpecialProductsType(false);
        renameField('rich_description', 'Rich Description');
        switch(taxonomyType.val()){
            case 'designer':
                showField('rich_description');
                break;
            // case 'event':    // Product association not handled in event taxonomy
            //     setSpecialProductsType(true);
            case 'special':
                showSpecialProducts();
                break;
            case 'material_compatibility':
                showField('swatch');
                hideField('description');
                break;
        }
    }

    taxonomyType.on('change', redrawPage);
    redrawPage();

    // Append the product table after the Form.php-generated table
    $('#special-products').insertAfter('.form-list');

    // Add a promo product row
    var i = 0;
    $('#add-new-row-button').click(function(event) {
        var newRow = $('<tr class="special-product-row">'
            + '<td><input class="required-entry input-text"  name="taxonomyData[special_products][sku][]" value="" type="text"></td>'
            + '<td><select class="select-dropdown product-discount-type-field" name="taxonomyData[special_products][discount_type][]" id=" class="" title=""><option value="percent">Percent</option><option value="absolute">Absolute</option><option value="fixed">Fixed</option></select></td>'
            + '<td><input class="required-entry input-text validate-number product-discount-value-field"  name="taxonomyData[special_products][discount_value][]" value="" type="text"></td>'
            + '<td class="last"><span title="Delete row"><button  title="Delete Row" type="button" class="scalable delete delete-select-row icon-btn" onclick="" style=""><span></span></button></span></td>'
            + '</tr>');

        $('table.special-product-table').append(newRow);

        // Add ability to remove promo product row right after creating this row
        $('.special-product-row .delete')
            .off('click')
            .on('click', function(event) {    // Registering 'click' event on this class
                $(this).parents('.special-product-row').remove();
            });
        i++;

        redrawPage();
    });

    // For pre-loaded rows
    $('.special-product-row .delete')
        .off('click')
        .on('click', function(event) {    // Registering 'click' event on this class
            $(this).parents('.special-product-row').remove();
            redrawPage();
        });

    // Add notice for start date/end date
    var html = "<tr><td colspan='2'><h3>Taxonomy Dates</h3><p style='color:#777'>End date \"2015-10-05\" will be saved as \"2015-10-05 23:59:59 PST\",<br>and the taxonomy item will go offline once the \"2015-10-06 0:00:00 PST\" reindex concludes.</p></td></tr>";
    jQuery("#start_date_1").parents('tr').first().before(html);

});}(jQuery));

