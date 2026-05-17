(function () {
    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("filtriForm");
        var btnCarica = document.getElementById("caricaAltro");

        if (form) {
            form.addEventListener("change", function () { caricaPrenotazioni(false); });

            form.addEventListener("submit", function (e) {
                e.preventDefault();
                caricaPrenotazioni(false);
            });

            form.addEventListener("reset", function () {
                setTimeout(function () { caricaPrenotazioni(false); }, 10);
            });
        }

        if (btnCarica) {
            btnCarica.addEventListener("click", function () { caricaPrenotazioni(true); });
        }

        caricaPrenotazioni(false);
    });

    async function caricaPrenotazioni(append) {
        if (append === void 0) { append = false; }
        var container = document.getElementById("bookings-container");
        var btn = document.getElementById("caricaAltro");
        var form = document.getElementById("filtriForm");

        if (!container) return;

        try {
            var formData = new FormData(form);
            var offset = append ? container.querySelectorAll(".book-container").length : 0;
            var filtro_stato = encodeURIComponent(formData.get('filtro_stato') || 'tutti');
            var data_inizio = formData.get('data_inizio') || '';
            var data_fine = formData.get('data_fine') || '';
            var url = '../api/bookings.php?status=' + filtro_stato + '&sort=idPrenotazione%20DESC&limit=10&offset=' + offset;
            if (data_inizio) url += '&from=' + encodeURIComponent(data_inizio);
            if (data_fine) url += '&to=' + encodeURIComponent(data_fine);

            var response = await fetch(url);

            if (!response.ok) throw new Error("Errore nel server");

            var prenotazioni = await response.json();

            if (!append) container.innerHTML = "";

            if (prenotazioni.length === 0) {
                if (!append) {
                    container.innerHTML = '<div class="alert alert-info text-center w-100">Nessuna prenotazione trovata.</div>';
                }
                if (btn) btn.classList.add('d-none');
                return;
            }

            prenotazioni.forEach(function (row) {
                var div = document.createElement('div');
                div.className = "book-container shadow-sm animate-in";
                div.id = 'container-prenotazione-' + row.id;

                div.innerHTML = ''
                    + '<div class="row g-0">'
                        + '<div class="col-md-6 left-panel">'
                            + '<div class="media media-book">'
                                + '<img src="../img/books/' + row.cover + '" class="me-4 shadow-sm book-cover" alt="Copertina">'
                                + '<div class="media-body">'
                                    + '<h3 class="h5 fw-bold book-title">' + row.title + '</h3>'
                                    + '<p class="text-muted mb-1 small">' + row.author + '</p>'
                                    + '<p class="small mb-2 status-badge ' + row.color + '">'
                                        + '\u25CF ' + row.status.toUpperCase()
                                    + '</p>'
                                    + '<div class="small text-muted">'
                                        + '<span>Dal: ' + row.start + '</span><br>'
                                        + '<span>Al: ' + row.end + '</span><br>'
                                        + '<span class="text-dark">User: ' + row.email + '</span>'
                                    + '</div>'
                                    + '<button class="btn btn-secondary btn-sm mt-3" onclick="apriDettaglioPrenotazione(' + row.id + ')">Gestisci</button>'
                                + '</div>'
                            + '</div>'
                        + '</div>'
                        + '<div class="col-md-6 right-panel d-none" id="dettaglio-content-' + row.id + '"></div>'
                        + '<div class="col-md-6 right-panel text-center text-muted" id="placeholder-' + row.id + '">'
                            + '<small>Seleziona "Gestisci" per azioni</small>'
                        + '</div>'
                    + '</div>';

                container.appendChild(div);
            });

            if (btn) {
                btn.classList.toggle('d-none', prenotazioni.length !== 10);
                btn.classList.toggle('d-inline-block', prenotazioni.length === 10);
            }

        } catch (error) {
            console.error("Errore:", error);
        }
    }
})();
