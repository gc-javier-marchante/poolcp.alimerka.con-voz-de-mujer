"use strict";

// Class definition
var GCSelectRedirect = function (element, options) {
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
        if (KTUtil.data(element).has('action-selection-element') === true) {
            the = KTUtil.data(element).get('action-selection-element');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('action-selection-element');

        // Elements
        the.element = element;

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('action-selection-element', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        $(the.element).on('change', _change);
    };

    // Event Handlers
    var _change = function () {
        let $selectedOption = $(the.element).children('option:selected');

        if ($selectedOption.length > 0
            && $selectedOption.is('[data-redirect]')) {
            location.href = $selectedOption.attr('data-redirect');
            $(the.element).val('');
        }
    };

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCSelectRedirect.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('action-selection-element')) {
        return KTUtil.data(element).get('action-selection-element');
    } else {
        return null;
    }
}

// Create instances
GCSelectRedirect.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCSelectRedirect(elements[i], {
                target: elements[i].getAttribute('data-action-selection'),
            });
        }
    }
}

// Global initialization
GCSelectRedirect.init = function () {
    GCSelectRedirect.createInstances('[data-action-selection]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCSelectRedirect.init);
} else {
    GCSelectRedirect.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCSelectRedirect;
}
