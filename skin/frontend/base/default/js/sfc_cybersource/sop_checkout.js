/**
 * StoreFront CyberSource Tokenized Payment Extension for Magento
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of StoreFront Consulting, Inc.
 *
 * @category  SFC
 * @package   SFC_CyberSource
 * @author    Garth Brantley <garth@storefrontconsulting.com>
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 * @license   http://www.storefrontconsulting.com/media/downloads/ExtensionLicense.pdf StoreFront Consulting Commercial License
 * @link      http://www.storefrontconsulting.com/cybersource-saved-credit-cards-extension-for-magento/
 *
 */

var sopCardNumber;

function submitSopForm() {
    (function($){
        // Add card number
        $('#card_number').val(sopCardNumber);
        // Insert current time
        var d = new Date();
        d = d.toISOString();
        $("#signed_date_time").val(d.substring(0, 19) + 'Z');
        // Generate unique transaction id
        $("#transaction_uuid").val(Math.floor(Math.random() * 100000000));
        // Generate ref num
        $("#reference_number").val(new Date().getTime());
        // Sign request using secret key
        var signedFieldNames = $('#signed_field_names').val().split(",");
        // Collect data to sign
        var dataToSign = [];
        signedFieldNames.forEach(function (item) {
            dataToSign.push(item + "=" + $('#' + item).val());
        });
        dataToSign = dataToSign.join(",");
        // Sign and store in signature field
        $("#signature").val(CryptoJS.HmacSHA256(dataToSign, sopSecretKey).toString(CryptoJS.enc.Base64));
        // Submit form
        $("#sop-pay-form").submit();
    })(jQuery);
}

// Replace review save function
function reviewSave() {
    (function($){
        if(payment.currentMethod != 'sfc_cybersource') {
            // Call original payment save function
            return review.save();
        }
        else {
            // Check if we are already running and return immediately if so
            if (checkout.loadWaiting!=false) return;
            // Turn on waiting indicator
            checkout.setLoadWaiting('review');
            // First submit the SOP payment form
            submitSopForm();
            // Save profile id in form
        }
    })(jQuery);
}

// Override credit card dependent validation functions for SOP
Validation.addAllThese([
    ['validate-cc-number-sop', 'Please enter a valid credit card number.', function(v, elm) {
        // Check if credit card number is stored in js var, if so use that number for validation
        if(sopCardNumber.length > 0) {
            v = sopCardNumber;
        }
        // Now call regular validation
        return Validation.get('validate-cc-number').test(v, elm);
    }],
    ['validate-cc-type-sop', 'Credit card number does not match credit card type.', function(v, elm) {
        // Check if credit card number is stored in js var, if so use that number for validation
        if(sopCardNumber.length > 0) {
            v = sopCardNumber;
        }
        // Now call regular validation
        return Validation.get('validate-cc-type').test(v, elm);
    }],
    ['validate-cc-type-select-sop', 'Card type does not match credit card number.', function(v, elm) {
        var ccNumberContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_type')) + '_cc_number');
        ccNumValue = ccNumberContainer.value;
        // Check if credit card number is stored in js var, if so use that number for validation
        if(sopCardNumber.length > 0) {
            ccNumValue = sopCardNumber;
        }
        // Now call validation
        if (Validation.isOnChange && Validation.get('IsEmpty').test(ccNumValue)) {
            return true;
        }
        if (Validation.get('validate-cc-type').test(ccNumValue, ccNumberContainer)) {
            Validation.validate(ccNumberContainer);
        }
        return Validation.get('validate-cc-type').test(ccNumValue, ccNumberContainer);
    }]
]);

jQuery(document).ready(function ( $ ) {
    // Replace payment save function
    payment.save = function() {
        if(this.currentMethod == 'sfc_cybersource') {
            // Customer is entering new credit card for this order
            // Check if CC field already obscured
            if ($('#sfc_cybersource_cc_number').val().indexOf('XXXX') == -1) {
                // We didn't find XXXX in cc number, this means cusotmer entered a new one
                // Save card number
                sopCardNumber = $('#sfc_cybersource_cc_number').val();
                // Obscure card number before posting to Magento server
                $('#sfc_cybersource_cc_number').val("XXXX" + sopCardNumber.substr(sopCardNumber.length - 4));
                $('#sfc_cybersource_cybersource_token').val('SOP_CREATE_NEW_PROFILE');
                $('#sfc_cybersource_saved_cc_last_4').val(sopCardNumber.substr(sopCardNumber.length - 4));
            }
            else {
                // Found XXXX in cc number, so customer is leaving previously entered one in place
            }
        }
        else {
            // Customer is choosing existing saved card or using another payment method
            // Don't do anything in this case
        }
        // Call original payment save function
        return Payment.prototype.save.call(this);
    };

    // Handle load on iframe
    $('#sop-iframe').load(function(){
        try {
            var response = $.parseJSON($('#sop-iframe').contents().find('#sop-iframe-textarea').val());
            if (response['status'] == 'success') {
                // Save token in payment form
                $('#sfc_cybersource_cybersource_token').val(response['payment_token']);
                // Turn off waiting indicator so review.save can turn it back on
                checkout.setLoadWaiting(false);
                // Create payment token via SOP was successful
                review.save();
            }
            else if (response['status'] == 'failed') {
                // Simulate checkout failed
                checkout.setLoadWaiting(false);
                // Put up error message
                alert(sopErrorMessage);
            }
            else {
                // No response, don't do anything
            }
        } catch(e) { }
    });

});
