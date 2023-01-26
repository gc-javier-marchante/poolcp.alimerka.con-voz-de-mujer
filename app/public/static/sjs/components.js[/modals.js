"use strict";

// Class definition
var GCModal = function (element, options) {
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
        if (KTUtil.data(element).has('open-modal') === true) {
            the = KTUtil.data(element).get('open-modal');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('open-modal');

        // Elements
        the.element = element;

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('open-modal', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        KTUtil.addEvent(the.element, 'click', _click);
    }

    // Event Handlers
    var _click = function (e) {
        e.preventDefault();

        GCModal.openModal(the.options);
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCModal.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('open-modal')) {
        return KTUtil.data(element).get('open-modal');
    } else {
        return null;
    }
}

// Create instances
GCModal.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCModal(elements[i], {
                url: elements[i].getAttribute('data-open-modal')
            });
        }
    }
}

// Global initialization
GCModal.init = function () {
    GCModal.createInstances('[data-open-modal]');
};

// Static methods
GCModal.openModal = (options) => {
    if (!options) {
        options = {};
    }

    let url = options.url,
        data = options.data,
        onLoad = options.onLoad,
        onSubmit = options.onSubmit;

    $.ajax({
        url: url,
        data: data,
        dataType: 'json',
        method: 'GET',
        success: function (result) {
            if (!result
                || !result.response
                || !result.response.succeeded
                || !result.response.html) {
                return;
            }

            $('.modal').filter(':visible').modal('hide');

            let $div = $('<div>');
            $div.append(result.response.html);
            $div = $div.children();
            $('body').append($div);
            KTUtil.domAdded();
            $div.modal('show');

            if (onSubmit) {
                $div.find('form').data('onSubmit', function (response) {
                    if (typeof (onSubmit) === 'string') {
                        let callbackFunction = function (response) {
                        };
                        eval('callbackFunction = ' + onSubmit + ';');
                        callbackFunction.call(caller, response);
                    } else {
                        onSubmit(response);
                    }
                });
            }

            if (onLoad) {
                onLoad($div);
            }

            $div.on('hidden.bs.modal', function () {
                $div.remove();
            });
        }
    });
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCModal.init);
} else {
    GCModal.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCModal;
}