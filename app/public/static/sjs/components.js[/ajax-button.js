"use strict";

// Class definition
var GCAjaxBtn = function (element, options) {
    ////////////////////////////
    // ** Private Variables  ** //
    ////////////////////////////
    var the = this;

    if (typeof element === "undefined" || element === null) {
        return;
    }

    // Default Options
    var defaultOptions = {

    };

    ////////////////////////////
    // ** Private Methods  ** //
    ////////////////////////////

    var _construct = function () {
        if (KTUtil.data(element).has('ajax-btn') === true) {
            the = KTUtil.data(element).get('ajax-btn');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('ajax-btn');

        // Elements
        the.element = element;

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('ajax-btn', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        KTUtil.addEvent(the.element, 'click', _click);
    }

    // Event Handlers
    var _click = function (e) {
        e.preventDefault();

        KTUtil.handlePost(the.options.action, {}, {
            loaders: the.element,
            target: the.element
        });
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCAjaxBtn.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('ajax-btn')) {
        return KTUtil.data(element).get('ajax-btn');
    } else {
        return null;
    }
}

// Create instances
GCAjaxBtn.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCAjaxBtn(elements[i], {
                action: elements[i].getAttribute('data-gc-ajax')
            });
        }
    }
}

// Global initialization
GCAjaxBtn.init = function () {
    GCAjaxBtn.createInstances('[data-gc-ajax]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCAjaxBtn.init);
} else {
    GCAjaxBtn.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCAjaxBtn;
}
