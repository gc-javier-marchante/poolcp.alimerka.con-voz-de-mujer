"use strict";

// Class definition
var GCModalInnerLink = function (element, options) {
    ////////////////////////////
    // ** Private Variables  ** //
    ////////////////////////////
    let the = this;

    if (typeof element === "undefined" || element === null) {
        return;
    }

    // Default Options
    let defaultOptions = {

    };

    ////////////////////////////
    // ** Private Methods  ** //
    ////////////////////////////

    let _construct = function () {
        if (KTUtil.data(element).has('modal-inner-link') === true) {
            the = KTUtil.data(element).get('modal-inner-link');
        } else {
            _init();
        }
    }

    let _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('modal-inner-link');

        // Elements
        the.element = element;

        if (!the.options.href
            || the.options.href.startsWith('#')
            || the.options.href.startsWith('javascript:')) {
            // Nothing
        } else {
            if ($(the.element).parents('.pagination, .clear-filters').length > 0) {
                // Event Handlers
                _handlers();
            } else {
                the.element.target = '_blank';
            }
        }

        // Bind Instance
        KTUtil.data(the.element).set('modal-inner-link', the);
    }

    // Init Event Handlers
    let _handlers = function () {
        KTUtil.addEvent(the.element, 'click', _click);
    }

    // Event Handlers
    let _click = function (e) {
        e.preventDefault();
        e.stopPropagation();
        GCModal.openModal({ url: the.options.href, data: {__modal_postback: 1} });
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCModalInnerLink.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('modal-inner-link')) {
        return KTUtil.data(element).get('modal-inner-link');
    } else {
        return null;
    }
}

// Create instances
GCModalInnerLink.createInstances = function (selector) {
    // Initialize Menus
    let elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (let i = 0, len = elements.length; i < len; i++) {
            new GCModalInnerLink(elements[i], {
                href: elements[i].getAttribute('href')
            });
        }
    }
}

// Global initialization
GCModalInnerLink.init = function () {
    GCModalInnerLink.createInstances('.modal a');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCModalInnerLink.init);
} else {
    GCModalInnerLink.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCModalInnerLink;
}
