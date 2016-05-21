/**
 * Separation Degrees Media
 *
 * video.js
 *
 * @author    Separation Degrees <magento@separationdegrees.com>
 * @category  Separation Degrees Media
 * @package   sdm
 * @copyright Copyright (c) 2015 Separation Degrees Media (http://www.separationdegrees.com)
 */
(function($) {
    $.noConflict();
    $(document).ready(function() {
        //
        // popup close
        $('.video-embed .close, .video-overlay').click(function(){
            $('.video-embed').hide();
        });
    });
})(jQuery);

function getFrameID(id) {
    var elem = document.getElementById(id);
    if (elem) {
        if (/^iframe$/i.test(elem.tagName)) return id; //Frame, OK
        // else: Look for frame
        var elems = elem.getElementsByTagName("iframe");
        if (!elems.length) return null; //No iframe found, FAILURE
        for (var i = 0; i < elems.length; i++) {
            if (/^https?:\/\/(?:www\.)?youtube(?:-nocookie)?\.com(\/|$)/i.test(elems[i].src)) break;
        }
        elem = elems[i]; //The only, or the best iFrame
        if (elem.id) return elem.id; //Existing ID, return it
        // else: Create a new ID
        do { //Keep postfixing `-frame` until the ID is unique
            id += "-frame";
        } while (document.getElementById(id));
        elem.id = id;
        return id;
    }
    // If no element, return null.
    return null;
}

// Define YT_ready function.
var YT_ready = (function() {
    var onReady_funcs = [],
        api_isReady = false;
    /* @param func function     Function to execute on ready
     * @param func Boolean      If true, all qeued functions are executed
     * @param b_before Boolean  If true, the func will added to the first
                                 position in the queue*/
    return function(func, b_before) {
        if (func === true) {
            api_isReady = true;
            for (var i = 0; i < onReady_funcs.length; i++) {
                // Removes the first func from the array, and execute func
                onReady_funcs.shift()();
            }
        }
        else if (typeof func == "function") {
            if (api_isReady) func();
            else onReady_funcs[b_before ? "unshift" : "push"](func);
        }
    }
})();

// This function will be called when the API is fully loaded
function onYouTubePlayerAPIReady() {
    YT_ready(true)
}

var players = {};
//  Define a player storage object, to enable later function calls,
//  without having to create a new class instance again.
YT_ready(function() {
    jQuery(".close, .video-overlay, iframe[id]").each(function() {
        var identifier = this.id;
        var frameID = getFrameID(identifier);
        if (frameID) { //If the frame exists
            players[frameID] = new YT.Player(frameID, {
                events: {
                    "onReady": createYTEvent(frameID, identifier)
                }
            });
        }
    });
});


//  Returns a function to enable multiple events
function createYTEvent(frameID, identifier) {
    return function (event) {
        var player = players[frameID]; // Set player reference
        var the_div = jQuery('#'+identifier).parent();
        the_div.children('.close').click(function() {
            player.pauseVideo();
        });
        the_div.parent().children('.video-overlay').click(function() {
            player.pauseVideo();
        });
    }
}

//  Load YouTube Frame API
var tag = document.createElement('script');
tag.src = "//www.youtube.com/player_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
