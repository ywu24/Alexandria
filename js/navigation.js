/**
 * Alexandria Library Management System
 *
 * @file Navigation utilities - mobile menu toggle
 */

(function () {
    const subMenu = document.getElementById('subMenu');

    window.toggleMenu = function () {
        if (subMenu) {
            subMenu.classList.toggle('open-menu');
        }
    };
})();
