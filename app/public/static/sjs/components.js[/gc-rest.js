"use strict";

// Class definition
var GCRest = (function () {
    return {
        get: function (collection, options, callback) {
            options.limit = 1;

            let elements = this.getAll(collection, options);

            if (elements
                && elements[0]) {
                return elements[0];
            }

            return {};
        },
        getAll: function (collection, options, callback) {
            let _return = [];

            $.ajax({
                dataType: 'json',
                url: window.gcPaths.LOCALED_ROOT_URL + 'rest/' + collection + '/',
                data: options,
                async: !!callback,
                success: function (result) {
                    if (result
                        && result.data
                        && result.data.length) {
                        _return = result.data;
                    } else {
                        _return = [];
                    }

                    if (callback) {
                        callback(_return);
                    }
                }
            });

            return _return;
        },
        init: function () { }
    }
})();

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCRest.init);
} else {
    GCRest.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCRest;
}
