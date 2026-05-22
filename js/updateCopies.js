/**
 * Gestione dinamica del Catalogo Libri tramite API.
 * 
 * FUNZIONALITÀ PRINCIPALI:
 * 1. Caricamento e Paginazione: Carica i libri a blocchi di 10 con supporto al "Carica altro",
 *    gestendo filtri di ricerca (searchInput) e ordinamento (sort_btn).
 * 2. Interfaccia Mobile: Genera una riga di dettaglio per mostrare i dati nascosti sui piccoli schermi.
 * 3. Gestione Copie Totali: Consente l'aggiornamento rapido del numero di copie direttamente dalla tabella.
 * 4. Dettaglio Copie Singole: Espande una sotto-tabella con lo stato di ogni singola copia (Disponibile/In Prestito).
 * 5. Eliminazione: Permette di eliminare l'intero libro (se ha 0 copie) o le singole copie (se non sono in prestito).
 * 
 * STRUTTURA: Racchiuso in una IIFE (funzione anonima auto-eseguibile) per isolare lo scope ed evitare conflitti globali.
 * Utilizza la Event Delegation (ascolto dei click sul 'document') per gestire gli elementi creati dinamicamente.
 */

(function () {
    var currentSort = 'Nome';
    var currentOffset = 0;
    var LIMIT = 10;

    async function caricaLibri(append) {
        if (append === void 0) { append = false; }
        var tableBody = document.getElementById("libriTableBody");
        var searchInput = document.getElementById("searchInput");
        var loadMoreBtn = document.getElementById("loadMoreBtn");

        if (!append) currentOffset = 0;

        var search = searchInput ? encodeURIComponent(searchInput.value) : "";
        var url = '../../api/books.php?search=' + search + '&sort=' + encodeURIComponent(currentSort) + '&offset=' + currentOffset + '&limit=' + LIMIT;

        try {
            var response = await fetch(url);
            var data = await response.json();

            if (!append) tableBody.innerHTML = "";

            if (!data || data.length === 0 || data.error) {
                if (!append) tableBody.innerHTML = '<tr><td colspan="8" class="text-center p-4">Nessun libro trovato</td></tr>';
                loadMoreBtn.classList.add('d-none');
                return;
            }

            data.forEach(function (libro) {
                var isbn = libro.isbn;
                var btnElimina = libro.copies === 0
                    ? "<button type='button' class='btn btn-danger btn-sm elimina'>Elimina</button>"
                    : "";
                var html = '\
                    <tr data-isbn="' + isbn + '">\
                        <th scope="row" class="row-header"><button class="btn btn-sm btn-info btn-espandi" type="button" data-isbn="' + isbn + '">+</button> ' + isbn + '</th>\
                        <td>' + libro.title + '</td>\
                        <td class="col-nascondi">' + libro.author + '</td>\
                        <td class="col-nascondi">' + libro.genre + '</td>\
                        <td class="col-nascondi">' + libro.year + '</td>\
                        <td class="col-nascondi">' + libro.publisher + '</td>\
                        <td class="col-nascondi">\
                            <div class="copie-cell-wrapper">\
                                <input type="number" value="' + libro.copies + '" class="form-control form-control-sm d-inline-block w-auto input-copie">\
                                <button class="btn btn-outline-info btn-sm save">Salva</button>\
                            </div>\
                        </td>\
                        <td class="col-nascondi">\
                            <a class="btn btn-primary btn-sm btn_modifica" href="modificaLibro.php?id=' + isbn + '">Modifica</a>\
                            ' + btnElimina + '\
                        </td>\
                        <td class="mobile-only">\
                            <button class="btn btn-sm btn-secondary btn-info-mobile" type="button">Info</button>\
                        </td>\
                    </tr>\
                    <tr id="row-details-' + isbn + '" class="bg-light d-none">\
                        <td colspan="9"><div id="content-' + isbn + '" class="p-3">Caricamento in corso...</div></td>\
                    </tr>';
                tableBody.insertAdjacentHTML('beforeend', html);
            });

            if (data.length === LIMIT) {
                loadMoreBtn.classList.remove('d-none');
                loadMoreBtn.classList.add('d-inline-block');
                currentOffset += LIMIT;
            } else {
                loadMoreBtn.classList.remove('d-inline-block');
                loadMoreBtn.classList.add('d-none');
            }
        } catch (e) {
            console.error("Errore:", e);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        caricaLibri();

        document.getElementById("searchBtn").onclick = function () { caricaLibri(false); };
        document.getElementById("searchInput").onkeyup = function (e) { if (e.key === "Enter") caricaLibri(false); };
        document.getElementById("loadMoreBtn").onclick = function () { caricaLibri(true); };

        document.querySelectorAll(".sort_btn").forEach(function (btn) {
            btn.onclick = function () {
                currentSort = this.dataset.sort;
                caricaLibri(false);
            };
        });
    });

    document.addEventListener("click", async function (e) {
        var target = e.target;

        if (target.classList.contains('btn-info-mobile') || target.closest('.btn-info-mobile')) {
            var btn = target.classList.contains('btn-info-mobile') ? target : target.closest('.btn-info-mobile');
            var row = btn.closest("tr");
            var isbn = row.dataset.isbn;
            var mobileRowId = 'mobile-info-' + isbn;
            var mobileRow = document.getElementById(mobileRowId);

            if (mobileRow) {
                if (mobileRow.classList.contains('d-none')) {
                    mobileRow.classList.remove('d-none');
                    mobileRow.classList.add('d-table-row');
                    btn.innerHTML = 'Chiudi';
                } else {
                    mobileRow.classList.remove('d-table-row');
                    mobileRow.classList.add('d-none');
                    btn.innerHTML = 'Info';
                }
            } else {
                var tds = row.querySelectorAll("td");
                var autore = tds[1] ? tds[1].innerHTML : '';
                var genere = tds[2] ? tds[2].innerHTML : '';
                var anno = tds[3] ? tds[3].innerHTML : '';
                var casa = tds[4] ? tds[4].innerHTML : '';
                var copie = tds[5] ? tds[5].innerHTML : '';
                var azioni = tds[6] ? tds[6].innerHTML : '';

                var html = '\
                    <tr id="' + mobileRowId + '" class="mobile-details-row d-none">\
                        <td colspan="3" class="p-0">\
                            <div class="p-3 bg-surface border-start border-info shadow-sm">\
                                <div class="detail-item"><span class="detail-label">Autore:</span> <span>' + (autore || 'N/D') + '</span></div>\
                                <div class="detail-item"><span class="detail-label">Genere:</span> <span>' + (genere || 'N/D') + '</span></div>\
                                <div class="detail-item"><span class="detail-label">Anno:</span> <span>' + (anno || 'N/D') + '</span></div>\
                                <div class="detail-item"><span class="detail-label">Casa Editrice:</span> <span>' + (casa || 'N/D') + '</span></div>\
                                <div class="detail-item"><span class="detail-label">Copie:</span> <span>' + (copie || 'N/D') + '</span></div>\
                                <div class="mt-3 pt-2 border-top">\
                                    <p class="small text-muted mb-2 text-uppercase fw-bold">Azioni:</p>\
                                    <div class="d-flex flex-column gap-2">' + azioni + '</div>\
                                </div>\
                            </div>\
                        </td>\
                    </tr>';
                row.insertAdjacentHTML('afterend', html);
                mobileRow = document.getElementById(mobileRowId);
                mobileRow.classList.remove('d-none');
                mobileRow.classList.add('d-table-row');
                btn.innerHTML = 'Chiudi';
            }
            return;
        }

        if (target.classList.contains("save")) {
            var row = target.closest("tr");
            var input = row.querySelector("input[type='number']");
            var isbn = row.dataset.isbn;
            var confirmed = await showConfirm("Aggiornare le copie?");
            if (!confirmed) return;

            var fd = new FormData();
            fd.append("isbn", isbn);
            fd.append("copie", input.value);
            

            var res = await fetch("updateCopie.php", { method: "POST", body: fd });
            var txt = await res.text();
            if (txt.includes("ok")) showToast("Aggiornato!", "success");
            var espandere = row.getElementsByClassName('btn-espandi');
            console.log(espandere);
            espandere[0].click();
            espandere[0].click();

        }

        if (target.classList.contains('btn-espandi')) {
            var isbn = target.dataset.isbn;
            var targetRow = document.getElementById('row-details-' + isbn);
            var contentDiv = document.getElementById('content-' + isbn);

            if (!targetRow.classList.contains('d-none')) {
                targetRow.classList.add('d-none');
                targetRow.classList.remove('d-table-row');
                target.textContent = '+';
                target.classList.replace('btn-danger', 'btn-info');
            } else {
                target.textContent = '...';
                var res = await fetch('../../api/copies.php?isbn=' + encodeURIComponent(isbn));
                var data = await res.json();
                renderCopie(contentDiv, data, e);
                targetRow.classList.remove('d-none');
                targetRow.classList.add('d-table-row');
                target.textContent = '-';
                target.classList.replace('btn-info', 'btn-danger');
            }
        }

        if (target.classList.contains("elimina")) {
            var row = target.closest("tr");
            var confirmed = await showConfirm("Sei sicuro di voler eliminare l'intera opera e tutte le sue copie?");
            if (!confirmed) return;
            var fd = new FormData();
            fd.append("isbn", row.dataset.isbn);
            var res = await fetch("eliminaLibro.php", { method: "POST", body: fd });
            var result = await res.json();
            if (result.success) {
                row.remove();
                var detailsRow = document.getElementById('row-details-' + row.dataset.isbn);
                if (detailsRow) detailsRow.remove();
                showToast(result.message, "success");
            } else {
                showToast(result.message, "error");
            }
        }

        if (target.classList.contains("delete") || target.closest(".delete")) {
            var btn = target.classList.contains("delete") ? target : target.closest(".delete");
            var row = btn.closest("tr");
            var idCopia = row.dataset.id;

            var confirmed = await showConfirm("Vuoi eliminare definitivamente la copia #" + idCopia + "?");
            if (!confirmed) return;

            var fd = new FormData();
            fd.append("id", idCopia);

            try {
                var res = await fetch("elimina_copia.php", { method: "POST", body: fd });
                var result = await res.text();

                 if (result.includes("ok")) {
                     showToast(result.slice(2), "success");
                    var rigaDettaglio = btn.closest('tr.bg-light') || btn.closest('td').closest('tr');
                    var rigaPrincipale = rigaDettaglio.previousElementSibling;
                    var btnEspandi = rigaPrincipale.querySelector('.btn-espandi');

                    if (btnEspandi) {
                        btnEspandi.click();
                        btnEspandi.click();
                    }
                } else {
                     showToast(result, "error");
                }
            } catch (error) {
                     showToast("Errore di connessione", "error");
            }
        }

        if (target.classList.contains("dettagli")) {
            window.location.href = "../../prenotazione/prenotazioneAdmin.php";
        }
    });

    function renderCopie(container, copie, e) {
        container.innerHTML = "";

        if (copie.length === 0 || copie.error) {
            container.innerHTML = "<div class='alert alert-info'>Nessuna copia disponibile per questo volume.</div>";
            return;
        }

        var html = '\
            <table class="table table-sm table-striped bg-surface border m-0">\
                <thead>\
                    <tr>\
                        <th>ID Copia</th>\
                        <th>Stato</th>\
                        <th class="text-end">Azioni</th>\
                    </tr>\
                </thead>\
                <tbody>';

        for (var i = 0; i < copie.length; i++) {
            var copia = copie[i];
            var isDisponibile = (copia.status === 'available');
            var statoTesto = isDisponibile ? 'Disponibile' : 'In Prestito';
            var badgeClass = isDisponibile ? 'bg-success' : 'bg-danger';

            var disabledAttr = isDisponibile ? '' : 'disabled';
            var deleteAction = isDisponibile
                ? ''
                : 'onclick="showToast(\'Impossibile eliminare una copia in prestito!\', \'error\')"';

            html += '\
                <tr data-id="' + copia.id + '">\
                    <td><strong>#' + copia.id + '</strong></td>\
                    <td><span class="badge ' + badgeClass + '">' + statoTesto + '</span></td>\
                    <td class="text-end">\
                        ' + (!isDisponibile ? '<button class="btn btn-sm btn-warning dettagli">Dettagli</button>' : '') + '\
                        <button class="btn btn-sm ' + (isDisponibile ? 'btn-outline-danger delete' : 'btn-outline-secondary') + '"\
                                ' + disabledAttr + '\
                                ' + deleteAction + '>\
                            <i class="fas fa-trash"></i> Elimina\
                        </button>\
                    </td>\
                </tr>';
        }

        html += '</tbody></table>';
        container.innerHTML = html;
    }
})();
