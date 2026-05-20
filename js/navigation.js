/**
 * Alexandria Library Management System
 *
 * @file Navigation utilities - mobile menu toggle and notification badges
 */

(function () {
    const subMenu = document.getElementById('subMenu');
    const dropdownIcons = document.querySelectorAll('.icon[onclick="toggleMenu()"] use');

    // Gestione apertura/chiusura menu e cambio icona freccia
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

    // INTEGRAZIONE NOTIFICHE
    document.addEventListener('DOMContentLoaded', () => {
        // Carica il numero di notifiche non lette all'avvio della pagina
        aggiornaBadgeNotifiche();

        setInterval(aggiornaBadgeNotifiche, 5000);
    });

    /**
     * Recupera il numero di notifiche non lette dall'API
     * e aggiorna entrambi i badge nella Navbar.
     */
    function aggiornaBadgeNotifiche() {
    const baseUrl = typeof ROOT_URL !== 'undefined' ? ROOT_URL : '';

    fetch(`${baseUrl}/api/get-notifiche.php`)
        .then(response => {
            if (!response.ok) {
                console.error("Errore HTTP:", response.status);
                return null; // Ferma l'esecuzione se l'API va in errore
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;

            // Log per farti vedere cosa sta arrivando realmente dall'API!
            console.log("Dati API Notifiche:", data);

            // Prende il valore in modo super sicuro
            const unreadCount = parseInt(data.unread_count ?? 0, 10);

            const badgeNavbar = document.getElementById('notifiche-badgeN');
            const badgeDropdown = document.getElementById('notifiche-badgeN-dropdown');

            if (unreadCount > 0) {
                if (badgeNavbar) {
                    badgeNavbar.textContent = unreadCount;
                    badgeNavbar.style.display = 'inline-flex';
                }
                if (badgeDropdown) {
                    badgeDropdown.textContent = unreadCount;
                    badgeDropdown.style.display = 'inline-flex';
                }
            } else {
                if (badgeNavbar) badgeNavbar.style.display = 'none';
                if (badgeDropdown) badgeDropdown.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Errore nel recupero del contatore notifiche:', error);
        });
    }
})();