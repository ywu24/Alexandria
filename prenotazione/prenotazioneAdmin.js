/**
 * Carica i dati dal server.
 * append = true -> cliccato "Carica Altro" (aggiunge righe in coda)
 * append = false -> filtri cambiati, ricerca o primo caricamento (resetta tabella)
 */
async function caricaPrenotazioni(append = false) {
    const container = document.getElementById("bookings");
    const btn = document.getElementById("caricaAltro");
    const form = document.getElementById("filtriForm");

    if (!container) return;

    try {
        const formData = new FormData(form);
        
        // Calcolo OFFSET basandosi direttamente sulle righe presenti nel DOM
        // Se append è false, l'offset deve essere 0 per ricominciare dall'inizio
        const righePresenti = append ? container.querySelectorAll("tr.clickable-row").length : 0;

        formData.append('caricaAltro', 10);
        formData.append('offset', righePresenti);

        const response = await fetch('getPrenotazioni.php', {
            method: 'POST',
            body: formData
        });

        const prenotazioni = await response.json();

        // RESET TABELLA: Se non stiamo aggiungendo, svuotiamo il contenitore
        if (!append) {
            container.innerHTML = "";
        }

        // CASO VUOTO
        if (prenotazioni.length === 0) {
            if (!append) {
                container.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-4">Nessun record trovato</td></tr>';
            }
            if (btn) btn.style.display = "none";
            return;
        }

        // CREAZIONE RIGHE
        prenotazioni.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = "clickable-row";
            tr.style.cursor = "pointer";
            tr.onclick = () => window.location.href = row.dettaglioUrl;

            tr.innerHTML = `
                <th scope="row">${row.ISBN}</th>
                <td>
                    <strong>${row.Nome}</strong><br>
                    <small class="text-muted">${row.Autore}</small>
                </td>
                <td>${row.email}</td>
                <td><span class="font-weight-bold ${row.color}">${row.stato_calcolato}</span></td>
                <td><small>Dal: ${row.inizio_formattato}<br>Al: ${row.fine_formattata}</small></td>
                <td>
                    <a href="${row.dettaglioUrl}" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">Gestisci</a>
                </td>
            `;
            container.appendChild(tr);
        });

        // GESTIONE VISIBILITÀ PULSANTE
        if (btn) {
            // Mostra il tasto solo se abbiamo ricevuto esattamente 10 righe
            // (se ne riceviamo meno, significa che i dati nel database sono finiti)
            btn.style.display = (prenotazioni.length === 10) ? "block" : "none";
        }

    } catch (error) {
        console.error("Errore nel caricamento:", error);
    }
}

// --- EVENT LISTENERS ---

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("filtriForm");
    const btnCarica = document.getElementById("caricaAltro");

    if (form) {
        // Submit manuale (tasto cerca o invio)
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            caricaPrenotazioni(false); 
        });

        // Reset filtri: svuota i campi e ricarica da zero
        form.addEventListener("reset", function () {
            setTimeout(() => {
                // Se hai campi hidden per il sort, resettali qui
                const sortInput = form.querySelector('input[name="sort_type"]');
                if (sortInput) sortInput.value = "";
                caricaPrenotazioni(false); 
            }, 10);
        });

        // Filtri automatici: al cambio di Select o Date ricarica subito
        form.querySelectorAll('select, input[type="date"]').forEach(input => {
            input.addEventListener('change', () => caricaPrenotazioni(false));
        });
    }

    // Listener per il tasto Carica Altro
    if (btnCarica) {
        btnCarica.addEventListener("click", () => caricaPrenotazioni(true));
    }

});