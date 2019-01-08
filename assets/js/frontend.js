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


    $(document).ready(function () {
        $('#spl_post_container').html('');
    });

    /**
     * Append dynamically loaded Posts to the top of the loader
     */
    var waypoints = $('#loader-container').waypoint({
        handler: function(direction) {
            if (direction === 'down') {
                var is_next_post = 0;
                var postId = '';
                //make ajax call to fetch next post
                if (is_next_post > 0 || typeof  $('#loader-container').data('next') !== 'undefined'){
                    postId = $('#loader-container').data('next');
                }else{
                    postId = $('#loader-container').data('post');
                }
                var cat = $('#loader-container').data('category');
                var data = {
                    action: 'splGetPostTemplate',
                    nonce: spl_ajax_params.nonce,
                    postID : postId,
                    category_id : cat
                };

                console.log(data);
                $.ajax({
                    url : spl_ajax_params.ajaxUrl,
                    type : 'POST',
                    data : data,
                    dataType: 'json',
                    success : function (resp, status) {
                        if ( postId.postID !== '' && status === 'success'){
                            var container = '<article itemscope="" itemtype="">' +
                                '<header class="entry-header entry-header-01" style="margin-top: 4em; height: 150px; background: #f5f5f5; padding: 20px !important;">' +
                                '<div class="entry-before-title">' +
                                '</div>' +
                                '<h1 class="g1-mega g1-mega-1st entry-title" itemprop="headline" style="color: #fea620 !important; ">'+ resp.current_post.post_title +'</h1>' +
                                '</header>' +
                                '<div class="g1-content-narrow g1-typography-xl entry-content" itemprop="articleBody">' +
                                resp.current_post.post_content +
                                '</div>' +
                                '</article>';

                            $('#spl_post_container').append(container);
                            is_next_post++;
                            $('#loader-container').attr('data-next', resp.nextID);
                        }
                    },
                    error : function (error) {
                        console.log(error);
                    }
                });
                // console.log(this.element.id + ' hit ' + direction);
            }
        }
    });
}(jQuery));