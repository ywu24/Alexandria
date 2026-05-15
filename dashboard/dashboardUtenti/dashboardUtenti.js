async function caricaUtenti(append = false) {
    const container = document.getElementById("userTableBody");
    const btnAltro = document.getElementById("caricaAltro");
    const form = document.getElementById("filtriForm");

    if (!container) return;

    try {
        const formData = new FormData(form);
        const offset = append ? container.querySelectorAll("tr.user-main-row").length : 0;

        formData.set('offset', offset);
        formData.set('limit', 10);

        const response = await fetch('getUtenti.php', {
            method: 'POST',
            body: formData
        });

        const utenti = await response.json();
        console.log(utenti);

        if (!append) {
            container.innerHTML = "";
        }

        if (utenti.length === 0) {
            if (!append) {
                container.innerHTML = '<tr><td colspan="10" class="text-center text-muted p-4">Nessun record trovato</td></tr>';
            }
            if (btnAltro) btnAltro.classList.add('d-none');
            return;
        }

        utenti.forEach(user => {
            const trMain = document.createElement('tr');
            trMain.className = "user-main-row";
            trMain.innerHTML = user.html;

            const toggleTd = document.createElement('td');
            toggleTd.className = "mobile-only text-center align-middle";
            toggleTd.innerHTML = `<button class="btn btn-sm btn-secondary btn-info-mobile">Info</button>`;
            trMain.appendChild(toggleTd);

            container.appendChild(trMain);

            const trDetails = document.createElement('tr');
            trDetails.className = "mobile-details-row d-none";

            let azioniHtml = "";

            if (USER_TYPE == 1) {
                azioniHtml = `
                    <div class="d-flex flex-column gap-2 mt-3">
                        <a class="btn btn-primary w-100" href="modificaUtente.php?id=${user.id}">Modifica</a>
                        <a class="btn btn-danger w-100" href="eliminaUtente.php?id=${user.email}">Elimina</a>
                    </div>
                `;
            } else {
                azioniHtml = `
                    <div class="d-flex flex-column gap-2 mt-3">
                        <a class="btn btn-primary w-100" href="dettaglioUtente.php?id=${user.id}">Prenotazioni</a>
                    </div>
                `;
            }

            trDetails.innerHTML = `
                <td colspan="10">
                    <div class="p-3 bg-white border-start border-info shadow-sm">
                        <div class="detail-item"><span class="detail-label">ID:</span> <span>${user.id || 'N/D'}</span></div>
                        <div class="detail-item"><span class="detail-label">Email:</span> <span>${user.email || 'N/D'}</span></div>
                        <div class="detail-item"><span class="detail-label">Ruolo:</span> <span>${user.ruolo || 'N/D'}</span></div>
                        <div class="detail-item"><span class="detail-label">Punteggio:</span> <span>${user.punteggio || '0'}</span></div>

                        <div class="mt-3 pt-2 border-top">
                            <p class="small text-muted mb-2 text-uppercase fw-bold">Azioni:</p>
                            ${azioniHtml}
                        </div>
                    </div>
                </td>
            `;
            container.appendChild(trDetails);
        });

        if (btnAltro) {
            btnAltro.classList.toggle('d-none', utenti.length !== 10);
            btnAltro.classList.toggle('d-block', utenti.length === 10);
        }

    } catch (error) {
        console.error("Errore nel caricamento utenti:", error);
    }
}

document.addEventListener("click", function(e) {
    const target = e.target;

    if (target.classList.contains('btn-info-mobile') || target.closest('.btn-info-mobile')) {
        const btn = target.classList.contains('btn-info-mobile') ? target : target.closest('.btn-info-mobile');
        const mainRow = btn.closest('tr');
        const detailsRow = mainRow.nextElementSibling;

        if (detailsRow && detailsRow.classList.contains('mobile-details-row')) {
            if (detailsRow.classList.contains('d-none')) {
                detailsRow.classList.remove('d-none');
                detailsRow.classList.add('d-table-row');
                btn.innerHTML = 'Chiudi';
                btn.classList.replace('btn-secondary', 'btn-dark');
            } else {
                detailsRow.classList.remove('d-table-row');
                detailsRow.classList.add('d-none');
                btn.innerHTML = 'Info';
                btn.classList.replace('btn-dark', 'btn-secondary');
            }
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const btnSearch = document.getElementById("searchBtn");
    const searchInput = document.getElementById("searchInput");

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

    document.querySelectorAll(".sort_btn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("sort_type").value = this.dataset.sort;
            caricaUtenti(false);
        });
    });

    const btnAltro = document.getElementById("caricaAltro");
    if (btnAltro) {
        btnAltro.addEventListener("click", () => caricaUtenti(true));
    }

    caricaUtenti(false);
});
