let currentSort = 'Nome';
let currentOffset = 0;
const LIMIT = 10;

async function caricaLibri(append = false) {
    const tableBody = document.getElementById("libriTableBody");
    const searchInput = document.getElementById("searchInput");
    const loadMoreBtn = document.getElementById("loadMoreBtn");
    
    if (!append) currentOffset = 0; // Se è una nuova ricerca/ordinamento, resetta l'offset

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

        // Se abbiamo ricevuto esattamente LIMIT risultati, mostriamo il tasto "Carica Altro"
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
    // Caricamento iniziale gestito da JS per coerenza con l'offset
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

// Mantieni il resto del codice (click globale per save, espandi, elimina...)

// LOGICA CLICK GLOBALE (Salva, Espandi, Elimina)
document.addEventListener("click", async (e) => {
    const target = e.target;

    if (target.classList.contains("save")) {
        // ... tua logica salva esistente ...
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

    if (target.classList.contains("elimina")) {
        const row = target.closest("tr");
        if (!confirm("Eliminare il libro?")) return;
        const fd = new FormData();
        fd.append("isbn", row.dataset.isbn);
        const res = await fetch("eliminaLibro.php", { method: "POST", body: fd });
        const txt = await res.text();
        if (txt.includes("ok")) {
            row.remove();
            document.getElementById(`row-details-${row.dataset.isbn}`)?.remove();
        }
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
        let statoTesto = 'Non Disponibile';
        let badgeClass = 'badge-danger';
        let btnClass = 'btn-outline-secondary';
        let action = "";
        let dettagli = "";
        let disabled = "";

        if (copia.Stato == '1') {
            badgeClass = 'badge-success';
            statoTesto = 'Disponibile';
            btnClass = 'btn-outline-danger delete';
            // Qui dovresti avere la logica per eliminare la singola copia (idCopia)
        } else {
            disabled = "disabled";
            action = "showMessage('Non puoi eliminare libri in prestito!', 'errore');";
            dettagli = '<button class="btn btn-sm btn-warning dettagli">Dettagli</button>';
        }

        html += `<tr data-id="${copia.idCopia}">
            <td><strong>#${copia.idCopia}</strong></td>
            <td><span class="badge ${badgeClass}">${statoTesto}</span></td>
            <td class="text-right">
                ${dettagli}
                <button class="btn btn-sm ${btnClass}" ${disabled} onclick="${action}">
                    <i class="fas fa-trash"></i> Elimina
                </button>
            </td>
        </tr>`;
    }

    html += '</tbody></table>';
    container.innerHTML = html;
}