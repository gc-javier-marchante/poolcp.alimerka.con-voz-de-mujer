"use strict";

// Class definition
var GCCalculatedAttribute = function (element, options) {
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

    var _construct = function (attribute) {
        if (KTUtil.data(element).has('calculated-attribute-' + attribute) === true) {
            the = KTUtil.data(element).get('calculated-attribute-' + attribute);
        } else {
            _init();
        }
    };

    var _sanitizeOperation = function (operation) {
        operation = operation.split('}}').join(' }}');
        operation = operation.split('{{').join('{{ ');
        operation = operation.trim();

        while (operation.split('  }}').length > 1) {
            operation = operation.split('  }}').join(' }}');
        }

        while (operation.split('{{  ').length > 1) {
            operation = operation.split('{{  ').join('{{ ');
        }

        return operation;
    };

    var _extractTargets = function () {
        let targetParts = the.operation.split('{{'),
            targets = {},
            form = the.element.closest('form');

        for (let i = 1; i < targetParts.length; i++) {
            let target_name = targetParts[i].split('}}')[0].trim();
            targets[target_name] = KTUtil.find(form, '[name$="[' + target_name + ']"]');
        }

        return targets;
    };

    var _calculate = function () {
        let calculated_operation = the.operation + '';

        for (let target_name in the.targets) {
            let value = the.targets[target_name].value;

            if (the.targets[target_name].type === 'radio' || the.targets[target_name].type === 'checkbox') {
                if (!the.targets[target_name].checked) {
                    value = false;
                }
            }

            if (the.options.format === 'number') {
                if (value === null || value === undefined || value === '' || isNaN(value)) {
                    // Stop excecution
                    return;
                }
            }

            calculated_operation = calculated_operation.split('{{ ' + target_name + ' }}').join(value);
        }

        // Execute on new thread
        setTimeout(function () {
            let calculated_attribute = eval(calculated_operation);

            if (the.options.format === 'number') {
                calculated_attribute = parseFloat(calculated_attribute);

                if (isNaN(calculated_attribute)) {
                    calculated_attribute = '';
                } else {
                    calculated_attribute = calculated_attribute.toFixed(2);
                }
            } else if (the.options.format === 'bool') {
                calculated_attribute = !!calculated_attribute;
            }

            if (the.attribute == 'value') {
                the.element.value = calculated_attribute;
                KTUtil.triggerCustomEvent(the.element, 'change');
            } else {
                the.element.setAttribute(the.attribute, calculated_attribute);
            }
        }, 1);
    };

    var _init = function () {
        // Variables
        the.options = KTUtil.deepExtend({}, defaultOptions, options);
        the.attribute = the.options.attribute;
        the.uid = KTUtil.getUniqueId('calculated-attribute-' + the.attribute);

        // Elements
        the.element = element;
        the.operation = _sanitizeOperation(the.options.operation);
        the.targets = _extractTargets();

        // Event Handlers
        _handlers();

        // Bind Instance
        KTUtil.data(the.element).set('calculated-attribute-' + the.attribute, the);
    };

    // Init Event Handlers
    var _handlers = function () {
        for (let target_name in the.targets) {
            $(the.targets[target_name]).on('change', _change);
        }
    };

    // Event Handlers
    var _change = function (e) {
        _calculate();
    };

    // Construct Class
    _construct(options.attribute);
    _calculate();

    ///////////////////////
    // ** Public API  ** //
    ///////////////////////
};

// Static methods
GCCalculatedAttribute.getInstance = function (element, attribute) {
    if (element !== null && KTUtil.data(element).has('calculated-attribute-' + attribute)) {
        return KTUtil.data(element).get('calculated-attribute-' + attribute);
    } else {
        return null;
    }
};

// Create instances
GCCalculatedAttribute.createInstances = function (selector, operation_attribute, target_attribute, format) {
    // Initialize Menus
    var elements = document.querySelectorAll(selector);

    if (elements && elements.length > 0) {
        for (var i = 0, len = elements.length; i < len; i++) {
            new GCCalculatedAttribute(elements[i], {
                attribute: target_attribute,
                format: (elements[i].getAttribute(operation_attribute + '_format') ? elements[i].getAttribute(operation_attribute + '_format') : format),
                operation: elements[i].getAttribute(operation_attribute)
            });
        }
    }
};

// Global initialization
GCCalculatedAttribute.init = function () {
    GCCalculatedAttribute.createInstances('[data-calculated-value]', 'data-calculated-value', 'value', 'number');
    GCCalculatedAttribute.createInstances('[data-calculated-visibility]', 'data-calculated-visibility', 'data-visibility', 'bool');
};

// On document ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', GCCalculatedAttribute.init);
} else {
    GCCalculatedAttribute.init();
}

// Webpack Support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = GCCalculatedAttribute;
}
