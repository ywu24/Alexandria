(function () {
    async function caricaUtenti(append) {
        if (append === void 0) { append = false; }
        var container = document.getElementById("userTableBody");
        var btnAltro = document.getElementById("loadMoreBtn");
        var form = document.getElementById("filtriForm");

        if (!container) return;

        try {
            var formData = new FormData(form);
            var offset = append ? container.querySelectorAll("tr.user-main-row").length : 0;
            var search = encodeURIComponent(formData.get('search') || '');
            var utenza = formData.get('utenza') || '';
            var sort = document.getElementById("sort_type") ? encodeURIComponent(document.getElementById("sort_type").value) : 'Nome';
            var url = '../../api/users.php?search=' + search + '&sort=' + sort + '&offset=' + offset + '&limit=10';
            if (utenza) url += '&utenza=' + encodeURIComponent(utenza);

            var response = await fetch(url);

            var utenti = await response.json();

            if (!append) {
                container.innerHTML = "";
            }

            if (utenti.length === 0 || utenti.error) {
                if (!append) {
                    container.innerHTML = '<tr><td colspan="10" class="text-center text-muted p-4">Nessun record trovato</td></tr>';
                }
                if (btnAltro) btnAltro.classList.add('d-none');
                return;
            }

            utenti.forEach(function (user) {
                var trMain = document.createElement('tr');
                trMain.className = "user-main-row";

                var mainHtml = '';
                if (USER_TYPE == 1) {
                    mainHtml = '\
                        <th scope="row" class="col-nascondi row-header">' + user.id + '</th>\
                        <td>' + user.name + '</td>\
                        <td>' + user.surname + '</td>\
                        <td class="col-nascondi">' + user.email + '</td>\
                        <td class="col-nascondi">' + user.role + '</td>\
                        <td class="col-nascondi">' + user.score + '</td>\
                        <td class="col-nascondi">\
                            <div class="btn_actions">';

                    if (user.role_id == 3 || user.role_id == 4) {
                        mainHtml += '\
                                <a class="btn btn-primary btn-sm" href="dettaglioUtente.php?id=' + user.id + '">Prenotazioni</a>';
                    }
                    mainHtml += '\
                                <a class="btn btn-primary btn-sm" href="modificaUtente.php?id=' + user.id + '">Modifica</a>\
                                <a class="btn btn-danger btn-sm" href="eliminaUtente.php?id=' + encodeURIComponent(user.email) + '">Elimina</a>\
                            </div>\
                        </td>';
                } else {
                    mainHtml = '\
                        <th scope="row" class="col-nascondi row-header">' + user.id + '</th>\
                        <td>' + user.name + '</td>\
                        <td>' + user.surname + '</td>\
                        <td class="col-nascondi">' + user.email + '</td>\
                        <td class="col-nascondi">' + user.role + '</td>\
                        <td class="col-nascondi">' + user.score + '</td>\
                        <td class="col-nascondi">\
                            <div class="btn_actions text-center">';
                    
                    if (user.role_id == 3 || user.role_id == 4) {
                        mainHtml += '\
                                <a class="btn btn-primary btn-sm" href="dettaglioUtente.php?id=' + user.id + '">Prenotazioni</a>';
                    }
                    mainHtml += '\
                            </div>\
                        </td>';
                }
                trMain.innerHTML = mainHtml;

                var toggleTd = document.createElement('td');
                toggleTd.className = "mobile-only text-center align-middle";
                toggleTd.innerHTML = '<button class="btn btn-sm btn-secondary btn-info-mobile">Info</button>';
                trMain.appendChild(toggleTd);
                container.appendChild(trMain);

                var trDetails = document.createElement('tr');
                trDetails.className = "mobile-details-row d-none";

                var azioniHtml = "";

                if (USER_TYPE == 1) {
                    azioniHtml = '\
                        <div class="d-flex flex-column gap-2 mt-3">';
                    if (user.role_id == 3 || user.role_id == 4) {
                        azioniHtml += '\
                            <a class="btn btn-primary w-100" href="dettaglioUtente.php?id=' + user.id + '">Prenotazioni</a>';
                    }
                    azioniHtml += '\
                            <a class="btn btn-primary w-100" href="modificaUtente.php?id=' + user.id + '">Modifica</a>\
                            <a class="btn btn-danger w-100" href="eliminaUtente.php?id=' + user.email + '">Elimina</a>\
                        </div>';
                } else {
                    azioniHtml = '\
                        <div class="d-flex flex-column gap-2 mt-3">';
                    if (user.role_id == 3 || user.role_id == 4) {
                        azioniHtml += '\
                            <a class="btn btn-primary w-100" href="dettaglioUtente.php?id=' + user.id + '">Prenotazioni</a>';
                    }
                    azioniHtml += '\
                        </div>';
                }

                trDetails.innerHTML = '\
                    <td colspan="10">\
                        <div class="p-3 bg-surface border-start border-info shadow-sm">\
                            <div class="detail-item"><span class="detail-label">ID:</span> <span>' + (user.id || 'N/D') + '</span></div>\
                            <div class="detail-item"><span class="detail-label">Email:</span> <span>' + (user.email || 'N/D') + '</span></div>\
                            <div class="detail-item"><span class="detail-label">Ruolo:</span> <span>' + (user.role || 'N/D') + '</span></div>\
                            <div class="detail-item"><span class="detail-label">Punteggio:</span> <span>' + (user.score || '0') + '</span></div>\
                            <div class="mt-3 pt-2 border-top">\
                                <p class="small text-muted mb-2 text-uppercase fw-bold">Azioni:</p>\
                                ' + azioniHtml + '\
                            </div>\
                        </div>\
                    </td>';
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

    document.addEventListener("click", function (e) {
        var target = e.target;

        if (target.classList.contains('btn-info-mobile') || target.closest('.btn-info-mobile')) {
            var btn = target.classList.contains('btn-info-mobile') ? target : target.closest('.btn-info-mobile');
            var mainRow = btn.closest('tr');
            var detailsRow = mainRow.nextElementSibling;

            if (detailsRow && detailsRow.classList.contains('mobile-details-row')) {
                if (detailsRow.classList.contains('d-none')) {
                    detailsRow.classList.remove('d-none');
                    detailsRow.classList.add('d-table-row');
                    btn.innerHTML = 'Chiudi';
                } else {
                    detailsRow.classList.remove('d-table-row');
                    detailsRow.classList.add('d-none');
                    btn.innerHTML = 'Info';
                }
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        var btnSearch = document.getElementById("searchBtn");
        var searchInput = document.getElementById("searchInput");

        if (btnSearch) {
            btnSearch.addEventListener("click", function () { caricaUtenti(false); });
        }

        if (searchInput) {
            searchInput.addEventListener("keypress", function (e) {
                if (e.key === "Enter") {
                    caricaUtenti(false);
                }
            });
        }

        document.querySelectorAll(".sort_btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                document.getElementById("sort_type").value = this.dataset.sort;
                caricaUtenti(false);
            });
        });

        var btnAltro = document.getElementById("loadMoreBtn");
        if (btnAltro) {
            btnAltro.addEventListener("click", function () { caricaUtenti(true); });
        }

        caricaUtenti(false);
    });
})();
