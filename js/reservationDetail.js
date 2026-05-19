/**
 * Alexandria Library Management System
 *
 * @file Booking detail panel — admin/librarian view of single booking with confirm/complete/cancel actions
 */
(function () {
    window.apriDettaglioPrenotazione = async function (id) {
        document.querySelectorAll('[id^="dettaglio-content-"]').forEach(function (el) {
            el.classList.add('d-none');
            el.style.display = '';
            el.innerHTML = '';
            var ph = document.getElementById('placeholder-' + el.id.replace('dettaglio-content-', ''));
            if (ph) ph.style.display = '';
        });

        var targetDiv = document.getElementById('dettaglio-content-' + id);
        if (!targetDiv) return;

        try {
            targetDiv.classList.remove('d-none');
            targetDiv.style.display = 'block';
            targetDiv.innerHTML = '<p class="text-muted small">Caricamento...</p>';

            var ph = document.getElementById('placeholder-' + id);
            if (ph) ph.style.display = 'none';

            var currentPath = window.location.pathname;
            var apiPath = '';
            if (currentPath.includes('prenotazione')) {
                apiPath = '../api/';
            } else if (currentPath.includes('dashboardUtenti')) {
                apiPath = '../../api/';
            }

            var response = await fetch(apiPath + 'booking-detail.php?id=' + id);
            var data = await response.json();

            var pulsantiAzione = '';
            var statoNormalizzato = data.Stato.toLowerCase();

            if (statoNormalizzato === 'prenotato') {
                pulsantiAzione = '\
                    <div class="mt-3 d-flex gap-2">\
                        <button class="btn btn-sm btn-success flex-fill" onclick="eseguiAzione(' + id + ', \'conferma\')">Conferma</button>\
                        <button class="btn btn-sm btn-danger flex-fill" onclick="eseguiAzione(' + id + ', \'elimina\')">Elimina</button>\
                    </div>';
            } else if (statoNormalizzato === 'in prestito' || statoNormalizzato === 'in ritardo') {
                pulsantiAzione = '\
                    <div class="mt-3 d-grid">\
                        <button class="btn btn-sm btn-warning" onclick="eseguiAzione(' + id + ', \'termina\')">Conferma Consegna</button>\
                    </div>';
            }

            targetDiv.innerHTML = '\
                <div class="slide-in-right p-2">\
                    <div class="d-flex justify-content-between align-items-center mb-3">\
                        <h3 class="m-0 fs-md text-dark fw-bold">Gestione</h3>\
                        <button type="button" class="btn-close" aria-label="Close" onclick="chiudiDettaglio(' + id + ')"></button>\
                    </div>\
                    <div class="fs-sm mb-3">\
                        <p class="text-truncate mb-1"><strong>User:</strong> ' + data.Email + '</p>\
                        <div class="bg-surface p-2 rounded border">\
                            <div><small class="text-muted">INIZIO:</small> <b>' + data.Inizio + '</b></div>\
                            <div><small class="text-muted">SCADENZA:</small> <b>' + data.Fine + '</b></div>\
                        </div>\
                    </div>\
                    <div class="mb-2">\
                        <span class="fw-bold fs-sm" style="color: ' + data.Color + ';">\
                            STATO: ' + data.Stato.toUpperCase() + '\
                        </span>\
                    </div>\
                    ' + pulsantiAzione + '\
                    <div class="text-center mt-3 border-top pt-2">\
                        <button class="btn btn-sm btn-link text-muted" onclick="chiudiDettaglio(' + id + ')">Annulla</button>\
                    </div>\
                </div>';
        } catch (error) {
            console.error("Errore:", error);
            targetDiv.innerHTML = '<div class="text-danger small">Errore caricamento dati.</div>';
        }
    };

    window.eseguiAzione = async function (id, azione) {
        var labels = { conferma: 'Conferma prenotazione', elimina: 'Elimina prenotazione', termina: 'Conferma consegna' };
        var confirmed = await showConfirm("Sei sicuro di voler eseguire l'operazione: " + (labels[azione] || azione) + "?");
        if (!confirmed) return;
        try {
            var formData = new URLSearchParams();
            formData.append('id', id);
            formData.append(azione, 'true');

            var currentPath = window.location.pathname;
            var apiPath = '';
            if (currentPath.includes('prenotazione')) {
                apiPath = '../api/';
            } else if (currentPath.includes('dashboardUtenti')) {
                apiPath = '../../api/';
            }

            var response = await fetch(apiPath + 'handle-booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });

            var result = await response.json();
            if (result.success) {
                showToast(result.message, 'success');
                chiudiDettaglio(id);

                if (azione === 'elimina') {
                    var card = document.getElementById('container-prenotazione-' + id);
                    if (card) {
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(function () { if (card.parentNode) card.remove(); }, 300);
                    }
                } else {
                    try {
                        var refreshResponse = await fetch(apiPath + 'booking-detail.php?id=' + id);
                        if (!refreshResponse.ok) {
                            var card = document.getElementById('container-prenotazione-' + id);
                            if (card) {
                                card.style.transition = 'opacity 0.3s';
                                card.style.opacity = '0';
                                setTimeout(function () { if (card.parentNode) card.remove(); }, 300);
                            }
                            return;
                        }
                        var refreshData = await refreshResponse.json();
                        var card = document.getElementById('container-prenotazione-' + id);
                        if (card) {
                            var badge = card.querySelector('.status-badge');
                            if (badge) {
                                badge.className = badge.className.replace(/text-\S+/g, '').trim() + ' ' + (refreshData.Color || 'text-muted');
                                badge.textContent = '\u25CF ' + (refreshData.Stato || '').toUpperCase();
                            }
                        }
                    } catch (e) {
                        console.error('Refresh card error:', e);
                    }
                }
                try {
                    // aggiorna statistiche
                    var refreshStats = await fetch(apiPath + 'booking-stats.php');
                        if (refreshStats.ok) {
                            
                            statsData = await refreshStats.json();
                            var stats = document.getElementById('statsTotale');
                            if (stats) {
                                stats.textContent = statsData.totale || '0';
                            }
                            stats = document.getElementById('statsPrenotati');
                            if (stats) {
                                stats.textContent = statsData.prenotati || '0';
                            }
                            stats = document.getElementById('statsInPrestito');
                            if (stats) {
                                stats.textContent = statsData.in_prestito || '0';
                            }
                            stats = document.getElementById('statsInRitardo');
                            if (stats) {
                                stats.textContent = statsData.in_ritardo || '0';
                            }
                            stats = document.getElementById('statsTerminati');
                            if (stats) {
                                stats.textContent = statsData.terminati || '0';
                            }
                            
                        }
                } catch (e) {
                    console.error('Refresh stats error:', e)
                }
            } else {
                showToast("Errore dal server: " + result.message, 'error');
            }
        } catch (error) {
            console.error("Errore durante l'invio:", error);
            showToast("Errore di connessione.", 'error');
        }
    };

    window.chiudiDettaglio = function (id) {
        var targetDiv = document.getElementById('dettaglio-content-' + id);
        if (targetDiv) {
            targetDiv.classList.add('d-none');
            targetDiv.style.display = '';
            targetDiv.innerHTML = '';
        }
        var ph = document.getElementById('placeholder-' + id);
        if (ph) ph.style.display = '';
    };
})();
