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
        window.$is_next = 0;
        $('.spl_post_container').html('');

        if ($('.spl-btn-container').hasClass('hide')){
            $('.spl-btn-container').removeClass('hide');
        }
    });
    
    function toggleLoader() {
        $(document).find('.loader').toggle(400);
    }

    /**
     * Append dynamically loaded Posts to the top of the loader
     */
    $(document).on('click', '.spl-btn-container', function (e) {

        toggleLoader();

        var container = $('.spl_post_container');

        var postId = '';
        //make ajax call to fetch next post
        if (typeof  container.attr('next') !== 'undefined'){
            postId = container.attr('next');
        }else{
            postId = container.attr('post');
        }

        var cat = container.attr('category');

        var data = {
            action: 'splGetPostTemplate',
            nonce: spl_ajax_params.nonce,
            postID : postId,
            category_id : cat
        };

        $.ajax({
            url : spl_ajax_params.ajaxUrl,
            type : 'POST',
            data : data,
            dataType: 'json',
            success : function (resp, status) {

                console.log(resp);
                toggleLoader();

                if ( data.postID !== '' && status === 'success'){

                   if (typeof resp.current_post.post_title !== 'undefined') {
                       var container = $('.spl_post_container');

                       var template =
                           '<div class="container main-content">'+
                           '<div class="row heading-title hentry" data-header-style="default_minimal">'+
                           '<div class="col span_12 section-title blog-title">'+

                           '<span class="meta-category">'+
                           '<a class="november-contest" href="http://worddev.com/?cat=23" alt="View all posts in November Contest">November Contest</a>'+
                           '</span>'+
                           '<h1 class="entry-title"> '+ resp.current_post.post_title +'</h1>'+

                           '<div id="single-below-header">'+
                           '<span class="meta-author vcard author"><span class="fn">By <a href="http://worddev.com/?author='+resp.current_post.post_author+'" title="Posts by '+resp.current_post.author+'" rel="author">'+resp.current_post.author+'</a></span></span>'+
                           '<span class="meta-date date updated">December 4, 2018</span>'+
                           '<span class="meta-comment-count"><a href="http://worddev.com/?p=32#respond"> No Comments</a></span>'+
                           '</div><!--/single-below-header-->'+

                           '</div><!--/section-title-->'+
                           '</div><!--/row-->'+


                           '<div class="row">'+

                           '<div class="post-area col standard-minimal span_12 col_last">'+
                           '<article id="post-32" class="regular post-32 post type-post status-publish format-standard has-post-thumbnail category-november-contest">'+

                           '<div class="inner-wrap animated">'+

                           '<div class="post-content">'+

                           '<div class="content-inner">'+

                           '<span class="post-featured-img">'+ resp.current_post.thumbnail+'</span>'+

                           '<div class="smart_content_wrapper"><p>'+ resp.current_post.post_content + '</p>'+
                           '</div>'+

                           '</div><!--/content-inner-->'+

                           '</div><!--/post-content-->'+

                           '</div><!--/inner-wrap-->'+
                           '</article><!--/article-->'+
                           '</div><!--/span_9-->'+
                           '</div><!--/row-->'+
                           '</div>';

                       container.append(template);

                       setTimeout(function () {
                           container.attr('next', resp.nextID);
                       }, 300);
                   }else {
                       return false;
                   }
                }
            },
            error : function (error) {
                //
            }
        });
    });
}(jQuery));