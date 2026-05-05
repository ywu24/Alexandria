async function caricaUtenti(append = false) {
    const container = document.getElementById("userTableBody");
    const btnAltro = document.getElementById("caricaAltro");
    const form = document.getElementById("filtriForm");

    if (!container) return;

    try {
        const formData = new FormData(form);
        // Calcolo offset basato sulle righe già presenti
        const offset = append ? container.querySelectorAll("tr").length : 0;
        
        formData.set('offset', offset);
        formData.set('limit', 10);

        const response = await fetch('getUtenti.php', {
            method: 'POST',
            body: formData
        });

        const utenti = await response.json();
        console.log(utenti);

        // Se non è append, stiamo facendo una nuova ricerca o ordinamento: svuota tutto
        if (!append) {
            container.innerHTML = "";
        }

        if (utenti.length === 0) {
            if (!append) {
                container.innerHTML = '<tr><td colspan="10" class="text-center text-muted p-4">Nessun record trovato</td></tr>';
            }
            if (btnAltro) btnAltro.style.display = "none";
            return;
        }

        utenti.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = user.html;
            container.appendChild(tr);
        });

        // Mostra il tasto Carica Altro solo se abbiamo ricevuto esattamente 10 record
        if (btnAltro) {
            btnAltro.style.display = (utenti.length === 10) ? "block" : "none";
        }

    } catch (error) {
        console.error("Errore nel caricamento utenti:", error);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const btnSearch = document.getElementById("searchBtn");
    const searchInput = document.getElementById("searchInput");
    
    // Gestione Cerca (Click e Invio)
    if (btnSearch) {
        btnSearch.addEventListener("click", () => caricaUtenti(false));
    }
    
    if (searchInput) {
        searchInput.addEventListener("keypress", (e) => {
            
            if (e.key === "Enter") {
                caricaUtenti(false);
            }
        });
    }

    // Gestione Ordinamento
    document.querySelectorAll(".sort_btn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("sort_type").value = this.dataset.sort;
            caricaUtenti(false);
        });
    });

    // Carica Altro
    const btnAltro = document.getElementById("caricaAltro");
    if (btnAltro) {
        btnAltro.addEventListener("click", () => caricaUtenti(true));
    }

    // Caricamento iniziale automatico
    caricaUtenti(false);
});