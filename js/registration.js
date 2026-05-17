(function () {
    localStorage.removeItem('reinvio_count');

    document.addEventListener('DOMContentLoaded', function () {
        // Selezioniamo tutti gli input del form che hanno un attributo 'name'
        var form = document.getElementById("myform");
        if (!form) return;
        var inputs = form.querySelectorAll('input[name]');
        var storageKeyPrefix = 'reg_form_';
        // 1. RIPRISTINO DEI DATI
        inputs.forEach(function (input) {
            if (input.type !== 'password' && input.type !== 'submit') {
                var savedValue = localStorage.getItem(storageKeyPrefix + input.name);
                if (savedValue) {
                    input.value = savedValue;
                }
            }
        });

        window.addEventListener('beforeunload', function () {
            inputs.forEach(function (input) {
                // Salviamo solo i campi non sensibili
                if (input.type !== 'password' && input.type !== 'submit' && input.value !== "") {
                    localStorage.setItem(storageKeyPrefix + input.name, input.value);
                }
            });
        });

        form.addEventListener('submit', function (e) {
            // Recuperiamo i due campi password
            var password = form.querySelector('input[name="password"]').value;
            var passwordAgain = form.querySelector('input[name="passwordAgain"]').value;
            // 1. Controllo Robustezza: Lunghezza minima e Carattere Speciale
            var specialChars = /[!#$.,:;()@%^\-&_+=[\]|\\/<>?~`]/;

            if (password.length < 8 || !specialChars.test(password)) {
                showMessage("La password deve essere di almeno 8 caratteri e contenere almeno un carattere speciale.", "errore");
                e.preventDefault();
                return;
            }

            // 2. Controllo Corrispondenza
            if (password !== passwordAgain) {
                // Blocchiamo l'invio del form al PHP
                showMessage("Le password inserite non coincidono. Riprova.", "errore");
                // Puliamo i campi password per sicurezza
                form.querySelector('input[name="password"]').value = "";
                form.querySelector('input[name="passwordAgain"]').value = "";
                form.querySelector('input[name="password"]').focus();
                e.preventDefault();
                return;
            }
        });
    });
})();
