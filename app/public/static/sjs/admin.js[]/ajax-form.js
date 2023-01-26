"use strict";

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