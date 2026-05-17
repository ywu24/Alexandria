document.addEventListener("DOMContentLoaded", function () {
    const storageKey = "reinvio_count";
    const reinvioContainer = document.querySelector('.reinvio');

    if (!reinvioContainer) return;

    setTimeout(function () {
        reinvioContainer.innerHTML = `
            <div class="mt-4 w-100">
                <h3 class="text-danger fw-bold fs-6 mb-1">
                    PROBLEMI CON LA MAIL?
                </h3>
                <div id="dynamic-content">
                    <h4 class="text-muted fst-italic fw-bold">
                        Attaccate ar cazzo (aspetta qualche secondo).
                    </h4>
                </div>
            </div>
        `;
    }, 2000);

    setTimeout(function () {
        let inviiEffettuati = localStorage.getItem(storageKey) || 0;
        const dynamicContent = document.getElementById('dynamic-content');

        if (inviiEffettuati > 2) {
            dynamicContent.innerHTML = `
                <h4 class="mb-2 text-muted fst-normal fw-normal">
                    Limite rinvii raggiunto, torna indietro e verifica che i dati inseriti siano corretti.
                </h4>`;
            return;
        }

        if (dynamicContent) {
            dynamicContent.innerHTML = `
                <h4 class="mb-2 text-muted fst-normal fw-normal">
                    Controlla la cartella Spam o prova a richiedere un nuovo invio.
                </h4>
                <form id="reinvio-form" action="confermaRegistrazione.php" method="POST">
                    <input type="hidden" name="reinvia" value="1">
                    <button type="submit" class="btn btn-link p-0 text-primary fw-bold fs-sm">
                        Clicca qui per reinviare
                    </button>
                </form>
            `;
        }
        document.getElementById('reinvio-form').addEventListener('submit', function() {
            inviiEffettuati++;
            localStorage.setItem(storageKey, inviiEffettuati);
        });
    }, 12000);
});
