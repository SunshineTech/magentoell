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
jQuery(document).ready(function ( $ ) {
    $('#edit_form').submit(function (event) {
        // Reformat expiration date
        var formattedExpDate = $("#cc_exp_month").val() + "-" + $("#cc_exp_year").val();
        $("#card_expiry_date").val(formattedExpDate);
        // Insert current time
        var d = new Date();
        d = d.toISOString();
        $("#signed_date_time").val(d.substring(0, 19) + 'Z');
        // Generate unique transaction id
        $("#transaction_uuid").val(Math.floor(Math.random() * 100000000));
        // Generate ref num
        $("#reference_number").val(new Date().getTime());
        // Include or exclude card number
        if($("#card_number").val().indexOf('XXXX') >= 0) {
            // Card number is still original masked version
            // Rename card_number field so it doesn't get sent to CyberSource
            $("#card_number").attr("name", "masked_card_number");
        }
        else {
            // User / customer entered new card number
            // Add card_number to signed fields
            $("#signed_field_names").val($("#signed_field_names").val() + ",card_number");
        }
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
    });
});
