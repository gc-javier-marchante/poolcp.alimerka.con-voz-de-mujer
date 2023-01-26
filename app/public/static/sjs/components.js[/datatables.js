"use strict";

// Class definition
var GCDatatables = function (element, options) {
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
        if (KTUtil.data(element).has('datatables') === true) {
            the = KTUtil.data(element).get('datatables');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('datatables');

        // Elements
        the.element = element;

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        the.datatable = $(the.element).DataTable({
            info: false,
            order: [],
            language: {
                emptyTable: __('No data available in table')
            }
        });

        // Bind Instance
        KTUtil.data(the.element).set('datatables', the);
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCDatatables.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('datatables')) {
        return KTUtil.data(element).get('datatables');
    } else {
        return null;
    }
}

// Create instances
GCDatatables.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCDatatables(elements[i]);
        }
    }
}

// Global initialization
GCDatatables.init = function () {
    GCDatatables.createInstances('[data-datatable]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCDatatables.init);
} else {
    GCDatatables.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCDatatables;
}
