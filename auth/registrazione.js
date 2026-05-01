localStorage.removeItem('reinvio_count');

document.addEventListener('DOMContentLoaded', function() {
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
    window.addEventListener('beforeunload', function() {
        inputs.forEach(input => {
            // Salviamo solo i campi non sensibili
            if (input.type !== 'password' && input.type !== 'submit' && input.value !== "") {
                localStorage.setItem(storageKeyPrefix + input.name, input.value);
            }
        });
    });
});
//2. Salvo i dati prima che la pagina venga cambiata/refreshata
