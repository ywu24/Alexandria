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
        // Calcola l'offset contando quanti card ci sono già
        const offset = append ? container.querySelectorAll(".book-container").length : 0;
        
        formData.append('offset', offset);
        formData.append('caricaAltro', 10);

        const response = await fetch('getPrenotazioni.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error("Errore nel server");

        const prenotazioni = await response.json();

        // Se non stiamo appendendo, svuotiamo tutto
        if (!append) container.innerHTML = "";

        if (prenotazioni.length === 0) {
            if (!append) {
                container.innerHTML = '<div class="alert alert-light text-center w-100">Nessuna prenotazione trovata.</div>';
            }
            if (btn) btn.style.display = "none";
            return;
        }

        // Ciclo sui risultati JSON
        prenotazioni.forEach(row => {
            const div = document.createElement('div');
            div.className = "book-container shadow-sm animate-in";
            div.id = `container-prenotazione-${row.idPrenotazione}`;
            // Stile in linea per coerenza con il caricamento PHP
            div.style = "background:#fff; border-radius:12px; margin-bottom:20px; overflow:hidden; border:1px solid #eee;";
            
            div.innerHTML = `
                <div class="row no-gutters" style="min-height: 180px;">
                    <!-- PARTE SINISTRA: Foto e Info -->
                    <div class="col-md-6 p-3 d-flex border-right">
                        <img src="../img/books/${row.Copertina}" class="shadow-sm mr-3" style="width:100px; height:140px; object-fit:cover; border-radius:5px;">
                        <div class="flex-grow-1">
                            <h4 class="h5 font-weight-bold mb-1">${row.Nome}</h4>
                            <p class="text-muted small mb-2">${row.Autore}</p>
                            <p class="small mb-2 ${row.color}" style="font-weight:800;">● ${row.stato_calcolato.toUpperCase()}</p>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                <span>Dal: ${row.inizio_formattato}</span><br>
                                <span>Al: ${row.fine_formattata}</span><br>
                                <span class="text-dark">User: ${row.email}</span>
                            </div>
                            <button class="btn btn-dark btn-sm mt-2 px-4" onclick="apriDettaglioPrenotazione(${row.idPrenotazione})">Gestisci</button>
                        </div>
                    </div>
                    <!-- PARTE DESTRA: Spazio Gestione Dinamica -->
                    <div class="col-md-6 right-panel" id="dettaglio-content-${row.idPrenotazione}" style="display:none; background:#fdfdfd; padding:20px;"></div>
                    <!-- Placeholder iniziale lato destro -->
                    <div class="col-md-6 right-panel text-center d-flex align-items-center justify-content-center text-muted" id="placeholder-${row.idPrenotazione}" style="background:#fafafa;">
                        <small>Seleziona una prenotazione per gestirla</small>
                    </div>
                </div>`;
            container.appendChild(div);
        });

        // Gestione visibilità pulsante Carica Altro
        if (btn) {
            btn.style.display = (prenotazioni.length === 10) ? "inline-block" : "none";
        }

    } catch (error) {
        console.error("Errore:", error);
    }
}