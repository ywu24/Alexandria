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
})();
