let cookie = {
    ignore_url_pattern: ['politica-de-cookies', 'aviso-legal', 'politica-de-privacidad'],
    init: function () {
        let show_alert = false;

        if (!cookie.isMessageHiddenByUser()) {
            show_alert = true;

            for (let i = 0; i < cookie.ignore_url_pattern.length; i++) {
                if ((location.href + '').split(cookie.ignore_url_pattern[i]).length > 1) {
                    show_alert = false;
                }
            }

            if (show_alert) {
                if (typeof (document.referrer) != 'undefined' && document.referrer.split(location.hostname).length > 1) {
                    let comes_from_legal_notice = false;

                    for (let i = 0; i < cookie.ignore_url_pattern.length; i++) {
                        if (document.referrer.split(cookie.ignore_url_pattern[i]).length > 1) {
                            comes_from_legal_notice = true;
                        }
                    }

                    if (!comes_from_legal_notice) {
                        cookie.saveCookiesEnabled();
                    }
                }
            }
        }

        if (cookie.areCookiesEnabled()) {
            let cookieReplaces = document.querySelectorAll('[data-cookie-replace]');

            for (let i = 0; i < cookieReplaces.length; i++) {
                cookieReplaces[i].outerHTML = cookieReplaces[i].getAttribute('data-cookie-replace');
            }

            let cookieHints = document.querySelectorAll('[data-cookie-hint]');

            for (let i = 0; i < cookieHints.length; i++) {
                cookieHints[i].removeAttribute('data-cookie-hint');
            }

            let cookieScripts = document.querySelectorAll('script[data-cookie-run]');

            for (let i = 0; i < cookieScripts.length; i++) {
                eval(cookieScripts[i].getAttribute('data-cookie-run'));
            }
        }

        if (!show_alert) {
            cookie.hideMessage();
        } else {
            let cookieAccepts = document.querySelectorAll('[data-cookie-accept]');

            for (let i = 0; i < cookieAccepts.length; i++) {
                cookieAccepts[i].addEventListener('click', function () {
                    cookie.userAcceptanceClick();
                });
            }
        }
    },
    areCookiesEnabled: function () {
        try {
            return localStorage.getItem('enable-cookies') === 'true';
        } catch (e) {
            return false;
        }
    },
    isMessageHiddenByUser: function () {
        try {
            return localStorage.getItem('message-hidden-cookies') === 'true';
        } catch (e) {
            return false;
        }
    },
    saveMessageHidden: function() {
        try {
            localStorage.setItem('message-hidden-cookies', 'true');
        } catch (e) {
            return;
        }
    },
    hideMessage: function () {
        let cookieAlerts = document.querySelectorAll('[data-cookie-banner]');

        for (let i = 0; i < cookieAlerts.length; i++) {
            cookieAlerts[i].parentNode.removeChild(cookieAlerts[i]);
        }
    },
    saveCookiesEnabled: function () {
        try {
            localStorage.setItem('enable-cookies', 'true');
        } catch (e) {
            return;
        }
    },
    saveCookiesDisabled: function () {
        try {
            localStorage.setItem('enable-cookies', 'false');
        } catch (e) {
            return;
        }
    },
    userAcceptanceClick: function () {
        cookie.saveMessageHidden();
        cookie.hideMessage();

        if (!cookie.areCookiesEnabled()) {
            cookie.saveCookiesEnabled();
            location.reload();
        }
    }
};

cookie.init();

window.resetCookieConfiguration = function () {
    this.localStorage.clear();
    location.reload();
};
