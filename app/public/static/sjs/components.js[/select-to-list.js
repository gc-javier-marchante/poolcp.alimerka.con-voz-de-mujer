"use strict";

// Class definition
var GCSelectToList = function (element, options) {
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
        if (KTUtil.data(element).has('select-to-list-element') === true) {
            the = KTUtil.data(element).get('select-to-list-element');
        } else {
            _init();
        }
    }

    let _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('select-to-list-element');

        // Elements
        the.element = element;
        the.id = ($(the.element).is('[data-id]') ? $(the.element).attr('data-id') : $(the.element).parents('[data-id]').attr('data-id'));
        the.$list = $('table[data-select-key="' + the.options.list + '"]');

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('select-to-list-element', the);
    }

    // Init Event Handlers
    let _handlers = function () {
        KTUtil.addEvent(the.element, 'click', _click);
    }

    // Event Handlers
    let _click = function (e) {
        e.preventDefault();
        e.stopPropagation();

        GCSelectToList.addIdToList(the.element, the.id, the.$list, $(the.element).is('[data-select-close]'));
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

GCSelectToList.addIdToList = function (target, id, list, close_modal, replace) {
    let $list = $(list),
        datatable = null,
        page_len = 1;

    if ($list.is('[data-datatable]')) {
        datatable = KTUtil.data($list.get(0)).get('datatables').datatable;
    }

    let resetDataTable = () => {
        if (datatable) {
            datatable.page.len(page_len);
            datatable.draw();
        }
    };

    if (datatable) {
        page_len = datatable.page.len();
        datatable.page.len(1000000);
        datatable.draw();
    }

    let $duplicate = $list.find('tr[data-id="' + id + '"]');

    if (!replace && $duplicate.length > 0) {
        resetDataTable();
        Swal.fire({
            text: __('Element is already on the list.'),
            icon: 'error',
            buttonsStyling: false,
            confirmButtonText: __('Ok'),
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
    } else {
        if ($duplicate.length > 0) {
            gcAjaxCallbacks.remove($duplicate);
        }

        resetDataTable();
        $.getJSON($list.attr('data-select-renderer'), { id: id }, function (result) {
            if (result.response.html) {
                let $theRow = $('<div>');
                $theRow[0].innerHTML = result.response.html;
                $theRow = $theRow.find('tbody tr');

                if (datatable) {
                    datatable.row.add($theRow[0]).draw(false);
                } else {
                    $list.append($theRow);
                }

                KTUtil.domAdded();
                $theRow.parents('form').attr('data-gc-has-changes', 'true');
            }
        });
    }

    if (close_modal) {
        $(target).parents('.modal').modal('hide');
    }
}

// Static methods
GCSelectToList.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('select-to-list-element')) {
        return KTUtil.data(element).get('select-to-list-element');
    } else {
        return null;
    }
}

// Create instances
GCSelectToList.createInstances = function (selector) {
    // Initialize Menus
    let elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (let i = 0, len = elements.length; i < len; i++) {
            new GCSelectToList(elements[i], {
                list: elements[i].getAttribute('data-select')
            });
        }
    }
}

// Global initialization
GCSelectToList.init = function () {
    GCSelectToList.createInstances('[data-select]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCSelectToList.init);
} else {
    GCSelectToList.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCSelectToList;
}
