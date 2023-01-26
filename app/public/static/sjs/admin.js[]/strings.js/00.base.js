"use strict";

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