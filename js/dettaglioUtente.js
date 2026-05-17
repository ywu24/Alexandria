/**
 * Alexandria Library Management System
 *
 * @file User detail page — load terminated bookings with review/renewal options
 */

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('load-terminated');
        var container = document.getElementById('terminate-container');

        if (!btn) return;

        btn.addEventListener('click', async function () {
            var idUtente = btn.getAttribute('data-id-utente');

            if (!idUtente) {
                console.error("ID Utente mancante nel bottone");
                return;
            }

            btn.disabled = true;
            btn.textContent = "Caricamento...";

            try {
                var formData = new FormData();
                formData.append('idUtente', idUtente);

                var response = await fetch('../../api/terminated-bookings.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error("Errore del server: " + response.status);

                var data = await response.json();

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '\
                        <hr>\
                        <div class="alert alert-info shadow-sm rounded-lg">\
                            Nessuna prenotazione terminata trovata per questo utente.\
                        </div>';
                    return;
                }

                var html = "<h2 class='h4 mb-4 mt-5 fw-bold text-primary text-start'>Storico Prenotazioni Terminate</h2>";

                data.forEach(function (row) {
                    var statoTesto = "TERMINATA";
                    var statoClasse = "text-muted";

                    if (row.FinePrestito && row.FineAttesa && (new Date(row.FinePrestito) > new Date(row.FineAttesa))) {
                        statoTesto = "TERMINATA IN RITARDO";
                        statoClasse = "text-danger";
                    }

                    var dataInizio = row.InizioPrestito || row.InizioPrenotazione || "-";
                    var dataFine = row.FinePrestito || row.FinePrenotazione || "-";

                    html +=
                        '<div class="book-container shadow-sm animate-in" id="container-prenotazione-' + row.idPrenotazione + '">' +
                            '<div class="row g-0">' +
                                '<div class="col-md-6 left-panel">' +
                                    '<div class="media media-book">' +
                                        '<img src="../../img/books/' + row.Copertina + '" class="me-4 shadow-sm book-cover" alt="Copertina">' +
                                        '<div class="media-body text-start">' +
                                            '<h3 class="h5 fw-bold book-title">' + row.Nome + '</h3>' +
                                            '<p class="text-muted mb-1 small">' + row.Autore + '</p>' +
                                            '<p class="small mb-2 status-badge ' + statoClasse + '">\u25cf ' + statoTesto + '</p>' +
                                            '<div class="small text-muted">' +
                                                '<span>Dal: ' + formattaData(dataInizio) + '</span><br>' +
                                                '<span>Al: ' + formattaData(dataFine) + '</span>' +
                                            '</div>' +
                                            '<button class="btn btn-secondary btn-sm mt-3" onclick="apriDettaglioPrenotazione(' + row.idPrenotazione + ')">Gestisci</button>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6 right-panel hidden-panel" id="dettaglio-content-' + row.idPrenotazione + '"></div>' +
                                '<div class="col-md-6 right-panel text-center text-muted" id="placeholder-' + row.idPrenotazione + '">' +
                                    '<small>Seleziona "Gestisci" per azioni</small>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
                });

                container.innerHTML = html;

            } catch (error) {
                console.error("Errore AJAX:", error);
                container.innerHTML = "<div class='alert alert-danger'>Errore nel caricamento dei dati.</div>";
                btn.disabled = false;
                btn.textContent = "Riprova";
            }
        });
    });

    function formattaData(stringaData) {
        if (!stringaData || stringaData === "-") return "-";
        try {
            var d = new Date(stringaData);
            return d.toLocaleDateString('it-IT');
        } catch (e) { return stringaData; }
    }
})();
