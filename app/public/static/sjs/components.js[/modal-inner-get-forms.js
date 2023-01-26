"use strict";

// Class definition
var GCModalInnerForm = function (element, options) {
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
        if (KTUtil.data(element).has('modal-inner-get-form') === true) {
            the = KTUtil.data(element).get('modal-inner-get-form');
        } else {
            _init();
        }
    }

    let _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('modal-inner-get-form');

        // Elements
        the.element = element;

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('modal-inner-get-form', the);
    }

    // Init Event Handlers
    let _handlers = function () {
        KTUtil.addEvent(the.element, 'submit', _submit);
    }

    // Event Handlers
    let _submit = function (e) {
        e.preventDefault();
        e.stopPropagation();
        let data = $(the.element).serializeArray();
        data.__modal_postback = 1;
        GCModal.openModal({ url: the.options.action, data });
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCModalInnerForm.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('modal-inner-get-form')) {
        return KTUtil.data(element).get('modal-inner-get-form');
    } else {
        return null;
    }
}

// Create instances
GCModalInnerForm.createInstances = function (selector) {
    // Initialize Menus
    let elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (let i = 0, len = elements.length; i < len; i++) {
            new GCModalInnerForm(elements[i], {
                action: elements[i].getAttribute('action')
            });
        }
    }
}

// Global initialization
GCModalInnerForm.init = function () {
    GCModalInnerForm.createInstances('.modal form[method=get]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCModalInnerForm.init);
} else {
    GCModalInnerForm.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCModalInnerForm;
}
