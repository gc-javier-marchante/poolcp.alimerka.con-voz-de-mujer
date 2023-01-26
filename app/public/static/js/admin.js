"use strict";
(function() { 
KTUtil.handlePost = function (url, data, options) {
    let loading = function (is_loading) {
        if (is_loading) {
            if (options.loaders) {
                $(options.loaders).each(function () {
                    this.setAttribute('data-kt-indicator', 'on');
                    this.disabled = true;
                });
            }
        } else {
            if (options.loaders) {
                $(options.loaders).each(function () {
                    this.removeAttribute('data-kt-indicator');
                    this.disabled = false;
                });
            }
        }
    };
    loading(true);
    let success = function (result) {
        loading(false);
        if (result.response.succeeded) {
            let complete = function () {
                if ($(options.target).is('[data-gc-has-changes]')) {
                    $(options.target).removeAttr('data-gc-has-changes');
                }
                if (result.response.redirect_to) {
                    loading(true);
                    location.href = result.response.redirect_to;
                }
                if (result.response.reload) {
                    loading(true);
                    location.reload();
                }
                if (options.target) {
                    $(options.target).trigger('submitted');
                    let callback = $(options.target).attr('data-gc-on-ajax');
                    if (!callback) {
                        callback = result.response.callback;
                    }
                    if (callback
                        && window.gcAjaxCallbacks
                        && window.gcAjaxCallbacks[callback]) {
                        window.gcAjaxCallbacks[callback](options.target, result);
                    }
                    if ($(options.target).is('[data-gc-on-ajax-click]')) {
                        $($(options.target).attr('data-gc-on-ajax-click')).each(function () {
                            this.click();
                            return false;
                        });
                    }
                    if ($(options.target).is('[data-gc-on-ajax-clear]')) {
                        $(form).find('input, textarea, select').each(function () {
                            if ($(this).is('[type="checkbox"],[type="radio"],[type="button"],[type="submit"]')) {
                                this.checked = false;
                            } else {
                                $(this).val('');
                            }
                        });
                    }
                    if ($(options.target).is('[data-gc-on-ajax-reload]')) {
                        loading(true);
                        location.reload();
                    }
                    if ($(options.target).is('[data-gc-on-ajax-redirect]')) {
                        loading(true);
                        location.href = $(options.target).attr('data-gc-on-ajax-redirect');
                    }
                    if ($(options.target).is('[data-dismiss-modal]')) {
                        $(options.target).parents('.modal').modal('hide');
                    }
                    if ($(options.target).data('onSubmit')) {
                        $(options.target).data('onSubmit')(result);
                    }
                }
            };
            if (result.response.message) {
                Swal.fire({
                    text: result.response.message,
                    icon: 'success',
                    buttonsStyling: false,
                    confirmButtonText: __('Ok'),
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                }).then(function () {
                    complete();
                });
            } else {
                complete();
            }
        } else {
            Swal.fire({
                text: (result.error ? result.error : __('Error')),
                icon: 'error',
                buttonsStyling: false,
                confirmButtonText: __('Ok'),
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
            if (options.errorsByField) {
                options.errorsByField(result.errorsByField);
            }
            if (options.validator) {
                options.validator.validate();
            }
        }
    };
    if (url === 'true' || url === true) {
        success({ response: { succeeded: true } });
        return;
    } else if (url === 'false' || url === false) {
        success({ response: { succeeded: false } });
        return;
    }
    let ajaxOptions = {
        url: url,
        data: data,
        dataType: 'json',
        method: 'POST'
    };
    if (data instanceof FormData) {
        ajaxOptions.processData = false;
        ajaxOptions.contentType = false;
    }
    ajaxOptions.success = success;
    ajaxOptions.error = function () {
        loading(false);
        Swal.fire({
            text: __('Sorry, your request could not be fulfilled at this time, please try again.'),
            icon: 'error',
            buttonsStyling: false,
            confirmButtonText: __('Ok'),
            customClass: {
                confirmButton: 'btn btn-primary'
            }
        });
    };
    $.ajax(ajaxOptions);
};
let AjaxForm = function () {
    let handleForm = function (form) {
        if ($(form).attr('data-ajax-form') === 'handled') return;
        $(form).attr('data-ajax-form', 'handled');
        $(form).attr('novalidate', 'novalidate');
        let onSubmit = null;
        if ($(form).parents('[data-drag-and-drop-upload]').length !== 0) {
            let $droppable = $(form).parents('[data-drag-and-drop-upload]'),
                $inputFile = $(form).find('input[type="file"]'),
                $inputMoveCategory = $(form).find('[name="move_category_id"]'),
                $inputMoveFile = $(form).find('[name="move_id"]'),
                classes = $droppable.attr('data-drag-and-drop-upload').split('/');
            $inputFile.on('change', function () {
                onSubmit();
            });
            $droppable.get(0).addEventListener('drop', function (e) {
                e.preventDefault();
                $droppable.removeClass(classes[1]).addClass(classes[0]);
                let dragged_id = e.dataTransfer.getData('text');
                if (!dragged_id) {
                    let dT = new DataTransfer();
                    if (e.dataTransfer.items) {
                        for (let i = 0; i < e.dataTransfer.items.length; i++) {
                            if (e.dataTransfer.items[i].kind === 'file') {
                                dT.items.add(e.dataTransfer.items[i].getAsFile());
                            }
                        }
                    } else {
                        for (let i = 0; i < e.dataTransfer.files.length; i++) {
                            dT.items.add(e.dataTransfer.files[i]);
                        }
                    }
                    $inputMoveCategory.val('');
                    $inputMoveFile.val('');
                    $inputFile.get(0).files = dT.files;
                } else {
                    let $element = $('#' + dragged_id);
                    if ($element.length == 0) {
                        return;
                    }
                    $inputFile.val('');
                    $inputMoveCategory.val($element.attr('data-move-category-id'));
                    $inputMoveFile.val($element.attr('data-move-id'));
                }
                onSubmit();
            });
            $droppable.get(0).addEventListener('dragover', function (e) {
                e.preventDefault();
            });
            $droppable.get(0).addEventListener('dragenter', function (e) {
                e.preventDefault();
                $droppable.removeClass(classes[0]).addClass(classes[1]);
            });
            $droppable.get(0).addEventListener('dragleave', function () {
                $droppable.removeClass(classes[1]).addClass(classes[0]);
            });
        }
        let submitButton = $(form).find('[type="submit"]').get(0),
            validator,
            $inputs,
            errorsByField = null;
        FormValidation.validators.gestymvc = function () {
            return {
                validate: function (input) {
                    let name = input.field,
                        result = {
                            valid: true,
                            message: null
                        };
                    if (errorsByField
                        && Object.keys(errorsByField).length > 0) {
                        for (let model_ in errorsByField) {
                            for (let field_ in errorsByField[model_]) {
                                let field_name_ = field_;
                                if (field_name_.endsWith('_digest')) {
                                    field_name_ = field_name_.substring(0, field_name_.length - '_digest'.length);
                                }
                                if (name.endsWith('[' + model_ + '][' + field_name_ + ']')) {
                                    result.valid = false;
                                    result.message = errorsByField[model_][field_];
                                    delete errorsByField[model_][field_];
                                }
                            }
                        }
                    }
                    return result;
                },
            };
        };
        let validatorFields = {};
        $inputs = $(form).find('.fv-row').find('input, textarea, select');
        $inputs.each(function () {
            let name = $(this).attr('name'),
                required = $(this).is('[required]'),
                email = $(this).is('[type="email"]'),
                number = $(this).is('[type="number"]'),
                ignored = $(this).is('[type="hidden"], [type="file"]'),
                maxlength = $(this).is('[maxlength]'),
                validators = {};
            if (!name || ignored) return;
            if (required) validators.notEmpty = { message: __('Required') };
            if (email) validators.emailAddress = { message: __('Invalid email address') };
            if (number) validators.numeric = { message: __('Invalid number') };
            if (maxlength) validators.stringLength = { message: __('Too long'), max: $(this).attr('maxlength') };
            validators.gestymvc = {};
            validatorFields[name] = { validators: validators };
        });
        validator = FormValidation.formValidation(
            form,
            {
                fields: validatorFields,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );
        $(form).find('.fv-row').find('[data-control="select2"], [data-kt-select2="true"]').on('change', function () {
            validator.revalidateField($(this).attr('name'));
        });
        onSubmit = function () {
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    // Fix datatable submittion
                    $(form).find('[data-datatable]').each(function () {
                        KTUtil.data(this).get('datatables').datatable.$('input, select, textarea').each(function () {
                            if (!$.contains(document, this)) {
                                let $copy = $(this).clone();
                                $copy.removeAttr('id').removeAttr('class').removeAttr('style');
                                $copy.attr('data-dummy-post-input', 'true')
                                $copy.addClass('d-none');
                                $(form).append($copy);
                            }
                        });
                    });
                    // Submit form
                    KTUtil.handlePost($(form).attr('action'), new FormData(form), {
                        loaders: submitButton,
                        target: form,
                        validator: validator,
                        errorsByField: function (errorsByField_) {
                            errorsByField = errorsByField_;
                        }
                    });
                    // Fix cleanup datatable submittion
                    $(form).find('[data-dummy-post-input]').remove();
                } else {
                    Swal.fire({
                        text: __('Sorry, looks like there are some errors detected, please try again.'),
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: __('Ok'),
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        };
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            onSubmit();
        });
    };
    return {
        init: function () {
            $('[data-ajax-form]').each(function () {
                handleForm(this);
            });
        }
    };
}();
KTUtil.onDOMContentLoaded(function () {
    AjaxForm.init();
});
KTUtil.onDomAdd.push(function () {
    AjaxForm.init();
});
})();
(function() { 
let gcAjaxCallbacks = {};
window.gcAjaxCallbacks = gcAjaxCallbacks;
gcAjaxCallbacks.reload = function () {
    location.reload();
};
gcAjaxCallbacks.userEmailChanged = function (target) {
    $('[data-user-email]').text($(target).find('[name="user[User][email]"]').val());
};
gcAjaxCallbacks.userAvatarChanged = function (target, result) {
    $('[data-user-avatar]').each(function () {
        if ($(this).is('img')) {
            this.src = result.response.avatar_url;
        }
    });
    $('[data-user-first-name]').text($(target).find('[name="user[User][first_name]"]').val());
};
gcAjaxCallbacks.userTwoFactorEnabled = function () {
    $('[data-two-factor-container]').addClass('d-status');
};
gcAjaxCallbacks.userTwoFactorDisabled = function () {
    $('[data-two-factor-container]').removeClass('d-status');
};
gcAjaxCallbacks.remove = function (target) {
    let $remove = $(target);
    $remove.parents('form').attr('data-gc-has-changes', 'true');
    if (!$remove.is('[data-remove]')) {
        $remove = $remove.parents('[data-remove]');
    }
    $remove.fadeOut(function () {
        if ($remove.is('tr')) {
            let $datatable = $remove.parents('[data-datatable]').eq(0);
            if ($datatable.length == 1) {
                let datatable = KTUtil.data($datatable.get(0)).get('datatables').datatable;
                datatable.row($remove.get(0)).remove().draw(false);
                return;
            }
        }
        $remove.remove();
    });
};
gcAjaxCallbacks.moveUp = function (target) {
    let $remove = $(target);
    if (!$remove.is('[data-remove]')) {
        $remove = $remove.parents('[data-remove]');
    }
    //$remove.parents('form').attr('data-gc-has-changes', 'true'); // Move is autosaved, no need for this
    const row_index = $remove.index();
    const $datatable = $remove.parents('[data-datatable]').eq(0);
    if ($datatable.length == 1) {
        const datatable = KTUtil.data($datatable.get(0)).get('datatables').datatable;
        const datatable_row_index = datatable.row($remove.get(0)).index();
        if (datatable_row_index == 0) {
            return;
        }
        const data1 = datatable.row(datatable_row_index).data();
        const data2 = datatable.row(datatable_row_index - 1).data();
        data1.order += -1;
        data2.order += 1;
        datatable.row(datatable_row_index).data(data2);
        datatable.row(datatable_row_index - 1).data(data1);
        if (row_index == 0) {
            datatable.page(datatable.page() - 1);
        }
        datatable.row($remove.get(0)).draw(false);
        KTUtil.domAdded();
    } else {
        if (row_index == 0) {
            if (window.URLSearchParams) {
                const urlParams = new URLSearchParams(window.location.search);
                const p = urlParams.get('p');
                if (p && p > 1) {
                    urlParams.set(p, p - 1);
                    location.href = '?' + urlParams.toString();
                    return;
                }
            }
            location.reload();
        } else {
            $remove.parent().children().eq($remove.index() - 1).before($remove);
        }
    }
};
gcAjaxCallbacks.addToList = function (target, result) {
    GCSelectToList.addIdToList(target, result.response.addToList.id, $('[data-select-key="' + result.response.addToList.list + '"]'), result.response.addToList.close, result.response.addToList.replace);
};
})();
(function() { 
// Class definition
var KTApp = function () {
    var initPageLoader = function () {
        // CSS3 Transitions only after page load(.page-loading class added to body tag and remove with JS on page load)
        KTUtil.removeClass(document.body, 'page-loading');
    }
    var initBootstrapTooltips = function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var options = {};
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            if (tooltipTriggerEl.hasAttribute('data-bs-delay-hide')) {
                options['delay'] = { hide: tooltipTriggerEl.getAttribute('data-bs-delay-hide') };
            }
            return new bootstrap.Tooltip(tooltipTriggerEl, options);
        });
    }
    var initBootstrapPopovers = function () {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var options = {};
        popoverTriggerList.map(function (popoverTriggerEl) {
            if (popoverTriggerEl.hasAttribute('data-bs-delay-hide')) {
                options['delay'] = { hide: popoverTriggerEl.getAttribute('data-bs-delay-hide') };
            }
            return new bootstrap.Popover(popoverTriggerEl, options);
        });
    }
    var initScrollSpy = function () {
        var elements = [].slice.call(document.querySelectorAll('[data-bs-spy="scroll"]'));
        elements.map(function (element) {
            var offset = element.hasAttribute('data-bs-target-offset') ? parseInt(element.getAttribute('data-bs-target-offset')) : 0;
            KTUtil.on(document.body, element.getAttribute('data-bs-target') + ' [href]', 'click', function (e) {
                e.preventDefault();
                var el = document.querySelector(this.getAttribute('href'));
                KTUtil.scrollTo(el, offset);
            });
            var scrollContent = document.querySelector(element.getAttribute('data-bs-target'));
            var scrollSpy = bootstrap.ScrollSpy.getInstance(scrollContent);
            if (scrollSpy) {
                scrollSpy.refresh();
            }
        });
    }
    var initButtons = function () {
        var buttonsGroup = [].slice.call(document.querySelectorAll('[data-kt-buttons="true"]'));
        buttonsGroup.map(function (group) {
            var selector = group.hasAttribute('data-kt-buttons-target') ? group.getAttribute('data-kt-buttons-target') : '.btn';
            // Toggle Handler
            KTUtil.on(group, selector, 'click', function (e) {
                var buttons = [].slice.call(group.querySelectorAll(selector + '.active'));
                buttons.map(function (button) {
                    button.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    }
    var initCheck = function () {
        // Toggle Handler
        KTUtil.on(document.body, '[data-kt-check="true"]', 'change', function (e) {
            var check = this;
            var targets = document.querySelectorAll(check.getAttribute('data-kt-check-target'));
            KTUtil.each(targets, function (target) {
                if (target.type == 'checkbox') {
                    target.checked = check.checked;
                } else {
                    target.classList.toggle('active');
                }
            });
        });
    }
    var initSelect2 = function () {
        var elements = [].slice.call(document.querySelectorAll('[data-control="select2"]:not([data-select2-id]), [data-kt-select2="true"]:not([data-select2-id])'));
        elements.map(function (element) {
            var options = {
                //dir: document.body.getAttribute('direction'),
                language: {
                    noResults: function () {
                        return __('No Results Found');
                    }
                }
            };
            if (element.getAttribute('data-hide-search') == 'true') {
                options.minimumResultsForSearch = Infinity;
            }
            if (element.getAttribute('placeholder')) {
                options.placeholder = element.getAttribute('placeholder');
            }
            $(element).select2(options);
        });
    }
    var initAutosize = function () {
        var inputs = [].slice.call(document.querySelectorAll('[data-kt-autosize="true"]'));
        inputs.map(function (input) {
            autosize(input);
        });
    }
    var initDropDowns = function () {
        $(document).on('show.bs.dropdown', function (e) {
            $(e.target).parents('.table-responsive').find('[aria-labelledby="' + e.target.id + '"]').css({ opacity: 0 });
        });
        $(document).on('shown.bs.dropdown', function (e) {
            setTimeout(function () {
                $(e.target).parents('.table-responsive').find('[aria-labelledby="' + e.target.id + '"]').css({ opacity: 1, marginTop: - $(document).scrollTop() });
            }, 10);
        });
        $(document).on('scroll', function () {
            $('.table-responsive .dropdown-menu.show').each(function () {
                $('#' + $(this).attr('aria-labelledby')).dropdown('hide');
            });
        });
    }
    var initCountUp = function () {
        var elements = [].slice.call(document.querySelectorAll('[data-kt-countup="true"]:not(.counted)'));
        elements.map(function (element) {
            if (KTUtil.isInViewport(element) && KTUtil.visible(element)) {
                var options = {};
                var value = element.getAttribute('data-kt-countup-value');
                value = parseFloat(value.replace(/,/, ''));
                if (element.hasAttribute('data-kt-countup-start-val')) {
                    options.startVal = parseFloat(element.getAttribute('data-kt-countup-start-val'));
                }
                if (element.hasAttribute('data-kt-countup-duration')) {
                    options.duration = parseInt(element.getAttribute('data-kt-countup-duration'));
                }
                if (element.hasAttribute('data-kt-countup-decimal-places')) {
                    options.decimalPlaces = parseInt(element.getAttribute('data-kt-countup-decimal-places'));
                }
                if (element.hasAttribute('data-kt-countup-prefix')) {
                    options.prefix = element.getAttribute('data-kt-countup-prefix');
                }
                if (element.hasAttribute('data-kt-countup-suffix')) {
                    options.suffix = element.getAttribute('data-kt-countup-suffix');
                }
                var count = new countUp.CountUp(element, value, options);
                count.start();
                element.classList.add('counted');
            }
        });
    }
    var initCountUpTabs = function () {
        // Initial call
        initCountUp();
        // Window scroll event handler
        window.addEventListener('scroll', initCountUp);
        // Tabs shown event handler
        var tabs = [].slice.call(document.querySelectorAll('[data-kt-countup-tabs="true"][data-bs-toggle="tab"]'));
        tabs.map(function (tab) {
            tab.addEventListener('shown.bs.tab', initCountUp);
        });
    }
    return {
        init: function () {
            this.initPageLoader();
            this.initBootstrapTooltips();
            this.initBootstrapPopovers();
            this.initScrollSpy();
            this.initButtons();
            this.initCheck();
            this.initSelect2();
            this.initCountUp();
            this.initCountUpTabs();
            this.initAutosize();
            this.initDropDowns();
        },
        initPageLoader: function () {
            initPageLoader();
        },
        initBootstrapTooltips: function () {
            initBootstrapTooltips();
        },
        initBootstrapPopovers: function () {
            initBootstrapPopovers();
        },
        initScrollSpy: function () {
            initScrollSpy();
        },
        initButtons: function () {
            initButtons();
        },
        initCheck: function () {
            initCheck();
        },
        initSelect2: function () {
            initSelect2();
        },
        initCountUp: function () {
            initCountUp();
        },
        initCountUpTabs: function () {
            initCountUpTabs();
        },
        initAutosize: function () {
            initAutosize();
        },
        initDropDowns: function () {
            initDropDowns();
        }
    };
}();
// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTApp.init();
});
KTUtil.onDomAdd.push(GCModalInnerLink.init);
KTUtil.onDomAdd.push(GCModalInnerForm.init);
KTUtil.onDomAdd.push(GCSelectToList.init);
KTUtil.onDomAdd.push(GCAjaxBtn.init);
KTUtil.onDomAdd.push(KTImageInput.init);
KTUtil.onDomAdd.push(KTFileInput.init);
KTUtil.onDomAdd.push(KTApp.initSelect2);
KTUtil.onDomAdd.push(GCCalculatedAttribute.init);
// On window load
window.addEventListener("load", function () {
    KTApp.initPageLoader();
});
})();
(function() { 
(function() { 
let strings = { 'es': {} };
let __ = function (str, lang) {
    if (!window.LANG) {
        window.LANG = document.documentElement.lang;
        if (!window.LANG) {
            window.LANG = 'es';
        }
    }
    if (lang === undefined) {
        lang = window.LANG;
    }
    if (strings[lang] !== undefined && strings[lang][str] !== undefined) {
        return strings[lang][str];
    }
    return str;
};
window.__ = __;
strings.es = (strings.es ? strings.es : {});
strings.es['Cancel'] = 'Cancelar';
strings.es['Continue'] = 'Continuar';
strings.es = (strings.es ? strings.es : {});
strings.es['No data available in table'] = 'No hay datos para mostrar';
strings.es['Element is already on the list.'] = 'El elemento ya está en la lista.';
strings.es = (strings.es ? strings.es : {});
strings.es['No Results Found'] = 'Sin resultados';
strings.es = (strings.es ? strings.es : {});
strings.es['Email address is required'] = 'Debes indicar la dirección de e-mail';
strings.es['The value is not a valid email address'] = 'El e-mail no es válido';
strings.es['The password is required'] = 'Debes indicar la contraseña';
strings.es['Please enter valid password'] = 'Por favor indica una contraseña válida';
strings.es['You have successfully logged in!'] = '¡Has iniciado sesión con éxito!';
strings.es['Ok, got it!'] = 'Vale!';
strings.es['Sorry, looks like there are some errors detected, please try again.'] = 'Parece que hay algún error, inténtalo otra vez.';
strings.es['Required'] = 'Obligatorio';
strings.es['Invalid email address'] = 'Dirección de e-mail inválida';
strings.es['Invalid number'] = 'Número inválido';
strings.es['Ok'] = 'Ok';
strings.es['Error'] = 'Error';
strings.es['Sorry, your request could not be fulfilled at this time, please try again.'] = 'Lo sentimos, no hemos podido procesar tu solicitud en este momento. Por favor, vuelve a intentarlo.';
strings.es['value'] = 'valor';})();
})();