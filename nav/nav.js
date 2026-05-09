let subMenu = document.getElementById("subMenu");

function toggleMenu() {
    subMenu.classList.toggle("open-menu");
}

// SCRIPT PER IL BADGE NOTIFICHE
document.addEventListener("DOMContentLoaded", function() {
    const badges = document.querySelectorAll('.badge');
    
    // Se il badge esiste, l'utente è loggato: carichiamo solo il conteggio
    if (badges.length>0) { 
        function aggiornaBadge() {
            fetch(`${ROOT_URL}/notifiche/get-notifiche.php`)
                .then(response => response.json())
                .then(data => {
                    badges.forEach(badge => {
                        if(data.non_lette > 0) {
                            badge.innerText = data.non_lette;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                        });
                })
                .catch(err => { console.log("Errore badge:", err);  badges.forEach(badge => badge.style.display = 'none'); });
        }

        aggiornaBadge();

        setInterval(aggiornaBadge, 20000);
    }
});