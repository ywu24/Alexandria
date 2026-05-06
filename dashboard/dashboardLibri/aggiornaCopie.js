let currentSort = 'Nome';
let currentOffset = 0;
const LIMIT = 10;

async function caricaLibri(append = false) {
    const tableBody = document.getElementById("libriTableBody");
    const searchInput = document.getElementById("searchInput");
    const loadMoreBtn = document.getElementById("loadMoreBtn");
    
    if (!append) currentOffset = 0; 

    const formData = new FormData();
    formData.append('search', searchInput ? searchInput.value : "");
    formData.append('sort_type', currentSort);
    formData.append('offset', currentOffset);

    try {
        const response = await fetch('getLibri.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (!append) tableBody.innerHTML = "";

        if (data.length === 0) {
            if (!append) tableBody.innerHTML = '<tr><td colspan="8" class="text-center p-4">Nessun libro trovato</td></tr>';
            loadMoreBtn.style.display = "none";
            return;
        }

        data.forEach(libro => {
            tableBody.insertAdjacentHTML('beforeend', libro.html);
        });

        if (data.length === LIMIT) {
            loadMoreBtn.style.display = "inline-block";
            currentOffset += LIMIT;
        } else {
            loadMoreBtn.style.display = "none";
        }

    } catch (e) {
        console.error("Errore:", e);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    caricaLibri();

    document.getElementById("searchBtn").onclick = () => caricaLibri(false);
    document.getElementById("searchInput").onkeyup = (e) => { if (e.key === "Enter") caricaLibri(false); };
    
    document.getElementById("loadMoreBtn").onclick = () => caricaLibri(true);

    document.querySelectorAll(".sort_btn").forEach(btn => {
        btn.onclick = function() {
            currentSort = this.dataset.sort;
            caricaLibri(false);
        };
    });
});

// LOGICA CLICK GLOBALE (Salva, Espandi, Elimina Opera, Elimina Copia)
document.addEventListener("click", async (e) => {
    const target = e.target;

    // --- MENU A TENDINA RESPONSIVE (INFO MOBILE) ---
    if (target.classList.contains('btn-info-mobile') || target.closest('.btn-info-mobile')) {
        const btn = target.classList.contains('btn-info-mobile') ? target : target.closest('.btn-info-mobile');
        const row = btn.closest("tr");
        const isbn = row.dataset.isbn;
        const mobileRowId = `mobile-info-${isbn}`;
        let mobileRow = document.getElementById(mobileRowId);

        if (mobileRow) {
            // Se esiste già, alterna visualizzazione
            if (mobileRow.style.display === 'none') {
                mobileRow.style.display = 'table-row';
                btn.innerHTML = 'Chiudi';
                btn.classList.replace('btn-secondary', 'btn-dark');
            } else {
                mobileRow.style.display = 'none';
                btn.innerHTML = 'Info';
                btn.classList.replace('btn-dark', 'btn-secondary');
            }
        } else {
            // Se non esiste, crea la riga estraendo i dati dalle colonne nascoste
            const tds = row.querySelectorAll("td");
            
            // Indici basati sul renderRow: 
            const autore = tds[1] ? tds[1].innerHTML : '';
            const genere = tds[2] ? tds[2].innerHTML : '';
            const anno = tds[3] ? tds[3].innerHTML : '';
            const casa = tds[4] ? tds[4].innerHTML : '';
            const copie = tds[5] ? tds[5].innerHTML : '';
            const azioni = tds[6] ? tds[6].innerHTML : '';

            // Layout a tendina
            const html = `
                <tr id="${mobileRowId}" class="bg-light shadow-sm riga-mobile-info">
                    <td colspan="3" class="p-3 border-info">
                        <ul class="list-unstyled mb-0 text-left">
                            <li class="mb-1"><strong>Autore:</strong> ${autore}</li>
                            <li class="mb-1"><strong>Genere:</strong> ${genere}</li>
                            <li class="mb-1"><strong>Casa Editrice:</strong> ${casa}</li>
                            <li class="mb-1"><strong>Anno:</strong> ${anno}</li>
                            <li class="mb-2 mt-3 p-2 bg-white border rounded d-flex align-items-center justify-content-between">
                                <strong>Copie:</strong>
                                <div>${copie}</div>
                            </li>
                            <li class="mt-3 text-right border-top pt-2">${azioni}</li>
                        </ul>
                    </td>
                </tr>
            `;
            // Inserisci sotto la riga principale
            row.insertAdjacentHTML('afterend', html);
            btn.innerHTML = 'Chiudi';
            btn.classList.replace('btn-secondary', 'btn-dark');
        }
        return; // Ferma l'esecuzione per evitare conflitti con altre logiche
    }

    // --- AGGIORNA COPIE (VELOCE) ---
    if (target.classList.contains("save")) {
        const row = target.closest("tr");
        const input = row.querySelector("input[type='number']");
        const isbn = row.dataset.isbn;
        if (!confirm("Aggiornare le copie?")) return;
        
        const fd = new FormData();
        fd.append("isbn", isbn);
        fd.append("copie", input.value);
        
        const res = await fetch("updateCopie.php", { method: "POST", body: fd });
        const txt = await res.text();
        if (txt.includes("ok")) showMessage("Aggiornato!");
    }

    // --- ESPANDI DETTAGLI COPIE ---
    if (target.classList.contains('btn-espandi')) {
        const isbn = target.dataset.isbn;
        const targetRow = document.getElementById(`row-details-${isbn}`);
        const contentDiv = document.getElementById(`content-${isbn}`);

        if (targetRow.style.display === 'table-row') {
            targetRow.style.display = 'none';
            target.textContent = '+';
            target.classList.replace('btn-danger', 'btn-info');
        } else {
            target.textContent = '...';
            const fd = new FormData();
            fd.append("isbn", isbn);
            const res = await fetch(`get_copie.php`, { method: "POST", body: fd });
            const data = await res.json();
            renderCopie(contentDiv, data, e);
            targetRow.style.display = 'table-row';
            target.textContent = '-';
            target.classList.replace('btn-info', 'btn-danger');
        }
    }

    // --- ELIMINA INTERA OPERA (ISBN) ---
    if (target.classList.contains("elimina")) {
        const row = target.closest("tr");
        if (!confirm("Sei sicuro di voler eliminare l'intera opera e tutte le sue copie?")) return;
        const fd = new FormData();
        fd.append("isbn", row.dataset.isbn);
        const res = await fetch("eliminaLibro.php", { method: "POST", body: fd });
        const txt = await res.text();
        if (txt.includes("ok")) {
            row.remove();
            document.getElementById(`row-details-${row.dataset.isbn}`)?.remove();
            showMessage("Opera eliminata correttamente");
        }
    }

    // --- NUOVA LOGICA: ELIMINA SINGOLA COPIA (ID) ---
    if (target.classList.contains("delete") || target.closest(".delete")) {
        // Se l'utente clicca sull'icona trash dentro il bottone
        const btn = target.classList.contains("delete") ? target : target.closest(".delete");
        const row = btn.closest("tr");
        const idCopia = row.dataset.id;

        if (!confirm(`Vuoi eliminare definitivamente la copia #${idCopia}?`)) return;

        const fd = new FormData();
        fd.append("id", idCopia);

        try {
            const res = await fetch("elimina_copia.php", { method: "POST", body: fd });
            const result = await res.text();

            if (result.includes("ok")) {
                showMessage(result.slice(2)); // Toglie 'ok' e mostra il resto
                
                // Per aggiornare il conteggio nella riga principale, simuliamo un refresh del dettaglio
                const rigaDettaglio = btn.closest('tr.bg-light') || btn.closest('td').closest('tr').closest('tbody').closest('table').closest('div').closest('td').closest('tr');
                const rigaPrincipale = rigaDettaglio.previousElementSibling;
                const btnEspandi = rigaPrincipale.querySelector('.btn-espandi');
                
                if (btnEspandi) {
                    btnEspandi.click(); // Chiude
                    btnEspandi.click(); // Riapre e ricarica i dati aggiornati
                }
            } else {
                showMessage(result, "errore");
            }
        } catch (error) {
            showMessage("Errore di connessione", "errore");
        }
    }

    // --- DETTAGLI PRENOTAZIONE ---
    if (target.classList.contains("dettagli")) {
        const idCopia = target.closest("tr").dataset.id;
        window.location.href = "gotoPrenotazione.php?id=" + idCopia;
    }
});

function showMessage(text, type = "successo") {
    const div = document.getElementById("messages");
    if(!div) return;
    const p = document.createElement("p");
    p.textContent = text;
    p.classList.add(type);
    div.appendChild(p);
    setTimeout(() => { p.remove(); }, 4000);
}

function renderCopie(container, copie, e) {
    container.innerHTML = "";

    if (copie.length === 0) {
        container.innerHTML = "<div class='alert alert-info'>Nessuna copia disponibile per questo volume.</div>";
        return;
    }

    let html = `
        <table class="table table-sm table-striped bg-white border m-0">
            <thead class="thead-light">
                <tr>
                    <th>ID Copia</th>
                    <th>Stato</th>
                    <th class="text-right">Azioni</th>
                </tr>
            </thead>
            <tbody>`;

    for (let copia of copie) {
        let isDisponibile = (copia.Stato == '1');
        let statoTesto = isDisponibile ? 'Disponibile' : 'In Prestito';
        let badgeClass = isDisponibile ? 'badge-success' : 'badge-danger';
        
        html += `<tr data-id="${copia.idCopia}">
            <td><strong>#${copia.idCopia}</strong></td>
            <td><span class="badge ${badgeClass}">${statoTesto}</span></td>
            <td class="text-right">
                ${!isDisponibile ? '<button class="btn btn-sm btn-warning dettagli">Dettagli</button>' : ''}
                <button class="btn btn-sm ${isDisponibile ? 'btn-outline-danger delete' : 'btn-outline-secondary'}" 
                        ${!isDisponibile ? 'disabled' : ''}
                        onclick="${!isDisponibile ? "showMessage('Impossibile eliminare una copia in prestito!', 'errore')" : ""}">
                    <i class="fas fa-trash"></i> Elimina
                </button>
            </td>
        </tr>`;
    }

    html += '</tbody></table>';
    container.innerHTML = html;
}