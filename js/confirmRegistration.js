document.addEventListener("DOMContentLoaded", function () {
    var reinviaBtn = document.getElementById('reinvia-link');
    var reinviaForm = document.getElementById('reinvia-form');
    var countdownEl = document.getElementById('countdown-timer');
    var statusEl = document.getElementById('reinvia-status');

    if (!reinviaBtn || !reinviaForm) return;

    var remainingSends = parseInt(reinviaBtn.dataset.remainingSends) || 0;
    var cooldownUntil = parseInt(reinviaBtn.dataset.cooldownUntil) || 0;
    var cooldownTimer = null;

    function updateUI() {
        var now = Math.floor(Date.now() / 1000);
        var remaining = cooldownUntil - now;

        if (remaining > 0) {
            reinviaBtn.disabled = true;
            if (countdownEl) {
                countdownEl.textContent = 'Potrai chiedere reinvio in ' + remaining + ' secondi';
                countdownEl.style.display = 'block';
            }
        } else {
            reinviaBtn.disabled = (remainingSends <= 0);
            if (countdownEl) {
                countdownEl.style.display = 'none';
            }
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
                cooldownTimer = null;
            }
        }

        if (statusEl) {
            statusEl.textContent = 'Reinvii rimasti: ' + remainingSends + '/3';
        }
    }

    if (cooldownUntil > Math.floor(Date.now() / 1000)) {
        updateUI();
        cooldownTimer = setInterval(updateUI, 1000);
    } else {
        updateUI();
    }

    reinviaBtn.addEventListener('click', function (e) {
        e.preventDefault();

        if (reinviaBtn.disabled) return;

        if (remainingSends <= 0) return;

        var formData = new FormData(reinviaForm);
        formData.append('ajax', '1');

        fetch(reinviaForm.action, {
            method: 'POST',
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                remainingSends = data.remaining;
                cooldownUntil = Math.floor(Date.now() / 1000) + 15;
                reinviaBtn.dataset.remainingSends = remainingSends;
                reinviaBtn.dataset.cooldownUntil = cooldownUntil;

                if (cooldownTimer) clearInterval(cooldownTimer);
                updateUI();
                cooldownTimer = setInterval(updateUI, 1000);
            }
        })
        .catch(function () {
            console.error('Errore reinvio');
        });
    });
});
