"use strict";

// Class definition
var GCDraggable = function (element, options) {
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
        if (KTUtil.data(element).has('gc-draggable') === true) {
            the = KTUtil.data(element).get('gc-draggable');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('gc-draggable');

        // Elements
        the.element = element;

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('gc-draggable', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        KTUtil.addEvent(the.element, 'dragstart', _dragstart);
    }

    // Event Handlers
    var _dragstart = function (e) {
        if (e.target.id) {
            e.dataTransfer.setData('text', e.target.id);
        }
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCDraggable.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('gc-draggable')) {
        return KTUtil.data(element).get('gc-draggable');
    } else {
        return null;
    }
}

// Create instances
GCDraggable.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCDraggable(elements[i]);
        }
    }
}

// Global initialization
GCDraggable.init = function () {
    GCDraggable.createInstances('[draggable]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCDraggable.init);
} else {
    GCDraggable.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCDraggable;
}
