(function($) {
    $(document).ready(function() {
        // Set events for add ship date links
        jQuery("#saved-quote-table").on('click', '.add-ship-date', function(){
            var thisLink = $(this);
            var thisUl = thisLink.parent().find('ul');
            var thisLi = thisUl.find('li').first();
            thisUl.append(
                $("<li>" + thisLi.html() + "</li>").find('input.qty').val('0').end()
            );
            return false;
        });

        // Load pre order dates
        var elements = jQuery('.preOrderDateSelect');
        if (elements.length) {
            // Load data for each pre order item
            elements.each(function() {
                var elem = jQuery(this);
                var name = getPreOrderElementSku(elem);
                loadPreOrderFromCookie(elem, name);
            });

            // Set save events for pre order item fields
            jQuery("#saved-quote-table")
                .on('change', '[name^=pre_order_]', function(){
                    var elem = jQuery(this).parents('.preOrderDateSelect');
                    var name = getPreOrderElementSku(elem);
                    var selects = elem.find('select');
                    elem.find('.validation-advice').hide();
                    parsePreorderDataOnChange(elements);
                    savePreOrderToCookie(selects, name);
                });

            // Create event for place pre order button
            jQuery('.btn-checkout')
                .on('click', function(){
                    // Check that quantities are valid
                    elements.each(function() {
                        var elem = jQuery(this);
                        var sku = getPreOrderElementSku(elem);
                        var skuQty = getElementCartQty(elem);
                        var userQty = 0;

                        // Hide validation message...
                        elem.find('.validation-advice').hide();

                        // And validate...
                        var userQty = getElementUserQty(elem);

                        // Show validation message on error
                        if (!userQty || userQty !== Number(skuQty)) {
                            elem.find('.validation-advice').show();
                        }
                    });

                    var hasErrors = jQuery("#saved-quote-table")
                        .find('.validation-advice')
                        .is(':visible');

                    if (hasErrors) {
                        alert("There are one or more errors with the product quantities you have selected.");
                        return false;
                    }

                    // Delete all pre order cookies and continue
                    elements.each(function() {
                        var elem = jQuery(this);
                        var name = getPreOrderElementSku(elem);
                        deletePreOrderCookies(name);
                    });
                });

            parsePreorderDataOnChange(elements);
        }
    });

    function getElementUserQty(elem) {
        var userQty = 0;
        elem.find('input').each(function(k,input){
            var qty = jQuery(input).val();
            if (!isNaN(parseFloat(qty)) && isFinite(qty) && qty >= 0) {
                userQty += Number(qty);
            }
        });
        return userQty;
    }

    function getElementCartQty(elem)
    {
        return elem.parents('tr').find('.product-cart-actions .qty').val();
    }

    function savePreOrderToCookie(selects, name, force) {
        var skuData = {};
        selects.each(function(){
            var select = jQuery(this);
            var input = select.parents('li').find('input.qty');
            if (typeof skuData[select.val()] === 'undefined') {
                skuData[select.val()] = 0;
            }
            skuData[select.val()] += Number(input.val());
        });
        var cookieValue = [];
        jQuery.each(skuData, function(k, value){
            value = Number(value);
            if (value > 0) {
                cookieValue.push(k + "," + value);
            }
        });
        jQuery.cookie('preorder-qtys-' + name, cookieValue.join('|'));
    }

    function loadPreOrderFromCookie(elem, name) {
        var cookie = jQuery.cookie('preorder-qtys-' + name);
        if (!cookie || !cookie.length) {
            return false;
        }

        var skuData = cookie.split('|');
        var dates = [];
        var qtys = [];
        jQuery.each(skuData, function(k, thisData){
            thisData = thisData.split(',');
            if (thisData.length === 2) {
                dates.push(thisData[0]);
                qtys.push(thisData[1]);
            }
        });

        // Trigger skuData.length-1 clicks
        for(clicks = 0; clicks < skuData.length-1; clicks++) {
            elem.find('.add-ship-date').trigger('click');
        }

        // Loop through each new element and set data
        elem.find('li').each(function(k, thisLi){
            thisLi = jQuery(thisLi);
            thisLi.find('select').val(dates[k]);
            thisLi.find('input').val(qtys[k]);
        });

        return true;
    }

    function deletePreOrderCookies(name)
    {
        return jQuery.cookie('preorder-qtys-' + name, '');
    }

    function getPreOrderElementSku(elem)
    {
        return elem.parents('.product-cart-info')
            .find('.product-cart-sku')
                .clone()
                .children()
                    .remove()
                .end()
                .text()
                .trim();
    }

    function parsePreorderDataOnChange(elements) {
        var dateTotals = {};

        // Update qty messages and get dateTotals for 
        elements.each(function(){
            var elem = jQuery(this);
            var userQty = getElementUserQty(elem);
            var cartQty = getElementCartQty(elem);
            var thisPrice = elem.parents('tr')
                .find('.product-cart-price .cart-price')
                .attr('data-price');

            // Update "total" qty message
            elem.find('.qty-total .user-qty').text(userQty);

            elem.find('select').each(function(){
                var thisSelect = jQuery(this);
                var thisQty = thisSelect.parents('li').find('input').val();
                var thisDate = thisSelect.find(':selected').text();
                if (!isNaN(parseFloat(thisQty)) && isFinite(thisQty) && thisQty >= 0) {
                    if (typeof dateTotals[thisDate] === 'undefined') {
                        dateTotals[thisDate] = 0;
                    }
                    dateTotals[thisDate] += Number(thisPrice * thisQty);
                }

            });
        });

        // Recalc totals
        $('.cart-totals #preorder-dates').remove();
        $('.cart-totals').append('<table id="preorder-dates"></table>');
        $.each(dateTotals, function(date, total) {
            $('#preorder-dates').append('<tr><th>' + date + ':</th><td>$' + total.toFixed(2) + '</td></tr>');
        });
    }
})(jQuery);

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD (Register as an anonymous module)
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {

    var pluses = /\+/g;

    function encode(s) {
        return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            // This is a quoted cookie as according to RFC2068, unescape...
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
            // Replace server-side written pluses with spaces.
            // If we can't decode the cookie, ignore it, it's unusable.
            // If we can't parse the cookie, ignore it, it's unusable.
            s = decodeURIComponent(s.replace(pluses, ' '));
            return config.json ? JSON.parse(s) : s;
        } catch(e) {}
    }

    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }

    var config = $.cookie = function (key, value, options) {

        // Write

        if (arguments.length > 1 && !$.isFunction(value)) {
            options = $.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
            }

            return (document.cookie = [
                encode(key), '=', stringifyCookieValue(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // Read

        var result = key ? undefined : {},
            // To prevent the for loop in the first place assign an empty array
            // in case there are no cookies at all. Also prevents odd result when
            // calling $.cookie().
            cookies = document.cookie ? document.cookie.split('; ') : [],
            i = 0,
            l = cookies.length;

        for (; i < l; i++) {
            var parts = cookies[i].split('='),
                name = decode(parts.shift()),
                cookie = parts.join('=');

            if (key === name) {
                // If second argument (value) is a function it's a converter...
                result = read(cookie, value);
                break;
            }

            // Prevent storing a cookie that we couldn't decode.
            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie;
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function (key, options) {
        // Must not alter options, thus extending a fresh object...
        $.cookie(key, '', $.extend({}, options, { expires: -1 }));
        return !$.cookie(key);
    };

}));
