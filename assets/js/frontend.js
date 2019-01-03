// Polyfill for creating CustomEvents on IE9/10/11

// code pulled from:
// https://github.com/d4tocchini/customevent-polyfill
// https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent#Polyfill

try {
    var ce = new window.CustomEvent('test');
    ce.preventDefault();
    if (ce.defaultPrevented !== true) {
        // IE has problems with .preventDefault() on custom events
        // http://stackoverflow.com/questions/23349191
        throw new Error('Could not prevent default');
    }
} catch(e) {
    var CustomEvent = function(event, params) {
        var evt, origPrevent;
        params = params || {
            bubbles: false,
            cancelable: false,
            detail: undefined
        };

        evt = document.createEvent("CustomEvent");
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        origPrevent = evt.preventDefault;
        evt.preventDefault = function () {
            origPrevent.call(this);
            try {
                Object.defineProperty(this, 'defaultPrevented', {
                    get: function () {
                        return true;
                    }
                });
            } catch(e) {
                this.defaultPrevented = true;
            }
        };
        return evt;
    };

    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent; // expose definition to window
}

(function ($) {

    "use strict";

    /**
     * Append dynamically loaded Posts to the top of the loader
     */
    var waypoints = $('#loader-container').waypoint({
        handler: function(direction) {
            if (direction === 'down') {
                //make ajax call to fetch next post

                console.log(this.element.id + ' hit ' + direction);
            }
        }
    });
}(jQuery));