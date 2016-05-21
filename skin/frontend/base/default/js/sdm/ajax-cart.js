/**
 * Separation Degrees One
 *
 * Checkout-related customization
 *
 * @category  SDM
 * @package   SDM_Checkout
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

// Declare reference to ajax cart in global namespace
var ellisonAjaxCart = null;
var ajaxCartSelectors = null;

// jQuery closure to map $ back to jQuery
jQuery(function ($) {
    $(document).ready(function () {

        /**
         * Update Minicart prototype to have new updateFormKey method
         * @param  string key
         * @return void
         */
        Minicart.prototype.updateFormKey = function (key) {
            this.formKey = key;
            return this;
        };

        /**
         * Updates the number of items and cart subtotal in the header
         * @param  object result
         * @return this
         */
        Minicart.prototype.updateTopBar = function (result) {
            if (typeof result['cart_header'] !== 'undefined'){
                var cartHeaderHtml = $("<div>"+result['cart_header']+"</div>");
                // Update header counts
                ellisonAjaxCart.minicart
                    .find('.count')
                    .text(cartHeaderHtml.find('.count').text());
                ellisonAjaxCart.minicart
                    .find('.count-desk')
                    .text(cartHeaderHtml.find('.count-desk').text());
            }
            return this;
        };

        /**
         * Adding functionality to updateContentOnRemove() to call updateTopBar()
         * @param  object result
         * @param  object el
         * @return this
         */
        Minicart.prototype.updateContentOnRemove = function(result, el) {
            this.updateCartWithResults(result);
            this.showMessage(result);
            return this;
        };

        /**
         * Adding functionality to updateContentOnUpdate() to call updateTopBar()
         * @param  object result
         * @param  object el
         * @return this
         */
        Minicart.prototype.updateContentOnUpdate = function(result) {
            this.updateCartWithResults(result);
            this.showMessage(result);
            return this;
        };

        /**
         * Fixed updating issues with removeItem
         * @param  {[type]} el [description]
         * @return {[type]}    [description]
         */
        Minicart.prototype.removeItem = function(el) {
            var cart = this;
            if (confirm(el.data('confirm'))) {
                cart.hideMessage();
                cart.showOverlay();
                $j.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {form_key: cart.formKey},
                    url: el.attr('href')
                }).done(function(result) {
                    cart.hideOverlay();
                    if (result.success) {
                        cart.updateCartWithResults(result);
                    } else {
                        cart.showMessage(result);
                    }
                }).error(function() {
                    cart.hideOverlay();
                    cart.showError(cart.defaultErrorMessage);
                });
            }
        };

        Minicart.prototype.updateCartWithResults = function(result) {
            $j(this.selectors.container).html(result.content);
            this.updateTopBar(result);
        }

        Minicart.prototype.showError = function(message) {
            $j(this.selectors.error).html(message).fadeIn('slow');
        }

        Minicart.prototype.showSuccess = function(message) {
            $j(this.selectors.success).html(message).fadeIn('slow');
        }

        // Custom ajaxcart selectors that will override the default selectors
        ajaxCartSelectors = {
            overlay    : '#header-cart',
            itemRemove : '#header-cart .remove'
        };

        /**
         * Ajax Cart Object
         * @return void
         */
        var ajaxAddToCart = function () {

            /**
             * Form being submitted
             * @type object
             */
            this.form = null;

            /**
             * The add to cart button
             * @type object
             */
            this.button = null;

            /**
             * jQuery object of the top cart
             * @type object
             */
            this.minicart = null;

            /**
             * The top cart links
             * @type object
             */
            this.topLinks = null;

            /**
             * Key to track animations so they don't overlap
             * @type int
             */
            this.animKey = 0;

            /**
             * Is there an add to cart ajax request?
             * @type bool
             */
            this.activeAjax = false;

            /**
             * Stores if the last request had an error
             * @type bool
             */
            this.hadError = false;

            /**
             * Standard error message
             * @type string
             */
            this.standardError = "An error has occurred adding this item to the cart.";

            /**
             * Initialize the add to cart controller
             * @return void
             */
            this.init = function () {
                // Primary ajax cart function
                $("body")
                    .off("click", '.ajax-cart')
                    .on("click", '.ajax-cart', $.proxy(this.onClick, this));

                // Set/reset jQuery references to variable (changing) DOM elements
                this.minicart = $('.header-minicart');
                $(".ajax-cart").parents('form').on("change", $.proxy(this.onFromChange, this));
            };

            /**
             * Set default value
             *
             * @param ev
             */
            this.onFromChange = function (ev) {
                var form = this.getFormFromEvent(ev);
                if (form && form.find(".ajax-cart")) {
                    form.find(".ajax-cart")
                        .removeClass("added");
                }
            };

            /**
             * Logic for handling button click event.
             * First get form, validate it, and submit on pass
             * @param  ev
             * @return void
             */
            this.onClick = function (ev) {
                // Check if we don't have an active ajax request already
                if (this.activeAjax) {
                    return false;
                }

                // Trigger event
                $(document).trigger("ajaxcart-start");

                // Get the button object from the jQuery event (if present)
                this.button = this.getButtonFromEvent(ev);

                // Prep ajax variables
                var url = '';
                var data = {};

                // Get the form object from jQuery event
                this.form = this.getFormFromEvent(ev);
                if (this.form !== false && this.form.is('form') && this.form.length === 1) {
                    // Create prototype version of form and run validation
                    var protoForm = new VarienForm(this.form.attr('id'), true);
                    if (!protoForm.validator.validate()) {
                        this.error("Form validation failed.");
                        return false;
                    }
                    url = this.form.attr('action');
                    data = this.form.serialize();
                }else{
                    url = this.button.attr('data-ajax-url');
                }

                // Ajaxify the URL
                url = url.replace("checkout/cart/add", "checkout/cart/ajaxadd")
                         .replace("http://", "//");

                // Make our request
                this.startLoading();
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data: data,
                    cache: false
                })

                // Set action for successful ajax connection
                .done($.proxy(function (json) {
                    json = json || {};
                    if (json.status == "ERROR") {
                        json.message = json.message || this.standardError;
                        this.swapMiniCartHtml(json);

                        alert(this.__(json.message));
                        this.hadError = true;
                    } else if (json.status == "SUCCESS") {
                        // Replace minicart HTML
                        this.swapMiniCartHtml(json);

                        // Alert the user if min. qty. was forced
                        if (json.minQtyOverride) {
                            alert(json.minQtyOverride);
                        }

                        // Refresh our jQuery object references
                        this.init();
                    } else {
                        alert(this.__(this.standardError));
                    }

                    // Trigger event
                    $(document).trigger("ajaxcart-done");

                }, this))

                // Set action for ajax request failure
                .fail($.proxy(function (result) {
                    this.hadError = true;
                    alert(this.__(this.standardError));
                }, this))

                // Set "always" action (occurs after success or fail)
                .always($.proxy(function () {
                    this.endLoading();
                }, this));
            }

            /**
             * Returns the add to cart button from the jQuery event object
             * @param  ev
             * @return bool|object
             */
            this.getButtonFromEvent = function (ev) {
                var target = $(ev.currentTarget);
                var type = target.attr('type');
                if (target.is('button') || type == "submit" || type == "button") {
                    return target;
                }
                return false;
            }

            /**
             * Returns the current form from the jQuery event object
             * @param  ev
             * @return object
             */
            this.getFormFromEvent = function (ev) {
                var target = $(ev.currentTarget);
                if (!target.is('form')) {
                    target = target.parents('form');
                }
                return target;
            }

            this.swapMiniCartHtml = function(json){
                if (typeof json['cart_header'] === 'undefined'){
                    return this;
                }
                if (typeof json['cart_content'] === 'undefined'){
                    return this;
                }

                var cartHeaderHtml = $(json['cart_header']);

                // Update cart contents
                Mini.updateContentOnUpdate({content : json['cart_content']});

                // Show cart message
                if (json.message){
                    Mini.showMessage({
                        'message' : json.message
                    });
                }

                // Update header counts
                this.minicart
                    .find('.count')
                    .text(cartHeaderHtml.find('.count').text());
                this.minicart
                    .find('.count-desk')
                    .text(cartHeaderHtml.find('.count-desk').text());

                // Reinitialize the minicart
                Mini.init();
            }

            /**
             * Logic for starting Ajax Cart loading
             * @return void
             */
            this.startLoading = function () {
                // Prep our new add request
                this.activeAjax = true;

                // Show mini cart overlay
                Mini.showOverlay();

                // Add loading animation to button
                this.button
                    .addClass("ajax-loading")
                    .removeClass("added")
                    .text(this.__("Adding..."));
            }

            /**
             * Logic for ending Ajax Cart loading
             * @return void
             */
            this.endLoading = function () {
                this.activeAjax = false;

                // Remove loading animation from button
                this.button
                    .removeClass("ajax-loading")
                    .text(this.__("Add To Cart"));

                // Drop the mini cart
                hoverCartOnAction();

                // Close the mini cart after 5 secs
                var thisHoverCartKey = ++hoverCartKey;
                setTimeout(function(){
                    if (hoverCartKey === thisHoverCartKey) {
                        if (!$("#header-cart").is(':hover') && !$(".header-minicart .skip-cart").is(':hover')) {
                            hoverCartOffAction();
                        }
                    }
                }, 5000);


                if (this.hadError) {
                    // We had an error; skip success animations
                    this.hadError = false;
                } else {
                    // Show success animation for all other ajax forms
                    this.button
                        .removeClass("ajax-loading")
                        .addClass("added")
                        .text(this.__("Added To Cart"));
                }

                // Show mini cart overlay
                Mini.hideOverlay();
            }


            /**
             * Wrapper for console.error (because old IE loves throwing
             * console undefined exceptions)
             * @return void
             */
            this.error = function (msg) {
                window.console && console.error(msg);
            }

            /**
             * Shortcut for the Magento JS Translate functionality
             * @return string
             */
            this.__ = function (string) {
                return Translator.translate(string);
            }
        };

        // Initialize Ajax Cart
        ellisonAjaxCart = new ajaxAddToCart;
        ellisonAjaxCart.init();


// End jQuery closure
    });
}(jQuery));

jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
};
