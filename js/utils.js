/**
 * Alexandria Library Management System
 *
 * @file Shared JS utilities — showMessage, debounce, $, and $$ for use across pages
 */
(function () {
    window.showMessage = function (text, type) {
        if (type === void 0) { type = 'successo'; }
        var div = document.getElementById("messages");
        if (!div) return;
        var p = document.createElement("p");
        p.textContent = text;
        p.classList.add(type);
        div.appendChild(p);
        setTimeout(function () { p.remove(); }, 4000);
    };

    window.debounce = function (fn, delay) {
        var timer = null;
        return function () {
            var args = arguments;
            var context = this;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(context, args); }, delay);
        };
    };

    window.$ = function (selector, parent) {
        if (parent === void 0) { parent = document; }
        return parent.querySelector(selector);
    };

    window.$$ = function (selector, parent) {
        if (parent === void 0) { parent = document; }
        return parent.querySelectorAll(selector);
    };
})();
