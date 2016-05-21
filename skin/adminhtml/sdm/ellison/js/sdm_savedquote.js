/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

jQuery(function($){$(document).ready(function(){

    // Grab "sample" row HTML for use when inserting rows later
    var newRowHtml = jQuery("#new-quote-item-table .sample").html();
    jQuery("#new-quote-item-table .sample").remove();

    // Add new item to preorder/quote
    $('#add-quote-item-button').click(function(){
        $('#add-item-wrap').show();
        $("#new-quote-item-table tbody").append(
            "<tr>" + newRowHtml + "</tr>"
        );
    });

    // Remove new item row from preorder/quote
    $('#new-quote-item-table').on('click', '.remove-add-item-row', function(){
        $(this).parents('tr').first().remove();
        if ($('#new-quote-item-table tr').length === 1) {
            $('#add-item-wrap').hide();
        }
    });

})}(jQuery));
