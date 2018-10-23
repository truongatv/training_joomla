jQuery(function($) {

    //NOTE: override default ajax method
    var ajax = $.ajax;

    $.ajax = function(url, options) {

        if(typeof url === "object") {
            options = url;
            url = undefined;
        }
        options = options || {};

        if(typeof(options.success) == 'function') {

            //NOTE: override original success method
            options.successOriginal = options.success;

            options.success = function(data) {

                MagicSlideshow.stop();

                //NOTE: call original function
                var r = options.successOriginal.apply(options, arguments);

                MagicSlideshow.refresh();

                return r;

            }
        }

        return ajax(url, options);

    };

});
