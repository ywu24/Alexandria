async function apriDettaglioPrenotazione(id) {
    // 1. Pulizia e chiusura di altri dettagli
    document.querySelectorAll('[id^="dettaglio-content-"]').forEach(el => {
        el.style.display = 'none';
        el.innerHTML = '';
    });

    const targetDiv = document.getElementById(`dettaglio-content-${id}`);
    if (!targetDiv) return;

    try {
        targetDiv.style.display = 'block';
        targetDiv.innerHTML = '<p class="text-muted small">Caricamento...</p>';

        const response = await fetch(`dettaglioPrenotazione.php?id=${id}`);
        const data = await response.json();

        // 2. Definizione dei pulsanti in base allo stato
        let pulsantiAzione = '';
        const statoNormalizzato = data.Stato.toLowerCase();

        if (statoNormalizzato === 'prenotato') {
            pulsantiAzione = `
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn btn-sm btn-success" style="flex:1" onclick="eseguiAzione(${id}, 'conferma')">Conferma</button>
                    <button class="btn btn-sm btn-danger" style="flex:1" onclick="eseguiAzione(${id}, 'elimina')">Elimina</button>
                </div>`;
        } else if (statoNormalizzato === 'in prestito' || statoNormalizzato === 'in ritardo') {
            pulsantiAzione = `
                <div style="margin-top: 15px;">
                    <button class="btn btn-sm btn-warning btn-block" onclick="eseguiAzione(${id}, 'termina')">Conferma Consegna</button>
                </div>`;
        }

        // 3. Render del contenuto
        targetDiv.innerHTML = `
            <div class="animate-in" style="padding: 10px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 style="margin: 0; font-size: 18px; color: #333; font-weight: bold;">Gestione</h3>
                    <button type="button" class="close" onclick="chiudiDettaglio(${id})">&times;</button>
                </div>
                
                <div style="font-size: 14px; margin-bottom: 15px;">
                    <p class="text-truncate" style="margin-bottom: 5px;"><strong>User:</strong> ${data.Email}</p>
                    <div style="background: #fdfdfd; padding: 10px; border-radius: 5px; border: 1px solid #eee;">
                        <div><small class="text-muted">INIZIO:</small> <b>${data.Inizio}</b></div>
                        <div><small class="text-muted">SCADENZA:</small> <b>${data.Fine}</b></div>
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <span style="color: ${data.Color}; font-weight: bold; font-size: 13px;">
                        STATO: ${data.Stato.toUpperCase()}
                    </span>
                </div>

                ${pulsantiAzione}

                <div class="text-center" style="margin-top: 15px; border-top: 1px solid #eee; pt-2;">
                    <button class="btn btn-xs btn-link" style="color: #999;" onclick="chiudiDettaglio(${id})">Annulla</button>
                </div>
            </div>
            
            <style>
                .animate-in { animation: fadeInRight 0.3s ease-in-out; }
                @keyframes fadeInRight { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
            </style>
        `;

    } catch (error) {
        console.error("Errore:", error);
        targetDiv.innerHTML = `<div class="text-danger small">Errore caricamento dati.</div>`;
    }
}

// 4. Nuova funzione per gestire le azioni POST 
async function eseguiAzione(id, azione) {
    if (azione === "elimina") {
        if (!confirm(`Sei sicuro di voler eseguire l'operazione: ${azione}?`)) return;
    }
    try {
        // Creiamo un oggetto FormData per simulare un invio di form standard
        const formData = new URLSearchParams();
        formData.append('id', id);      // <--- Questo risolve il primo errore
        formData.append(azione, 'true'); // <--- Questo attiva lo 'switch' nel PHP

        const response = await fetch(`handlePrenotazione.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        });

        const result = await response.text();
        console.log("Risposta server:", result); // Debug fondamentale

        if (result.includes("ok")) {
            // Se l'operazione è 'elimina', chiudiamo il dettaglio e magari aggiorniamo la pagina
            location.reload(); // Ricarica per far sparire la riga dalla lista

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
        targetDiv.style.display = 'none';
        targetDiv.innerHTML = '';
    }
}
// Esempio di utilizzo (facendo finta di avere l'ID 123)
// showReservationPopup(123);