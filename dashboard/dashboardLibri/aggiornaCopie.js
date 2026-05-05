document.addEventListener("DOMContentLoaded", () => {
    // Caricamento iniziale (se necessario o se vuoi gestire il refresh)
    // caricaLibri(); 
});

document.addEventListener("click", async (e) => {
    const target = e.target;

    // --- 1. RICERCA (Pulsante Cerca) ---
    if (target.id === "searchBtn") {
        eseguiRicerca();
    }

    // --- 2. SALVA AGGIORNAMENTO VELOCE ---
    else if (target.classList.contains("save")) {
        const button = target;
        const row = button.closest("tr");
        const cell = button.closest("td");
        const input = cell.querySelector("input");
        let copie = input.value;
        let isbn = row.dataset.isbn;

        if (copie < 0) {
            showMessage("Errore: il numero deve essere positivo!", "errore");
            return;
        }

        if (!confirm("Confermi aggiornamento veloce copie?")) return;

        const formData = new FormData();
        formData.append("isbn", isbn);
        formData.append("copie", copie);

        const response = await fetch("updateCopie.php", { method: "POST", body: formData });
        const result = await response.text();

        if (result.includes("ok")) {
            showMessage(result.slice(2));
            refreshEspandibile(isbn);
        } else if (!result.includes("ignora")) {
            showMessage(result, "errore");
        }
    }

    // --- 3. ELIMINA SINGOLA COPIA (RAMO DELETE) ---
    else if (target.classList.contains("delete") || target.closest(".delete")) {
        const button = target.classList.contains("delete") ? target : target.closest(".delete");
        const row = button.closest("tr");
        const id = row.dataset.id;

        if (!confirm(`Vuoi eliminare la copia fisica #${id}?`)) return;

        const formData = new FormData();
        formData.append("id", id);

        const response = await fetch("elimina_copia.php", { method: "POST", body: formData });
        const result = await response.text();

        if (result.includes("ok")) {
            showMessage(result.slice(2));
            // Trova l'ISBN risalendo dalla riga di dettaglio a quella precedente
            const rigaDettaglio = row.closest('tr.bg-light');
            const rigaPrincipale = rigaDettaglio.previousElementSibling;
            const isbn = rigaPrincipale.dataset.isbn;
            refreshEspandibile(isbn);
        } else {
            showMessage(result, "errore");
        }
    }

    // --- 4. ESPANDI/CONTRAI ---
    else if (target.classList.contains('btn-espandi')) {
        gestisciEspansione(target);
    }

    // --- 5. ELIMINA INTERA OPERA ---
    else if (target.classList.contains("elimina")) {
        const row = target.closest("tr");
        let isbn = row.dataset.isbn;

        if (!confirm(`Eliminare l'opera ISBN: ${isbn}?`)) return;

        const formData = new FormData();
        formData.append("isbn", isbn);

        const response = await fetch(`eliminaLibro.php`, { method: "POST", body: formData });
        const ans = await response.text();

        if (ans.includes("ok")) {
            document.getElementById(`row-details-${isbn}`)?.remove();
            row.remove();
            showMessage(ans.slice(2));
        } else {
            showMessage(ans, "errore");
        }
    }

    // --- 6. VAI A PRENOTAZIONE ---
    else if (target.classList.contains("dettagli")) {
        const idLibro = target.closest("tr").dataset.id;
        window.location.href = "gotoPrenotazione.php?id=" + idLibro;
    }
});

// --- LOGICA DI RICERCA (Tasto Invio) ---
document.getElementById("searchInput")?.addEventListener("keypress", (e) => {
    if (e.key === "Enter") eseguiRicerca();
});

function eseguiRicerca() {
    const query = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#libriTableBody tr:not(.bg-light)");

    rows.forEach(row => {
        const testoRiga = row.innerText.toLowerCase();
        const isbn = row.dataset.isbn;
        const rigaDettaglio = document.getElementById(`row-details-${isbn}`);

        if (testoRiga.includes(query)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
            if (rigaDettaglio) rigaDettaglio.style.display = "none";
        }
    });
}

// --- FUNZIONI DI SUPPORTO ---

async function gestisciEspansione(btn) {
    const isbn = btn.getAttribute('data-isbn');
    const targetRow = document.getElementById(`row-details-${isbn}`);
    const contentDiv = document.getElementById(`content-${isbn}`);

    if (targetRow.style.display === 'table-row') {
        targetRow.style.display = 'none';
        btn.textContent = '+';
        btn.classList.replace('btn-danger', 'btn-info');
    } else {
        btn.textContent = '...';
        const formData = new FormData();
        formData.append("isbn", isbn);

        const response = await fetch(`get_copie.php`, { method: "POST", body: formData });
        const data = await response.json();

        renderCopie(contentDiv, data, isbn);
        targetRow.style.display = 'table-row';
        btn.textContent = '-';
        btn.classList.replace('btn-info', 'btn-danger');
    }
}

function refreshEspandibile(isbn) {
    const btn = document.querySelector(`.btn-espandi[data-isbn="${isbn}"]`);
    if (btn && btn.textContent === '-') {
        btn.click(); // Chiude
        btn.click(); // Riapre
    }
}

function renderCopie(container, copie, isbn) {
    container.innerHTML = "";
    if (copie.length === 0) {
        container.innerHTML = "<div class='alert alert-info m-0'>Nessuna copia fisica.</div>";
        return;
    }

    let html = `<table class="table table-sm table-striped bg-white border m-0">
        <thead class="thead-light"><tr><th>ID Copia</th><th>Stato</th><th class="text-right">Azioni</th></tr></thead>
        <tbody>`;

    for (let copia of copie) {
        let isDisponibile = copia.Stato == '1';
        html += `
            <tr data-id="${copia.idCopia}">
                <td><strong>#${copia.idCopia}</strong></td>
                <td><span class="badge ${isDisponibile ? 'badge-success' : 'badge-danger'}">${isDisponibile ? 'Disponibile' : 'In Prestito'}</span></td>
                <td class="text-right">
                    ${!isDisponibile ? '<button class="btn btn-sm btn-warning dettagli mr-1">Dettagli</button>' : ''}
                    <button class="btn btn-sm ${isDisponibile ? 'btn-outline-danger delete' : 'btn-outline-secondary'}" 
                            ${!isDisponibile ? 'onclick="showMessage(\'In prestito!\', \'errore\')"' : ''}>
                        <i class="fas fa-trash"></i> Elimina
                    </button>
                </td>
            </tr>`;
    }
    html += '</tbody></table>';
    container.innerHTML = html;
}

function showMessage(text, type = "successo") {
    const div = document.getElementById("messages");
    const p = document.createElement("p");
    p.textContent = text;
    p.className = type;
    div.appendChild(p);
    setTimeout(() => p.remove(), 4000);
}