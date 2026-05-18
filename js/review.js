(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const titolo = document.getElementById('titolo');
        const titoloCounter = document.getElementById('titolo-counter');
        if (titolo && titoloCounter) {
            titolo.addEventListener('input', function () {
                titoloCounter.innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
            });
        }

        const messaggio = document.getElementById('messaggio');
        const messaggioCounter = document.getElementById('messaggio-counter');
        if (messaggio && messaggioCounter) {
            messaggio.addEventListener('input', function () {
                messaggioCounter.innerText = 'Caratteri rimanenti: ' + (500 - this.value.length);
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var msgDiv = document.getElementById('messages');
        if (msgDiv && msgDiv.getAttribute('data-punti-guadagnati') === '1') {
            var pointsAlert = document.createElement('div');
            pointsAlert.className = 'alert alert-warning position-fixed top-0 end-0 m-3 shadow-md fw-bold fs-md';
            pointsAlert.setAttribute('role', 'alert');
            pointsAlert.style.cssText = 'z-index: 9999; opacity: 0; transition: opacity 0.5s;';
            pointsAlert.innerHTML = '<i class="fas fa-coins me-2"></i> +5 Punti guadagnati!';

            document.body.appendChild(pointsAlert);

            requestAnimationFrame(function () {
                pointsAlert.style.opacity = '1';
            });

            setTimeout(function () {
                pointsAlert.style.opacity = '0';
                setTimeout(function () {
                    pointsAlert.remove();
                }, 500);
            }, 3500);
        }
    });
})();
