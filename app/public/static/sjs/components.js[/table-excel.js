"use strict";

// Class definition
var GCTableDownload = function (element, options) {
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
        if (KTUtil.data(element).has('table-download') === true) {
            the = KTUtil.data(element).get('table-download');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('table-download');

        // Elements
        the.element = element;
        the.target = KTUtil.find(document, the.options.target);
        the.name = the.target.getAttribute('data-download-name');

        if (!$(the.target).is('table')) {
            the.target = $(the.target).find('table').get(0);
        }

        if (!the.name) {
            the.name = __('Table');
        }

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('table-download', the);
    }

    // Init Event Handlers
    var _handlers = function () {
        if ($(the.element).is('option')) {
            let $select = $(the.element).parents('select').eq(0);

            $select.on('change', function () {
                if ($select.val() == 'excel') {
                    $select.val('');
                    _click();
                }
            });
        } else {
            KTUtil.addEvent(the.element, 'click', _click);
        }
    }

    // Event Handlers
    var _click = function (e) {
        $(the.target).table2excel({
            exclude: '.noExl',
            name: 'Hoja 1',
            filename: the.name,
            fileext: '.xls'
        });
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCTableDownload.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('table-download')) {
        return KTUtil.data(element).get('table-download');
    } else {
        return null;
    }
}

// Create instances
GCTableDownload.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCTableDownload(elements[i], {
                target: elements[i].getAttribute('data-export-table'),
            });
        }
    }
}

// Global initialization
GCTableDownload.init = function () {
    GCTableDownload.createInstances('[data-export-table]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCTableDownload.init);
} else {
    GCTableDownload.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCTableDownload;
}
