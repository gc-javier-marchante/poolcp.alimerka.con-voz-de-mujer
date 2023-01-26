"use strict";

// Class definition
var KTFileInput = function (element, options) {
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
        if (KTUtil.data(element).has('file-input') === true) {
            the = KTUtil.data(element).get('file-input');
        } else {
            _init();
        }
    }

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.uid = KTUtil.getUniqueId('file-input');

        // Elements
        the.element = element;
        the.inputElement = KTUtil.find(element, 'input[type="file"]');
        the.wrapperElement = KTUtil.find(element, '.file-input-wrapper');
        the.cancelElement = KTUtil.find(element, '[data-kt-file-input-action="cancel"]');
        the.removeElement = KTUtil.find(element, '[data-kt-file-input-action="remove"]');
        the.hiddenElement = KTUtil.find(element, 'input[type="hidden"]');
        the.name = the.wrapperElement.innerText.trim();

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('file-input', the);

        if (the.options.clear_on_init) {
            the.removeElement.click();
        }
    }

    // Init Event Handlers
    var _handlers = function () {
        KTUtil.addEvent(the.inputElement, 'change', _change);
        KTUtil.addEvent(the.cancelElement, 'click', _cancel);
        KTUtil.addEvent(the.removeElement, 'click', _remove);
    }

    // Event Handlers
    var _change = function (e) {
        e.preventDefault();

        if (the.inputElement !== null && the.inputElement.files && the.inputElement.files[0]) {
            // Fire change event
            if (KTEventHandler.trigger(the.element, 'kt.fileinput.change', the) === false) {
                return;
            }

            the.wrapperElement.innerText = the.inputElement.files[0].name;

            KTUtil.addClass(the.element, 'file-input-changed');
            KTUtil.removeClass(the.element, 'file-input-empty');

            // Fire removed event
            KTEventHandler.trigger(the.element, 'kt.fileinput.changed', the);
        }

        $(the.inputElement).parents('form').attr('data-gc-has-changes', 'true');
    }

    var _cancel = function (e) {
        e.preventDefault();

        // Fire cancel event
        if (KTEventHandler.trigger(the.element, 'kt.fileinput.cancel', the) === false) {
            return;
        }

        KTUtil.removeClass(the.element, 'file-input-changed');
        KTUtil.removeClass(the.element, 'file-input-empty');
        KTUtil.css(the.wrapperElement, 'background-file', the.src);
        the.inputElement.value = "";

        if (the.hiddenElement !== null) {
            the.hiddenElement.value = "0";
        }

        // Fire canceled event
        KTEventHandler.trigger(the.element, 'kt.fileinput.canceled', the);
        $(the.inputElement).parents('form').attr('data-gc-has-changes', 'true');
    }

    var _remove = function (e) {
        e.preventDefault();

        // Fire remove event
        if (KTEventHandler.trigger(the.element, 'kt.fileinput.remove', the) === false) {
            return;
        }

        KTUtil.removeClass(the.element, 'file-input-changed');
        KTUtil.addClass(the.element, 'file-input-empty');
        the.wrapperElement.innerText = 'Seleccione...';
        the.inputElement.value = "";

        if (the.hiddenElement !== null) {
            the.hiddenElement.value = "1";
        }

        // Fire removed event
        KTEventHandler.trigger(the.element, 'kt.fileinput.removed', the);
        $(the.inputElement).parents('form').attr('data-gc-has-changes', 'true');
    }

    // Construct Class
    _construct();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////

    // Plugin API
    the.getInputElement = function () {
        return the.inputElement;
    }

    the.goElement = function () {
        return the.element;
    }

    // Event API
    the.on = function (name, handler) {
        return KTEventHandler.on(the.element, name, handler);
    }

    the.one = function (name, handler) {
        return KTEventHandler.one(the.element, name, handler);
    }

    the.off = function (name) {
        return KTEventHandler.off(the.element, name);
    }

    the.trigger = function (name, event) {
        return KTEventHandler.trigger(the.element, name, event, the, event);
    }
};

// Static methods
KTFileInput.getInstance = function (element) {
    if (element !== null && KTUtil.data(element).has('file-input')) {
        return KTUtil.data(element).get('file-input');
    } else {
        return null;
    }
}

// Create instances
KTFileInput.createInstances = function (selector) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new KTFileInput(elements[i], {
                clear_on_init: elements[i].getAttribute('data-kt-file-input-preloaded') !== 'true'
            });
        }
    }
}

// Global initialization
KTFileInput.init = function () {
    KTFileInput.createInstances('[data-kt-file-input]');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', KTFileInput.init);
} else {
    KTFileInput.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = KTFileInput;
}
