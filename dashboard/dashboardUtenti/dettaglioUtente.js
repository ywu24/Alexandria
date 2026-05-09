document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-terminated');
    const container = document.getElementById('terminate-container');

    if (!btn) return;

    btn.addEventListener('click', async () => {
        const idUtente = btn.getAttribute('data-id-utente');
        
        // Protezione: se l'ID manca, non procedere
        if (!idUtente) {
            console.error("ID Utente mancante nel bottone");
            return;
        }

        btn.disabled = true;
        btn.textContent = "Caricamento...";

        try {
            const formData = new FormData();
            formData.append('idUtente', idUtente);

            const response = await fetch('get_terminate.php', {
                method: 'POST',
                body: formData
            });

            // Debug: se la risposta non è OK (es. 404 o 500)
            if (!response.ok) throw new Error("Errore del server: " + response.status);

            const data = await response.json();
            
            // Se data non è un array o è vuoto
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `
                    <hr>
                    <div class='alert alert-info shadow-sm' style='border-radius:15px;'>
                        Nessuna prenotazione terminata trovata per questo utente.
                    </div>`;
                return;
            }

            let html = "<h2 class='h4 mb-4 mt-5 font-weight-bold text-secondary text-left'>Storico Prenotazioni Terminate</h2>";

            data.forEach(row => {
                // Logica Stato e Colore (Uguale al PHP)
                let statoTesto = "TERMINATA";
                let statoClasse = "text-muted";

                // Se la data di fine prestito è maggiore della fine attesa, è in ritardo
                if (row.FinePrestito && row.FineAttesa && (new Date(row.FinePrestito) > new Date(row.FineAttesa))) {
                    statoTesto = "TERMINATA IN RITARDO";
                    statoClasse = "text-danger";
                }

                const dataInizio = row.InizioPrestito || row.InizioPrenotazione || "-";
                const dataFine = row.FinePrestito || row.FinePrenotazione || "-";

                // HTML Identico al layout delle prenotazioni attive
                html += `
                <div class="book-container shadow-sm animate-in" id="container-prenotazione-${row.idPrenotazione}">
                    <div class="row no-gutters">
                        <div class="col-md-6 left-panel">
                            <div class="media" style="margin-left: 5%;">
                                <img src="../../img/books/${row.Copertina}" class="mr-4 shadow-sm" width="110" style="border-radius:5px; height: 160px; object-fit: cover;">
                                <div class="media-body text-left">
                                    <h3 class="h5 font-weight-bold" style="margin:0;">${row.Nome}</h3>
                                    <p class="text-muted mb-1 small">${row.Autore}</p>
                                    <p class="small mb-2 ${statoClasse}" style="font-weight:bold;">● ${statoTesto}</p>
                                    <div class="small text-muted">
                                        <span>Dal: ${formattaData(dataInizio)}</span><br>
                                        <span>Al: ${formattaData(dataFine)}</span>
                                    </div>
                                    <button class="btn btn-dark btn-sm mt-3" 
                         
                                            onclick="apriDettaglioPrenotazione(${row.idPrenotazione})">
                                        Gestisci
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 right-panel" id="dettaglio-content-${row.idPrenotazione}" style="display:none;"></div>
                        <div class="col-md-6 right-panel text-center text-muted" id="placeholder-${row.idPrenotazione}">
                            <small>Seleziona "Gestisci" per azioni</small>
                        </div>
                    </div>
                </div>`;
            });

            container.innerHTML = html;

        } catch (error) {
            console.error("Errore AJAX:", error);
            container.innerHTML = `<div class='alert alert-danger'>Errore nel caricamento dei dati. Controlla la console.</div>`;
            btn.disabled = false;
            btn.textContent = "Riprova";
        }
    });
});

// Funzione helper per rendere le date più belle (opzionale)
function formattaData(stringaData) {
    if(!stringaData || stringaData === "-") return "-";
    try {
        const d = new Date(stringaData);
        return d.toLocaleDateString('it-IT');
    } catch(e) { return stringaData; }
}