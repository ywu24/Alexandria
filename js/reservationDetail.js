/**
 * Alexandria Library Management System
 *
 * @file Booking detail panel — admin/librarian view of single booking with confirm/complete/cancel actions
 */
(function () {
    function showToast(message, type) {
        try {
            type = type || 'success';
            var container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
                document.body.appendChild(container);
            }

            var icons = { success: '\u2713', error: '\u2717', warning: '\u26A0' };
            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.style.cssText = 'pointer-events:auto;display:flex;align-items:center;gap:12px;min-width:300px;max-width:420px;padding:14px 20px;border-radius:12px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,0.12);border-left:5px solid ' + (type === 'success' ? '#2d6a4f' : type === 'error' ? '#c1292e' : '#e09f3e') + ';font-size:0.875rem;color:#1e1e1e;animation:toastSlideIn 0.35s ease-out;';

            var iconSpan = document.createElement('span');
            iconSpan.className = 'toast-icon';
            iconSpan.textContent = icons[type] || '';
            iconSpan.style.cssText = 'font-size:1.3rem;flex-shrink:0;line-height:1;color:' + (type === 'success' ? '#2d6a4f' : type === 'error' ? '#c1292e' : '#e09f3e') + ';';

            var msgSpan = document.createElement('span');
            msgSpan.className = 'toast-message';
            msgSpan.textContent = message;
            msgSpan.style.cssText = 'flex:1;line-height:1.4;';

            var closeBtn = document.createElement('button');
            closeBtn.className = 'toast-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = 'flex-shrink:0;background:none;border:none;color:#6b6b6b;font-size:1.1rem;cursor:pointer;padding:0 4px;line-height:1;';
            closeBtn.onclick = function () {
                toast.style.animation = 'toastSlideOut 0.3s ease-in forwards';
                setTimeout(function () { if (toast.parentNode) toast.remove(); }, 300);
            };

            toast.appendChild(iconSpan);
            toast.appendChild(msgSpan);
            toast.appendChild(closeBtn);
            container.appendChild(toast);

            setTimeout(function () {
                toast.style.animation = 'toastSlideOut 0.3s ease-in forwards';
                setTimeout(function () { if (toast.parentNode) toast.remove(); }, 300);
            }, 4000);

            var style = document.getElementById('toast-keyframes');
            if (!style) {
                style = document.createElement('style');
                style.id = 'toast-keyframes';
                style.textContent = '@keyframes toastSlideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes toastSlideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}';
                document.head.appendChild(style);
            }
        } catch (e) {
            console.error('Toast error:', e);
            alert(message);
        }
    }

    function showConfirm(message) {
        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML =
                '<div class="confirm-dialog">' +
                    '<p>' + message + '</p>' +
                    '<div class="confirm-actions">' +
                        '<button class="btn btn-secondary" id="confirm-cancel">Annulla</button>' +
                        '<button class="btn btn-danger" id="confirm-ok">Conferma</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(overlay);

            document.getElementById('confirm-ok').addEventListener('click', function () {
                overlay.classList.add('confirm-hiding');
                setTimeout(function () { overlay.remove(); resolve(true); }, 150);
            });
            document.getElementById('confirm-cancel').addEventListener('click', function () {
                overlay.classList.add('confirm-hiding');
                setTimeout(function () { overlay.remove(); resolve(false); }, 150);
            });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    overlay.classList.add('confirm-hiding');
                    setTimeout(function () { overlay.remove(); resolve(false); }, 150);
                }
            });
        });
    }

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
