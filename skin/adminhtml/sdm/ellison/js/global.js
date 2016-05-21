/**
 * Separation Degrees One
 *
 * Ellison's custom product taxonomy implementation.
 *
 * @category  SDO
 * @package   SDO
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

(function($){
    $.noConflict();
    $(document).ready(function() {
        // (╯°□°）╯︵ ┻━┻

        // Make it so when we create a new customer that
        // the "Admin" value is missing by default
        $("#_accountwebsite_id option[value='0']").removeAttr('selected');

        // If we're in the custgomer admin, remove a few tabs...
        if ($('body.adminhtml-customer-edit').length) {
            $('#customer_info_tabs_customer_edit_tab_recurring_profile, #customer_info_tabs_customer_edit_tab_agreements, #customer_info_tabs_newsletter, #customer_info_tabs_tags')
            .attr('title', 'This tab was hidden by /skin/adminhtml/sdm/ellison/js/global.js')
            .hide();
        }
    });
}(jQuery));

function CategoryTreeColored()
{
    var catalogTreeReg = /^(Catalog|Print Catalogs) \([\d]*\)$/;
    var parentTreeReg = /^(Products|Projects|Designers|Blog|Lessons|Videos|Ideas) \([\d]*\)$/;
    var columnReg = /^Column [\d]* \([\d]*\)$/;
    var featuredReg = /^Featured Products \([\d]*\)$/;

    jQuery('.x-tree-node-el a span').each(function(){
        var link = jQuery(this);
        var text = jQuery.trim(link.text());
        if (catalogTreeReg.test(text)){
            link.css({
                background: 'rgb(72, 168, 237)',
                color: '#FFF'
            })
            .parent()
            .parent()
            .removeClass('no-active-category');
        } else if (parentTreeReg.test(text)){
            link.css({
                background: '#525759',
                color: '#fff'
            });
        } else if (columnReg.test(text)){
            link.css({
                background: '#DF7B84',
                color: '#fff'
            });
        } else if (featuredReg.test(text)){
            link.css({
                background: 'rgb(61, 189, 72)',
                color: '#fff'
            });
        }
    });
}
