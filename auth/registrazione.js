localStorage.removeItem('reinvio_count');

document.addEventListener('DOMContentLoaded', function () {
    // Selezioniamo tutti gli input del form che hanno un attributo 'name'
    const form = document.getElementById("myform");
    const inputs = form.querySelectorAll('input[name]');
    const storageKeyPrefix = 'reg_form_';

    // 1. RIPRISTINO DEI DATI
    inputs.forEach(input => {
        //le password e submit non si ripristinano
        if (input.type !== 'password' && input.type !== 'submit') {
            const savedValue = localStorage.getItem(storageKeyPrefix + input.name);
            if (savedValue) {
                input.value = savedValue;
            }
        }
    });
    window.addEventListener('beforeunload', function () {
        inputs.forEach(input => {
            // Salviamo solo i campi non sensibili
            if (input.type !== 'password' && input.type !== 'submit' && input.value !== "") {
                localStorage.setItem(storageKeyPrefix + input.name, input.value);
            }
        });
    });

    form.addEventListener('submit', function (e) {
        // Recuperiamo i due campi password
        const password = form.querySelector('input[name="password"]').value;
        const passwordAgain = form.querySelector('input[name="passwordAgain"]').value;

        // Confronto
        if (password !== passwordAgain) {
            // Blocchiamo l'invio del form al PHP
            e.preventDefault();

            showMessage("Le password inserite non coincidono. Riprova.", "errore");

            //puliamo i campi password per sicurezza
            form.querySelector('input[name="password"]').value = "";
            form.querySelector('input[name="passwordAgain"]').value = "";
            form.querySelector('input[name="password"]').focus();
        }
    });
});
function showMessage(text, type = "successo") {
    const div = document.getElementById("messages");

    const p = document.createElement("p");
    p.textContent = text;

    p.classList.add(type);

    div.appendChild(p);

    // sparisce dopo 5 secondi
    setTimeout(() => {
        p.remove();
    }, 4000);
}


