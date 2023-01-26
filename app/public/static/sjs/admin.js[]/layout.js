"use strict";

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