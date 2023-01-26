"use strict";

// Class definition
var GCConfirmModal = function (element, options) {
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
        if (KTUtil.data(element).has('confirm-modal') === true) {
            the = KTUtil.data(element).get('confirm-modal');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('confirm-modal');

        if (!the.options.text) {
            the.options.text = __('Are you sure?');
        }

        // Elements
        the.element = element;

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('confirm-modal', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        KTUtil.addEvent(the.element, 'click', _click);
    }

    // Event Handlers
    var _click = function (e) {
        e.preventDefault();

        Swal.fire({
            text: the.options.text,
            icon: 'warning',
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: __('Continue'),
            cancelButtonText: __('Cancel'),
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-white'
            },
            reverseButtons: true
        }).then(function (value) {
            if (value.isConfirmed) {
                if (the.options.action) {
                    KTUtil.handlePost(the.options.action, {}, {
                        loaders: the.element,
                        target: the.element
                    });
                } else {
                    location.href = the.options.href;
                }
            }
        });
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCConfirmModal.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('confirm-modal')) {
        return KTUtil.data(element).get('confirm-modal');
    } else {
        return null;
    }
}

// Create instances
GCConfirmModal.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCConfirmModal(elements[i], {
                text: elements[i].getAttribute('data-gc-confirm'),
                action: elements[i].getAttribute('data-gc-confirm-action'),
                href: elements[i].getAttribute('href'),
            });
        }
    }
}

// Global initialization
GCConfirmModal.init = function () {
    GCConfirmModal.createInstances('[data-gc-confirm]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCConfirmModal.init);
} else {
    GCConfirmModal.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCConfirmModal;
}
