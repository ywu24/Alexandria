
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById("myform");
    // Selezioniamo direttamente l'input email
    const emailInput = form.querySelector('input[name="email"]');
    const storageKey = 'log_form_email';

    // 1. RIPRISTINO
    if (emailInput) {
        const savedValue = localStorage.getItem(storageKey);
        if (savedValue) {
            emailInput.value = savedValue;
        }
    }

    // 2. SALVATAGGIO
    window.addEventListener('beforeunload', function() {
        if (emailInput && emailInput.value !== "") {
            localStorage.setItem(storageKey, emailInput.value);
        }
    });
});