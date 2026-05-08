document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', async function (event) {

        // 1. "GESTISCI"
        if (event.target.classList.contains('btn-gestisci')) {
            const idPrenotazione = event.target.getAttribute('data-id');
            window.location.href = `../dashboard/dashboardUtenti/dettaglioPrenotazione.php?id=${idPrenotazione}`;
        }

        // 2. "ELIMINA"
        const deleteBtn = event.target.closest('.delete-button');
        if (deleteBtn) {
            const idPrenotazione = deleteBtn.getAttribute('data-id');
            try {
                const formData = new FormData();
                formData.append('id', idPrenotazione)
                formData.append('delete', '1');
                const response = await fetch('eliminaPrenotazione.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.text();
                if (result.includes("ok")) {
                    let row = deleteBtn.closest('.book-container');
                    row.remove();
                    showMessage(result.slice(2));
                } else {
                    showMessage(result, "errore");
                }
            } catch (error) {
                showMessage("Errore: " + error, "errore");
            }
        }

        // 3. CARICAMENTO TERMINATE (HTML UNIFORMATO AL PHP)
        const btn = event.target.closest("#load-terminated");
        if (btn) {
            const container = document.getElementById('terminate-container');
            const email = btn.getAttribute('data-id-utente');
            btn.disabled = true;
            btn.textContent = "Caricamento...";

            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('../dashboard/dashboardUtenti/get_terminate.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.length === 0) {
                    container.innerHTML = "<div class='alert alert-info'>Nessuna prenotazione terminata trovata.</div>";
                } else {
                    let html = "<h3 class='mb-4 mt-2'>Prenotazioni Terminate</h3>";

                    data.forEach(row => {
    let ritardoInfo = "";
    let ritardoLabel = "";

    if (row.FinePrestito > row.FineAttesa) {
        ritardoInfo = `<span><strong>Scadenza originale:</strong> ${row.FineAttesa}</span><br>`;
        ritardoLabel = "con ritardo";
    }

   // ... dentro data.forEach(row => { ...
html += `
<div class="book-container shadow-sm">
    <div class="row no-gutters">
        <!-- Colonna Sinistra (9/12) -->
        <div class="col-md-9 left-panel p-3 d-flex">
            <!-- Copertina Ingrandita a 140px -->
            <img src="../img/books/${row.Copertina}" class="book-cover" alt="Copertina" 
                 style="width:140px; height:200px; object-fit:cover; border-radius:8px; margin-right:20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            
            <div class="flex-grow-1 text-left"> <!-- Testo allineato a sinistra -->
                <h4 class="h4 font-weight-bold mb-1">${row.Nome}</h4>
                <p class="info-meta mb-2" style="font-size: 1.1rem;">${row.Autore} | ${row.CasaEditrice}</p>
                
                <p class="status-dot text-muted mb-2">● Terminata ${ritardoLabel}</p>
                
                <div class="small text-muted mb-3">
                    <span><strong>Inizio prestito:</strong> ${row.InizioPrestito}</span><br>
                    <span><strong>Fine prestito:</strong> ${row.FinePrestito}</span><br>
                    ${ritardoInfo}
                    
                    <!-- ISBN ALLINEATO AL CENTRO -->
                    
                    <span class="text-dark"><strong>ISBN:</strong> ${row.ISBN}</span>
                    
                </div>

                <div class="container-pulsanti">
                    ${row.recensito == 0 ? 
                        `<a href="../recensione/recensione.php?id=${row.idOpera}" class="btn btn-warning px-4">Recensisci</a>` 
                        : ``
                    }
                </div>
            </div>
        </div>

        <!-- Colonna Destra (3/12) -->
        <div class="col-md-3 bg-light d-flex align-items-start p-3 border-left text-left">
            <p class="text-muted small mb-0">
                Storico: Libro restituito correttamente.
            </p>
        </div>
    </div>
</div>`;
});
                    container.innerHTML = html;
                }
                btn.style.display = 'none';
            } catch (error) {
                console.error("Errore:", error);
                alert("Impossibile caricare le prenotazioni terminate.");
                btn.disabled = false;
                btn.textContent = "Riprova";
            }
        }
    });
});

function showMessage(text, type = "successo") {
    const div = document.getElementById("messages");
    const p = document.createElement("p");
    p.textContent = text;
    p.classList.add(type);
    div.appendChild(p);
    setTimeout(() => { p.remove(); }, 4000);
}