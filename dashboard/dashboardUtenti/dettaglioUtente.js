document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('load-terminated');
    const container = document.getElementById('terminate-container');

    if (!btn) return;

    btn.addEventListener('click', async () => {
        const idUtente = btn.getAttribute('data-id-utente');
        
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

            if (!response.ok) throw new Error("Errore del server: " + response.status);

            const data = await response.json();
            
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `
                    <hr>
                    <div class='alert alert-info shadow-sm' style='border-radius:15px;'>
                        Nessuna prenotazione terminata trovata per questo utente.
                    </div>`;
                btn.style.display = 'none'; // Nasconde il tasto se non c'è altro da caricare
                return;
            }

            // Titolo dello storico
            let html = "<h2 class='h4 mb-4 mt-5 font-weight-bold text-secondary text-left'>Storico Prenotazioni Terminate</h2>";

            data.forEach(row => {
                // Logica Stato e Colore
                let statoTesto = "TERMINATA";
                let statoClasse = "text-muted";

                if (row.FinePrestito && row.FineAttesa && (new Date(row.FinePrestito) > new Date(row.FineAttesa))) {
                    statoTesto = "TERMINATA IN RITARDO";
                    statoClasse = "text-danger";
                }

                const dataInizio = row.InizioPrestito || row.InizioPrenotazione || "-";
                const dataFine = row.FinePrestito || row.FinePrenotazione || "-";

                // --- STRUTTURA ALLINEATA AL PHP ---
                html += `
                <div class="book-container shadow-sm animate-in" id="container-prenotazione-${row.idPrenotazione}">
                    <div class="row no-gutters">
                        <!-- Pannello Sinistro: Info Libro -->
                        <div class="col-md-6 left-panel">
                            <div class="media">
                                <img src="../../img/books/${row.Copertina}" class="mr-4 shadow-sm" alt="Copertina">
                                <div class="media-body text-left">
                                    <h3 class="h5 font-weight-bold">${row.Nome}</h3>
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
                        <!-- Pannello Destro: Dettaglio (nascondo il placeholder e mostro il contenuto se necessario) -->
                        <div class="col-md-6 right-panel" id="dettaglio-content-${row.idPrenotazione}" style="display:none;"></div>
                        <div class="col-md-6 right-panel text-center text-muted" id="placeholder-${row.idPrenotazione}">
                            <small>Seleziona "Gestisci" per azioni</small>
                        </div>
                    </div>
                </div>`;
            });

            container.innerHTML = html;
            btn.style.display = 'none'; // Nasconde il tasto dopo il caricamento riuscito

        } catch (error) {
            console.error("Errore AJAX:", error);
            container.innerHTML = `<div class='alert alert-danger'>Errore nel caricamento dei dati.</div>`;
            btn.disabled = false;
            btn.textContent = "Riprova";
        }
    });
});

function formattaData(stringaData) {
    if(!stringaData || stringaData === "-") return "-";
    try {
        const d = new Date(stringaData);
        return d.toLocaleDateString('it-IT');
    } catch(e) { return stringaData; }
}