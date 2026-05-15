async function apriDettaglioPrenotazione(id) {
    document.querySelectorAll('[id^="dettaglio-content-"]').forEach(el => {
        el.classList.add('d-none');
        el.innerHTML = '';
    });

    const targetDiv = document.getElementById(`dettaglio-content-${id}`);
    if (!targetDiv) return;

    try {
        targetDiv.classList.remove('d-none');
        targetDiv.innerHTML = '<p class="text-muted small">Caricamento...</p>';

        const response = await fetch(`../dashboard/dashboardUtenti/dettaglioPrenotazione.php?id=${id}`);
        const data = await response.json();

        let pulsantiAzione = '';
        const statoNormalizzato = data.Stato.toLowerCase();

        if (statoNormalizzato === 'prenotato') {
            pulsantiAzione = `
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-sm btn-success flex-fill" onclick="eseguiAzione(${id}, 'conferma')">Conferma</button>
                    <button class="btn btn-sm btn-danger flex-fill" onclick="eseguiAzione(${id}, 'elimina')">Elimina</button>
                </div>`;
        } else if (statoNormalizzato === 'in prestito' || statoNormalizzato === 'in ritardo') {
            pulsantiAzione = `
                <div class="mt-3 d-grid">
                    <button class="btn btn-sm btn-warning" onclick="eseguiAzione(${id}, 'termina')">Conferma Consegna</button>
                </div>`;
        }

        targetDiv.innerHTML = `
            <div class="slide-in-right p-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="m-0 fs-md text-dark fw-bold">Gestione</h3>
                    <button type="button" class="btn-close" aria-label="Close" onclick="chiudiDettaglio(${id})"></button>
                </div>

                <div class="fs-sm mb-3">
                    <p class="text-truncate mb-1"><strong>User:</strong> ${data.Email}</p>
                    <div class="bg-surface p-2 rounded border">
                        <div><small class="text-muted">INIZIO:</small> <b>${data.Inizio}</b></div>
                        <div><small class="text-muted">SCADENZA:</small> <b>${data.Fine}</b></div>
                    </div>
                </div>

                <div class="mb-2">
                    <span class="fw-bold fs-sm" style="color: ${data.Color};">
                        STATO: ${data.Stato.toUpperCase()}
                    </span>
                </div>

                ${pulsantiAzione}

                <div class="text-center mt-3 border-top pt-2">
                    <button class="btn btn-sm btn-link text-muted" onclick="chiudiDettaglio(${id})">Annulla</button>
                </div>
            </div>
        `;

    } catch (error) {
        console.error("Errore:", error);
        targetDiv.innerHTML = `<div class="text-danger small">Errore caricamento dati.</div>`;
    }
}

async function eseguiAzione(id, azione) {
    if (azione === "elimina") {
        if (!confirm(`Sei sicuro di voler eseguire l'operazione: ${azione}?`)) return;
    }
    try {
        const formData = new URLSearchParams();
        formData.append('id', id);
        formData.append(azione, 'true');

        const response = await fetch(`../dashboard/dashboardUtenti/handlePrenotazione.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });

        const result = await response.text();
        console.log("Risposta server:", result);

        if (result.includes("ok")) {
            location.reload();
        } else {
            alert("Errore dal server: " + result);
        }
    } catch (error) {
        console.error("Errore durante l'invio:", error);
        alert("Errore di connessione.");
    }
}

function chiudiDettaglio(id) {
    const targetDiv = document.getElementById(`dettaglio-content-${id}`);
    if (targetDiv) {
        targetDiv.classList.add('d-none');
        targetDiv.innerHTML = '';
    }
}
