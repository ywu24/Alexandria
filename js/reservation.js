/**
 * Alexandria Library Management System
 *
 * @file User bookings page — booking cancellation and terminated bookings loader
 */

(function () {
    document.addEventListener('DOMContentLoaded', function () {

        document.addEventListener('click', async function (event) {

            var deleteBtn = event.target.closest('.delete-button');
            if (deleteBtn) {
                var idPrenotazione = deleteBtn.getAttribute('data-id');
                try {
                    var formData = new FormData();
                    formData.append('id', idPrenotazione);
                    var response = await fetch('../api/cancel-booking.php', {
                        method: 'POST',
                        body: formData
                    });

                    var data = await response.json();
                    if (data.success) {
                        var row = deleteBtn.closest('.book-container');
                        row.remove();
                        showMessage(data.message);
                    } else {
                        showMessage(data.message, "errore");
                    }
                } catch (error) {
                    showMessage("Errore: " + error, "errore");
                }
            }

            var btn = event.target.closest("#load-terminated");
            if (btn) {
                var container = document.getElementById('terminate-container');
                var email = btn.getAttribute('data-id-utente');
                btn.disabled = true;
                btn.textContent = "Caricamento...";

                try {
                    var formData = new FormData();
                    formData.append('email', email);

                    var response = await fetch('../api/terminated-bookings.php', {
                        method: 'POST',
                        body: formData
                    });

                    var data = await response.json();
                    if (data.length === 0) {
                        container.innerHTML = "<div class='alert alert-info'>Nessuna prenotazione terminata trovata.</div>";
                    } else {
                        var html = "<h3 class='mb-4 mt-2'>Prenotazioni Terminate</h3>";

                        data.forEach(function (row) {
                            var ritardoInfo = "";
                            var ritardoLabel = "";

                            if (row.FinePrestito > row.FineAttesa) {
                                ritardoInfo = "<span><strong>Scadenza originale:</strong> " + row.FineAttesa + "</span><br>";
                                ritardoLabel = "con ritardo";
                            }

                            html +=
                                '<div class="book-container shadow-sm">' +
                                    '<div class="row g-0">' +
                                        '<div class="col-md-9 left-panel p-3 d-flex">' +
                                            '<img src="../img/books/' + row.Copertina + '" class="book-cover me-4 rounded shadow-sm" alt="Copertina">' +
                                            '<div class="flex-grow-1 text-start">' +
                                                '<h4 class="h4 fw-bold mb-1">' + row.Nome + '</h4>' +
                                                '<p class="info-meta mb-2 fs-md">' + row.Autore + ' | ' + row.CasaEditrice + '</p>' +
                                                '<p class="status-dot text-muted mb-2">● Terminata ' + ritardoLabel + '</p>' +
                                                '<div class="small text-muted mb-3">' +
                                                    '<span><strong>Inizio prestito:</strong> ' + row.InizioPrestito + '</span><br>' +
                                                    '<span><strong>Fine prestito:</strong> ' + row.FinePrestito + '</span><br>' +
                                                    ritardoInfo +
                                                    '<span class="text-dark"><strong>ISBN:</strong> ' + row.ISBN + '</span>' +
                                                '</div>' +
                                                '<div class="container-pulsanti">' +
                                                    (row.recensito == 0
                                                        ? '<a href="../recensione/recensione.php?id=' + row.idOpera + '" class="btn btn-warning px-4">Recensisci</a>'
                                                        : ''
                                                    ) +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' +
                                        '<div class="col-md-3 bg-light d-flex align-items-start p-3 border-start text-start">' +
                                            '<p class="text-muted small mb-0">Storico: Libro restituito correttamente.</p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>';
                        });
                        container.innerHTML = html;
                    }
                    btn.classList.add('d-none');
                } catch (error) {
                    console.error("Errore:", error);
                    alert("Impossibile caricare le prenotazioni terminate.");
                    btn.disabled = false;
                    btn.textContent = "Riprova";
                }
            }
        });
    });
})();
