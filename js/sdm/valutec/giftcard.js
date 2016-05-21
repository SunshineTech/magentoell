/**
 * Separation Degrees One
 *
 * Valutec Giftcard Integration
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_Valutec
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

var SDM_Valutec_Giftcard = Class.create();
SDM_Valutec_Giftcard.prototype = {
    /**
     * Initialize the object
     * 
     * @param  string urlApply
     * @param  string urlBalance
     * @param  string urlRemove
     */
    initialize: function(urlApply, urlBalance, urlRemove) {
        this.button = {};
        this.url = {};
        // HTML IDs
        this.form           = 'co-payment-form';
        this.button.apply   = 'sdm-valutec-giftcard-apply-button';
        this.button.balance = 'sdm-valutec-giftcard-balance-button';
        this.button.remove  = 'sdm-valutec-giftcard-remove-button';
        this.response       = 'sdm-valutec-giftcard-balance-response';
        // Ajax URLs
        this.url.apply      = urlApply;
        this.url.balance    = urlBalance;
        this.url.remove     = urlRemove;
    },

    /**
     * Apply the giftcard to the quote
     */
    apply: function() {
        if (this._valid()) {
            this._request(this.url.apply);
        }
    },

    /**
     * Perform giftcard balance lookup
     */
    getBalance: function() {
        if (this._valid()) {
            this._request(this.url.balance);
        }
    },

    /**
     * Remove the giftcard from the quote
     */
    remove: function() {
        this._request(this.url.remove);
    },

    /**
     * Make an ajax request to a url
     *
     * @param  string url
     */
    _request: function(url) {
        this._processingStart();
        new Ajax.Request(url, {
            method: 'post',
            parameters: Form.serialize(this.form),
            onComplete: this._onComplete.bind(this),
            onSuccess: this._onSuccess.bind(this),
            onFailure: this._onFailure.bind(this)
        });
    },

    /**
     * Validate the giftcard form
     * 
     * @return boolean
     */
    _valid: function() {
        var validator = new Validation(this.form);
        return validator.validate();
    },

    /**
     * Events fired after ajax request completes (success or failure)
     */
    _onComplete: function() {
        this._processingEnd();
    },

    /**
     * Events fired if the ajax request was successful (200)
     */
    _onSuccess: function(response) {
        this._showMessage(response.responseJSON.message);
        if ("remove_visible" in response.responseJSON) {
            this._removeVisibility(response.responseJSON.remove_visible);
        }
        if ("update_section" in response.responseJSON) {
            $('checkout-' + response.responseJSON.update_section.name + '-load').update(response.responseJSON.update_section.html);
        }
    },

    /**
     * Events fired if the ajax request was unsuccessful
     */
    _onFailure: function() {
        this._showMessage('An unknown error has occurred');
    },

    /**
     * Display text to the customer
     *
     * @param  string message
     */
    _showMessage: function(message) {
        $(this.response).show().update(message);
    },

    /**
     * Events fired when the ajax request starts
     */
    _processingStart: function() {
        this._buttonDisable();
        this._showMessage('Please wait...');
    },

    /**
     * After ajax completion
     */
    _processingEnd: function() {
        this._buttonEnable();
    },

    /**
     * Disable all buttons while processing
     */
    _buttonDisable: function() {
        this._buttonSetDisable(true);
    },

    /**
     * Enable all buttons after processing
     */
    _buttonEnable: function() {
        this._buttonSetDisable(false);
    },

    /**
     * Enable or disable buttons
     *
     * @param  boolean flag
     */
    _buttonSetDisable: function(flag) {
        for (var key in this.button) {
            var elem = $(this.button[key])
            if (elem) {
                elem.disabled = flag;
            }
        }
    },

    /**
     * Hide or show the remove button based on flag
     *
     * @param  boolean flag
     */
    _removeVisibility:function (flag) {
        if (flag) {
            $(this.button.remove).show();
        } else {
            $(this.button.remove).hide();
        }
    }
};
