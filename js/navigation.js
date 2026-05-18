/**
 * Alexandria Library Management System
 *
 * @file Navigation utilities - mobile menu toggle
 */

(function () {
    const subMenu = document.getElementById('subMenu');
    const dropdownIcons = document.querySelectorAll('.icon[onclick="toggleMenu()"] use');

    window.toggleMenu = function () {
        if (subMenu) {
            subMenu.classList.toggle('open-menu');
            dropdownIcons.forEach(function(icon) {
                const currentHref = icon.getAttribute('href');
                const baseUrl = currentHref.split('#')[0];
                if (subMenu.classList.contains('open-menu')) {
                    icon.setAttribute('href', baseUrl + '#dropdown-menu-up');
                } else {
                    icon.setAttribute('href', baseUrl + '#dropdown-menu');
                }
            });
        }
    };
})();
