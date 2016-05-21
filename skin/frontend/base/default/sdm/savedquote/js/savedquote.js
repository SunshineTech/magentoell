/**
 * Separation Degrees Media
 *
 * Allows saving quotes that can be later be converted into orders with preserved
 * pricing.
 *
 * @category  SDM
 * @package   SDM_SavedQuote
 * @author    Separation Degrees Media <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */

jQuery(function(){
    var selectAddressDiv = jQuery('#saved-quote-select-address');
    if (!selectAddressDiv.length) {
        return;
    }

    var filledFields = ['firstname', 'lastname', 'company', 'street1', 'street2', 'city', 'telephone', 'fax'];
    selectAddressDiv
        .find('.address-select')
        .change(function(){
            var addressId = jQuery(this).val();
            var hasValues = typeof addressFillData[addressId] !== 'undefined';
            var savedForm = jQuery("#shipping-new-address-form");
            if (hasValues) {
                // Use saved address (hide form)
                savedForm.hide();
                jQuery("#shipping\\:save_in_address_book").attr('checked', false);
                jQuery.each(filledFields, function(k,v){
                    jQuery('#shipping\\:'+v)
                        .val(addressFillData[addressId][v]);
                });
                savedInAddressBook.parent().hide();
            } else {
                // Show form again (for editing)
                savedForm.show();
                savedInAddressBook.parent().show();
            }   
        });
});
