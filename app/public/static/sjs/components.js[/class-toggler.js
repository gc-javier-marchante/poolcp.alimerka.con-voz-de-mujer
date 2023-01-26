"use strict";

// Class definition
var GCClassToggler = function (element, options) {
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
        if (KTUtil.data(element).has('class-toggler') === true) {
            the = KTUtil.data(element).get('class-toggler');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('class-toggler');

        // Elements
        the.element = element;
        the.target = KTUtil.find(document, the.options.target);

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('class-toggler', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        KTUtil.addEvent(the.element, 'click', _click);
    }

    // Event Handlers
    var _click = function (e) {
        e.preventDefault();

        if (!KTUtil.hasClass(the.target, the.options.class)) {
            KTUtil.addClass(the.target, the.options.class);
        } else {
            KTUtil.removeClass(the.target, the.options.class);
        }
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCClassToggler.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('class-toggler')) {
        return KTUtil.data(element).get('class-toggler');
    } else {
        return null;
    }
}

// Create instances
GCClassToggler.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCClassToggler(elements[i], {
                class: elements[i].getAttribute('data-class-toggle'),
                target: elements[i].getAttribute('data-class-toggle-target'),
            });
        }
    }
}

// Global initialization
GCClassToggler.init = function () {
    GCClassToggler.createInstances('[data-class-toggle]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCClassToggler.init);
} else {
    GCClassToggler.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCClassToggler;
}
