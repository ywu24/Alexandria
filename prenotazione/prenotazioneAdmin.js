document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("filtriForm");
    const btnCarica = document.getElementById("caricaAltro");

    if (form) {
        // Al cambio di qualsiasi input (select o date), ricarica da zero
        form.addEventListener("change", () => caricaPrenotazioni(false));
        
        // Impedisce il refresh della pagina al submit
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            caricaPrenotazioni(false);
        });

        // Gestione Reset
        form.addEventListener("reset", () => {
            setTimeout(() => caricaPrenotazioni(false), 10);
        });
    }

    if (btnCarica) {
        btnCarica.addEventListener("click", () => caricaPrenotazioni(true));
    }
});

async function caricaPrenotazioni(append = false) {
    const container = document.getElementById("bookings-container");
    const btn = document.getElementById("caricaAltro");
    const form = document.getElementById("filtriForm");

    if (!container) return;

    try {
        const formData = new FormData(form);
        const offset = append ? container.querySelectorAll(".book-container").length : 0;
        
        formData.append('offset', offset);
        formData.append('caricaAltro', 10);

        const response = await fetch('getPrenotazioni.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error("Errore nel server");

        const prenotazioni = await response.json();

        if (!append) container.innerHTML = "";

        if (prenotazioni.length === 0) {
            if (!append) {
                container.innerHTML = '<div class="alert alert-light text-center w-100">Nessuna prenotazione trovata.</div>';
            }
            if (btn) btn.style.display = "none";
            return;
        }

        prenotazioni.forEach(row => {
    const div = document.createElement('div');
    div.className = "book-container shadow-sm animate-in";
    div.id = `container-prenotazione-${row.idPrenotazione}`;
    
    div.innerHTML = `
        <div class="row no-gutters">
            <!-- PARTE SINISTRA: Foto e Info -->
            <div class="col-md-6 left-panel">
                <div class="media media-book">
                    <!-- AGGIUNTA CLASSE book-cover QUI SOTTO -->
                    <img src="../img/books/${row.Copertina}" class="mr-4 shadow-sm book-cover" alt="Copertina">
                    <div class="media-body">
                        <h3 class="h5 font-weight-bold book-title">${row.Nome}</h3>
                        <p class="text-muted mb-1 small">${row.Autore}</p>
                        
                        <!-- RIMOZIONE INLINE STYLE: usiamo status-badge -->
                        <p class="small mb-2 status-badge ${row.color}">
                            ● ${row.stato_calcolato.toUpperCase()}
                        </p>

                        <div class="small text-muted">
                            <span>Dal: ${row.inizio_formattato}</span><br>
                            <span>Al: ${row.fine_formattata}</span><br>
                            <span class="text-dark">User: ${row.email}</span>
                        </div>
                        <button class="btn btn-dark btn-sm mt-3" onclick="apriDettaglioPrenotazione(${row.idPrenotazione})">Gestisci</button>
                    </div>
                </div>
            </div>
            <!-- PARTE DESTRA: Spazio Gestione Dinamica -->
            <div class="col-md-6 right-panel" id="dettaglio-content-${row.idPrenotazione}" style="display:none;"></div>
            <!-- Placeholder iniziale lato destro -->
            <div class="col-md-6 right-panel text-center text-muted" id="placeholder-${row.idPrenotazione}">
                <small>Seleziona "Gestisci" per azioni</small>
            </div>
        </div>`;
    container.appendChild(div);
});

        if (btn) {
            btn.style.display = (prenotazioni.length === 10) ? "inline-block" : "none";
        }

    } catch (error) {
        console.error("Errore:", error);
    }
}