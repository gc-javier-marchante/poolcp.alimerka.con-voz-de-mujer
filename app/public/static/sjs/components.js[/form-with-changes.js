"use strict";

// Class definition
var GCFormWithChanges = function (element, options) {
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
        if (KTUtil.data(element).has('form-with-changes') === true) {
            the = KTUtil.data(element).get('form-with-changes');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('form-with-changes');

        // Elements
        the.element = element;
        the.targets = element.querySelectorAll('input, select, textarea');

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('form-with-changes', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        $(the.targets).on('change', _change);
    }

    // Event Handlers
    var _change = function (e) {
        e.preventDefault();
        the.element.setAttribute('data-gc-has-changes', 'true');
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCFormWithChanges.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('form-with-changes')) {
        return KTUtil.data(element).get('form-with-changes');
    } else {
        return null;
    }
}

// Create instances
GCFormWithChanges.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCFormWithChanges(elements[i]);
        }
    }
}

// Global initialization
GCFormWithChanges.init = function () {
    GCFormWithChanges.createInstances('form');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCFormWithChanges.init);
} else {
    GCFormWithChanges.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCFormWithChanges;
}
