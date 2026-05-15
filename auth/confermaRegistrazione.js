document.addEventListener("DOMContentLoaded", function () {
    const storageKey = "reinvio_count";
    // Selezioniamo il div con classe 'reinvio' che hai aggiunto nel PHP
    const reinvioContainer = document.querySelector('.reinvio');

    // Se il div non esiste, interrompiamo lo script per evitare errori
    if (!reinvioContainer) return;

    // Appare la versione scherzosa dopo 2 secondi
    setTimeout(function () {
        // Popoliamo il div con il titolo rosso e il messaggio iniziale
        reinvioContainer.innerHTML = `
            <div style="margin-top: 25px; width: 100%;">
                <h3 style="color: #e74c3c; font-weight: bold; font-size: 15px; margin-bottom: 5px;">
                    PROBLEMI CON LA MAIL?
                </h3>
                <div id="dynamic-content">
                    <h4 style="color: rgb(70, 70, 70); font-style: italic; font-weight: bold;">
                        Attaccate ar cazzo (aspetta qualche secondo).
                    </h4>
                </div>
            </div>
        `;
    }, 2000);

    // Dopo 12 secondi, cambiamo solo il contenuto di 'dynamic-content'
    setTimeout(function () {
        let inviiEffettuati = localStorage.getItem(storageKey) || 0;
        const dynamicContent = document.getElementById('dynamic-content');
        // Se l'utente ha già fatto 2 o più tentativi, non mostriamo nulla
        if (inviiEffettuati > 2) {
            dynamicContent.innerHTML = `
                    <h4 style="margin-bottom: 10px; color: rgb(70, 70, 70); font-style: normal; font-weight: normal;">
                        Limite rinvii raggiunto, torna indietro e verifica che i dati inseriti siano corretti.
                    </h4>`
            return;
        }
        

        if (dynamicContent) {
            dynamicContent.innerHTML = `
                    <h4 style="margin-bottom: 10px; color: rgb(70, 70, 70); font-style: normal; font-weight: normal;">
                        Controlla la cartella Spam o prova a richiedere un nuovo invio.
                    </h4>
                    <form id="reinvio-form" action="confermaRegistrazione.php" method="POST">
                        <input type="hidden" name="reinvia" value="1">
                        <button type="submit" style="background: none; border: none; color: #1f3a78; text-decoration: underline; cursor: pointer; font-weight: bold; font-size: 0.85em; padding: 0;">
                            Clicca qui per reinviare
                        </button>
                    </form>
                `;
        }
        document.getElementById('reinvio-form').addEventListener('submit', function() {
                // Incrementiamo il contatore nel localStorage
                inviiEffettuati++;
                localStorage.setItem(storageKey, inviiEffettuati);
            });
    }, 2000);


});

